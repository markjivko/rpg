<?php
/**
 * Stephino_Rpg_WordPress_RestApi
 * 
 * @title      WordPress Rest API integration
 * @desc       Register JSON Rest API methods
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_WordPress_RestApi extends WP_REST_Controller {
    
    // Rest keys
    const REST_METHODS                = 'methods';
    const REST_ARGS                   = 'args';
    const REST_ARGS_REQUIRED          = 'required';
    const REST_ARGS_DEFAULT           = 'default';
    const REST_ARGS_VALIDATE_CALLBACK = 'validate_callback';
    const REST_CALLBACK               = 'callback';
    const REST_CALLBACK_PERMISSION    = 'permission_callback';
    
    // Response keys
    const RESP_RESULT  = 'result';
    const RESP_MESSAGE = 'message';
    
    // Allow all
    const CALLBACK_ALL = '__return_true';
    
    // Request keys
    const REQ_USER_EMAIL    = 'userEmail';
    const REQ_USER_PASSWORD = 'userPassword';
    
    /**
     * Stephino RPG Rest API
     */
    public function __construct() {
        $this->namespace = Stephino_Rpg::PLUGIN_SLUG . '/v1';
    }
    
    /**
     * Register the routes
     */
    public function register_routes() {
        /**
         * Prepare the callback for this method
         * 
         * @return callable
         */
        $getCallback = function($methodName) {
            /**
             * Catch Exceptions and wrapp them as WP_Error objects<br/>
             * 
             * @param WP_REST_Request $request
             * @return WP_Error|array A WordPress error instance of an associative array of <ul>
             * <li>self::RESP_MESSAGE => (string) Method output</li>
             * <li>self::RESP_RESULT => (mixed) Method result</li>
             * </ul>
             */
            return function($request) use($methodName) {
                ob_start();
                $result = null;
        
                try {
                    if (!method_exists($this, $methodName)) {
                        throw new Exception('Invalid API method');
                    }
                    
                    // Get the result
                    $result = call_user_func(
                        array($this, $methodName), 
                        $request
                    );
                } catch (Exception $exc) {
                    $result = new WP_Error(
                        'stephino_rpg_exc', 
                        $exc->getMessage(), 
                        array('status' => 400)
                    );
                }
                
                // Prepare the message content
                $message = ob_get_clean();
                
                // Format the result
                return $result instanceof WP_Error 
                    ? $result 
                    : array(
                        self::RESP_MESSAGE => $message,
                        self::RESP_RESULT  => $result
                    );
            };
        };
        
        // Plugin version
        register_rest_route($this->namespace, '/version', array(
            self::REST_METHODS => WP_REST_Server::READABLE,
            self::REST_CALLBACK => $getCallback('getVersion'),
            self::REST_CALLBACK_PERMISSION => self::CALLBACK_ALL
        ));

        // Get auth key for remote clients (Desktop and Android)
        register_rest_route($this->namespace, '/auth', array(
            self::REST_METHODS => WP_REST_Server::CREATABLE,
            self::REST_CALLBACK => $getCallback('getAuth'),
            self::REST_CALLBACK_PERMISSION => self::CALLBACK_ALL,
            self::REST_ARGS => array(
                self::REQ_USER_EMAIL => array(
                    self::REST_ARGS_VALIDATE_CALLBACK => function($arg) {
                        return strlen($arg) && filter_var($arg, FILTER_VALIDATE_EMAIL);
                    },
                    self::REST_ARGS_REQUIRED => true,
                ),
                self::REQ_USER_PASSWORD => array(
                    self::REST_ARGS_DEFAULT => '',
                )
            )
        ));
    }

    /**
     * Get the current Stephino RPG version
     * 
     * @param WP_REST_Request $request WP Rest Request
     * @return string Current plugin version
     */
    public function getVersion($request) {
        return Stephino_Rpg_Utils_Media::getPwaVersion(true, false);
    }
    
    /**
     * Try to authorize a client user:<ul>
     * <li>Create new account if e-mail not used and return the new password</li>
     * <li>Try to log in with e-mail and password (sends cookie headers)</li>
     * </ul>
     * 
     * @param WP_REST_Request $request WP Rest Request
     * @return string|WP_Error|null New user password if a new account was added OR WordPress login error OR null if auth was successful for existing user
     */
    public function getAuth($request) {
        // Get the auth parameters
        $userEmail = $request->get_param(self::REQ_USER_EMAIL);
        $userPassword = $request->get_param(self::REQ_USER_PASSWORD);
        
        /* @var $resultUser WP_User|false */
        $resultUser = false;
        
        /* @var $resultError WP_Error|null */
        $resultError = null;
        
        /* @var $resultPassword string|null */
        $resultPassword = null;
        
        do {
            // Get user information by e-mail
            $resultUser = get_user_by('email', $userEmail);
            
            // A new e-mail address, unauthenticated user
            if (false === $resultUser) {
                $transientKey = Stephino_Rpg::OPTION_CACHE . '_auth_' . md5(
                    isset($_SERVER['REMOTE_ADDR']) 
                    ? $_SERVER['REMOTE_ADDR'] 
                    : '1'
                );
                
                // Get the number of account creation actions
                $transientData = get_site_transient($transientKey);
                
                // Initialize as [number, expiration timestamp]
                if (!is_array($transientData)) {
                    $transientData = array(0, time() + 3600);
                }
                
                // Increment # of account creation actions and calculate transient expiration
                $transientData[0]++;
                $transientExpiration = $transientData[1] - time();
                
                // Update remaining time
                if ($transientExpiration > 0) {
                    set_site_transient($transientKey, $transientData, $transientExpiration);
                } else {
                    delete_site_transient($transientKey);
                }
                
                // QoS: don't allow more than this number of new accounts from the same IP within 1 hour
                if ($transientData[0] > Stephino_Rpg_Config::get()->core()->getRestAuthHourly()) {
                    $resultError = new WP_Error(
                        'stephino_rpg_exc', 
                        __('Too many new accounts', 'stephino-rpg'), 
                        array('status' => 400)
                    );
                    break;
                }
                
                // Prepare the new password
                if (strlen($userPassword)) {
                    // Provided by user
                    $resultPassword = $userPassword;
                } else {
                    // Generate a new one
                    $userPassword = $resultPassword = wp_generate_password(32, true, true);
                }

                // Attempt to create the user
                $userIdOrError = wp_create_user(
                    trim(strtolower(preg_replace('%(?:\@.*$|\W+| {2,})%', ' ', $userEmail))),
                    $userPassword, 
                    $userEmail
                );

                // Store the object accordingly
                if ($userIdOrError instanceof WP_Error) {
                    $resultError = new WP_Error(
                        'stephino_rpg_exc', 
                        __('Could not create new account', 'stephino-rpg'), 
                        array('status' => 400)
                    );
                    break;
                }
                
                // Get the user object
                $resultUser = get_user_by('id', $userIdOrError);
                
                // Log in
                wp_set_current_user($userIdOrError);
                wp_set_auth_cookie($userIdOrError);
                
                // Email login credentials
                wp_new_user_notification($userIdOrError, null, 'both');
            } else {
                // Try to sign in
                if (wp_check_password($userPassword, $resultUser->data->user_pass, $resultUser->ID)) {
                    wp_set_current_user($resultUser->ID);
                    wp_set_auth_cookie($resultUser->ID);
                } else {
                    $resultUser = false;
                    $resultError = new WP_Error(
                        'stephino_rpg_exc', 
                        __('Please re-enter your password', 'stephino-rpg'), 
                        array('status' => 400)
                    );
                }
            }
        } while(false);
        
        // Managed sign in/registration
        if ($resultUser instanceof WP_User) {
            echo sprintf(
                null !== $resultPassword
                    ? __('Welcome to %s!', 'stephino-rpg')
                    : __('Welcome back to %s!', 'stephino-rpg'),
                '<b>' . Stephino_Rpg_Config::get()->core()->getName(true) . '</b>'
            );
        } else {
            echo __('Please sign in', 'stephino-rpg');
        }
        
        // Prepare the result
        return null !== $resultPassword ? $resultPassword : $resultError;
    }
}

/*EOF*/