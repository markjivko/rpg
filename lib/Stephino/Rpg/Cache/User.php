<?php

/**
 * Stephino_Rpg_Cache_User
 * 
 * @title      User Data Caching
 * @desc       Basic session caching for each individual player; only works for authenticated users
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Cache_User {
    
    /**
     * Announcement read
     */
    const KEY_ANN        = 'ann';
    
    /**
     * Changelog read (binds to current game version)
     */
    const KEY_CHL        = 'chl';
    
    /**
     * Last selected language
     */
    const KEY_LANG       = 'lang';
    
    // Volumes
    const KEY_VOL_MUSIC  = 'vol_music';
    const KEY_VOL_BKG    = 'vol_bkg';
    const KEY_VOL_CELLS  = 'vol_cells';
    const KEY_VOL_EVENTS = 'vol_events';
    
    // Platformer
    const KEY_PTF_DATA   = 'ptf_data';
    const KEY_PTF_TIME   = 'ptf_time';
    const KEY_PTF_RATES  = 'ptf_rates';
    
    // Robot attack revenge list and last attack time
    const KEY_ROBOT_ATT_LIST = 'att_list';
    const KEY_ROBOT_ATT_TIME = 'att_time';
    
    /**
     * Platformer game started 
     */
    const PTF_DATA_STARTED = 1;
    
    /**
     * Platformer game lost
     */
    const PTF_DATA_LOST = 0;
    
    /**
     * Platformer game won
     */
    const PTF_DATA_WON = 2;
    
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
     * Values changed
     * 
     * @var boolean
     */
    protected $_changed = false;
    
    /**
     * Get the user cache instance, time-lapse workspace dependant
     * 
     * @return Stephino_Rpg_Cache_User
     */
    public static function get() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        
        // Get the current workspace user ID
        $userId = Stephino_Rpg_TimeLapse::get()->userId();
        
        // Re-initialize
        if (self::$_instance->_userId != $userId) {
            self::$_instance->_userId  = $userId;
            self::$_instance->_data    = array();
            self::$_instance->_changed = false;

            // Initialize the local data storage
            if (self::$_instance->_userId) {
                list($wpUserId, $robotId) = Stephino_Rpg_TimeLapse::getWorkspace();
                self::$_instance->_data = Stephino_Rpg_Db::get($robotId, $wpUserId)
                    ->tableUsers()
                    ->getGameSettings();
            }
        }
        
        return self::$_instance;
    }
    
    /**
     * Transient caching
     */
    protected function __construct() {
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
    public function data() {
        return $this->_data;
    }
    
    /**
     * Get a value from the cache
     * 
     * @param string $cacheKey Cache key
     * @param mixed  $default  (optional) Default value to return in case cache not set; default <b>null</b>
     * @return mixed|null Cached value or the default on error
     */
    public function read($cacheKey, $default = null) {
        return in_array($cacheKey, $this->_allowedKeys) && isset($this->_data[$cacheKey]) 
            ? $this->_data[$cacheKey] 
            : $default;
    }
    
    /**
     * Store a value in cache; must call "commit" to save the changed values to the database
     * 
     * @param string $cacheKey   Cache key
     * @param mixed  $cacheValue Value to store
     * @return Stephino_Rpg_Cache_User
     */
    public function write($cacheKey, $cacheValue) {
        // Valid key
        if (in_array($cacheKey, $this->_allowedKeys)) {
            $this->_data[$cacheKey] = $cacheValue;
            $this->_changed = true;
        }
        
        return $this;
    }
    
    /**
     * Commit the changed values to the database; must be called after "write"
     * 
     * @return boolean
     */
    public function commit() {
        $result = false;
        
        // Settings changed
        if ($this->_changed && $this->_userId) {
            list($wpUserId, $robotId) = Stephino_Rpg_TimeLapse::getWorkspace();
            
            $result = Stephino_Rpg_Db::get($robotId, $wpUserId)
                ->tableUsers()
                ->setGameSettings(
                    $this->_data, 
                    $this->_userId
                );
            
            $this->_changed = false;
        }
        
        return $result;
    }
}

/*EOF*/