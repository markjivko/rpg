<?php

/**
 * Stephino_Rpg_Renderer_Ajax_Cells
 * 
 * @title      Grid info
 * @desc       Get the grid info for available views
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Renderer_Ajax_Cells {
    
    // Cell data response keys
    const CELL_DATA_TYPE          = 'type';
    const CELL_DATA_TYPE_ISLAND   = Stephino_Rpg_Config_Islands::KEY;
    const CELL_DATA_TYPE_STATUE   = Stephino_Rpg_Config_IslandStatues::KEY;
    const CELL_DATA_TYPE_CITY     = Stephino_Rpg_Config_Cities::KEY;
    const CELL_DATA_TYPE_BUILDING = Stephino_Rpg_Config_Buildings::KEY;
    const CELL_DATA_QUEUE         = 'queue';
    const CELL_CONFIG_ID          = 'id';
    const CELL_CONFIG_NAME        = 'name';
    const CELL_DATA_X             = 'x';
    const CELL_DATA_Y             = 'y';
    const CELL_DATA_DATA          = 'data';
    const CELL_DATA_CONFIG_ID     = 'configId';
    const CELL_DATA_ANIMATIONS    = 'anim';
    const CELL_DATA_CITY_SLOTS    = '_citySlots';
    const CELL_DATA_ISLAND_W      = '_islandWidth';
    const CELL_DATA_ISLAND_H      = '_islandHeight';
    const CELL_DATA_ISLAND_STATUE = '_islandStatueSlot';
    const CELL_DATA_ISLAND_OWN    = '_islandOwn';
    const CELL_DATA_CITY_OWN      = '_cityOwn';
    
    // Request keys
    const REQUEST_X         = 'x';
    const REQUEST_Y         = 'y';
    const REQUEST_RADIUS    = 'radius';
    const REQUEST_EXCLUDE   = 'exclude';
    const REQUEST_ISLAND_ID = 'islandId';
    const REQUEST_CITY_ID   = 'cityId';
    
    // Result keys
    const RESULT_GRID              = 'grid';
    const RESULT_ISLAND_CONFIG     = 'islandConfig';
    const RESULT_CITY_CONFIG       = 'cityConfig';
    
    /**
     * Get the islands around a specified point
     * 
     * @param array $data Associative array of <ul>
     * <li>Stephino_Rpg_Renderer_Ajax_Cells::REQUEST_X</li>
     * <li>Stephino_Rpg_Renderer_Ajax_Cells::REQUEST_Y</li>
     * <li>Stephino_Rpg_Renderer_Ajax_Cells::REQUEST_RADIUS</li>
     * <li>Stephino_Rpg_Renderer_Ajax_Cells::REQUEST_EXCLUDE</li>
     * </ul>
     * @return array|null Grid details or null if no new cell found
     * @throws Exception
     */
    public static function ajaxCellsWorld($data) {
        // Sanitize X
        if (!isset($data[self::REQUEST_X])) {
            throw new Exception(__('X coordinate not provided', 'stephino-rpg'));
        }
        $coordX = intval($data[self::REQUEST_X]);
        
        // Sanitize Y
        if (!isset($data[self::REQUEST_Y])) {
            throw new Exception(__('Y coordinate not provided', 'stephino-rpg'));
        }
        $coordY = intval($data[self::REQUEST_Y]);
        
        // Get the radius
        $radius = isset($data[self::REQUEST_RADIUS]) 
            ? intval($data[self::REQUEST_RADIUS]) 
            : 4;
        
        // Get the excluded IDs
        $excludedIds = (isset($data[self::REQUEST_EXCLUDE]) && is_array($data[self::REQUEST_EXCLUDE]))
            ? array_map('intval', $data[self::REQUEST_EXCLUDE])
            : array();
        
        // Get the islands around this point
        $cellGrid = Stephino_Rpg_Db::get()->tableIslands()->getIslands($coordX, $coordY, $radius, $excludedIds);

        // Valid list of islands found
        if (is_array($cellGrid) && count($cellGrid)) {
            // Prepare our island IDs
            $ourIslands = array();
            if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
                foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $resDbRow) {
                    $resIslandId = intval($resDbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_ISLAND_ID]);
                    if (!in_array($resIslandId, $ourIslands)) {
                        $ourIslands[] = $resIslandId;
                    }
                }
            }

            // Parse the data
            foreach ($cellGrid as $key => &$islandData) {
                do {
                    // Get the island configuration
                    if (null !== $configIsland = Stephino_Rpg_Config::get()
                        ->islands()
                        ->getById($islandData[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_CONFIG_ID])) {
                        // Get the statue slot
                        if (null !== $statueSlot = $configIsland->getStatueSlot()) {
                            // Get the x, y coordinates
                            $coords = json_decode($statueSlot);
                            if (is_array($coords) && 2 === count($coords)) {
                                // Store the island position
                                list(
                                    $islandData[self::CELL_DATA_X], 
                                    $islandData[self::CELL_DATA_Y]
                                ) = Stephino_Rpg_Utils_Math::getSnakePoint(
                                    $islandData[Stephino_Rpg_Db_Table_Islands::COL_ID]
                                );
                                
                                // Store the config data
                                $islandData[self::CELL_DATA_CITY_SLOTS] = @json_decode($configIsland->getCitySlots(), true);
                                $islandData[self::CELL_DATA_ISLAND_STATUE] = $coords;
                                $islandData[self::CELL_DATA_ISLAND_W]      = $configIsland->getIslandWidth();
                                $islandData[self::CELL_DATA_ISLAND_H]      = $configIsland->getIslandHeight();

                                // Store the available animations
                                $animationsArray = json_decode($configIsland->getWorldAnimations(), true);
                                if (!is_array($animationsArray)) {
                                    $islandData[self::CELL_DATA_ANIMATIONS] = array();
                                } else {
                                    $islandData[self::CELL_DATA_ANIMATIONS] = array_keys($animationsArray);
                                }

                                // Prepare the island ID
                                $islandId = intval($islandData[Stephino_Rpg_Db_Table_Islands::COL_ID]);

                                // Is this our island?
                                if (in_array($islandId, $ourIslands)) {
                                    $islandData[self::CELL_DATA_ISLAND_OWN] = true;
                                }

                                // Valid entry
                                break;
                            }
                        }
                    }

                    // Invalid island definition
                    unset($cellGrid[$key]);
                } while (false);
            }
        }
        
        // All done
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_GRID => $cellGrid,
            )
        );
    }
    
    /**
     * Get the cells for the specified island. Includes data for cities and the island statue.
     * 
     * @param array $data Associative array containing<ul>
     * <li><b>self::REQUEST_ISLAND_ID</b> (int) Island ID</li>
     * </ul>
     * @return array Grid details
     * @throws Exception
     */
    public static function ajaxCellsIsland($data) {
        if (!isset($data[self::REQUEST_ISLAND_ID])) {
            throw new Exception(
                sprintf(
                    __('Invalid ID (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigIslandName()
                )
            );
        }
        $islandId = intval($data[self::REQUEST_ISLAND_ID]);
        
        // Get the island information
        if (null === $islandData = Stephino_Rpg_Db::get()->tableIslands()->getById($islandId)) {
            throw new Exception(
                sprintf(
                    __('Not found (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigIslandName()
                )
            );
        }
        
        // Prepare the cities information
        $citiesData = Stephino_Rpg_Db::get()->tableCities()->getByIsland($islandId);
        
        // Get the island config
        if (null === $configIsland = Stephino_Rpg_Config::get()
            ->islands()
            ->getById($islandData[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_CONFIG_ID])) {
            throw new Exception(
                sprintf(
                    __('Invalid configuration (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigIslandName()
                )
            );
        }
        
        // Get the island statue config
        if (null === $configIslandStatue = Stephino_Rpg_Config::get()
            ->islandStatues()
            ->getById($islandData[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_STATUE_CONFIG_ID])) {
            throw new Exception(
                sprintf(
                    __('Invalid configuration (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigIslandStatueName()
                )
            );
        }
        
        // Prepare the city slots
        $citySlots = array();
        if (null !== $configIsland->getCitySlots()) {
            $citySlots = array_map(
                function($item) {return implode(',', $item);}, 
                json_decode($configIsland->getCitySlots(), true)
            );
        }

        // Prepare the animation slots
        $animationSlots = array();
        if (null !== $configIsland->getAnimationSlots()) {
            $animationSlots = array_map(
                function($item) {return implode(',', $item);}, 
                json_decode($configIsland->getAnimationSlots(), true)
            );
        }

        // Prepare the statue slot
        $statueSlot = '';
        if (null !== $configIsland->getStatueSlot()) {
            $statueSlot = implode(',', json_decode($configIsland->getStatueSlot(), true));
        }
        
        // Prepare the currentuser data
        $userData = null;
        
        // Valid data stored for this user
        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
            // Get the first row
            $userData = current(
                Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData()
            );
        }
        
        // Prepare the grid
        $grid = array_map(
            function($item) use($userData, $islandData, $citiesData, $configIsland, $configIslandStatue, $citySlots, $animationSlots, $statueSlot) {
                // Prepare the cell data
                $cellData = array(
                    self::CELL_DATA_X          => $item[0],
                    self::CELL_DATA_Y          => $item[1],
                    self::CELL_DATA_ANIMATIONS => array(),
                );
                
                // Prepare the current slot
                $currentSlot = $item[0] . ',' . $item[1];
                
                // Is this a statue slot?
                if ($currentSlot == $statueSlot) {
                    $cellData[self::CELL_DATA_TYPE] = self::CELL_DATA_TYPE_STATUE;
                    $cellData[self::CELL_DATA_DATA] = array(
                        self::CELL_CONFIG_ID   => $configIslandStatue->getId(),
                        self::CELL_CONFIG_NAME => $configIslandStatue->getName(),
                    );
                    
                    // Get the island animations array
                    if (is_array($animationsArray = json_decode($configIslandStatue->getIslandAnimations(), true))) {
                        $cellData[self::CELL_DATA_ANIMATIONS][self::CELL_DATA_TYPE_STATUE] = array(
                            $configIslandStatue->getId() => array_keys($animationsArray)
                        );
                    }
                } else {
                    // This is a city slot
                    if (false !== $citySlotIndex = array_search($currentSlot, $citySlots)) {
                        // This is a city!
                        $cellData[self::CELL_DATA_TYPE] = self::CELL_DATA_TYPE_CITY;
                        $cellData[self::CELL_DATA_DATA] = null;
                        
                        // Prepare the city data
                        $cityData = null;
                        
                        // Go through the cities list
                        if (is_array($citiesData)) {
                            foreach ($citiesData as $cityDbRow) {
                                if ($citySlotIndex == $cityDbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_INDEX]) {
                                    $cityData = $cityDbRow;
                                    break;
                                }
                            }
                        }
                            
                        // Found a city
                        if (null !== $cityData) {
                            // Prepare the city data
                            $cellData[self::CELL_DATA_DATA] = array(
                                Stephino_Rpg_Db_Table_Cities::COL_ID => $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID],
                                Stephino_Rpg_Db_Table_Cities::COL_CITY_NAME => $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_NAME],
                                Stephino_Rpg_Db_Table_Cities::COL_CITY_CONFIG_ID => $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_CONFIG_ID],
                                Stephino_Rpg_Db_Table_Cities::COL_CITY_IS_CAPITAL => $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_IS_CAPITAL],
                                Stephino_Rpg_Renderer_Ajax_Action_City::CELL_CONFIG_CITY_BKG => Stephino_Rpg_Utils_Media::getClosestBackgroundId(
                                    Stephino_Rpg_Config_Cities::KEY,
                                    $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_CONFIG_ID],
                                    $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL]
                                )
                            );

                            // This is my city!
                            $cellData[self::CELL_DATA_CITY_OWN] = (null !== $userData && ($userData[Stephino_Rpg_Db_Table_Users::COL_ID] == $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_USER_ID]));
                            
                            // Get the configuration ID
                            if (null !== $configCity = Stephino_Rpg_Config::get()->cities()->getById($cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_CONFIG_ID])) {
                                $cellData[self::CELL_DATA_CONFIG_ID] = $configCity->getId();

                                // Get the island animations array
                                $closestAnimation = Stephino_Rpg_Renderer_Ajax_Action_City::getClosestIslandAnimation(
                                    $configCity->getId(), 
                                    $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL],
                                    true
                                );
                                if (null !== $closestAnimation) {
                                    $cellData[self::CELL_DATA_ANIMATIONS][self::CELL_DATA_TYPE_CITY] = $closestAnimation;
                                }
                            }
                        } else {
                            // Vacant lot
                            if (is_array($vacantLotAnimations = json_decode($configIsland->getVacantLotAnimations(), true))) {
                                $cellData[self::CELL_DATA_ANIMATIONS][self::CELL_DATA_TYPE_ISLAND] = array(
                                    $configIsland->getId() . '-' . Stephino_Rpg_Renderer_Ajax_Css::ANIMATION_QUALIFIER_VACANT => array_keys($vacantLotAnimations)
                                );
                            }
                        }
                    }
                }
                
                // Add the island animations (may overlap vacant slots)
                if (false !== $animationSlotIndex = array_search($currentSlot, $animationSlots)) {
                    if (!isset($cellData[self::CELL_DATA_ANIMATIONS][self::CELL_DATA_TYPE_ISLAND])) {
                        $cellData[self::CELL_DATA_ANIMATIONS][self::CELL_DATA_TYPE_ISLAND] = array();
                    }
                    $cellData[self::CELL_DATA_ANIMATIONS][self::CELL_DATA_TYPE_ISLAND][$configIsland->getId()] = array($animationSlotIndex);
                }
                
                // Cell structure complete
                return $cellData;
            },
            
            // Get the snake grid
            Stephino_Rpg_Utils_Math::getSnakeGrid(
                $configIsland->getIslandWidth(),
                $configIsland->getIslandHeight()
            )
        );
        
        // All done
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_ISLAND_CONFIG => array(
                    self::CELL_CONFIG_ID   => $configIsland->getId(),
                    self::CELL_CONFIG_NAME => $configIsland->getName()
                ),
                self::RESULT_GRID          => $grid
            ),
            null,
            $islandId, 
            Stephino_Rpg_Db_Table_Islands::COL_ID
        );
    }
    
    /**
     * Get the cells for the specified city. Includes buildings data.
     * 
     * @param array $data Associative array specifying self::REQUEST_CITY_ID
     * @return array Grid details
     * @throws Exception
     */
    public static function ajaxCellsCity($data) {
        if (!isset($data[self::REQUEST_CITY_ID])) {
            throw new Exception(
                __('Invalid request', 'stephino-rpg')
            );
        }
        $cityId = intval($data[self::REQUEST_CITY_ID]);
        
        // Get the city information
        $buildingsData = array();
        
        // Get the building queues
        $queuesData = Stephino_Rpg_Renderer_Ajax_Action::getBuildingQueue($cityId, true);

        // Prepare the city configuration
        $configCity = null;
        
        // Prepare the city level
        $cityLevel = null;
        
        // Valid info stored for this user
        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $dbRow) {
                // Found a building in our city
                if($dbRow[Stephino_Rpg_Db_Table_Cities::COL_ID] == $cityId) {
                    // Store the city level
                    $cityLevel = $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL];
                    
                    // Store the data
                    $buildingsData[$dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID]] = array(
                        Stephino_Rpg_Db_Table_Buildings::COL_ID => $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_ID],
                        Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL => $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL],
                        Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID => $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID],
                        Stephino_Rpg_Renderer_Ajax_Action_Building::CELL_CONFIG_ACTION_AREA => Stephino_Rpg_Renderer_Ajax_Action_Building::getClosestActionArea(
                            $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID],
                            $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL]
                        ),
                        Stephino_Rpg_Renderer_Ajax_Action_Building::CELL_CONFIG_BUILDING_BKG => Stephino_Rpg_Utils_Media::getClosestBackgroundId(
                            Stephino_Rpg_Config_Buildings::KEY,
                            $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID],
                            $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL]
                        )
                    );
                    
                    // Initialize the configuration
                    if (null === $configCity) {
                        $configCity = Stephino_Rpg_Config::get()->cities()->getById($dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_CONFIG_ID]);
                    }
                }
            }
        }
        
        // This is not our city
        if (!count($buildingsData)) {
            throw new Exception(
                sprintf(
                    __('Not found (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigCityName()
                )
            );
        }
        
        // Get the city configuration
        if (null === $configCity) {
            throw new Exception(
                sprintf(
                    __('Invalid configuration (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigCityName()
                )
            );
        }
        
        // Prepare the building slots
        $cityBuildingSlots = array();
        if (null !== $configCity->getCityBuildingSlots()) {
            $cityBuildingSlots = array_map(function($item) {return implode(',', $item);}, json_decode($configCity->getCityBuildingSlots(), true));
        }
        
        // Prepare the animation slots
        $animationSlots = array();
        if (null !== $configCity->getCityAnimationSlots()) {
            $animationSlots = array_map(function($item) {return implode(',', $item);}, json_decode($configCity->getCityAnimationSlots(), true));
        }

        // Prepare the grid
        $grid = array_map(
            function($item) use($buildingsData, $queuesData, $configCity, $cityBuildingSlots, $animationSlots, $cityId) {
                // Prepare the cell data
                $cellData = array(
                    self::CELL_DATA_X          => $item[0],
                    self::CELL_DATA_Y          => $item[1],
                    self::CELL_DATA_ANIMATIONS => array(),
                );
                
                // Prepare the current slot
                $currentSlot = $item[0] . ',' . $item[1];
                
                // This is a building slot
                if (false !== $buildingSlotIndex = array_search($currentSlot, $cityBuildingSlots)) {
                    $cellData[self::CELL_DATA_TYPE] = self::CELL_DATA_TYPE_BUILDING;
                    $cellData[self::CELL_DATA_DATA] = null;
                    
                    // Get the building configuration
                    if (null !== $configBuilding = Stephino_Rpg_Config::get()->buildings()->getById($buildingSlotIndex)) {
                        // Store the building data
                        if (isset($buildingsData[$configBuilding->getId()]) 
                            && $buildingsData[$configBuilding->getId()][Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL] > 0) {
                            $cellData[self::CELL_DATA_DATA] = $buildingsData[$configBuilding->getId()];
                            
                            // Prepare the animation
                            $closestAnimation = Stephino_Rpg_Renderer_Ajax_Action_Building::getClosestAnimation(
                                $configBuilding->getId(),
                                $buildingsData[$configBuilding->getId()][Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL]
                            );

                            // Initialize the array
                            if (null !== $closestAnimation) {
                                if (!isset($cellData[self::CELL_DATA_ANIMATIONS][self::CELL_DATA_TYPE_BUILDING])) {
                                    $cellData[self::CELL_DATA_ANIMATIONS][self::CELL_DATA_TYPE_BUILDING] = array();
                                }
                                if (!isset($cellData[self::CELL_DATA_ANIMATIONS][self::CELL_DATA_TYPE_BUILDING][$configBuilding->getId()])) {
                                    $cellData[self::CELL_DATA_ANIMATIONS][self::CELL_DATA_TYPE_BUILDING][$configBuilding->getId()] = $closestAnimation;
                                }
                            }
                        } else {
                            list(, $buildingRequirementsMet) = Stephino_Rpg_Renderer_Ajax_Action::getRequirements($configBuilding, $cityId);
                            if ($buildingRequirementsMet) {
                                $cellData[self::CELL_DATA_DATA] = true;
                            }
                        }
                        
                        // Store the queue data
                        if (isset($queuesData[$configBuilding->getId()])) {
                            $cellData[self::CELL_DATA_QUEUE] = $queuesData[$configBuilding->getId()];
                        }
                        
                        // Store the configuration
                        $cellData[self::CELL_DATA_CONFIG_ID] = $configBuilding->getId();
                        
                        // Get the under construction animations
                        if (is_array($ucAnimation = json_decode($configCity->getUnderConstructionAnimations(), true))) {
                            // Initialize the array
                            if (!isset($cellData[self::CELL_DATA_ANIMATIONS][self::CELL_DATA_TYPE_BUILDING])) {
                                $cellData[self::CELL_DATA_ANIMATIONS][self::CELL_DATA_TYPE_BUILDING] = array();
                            }
                            if (!isset($cellData[self::CELL_DATA_ANIMATIONS][self::CELL_DATA_TYPE_BUILDING][$configBuilding->getId()])) {
                                $cellData[self::CELL_DATA_ANIMATIONS][self::CELL_DATA_TYPE_BUILDING][$configBuilding->getId()] = array();
                            }
                            $cellData[self::CELL_DATA_ANIMATIONS][self::CELL_DATA_TYPE_BUILDING][$configBuilding->getId()][Stephino_Rpg_Renderer_Ajax_Css::ANIMATION_QUALIFIER_UC] = array_keys($ucAnimation);
                        }
                    }
                }
                
                // Add the animations
                if (false !== $animationSlotIndex = array_search($currentSlot, $animationSlots)) {
                    $cellData[self::CELL_DATA_ANIMATIONS][self::CELL_DATA_TYPE_CITY] = array(
                        $configCity->getId() => array($animationSlotIndex),
                    );
                }
                
                // Cell structure complete
                return $cellData;
            },
            
            // Get the snake grid
            Stephino_Rpg_Utils_Math::getSnakeGrid(
                $configCity->getCityWidth(),
                $configCity->getCityHeight()
            )
        );
            
        // All done
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                Stephino_Rpg_Renderer_Ajax_Action_City::CELL_CONFIG_CITY_BKG => Stephino_Rpg_Utils_Media::getClosestBackgroundId(
                    Stephino_Rpg_Config_Cities::KEY,
                    $configCity->getId(),
                    $cityLevel,
                    Stephino_Rpg_Renderer_Ajax_Action_City::IMAGE_FULL,
                    Stephino_Rpg_Utils_Media::EXT_JPG
                ),
                self::RESULT_CITY_CONFIG => array(
                    self::CELL_CONFIG_ID   => $configCity->getId(),
                    self::CELL_CONFIG_NAME => $configCity->getName(),
                ),
                self::RESULT_GRID => $grid
            ),
            $cityId,
            $cityId
        );
    }
}

/*EOF*/