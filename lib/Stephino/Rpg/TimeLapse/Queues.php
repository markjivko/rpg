<?php
/**
 * Stephino_Rpg_TimeLapse_Queues
 * 
 * @title      Time-Lapse::Queues
 * @desc       Manage the queues time-lapse
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_TimeLapse_Queues extends Stephino_Rpg_TimeLapse_Abstract {

    /**
     * Time-Lapse::Queues
     */
    const KEY = 'Queues';
    
    // Queue actions
    const ACTION_PREMIUM_EXP = 'premium_exp';
    
    /**
     * Stepper
     * 
     * @param int $checkPointTime  UNIX timestamp
     * @param int $checkPointDelta Time difference in seconds from the last timestamp
     */
    public function step($checkPointTime, $checkPointDelta) {
        $this->_stepTime = $checkPointTime;
        
        // Get the data
        $data = $this->getData();
        
        // Get the building data
        $buildingData = $this->getData(Stephino_Rpg_TimeLapse_Resources::KEY);
        
        // Prepare the Research Fields data
        $supportResearchData = $this->getData(Stephino_Rpg_TimeLapse_Support_ResearchFields::KEY);
        
        // Prepare the Entities data
        $supportEntitiesData = $this->getData(Stephino_Rpg_TimeLapse_Support_Entities::KEY);
        
        // Earned queue points
        $earnedPoints = 0;
        
        // Go through the rows
        foreach($data as &$dataRow) {
            // Already parsed this entry and marked for removal
            if (isset($dataRow[self::MAGIC_KEY_DELETE])) {
                continue;
            }
            
            // Go through the queueable types
            switch ($dataRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_TYPE]) {
                // Building
                case Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_BUILDING:
                    // Construction complete
                    if ($dataRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_TIME] <= $checkPointTime) {
                        // Prepare the building level
                        $newBuildingLevel = null;
                        
                        // Store the message data
                        $buildingMessageData = null;
                        
                        // Prepare the city ID that need a level update
                        $newBuildingLevelCityId = null;
                        
                        // Go through the buildings data
                        foreach($buildingData as &$buildingRow) {
                            if ($buildingRow[Stephino_Rpg_Db_Table_Buildings::COL_ID] == $dataRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID]) {
                                // Increment the building level
                                $buildingRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL] += 1;
                                
                                // Store the new building level
                                $newBuildingLevel = $buildingRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL];
                                
                                // Get the city configuration
                                $buildingConfig = Stephino_Rpg_Config::get()->buildings()->getById(
                                    $buildingRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID]
                                );
                                
                                // Main building
                                if (null !== $buildingConfig && $buildingConfig->isMainBuilding()) {
                                    $newBuildingLevelCityId = $buildingRow[Stephino_Rpg_Db_Table_Cities::COL_ID];
                                }
                                
                                // Store the message data
                                $buildingMessageData = $buildingRow;
                                
                                // Stop here
                                break;
                            }
                        }
                        
                        // New building level
                        if (null !== $newBuildingLevel) {
                            // Update the city levels in the time-lapse dataset
                            if (null !== $newBuildingLevelCityId) {
                                foreach($buildingData as &$buildingRow) {
                                    if ($buildingRow[Stephino_Rpg_Db_Table_Cities::COL_ID] == $newBuildingLevelCityId) {
                                        $buildingRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL] = $newBuildingLevel;
                                    }
                                }
                            }
                        
                            // Add to the message list
                            $this->_addMessage(
                                Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_ECONOMY, 
                                Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_BUILDING, 
                                $dataRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID],
                                $buildingMessageData
                            );
                            
                            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::info('Queue - Economy: Building');
                            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($buildingMessageData);
                            
                            // Earned points
                            $earnedPoints += ($newBuildingLevel * Stephino_Rpg_Config::get()->core()->getScoreQueueBuilding());
                        }
                        
                        // Mark row for deletion
                        $dataRow[self::MAGIC_KEY_DELETE] = true;
                    }
                    break;
                    
                // Premium Modifier
                case Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_PREMIUM:
                    if ($dataRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_TIME] <= $checkPointTime) {
                        $this->_addMessage(
                            Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_DIPLOMACY, 
                            self::ACTION_PREMIUM_EXP, 
                            $dataRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID],
                            $dataRow
                        );
                        
                        Stephino_Rpg_Log::check() && Stephino_Rpg_Log::info('Queue - Diplomacy: Premium modifier expired');
                        Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($dataRow);
                        
                        // Mark row for deletion
                        $dataRow[self::MAGIC_KEY_DELETE] = true;
                    }
                    break;

                // Research Field
                case Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_RESEARCH:
                    // R&D Complete
                    if ($dataRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_TIME] <= $checkPointTime) {
                        // Prepare the new research field level
                        $newResearchFieldLevel = null;
                        
                        // Prepare the message data
                        $supportResearchMessageData = null;
                        
                        // Update the research data
                        foreach ($supportResearchData as &$supportResearchRow) {
                            // Found our row
                            if ($supportResearchRow[Stephino_Rpg_Db_Table_ResearchFields::COL_ID] == $dataRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID]) {
                                // Update the research field
                                $supportResearchRow[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_LEVEL] += 1;
                                
                                // Store the value locally
                                $newResearchFieldLevel = $supportResearchRow[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_LEVEL];
                                
                                // Store the message data
                                $supportResearchMessageData = $supportResearchRow;
                                
                                // Stop here
                                break;
                            }
                        }
                        
                        // Add to the message list
                        if (null !== $newResearchFieldLevel) {
                            $this->_addMessage(
                                Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_RESEARCH, 
                                Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_RESEARCH, 
                                $dataRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID],
                                $supportResearchMessageData
                            );
                            
                            // Finished a research field for the first time
                            if (1 == $newResearchFieldLevel) {
                                $researchFieldConfig = Stephino_Rpg_Config::get()->researchFields()->getById(
                                    $supportResearchMessageData[Stephino_Rpg_Db_Table_ResearchFields::COL_ID]
                                );
                                if (null !== $researchFieldConfig && strlen($researchFieldConfig->getStory())) {
                                    $this->_addMessage(
                                        Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_DIPLOMACY, 
                                        Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_RESEARCH, 
                                        $researchFieldConfig->getId(), 
                                        $researchFieldConfig->getStory()
                                    );
                                }
                            }
                            
                            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::info('Queue - Research: Complete');
                            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($supportResearchMessageData);
                            
                            $earnedPoints += ($newResearchFieldLevel * Stephino_Rpg_Config::get()->core()->getScoreQueueResearch());
                        }
                        
                        // Mark row for deletion
                        $dataRow[self::MAGIC_KEY_DELETE] = true;
                    }
                    break;

                // Entity::Ship or Entity:Unit
                case Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_SHIP:
                case Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_UNIT:
                    // Invalid quantity
                    if ($dataRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_QUANTITY] <= 0) {
                        // Mark row for deletion
                        $dataRow[self::MAGIC_KEY_DELETE] = true;
                    } else {
                        // Get the batch start time
                        $batchStartTime = $dataRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_TIME] 
                            - $dataRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_DURATION];
                        
                        // Probably created some entities
                        if ($batchStartTime <= $checkPointTime) {
                            // Get the difference
                            $batchElapedTime = $checkPointTime - $batchStartTime;
                            
                            // Get the duration needed for 1 item increment
                            $batchUM = abs(
                                $dataRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_DURATION] 
                                / $dataRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_QUANTITY]
                            );
                            
                            // Get the total number of entities created
                            if ($batchUM > 0) {
                                $entitiesDelta = intval($batchElapedTime / $batchUM);
                            } else {
                                // No time left, assume we trained all the entities
                                $entitiesDelta = $dataRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_QUANTITY];
                            }
                            
                            // Cap the delta (for very old queues)
                            if ($entitiesDelta > $dataRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_QUANTITY]) {
                                $entitiesDelta = $dataRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_QUANTITY];
                            }
                            
                            // Prepare the message data
                            $entitiesMessageData = null;
                            
                            // We've got something
                            if ($entitiesDelta >= 1) {
                                // Prepare the new duration
                                $dataRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_DURATION] -= ($entitiesDelta * $batchUM);
                                
                                // Prepare the new entities total
                                $dataRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_QUANTITY] -= $entitiesDelta;
                                
                                // Update the entities
                                foreach ($supportEntitiesData as &$supportEntitiesRow) {
                                    if ($supportEntitiesRow[Stephino_Rpg_Db_Table_Entities::COL_ID] == $dataRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID]
                                        && $supportEntitiesRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE] == $dataRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_TYPE]) {
                                        // Join the troops
                                        $supportEntitiesRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT] += $entitiesDelta;
                                        
                                        // Store the message data
                                        $entitiesMessageData = array(
                                            $supportEntitiesRow
                                        );
                                        
                                        // Stop here
                                        break;
                                    }
                                }
                                
                                // Earned points
                                $earnedPoints += ($entitiesDelta * Stephino_Rpg_Config::get()->core()->getScoreQueueEntity());
                            }
                            
                            // Mark for deletion?
                            if ($dataRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_QUANTITY] <= 0 
                                || $dataRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_DURATION] <= 0) {
                                $dataRow[self::MAGIC_KEY_DELETE] = true;
                                
                                // Add to the message list
                                if (null !== $entitiesMessageData) {
                                    $this->_addMessage(
                                        Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_ECONOMY, 
                                        $dataRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_TYPE], 
                                        $dataRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID],
                                        $entitiesMessageData
                                    );
                                    
                                    // Log the details
                                    Stephino_Rpg_Log::check() && Stephino_Rpg_Log::info('Queue - Economy: ' . (
                                        Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_UNIT == $dataRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_TYPE] 
                                            ? 'Unit' 
                                            : 'Ship'
                                    ));
                                    Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($entitiesMessageData);
                                }
                            }
                        }
                    }
                    break;
            }
        }
        
        // Update the points
        if ($earnedPoints > 0) {
            foreach ($buildingData as &$buildingRow) {
                $buildingRow[Stephino_Rpg_Db_Table_Users::COL_USER_SCORE] += $earnedPoints;
            }
        }
        
        // Save the queue data
        $this->setData(self::KEY, $data);
        
        // Save the Building data
        $this->setData(Stephino_Rpg_TimeLapse_Resources::KEY, $buildingData);
        
        // Save the Research Field data
        $this->setData(Stephino_Rpg_TimeLapse_Support_ResearchFields::KEY, $supportResearchData);
        
        // Save the Entities data
        $this->setData(Stephino_Rpg_TimeLapse_Support_Entities::KEY, $supportEntitiesData);
    }

    /**
     * Get the definition for removals on save()
     * 
     * @return array
     */
    protected function _getDeleteStructure() {
        return array(
            $this->getDb()->tableQueues()->getTableName() => Stephino_Rpg_Db_Table_Queues::COL_ID,
        );
    }
    
    /**
     * Get the table structure that needs to be updated on save()
     * 
     * @return array
     */
    protected function _getUpdateStructure() {
        return array(
            $this->getDb()->tableQueues()->getTableName()  => array(
                Stephino_Rpg_Db_Table_Queues::COL_ID,
                array(
                    Stephino_Rpg_Db_Table_Queues::COL_QUEUE_QUANTITY,
                    Stephino_Rpg_Db_Table_Queues::COL_QUEUE_DURATION,
                ),
            ),
        );
    }
    
    /**
     * Initialize the worker data
     * 
     * @return array
     */
    protected function _initData() {
        // Get the results
        return $this->getDb()->getWpdb()->get_results(
            "SELECT * FROM `" . $this->getDb()->tableQueues() . "`"
            . " WHERE `" . $this->getDb()->tableQueues() . "`.`" . Stephino_Rpg_Db_Table_Queues::COL_QUEUE_USER_ID . "` = '" . $this->_userId . "'", 
            ARRAY_A
        );
    }

}

/*EOF*/