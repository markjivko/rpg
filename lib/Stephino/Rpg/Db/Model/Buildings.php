<?php
/**
 * Stephino_Rpg_Db_Model_Buildings
 * 
 * @title     Model:Buildings
 * @desc      Buildings Model
 * @copyright (c) 2021, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Db_Model_Buildings extends Stephino_Rpg_Db_Model {

    /**
     * Buildings Model Name
     */
    const NAME = 'buildings';
    
    /**
     * Add a building at the desired level, updating the parent city level if necessary
     * 
     * @param int $cityId           City ID
     * @param int $buildingConfigId Building Configuration ID
     * @param int $buildingLevel            (optional) Building level; default <b>1</b>
     * @return int|null New Building DB ID or Null on error
     * @throws Exception
     */
    public function create($cityId, $buildingConfigId, $buildingLevel = 1) {
        // Validate the building config
        if (null === $buildingConfig = Stephino_Rpg_Config::get()->buildings()->getById($buildingConfigId)) {
            throw new Exception(
                sprintf(
                    __('Invalid configuration (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigBuildingName()
                )
            );
        }
        
        // Get the city data
        if (!is_array($cityInfo = $this->getDb()->tableCities()->getById($cityId))) {
            throw new Exception(
                sprintf(
                    __('Invalid ID (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigCityName()
                )
            );
        }
        
        // Get the city configuration
        if (null === $cityConfig = Stephino_Rpg_Config::get()->cities()->getById($cityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_CONFIG_ID])) {
            throw new Exception(
                sprintf(
                    __('Invalid configuration (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigCityName()
                )
            );
        }
        
        // Get the new building ID
        $buildingId = $this->getDb()->tableBuildings()->create(
            $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_USER_ID], 
            $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID], 
            $cityId, 
            $buildingConfigId, 
            $buildingLevel
        );

        // Need to update the city level as well
        if(null !== $buildingId && $buildingConfig->isMainBuilding()) {
            if (false === $this->getDb()->tableCities()->updateById(
                array(Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL => intval($buildingLevel)), 
                $cityId
            )) {
                throw new Exception(
                    sprintf(
                        __('Could not update level (%s)', 'stephino-rpg'),
                        Stephino_Rpg_Config::get()->core()->getConfigCityName()
                    )
                );
            }
        }
        
        return $buildingId;
    }
    
    /**
     * Get the build time
     * 
     * @param Stephino_Rpg_Config_Building $buildingConfig Building Configuration Object
     * @param int                            $buildingLevel  Building Level
     * @param int                            $userId              (optional) User ID; <b>ignored in time-lapse mode</b>
     * @param boolean                        $timelapseMode       (optional) Time-lapse mode; default <b>true</b>
     * @return int Time in seconds
     */
    public function getBuildTime($buildingConfig, $buildingLevel = 0, $userId = null, $timelapseMode = true) {
        $result = Stephino_Rpg_Utils_Config::getPolyValue(
            Stephino_Rpg_Config::get()->core()->getSandbox() 
                ? null
                : $buildingConfig->getCostTimePolynomial(),
            abs((int) $buildingLevel) + 1, 
            $buildingConfig->getCostTime()
        );
        
        // Get the time contraction
        list($timeContraction) = $this->getDb()->modelPremiumModifiers()->getTimeContraction(
            Stephino_Rpg_Db_Model_Buildings::NAME, 
            $userId, 
            $timelapseMode
        );
        
        // Valid value
        if ($timeContraction > 1) {
            $result /= $timeContraction;
        }
        return (int) $result;
    }
    
    /**
     * Initialize a building at the specified level (create or update), updating the parent city level when needed
     * 
     * @param int $cityId           City ID
     * @param int $buildingConfigId Building Configuration ID
     * @param int $buildingLevel    Building Level
     * @return int Building DB ID
     * @throws Exception
     */
    public function setLevel($cityId, $buildingConfigId, $buildingLevel) {
        // Validate the building config
        if (null === $buildingConfig = Stephino_Rpg_Config::get()->buildings()->getById($buildingConfigId)) {
            throw new Exception(
                sprintf(
                    __('Invalid configuration (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigBuildingName()
                )
            );
        }
        
        // Get the city data
        if (!is_array($cityInfo = $this->getDb()->tableCities()->getById($cityId))) {
            throw new Exception(
                sprintf(
                    __('Invalid ID (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigCityName()
                )
            );
        }
        
        // Prepare the result
        $buildingId = null;
        
        // Get the building information
        $buildingInfo = $this->getDb()->tableBuildings()->getByCityAndConfig($cityId, $buildingConfigId);
        
        // Building not found
        if (null === $buildingInfo) {
            // Create the building
            if (null === $buildingId = $this->create($cityId, $buildingConfigId, $buildingLevel)) {
                throw new Exception(
                    sprintf(
                        __('Could not create (%s)', 'stephino-rpg'),
                        Stephino_Rpg_Config::get()->core()->getConfigBuildingName()
                    )
                );
            }
        } else {
            // Store the building ID
            $buildingId = $buildingInfo[Stephino_Rpg_Db_Table_Buildings::COL_ID];
            
            // Update building level
            if (false === $this->getDb()->tableBuildings()->updateById(
                array(Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL => intval($buildingLevel)), 
                $buildingId
            )) {
                throw new Exception(
                    sprintf(
                        __('Could not update level (%s)', 'stephino-rpg'),
                        Stephino_Rpg_Config::get()->core()->getConfigBuildingName()
                    )
                );
            }
            
            // Need to update the city level as well
            if ($buildingConfig->isMainBuilding()) {
                if (false === $this->getDb()->tableCities()->updateById(
                    array(Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL => intval($buildingLevel)), 
                    $cityId
                )) {
                    throw new Exception(
                        sprintf(
                            __('Could not update level (%s)', 'stephino-rpg'),
                            Stephino_Rpg_Config::get()->core()->getConfigCityName()
                        )
                    );
                }
            }
        }
        return $buildingId;
    }
    
    /**
     * Delete all buildings by user ID; also deletes: <ul>
     *     <li>entities</li>
     * </ul>
     * 
     * @param int $userId User ID
     * @return int|false The number of rows updated or false on error
     */
    public function deleteByUser($userId) {
        // Remove all entities
        $this->getDb()->modelEntities()->deleteByUser($userId);
        
        // Remove all buildings
        return $this->getDb()->tableBuildings()->deleteByUser($userId);
    }
    
}

/* EOF */