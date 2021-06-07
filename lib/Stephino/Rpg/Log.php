<?php

/**
 * Stephino_Rpg
 * 
 * @title      RPG Logging utility
 * @desc       Entry points handler
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Log {
    
    /**
     * Debug logging
     * 
     * @ignore
     */
    public static function debug() {
        // Disabled in production 
    }
    
    /**
     * Debug logging (file and line from parent caller)
     * 
     * @ignore
     */
    public static function debugParent() {
        // Disabled in production 
    }
    
    /**
     * Info logging
     * 
     * @ignore
     */
    public static function info() {
        // Disabled in production 
    }
    
    /**
     * Info logging (file and line from parent caller)
     * 
     * @ignore
     */
    public static function infoParent() {
        // Disabled in production 
    }
    
    /**
     * Warning logging
     * 
     * @ignore
     */
    public static function warning() {
        // Disabled in production 
    }
    
    /**
     * Warning logging (file and line from parent caller)
     * 
     * @ignore
     */
    public static function warningParent() {
        // Disabled in production 
    }
    
    /**
     * Error logging
     * 
     * @ignore
     */
    public static function error() {
        // Disabled in production 
    }
    
    /**
     * Error logging (file and line from parent caller)
     * 
     * @ignore
     */
    public static function errorParent() {
        // Disabled in production 
    }
    
    /**
     * Check whether logs are used; returns false in production
     * 
     * @return boolean
     */
    public static function check() {
        // Disabled in production
        return false;
        //
    }
    
    /**
     * Log helper
     * 
     * @ignore
     * @param array $data Function arguments
     * @return mixed
     */
    protected static function _print($data) {
        // Disabled in production
    }
}

/*EOF*/