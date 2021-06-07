<?php
/**
 * Stephino_Rpg_Utils_Sanitizer
 * 
 * @title     Utils:Sanitizer
 * @desc      Sanitizer utils
 * @copyright (c) 2021, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Utils_Sanitizer {

    /**
     * _GET Arguments
     */
    const CALL_LOGIN        = 'stephino-rpg-login';
    const CALL_PAGE         = 'page';
    const CALL_THEME        = 'theme';
    const CALL_REDIRECT_TO  = 'redirect_to';
    
    /**
     * Get whether we are currently inside an AJAX request
     * 
     * @param boolean $excludePublic (optional) If running public controllers, consider this NOT an AJAX request; default <b>true</b>
     * @global string $pagenow
     * @return boolean
     */
    public static function isAjax($excludePublic = true) {
        global $pagenow;
        
        // This is an AJAX request
        $result = 'admin-ajax.php' === $pagenow 
            && isset($_REQUEST)
            && isset($_REQUEST[Stephino_Rpg_Renderer_Ajax::CALL_ACTION])
            && Stephino_Rpg::PLUGIN_VARNAME === $_REQUEST[Stephino_Rpg_Renderer_Ajax::CALL_ACTION];
        
        // Need to exclude public controllers
        if ($result && $excludePublic) {
            // Method specified
            if (isset($_REQUEST[Stephino_Rpg_Renderer_Ajax::CALL_METHOD])) {
                // Public controller
                if (in_array(strtolower($_REQUEST[Stephino_Rpg_Renderer_Ajax::CALL_METHOD]), Stephino_Rpg_Renderer_Ajax::PUBLIC_CONTROLLERS)) {
                    $result = false;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Get the sanitized theme slug _GET parameter (if set)
     * 
     * @return string|null
     */
    public static function getTheme() {
        $themeSlug = isset($_GET) && isset($_GET[self::CALL_THEME])
            ? trim($_GET[self::CALL_THEME])
            : null;
        
        // Cannot edit the default theme or a theme that does not exist
        if (null !== $themeSlug) {
            if (Stephino_Rpg_Theme::THEME_DEFAULT === $themeSlug || null === Stephino_Rpg_Utils_Themes::getTheme($themeSlug)) {
                $themeSlug = null;
            }
        }
        
        return $themeSlug;
    }
    
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
     * Get the sanitized media path:<ul>
     * <li>Contains word characters and ".", "/", "-"</li>
     * <li>Starts and ends with a word character (0-9, a-z, _)</li>
     * <li>No more than 1 consecutive dot character</li>
     * <li>No access to hidden files (starting with .)</li>
     * </ul>
     * 
     * @param $mediaPath (optional) Treat this path instead of the _GET argument; default <b>null</b>
     * @return string
     */
    public static function getMediaPath($mediaPath = null) {
        // Prepare the result
        $result = '';
        
        if (null === $mediaPath) {
            $mediaPath = isset($_GET[Stephino_Rpg_Renderer_Ajax::CALL_MEDIA_PATH])
                ? $_GET[Stephino_Rpg_Renderer_Ajax::CALL_MEDIA_PATH]
                : null;
        }
        
        // View data set
        if (null !== $mediaPath) {
            $result = preg_replace(
                array('%(?:[^\w\.\/\-]+|^\W|\W$)%i', '%(?:\/\.|\/{2,})%'), 
                array('', '/'), 
                trim($mediaPath)
            );
        }
        
        return $result;
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