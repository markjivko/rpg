<?php

/**
 * Stephino_Rpg_Renderer_Ajax_Admin
 * 
 * @title      Admin actions
 * @desc       Perform actions that are only available to the site admin
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Renderer_Ajax_Admin {

    /**
     * Request keys
     */
    const REQUEST_STATS_TYPE  = 'statsType';
    const REQUEST_STATS_YEAR  = 'statsYear';
    const REQUEST_STATS_MONTH = 'statsMonth';
    const REQUEST_ANN_TITLE   = 'annTitle';
    const REQUEST_ANN_CONTENT = 'annContent';
    const REQUEST_ANN_DAYS    = 'annDays';
    
    /**
     * Get the configuration definition
     * 
     * @return array
     */
    public static function ajaxAdminGetConfig() {
        return Stephino_Rpg_Config::definition();
    }
    
    /**
     * Get the statistics
     * 
     * @param array $data
     * @return array
     * @throws Exception
     */
    public static function ajaxAdminGetStats($data) {
        if (!is_super_admin()) {
            throw new Exception(__('You do not have access to the Dashboard', 'stephino-rpg'));
        }
        
        return Stephino_Rpg_Db::get()->modelStatistics()->export(
            isset($data[self::REQUEST_STATS_TYPE]) ? trim($data[self::REQUEST_STATS_TYPE]) : null,
            isset($data[self::REQUEST_STATS_YEAR]) ? (int) $data[self::REQUEST_STATS_YEAR] : null,
            isset($data[self::REQUEST_STATS_MONTH]) ? (int) $data[self::REQUEST_STATS_MONTH] : null
        );
    }
    
    /**
     * Set an announcement
     * 
     * @param array $data
     * @return array
     * @throws Exception
     */
    public static function ajaxAdminSetAnnouncement($data) {
        if (!is_super_admin()) {
            throw new Exception(__('You do not have access to the Announcements', 'stephino-rpg'));
        }
        
        // Set the announcement
        Stephino_Rpg_Db::get()->modelAnnouncement()->set(
            isset($data[self::REQUEST_ANN_TITLE]) ? trim($data[self::REQUEST_ANN_TITLE]) : '',
            isset($data[self::REQUEST_ANN_CONTENT]) ? trim($data[self::REQUEST_ANN_CONTENT]) : '',
            isset($data[self::REQUEST_ANN_DAYS]) ? abs((int) $data[self::REQUEST_ANN_DAYS]) : 1
        );
        
        // Export the stats
        return Stephino_Rpg_Db::get()->modelStatistics()->export(
            Stephino_Rpg_Db_Model_Statistics::EXPORT_ANNOUNCEMENT
        );
    }
    
    /**
     * Delete the announcement
     * 
     * @return array
     * @throws Exception
     */
    public static function ajaxAdminDelAnnouncement() {
        if (!is_super_admin()) {
            throw new Exception(__('You do not have access to the Announcements', 'stephino-rpg'));
        }
        
        // Set the announcement
        Stephino_Rpg_Db::get()->modelAnnouncement()->del();
        
        // Export the stats
        return Stephino_Rpg_Db::get()->modelStatistics()->export(
            Stephino_Rpg_Db_Model_Statistics::EXPORT_ANNOUNCEMENT
        );
    }

    /**
     * Export the configuration data in JSON format
     * 
     * @return string JSON
     * @throws Exception
     */
    public static function ajaxAdminExportConfig() {
        if (!is_super_admin()) {
            throw new Exception(__('You do not have permission to export the game configuration', 'stephino-rpg'));
        }
        return Stephino_Rpg_Config::export(false, true);
    }
    
    /**
     * Reset the game configuration to default values
     */
    public static function ajaxAdminResetConfig() {
        if (!is_super_admin()) {
            throw new Exception(__('You do not have permission to reset the game configuration', 'stephino-rpg'));
        }
        return Stephino_Rpg_Config::reset();
    }
    
    /**
     * Restart the game, erasing all game progress for all players
     * 
     * @throws Exception
     */
    public static function ajaxAdminRestartGame() {
        if (!is_super_admin()) {
            throw new Exception(__('You do not have permission to reset the game', 'stephino-rpg'));
        }
        
        // Remove all options (except for the PRO-level config)
        Stephino_Rpg::get()->purge();

        // Drop all tables
        Stephino_Rpg_Db::get()->purge();
    }

    /**
     * Save the configuration data
     * 
     * @param array $data Configuration array
     * @throws Exception
     */
    public static function ajaxAdminSetConfig($data) {
        if (!is_super_admin()) {
            throw new Exception(__('(DEMO) You are free to experiment but your changes will not be saved', 'stephino-rpg'));
        }
        
        // Get the data
        if (!is_array($data) || !count($data)) {
            throw new Exception(__('Invalid configuration object', 'stephino-rpg'));
        }

        // Set the data
        Stephino_Rpg_Config::set($data);

        // Validate and save the data
        Stephino_Rpg_Config::save();

        // Regenerate the CSS animation rules
        Stephino_Rpg_Renderer_Ajax_Css::generate();
    }

}

/*EOF*/