<?php
/**
 * Stephino_Rpg_Renderer
 * 
 * @title      Rendering
 * @desc       Perform the game rendering
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Renderer {
    
    // Interfaces
    const INTERFACE_HTML = 'interfaceHtml';
    const INTERFACE_AJAX = 'interfaceAjax';
    
    /**
     * Display the game pages (which redirect to the cached AJAX html pages) and options
     */
    public static function interfaceHtml() {
        // Get the current page
        if (!strlen($page = preg_replace('%^' . preg_quote(Stephino_Rpg::PLUGIN_SLUG) . '\-?%i', '', Stephino_Rpg_Utils_Sanitizer::getPage()))) {
            $page = Stephino_Rpg_Renderer_Html::METHOD_GAME;
        }
        
        // Prepare the method name
        $methodName = Stephino_Rpg_Renderer_Html::METHOD_PREFIX . ucfirst($page);
        
        // Prepare the callable
        $callable = array(Stephino_Rpg_Renderer_Html::class, $methodName);
        
        // Perform the tasks; extra validation handled by WordPress
        if (is_callable($callable)) {
            call_user_func($callable);
        }
    }
    
    /**
     * AJAX interface - displays JSON XHR requests and cached CSS and HTML content
     */
    public static function interfaceAjax() {
        // Prepare the header
        while(@ob_end_clean());
        
        // Prepare the result
        $result = array(
            Stephino_Rpg_Renderer_Ajax::CALL_RESPONSE_RESULT  => null,
            Stephino_Rpg_Renderer_Ajax::CALL_RESPONSE_CONTENT => null,
            Stephino_Rpg_Renderer_Ajax::CALL_RESPONSE_STATUS  => true,
        );
        
        // Prepare the cache mechanism
        $ajaxCache = null;
        
        // Prepare the call method
        $callMethod = Stephino_Rpg_Renderer_Ajax::CONTROLLER_HTML;
        
        // Start the buffer
        ob_start();
        
        // Custom error handler
        set_error_handler(array(Stephino_Rpg_Log::class, 'error'));
        
        
        try {
            // Sanitize the method name
            if (isset($_REQUEST) && isset($_REQUEST[Stephino_Rpg_Renderer_Ajax::CALL_METHOD])) {
                $callMethod = preg_replace('%\W+%', '', trim($_REQUEST[Stephino_Rpg_Renderer_Ajax::CALL_METHOD]));
            }

            // Invalid method
            if (!strlen($callMethod)) {
                throw new Exception(__('Invalid method specified', 'stephino-rpg'));
            }
            
            // Only authenticated users are allowed; CSS and JS outputs are available after logout to prevent a layout crash
            if (!is_user_logged_in() && !in_array(strtolower($callMethod), Stephino_Rpg_Renderer_Ajax::PUBLIC_CONTROLLERS)) {
                throw new Exception(__('You have logged out. Please refresh and try again', 'stephino-rpg'));
            }
            
            // Admin method?
            if (preg_match('%^admin%i', $callMethod) && !Stephino_Rpg::get()->isDemo() && !Stephino_Rpg_Cache_User::get()->isGameAdmin()) {
                throw new Exception(__('Insufficient privileges', 'stephino-rpg'));
            }
            
            // Run the User-facing Cron Tasks, but only on the XHR and HTML threads
            if (!in_array(strtolower($callMethod), Stephino_Rpg_Renderer_Ajax::PUBLIC_CONTROLLERS)) {
                // The admin should be able to edit the config no matter what
                if (!preg_match('%^admin%i', $callMethod)) {
                    // Using time-lapse workers to fetch DB data from local memory instead of performing new SELECT queries
                    Stephino_Rpg_Task_Cron::player(true, !!preg_match('%^dialog\w+%i', $callMethod));
                }
            }

            // Prepare the class name
            $className = Stephino_Rpg_Renderer_Ajax::class;
            if (preg_match('%^(' . implode('|', Stephino_Rpg_Renderer_Ajax::AVAILABLE_CONTROLLERS) . ')%i', $callMethod, $callMethodGroup)) {
                $className = Stephino_Rpg_Renderer_Ajax::class . '_' . ucfirst(strtolower($callMethodGroup[1]));
            }

            // Prepare the method name
            $methodName = Stephino_Rpg_Renderer_Ajax::METHOD_PREFIX . ucfirst(strtolower($callMethod));
            
            // Check the method is implemented
            if (!is_callable(array($className, $methodName))) {
                do {
                    // Sub-class
                    if (preg_match('%^[a-z]+?([A-Z][a-z0-9]+)([A-Z][a-zA-Z0-9]+)$%', $callMethod, $subClassGroup)) {
                        // Prepare the new method name
                        $methodName = Stephino_Rpg_Renderer_Ajax::METHOD_PREFIX . $subClassGroup[2];

                        // Prepare the new class name
                        $className .= '_' . ucfirst($subClassGroup[1]);

                        // The sub-class method was implemented
                        if (is_callable(array($className, $methodName))) {
                            break;
                        }
                    }
                    
                    throw new Exception(__('Method not implemented', 'stephino-rpg'));
                } while(false);
            }
            
            // Store the cache mechanism
            $ajaxCache = Stephino_Rpg_Cache_Ajax::get(
                $className, 
                $methodName, 
                array(
                    Stephino_Rpg_Utils_Sanitizer::getView(),
                    Stephino_Rpg_Utils_Sanitizer::getConfigId(),
                )
            );

            // Prepare the method arguments
            $methodData = isset($_REQUEST) && isset($_REQUEST[Stephino_Rpg_Renderer_Ajax::CALL_DATA]) 
                ? @json_decode(base64_decode($_REQUEST[Stephino_Rpg_Renderer_Ajax::CALL_DATA]), true) 
                : array();
            
            // Prepare the result; avoid unnecessary labor
            $result[Stephino_Rpg_Renderer_Ajax::CALL_RESPONSE_RESULT] = $ajaxCache->cacheHit() 
                ? null 
                : call_user_func(array($className, $methodName), is_array($methodData) ? $methodData : array());
            
        } catch (Exception $exc) {
            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::warning($exc->getMessage());
            
            // Store the message
            $result[Stephino_Rpg_Renderer_Ajax::CALL_RESPONSE_RESULT] = $exc->getMessage();
            
            // Invalid result
            $result[Stephino_Rpg_Renderer_Ajax::CALL_RESPONSE_STATUS] = false;
        }
        
        // Store the content
        $result[Stephino_Rpg_Renderer_Ajax::CALL_RESPONSE_CONTENT] = preg_replace('% {2,}%', ' ', ob_get_clean());
        
        // Media output
        if (Stephino_Rpg_Renderer_Ajax::CONTROLLER_MEDIA === $callMethod) {
            // Valid server response, try to cache
            if ($result[Stephino_Rpg_Renderer_Ajax::CALL_RESPONSE_STATUS]) {
                // Enable web caching
                null !== $ajaxCache && $ajaxCache->sendHeaders();
                
                // Output the file
                header("Content-Type: " . mime_content_type($result[Stephino_Rpg_Renderer_Ajax::CALL_RESPONSE_RESULT]));
                header("Content-Length: " . filesize($result[Stephino_Rpg_Renderer_Ajax::CALL_RESPONSE_RESULT]));
                readfile($result[Stephino_Rpg_Renderer_Ajax::CALL_RESPONSE_RESULT]);
            } else {
                // File not found
                http_response_code(404);
            }

            // Stop here
            exit();
        }
        
        // CSS output - cacheable
        if (Stephino_Rpg_Renderer_Ajax::CONTROLLER_CSS === $callMethod) {
            // Set the content type
            header('Content-Type: text/css');
                
            // Valid server response, try to cache
            if ($result[Stephino_Rpg_Renderer_Ajax::CALL_RESPONSE_STATUS]) {
                // Enable web caching
                null !== $ajaxCache && $ajaxCache->sendHeaders();
                
                // Time to download the game and animations CSS (not yet cached by browser)
                header("Content-Length: " . strlen($result[Stephino_Rpg_Renderer_Ajax::CALL_RESPONSE_RESULT]));
                echo $result[Stephino_Rpg_Renderer_Ajax::CALL_RESPONSE_RESULT];
            } else {
                // Show the error
                echo '/* ' . json_encode($result) . ' */';
            }
            
            // Stop here
            exit();
        }
        
        // JS output - cacheable
        if (Stephino_Rpg_Renderer_Ajax::CONTROLLER_JS === $callMethod) {
            // Set the content type
            header('Content-Type: text/javascript');
                
            // Valid server response, try to cache
            if ($result[Stephino_Rpg_Renderer_Ajax::CALL_RESPONSE_STATUS]) {
                // Enable web caching
                null !== $ajaxCache && $ajaxCache->sendHeaders();
                
                // Time to download the JS (not yet cached by browser)
                header("Content-Length: " . strlen($result[Stephino_Rpg_Renderer_Ajax::CALL_RESPONSE_RESULT]));
                echo $result[Stephino_Rpg_Renderer_Ajax::CALL_RESPONSE_RESULT];
            } else {
                // Output the result
                echo json_encode($result);
            }
            
            // Stop here
            exit();
        }
        
        // HTML output - cacheable
        if (Stephino_Rpg_Renderer_Ajax::CONTROLLER_HTML === $callMethod) {
            if ($result[Stephino_Rpg_Renderer_Ajax::CALL_RESPONSE_STATUS]) {
                // Set the content type
                header('Content-Type: text/html');

                // Time to download the HTML (not yet cached by browser)
                echo $result[Stephino_Rpg_Renderer_Ajax::CALL_RESPONSE_RESULT];

                // Stop here
                exit();
            } else {
                if (
                    !is_user_logged_in() 
                    || (
                        null !== Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)
                        && is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData()) 
                        && count(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())
                    )
                ) {
                    // Prepare the redirect location
                    $locationUrl = is_user_logged_in()
                        ? Stephino_Rpg_Utils_Media::getAdminUrl(true, false)
                        : add_query_arg(Stephino_Rpg_Utils_Sanitizer::CALL_LOGIN, 1, wp_login_url());
                    
                    // Redirect to the log in page if session expired or to the main city page
                    header("Location: $locationUrl", true);
                    
                    // Stop here
                    exit();
                }
            }
        }
        
        // Bad request
        if (!$result[Stephino_Rpg_Renderer_Ajax::CALL_RESPONSE_STATUS]) {
            http_response_code(400);
        }
        
        // JSON data
        header('Content-Type: text/json');
        
        // Output the result
        echo json_encode($result);
        
        // Restore the error handler
        restore_error_handler();
        
        // Stop here
        exit();
    }
}

/*EOF*/