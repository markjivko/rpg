<?php
/**
 * Stephino_Rpg_Renderer_Ajax_Action_City
 * 
 * @title     Action::City
 * @desc      City Actions
 * @copyright (c) 2021, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Renderer_Ajax_Action_City extends Stephino_Rpg_Renderer_Ajax_Action {

    // Request keys
    const REQUEST_CITY_WORKERS       = 'cityWorkers';
    const REQUEST_CITY_NAME          = 'cityName';
    const REQUEST_CITY_GOVERNMENT_ID = 'cityGovernmentId';
    
    // PNG file names
    const IMAGE_512  = '512';
    const IMAGE_FULL = 'full';
    
    // Data keys
    const CELL_CONFIG_CITY_BKG = 'cityBkg';
    
    // Configuration
    const MAX_LENGTH_CITY_NAME = 20;
    
    /**
     * Rename a city
     * 
     * @param array $data Data containing <ul>
     * <li><b>cityId</b> (int) City ID</li>
     * <li><b>cityName</b> (string) New city name</li>
     * </ul>
     * @return string New city name
     */
    public static function ajaxRename($data) {
        // Get the city ID
        $cityId = isset($data[self::REQUEST_CITY_ID]) ? intval($data[self::REQUEST_CITY_ID]) : null;
        
        // Prepare the city information
        $cityInfo = self::getCityInfo($cityId);
        
        // Get the original name
        $cityNameOriginal = $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_NAME];
        
        // Prepare the city name
        $cityName = isset($data[self::REQUEST_CITY_NAME]) ? trim($data[self::REQUEST_CITY_NAME]) : $cityNameOriginal;
        
        // Sanitize it, removing anything that is not a valid UTF-8 alpha-numeric character or space
        $cityName = trim(preg_replace(array('%[^ \d\p{L}]+%u', '%\s+%s'), array('', ' '), $cityName));
        
        // Cannot be an empty string
        if (!strlen($cityName)) {
            throw new Exception(__('Invalid name provided, use alpha-numeric characters only', 'stephino-rpg'));
        }
        
        // Name is too long
        if (strlen($cityName) > self::MAX_LENGTH_CITY_NAME) {
            throw new Exception(
                sprintf(
                    __('The name is too long, please keep it shorter than %d characters', 'stephino-rpg'), 
                    self::MAX_LENGTH_CITY_NAME
                )
            );
        }
        
        // Update the city name
        if (false === Stephino_Rpg_Db::get()->tableCities()->updateById(
            array(Stephino_Rpg_Db_Table_Cities::COL_CITY_NAME => $cityName), $cityId
        )) {
            throw new Exception(__('Could not update name, please try again later', 'stephino-rpg'));
        }
            
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                Stephino_Rpg_Db_Table_Cities::COL_CITY_NAME       => $cityName,
                Stephino_Rpg_Db_Table_Cities::COL_CITY_IS_CAPITAL => $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_IS_CAPITAL],
            ),
            $cityId
        );
    }
    
    /**
     * Set the workforce
     * 
     * @param array $data Data containing <ul>
     * <li><b>cityId</b> (int) City ID</li>
     * </ul>
     */
    public static function ajaxWorkforce($data) {
        // Prepare the city ID
        $cityId = isset($data[self::REQUEST_CITY_ID]) ? intval($data[self::REQUEST_CITY_ID]) : null;
        $cityWorkers = isset($data[self::REQUEST_CITY_WORKERS]) ? $data[self::REQUEST_CITY_WORKERS] : null;
        
        // Get our city information
        $cityInfo = self::getCityInfo($cityId);
        
        // Invalid workers type
        if (!is_array($cityWorkers)) {
            throw new Exception(__('Invalid request', 'stephino-rpg'));
        }
        
        // Store the new worker info
        $cityBuildingWorkers = array();
        
        // Validate the workers
        foreach ($cityWorkers as $buildingConfigId => $buildingWorkers) {
            /* @var $buildingConfig Stephino_Rpg_Config_Building */
            list($buildingData, $buildingConfig) = self::getBuildingInfo(
                $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_ID], 
                $buildingConfigId
            );
            
            // Building not created
            if (!is_array($buildingData)) {
                throw new Exception(
                    sprintf(
                        __('Not found (%s)', 'stephino-rpg'),
                        Stephino_Rpg_Config::get()->core()->getConfigBuildingName()
                    )
                );
            }
            
            // Workers not allowed
            if (!$buildingConfig->getUseWorkers()) {
                throw new Exception(
                    sprintf(
                        __('"%s" does not use workers', 'stephino-rpg'),
                        $buildingConfig->getName()
                    )
                );
            }
            
            // Get the building level
            if ($buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL] <= 0) {
                throw new Exception(
                    sprintf(
                        __('"%s" is still under construction', 'stephino-rpg'),
                        $buildingConfig->getName()
                    )
                );
            }
            
            // Get the workers capacity
            $workersCapacity = Stephino_Rpg_Utils_Config::getPolyValue(
                $buildingConfig->getWorkersCapacityPolynomial(),
                $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL], 
                $buildingConfig->getWorkersCapacity()
            );
            
            // Invalid number of workers assigned
            if ($buildingWorkers > $workersCapacity) {
                throw new Exception(
                    sprintf(
                        __('Too many workers assigned to "%s"', 'stephino-rpg'),
                        $buildingConfig->getName()
                    )
                );
            }
            
            // Store the data
            $cityBuildingWorkers[$buildingData[Stephino_Rpg_Db_Table_Buildings::COL_ID]] = $buildingWorkers;
        }
        
        // Validate the workers
        if (array_sum($cityBuildingWorkers) > floor($cityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION])) {
            throw new Exception(
                sprintf(
                    __('Insufficient resources (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getMetricPopulationName()
                )
            );
        }
        
        // Workers multi-update
        $result = Stephino_Rpg_Db::get()->tableBuildings()->updateWorkers($cityBuildingWorkers);
        
        return Stephino_Rpg_Renderer_Ajax::wrap(
            $result,
            $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_ID]
        );
    }
    
    /**
     * Move the Metropolis
     * 
     * @param array $data Data containing <ul>
     * <li><b>cityId</b> (int) City ID</li>
     * </ul>
     */
    public static function ajaxMoveCapital($data) {
        // Prepare the city ID
        $cityId = isset($data[self::REQUEST_CITY_ID]) ? intval($data[self::REQUEST_CITY_ID]) : null;
        
        // Get our city information
        $cityData = self::getCityInfo($cityId);
        
        // Already the Metropolis
        if ($cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_IS_CAPITAL]) {
            throw new Exception(
                sprintf(
                    __('This %s is already your empire\'s metropolis', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigCityName()
                )
            );
        }
        
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
        
        // Get the Metropolis movement cost data
        $costData = self::getCostData(
            $cityConfig, 
            $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL] - 1
        );
        
        // Metropolis cannot be changed
        if (!count($costData)) {
            throw new Exception(__('Metropolis cannot be changed', 'stephino-rpg'));
        }
        
        // Try to pay for the Metropolis change
        self::spend($costData, $cityData);
        
        // Update the Metropolis
        $result = Stephino_Rpg_Db::get()->tableCities()->setCapitalByUser(
            $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_USER_ID],
            $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]
        );
        
        if (!$result) {
            throw new Exception(__('Could not move metropolis', 'stephino-rpg'));
        }
        
        // Inform the user
        echo esc_html__('The metropolis moved successfully', 'stephino-rpg');
        
        // Store the result
        return Stephino_Rpg_Renderer_Ajax::wrap(
            $result,
            $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]
        );
    }
    
    /**
     * Instate a Government
     * 
     * @param array $data Data containing <ul>
     * <li><b>cityId</b> (int) City ID</li>
     * <li><b>cityGovernmentId</b> (int) Government Configuration ID</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxGovernmentSet($data) {
        // No governments to choose from
        if (!count(Stephino_Rpg_Config::get()->governments()->getAll())) {
            throw new Exception(
                sprintf(
                    __('Not supported (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigGovernmentsName()
                )
            );
        }
        
        // Get our city information
        $cityInfo = self::getCityInfo(isset($data[self::REQUEST_CITY_ID]) ? intval($data[self::REQUEST_CITY_ID]) : null);
        
        // Prepare the government ID
        $governmentConfigId = isset($data[self::REQUEST_CITY_GOVERNMENT_ID]) ? intval($data[self::REQUEST_CITY_GOVERNMENT_ID]) : null;
        
        // Get the government configuration
        if (null === $governmentConfig = Stephino_Rpg_Config::get()->governments()->getById($governmentConfigId)) {
            throw new Exception(
                sprintf(
                    __('Invalid configuration (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigGovernmentName()
                )
            );
        }
        
        // Get the cost data
        $costData = self::getCostData(
            $governmentConfig, 
            $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL]
        );
        
        // Try to pay for the government change
        self::spend($costData, $cityInfo);
        
        // Update the government
        return Stephino_Rpg_Renderer_Ajax::wrap(
            Stephino_Rpg_Db::get()->modelCities()->setGovernment(
                $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_ID], 
                $governmentConfigId
            ),
            $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_ID]
        );
    }
    
    /**
     * Get the closest available animation info for this city
     * 
     * @param int $configId City configuration ID
     * @param int $level    City Level
     * @return array|null
     */
    public static function getClosestIslandAnimation($configId, $level) {
        // Prepare the closest animation
        $closestAnimation = null;
        
        // Sanitize the level
        $cityLevel = intval($level);
        
        // Get the building animations array
        if ($cityLevel > 0 && null !== $cityConfig = Stephino_Rpg_Config::get()->cities()->getById($configId)) {
            if (is_array($animationsArray = json_decode($cityConfig->getIslandAnimations(), true))) {
                for ($i = 1; $i <= $cityLevel; $i++) {
                    if (isset($animationsArray[$i])) {
                        // Store as animation level => animation keys
                        $closestAnimation = array($configId . '-' . $i => array_keys($animationsArray[$i]));
                    }
                }
            }
        }
        return $closestAnimation;
    }
}

/* EOF */