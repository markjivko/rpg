<?php
/**
 * Stephino_Rpg_Db_Model_Cities
 * 
 * @title     Model:Cities
 * @desc      Cities Model
 * @copyright (c) 2021, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Db_Model_Cities extends Stephino_Rpg_Db_Model {

    /**
     * Cities Model Name
     */
    const NAME = 'cities';
    
    /**
     * Text value for the name of a missing city
     */
    const UNKNOWN_CITY = '###';
    
    /**
     * Generate a unique (enough) city name; the player can change this anytime
     * 
     * @param Stephino_Rpg_Config_City $configCity  City configuration
     * @param int                        $islandId    Island ID
     * @param int                        $islandIndex City index on island
     * @return string
     */
    protected function _generateName($configCity, $islandId, $islandIndex) {
        do {
            // Prepare the city name
            $cityName = '';
            
            // Prepare the file handler
            if (is_file($filePath = Stephino_Rpg_Config::get()->themePath() . '/txt/' . self::NAME . '.txt')) {
                $fileHandler = new SplFileObject($filePath, 'r');
                $fileHandler->seek(PHP_INT_MAX);

                // Get the number of rows
                $fileRows = $fileHandler->key() + 1; 
                
                // Valid number of rows
                if ($fileRows >= 1) {
                    // Prepare a random row
                    $randomRow = mt_rand(1, $fileRows);

                    // Rewind
                    $fileHandler->rewind();

                    // Go through all the rows
                    while($fileHandler->valid()) {
                        // Store the identifier
                        $cityName = $fileHandler->fgets();

                        // Reached our row
                        if ($fileHandler->key() == $randomRow - 1) {
                            // Trim the line
                            $cityName = trim($cityName);
                            break;
                        }
                    }
                }
                
                // Valid identifier found
                if (strlen($cityName)) {
                    break;
                }
            }
            
            // Store the default city name
            $cityName = $configCity->getName() . ' ' . $islandId . ':' . $islandIndex;
            
        } while(false);
        
        return $cityName;  
    }
    
    /**
     * Create a city, adding the first buildings as well
     * 
     * @param int     $userId       Owner User ID
     * @param boolean $isRobot      Owner is a Robot account
     * @param int     $islandId     Island ID
     * @param boolean $isCapital    (optional) Is this city a capital? default <b>false</b>
     * @param string  $cityName     (optional) City name; default <b>null</b>
     * @param int     $configCityId (optional) City configuration ID; default <b>null</b> - assign a random one
     * @param int     $cityLevel    (optional) Level; default <b>1</b>
     * @param int     $islandIndex  (optional) Island index; default <b>null</b>, auto-allocated
     * @return [int,Stephino_Rpg_Config_City] Array of City ID and City Configuration object
     * @throws Exception
     */
    public function create($userId, $isRobot, $islandId, $isCapital = false, $cityName = null, $configCityId = null, $cityLevel = 1, $islandIndex = null) {
        // Validate the city configuration
        $configCityObject = Stephino_Rpg_Config::get()->cities()->getById($configCityId);
        
        // Validate the city slots
        $cityBuildingSlotsValid = false;
        
        // Get all the configuration objects
        $allConfigCityObjects = Stephino_Rpg_Config::get()->cities()->getAll();
        shuffle($allConfigCityObjects);
        do {
            // Prepare the building slots
            $cityBuildingSlots = (null !== $configCityObject && null !== $configCityObject->getCityBuildingSlots() ? json_decode($configCityObject->getCityBuildingSlots(), true) : null);

            // Invalid island slots configuration
            if (is_array($cityBuildingSlots)) {
                $cityBuildingSlotsValid = true;
                break;
            }
            
            // Start from the beginning
            $configCityObject = current($allConfigCityObjects);
            
            // No more items in the list
            if (false === $configCityObject) {
                break;
            }
            
            // Go to the next item
            next($allConfigCityObjects);
        } while (true);
        
        // Invalid city slots
        if (!$cityBuildingSlotsValid) {
            throw new Exception(
                sprintf(
                    __('Invalid slots configuration (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigBuildingName()
                )
            );
        }
        
        // Get the island row
        if (!is_array($islandDbRow = $this->getDb()->tableIslands()->getById($islandId))) {
            throw new Exception(
                sprintf(
                    __('Invalid ID (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigIslandName()
                )
            );
        }
        
        // Prepare the island configuration object
        $configIsland = Stephino_Rpg_Config::get()->islands()->getById($islandDbRow[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_CONFIG_ID]);
        
        // Prepare the city slots
        $islandCitySlots = (null !== $configIsland && null !== $configIsland->getCitySlots() ? json_decode($configIsland->getCitySlots(), true) : null);

        // Invalid island slots configuration
        if (!is_array($islandCitySlots)) {
            throw new Exception(
                sprintf(
                    __('Invalid slots configuration (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigCityName()
                )
            );
        }
        
        // Get the occupied slots
        $occupiedSlots = array();
            
        // Get the other cities on this island
        if (is_array($otherCities = $this->getDb()->tableCities()->getByIsland($islandId))) {
            // Go through the cities on this island
            foreach ($otherCities as $otherCity) {
                $occupiedSlots[] = intval($otherCity[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_INDEX]);
            }
        }
        
        // Island already full
        if (count($occupiedSlots) >= count($islandCitySlots)) {
            // Redundancy
            $this->getDb()->tableIslands()->markAsFull($islandId);
            
            // Stop here
            throw new Exception(
                sprintf(
                    __('%s at maximum capacity', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigIslandName()
                )
            );
        }
        
        // Prepare the empty slots
        $emptySlots = array_diff(array_keys($islandCitySlots), $occupiedSlots);
            
        // No Island Index provided, generate a new one
        if (null === $islandIndex) {
            shuffle($emptySlots);

            // Get the new island index
            $islandIndex = current($emptySlots);
        } else {
            if (!in_array($islandIndex, $emptySlots)) {
                throw new Exception(__('Lot already occupied', 'stephino-rpg'));
            }
        }
        
        // Append to the occupied slots list
        $occupiedSlots[] = $islandIndex;
        
        // Generate the name
        if (!is_string($cityName) || !strlen($cityName)) {
            $cityName = $this->_generateName($configCityObject, $islandId, $islandIndex);
        }
        
        // Execute the query
        $cityId = $this->getDb()->tableCities()->create(
            $userId, 
            $isRobot,
            $islandId, 
            $islandIndex, 
            $cityName, 
            $configCityObject, 
            $isCapital, 
            $cityLevel
        );
        
        // Successful insert
        if (false === $cityId) {
            throw new Exception(
                sprintf(
                    __('Could not create (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigCityName()
                )
            );
        }
        
        // Island is full
        if (count($occupiedSlots) >= count($islandCitySlots)) {
            $this->getDb()->tableIslands()->markAsFull($islandId);
        }
        
        // Go through the configurations
        foreach (Stephino_Rpg_Config::get()->core()->cityInitialBuildings() as $buildingConfig) {
            try {
                // Create the building
                $this->getDb()->modelBuildings()->create($cityId, $buildingConfig->getId());
            } catch (Exception $exc) {
                Stephino_Rpg_Log::check() && Stephino_Rpg_Log::warning(
                    "Db_Model_Cities.create, city #$cityId, building config #{$buildingConfig->getId()}: {$exc->getMessage()}"
                );
            }
        }
        
        return array($cityId, $configCityObject);
    }
    
    /**
     * Delete a city and mark the island as having a vacancy
     * 
     * @param int $cityId User ID
     * @return int|false The number of rows updated or false on error
     */
    public function delete($cityId) {
        // Get the city data
        $cityData = $this->getDb()->tableCities()->getById($cityId);
        
        // City not found
        if (null === $cityData) {
            return false;
        }
        
        // Remove the city
        $result = $this->getDb()->tableCities()->deleteById($cityId);
        
        if (false !== $result) {
            // Mark this island as not full
            $this->getDb()->tableIslands()->markAsFull($cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID], false);
        }
        
        return $result;
    }
    
    /**
     * Delete cities by user ID, un-marking full islands as necessary<br/>
     * <ul>
     *    <li>islands (mark as not full)</li>
     *    <li>buildings
     *        <ul>
     *            <li>entities</li>
     *        </ul>
     *    </li>
     *    <li>queues</li>
     *    <li>convoys</li>
     * </ul>
     * 
     * @param int $userId User ID
     * @return int|false The number of rows updated or false on error
     */
    public function deleteByUser($userId) {
        // Get all the cities
        $cities = $this->getDb()->tableCities()->getByUser($userId);
        
        // Remove all Buildings
        $this->getDb()->modelBuildings()->deleteByUser($userId);
        
        // Remove all Queues
        $this->getDb()->modelQueues()->deleteByUser($userId);
        
        // Remove all Convoys
        $this->getDb()->modelConvoys()->deleteByUser($userId);
        
        // Prepare the result
        if (false !== $result = $this->getDb()->tableCities()->deleteByUser($userId)) {
            if (is_array($cities)) {
                // Get the islands that now have vacancies
                $islandIds = array();
                foreach ($cities as $cityRow) {
                    $islandIds[] = intval($cityRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID]);
                }

                // Mark this island as not full
                $this->getDb()->tableIslands()->markAsFull(array_unique($islandIds), false);
            }
        }
        
        return $result;
    }
    
    /**
     * Update a the city government
     * 
     * @param int $cityId             City DB ID
     * @param int $governmentConfigId Government Configuration ID
     * @throws Exception
     */
    public function setGovernment($cityId, $governmentConfigId) {
        // Invalid city
        if (!is_array($cityData = $this->getDb()->tableCities()->getById($cityId))) {
            throw new Exception(
                sprintf(
                    __('Invalid ID (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigCityName()
                )
            );
        }
        
        // Invalid configuration
        if (null === Stephino_Rpg_Config::get()->governments()->getById($governmentConfigId)) {
            throw new Exception(
                sprintf(
                    __('Invalid configuration (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigGovernmentName()
                )
            );
        }
        
        // Update the field
        if (false === $this->getDb()->tableCities()->updateById(
            array(Stephino_Rpg_Db_Table_Cities::COL_CITY_GOVERNMENT_CONFIG_ID => $governmentConfigId), 
            $cityId
        )) {
            throw new Exception(
                sprintf(
                    __('Could not update (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigGovernmentName()
                )
            );
        }
    }
    
    /**
     * Update a resource value
     * 
     * @param int    $cityId        City ID
     * @param string $resourceName  Resource name, one of <ul>
     * <li>Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA</li>
     * <li>Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_BETA</li>
     * <li>Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_GAMMA</li>
     * <li>Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1</li>
     * <li>Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2</li>
     * </ul>
     * @param float  $resourceValue Resource value
     * @return int Updated resource value
     * @throws Exception
     */
    public function setResource($cityId, $resourceName, $resourceValue) {
        // Invalid resource name
        $allowedResourceNames = array(
            Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA,
            Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_BETA,
            Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_GAMMA,
            Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1,
            Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2,
            Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_STORAGE,
        );
        if (!in_array($resourceName, $allowedResourceNames)) {
            throw new Exception(__('Invalid resource name', 'stephino-rpg'));
        }
        
        // Sanitize the resource value
        $resourceValue = floatval($resourceValue);
        
        // Invalid city
        if (!is_array($cityData = $this->getDb()->tableCities()->getById($cityId))) {
            throw new Exception(
                sprintf(
                    __('Invalid ID (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigCityName()
                )
            );
        }
        
        // Resource minimum
        if ($resourceValue < 0) {
            $resourceValue = 0;
        }
        
        // Resource maximum
        if (Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_STORAGE == $resourceName) {
            // Get the city configuration
            $cityConfig = Stephino_Rpg_Config::get()->cities()->getById(
                $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_CONFIG_ID]
            );
            
            // Invalid city configuration
            if (null === $cityConfig) {
                throw new Exception(
                    sprintf(
                        __('Invalid configuration (%s)', 'stephino-rpg'),
                        Stephino_Rpg_Config::get()->core()->getConfigCityName()
                    )
                );
            }
            
            // Get the maximum storage value
            $storageMax = Stephino_Rpg_Utils_Config::getPolyValue(
                $cityConfig->getMaxStoragePolynomial(),
                $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL], 
                $cityConfig->getMaxStorage()
            );
            
            // No higher than that
            if ($resourceValue > $storageMax) {
                $resourceValue = $storageMax;
            }
        } else {
            if ($resourceValue > $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_STORAGE]) {
                $resourceValue = floatval($cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_STORAGE]);
            }
        }
        
        // Update the field
        if (false === $this->getDb()->tableCities()->updateById(array($resourceName => $resourceValue), $cityId)) {
            throw new Exception(__('Could not update resource', 'stephino-rpg'));
        }
        return $resourceValue;
    }
    
    /**
     * Get the number of cities that occupy an island by ID
     * 
     * @param int[] $islandIds Island DB IDs
     * @return int Associative array of Island DB ID => Number of cities
     */
    public function getCountByIslands($islandIds) {
        $result = array();
        
        // Perform the search
        if (is_array($islandIds)) {
            // Sanitize the island IDs
            $sanitizedIslandIds = array_unique(
                array_filter(
                    array_map('intval', $islandIds), 
                    function($islandId) {
                        return $islandId > 0;
                    }
                )
            );

            if (count($sanitizedIslandIds)) {
                // Initialize the result
                foreach ($sanitizedIslandIds as $islandId) {
                    $result[$islandId] = 0;
                }

                // Get the cities rows
                $cities = $this->getDb()->tableCities()->getByIslands($sanitizedIslandIds);

                // Group
                foreach ($cities as $dbRow) {
                    $result[(int) $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID]]++;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Growth factor
     * 
     * @param float $population   Population value
     * @param float $satisfaction Satisfaction value
     * @return float Factor between -1.0 and 2.0
     */
    public function getGrowthFactor($population, $satisfaction) {
        $result = 2.0;
        
        // Sanitize the values
        $population = $population < 0 ? 0 : (float) $population;
        $satisfaction = $satisfaction < 0 ? 0 : (float) $satisfaction;
        
        // Avoid division by zero
        if ($population > 0 && $satisfaction <= 3 * $population) {
            $result = round(($satisfaction - $population) / $population, 4);
        }
        
        return $result;
    }
    
    /**
     * Get the city name by ID. The result is HTML escaped
     * 
     * @param int $cityId City ID
     * @return string City name or self::UNKNOWN_CITY if city not found
     */
    public function getName($cityId) {
        // Prepare the result
        $result = self::UNKNOWN_CITY;
        
        try {
            // Get city info from time-lapse data store (one less query)
            $cityData = Stephino_Rpg_Renderer_Ajax_Action::getCityInfo($cityId);
        } catch (Exception $exc) {
            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::warning(
                "Db_Model_Cities.getName, city #$cityId: {$exc->getMessage()}"
            );
            
            // Get foreign city info
            $cityData = $this->getDb()->tableCities()->getById($cityId, true);
        }

        // City found
        if (null !== $cityData) {
            $result = Stephino_Rpg_Utils_Lingo::getCityName($cityData);
        }
        
        return $result;
    }
}

/* EOF */