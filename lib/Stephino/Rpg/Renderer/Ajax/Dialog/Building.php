<?php

/**
 * ThemeWarlock - Stephino_Rpg_Renderer_Ajax_Dialog_Building
 * 
 * @title      Dialog::Building
 * @desc       Building dialogs
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    ThemeWarlock
 * @since      TW 1.0
 */
class Stephino_Rpg_Renderer_Ajax_Dialog_Building extends Stephino_Rpg_Renderer_Ajax_Dialog {

    // Dialog templates
    const TEMPLATE_INFO                = 'building/building-info';
    const TEMPLATE_INFO_RESEARCH_AREAS = 'building/building-info-research-areas';
    const TEMPLATE_INFO_METRICS        = 'building/building-info-metrics';
    const TEMPLATE_INFO_ENTITIES       = 'building/building-info-entities';
    const TEMPLATE_UPGRADE             = 'building/building-upgrade';
    const TEMPLATE_UPGRADE_CANCEL      = 'building/building-upgrade-cancel';
    const TEMPLATE_MARKET              = 'building/building-market';
    
    // Request keys
    const REQUEST_BUILDING_CONFIG_ID = 'buildingConfigId';
    
    // Result keys
    const RESULT_BUILDING_CONFIG = 'buildingConfig';
    const RESULT_BUILDING_QUEUE  = 'buildingQueue';
    const RESULT_BUILDING_COST   = 'buildingCost';
    
