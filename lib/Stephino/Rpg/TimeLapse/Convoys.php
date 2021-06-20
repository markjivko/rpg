<?php

/**
 * Stephino_Rpg_TimeLapse_Convoys
 * 
 * @title      Time-Lapse::Convoys
 * @desc       Manage the convoys time-lapse
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_TimeLapse_Convoys extends Stephino_Rpg_TimeLapse_Abstract {

    /**
     * Time-Lapse::Convoys
     */
    const KEY = 'Convoys';
    
    // Payload keys
    const PAYLOAD_DATA      = 'data';
    const PAYLOAD_ENTITIES  = 'entities';
    const PAYLOAD_RESOURCES = 'resources';
    const PAYLOAD_RESOURCES_KEYS = array(
        Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD,
        Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH,
        Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM,
        Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA,
        Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_BETA,
        Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_GAMMA,
        Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1,
        Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2,
    );
    
    // Convoy actions
    const ACTION_COLONIZE  = 'colonize';
    const ACTION_SPY       = 'spy';
    const ACTION_CHALLENGE = 'challenge';
    const ACTION_TRANSPORT = 'transport';
    const ACTION_ATTACK    = 'attack';
    const ACTION_RETURN    = 'return';

    // Arsenal
    const ARSENAL_DEFENSE = 'defense';
    const ARSENAL_OFFENSE = 'offense';
    
    // Attack results
    const ATTACK_DEFEAT_RETREAT   = 'dr';
    const ATTACK_DEFEAT_CRUSHING  = 'dc';
    const ATTACK_DEFEAT_HEROIC    = 'dh';
    const ATTACK_DEFEAT_BITTER    = 'db';
    const ATTACK_VICTORY_EASY     = 've';
    const ATTACK_VICTORY_CRUSHING = 'vc';
    const ATTACK_VICTORY_HEROIC   = 'vh';
    const ATTACK_VICTORY_BITTER   = 'vb';
    
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
        
        // Prepare the Entities data
        $supportEntitiesData = $this->getData(Stephino_Rpg_TimeLapse_Support_Entities::KEY);
        
        // Parse the convoys
        foreach ($data as &$dataRow) {
            // Already parsed this entry and marked for removal
            if (isset($dataRow[self::MAGIC_KEY_DELETE])) {
                continue;
            }
            
            // Return troops home
            $returnMode = $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_RETREAT_TIME] > 0;
            
            // Get the convoy time
            $convoyTime = intval(
                $returnMode 
                    ? $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_RETREAT_TIME] 
                    : $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TIME]
            );
            
            // Drums are beating
            if ($checkPointTime >= $convoyTime) {
                do {
                    // Colonize action
                    if (Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_COLONIZER == $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TYPE]) {
                        if ($this->_userId == $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_USER_ID]) {
                            // Colonize the empty slot
                            $this->_colonize($dataRow, $buildingData);
                            
                            // Destroy the colonizer entity
                            $dataRow[self::MAGIC_KEY_DELETE] = true;
                            break;
                        }
                    }
                    
                    // We are being approached
                    if (!$returnMode && $this->_userId == $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_USER_ID]) {
                        switch($dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TYPE]) {
                            // Military action
                            case Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_ATTACK:
                                // Remove killed units/ships from both armies; if we lost, transfer resources to payload
                                $newPayload = $this->_attack($dataRow, $buildingData, $supportEntitiesData);
                                break;
                            
                            // Spy action
                            case Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_SPY:
                                // Populate payload_data if succesful, remove payload_entities otherwise
                                $newPayload = $this->_spy($dataRow, $buildingData, $supportEntitiesData);
                                break;
                            
                            // Resource transport
                            case Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_TRANSPORTER:
                                // Add resources; transfer payload_data to payload_resources
                                $newPayload = $this->_transport($dataRow, $buildingData, $supportEntitiesData);
                                break;
                            
                            // Sentry challenge
                            case Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_SENTRY:
                                // Sentry challenge; transfer resources if successful
                                $newPayload = $this->_challenge($dataRow, $buildingData);
                                break;
                        }
                        
                        // Our adversary has troops to recover OR a peaceful transport OR sentry challenge
                        if (Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_TRANSPORTER == $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TYPE]
                            || Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_SENTRY == $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TYPE]
                            || (isset($newPayload[self::PAYLOAD_ENTITIES]) && count($newPayload[self::PAYLOAD_ENTITIES]))) {
                            // Store the new payload
                            $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_PAYLOAD] = json_encode($newPayload);
                            
                            // Post-attack speed adjustments
                            if (Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_ATTACK == $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TYPE]) {
                                // Check units have enough ships
                                $shipsCapacity = 0;
                                $unitsMass = 0;
                                if (Stephino_Rpg_Config::get()->core()->getTravelTime() > 0) {
                                    foreach ($newPayload[self::PAYLOAD_ENTITIES] as $entityId => $entityData) {
                                        // Count the units
                                        if (Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT == $entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE]) {
                                            if (null !== $configUnit = Stephino_Rpg_Config::get()->units()->getById(
                                                $entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]
                                            )) {
                                                $unitsMass += ($entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT] * $configUnit->getTroopMass());
                                            }
                                        } else {
                                            // Get the ship config
                                            if (null !== $configShip = Stephino_Rpg_Config::get()->ships()->getById(
                                                $entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]
                                            )) {
                                                $shipsCapacity += ($entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT] * $configShip->getTroopCapacity());
                                            }
                                        }
                                    }
                                }

                                // Some soldiers are going ot have to walk home
                                if ($unitsMass > $shipsCapacity) {
                                    // Came in with a speed advantage, left with more troops than ships
                                    if ($dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TRAVEL_FAST] == 1) {
                                        // The convoy moves 2 times slower
                                        $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TRAVEL_FAST] = 0;
                                        $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TRAVEL_DURATION] *= 2;
                                    }
                                } else {
                                    // Came in slowly with many troops, now there are enough ships to carry us home fast
                                    if ($dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TRAVEL_FAST] == 0) {
                                        // The convoy moves 2 times faster
                                        $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TRAVEL_FAST] = 1;
                                        $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TRAVEL_DURATION] = intval(
                                            $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TRAVEL_DURATION] / 2
                                        );
                                    }
                                }
                            }
                            
                            // Set the retreat time
                            $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_RETREAT_TIME] = (
                                $convoyTime + $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TRAVEL_DURATION]
                            );
                        } else {
                            // Even if they won, there's nobody to recover the resources; the attack is over
                            $dataRow[self::MAGIC_KEY_DELETE] = true;
                        }
                        break;
                    }
                    
                    // Units/ships have returned from an attack we initiated
                    if ($returnMode && $this->_userId == $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_USER_ID]) {
                        // Get the payload array
                        $payloadArray = @json_decode($dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_PAYLOAD], true);
                        
                        // Seemingly valid payload
                        if (is_array($payloadArray)) {
                            // Resources
                            if (isset($payloadArray[self::PAYLOAD_RESOURCES]) && is_array($payloadArray[self::PAYLOAD_RESOURCES])) {
                                // Prepare the resources delta
                                $resourcesDelta = array();

                                // Go through the allowed list of resources
                                foreach (self::PAYLOAD_RESOURCES_KEYS as $resourceKey) {
                                    if (isset($payloadArray[self::PAYLOAD_RESOURCES][$resourceKey])) {
                                        $resourcesDelta[$resourceKey] = intval($payloadArray[self::PAYLOAD_RESOURCES][$resourceKey]);
                                    }
                                }

                                // Update all resource rows
                                if (count($resourcesDelta)) {
                                    foreach ($buildingData as $bKey => $bValue) {
                                        foreach ($resourcesDelta as $resourceKey => $resourceValue) {
                                            // Update the cold resource on all rows
                                            if (Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD == $resourceKey) {
                                                $buildingData[$bKey][$resourceKey] += $resourceValue;
                                            } else {
                                                // Update attacking city's resources only
                                                if ($bValue[Stephino_Rpg_Db_Table_Cities::COL_ID] == $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_CITY_ID]) {
                                                    $buildingData[$bKey][$resourceKey] += $resourceValue;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            
                            // Entities
                            if (isset($payloadArray[self::PAYLOAD_ENTITIES]) && is_array($payloadArray[self::PAYLOAD_ENTITIES])) {
                                foreach ($supportEntitiesData as &$eValue) {
                                    // Update attacking city's entities only
                                    if ($eValue[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CITY_ID] == $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_CITY_ID]) {
                                        foreach ($payloadArray[self::PAYLOAD_ENTITIES] as $entityId => $entityData) {
                                            if (is_array($entityData) && isset($entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT])) {
                                                // Found our entity
                                                if ($eValue[Stephino_Rpg_Db_Table_Entities::COL_ID] == $entityId) {
                                                    $eValue[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT] += intval($entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT]);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        
                        // Our entities have returned home
                        switch ($dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TYPE]) {
                            case Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_TRANSPORTER:
                                $this->_addMessage(
                                    Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_ECONOMY, 
                                    self::ACTION_TRANSPORT, 
                                    $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_ID], 
                                    array(
                                        // Returned
                                        true,
                                        // Payload
                                        $payloadArray,
                                        // Convoy Row
                                        $dataRow
                                    )
                                );
                                break;
                            
                            case Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_SPY:
                                $this->_addMessage(
                                    Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_MILITARY, 
                                    self::ACTION_SPY, 
                                    $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_ID], 
                                    array(
                                        // Returned
                                        true,
                                        // Payload
                                        $payloadArray,
                                        // Convoy Row
                                        $dataRow
                                    )
                                );
                                break;
                            
                            case Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_SENTRY:
                                // Mark the sentry as inactive
                                foreach ($buildingData as &$buildingRow) {
                                    $buildingRow[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_ACTIVE] = 0;
                                }
                                
                                // Mission accomplished
                                if (isset($payloadArray[self::PAYLOAD_DATA]) && is_array($payloadArray[self::PAYLOAD_DATA])) {
                                    // Mission successful
                                    if (isset($payloadArray[self::PAYLOAD_DATA][2]) && $payloadArray[self::PAYLOAD_DATA][2]) {
                                        // Update the total score
                                        if (0 !== $sentryScore = Stephino_Rpg_Config::get()->core()->getSentryScore()) {
                                            foreach ($buildingData as &$buildingRow) {
                                                $buildingRow[Stephino_Rpg_Db_Table_Users::COL_USER_SCORE] += $sentryScore;
                                            }
                                        }
                                        $levelColumns = $this->getDb()->modelSentries()->getColumns();

                                        // Valid sentry challenge type
                                        if (isset($levelColumns[$payloadArray[self::PAYLOAD_DATA][0]])) {
                                            $levelColumn = $levelColumns[$payloadArray[self::PAYLOAD_DATA][0]];

                                            // Increment the level
                                            if (0 == Stephino_Rpg_Config::get()->core()->getSentryMaxLevel()
                                                || $buildingRow[$levelColumn] < Stephino_Rpg_Config::get()->core()->getSentryMaxLevel()) {
                                                foreach ($buildingData as &$buildingRow) {
                                                    $buildingRow[$levelColumn]++;
                                                }
                                            }
                                        }
                                    }
                                }
                                
                                // Store the return message
                                $this->_addMessage(
                                    Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_DIPLOMACY, 
                                    self::ACTION_CHALLENGE, 
                                    $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_ID], 
                                    array(
                                        // Returned
                                        true,
                                        // Payload
                                        $payloadArray,
                                        // Convoy Row
                                        $dataRow
                                    )
                                );
                                break;
                            
                            default:
                                $this->_addMessage(
                                    Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_MILITARY, 
                                    self::ACTION_RETURN, 
                                    $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_ID],
                                    array(
                                        // Attacker
                                        true,
                                        // Status
                                        null,
                                        // Payload
                                        $payloadArray,
                                        // Convoy Row
                                        $dataRow
                                    )
                                );
                                break;
                        }
                        Stephino_Rpg_Log::check() && Stephino_Rpg_Log::info($dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TYPE] . ': ' . self::ACTION_RETURN);
                        Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($payloadArray, $dataRow);
                        
                        // Delete the convoy
                        $dataRow[self::MAGIC_KEY_DELETE] = true;
                        break;
                    }
                    
                    // Another user is being attacked or recovers his troops
                    $otherUserId = $returnMode 
                        ? $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_USER_ID] 
                        : $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_USER_ID];
                    
                    // Get the other user's information
                    if (is_array($otherUserData = $this->getDb()->tableUsers()->getById($otherUserId, true))) {
                        // Get the other user's WP user id
                        $otherUserWpId = intval($otherUserData[Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID]);
                        $otherUserRobotId = null;
                        
                        // A robot account
                        if ($otherUserWpId == 0) {
                            $otherUserWpId = null;
                            $otherUserRobotId = $otherUserId;
                        }
                        
                        // Register this time-lapse at the end
                        Stephino_Rpg_TimeLapse::get()->registerThread($otherUserWpId, $otherUserRobotId, true);
                    } else {
                        // We have attacked, but the defending user disappeared
                        if (!$returnMode 
                            && $this->_userId == $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_USER_ID]) {
                            // Return home
                            $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_RETREAT_TIME] = $convoyTime 
                                + $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TRAVEL_DURATION];
                        } else {
                            // Other user was deleted, remove the convoy
                            $dataRow[self::MAGIC_KEY_DELETE] = true;
                        }
                    }
                } while (false);
            }
        }
        
        // Save the Convoy data
        $this->setData(self::KEY, $data);
        
        // Save the Building data
        $this->setData(Stephino_Rpg_TimeLapse_Resources::KEY, $buildingData);
        
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
            $this->getDb()->tableConvoys()->getTableName() => Stephino_Rpg_Db_Table_Convoys::COL_ID,
        );
    }
    
    /**
     * Get the table structure that needs to be updated on save()
     * 
     * @return array
     */
    protected function _getUpdateStructure() {
        return array(
            $this->getDb()->tableConvoys()->getTableName()  => array(
                Stephino_Rpg_Db_Table_Convoys::COL_ID,
                array(
                    Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_PAYLOAD,
                    Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_RETREAT_TIME,
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
        $tableConvoys = $this->getDb()->tableConvoys()->getTableName();
        
        // Prepare the query
        $query = "SELECT * FROM `$tableConvoys` " . PHP_EOL
            . "WHERE `" . Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_USER_ID . "` = {$this->_userId}" 
                . " OR `" . Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_USER_ID . "` = {$this->_userId}";
        Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($query . PHP_EOL);
        
        // Get the results
        return $this->getDb()->getWpdb()->get_results($query, ARRAY_A);
    }
    
    /**
     * Colonize an empty slot
     * 
     * @param array $dataRow Convoy DataBase row
     */
    protected function _colonize($dataRow, $buildingData) {
        // Prepare the target island and slot
        $targetIslandId = null;
        $targetIslandIndex = null;
        
        // Prepare the message details
        $messageDetails = false;
        
        // Valid input
        if (is_array($dataRow) && isset($dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_PAYLOAD])) {
            // Prepare the payload array
            $payloadArray = @json_decode($dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_PAYLOAD], true);

            // Validate the payload
            if (is_array($payloadArray) 
                && isset($payloadArray[self::PAYLOAD_DATA]) 
                && is_array($payloadArray[self::PAYLOAD_DATA]) 
                && 2 == count($payloadArray[self::PAYLOAD_DATA])) {
                list($targetIslandId, $targetIslandIndex) = array_map('intval', $payloadArray[self::PAYLOAD_DATA]);
            }
        }
        
        // Invalid input, nothing to do
        do {
            if (null === $targetIslandId || null === $targetIslandIndex) {
                break;
            }

            // City already built here
            if (null !== $cityData = $this->getDb()->tableCities()->getByIslandAndIndex($targetIslandId, $targetIslandIndex)) {
                // (race condition) Our city already created by another thread
                if ($cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_USER_ID] == $this->_userId) {
                    $messageDetails = array(
                        (int) $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID], 
                        (int) $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_CONFIG_ID]
                    );
                }
                break;
            }
            
            try {
                // Get the configurations already used on this island
                $cityConfigsUsed = array();
                
                // Find all other city configs on this island
                foreach ($buildingData as $dbRow) {
                    if ($targetIslandId == $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID]
                        && !in_array($dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_CONFIG_ID], $cityConfigsUsed)) {
                        $cityConfigsUsed[] = intval($dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_CONFIG_ID]);
                    }
                }
                
                // Get the unused configuration IDs
                $cityConfigsAvailable = array_diff(
                    array_map(
                        /* @var $cityConfig Stephino_Rpg_Config_City */
                        function($cityConfig) {
                            return $cityConfig->getId();
                        },
                        Stephino_Rpg_Config::get()->cities()->getAll()
                    ), 
                    $cityConfigsUsed
                );
                
                // Prepare the new city configuration ID
                $newCityConfigId = null;
                
                // First, assign all possible city configurations for this island (makes for a more rewarding gameplay)
                if (count($cityConfigsAvailable)) {
                    shuffle($cityConfigsAvailable);
                    $newCityConfigId = current($cityConfigsAvailable);
                }
                
                /* @var $newCityConfig Stephino_Rpg_Config_City */
                list($newCityId, $newCityConfig) = $this->getDb()->modelCities()->create(
                    $this->_userId, 
                    false,
                    $targetIslandId, 
                    false, 
                    null, 
                    $newCityConfigId, 
                    1, 
                    $targetIslandIndex
                );
                
                $messageDetails = array($newCityId, $newCityConfig->getId());
            } catch (Exception $exc) {
                Stephino_Rpg_Log::check() && Stephino_Rpg_Log::warning(
                    "Timelapse_Convoys._colonize, island #$targetIslandId, index #$targetIslandId: {$exc->getMessage()}"
                );
            }
        } while(false);
        
        // Send the message
        $this->_colonizeMessage($dataRow, $messageDetails);
    }
    
    /**
     * Colonizer messages
     * 
     * @param array         $dataRow Convoy DataBase row
     * @param array|boolean $info    New city information OR false on error
     */
    protected function _colonizeMessage($dataRow, $info) {
        // Send messages to the colonizer
        $this->_addMessage(
            Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_DIPLOMACY, 
            self::ACTION_COLONIZE, 
            $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_ID], 
            array(
                // Information
                $info,
                // Convoy Row
                $dataRow,
            )
        );
    }
    
    /**
     * Spy in our city
     * 
     * @param array $dataRow             Convoy DataBase row
     * @param array $buildingData        Building data
     * @param array $supportEntitiesData Entities data
     * @return array New payload
     */
    protected function _spy($dataRow, $buildingData, $supportEntitiesData) {
        // Prepare the result
        $finalConvoyPayload = array();
        
        // Valid input
        if (is_array($dataRow) 
            && isset($dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_PAYLOAD])
            && isset($dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID])) {
            // Prepare the payload array
            $payloadArray = @json_decode($dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_PAYLOAD], true);
            
            // Get the success rate
            $successRate = isset($payloadArray[self::PAYLOAD_DATA]) ? intval($payloadArray[self::PAYLOAD_DATA]) : 50;
            
            // Successful spy mission
            if (mt_rand(1, 100) <= $successRate) {
                // The spy gets to return home
                $finalConvoyPayload[self::PAYLOAD_ENTITIES] = $payloadArray[self::PAYLOAD_ENTITIES];
                
                // Prepare the city data
                $cityData = null;
                
                // Go through the building data
                foreach($buildingData as $buildingRow) {
                    if ($buildingRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CITY_ID] == $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID]) {
                        $cityData = $buildingRow;
                        break;
                    }
                }
                
                // Prepare the payload
                $spyPayloadResources = array();
                foreach (array_keys(Stephino_Rpg_Renderer_Ajax_Action::getResourceData()) as $spyResKey) {
                    $spyPayloadResources[$spyResKey] = mt_rand(1, 125) <= $successRate
                        ? round($buildingRow[$spyResKey], 0)
                        : false;
                }
                $spyPayloadEntities = array();
                foreach ($supportEntitiesData as $entityRow) {
                    if ($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CITY_ID] == $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID]) {
                        if (mt_rand(1, 125) <= $successRate) {
                            $spyPayloadEntities[] = array(
                                Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE => $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE],
                                Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID => $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID],
                                Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT => mt_rand(1, 125) <= $successRate
                                    ? (int) $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT]
                                    : false
                            );
                        }
                    }
                }
                
                // Store the details for later
                $finalConvoyPayload[self::PAYLOAD_DATA] = array($spyPayloadResources, $spyPayloadEntities);
                
                // Successful action
                $this->_spyMessage($dataRow, $cityData);
            } else {
                // Spy got caught
                $this->_spyMessage($dataRow, false);
            }
        }
        
        return $finalConvoyPayload;
    }
    
    /**
     * Spy messages
     * 
     * @param array         $dataRow Convoy DataBase row
     * @param array|boolean $payload Spy mission city data or false if got caught
     */
    protected function _spyMessage($dataRow, $payload) {
        // Attacker
        $this->_addMessage(
            Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_MILITARY, 
            self::ACTION_SPY, 
            $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_ID], 
            array(
                // Returned
                false,
                // Payload
                $payload,
                // Convoy Row
                $dataRow
            ),
            false,
            $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_USER_ID]
        );
            
        // Spy got caught
        if (false === $payload) {
            // Defender
            $this->_addMessage(
                Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_MILITARY, 
                self::ACTION_SPY, 
                $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_ID], 
                array(
                    // Returned
                    false,
                    // Payload
                    $payload,
                    // Convoy Row
                    $dataRow
                )
            );
        }
    }
    
    /**
     * Fight the local sentry and loot if necessary
     * 
     * @param array  $dataRow             Convoy DataBase row containing these keys: <ul>
     * <li>Stephino_Rpg_Db_Table_Convoys::COL_ID</li>
     * <li>Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID</li>
     * <li>Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_PAYLOAD</li>
     * <li>Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_USER_ID</li>
     * </ul>
     * @param array  $buildingData        Building data
     * @return array New payload
     */
    protected function _challenge($dataRow, &$buildingData) {
        $userData = current($buildingData);
        
        // Prepare the result
        $finalConvoyPayload = array();
        
        // Get the payload array
        $payloadArray = @json_decode($dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_PAYLOAD], true);
        
        // Local sentry is ready to defend
        $localSentryReady = (0 === (int) $userData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_ACTIVE]);
        
        // Transfer all resources and entities locally
        if (is_array($payloadArray)) {
            if (isset($payloadArray[self::PAYLOAD_DATA]) && is_array($payloadArray[self::PAYLOAD_DATA]) && count($payloadArray[self::PAYLOAD_DATA]) >= 2) {
                list($sentryChallenge, $sentryLevels) = $payloadArray[self::PAYLOAD_DATA];
                
                do {
                    // Invalid sentry challenge type
                    if( !in_array($sentryChallenge, array_keys(Stephino_Rpg_Db::get()->modelSentries()->getColumns()))) {
                        break;
                    }
                    
                    // Invalid levels
                    if (!is_array($sentryLevels)) {
                        break;
                    }
                    
                    // Validate the sentry levels
                    foreach (array_keys(Stephino_Rpg_Db::get()->modelSentries()->getColumns()) as $pqt) {
                        if (!isset($sentryLevels[$pqt])) {
                            break 2;
                        }
                        $sentryLevels[$pqt] = (int) $sentryLevels[$pqt];
                    }
                        
                    // Decide the probability of success
                    $successRate = $this->getDb()->modelSentries()->getSuccessRate(
                        (int) $sentryLevels[Stephino_Rpg_Db_Model_Sentries::CHALLENGE_ATTACK], 
                        (int) $sentryLevels[Stephino_Rpg_Db_Model_Sentries::CHALLENGE_DEFENSE], 
                        (int) $userData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_LEVEL_ATTACK],
                        (int) $userData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_LEVEL_DEFENSE]
                    );

                    // Successful fight
                    $challengeWon = !$localSentryReady || (mt_rand(0, 1000) <= $successRate * 10);

                    // Prepare the payload
                    $finalConvoyPayload = array(
                        self::PAYLOAD_DATA => array(
                            $sentryChallenge,
                            $sentryLevels,
                            $challengeWon
                        ),
                    );
                    
                    // Challenge won by attacker
                    if ($challengeWon) {
                        // Loot!
                        $finalConvoyPayload[self::PAYLOAD_RESOURCES] = array();
                        
                        // Prepare the loot and the gem reward (optional)
                        $sentryProductionData = Stephino_Rpg_Renderer_Ajax_Action::getProductionDataSentry(
                            isset($sentryLevels[Stephino_Rpg_Db_Model_Sentries::CHALLENGE_LOOTING])
                                ? $sentryLevels[Stephino_Rpg_Db_Model_Sentries::CHALLENGE_LOOTING]
                                : 0,
                            $sentryLevels[$sentryChallenge],
                            $successRate
                        );
                        
                        // Reward available
                        if (isset($sentryProductionData[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM])
                            && $sentryProductionData[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM][1] > 0) {
                            $finalConvoyPayload[self::PAYLOAD_RESOURCES][Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM] = $sentryProductionData[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM][1];
                        }

                        // Prepare the updated resources
                        $userResources = array(
                            Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD     => null,
                            Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH => null,
                        );
                        
                        // Update the resources and transfer the loot
                        foreach ($sentryProductionData as $dbKey => $prodData) {
                            // Gems are rewarded, not looted
                            if (Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM === $dbKey) {
                                continue;
                            }
                            
                            // Get the maximum loot
                            list(, $maxLootSize) = $prodData;
                            
                            // Valid loot value provided
                            if ($maxLootSize > 0) {
                                foreach($buildingData as &$buildingRow) {
                                    // Initialize the user left-over resources
                                    if (null === $userResources[$dbKey]) {
                                        $userResources[$dbKey] = floatval($buildingRow[$dbKey]);

                                        // Plenty of resources
                                        if ($userResources[$dbKey] >= $maxLootSize) {
                                            // Grab all that you can
                                            $finalConvoyPayload[self::PAYLOAD_RESOURCES][$dbKey] = $maxLootSize;

                                            // Remove from user
                                            $userResources[$dbKey] -= $maxLootSize;
                                        } else {
                                            // Heartless
                                            $finalConvoyPayload[self::PAYLOAD_RESOURCES][$dbKey] = $userResources[$dbKey];

                                            // Leave them with nothing
                                            $userResources[$dbKey] = 0;
                                        }
                                    }

                                    // Update building row
                                    $buildingRow[$dbKey] = $userResources[$dbKey];
                                }
                            }
                        }
                    }
                } while(false);
            }
        }
        
        // Notify the current user
        $this->_addMessage(
            Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_DIPLOMACY, 
            self::ACTION_CHALLENGE, 
            $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_ID] . '-' . $this->_userId, 
            array(
                // Returned
                false,
                // Payload
                $finalConvoyPayload,
                // Convoy Row
                $dataRow
            ),
            false,
            $this->_userId
        );
        
        return $finalConvoyPayload;
    }
    
    /**
     * Transfer resources to our city and prepare the return payload, as previously agreed
     * 
     * @param array  $dataRow             Convoy DataBase row containing these keys: <ul>
     * <li>Stephino_Rpg_Db_Table_Convoys::COL_ID</li>
     * <li>Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID</li>
     * <li>Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_PAYLOAD</li>
     * <li>Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_USER_ID</li>
     * </ul>
     * @param array  $buildingData        Building data
     * @param array  $supportEntitiesData Entities (defending Units and Ships) data
     * @return array New payload
     */
    protected function _transport($dataRow, &$buildingData, &$supportEntitiesData) {
        // Prepare the result
        $finalConvoyPayload = array();
        
        // Get the payload array
        $payloadArray = @json_decode($dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_PAYLOAD], true);

        // Transfer all resources and entities locally
        if (is_array($payloadArray)) {
            $this->_transportMessage($dataRow, $payloadArray);
        
            // Transfer resources
            if (isset($payloadArray[self::PAYLOAD_RESOURCES]) && is_array($payloadArray[self::PAYLOAD_RESOURCES])) {
                // Prepare the resources delta
                $resourcesDelta = array();

                // Go through the allowed list of resources
                foreach (self::PAYLOAD_RESOURCES_KEYS as $resourceKey) {
                    if (isset($payloadArray[self::PAYLOAD_RESOURCES][$resourceKey])) {
                        $resourcesDelta[$resourceKey] = intval($payloadArray[self::PAYLOAD_RESOURCES][$resourceKey]);
                    }
                }

                // Update all resource rows
                if (count($resourcesDelta)) {
                    foreach ($buildingData as $bKey => $bValue) {
                        foreach ($resourcesDelta as $resourceKey => $resourceValue) {
                            // Update the cold resource on all rows
                            if (Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD == $resourceKey) {
                                $buildingData[$bKey][$resourceKey] += $resourceValue;
                            } else {
                                // Update attacking city's resources only
                                if ($bValue[Stephino_Rpg_Db_Table_Cities::COL_ID] == $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID]) {
                                    $buildingData[$bKey][$resourceKey] += $resourceValue;
                                }
                            }
                        }
                    }
                }
            }
            
            // Entities
            if (isset($payloadArray[self::PAYLOAD_ENTITIES]) && is_array($payloadArray[self::PAYLOAD_ENTITIES])) {
                // Prepare the entity configurations
                $entityConfigs = array();

                // Go through the payload entities
                foreach ($payloadArray[self::PAYLOAD_ENTITIES] as $entityId => $entityData) {
                    // Valid entity
                    if (is_array($entityData) 
                        && isset($entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT])
                        && isset($entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE])
                        && isset($entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID])){

                        // Prepare the configuration object
                        if (!isset($entityConfigs[$entityId])) {
                            if (Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT == $entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE]) {
                                $entityConfigs[$entityId] = Stephino_Rpg_Config::get()->units()->getById(
                                    $entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]
                                );
                            } else {
                                $entityConfigs[$entityId] = Stephino_Rpg_Config::get()->ships()->getById(
                                    $entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]
                                );
                            }
                        }

                        // Go through the data
                        foreach ($supportEntitiesData as &$eValue) {
                            if ($eValue[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CITY_ID] == $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID]
                                && $eValue[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE] == $entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE]
                                && $eValue[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID] == $entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]
                                && null !== $entityConfigs[$entityId]
                                // Don't transfer the transporters
                                && (!$entityConfigs[$entityId] instanceof Stephino_Rpg_Config_Ship || !$entityConfigs[$entityId]->getAbilityTransport())
                            ) {
                                // Increment the count for locally initialized entities
                                $eValue[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT] += intval($entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT]);

                                // Remove from the the payload
                                unset($payloadArray[self::PAYLOAD_ENTITIES][$entityId]);
                            }
                        }
                    }
                }

                // Unknown (uninitialized) entities or transporter
                foreach ($payloadArray[self::PAYLOAD_ENTITIES] as $entityId => $entityData) {
                    // Invalid configuration ID
                    if (!isset($entityConfigs[$entityId]) || null === $entityConfigs[$entityId]) {
                        unset($payloadArray[self::PAYLOAD_ENTITIES][$entityId]);
                        continue;
                    }

                    // Keep the transporter for the journey home
                    if ($entityConfigs[$entityId] instanceof Stephino_Rpg_Config_Ship 
                        && $entityConfigs[$entityId]->getAbilityTransport()) {
                        continue;
                    }

                    // Initialize the entity
                    try {
                        $this->getDb()->modelEntities()->set(
                            $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID], 
                            $entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE], 
                            $entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID], 
                            $entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT] 
                        );
                    } catch (Exception $exc) {
                        Stephino_Rpg_Log::check() && Stephino_Rpg_Log::warning(
                            "Timelapse_Convoys._transport, city #{$dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID]},"
                                . " Entity/{$entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE]}"
                                . " ({$entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]})"
                                . " x {$entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT]}: {$exc->getMessage()}"
                        );
                    }

                    // This entity does not return
                    unset($payloadArray[self::PAYLOAD_ENTITIES][$entityId]);
                }
            }

            // Prepare the result
            $finalConvoyPayload = array(
                // Peaceful transport convoys are always safe
                self::PAYLOAD_ENTITIES  => $payloadArray[self::PAYLOAD_ENTITIES],
                self::PAYLOAD_RESOURCES => array()
            );

            // Escrow - resources only
            if (isset($payloadArray[self::PAYLOAD_DATA]) && is_array($payloadArray[self::PAYLOAD_DATA])) {
                // Release agreed-upon resources for the return trip
                foreach (self::PAYLOAD_RESOURCES_KEYS as $resourceKey) {
                    if (isset($payloadArray[self::PAYLOAD_DATA][$resourceKey])) {
                        $finalConvoyPayload[self::PAYLOAD_RESOURCES][$resourceKey] = $payloadArray[self::PAYLOAD_DATA][$resourceKey];
                    }
                }
            }
        }
        
        // Return convoy prepared
        return $finalConvoyPayload;
    }
    
    /**
     * Transport messages
     * 
     * @param array   $dataRow Convoy DataBase row
     * @param boolean $convoyPayload  Return convoy payload
     */
    protected function _transportMessage($dataRow, $convoyPayload) {
        // Origin
        $this->_addMessage(
            Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_ECONOMY, 
            self::ACTION_TRANSPORT, 
            $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_ID] . '-' 
                . $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_USER_ID], 
            array(
                // Returned
                false,
                // Information
                $convoyPayload,
                // Convoy Row
                $dataRow
            ),
            false,
            $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_USER_ID]
        );
        
        // Destination
        if ($dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_USER_ID] 
            != $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_USER_ID]) {
            $this->_addMessage(
                Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_ECONOMY, 
                self::ACTION_TRANSPORT, 
                $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_ID] . '-' 
                    . $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_USER_ID], 
                array(
                    // Returned
                    false,
                    // Information
                    $convoyPayload,
                    // Convoy Row
                    $dataRow
                ),
                false,
                $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_USER_ID]
            );
        }
    }
    
    /**
     * Decide the winner of an attack, move units and ships, transfer resources and send messages
     * 
     * @param array  $dataRow             Convoy DataBase row containing these keys: <ul>
     * <li>Stephino_Rpg_Db_Table_Convoys::COL_ID</li>
     * <li>Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID</li>
     * <li>Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_PAYLOAD</li>
     * <li>Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_USER_ID</li>
     * </ul>
     * @param array  $buildingData        Building data
     * @param array  $supportEntitiesData Entities (defending Units and Ships) data
     * @return array New payload
     */
    protected function _attack($dataRow, &$buildingData, &$supportEntitiesData) {
        list(, $robotId) = Stephino_Rpg_TimeLapse::getWorkspace();
        if (null !== $robotId) {
            if (!is_array($revengeList = Stephino_Rpg_Cache_User::get()->read(Stephino_Rpg_Cache_User::KEY_ROBOT_ATT_LIST, array()))) {
                $revengeList = array();
            }
            
            // Robot: I shall have my revenge!
            $revengeList[] = (int) $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_CITY_ID];
            
            // Maximum 10 enemies
            $revengeList = array_unique(array_slice($revengeList, 0, 10));
            
            // Save the enemy
            Stephino_Rpg_Cache_User::get()->write(
                Stephino_Rpg_Cache_User::KEY_ROBOT_ATT_LIST, 
                $revengeList
            )->commit();
        }
        
        // Prepare the result
        $finalConvoyPayload = array(
            self::PAYLOAD_ENTITIES  => array(),
            self::PAYLOAD_RESOURCES => array(),
        );
        
        // Fast validation of the Data Row
        if (is_array($dataRow) && isset($dataRow[Stephino_Rpg_Db_Table_Convoys::COL_ID])) {
            list($attackId, $cityId, $attackPayload, $attUserId) = array(
                $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_ID], 
                $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID], 
                $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_PAYLOAD],
                $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_USER_ID],
            );
        } else {
            return $finalConvoyPayload;
        }
        
        // Prepare the final attack payload
        $attackPayloadArray = @json_decode($attackPayload, true);
        if (is_array($attackPayloadArray)) {
            foreach (array_keys($finalConvoyPayload) as $payloadKey) {
                if (isset($attackPayloadArray[$payloadKey]) && is_array($attackPayloadArray[$payloadKey])) {
                    $finalConvoyPayload[$payloadKey] = $attackPayloadArray[$payloadKey];
                }
            }
        }
        
        // Prepare the city arsenal
        $cityWalls = array(
            self::ARSENAL_DEFENSE => 0,
            self::ARSENAL_OFFENSE => 0,
        );
        
        // Prepare the city troops
        $cityTroopsBefore = array(
            self::ARSENAL_DEFENSE => 0,
            self::ARSENAL_OFFENSE => 0,
        );
        
        // Prepare the attacker troops
        $attTroopsBefore = array(
            self::ARSENAL_DEFENSE => 0,
            self::ARSENAL_OFFENSE => 0,
        );
        
        // Disperse the troops on the field
        $attTroopsDispersed  = array();
        $cityTroopsDispersed = array();
        
        // Store the loot sizes
        $attTroopsLootBoxes = array();
        
        // Ready our city's military capabilities
        foreach($buildingData as $buildingRow) {
            if ($buildingRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CITY_ID] == $cityId) {
                $buildingLevel = (int) $buildingRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL];
                if ($buildingLevel > 0 && null !== $configBuilding = Stephino_Rpg_Config::get()->buildings()->getById(
                    $buildingRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID]
                )) {
                    // Military structure
                    if ($configBuilding->getAttackPoints() > 0 || $configBuilding->getDefensePoints() > 0) {
                        // Military production factor
                        $prodFactor = Stephino_Rpg_Renderer_Ajax_Action::getBuildingProdFactor(
                            Stephino_Rpg_Config::get()->cities()->getById(
                                $buildingRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_CONFIG_ID]
                            ),
                            $configBuilding, 
                            $buildingRow
                        );
                        if ($prodFactor > 0) {
                            // Offense
                            $cityWalls[self::ARSENAL_OFFENSE] += Stephino_Rpg_Utils_Config::getPolyValue(
                                $configBuilding->getAttackPointsPolynomial(),
                                $buildingLevel, 
                                $configBuilding->getAttackPoints()
                            ) * $prodFactor;

                            // Defense
                            $cityWalls[self::ARSENAL_DEFENSE] += Stephino_Rpg_Utils_Config::getPolyValue(
                                $configBuilding->getDefensePointsPolynomial(),
                                $buildingLevel, 
                                $configBuilding->getDefensePoints()
                            ) * $prodFactor;
                        }
                    }
                }
            }
        }
        
        // The city's troops have joined the attack
        foreach ($supportEntitiesData as $entityRow) {
            if ($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CITY_ID] == $cityId) {
                // Prepare the entity count
                $entityCount = intval($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT]);
                if ($entityCount <= 0) {
                    continue;
                }
                
                // Ship/Unit
                switch ($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE]) {
                    case Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT:
                    case Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_SHIP:
                        $configEntity = Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT == $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE]
                            ? Stephino_Rpg_Config::get()->units()->getById($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID])
                            : Stephino_Rpg_Config::get()->ships()->getById($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]);
                        
                        // Get the unit configuration
                        if (null !== $configEntity) {
                            // Offensive capabilities
                            $cityTroopsBefore[self::ARSENAL_OFFENSE] += $entityCount * ($configEntity->getDamage() * $configEntity->getAmmo());
                            
                            // Defensive capabilities
                            $cityTroopsBefore[self::ARSENAL_DEFENSE] += $entityCount * ($configEntity->getArmour() * $configEntity->getAgility());
                            
                            // Disperse the troops
                            for ($i = 1; $i <= $entityCount; $i++) {
                                $cityTroopsDispersed[] = array(
                                    $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ID], 
                                    $configEntity->getArmour() * $configEntity->getAgility()
                                );
                            }
                        }
                        break;
                }
            }
        }
        
        // Get the attacking entities data
        foreach ($finalConvoyPayload[self::PAYLOAD_ENTITIES] as $entityId => $attackerEntityRow) {
            // Invalid entity configuration
            if (!isset($attackerEntityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT])
                || !isset($attackerEntityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE])
                || !isset($attackerEntityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID])) {
                
                // Remove the item
                unset($finalConvoyPayload[self::PAYLOAD_ENTITIES][$entityId]);
                
                // Move on
                continue;
            }
            
            // Prepare the entities count
            $entityCount = intval($attackerEntityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT]);
            
            // Invalid number
            if ($entityCount <= 0) {
                continue;
            }
            
            // Ship/Unit
            switch ($attackerEntityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE]) {
                case Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT:
                case Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_SHIP:
                    $configEntity = Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT == $attackerEntityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE]
                        ? Stephino_Rpg_Config::get()->units()->getById($attackerEntityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID])
                        : Stephino_Rpg_Config::get()->ships()->getById($attackerEntityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]);
                    
                    // Get the unit configuration
                    if (null !== $configEntity) {
                        // Offensive capabilities
                        $attTroopsBefore[self::ARSENAL_OFFENSE] += $entityCount * ($configEntity->getDamage() * $configEntity->getAmmo());

                        // Defensive capabilities
                        $attTroopsBefore[self::ARSENAL_DEFENSE] += $entityCount * ($configEntity->getArmour() * $configEntity->getAgility());
                        
                        // Disperse the troops
                        for ($i = 1; $i <= $entityCount; $i++) {
                            $attTroopsDispersed[] = array(
                                $entityId, 
                                $configEntity->getArmour() * $configEntity->getAgility()
                            );
                        }
                        
                        // Store the loot size
                        $attTroopsLootBoxes[$entityId] = intval($configEntity->getLootBox());
                    }
                    break;
            }
        }
        
        // Store the initial convoy payload
        $attEntities = $finalConvoyPayload[self::PAYLOAD_ENTITIES];
        
        // Store a copy of the pre-attack layout
        $attTroopsAfter = $attTroopsBefore;
        $cityTroopsAfter = $cityTroopsBefore;
        
        // Shuffle the dispersed troops to randomly select remaining entities
        shuffle($attTroopsDispersed);
        shuffle($cityTroopsDispersed);
        
        // Siege
        $cityWalls[self::ARSENAL_DEFENSE] -= $attTroopsAfter[self::ARSENAL_OFFENSE];
        $attTroopsAfter[self::ARSENAL_DEFENSE] -= $cityWalls[self::ARSENAL_OFFENSE];
        
        // Prepare city walls
        $cityWallsIntact = true;
        
        // Gone in the first wave
        if ($attTroopsAfter[self::ARSENAL_DEFENSE] <= 0) {
            // Send the message
            $this->_attackMessage(
                $attUserId, 
                self::ATTACK_DEFEAT_CRUSHING, 
                array(
                    self::PAYLOAD_ENTITIES  => $attEntities,
                    self::PAYLOAD_RESOURCES => $finalConvoyPayload[self::PAYLOAD_RESOURCES]
                ), 
                $dataRow, 
                $buildingData,
                $cityWallsIntact
            );
            
            // Remove the attack
            $finalConvoyPayload = array();
        } else {
            // City walls breached
            if ($cityWalls[self::ARSENAL_DEFENSE] <= 0) {
                $cityWallsIntact = false;
                
                // Scale-down the invading army
                if ($attTroopsBefore[self::ARSENAL_DEFENSE] > 0) {
                    $attTroopsAfter[self::ARSENAL_OFFENSE] *= (
                        $attTroopsAfter[self::ARSENAL_DEFENSE] / $attTroopsBefore[self::ARSENAL_DEFENSE]
                    );
                }
                    
                // Inner-city attack
                $cityTroopsAfter[self::ARSENAL_DEFENSE] -= $attTroopsAfter[self::ARSENAL_OFFENSE];
                $attTroopsAfter[self::ARSENAL_DEFENSE] -= $cityTroopsAfter[self::ARSENAL_OFFENSE];
                
                // Gone in the second wave
                if ($attTroopsAfter[self::ARSENAL_DEFENSE] <= 0) {
                    // Send the message
                    $this->_attackMessage(
                        $attUserId,
                        $cityTroopsAfter[self::ARSENAL_DEFENSE] <= 0 
                            ? self::ATTACK_DEFEAT_BITTER 
                            : self::ATTACK_DEFEAT_HEROIC, 
                        array(
                            self::PAYLOAD_ENTITIES  => $attEntities,
                            self::PAYLOAD_RESOURCES => $finalConvoyPayload[self::PAYLOAD_RESOURCES]
                        ), 
                        $dataRow,
                        $buildingData,
                        $cityWallsIntact
                    );
                    
                    // Remove attack
                    $finalConvoyPayload = array();
                } else {
                    // Prepare the remaining army
                    $attStandingArmy = $this->_attackStandingArmy(
                        $attTroopsDispersed, 
                        $attTroopsAfter[self::ARSENAL_DEFENSE]
                    );
                    
                    // The invading army has won
                    if (count($attStandingArmy)) {
                        // Prepare the total loot size for each resource
                        $maxLootSize = 0;
                        
                        // Modify the payload entities
                        foreach ($finalConvoyPayload[self::PAYLOAD_ENTITIES] as $entityId => $attackerEntityRow) {
                            if (!isset($attStandingArmy[$entityId])) {
                                unset($finalConvoyPayload[self::PAYLOAD_ENTITIES][$entityId]);
                            } else {
                                $finalConvoyPayload[self::PAYLOAD_ENTITIES][$entityId][Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT] = $attStandingArmy[$entityId];
                                
                                // Add to the looting capacity
                                if (isset($attTroopsLootBoxes[$entityId]) && $attTroopsLootBoxes[$entityId] > 0) {
                                    $maxLootSize += ($attTroopsLootBoxes[$entityId] * $attStandingArmy[$entityId]);
                                }
                            }
                        }
                        
                        if ($maxLootSize > 0) {
                            // Loot!
                            $cityResources = array();
                            $defenderGold = null;

                            // Update the resource array
                            foreach($buildingData as $buildingKey => $buildingRow) {
                                // Initialize the gold transfer
                                if (null === $defenderGold) {
                                    $defenderGold = floatval($buildingRow[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD]);

                                    // Plenty of gold
                                    if ($defenderGold >= $maxLootSize) {
                                        // Grab all that you can
                                        $finalConvoyPayload[self::PAYLOAD_RESOURCES][Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD] = $maxLootSize;

                                        // Remove from user
                                        $defenderGold -= $maxLootSize;
                                    } else {
                                        // Heartless
                                        $finalConvoyPayload[self::PAYLOAD_RESOURCES][Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD] = $defenderGold;

                                        // Leave them with nothing
                                        $defenderGold = 0;
                                    }
                                }

                                // Update all city rows
                                $buildingData[$buildingKey][Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD] = $defenderGold;

                                // Current city
                                if ($buildingRow[Stephino_Rpg_Db_Table_Cities::COL_ID] == $cityId) {
                                    foreach (array(
                                        Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA,
                                        Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_BETA,
                                        Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_GAMMA,
                                        Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1,
                                        Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2,
                                    ) as $resourceKey) {
                                        // Initiate the resource transfer
                                        if (!isset($cityResources[$resourceKey])) {
                                            // Get available resources
                                            $cityResources[$resourceKey] = floatval($buildingRow[$resourceKey]);

                                            // Plenty of resources
                                            if ($cityResources[$resourceKey] >= $maxLootSize) {
                                                // Grab all that you can
                                                $finalConvoyPayload[self::PAYLOAD_RESOURCES][$resourceKey] = $maxLootSize;

                                                // Remove from user
                                                $cityResources[$resourceKey] -= $maxLootSize;
                                            } else {
                                                // Heartless
                                                $finalConvoyPayload[self::PAYLOAD_RESOURCES][$resourceKey] = $cityResources[$resourceKey];

                                                // Leave them with nothing
                                                $cityResources[$resourceKey] = 0;
                                            }
                                        }

                                        // Update all city rows
                                        $buildingData[$buildingKey][$resourceKey] = $cityResources[$resourceKey];
                                    }
                                }
                            }
                        }
                        
                        // Send the message
                        $this->_attackMessage(
                            $attUserId, 
                            self::ATTACK_VICTORY_HEROIC, 
                            array(
                                self::PAYLOAD_ENTITIES  => $attEntities,
                                self::PAYLOAD_RESOURCES => $finalConvoyPayload[self::PAYLOAD_RESOURCES]
                            ), 
                            $dataRow,
                            $buildingData,
                            $cityWallsIntact
                        );
                    } else {
                        // Send the message
                        $this->_attackMessage(
                            $attUserId, 
                            self::ATTACK_VICTORY_BITTER, 
                            array(
                                self::PAYLOAD_ENTITIES  => $attEntities,
                                self::PAYLOAD_RESOURCES => $finalConvoyPayload[self::PAYLOAD_RESOURCES]
                            ), 
                            $dataRow,
                            $buildingData,
                            $cityWallsIntact
                        );
                        
                        // Remove attack
                        $finalConvoyPayload = array();
                    }
                }
            } else {
                // Prepare the remaining army
                $attStandingArmy = $this->_attackStandingArmy($attTroopsDispersed, $attTroopsAfter[self::ARSENAL_DEFENSE]);
                
                // Return home with the remaining troops
                if (count($attStandingArmy)) {
                    // Modify the payload entities
                    foreach ($finalConvoyPayload[self::PAYLOAD_ENTITIES] as $entityId => $attackerEntityRow) {
                        if (!isset($attStandingArmy[$entityId])) {
                            unset($finalConvoyPayload[self::PAYLOAD_ENTITIES][$entityId]);
                        } else {
                            $finalConvoyPayload[self::PAYLOAD_ENTITIES][$entityId][Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT] = $attStandingArmy[$entityId];
                        }
                    }
                    
                    // Send the message
                    $this->_attackMessage(
                        $attUserId, 
                        self::ATTACK_DEFEAT_RETREAT, 
                        array(
                            self::PAYLOAD_ENTITIES  => $attEntities,
                            self::PAYLOAD_RESOURCES => $finalConvoyPayload[self::PAYLOAD_RESOURCES]
                        ), 
                        $dataRow,
                        $buildingData,
                        $cityWallsIntact
                    );
                } else {
                    // Send the message
                    $this->_attackMessage(
                        $attUserId, 
                        self::ATTACK_DEFEAT_CRUSHING, 
                        array(
                            self::PAYLOAD_ENTITIES  => $attEntities,
                            self::PAYLOAD_RESOURCES => $finalConvoyPayload[self::PAYLOAD_RESOURCES]
                        ), 
                        $dataRow,
                        $buildingData,
                        $cityWallsIntact
                    );
                    
                    // Remove attack
                    $finalConvoyPayload = array();
                }
            }
            
            // Prepare the remaining city army
            $cityStandingArmy = $this->_attackStandingArmy($cityTroopsDispersed, $cityTroopsAfter[self::ARSENAL_DEFENSE]);
            
            // Update the city troops; defensive buildings auto-recover instantly
            foreach ($supportEntitiesData as $entityKey => $entityRow) {
                // Our city
                if ($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CITY_ID] == $cityId) {
                    // Prepare the entity ID
                    $entityId = $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ID];
                    
                    // Update the entity count
                    $supportEntitiesData[$entityKey][Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT] = (
                        isset($cityStandingArmy[$entityId]) 
                            ? $cityStandingArmy[$entityId] 
                            : 0
                    );
                }
            }
        }
        
        return $finalConvoyPayload;
    }
    
    /**
     * Send the post-attack message to both parties
     * 
     * @param int     $attackerUserId  Attacker user ID
     * @param string  $attackStatus    Attack status
     * @param array   $attackerPayload Attacker payload
     * @param array   $dataRow         Convoy DB Row
     * @param array   $buildingData    Building Data
     * @param boolean $cityWallsIntact City walls are intact
     */
    protected function _attackMessage($attackerUserId, $attackStatus, $attackerPayload, $dataRow, &$buildingData, $cityWallsIntact) {
        // Prepare the attack status for the defender
        $defenderAttackStatus = self::ATTACK_VICTORY_HEROIC;
        
        // Impasse
        $attackResult = 0;
        $defenderAttackResult = 0;
        
        // Parse the battle result
        switch ($attackStatus) {
            case self::ATTACK_DEFEAT_RETREAT: 
                    $attackResult = -1;
                    $defenderAttackResult = 1;
                    $defenderAttackStatus = self::ATTACK_VICTORY_EASY;
                break;
            
            case self::ATTACK_VICTORY_EASY: 
                    $attackResult = 1;
                    $defenderAttackResult = -1;
                    $defenderAttackStatus = self::ATTACK_DEFEAT_RETREAT;
                break;

            case self::ATTACK_DEFEAT_CRUSHING: 
                    $attackResult = -1;
                    $defenderAttackResult = 1;
                    $defenderAttackStatus = self::ATTACK_VICTORY_CRUSHING;
                break;

            case self::ATTACK_VICTORY_CRUSHING: 
                    $attackResult = 1;
                    $defenderAttackResult = -1;
                    $defenderAttackStatus = self::ATTACK_DEFEAT_CRUSHING;
                break;

            case self::ATTACK_DEFEAT_HEROIC: 
                    $attackResult = -1;
                    $defenderAttackResult = 1;
                    $defenderAttackStatus = self::ATTACK_VICTORY_HEROIC;
                break;

            case self::ATTACK_VICTORY_HEROIC: 
                    $attackResult = 1;
                    $defenderAttackResult = -1;
                    $defenderAttackStatus = self::ATTACK_DEFEAT_HEROIC;
                break;

            // Impasse
            case self::ATTACK_DEFEAT_BITTER: 
                    $defenderAttackStatus = self::ATTACK_VICTORY_BITTER;
                break;

            // Impasse
            case self::ATTACK_VICTORY_BITTER: 
                    $defenderAttackStatus = self::ATTACK_DEFEAT_BITTER;
                break;
        }
        
        // Send messages to the attacker
        $this->_addMessage(
            Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_MILITARY, 
            self::ACTION_ATTACK, 
            $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_ID], 
            array(
                // Attacker
                true,
                // Status
                $attackStatus,
                // Payload
                $attackerPayload,
                // Convoy Row
                $dataRow,
                // City Walls
                $cityWallsIntact,
            ),
            false,
            $attackerUserId
        );
        
        // Send message to the defender
        $this->_addMessage(
            Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_MILITARY, 
            self::ACTION_ATTACK, 
            $dataRow[Stephino_Rpg_Db_Table_Convoys::COL_ID], 
            array(
                // Attacker
                false,
                // Status
                $defenderAttackStatus,
                // Payload
                $attackerPayload,
                // Convoy Row
                $dataRow,
                // City Walls
                $cityWallsIntact,
            )
        );
        
        // Adjust the attacker battle points
        if (is_array($attackerInfo = $this->getDb()->tableUsers()->getById($attackerUserId))) {
            $attackerInfoUpdate = null;
            switch($attackResult) {
                case -1:
                    $newScore = $attackerInfo[Stephino_Rpg_Db_Table_Users::COL_USER_SCORE] + Stephino_Rpg_Config::get()->core()->getScoreBattleDefeat();
                    if ($newScore < 0) {
                        $newScore = 0;
                    }
                    $attackerInfoUpdate = array(
                        Stephino_Rpg_Db_Table_Users::COL_USER_SCORE => $newScore,
                        Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_DEFEATS => $attackerInfo[Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_DEFEATS] + 1,
                    );
                    break;
                
                case 0:
                    $newScore = $attackerInfo[Stephino_Rpg_Db_Table_Users::COL_USER_SCORE] + Stephino_Rpg_Config::get()->core()->getScoreBattleDraw();
                    if ($newScore < 0) {
                        $newScore = 0;
                    }
                    $attackerInfoUpdate = array(
                        Stephino_Rpg_Db_Table_Users::COL_USER_SCORE => $newScore,
                        Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_DRAWS => $attackerInfo[Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_DRAWS] + 1,
                    );
                    break;
                
                case 1:
                    $newScore = $attackerInfo[Stephino_Rpg_Db_Table_Users::COL_USER_SCORE] + Stephino_Rpg_Config::get()->core()->getScoreBattleVictory();
                    if ($newScore < 0) {
                        $newScore = 0;
                    }
                    $attackerInfoUpdate = array(
                        Stephino_Rpg_Db_Table_Users::COL_USER_SCORE => $newScore,
                        Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_VICTORIES => $attackerInfo[Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_VICTORIES] + 1,
                    );
                    break;
            }
            
            if (null !== $attackerInfoUpdate) {
                $this->getDb()->tableUsers()->updateById(
                    $attackerInfoUpdate, 
                    $attackerUserId
                );
            }
        }
        
        // Adjust the defender battle points
        foreach ($buildingData as &$buildingRow) {
            switch($defenderAttackResult) {
                case -1:
                    $buildingRow[Stephino_Rpg_Db_Table_Users::COL_USER_SCORE] += Stephino_Rpg_Config::get()->core()->getScoreBattleDefeat();
                    $buildingRow[Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_DEFEATS]++;
                    break;
                
                case 0:
                    $buildingRow[Stephino_Rpg_Db_Table_Users::COL_USER_SCORE] += Stephino_Rpg_Config::get()->core()->getScoreBattleDraw();
                    $buildingRow[Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_DRAWS]++;
                    break;
                
                case 1:
                    $buildingRow[Stephino_Rpg_Db_Table_Users::COL_USER_SCORE] += Stephino_Rpg_Config::get()->core()->getScoreBattleVictory();
                    $buildingRow[Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_VICTORIES]++;
                    break;
            }
        }
        
        // Log the details
        Stephino_Rpg_Log::check() && Stephino_Rpg_Log::info('Attack: ' . $attackStatus);
        Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($attackerPayload, $dataRow);
    }
    
    /**
     * Calculate standing army
     * 
     * @param array $troopsDispersed Dispersed troops
     * @param int   $troopsDefense   Remaining defense points
     * @return array Associative array of Entity ID => Entity Count
     */
    protected function _attackStandingArmy($troopsDispersed, $troopsDefense) {
        $result = array();
        
        // Re-assemble the attacking army
        if ($troopsDefense > 0) {
            foreach($troopsDispersed as $entityData) {
                // Get the entity ID and Defense points
                list($entityId, $entityDefense) = $entityData;

                // Large defensive entities are destroyed first
                if ($entityDefense <= $troopsDefense) {
                    // Initialize the salvage array
                    if (!isset($result[$entityId])) {
                        $result[$entityId] = 0;
                    }
                    $result[$entityId]++;

                    // Account for saving this unit
                    $troopsDefense -= $entityDefense;

                    // No need to continue
                    if ($troopsDefense <= 0) {
                        break;
                    }
                }
            }
        }
        
        return $result;
    }
}

/*EOF*/