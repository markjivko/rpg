<?php

/**
 * Stephino_Rpg_Cache_Game
 * 
 * @title      Game Data Caching
 * @desc       Caching mechanism for common game data
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Cache_Game {

    // Cache keys
    const KEY_DB_VERSION             = 'db_version';
    const KEY_PTF_VERSION            = 'ptf_version';
    const KEY_WORLD_INIT             = 'world_init';
    const KEY_ANIMATIONS             = 'animations';
    const KEY_ANIMATIONS_LAST_CHANGE = 'animations_last_change';
    
    /**
     * Singleton instance
     *
     * @var Stephino_Rpg_Cache_Game
     */
    protected static $_instance = null;
    
    /**
     * Cache data - local storage
     * 
     * @var array
     */
    protected $_data = null;
    
    /**
     * List of allowed keys
     * 
     * @var string[]
     */
    protected $_allowedKeys = array();
    
    /**
     * Get the cache instance
     * 
     * @return Stephino_Rpg_Cache_Game
     */
    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * Caching
     */
    protected function __construct() {
        // Initialize the local data storage
        $this->_data = json_decode(get_option(Stephino_Rpg::OPTION_CACHE, '[]'), true);
        
        // Invalid value
        if (!is_array($this->_data)) {
            $this->_data = array();
        }
        
        // Prepare the allowed keys
        $classReflection = new ReflectionClass($this);
        foreach ($classReflection->getConstants() as $constantName => $constantValue) {
            if (preg_match('%^KEY_%', $constantName)) {
                $this->_allowedKeys[] = $constantValue;
            }
        }
    }
    
    /**
     * Get a value from the cache
     * 
     * @param string $cacheKey Cache key
     * @param mixed  $default  (optional) Default value to return in case cache not set; default <b>null</b>
     * @return mixed|null Cached value or the default on error
     */
    public function getValue($cacheKey, $default = null) {
        return (in_array($cacheKey, $this->_allowedKeys) && isset($this->_data[$cacheKey]) ? $this->_data[$cacheKey] : $default);
    }
    
    /**
     * Store a value in cache
     * 
     * @param string $cacheKey Cache key
     * @param mixed  $value    Value to store
     * @return Stephino_Rpg_Cache_Game
     */
    public function setValue($cacheKey, $value) {
        // Valid key
        if (in_array($cacheKey, $this->_allowedKeys)) {
            // Store the local value
            $this->_data[$cacheKey] = $value;
            
            // Update the option
            update_option(Stephino_Rpg::OPTION_CACHE, json_encode($this->_data));
        }
        
        // Method chaining
        return $this;
    }
}

/*EOF*/