<?php
/**
 * Stephino_Rpg_TimeLapse
 * 
 * @title      Time-Lapse
 * @desc       Calculate queues, convoys and resources for the given user
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_TimeLapse {

    // Message templates
    const TEMPLATE_DIPLOMACY               = 'timelapse-diplomacy';
    const TEMPLATE_ECONOMY                 = 'timelapse-economy';
    const TEMPLATE_MILITARY                = 'timelapse-military';
    const TEMPLATE_RESEARCH                = 'timelapse-research';
    const TEMPLATE_COMMON_LIST_ENTITIES    = 'common/timelapse-list-entities';
    const TEMPLATE_COMMON_LIST_RESOURCES   = 'common/timelapse-list-resources';
    const TEMPLATE_NOTIF_PREMIUM_PACKAGE   = 'notification/notif-premium-package';
    const TEMPLATE_NOTIF_PREMIUM_MODIFIER  = 'notification/notif-premium-modifier';
    const TEMPLATE_NOTIF_TUTORIAL_REWARDS  = 'notification/notif-tutorial-rewards';
    const TEMPLATE_NOTIF_PTF_AUTHOR_REWARD = 'notification/notif-ptf-author-reward';
    
    /**
     * DataBase object
     *
     * @var Stephino_Rpg_Db 
     */
    private $_db = null;
    
    /**
     * Forced recalculation
     *
     * @var boolean
     */
    protected $_isForced = false;
    
    /**
     * User Data
     * 
     * @var array
     */
    protected $_userData = null;
    
    /**
     * Workers
     *
     * @var Stephino_Rpg_TimeLapse_Abstract[] 
     */
    protected $_workers = array();
    
    /**
     * Other time-lapse threads to be executed after the Run task
     *
     * @var array[]
     */
    protected $_threads = array();
    
    /**
     * Singleton instances of Stephino_Rpg_TimeLapse
     *
     * @var Stephino_Rpg_TimeLapse[] 
     */
    protected static $_instances = array();
    
    /**
     * Current workspace instance key
     *
     * @var string
     */
    protected static $_workspaceKey = null;
    
    /**
     * Get a time-lapse template path
     * 
     * @param string $templateName Time-lapse template name
     * @return string|null
     */
    public static function getTemplatePath($templateName) {
        if (!is_file($templatePath = STEPHINO_RPG_ROOT . '/ui/tpl/timelapse/' . $templateName . '.php')) {
            throw new Exception('Time-lapse template "' . $templateName . '" not found');
        }
        return $templatePath;
    }
    
    /**
     * Set the current workspace
     * 
     * @param int     $wpUserId (optional) WordPress User ID for the tables interactions; default <b>null</b>, auto-populated with get_current_user_id()
     * @param int     $robotId  (optional) The direct Game User ID belongs to a robot (Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID is null); default <b>null</b>, meaning a human account
     * @param boolean $forced   (optional) Forced recalculation; default <b>false</b>
     */
    public static function setWorkspace($wpUserId = null, $robotId = null, $forced = false) {
        // Set the workspace key
        self::$_workspaceKey = (null === $wpUserId ? 'n' : $wpUserId) . ',' . (null === $robotId ? 'n' : $robotId) . ',' . ($forced ? 'f' : 'n');
    }
    
    /**
     * Get the current workspace key
     * 
     * @return string
     */
    public static function getWorkspaceKey() {
        if (null === self::$_workspaceKey) {
            self::setWorkspace();
        }
        return self::$_workspaceKey;
    }
    
    /**
     * Get the current workspace configuration
     * 
     * @return array Array of <ul>
     * <li>(int|null) WordPress User ID</li>
     * <li>(int|null) Robot ID</li>
     * <li>(boolean) Forced timelapse</li>
     * </ul>
     */
    public static function getWorkspace() {
        // Get the components
        list($wpUserId, $robotId, $forced) = explode(',', self::getWorkspaceKey());
        return array(
            'n' == $wpUserId ? null : intval($wpUserId), 
            'n' == $robotId ? null : intval($robotId), 
            'f' == $forced
        );
    }
    
    /**
     * Get a time-lapse instance
     * 
     * @param boolean $reInit (optional) Re-initialize the user data and workers; default <b>false</b>
     * @return Stephino_Rpg_TimeLapse
     */
    public static function get($reInit = false) {
        // Prepare the key
        $key = self::getWorkspaceKey();
        
        // Get the value
        if (!isset(self::$_instances[$key])) {
            // Get the components
            list($wpUserId, $robotId, $forced) = self::getWorkspace();
            
            // Create the workspace
            self::$_instances[$key] = new self($wpUserId, $robotId, $forced);
        }
        
        // Re-initialize
        if ($reInit) {
            self::$_instances[$key]->_init();
        }
        
        return self::$_instances[$key];
    }
    
    /**
     * Time-Lapse instance
     * 
     * @param int     $wpUserId (optional) WordPress User ID for the tables interactions; default <b>null</b>, auto-populated with get_current_user_id()
     * @param int     $robotId  (optional) The direct Game User ID belongs to a robot (Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID is null); default <b>null</b>, meaning a human account
     * @param boolean $forced   (optional) Forced recalculation; default <b>false</b>
     */
    protected function __construct($wpUserId = null, $robotId = null, $forced = false) {
        // Store the details
        $this->_isForced = $forced;
        
        // Prepare the DataBase object - Referenced by all model/table/timelapse methods
        $this->_db = Stephino_Rpg_Db::get($robotId, $wpUserId);
        
        // Initialize the user data and workers
        $this->_init();
    }
    
    /**
     * Initialize the user data and workers
     */
    protected function _init() {
        // Prepare the user data
        $this->_userData = $this->db()->tableUsers()->getData();
        
        // Prepare the workers
        if (is_array($this->_userData)) {
            $this->_workers = array(
                // Resources generated by Buildings; updates Users, Cities, Buildings
                Stephino_Rpg_TimeLapse_Resources::KEY              => new Stephino_Rpg_TimeLapse_Resources($this->db(), $this->_userData[Stephino_Rpg_Db_Table_Users::COL_ID]),
                // Construction/Recruitment Queues; updates Queues (removing rows on complete); adds Messages
                Stephino_Rpg_TimeLapse_Queues::KEY                 => new Stephino_Rpg_TimeLapse_Queues($this->db(), $this->_userData[Stephino_Rpg_Db_Table_Users::COL_ID]),
                // Convoys; updates Convoys (removing rows on complete); adds Messages
                Stephino_Rpg_TimeLapse_Convoys::KEY                => new Stephino_Rpg_TimeLapse_Convoys($this->db(), $this->_userData[Stephino_Rpg_Db_Table_Users::COL_ID]),
                // Support - Research Fields; updates ResearchFields
                Stephino_Rpg_TimeLapse_Support_ResearchFields::KEY => new Stephino_Rpg_TimeLapse_Support_ResearchFields($this->db(), $this->_userData[Stephino_Rpg_Db_Table_Users::COL_ID]),
                // Support - Entities; updates Entities
                Stephino_Rpg_TimeLapse_Support_Entities::KEY       => new Stephino_Rpg_TimeLapse_Support_Entities($this->db(), $this->_userData[Stephino_Rpg_Db_Table_Users::COL_ID]),
            );
        }
    }
    
    /**
     * Get the current Game User ID
     * 
     * @return int|null
     */
    public function userId() {
        return is_array($this->_userData) ? (int) $this->_userData[Stephino_Rpg_Db_Table_Users::COL_ID] : null;
    }
    
    /**
     * Get the current Game User data
     * 
     * @return array|null
     */
    public function userData() {
        return is_array($this->_userData) ? $this->_userData : null;
    }
    
    /**
     * Get the DataBase object
     * 
     * @return Stephino_Rpg_Db
     */
    public function db() {
        return $this->_db;
    }
    
    /**
     * Get a worker by name
     * 
     * @param string $workerName Worker name
     * @return Stephino_Rpg_TimeLapse_Abstract|null
     */
    public function worker($workerName) {
        if (isset($this->_workers[$workerName])) {
            return $this->_workers[$workerName];
        }
        return null;
    }
    
    /**
     * Register another time-lapse thread
     * 
     * @param int|null $wpUserId (optional) WordPress User ID for the tables interactions; default <b>null</b>, auto-populated with get_current_user_id()
     * @param int|null $robotId  (optional) The direct Game User ID belongs to a robot (Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID is null); default <b>null</b>, meaning a human account
     * @param boolean  $forced   (optional) Forced recalculation; default <b>false</b>
     * @return Stephino_Rpg_TimeLapse
     */
    public function registerThread($wpUserId = null, $robotId = null, $forced = false) {
        // Avoid infinite loops
        if ((
                null !== $wpUserId 
                    ? $wpUserId != $this->db()->getWpUserId()
                    : $robotId != $this->db()->getRobotId()
            )
            && $forced != $this->_isForced
        ) {
            // Add the thread
            $this->_threads[] = array($wpUserId, $robotId, $forced);
        }
        
        return $this;
    }
    
    /**
     * Run the available tools
     * 
     * @param boolean $ajaxOrigin   (optional) The time-lapse procedure was initiated by an AJAX call; default <b>false</b>
     * @param boolean $dialogOrigin (optional) The time-lapse procedure was initiated by an AJAX Dialog call; default <b>false</b>
     */
    public function run($ajaxOrigin = false, $dialogOrigin = false) {
        // No workers - a bad User ID was provided
        if (!count($this->_workers)) {
            return;
        }
        
        // Prepare the current tick
        $currentTick = time();
        
        // Are we allowed to run?
        if ($this->_isForced || ($currentTick - $this->_userData[Stephino_Rpg_Db_Table_Users::COL_USER_LAST_TICK]) >= Stephino_Rpg_Config::get()->core()->getTimeLapseCooldown()) {
            // Get the user ID
            $userId = $this->_userData[Stephino_Rpg_Db_Table_Users::COL_ID];
            
            // Prepare the checkpoint data
            $checkPointList = Stephino_Rpg_TimeLapse_Abstract::getCheckpoints($userId, $currentTick);
            
            // Go through the checkpoints
            foreach ($checkPointList as $checkPointKey => $checkPointData) {
                // Get the events
                list($checkPointTime, $checkPointDelta) = $checkPointData;
                
                // Go through the workers
                foreach ($this->_workers as $worker) {
                    $logWorkerStart = microtime(true);
                    
                    // Perform the step
                    $worker->step($checkPointTime, $checkPointDelta);
                    
                    // Log the result
                    Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug(
                        'TL-CP U' . $userId . ' (' . ($checkPointKey + 1) . '/' . count($checkPointList) . '): ' 
                        . get_class($worker) . ' (' . $checkPointTime . ', ' . $checkPointDelta . ') in ' 
                        . (microtime(true) - $logWorkerStart)
                    );
                }
            }
            
            // Update the last tick
            self::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->updateRef(
                Stephino_Rpg_Db_Table_Users::COL_ID, 
                $userId, 
                Stephino_Rpg_Db_Table_Users::COL_USER_LAST_TICK, 
                $currentTick
            );
            
            // Update the last tick generated by AJAX
            $ajaxOrigin && self::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->updateRef(
                Stephino_Rpg_Db_Table_Users::COL_ID, 
                $userId, 
                Stephino_Rpg_Db_Table_Users::COL_USER_LAST_TICK_AJAX, 
                $currentTick
            );
            
            // Dialogs are read-only
            if (!$dialogOrigin) {
                // Perform robot actions
                list($wpUserId, $robotId, $forced) = self::getWorkspace();
                null !== $robotId && Stephino_Rpg_Task_Robot::get()->run();

                // Save the changed rows back to the DB
                foreach ($this->_workers as $worker) {
                    $logWorkerStart = microtime(true);

                    // DB Update
                    $worker->save();

                    // Log the result
                    Stephino_Rpg_Log::check() && Stephino_Rpg_Log::info('TL-SV: ' . get_class($worker) . ' in ' . (microtime(true) - $logWorkerStart));
                }

                // New threads were registered
                if (count($this->_threads)) {
                    // Go through the other threads
                    foreach ($this->_threads as $otherWorkspace) {
                        // Validate the new workspace format
                        if (!is_array($otherWorkspace) || 3 != count($otherWorkspace)) {
                            continue;
                        }

                        // Get the new workspace arguments
                        list($otherWpUserId, $otherRobotId, $otherForced) = $otherWorkspace;

                        // Same as the current workspace, avoid an infinite loop
                        if ($otherWpUserId == $wpUserId && $otherRobotId == $robotId && $otherForced == $forced) {
                            continue;
                        }

                        // Set up the new workspace
                        self::setWorkspace($otherWpUserId, $otherRobotId, $otherForced);

                        // Run the tasks for this different user
                        self::get()->run();
                    }

                    // Set the current workspace back
                    self::setWorkspace($wpUserId, $robotId, $forced);
                }
            }
        }
    }
}

/*EOF*/