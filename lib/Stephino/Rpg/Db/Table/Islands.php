<?php

/**
 * Stephino_Rpg_Db_Table_Islands
 * 
 * @title      Table:Islands
 * @desc       Holds the islands information
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Db_Table_Islands extends Stephino_Rpg_Db_Table {
    
    /**
     * Islands Table NAme
     */
    const NAME = 'islands';
    
    /**
     * Island ID
     * 
     * @var int
     */
    const COL_ID = 'island_id';
    
    /**
     * Name
     * 
     * @var string 128 characters
     */
    const COL_ISLAND_NAME = 'island_name';
    
    /**
     * Configuration ID
     * 
     * @var int
     */
    const COL_ISLAND_CONFIG_ID = 'island_config_id';
    
    /**
     * Island Statue Configuration ID
     * 
     * @var int
     */
    const COL_ISLAND_STATUE_CONFIG_ID = 'island_statue_config_id';
    
    /**
     * Island Statue Level
     * 
     * @var int
     */
    const COL_ISLAND_STATUE_LEVEL = 'island_statue_level';
    
    /**
     * Island is full
     * 
     * @var int 0|1, default 0
     */
    const COL_ISLAND_IS_FULL = 'island_is_full';
    
    /**
     * Table creation SQL statement
     * 
     * @return string
     */
    public function getCreateStatement() {
        return "CREATE TABLE `$this` (
    `" . self::COL_ID . "` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `" . self::COL_ISLAND_NAME . "` varchar(128) NOT NULL,
    `" . self::COL_ISLAND_CONFIG_ID . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_ISLAND_STATUE_CONFIG_ID . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_ISLAND_STATUE_LEVEL . "` int(11) UNSIGNED NOT NULL DEFAULT '1',
    `" . self::COL_ISLAND_IS_FULL . "` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    UNIQUE KEY `" . self::COL_ID . "` (`" . self::COL_ID . "`)
);";
    }
    
    /**
     * Create an island
     * 
     * @param int $islandConfigId Island Configuration ID
     * @param int $statueConfigId Island Statue Configuration ID
     * @return int|null New Island ID or Null on error
     */
    public function create($islandConfigId, $statueConfigId) {
        $result = $this->getDb()->getWpDb()->query(
            Stephino_Rpg_Utils_Db::insert(
                $this->getTableName(), 
                array(
                    self::COL_ISLAND_CONFIG_ID        => abs((int) $islandConfigId),
                    self::COL_ISLAND_STATUE_CONFIG_ID => abs((int) $statueConfigId)
                )
            )
        );
        
        // Get the new island ID
        return (false !== $result ? $this->getDb()->getWpDb()->insert_id : null);
    }
    
    /**
     * Get the islands around this specified point; used when provisioning a world view map
     * 
     * @param int   $coordX      X Coordinate
     * @param int   $coordY      Y Coordinate
     * @param int   $radius      (optional) Radius; default <b>3</b>
     * @param array $excludedIds (optional) Array of excluded IDs; default <b>[]</b>
     * @return array|null Array of arrays or Null on error
     */
    public function getIslands($coordX, $coordY, $radius = 3, $excludedIds = array()) {
        // Sanitize the radius
        $radius = abs((int) $radius);
        
        // Prepare the island IDs
        $islandIds = array();
        for ($x = $coordX - $radius; $x <= $coordX + $radius; $x++) {
            for ($y = $coordY - $radius; $y <= $coordY + $radius; $y++) {
                $islandId = Stephino_Rpg_Utils_Math::getSnakeLength($x, $y);
                if (0 != $islandId && !in_array($islandId, $excludedIds)) {
                    $islandIds[] = $islandId;
                }
            }
        }
        
        // Nothing to select
        if (!count($islandIds)) {
            return null;
        }
        
        return $this->getByIds($islandIds);
    }
    
    /**
     * Get the ID of a random island that still has empty slots
     * 
     * @return [int,Stephino_Rpg_Config_Island|null,Stephino_Rpg_Config_IslandStatue|null]|null
     */
    public function getRandom() {
        $result = null;
        
        // Get a random island by ID
        $islandRows = $this->getDb()->getWpDb()->get_results(
            Stephino_Rpg_Utils_Db::selectAll(
                $this->getTableName(), 
                array(
                    self::COL_ISLAND_IS_FULL => 0,
                ),
                1000
            ),
            ARRAY_A
        );

        // Found empty islands
        if (is_array($islandRows) && count($islandRows)) {
            // Get a random key
            $key = array_rand($islandRows);

            // Get the island ID
            if (isset($islandRows[$key][self::COL_ISLAND_CONFIG_ID]) && isset($islandRows[$key][self::COL_ISLAND_STATUE_CONFIG_ID])) {
                $result = array(
                    $islandRows[$key][self::COL_ID], 
                    Stephino_Rpg_Config::get()->islands()->getById($islandRows[$key][self::COL_ISLAND_CONFIG_ID]),
                    Stephino_Rpg_Config::get()->islandStatues()->getById($islandRows[$key][self::COL_ISLAND_STATUE_CONFIG_ID]),
                );
            }
        }
        
        // Nothing found
        return $result;
    }
    
    /**
     * Mark island(s) as full
     * 
     * @param int|int[]     $islandIds Island ID or list of island IDs
     * @param boolean $full (optional) Full flag; default <b>true</b>
     * @return int|false number of rows updated or false on error
     */
    public function markAsFull($islandIds, $full = true) {
        // Prepare the result
        $updateResult = false;
        
        // List of islands
        if (is_array($islandIds)) {
            if (count($islandIds)) {
                // Prepare the multi-update payload
                $fieldsArray = array();
                foreach ($islandIds as $islandId) {
                    $fieldsArray[abs((int) $islandId)] = array(
                        self::COL_ISLAND_IS_FULL => $full ? 1 : 0
                    );
                }

                // Valid query produced
                if (null !== $multiUpdateQuery = Stephino_Rpg_Utils_Db::multiUpdate($this->getTableName(), self::COL_ID, $fieldsArray)) {
                    $updateResult = $this->getDb()->getWpDb()->query($multiUpdateQuery);
                }
            }
        } else {
            $updateResult = $this->updateById(
                array(
                    self::COL_ISLAND_IS_FULL => $full ? 1 : 0,
                ), 
                $islandIds
            );
        }
        
        return $updateResult;
    }
}

/*EOF*/