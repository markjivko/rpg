<?php

/**
 * Stephino_Rpg_Renderer_Ajax_Action
 * 
 * @title      Actions
 * @desc       Common action methods
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Renderer_Ajax_Action {
    
    // Request keys
    const REQUEST_CITY_ID     = 'cityId';
    const REQUEST_COMMON_ARGS = 'commonArgs';
    
    // Data keys
    const DATA_QUEUE_ID         = 'queueId';
    const DATA_QUEUE_QUANTITY   = 'queueQuantity';
    const DATA_QUEUE_TIME_TOTAL = 'timeTotal';
    const DATA_QUEUE_TIME_LEFT  = 'timeLeft';
    
    /**
     * Get all entities (matching the required capability) in all of our cities, (excluding the specified city)
     * 
     * @param int      $excludedCityId          (optional) Excluded City ID; default <b>null</b>
     * @param string[] $entityCapabilities      (optional) Entity capabilities; one of <ul>
     *     <li>Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_TRANSPORTER</li>
     *     <li>Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_SPY</li>
     *     <li>Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_COLONIZER</li>
     * </ul> default <b>null</b>
     * @param boolean  $entityCapabilityInclude (optional) Whether to include or exclude special entities; default <b>false</b>
     * @return Associative array
     * [<ul>
     *     <li>(int) <b>... City ID</b> => 
     *         [<ul>
     *             <li>(array) <b>City DB Row</b></li>
     *             <li>(array) [ (int) <b>...Entity ID</b> =>
     *                 [<ul>
     *                     <li>(array) <b>Entity DB Row</b></li>
     *                     <li>(Stephino_Rpg_Config_Unit|Stephino_Rpg_Config_Ship) <b>Entity Configuration</b></li>
     *                 </ul>]]
     *             </li>
     *             <li>(array|null) <b>Entity Building DB Row</b></li>
     *         </ul>]
     *     </li>
     * </ul>]<br/>
     * The result may be an empty array. All populated city rows contain at least one entity.
     */
    public static function getAllEntities($excludedCityId = null, $entityCapabilities = null, $entityCapabilityInclude = false) {
        // Prepare the result
        $result = array();
        
        // Get the first building of every city - also contains City table information
        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_Entities::KEY)->getData()) 
            && is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_Entities::KEY)->getData() as $entityDbRow) {
                // Skip this city
                if (null !== $excludedCityId && $excludedCityId == $entityDbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CITY_ID]) {
                    continue;
                }
                
                // Valid count
                if ($entityDbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT] > 0
                    && self::_checkEntityCapabilities($entityDbRow, $entityCapabilities, $entityCapabilityInclude)) {
                    // Entity configuration object
                    $entityConfig = Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT == $entityDbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE]
                        ? Stephino_Rpg_Config::get()->units()->getById($entityDbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID])
                        : Stephino_Rpg_Config::get()->ships()->getById($entityDbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]);
                    
                    // Append the result
                    if (null !== $entityConfig) {
                        // Initialize the result
                        if (!isset($result[$entityDbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CITY_ID]])) {
                            // Find the City DB row
                            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $dbRow) {
                                if ($dbRow[Stephino_Rpg_Db_Table_Cities::COL_ID] == $entityDbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CITY_ID]) {
                                    $result[$entityDbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CITY_ID]] = array(
                                        $dbRow,
                                        array()
                                    );
                                    break;
                                }
                            }
                        }
                    
                        // Get the entity building
                        $buildingRow = null;
                        if (null !== $entityConfig->getBuilding()) {
                            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $dbRow) {
                                if ($dbRow[Stephino_Rpg_Db_Table_Cities::COL_ID] == $entityDbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CITY_ID]
                                    && $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID] == $entityConfig->getBuilding()->getId()) {
                                    $buildingRow = $dbRow;
                                    break;
                                }
                            }
                        }
                        
                        // Store the entity data
                        $result[$entityDbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CITY_ID]][1][$entityDbRow[Stephino_Rpg_Db_Table_Entities::COL_ID]] = array(
                            $entityDbRow, 
                            $entityConfig,
                            $buildingRow
                        );
                    }
                }
            }
        }
        return $result;
    }
    
    /**
     * Whether or not an Entity DB row has/lacks one of the specified capabilities
     * 
     * @param array    $entityDbRow        Entity DB Row
     * @param string[] $entityCapabilities Entity capabilities; one of <ul>
     *     <li>Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_TRANSPORTER</li>
     *     <li>Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_SPY</li>
     *     <li>Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_COLONIZER</li>
     * </ul>
     * @param boolean  $include            Whether to include or exclude the caps; default <b>true</b>
     * @return type
     */
    protected static function _checkEntityCapabilities($entityDbRow, $entityCapabilities, $include = true) {
        $result = !$include;

        // Entity configuration object
        $entityConfig = Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT == $entityDbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE]
            ? Stephino_Rpg_Config::get()->units()->getById($entityDbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID])
            : Stephino_Rpg_Config::get()->ships()->getById($entityDbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]);

        // Capability check
        if (is_array($entityCapabilities)) {
            foreach ($entityCapabilities as $entityCapability) {
                $entityFlag = null;
                
                switch ($entityCapability) {
                    // Retrieve transporters only
                    case Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_TRANSPORTER:
                        $entityFlag = $entityConfig instanceof Stephino_Rpg_Config_Ship && $entityConfig->getCivilian() && $entityConfig->getAbilityTransport();
                        break;

                    // Retrieve spies only
                    case Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_SPY:
                        $entityFlag = $entityConfig instanceof Stephino_Rpg_Config_Unit && $entityConfig->getCivilian() && $entityConfig->getAbilitySpy();
                        break;

                    // Retrieve colonizers only
                    case Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_COLONIZER:
                        $entityFlag = null !== $entityConfig && $entityConfig->getCivilian() && $entityConfig->getAbilityColonize();
                        break;
                }
                
                if (null !== $entityFlag) {
                    $result = $include ? ($result || $entityFlag) : ($result && !$entityFlag);
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Get the current user's total number of cities
     * 
     * @param boolean $includeConvoys (optional) Include convoys; default <b>true</b>
     * @return int Total number of cities
     */
    public static function getCitiesCount($includeConvoys = true) {
        // Go through the cities list
        $cities = array();
        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $dbRow) {
                $cities[] = (int) $dbRow[Stephino_Rpg_Db_Table_Cities::COL_ID];
            }
        }
        
        // Total number of cities
        $result = count(array_unique($cities));
        
        // Go through the convoys list
        if ($includeConvoys && is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Convoys::KEY)->getData())) {
            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Convoys::KEY)->getData() as $dbRow) {
                if (Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_COLONIZER === $dbRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TYPE]) {
                    $result++;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Get the list of upgradeable buildings from a city
     * 
     * @param int $cityId City ID
     * @return int[]|null Building Configuration IDs or Null if invalid City ID provided
     */
    public static function getBuildingUpgs($cityId) {
        $cityId = abs((int) $cityId);
        
        // Prepare the list
        $buildingsUpgradable = null;

        // Go through the available buildings list
        if ($cityId > 0) {
            $buildingsUpgradable = array();
            if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
                foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $dbRow) {
                    if ($cityId !== (int) $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CITY_ID]) {
                        continue;
                    }
                    
                    // Get the configuration ID
                    $buildingConfigId = (int) $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID];
        
                    // Invalid building configuration
                    if (null === $buildingConfig = Stephino_Rpg_Config::get()->buildings()->getById($buildingConfigId)) {
                        continue;
                    }
        
                    // Building already under construction
                    if (null !== self::getBuildingQueue($dbRow[Stephino_Rpg_Db_Table_Buildings::COL_ID])) {
                        continue;
                    }
                    
                    // Prepare the cost data
                    $costData = self::getCostData($buildingConfig, $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL], true);

                    // Validate the cost
                    $buildingUpgradeable = true;
                    foreach ($costData as $costKey => $costInfo) {
                        // Invalid cost information
                        if (!is_array($costInfo) || count($costInfo) < 2) {
                            continue;
                        }

                        // Get the cost name and value
                        list($costName, $costValue) = $costInfo;

                        // Sanitize the cost
                        $costValue = floatval($costValue);

                        // No cost or invalid key
                        if ($costValue <= 0 || !isset($dbRow[$costKey])) {
                            continue;
                        }

                        // Get the current balance
                        $costBalance = floatval($dbRow[$costKey]);

                        // We cannot afford this
                        if ($costValue > $costBalance) {
                            $buildingUpgradeable = false;
                            break;
                        }
                    }

                    // Upgrade available
                    if ($buildingUpgradeable) {
                        $buildingsUpgradable[] = $buildingConfigId;
                    }
                }
            }
        }

        return $buildingsUpgradable;
    }
    
    /**
     * Get all entities in a city
     * 
     * @param int      $cityId                  City ID
     * @param string[] $entityCapabilities      (optional) Entity capabilities; one of <ul>
     *     <li>Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_TRANSPORTER</li>
     *     <li>Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_SPY</li>
     *     <li>Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_COLONIZER</li>
     * </ul> default <b>null</b>
     * @param boolean  $entityCapabilityInclude (optional) Whether to include or exclude special entities; default <b>true</b>
     * @return Associative array
     * [<ul>
     *     <li>(array) <b>City DB Row</b></li>
     *     <li>(array) [
     *         (int) <b>...Entity ID => </b>[<ul>
     *             <li>(array) <b>Entity DB Row</b></li>
     *             <li>(Stephino_Rpg_Config_Unit|Stephino_Rpg_Config_Ship) <b>Entity Configuration</b></li>
     *         </ul>]]
     *     </li>
     * </ul>] or <b>null</b>
     */
    public static function getCityEntities($cityId, $entityCapabilities = null, $entityCapabilityInclude = true) {
        // Prepare the result
        $result = null;
        
        // Get the first building of every city - also contains City table information
        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_Entities::KEY)->getData()) 
            && is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_Entities::KEY)->getData() as $entityDbRow) {
                // Skip this city
                if ($cityId != $entityDbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CITY_ID]) {
                    continue;
                }
                
                // Initialize the result
                if (null === $result) {
                    // Find the City DB row
                    foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $dbRow) {
                        if ($dbRow[Stephino_Rpg_Db_Table_Cities::COL_ID] == $entityDbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CITY_ID]) {
                            $result = array(
                                $dbRow,
                                array()
                            );
                            break;
                        }
                    }
                }
                
                // Valid count
                if ($entityDbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT] > 0) {
                    // Entity configuration object
                    $entityConfig = Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT == $entityDbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE]
                        ? Stephino_Rpg_Config::get()->units()->getById($entityDbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID])
                        : Stephino_Rpg_Config::get()->ships()->getById($entityDbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]);

                    // Entity DB Row, Entity Config
                    if (null !== $entityConfig && self::_checkEntityCapabilities($entityDbRow, $entityCapabilities, $entityCapabilityInclude)) {
                        $result[1][$entityDbRow[Stephino_Rpg_Db_Table_Entities::COL_ID]] = array(
                            $entityDbRow, 
                            $entityConfig
                        );
                    }
                }
            }
        }
        return $result;
    }
    
    /**
     * Get the transferable resources descriptions
     * 
     * @param array   $payloadResources      (optional) Resources Convoy Payload; default <b>null</b>
     * @param boolean $includeCityMetrics    (optional) Include City metrics in the result; default <b>false</b>
     * @param boolean $includeMilitaryPoints (optional) Inlcude Military points in the result; default <b>false</b>
     * @return array Associative array of 
     * <ul>
     *     <li>(string) <b>Resource Key</b> <i>ex. Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA</i> => array(
     *         <ul>
     *             <li>(string) <b>Resource Name</b></li>
     *             <li>(string) <b>Resource AJAX key</b> <i>ex. Stephino_Rpg_Renderer_Ajax::RESULT_RES_ALPHA</i></li>
     *         </ul>)
     *     </li>
     * </ul>
     * <b>OR</b>
     * <ul>
     *     <li>(string) <b>Resource Key</b> <i>ex. Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA</i> => array(
     *         <ul>
     *             <li>(string) <b>Resource Name</b></li>
     *             <li>(int) <b>Resource Value</b></li>
     *             <li>(string) <b>Resource AJAX key</b> <i>ex. Stephino_Rpg_Renderer_Ajax::RESULT_RES_ALPHA</i></li>
     *         </ul>)
     *     </li>
     * </ul>
     * if <b>$payloadResources</b> provided
     */
    public static function getResourceData($payloadResources = null, $includeCityMetrics = false, $includeMilitaryPoints = false) {
        // Prepare the result
        $resourceDescriptions = array(
            Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD => array(
                Stephino_Rpg_Config::get()->core()->getResourceGoldName(), 
                Stephino_Rpg_Renderer_Ajax::RESULT_RES_GOLD,
            ),
            Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH => array(
                Stephino_Rpg_Config::get()->core()->getResourceResearchName(), 
                Stephino_Rpg_Renderer_Ajax::RESULT_RES_RESEARCH,
            ),
            Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM => array(
                Stephino_Rpg_Config::get()->core()->getResourceGemName(), 
                Stephino_Rpg_Renderer_Ajax::RESULT_RES_GEM,
            ),
            Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA => array(
                Stephino_Rpg_Config::get()->core()->getResourceAlphaName(), 
                Stephino_Rpg_Renderer_Ajax::RESULT_RES_ALPHA,
            ),
            Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_BETA  => array(
                Stephino_Rpg_Config::get()->core()->getResourceBetaName(), 
                Stephino_Rpg_Renderer_Ajax::RESULT_RES_BETA,
            ),
            Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_GAMMA => array(
                Stephino_Rpg_Config::get()->core()->getResourceGammaName(), 
                Stephino_Rpg_Renderer_Ajax::RESULT_RES_GAMMA,
            ),
            Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1 => array(
                Stephino_Rpg_Config::get()->core()->getResourceExtra1Name(), 
                Stephino_Rpg_Renderer_Ajax::RESULT_RES_EXTRA_1,
            ),
            Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2 => array(
                Stephino_Rpg_Config::get()->core()->getResourceExtra2Name(), 
                Stephino_Rpg_Renderer_Ajax::RESULT_RES_EXTRA_2,
            ),
        );
        
        // Include city metrics
        if ($includeCityMetrics) {
            $resourceDescriptions[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION] = array(
                Stephino_Rpg_Config::get()->core()->getMetricPopulationName(), 
                Stephino_Rpg_Renderer_Ajax::RESULT_MTR_POPULATION,
            );
            $resourceDescriptions[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_SATISFACTION] = array(
                Stephino_Rpg_Config::get()->core()->getMetricSatisfactionName(), 
                Stephino_Rpg_Renderer_Ajax::RESULT_MTR_SATISFACTION,
            );
            $resourceDescriptions[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_STORAGE] = array(
                Stephino_Rpg_Config::get()->core()->getMetricStorageName(), 
                Stephino_Rpg_Renderer_Ajax::RESULT_MTR_STORAGE,
            );
        }
        
        // Include military points
        if ($includeMilitaryPoints) {
            $resourceDescriptions[Stephino_Rpg_Config_Building::RES_MILITARY_ATTACK] = array(
                Stephino_Rpg_Config::get()->core()->getMilitaryAttackName(), 
                Stephino_Rpg_Renderer_Ajax::RESULT_MIL_ATTACK,
            );
            $resourceDescriptions[Stephino_Rpg_Config_Building::RES_MILITARY_DEFENSE] = array(
                Stephino_Rpg_Config::get()->core()->getMilitaryDefenseName(), 
                Stephino_Rpg_Renderer_Ajax::RESULT_MIL_DEFENSE,
            );
        }
        
        // Insert the value
        if (is_array($payloadResources)) {
            foreach ($resourceDescriptions as $resKey => &$resDescription) {
                list($resDescName, $resDescAjaxKey) = $resDescription;
                $resDescription = array(
                    $resDescName,
                    isset($payloadResources[$resKey]) ? $payloadResources[$resKey] : 0,
                    $resDescAjaxKey,
                );
            }
        }
        
        return $resourceDescriptions;
    }
    
    /**
     * Get island information
     * 
     * @param int $islandId Island ID
     * @return array Array of
     * <ul>
     *     <li><b>islandData</b> (array) Island database row</li>
     *     <li><b>islandConfig</b> (Stephino_Rpg_Config_Island) Island Configuration object</li>
     *     <li><b>islandStatueConfig</b> (Stephino_Rpg_Config_IslandStatue) Island Statue Configuration object</li>
     *     <li><b>colonizersList</b> [
     *         <ul>
     *             <li>(int) <b>...City ID</b> => [
     *                 <ul>
     *                     <li>(int) <b>Entity ID</b></li>
     *                     <li>(Stephino_Rpg_Config_Unit|Stephino_Rpg_Config_Ship) <b>Entity Configuration Object</b></li>
     *                 </ul>]
     *             </li>
     *         </ul>]
     *     </li>
     *     <li>
     *         <b>costData</b> - Associative array of 
     *         <ul>
     *             <li>(string) <b>Cost Key</b> <i>ex. Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA</i> => array(
     *                 <ul>
     *                     <li>(string) <b>Unit Name</b></li>
     *                     <li>(int) <b>Unit Value</b></li>
     *                     <li>(string) <b>Unit AJAX key</b> <i>ex. Stephino_Rpg_Renderer_Ajax::RESULT_RES_ALPHA</i></li>
     *                 </ul>)
     *             </li>
     *         </ul>
     *     </li>
     *     <li>productionData</li>
     *     <li>affordList</li>
     * </ul>
     * @throws Exception
     */
    public static function getIslandInfo($islandId) {
        // Invalid input
        if (null === $islandId || $islandId <= 0) {
            throw new Exception(
                sprintf(
                    __('Invalid ID (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigIslandName()
                )
            );
        }
        
        // Prepare the island data
        $islandData = null;
        
        // Use the cached time-lapse resources (avoids an extra, unnecessary DB query)
        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $dbRow) {
                // Found our island
                if ($islandId == $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID]) {
                    $islandData = $dbRow;
                    break;
                }
            }
        }
        
        // We have not gone to this island yet, let's explore
        if (null === $islandData) {
            $islandData = Stephino_Rpg_Db::get()->tableIslands()->getById($islandId);
        }
        
        // Island not found
        if (null === $islandData) {
            throw new Exception(
                sprintf(
                    __('Not found (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigIslandName()
                )
            );
        }
        
        // Get the island configuration
        if (null === $islandConfig = Stephino_Rpg_Config::get()->islands()->getById(
            $islandData[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_CONFIG_ID]
        )) {
            throw new Exception(
                sprintf(
                    __('Invalid configuration (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigIslandName()
                )
            );
        }
        
        // Get the island statue configuration
        if (null === $islandStatueConfig = Stephino_Rpg_Config::get()->islandStatues()->getById(
            $islandData[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_STATUE_CONFIG_ID]
        )) {
            throw new Exception(
                sprintf(
                    __('Invalid configuration (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigIslandStatueName()
                )
            );
        }
        
        // Prepare the list of colonizer IDs, grouped by city ID
        $colonizersList = self::getAllEntities(
            null, 
            array(
                Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_COLONIZER
            ),
            true
        );
        
        // Prepare the cost data
        $costData = self::getCostData(
            $islandStatueConfig,
            $islandData[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_STATUE_LEVEL]
        );
        
        // Get the production data
        $productionData = self::getProductionData(
            $islandStatueConfig,
            $islandData[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_STATUE_LEVEL],
            $islandData[Stephino_Rpg_Db_Table_Islands::COL_ID]
        );
        
        // Prepare the resources
        $resources = Stephino_Rpg_Renderer_Ajax::getResources();
        
        // Go through the costs
        $affordList = array();
        if (count($costData)) {
            foreach ($costData as list(, $unitValue, $unitAjaxKey)) {
                if ($unitValue > 0) {
                    $affordList[$unitAjaxKey] = intval($resources[$unitAjaxKey][0] / $unitValue);
                }
            }
        }
        
        return array(
            $islandData,
            $islandConfig,
            $islandStatueConfig,
            $colonizersList,
            $costData,
            $productionData,
            $affordList
        );
    }
    
    /**
     * Get the price discount (premium or research-level feature)
     * 
     * @param Stephino_Rpg_Config_Item_Single $configObject Building, Unit or Ship configuration object
     * @return array|null [<ul>
     *     <li>(int) <b>Discount percent</b></li>
     *     <li>(string) <ul>
     *            <li>Stephino_Rpg_Config_ResearchFields::KEY</li>
     *            <li>Stephino_Rpg_Config_PremiumModifiers::KEY</li>
     *         </ul>
     *     </li>
     *     <li>(int) <b>Discount Configuration Object ID</b></li>
     * </ul>] or <b>null</b> if no discount available
     */
    public static function getDiscount($configObject) {
        // Using the Discount Trait
        if (!$configObject instanceof Stephino_Rpg_Config_Building
            && !$configObject instanceof Stephino_Rpg_Config_Unit
            && !$configObject instanceof Stephino_Rpg_Config_Ship) {
            return null;
        }
        
        // Prepare the discount options
        $discountOptions = array();

        // Prepare the discount collections
        $discountCollections = array(
            Stephino_Rpg_Config_ResearchFields::KEY   => Stephino_Rpg_Config::get()->researchFields()->getAll(),
            Stephino_Rpg_Config_PremiumModifiers::KEY => Stephino_Rpg_Config::get()->premiumModifiers()->getAll(),
        );
        
        // Prepare research fields that offer discounts
        foreach ($discountCollections as $discountAjaxKey => $discountCollection) {
            /* @var $discountConfig Stephino_Rpg_Config_Trait_Discount */
            foreach ($discountCollection as $discountConfig) {
                switch (true) {
                    case $configObject instanceof Stephino_Rpg_Config_Building:
                        if ($discountConfig->getEnablesDiscountBuildings() && is_array($discountConfig->getDiscountBuildings())) {
                            foreach ($discountConfig->getDiscountBuildings() as $discountBuilding) {
                                if ($discountBuilding->getId() == $configObject->getId()) {
                                    $discountOptions[] = array(
                                        $discountConfig->getDiscountBuildingsPercent(),
                                        $discountAjaxKey,
                                        $discountConfig->getId()
                                    );
                                    break;
                                }
                            }
                        }
                        break;
                        
                    case $configObject instanceof Stephino_Rpg_Config_Unit:
                        if ($discountConfig->getEnablesDiscountUnits() && is_array($discountConfig->getDiscountUnits())) {
                            foreach ($discountConfig->getDiscountUnits() as $discountUnit) {
                                if ($discountUnit->getId() == $configObject->getId()) {
                                    $discountOptions[] = array(
                                        $discountConfig->getDiscountUnitsPercent(),
                                        $discountAjaxKey,
                                        $discountConfig->getId()
                                    );
                                    break;
                                }
                            }
                        }
                        break;
                        
                    case $configObject instanceof Stephino_Rpg_Config_Ship:
                        if ($discountConfig->getEnablesDiscountShips() && is_array($discountConfig->getDiscountShips())) {
                            foreach ($discountConfig->getDiscountShips() as $discountShip) {
                                if ($discountShip->getId() == $configObject->getId()) {
                                    $discountOptions[] = array(
                                        $discountConfig->getDiscountShipsPercent(),
                                        $discountAjaxKey,
                                        $discountConfig->getId()
                                    );
                                    break;
                                }
                            }
                        }
                        break;
                }
            }
        }
        
        // Prepare the result
        $result = null;
        
        // Get the maximum discount option available
        if (count($discountOptions)) {
            foreach ($discountOptions as $discountOption) {
                list($discountPercent, $discountAjaxKey, $discountConfigId) = $discountOption;
                switch ($discountAjaxKey) {
                    case Stephino_Rpg_Config_ResearchFields::KEY:
                        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_ResearchFields::KEY)->getData())) {
                            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_ResearchFields::KEY)->getData() as $dbRow) {
                                if ($dbRow[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_CONFIG_ID] == $discountConfigId
                                    && $dbRow[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_LEVEL] > 0) {
                                    if (null === $result || $result[0] < $discountPercent) {
                                        $result = $discountOption;
                                    }
                                    break;
                                }
                            }
                        }
                        break;
                        
                    case Stephino_Rpg_Config_PremiumModifiers::KEY:
                        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Queues::KEY)->getData())) {
                            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Queues::KEY)->getData() as $dbRow) {
                                if (Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_PREMIUM == $dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_TYPE]
                                    && $dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID] == $discountConfigId) {
                                    if (null === $result || $result[0] < $discountPercent) {
                                        $result = $discountOption;
                                    }
                                    break;
                                }
                            }
                        }
                        break;
                }
            }
        }
        return $result;
    }
    
    /**
     * Get the time contraction (premium feature)
     * 
     * @param object $configObject Configuration object, one of <ul>
     *     <li>Stephino_Rpg_Config_Building</li>
     *     <li>Stephino_Rpg_Config_Unit</li>
     *     <li>Stephino_Rpg_Config_Ship</li>
     *     <li>Stephino_Rpg_Config_ResearchField</li>
     * </ul>
     * @return array|null [<ul>
     *     <li>(int) <b>Time contraction</b> 1 or lower means contraction disabled</li>
     *     <li>(int) <b>Premium Modifier Configuration ID</b> or <b>0</b> if none applied</li>
     * </ul>] or <b>null</b> if invalid configuration object
     */
    public static function getTimeContraction($configObject) {
        // Prepare the queue type
        $queueType = null;
        switch (true) {
            case $configObject instanceof Stephino_Rpg_Config_Building:
                $queueType = Stephino_Rpg_Db_Model_Buildings::NAME;
                break;
            
            case $configObject instanceof Stephino_Rpg_Config_Unit:
            case $configObject instanceof Stephino_Rpg_Config_Ship:
                $queueType = Stephino_Rpg_Db_Model_Entities::NAME;
                break;
            
            case $configObject instanceof Stephino_Rpg_Config_ResearchField:
                $queueType = Stephino_Rpg_Db_Model_ResearchFields::NAME;
                break;
        }
        
        // Prepare the result
        $result = null;
        
        // Valid queue type
        if (null !== $queueType) {
            $result = Stephino_Rpg_Db::get()->modelPremiumModifiers()->getTimeContraction($queueType);
        }
        
        return $result;
    }
    
    /**
     * Get item requirements, checking them against the time-lapse data for the provided city
     * 
     * @param Stephino_Rpg_Config_Trait_Requirement $configObject    Configuration object that supports requirements
     * @param int                                   $cityId          (optional) City ID to validate the requirements against; default <b>null</b> - used for user-level resources
     * @param boolean                               $exceptionOnFail (optional) Whether to throw an exception on fail; default <b>false</b>
     * @return array|null Numeric array with 2 values:
     * <ul>
     *     <li>
     *         (array) [
     *         <ul>
     *             <li>
     *                 Stephino_Rpg_Config_Building::KEY => [
     *                 <ul>
     *                     <li>(Stephino_Rpg_Config_Building|null) Required Building configuration object</li>
     *                     <li>(int|null) Required Building Level</li>
     *                     <li>(boolean) Requirement met</li>
     *                 </ul>]
     *             </li>
     *             <li>
     *                 Stephino_Rpg_Config_ResearchField::KEY => [
     *                 <ul>
     *                     <li>(Stephino_Rpg_Config_ResearchField|null) Research Field configuration object</li>
     *                     <li>(int|null) Required Research Field Level</li>
     *                     <li>(boolean) Requirement met</li>
     *                 </ul>]
     *             </li>
     *         </ul>]
     *     </li>
     *     <li>
     *     (boolean) Whether the requirements were met
     *     </li>
     * </ul> or <b>null</b> if <B>$configObject</b> is null or does not use Stephino_Rpg_Config_Trait_Requirement
     * @throws Exception
     */
    public static function getRequirements($configObject, $cityId = null, $exceptionOnFail = false) {
        // Get the default city ID
        if (null === $cityId) {
            // Prepare the city data; any city will do, as we'll be spending user-level resources only
            if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
                $cityData = current(
                    Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData()
                );
                $cityId = $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID];
            }
        }
        
        do {
            // Object provided
            if (null !== $configObject) {
                // Using the Requirement Trait
                if (in_array(Stephino_Rpg_Config_Trait_Requirement::class, class_uses($configObject))) {
                    break;
                }

                // Object provided but with no traits defined
                if ($exceptionOnFail) {
                    throw new Exception(
                        sprintf(
                            __('No traits defined for "%s"', 'stephino-rpg'),
                            get_class($configObject)
                        )
                    );
                }
            }
            
            // No requirements
            return null;
        } while(false);
        
        // Prepare the result
        $requirementsData = array(
            Stephino_Rpg_Config_Building::KEY => array(
                $configObject->getRequiredBuilding(),
                $configObject->getRequiredBuildingLevel()
            ),
            Stephino_Rpg_Config_ResearchField::KEY => array(
                $configObject->getRequiredResearchField(),
                $configObject->getRequiredResearchFieldLevel(),
            )
        );
        
        // Validate the status
        foreach ($requirementsData as $resKey => &$resData) {
            $requirementMet = false;
            switch ($resKey) {
                case Stephino_Rpg_Config_Building::KEY:
                    /* @var $buildingConfig Stephino_Rpg_Config_Building */
                    list($buildingConfig, $buildingLevel) = $resData;
                    if (null === $buildingConfig) {
                        $requirementMet = true;
                    } else {
                        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
                            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $dbRow) {
                                if ($cityId == $dbRow[Stephino_Rpg_Db_Table_Cities::COL_ID]) {
                                    if ($dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID] == $buildingConfig->getId()) {
                                        $requirementMet = $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL] >= $buildingLevel;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    break;
                    
                case Stephino_Rpg_Config_ResearchField::KEY:
                    /* @var $researchFieldConfig Stephino_Rpg_Config_ResearchField */
                    list($researchFieldConfig, $researchFieldLevel) = $resData;
                    if (null === $researchFieldConfig) {
                        $requirementMet = true;
                    } else {
                        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_ResearchFields::KEY)->getData())) {
                            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_ResearchFields::KEY)->getData() as $dbRow) {
                                if ($dbRow[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_CONFIG_ID] == $researchFieldConfig->getId()) {
                                    $requirementMet = $dbRow[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_LEVEL] >= $researchFieldLevel;
                                }
                            }
                        }
                    }
                    break;
            }
            
            // Store the flag on index 2
            $resData[] = $requirementMet;
        }
        
        // Final validation
        $requirementsMet = $requirementsData[Stephino_Rpg_Config_Building::KEY][2] && $requirementsData[Stephino_Rpg_Config_ResearchField::KEY][2];
        
        // Fail on error
        if ($exceptionOnFail && !$requirementMet) {
            throw new Exception(__('Requirements not met', 'stephino-rpg'));
        }
        
        return array($requirementsData, $requirementsMet);
    }
    
    /**
     * Get the most effective military entity available with the lowest maintenance
     * 
     * @param int $cityId City ID
     * @return array|null Numeric array with 2 values:
     * <ul>
     *     <li>(Stephino_Rpg_Config_Unit|Stephino_Rpg_Config_Ship) Entity Configuration Object</li>
     *     <li>(int) Positive integer: entities afforded by both upfront cost and maintenance</li>
     *     <li>(array) Cost data</li>
     * </ul> or NULL if none found
     */
    public static function getEntityMVP($cityId) {
        $result = null;
        
        // Prepare the entity and production data
        $mvpEntity     = null;
        $mvpProduction = null;
        $mvpBuilding   = null;

        // Get the available military entities configs
        $entityConfigs = array_filter(
            Stephino_Rpg_Utils_Config::getEntitiesByCapability(Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_ATTACK),
            function($entityConfig) use($cityId) {
                list(, $requirementsMet) = self::getRequirements($entityConfig, $cityId);
                return $requirementsMet;
            }
        );
        
        do {
            // No entities available
            if (!count($entityConfigs)) {
                break;
            }
            
            // Store least costly
            $leastCostlyValue = null;
            
            // Go through the entities
            foreach ($entityConfigs as /*@var $entityConfig Stephino_Rpg_Config_Unit|Stephino_Rpg_Config_Ship*/$entityConfig) {
                // Use the cached time-lapse resources (avoids an extra, unnecessary DB query)
                $parentBuildingData = null;
                if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
                    foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $dbRow) {
                        // Found our city
                        if ($cityId == $dbRow[Stephino_Rpg_Db_Table_Cities::COL_ID]) {
                            // Found the parent building
                            if (null !== $entityConfig->getBuilding() && $entityConfig->getBuilding()->getId() == $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID]) {
                                $parentBuildingData = $dbRow;
                                break;
                            }
                        }
                    }
                }

                // Get the production data
                $productionData = self::getProductionData(
                    $entityConfig,
                    null === $parentBuildingData ? 0 : $parentBuildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL],
                    $parentBuildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_ISLAND_ID]
                );

                // First result is enough
                if (1 === count($entityConfigs)) {
                    $mvpEntity     = $entityConfig;
                    $mvpProduction = $productionData;
                    $mvpBuilding   = $parentBuildingData;
                    break;
                } 
                
                // Prepare the total value
                $productionValue = isset($productionData[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD])
                    ? floatval($productionData[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD][1])
                    : 0;

                // Gems > Gold
                if (isset($productionData[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM])) {
                    $gemToGoldRatio = Stephino_Rpg_Config::get()->core()->getGemToGoldRatio() > 0
                        ? Stephino_Rpg_Config::get()->core()->getGemToGoldRatio()
                        : 1;
                    $productionValue += floatval($productionData[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM][1]) * $gemToGoldRatio;
                }
                
                // Research Points > Gold
                if (isset($productionData[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH])) {
                    $researchToGoldRatio = (Stephino_Rpg_Config::get()->core()->getGemToGoldRatio() > 0
                        ? Stephino_Rpg_Config::get()->core()->getGemToGoldRatio()
                        : 1
                    ) / (Stephino_Rpg_Config::get()->core()->getGemToResearchRatio() > 0
                        ? Stephino_Rpg_Config::get()->core()->getGemToResearchRatio()
                        : 1
                    );
                    $productionValue += floatval($productionData[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH][1]) * $researchToGoldRatio;
                }
                
                // City Resources > Gold
                foreach (array(
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA,
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_BETA,
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_GAMMA,
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1,
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2,
                ) as $resourceName) {
                    if (isset($productionData[$resourceName])) {
                        $resourceToGoldRatio = 1;
                        switch ($resourceName) {
                            case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA:
                                $resourceToGoldRatio = Stephino_Rpg_Config::get()->core()->getMarketResourceAlpha();
                                break;
                        }
                        if ($resourceToGoldRatio <= 0) {
                            $resourceToGoldRatio = 1;
                        }
                        $productionValue += floatval($productionData[$resourceName][1]) * $resourceToGoldRatio;
                    }
                }
                
                // Get the military caps
                $entityMilitaryCaps = (
                    $entityConfig->getAgility() * $entityConfig->getArmour()
                    + $entityConfig->getAmmo() * $entityConfig->getDamage()
                );
                if ($entityMilitaryCaps < 1) {
                    $entityMilitaryCaps = 1;
                }
                
                // Adjust the production value with the military capabilities
                $productionValue /= $entityMilitaryCaps;
                
                // Most affordable upkeep
                if (null === $leastCostlyValue || $productionValue > $leastCostlyValue) {
                    $leastCostlyValue = $productionValue;
                    $mvpEntity        = $entityConfig;
                    $mvpProduction    = $productionData;
                    $mvpBuilding      = $parentBuildingData;
                }
            }
        } while(false);
        
        // Number of entities afforded (maintenance)
        if (null !== $mvpProduction) {
            $affordList = array();
            foreach ($mvpProduction as $resName => $prodData) {
                $affordCount = 10;
                
                if ($prodData[1] < 0) {
                    $metricInfo = Stephino_Rpg_Renderer_Ajax_Action::getModifierEffectInfo($resName, $cityId);

                    // Prepare the total metric
                    $cityTotal = 0;
                    if (count($metricInfo)) {
                        foreach($metricInfo as $bdSatData) {
                            foreach($bdSatData as $bdSatValue) {
                                $cityTotal += $bdSatValue[0];
                            }
                        }
                    }
                    
                    // Total production
                    if ($cityTotal > 0) {
                        $affordCount = intval(floor($cityTotal / 2 / abs($prodData[1])));
                    }
                }
                
                $affordList[] = $affordCount;
                if ($affordCount <= 0) {
                    break;
                }
            }
            
            // Store the entities we afford considering maintenance costs
            $affordMaintenance = count($affordList) ? min($affordList) : 0;
            if ($affordMaintenance > 0) {
                // Prepare the resources
                $resources = Stephino_Rpg_Renderer_Ajax::getResources($cityId);

                // Prepare the cost data (per entity)
                $costData = Stephino_Rpg_Renderer_Ajax_Action::getCostData(
                    $mvpEntity,
                    null === $mvpBuilding ? 0 : $mvpBuilding[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL] - 1,
                    true
                );

                // Go through the costs
                $affordList = array();
                if (count($costData)) {
                    foreach ($costData as list(, $unitValue, $unitAjaxKey)) {
                        if ($unitValue > 0) {
                            $affordList[$unitAjaxKey] = intval($resources[$unitAjaxKey][0] / $unitValue);
                        }
                    }
                } else {
                    // 10 at a time for priceless units
                    $affordList = array(10);
                }

                // Store the entities we afford considering upfront cost
                $affordCost = count($affordList) ? min($affordList) : 0;

                // We afford at least one
                if ($affordCost > 0) {
                    $mvpEntityCount = min($affordCost, $affordMaintenance);
                    
                    $mvpCostData = Stephino_Rpg_Renderer_Ajax_Action::getCostData(
                        $mvpEntity,
                        null === $mvpBuilding ? 0 : $mvpBuilding[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL] - 1,
                        true,
                        $mvpEntityCount
                    );
                    
                    $result = array($mvpEntity, $mvpEntityCount, $mvpCostData);
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Get the next item to create/upgrade
     * 
     * @param int $cityId ID City ID
     * @return array|null Array of [<ul>
     *     <li><b>Stephino_Rpg_Config_Building|Stephino_Rpg_Config_ResearchField</b> Item configuration object</li>
     *     <li><b>int</b> Item current level</li>
     *     <li><b>int</b> Item target level</li>
     *     <li><b>boolean</b> Item queued</li>
     * </ul>] or <b>null</b> if no step available
     */
    public static function getUnlockNext($cityId) {
        // Prepare the buildings
        $buildingLevels = array();
        $buildingIds = array();
        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $dbRow) {
                if ($cityId == $dbRow[Stephino_Rpg_Db_Table_Cities::COL_ID]) {
                    $buildingLevels[$dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID]] = $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL];
                    $buildingIds[$dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID]] = $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_ID];
                }
            }
        }
                        
        // Prepare the research fields
        $researchFieldLevels = array();
        $researchFieldIds = array();
        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_ResearchFields::KEY)->getData())) {
            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_ResearchFields::KEY)->getData() as $dbRow) {
                $researchFieldLevels[$dbRow[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_CONFIG_ID]] = $dbRow[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_LEVEL];
                $researchFieldIds[$dbRow[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_CONFIG_ID]] = $dbRow[Stephino_Rpg_Db_Table_ResearchFields::COL_ID];
            }
        }
        
        // Get the next object
        $result = Stephino_Rpg_Utils_Config::getUnlockNext($buildingLevels, $researchFieldLevels);
        
        // Valid result
        if (null !== $result) {
            // Prepare the flag
            $itemQueued = false;
            
            // Get the item database ID
            $itemDbId = null;
            if ($result[0] instanceof Stephino_Rpg_Config_Building) {
                if (isset($buildingIds[$result[0]->getId()])) {
                    $itemDbId = $buildingIds[$result[0]->getId()];
                }
            } elseif ($result[0] instanceof Stephino_Rpg_Config_ResearchField) {
                if (isset($researchFieldIds[$result[0]->getId()])) {
                    $itemDbId = $researchFieldIds[$result[0]->getId()];
                }
            }
            
            // Item not initialized, don't look for any queue
            if (null !== $itemDbId && is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Queues::KEY)->getData())) {
                foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Queues::KEY)->getData() as $dbRow) {
                    if ($result[0] instanceof Stephino_Rpg_Config_Building) {
                        if ($cityId == $dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_CITY_ID]
                            && Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_BUILDING == $dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_TYPE]
                            && $itemDbId == $dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID]) {
                            $itemQueued = true;
                            break;
                        }
                    } else {
                        if (Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_RESEARCH == $dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_TYPE]
                            && $itemDbId == $dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID]) {
                            $itemQueued = true;
                            break;
                        }
                    }
                }
            }
            
            // Item is in queue
            $result[] = $itemQueued;
        }
        
        return $result;
    }
    
    /**
     * Get all building levels, research field levels and unlock stages for a city
     * 
     * @param int $cityId City ID
     * @return array Array of [<ul>
     *     <li><b>array</b> Associative array of Building Config ID => Building Level</li>
     *     <li><b>array</b> Associative array of Research Field Config ID => Research Field Level</li>
     *     <li><b>array</b> Stages, [<b>(int)</b> Stage Key => [
     *          <ul>
     *             <li><b>(string|Stephino_Rpg_Config_Item_Single)</b> Configuration Item</li>
     *             <li>...</li>
     *         </ul>], ...]
     *     </li>
     * </ul>]
     * @throws Exception
     */
    public static function getUnlockStages($cityId) {
        // Prepare the buildings
        $buildingLevels = array();
        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $dbRow) {
                if ($cityId == $dbRow[Stephino_Rpg_Db_Table_Cities::COL_ID]) {
                    $buildingLevels[$dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID]] = $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL];
                }
            }
        }
                        
        // Prepare the research fields
        $researchFieldLevels = array();
        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_ResearchFields::KEY)->getData())) {
            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_ResearchFields::KEY)->getData() as $dbRow) {
                $researchFieldLevels[$dbRow[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_CONFIG_ID]] = $dbRow[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_LEVEL];
            }
        }
        
        // Get the stages
        return array($buildingLevels, $researchFieldLevels, Stephino_Rpg_Utils_Config::getUnlockStages(true));
    }
    
    /**
     * Get city information
     * 
     * @param int $cityId City ID
     * @return array City DB row
     * @throws Exception
     */
    public static function getCityInfo($cityId) {
        // Invalid input
        if (null === $cityId || $cityId <= 0) {
            throw new Exception(
                sprintf(
                    __('Invalid ID (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigCityName()
                )
            );
        }
        
        // Prepare the city data
        $cityInfo = null;
        
        // Use the cached time-lapse resources (avoids an extra, unnecessary DB query)
        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $dbRow) {
                // Found our city
                if ($cityId == $dbRow[Stephino_Rpg_Db_Table_Cities::COL_ID]) {
                    $cityInfo = $dbRow;
                    break;
                }
            }
        }
        
        // City not found
        if (null === $cityInfo) {
            throw new Exception(
                sprintf(
                    __('Not found (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigCityName()
                )
            );
        }
        return $cityInfo;
    }
    
    /**
     * Get the building production factor
     * 
     * @param Stephino_Rpg_Config_City     $cityConfig     City configuration object
     * @param Stephino_Rpg_Config_Building $buildingConfig Building configuration object
     * @param array                        $buildingRow    Building DataBase row containing the following keys:<ul>
     * <li>Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL</li>
     * <li>Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_WORKERS</li>
     * <li>Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION</li>
     * </ul>
     * @return float 4 decimal places precision float
     */
    public static function getBuildingProdFactor($cityConfig, $buildingConfig, $buildingRow) {
        $prodFactor = 1.0;
        if ($buildingConfig->getUseWorkers()) {
            // Get the maximum number of workers
            $buildingWorkersMax = Stephino_Rpg_Utils_Config::getPolyValue(
                $buildingConfig->getWorkersCapacityPolynomial(), 
                $buildingRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL], 
                $buildingConfig->getWorkersCapacity()
            );

            // Get the workers factor
            if ($buildingWorkersMax > 0 && $buildingRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_WORKERS] <= $buildingWorkersMax) {
                $prodFactor = round($buildingRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_WORKERS] / $buildingWorkersMax, 4);
            }
        } elseif ($buildingConfig->isMainBuilding() && null !== $cityConfig) {
            // Get the maximum city population at this level
            $cityPopulationMax = Stephino_Rpg_Utils_Config::getPolyValue(
                $cityConfig->getMaxPopulationPolynomial(),
                $buildingRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL], 
                $cityConfig->getMaxPopulation()
            );

            // Get the population factor
            if ($cityPopulationMax > 0 && $buildingRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION] <= $cityPopulationMax) {
                $prodFactor = round($buildingRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION] / $cityPopulationMax, 4);
            }
        }
        return $prodFactor;
    }
    
    /**
     * Get building information
     * 
     * @param int $cityId           City ID
     * @param int $buildingConfigId Building configuration ID
     * @return array Array of <ul>
     * <li>buildingData (array|null)</li>
     * <li>buildingConfig (Stephino_Rpg_Config_Building)</li>
     * <li>cityData (array)</li>
     * <li>queueData (array)</li>
     * <li>
     *     <b>costData</b> - Associative array of 
     *     <ul>
     *         <li>(string) <b>Cost Key</b> <i>ex. Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA</i> => array(
     *             <ul>
     *                 <li>(string) <b>Unit Name</b></li>
     *                 <li>(int) <b>Unit Value</b></li>
     *                 <li>(string) <b>Unit AJAX key</b> <i>ex. Stephino_Rpg_Renderer_Ajax::RESULT_RES_ALPHA</i></li>
     *             </ul>)
     *         </li>
     *     </ul>
     * </li>
     * <li>productionData (array)</li>
     * <li>affordList (array)</li>
     * </ul>
     * @throws Exception
     */
    public static function getBuildingInfo($cityId, $buildingConfigId) {
        // Invalid input
        if (null === $cityId || $cityId <= 0) {
            throw new Exception(
                sprintf(
                    __('Invalid ID (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigCityName()
                )
            );
        }
        if (null === $buildingConfigId || $buildingConfigId <= 0) {
            throw new Exception(
                sprintf(
                    __('Invalid ID (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigBuildingName()
                )
            );
        }
        
        // Prepare the building config
        if (null === $buildingConfig = Stephino_Rpg_Config::get()->buildings()->getById($buildingConfigId)) {
            throw new Exception(
                sprintf(
                    __('Invalid configuration (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigBuildingName()
                )
            );
        }
        
        // Prepare the city data
        $cityData = null;
        
        // Prepare the building data
        $buildingData = null;
        
        // Use the cached time-lapse resources (avoids an extra, unnecessary DB query)
        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $dbRow) {
                // Found our city
                if ($cityId == $dbRow[Stephino_Rpg_Db_Table_Cities::COL_ID]) {
                    // Store the city data
                    if (null === $cityData) {
                        $cityData = $dbRow;
                    }
                    
                    // Found our building
                    if ($buildingConfigId == $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID]) {
                        $buildingData = $dbRow;
                        break;
                    }
                }
            }
        }
        
        // City not found
        if (null === $cityData) {
            throw new Exception(
                sprintf(
                    __('Not found (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigCityName()
                )
            );
        }
        
        // Get the queue data for this building
        $queueData = null === $buildingData 
            ? null 
            : self::getBuildingQueue($buildingData[Stephino_Rpg_Db_Table_Buildings::COL_ID]);
        
        // Prepare the cost data
        $costData = self::getCostData(
            $buildingConfig,
            null === $buildingData 
                ? 0 
                : $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL],
            true
        );
        
        // Get the production data
        $productionData = null === $buildingData ? array() : self::getProductionData(
            $buildingConfig,
            $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL],
            $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID],
            $buildingConfig->isMainBuilding()
                ? $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION]
                : $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_WORKERS],
            $buildingConfig->isMainBuilding()
                ? Stephino_Rpg_Config::get()->cities()->getById($cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_CONFIG_ID])
                : null,
            // Don't include the city metrics for the main buildings - already detailed
            !$buildingConfig->isMainBuilding(),
            // Include military capabilities as virtual resources
            true
        );
        
        // Prepare the resources
        $resources = Stephino_Rpg_Renderer_Ajax::getResources(
            $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]
        );
        
        // Go through the costs
        $affordList = array();
        if (count($costData)) {
            foreach ($costData as list(, $unitValue, $unitAjaxKey)) {
                if ($unitValue > 0) {
                    $affordList[$unitAjaxKey] = intval($resources[$unitAjaxKey][0] / $unitValue);
                }
            }
        }
        
        // Get the data and configuration
        return array(
            $buildingData, 
            $buildingConfig, 
            $cityData, 
            $queueData, 
            $costData, 
            $productionData,
            $affordList
        );
    }
    
    /**
     * Get marketplace resource trading ratios
     * 
     * @param Stephino_Rpg_Config_Building $buildingConfig Building Configuration object
     * @param int                            $itemLevel      Building Level
     * @return array|null Marketplace resource to gold ratio or null if the building is not allowed to trade resources
     */
    public static function getMarketplaceData($buildingConfig, $itemLevel = 1) {
        $buildingIsMarketplace = $buildingConfig instanceof Stephino_Rpg_Config_Building
            && Stephino_Rpg_Config::get()->core()->getMarketEnabled() 
            && null !== Stephino_Rpg_Config::get()->core()->getMarketBuilding()
            && Stephino_Rpg_Config::get()->core()->getMarketBuilding()->getId() == $buildingConfig->getId();
        
        if (!$buildingIsMarketplace) {
            return null;
        }
        
        // Prepare the result
        $result = array();
        
        // Get the resources
        foreach (self::getResourceData() as $productionKey => $productionValue) {
            list($prodName, $prodAjaxKey) = $productionValue;
            
            // Prepare the value
            $prodValue = null;

            // Go through the known keys
            switch ($productionKey) {
                case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA:
                    $prodValue = Stephino_Rpg_Config::get()->core()->getMarketResourceAlpha();
                    break;

                case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_BETA:
                    $prodValue = Stephino_Rpg_Config::get()->core()->getMarketResourceBeta();
                    break;

                case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_GAMMA:
                    $prodValue = Stephino_Rpg_Config::get()->core()->getMarketResourceGamma();
                    break;

                case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1:
                    $prodValue = Stephino_Rpg_Config::get()->core()->getMarketResourceExtra1();
                    break;

                case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2:
                    $prodValue = Stephino_Rpg_Config::get()->core()->getMarketResourceExtra2();
                    break;
            }
            
            if (null !== $prodValue) {
                $result[$productionKey] = array(
                    $prodName,
                    intval(Stephino_Rpg_Utils_Config::getPolyValue(
                        Stephino_Rpg_Config::get()->core()->getMarketPolynomial(),
                        $itemLevel, 
                        $prodValue
                    )),
                    $prodAjaxKey
                );
            }
        }
        return $result;
    }
    
    /**
     * Get the production data
     * 
     * @param Stephino_Rpg_Config_Item_Single $itemConfig            Configuration item that implements a Modifier
     * @param int                             $itemLevel             (optional) Item Level; default <b>1</b>
     * @param int                             $islandId              (optional) Island DataBase ID, used to calculate abundance values; default <b>null</b>
     * @param int                             $multiplier            (optional) <ul>
     * <li><i>City Population</i> for <b>Main Building</b></li>
     * <li><i>Workers</i> for <b>Buildings</b></li>
     * <li><i>Entity Count</i> for <b>Units</b> and <b>Ships</b></li>
     * </ul> default <b>null</b>
     * @param Stephino_Rpg_Config_City        $cityConfig            (optional) City Configuration object, only for <b>Main Building</b>; default <b>null</b>
     * @param boolean                         $includeCityMetrics    (optional) Include city metrics; default <b>true</b>
     * @param boolean                         $includeMilitaryPoints (optional) Include military points, only for <b>Buildings</b>; default <b>false</b>
     * @return array
     */
    public static function getProductionData($itemConfig, $itemLevel = 1, $islandId = null, $multiplier = null, $cityConfig = null, $includeCityMetrics = true, $includeMilitaryPoints = false) {
        $result = array();
        
        // Prepare the building workers factor
        $prodFactor = null;
        if (null !== $multiplier) {
            // Building that requires workers
            if ($itemConfig instanceof Stephino_Rpg_Config_Building) {
                // Get the building production factor
                $prodFactor = self::getBuildingProdFactor(
                    $cityConfig, 
                    $itemConfig, 
                    array(
                        Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL      => $itemLevel,
                        Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_WORKERS    => $multiplier,
                        Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION => $multiplier,
                    )
                );
            }
            
            // Entities
            if ($itemConfig instanceof Stephino_Rpg_Config_Unit || $itemConfig instanceof Stephino_Rpg_Config_Ship) {
                $prodFactor = $multiplier;
            }
        }
        
        /* @var $itemModifier Stephino_Rpg_Config_Modifier */
        if ($itemConfig instanceof Stephino_Rpg_Config_Item_Single
            && in_array(Stephino_Rpg_Config_Trait_Modifier::class, class_uses($itemConfig))
            && null !== $itemModifier = $itemConfig->getModifier()) {
            
            // Get the resources
            foreach (self::getResourceData(null, $includeCityMetrics, $includeMilitaryPoints) as $productionKey => $productionValue) {
                list($prodName, $prodAjaxKey) = $productionValue;
                
                // Prepare the value
                $prodValue = null;
                
                // Prepare the production poly
                $prodPoly = $itemConfig->getModifierPolynomial();
                
                // Go through the known keys
                switch ($productionKey) {
                    case Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD:
                        $prodValue = $itemModifier->getEffectResourceGold();
                        break;
                    
                    case Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH:
                        $prodValue = $itemModifier->getEffectResourceResearch();
                        break;
                    
                    case Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM:
                        $prodValue = $itemModifier->getEffectResourceGem();
                        break;

                    case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA:
                        $prodValue = $itemModifier->getEffectResourceAlpha();
                        break;

                    case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_BETA:
                        $prodValue = $itemModifier->getEffectResourceBeta();
                        break;

                    case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_GAMMA:
                        $prodValue = $itemModifier->getEffectResourceGamma();
                        break;

                    case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1:
                        $prodValue = $itemModifier->getEffectResourceExtra1();
                        break;

                    case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2:
                        $prodValue = $itemModifier->getEffectResourceExtra2();
                        break;
                    
                    case Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION:
                        $prodValue = $itemModifier->getEffectMetricPopulation();
                        break;
                    
                    case Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_SATISFACTION:
                        $prodValue = $itemModifier->getEffectMetricSatisfaction();
                        break;
                    
                    case Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_STORAGE:
                        $prodValue = $itemModifier->getEffectMetricStorage();
                        break;
                    
                    case Stephino_Rpg_Config_Building::RES_MILITARY_ATTACK:
                        if ($itemConfig instanceof Stephino_Rpg_Config_Building) {
                            $prodValue = $itemConfig->getAttackPoints();
                            $prodPoly = $itemConfig->getAttackPointsPolynomial();
                        }
                        break;
                        
                    case Stephino_Rpg_Config_Building::RES_MILITARY_DEFENSE:
                        if ($itemConfig instanceof Stephino_Rpg_Config_Building) {
                            $prodValue = $itemConfig->getDefensePoints();
                            $prodPoly = $itemConfig->getDefensePointsPolynomial();
                        }
                        break;
                }
                
                if (null !== $prodValue) {
                    // Abundance factor, island-level
                    $abundanceFactor = self::getAbundanceInfo($islandId, $productionKey);
                            
                    // Population, Satisfaction and Storage not affected by building workers/city population for main building
                    if (!$itemConfig instanceof Stephino_Rpg_Config_Building || !in_array($productionKey, array(
                        Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_SATISFACTION,
                        Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION,
                        Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_STORAGE,
                    ))) {
                        // Production factor at play
                        if (null !== $prodFactor) {
                            $prodValue *= $prodFactor;
                        }
                    }
                    
                    if (null !== $abundanceFactor) {
                        $prodValue *= $abundanceFactor[0];
                    }
                    
                    // Set the result
                    $result[$productionKey] = array(
                        // Configuration name
                        $prodName,
                        
                        // Final value
                        Stephino_Rpg_Utils_Config::getPolyValue(
                            $prodPoly,
                            $itemLevel, 
                            $prodValue
                        ),
                        
                        // AJAX Key
                        $prodAjaxKey,
                        
                        // Abundance factor array of [abundance factor, config key, config id]; only for extra1, extra2 resources
                        $abundanceFactor
                    );
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Get the cost data for the next level based on the current level
     * 
     * @param Stephino_Rpg_Config_Trait_Cost $itemConfig    Configuration item that uses a Cost trait
     * @param int                            $itemLevel     (optional) Item Level; default <b>0</b>
     * @param boolean                        $applyDiscount (optional) Apply discount based on time-lapse data; default <b>false</b>
     * @param int                            $multiply      (optional) Multiply the cost by this number; default <b>1</b>
     * @return array
     */
    public static function getCostData($itemConfig, $itemLevel = 0, $applyDiscount = false, $multiply = 1) {
        $result = array();
        
        /* @var $itemConfig Stephino_Rpg_Config_Trait_Cost */
        if ($itemConfig instanceof Stephino_Rpg_Config_Item_Single
            && in_array(Stephino_Rpg_Config_Trait_Cost::class, class_uses($itemConfig))) {
            
            // Get the discount
            if ($applyDiscount) {
                $costDiscount = self::getDiscount($itemConfig);
            }
            
            // Get the costs
            foreach (self::getResourceData() as $costKey => $costValue) {
                list($unitName, $unitAjaxKey) = $costValue;
                
                // Prepare the value
                $unitValue = null;
                
                // Go through the known keys
                switch ($costKey) {
                    case Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD:
                        if (is_callable(array($itemConfig, 'getCostGold'))) {
                            $unitValue = $itemConfig->getCostGold();
                        }
                        break;
                    
                    case Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH:
                        if (is_callable(array($itemConfig, 'getCostResearch'))) {
                            $unitValue = $itemConfig->getCostResearch();
                        }
                        break;
                    
                    case Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM:
                        if (is_callable(array($itemConfig, 'getCostGem'))) {
                            $unitValue = $itemConfig->getCostGem();
                        }
                        break;

                    case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA:
                        if (is_callable(array($itemConfig, 'getCostAlpha'))) {
                            $unitValue = $itemConfig->getCostAlpha();
                        }
                        break;

                    case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_BETA:
                        if (is_callable(array($itemConfig, 'getCostBeta'))) {
                            $unitValue = $itemConfig->getCostBeta();
                        }
                        break;

                    case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_GAMMA:
                        if (is_callable(array($itemConfig, 'getCostGamma'))) {
                            $unitValue = $itemConfig->getCostGamma();
                        }
                        break;

                    case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1:
                        if (is_callable(array($itemConfig, 'getCostResourceExtra1'))) {
                            $unitValue = $itemConfig->getCostResourceExtra1();
                        }
                        break;

                    case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2:
                        if (is_callable(array($itemConfig, 'getCostResourceExtra2'))) {
                            $unitValue = $itemConfig->getCostResourceExtra2();
                        }
                        break;
                }

                // Valid field
                if (null !== $unitValue) {
                    // Ajust the value to the desired level
                    $unitPolyValue = Stephino_Rpg_Utils_Config::getPolyValue(
                        $itemConfig->getCostPolynomial(),
                        $itemLevel + 1, 
                        $unitValue
                    );
                    
                    // Apply a discount
                    if ($applyDiscount && null !== $costDiscount) {
                        $unitPolyValue *= (100 - $costDiscount[0]) / 100;
                    }
                    
                    // Store the cost data as an integer
                    $result[$costKey] = array(
                        $unitName,
                        round($unitPolyValue, 2),
                        $unitAjaxKey
                    );
                }
            }
        }
        
        // Multiply
        if (1 !== $multiply) {
            foreach ($result as &$costItem) {
                $costItem[1] *= $multiply;
            }
        }
                    
        return $result;
    }
    
    /**
     * Get the Queue information for buildings
     * 
     * @param int     $buildingOrCityId Building or City ID
     * @param boolean $cityMode         (optional) City mode; if true, the 
     * $buildingOrCityId argument defines the city ID; default <b>false</b>
     * @return array|null Associative array<ul>
     * <li>if <b>$cityMode == true</b>: array({int - building config id} => array(
     * 'queueId' => {int}, 'timeTotal' => {int}, 'timeLeft' => {int}), ...)</li>
     * <li>if <b>$cityMode == false</b>: array('queueId' => {int}, 'timeTotal' => {int}, 'timeLeft' => {int})</li>
     * </ul> or null on error
     */
    public static function getBuildingQueue($buildingOrCityId, $cityMode = false) {
        // Prepare the result
        $queueData = null;
       
        // Invalid input
        if (!is_numeric($buildingOrCityId)) {
            return $queueData;
        }
        
        // Prepare the queued buildings in this city; array of Building ID => Building Config ID
        $cityBuildingConfigIds = array();
        
        // Get the last tick
        $lastTick = null;
        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $dbRow) {
                // Store the last tick
                if (null === $lastTick) {
                    $lastTick = $dbRow[Stephino_Rpg_Db_Table_Users::COL_USER_LAST_TICK];
                    
                    // No need to search further if we don't need building config IDs for this city
                    if (!$cityMode) {
                        break;
                    }
                }
                
                // Add to the building configs
                if ($cityMode) {
                    if ($dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CITY_ID] == $buildingOrCityId) {
                        $cityBuildingConfigIds[$dbRow[Stephino_Rpg_Db_Table_Buildings::COL_ID]] = $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID];
                    }
                }
            }
        }
        
        // Go through the queues
        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Queues::KEY)->getData())) {
            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Queues::KEY)->getData() as $dbRow) {
                // Building queue
                if (Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_BUILDING == $dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_TYPE]) {
                    // Get all data for all queued buildings
                    if ($cityMode) {
                        // Initialize the queue data
                        if (null === $queueData) {
                            $queueData = array();
                        }
                        
                        // Get the config ID
                        if (isset($cityBuildingConfigIds[$dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID]])) {
                            // The queue data key is the current buildings' configuration ID (discovered above)
                            $queueData[$cityBuildingConfigIds[$dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID]]] = array(
                                self::DATA_QUEUE_ID    => $dbRow[Stephino_Rpg_Db_Table_Queues::COL_ID],
                                self::DATA_QUEUE_TIME_TOTAL => $dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_DURATION],
                                self::DATA_QUEUE_TIME_LEFT  => $dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_TIME] - time(),
                            );
                        }
                    } else {
                        // Found our matching building by ID
                        if ($buildingOrCityId == $dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID]) {
                            $queueData = array(
                                self::DATA_QUEUE_ID    => $dbRow[Stephino_Rpg_Db_Table_Queues::COL_ID],
                                self::DATA_QUEUE_TIME_TOTAL => $dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_DURATION],
                                self::DATA_QUEUE_TIME_LEFT  => $dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_TIME] - time(),
                            );
                            break;
                        }
                    }
                }
            }
        }

        return $queueData;
    }
    
    /**
     * Get entity information
     * 
     * @param int    $cityId         City ID
     * @param string $entityType     Entity type, one of <ul>
     * <li>Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT</li>
     * <li>Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_SHIP</li>
     * </ul>
     * @param int    $entityConfigId Entity configuration ID
     * @param int    $entityCount    Entity count
     * @return array Array of <ul>
     * <li><b>entityData</b> (array|null) Entity DB row</li>
     * <li>entityConfig</li>
     * <li>cityData</li>
     * <li>buildingData</li>
     * <li>
     *     <b>queueData</b> - Associative array: 
     *     <ul>
     *         <li><b>self::DATA_QUEUE_ID</b> => (int) Queue ID</li>
     *         <li><b>self::DATA_QUEUE_TOTAL</b> => (int) Queue Duration</li>
     *         <li><b>self::DATA_QUEUE_LEFT</b> => (int) Queue Time Left</li>
     *         <li><b>self::DATA_QUEUE_QUANTITY</b> => (int) Queue Quantity</li>
     *     </ul> or <b>null</b>
     * </li>
     * <li>
     *     <b>costData</b> - Associative array of 
     *     <ul>
     *         <li>(string) <b>Cost Key</b> <i>ex. Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA</i> => array(
     *             <ul>
     *                 <li>(string) <b>Unit Name</b></li>
     *                 <li>(int) <b>Unit Value</b></li>
     *                 <li>(string) <b>Unit AJAX key</b> <i>ex. Stephino_Rpg_Renderer_Ajax::RESULT_RES_ALPHA</i></li>
     *             </ul>)
     *         </li>
     *     </ul>
     * </li>
     * <li>productionData</li>
     * <li>affordList</li>
     * </ul>
     * @throws Exception
     */
    public static function getEntityInfo($cityId, $entityType, $entityConfigId, $entityCount = 1) {
        // Invalid input
        if (null === $cityId || $cityId <= 0) {
            throw new Exception(
                sprintf(
                    __('Invalid ID (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigCityName()
                )
            );
        }
        
        // Validate entity type
        if (!in_array($entityType, array(
            Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT,
            Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_SHIP
        ))) {
            throw new Exception(__('Invalid entity type', 'stephino-rpg'));
        }
        
        // Validate entity config
        if (null === $entityConfigId || $entityConfigId <= 0) {
            throw new Exception(
                sprintf(
                    __('Invalid configuration (%s)', 'stephino-rpg'),
                    __('entity', 'stephino-rpg')
                )
            );
        }
        
        // Prepare the entity config
        $entityConfig = null;
        switch ($entityType) {
            case Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT:
                $entityConfig = Stephino_Rpg_Config::get()->units()->getById($entityConfigId);
                break;
            
            case Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_SHIP:
                $entityConfig = Stephino_Rpg_Config::get()->ships()->getById($entityConfigId);
                break;
        }
        if (null === $entityConfig) {
            throw new Exception(__('Entity configuration ID is invalid', 'stephino-rpg'));
        }
        
        // Prepare the entity data
        $entityData = null;
        
        // Prepare the city data
        $cityData = null;
        
        // Prepare the parent building data
        $parentBuildingData = null;
        
        // Use the cached time-lapse queue support data
        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_Entities::KEY)->getData())) {
            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_Entities::KEY)->getData() as $dbRow) {
                if ($cityId == $dbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CITY_ID]
                    && $entityType == $dbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE]
                    && $entityConfigId == $dbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]) {
                    $entityData = $dbRow;
                    break;
                }
            }
        }
        
        // Use the cached time-lapse resources (avoids an extra, unnecessary DB query)
        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $dbRow) {
                // Found our city
                if ($cityId == $dbRow[Stephino_Rpg_Db_Table_Cities::COL_ID]) {
                    if (null === $cityData) {
                        $cityData = $dbRow;
                    }
                    
                    // Found the parent building
                    if (null !== $entityConfig->getBuilding() && $entityConfig->getBuilding()->getId() == $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID]) {
                        $parentBuildingData = $dbRow;
                        break;
                    }
                }
            }
        }
        
        // City not found
        if (null === $cityData) {
            throw new Exception(
                sprintf(
                    __('Not found (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigCityName()
                )
            );
        }
        
        // Get the queue data
        $queueData = null;
        if (null !== $entityData) {
            $queueData = self::getEntityQueue(
                $entityData[Stephino_Rpg_Db_Table_Entities::COL_ID],
                $entityType
            );
        }
        
        // Prepare the cost data (per entity)
        $costData = self::getCostData(
            $entityConfig,
            null === $parentBuildingData ? 0 : $parentBuildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL] - 1,
            true
        );
        
        // Get the production data
        $productionData = self::getProductionData(
            $entityConfig,
            null === $parentBuildingData ? 0 : $parentBuildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL],
            $parentBuildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_ISLAND_ID]
        );
        
        // Prepare the resources
        $resources = Stephino_Rpg_Renderer_Ajax::getResources($cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]);
        
        // Go through the costs
        $affordList = array();
        if (count($costData)) {
            foreach ($costData as list(, $unitValue, $unitAjaxKey)) {
                if ($unitValue > 0) {
                    $affordList[$unitAjaxKey] = intval($resources[$unitAjaxKey][0] / $unitValue);
                }
            }
        } else {
            // 10 at a time for priceless units
            $affordList = array(10);
        }
        
        // Update the cost data for all entities
        if ($entityCount != 1) {
            foreach ($costData as &$costItem) {
                $costItem[1] *= $entityCount;
            }
        }
        
        // Get the data and configuration
        return array(
            $entityData, 
            $entityConfig, 
            $cityData, 
            $parentBuildingData,
            $queueData, 
            $costData, 
            $productionData,
            $affordList
        );
    }
    
    /**
     * Get the Queue information for entities
     * 
     * @param int    $entityId   Entity ID
     * @param string $entityType Entity Type, one of <ul>
     *     <li>Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT</li>
     *     <li>Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_SHIP</li>
     * </ul>
     * @return array|null Associative array: <ul>
     *     <li><b>self::DATA_QUEUE_ID</b> => (int) Queue ID</li>
     *     <li><b>self::DATA_QUEUE_TOTAL</b> => (int) Queue Duration</li>
     *     <li><b>self::DATA_QUEUE_LEFT</b> => (int) Queue Time Left</li>
     *     <li><b>self::DATA_QUEUE_QUANTITY</b> => (int) Queue Quantity</li>
     * </ul> or null on error
     */
    public static function getEntityQueue($entityId, $entityType) {
        // Prepare the result
        $queueData = null;
       
        // Invalid input
        if (!is_numeric($entityId)) {
            return $queueData;
        }
        
        // Go through the queues
        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Queues::KEY)->getData())) {
            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Queues::KEY)->getData() as $dbRow) {
                // Building queue
                if ($entityType == $dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_TYPE]
                    && $entityId == $dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID]) {
                    $queueData = array(
                        self::DATA_QUEUE_ID         => intval($dbRow[Stephino_Rpg_Db_Table_Queues::COL_ID]),
                        self::DATA_QUEUE_TIME_TOTAL => intval($dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_DURATION]),
                        self::DATA_QUEUE_TIME_LEFT  => intval($dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_TIME]) - time(),
                        self::DATA_QUEUE_QUANTITY   => intval($dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_QUANTITY]),
                    );
                    break;
                }
            }
        }

        return $queueData;
    }
    
    /**
     * Get the Research Areas information for a specific building
     * 
     * @param Stephino_Rpg_Config_Building $buildingConfig Building configuration
     * @return array Array of 
     * <ul>
     *     <li><b>researchAreaConfigs</b> <i>(Stephino_Rpg_Config_ResearchArea[])</i> Research Area Configuration Objects</li>
     *     <li>
     *         <b>researchQueues</b> <i>(array)</i> array(
     *         <ul>
     *             <li>(int) <b>...Research Area Config Id</b> => array(
     *                 <ul>
     *                     <li>(int) <b>...Research Field Config Id</b> => array(
     *                         <ul>
     *                             <li><b>self::DATA_QUEUE_ID</b> => (int) Queue ID</li>
     *                             <li><b>self::DATA_QUEUE_TOTAL</b> => (int) Queue Duration</li>
     *                             <li><b>self::DATA_QUEUE_LEFT</b> => (int) Queue Time Left</li>
     *                             <li><b>self::DATA_QUEUE_QUANTITY</b> => (int) Queue Quantity</li>
     *                         </ul>)
     *                     </li>
     *                 </ul>)
     *             </li>
     *         </ul>)
     *     </li>
     * </ul>
     */
    public static function getResearchBuildingInfo($buildingConfig) {
        // Prepare the research configuration objects
        $researchAreaConfigs = Stephino_Rpg_Config::get()->researchAreas()->getAll();
        
        // Prepare the research queues
        $researchQueues = array();
        
        // Go through all the fields
        foreach (Stephino_Rpg_Config::get()->researchFields()->getAll() as $researchFieldConfig) {
            // Get the parent research area
            if (null !== $researchAreaConfig = $researchFieldConfig->getResearchArea()) {
                if (!isset($researchQueues[$researchAreaConfig->getId()])
                    && null !== $researchAreaConfig->getBuilding() 
                    && $researchAreaConfig->getBuilding()->getId() == $buildingConfig->getId()
                ) {
                    $researchQueues[$researchAreaConfig->getId()] = self::getResearchQueue($researchAreaConfig->getId());
                }
            }
        }
        
        // Prepare the valid research areas for this building
        $validResearchAreaIds = array_keys($researchQueues);
        foreach (array_keys($researchAreaConfigs) as $resAreaConfigId) {
            if (!in_array($resAreaConfigId, $validResearchAreaIds)) {
                unset($researchAreaConfigs[$resAreaConfigId]);
            }
        }
        
        return array(
            $researchAreaConfigs,
            $researchQueues
        );
    }
    
    /**
     * Get the Research information for a specific research area
     * 
     * @param int $researchAreaConfigId Research configuration ID
     * @return array Array of 
     * <ul>
     *     <li><b>researchAreaConfig</b> <i>(Stephino_Rpg_Config_ResearchArea)</i> Research Area Configuration</li>
     *     <li>
     *         <b>researchFieldConfigs</b> <i>(array)</i>
     *         <ul>
     *             <li>(int) <b>...Research Field Config Id</b> => (Stephino_Rpg_Config_ResearchField) <b>Research Field Config</b></li>
     *         </ul>
     *     </li>
     *     <li>
     *         <b>researchFieldData</b> <i>(array)</i>
     *         <ul>
     *             <li>(int) <b>...Research Field Config Id</b> => (array) <b>Research Field Db Row</b></li>
     *         </ul>
     *     </li>
     *     <li>
     *         <b>researchQueue</b> <i>(array)</i> 
     *         <ul>
     *             <li>(int) <b>...Research Field Config Id</b> => array(
     *                 <ul>
     *                     <li><b>self::DATA_QUEUE_ID</b> => (int) Queue ID</li>
     *                     <li><b>self::DATA_QUEUE_TOTAL</b> => (int) Queue Duration</li>
     *                     <li><b>self::DATA_QUEUE_LEFT</b> => (int) Queue Time Left</li>
     *                     <li><b>self::DATA_QUEUE_QUANTITY</b> => (int) Queue Quantity</li>
     *                 </ul>)
     *             </li>
     *         </ul>
     *     </li>
     *     <li>
     *         <b>researchCostData</b> <i>(array)</i> 
     *         <ul>
     *             <li>(int) <b>...Research Field Config Id</b> => array(
     *                 <ul>
     *                     <li>
     *                         <b>...Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_*</b> => array(
     *                         <ul>
     *                             <li>(string) <b>Resource name</b></li>
     *                             <li>(float) <b>Resource cost</b></li>
     *                             <li>(string) <b>Resource AJAX Key</b></li>
     *                         </ul>)
     *                     </li>
     *                 </ul>)
     *             </li>
     *         </ul>
     *     </li>
     *     <li>
     *         <b>researchAffordList</b> <i>(array)</i> 
     *         <ul>
     *             <li>(int) <b>...Research Field Config Id</b> => array(
     *                 <ul>
     *                     <li>(string) <b>...Resource AJAX Key</b> => (int) Count</li>
     *                 </ul>)
     *             </li>
     *         </ul>
     *     </li>
     * </ul>
     * @throws Exception
     */
    public static function getResearchAreaInfo($researchAreaConfigId) {
        // Get the research configuration
        if (null === $researchAreaConfig = Stephino_Rpg_Config::get()->researchAreas()->getById($researchAreaConfigId)) {
            throw new Exception(
                sprintf(
                    __('Invalid configuration (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigResearchAreaName()
                )
            );
        }
        
        // Go through all the fields
        $researchFieldConfigs = array();
        foreach (Stephino_Rpg_Config::get()->researchFields()->getAll() as $researchFieldConfig) {
            if (null !== $researchFieldConfig->getResearchArea()
                && $researchFieldConfig->getResearchArea()->getId() == $researchAreaConfigId) {
                $researchFieldConfigs[$researchFieldConfig->getId()] = $researchFieldConfig;
            }
        }
        
        // Prepare the field data
        $researchFieldData = array();
        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_ResearchFields::KEY)->getData())) {
            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_ResearchFields::KEY)->getData() as $dbRow) {
                if (isset($researchFieldConfigs[$dbRow[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_CONFIG_ID]])) {
                    $researchFieldData[$dbRow[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_CONFIG_ID]] = $dbRow;
                }
            }
        }
        
        // Prepare the resources (city agnostic - all research buildings share the same data)
        $resources = Stephino_Rpg_Renderer_Ajax::getResources();
        
        // Prepare the costs
        $researchCostData = array();
        
        // Prepare the afforded costs list
        $researchAffordList = array();
        
        // Go through the costs
        foreach ($researchFieldConfigs as $researchFieldConfig) {
            // Prepare the research level
            $researchFieldLevel = $researchFieldConfig->getLevelsEnabled() 
                ? (
                    isset($researchFieldData[$researchFieldConfig->getId()])
                        ? $researchFieldData[$researchFieldConfig->getId()][Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_LEVEL]
                        : 0
                )
                : 0;
            
            // Initialize the cost data
            $researchCostData[$researchFieldConfig->getId()] = self::getCostData(
                $researchFieldConfig,
                $researchFieldLevel
            );
            
            // Initialize the afford list
            $researchAffordList[$researchFieldConfig->getId()] = array();
            
            // Cost data defined
            if (count($researchCostData[$researchFieldConfig->getId()])) {
                foreach ($researchCostData[$researchFieldConfig->getId()] as list(, $unitValue, $unitAjaxKey)) {
                    if ($unitValue > 0) {
                        $researchAffordList[$researchFieldConfig->getId()][$unitAjaxKey] = intval($resources[$unitAjaxKey][0] / $unitValue);
                    }
                }
            }
        }
        
        return array(
            $researchAreaConfig,
            $researchFieldConfigs,
            $researchFieldData,
            self::getResearchQueue($researchAreaConfigId),
            $researchCostData,
            $researchAffordList
        );
    }
    
    /**
     * Get the Queue information for research areas
     * 
     * @param int $researchAreaConfigId Research configuration ID
     * @return array array(
     * <ul>
     *     <li>(int) <b>...Research Field Id</b> => array(
     *         <ul>
     *             <li><b>self::DATA_QUEUE_ID</b> => (int) Queue ID</li>
     *             <li><b>self::DATA_QUEUE_TOTAL</b> => (int) Queue Duration</li>
     *             <li><b>self::DATA_QUEUE_LEFT</b> => (int) Queue Time Left</li>
     *             <li><b>self::DATA_QUEUE_QUANTITY</b> => (int) Queue Quantity</li>
     *         </ul>) or <b>null</b>
     *     </li>
     * </ul>) 
     */
    public static function getResearchQueue($researchAreaConfigId) {
        $researchQueueData = array();
        
        do {
            // Validate the research area
            if (null === Stephino_Rpg_Config::get()->researchAreas()->getById($researchAreaConfigId)) {
                break;
            }

            // Get the initialized research fields {Research Field Db Id} => {Research Field Config ID}
            $initializedResearchFields = array();
            if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_ResearchFields::KEY)->getData())) {
                // Prepare research fields for this research area
                $researchAreaFieldIds = array();
                foreach (Stephino_Rpg_Config::get()->researchFields()->getAll() as $researchFieldConfig) {
                    if (null !== $researchAreaConfig = $researchFieldConfig->getResearchArea()) {
                        if ($researchAreaConfig->getId() == $researchAreaConfigId) {
                            $researchAreaFieldIds[] = $researchFieldConfig->getId();
                        }
                    }
                }
                
                // Go through the entries
                if (count($researchAreaFieldIds)) {
                    foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_ResearchFields::KEY)->getData() as $dbRow) {
                        // Our research fields
                        if (Stephino_Rpg_TimeLapse::get()->userId() == $dbRow[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_USER_ID]
                            // From the parent research area
                            && in_array($dbRow[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_CONFIG_ID], $researchAreaFieldIds)) {
                            $initializedResearchFields[$dbRow[Stephino_Rpg_Db_Table_ResearchFields::COL_ID]] = $dbRow[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_CONFIG_ID];
                        }
                    }
                }
            }

            // No initialized research fields
            if (!count($initializedResearchFields)) {
                break; 
            }

            // Go through the queues
            if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Queues::KEY)->getData())) {
                foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Queues::KEY)->getData() as $dbRow) {
                    // Building queue
                    if (Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_RESEARCH == $dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_TYPE]
                        && isset($initializedResearchFields[$dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID]])) {
                        
                        // Get the config ID
                        $researchFieldConfigId = $initializedResearchFields[$dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID]];
                        
                        // The key is the Research Field Config ID
                        $researchQueueData[$researchFieldConfigId] = array(
                            self::DATA_QUEUE_ID         => intval($dbRow[Stephino_Rpg_Db_Table_Queues::COL_ID]),
                            self::DATA_QUEUE_TIME_TOTAL => intval($dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_DURATION]),
                            self::DATA_QUEUE_TIME_LEFT  => intval($dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_TIME]) - time(),
                            self::DATA_QUEUE_QUANTITY   => intval($dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_QUANTITY]),
                        );
                    }
                }
            }
        } while(false);
        
        return $researchQueueData;
    }
    
    /**
     * Get the abundance factor and responsible configuration for this extra resource and city
     * 
     * @param int    $islandId    Island DataBase ID
     * @param string $extraResKey Extra resource column; one of <ul>
     *     <li>Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1</li>
     *     <li>Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2</li>
     * </ul>
     * @return array Array of [<ul>
     *     <li>(float) Abundance value, [0,1]</li>
     *     <li>(string) Responsible configuration key; ex. "Stephino_Rpg_Config_Islands::KEY"</li>
     *     <li>(int) Responsible configuration id</li>
     * </ul>]
     */
    public static function getAbundanceInfo($islandId, $extraResKey) {
        // Abundance data
        $abundanceFactor = null;
        
        // Valid column provided
        if (is_numeric($islandId) && in_array($extraResKey, array(
            Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1,
            Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2,
        ))) {
            // Prepare the island data
            $islandRow = null;

            // Go through the time-lapse data set
            if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
                foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $dbRow) {
                    if ($dbRow[Stephino_Rpg_Db_Table_Islands::COL_ID] == $islandId) {
                        $islandRow = $dbRow;
                        break;
                    }
                }
            }

            // Found our first building in this city
            if (null !== $islandRow) {
                // Valid island configuration
                if (null !== $configIsland = Stephino_Rpg_Config::get()->islands()->getById(
                    $islandRow[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_CONFIG_ID]
                )) {
                    switch ($extraResKey) {
                        case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1:
                            $abundanceFactor = array(
                                $configIsland->getResourceExtra1Abundance() / 100,
                                Stephino_Rpg_Config_Islands::KEY,
                                $islandRow[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_CONFIG_ID]
                            );
                            break;

                        case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2:
                            $abundanceFactor = array(
                                $configIsland->getResourceExtra2Abundance() / 100,
                                Stephino_Rpg_Config_Islands::KEY,
                                $islandRow[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_CONFIG_ID]
                            );
                            break;
                    }
                }
            }

            // Search for overriding research fields
            if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_ResearchFields::KEY)->getData())) {
                foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_ResearchFields::KEY)->getData() as $researchField) {
                    if (null !== $configResearchField = Stephino_Rpg_Config::get()->researchFields()->getById(
                        $researchField[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_CONFIG_ID]
                    )) {
                        // Get the research field level
                        $researchFieldLevel = $researchField[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_LEVEL];

                        // Research finished
                        if ($researchFieldLevel > 0) {
                            // Override abundance factors
                            switch ($extraResKey) {
                                case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1:
                                    if ($configResearchField->getResourceExtra1Abundant()) {
                                        $abundanceFactor = array(
                                            1,
                                            Stephino_Rpg_Config_ResearchFields::KEY,
                                            $researchField[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_CONFIG_ID]
                                        );
                                    }
                                    break;

                                case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2:
                                    if ($configResearchField->getResourceExtra2Abundant()) {
                                        $abundanceFactor = array(
                                            1,
                                            Stephino_Rpg_Config_ResearchFields::KEY,
                                            $researchField[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_CONFIG_ID]
                                        );
                                    }
                                    break;
                            }
                        }
                    }
                }
            }

            // Search for premium queues
            if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Queues::KEY)->getData())) {
                foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Queues::KEY)->getData() as $queueRow) {
                    if (Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_PREMIUM == $queueRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_TYPE]) {
                        // Prepare the configuration object
                        if (null !== $configPremiumModifier = Stephino_Rpg_Config::get()->premiumModifiers()->getById(
                            $queueRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID]
                        )) {
                            // Override abundance factors
                            switch ($extraResKey) {
                                case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1:
                                    if ($configPremiumModifier->getResourceExtra1Abundant()) {
                                        $abundanceFactor = array(
                                            1,
                                            Stephino_Rpg_Config_PremiumModifiers::KEY,
                                            $queueRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID]
                                        );
                                    }
                                    break;

                                case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2:
                                    if ($configPremiumModifier->getResourceExtra2Abundant()) {
                                        $abundanceFactor = array(
                                            1,
                                            Stephino_Rpg_Config_PremiumModifiers::KEY,
                                            $queueRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID]
                                        );
                                    }
                                    break;
                            }
                        }
                    }
                }
            }
        }
        return $abundanceFactor;
    }
    
    /**
     * Get the metric break-down for this resource and city
     * 
     * @param string $cityColumn Metric/resource column
     * @param int    $cityId     City ID
     * @return array See "tpl/settings/settings-resources.php"
     */
    public static function getModifierEffectInfo($cityColumn, $cityId) {
        // Prepare the result
        $result = array();
        
        // Abundance factor
        $abundanceFactor = null;
        
        // Prepare the city configuration object
        $cityConfig = null;
        
        // Prepare the city row
        $cityRow = null;
        
        // Prepare the island ID
        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $dbRow) {
                // Found the city
                if ($cityId == $dbRow[Stephino_Rpg_Db_Table_Cities::COL_ID]) {
                    // Get the city configuration object
                    $cityConfig = Stephino_Rpg_Config::get()->cities()->getById(
                        $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_CONFIG_ID]
                    );
                    
                    // Store the city row
                    $cityRow = $dbRow;
                    break;
                }
            }
        }
        
        // Need more info
        if (in_array($cityColumn, array(
            Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_SATISFACTION,
            Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1,
            Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2,
        ))) {
            // Satisfaction baseline is at city level
            if (Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_SATISFACTION == $cityColumn) {
                // Valid city configuration object
                if (null !== $cityConfig) {
                    // Store the base satisfaction value
                    $result = array(
                        Stephino_Rpg_Config_Cities::KEY => array(
                            $cityConfig->getId() => array(
                                Stephino_Rpg_Utils_Config::getPolyValue(
                                    $cityConfig->getSatisfactionPolynomial(),
                                    $cityRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL], 
                                    $cityConfig->getSatisfaction()
                                ),
                                $cityConfig->getName()
                            )
                        )
                    );
                }
            } else {
                // Abundance factor, island-level
                if (null !== $cityRow) {
                    $abundanceFactor = self::getAbundanceInfo(
                        $cityRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID], 
                        $cityColumn
                    );
                }
            }
        }
        
        // Prepare the modifier method name
        $modifierMethodName = null;
        switch ($cityColumn) {
            case Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_SATISFACTION:
                $modifierMethodName = 'getEffectMetricSatisfaction';
                break;
            
            case Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION:
                $modifierMethodName = 'getEffectMetricPopulation';
                break;
            
            case Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_STORAGE:
                $modifierMethodName = 'getEffectMetricStorage';
                break;
            
            case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA:
                $modifierMethodName = 'getEffectResourceAlpha';
                break;
            
            case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_BETA:
                $modifierMethodName = 'getEffectResourceBeta';
                break;
            
            case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_GAMMA:
                $modifierMethodName = 'getEffectResourceGamma';
                break;
            
            case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1:
                $modifierMethodName = 'getEffectResourceExtra1';
                break;
            
            case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2:
                $modifierMethodName = 'getEffectResourceExtra2';
                break;
            
            case Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD:
                $modifierMethodName = 'getEffectResourceGold';
                break;
            
            case Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM:
                $modifierMethodName = 'getEffectResourceGem';
                break;
            
            case Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH:
                $modifierMethodName = 'getEffectResourceResearch';
                break;
        }
        
        // Invalid column
        if (null === $modifierMethodName) {
            return $result;
        }
        
        // Prepare the configuration sets
        $configSets = array(
            Stephino_Rpg_Config_Buildings::KEY        => Stephino_Rpg_Config::get()->buildings()->getAll(),
            Stephino_Rpg_Config_Governments::KEY      => Stephino_Rpg_Config::get()->governments()->getAll(),
            Stephino_Rpg_Config_IslandStatues::KEY    => Stephino_Rpg_Config::get()->islandStatues()->getAll(),
            Stephino_Rpg_Config_PremiumModifiers::KEY => Stephino_Rpg_Config::get()->premiumModifiers()->getAll(),
            Stephino_Rpg_Config_ResearchFields::KEY   => Stephino_Rpg_Config::get()->researchFields()->getAll(),
            Stephino_Rpg_Config_Ships::KEY            => Stephino_Rpg_Config::get()->ships()->getAll(),
            Stephino_Rpg_Config_Units::KEY            => Stephino_Rpg_Config::get()->units()->getAll(),
        );
        
        // Prepare the valid modifier sets
        $configSetsValid = array();
        foreach ($configSets as $configSetKey => $configSetObjects) {
            foreach($configSetObjects as $configSetObject) {
                $modifierConfig = $configSetObject->getModifier();
                if (null !== $modifierConfig && null !== $modifierConfig->$modifierMethodName()) {
                    // Initialize the key
                    if (!isset($configSetsValid[$configSetKey])) {
                        $configSetsValid[$configSetKey] = array();
                    }
                    
                    // Append the result
                    $configSetsValid[$configSetKey][$configSetObject->getId()] = array(
                        $configSetObject,
                        $modifierConfig->$modifierMethodName()
                    );
                }
            }
        }
        
        // Convert to final values
        if (count($configSetsValid)) {
            foreach($configSetsValid as $configSetKey => &$configSetData) {
                foreach ($configSetData as $configId => &$entry) {
                    // Prepare the level
                    $level = 0;
                    
                    // Prepare the production factor
                    $prodFactor = null;
                    switch($configSetKey) {
                        case Stephino_Rpg_Config_Governments::KEY:
                            if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
                                foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $dbRow) {
                                    if ($cityId == $dbRow[Stephino_Rpg_Db_Table_Cities::COL_ID]
                                        && $configId == $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_GOVERNMENT_CONFIG_ID]) {
                                        $level = intval($dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL]);
                                        break;
                                    }
                                }
                            }
                            break;
                            
                        case Stephino_Rpg_Config_PremiumModifiers::KEY:
                            if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Queues::KEY)->getData())) {
                                foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Queues::KEY)->getData() as $dbRow) {
                                    if (Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_PREMIUM == $dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_TYPE]
                                        && $configId == $dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID]) {
                                        $level = intval($dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_QUANTITY]);
                                        break;
                                    }
                                }
                            }
                            break;
                        
                        case Stephino_Rpg_Config_IslandStatues::KEY:
                            if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
                                foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $dbRow) {
                                    if ($cityId == $dbRow[Stephino_Rpg_Db_Table_Cities::COL_ID]
                                        && $configId == $dbRow[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_STATUE_CONFIG_ID]) {
                                        $level = intval($dbRow[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_STATUE_LEVEL]);
                                        break;
                                    }
                                }
                            }
                            break;
                        
                        case Stephino_Rpg_Config_Buildings::KEY:
                            if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
                                foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $dbRow) {
                                    if ($cityId == $dbRow[Stephino_Rpg_Db_Table_Cities::COL_ID]
                                        && $configId == $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID]) {
                                        $level = intval($dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL]);
                                        
                                        // Workers/Population are used for production calculation; @see Stephino_Rpg_TimeLapse_Resources::_resourcesAdditive
                                        if (!in_array($cityColumn, array(
                                            Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_SATISFACTION,
                                            Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION,
                                            Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_STORAGE,
                                        ))) {
                                            $prodFactor = self::getBuildingProdFactor(
                                                $cityConfig, 
                                                $entry[0], 
                                                $dbRow
                                            );
                                        }
                                        break;
                                    }
                                }
                            }
                            break;
                            
                        case Stephino_Rpg_Config_ResearchFields::KEY:
                            if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_ResearchFields::KEY)->getData())) {
                                foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_ResearchFields::KEY)->getData() as $dbRow) {
                                    if ($configId == $dbRow[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_CONFIG_ID]) {
                                        $level = intval($dbRow[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_LEVEL]);
                                        break;
                                    }
                                }
                            }
                            break;
                            
                        case Stephino_Rpg_Config_Units::KEY:
                        case Stephino_Rpg_Config_Ships::KEY:
                            // Prepare the database key to compare against
                            $entityType = $configSetKey == Stephino_Rpg_Config_Units::KEY
                                ? Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT
                                : Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_SHIP;
                            
                            // Get the parent building's level
                            if (null !== $entry[0]->getBuilding()) {
                                if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
                                    foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $dbRow) {
                                        if ($cityId == $dbRow[Stephino_Rpg_Db_Table_Cities::COL_ID]
                                            && $entry[0]->getBuilding()->getId() == $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID]) {
                                            $level = intval($dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL]);
                                            break;
                                        }
                                    }
                                }
                            }
                            
                            // Get the entity count
                            if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_Entities::KEY)->getData())) {
                                foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_Entities::KEY)->getData() as $dbRow) {
                                    if ($cityId == $dbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CITY_ID]
                                        && $configId == $dbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]
                                        && $entityType == $dbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE]) {
                                        if ((int) $dbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT] > 0) {
                                            $prodFactor = (int) $dbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT];
                                        }
                                        break;
                                    }
                                }
                            }
                            
                            // No units/ships garrisoned here, don't show "0 entities"
                            if (null === $prodFactor) {
                                $level = 0;
                            }
                            break;
                    }
                    
                    // Invalid entry
                    if ($level <= 0) {
                        unset($configSetData[$configId]);
                        continue;
                    }
                    
                    // Prepare the new value
                    $prodValue = Stephino_Rpg_Utils_Config::getPolyValue(
                        $entry[0]->getModifierPolynomial(),
                        $level, 
                        $entry[1]
                    );
                    
                    // Labor-based production (building) or entity amplifier (units and ships)
                    if (null !== $prodFactor) {
                        $prodValue *= $prodFactor;
                    }
                    
                    // Abundance factor
                    if (null !== $abundanceFactor) {
                        $prodValue *= $abundanceFactor[0];
                    }
                    
                    // Replace entry with the new value
                    $entry = array(
                        // Final value
                        $prodValue,
                        
                        // Configuration entity name
                        $entry[0]->getName(),
                        
                        // Production factor
                        $prodFactor,
                        
                        // Abundance factor array of [abundance factor, config key, config id]; only for extra1, extra2 resources
                        $abundanceFactor
                    );
                }
                
                // Valid result
                if (count($configSetData)) {
                    $result[$configSetKey] = $configSetData;
                }
            }
        }

        // Convert to final values
        return $result;
    }
    
    /**
     * Spend resources updating both the DB and the local time-lapse data store, optionally double-checking item requirements first
     * 
     * @param array                           $resourceData Associative array of 
     * <ul>
     *     <li>(string) <b>Resource Key</b> <i>ex. Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA</i> => array(
     *         <ul>
     *             <li>(string) <b>Resource Name</b></li>
     *             <li>(int) <b>Resource Value</b></li>
     *             <li>(string) <b>Resource AJAX key</b> <i>ex. Stephino_Rpg_Renderer_Ajax::RESULT_RES_ALPHA</i></li>
     *         </ul>)
     *     </li>
     * </ul>
     * @param array                           $cityData (optional) City DB array. Must contain the following keys:<ul>
     *     <li>Stephino_Rpg_Db_Table_Users::COL_ID</li>
     *     <li>Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD</li>
     *     <li>Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH</li>
     *     <li>Stephino_Rpg_Db_Table_Cities::COL_ID</li>
     *     <li>Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA</li>
     *     <li>Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_BETA</li>
     *     <li>Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_GAMMA</li>
     *     <li>Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1</li>
     *     <li>Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2</li>
     * </ul> default <b>null</b> - auto-populated with user-level resources
     * @param int                             $multiplier (optional) Cost multiplier; default <b>1</b>
     * @param Stephino_Rpg_Config_Item_Single $itemConfig (optional) Configuration object to check Requirements against; default <b>null</b>
     * @param boolean                         $refund (optional) Reverse an expense; default <b>false</b>   
     * @throws Exception
     */
    public static function spend($resourceData, $cityData = null, $multiplier = 1, $itemConfig = null, $refund = false) {
        // Multiplier
        $multiplier = abs((int) $multiplier);
        
        // Prepare the city data
        if (null === $cityData) {
            // Prepare the city data; any city will do, as we'll be spending user-level resources
            if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
                $cityData = current(
                    Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData()
                );
            }
        }
        
        // Nothing to do
        if ($multiplier == 0 || !is_array($resourceData) || !is_array($cityData) 
            || !isset($cityData[Stephino_Rpg_Db_Table_Cities::COL_ID])
            || !isset($cityData[Stephino_Rpg_Db_Table_Users::COL_ID])) {
            throw new Exception(
                $refund 
                ? __('Nothing to refund', 'stephino-rpg')
                : __('Could not acquire item', 'stephino-rpg')
            );
        }
        
        // Validate requirements, throwing an Exception on error
        self::getRequirements($itemConfig, $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID], true);
        
        // Store the city ID
        $cityDataId = $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID];
        $userDataId = $cityData[Stephino_Rpg_Db_Table_Users::COL_ID];
        
        // Prepare the cost updates
        $updatesUser = array(
            $userDataId => array()
        );
        $updatesCity = array(
            $cityDataId => array()
        );
        
        // Go through the costs
        foreach ($resourceData as $costKey => $costInfo) {
            // Invalid cost information
            if (!is_array($costInfo) || count($costInfo) < 2) {
                continue;
            }
            
            // Get the cost name and value
            list($costName, $costValue) = $costInfo;
            
            // Sanitize the cost
            $costValue = floatval($costValue);
            
            // No cost or invalid key
            if ($costValue <= 0 || !isset($cityData[$costKey])) {
                continue;
            }
            
            // Get the current balance
            $costBalance = floatval($cityData[$costKey]);
            
            // Cost multiplier
            if (1 != $multiplier) {
                $costValue *= $multiplier;
            }
            
            // We cannot afford this
            if (!$refund && $costValue > $costBalance) {
                throw new Exception(
                    sprintf(
                        __('Insufficient resources (%s)', 'stephino-rpg'),
                        $costName
                    )
                );
            }
            
            // Go through the known keys
            switch($costKey) {
                case Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD:
                case Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH:
                case Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM:
                    $updatesUser[$userDataId][$costKey] = round(
                        $costBalance + ($refund ? 1 : -1) * $costValue, 
                        4
                    );

                    // Update the time-lapse references (for the wrap method to work)
                    Stephino_Rpg_TimeLapse::get()
                        ->worker(Stephino_Rpg_TimeLapse_Resources::KEY)
                        ->updateRef(
                            Stephino_Rpg_Db_Table_Users::COL_ID, 
                            $userDataId, 
                            $costKey, 
                            $updatesUser[$userDataId][$costKey]
                        );
                    break;
                
                case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA:
                case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_BETA:
                case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_GAMMA:
                case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1:
                case Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2:
                    $updatesCity[$cityDataId][$costKey] = round(
                        $costBalance + ($refund ? 1 : -1) * $costValue, 
                        4
                    );
                    
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
        if (count($updatesUser[$userDataId])) {
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
        if (count($updatesCity[$cityDataId])) {
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
}
        
/*EOF*/