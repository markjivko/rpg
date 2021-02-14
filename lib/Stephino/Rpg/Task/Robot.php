<?php

/**
 * Stephino_Rpg_Task_Robot
 * 
 * @title      Robot
 * @desc       Perform automatic queue/military actions
 * @copyright  (c) 2021, Stephino
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
     * Robot ID
     * 
     * @var int
     */
    protected $_robotId = null;
    
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
            list($wpUserId, $this->_robotId) = Stephino_Rpg_TimeLapse::getWorkspace();

            // Ignore human players
            if (null === $this->_robotId) {
                break;
            }
            
            // Store the credentials
            $this->_db = Stephino_Rpg_Db::get($this->_robotId, $wpUserId);
            $this->_logTag = '<Robot #' . $this->_robotId . '> ';
            
            // Fervor
            if (mt_rand(1, 100) > Stephino_Rpg_Config::get()->core()->getRobotsFervor()) {
                Stephino_Rpg_Log::check() && Stephino_Rpg_Log::info("{$this->_logTag} Zzz");
                break;
            }
            
            // Queue military entities
            $this->_queueEntities();
            
            // Queue Research Fields and Buildings
            $this->_queueAdvisor();
            
            // Assign Workers to Buildings
            $this->_queueWorkers();
            
            // Attack other players
            $this->_militaryAttack();
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
                        $this->_db->modelQueues()->queueBuilding(
                            $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID],
                            $unlockObject->getId()
                        );
                        Stephino_Rpg_Log::check() && Stephino_Rpg_Log::info("{$this->_logTag} Queued building: {$unlockObject->getName()}");
                    } 
                    
                    // Research fields queue
                    if($unlockObject instanceof Stephino_Rpg_Config_ResearchField 
                        && count($queuedResearchFields) < Stephino_Rpg_Config::get()->core()->getMaxQueueResearchFields()) {
                        // Try the upgrade
                        $this->_spend($costData, $cityData);
                        
                        // Queue the research field
                        $this->_db->modelQueues()->queueResearchField(
                            $cityData[Stephino_Rpg_Db_Table_Users::COL_ID],
                            $unlockObject->getId()
                        );
                        Stephino_Rpg_Log::check() && Stephino_Rpg_Log::info("{$this->_logTag} Queued research field: {$unlockObject->getName()}");
                    }
                } catch (Exception $exc) {
                    Stephino_Rpg_Log::check() && Stephino_Rpg_Log::warning(
                        "Task_Robot._queueAdvisor, " . get_class($unlockObject) . " ({$unlockObject->getId()}): {$exc->getMessage()}"
                    );
                }
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
                
                // Get a random building
                shuffle($buildingAvailable);
                list($buildingConfigId, $buildingLevel) = current($buildingAvailable);
                
                try {
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
                        $this->_db->modelQueues()->queueBuilding(
                            $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID],
                            $buildingConfig->getId()
                        );
                        Stephino_Rpg_Log::check() && Stephino_Rpg_Log::info("{$this->_logTag} Queued random building: {$buildingConfig->getName()}");
                    }
                } catch (Exception $exc) {
                    Stephino_Rpg_Log::check() && Stephino_Rpg_Log::warning(
                        "Task_Robot._queueAdvisor, Building ($buildingConfigId): {$exc->getMessage()}"
                    );
                }
            }
        }
    }
    
    /**
     * Queues military entities, prioritizing lower upkeep
     * Upkeep must not be greater than 50% of total production
     */
    protected function _queueEntities() {
        // Military vs Economy stance
        $recruitProbability = 0;
        switch (Stephino_Rpg_Config::get()->core()->getRobotsAggression()) {
            case Stephino_Rpg_Config_Core::ROBOT_AGG_HIGH:
                $recruitProbability = 75;
                break;
            
            case Stephino_Rpg_Config_Core::ROBOT_AGG_MEDIUM:
                $recruitProbability = 25;
                break;
            
            case Stephino_Rpg_Config_Core::ROBOT_AGG_LOW:
                $recruitProbability = 5;
                break;
        }
        
        do {
            // Don't recruit yet
            if (mt_rand(1, 100) > $recruitProbability) {
                break;
            }
            
            // Get user data (first city)
            $cityData = current(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData());
            
            // Entities queued
            $queuedEntities = false;
            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Queues::KEY)->getData() as $queueData) {
                if (Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_UNIT == $queueData[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_TYPE]
                    || Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_SHIP == $queueData[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_TYPE]) {
                    $queuedEntities = true;
                    break;
                }
            }
            
            // Don't overlap with other entity queues
            if ($queuedEntities) {
                break;
            }
            
            // Get the most powerful military entity with lowest upkeep
            if (null === $entityMvp = Stephino_Rpg_Renderer_Ajax_Action::getEntityMVP(
                (int) $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]
            )) {
                break;
            }
            
            // Get the entity information
            list($entityConfig, $entityCount, $costData) = $entityMvp;
            
            // Prepare the entity type
            $entityType = $entityConfig instanceof Stephino_Rpg_Config_Unit
                ? Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT
                : Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_SHIP;
            
            try {
                // Spend resources for 1 x (block cost for $entityCount)
                $this->_spend($costData, $cityData, 1);

                // Enqueue entity
                $this->_db->modelQueues()->queueEntity(
                    $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID], 
                    $entityType,
                    $entityConfig->getId(), 
                    $entityCount
                );
                Stephino_Rpg_Log::check() && Stephino_Rpg_Log::info(
                    "{$this->_logTag} Queued military entity: {$entityCount} x {$entityConfig->getName()}"
                );
            } catch (Exception $exc) {
                Stephino_Rpg_Log::check() && Stephino_Rpg_Log::warning(
                    "Task_Robot._queueEntities, Entity/$entityType ({$entityConfig->getId()}) x {$entityCount}: {$exc->getMessage()}"
                );
            }
        } while(false);
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
                Stephino_Rpg_Log::check() && Stephino_Rpg_Log::info("{$this->_logTag} Assigned {$buildingWorkers} / {$buildingWorkersMax} workers to building #{$buildingId}");
            }
        }
    }
    
    /**
     * Attack other players
     */
    protected function _militaryAttack() {
        do {
            // Low: Never attack
            if (Stephino_Rpg_Config_Core::ROBOT_AGG_LOW === Stephino_Rpg_Config::get()->core()->getRobotsAggression()) {
                break;
            }
            
            // Store the current time
            $currentTime = time();
            
            // Don't attack too often
            if ($currentTime - Stephino_Rpg_Cache_User::get()->read(Stephino_Rpg_Cache_User::KEY_ROBOT_ATT_TIME, 0) 
                < 3600 * Stephino_Rpg_Config::get()->core()->getRobotsTimeout()) {
                break;
            }
            
            // Get the revenge list
            if (!is_array($revengeList = Stephino_Rpg_Cache_User::get()->read(Stephino_Rpg_Cache_User::KEY_ROBOT_ATT_LIST, array()))) {
                $revengeList = array();
            }
            
            // Medium: only fight back
            if (Stephino_Rpg_Config_Core::ROBOT_AGG_MEDIUM === Stephino_Rpg_Config::get()->core()->getRobotsAggression() 
                && !count($revengeList)) {
                break;
            }
            
            // Get user data (first city)
            $cityData = current(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData());

            // Store the attacking city ID
            $cityId = (int) $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID];
                
            // Prepare the defending city ID
            $defCityId = null;
            
            // Valid revenge list
            if (count($revengeList)) {
                shuffle($revengeList);

                // Get the first city
                $defCityId = (int) array_shift($revengeList);
                
                // Forgive and forget
                Stephino_Rpg_Cache_User::get()->write(
                    Stephino_Rpg_Cache_User::KEY_ROBOT_ATT_LIST, 
                    $revengeList
                );
            } else {
                // High: Initiate attacks on random players
                if (is_array($randomPlayers = $this->_db->tableUsers()->getRandom(3))) {
                    // Get the first player from the list
                    $defUserRow = current($randomPlayers);
                    
                    // Get the player's cities
                    $playerCities = $this->_db->tableCities()->getByUser(
                        (int) $defUserRow[Stephino_Rpg_Db_Table_Users::COL_ID]
                    );
                    
                    // Find a suitable city to attack
                    if (is_array($playerCities)) {
                        // Get a random city
                        shuffle($playerCities);
                        foreach ($playerCities as $defCityRow) {
                            // Check city level, not too low, not too high
                            $defCityLevel = (int) $defCityRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL];
                            $attCityLevel = (int) $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL];
                            
                            // Mark the city for attack; protect noob cities forever
                            if ($attCityLevel - $defCityLevel <= Stephino_Rpg_Config::get()->core()->getNoobLevels()) {
                                $defCityId = (int) $defCityRow[Stephino_Rpg_Db_Table_Cities::COL_ID];
                                break;
                            }
                        }
                    }
                }
            }
            
            // Found our target
            if (null !== $defCityId && $defCityId >= 1) {
                // Prepare the army payload
                $attackArmy = array();
                $totalAttack = 0;
                
                // Prepare our troops
                if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_Entities::KEY)->getData())) {
                    foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_Entities::KEY)->getData() as $entityRow) {
                        if ($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CITY_ID] == $cityId) {
                            /* @var $entityConfig Stephino_Rpg_Config_Unit|Stephino_Rpg_Config_Ship */
                            $entityConfig = Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT == $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE]
                                ? Stephino_Rpg_Config::get()->units()->getById($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID])
                                : Stephino_Rpg_Config::get()->ships()->getById($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]);

                            // Only count valid entity configs
                            if (null !== $entityConfig) {
                                // Prepare the payload key
                                $entityKey = $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE] 
                                    . '_' . $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID];

                                // Store the entity
                                $attackArmy[$entityKey] = (int) $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT];
                                
                                // Store the attack points
                                $totalAttack += $attackArmy[$entityKey] * $entityConfig->getAmmo() * $entityConfig->getDamage();
                            }
                        }
                    }
                }

                // Get the new convoy ID
                if (count($attackArmy) && $totalAttack > 0) {
                    Stephino_Rpg_Log::check() && Stephino_Rpg_Log::info(
                        "{$this->_logTag} Attacking city {$defCityId} with {$totalAttack} attack points"
                    );
                        
                    try {
                        $this->_db->modelConvoys()->createAttack(
                            $cityId, 
                            $defCityId, 
                            $attackArmy
                        );
                        
                        Stephino_Rpg_Cache_User::get()->write(
                            Stephino_Rpg_Cache_User::KEY_ROBOT_ATT_TIME, 
                            $currentTime
                        );
                    } catch (Exception $exc) {
                        Stephino_Rpg_Log::check() && Stephino_Rpg_Log::warning(
                            "Task_Robot._militaryAttack, from city #$cityId to city #$defCityId: {$exc->getMessage()}"
                        );
                    }
                }
            }
            
            // Commit the changes, if any
            Stephino_Rpg_Cache_User::get()->commit();
        } while(false);
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
            
            // Get the current balance
            $costBalance = floatval($cityData[$costKey]);
            
            // Cost multiplier
            if (1 != $multiplier) {
                $costValue *= $multiplier;
            }
            
            // We cannot afford this
            if ($costValue > $costBalance) {
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
                            round($costBalance - $costValue, 4)
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
                            round($costBalance - $costValue, 4)
                        );
                    break;
            }
        }
    }
}

/* EOF */