<?php

/**
 * Stephino_Rpg
 * 
 * @title      RPG
 * @desc       Entry points handler
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg {
    
    // Plugin slug
    const PLUGIN_SLUG             = 'stephino-rpg';
    
    // Plugin name
    const PLUGIN_NAME             = 'Stephino RPG';
    
    // Plugin variable name
    const PLUGIN_VARNAME          = 'stephino_rpg';
    
    // Plugin version
    const PLUGIN_VERSION          = '0.3.8';
    
    // Pro Plugin minimum compatible version
    const PLUGIN_VERSION_PRO      = '0.2.1';
    
    // DataBase version
    const PLUGIN_VERSION_DB       = '0.2.3';
    
    // Firebase version
    const PLUGIN_VERSION_FIREBASE = '8.4.3';
    
    // Pro Plugin download link
    const PLUGIN_URL_PRO          = 'https://gum.co/stephino-rpg';
    
    // Discord link
    const PLUGIN_URL_DISCORD      = 'https://discord.gg/32gFsSm';
    
    // WordPress.org link
    const PLUGIN_URL_WORDPRESS    = 'https://wordpress.org/plugins/stephino-rpg';
    
    // Demo Mode
    const PLUGIN_DEMO             = false;
    
    // Run cron tasks on the public pages
    const PLUGIN_CRON_PUBLIC      = false;
    
    // Plugin cache key
    const OPTION_CACHE            = 'stephino_rpg_cache';
    
    // Folders
    const FOLDER_THEMES = 'themes';
    const FOLDER_UI_CSS = 'ui/css';
    const FOLDER_UI_IMG = 'ui/img';
    const FOLDER_UI_JS  = 'ui/js';
    const FOLDER_UI_TPL = 'ui/tpl';
    
    /**
     * Singleton instance of Stephino_Rpg
     *
     * @var Stephino_Rpg
     */
    protected static $_instance = null;
    
    /**
     * Cache the admin
     * 
     * @var boolean
     */
    protected $_isAdmin = null;
    
    /**
     * Cache the activation flag
     * 
     * @var boolean
     */
    protected $_isPro = null;
    
    /**
     * Cache the demo flag
     * 
     * @var boolean
     */
    protected $_isDemo = null;
    
    /**
     * Get a Singleton instance of Stephino_Rpg
     * 
     * @return Stephino_Rpg
     */
    public static function get() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * Class constructor
     * 
     * @return Stephino_Rpg
     */
    protected function __construct() {
        // Perform all WordPress integration actions
        Stephino_Rpg_WordPress::init();
    }
    
    /**
     * Get whether or not this plugin was activated/unlocked with the pro plugin, also validating minimum required
     * PRO plugin version
     * 
     * @return boolean
     */
    public function isPro() {
        if (null === $this->_isPro) {
            $this->_isPro = defined('STEPHINO_RPG_PRO_ROOT') 
                && class_exists('Stephino_Rpg_Pro')
                && version_compare(Stephino_Rpg_Pro::PLUGIN_VERSION, self::PLUGIN_VERSION_PRO) >= 0;
        }
        return $this->_isPro;
    }
    
    /**
     * Get whether or not this plugin runs in Demo mode
     * 
     * @return boolean
     */
    public function isDemo() {
        if (null === $this->_isDemo) {
            $this->_isDemo = (
                self::PLUGIN_DEMO 
                || (
                    isset($_SERVER) && is_array($_SERVER) && isset($_SERVER['HTTP_HOST'])
                    && false !== strpos($_SERVER['HTTP_HOST'], 'stephino.com')
                )
            );
        }
        return $this->_isDemo;
    }
}

/*EOF*/