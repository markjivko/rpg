<?php

/**
 * Stephino_Rpg_Task_Robot
 * 
 * @title      Robot
 * @desc       Perform automatic queue/military actions
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Task_Robot {
    
    /**
     * Singleton instance
     * 
     * @var Stephino_Rpg_Task_Robot
     */
    protected static $_instance = null;
    
    /**
     * DataBase instance
     *
     * @var Stephino_Rpg_Db 
     */
    protected $_db = null;
    
    /**
     * Store the log tag
     * 
     * @var string
     */
    protected $_logTag = '<Robot>';
    
    /**
     * Constructor
     */
    protected function __construct() {}
    
    /**
     * Get a Singleton instance of Robot Tasks
     * 
     * @return Stephino_Rpg_Task_Robot
     */
    public static function get() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * Run the queue/military actions
     */
    public function run() {
        do {
            // User not ready
            if (!is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
                break;
            }
            
            // Get the current user information
            list($wpUserId, $robotId) = Stephino_Rpg_TimeLapse::getWorkspace();

            // Ignore human players
            if (null === $robotId) {
                break;
            }
            
            // Store the credentials
            $this->_db = Stephino_Rpg_Db::get($robotId, $wpUserId);
            $this->_logTag = '<Robot #' . $robotId . '> ';
            
            // Fervor
            if (mt_rand(1, 100) > Stephino_Rpg_Config::get()->core()->getRobotsFervor()) {
                Stephino_Rpg_Log::info("{$this->_logTag} Zzz");
                break;
            }
            
            // Queue Research Fields and Buildings
            $this->_queueAdvisor();
            
            // Assign Workers to Buildings
            $this->_queueWorkers();
        } while(false);
    }
    
    /**
     * Queues research fields and buildings according to the advisor<br/>
     * A 50% chance of upgrading a random building
     */
    protected function _queueAdvisor() {
        // Get user data (first city)
        $cityData = current(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData());
        
        // Re-initialize robots
        if ($cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL] <= 2 && 0 == $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1]) {
            // Get the city config
            $cityConfig = Stephino_Rpg_Config::get()->cities()->getById($cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_CONFIG_ID]);
            if (null !== $cityConfig) {
                // Maximum storage
                $maxStorage = Stephino_Rpg_Utils_Config::getPolyValue(
                    $cityConfig->getMaxStoragePolynomial(), 
                    abs((int) $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL]), 
                    $cityConfig->getMaxStorage()
                );

                // Robot account: everything to 100%
                foreach (array(
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA,
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_BETA,
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_GAMMA,
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1,
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2,
                ) as $resourceKey) {
                    Stephino_Rpg_TimeLapse::get()
                        ->worker(Stephino_Rpg_TimeLapse_Resources::KEY)
                        ->updateRef(
                            Stephino_Rpg_Db_Table_Cities::COL_ID, 
                            $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID], 
                            $resourceKey, 
                            $maxStorage
                        );
                }

                // Get the info
                $cityData = current(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData());
            }
        }
        
        // Prepare building/research queues
        $queuedBuildings = array();
        $queuedResearchFields = array();
        foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Queues::KEY)->getData() as $queueData) {
            if (Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_BUILDING == $queueData[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_TYPE]
                && $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID] == $queueData[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_CITY_ID]) {
                $queuedBuildings[] = $queueData[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID];
            }
            if (Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_RESEARCH == $queueData[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_TYPE]) {
                $queuedResearchFields[] = $queueData[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID];
            }
        }
        
        // Get the next item to unlock with a 50% probability
        $unlockNext = mt_rand(1, 100) <= 50 
            ? Stephino_Rpg_Renderer_Ajax_Action::getUnlockNext($cityData[Stephino_Rpg_Db_Table_Cities::COL_ID])
            : null;

        if (null != $unlockNext) {
            list($unlockObject, $unlockCurrentLevel, $unlockTargetLevel, $unlockQueued) = $unlockNext;
            if (!$unlockQueued) {
                // Prepare the cost data
                $costData = Stephino_Rpg_Renderer_Ajax_Action::getCostData(
                    $unlockObject,
                    $unlockCurrentLevel,
                    true
                );
            
                try {
                    // Building queue
                    if ($unlockObject instanceof Stephino_Rpg_Config_Building 
                        && count($queuedBuildings) < Stephino_Rpg_Config::get()->core()->getMaxQueueBuildings()) {
                        // Try the upgrade
                        $this->_spend($costData, $cityData);
                        
                        // Queue the building
                        Stephino_Rpg_Db::get()->modelQueues()->queueBuilding(
                            $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID],
                            $unlockObject->getId()
                        );
                        
                        Stephino_Rpg_Log::info("{$this->_logTag} Queued building: {$unlockObject->getName()}");
                    } 
                    
                    // Research fields queue
                    if($unlockObject instanceof Stephino_Rpg_Config_ResearchField 
                        && count($queuedResearchFields) < Stephino_Rpg_Config::get()->core()->getMaxQueueResearchFields()) {
                        // Try the upgrade
                        $this->_spend($costData, $cityData);
                        
                        // Queue the research field
                        Stephino_Rpg_Db::get()->modelQueues()->queueResearchField(
                            $cityData[Stephino_Rpg_Db_Table_Users::COL_ID],
                            $unlockObject->getId()
                        );
                        Stephino_Rpg_Log::info("{$this->_logTag} Queued research field: {$unlockObject->getName()}");
                    }
                } catch (Exception $exc) {}
            }
        } else {
            if (count($queuedBuildings) < Stephino_Rpg_Config::get()->core()->getMaxQueueBuildings()) {
                // Get a random building to upgrade
                $buildingAvailable = array();
                foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $buildingData) {
                    if ($cityData[Stephino_Rpg_Db_Table_Cities::COL_ID] == $buildingData[Stephino_Rpg_Db_Table_Cities::COL_ID]
                        && !in_array($buildingData[Stephino_Rpg_Db_Table_Buildings::COL_ID], $queuedBuildings)) {
                        $buildingAvailable[] = array(
                            (int) $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID],
                            (int) $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL]
                        );
                    }
                }
                shuffle($buildingAvailable);

                try {
                    list($buildingConfigId, $buildingLevel) = current($buildingAvailable);
                    if (null !== $buildingConfig = Stephino_Rpg_Config::get()->buildings()->getById($buildingConfigId)) {
                        // Prepare the cost data
                        $costData = Stephino_Rpg_Renderer_Ajax_Action::getCostData(
                            $buildingConfig->getId(),
                            $buildingLevel,
                            true
                        );

                        // Allocate resources
                        $this->_spend($costData, $cityData);

                        // Queue the building
                        Stephino_Rpg_Db::get()->modelQueues()->queueBuilding(
                            $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID],
                            $buildingConfig->getId()
                        );
                        Stephino_Rpg_Log::info("{$this->_logTag} Queued random building: {$buildingConfig->getName()}");
                    }
                } catch (Exception $exc) {}
            }
        }
    }
    
    /**
     * Assign workers to buildings
     */
    protected function _queueWorkers() {
        // Prepare the workers
        $workers = array();
        $workersUsed = 0;
        foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $buildingData) {
            if ($buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL] > 0) {
                $buildingConfig = Stephino_Rpg_Config::get()->buildings()->getById(
                    $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID]
                );
                if (null !== $buildingConfig && $buildingConfig->getUseWorkers()) {
                    // Store the workers used
                    $workersUsed += (int) $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_WORKERS];
                    
                    // Get the maximum workers
                    $buildingWorkersMax = Stephino_Rpg_Utils_Config::getPolyValue(
                        $buildingConfig->getWorkersCapacityPolynomial(),
                        $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL], 
                        $buildingConfig->getWorkersCapacity()
                    );
                    
                    // Store the building that needs workers
                    if ($buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_WORKERS] < $buildingWorkersMax) {
                        $workers[] = array(
                            (int) $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_ID],
                            (int) $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_WORKERS],
                            $buildingWorkersMax
                        );
                    }
                }
            }
        }
        
        // Some buildings require extra workers
        if (count($workers)) {
            shuffle($workers);

            // Get user data (first city)
            $cityData = current(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData());

            // Get the available workers
            $workersAvailable = floor($cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION]) - $workersUsed;
            
            // Go through the buildings
            foreach ($workers as list($buildingId, $buildingWorkers, $buildingWorkersMax)) {
                if ($workersAvailable <= 0) {
                    break;
                }
                
                // Assign the workers
                if ($workersAvailable >= $buildingWorkersMax - $buildingWorkers) {
                    $workersAvailable -= ($buildingWorkersMax - $buildingWorkers);
                    $buildingWorkers = $buildingWorkersMax;
                } else {
                    $buildingWorkers += $workersAvailable;
                    $workersAvailable = 0;
                }
                
                // Update the time-lapse references (for the wrap method to work)
                Stephino_Rpg_TimeLapse::get()
                    ->worker(Stephino_Rpg_TimeLapse_Resources::KEY)
                    ->updateRef(
                        Stephino_Rpg_Db_Table_Buildings::COL_ID, 
                        $buildingId, 
                        Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_WORKERS, 
                        $buildingWorkers
                    );
                Stephino_Rpg_Log::info("{$this->_logTag} Assigned {$buildingWorkers} / {$buildingWorkersMax} workers to building #{$buildingId}");
            }
        }
    }
    
    /**
     * Try to allocate resources for an upgrade
     * 
     * @param array $resourceData Resource data
     * @param array $cityData     City Data
     * @param int   $multiplier   (optional) Multiplier; default <b>1</b>
     * @throws Exception
     */
    protected function _spend($resourceData, $cityData, $multiplier = 1) {
        // Store the city ID
        $cityDataId = $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID];
        $userDataId = $cityData[Stephino_Rpg_Db_Table_Users::COL_ID];
        
        // Go through the costs
        foreach ($resourceData as $costKey => $costInfo) {
            // Invalid cost information
            if (!is_array($costInfo) || count($costInfo) < 2) {
                continue;
            }
            
            // Get the cost name and value
            list($costName, $costValue) = $costInfo;
            
            // Sanitize the cost
            $costValue = floatval($costValue);
            
            // No cost or invalid key
            if ($costValue <= 0 || !isset($cityData[$costKey])) {
                continue;
            }
            
            // Get the current ballance
            $costBallance = floatval($cityData[$costKey]);
            
            // Cost multiplier
            if (1 != $multiplier) {
                $costValue *= $multiplier;
            }
            
            // We cannot afford this
            if ($costValue > $costBallance) {
                throw new Exception(
                    sprintf(
                        __('More %s needed', 'stephino-rpg'),
                        $costName
                    )
                );
            }
            
            // Go through the known keys
            switch($costKey) {
                case Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD:
                case Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH:
                case Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM:
                    // Update the time-lapse references (for the wrap method to work)
                    Stephino_Rpg_TimeLapse::get()
                        ->worker(Stephino_Rpg_TimeLapse_Resources::KEY)
                        ->updateRef(
                            Stephino_Rpg_Db_Table_Users::COL_ID, 
                            $userDataId, 
                            $costKey, 
                            round($costBallance - $costValue, 4)
                        );
                    break;
                
                case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA:
                case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_BETA:
                case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_GAMMA:
                case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1:
                case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2:
                    // Update the time-lapse references (for the wrap method to work)
                    Stephino_Rpg_TimeLapse::get()
                        ->worker(Stephino_Rpg_TimeLapse_Resources::KEY)
                        ->updateRef(
                            Stephino_Rpg_Db_Table_Cities::COL_ID, 
                            $cityDataId, 
                            $costKey, 
                            round($costBallance - $costValue, 4)
                        );
                    break;
            }
        }
    }
}

/* EOF */