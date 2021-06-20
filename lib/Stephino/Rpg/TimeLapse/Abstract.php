<?php

/**
 * Stephino_Rpg_TimeLapse_Abstract
 * 
 * @title      Abstract time-lapses
 * @desc       Handle time-lapse processes
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
abstract class Stephino_Rpg_TimeLapse_Abstract {

    /**
     * Time-Lapse::Abstract
     */
    const KEY = 'Abstract';
    
    // Deletion key
    const MAGIC_KEY_DELETE = '*delete';
    
    // Core keys
    const CORE_KEY_ORIGINAL = 'original';
    const CORE_KEY_CURRENT  = 'current';

    /**
     * DataBase object
     * 
     * @var Stephino_Rpg_Db 
     */
    private $_db = null;
    
    /**
     * Current user ID
     * 
     * @var int
     */
    protected $_userId = null;
    
    /**
     * Messages storage
     * 
     * @var array
     */
    protected $_messages = array();

    /**
     * Store the last checkpoint time
     * 
     * @var int
     */
    protected $_stepTime = null;
    
    /**
     * Current workers data; initialized at startup; keys are defined in each Stephino_Rpg_TimeLapse class
     * 
     * @var array
     */
    public static $_workerData = array();
    
    /**
     * Stepper
     * 
     * @param int $checkPointTime  UNIX timestamp
     * @param int $checkPointDelta Time difference in seconds from the last timestamp
     */
    abstract public function step($checkPointTime, $checkPointDelta);
    
    /**
     * Get the DataBase object
     * 
     * @return Stephino_Rpg_Db
     */
    protected function getDb() {
        return $this->_db;
    }
    
    /**
     * Time-lapse save action
     * Also removes the temporary MAGIC_KEY_DELETE flags from rows
     */
    public function save() {
        // Prepare the updates array
        $updates = array();
        
        // Prepare the removals array
        $removals = array();
        
        // Prepare the table update structure
        $updateStructure = $this->_getUpdateStructure();
        if (!is_array($updateStructure)) {
            $updateStructure = array();
        }
        
        // Prepare the table removals structure
        $removalStructure = $this->_getDeleteStructure();
        if (!is_array($removalStructure)) {
            $removalStructure = array();
        }
        
        // No need to load-up the memory if no update/removal structures defined
        if (count($updateStructure) || count($removalStructure)) {
            // Get the original data
            $dataOriginal = $this->getData(null, true);

            // Get the modified data
            $dataModified = $this->getData();
        }
        
        // Parse the modified data
        if (count($updateStructure)) {
            foreach ($dataModified as $dataKey => $dataRow) {
                foreach ($updateStructure as $tableName => $tableInfo) {
                    // Get the details
                    list($tableIdentifierColumn, $tableColumns) = $tableInfo;

                    // Table not prepared for update
                    if (!isset($updates[$tableName])) {
                        $updates[$tableName] = array();
                    }

                    // Found the table identifier column
                    if (isset($dataRow[$tableIdentifierColumn])) {
                        // Get the identifier
                        $tableIdentifier = $dataRow[$tableIdentifierColumn];

                        // Identifier not set for this table
                        if (!isset($updates[$tableName][$tableIdentifier])) {
                            // Prepare the table identifier columns
                            $updates[$tableName][$tableIdentifier] = array();

                            // Go through the required columns
                            foreach ($tableColumns as $tableColumn) {
                                if (isset($dataRow[$tableColumn])) {
                                    // Negative values not allowed
                                    if (!is_string($dataRow[$tableColumn]) && $dataRow[$tableColumn] < 0) {
                                        $dataRow[$tableColumn] = 0;
                                    }
                                    
                                    // Compare with original data, don't perform unnecessary operations
                                    if (isset($dataOriginal[$dataKey]) && $dataOriginal[$dataKey][$tableColumn] != $dataRow[$tableColumn]) {
                                        $updates[$tableName][$tableIdentifier][$tableColumn] = $dataRow[$tableColumn];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // Parse the removed data
        if (count($removalStructure)) {
            foreach ($dataOriginal as $dataKey => $dataRow) {
                // Entry marked for removal
                if (isset($dataModified[$dataKey]) && isset($dataModified[$dataKey][self::MAGIC_KEY_DELETE])) {
                    foreach ($removalStructure as $tableName => $tableIdentifier) {
                        // Don't perform unnecessary updates
                        if (isset($updates[$tableName]) && isset($updates[$tableName][$dataRow[$tableIdentifier]])) {
                            unset($updates[$tableName][$dataRow[$tableIdentifier]]);
                        }
                        
                        // Initialize the array
                        if (!isset($removals[$tableName])) {
                            $removals[$tableName] = array();
                        }

                        // Store the ID that needs to be removed
                        $removals[$tableName][] = $dataRow[$tableIdentifier];
                    }
                    
                    // Remove the row
                    unset($dataModified[$dataKey]);
                }
            }
        }
        
        // Go through the updates
        foreach ($updates as $tableName => $updateInfo) {
            // Get the identifier column name
            $tableIdentifier = $updateStructure[$tableName][0];
            
            // Prepare the query
            if (null !== $multiUpdateQuery = Stephino_Rpg_Utils_Db::multiUpdate($tableName, $tableIdentifier, $updateInfo, false)) {
                $this->getDb()->getWpDb()->query($multiUpdateQuery);
            }
        }
        
        // Go through the removals
        foreach ($removals as $tableName => $removeInfo) {
            // Get the identifier column name
            $tableIdentifier = $removalStructure[$tableName];
            
            // Prepare the query
            if (null !== $multiDeleteQuery = Stephino_Rpg_Utils_Db::multiDelete($tableName, $tableIdentifier, $removeInfo)) {
                $this->getDb()->getWpDb()->query($multiDeleteQuery);
            }
        }
        
        // Create the messages
        $this->_sendMessages();
        
        // Update the data with deleted rows
        $this->setData(static::KEY, $dataModified);
    }
    
    /**
     * Send the messages associated with this time-lapse task
     */
    private function _sendMessages() {
        // Nothing to do
        if (!count($this->_messages)) {
            return;
        }
        
        // Prepare the payload
        $payload = array();
        
        // Go through the messages
        foreach ($this->_messages as $userId => $userMessages) {
            foreach ($userMessages as $messageType => $notifData) {
                if (!in_array($messageType, array(
                    Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_MILITARY,
                    Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_ECONOMY,
                    Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_DIPLOMACY,
                    Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_RESEARCH,
                ))) {
                    continue;
                }
                
                foreach ($notifData as $itemType => $itemInfo) {
                    foreach ($itemInfo as $itemId => list($stepTime, $itemData)) {
                        // Prepare the notification arguments
                        $notifTemplate = null;
                        $notifData = null;
                        
                        switch ($messageType) {
                            case Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_RESEARCH:
                                switch ($itemType) {
                                    case Stephino_Rpg_TimeLapse_Queues::ACTION_RESEARCH_UNLOCK:
                                        if (is_array($itemData) && count($itemData)) {
                                            $notifTemplate = Stephino_Rpg_Db_Model_Messages::TEMPLATE_TIMELAPSE_NOTIF_RESEARCH_UNLOCK;
                                            $notifData = array(
                                                // unlockedItems
                                                $itemData
                                            );
                                        }
                                        break;
                                    
                                    case Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_RESEARCH:
                                        $researchFieldConfig = Stephino_Rpg_Config::get()->researchFields()->getById(
                                            $itemData[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_CONFIG_ID]
                                        );
                                        if (null !== $researchFieldConfig) {
                                            $notifTemplate = Stephino_Rpg_Db_Model_Messages::TEMPLATE_TIMELAPSE_NOTIF_RESEARCH_DONE;
                                            $notifData = array(
                                                // researchFieldConfigId
                                                $researchFieldConfig->getId(),
                                                // researchFieldLevel
                                                (int) $itemData[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_LEVEL],
                                            );
                                        }
                                        break;
                                    
                                }
                                break;

                            case Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_ECONOMY:
                                switch ($itemType) {
                                    case Stephino_Rpg_TimeLapse_Convoys::ACTION_TRANSPORT:
                                        $notifTemplate = Stephino_Rpg_Db_Model_Messages::TEMPLATE_TIMELAPSE_NOTIF_ECONOMY_TRANSPORT;
                                        $notifData = array(
                                            // transportReturned
                                            !!$itemData[0],
                                            // payloadArray
                                            $itemData[1],
                                            // fromCityId
                                            (int) $itemData[2][Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_CITY_ID],
                                            // toCityId
                                            (int) $itemData[2][Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID],
                                        );
                                        break;
                                    
                                    case Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_BUILDING:
                                        $buildingConfig = Stephino_Rpg_Config::get()->buildings()->getById(
                                            $itemData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID]
                                        );
                                        if (null !== $buildingConfig) {
                                            $notifTemplate = Stephino_Rpg_Db_Model_Messages::TEMPLATE_TIMELAPSE_NOTIF_ECONOMY_BUILDING;
                                            $notifData = array(
                                                // buildingConfigId
                                                $buildingConfig->getId(),
                                                // buildingLevel
                                                (int) $itemData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL],
                                                // buildingCityId
                                                (int) $itemData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CITY_ID],
                                            );
                                        }
                                        break;
                                        
                                    case Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_UNIT:
                                    case Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_SHIP:
                                        $entityConfig = Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_UNIT === $itemType
                                            ? Stephino_Rpg_Config::get()->units()->getById(
                                                $itemData[0][Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]
                                            )
                                            : Stephino_Rpg_Config::get()->ships()->getById(
                                                $itemData[0][Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]
                                            );
                                        if (null !== $entityConfig) {
                                            $notifTemplate = Stephino_Rpg_Db_Model_Messages::TEMPLATE_TIMELAPSE_NOTIF_ECONOMY_ENTITY;
                                            $notifData = array(
                                                // entityConfigId
                                                $entityConfig->getId(),
                                                // entityType
                                                $itemType,
                                                // entityCityId
                                                (int) $itemData[0][Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CITY_ID],
                                            );
                                        }
                                        break;
                                }
                                break;

                            case Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_MILITARY:
                                switch ($itemType) {
                                    case Stephino_Rpg_TimeLapse_Convoys::ACTION_ATTACK:
                                        $notifTemplate = Stephino_Rpg_Db_Model_Messages::TEMPLATE_TIMELAPSE_NOTIF_MILITARY_ATTACK;
                                        $notifData = array(
                                            // attacker
                                            !!$itemData[0],
                                            // attackStatus
                                            $itemData[1],
                                            // payloadArray
                                            $itemData[2],
                                            // fromCityId
                                            (int) $itemData[3][Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_CITY_ID],
                                            // toCityId
                                            (int) $itemData[3][Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID],
                                            // cityWalls
                                            !!$itemData[4],
                                        );
                                    break;
                                
                                    case Stephino_Rpg_TimeLapse_Convoys::ACTION_SPY:
                                        $notifTemplate = Stephino_Rpg_Db_Model_Messages::TEMPLATE_TIMELAPSE_NOTIF_MILITARY_SPY;
                                        $notifData = array(
                                            // transportReturned
                                            $itemData[0],
                                            // payloadArray
                                            $itemData[1],
                                            // fromCityId
                                            (int) $itemData[2][Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_CITY_ID],
                                            // toCityId
                                            (int) $itemData[2][Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID],
                                            // currentUser
                                            $userId == $this->_userId
                                        );
                                        break;
                                    
                                    default:
                                        $notifTemplate = Stephino_Rpg_Db_Model_Messages::TEMPLATE_TIMELAPSE_NOTIF_MILITARY_RETURN;
                                        $notifData = array(
                                            // payloadArray
                                            $itemData[2],
                                            // fromCityId
                                            (int) $itemData[3][Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_CITY_ID],
                                            // toCityId
                                            (int) $itemData[3][Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID],
                                        );
                                }
                                break;
                                
                            case Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_DIPLOMACY:
                                switch($itemType) {
                                    case Stephino_Rpg_TimeLapse_Convoys::ACTION_COLONIZE:
                                        $notifTemplate = Stephino_Rpg_Db_Model_Messages::TEMPLATE_TIMELAPSE_NOTIF_DIPLOMACY_COLONY;
                                        $notifData = array(
                                            // itemDataCityInfo
                                            $itemData[0],
                                        );
                                        break;
                                    
                                    case Stephino_Rpg_TimeLapse_Queues::ACTION_PREMIUM_EXP:
                                        $premiumModifierConfig = Stephino_Rpg_Config::get()->premiumModifiers()->getById(
                                            $itemData[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID]
                                        );
                                        if (null !== $premiumModifierConfig) {
                                            $notifTemplate = Stephino_Rpg_Db_Model_Messages::TEMPLATE_TIMELAPSE_NOTIF_DIPLOMACY_PREMIUM;
                                            $notifData = array(
                                                // premiumModifierConfigId
                                                (int) $itemData[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID],
                                                // premiumDuration
                                                (int) $itemData[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_DURATION],
                                                // premiumQuantity
                                                (int) $itemData[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_QUANTITY],
                                            );
                                        }
                                        break;
                                    
                                    case Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_RESEARCH:
                                        $notifTemplate = Stephino_Rpg_Db_Model_Messages::TEMPLATE_TIMELAPSE_NOTIF_DIPLOMACY_DISCOVERY;
                                        $notifData = array(
                                            // researchFieldConfigId
                                            $itemData
                                        );
                                        break;
                                    
                                    case Stephino_Rpg_TimeLapse_Convoys::ACTION_CHALLENGE:
                                        $notifTemplate = Stephino_Rpg_Db_Model_Messages::TEMPLATE_TIMELAPSE_NOTIF_DIPLOMACY_CHALLENGE;
                                        $notifData = array(
                                            // sentryReturned
                                            $itemData[0],
                                            // payloadArray (challenge type, levels, challenge successful)
                                            $itemData[1],
                                            // fromCityId
                                            (int) $itemData[2][Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_CITY_ID],
                                            // toCityId
                                            (int) $itemData[2][Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID],
                                        );
                                        break;
                                }
                                break;
                        }

                        // Append the payload; compatible with Stephino_Rpg_Db_Model_Messages::_parse()
                        if (null !== $notifTemplate && is_array($notifData)) {
                            $payload[] = array(
                                Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_TO      => $userId,
                                Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_TYPE    => $messageType,
                                Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_SUBJECT => $notifTemplate,
                                Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_CONTENT => json_encode($notifData),
                                Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_TIME    => $stepTime,
                            );
                        }
                    }
                }
            }
        }
        
        // Reset the message counter
        $this->_messages = array();
        
        // Prepare the query
        $this->getDb()->modelMessages()->sendMultiple($payload);
    }
    
    /**
     * Append to the message queue
     * 
     * @param string  $messageType Message type, one of <ul>
     * <li>Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_RESEARCH</li>
     * <li>Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_ECONOMY</li>
     * <li>Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_MILITARY</li>
     * <li>Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_DIPLOMACY</li>
     * </ul>
     * @param string  $itemType    Item type
     * @param int     $itemId      Item ID
     * @param mixed   $itemData    Item data; treated as an integer if <b>$incremental</b> == true.
     * @param boolean $incremental (optional) Is item quantity incremental? default <b>false</b> 
     * @param int     $userId      (optional) Recepient user ID; default <b>null</b> for current user
     * @return boolean False it invalid message type
     */
    protected function _addMessage($messageType, $itemType, $itemId, $itemData, $incremental = false, $userId = null) {
        $result = false;
        do {
            // Validte the message type
            if (!in_array($messageType, array(
                Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_RESEARCH,
                Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_ECONOMY,
                Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_MILITARY,
                Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_DIPLOMACY,
            ))) {
                // Invalid message type
                break;
            }

            // Get the user ID
            if (null === $userId) {
                $userId = $this->_userId;
            } else {
                $userId = intval($userId);

                // Invalid user provided
                if ($userId <= 0) {
                    break;
                }
            }

            // Initialize the storage
            if (!isset($this->_messages[$userId])) {
                $this->_messages[$userId] = array();
            }
            if (!isset($this->_messages[$userId][$messageType])) {
                $this->_messages[$userId][$messageType] = array();
            }
            if (!isset($this->_messages[$userId][$messageType][$itemType])) {
                $this->_messages[$userId][$messageType][$itemType] = array();
            }

            // Incremental
            if ($incremental) {
                if (!isset($this->_messages[$userId][$messageType][$itemType][$itemId])) {
                    $this->_messages[$userId][$messageType][$itemType][$itemId] = array(
                        $this->_stepTime,
                        0
                    );
                }

                // Increment the item quantity
                $this->_messages[$userId][$messageType][$itemType][$itemId][1] += intval($itemData);
            } else {
                // Store the item level
                $this->_messages[$userId][$messageType][$itemType][$itemId] = array(
                    $this->_stepTime,
                    $itemData
                );
            }
            
            $result = true;
        } while(false);
        
        return $result;
    }
    
    /**
     * Get the table structure that needs to be updated on save()
     */
    abstract protected function _getUpdateStructure();
    
    /**
     * Get the definition for removals on save()
     */
    abstract protected function _getDeleteStructure();
    
    /**
     * Initialize the worker data
     */
    abstract protected function _initData();
    
    /**
     * Get the checkpoints for this time-lapse
     * 
     * @param array $userId      Current user ID
     * @param int   $currentTick UNIX timestamp
     * @return array Unassociative array of <ul>
     * <li>(int) UNIX timestamp</li>
     * <li>(int) Time difference in seconds from the last valid timestamp</li>
     * </ul>
     */
    public static function getCheckpoints($userId, $currentTick) {
        // Prepare the result
        $result = array();
        $coreKey = self::CORE_KEY_CURRENT;
        $maxTickAge = Stephino_Rpg_Config::get()->core()->getCronMaxAge() * 86400;
        do {
            // Invalid user ID
            if (null === $userId) {
                break;
            }
            
            // Invalid worker data
            if (null === $workerData = (
                isset(self::$_workerData[$coreKey]) && isset(self::$_workerData[$coreKey][$userId]) 
                    ? self::$_workerData[$coreKey][$userId] 
                    : null
            )) {
                break;
            }
            
            // Prepare the last tick mark
            $lastTick = null;
            
            // Convert to integer
            $currentTick = intval($currentTick);
            
            // Prepare the timestamps
            $timeStamps = array($currentTick);
            
            // Go through the worker data
            foreach ($workerData as $workerName => $workerItems) {
                // Prepare the columns to look for
                $checkpointColumns = array();
                switch ($workerName) {
                    case Stephino_Rpg_TimeLapse_Resources::KEY:
                        $checkpointColumns = array(
                            Stephino_Rpg_Db_Table_Users::COL_USER_LAST_TICK
                        );
                        break;
                    
                    case Stephino_Rpg_TimeLapse_Queues::KEY:
                        $checkpointColumns = array(
                            Stephino_Rpg_Db_Table_Queues::COL_QUEUE_TIME,
                        );
                        break;
                    
                    case Stephino_Rpg_TimeLapse_Convoys::KEY:
                        $checkpointColumns = array(
                            Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TIME,
                            Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_RETREAT_TIME,
                        );
                        break;
                }
                
                // Go through the details
                foreach ($workerItems as $workerItemArray) {
                    // Go through the rows
                    foreach ($workerItemArray as $dbColumn => $dbValue) {
                        // Storable column
                        if (in_array($dbColumn, $checkpointColumns)) {
                            if (Stephino_Rpg_TimeLapse_Resources::KEY === $workerName) {
                                if (null === $lastTick) {
                                    $lastTick = intval($dbValue);
                                }
                            } else {
                                // Convert to integer
                                $newTimeStamp = intval($dbValue);
                                
                                // Must be a value in the past x days
                                if ($newTimeStamp >= $currentTick - $maxTickAge && $newTimeStamp <= $currentTick && !in_array($dbValue, $timeStamps)) {
                                    $timeStamps[] = intval($dbValue);
                                }
                            }
                        }
                    }
                }
            }
            
            // Very old account or improperly initialized; maximum x days behind
            if (null === $lastTick || $lastTick < $currentTick - $maxTickAge) {
                $lastTick = $currentTick - $maxTickAge;
            }
            
            // Set into the future
            if ($lastTick > $currentTick) {
                $lastTick = $currentTick;
            }
            
            // Sort the timestamps
            sort($timeStamps);
            
            // Re-index the array
            $timeStamps = array_values($timeStamps);

            // Go through the data
            foreach ($timeStamps as $tsKey => $timeStamp) {
                // Prepare the delta
                $timeStampDelta = 0;
                
                // Valid last tick
                if ($timeStamp > $lastTick) {
                    // Prepare the last delta
                    $previousDelta = isset($result[$tsKey - 1]) ? $result[$tsKey - 1][1] : 0;
                    
                    // Store the new value
                    $timeStampDelta = $timeStamp - $lastTick - $previousDelta;
                }
                
                // Append to the result
                $result[$tsKey] = array(
                    // TimeStamp
                    $timeStamp,
                    
                    // TimeStamp Delta
                    $timeStampDelta
                );
            }
        } while(false);
        
        return $result;
    }
    
    /**
     * Time-Lapse
     * 
     * @param Stephino_Rpg_Db $dbObject DataBase object
     * @param int             $userId   Current User ID
     */
    public function __construct(Stephino_Rpg_Db $dbObject, $userId) {
        // Store the DB object
        $this->_db = $dbObject;
        
        // Store the user data
        $this->_userId = abs((int) $userId);
        
        // Store the worker data
        $this->setData(static::KEY, $this->_initData());
        
        // Prepare the original store
        if (!isset(self::$_workerData[self::CORE_KEY_ORIGINAL])) {
            self::$_workerData[self::CORE_KEY_ORIGINAL] = array();
        }
        
        // Initialize the user id
        if (!isset(self::$_workerData[self::CORE_KEY_ORIGINAL][$this->_userId])) {
            self::$_workerData[self::CORE_KEY_ORIGINAL][$this->_userId] = array();
        }
        
        // Store the original value for later read-only use
        self::$_workerData[self::CORE_KEY_ORIGINAL][$this->_userId][static::KEY] = $this->getData(static::KEY);
    }
    
    /**
     * Get the stored data by key
     * 
     * @param string  $key      Data Key
     * @param boolean $original (optional) Whether to work on the original cache or the current version; 
     * default <b>false</b>
     * @return mixed|null
     */
    public function getData($key = null, $original = false) {
        // Set the default key
        if (null === $key) {
            $key = static::KEY;
        }

        // Prepare the core key
        $coreKey = $this->_getCoreKey($original);
        
        // Search for the value
        if (isset(self::$_workerData[$coreKey]) && isset(self::$_workerData[$coreKey][$this->_userId])) {
            if (isset(self::$_workerData[$coreKey][$this->_userId][$key])) {
                reset(self::$_workerData[$coreKey][$this->_userId][$key]);
                return self::$_workerData[$coreKey][$this->_userId][$key];
            }
        }
        
        // Nothing found
        return null;
    }

    /**
     * Update references
     * 
     * @param string $idColumnName  Table ID column name
     * @param mixed  $idColumnValue Table ID column value
     * @param string $columnName    Affected column name
     * @param mixed  $columnValue   New column value
     * @return boolean
     */
    public function updateRef($idColumnName, $idColumnValue, $columnName, $columnValue) {
        // Get the data
        $data = $this->getData();
        
        // No data found
        if (null === $data || !is_array($data)) {
            return false;
        }
        
        // Update all affected rows
        foreach ($data as &$dataRow) {
            if (isset($dataRow[$idColumnName]) && $dataRow[$idColumnName] == $idColumnValue) {
                if (isset($dataRow[$columnName])) {
                    $dataRow[$columnName] = $columnValue;
                }
            }
        }
        
        // Store the data
        $this->setData(static::KEY, $data);
        return true;
    }
    
    /**
     * Append a row to the final data
     * 
     * @param array $rowData Data Row
     * @return boolean
     */
    public function addRow($rowData) {
        // Get the data
        $data = $this->getData();
        
        // Invalid row
        if (!is_array($rowData)) {
            return false;
        }
        
        // Initialize the data
        if (null === $data || !is_array($data)) {
            $data = array();
        }
        
        // Append the new row
        $data[] = $rowData;
        
        // Store the data
        $this->setData(static::KEY, $data);
        return true;
    }
    
    /**
     * Set a commonly available data
     * 
     * @param string  $key   Data Key
     * @param mixed   $value Data Value
     */
    public function setData($key, $value) {
        // Prepare the core key
        $coreKey = $this->_getCoreKey(false);
        
        // Set the key
        if (!isset(self::$_workerData[$coreKey])) {
            self::$_workerData[$coreKey] = array();
        }
        
        // Initialize the user id
        if (!isset(self::$_workerData[$coreKey][$this->_userId])) {
            self::$_workerData[$coreKey][$this->_userId] = array();
        }
        self::$_workerData[$coreKey][$this->_userId][$key] = $value;
    }
    
    /**
     * Get the core key for the worker data cache
     * 
     * @param boolean $original Whether to work on the original cache or the current version
     * @return string
     */
    protected function _getCoreKey($original = false) {
        return $original ? self::CORE_KEY_ORIGINAL : self::CORE_KEY_CURRENT;
    }
}

/*EOF*/