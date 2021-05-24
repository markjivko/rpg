<?php
/**
 * Stephino_Rpg_Utils_Themes
 * 
 * @title     Utils:Themes
 * @desc      Themes utils
 * @copyright (c) 2021, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Utils_Themes {
    
    /**
     * List of themes
     * 
     * @var Stephino_Rpg_Theme[]
     */
    protected static $_installed = array();
    
    /**
     * Get the list of installed themes
     * 
     * @return Stephino_Rpg_Theme[]
     */
    public static function getInstalled() {
        if (!count(self::$_installed)) {
            // The default theme is pre-bundled
            $result = array(
                Stephino_Rpg_Theme::THEME_DEFAULT => Stephino_Rpg_Theme::get(Stephino_Rpg_Theme::THEME_DEFAULT)
            );

            // Get other themes
            $uploadDir = wp_upload_dir();
            $themePaths = glob(rtrim($uploadDir['basedir'], '/\\') . '/' . Stephino_Rpg::PLUGIN_SLUG . '/' . Stephino_Rpg::FOLDER_THEMES . '/*', GLOB_ONLYDIR);

            // Found our items
            if (is_array($themePaths)) {
                foreach ($themePaths as $themePath) {
                    $themeSlug = basename($themePath);
                    if (!isset($result[$themeSlug])) {
                        $result[$themeSlug] = Stephino_Rpg_Theme::get($themeSlug);
                    }
                }
            }
            self::$_installed = array_filter($result);
        }
        
        return self::$_installed;
    }
    
    /**
     * Set this theme as active
     * 
     * @param string $themeSlug
     */
    public static function setActive($themeSlug) {
        // Different value
        if ($themeSlug !== Stephino_Rpg_Cache_Game::get()->read(Stephino_Rpg_Cache_Game::KEY_THEME, Stephino_Rpg_Theme::THEME_DEFAULT)) {
            $newThemeSlug = Stephino_Rpg_Theme::THEME_DEFAULT;

            // Default theme provided, no checks necessary
            if (Stephino_Rpg_Theme::THEME_DEFAULT !== $themeSlug) {
                // Sanitize the name
                $themeSlug = trim(preg_replace('%[^\w\-]+%', '', $themeSlug));

                // Validate theme
                if (strlen($themeSlug) && null !== self::getTheme($themeSlug)) {
                    $newThemeSlug = $themeSlug;
                }
            }

            Stephino_Rpg_Cache_Game::get()->write(Stephino_Rpg_Cache_Game::KEY_THEME, $newThemeSlug);
        }
    }
    
    /**
     * Get the currently active theme, reverting to the default if the current theme was corrupted
     * 
     * @return Stephino_Rpg_Theme
     */
    public static function getActive() {
        $themeSlug = Stephino_Rpg_Cache_Game::get()->read(
            Stephino_Rpg_Cache_Game::KEY_THEME, 
            Stephino_Rpg_Theme::THEME_DEFAULT
        );
        
        // Theme corrupted, return to the default
        if (null === self::getTheme($themeSlug)) {
            self::setActive($themeSlug = Stephino_Rpg_Theme::THEME_DEFAULT);
        }
        
        return self::getTheme($themeSlug);
    }
    
    /**
     * Get the theme slug by theme name
     * 
     * @param string $themeName
     * @return string
     */
    public static function getSlug($themeName) {
        return strtolower(
            preg_replace(
                array('%\s+%', '%(?:[^[a-z\-0-9]|[\s\-]+$)%i'), 
                array('-', ''),
                $themeName
            )
        );
    }

    /**
     * Get a theme object
     * 
     * @param string $themeSlug Theme slug
     * @return Stephino_Rpg_Theme|null
     */
    public static function getTheme($themeSlug) {
        // Populate the installed cache
        !count(self::$_installed) && self::getInstalled();
        
        // Get the theme by slug
        return isset(self::$_installed[$themeSlug])
            ? self::$_installed[$themeSlug]
            : null;
    }
    
    /**
     * Get the file path based on the theme slug
     * 
     * @param string|boolean $themeSlug    Theme slug; use <b>false</b> for the temporary upload folder
     * @param string         $relativePath (optional) Relative path; default <b>null</b>
     * @return string
     */
    public static function getPath($themeSlug, $relativePath = null) {
        // Clean-up the path
        $relativePath = Stephino_Rpg_Utils_Sanitizer::getMediaPath($relativePath);
        
        // Prepare the path ending
        $pathTail = strlen($relativePath) ? ('/' . $relativePath) : '';
        
        // Prepare the result
        $result = STEPHINO_RPG_ROOT . '/' . Stephino_Rpg::FOLDER_THEMES . '/' . Stephino_Rpg_Theme::THEME_DEFAULT . $pathTail;

        // Clean-up (theme config might be corrupted; prevent directory traversal)
        $themeSlug = false === $themeSlug
            ? '_temp'
            : preg_replace('%[^a-z\-\d]+%', '', strtolower($themeSlug));

        // Outside theme
        if (strlen($themeSlug) && Stephino_Rpg_Theme::THEME_DEFAULT !== $themeSlug) {
            $uploadDir = wp_upload_dir();
            $result = rtrim($uploadDir['basedir'], '/\\') . '/' . Stephino_Rpg::PLUGIN_SLUG . '/' . Stephino_Rpg::FOLDER_THEMES . '/' . $themeSlug . $pathTail;
        }
        
        return $result;
    }
    
    /**
     * Remove all themes
     */
    public static function purge() {
        try {
            $themesDir = Stephino_Rpg_Utils_Folder::get()->baseDir() . '/' . Stephino_Rpg::PLUGIN_SLUG . '/' . Stephino_Rpg::FOLDER_THEMES;
            if (Stephino_Rpg_Utils_Folder::get()->fileSystem()->is_dir($themesDir)) {
                Stephino_Rpg_Utils_Folder::get()->fileSystem()->delete($themesDir, true);
            }
        } catch (Exception $exc) {
            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::warning($exc->getMessage());
        }
    }
}

/* EOF */