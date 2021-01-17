<?php

/**
 * Stephino_Rpg_Db_Table_Entities
 * 
 * @title      Table:Entities
 * @desc       Holds the units and ships information
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Db_Table_Entities extends Stephino_Rpg_Db_Table {
    
    // Item types
    const ENTITY_TYPE_UNIT = Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_UNIT;
    const ENTITY_TYPE_SHIP = Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_SHIP;
    
    /**
     * Entities Table Name
     */
    const NAME = 'entities';
    
    /**
     * Unit/Ship ID
     * 
     * @var int
     */
    const COL_ID = 'entity_id';
    
    /**
     * User ID
     * 
     * @var int
     */
    const COL_ENTITY_USER_ID = 'entity_user_id';
    
    /**
     * Island ID
     * 
     * @var int
     */
    const COL_ENTITY_ISLAND_ID = 'entity_island_id';
    
    /**
     * City ID
     * 
     * @var int
     */
    const COL_ENTITY_CITY_ID = 'entity_city_id';
    
    /**
     * Entity type
     * 
     * @var string <ul>
     *     <li><b>'u'</b> for Unit</li>
     *     <li><b>'s'</b> for Ship</li>
     * </ul>
     */
    const COL_ENTITY_TYPE = 'entity_type';
    
    /**
     * Configuration ID
     * 
     * @var int
     */
    const COL_ENTITY_CONFIG_ID = 'entity_config_id';
    
    /**
     * Count
     * 
     * @var int
     */
    const COL_ENTITY_COUNT = 'entity_count';
    
    /**
     * Table creation SQL statement
     * 
     * @return string
     */
    public function getCreateStatement() {
        return "CREATE TABLE `$this` (
    `" . self::COL_ID . "` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `" . self::COL_ENTITY_USER_ID . "` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_ENTITY_ISLAND_ID . "` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_ENTITY_CITY_ID . "` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_ENTITY_TYPE . "` char(1) NOT NULL DEFAULT '" . self::ENTITY_TYPE_UNIT . "',
    `" . self::COL_ENTITY_CONFIG_ID . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_ENTITY_COUNT . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    UNIQUE KEY `" . self::COL_ID . "` (`" . self::COL_ID . "`)
);";
    }
    
    /**
     * Create an entity
     * 
     * @param int $userId         User ID
     * @param int $islandId       Island ID
     * @param int $cityId         City ID
     * @param int $entityType     Entity Type, one of <ul>
     * <li>Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT</li>
     * <li>Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_SHIP</li>
     * </ul>
     * @param int $entityConfigId Entity Configuration ID
     * @param int $entityCount    Entity count
     * @return int|null New Entity ID or Null on error
     */
    public function create($userId, $islandId, $cityId, $entityType, $entityConfigId, $entityCount) {
        $result = null;
        
        do {
            // Invalid entity type
            if (!in_array($entityType, array(
                Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT,
                Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_SHIP,
            ))) {
                break;
            }
            
            // Check for uniqueness
            $entityRow = $this->getDb()->getWpDb()->get_results(
                "SELECT * FROM `$this`"
                . " WHERE `" . self::COL_ENTITY_CITY_ID . "` = '" . abs((int) $cityId) . "'"
                    . " AND `" . self::COL_ENTITY_TYPE . "` = '" . $entityType . "'"
                    . " AND `" . self::COL_ENTITY_CONFIG_ID . "` = '" . abs((int) $entityConfigId) . "'",
                ARRAY_A
            );
            if (is_array($entityRow) && count($entityRow)) {
                break;
            }
            
            // Failed insert
            if (!$this->getDb()->getWpDb()->insert(
                $this->getTableName(), 
                array(
                    self::COL_ENTITY_USER_ID     => abs((int) $userId),
                    self::COL_ENTITY_ISLAND_ID   => abs((int) $islandId),
                    self::COL_ENTITY_CITY_ID     => abs((int) $cityId),
                    self::COL_ENTITY_TYPE        => $entityType,
                    self::COL_ENTITY_CONFIG_ID   => abs((int) $entityConfigId),
                    self::COL_ENTITY_COUNT       => abs((int) $entityCount),
                )
            )) {
                break;
            }
            
            // Store the last insert ID
            $result = $this->getDb()->getWpDb()->insert_id;
        } while (false);
        
        return $result;
    }
    
    /**
     * Update multiple entities counts
     * 
     * @param array $entities Associative array of <br>
     * <i><b>(int)</b> Entity ID</i> => <i><b>(int)</b> Entity Count</i>
     * @return int|false Number of rows updated or false on error
     */
    public function setCounts($entities) {
        // Prepare the result
        $result = false;
        
        // Prepare the fields information
        $fieldsInfo = array();
        if (is_array($entities)) {
            foreach ($entities as $entityId => $entityCount) {
                $entityId = abs((int) $entityId);
                if ($entityId > 0) {
                    $fieldsInfo[$entityId] = array(
                        self::COL_ENTITY_COUNT => $entityCount < 0 ? 0 : $entityCount
                    );
                }
            }
        }
        
        // Invalid list
        if (!count($fieldsInfo)) {
            return false;
        }
        
        // Valid query produced
        if (null !== $multiUpdateQuery = Stephino_Rpg_Utils_Db::getMultiUpdate($fieldsInfo, $this->getTableName(), self::COL_ID)) {
            $result = $this->getDb()->getWpDb()->query($multiUpdateQuery);
        }
        
        return $result;
    }
    
    /**
     * Get all the entities (units and ships) garrisoned in this city
     * 
     * @param int $cityId City ID
     * @return array|null
     */
    public function getByCity($cityId) {
        $result = $this->getDb()->getWpDb()->get_results(
            "SELECT * FROM `$this`"
            . " WHERE `" . self::COL_ENTITY_CITY_ID . "` = '" . abs((int) $cityId) . "'",
            ARRAY_A
        );
        
        return is_array($result) && count($result) ? $result : null;
    }

    /**
     * Delete entities by city ID
     * 
     * @param int $cityId City ID
     * @return int|false The number of rows deleted or false on error
     */
    public function deleteByCity($cityId) {
        return $this->getDb()->getWpDb()->delete(
            $this->getTableName(),
            array(
                self::COL_ENTITY_CITY_ID => abs((int) $cityId),
            )
        );
    }
    
    /**
     * Delete entities by user ID
     * 
     * @param int $userId User ID
     * @return int|false The number of rows deleted or false on error
     */
    public function deleteByUser($userId) {
        return $this->getDb()->getWpDb()->delete(
            $this->getTableName(),
            array(
                self::COL_ENTITY_USER_ID => abs((int) $userId),
            )
        );
    }
}

/*EOF*/