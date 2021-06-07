<?php

/**
 * Stephino_Rpg_Db_Table_Buildings
 * 
 * @title      Table:Buildings
 * @desc       Holds the buildings information
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Db_Table_Buildings extends Stephino_Rpg_Db_Table {
    
    /**
     * Building Table NAme
     */
    const NAME = 'buildings';
    
    /**
     * Building ID
     * 
     * @var int
     */
    const COL_ID = 'building_id';
    
    /**
     * User ID
     * 
     * @var int
     */
    const COL_BUILDING_USER_ID = 'building_user_id';
    
    /**
     * Parent Island ID
     * 
     * @var int
     */
    const COL_BUILDING_ISLAND_ID = 'building_island_id';
    
    /**
     * Parent City ID
     * 
     * @var int
     */
    const COL_BUILDING_CITY_ID = 'building_city_id';
    
    /**
     * Configuration ID
     * 
     * @var int
     */
    const COL_BUILDING_CONFIG_ID = 'building_config_id';
    
    /**
     * Level
     * 
     * @var int
     */
    const COL_BUILDING_LEVEL = 'building_level';
    
    /**
     * Workers
     * 
     * @var int
     */
    const COL_BUILDING_WORKERS = 'building_workers';

    /**
     * Table creation SQL statement
     * 
     * @return string
     */
    public function getCreateStatement() {
        return "CREATE TABLE `$this` (
    `" . self::COL_ID . "` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `" . self::COL_BUILDING_USER_ID . "` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_BUILDING_ISLAND_ID . "` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_BUILDING_CITY_ID . "` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_BUILDING_CONFIG_ID . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_BUILDING_LEVEL . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_BUILDING_WORKERS . "` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
    UNIQUE KEY `" . self::COL_ID . "` (`" . self::COL_ID . "`)
);";
    }
    
    /**
     * Add a building at the desired level
     * 
     * @param int $userId   User ID
     * @param int $islandId Island ID
     * @param int $cityId   City ID
     * @param int $configId Building Configuration ID
     * @param int $level    (optional) Building level; default <b>1</b>
     * @return int|null New Building ID or Null on error
     */
    public function create($userId, $islandId, $cityId, $configId, $level = 1) {
        $result = $this->getDb()->getWpDb()->query(
            Stephino_Rpg_Utils_Db::insert(
                $this->getTableName(),
                array(
                    self::COL_BUILDING_USER_ID   => abs((int) $userId),
                    self::COL_BUILDING_ISLAND_ID => abs((int) $islandId),
                    self::COL_BUILDING_CITY_ID   => abs((int) $cityId),
                    self::COL_BUILDING_CONFIG_ID => abs((int) $configId),
                    self::COL_BUILDING_LEVEL     => abs((int) $level)
                )
            )
        );
        
        // Get the new building ID
        return (false !== $result ? $this->getDb()->getWpDb()->insert_id : null);
    }
    
    /**
     * Get all the buildings belonging to this user
     * 
     * @param int $userId User ID
     * @return array[]|null
     */
    public function getByUser($userId) {
        $result = $this->getDb()->getWpDb()->get_results(
            Stephino_Rpg_Utils_Db::selectAll(
                $this->getTableName(), 
                array(
                    self::COL_BUILDING_USER_ID => abs((int) $userId)
                )
            ), 
            ARRAY_A
        );
        
        return is_array($result) && count($result) ? $result : null;
    }
    
    /**
     * Get all the buildings in this city
     * 
     * @param int $cityId City ID
     * return array|null
     */
    public function getByCity($cityId) {
        $result = $this->getDb()->getWpDb()->get_results(
            Stephino_Rpg_Utils_Db::selectAll(
                $this->getTableName(), 
                array(
                    self::COL_BUILDING_CITY_ID => abs((int) $cityId)
                )
            ), 
            ARRAY_A
        );
        
        return is_array($result) && count($result) ? $result : null;
    }
    
    /**
     * Get a building by city ID and building configuration ID
     * 
     * @param int $cityId           City ID
     * @param int $buildingConfigId Building Configuration ID
     * @return array|null
     */
    public function getByCityAndConfig($cityId, $buildingConfigId) {
        return $this->getDb()->getWpDb()->get_row(
            Stephino_Rpg_Utils_Db::selectAll(
                $this->getTableName(), 
                array(
                    self::COL_BUILDING_CITY_ID   => abs((int) $cityId),
                    self::COL_BUILDING_CONFIG_ID => abs((int) $buildingConfigId)
                )
            ), 
            ARRAY_A
        );
    }
    
    /**
     * Delete buildings by user ID
     * 
     * @param int $userId User ID
     * @return int|false The number of rows deleted or false on error
     */
    public function deleteByUser($userId) {
        return $this->getDb()->getWpDb()->query(
            Stephino_Rpg_Utils_Db::delete(
                $this->getTableName(), 
                array(
                    self::COL_BUILDING_USER_ID => abs((int) $userId)
                )
            )
        );
    }
    
    /**
     * Delete buildings by city ID
     * 
     * @param int $cityId City ID
     * @return int|false The number of rows deleted or false on error
     */
    public function deleteByCity($cityId) {
        return $this->getDb()->getWpDb()->query(
            Stephino_Rpg_Utils_Db::delete(
                $this->getTableName(),
                array(
                    self::COL_BUILDING_CITY_ID => abs((int) $cityId)
                )
            )
        );
    }
    
    /**
     * Update multiple buildings workers
     * 
     * @param int[] $buildingWorkers Associative array of <b>Building ID</b> => <b>Building Workers</b>
     * @return int|false|null Number of rows updated or false on update query; null if operation is not allowed
     */
    public function updateWorkers($buildingWorkers) {
        $result = null;
        
        // Valid data set
        if (is_array($buildingWorkers) && count($buildingWorkers)) {
            // Prepare the fields info
            $fieldsArray = array();
            foreach ($buildingWorkers as $buildingId => $buildingWorkerCount) {
                $buildingWorkerCount = (int)$buildingWorkerCount;

                // Negative values not allowed
                if ($buildingWorkerCount >= 0) {
                    $fieldsArray[(int) $buildingId] = array(
                        self::COL_BUILDING_WORKERS => (int) $buildingWorkerCount,
                    );
                }
            }

            // Prepare the query
            if (null !== $multiUpdateQuery = Stephino_Rpg_Utils_Db::multiUpdate($this->getTableName(), self::COL_ID, $fieldsArray)) {
                $result = $this->getDb()->getWpDb()->query($multiUpdateQuery);
            }
        }
        
        return $result;
    }
}

/*EOF*/