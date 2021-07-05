<?php

/**
 * Stephino_Rpg_Db_Table_Convoys
 * 
 * @title      Table:Convoys
 * @desc       Holds the convoys information
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Db_Table_Convoys extends Stephino_Rpg_Db_Table {
    
    // Item types
    const CONVOY_TYPE_ATTACK      = 'a';
    const CONVOY_TYPE_SPY         = 's';
    const CONVOY_TYPE_COLONIZER   = 'c';
    const CONVOY_TYPE_TRANSPORTER = 't';
    const CONVOY_TYPE_SENTRY      = 'y';
    
    // Convoy types
    const CONVOY_TYPES = array(
        self::CONVOY_TYPE_ATTACK,
        self::CONVOY_TYPE_SPY,
        self::CONVOY_TYPE_COLONIZER,
        self::CONVOY_TYPE_TRANSPORTER,
        self::CONVOY_TYPE_SENTRY,
    );
    
    /**
     * Convoys Table Name
     */
    const NAME = 'convoys';

    /**
     * Convoy ID
     * 
     * @var int
     */
    const COL_ID = 'convoy_id';
    
    /**
     * Origin User ID
     * 
     * @var int
     */
    const COL_CONVOY_FROM_USER_ID = 'convoy_from_user_id';
    
    /**
     * Destination User ID
     * 
     * @var int
     */
    const COL_CONVOY_TO_USER_ID = 'convoy_to_user_id';
    
    /**
     * Origin Island ID
     * 
     * @var int
     */
    const COL_CONVOY_FROM_ISLAND_ID = 'convoy_from_island_id';
    
    /**
     * Destination Island ID
     * 
     * @var int
     */
    const COL_CONVOY_TO_ISLAND_ID = 'convoy_to_island_id';
    
    /**
     * Origin City ID
     * 
     * @var int
     */
    const COL_CONVOY_FROM_CITY_ID = 'convoy_from_city_id';
    
    /**
     * Destination City ID
     * 
     * @var int
     */
    const COL_CONVOY_TO_CITY_ID = 'convoy_to_city_id';
    
    /**
     * Convoy Type
     * 
     * @var string <ul>
     *     <li><b>'a'</b> for Attack</li>
     *     <li><b>'s'</b> for Spy</li>
     *     <li><b>'c'</b> for Colonizer</li>
     *     <li><b>'t'</b> for Transporter</li>
     * </ul>
     */
    const COL_CONVOY_TYPE = 'convoy_type';
    
    /**
     * Convoy travel duration in seconds
     * 
     * @var int
     */
    const COL_CONVOY_TRAVEL_DURATION = 'convoy_travel_duration';
    
    /**
     * The convoy is traveling at maximum speed
     * 
     * @var int 0|1, default 0
     */
    const COL_CONVOY_TRAVEL_FAST = 'convoy_travel_fast';
    
    /**
     * Time
     * 
     * @var int UNIX timestamp
     */
    const COL_CONVOY_TIME = 'convoy_time';
    
    /**
     * Retreat time
     * 
     * @var int UNIX timestamp
     */
    const COL_CONVOY_RETREAT_TIME = 'convoy_retreat_time';
    
    /**
     * Payload
     * 
     * @var string JSON object
     */
    const COL_CONVOY_PAYLOAD = 'convoy_payload';

    /**
     * Table creation SQL statement
     * 
     * @return string
     */
    public function getCreateStatement() {
        return "CREATE TABLE `$this` (
    `" . self::COL_ID . "` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `" . self::COL_CONVOY_FROM_USER_ID . "` bigint(20) UNSIGNED NOT NULL,
    `" . self::COL_CONVOY_TO_USER_ID . "` bigint(20) UNSIGNED NOT NULL,
    `" . self::COL_CONVOY_FROM_ISLAND_ID . "` bigint(20) UNSIGNED NOT NULL,
    `" . self::COL_CONVOY_TO_ISLAND_ID . "` bigint(20) UNSIGNED NOT NULL,
    `" . self::COL_CONVOY_FROM_CITY_ID . "` bigint(20) UNSIGNED NOT NULL,
    `" . self::COL_CONVOY_TO_CITY_ID . "` bigint(20) UNSIGNED NOT NULL,
    `" . self::COL_CONVOY_TYPE . "` char(1) NOT NULL DEFAULT '" . self::CONVOY_TYPE_ATTACK . "',
    `" . self::COL_CONVOY_TRAVEL_DURATION . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_CONVOY_TRAVEL_FAST . "` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_CONVOY_TIME . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_CONVOY_RETREAT_TIME . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_CONVOY_PAYLOAD . "` text NOT NULL DEFAULT '',
    UNIQUE KEY `" . self::COL_ID . "` (`" . self::COL_ID . "`), 
    KEY `" . self::COL_CONVOY_FROM_USER_ID . "` (`" . self::COL_CONVOY_FROM_USER_ID . "`), 
    KEY `" . self::COL_CONVOY_TO_USER_ID . "` (`" . self::COL_CONVOY_TO_USER_ID . "`), 
    KEY `" . self::COL_CONVOY_FROM_CITY_ID . "` (`" . self::COL_CONVOY_FROM_CITY_ID . "`), 
    KEY `" . self::COL_CONVOY_TO_CITY_ID . "` (`" . self::COL_CONVOY_TO_CITY_ID . "`)
);";
    }
    
    /**
     * Create a convoy entry
     * 
     * @param int     $fromUserId        Origin City's User ID
     * @param int     $fromIslandId      Origin City's Island ID
     * @param int     $fromCityId        Origin City ID
     * @param int     $toUserId          Destination City's User ID
     * @param int     $toIslandId        Destination City's Island ID
     * @param int     $toCityId          Destination City ID
     * @param int     $traveDuration     Convoy travel duration in seconds
     * @param boolean $travelFast        Convoy traveling at max speed
     * @param int     $convoyTime        Convoy time (UNIX timestamp)
     * @param int     $convoyRetreatTime Convoy retreat time (UNIX timestamp)
     * @param array   $convoyPayload     Associative array of 
     * <ul>
     *     <li>Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES => [<i><b>int</b> Entiy ID</i> => [
     *         <ul>
     *             <li>Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT     => <b>int</b> Entity Count</li>
     *             <li>Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE      => <b>string</b> Entity Type</li>
     *             <li>Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID => <b>int</b> Entity Configuration ID</li>
     *         </ul>]]
     *     </li>
     *     <li>Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_DATA => (mixed)</li>
     *     <li>Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES => (array)</li>
     * </ul>
     * @param string  $convoyType        Convoy type, one of <ul>
     *     <li>Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_ATTACK</li>
     *     <li>Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_SPY</li>
     *     <li>Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_COLONIZER</li>
     *     <li>Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_TRANSPORTER</li>
     *     <li>Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_SENTRY</li>
     * </ul>
     * @return array|null Array of [<i>(int)</i>New convoy ID, <i>(array)</i> sanitized <b>$convoyEntities</b>] or Null on error
     */
    public function create($fromUserId, $fromIslandId, $fromCityId, $toUserId, $toIslandId, $toCityId, $traveDuration, 
        $travelFast, $convoyTime, $convoyRetreatTime, $convoyPayload, $convoyType = self::CONVOY_TYPE_ATTACK) {

        // Validate the convoy type
        if (!in_array($convoyType, self::CONVOY_TYPES)) {
            return null;
        }
        
        // Initialize the payload
        if (!is_array($convoyPayload)) {
            $convoyPayload = array();
        }
        
        // Remove invalid keys
        foreach (array_keys($convoyPayload) as $cpKey) {
            if (!in_array($cpKey, array(
                Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES,
                Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES,
                Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_DATA,
            ))) {
                unset($convoyPayload[$cpKey]);
            }
        }
        
        // Initialize the entities
        if (!isset($convoyPayload[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES])) {
            $convoyPayload[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES] = array();
        }
        
        // Sanitize the entities
        foreach ($convoyPayload[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES] as $entityId => &$entityRow) {
            do {
                if (is_numeric($entityId)
                    && is_array($entityRow)
                    && isset($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT])
                    && isset($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE])
                    && isset($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID])
                ) {
                    // Valid entity type
                    if (in_array($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE], array(
                        Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT,
                        Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_SHIP,
                    ))) {
                        if ($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT] > 0
                            && $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID] > 0) {
                            $entityRow = array(
                                Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT     => abs((int) $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT]),
                                Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE      => $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE],
                                Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID => abs((int) $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]),
                            );
                        }
                    }
                    break;
                }
                
                // Invalid entity
                unset($convoyPayload[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES][$entityId]);
                continue;
            } while(false);
        }
        
        // Invalid convoy entities payload
        if (!count($convoyPayload[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES]) && self::CONVOY_TYPE_SENTRY !== $convoyType) {
            return null;
        }
        
        // Prepare the row
        $rowData = array(
            self::COL_CONVOY_FROM_USER_ID    => abs((int) $fromUserId),
            self::COL_CONVOY_FROM_ISLAND_ID  => abs((int) $fromIslandId),
            self::COL_CONVOY_FROM_CITY_ID    => abs((int) $fromCityId),
            self::COL_CONVOY_TO_USER_ID      => abs((int) $toUserId),
            self::COL_CONVOY_TO_ISLAND_ID    => abs((int) $toIslandId),
            self::COL_CONVOY_TO_CITY_ID      => abs((int) $toCityId),
            self::COL_CONVOY_TYPE            => $convoyType,
            self::COL_CONVOY_TRAVEL_DURATION => abs((int) $traveDuration),
            self::COL_CONVOY_TRAVEL_FAST     => $travelFast ? 1 : 0,
            self::COL_CONVOY_TIME            => abs((int) $convoyTime),
            self::COL_CONVOY_RETREAT_TIME    => abs((int) $convoyRetreatTime),
            self::COL_CONVOY_PAYLOAD         => json_encode($convoyPayload),
        );
        
        // Prepare the payload
        $result = $this->getDb()->getWpDb()->query(
            Stephino_Rpg_Utils_Db::insert(
                $this->getTableName(), 
                $rowData
            )
        );
        
        // Insert
        if (false !== $result) {
            // Append the ID
            $rowData[self::COL_ID] = $this->getDb()->getWpDb()->insert_id;
            
            // Store in the time-lapse data set
            Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Convoys::KEY)->addRow($rowData);
        }
        
        // Mark the sentry as active (challenge in progress)
        $this->getDb()->tableUsers()->updateById(
            array(
                Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_ACTIVE => abs((int) $toUserId)
            ), 
            $fromUserId
        );
        
        // Get the new convoy ID and sanitized entities
        return (false !== $result ? array($this->getDb()->getWpDb()->insert_id, $convoyPayload[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES]) : null);
    }
    
    /**
     * Get the number of convoys this user is involved in
     * 
     * @param int $userId User ID
     * @return int
     */
    public function getCountByUser($userId) {
        $result = 0;
        
        // Sanitize the user ID
        $userId = abs((int) $userId);
        if ($userId > 0) {
            // Prepare the query
            $query = "SELECT COUNT(`" . self::COL_ID . "`) as `count` FROM `$this` " . PHP_EOL
                . "WHERE `" . self::COL_CONVOY_FROM_USER_ID  . "` = $userId"
                    . " OR `" . self::COL_CONVOY_TO_USER_ID  . "` = $userId";
            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($query . PHP_EOL);
            
            // Get the db row
            $dbRow = $this->getDb()->getWpDb()->get_row($query, ARRAY_A);

            // Valid result
            if (is_array($dbRow) && isset($dbRow['count'])) {
                $result = abs((int) $dbRow['count']);
            }
        }
        
        return $result;
    }
    
    /**
     * Get convoy information by island ID and island index
     * 
     * @param int $islandId    City Island ID
     * @param int $islandIndex City Island Index
     * @return array|null DB row or null on error
     */
    public function getByIslandAndIndex($islandId, $islandIndex) {
        // Prepare the result
        $result = null;
        
        // Get the rows
        $dbRows = $this->getDb()->getWpDb()->get_results(
            Stephino_Rpg_Utils_Db::selectAll(
                $this->getTableName(), 
                array(
                    self::COL_CONVOY_TO_ISLAND_ID => abs((int) $islandId)
                )
            ),
            ARRAY_A
        );

        // Filter by island index
        if (is_array($dbRows) && count($dbRows)) {
            foreach($dbRows as $dbRow) {
                // Decode the payload
                $payloadArray = json_decode($dbRow[self::COL_CONVOY_PAYLOAD], true);

                // Valid payload
                if (is_array($payloadArray) && isset($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_DATA])
                    && is_array($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_DATA]) 
                    && count($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_DATA]) >= 2) {

                    // Validate the island ID and island index
                    list($plIslandId, $plIslandIndex) = $payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_DATA];
                    
                    // Found the island index in a valid dataset
                    if ($plIslandIndex == $islandIndex && $plIslandId == $islandId) {
                        // Store the result
                        $result = $dbRow;
                        
                        // Stop the search
                        break;
                    }
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Get all the convoys to/from this user
     * 
     * @param int    $userId     User ID
     * @param string $convoyType (optional) Convoy Type; default <b>Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_ATTACK</b><br/>One of <ul>
     *     <li>Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_ATTACK</li>
     *     <li>Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_SENTRY</li>
     *     <li>Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_SPY</li>
     *     <li>Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_COLONIZER</li>
     *     <li>Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_TRANSPORTER</li>
     * </ul>
     * @return array[]|null
     */
    public function getByUserAndType($userId, $convoyType = self::CONVOY_TYPE_ATTACK) {
        // Sanitize
        if (!in_array($convoyType, self::CONVOY_TYPES)) {
            $convoyType = self::CONVOY_TYPE_ATTACK;
        }
        
        // Prepare the query
        $query = "SELECT * FROM `$this` " . PHP_EOL
            . "WHERE ("
                . " `" . self::COL_CONVOY_FROM_USER_ID  . "` = " . abs((int) $userId)
                . " OR `" . self::COL_CONVOY_TO_USER_ID  . "` = " . abs((int) $userId)
            . " ) AND `" . self::COL_CONVOY_TYPE . "` = '$convoyType'";
        Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($query . PHP_EOL);
        
        // Get the result
        $result = $this->getDb()->getWpDb()->get_results($query, ARRAY_A);
        
        return is_array($result) && count($result) ? $result : null;
    }
    
    /**
     * Get the sentry convoy sent by this user
     * 
     * @param int $userId Sentry owner ID
     * @return array|null
     */
    public function getSentryFromUser($userId) {
        return $this->getDb()->getWpDb()->get_row(
            Stephino_Rpg_Utils_Db::selectAll(
                $this->getTableName(), 
                array(
                    self::COL_CONVOY_FROM_USER_ID => abs((int) $userId),
                    self::COL_CONVOY_TYPE         => self::CONVOY_TYPE_SENTRY
                )
            ), 
            ARRAY_A
        );
    }
    
    /**
     * Get all the convoys to/from this user
     * 
     * @param int $userId User ID
     * @return array[]|null
     */
    public function getByUser($userId) {
        // Prepare the query
        $query = "SELECT * FROM `$this` " . PHP_EOL
            . "WHERE `" . self::COL_CONVOY_FROM_USER_ID  . "` = " . abs((int) $userId)
                . " OR `" . self::COL_CONVOY_TO_USER_ID  . "` = " . abs((int) $userId);
        Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($query . PHP_EOL);
        
        // Get the result
        $result = $this->getDb()->getWpDb()->get_results($query, ARRAY_A);
        
        return is_array($result) && count($result) ? $result : null;
    }
    
    /**
     * Delete convoys this user initiated (along with entities in the convoy payload)
     * 
     * @param int $userId User ID
     * @return int|false Number of rows deleted or false on error
     */
    public function deleteByUser($userId) {
        return $this->getDb()->getWpDb()->query(
            Stephino_Rpg_Utils_Db::delete(
                $this->getTableName(),
                array(
                    self::COL_CONVOY_FROM_USER_ID => abs((int) $userId)
                )
            )
        );
    }
}

/*EOF*/