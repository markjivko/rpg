<?php

/**
 * Stephino_Rpg
 * 
 * @title      RPG
 * @desc       Entry points handler
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg {
    
    // Plugin slug
    const PLUGIN_SLUG             = 'stephino-rpg';
    
    // Plugin variable name
    const PLUGIN_VARNAME          = 'stephino_rpg';
    
    // Plugin version
    const PLUGIN_VERSION          = '0.3.3';
    
    // Pro Plugin minimum compatible version
    const PLUGIN_VERSION_PRO      = '0.2.0';
    
    // DataBase version
    const PLUGIN_VERSION_DB       = '0.2.3';
    
    // Firebase version
    const PLUGIN_VERSION_FIREBASE = '7.22.1';
    
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
    
    // Plugin configuration key
    const OPTION_CONFIG           = 'stephino_rpg_config';
    
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
     * Stephino: Multi-player Online Role-Playing Game engine for WordPress
     */
    protected function __construct() {
        // Register the hooks
        Stephino_Rpg_WordPress::registerHooks();
        
        // Perform meta changes
        Stephino_Rpg_WordPress::metaChages();
        
        // Register the pages
        Stephino_Rpg_WordPress::registerPages();
        
        // Register the AJAX handler
        Stephino_Rpg_WordPress::registerAjax();
        
        // Register the Robots Cron actions
        Stephino_Rpg_WordPress::registerRobotsCron();
        
        // Register the widgets
        Stephino_Rpg_WordPress::registerWidgets();
    }
    
    /**
     * Get whether the current user is a site admin
     * 
     * @return boolean
     */
    public function isAdmin() {
        if (null === $this->_isAdmin) {
            $this->_isAdmin = is_super_admin();
        }
        return $this->_isAdmin;
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