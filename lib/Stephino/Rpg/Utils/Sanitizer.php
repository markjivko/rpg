<?php
/**
 * Stephino_Rpg_Utils_Sanitizer
 * 
 * @title     Utils:Sanitizer
 * @desc      Sanitizer utils
 * @copyright (c) 2020, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Utils_Sanitizer {

    /**
     * _GET Arguments
     */
    const CALL_LOGIN        = 'stephino-rpg-login';
    const CALL_PAGE         = 'page';
    const CALL_REDIRECT_TO  = 'redirect_to';
    
    /**
     * Get whether the login page was requested by the game
     * 
     * @return boolean
     */
    public static function getLogin() {
        return (isset($_GET) && isset($_GET[self::CALL_LOGIN]) && 1 === intval($_GET[self::CALL_LOGIN]));
    }
    
    /**
     * Get the sanitized Page _GET parameter (if set)
     * 
     * @return string|null Sanitized Page or null
     */
    public static function getPage() {
        return isset($_GET) && is_array($_GET) && isset($_GET[self::CALL_PAGE]) 
            ? preg_replace('%[^\w\-]+%', '', $_GET[self::CALL_PAGE])
            : null;
    }
    
    /**
     * Get the sanitized View Name _GET parameter (if set)
     * 
     * @param $emptyDefault (optional) Set the default to null, Stephino_Rpg_Renderer_Ajax::VIEW_CITY otherwise; default <b>true</b>
     * @return string View Name
     */
    public static function getView($emptyDefault = true) {
        // Prepare the default
        $default = $emptyDefault ? null : Stephino_Rpg_Renderer_Ajax::VIEW_CITY;
        
        // Get the view name
        $viewName = isset($_GET[Stephino_Rpg_Renderer_Ajax::CALL_VIEW]) 
            ? trim($_GET[Stephino_Rpg_Renderer_Ajax::CALL_VIEW]) 
            : $default;
        
        // Validate the view
        if (!in_array($viewName, Stephino_Rpg_Renderer_Ajax::AVAILABLE_VIEWS)) {
            $viewName = $default;
        }
        
        return $viewName;
    }
    
    /**
     * Get the sanitized the View Data _GET parameter (if set)
     * Allowed characters: [0-9x\-]
     * 
     * @return string|null Valid view data string or Null if not set correctly
     */
    public static function getViewData() {
        // Prepare the result
        $result = '';
        
        // View data set
        if (isset($_GET[Stephino_Rpg_Renderer_Ajax::CALL_VIEW_DATA])) {
            // Allowed characters: decimals, "-", "x"
            $result = preg_replace(
                '%[^\d\-x]+%', 
                '', 
                trim($_GET[Stephino_Rpg_Renderer_Ajax::CALL_VIEW_DATA])
            );
        }
        
        return strlen($result) ? $result : null;
    }
    
    /**
     * Get the sanitized Configuration ID _GET parameter (if set)
     * 
     * @return int|null Configuration ID
     */
    public static function getConfigId() {
        return isset($_GET[Stephino_Rpg_Renderer_Ajax::CALL_CONFIG_ID]) 
            ? intval($_GET[Stephino_Rpg_Renderer_Ajax::CALL_CONFIG_ID]) 
            : null;
    }
    
    /**
     * Get the sanitized Redirection URL _GET parameter (if set)
     * 
     * @return string|null Redirection parameter or null
     */
    public static function getRedirectTo() {
        $result = isset($_GET) && isset($_GET[self::CALL_REDIRECT_TO]) 
            ? trim($_GET[self::CALL_REDIRECT_TO]) 
            : '';
        
        return strlen($result) ? $result : null;
    }
    
}

/*EOF*/