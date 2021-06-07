<?php

/**
 * Stephino_Rpg_TimeLapse_Resources
 * 
 * @title      Time-Lapse::Resources
 * @desc       Manage the resources time-lapse
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_TimeLapse_Resources extends Stephino_Rpg_TimeLapse_Abstract {

    /**
     * Time-Lapse::Resources
     */
    const KEY = 'Resources';
    
    /**
     * Stepper
     * 
     * @param int     $checkPointTime UNIX timestamp
     * @param int     $productionTime Production time in seconds
     * @param boolean $stepMode       (optional) Step mode; used in recursive calls; default <b>false</b>
     */
    public function step($checkPointTime, $productionTime, $stepMode = false) {
        $this->_stepTime = $checkPointTime;

        // Nothing to do yet
        if ($productionTime <= 0) {
            return;
        }
        
        // Since population and satisfaction require close to real-time simulation, a discretization of large intervals is necessary
        // The discretization step size is configurable as "cron interval" and used as-is for the first 24 hours
        // Afterwards a degradation of accuracy occurs (gradually increasing the discretization step size) in order to improve performance
        // This is done so that inactive accounts don't negatively impact performance for other players
        if (!$stepMode) {
            // Get the maximum interval in seconds
            $productionMaxInterval = Stephino_Rpg_Config::get()->core()->getCronInterval() * 60;
            
            // Need to split the production time into smaller windows
            if ($productionMaxInterval > 0 && $productionTime > $productionMaxInterval) {
                // Prepare the intervals
                $productionIntervals = Stephino_Rpg_Utils_Math::getDiscretization(
                    $checkPointTime,
                    $productionTime,
                    $productionMaxInterval,
                    Stephino_Rpg_Config::get()->core()->getCronAccuracy() * 3600
                );
                
                // Log the call count
                Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug(
                    'TL-CP: ' . __CLASS__ . '::[' . count($productionIntervals) . ' steps, ~' 
                    . Stephino_Rpg_Config::get()->core()->getCronInterval() . ' min./step, first ' 
                    . Stephino_Rpg_Config::get()->core()->getCronAccuracy() . ' hours]'
                );

                // Start the serial steps
                foreach ($productionIntervals as list($newCheckPointTime, $newProductionTime)) {
                    $this->step($newCheckPointTime, $newProductionTime, true);
                }

                // Stop here
                return;
            }
        }
        
        // Get the data
        $data = $this->getData();
        
        // Prepare the Research Fields data (read-only)
        $supportResearchData = $this->getData(Stephino_Rpg_TimeLapse_Support_ResearchFields::KEY);
        
        // Prepare the Entities data (read-only)
        $supportEntities = $this->getData(Stephino_Rpg_TimeLapse_Support_Entities::KEY);
        
        // Prepare the Queues data (read-only, for premium modifiers)
        $queuesData = $this->getData(Stephino_Rpg_TimeLapse_Queues::KEY);
        
        // Prepare the instant data holder
        $instantData = array();
        
        // Store the Metropolises
        $capitals = array();
        
        // Prepare the city populations
        $populations = array();
        
        // Prepare the maximum values for each city
        $cityMaxes = array();
        
        // Store single passes
        $singlePasses = array(
            Stephino_Rpg_Db_Table_Cities::COL_ID  => array(),
            Stephino_Rpg_Db_Table_Islands::COL_ID => array(),
        );
        
        // Store abundance values
        $islandAbundanceValues = array();
        
        // Go through the buildings (cities, islands and users data also available)
        foreach ($data as $dataKey => $dataRow) {
            // Get the island ID
            $islandId = $dataRow[Stephino_Rpg_Db_Table_Islands::COL_ID];
            
            // Get the city id
            $cityId = $dataRow[Stephino_Rpg_Db_Table_Cities::COL_ID];
            
            // Get the city configuration
            $configCity = Stephino_Rpg_Config::get()->cities()->getById(
                $dataRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_CONFIG_ID]
            );
            
            // City is Metropolis
            if (!isset($capitals[$cityId])) {
                $capitals[$cityId] = (1 === intval($dataRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_IS_CAPITAL]));
            }
            
            // Store city population
            if (!isset($populations[$cityId])) {
                $populations[$cityId] = $dataRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION];
            }
            
            // Valid building configuration
            if (null !== $configBuilding = Stephino_Rpg_Config::get()->buildings()->getById(
                $dataRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID]
            )) {
                // Get the current building level
                $levelBuilding = $dataRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL];
                
                // Instant resources (building level)
                $this->_resourcesInstant(
                    $instantData, 
                    $cityId, 
                    $configBuilding, 
                    $levelBuilding
                );
            }
            
            // Instant resources (island level)
            if (!in_array(
                $dataRow[Stephino_Rpg_Db_Table_Islands::COL_ID], 
                $singlePasses[Stephino_Rpg_Db_Table_Islands::COL_ID]
            )) {
                // Mark this island's modifiers as handled
                $singlePasses[Stephino_Rpg_Db_Table_Islands::COL_ID][] = $dataRow[Stephino_Rpg_Db_Table_Islands::COL_ID];
                
                // Valid island configuration
                if (null !== $configIsland = Stephino_Rpg_Config::get()->islands()->getById(
                    $dataRow[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_CONFIG_ID]
                )) {
                    $islandAbundanceValues[$islandId] = array(
                        Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1 => $configIsland->getResourceExtra1Abundance(),
                        Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2 => $configIsland->getResourceExtra2Abundance(),
                    );
                }
                
                // Valid island statue configuration
                if (null !== $configIslandStatue = Stephino_Rpg_Config::get()->islandStatues()->getById(
                    $dataRow[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_STATUE_CONFIG_ID]
                )) {
                    $this->_resourcesInstant(
                        $instantData, 
                        $cityId, 
                        $configIslandStatue, 
                        $dataRow[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_STATUE_LEVEL]
                    );
                }
            }
            
            // Once per city
            if (!in_array($cityId, $singlePasses[Stephino_Rpg_Db_Table_Cities::COL_ID])) {
                // Mark this city's modifiers as handled
                $singlePasses[Stephino_Rpg_Db_Table_Cities::COL_ID][] = $cityId;
                    
                // Base satisfaction is handled at city level
                if (!isset($instantData[$cityId])) {
                    $instantData[$cityId] = array();
                }
                if (!isset($instantData[$cityId][Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_SATISFACTION])) {
                    $instantData[$cityId][Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_SATISFACTION] = 0;
                }
                $instantData[$cityId][Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_SATISFACTION] += (
                    null === $configCity 
                        ? 0 
                        : Stephino_Rpg_Utils_Config::getPolyValue(
                            $configCity->getSatisfactionPolynomial(),
                            $dataRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL], 
                            $configCity->getSatisfaction()
                        )
                );

                // Government
                if (null !== $configGovernment = Stephino_Rpg_Config::get()->governments()->getById(
                    $dataRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_GOVERNMENT_CONFIG_ID]
                )) {
                    // Instant resources (city level)
                    $this->_resourcesInstant(
                        $instantData, 
                        $cityId, 
                        $configGovernment, 
                        $dataRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL]
                    );
                }
                
                // Research fields
                foreach ($supportResearchData as $researchField) {
                    if (null !== $configResearchField = Stephino_Rpg_Config::get()->researchFields()->getById(
                        $researchField[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_CONFIG_ID]
                    )) {
                        // Get the research field level
                        $researchFieldLevel = $researchField[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_LEVEL];
                        
                        // Research finished
                        if ($researchFieldLevel > 0) {
                            // Override abundance factors
                            if ($configResearchField->getResourceExtra1Abundant()) {
                                $islandAbundanceValues[$islandId][Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1] = 100;
                            }
                            if ($configResearchField->getResourceExtra2Abundant()) {
                                $islandAbundanceValues[$islandId][Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2] = 100;
                            }
                        }
                        
                        // Instant resources (city level)
                        $this->_resourcesInstant(
                            $instantData, 
                            $cityId, 
                            $configResearchField, 
                            $researchFieldLevel
                        );
                    }
                }
                
                // Premium modifers
                foreach ($queuesData as $queueRow) {
                    if (Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_PREMIUM == $queueRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_TYPE]) {
                        // Prepare the configuration object
                        if (null !== $configPremiumModifier = Stephino_Rpg_Config::get()->premiumModifiers()->getById(
                            $queueRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID]
                        )) {
                            // Override abundance factors
                            if ($configPremiumModifier->getResourceExtra1Abundant()) {
                                $islandAbundanceValues[$islandId][Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1] = 100;
                            }
                            if ($configPremiumModifier->getResourceExtra2Abundant()) {
                                $islandAbundanceValues[$islandId][Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2] = 100;
                            }
                            
                            // Append the instant resources
                            $this->_resourcesInstant(
                                $instantData, 
                                $cityId, 
                                $configPremiumModifier, 
                                $queueRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_QUANTITY]
                            );
                        }
                    }
                }
            }
            
            // Ships/Units
            foreach ($supportEntities as $shipOrUnit) {
                // Current city
                if ($shipOrUnit[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CITY_ID] == $dataRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CITY_ID]) {
                    // Get the entity configuration id
                    $entityConfigId = $shipOrUnit[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID];

                    // Prepare the configuration object
                    $configEntity = null;
                    switch ($shipOrUnit[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE]) {
                        case Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_SHIP:
                            $configEntity = Stephino_Rpg_Config::get()->ships()->getById($entityConfigId);
                            break;

                        case Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT:
                            $configEntity = Stephino_Rpg_Config::get()->units()->getById($entityConfigId);
                            break;
                    }

                    // Valid configuration
                    if (null !== $configEntity && null !== $configEntity->getBuilding()) {
                        // Current building
                        if ($configEntity->getBuilding()->getId() == $dataRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID]) {
                            // Add the resources
                            $this->_resourcesInstant(
                                $instantData, 
                                $cityId, 
                                $configEntity, 
                                $dataRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL],
                                $shipOrUnit[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT]
                            );
                        }
                    }
                }
            }
            
            // Value not defined
            if (!isset($cityMaxes[$cityId])) {
                // Store the city max
                $cityMaxes[$cityId] = array(
                    // Max population
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION => null === $configCity 
                        ? 0 
                        : Stephino_Rpg_Utils_Config::getPolyValue(
                            $configCity->getMaxPopulationPolynomial(),
                            $dataRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL], 
                            $configCity->getMaxPopulation()
                        ),

                    // Max storage
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_STORAGE => null === $configCity 
                        ? 0 
                        : Stephino_Rpg_Utils_Config::getPolyValue(
                            $configCity->getMaxStoragePolynomial(),
                            $dataRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL], 
                            $configCity->getMaxStorage()
                        ),
                );
            }
        }
        
        // Store the instant resource data
        foreach ($instantData as $cityId => $cityData) {
            foreach ($cityData as $columnName => $columnValue) {
                // Satisfaction tweaks
                if (Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_SATISFACTION === $columnName) {
                    // Metropolis boost
                    if ($capitals[$cityId]) {
                        // Get the satisfaction boost
                        $satisfactionBoost = Stephino_Rpg_Config::get()->core()->getCapitalSatisfactionBonus();
                        if ($satisfactionBoost > 0) {
                            if ($columnValue < 0) {
                                // Lessen negative satisfaction
                                $columnValue *= (1 - $satisfactionBoost/100);
                            } else {
                                // Increase positive satisfaction
                                $columnValue *= (1 + $satisfactionBoost/100);
                            }
                        }
                    }

                    // Negative values not allowed
                    if ($columnValue < 0) {
                        $columnValue = 0;
                    }
                }
                
                // Update the dataset
                foreach ($data as $dataKey => $dataRow) {
                    if ($cityId == $dataRow[Stephino_Rpg_Db_Table_Cities::COL_ID]) {
                        $data[$dataKey][$columnName] = $columnValue;
                    }
                }
            }
        }

        // Store single passes
        $singlePasses = array(
            Stephino_Rpg_Db_Table_Cities::COL_ID  => array(),
            Stephino_Rpg_Db_Table_Islands::COL_ID => array(),
        );
        
        // Go through the buildings (cities, islands and users data also available)
        foreach ($data as $dataKey => $dataRow) {
            // Get the city id
            $cityId = $dataRow[Stephino_Rpg_Db_Table_Cities::COL_ID];
            
            // Prepare the maximum values array
            $maxValues = isset($cityMaxes[$cityId]) ? $cityMaxes[$cityId] : array();
            
            // Prepare the building capacity
            $capacity = 1;
            
            // Valid building configuration
            if (null !== $configBuilding = Stephino_Rpg_Config::get()->buildings()->getById(
                $dataRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID]
            )) {
                // Get the current building level
                $levelBuilding = $dataRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL];
                
                do {
                    // Not using workers
                    if (!$configBuilding->getUseWorkers()) {
                        // Disperse workers into general population - config change fix to unblock unused workers
                        $data[$dataKey][Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_WORKERS] = 0;
                        
                        // Main building - use the entire population instead
                        if ($configBuilding->isMainBuilding()) {
                            // Get the maximum population
                            $maxPopulation = isset($maxValues[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION]) 
                                ? $maxValues[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION] 
                                : 0;

                            // Upper limit defined
                            if ($maxPopulation > 0) {
                                // City population / max city population
                                $capacity = $dataRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION] / $maxPopulation;
                            }
                        }
                        break;
                    }
                    
                    // Get the maximum number of workers
                    $workersCapacity = Stephino_Rpg_Utils_Config::getPolyValue(
                        $configBuilding->getWorkersCapacityPolynomial(),
                        $levelBuilding, 
                        $configBuilding->getWorkersCapacity()
                    );

                    // Building workers / max building workers
                    if ($workersCapacity > 0) {
                        $capacity = $dataRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_WORKERS] / $workersCapacity;
                    }
                } while(false);
                
                // Additive resources (building level)
                $this->_resourcesAdditive(
                    $data, 
                    $dataRow, 
                    $configBuilding, 
                    $levelBuilding, 
                    $productionTime, 
                    $capacity, 
                    $maxValues, 
                    $islandAbundanceValues
                );
            }
            
            // Once per city
            if (!in_array($cityId, $singlePasses[Stephino_Rpg_Db_Table_Cities::COL_ID])) {
                // Mark this city's modifiers as handled
                $singlePasses[Stephino_Rpg_Db_Table_Cities::COL_ID][] = $cityId;
                
                // Valid government configuration
                if (null !== $configGovernment = Stephino_Rpg_Config::get()->governments()->getById(
                    $dataRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_GOVERNMENT_CONFIG_ID]
                )) {
                    // Additive resources (city level)
                    $this->_resourcesAdditive(
                        $data, 
                        $dataRow, 
                        $configGovernment, 
                        $dataRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL], 
                        $productionTime, 
                        1, 
                        $maxValues, 
                        $islandAbundanceValues
                    );
                }
                
                // Premium modifers
                foreach ($queuesData as $queueRow) {
                    // Premium modifier enabled
                    if (Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_PREMIUM == $queueRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_TYPE]) {
                        // Prepare the configuration object
                        $configPremiumModifier = Stephino_Rpg_Config::get()->premiumModifiers()->getById(
                            $queueRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID]
                        );
                        
                        // Append the instant resources
                        if (null !== $configPremiumModifier) {
                            $this->_resourcesAdditive(
                                $data, 
                                $dataRow, 
                                $configPremiumModifier, 
                                $queueRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_QUANTITY], 
                                $productionTime, 
                                1, 
                                $maxValues, 
                                $islandAbundanceValues
                            );
                        }
                    }
                }
                    
                // Research fields
                foreach ($supportResearchData as $researchField) {
                    // Valid research field id
                    if (null !== $configResearchField = Stephino_Rpg_Config::get()->researchFields()->getById(
                        $researchField[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_CONFIG_ID]
                    )) {
                        // Get the research field level
                        $researchFieldLevel = $researchField[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_LEVEL];
                        
                        // Add the resources
                        $this->_resourcesAdditive(
                            $data, 
                            $dataRow, 
                            $configResearchField, 
                            $researchFieldLevel, 
                            $productionTime, 
                            1, 
                            $maxValues, 
                            $islandAbundanceValues
                        );
                    }
                }
            }
            
            // Ships/Units
            foreach ($supportEntities as $shipOrUnit) {
                // Current city only
                if ($shipOrUnit[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CITY_ID] == $dataRow[Stephino_Rpg_Db_Table_Buildings::COL_ID]) {
                    // Get the entity configuration id
                    $entityConfigId = $shipOrUnit[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID];

                    // Prepare the configuration object
                    $configEntity = null;
                    switch ($shipOrUnit[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE]) {
                        case Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_SHIP:
                            $configEntity = Stephino_Rpg_Config::get()->ships()->getById($entityConfigId);
                            break;

                        case Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT:
                            $configEntity = Stephino_Rpg_Config::get()->units()->getById($entityConfigId);
                            break;
                    }

                    // Valid configuration
                    if (null !== $configEntity && null !== $configEntity->getBuilding()) {
                        if ($configEntity->getBuilding()->getId() == $dataRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID]) {
                            // Add the resources
                            $this->_resourcesAdditive(
                                $data, 
                                $dataRow, 
                                $configEntity, 
                                $dataRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL], 
                                $productionTime, 
                                1, 
                                $maxValues, 
                                $islandAbundanceValues,
                                intval($shipOrUnit[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT])
                            );
                        }
                    }
                }
            }
            
            // Additive resources (island statue level)
            if (!in_array($dataRow[Stephino_Rpg_Db_Table_Islands::COL_ID], $singlePasses[Stephino_Rpg_Db_Table_Islands::COL_ID])) {
                // Mark this island's modifiers as handled
                $singlePasses[Stephino_Rpg_Db_Table_Islands::COL_ID][] = $dataRow[Stephino_Rpg_Db_Table_Islands::COL_ID];
                    
                // Valid island statue configuration
                if (null !== $configIslandStatue = Stephino_Rpg_Config::get()->islandStatues()->getById(
                    $dataRow[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_STATUE_CONFIG_ID]
                )) {
                    $this->_resourcesAdditive(
                        $data, 
                        $dataRow, 
                        $configIslandStatue, 
                        $dataRow[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_STATUE_LEVEL], 
                        $productionTime, 
                        1, 
                        $maxValues, 
                        $islandAbundanceValues
                    );
                }
            }
        }
        
        // Save the data
        $this->setData(self::KEY, $data);
    }
    
    /**
     * Get the definition for removals on save()
     * 
     * @return array
     */
    protected function _getDeleteStructure() {
        // No buildings/cities/islands/users are removed when computing resources
        return array();
    }
    
    /**
     * Get the table structure that needs to be updated on save()
     * 
     * @return array
     */
    protected function _getUpdateStructure() {
        return array(
            $this->getDb()->tableUsers()->getTableName()  => array(
                Stephino_Rpg_Db_Table_Users::COL_ID,
                array(
                    Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD,
                    Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH,
                    Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM,
                    Stephino_Rpg_Db_Table_Users::COL_USER_SCORE,
                    Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_VICTORIES,
                    Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_DRAWS,
                    Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_DEFEATS,
                    Stephino_Rpg_Db_Table_Users::COL_USER_LAST_TICK,
                    Stephino_Rpg_Db_Table_Users::COL_USER_LAST_TICK_AJAX,
                )
            ),
            $this->getDb()->tableCities()->getTableName() => array(
                Stephino_Rpg_Db_Table_Cities::COL_ID,
                array(
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL,
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA,
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_BETA,
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_GAMMA,
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1,
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2,
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_STORAGE,
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION,
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_SATISFACTION,
                ),
            ),
            $this->getDb()->tableBuildings()->getTableName() => array(
                Stephino_Rpg_Db_Table_Buildings::COL_ID,
                array(
                    Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL,
                    Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_WORKERS,
                )
            ),
        );
    }
    
    /**
     * Initialize the worker data
     * 
     * @return array
     */
    protected function _initData() {
        // Prepare table names
        $tableUsers = $this->getDb()->tableUsers()->getTableName();
        $tableIslands = $this->getDb()->tableIslands()->getTableName();
        $tableCities = $this->getDb()->tableCities()->getTableName();
        $tableBuildings = $this->getDb()->tableBuildings()->getTableName();
        
        // Prepare the query
        $query = "SELECT * FROM `$tableBuildings`" . PHP_EOL
                . "  INNER JOIN `$tableCities` ON `$tableBuildings`.`" . Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CITY_ID . "` = "
                    . "`$tableCities`.`" . Stephino_Rpg_Db_Table_Cities::COL_ID . "`" . PHP_EOL
                . "  INNER JOIN `$tableIslands` ON `$tableBuildings`.`" . Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_ISLAND_ID . "` = "
                    . "`$tableIslands`.`" . Stephino_Rpg_Db_Table_Islands::COL_ID . "`" . PHP_EOL
                . "  INNER JOIN `$tableUsers` ON `$tableBuildings`.`" . Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_USER_ID . "` = "
                    . "`$tableUsers`.`" . Stephino_Rpg_Db_Table_Users::COL_ID . "` " . PHP_EOL
            . "WHERE `$tableBuildings`.`" . Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_USER_ID . "` = {$this->_userId}";
        Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($query . PHP_EOL);
        
        // Get the results
        return $this->getDb()->getWpdb()->get_results($query, ARRAY_A);
    }
    
    /**
     * Instantaneous resources
     * 
     * @param array                             $instantData  Instant Data array
     * @param int                               $cityId       Instant Data key
     * @param Stephino_Rpg_Config_Item_Abstract $configObject Item configuration object
     * @param int                               $itemLevel    Item level
     * @param int                               $multiplier   (optional) Effect multiplier (for entities); default <b>1</b>
     */
    protected function _resourcesInstant(&$instantData, $cityId, $configObject, $itemLevel, $multiplier = 1) {
        // Get the modifier
        if ($configObject instanceof Stephino_Rpg_Config_Item_Abstract 
            && in_array(Stephino_Rpg_Config_Trait_Modifier::class, class_uses($configObject))
            && null !== $modifier = $configObject->getModifier()) {
            // Prepare the incremental resources
            $instantResourcesArray = array(
                Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_SATISFACTION => $modifier->getEffectMetricSatisfaction(),
            );

            // Go through each one
            foreach ($instantResourcesArray as $resourceKey => $resourceValue) {
                // Satisfaction metric not set
                if (null === $resourceValue) {
                    continue;
                }
                
                // Prepare the production value
                $value = Stephino_Rpg_Utils_Config::getPolyValue(
                    $configObject->getModifierPolynomial(),
                    $itemLevel, 
                    $resourceValue
                ) * $multiplier;

                // Need to update the data hoder
                if ($value > 0) {
                    // Initialize the data
                    if (!isset($instantData[$cityId])) {
                        $instantData[$cityId] = array();
                    }
                    if (!isset($instantData[$cityId][$resourceKey])) {
                        $instantData[$cityId][$resourceKey] = 0;
                    }
                    
                    // Add our value
                    $instantData[$cityId][$resourceKey] += $value;
                }
            }
        }
    }
    
    /**
     * Incremental resources
     * 
     * @param &array                            $data                  Current data array
     * @param array                             $dataRow               Data row
     * @param Stephino_Rpg_Config_Item_Abstract $configObject          Item configuration object
     * @param int                               $itemLevel             Item level
     * @param int                               $productionTime        Production time
     * @param float                             $capacity              Building workers capacity (between 0 and 1)
     * @param array                             $maxValues             Associative array of maximum values for each resource (by city)
     * @param array                             $islandAbundanceValues Abundance factors for extra resources by Island ID
     * @param int                               $multiplier            (optional) Effect multiplier (for entities); default <b>1</b>
     */
    protected function _resourcesAdditive(&$data, $dataRow, $configObject, $itemLevel, $productionTime, $capacity = 1, $maxValues, $islandAbundanceValues, $multiplier = 1) {
        // Nothing to do here
        if (0 == $productionTime || 0 == $itemLevel) {
            return;
        }
        
        // Get the modifier
        if ($configObject instanceof Stephino_Rpg_Config_Item_Abstract 
            && in_array(Stephino_Rpg_Config_Trait_Modifier::class, class_uses($configObject))
            && null !== $modifier = $configObject->getModifier()) {
            // Prepare the incremental resources
            $incrementalResourcesArray = array(
                Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION  => $modifier->getEffectMetricPopulation(),
                Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_STORAGE     => $modifier->getEffectMetricStorage(),
                Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA     => $modifier->getEffectResourceAlpha(),
                Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_BETA      => $modifier->getEffectResourceBeta(),
                Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_GAMMA     => $modifier->getEffectResourceGamma(),
                Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1   => $modifier->getEffectResourceExtra1(),
                Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2   => $modifier->getEffectResourceExtra2(),
                Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD       => $modifier->getEffectResourceGold(),
                Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH   => $modifier->getEffectResourceResearch(),
                Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM        => $modifier->getEffectResourceGem(),
            );
            
            // Get the island ID
            $islandId = $dataRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID];

            // Go through each one
            foreach ($incrementalResourcesArray as $resourceKey => $productionInitialHourlyValue) {
                // Prepare the production capacity
                $productionCapacity = $capacity;
                
                // Go through the exceptions
                switch ($resourceKey) {
                    // Population and storage are not affected by workers or population (to avoid lock-downs)
                    case Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION:
                    case Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_STORAGE:
                        $productionCapacity = 1;
                        break;
                    
                    case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1:
                    case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2:
                        if (isset($islandAbundanceValues[$islandId]) 
                            && isset($islandAbundanceValues[$islandId][$resourceKey])
                            && 100 != $islandAbundanceValues[$islandId][$resourceKey]) {
                            $productionCapacity = ($capacity * $islandAbundanceValues[$islandId][$resourceKey] / 100.0);
                        }
                        break;
                }
                
                // Prepare the production delta
                $delta = Stephino_Rpg_Utils_Config::getPolyValue(
                    $configObject->getModifierPolynomial(),
                    $itemLevel, 
                    $productionInitialHourlyValue, 
                    $productionTime
                ) * $productionCapacity * $multiplier;
                
                // Need to increment the table
                if (0 != $delta) {
                    // Population delta
                    if (Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION === $resourceKey) {
                        // Population migration is governed by overall satisfaction
                        $delta *= $this->getDb()->modelCities()->getGrowthFactor(
                            $dataRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION],
                            $dataRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_SATISFACTION]
                        );
                    }

                    // Prepare the identifier
                    $identifier = Stephino_Rpg_Db_Table_Cities::COL_ID;
                    
                    // Differs by column name
                    switch($resourceKey) {
                        case Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD:
                        case Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH:
                        case Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM:
                            $identifier = Stephino_Rpg_Db_Table_Users::COL_ID;
                            break;
                    }
                    
                    // Go through all the rows
                    foreach ($data as &$dRow) {
                        if ($dataRow[$identifier] === $dRow[$identifier]) {
                            $dRow[$resourceKey] += $delta;
                            
                            // Maximum values
                            if (isset($maxValues[$resourceKey]) && $dRow[$resourceKey] > $maxValues[$resourceKey]) {
                                $dRow[$resourceKey] = $maxValues[$resourceKey];
                            }
                            
                            // Maximum Storage
                            if (Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_STORAGE == $resourceKey 
                                && !isset($maxValues[Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA])) {
                                // Impose limits on all other resources based on the current max storage
                                $maxValues[Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA]   = $dRow[$resourceKey];
                                $maxValues[Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_BETA]    = $dRow[$resourceKey];
                                $maxValues[Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_GAMMA]   = $dRow[$resourceKey];
                                $maxValues[Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1] = $dRow[$resourceKey];
                                $maxValues[Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2] = $dRow[$resourceKey];
                            }
                        }
                    }
                }
            }
        }
    }
}

/*EOF*/