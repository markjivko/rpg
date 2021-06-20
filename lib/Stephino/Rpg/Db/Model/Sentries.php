<?php
/**
 * Stephino_Rpg_Db_Model_Sentries
 * 
 * @title     Model:Sentries
 * @desc      Sentries Model
 * @copyright (c) 2021, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Db_Model_Sentries extends Stephino_Rpg_Db_Model {

    /**
     * Sentries Model Name
     */
    const NAME = 'sentries';
    
    // Files
    const FILE_DEFINITION = 'def.json';
    const FILE_ICON       = '512.png';
    
    // Sentry challenge types
    const CHALLENGE_ATTACK  = 'a';
    const CHALLENGE_DEFENSE = 'd';
    const CHALLENGE_LOOTING = 'l';
    
    // Maximum lengths
    const MAX_LENGTH_NAME = 30;
    
    /**
     * Path to sentry images in the WordPress upload folder
     * 
     * @var string
     */
    protected $_sentryPath = null;
    
    /**
     * Cache for various methods
     * 
     * @var array
     */
    protected $_cache = array();
    
    /**
     * Model definition
     * 
     * @param Stephino_Rpg_Db $dbObject
     */
    public function __construct(Stephino_Rpg_Db $dbObject) {
        parent::__construct($dbObject);
        
        // Prepare the upload directory
        $uploadDir = wp_upload_dir();
        
        // Store the paths
        $this->_sentryPath = rtrim($uploadDir['basedir'], '/\\') . '/' . Stephino_Rpg::PLUGIN_SLUG . '/' . Stephino_Rpg::FOLDER_SENTRIES;
    }
    
    /**
     * Get the over-power yield factor
     * 
     * @param int $successRate Success rate, [0-100]
     * @return int [1,100]
     */
    public function getYield($successRate) {
        // Prepare the reduction factor
        $overPowerYield = Stephino_Rpg_Config::get()->core()->getSentryOPYield();
        
        // Get the final yield
        return $successRate <= 50
            ? 100
            : (($overPowerYield - 100) * ($successRate - 100) / 50 + $overPowerYield);
    }
    
    /**
     * Get the success rate for a sentry challenge
     * 
     * @param int $aAttackLevel  Attacking player attack level
     * @param int $aDefenseLevel Attacking player defense level
     * @param int $bAttackLevel  Defending player attack level
     * @param int $bDefenseLevel Defending player defense level
     * @return float [0,100]
     */
    public function getSuccessRate($aAttackLevel, $aDefenseLevel, $bAttackLevel, $bDefenseLevel) {
        // When the level difference is this large, success is 100%
        $levelDifference = 5;
        
        // Get the first round success rate
        $roundA = (50 / $levelDifference) * ($aAttackLevel - $bDefenseLevel) + 50;
        $roundA = $roundA < 0 ? 0 : ($roundA > 100 ? 100 : $roundA);
        
        // Get the secound success rate
        $roundB = (50 / $levelDifference) * ($bAttackLevel - $aDefenseLevel) + 50;
        $roundB = 100 - ($roundB < 0 ? 0 : ($roundB > 100 ? 100 : $roundB));

        // The attack is more important than the defense
        $result = (4 * $roundA + $roundB) / 5;
        
        // Starter boost
        if ($aAttackLevel < $levelDifference || $aDefenseLevel < $levelDifference) {
            $result += max($levelDifference - $aAttackLevel, $levelDifference - $aDefenseLevel) + 1;
        }
        
        return $result < 0 ? 0 : ($result > 100 ? 100 : $result);
    }
    
    /**
     * Get corresponding Users table columns for known (and allowed) challenge types
     * 
     * @return string[] Associative array of sentry challenge type => corresponding User table sentry level column
     */
    public function getColumns() {
        // Prepare the cache key
        $cacheKey = __FUNCTION__;
        
        // Cache check
        if (!isset($this->_cache[$cacheKey])) {
            $this->_cache[$cacheKey] = array(
                self::CHALLENGE_ATTACK  => Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_LEVEL_ATTACK,
                self::CHALLENGE_DEFENSE => Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_LEVEL_DEFENSE,
                self::CHALLENGE_LOOTING => Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_LEVEL_LOOTING,
            );

            // Looting not available
            if (!Stephino_Rpg_Config::get()->core()->getSentryLootGold() 
                && !Stephino_Rpg_Config::get()->core()->getSentryLootResearch()) {
                unset($this->_cache[$cacheKey][self::CHALLENGE_LOOTING]);
            }
        }
        
        return $this->_cache[$cacheKey];
    }
    /**
     * Get corresponding CSS icon classes for known (and allowed) challenge types
     * 
     * @return string[] Associative array of sentry challenge type => corresponding icon class
     */
    public function getIcons() {
        // Prepare the cache key
        $cacheKey = __FUNCTION__;
        
        // Cache check
        if (!isset($this->_cache[$cacheKey])) {
            $this->_cache[$cacheKey] = array(
                self::CHALLENGE_ATTACK  => Stephino_Rpg_Renderer_Ajax::RESULT_MIL_ATTACK,
                self::CHALLENGE_DEFENSE => Stephino_Rpg_Renderer_Ajax::RESULT_MIL_DEFENSE,
                self::CHALLENGE_LOOTING => Stephino_Rpg_Renderer_Ajax::RESULT_MTR_STORAGE,
            );

            // Looting not available
            if (!Stephino_Rpg_Config::get()->core()->getSentryLootGold() 
                && !Stephino_Rpg_Config::get()->core()->getSentryLootResearch()) {
                unset($this->_cache[$cacheKey][self::CHALLENGE_LOOTING]);
            }
        }
        
        return $this->_cache[$cacheKey];
    }
    
    /**
     * Get the challenge labels
     * 
     * @param boolean $actionLabel (optional) Get the action label (ex. "Improve attack") instead of the object label (ex. "Attack"); default <b>false</b>
     * @return array Associative array of (string) sentry challenge type => (string) i18n challenge label
     */
    public function getLabels($actionLabel = false) {
        // Prepare the cache key
        $cacheKey = __FUNCTION__ . ':' . ($actionLabel ? '1' : '0');
        
        // Cache check
        if (!isset($this->_cache[$cacheKey])) {
            $this->_cache[$cacheKey] = $actionLabel
                ? array(
                    self::CHALLENGE_ATTACK  => __('Improve attack', 'stephino-rpg'),
                    self::CHALLENGE_DEFENSE => __('Improve defense', 'stephino-rpg'),
                    self::CHALLENGE_LOOTING => __('Improve looting', 'stephino-rpg'),
                )
                : array(
                    self::CHALLENGE_ATTACK  => __('Attack', 'stephino-rpg'),
                    self::CHALLENGE_DEFENSE => __('Defense', 'stephino-rpg'),
                    self::CHALLENGE_LOOTING => __('Looting', 'stephino-rpg'),
                );

            // Looting not available
            if (!Stephino_Rpg_Config::get()->core()->getSentryLootGold() 
                && !Stephino_Rpg_Config::get()->core()->getSentryLootResearch()) {
                unset($this->_cache[$cacheKey][self::CHALLENGE_LOOTING]);
            }
        }
        
        return $this->_cache[$cacheKey];
    }
    
        /**
     * Get the sentry levels
     * 
     * @param array $userData (optional) User DB row; default <b>null</b>
     * @return array Associative array of (string) sentry challenge type => (int) sentry level
     */
    public function getLevels($userData = null) {
        $result = array();
        
        foreach ($this->getColumns() as $sentryChallenge => $columnName) {
            $result[$sentryChallenge] = is_array($userData) && isset($userData[$columnName]) 
                ? (int) $userData[$columnName] 
                : 0;
        }
        
        return $result;
    }
    
    /**
     * Get all sentries that are not on a challenge excluding this user, in ascending order of defense level<br/>
     * Auto-populate sentry names on <b>$init</b>
     * 
     * @param int     $excludeUserId Excluded User ID
     * @param int     $limitCount    (optional) Query limit; default <b>null</b>
     * @param int     $limitOffset   (optional) Query limit offset; default <b>null</b>
     * @param boolean $init          (optional) Auto-populate sentry names; default <b>false</b>
     * @return array
     */
    public function getList($excludeUserId, $limitCount = null, $limitOffset = null, $init = false) {
        $result = $this->getDb()->tableUsers()->getSentries($excludeUserId, $limitCount, $limitOffset);
        
        // Initialize procedure
        if ($init) {
            $fieldsArray = array();
            
            // Go through the rows
            foreach ($result as &$dbRow) {
                if (!strlen($dbRow[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_NAME])) {
                    // Generate a new sentry name
                    $newSentryName = Stephino_Rpg_Utils_Lingo::generateSentryName();

                    // Append to the multi-update chore
                    $fieldsArray[(int) $dbRow[Stephino_Rpg_Db_Table_Users::COL_ID]] = array(
                        Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_NAME => $newSentryName
                    );

                    // Update the local result
                    $dbRow[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_NAME] = $newSentryName;
                }
            }
            
            // Multi-update ready
            if (count($fieldsArray)) {
                if (null !== $multiUpdateQuery = Stephino_Rpg_Utils_Db::multiUpdate(
                        $this->getDb()->tableUsers()->getTableName(), 
                        Stephino_Rpg_Db_Table_Users::COL_ID, 
                        $fieldsArray
                    )
                ) {
                    $this->getDb()->getWpDb()->query($multiUpdateQuery);
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Get a sentry's file path (located in the uploads/sentries folder)
     * 
     * @param int     $userId   Corresponding user ID
     * @param string  $fileName (optional) File type; one of <ul>
     * <li>Stephino_Rpg_Db_Model_Sentries::FILE_ICON</li>
     * <li>Stephino_Rpg_Db_Model_Sentries::FILE_DEFINITION</li>
     * </ul>
     * @param boolean $initIcon (optional) Copy over the icon from the current theme if it's missing from the corresponding upload folder; default <b>false</b>
     * @return string
     */
    public function getFilePath($userId, $fileName = self::FILE_ICON, $initIcon = false) {
        $userId = abs((int) $userId);
        
        // Prepare the result
        $result = $this->_sentryPath . '/' . $userId . '/' . $fileName;
        
        // Initialize the sentry icon
        if (self::FILE_ICON === $fileName && $initIcon) {
            // Prepare the parent directory
            if (!Stephino_Rpg_Utils_Folder::get()->fileSystem()->is_dir($this->_sentryPath)) {
                if (!Stephino_Rpg_Utils_Folder::get()->fileSystem()->is_dir(dirname($this->_sentryPath))) {
                    Stephino_Rpg_Utils_Folder::get()->fileSystem()->mkdir(dirname($this->_sentryPath));
                }
                if (!Stephino_Rpg_Utils_Folder::get()->fileSystem()->mkdir($this->_sentryPath)) {
                    throw new Exception(__('Could not create new directory', 'stephino-rpg') . '( ' . self::NAME . ')');
                }
            }
            
            // Create the avatar folder
            if (!Stephino_Rpg_Utils_Folder::get()->fileSystem()->is_dir($this->_sentryPath . '/' . $userId)) {
                if (!Stephino_Rpg_Utils_Folder::get()->fileSystem()->mkdir($this->_sentryPath . '/' . $userId)) {
                    throw new Exception(__('Could not create new directory', 'stephino-rpg') . '( ' . self::NAME . '/' . $userId . ')');
                }
            }
            
            // Copy over the default avatar
            if (!Stephino_Rpg_Utils_Folder::get()->fileSystem()->is_file($result)) {
                Stephino_Rpg_Utils_Folder::get()->fileSystem()->copy(
                    Stephino_Rpg_Utils_Themes::getActive()->getFilePath(
                        Stephino_Rpg_Theme::FOLDER_IMG_UI . '/' . self::NAME . '/' . $fileName
                    ), 
                    $result
                );
            }
        }
        
        return $result;
    }
}

/* EOF */