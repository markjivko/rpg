<?php

/**
 * Stephino_Rpg_Renderer_Ajax_Action_Settings
 * 
 * @title      Action::Settings
 * @desc       Settings actions
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Renderer_Ajax_Action_Settings extends Stephino_Rpg_Renderer_Ajax_Action {

    // Response keys
    const DATA_HEARTBEAT_QUEUES = 'heartBeatQueues';
    
    // Request keys
    const REQUEST_KEY      = 'key';
    const REQUEST_VALUE    = 'value';
    const REQUEST_COMMAND  = 'command';
    const REQUEST_PASSWORD = 'password';
    const REQUEST_LANGUAGE = 'language';
    
    // Maximum lengths
    const MAX_LENGTH_USER_NAME = 60;
    const MAX_LENGTH_USER_DESC = 250;
    
    /**
     * Update the current user's WordPress account password
     * 
     * @param array $data Data containing <ul>
     * <li><b>self::REQUEST_PASSWORD</b> (string) New account password</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxPassword($data) {
        // Get the password
        $newPassword = isset($data[self::REQUEST_PASSWORD]) ? trim($data[self::REQUEST_PASSWORD]) : '';
        
        // Invalid password
        if (strlen($newPassword) < 6) {
            throw new Exception(__('Passwords must be at least 6 characters long', 'stephino-rpg'));
        }
        
        // Get the current user
        $currentUser = wp_get_current_user();
        
        // Invalid user
        if (0 == $currentUser->ID) {
            throw new Exception(__('You are logged out', 'stephino-rpg'));
        }

        // Change password
        wp_set_password($newPassword, $currentUser->ID);

        // Log-in again
        wp_set_auth_cookie($currentUser->ID);
        wp_set_current_user($currentUser->ID);
        do_action('wp_login', $currentUser->user_login, $currentUser);
        
        // Inform the user
        return __('Password updated successfully', 'stephino-rpg');
    }
    
    /**
     * Update player language
     * 
     * @param array $data Data containing <ul>
     * <li><b>self::REQUEST_LANGUAGE</b> (string) User language</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxLanguage($data) {
        // Sanitize the language
        $locale = isset($data[self::REQUEST_LANGUAGE]) ? trim($data[self::REQUEST_LANGUAGE]) : null;
        if (null === $locale || !strlen($locale)) {
            throw new Exception(__('Language missing', 'stephino-rpg'));
        }
        
        // Validate it
        $allowedLanguages = array_keys(Stephino_Rpg_Utils_Lingo::getLanguages());
        if (!in_array($locale, $allowedLanguages)) {
            throw new Exception(__('Language not defined', 'stephino-rpg'));
        }
        
        // Valid user
        if (null !== $currentUser = wp_get_current_user()) {
            // Commit to user cache
            Stephino_Rpg_Cache_User::get()
                ->write(Stephino_Rpg_Cache_User::KEY_LANG, $locale)
                ->commit();
            
            // Change WordPress locale for this user
            $currentUser->locale = $locale;
            wp_update_user($currentUser);
        }
    }
    
    /**
     * Update a setting
     * 
     * @param array $data Data containing <ul>
     * <li><b>self::REQUEST_KEY</b> (string) Setting key</li>
     * <li><b>self::REQUEST_VALUE</b> (string|int) Setting value</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxUpdate($data) {
        // Sanitize the key
        $dataKey = isset($data[self::REQUEST_KEY]) ? trim($data[self::REQUEST_KEY]) : null;
        if (null === $dataKey || !strlen($dataKey)) {
            throw new Exception(__('Setting key missing', 'stephino-rpg'));
        }
        
        // Sanitize the value
        $dataValue = isset($data[self::REQUEST_VALUE]) ? trim($data[self::REQUEST_VALUE]) : null;
        if (null === $dataValue) {
            throw new Exception(__('Setting value missing', 'stephino-rpg'));
        }

        // Prepare the result
        $result = null;
        
        // Get the current user
        $currentUser = wp_get_current_user();
        
        // Invalid user
        if (0 == $currentUser->ID) {
            throw new Exception(__('Invalid user', 'stephino-rpg'));
        }
        
        // Update the value
        switch ($dataKey) {
            case Stephino_Rpg_WordPress::USER_META_NICKNAME:
            case Stephino_Rpg_WordPress::USER_META_DESCRIPTION:
                $dataValue = Stephino_Rpg_Utils_Lingo::cleanup($dataValue);
                
                // The user nick-name is mandatory
                if (Stephino_Rpg_WordPress::USER_META_NICKNAME === $dataKey && !strlen($dataValue)) {
                    throw new Exception(__('Nickname is mandatory', 'stephino-rpg'));
                }
                
                // Validate the length
                if (strlen($dataValue) > (Stephino_Rpg_WordPress::USER_META_NICKNAME === $dataKey 
                    ? self::MAX_LENGTH_USER_NAME : self::MAX_LENGTH_USER_DESC)) {
                    throw new Exception(
                        Stephino_Rpg_WordPress::USER_META_NICKNAME === $dataKey 
                            ? __('Nickname is too long', 'stephino-rpg')
                            : __('Biography is too long', 'stephino-rpg')
                    );
                }
                
                // Prepare the result
                $result = update_user_meta($currentUser->ID, $dataKey, $dataValue);
                
                // Get the updated value
                $dataValue = html_entity_decode(get_user_meta($currentUser->ID, $dataKey, true));
                break;
            
            case Stephino_Rpg_Cache_User::KEY_VOL_MUSIC:
            case Stephino_Rpg_Cache_User::KEY_VOL_BKG:
            case Stephino_Rpg_Cache_User::KEY_VOL_CELLS:
            case Stephino_Rpg_Cache_User::KEY_VOL_EVENTS:
                // Store the new value
                $dataValue = intval($dataValue);
                
                // Range check
                if ($dataValue < 0 || $dataValue > 100) {
                    throw new Exception(__('Invalid volume specified. Expected [0, 100]', 'stephino-rpg'));
                }
                
                // Update the user settings
                $result = Stephino_Rpg_Cache_User::get()->write($dataKey, $dataValue)->commit();
                break;
            
        }
        
        // Unknown key
        if (null === $result) {
            throw new Exception(__('Unknown setting', 'stephino-rpg'));
        }
        
        return Stephino_Rpg_Renderer_Ajax::wrap($dataValue);
    }
    
    /**
     * Game admin console
     * 
     * @param array $data Data containing <ul>
     * <li><b>self::REQUEST_COMMAND</b> (string) Command string</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxConsole($data) {
        // Not allowed
        if (!Stephino_Rpg::get()->isDemo() && !Stephino_Rpg::get()->isAdmin()) {
            throw new Exception(__('Insufficient privileges', 'stephino-rpg'));
        }
        
        // Allowed methods for non-admins
        $allowedMethodsRegex = '%^(?:help|whoami|list\-island\b)\b%i';
        
        // Console not enabled
        if (!Stephino_Rpg_Config::get()->core()->getConsoleEnabled()) {
            throw new Exception(__('Console not enabled from Game Mechanics', 'stephino-rpg'));
        }
        
        // Sanitize the key
        $dataCommand = isset($data[self::REQUEST_COMMAND]) ? trim($data[self::REQUEST_COMMAND]) : '';
        
        // Invalid command
        if (!strlen($dataCommand)) {
            throw new Exception(__('No command given', 'stephino-rpg'));
        }
        
        // Prepare the string placeholders
        $stringPlaceholders = array();
        
        // Replace string patterns ('a' or "a") with temporary numbered placeholders
        $dataCommand = preg_replace_callback(
            // Support for escaping; example: 'foo\'s "bar"' and "bar 'has' \"baz\""
            '%([\'"])((?:\\\\1|.)*?)(?<!\\\)\1%', 
            function($item) use(&$stringPlaceholders) {
                // Get the new key
                $newKey = count($stringPlaceholders);
                
                // Unescape single and double quotes
                $stringPlaceholders[] = preg_replace('%\\\\' . preg_quote($item[1]) . '%', $item[1], $item[2]);
                
                // Store the placeholder
                return '__' . $newKey . '__';
            }, 
            preg_replace('%\s+%', ' ', $dataCommand)
        );

        // Prepare the arguments
        $methodArguments = array_filter(
            array_map(
                function($item) use ($stringPlaceholders) {
                    $result = $item;
                    if (preg_match('%__(\d+)__%', $item, $itemMatch)) {
                        if (isset($stringPlaceholders[$itemMatch[1]])) {
                            $result = $stringPlaceholders[$itemMatch[1]];
                        }
                    }
                    return $result;
                }, 
                preg_split('%\s+%s', $dataCommand)
            ), 
            'strlen'
        );

        // Count the arguments
        if (!count($methodArguments)) {
            throw new Exception(__('Invalid number of arguments', 'stephino-rpg'));
        }
        
        // Help menu
        if (isset($methodArguments[1]) && in_array($methodArguments[1], array('--help', '/?'))) {
            // Set the help argument
            $methodArguments[1] = $methodArguments[0];
            
            // Replace the method with help
            $methodArguments[0] = 'help';
        }
        
        // Help mode for players
        if (Stephino_Rpg::get()->isDemo() && !Stephino_Rpg::get()->isAdmin() && !preg_match($allowedMethodsRegex, $methodArguments[0])) {
            echo '<span class="badge badge-info">(DEMO) ' . esc_html__('Commands are read-only for non-admins', 'stephino-rpg') . '</span><br/>';
            array_unshift($methodArguments, 'help');
        }
        
        // Prepar the command name
        $commandName = preg_replace(
            array('%[^\w\-]+%', '%\-+%'), 
            array('', '-'), 
            strtolower(array_shift($methodArguments))
        );
        
        // Prepare the method
        $methodName = 'cli' . implode(
            '', 
            array_filter(
                array_map(
                    'ucfirst', 
                    preg_split(
                        '%\-%', 
                        $commandName
                    )
                ),
                'strlen'
            )
        );

        // Check the method exists
        if (!is_callable(array(Stephino_Rpg_Renderer_Console::class, $methodName))) {
            // Prepare the reflection
            $consoleReflection = new ReflectionClass(Stephino_Rpg_Renderer_Console::class);
            
            // Get the available methods
            $consoleCommands = array_filter(
                array_map(
                    function($reflectionMethod) {
                        $result = null;
                        if (preg_match('%^cli\w+%i', $reflectionMethod->name)) {
                            $result = strtolower(
                                preg_replace(
                                    array('%([A-Z])%', '%^cli\-%'), 
                                    array('-$1', ''), 
                                    $reflectionMethod->name
                                )
                            );
                        }
                        return $result;
                    }, 
                    $consoleReflection->getMethods(
                        ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_STATIC
                    )
                )
            );
                    
            // Prepare the elastic search
            $commandsNeedle = '%' . preg_replace(
                array('%(^\w|\w$)%i', '%\-+%'), 
                array('$1?', '.*?'), 
                $commandName
            ) . '%';

            // Get the similar methods
            $suggestionList = array_map(
                function($similarCommand) {
                    return "`$similarCommand`";
                },
                array_filter(
                    $consoleCommands, 
                    function($similarCommand) use($commandsNeedle) {
                        return preg_match($commandsNeedle, $similarCommand);
                    }
                )
            );
            
            // Show the suggestions
            throw new Exception(
                sprintf(
                    __('Command not found, try %s', 'stephino-rpg'),
                    count($suggestionList) ? implode(' or ', $suggestionList) : '`help`'
                )
            );
        }

        // Prepare the result
        $result = call_user_func_array(
            // Method may output some data
            array(Stephino_Rpg_Renderer_Console::class, $methodName), 
            $methodArguments
        );
        
        return Stephino_Rpg_Renderer_Ajax::wrap($result);
    }
    
    /**
     * Get status updates
     * 
     * @param array $data Data containing <ul>
     * <li><b>self::REQUEST_CITY_ID</b> (int) City ID</li>
     * </ul>
     */
    public static function ajaxHeartbeat($data) {
        // Prepare the city ID
        $cityId = isset($data[self::REQUEST_CITY_ID]) ? intval($data[self::REQUEST_CITY_ID]) : 0;
        
        // Prepare the queue data
        $queuesData = null;
        if ($cityId > 0) {
            $queuesData = self::getBuildingQueue($cityId, true);
        }

        // Wrap the result
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::DATA_HEARTBEAT_QUEUES => $queuesData
            ), 
            $cityId <= 0 ? null : $cityId
        );
    }
}

/*EOF*/