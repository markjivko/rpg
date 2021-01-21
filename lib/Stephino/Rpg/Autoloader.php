<?php
/**
 * Stephino_Rpg_Autoloader
 * 
 * @title      Autoloader
 * @desc       PHP Autoloader
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Autoloader {

    /**
     * Maximum execution time in seconds; prevents zombie processes
     */
    const TIME_LIMIT = 300;
    
    /**
     * Autoloader instance
     * 
     * @var Stephino_Rpg_Autoloader
     */
    protected static $_instance;

    /**
     * Singleton
     * 
     * @return Stephino_Rpg_Autoloader
     */
    public static function getInstance() {
        if (!isset(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Private constructor
     */
    protected function __construct() {
        // Memory limit
        @ini_set('memory_limit', '256M');
        @ini_set('pcre.backtrack_limit', '1024M');

        // Time limit
        set_time_limit(self::TIME_LIMIT);
        @ini_set('max_execution_time', self::TIME_LIMIT);
        @ini_set('max_input_time', self::TIME_LIMIT);

        // Autoloader
        spl_autoload_register(array($this, '_findClass'));
    }

    /**
     * Locate and include a class by name
     * 
     * @param string $className Class name
     */
    protected function _findClass($className = '') {
        // Prepare the class path
        $classPath = str_replace(array(' ', '\\'), '/', ucwords(str_replace('_', ' ', $className)));
        if (file_exists($classFileName = STEPHINO_RPG_ROOT . '/lib/' . $classPath . '.php')
            || (defined('STEPHINO_RPG_PRO_ROOT') && file_exists($classFileName = STEPHINO_RPG_ROOT . '-pro/lib/' . $classPath . '.php'))) {
            require_once $classFileName;
        }
    }
}

/* EOF */