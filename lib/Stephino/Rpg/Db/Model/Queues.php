<?php
/**
 * Stephino_Rpg_Db_Model_Queues
 * 
 * @title     Model:Queues
 * @desc      Queues Model
 * @copyright (c) 2020, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Db_Model_Queues extends Stephino_Rpg_Db_Model {

    /**
     * Queues Model Name
     */
    const NAME = 'queues';

    /**
     * Delete all queues by user ID
     * 
     * @param int $userId User ID
     * @return int|false The number of rows deleted or false on error
     */
    public function deleteByUser($userId) {
        return $this->getDb()->tableQueues()->deleteByUser($userId);
    }
    
    /**
     * Enable a premium modifier
     * 
     * @param int     $userId                  User ID
     * @param int     $premiumModifierConfigId Premium modifier configuration ID
     * @param int     $premiumModifierCount    Premium modifier count
     * @param boolean $timelapseMode           (optional) Time-lapse mode; default <b>true</b>
     * @return int Queue ID
     */
    public function queuePremiumModifier($userId, $premiumModifierConfigId, $premiumModifierCount, $timelapseMode = true) {
        if (null === $premiumModifierConfig = Stephino_Rpg_Config::get()->premiumModifiers()->getById($premiumModifierConfigId)) {
            throw new Exception(__('Invalid premium modifier', 'stephino-rpg'));
        }
        
        // Invalid quantity
        if ($premiumModifierCount < 1) {
            throw new Exception(__('Invalid number of premium modifiers', 'stephino-rpg'));
        }
        
        // Get the Queue ID
        return $this->_queue(
            $userId, 
            0, 
            Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_PREMIUM, 
            $premiumModifierConfigId, 
            $premiumModifierCount, 
            $premiumModifierConfig->getDuration() * 3600 * $premiumModifierCount, 
            $timelapseMode
        );
    }
    
    /**
     * Building construction queue/dequeue
     * 
     * @param int     $cityId           City ID
     * @param int     $buildingConfigId Building Configuration ID
     * @param boolean $queue            (optional) Whether to queue or dequeue the building; default <b>true</b>
     * @param boolean $timelapseMode    (optional) Using the model in Time-Lapse mode; default <b>true</b>
     * @return int|null Queue ID or null on error or if the queue was removed
     * @throws Exception
     */
    public function queueBuilding($cityId, $buildingConfigId, $queue = true, $timelapseMode = true) {
        // Get the building configuration object
        if (null === $buildingConfig = Stephino_Rpg_Config::get()->buildings()->getById($buildingConfigId)) {
            throw new Exception(
                sprintf(
                    __('Invalid configuration (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigBuildingName()
                )
            );
        }
        
        // Prepare the city information
        $cityRow = null;
        
        // Prepare the building information
        $buildingRow = null;
        
        // Time-lapse mode
        if ($timelapseMode) {
            if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
                foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $dbRow) {
                    // City row found
                    if (null === $cityRow && $cityId == $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CITY_ID]) {
                        $cityRow = $dbRow;
                    }
                    
                    // Building row found
                    if (null === $buildingRow && $cityId == $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CITY_ID]
                        && $buildingConfig->getId() == $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID]) {
                        $buildingRow = $dbRow;
                    }
                    
                    if (null !== $cityRow && null !== $buildingRow) {
                        break;
                    }
                }
            }
        } else {
            $buildingRow = $this->getDb()->tableBuildings()->getByCityAndConfig($cityId, $buildingConfig->getId());
            $cityRow = $this->getDb()->tableCities()->getById($cityId);
        }
        
        // Invalid City
        if (!is_array($cityRow)) {
            throw new Exception(
                sprintf(
                    __('Invalid ID (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigCityName()
                )
            );
        }
        
        // Check max queue for buildings
        $this->_validateMaxQueue(
            $cityRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_USER_ID], 
            $cityRow[Stephino_Rpg_Db_Table_Cities::COL_ID], 
            Stephino_Rpg_Db_Model_Buildings::NAME,
            $timelapseMode
        );
        
        // Add the building at level 0
        if (null === $buildingRow) {
            $buildingId = $this->getDb()->modelBuildings()->create(
                $cityId, 
                $buildingConfig->getId(), 
                0
            );
            
            // Insert failed
            if (null === $buildingId) {
                throw new Exception(
                    sprintf(
                        __('Could not create (%s)', 'stephino-rpg'),
                        Stephino_Rpg_Config::get()->core()->getConfigBuildingName()
                    )
                );
            }
        } else {
            $buildingId = $buildingRow[Stephino_Rpg_Db_Table_Buildings::COL_ID];
        }
        
        // Get the Queue ID
        return $this->_queue(
            $cityRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_USER_ID], 
            $cityRow[Stephino_Rpg_Db_Table_Cities::COL_ID], 
            Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_BUILDING, 
            $buildingId, 
            $queue ? 1 : -1, 
            $this->getDb()->modelBuildings()->getBuildTime(
                $buildingConfig, 
                null === $buildingRow 
                    ? 0
                    : $buildingRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL],
                $cityRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_USER_ID],
                $timelapseMode
            ), 
            $timelapseMode
        );
    }
    
    /**
     * Research field queue/dequeue
     * 
     * @param int     $userId                User ID; overwritten with the current user ID in timelapse mode
     * @param int     $researchFieldConfigId Research Field Configuration ID
     * @param boolean $queue                 (optional) Whether to queue or dequeue the research field; default <b>true</b>
     * @param boolean $timelapseMode         (optional) Using the model in Time-Lapse mode; default <b>true</b>
     * @return int Queue ID
     * @throws Exception
     */
    public function queueResearchField($userId, $researchFieldConfigId, $queue = true, $timelapseMode = true) {
        // Validate the configuration
        if (null === $researchFieldConfig = Stephino_Rpg_Config::get()->researchFields()->getById($researchFieldConfigId)) {
            throw new Exception(
                sprintf(
                    __('Invalid configuration (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigResearchFieldName()
                )
            );
        }
        
        // Check max queue for research fields
        $this->_validateMaxQueue(
            $userId, 
            0, 
            Stephino_Rpg_Db_Model_ResearchFields::NAME,
            $timelapseMode
        );
        
        // Prepare the research row
        $researchRow = null;
        
        // Go through the timelapse
        if ($timelapseMode) {
            if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_ResearchFields::KEY)->getData())) {
                foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_ResearchFields::KEY)->getData() as $dbRow) {
                    if ($userId == $dbRow[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_USER_ID]
                        && $researchFieldConfigId == $dbRow[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_CONFIG_ID]) {
                        $researchRow = $dbRow;
                        break;
                    }
                }
            }
        } else {
            $researchRow = $this->getDb()->tableResearchFields()->getByUserAndConfig($userId, $researchFieldConfigId);
        }
        
        // Initialize the research field
        if (null === $researchRow) {
            $researchFieldId = $this->getDb()->modelResearchFields()->setLevel($userId, $researchFieldConfigId, 0);
            
            // Insert failed
            if (null === $researchFieldId) {
                throw new Exception(
                    sprintf(
                        __('Could not create (%s)', 'stephino-rpg'),
                        Stephino_Rpg_Config::get()->core()->getConfigResearchFieldName()
                    )
                );
            }
        } else {
            $researchFieldId = $researchRow[Stephino_Rpg_Db_Table_ResearchFields::COL_ID];
        }
        
        return $this->_queue(
            $userId,
            0,
            Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_RESEARCH, 
            $researchFieldId, 
            $queue ? 1 : -1, 
            $this->getDb()->modelResearchFields()->getResearchTime(
                $researchFieldConfig, 
                null === $researchRow 
                    ? 0
                    : $researchRow[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_LEVEL],
                $userId,
                $timelapseMode
            ), 
            $timelapseMode
        );
    }
    
    /**
     * Entity recruitment queue/dequeue
     * 
     * @param int     $cityId         City ID
     * @param int     $entityType     Entity Type, one of
     * <ul>
     *     <li>Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT</li>
     *     <li>Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_SHIP</li>
     * </ul>
     * @param int     $entityConfigId Entity Configuration ID
     * @param int     $entityCount    Entity count; negative values allowed to remove entities from queue
     * @param boolean $timelapseMode (optional) Using the model in Time-Lapse mode; default <b>true</b>
     * @param boolean $enqueue       (optional) Enqueue/dequeue the entity; default <b>true</b>
     * @return int|bool|null Queue ID or null on error (enqueue) | Queue ID or true (dequeue)
     * @throws Exception
     */
    public function queueEntity($cityId, $entityType, $entityConfigId, $entityCount, $timelapseMode = true, $enqueue = true) {
        /* @var $configEntity Stephino_Rpg_Config_Unit|Stephino_Rpg_Config_Ship*/
        $configEntity = null;
        
        // Get the Entity Configuration Object
        switch ($entityType) {
            case Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT:
                $configEntity = Stephino_Rpg_Config::get()->units()->getById($entityConfigId);
                break;
            
            case Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_SHIP:
                $configEntity = Stephino_Rpg_Config::get()->ships()->getById($entityConfigId);
                break;
        }
        
        // Invalid Entity Configuration
        if (null === $configEntity) {
            throw new Exception(__('Invalid entity configuration ID', 'stephino-rpg'));
        }
        
        // Get the parent configuration
        if (null === $configBuilding = $configEntity->getBuilding()) {
            throw new Exception(
                sprintf(
                    __('Invalid configuration (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigBuildingName()
                )
            );
        }
        
        // Prepare the building information
        $buildingRow = null;
        if ($timelapseMode) {
            if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
                foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $dbRow) {
                    if ($configBuilding->getId() == $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID]
                        && $cityId == $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CITY_ID]) {
                        $buildingRow = $dbRow;
                        break;
                    }
                }
            }
        } else {
            $buildingRow = $this->getDb()->tableBuildings()->getByCityAndConfig($cityId, $configBuilding->getId());
        }
        
        // No parent building
        if (null === $buildingRow) {
            throw new Exception(
                sprintf(
                    __('Invalid ID (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigBuildingName()
                )
            );
        }
        
        // Check max queue for entities
        $this->_validateMaxQueue(
            $buildingRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_USER_ID], 
            $buildingRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CITY_ID], 
            Stephino_Rpg_Db_Model_Entities::NAME,
            $timelapseMode
        );
        
        // Prepare the entity ID
        $entityId = null;
        
        // Get all the entities
        $entityRows = $timelapseMode 
            ? Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_Entities::KEY)->getData()
            : $this->getDb()->tableEntities()->getByCity($cityId);
        
        if (is_array($entityRows)) {
            foreach ($entityRows as $entityRow) {
                if ($entityConfigId == $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]
                    && $entityType == $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE]
                    && $cityId == $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CITY_ID]) {
                    // Store the entity ID
                    $entityId = (int) $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ID];
                    break;
                }
            }
        }
        
        // No entity found
        if (null === $entityId) {
            // Create an entity slot in this city
            $entityId = $this->getDb()->modelEntities()->set(
                $cityId, 
                $entityType, 
                $entityConfigId
            );
        }
        
        // Prepare the recruitment time in seconds
        $recruitTime = $this->getDb()->modelEntities()->getRecruitTime(
            $configEntity, 
            $entityCount, 
            $buildingRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL],
            $buildingRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_USER_ID],
            $timelapseMode
        );
        
        // Create the queue
        return $this->_queue(
            $buildingRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_USER_ID],
            $buildingRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CITY_ID], 
            $entityType, 
            $entityId, 
            $entityCount, 
            $recruitTime, 
            $timelapseMode,
            $enqueue
        );
    }
    
    /**
     * Building, unit, ship and research queue
     * 
     * @param int     $userId       User ID
     * @param int     $cityId       City ID
     * @param string  $itemType     Item type, one of 
     * <ul>
     *     <li>Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_BUILDING</li>
     *     <li>Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_RESEARCH</li>
     *     <li>Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_UNIT</li>
     *     <li>Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_SHIP</li>
     * </ul>
     * @param int     $itemId        Item ID
     * @param int     $itemQuantity  Item Quantity (levels or number of units/ships)
     * @param int     $queueDuration Task completion time in seconds
     * @param boolean $timelapseMode (optional) Using the model in Time-Lapse mode; default <b>true</b>
     * @param boolean $enqueue       (optional) Enqueue/Dequeue an item; default <b>true</b>
     * @return int|bool|null Queue ID or null on error (enqueue) | Queue ID or true (dequeue)
     * @throws Exception
     */
    protected function _queue($userId, $cityId, $itemType, $itemId, $itemQuantity, $queueDuration, $timelapseMode = true, $enqueue = true) {
        // Prepare the result
        $result = true;
        
        // Sanitize the integers
        $userId = intval($userId);
        $itemId = intval($itemId);
        $itemQuantity = intval($itemQuantity);
        $queueDuration = intval($queueDuration);
        
        // Validate the values
        if ($userId <= 0 || $itemId <= 0) {
            throw new Exception(__('Invalid User or Item ID', 'stephino-rpg'));
        }
        
        // Sanitize the item type
        if (!in_array($itemType, array(
            Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_BUILDING,
            Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_RESEARCH,
            Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_UNIT,
            Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_SHIP,
            Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_PREMIUM,
        ))) {
            $itemType = Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_BUILDING;
        }
        
        // Check for uniqueness
        $queueData = null;
        
        // Get the rows
        $queueRows = $timelapseMode
            ? Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Queues::KEY)->getData()
            : $this->getDb()->tableQueues()->getById($userId);
        
        // Ships and units can have multiple queues
        if (is_array($queueRows)) {
            foreach ($queueRows as $dbRow) {
                // Unique key handled here instead of the DB
                if ($dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_TYPE] == $itemType
                    && $dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID] == $itemId) {
                    // Store the entry
                    $queueData = $dbRow;
                    break;
                }
            }
        }
            
        do {
            // Entry found
            if (null !== $queueData) {
                // Prepare the new quantity
                $newItemQuantity = $queueData[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_QUANTITY] + ($enqueue ? 1 : -1) * $itemQuantity;

                // Removed from queue
                if ($newItemQuantity <= 0) {
                    $this->getDb()->tableQueues()->unqueue($queueData[Stephino_Rpg_Db_Table_Queues::COL_ID]);
                    break;
                } else {
                    // Units/Ships actions are additive
                    if (in_array($itemType, array(Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_UNIT, Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_SHIP))) {
                        // Prepare the new duration
                        $newQueueDuration = $queueData[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_DURATION] + ($enqueue ? 1 : -1) * $queueDuration;
                        if ($newQueueDuration < 0) {
                            $newQueueDuration = 0;
                        }

                        // Update the data
                        $this->getDb()->tableQueues()->updateById(
                            array(
                                Stephino_Rpg_Db_Table_Queues::COL_QUEUE_QUANTITY => $newItemQuantity,
                                Stephino_Rpg_Db_Table_Queues::COL_QUEUE_DURATION => $newQueueDuration,
                                Stephino_Rpg_Db_Table_Queues::COL_QUEUE_TIME     => $newQueueDuration + time(),
                            ), 
                            $queueData[Stephino_Rpg_Db_Table_Queues::COL_ID]
                        );
                    }
                }

                $result = (int) $queueData[Stephino_Rpg_Db_Table_Queues::COL_ID];
                break;
            }

            // Item not in queue and we're trying to remove it
            if (!$enqueue) {
                break;
            }

            // Invalid quantity
            if ($itemQuantity <= 0) {
                throw new Exception(__('Invalid quantity', 'stephino-rpg'));
            }

            // Sanitize the duration
            if ($queueDuration < 0) {
                $queueDuration = 0;
            }

            // Insert the value
            $result = $this->getDb()->tableQueues()->create(
                $userId,
                $cityId,
                $itemType,
                $itemId,
                $itemQuantity,
                $queueDuration
            );
        } while(false);
        
        return $result;
    }
    
    /**
     * Check that the queue action is allowed (max queue not exceeded)
     * 
     * @param int      $userId        User ID, <b>ignored in time-lase mode</b>
     * @param int      $cityId        City ID
     * @param string   $queueType     Queue type, one of 
     * <ul>
     *     <li>Stephino_Rpg_Db_Model_Buildings::NAME</li>
     *     <li>Stephino_Rpg_Db_Model_Entities::NAME</li>
     *     <li>Stephino_Rpg_Db_Model_ResearchFields::NAME</li>
     * </ul>
     * @param boolean  $timelapseMode (optional) Using the model in Time-Lapse mode; default <b>true</b>
     * @throws Exception
     * @return int Current queue size, strictly lower than the maximum
     */
    protected function _validateMaxQueue($userId, $cityId, $queueType, $timelapseMode = true) {
        // Prepare the result
        $queueCount = 0;
        
        // Get the maximum queue size
        list($queueMax) = $this->getDb()->modelPremiumModifiers()->getMaxQueue($queueType, $userId, $timelapseMode);
        
        // Get the item types
        $queueItemTypes = array();
        switch($queueType) {
            case Stephino_Rpg_Db_Model_Buildings::NAME:
                $queueItemTypes = array(
                    Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_BUILDING
                );
                break;

            case Stephino_Rpg_Db_Model_Entities::NAME:
                $queueItemTypes = array(
                    Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_UNIT,
                    Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_SHIP,
                );
                break;

            case Stephino_Rpg_Db_Model_ResearchFields::NAME:
                $queueItemTypes = array(
                    Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_RESEARCH,
                );
                break;
        }

        // Get the data set
        $queueRows = $timelapseMode
            ? Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Queues::KEY)->getData()
            : $this->getDb()->tableQueues()->getById($userId, true);

        // Valid data set
        if (is_array($queueRows)) {
            foreach ($queueRows as $dbRow) {
                // Unique key handled here instead of the DB
                if (in_array($dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_TYPE], $queueItemTypes)
                    && $dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_CITY_ID] == $cityId) {
                    // Increment the counter
                    $queueCount++;

                    // Counter breached
                    if ($queueCount >= $queueMax) {
                        throw new Exception(__('Max. queue reached', 'stephino-rpg'));
                    }
                }
            }
        }
        return $queueCount;
    }
}

/* EOF */