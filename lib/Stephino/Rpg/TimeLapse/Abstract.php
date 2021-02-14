<?php

/**
 * Stephino_Rpg_TimeLapse_Abstract
 * 
 * @title      Abstract time-lapses
 * @desc       Handle time-lapse processes
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
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
            if (null !== $multiUpdateQuery = Stephino_Rpg_Utils_Db::getMultiUpdate(
                $updateInfo, $tableName, $tableIdentifier
            )) {
                $this->getDb()->getWpDb()->query($multiUpdateQuery);
            }
        }
        
        // Go through the removals
        foreach ($removals as $tableName => $removeInfo) {
            // Get the identifier column name
            $tableIdentifier = $removalStructure[$tableName];
            
            // Prepare the query
            if (null !== $multiDeleteQuery = Stephino_Rpg_Utils_Db::getMultiDelete(
                $removeInfo, $tableName, $tableIdentifier
            )) {
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
            foreach ($userMessages as $messageType => $messageData) {
                foreach ($messageData as $itemType => $itemInfo) {
                    foreach ($itemInfo as $itemId => list($stepTime, $itemData)) {
                        // Prepare the message subject
                        $messageSubject = '(' . esc_html__('empty', 'stephino-rpg') . ')';
                        switch ($messageType) {
                            case Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_RESEARCH:
                                $researchFieldConfig = Stephino_Rpg_Config::get()->researchFields()->getById(
                                    $itemData[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_CONFIG_ID]
                                );
                                
                                // Invalid research field configuration
                                if (null === $researchFieldConfig) {
                                    continue 2;
                                }
                                
                                // Store the subject
                                $messageSubject = sprintf(esc_html__('%s: research complete', 'stephino-rpg'), $researchFieldConfig->getName(true));
                                break;

                            case Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_ECONOMY:
                                switch ($itemType) {
                                    case Stephino_Rpg_TimeLapse_Convoys::ACTION_TRANSPORT:
                                        if ($itemData[0]) {
                                            $messageSubject = esc_html__('Transporter returned', 'stephino-rpg');
                                        } else {
                                            $messageSubject = esc_html__('Goods delivered', 'stephino-rpg');
                                        }
                                        break;
                                    
                                    case Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_BUILDING:
                                        $buildingConfig = Stephino_Rpg_Config::get()->buildings()->getById(
                                            $itemData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID]
                                        );
                                        
                                        // Invalid building configuration
                                        if (null === $buildingConfig) {
                                            continue 3;
                                        }
                                        $messageSubject = sprintf(esc_html__('%s: upgraded', 'stephino-rpg'), $buildingConfig->getName(true));
                                        break;
                                        
                                    case Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_UNIT:
                                        $unitConfig = Stephino_Rpg_Config::get()->units()->getById(
                                            $itemData[0][Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]
                                        );
                                        
                                        // Invalid unit configuration
                                        if (null === $unitConfig) {
                                            continue 3;
                                        }
                                        $messageSubject = sprintf(esc_html__('%s: recruited', 'stephino-rpg'), $unitConfig->getName(true));
                                        break;
                                        
                                    case Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_SHIP:
                                        $shipConfig = Stephino_Rpg_Config::get()->ships()->getById(
                                            $itemData[0][Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]
                                        );
                                        
                                        // Invalid ship configuration
                                        if (null === $shipConfig) {
                                            continue 3;
                                        }
                                        $messageSubject = sprintf(esc_html__('%s: built', 'stephino-rpg'), $shipConfig->getName(true));
                                        break;
                                    
                                }
                                break;

                            case Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_MILITARY:
                                switch ($itemType) {
                                    case Stephino_Rpg_TimeLapse_Convoys::ACTION_ATTACK:
                                        // Prepare the title
                                        list(, $convoyStatus) = $itemData;
                                        switch ($convoyStatus) {
                                            case Stephino_Rpg_TimeLapse_Convoys::ATTACK_DEFEAT_RETREAT: 
                                                $messageSubject = esc_html__('Retreat', 'stephino-rpg');
                                                break;

                                            case Stephino_Rpg_TimeLapse_Convoys::ATTACK_DEFEAT_CRUSHING: 
                                                $messageSubject = esc_html__('Crushing Defeat', 'stephino-rpg');
                                                break;

                                            case Stephino_Rpg_TimeLapse_Convoys::ATTACK_VICTORY_CRUSHING: 
                                                $messageSubject = esc_html__('Crushing Victory', 'stephino-rpg');
                                                break;

                                            case Stephino_Rpg_TimeLapse_Convoys::ATTACK_DEFEAT_HEROIC: 
                                                $messageSubject = esc_html__('Heroic Defeat', 'stephino-rpg');
                                                break;

                                            case Stephino_Rpg_TimeLapse_Convoys::ATTACK_VICTORY_HEROIC: 
                                                $messageSubject = esc_html__('Heroic Victory', 'stephino-rpg');
                                                break;

                                            case Stephino_Rpg_TimeLapse_Convoys::ATTACK_DEFEAT_BITTER: 
                                                $messageSubject = esc_html__('Bitter Defeat', 'stephino-rpg');
                                                break;

                                            case Stephino_Rpg_TimeLapse_Convoys::ATTACK_VICTORY_BITTER: 
                                                $messageSubject = esc_html__('Bitter Victory', 'stephino-rpg');
                                                break;
                                        }
                                    break;
                                
                                    case Stephino_Rpg_TimeLapse_Convoys::ACTION_SPY:
                                        list($transportReturned, $payloadArray) = $itemData;
                                        if (!$transportReturned) {
                                            if ($userId == $this->_userId && !is_array($payloadArray)) {
                                                $messageSubject = esc_html__('We caught a spy', 'stephino-rpg');
                                            } else {
                                                $messageSubject = (
                                                    is_array($payloadArray) 
                                                        ? esc_html__('Spy mission successful', 'stephino-rpg') 
                                                        : esc_html__('Spy mission failed', 'stephino-rpg')
                                                );
                                            }
                                        } else {
                                            $messageSubject = esc_html__('Our spy has returned', 'stephino-rpg');
                                        }
                                        break;
                                    
                                    default:
                                        $messageSubject = esc_html__('Our troops have returned', 'stephino-rpg');
                                }
                                break;
                                
                            case Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_DIPLOMACY:
                                switch($itemType) {
                                    case Stephino_Rpg_TimeLapse_Convoys::ACTION_COLONIZE:
                                        $messageSubject = esc_html__('Colonization', 'stephino-rpg');
                                        break;
                                    
                                    case Stephino_Rpg_TimeLapse_Queues::ACTION_PREMIUM_EXP:
                                        $messageSubject = esc_html__('Premium modifier expired', 'stephino-rpg');
                                        break;
                                    
                                    case Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_RESEARCH:
                                        $messageSubject = esc_html__('Discovery', 'stephino-rpg');
                                        break;
                                }
                                break;
                        }

                        // Prepare the message content
                        $messageContent = '(' . esc_html__('empty', 'stephino-rpg') . ')';

                        // Prepare the template name
                        $templateFileName = null;
                        switch ($messageType) {
                            case Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_MILITARY:
                                $templateFileName = Stephino_Rpg_TimeLapse::TEMPLATE_MILITARY;
                                break;
                            
                            case Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_ECONOMY:
                                $templateFileName = Stephino_Rpg_TimeLapse::TEMPLATE_ECONOMY;
                                break;
                            
                            case Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_DIPLOMACY:
                                $templateFileName = Stephino_Rpg_TimeLapse::TEMPLATE_DIPLOMACY;
                                break;
                            
                            case Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_RESEARCH:
                                $templateFileName = Stephino_Rpg_TimeLapse::TEMPLATE_RESEARCH;
                                break;
                        }
                        
                        // Start the buffer
                        if (null !== $templateFileName) {
                            // Start the buffer
                            ob_start();
                            
                            try {
                                // Load the template
                                require Stephino_Rpg_TimeLapse::getTemplatePath($templateFileName);
                            } catch (Exception $exc) {
                                Stephino_Rpg_Log::check() && Stephino_Rpg_Log::warning(
                                    "Timelapse_Abstract._sendMessages: {$exc->getMessage()}"
                                );
                            }
                            
                            // Store the result, removing extra spaces
                            $messageContent = trim(
                                preg_replace(
                                    array(
                                        '%[\r\n\t]+%',
                                        '% {2,}%',
                                    ), 
                                    ' ', 
                                    ob_get_clean()
                                )
                            );
                        }

                        // Add to the payload
                        if (strlen($messageContent)) {
                            $payload[] = array(
                                Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_TIME    => $stepTime,
                                Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_TO      => $userId,
                                Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_TYPE    => $messageType,
                                Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_SUBJECT => $messageSubject,
                                Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_CONTENT => $messageContent,
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
     * @param int               $userId   Current User ID
     */
    public function __construct(Stephino_Rpg_Db $dbObject, $userId) {
        // Store the DB object
        $this->_db = $dbObject;
        
        // Store the user data
        $this->_userId = $userId;
        
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