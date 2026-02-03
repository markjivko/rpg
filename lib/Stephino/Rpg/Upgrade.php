<?php
/**
 * Stephino_Rpg_Upgrade
 * 
 * @title      Upgrade
 * @desc       Handle plugin upgrades and migrations
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Upgrade {
    
    /**
     * Option key to track upgrade status
     */
    const UPGRADE_OPTION_KEY = 'wp_rpg_upgrade_version';
    
    /**
     * Current upgrade version
     */
    const UPGRADE_VERSION = '1.0.0';
    
    /**
     * Singleton instance of Stephino_Rpg_Upgrade
     * 
     * @var Stephino_Rpg_Upgrade
     */
    protected static $_instance = null;
    
    /**
     * Get a Singleton instance of Stephino_Rpg_Upgrade
     * 
     * @return Stephino_Rpg_Upgrade
     */
    public static function get() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * Initialize upgrade routines
     */
    public static function init() {
        // Run upgrade check on admin_init
        add_action('admin_init', array(self::get(), 'checkUpgrade'));
    }
    
    /**
     * Class constructor
     */
    protected function __construct() {
        // Private constructor for singleton
    }
    
    /**
     * Check if an upgrade is needed and run migrations
     */
    public function checkUpgrade() {
        $currentVersion = get_option(self::UPGRADE_OPTION_KEY, '0.0.0');
        
        // Check if we need to run the stephino_rpg to wp_rpg migration
        if (version_compare($currentVersion, '1.0.0', '<')) {
            $this->_migrateStephinoRpgToWpRpg();
            update_option(self::UPGRADE_OPTION_KEY, '1.0.0');
        }
        
        // Update to current version if all migrations are complete
        if (version_compare($currentVersion, self::UPGRADE_VERSION, '<')) {
            update_option(self::UPGRADE_OPTION_KEY, self::UPGRADE_VERSION);
        }
    }
    
    /**
     * Migrate option keys from stephino_rpg_* to wp_rpg_*
     * This is idempotent and safe to run multiple times
     * 
     * @return void
     */
    protected function _migrateStephinoRpgToWpRpg() {
        global $wpdb;
        
        // Get all options starting with stephino_rpg_
        $oldOptions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
                'stephino_rpg_%'
            ),
            ARRAY_A
        );
        
        if (!empty($oldOptions)) {
            foreach ($oldOptions as $option) {
                $oldKey = $option['option_name'];
                $newKey = str_replace('stephino_rpg_', 'wp_rpg_', $oldKey);
                
                // Only copy if the new key doesn't exist yet
                if (false === get_option($newKey, false)) {
                    // Copy the value to the new key
                    update_option($newKey, $option['option_value']);
                }
            }
            
            // Log migration completion
            if (function_exists('error_log')) {
                error_log(
                    sprintf(
                        'Stephino RPG: Migrated %d option(s) from stephino_rpg_* to wp_rpg_*',
                        count($oldOptions)
                    )
                );
            }
        }
    }
}

/*EOF*/
