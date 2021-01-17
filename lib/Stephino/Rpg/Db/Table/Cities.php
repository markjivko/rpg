<?php

/**
 * Stephino_Rpg_Db_Table_Cities
 * 
 * @title      Table:Cities
 * @desc       Holds the cities information
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Db_Table_Cities extends Stephino_Rpg_Db_Table {
    
    /**
     * Cities Table Name
     */
    const NAME = 'cities';
    
    /**
     * City ID
     * 
     * @var int
     */
    const COL_ID = 'city_id';
    
    /**
     * Timestamp of user creation
     * 
     * @var int UNIX timestamp
     */
    const COL_CITY_CREATED = 'city_created';
    
    /**
     * User ID
     * 
     * @var int
     */
    const COL_CITY_USER_ID = 'city_user_id';
    
    /**
     * Island ID
     * 
     * @var int
     */
    const COL_CITY_ISLAND_ID = 'city_island_id';
    
    /**
     * Island Index
     * 
     * @var int
     */
    const COL_CITY_ISLAND_INDEX = 'city_island_index';
    
    /**
     * Name
     * 
     * @var string 128 characters
     */
    const COL_CITY_NAME = 'city_name';
    
    /**
     * City is Metropolis
     * 
     * @var int 0|1, default 0
     */
    const COL_CITY_IS_CAPITAL = 'city_is_capital';
    
    /**
     * Configuration ID
     * 
     * @var int
     */
    const COL_CITY_CONFIG_ID = 'city_config_id';
    
    /**
     * Level
     * 
     * @var int
     */
    const COL_CITY_LEVEL = 'city_level';
    
    /**
     * Government Configuration ID
     * 
     * @var int
     */
    const COL_CITY_GOVERNMENT_CONFIG_ID = 'city_government_config_id';
    
    /**
     * "Alpha" Resource
     * 
     * @var float
     */
    const COL_CITY_RESOURCE_ALPHA = 'city_resource_alpha';
    
    /**
     * "Beta" Resource
     * 
     * @var float
     */
    const COL_CITY_RESOURCE_BETA = 'city_resource_beta';
    
    /**
     * "Gamma" Resource
     * 
     * @var float
     */
    const COL_CITY_RESOURCE_GAMMA = 'city_resource_gamma';
    
    /**
     * "Extra 1" Resource
     * 
     * @var float
     */
    const COL_CITY_RESOURCE_EXTRA_1 = 'city_resource_extra_1';
    
    /**
     * "Extra 2" Resource
     * 
     * @var float
     */
    const COL_CITY_RESOURCE_EXTRA_2 = 'city_resource_extra_2';
    
    /**
     * Storage
     * 
     * @var float
     */
    const COL_CITY_METRIC_STORAGE = 'city_metric_storage';
    
    /**
     * Population
     * 
     * @var float
     */
    const COL_CITY_METRIC_POPULATION = 'city_metric_population';
    
    /**
     * Satisfaction
     * 
     * @var float
     */
    const COL_CITY_METRIC_SATISFACTION = 'city_metric_satisfaction';
    
    /**
     * Table creation SQL statement
     * 
     * @return string
     */
    public function getCreateStatement() {
        return "CREATE TABLE `$this` (
    `" . self::COL_ID . "` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `" . self::COL_CITY_CREATED . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_CITY_USER_ID . "` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_CITY_ISLAND_ID . "` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_CITY_ISLAND_INDEX . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_CITY_NAME . "` varchar(128) NOT NULL,
    `" . self::COL_CITY_IS_CAPITAL . "` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_CITY_CONFIG_ID . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_CITY_LEVEL . "` int(11) UNSIGNED NOT NULL DEFAULT '1',
    `" . self::COL_CITY_GOVERNMENT_CONFIG_ID . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_CITY_RESOURCE_ALPHA . "` decimal(24,4) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_CITY_RESOURCE_BETA . "` decimal(24,4) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_CITY_RESOURCE_GAMMA . "` decimal(24,4) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_CITY_RESOURCE_EXTRA_1 . "` decimal(24,4) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_CITY_RESOURCE_EXTRA_2 . "` decimal(24,4) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_CITY_METRIC_STORAGE . "` decimal(24,4) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_CITY_METRIC_POPULATION . "` decimal(24,4) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_CITY_METRIC_SATISFACTION . "` decimal(24,4) UNSIGNED NOT NULL DEFAULT '0',
    UNIQUE KEY `" . self::COL_ID . "` (`" . self::COL_ID . "`)
);";
    }
    
    /**
     * Create a city
     * 
     * @param int                      $userId      Owner User ID
     * @param boolean                  $isRobot     Owner is a robot
     * @param int                      $islandId    Island ID
     * @param int                      $islandIndex Island index
     * @param string                   $cityName    City name
     * @param Stephino_Rpg_Config_City $cityConfig  City Configuration Object
     * @param boolean                  $isCapital   (optional) Is this city a capital? default <b>false</b>
     * @param int                      $cityLevel   (optional) Level; default <b>1</b>
     * @return int|null New City ID or Null on error
     */
    public function create($userId, $isRobot, $islandId, $islandIndex, $cityName, $cityConfig, $isCapital = false, $cityLevel = 1) {
        if (!$cityConfig instanceof Stephino_Rpg_Config_City) {
            return null;
        }
        
        // Maximum storage
        $maxStorage = Stephino_Rpg_Utils_Config::getPolyValue(
            $cityConfig->getMaxStoragePolynomial(), 
            abs((int) $cityLevel), 
            $cityConfig->getMaxStorage()
        );
        
        // Prepare the insert data
        $insertData = array(
            self::COL_CITY_CREATED        => time(),
            self::COL_CITY_USER_ID        => abs((int) $userId),
            self::COL_CITY_ISLAND_ID      => abs((int) $islandId),
            self::COL_CITY_ISLAND_INDEX   => abs((int) $islandIndex),
            self::COL_CITY_NAME           => trim($cityName),
            self::COL_CITY_IS_CAPITAL     => $isCapital ? 1 : 0,
            self::COL_CITY_CONFIG_ID      => abs((int) $cityConfig->getId()),
            self::COL_CITY_LEVEL          => abs((int) $cityLevel),
            self::COL_CITY_METRIC_STORAGE => $maxStorage
        );
        
        if ($isRobot) {
            // Robot account: everything to 100%
            foreach (array(
                self::COL_CITY_RESOURCE_ALPHA,
                self::COL_CITY_RESOURCE_BETA,
                self::COL_CITY_RESOURCE_GAMMA,
                self::COL_CITY_RESOURCE_EXTRA_1,
                self::COL_CITY_RESOURCE_EXTRA_2,
            ) as $resourceKey) {
                $insertData[$resourceKey] = $maxStorage;
            }
        }
        
        // Create the entry
        $result = $this->getDb()->getWpDb()->insert($this->getTableName(), $insertData);
        
        // Get the new city ID
        return (false !== $result ? $this->getDb()->getWpDb()->insert_id : null);
    }
    
    /**
     * Get all the cities belonging to this user
     * 
     * @param int $userId User ID
     * @return array[]|null
     */
    public function getByUser($userId) {
        $result = $this->getDb()->getWpDb()->get_results(
            "SELECT * FROM `$this`"
            . " WHERE `" . self::COL_CITY_USER_ID  . "` = '" . abs((int) $userId) . "'", 
            ARRAY_A
        );
        
        return is_array($result) && count($result) ? $result : null;
    }
    
    /**
     * Get all the cities placed on an island
     * 
     * @param int $islandId Island ID
     * @return array[]|null
     */
    public function getByIsland($islandId) {
        $result = $this->getDb()->getWpDb()->get_results(
            "SELECT * FROM `$this`"
            . " WHERE `" . self::COL_CITY_ISLAND_ID  . "` = '" . intval($islandId) . "'", 
            ARRAY_A
        );
        return is_array($result) && count($result) ? $result : null;
    }
    
    /**
     * Get a city information by island ID and island index
     * 
     * @param int $islandId    City Island ID
     * @param int $islandIndex City Island Index
     * @return array|null DB row or null on error
     */
    public function getByIslandAndIndex($islandId, $islandIndex) {
        return $this->getDb()->getWpDb()->get_row(
            "SELECT * FROM `$this`"
            . " WHERE `" . self::COL_CITY_ISLAND_ID  . "` = '" . intval($islandId) . "'"
            . " AND `" . self::COL_CITY_ISLAND_INDEX  . "` = '" . intval($islandIndex) . "'", 
            ARRAY_A
        );
    }
    
    /**
     * Get a user's metropolis
     * 
     * @param int $userId User ID
     * @return array|null
     */
    public function getCapitalByUser($userId) {
        return $this->getDb()->getWpDb()->get_row(
            "SELECT * FROM `$this`"
            . " WHERE `" . self::COL_CITY_USER_ID . "` = '" . intval($userId) . "'"
            . " AND `" . self::COL_CITY_IS_CAPITAL . "` = '1'", 
            ARRAY_A
        );
    }
    
    /**
     * Change a user's metropolis; city must belong to user and not be a metropolis
     * 
     * @param int $userId User ID
     * @param int $cityId City ID
     * @return int|false|null Number of rows updated or false on update query; null if operation is not allowed
     */
    public function setCapitalByUser($userId, $cityId) {
        // Prepare the old Metropolis ID - should only be 1, but gathering all just to be sure
        $fieldsInfo = array();
        
        // Go through the user's cities
        if (is_array($userCities = $this->getByUser($userId))) {
            foreach ($userCities as $dbRow) {
                // Old Metropolises become regular cities
                if ($dbRow[self::COL_CITY_IS_CAPITAL]) {
                    $fieldsInfo[(int) $dbRow[self::COL_ID]] = array(
                        self::COL_CITY_IS_CAPITAL => 0,
                    );
                } 
                
                // Found our city - not trying to change other user's Metropolises
                if ($dbRow[self::COL_ID] == $cityId) {
                    $fieldsInfo[(int) $dbRow[self::COL_ID]] = array(
                        self::COL_CITY_IS_CAPITAL => 1,
                    );
                }
            }
        }

        // Valid query produced
        $result = null;
        if (null !== $multiUpdateQuery = Stephino_Rpg_Utils_Db::getMultiUpdate($fieldsInfo, $this->getTableName(), self::COL_ID)) {
            $result = $this->getDb()->getWpDb()->query($multiUpdateQuery);
        }
        return $result;
    }
    
    /**
     * Delete cities by user ID
     * 
     * @param int $userId User ID
     * @return int|false The number of rows deleted or false on error
     */
    public function deleteByUser($userId) {
        return $this->getDb()->getWpDb()->delete(
            $this->getTableName(),
            array(
                self::COL_CITY_USER_ID => abs((int) $userId),
            )
        );
    }
    
    /**
     * Delete cities by island ID
     * 
     * @param int $islandId Island ID
     * @return int|false The number of rows deleted or false on error
     */
    public function deleteByIsland($islandId) {
        return $this->getDb()->getWpDb()->delete(
            $this->getTableName(),
            array(
                self::COL_CITY_ISLAND_ID => abs((int) $islandId),
            )
        );
    }
}

/*EOF*/