<?php

/**
 * Stephino_Rpg_Cache_User
 * 
 * @title      User Data Caching
 * @desc       Basic session caching for each individual player; only works for authenticated users
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Cache_User {
    
    // Cache keys
    const KEY_ANN_READ   = 'ann_read';
    const KEY_VOL_MUSIC  = 'vol_music';
    const KEY_VOL_BKG    = 'vol_bkg';
    const KEY_VOL_CELLS  = 'vol_cells';
    const KEY_VOL_EVENTS = 'vol_events';
    
    /**
     * Singleton instance
     *
     * @var Stephino_Rpg_Cache_User
     */
    protected static $_instance = null;
    
    /**
     * Cache data - local storage
     * 
     * @var array
     */
    protected $_data = array();
    
    /**
     * Current user ID
     * 
     * @var int|null
     */
    protected $_userId = null;
    
    /**
     * List of allowed keys
     * 
     * @var string[]
     */
    protected $_allowedKeys = array();
    
    /**
     * Get the cache instance
     * 
     * @return Stephino_Rpg_Cache_User
     */
    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * Transient caching
     */
    protected function __construct() {
        // Current user ID
        $this->_userId = Stephino_Rpg_TimeLapse::get()->userId();
        
        // Initialize the local data storage
        if ($this->_userId) {
            $this->_data = Stephino_Rpg_Db::get()->tableUsers()->getGameSettings();
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
     * Get the current user data
     * 
     * @return array
     */
    public function getData() {
        return $this->_data;
    }
    
    /**
     * Get a value from the cache
     * 
     * @param string $cacheKey Cache key
     * @param mixed  $default  (optional) Default value to return in case cache not set; default <b>null</b>
     * @return mixed|null Cached value or the default on error
     */
    public function getValue($cacheKey, $default = null) {
        return (
            in_array($cacheKey, $this->_allowedKeys) && isset($this->_data[$cacheKey]) 
                ? $this->_data[$cacheKey] 
                : $default
        );
    }
    
    /**
     * Store a value in cache
     * 
     * @param string $cacheKey   Cache key
     * @param mixed  $cacheValue Value to store
     * @return boolean
     */
    public function setValue($cacheKey, $cacheValue) {
        // Prepare the result
        $result = false;
        
        // Valid key
        if (in_array($cacheKey, $this->_allowedKeys)) {
            // Store the local value
            $this->_data[$cacheKey] = $cacheValue;
            
            // Update the option
            if ($this->_userId) {
                $result = Stephino_Rpg_Db::get()->tableUsers()->setGameSettings($this->_data, $this->_userId);
            }
        }
        
        // All done
        return $result;
    }
}

/*EOF*/