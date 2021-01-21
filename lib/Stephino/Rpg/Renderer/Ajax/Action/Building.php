<?php

/**
 * Stephino_Rpg_Renderer_Ajax_Action_Building
 * 
 * @title      Action::Building
 * @desc       Building actions
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Renderer_Ajax_Action_Building extends Stephino_Rpg_Renderer_Ajax_Action {

    // Trade types
    const TRADE_BUY  = 'buy';
    const TRADE_SELL = 'sell';
    
    // Request keys
    const REQUEST_BUILDING_CONFIG_ID       = 'buildingConfigId';
    const REQUEST_BUILDING_WORKERS         = 'buildingWorkers';
    const REQUEST_BUILDING_TRADE_TYPE      = 'buildingTradeType';
    const REQUEST_BUILDING_TRADE_RESOURCES = 'buildingTradeResources';
    
    // Data keys
    const CELL_CONFIG_ACTION_AREA  = 'actionArea';
    const CELL_CONFIG_BUILDING_BKG = 'buildingBkg';
    
    /**
     * Assign workers
     * 
     * @param array $data Data containing <ul>
     * <li><b>cityId</b> (int) City ID</li>
     * <li><b>buildingConfigId</b> (int) Building Configuration ID</li>
     * <li><b>buildingWorkers</b> (int) Number of workers assigned to this building</li>
     * </ul>
     * @return mixed
     * @throws Exception
     */
    public static function ajaxProductionPreview($data) {
        // Get the building information
        /* @var $buildingConfig Stephino_Rpg_Config_Building */
        list($buildingData, $buildingConfig, $cityData, $queueData, $costData) = self::getBuildingInfo(
            isset($data[self::REQUEST_CITY_ID]) ? intval($data[self::REQUEST_CITY_ID]) : null, 
            isset($data[self::REQUEST_BUILDING_CONFIG_ID]) ? intval($data[self::REQUEST_BUILDING_CONFIG_ID]) : null
        );

        // Building not found
        if (!is_array($buildingData)) {
            throw new Exception(
                sprintf(
                    __('Not found (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigBuildingName()
                )
            );
        }
        
        // Get the worker count
        $workersAllotted = isset($data[self::REQUEST_BUILDING_WORKERS]) ? intval($data[self::REQUEST_BUILDING_WORKERS]) : null;
        
        // Invalid worker count
        if (null === $workersAllotted || $workersAllotted < 0) {
            throw new Exception(__('Invalid number of workers', 'stephino-rpg'));
        }
        
        // Maximum allowed value
        $workersCapacity = Stephino_Rpg_Utils_Config::getPolyValue(
            $buildingConfig->getWorkersCapacityPolynomial(), 
            $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL], 
            $buildingConfig->getWorkersCapacity()
        );

        // Value too high
        if ($workersAllotted > $workersCapacity) {
            throw new Exception(
                sprintf(
                    __('Cannot assign more than %d workers', 'stephino-rpg'), 
                    $workersCapacity
                )
            );
        }
        
        // Update the workers
        Stephino_Rpg_Db::get()->tableBuildings()->updateById(
            array(
                Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_WORKERS => $workersAllotted
            ),
            $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_ID]
        );
        
        // Get the production data
        $productionData = self::getProductionData(
            $buildingConfig,
            $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL],
            $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_ISLAND_ID],
            $buildingConfig->isMainBuilding() 
                ? $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION] 
                : $workersAllotted,
            $buildingConfig->isMainBuilding() 
                ? Stephino_Rpg_Config::get()->cities()->getById($cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_CONFIG_ID])
                : null,
            !$buildingConfig->isMainBuilding(),
            true
        );
        
        // Load the table
        require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
            Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_PRODUCTION
        );
        
        // Create the construction queue
        return Stephino_Rpg_Renderer_Ajax::wrap(
            $workersAllotted,
            $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CITY_ID]
        );
    }
    
    /**
     * Trade resources for gold
     * 
     * @param array $data Data containing <ul>
     * <li><b>cityId</b> (int) City ID</li>
     * <li><b>buildingConfigId</b> (int) Building Configuration ID</li>
     * <li><b>buildingTradeType</b> (string) Trade type (buy or sell)</li>
     * <li><b>buildingTradeResources</b> (array) Resources to trade</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxTrade($data) {
        // Get the building information
        /* @var $buildingConfig Stephino_Rpg_Config_Building */
        list($buildingData, $buildingConfig, $cityData) = self::getBuildingInfo(
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
        
        // Get the trade type
        $tradeType = isset($data[self::REQUEST_BUILDING_TRADE_TYPE]) ? trim($data[self::REQUEST_BUILDING_TRADE_TYPE]) : null;
        
        // Invalid trade type
        if (!in_array($tradeType, array(self::TRADE_BUY, self::TRADE_SELL))) {
            throw new Exception(__('Invalid trade type', 'stephino-rpg'));
        }
        
        // Prepare the resources
        $tradeResources = isset($data[self::REQUEST_BUILDING_TRADE_RESOURCES]) ? $data[self::REQUEST_BUILDING_TRADE_RESOURCES] : null;
        if (!is_array($tradeResources)) {
            throw new Exception(__('Invalid request', 'stephino-rpg'));
        }
        
        // Prepare the city updates
        $updatesCity = array();
        $cityDataId = $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID];
        
        // Get the total gold expense
        $tradeGold = 0;
        foreach ($tradeResources as $tradeResKey => $tradeResValue) {
            // Prepare the resource count
            $tradeResValue = abs($tradeResValue);

            // Invalid resource count
            if ($tradeResValue == 0) {
                continue;
            }

            // Prepare the trade ratio
            $tradeRatioCore = 0;
            switch ($tradeResKey) {
                case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA:
                    $tradeRatioCore = Stephino_Rpg_Config::get()->core()->getMarketResourceAlpha();
                    break;

                case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_BETA:
                    $tradeRatioCore = Stephino_Rpg_Config::get()->core()->getMarketResourceBeta();
                    break;

                case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_GAMMA:
                    $tradeRatioCore = Stephino_Rpg_Config::get()->core()->getMarketResourceGamma();
                    break;

                case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1:
                    $tradeRatioCore = Stephino_Rpg_Config::get()->core()->getMarketResourceExtra1();
                    break;

                case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2:
                    $tradeRatioCore = Stephino_Rpg_Config::get()->core()->getMarketResourceExtra2();
                    break;
            }

            // Valid trade ratio
            if ($tradeRatioCore > 0) {
                // Buying resources
                if (self::TRADE_BUY == $tradeType) {
                    $tradeRatioCore *= (1 + Stephino_Rpg_Config::get()->core()->getMarketGain() / 100);
                }
                
                if (!isset($updatesCity[$cityDataId])) {
                    $updatesCity[$cityDataId] = array();
                }
                
                // Selling resources
                if (self::TRADE_SELL == $tradeType) {
                    // Not enough resources
                    if (floor($cityData[$tradeResKey]) < $tradeResValue) {
                        $tradeResValue = floor($cityData[$tradeResKey]);
                    }
                }
                
                // Get the resource value in gold
                $tradeGold += $tradeResValue * Stephino_Rpg_Utils_Config::getPolyValue(
                    Stephino_Rpg_Config::get()->core()->getMarketPolynomial(), 
                    $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL], 
                    $tradeRatioCore
                );
                
                // Buying resources
                if (self::TRADE_BUY == $tradeType) {
                    // Too expensive
                    if (floor($buildingData[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD]) < $tradeGold) {
                        throw new Exception(__('Could not afford this transaction', 'stephino-rpg'));
                    }
                }
                
                // Move the resources
                $updatesCity[$cityDataId][$tradeResKey] = floatval($cityData[$tradeResKey]) + (self::TRADE_BUY == $tradeType ? $tradeResValue : -$tradeResValue);
                
                // Update the time-lapse references (for the wrap method to work)
                Stephino_Rpg_TimeLapse::get()
                    ->worker(Stephino_Rpg_TimeLapse_Resources::KEY)
                    ->updateRef(
                        Stephino_Rpg_Db_Table_Cities::COL_ID, 
                        $cityDataId, 
                        $tradeResKey, 
                        $updatesCity[$cityDataId][$tradeResKey]
                    );
            }
        }
        
        // Prepare the new gold total
        $tradeGoldTotal = floatval($buildingData[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD]) + (self::TRADE_BUY == $tradeType ? -$tradeGold : $tradeGold);
        Stephino_Rpg_TimeLapse::get()
            ->worker(Stephino_Rpg_TimeLapse_Resources::KEY)
            ->updateRef(
                Stephino_Rpg_Db_Table_Cities::COL_ID, 
                $cityDataId, 
                Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD, 
                $tradeGoldTotal
            );
        
        // Update the city resources
        if (isset($updatesCity[$cityDataId]) && count($updatesCity[$cityDataId])) {
            $multiUpdateQuery = Stephino_Rpg_Utils_Db::getMultiUpdate(
                $updatesCity, 
                Stephino_Rpg_Db::get()->tableCities()->getTableName(), 
                Stephino_Rpg_Db_Table_Cities::COL_ID
            );
            if (null !== $multiUpdateQuery) {
                Stephino_Rpg_Db::get()->getWpDb()->query($multiUpdateQuery);
            }
        }
        
        // Update the gold
        Stephino_Rpg_Db::get()->tableUsers()->updateById(
            array(
                Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD => $tradeGoldTotal
            ), 
            $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_USER_ID]
        );
        
        // Create the construction queue
        return Stephino_Rpg_Renderer_Ajax::wrap(
            true,
            $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]
        );
    }
    
    /**
     * Upgrade a building
     * 
     * @param array $data Data containing <ul>
     * <li><b>cityId</b> (int) City ID</li>
     * <li><b>buildingConfigId</b> (int) Building Configuration ID</li>
     * </ul>
     * @return mixed
     * @throws Exception
     */
    public static function ajaxUpgrade($data) {
        // Get the building information
        /* @var $buildingConfig Stephino_Rpg_Config_Building */
        list($buildingData, $buildingConfig, $cityData, $queueData, $costData) = self::getBuildingInfo(
            isset($data[self::REQUEST_CITY_ID]) ? intval($data[self::REQUEST_CITY_ID]) : null, 
            isset($data[self::REQUEST_BUILDING_CONFIG_ID]) ? intval($data[self::REQUEST_BUILDING_CONFIG_ID]) : null
        );

        // Already in queue
        if (null !== $queueData) {
            throw new Exception(__('Construction in progress', 'stephino-rpg'));
        }
        
        // Spend resources
        self::spend($costData, $cityData, 1, $buildingConfig);
        
        // Queue the building
        $queueId = Stephino_Rpg_Db::get()->modelQueues()->queueBuilding(
            $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID],
            $buildingConfig->getId()
        );
        
        // Get the build time
        $buildTime = Stephino_Rpg_Db::get()->modelBuildings()->getBuildTime(
            $buildingConfig, 
            null === $buildingData 
                ? 0
                : $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL]
        );
        
        // Create the construction queue
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                Stephino_Rpg_Renderer_Ajax_Dialog_Building::RESULT_BUILDING_QUEUE  => array(
                    self::DATA_QUEUE_ID         => $queueId,
                    self::DATA_QUEUE_TIME_TOTAL => $buildTime,
                    self::DATA_QUEUE_TIME_LEFT  => $buildTime,
                ),
            ),
            $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]
        );
    }
    
    /**
     * Get the Post-Upgrade new building information. <br/>
     * Includes animations, action area and background ID for the building and the new city background ID.<br/>
     * Includes building animations only - which may be null.
     * 
     * @param array $data Data containing <ul>
     * <li><b>cityId</b> (int) City ID</li>
     * <li><b>buildingConfigId</b> (int) Building Configuration ID</li>
     * </ul>
     * @return array
     * @throws Exception
     */
    public static function ajaxUpgradeInfo($data) {
        // Get the building information
        /* @var $buildingConfig Stephino_Rpg_Config_Building */
        list($buildingData, $buildingConfig, $cityData) = self::getBuildingInfo(
            isset($data[self::REQUEST_CITY_ID]) ? intval($data[self::REQUEST_CITY_ID]) : null, 
            isset($data[self::REQUEST_BUILDING_CONFIG_ID]) ? intval($data[self::REQUEST_BUILDING_CONFIG_ID]) : null
        );
        
        // Prepare building values
        $buildingId    = null;
        $buildingLevel = 0;
        
        // Valid building data
        if (is_array($buildingData)) {
            $buildingId    = (int) $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_ID];
            $buildingLevel = (int) $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL];
        }
        
        // Wrap the result
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                Stephino_Rpg_Renderer_Ajax_Cells::CELL_DATA_ANIMATIONS => Stephino_Rpg_Renderer_Ajax_Action_Building::getClosestAnimation(
                    $buildingConfig->getId(),
                    $buildingLevel
                ),
                Stephino_Rpg_Renderer_Ajax_Cells::CELL_DATA_DATA       => array(
                    Stephino_Rpg_Db_Table_Buildings::COL_ID                 => $buildingId,
                    Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL     => $buildingLevel,
                    Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID => $buildingConfig->getId(),
                    self::CELL_CONFIG_ACTION_AREA                           => self::getClosestActionArea(
                        $buildingConfig->getId(),
                        $buildingLevel
                    ),
                    self::CELL_CONFIG_BUILDING_BKG                          => Stephino_Rpg_Utils_Media::getClosestBackgroundId(
                        Stephino_Rpg_Config_Buildings::KEY,
                        $buildingConfig->getId(),
                        $buildingLevel
                    )
                )
            ),
            $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]
        );
    }
    
    /**
     * Cancel a building upgrade
     * 
     * @param array $data Data containing <ul>
     * <li><b>cityId</b> (int) City ID</li>
     * <li><b>buildingConfigId</b> (int) Building Configuration ID</li>
     * </ul>
     * @return array
     * @throws Exception
     */
    public static function ajaxUpgradeCancel($data) {
        // Get the building information
        /* @var $buildingConfig Stephino_Rpg_Config_Building */
        list($buildingData, $buildingConfig, $cityData, $queueData, $costData) = self::getBuildingInfo(
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
        
        // Prepare the result
        $result = false;
        
        // Valid queue found
        if (is_array($queueData) && isset($queueData[self::DATA_QUEUE_ID])) {
            // Prepare the refund percentage
            $refundPercent = $buildingConfig->getRefundPercent();

            // Refunding part or all of the building costs
            if ($refundPercent > 0) {
                // Prepare the refunds
                $updatesUser = array();
                $updatesCity = array();
                
                // Go through the cost info
                foreach ($costData as $costKey => $costInfo) {
                    list($costName, $costValue) = $costInfo;
                    switch($costKey) {
                        case Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD:
                        case Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH:
                        case Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM:
                            // Expend the gold
                            $updatesUser[$cityData[Stephino_Rpg_Db_Table_Users::COL_ID]] = array(
                                $costKey => floatval($cityData[$costKey]) + ($costValue * $refundPercent / 100.0)
                            );

                            // Update the time-lapse references (for the wrap method to work)
                            Stephino_Rpg_TimeLapse::get()
                                ->worker(Stephino_Rpg_TimeLapse_Resources::KEY)
                                ->updateRef(
                                    Stephino_Rpg_Db_Table_Users::COL_ID, 
                                    $cityData[Stephino_Rpg_Db_Table_Users::COL_ID], 
                                    $costKey, 
                                    $updatesUser[$cityData[Stephino_Rpg_Db_Table_Users::COL_ID]][$costKey]
                                );
                            break;

                        case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA:
                        case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_BETA:
                        case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_GAMMA:
                        case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1:
                        case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2:
                            $cityDataId = $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID];
                            
                            // Initialize the array
                            if (!isset($updatesCity[$cityDataId])) {
                                $updatesCity[$cityDataId] = array();
                            }

                            // Expend the resource
                            $updatesCity[$cityDataId][$costKey] = floatval($cityData[$costKey]) + ($costValue * $refundPercent / 100.0);

                            // Update the time-lapse references (for the wrap method to work)
                            Stephino_Rpg_TimeLapse::get()
                                ->worker(Stephino_Rpg_TimeLapse_Resources::KEY)
                                ->updateRef(
                                    Stephino_Rpg_Db_Table_Cities::COL_ID, 
                                    $cityDataId, 
                                    $costKey, 
                                    $updatesCity[$cityDataId][$costKey]
                                );
                            break;
                    }
                }

                // Update the user resources
                if (count($updatesUser)) {
                    $multiUpdateQuery = Stephino_Rpg_Utils_Db::getMultiUpdate(
                        $updatesUser, 
                        Stephino_Rpg_Db::get()->tableUsers()->getTableName(), 
                        Stephino_Rpg_Db_Table_Users::COL_ID
                    );
                    if (null !== $multiUpdateQuery) {
                        Stephino_Rpg_Db::get()->getWpDb()->query($multiUpdateQuery);
                    }
                }

                // Update the city resources
                if (count($updatesCity)) {
                    $multiUpdateQuery = Stephino_Rpg_Utils_Db::getMultiUpdate(
                        $updatesCity, 
                        Stephino_Rpg_Db::get()->tableCities()->getTableName(), 
                        Stephino_Rpg_Db_Table_Cities::COL_ID
                    );
                    if (null !== $multiUpdateQuery) {
                        Stephino_Rpg_Db::get()->getWpDb()->query($multiUpdateQuery);
                    }
                }
            }
            
            // Get the Delete query result
            $result = Stephino_Rpg_Db::get()->tableQueues()->unqueue($queueData[self::DATA_QUEUE_ID]);
        }
        
        return Stephino_Rpg_Renderer_Ajax::wrap(
            $result,
            $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CITY_ID]
        );
    }
    
    /**
     * Get the closest available action area for this building
     * 
     * @param int $configId Building configuration ID
     * @param int $level    Building Level
     * @return string|null
     */
    public static function getClosestActionArea($configId, $level) {
        // Prepare the action area
        $buildingsDataActionArea = null;
        
        // Sanitize the level
        $buildingLevel = intval($level);
        
        // Found the configuration
        if ($buildingLevel > 0 && null !== $buildingConfig = Stephino_Rpg_Config::get()->buildings()->getById($configId)) {
            // Get the configuration
            $buildingsDataActionAreaArray = json_decode($buildingConfig->getActionArea(), true);
            
            // Valid info
            if (is_array($buildingsDataActionAreaArray)) {
                // Prepare the closest level
                $buildingLevelClosest = 0;
                
                // Go through the data
                for ($i = 1; $i <= $buildingLevel; $i++) {
                    if (isset($buildingsDataActionAreaArray[$i])) {
                        $buildingLevelClosest = $i;
                    }
                }

                // Found the closest action area
                if (isset($buildingsDataActionAreaArray[$buildingLevelClosest])) {
                    $buildingsDataActionArea = $buildingsDataActionAreaArray[$buildingLevelClosest];
                }
            }
        }
        return $buildingsDataActionArea;
    }
    
    /**
     * Get the closest available animation info for this building
     * 
     * @param int $configId Building configuration ID
     * @param int $level    Building Level
     * @return array|null
     */
    public static function getClosestAnimation($configId, $level) {
        // Prepare the closest animation
        $closestAnimation = null;
        
        // Sanitize the level
        $buildingLevel = intval($level);
        
        // Get the building animations array
        if ($buildingLevel > 0 && null !== $buildingConfig = Stephino_Rpg_Config::get()->buildings()->getById($configId)) {
            if (is_array($animationsArray = json_decode($buildingConfig->getCityAnimations(), true))) {
                for ($i = 1; $i <= $buildingLevel; $i++) {
                    if (isset($animationsArray[$i])) {
                        // Store as animation level => animation keys
                        $closestAnimation = array($i => array_keys($animationsArray[$i]));
                    }
                }
            }
        }
        return $closestAnimation;
    }
}

/*EOF*/