    /**
     * Show the building information
     * 
     * @param array $data Data containing <ul>
     * <li><b>cityId</b> (int) City ID</li>
     * <li><b>buildingConfigId</b> (int) Building Configuration ID</li>
     * </ul>
     * @return array Array of building name and extra building information
     * @throws Exception
     */
    public static function ajaxInfo($data) {
        // Get the building information
        /* @var $buildingConfig Stephino_Rpg_Config_Building */
        list($buildingData, $buildingConfig, $cityData, $queueData, $costData, $productionData, $affordList) = Stephino_Rpg_Renderer_Ajax_Action::getBuildingInfo(
            isset($data[self::REQUEST_CITY_ID]) ? intval($data[self::REQUEST_CITY_ID]) : null, 
            isset($data[self::REQUEST_BUILDING_CONFIG_ID]) ? intval($data[self::REQUEST_BUILDING_CONFIG_ID]) : null
        );
        
        // Research data
        list($researchAreaConfigs, $researchQueueData) = Stephino_Rpg_Renderer_Ajax_Action::getResearchBuildingInfo($buildingConfig);
        
        // Initialize entities
        $unitsData = array();
        $shipsData = array();
        
        // Only these entity configs are allowed
        foreach (Stephino_Rpg_Config::get()->units()->getAll() as $unitConfig) {
            if (null !== $unitConfig->getBuilding() && $unitConfig->getBuilding()->getId() == $buildingConfig->getId()) {
                $unitsData[$unitConfig->getId()] = array(
                    $unitConfig,
                    null,
                    null
                );
            }
        }
        foreach (Stephino_Rpg_Config::get()->ships()->getAll() as $shipConfig) {
            if (null !== $shipConfig->getBuilding() && $shipConfig->getBuilding()->getId() == $buildingConfig->getId()) {
                $shipsData[$shipConfig->getId()] = array(
                    $shipConfig,
                    null,
                    null
                );
            }
        }
        
        // This building hosts units or ships
        if (count($unitsData) || count($shipsData)) {
            // Entities data
            if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_Entities::KEY)->getData())) {
                foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_Entities::KEY)->getData() as $entityDbRow) {
                    // Garrisoned in this city
                    if ($cityData[Stephino_Rpg_Db_Table_Cities::COL_ID] == $entityDbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CITY_ID]) {
                        // Get the configuration ID
                        $entityConfigId = $entityDbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID];

                        // Assign to data sets
                        switch ($entityDbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE]) {
                            case Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT:
                                if (isset($unitsData[$entityConfigId]) && null === $unitsData[$entityConfigId][1]) {
                                    $unitsData[$entityConfigId][1] = $entityDbRow;
                                    $unitsData[$entityConfigId][2] = Stephino_Rpg_Renderer_Ajax_Action::getEntityQueue(
                                        $entityDbRow[Stephino_Rpg_Db_Table_Entities::COL_ID],
                                        Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_UNIT
                                    );
                                }
                                break;

                            case Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_SHIP:
                                if (isset($shipsData[$entityConfigId]) && null === $shipsData[$entityConfigId][1]) {
                                    $shipsData[$entityConfigId][1] = $entityDbRow;
                                    $shipsData[$entityConfigId][2] = Stephino_Rpg_Renderer_Ajax_Action::getEntityQueue(
                                        $entityDbRow[Stephino_Rpg_Db_Table_Entities::COL_ID],
                                        Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_SHIP
                                    );
                                }
                                break;
                        }
                    }
                }
            }
        }
        
        // City Configuration
        $cityConfig = Stephino_Rpg_Config::get()->cities()->getById($cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_CONFIG_ID]);
        
        // Invalid city configuration
        if (null === $cityConfig) {
            throw new Exception(
                sprintf(
                    __('Invalid configuration (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigCityName()
                )
            );
        }
        
        // Prepare the capacity
        $workersCapacity = 0;
        
        // And the available workers
        $workersAvailable = $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION];
        
        // Using workers
        if ($buildingConfig->getUseWorkers() && is_array($buildingData)) {
            // Get the maximum number of workers
            $workersCapacity = Stephino_Rpg_Utils_Config::getPolyValue(
                $buildingConfig->getWorkersCapacityPolynomial(),
                $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL], 
                $buildingConfig->getWorkersCapacity()
            );
            
            // Use the cached time-lapse resources (avoids an extra, unnecessary DB query)
            if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
                foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $dbRow) {
                    // Our city
                    if ($dbRow[Stephino_Rpg_Db_Table_Cities::COL_ID] == $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]) {
                        // All other buildings
                        if ($dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID] != $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID]) {
                            $workersAvailable -= intval($dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_WORKERS]);
                        }
                    }
                }
            }

            // Set available workers min and max
            if ($workersAvailable < 0) {
                $workersAvailable = 0;
            }
            if ($workersAvailable > $workersCapacity) {
                $workersAvailable = $workersCapacity;
            }
        }
        
        // Calculate the maximum population
        $cityMaxPopulation = Stephino_Rpg_Utils_Config::getPolyValue(
            $cityConfig->getMaxPopulationPolynomial(),
            $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL], 
            $cityConfig->getMaxPopulation()
        );
        
        // Core configuration
        $coreConfig = Stephino_Rpg_Config::get()->core();
        
        // Get the government config
        $governmentConfig = Stephino_Rpg_Config::get()->governments()->getById(
            $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_GOVERNMENT_CONFIG_ID]
        );
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_INFO);

        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE           => $buildingConfig->getName(),
                self::RESULT_DATA            => $buildingData,
                self::RESULT_BUILDING_CONFIG => $buildingConfig->toArray(),
                self::RESULT_BUILDING_QUEUE  => $queueData,
            ),
            $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]
        );
    }
    
    /**
     * Show the resource trading dialog
     * 
     * @param array $data Data containing <ul>
     * <li><b>cityId</b> (int) City ID</li>
     * <li><b>buildingConfig</b> (int) Building Configuration ID</li>
     * </ul>
     * @return array 
     * @throws Exception
     */
    public static function ajaxTrade($data) {
        // Get the building information
        /* @var $buildingConfig Stephino_Rpg_Config_Building */
        list($buildingData, $buildingConfig, $cityData) = Stephino_Rpg_Renderer_Ajax_Action::getBuildingInfo(
            isset($data[self::REQUEST_CITY_ID]) ? intval($data[self::REQUEST_CITY_ID]) : null, 
            isset($data[self::REQUEST_BUILDING_CONFIG_ID]) ? intval($data[self::REQUEST_BUILDING_CONFIG_ID]) : null
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
        
        // Still not constructed
        if ($buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL] < 1) {
            throw new Exception(__('Under construction', 'stephino-rpg'));
        }
        
        // Market not enabled
        if (!Stephino_Rpg_Config::get()->core()->getMarketEnabled() || null === Stephino_Rpg_Config::get()->core()->getMarketBuilding()) {
            throw new Exception(__('The market is not enabled', 'stephino-rpg'));
        }
        
        // This is not a marketplace building
        if ($buildingConfig->getId() != Stephino_Rpg_Config::get()->core()->getMarketBuilding()->getId()) {
            throw new Exception(__('Invalid request', 'stephino-rpg'));
        }
        
        // Get the minimum trade ratio
        $tradeRatioMin = null;
        
        // Prepare the resources
        $resourceData = Stephino_Rpg_Renderer_Ajax_Action::getResourceData();
        foreach ($resourceData as $resKey => &$resValue) {
            if (in_array($resKey, array(
                Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD,
                Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM,
                Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH,
            ))) {
                unset($resourceData[$resKey]);
                continue;
            }

            // Prepare the trade ratio
            $tradeRatio = 0;
            switch ($resKey) {
                case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA:
                    $tradeRatio = Stephino_Rpg_Utils_Config::getPolyValue(
                        Stephino_Rpg_Config::get()->core()->getMarketPolynomial(), 
                        $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL], 
                        Stephino_Rpg_Config::get()->core()->getMarketResourceAlpha()
                    );
                    break;

                case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_BETA:
                    $tradeRatio = Stephino_Rpg_Utils_Config::getPolyValue(
                        Stephino_Rpg_Config::get()->core()->getMarketPolynomial(), 
                        $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL], 
                        Stephino_Rpg_Config::get()->core()->getMarketResourceBeta()
                    );
                    break;

                case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_GAMMA:
                    $tradeRatio = Stephino_Rpg_Utils_Config::getPolyValue(
                        Stephino_Rpg_Config::get()->core()->getMarketPolynomial(), 
                        $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL], 
                        Stephino_Rpg_Config::get()->core()->getMarketResourceGamma()
                    );
                    break;

                case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1:
                    $tradeRatio = Stephino_Rpg_Utils_Config::getPolyValue(
                        Stephino_Rpg_Config::get()->core()->getMarketPolynomial(), 
                        $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL], 
                        Stephino_Rpg_Config::get()->core()->getMarketResourceExtra1()
                    );
                    break;

                case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2:
                    $tradeRatio = Stephino_Rpg_Utils_Config::getPolyValue(
                        Stephino_Rpg_Config::get()->core()->getMarketPolynomial(), 
                        $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL], 
                        Stephino_Rpg_Config::get()->core()->getMarketResourceExtra2()
                    );
                    break;
            }

            // Invalid trade ratio
            if ($tradeRatio == 0) {
                unset($resourceData[$resKey]);
                continue;
            }
            
            // Store the trade ratio
            $resValue[] = $tradeRatio;
            
            // Store the minimum trade ratio
            if (null === $tradeRatioMin || $tradeRatioMin > $tradeRatio) {
                $tradeRatioMin = $tradeRatio;
            }
        }
        
        // Large sized modal
        Stephino_Rpg_Renderer_Ajax::setModalSize(true);
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_MARKET);
        
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE           => 'Trade resources',
                self::RESULT_BUILDING_CONFIG => $buildingConfig->toArray(),
            ),
            $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]
        );
    }
    
    /**
     * Upgrade Confirmation
     * 
     * @param array $data Data containing <ul>
     * <li><b>cityId</b> (int) City ID</li>
     * <li><b>buildingConfigId</b> (int) Building Configuration ID</li>
     * </ul>
     * @return array Array of building name and extra building information
     * @throws Exception
     */
    public static function ajaxUpgrade($data) {
        // Get the building information
        /* @var $buildingConfig Stephino_Rpg_Config_Building */
        list($buildingData, $buildingConfig, $cityData, $queueData, $costData) = Stephino_Rpg_Renderer_Ajax_Action::getBuildingInfo(
            isset($data[self::REQUEST_CITY_ID]) ? intval($data[self::REQUEST_CITY_ID]) : null, 
            isset($data[self::REQUEST_BUILDING_CONFIG_ID]) ? intval($data[self::REQUEST_BUILDING_CONFIG_ID]) : null
        );
        
        // Get the Building requirements
        list($requirements, $requirementsMet) = Stephino_Rpg_Renderer_Ajax_Action::getRequirements(
            $buildingConfig,
            $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]
        );
        
        // Prepare the upgrade time in seconds
        $costTime = Stephino_Rpg_Db::get()->modelBuildings()->getBuildTime(
            $buildingConfig, 
            null === $buildingData 
                ? 0 
                : $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL]
        );
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_UPGRADE);
        
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE           => $buildingConfig->getName() . ' - ' . (!is_array($buildingData) ? 'Build' : 'Upgrade'),
                self::RESULT_DATA            => $buildingData,
                self::RESULT_BUILDING_CONFIG => $buildingConfig->toArray(),
                self::RESULT_BUILDING_QUEUE  => $queueData,
                self::RESULT_BUILDING_COST   => $costData,
            ),
            $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]
        );
    }
    
    /**
     * Cancel Upgrade Confirmation
     * 
     * @param array $data Data containing <ul>
     * <li><b>cityId</b> (int) City ID</li>
     * <li><b>buildingConfigId</b> (int) Building Configuration ID</li>
     * </ul>
     * @return array Array of building name and extra building information
     * @throws Exception
     */
    public static function ajaxUpgradeCancel($data) {
        // Get the building information
        /* @var $buildingConfig Stephino_Rpg_Config_Building */
        list($buildingData, $buildingConfig, $cityData, $queueData, $costData) = Stephino_Rpg_Renderer_Ajax_Action::getBuildingInfo(
            isset($data[self::REQUEST_CITY_ID]) ? intval($data[self::REQUEST_CITY_ID]) : null, 
            isset($data[self::REQUEST_BUILDING_CONFIG_ID]) ? intval($data[self::REQUEST_BUILDING_CONFIG_ID]) : null
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
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_UPGRADE_CANCEL);
        
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE           => $buildingConfig->getName() . ' - Cancel Upgrade',
                self::RESULT_DATA            => $buildingData,
                self::RESULT_BUILDING_CONFIG => $buildingConfig->toArray(),
                self::RESULT_BUILDING_QUEUE  => $queueData,
                self::RESULT_BUILDING_COST   => $costData,
            ),
            $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]
        );
    }
}

/* EOF */