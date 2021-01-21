<?php
/**
 * Stephino_Rpg_Cache_Ajax
 * 
 * @title      Rendering Cache
 * @desc       Browser cache mechanism
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Cache_Ajax {
    
    /**
     * Web Cache time in seconds (365 days).<br/>
     * The ETag changes - forcing a cache reset - every time you <b>modify the 
     * Game Mechanics</b> or <b>update this plugin</b>, and individually on each <b>player sign-in</b> 
     * so it is safe to set a very large value. 
     */
    const WEB_CACHE_TIME = 31536000;
    
    /**
     * Singleton instances of Stephino_Rpg_Cache_Ajax
     * 
     * @var Stephino_Rpg_Cache_Ajax[] 
     */
    protected static $_instances = array();
    
    /**
     * Called class name
     * 
     * @var string
     */
    protected $_className = null;
    
    /**
     * Called class method name
     * 
     * @var string
     */
    protected $_methodName = null;
    
    /**
     * Unique instance identifier
     * 
     * @var string
     */
    protected $_instanceKey = null;
    
    /**
     * Animations last change
     *
     * @var int|null
     */
    protected $_timestampCreated = null;
    
    /**
     * Animations last change date time string
     *
     * @var string|null
     */
    protected $_timestampCreatedString = null;
    
    /**
     * Entity Tag
     * 
     * @var string
     */
    protected $_eTag = null;
    
    /**
     * Get a Singleton instance
     * 
     * @param string $className  Called class name
     * @param string $methodName Called class method name
     * @param array  $extraInfo  (optional) Extra info, list of strings; default <b>[]</b>
     * @return Stephino_Rpg_Cache_Ajax
     */
    public static function getInstance($className, $methodName, Array $extraInfo = array()) {
        // Prepare the instance key
        $instanceKey = trim($className) . '.' . trim($methodName) . '.' . implode('.', $extraInfo);
        
        // Prepare the instance
        if (!isset(self::$_instances[$instanceKey])) {
            self::$_instances[$instanceKey] = new self($className, $methodName, $instanceKey);
        }
        
        return self::$_instances[$instanceKey];
    }
    
    /**
     * Class constructor
     * 
     * @param string $className   Called class name
     * @param string $methodName  Called class method name
     * @param array  $instanceKey Unique instance identifier
     * @return Stephino_Rpg_Cache_Ajax
     */
    protected function __construct($className, $methodName, $instanceKey) {
        $this->_className = $className;
        $this->_methodName = $methodName;
        $this->_instanceKey = $instanceKey;
        
        // Store the last animation change timestamp
        $this->_timestampCreated = Stephino_Rpg_Cache_Game::getInstance()->getValue(Stephino_Rpg_Cache_Game::KEY_ANIMATIONS_LAST_CHANGE, null);
        $this->_timestampCreatedString = null === $this->_timestampCreated ? null : (gmdate('D, d M Y H:i:s ', $this->_timestampCreated) . 'GMT');
        
        // Create the entity tag
        $this->_eTag = md5(implode('-', array(
            Stephino_Rpg::PLUGIN_VERSION,
            Stephino_Rpg::PLUGIN_VERSION_DATABASE,
            $this->_instanceKey,
            $this->_timestampCreated,
            isset($_COOKIE) && isset($_COOKIE[LOGGED_IN_COOKIE]) ? trim($_COOKIE[LOGGED_IN_COOKIE]) : 'x',
        )));
    }
    
    /**
     * Cache hit?
     * 
     * @return boolean
     */
    public function cacheHit() {
        // Prepare the flags
        $flagModifiedSince = isset($_SERVER) && isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false;
        $flagNoneMatch = isset($_SERVER) && isset($_SERVER['HTTP_IF_NONE_MATCH']) ? $_SERVER['HTTP_IF_NONE_MATCH'] : false;
        
        // Cache hit
        return (($flagNoneMatch && $flagNoneMatch == $this->_eTag) || (!$flagNoneMatch)) 
            && ($flagModifiedSince && $flagModifiedSince == $this->_timestampCreatedString);
    }
    
    /**
     * Send a "304 Not Modified" header if necessary.<br/>
     * <b>Will call exit() after sending the headers!</b>
     * 
     * @param int $cacheTime (optional) Maximum cache age; default <b>null</b> - converted to self::WEB_CACHE_TIME
     */
    public function sendHeaders($cacheTime = null) {
        // Default caching
        if (null === $cacheTime) {
            $cacheTime = self::WEB_CACHE_TIME;
        } else {
            $cacheTime = intval($cacheTime);
            if ($cacheTime < 0) {
                $cacheTime = 0;
            }
        }
        
        // Correctly saved by the user
        if (null !== $this->_timestampCreated) {
            // Prepare the expiration timestamp
            $timestampExpiresString = gmdate('D, d M Y H:i:s ', $this->_timestampCreated + $cacheTime) . 'GMT';

            // Cache this file
            header("Cache-Control: private, max-age={$cacheTime}");
            header("Last-Modified: {$this->_timestampCreatedString}");
            header("Expires: {$timestampExpiresString}");
            header("ETag: $this->_eTag");

            // File not modified
            if ($this->cacheHit()) {
                header(wp_get_server_protocol() . ' 304 Not Modified');
                exit();
            }
        }
    }
}

/*EOF*/