<?php
/**
 * Stephino_Rpg_Utils_Media
 * 
 * @title     Utils:Media
 * @desc      Media utils
 * @copyright (c) 2021, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Utils_Media {
    
    /**
     * Folder names
     */
    const FOLDER_COMMON = 'common';
    
    /**
     * File names
     */
    const IMAGE_512              = '512';
    const IMAGE_512_VACANT       = '512-vacant';
    const IMAGE_512_VACANT_ABOVE = '512-vacant-above';
    
    /**
     * File extensions
     */
    const EXT_PNG = 'png';
    const EXT_JPG = 'jpg';
    
    /**
     * Allowed extensions
     */
    const EXTENSIONS = array(
        self::EXT_PNG,
        self::EXT_JPG,
    );
    
    /**
     * Cache the glob requests for closest background searches
     * 
     * @var int[][]
     */
    protected static $_cacheClosestBackground = array();
    
    /**
     * Cache for the plugins URL
     * 
     * @var string
     */
    protected static $_cachePluginsUrl = null;
    
    /**
     * Cache for admin URLs
     * 
     * @var string[]
     */
    protected static $_cacheAdminUrls = array();
    
    /**
     * Get the Avatar URL for a user
     * 
     * @param int    $wpUserId WordPress User ID
     * @param int    $size     (optional) Height and width of the avatar image file in pixels; default <b>256</b>
	 * @param string $default  (optional) URL for the default image or a default type; accepts: <ul>
     * <li>'404': return a 404 instead of a default image</li>
     * <li>'retro': 8bit</li>
     * <li>'monsterid': monster</li>
     * <li>'wavatar': cartoon face</li>
     * <li>'indenticon': the "quilt"</li>                           
     * <li>'mystery', 'mm', or 'mysteryman': The Oyster Man</li>
     * <li>'blank': transparent GIF</li>
	 * <li>'gravatar_default': the Gravatar logo</li>
     * </ul>
     * default <b>wavatar</b>
     * @return string|null WordPress User Avatar URL or null on error
     */
    public static function getAvatarUrl($wpUserId, $size = 256, $default = 'wavatar') {
        $result = null;

        do {
            // Filtered avatar <img/> tag
            if (preg_match('%\bsrc\s*=\s*["\'](.*?)["\']%ims', get_avatar($wpUserId, $size, $default), $matches)) {
                $result = $matches[1];
                break;
            }

            // Fall-back to the unfiltered avatar url
            $result = get_avatar_url(
                $wpUserId,
                array(
                    'size'    => $size,
                    'default' => $default
                )
            );
        } while (false);

        return $result;
    }

    /**
     * Get the plugins URL
     * 
     * @param boolean $proVersion (optional) Get the pro version link; default <b>false</b>
     * @return string
     */
    public static function getPluginsUrl($proVersion = false) {
        // Cache check
        if (null === self::$_cachePluginsUrl) {
            self::$_cachePluginsUrl = plugins_url('', STEPHINO_RPG_ROOT) . '/' . Stephino_Rpg::PLUGIN_SLUG;
        }
        
        return self::$_cachePluginsUrl . ($proVersion ? '-pro' : '');
    }
    
    /**
     * Get URLs to <b>admin.php</b> and <b>admin-ajax.php</b> pages
     * 
     * @param boolean $ajaxPage     (optional) Get the <b>admin-ajax.php</b> page; default <b>false</b>
     * @param boolean $relativeLink (optional) Get the relative link; default <b>true</b>
     * @return string
     */
    public static function getAdminUrl($ajaxPage = false, $relativeLink = true) {
        // Prepare the cache key
        $cacheKey = ($ajaxPage ? 0 : 1) . '-' . ($relativeLink ? 0 : 1);
        
        // Cache check
        if (!isset(self::$_cacheAdminUrls[$cacheKey])) {
            self::$_cacheAdminUrls[$cacheKey] = rtrim(admin_url(
                $ajaxPage
                    ? 'admin-ajax.php?action=' . Stephino_Rpg::PLUGIN_VARNAME
                    : 'admin.php?page=' . Stephino_Rpg::PLUGIN_SLUG
            ), '/');
            if ($relativeLink) {
                self::$_cacheAdminUrls[$cacheKey] = wp_make_link_relative(self::$_cacheAdminUrls[$cacheKey]);
            }
        }
        
        return self::$_cacheAdminUrls[$cacheKey];
    }
    
    /**
     * Get the PWA app version
     * 
     * @boolean $includeProVersion (optional) Include the Pro plugin version; default <b>false</b>
     * @boolean $includeHash       (optional) Include 12 characters last change hash; default <b>truee</b>
     * @return string
     */
    public static function getPwaVersion($includeProVersion = false, $includeHash = true) {
        // Prepare the Hash part
        $hash = $includeHash
            ? (
                substr(
                    md5(Stephino_Rpg_Cache_Game::get()->read(Stephino_Rpg_Cache_Game::KEY_MEDIA_CHANGED, 1)), 
                    0, 
                    12
                ) . '/'
            ) 
            : '';
        
        // Final plugin version
        return Stephino_Rpg::PLUGIN_VERSION . '/' . $hash . (
            Stephino_Rpg::get()->isPro() 
                ? ('PRO' . ($includeProVersion ? '-' . Stephino_Rpg_Pro::PLUGIN_VERSION : '')) 
                : __('Free', 'stephino-rpg')
        );
    }
    
    /**
     * Get the audio sprite definition for "events.mp3" stored in "events.json" in the PRO plugin
     * 
     * @return array
     */
    public static function getEventsSprite() {
        // Prepare the result
        $result = array();
        
        // Check the events file
        if (is_file($filePath = Stephino_Rpg_Utils_Themes::getActive()->getFilePath(Stephino_Rpg_Theme::FILE_AUDIO_EVENTS))) {
            $result = @json_decode(file_get_contents($filePath), true);
        }
        return $result;
    }

    /**
     * Get the background url of this common file
     * 
     * @param string $configKey Configuration Key; example: <b>Stephino_Rpg_Config_Buildings::KEY</b>
     * @param string $fileName  File name
     * @return type
     */
    public static function getCommonBackgroundUrl($configKey, $fileName = self::IMAGE_512_VACANT) {
        return Stephino_Rpg_Utils_Themes::getActive()->getFileUrl(
            'img/story/' . $configKey . '/' . self::FOLDER_COMMON . '/' . $fileName . '.png'
        );
    }
    
    /**
     * Get the closest available background ID for this item
     * 
     * @param string $configKey     Configuration Key; example: <b>Stephino_Rpg_Config_Buildings::KEY</b>
     * @param int    $configId      Configuration ID
     * @param int    $itemLevel     (optional) Item target level; default <b>1</b>
     * @param string $fileName      (optional) Item file name; default <b>Stephino_Rpg_Utils_Media::IMAGE_512</b>
     * @param string $fileExtension (optional) Item file extension; default <b>Stephino_Rpg_Utils_Media::EXT_PNG</b>
     * @return int|null Closest background ID or null on error
     */
    public static function getClosestBackgroundId($configKey, $configId, $itemLevel = 1, $fileName = self::IMAGE_512, $fileExtension = self::EXT_PNG) {
        if (!in_array($fileExtension, self::EXTENSIONS)) {
            $fileExtension = self::EXT_PNG;
        }
        
        // Prepare the closest background
        $closestBackground = null;

        // Sanitize the values
        $configId = intval($configId);
        $itemLevel = intval($itemLevel);
        
        // Prepare the cache key
        $cacheKey = $configKey . '-' . $fileExtension;
        
        // Valid level requested
        if ($itemLevel > 0) {
            // Cache miss
            if (!isset(self::$_cacheClosestBackground[$cacheKey])) {
                // Initialize as an array
                self::$_cacheClosestBackground[$cacheKey] = array();
                
                // Prepare the image pattern
                $imagePattern = 'img/story/' . $configKey . '/*/*.' . $fileExtension;
                
                // Check for the default theme
                if (Stephino_Rpg_Utils_Themes::getActive()->isDefault()) {
                    $globList = glob(Stephino_Rpg_Utils_Themes::getActive()->getFilePath() . '/' . $imagePattern);
                    
                    // Default pro path already scoped
                    if (Stephino_Rpg::get()->isPro()) {
                        // Search locally
                        $globListLocal = glob(Stephino_Rpg_Utils_Themes::getActive()->getFilePath(null, true) . '/' . $imagePattern);
                        
                        // Append the results
                        $globList = is_array($globList)
                            ? array_merge($globList, $globListLocal)
                            : $globListLocal;
                    }
                } else {
                    $globList = glob(Stephino_Rpg_Utils_Themes::getActive()->getFilePath() . '/' . $imagePattern);
                }
                
                // Get the files list
                if (is_array($globList)) {
                    natsort($globList);
                    foreach($globList as $globItem) {
                        // Prepare the configuration ID
                        $globConfigId = intval(basename(dirname($globItem)));
                        
                        // Not a valid configuration folder
                        if ($globConfigId <= 0) {
                            continue;
                        }
                        
                        // Prepare the file ID
                        $globBaseName = basename($globItem, '.' . $fileExtension);
                        $globFileId = preg_match('%\-\d+$%', $globBaseName)
                            ? intval(preg_replace('%^.*?\-(\d+)$%', '$1', $globBaseName))
                            : 1;
                        
                        // Prepare the file name root
                        $globFileName = preg_replace('%\-\d+$%', '', $globBaseName);
                        
                        // Initialize the holder
                        if (!isset(self::$_cacheClosestBackground[$cacheKey][$globConfigId])) {
                            self::$_cacheClosestBackground[$cacheKey][$globConfigId] = array();
                        }
                        if (!isset(self::$_cacheClosestBackground[$cacheKey][$globConfigId][$globFileName])) {
                            self::$_cacheClosestBackground[$cacheKey][$globConfigId][$globFileName] = array();
                        }
                        
                        // Store the file
                        self::$_cacheClosestBackground[$cacheKey][$globConfigId][$globFileName][] = $globFileId;
                    }
                }
            }
            
            // Valid file list defined
            if (isset(self::$_cacheClosestBackground[$cacheKey]) 
                && isset(self::$_cacheClosestBackground[$cacheKey][$configId])
                && isset(self::$_cacheClosestBackground[$cacheKey][$configId][$fileName])) {
                // Find the closest level
                for ($fileIndex = $itemLevel; $fileIndex >= 1; $fileIndex--) {
                    // File match
                    if (in_array($fileIndex, self::$_cacheClosestBackground[$cacheKey][$configId][$fileName])) {
                        $closestBackground = $fileIndex;
                        break;
                    }
                }
            }
        }

        return $closestBackground;
    }
    
    /**
     * Get the closest available background URL for this item
     * 
     * @param string $configKey      Configuration Key; example: <b>Stephino_Rpg_Config_Buildings::KEY</b>
     * @param int    $configId       Configuration ID
     * @param int    $itemLevel      (optional) Item target level; default <b>1</b>
     * @param string $fileName       (optional) Item file name; default <b>Stephino_Rpg_Utils_Media::IMAGE_512</b>
     * @param string $fileExtension  (optional) Item file extension; default <b>Stephino_Rpg_Utils_Media::EXT_PNG</b>
     * @return string|null Closest background URL or null on error
     */
    public static function getClosestBackgroundUrl($configKey, $configId, $itemLevel = 1, $fileName = self::IMAGE_512, $fileExtension = self::EXT_PNG) {
        if (!in_array($fileExtension, self::EXTENSIONS)) {
            $fileExtension = self::EXT_PNG;
        }
        
        // Prepare the background ID
        if (null === $backgroundId = self::getClosestBackgroundId($configKey, $configId, $itemLevel, $fileName, $fileExtension)) {
            return null;
        }
        
        // Get the path
        return Stephino_Rpg_Utils_Themes::getActive()->getFileUrl(
            'img/story/' . $configKey . '/' . $configId . '/' . $fileName 
            . (1 === $backgroundId ? '' : '-' . $backgroundId) . '.' . $fileExtension
        );
    }
}

/*EOF*/