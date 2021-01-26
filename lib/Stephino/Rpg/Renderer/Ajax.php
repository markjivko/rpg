<?php

/**
 * Stephino_Rpg_Renderer_Ajax
 * 
 * @title      AJAX Renderer
 * @desc       Holds the available AJAX methods
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Renderer_Ajax {
    
    // AJAX request keys
    const METHOD_PREFIX         = 'ajax';
    const CALL_VERSION          = 'ver';
    const CALL_METHOD           = 'method';
    const CALL_DATA             = 'data';
    const CALL_CONFIG_ID        = 'configId';
    const CALL_VIEW             = 'view';
    const CALL_VIEW_DATA        = 'viewData';
    const CALL_RESPONSE_STATUS  = 'status';
    const CALL_RESPONSE_RESULT  = 'result';
    const CALL_RESPONSE_CONTENT = 'content';    
    
    // AJAX Controllers
    const CONTROLLER_ACTION = 'action';
    const CONTROLLER_ADMIN  = 'admin';
    const CONTROLLER_CELLS  = 'cells';
    const CONTROLLER_CSS    = 'css';
    const CONTROLLER_DIALOG = 'dialog';
    const CONTROLLER_HTML   = 'html';
    const CONTROLLER_JS     = 'js';
    
    // Common JS and CSS scripts
    const FILE_COMMON   = 'common';
    
    // Platformer files
    const FILE_PTF_MAIN = 'ptf-main';
    const FILE_PTF_LIST = 'ptf-list';
    
    // Modal sizes
    const MODAL_SIZE_LARGE  = true;
    const MODAL_SIZE_NORMAL = null;
    const MODAL_SIZE_SMALL  = false;
    
    /**
     * Available AJAX controllers
     * 
     * @var string[]
     */
    const AVAILABLE_CONTROLLERS = array(
        self::CONTROLLER_ACTION,
        self::CONTROLLER_ADMIN,
        self::CONTROLLER_CELLS,
        self::CONTROLLER_CSS,
        self::CONTROLLER_DIALOG,
        self::CONTROLLER_HTML,
        self::CONTROLLER_JS,
    );
    
    // Game views
    const VIEW_WORLD  = 'world';
    const VIEW_ISLAND = 'island';
    const VIEW_CITY   = 'city';
    const VIEW_PWA    = 'pwa';
    const VIEW_PTF    = 'ptf';
    
    /**
     * Available Game views
     * 
     * @var string[]
     */
    const AVAILABLE_VIEWS = array(
        self::VIEW_WORLD,
        self::VIEW_ISLAND,
        self::VIEW_CITY,
        self::VIEW_PWA,
        self::VIEW_PTF,
    );
    
    // Result keys
    const RESULT_DATA              = 'data';
    const RESULT_TUTORIAL          = 'tutorial';
    const RESULT_MESSAGES          = 'messages';
    const RESULT_CONVOYS           = 'convoys';
    const RESULT_PREMIUM           = 'premium';
    const RESULT_QUEUES            = 'queues';
    const RESULT_RESOURCES         = 'resources';
    const RESULT_ENTITIES          = 'entities';
    const RESULT_ANNOUNCEMENT      = 'announcement';
    const RESULT_CHANGELOG         = 'changelog';
    const RESULT_NAVIGATION        = 'navigation';
    const RESULT_BUILDING_UPGS     = 'building_upgs';
    const RESULT_SETTINGS          = 'settings';
    const RESULT_MODAL_SIZE        = 'modal_size';
    const RESULT_SET_TLCD          = 'tlcd';
    const RESULT_TUT_TOTAL         = 'total';
    const RESULT_RES_FIAT          = 'fiat';
    const RESULT_RES_GOLD          = 'gold';
    const RESULT_RES_GEM           = 'gem';
    const RESULT_RES_RESEARCH      = 'research';
    const RESULT_RES_ALPHA         = 'alpha';
    const RESULT_RES_BETA          = 'beta';
    const RESULT_RES_GAMMA         = 'gamma';
    const RESULT_RES_EXTRA_1       = 'extra1';
    const RESULT_RES_EXTRA_2       = 'extra2';
    const RESULT_MTR_SATISFACTION  = 'mtr_satisfaction';
    const RESULT_MTR_STORAGE       = 'mtr_storage';
    const RESULT_MTR_POPULATION    = 'mtr_population';
    const RESULT_MIL_ATTACK        = 'mil_attack';
    const RESULT_MIL_DEFENSE       = 'mil_defense';
    const RESULT_NAV_CITIES        = 'cities';
    const RESULT_NAV_NAME          = 'name';
    const RESULT_NAV_ICON          = 'icon';
    const RESULT_NAV_LEVEL         = 'level';
    const RESULT_NAV_CAPITAL       = 'capital';
    const RESULT_NAV_CURRENT       = 'current';
    const RESULT_NAV_ISLANDS       = 'islands';
    const RESULT_NAV_ISLAND_COORDS = 'coords';
    
    /**
     * Intermediate tutorial step
     *
     * @var int|null
     */
    protected static $_tutorialStep = null;
    
    /**
     * Modal Size: false for "md", null for normal, true for "xl"
     *
     * @var boolean|null
     */
    protected static $_modalSize = null;
    
    /**
     * Wrap the final result
     * 
     * @param mixed  $result      AJAX method result to return
     * @param int    $resCityId   (optional) City ID - used for resources; default <b>null</b>
     * @param int    $navIdValue  (optional) DB identifier value - used for navigation; default <b>null</b>
     * @param string $navIdColumn (optional) DB column to compare against - used for navigation; default <b>Stephino_Rpg_Db_Table_Cities::COL_ID</b>
     * @return array Associative array of AJAX result
     */
    public static function wrap($result, $resCityId = null, $navIdValue = null, $navIdColumn = Stephino_Rpg_Db_Table_Cities::COL_ID) {
        // Prepare the final result
        $wrappedResult = array(
            self::RESULT_SETTINGS      => self::getSettings(),
            self::RESULT_RESOURCES     => self::getResources($resCityId),
            self::RESULT_ENTITIES      => self::getEntities($resCityId),
            self::RESULT_MODAL_SIZE    => self::getModalSize(),
            self::RESULT_PREMIUM       => self::getQueues(null, true),
            self::RESULT_QUEUES        => self::getQueues($resCityId),
            self::RESULT_MESSAGES      => self::_getMessages(),
            self::RESULT_CONVOYS       => self::_getConvoys(),
            self::RESULT_TUTORIAL      => self::_getTutorial(),
            self::RESULT_NAVIGATION    => self::_getNavigation($navIdValue, $navIdColumn),
        );
        
        // City-level information
        if (null !== $resCityId) {
            $wrappedResult[self::RESULT_BUILDING_UPGS] = Stephino_Rpg_Renderer_Ajax_Action::getBuildingUpgs($resCityId);
        }
        
        // Append our details
        if (is_array($result)) {
            // Cell AJAX request
            if (isset($result[Stephino_Rpg_Renderer_Ajax_Cells::RESULT_GRID])) {
                $wrappedResult[self::RESULT_ANNOUNCEMENT] = self::_getAnnouncement();
                $wrappedResult[self::RESULT_CHANGELOG]    = self::_getChangelog();
            }
            foreach ($result as $key => $value) {
                $wrappedResult[$key] = $value;
            }
        } else {
            if (null !== $result) {
                $wrappedResult[self::RESULT_DATA] = $result;
            }
        }
        
        return $wrappedResult;
    }
    
    /**
     * Get the tutorial step information
     * 
     * @return array|null Current tutorial level information or null on error
     */
    protected static function _getTutorial() {
        // Prepare the result
        $result = null;
        
        // Prepare the new level
        if (null !== self::$_tutorialStep) {
            $newLevel = self::$_tutorialStep;
        } else {
            // Valid time-lapse info available
            if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
                // Get the first row
                $dbRow = current(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData());

                // Get the tutorial level
                $tutorialLevel = intval($dbRow[Stephino_Rpg_Db_Table_Users::COL_USER_TUTORIAL_LEVEL]);

                // Get the checkpoints
                $tutorialCheckpoints = array();
                foreach(Stephino_Rpg_Config::get()->tutorials()->getAll() as $tutorialObject) {
                    if ($tutorialObject->getTutorialIsCheckPoint() && $tutorialObject->getId() <= $tutorialLevel) {
                        $tutorialCheckpoints[] = $tutorialObject->getId();
                    }
                }

                // Get the step after the latest checkpoint
                $newLevel = count($tutorialCheckpoints) ? max($tutorialCheckpoints) + 1 : 1;
            }
        }
        
        // Get the configuration object
        if (null !== $newLevel && null != $tutorialObject = Stephino_Rpg_Config::get()->tutorials()->getById($newLevel)) {
            // Store the current tutorial info
            $result = $tutorialObject->toArray();
            
            // MarkDown enabled on tutorial descriptions
            $result['description'] = Stephino_Rpg_Utils_Lingo::markdown($result['description']);
            
            // Get the total number of steps
            $result[self::RESULT_TUT_TOTAL] = count(Stephino_Rpg_Config::get()->tutorials()->getAll());
        }
        
        return $result;
    }
    
    /**
     * Get the intermediate tutorial step - used to trigger actions between checkpoints
     * 
     * @return int|null Tutorial Step
     */
    public static function getTutorialStep() {
        return self::$_tutorialStep;
    }
    
    /**
     * Set an intermediate tutorial step - used to trigger actions between checkpoints
     * 
     * @param int|null $tutorialStep Tutorial Step
     */
    public static function setTutorialStep($tutorialStep) {
        self::$_tutorialStep = (null === $tutorialStep ? null : intval($tutorialStep));
    }
    
    /**
     * Get the dialog size<ul>
     *     <li><b>false</b> for "md"</li>
     *     <li><b>null</b> for normal</li>
     *     <li><b>true</b> for "xl"</li>
     * </ul>
     * 
     * @return boolean|null
     */
    public static function getModalSize() {
        return self::$_modalSize;
    }
    
    /**
     * Set the modal size<ul>
     *     <li><b>self::MODAL_SIZE_SMALL</b> for "md"</li>
     *     <li><b>self::MODAL_SIZE_NORMAL</b> for normal</li>
     *     <li><b>self::MODAL_SIZE_LARGE</b> for "xl"</li>
     * </ul>
     * 
     * @param boolean|null $modalSize Dialog size flag
     */
    public static function setModalSize($modalSize = null) {
        self::$_modalSize = (null === $modalSize ? null : (boolean) $modalSize);
    }
    
    /**
     * Get the resources array for a given city
     * 
     * @param int $cityId (optional) City ID; default <b>null</b>
     * @return array Resources array
     */
    public static function getResources($cityId = null) {
        // Prepare the result
        $result = array();
        
        // Go through the data
        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $dbRow) {
                // Negative values not allowed
                foreach ($dbRow as &$dbRowValue) {
                    if (!is_string($dbRowValue) && $dbRowValue < 0) {
                        $dbRowValue = 0;
                    }
                }
                
                // Top-level info
                if (null === $cityId) {
                    $result = array(
                        self::RESULT_RES_GOLD => array(
                            $dbRow[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD],
                            Stephino_Rpg_Config::get()->core()->getResourceGoldName(),
                        ),
                        self::RESULT_RES_RESEARCH => array(
                            $dbRow[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH],
                            Stephino_Rpg_Config::get()->core()->getResourceResearchName(),
                        ),
                        self::RESULT_RES_GEM => array(
                            $dbRow[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM],
                            Stephino_Rpg_Config::get()->core()->getResourceGemName(),
                        ),
                    );
                    break;
                }
                
                // Detailed city resources
                if ($dbRow[Stephino_Rpg_Db_Table_Cities::COL_ID] == $cityId) {
                    // Get the max storage
                    $maxStorage = $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_STORAGE];
                    
                    // Prepare the final result
                    $result = array(
                        self::RESULT_RES_GOLD => array(
                            $dbRow[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD],
                            Stephino_Rpg_Config::get()->core()->getResourceGoldName(),
                        ),
                        self::RESULT_RES_RESEARCH => array(
                            $dbRow[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH],
                            Stephino_Rpg_Config::get()->core()->getResourceResearchName(),
                        ),
                        self::RESULT_RES_GEM => array(
                            $dbRow[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM],
                            Stephino_Rpg_Config::get()->core()->getResourceGemName(),
                        ),
                        self::RESULT_RES_ALPHA => array(
                            $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA],
                            Stephino_Rpg_Config::get()->core()->getResourceAlphaName(),
                            $maxStorage,
                        ),
                        self::RESULT_RES_BETA => array(
                            $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_BETA],
                            Stephino_Rpg_Config::get()->core()->getResourceBetaName(),
                            $maxStorage,
                        ),
                        self::RESULT_RES_GAMMA => array(
                            $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_GAMMA],
                            Stephino_Rpg_Config::get()->core()->getResourceGammaName(),
                            $maxStorage,
                        ),
                        self::RESULT_RES_EXTRA_1 => array(
                            $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1],
                            Stephino_Rpg_Config::get()->core()->getResourceExtra1Name(),
                            $maxStorage,
                        ),
                        self::RESULT_RES_EXTRA_2 => array(
                            $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2],
                            Stephino_Rpg_Config::get()->core()->getResourceExtra2Name(),
                            $maxStorage,
                        ),
                    );
                    break;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Get all entities in this city
     * 
     * @param int $cityId (optional) City ID; default <b>null</b>
     * @return int|null Total entities count in this city or null if no city ID provided
     */
    public static function getEntities($cityId = null) {
        $result = null;
        
        if (null !== $cityId) {
            $result = 0;
            if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_Entities::KEY)->getData())) {
                foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_Entities::KEY)->getData() as $entityRow) {
                    if ($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CITY_ID] == $cityId) {
                        // Get the entity configuration
                        $entityConfig = Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT == $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE]
                            ? Stephino_Rpg_Config::get()->units()->getById($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID])
                            : Stephino_Rpg_Config::get()->ships()->getById($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]);
                        
                        // Only count valid entity configs
                        if (null !== $entityConfig) {
                            $result += intval($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT]);
                        }
                    }
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Get the navigation information
     * 
     * @param int    $idValue  (optional) DB identifier value; default <b>null</b>
     * @param string $idColumn (optional) DB column to compare against; default <b>Stephino_Rpg_Db_Table_Cities::COL_ID</b>
     * @return array
     */
    protected static function _getNavigation($idValue = null, $idColumn = Stephino_Rpg_Db_Table_Cities::COL_ID) {
        // Prepare the result
        $result = array(
            self::RESULT_NAV_CITIES => array(),
            self::RESULT_NAV_ISLANDS => array(),
        );
        
        // Valid info stored for this user
        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $dbRow) {
                // Get the city ID
                $cityId = $dbRow[Stephino_Rpg_Db_Table_Cities::COL_ID];
                
                // Append to the list
                if (!isset($result[self::RESULT_NAV_CITIES][$cityId])) {
                    $result[self::RESULT_NAV_CITIES][$cityId] = array(
                        self::RESULT_NAV_NAME    => $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_NAME],
                        self::RESULT_NAV_ICON    => $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_CONFIG_ID],
                        self::RESULT_NAV_LEVEL   => Stephino_Rpg_Utils_Media::getClosestBackgroundId(
                            Stephino_Rpg_Config_Cities::KEY,
                            $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_CONFIG_ID], 
                            $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL]
                        ),
                        self::RESULT_NAV_CAPITAL => 1 == $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_IS_CAPITAL],
                        self::RESULT_NAV_CURRENT => ($idColumn === Stephino_Rpg_Db_Table_Cities::COL_ID && $idValue == $cityId),
                    );
                }
                
                // Get the island ID
                $islandId = $dbRow[Stephino_Rpg_Db_Table_Islands::COL_ID];
                
                // Append to the list
                if (!isset($result[self::RESULT_NAV_ISLANDS][$islandId])) {
                    $result[self::RESULT_NAV_ISLANDS][$islandId] = array(
                        self::RESULT_NAV_NAME          => $dbRow[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_NAME],
                        self::RESULT_NAV_ICON          => $dbRow[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_CONFIG_ID],
                        self::RESULT_NAV_CURRENT       => ($idColumn === Stephino_Rpg_Db_Table_Islands::COL_ID && $idValue == $islandId),
                        self::RESULT_NAV_ISLAND_COORDS => Stephino_Rpg_Utils_Math::getSnakePoint($islandId),
                    );
                }
                
                // Mark the current island based on the current city
                $result[self::RESULT_NAV_ISLANDS][$islandId][self::RESULT_NAV_CURRENT] = 
                    $result[self::RESULT_NAV_ISLANDS][$islandId][self::RESULT_NAV_CURRENT] 
                    || ($idColumn === Stephino_Rpg_Db_Table_Cities::COL_ID && $idValue == $cityId);
            }
        }
        
        return $result;
    }
    
    /**
     * Is there an announcement ready for this user?
     * 
     * @return boolean An announcement is available for this user
     */
    protected static function _getAnnouncement() {
        // Get the announcement
        $result = Stephino_Rpg_Db::get()->modelAnnouncement()->get();
        
        do {
            // Valid result that has not expired
            if (is_array($result) && $result[3] >= 0) {
                // Authenticated user
                if (Stephino_Rpg_TimeLapse::get()->userId()) {
                    // Have not read this announcement yet
                    if ($result[0] != Stephino_Rpg_Cache_User::getInstance()->getValue(Stephino_Rpg_Cache_User::KEY_ANN_READ)) {
                        break;
                    }
                }
            }
            
            // Something went wrong
            $result = null;
        } while(false);
        
        return is_array($result);
    }
    
    /**
     * Is there a "game updated" message ready for this user?
     * 
     * @return boolean A "game updated" message is ready for this user
     */
    protected static function _getChangelog() {
        return Stephino_Rpg_Config::get()->core()->getShowAbout() 
            && Stephino_Rpg::PLUGIN_VERSION !== Stephino_Rpg_Cache_User::getInstance()->getValue(Stephino_Rpg_Cache_User::KEY_CHL_READ);
    }
    
    /**
     * Get the list of unread messages. <br/>
     * Messages may be created by time-lapse Queues and Convoys (without knowing their multi-insert IDs) so they need to be fetched last.
     * 
     * @return array
     */
    protected static function _getMessages() {
        return Stephino_Rpg_Db::get()->tableMessages()->getUnread(
            Stephino_Rpg_TimeLapse::get()->userId()
        );
    }
    
    /**
     * Get the number of active convoys from the time-lapse
     * 
     * @return int
     */
    protected static function _getConvoys() {
        $result = 0;
        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Convoys::KEY)->getData())) {
            $result = count(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Convoys::KEY)->getData());
        }
        return $result;
    }
    
    /**
     * Get the number of active queues
     * 
     * @param int     $cityId   (optional) Get queues specific to this city; default <b>null</b>
     * @param boolean $premium  (optional) Get the premium packages only; otherwise, get everything except premium packages; default <b>false</b>
     * @param boolean $getCount (optional) Get queue count instead of an array; default <b>true</b>
     * @return int|array|null Null if city ID not provided for non-premium queues, int - number of queues, array - list of queue rows otherwise 
     */
    public static function getQueues($cityId = null, $premium = false, $getCount = true) {
        // Get the city ID
        $cityId = intval($cityId);
        
        // No city ID provided and not in premium mode, don't update the counter
        if (0 == $cityId && !$premium) {
            return null;
        }
        
        // Prepare the result
        $result = array();
        
        // Go through the queues
        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Queues::KEY)->getData())) {
            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Queues::KEY)->getData() as $queueRow) {
                $itemTypePremium = Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_PREMIUM == $queueRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_TYPE];
                $itemTypeResearch = Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_RESEARCH == $queueRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_TYPE];
                if (
                    (($premium && $itemTypePremium) || (!$premium && !$itemTypePremium)) 
                    && ($itemTypeResearch || $cityId == $queueRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_CITY_ID])
                ) {
                    $result[] = $queueRow;
                }
            }
        }
        
        return $getCount ? count($result) : $result;
    }
    
    /**
     * Get the current user settings
     * 
     * @return array
     */
    public static function getSettings() {
        // Prepare the result
        $result = array(
            Stephino_Rpg_Cache_User::KEY_VOL_MUSIC  => 100,
            Stephino_Rpg_Cache_User::KEY_VOL_BKG    => 100,
            Stephino_Rpg_Cache_User::KEY_VOL_CELLS  => 100,
            Stephino_Rpg_Cache_User::KEY_VOL_EVENTS => 100,
        );
        
        // Get the current user data
        $userCache = Stephino_Rpg_Cache_User::getInstance()->getData();
        foreach ($userCache as $cacheKey => $cacheValue) {
            if (isset($result[$cacheKey])) {
                $result[$cacheKey] = intval($cacheValue);
            }
        }
        
        // Store the timelapse cooldown
        $result[self::RESULT_SET_TLCD] = Stephino_Rpg_Config::get()->core()->getTimeLapseCooldown();
        
        return $result;
    }
}

/*EOF*/