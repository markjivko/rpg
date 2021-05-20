<?php
/**
 * Stephino_Rpg_Renderer_Ajax_Action_Island
 * 
 * @title     Action::Island
 * @desc      Island Actions
 * @copyright (c) 2021, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Renderer_Ajax_Action_Island extends Stephino_Rpg_Renderer_Ajax_Action {

    // Request keys
    const REQUEST_ISLAND_ID           = 'islandId';
    const REQUEST_ISLAND_SLOT         = 'islandSlot';
    const REQUEST_COLONIZER_CITY_ID   = 'colonizerCityId';
    const REQUEST_COLONIZER_ENTITY_ID = 'colonizerEntityId';
    
    /**
     * Colonize a new city
     * 
     * @param array $data Data containing <ul>
     * <li><b>islandId</b> (int) Island ID</li>
     * <li><b>islandSlot</b> (int) Island Slot</li>
     * <li><b>colonizerCityId</b> (int) Colonizer City ID</li>
     * <li><b>colonizerEntityId</b> (int) Colonizer Entity ID</li>
     * </ul>
     */
    public static function ajaxColonize($data) {
        // Prepare the island ID
        $islandId = isset($data[self::REQUEST_ISLAND_ID]) ? intval($data[self::REQUEST_ISLAND_ID]) : null;
        $islandSlot = isset($data[self::REQUEST_ISLAND_SLOT]) ? intval($data[self::REQUEST_ISLAND_SLOT]) : null;
        $colonizerCityId = isset($data[self::REQUEST_COLONIZER_CITY_ID]) ? intval($data[self::REQUEST_COLONIZER_CITY_ID]) : null;
        $colonizerEntityId = isset($data[self::REQUEST_COLONIZER_ENTITY_ID]) ? intval($data[self::REQUEST_COLONIZER_ENTITY_ID]) : null;
        
        // Get the island information
        list($islandData, $islandConfig) = self::getIslandInfo($islandId);
        
        // Get the city index
        $islandCityIndex = Stephino_Rpg_Db::get()->modelIslands()->getCityIndex($islandConfig, $islandSlot);
        
        // Invalid index
        if (null === $islandCityIndex) {
            throw new Exception(__('Invalid slot', 'stephino-rpg'));
        }
        
        // This slot was occupied
        if (null !== $cityData = Stephino_Rpg_Db::get()->tableCities()->getByIslandAndIndex($islandId, $islandCityIndex)) {
            throw new Exception(__('You can only colonize an empty slot', 'stephino-rpg'));
        }
        
        // Validate city belongs to us
        self::getCityInfo($colonizerCityId);
        
        // Get colonization costs
        $costData = self::getCostData(
            $islandConfig,
            self::getCitiesCount()
        );
        
        // Other convoy is active
        if (null !== Stephino_Rpg_Db::get()->tableConvoys()->getByIslandAndIndex($islandId, $islandCityIndex)) {
            throw new Exception(__('This slot was already claimed by another colonizer', 'stephino-rpg'));
        }
        
        // Spend the resources
        self::spend($costData);
        
        // Prepare the result
        $result = Stephino_Rpg_Db::get()->modelConvoys()->createColonizer(
            $colonizerCityId,
            $colonizerEntityId,
            $islandId, 
            $islandCityIndex,
            Stephino_Rpg_Db::get()->modelIslands()->getColonizationTime(
                $islandConfig,
                self::getCitiesCount()
            )
        );
        
        return Stephino_Rpg_Renderer_Ajax::wrap(
            $result
        );
    }
    
    /**
     * Upgrade the statue on an island
     * 
     * @param array $data Data containing <ul>
     * <li><b>islandId</b> (int) Island ID</li>
     * </ul>
     */
    public static function ajaxUpgrade($data) {
        // Prepare the island ID
        $islandId = isset($data[self::REQUEST_ISLAND_ID]) ? intval($data[self::REQUEST_ISLAND_ID]) : null;
        
        // Get the island information
        list(
            $islandData,
            $islandConfig,
            $islandStatueConfig,
            $colonizersList,
            $costData,
            $productionData,
            $affordList
        ) = self::getIslandInfo($islandId);
        
        // Data not retrieved from time-lapse
        if (!isset($islandData[Stephino_Rpg_Db_Table_Users::COL_ID])) {
            throw new Exception(
                __('Cannot upgrade this', 'stephino-rpg')
            );
        }
        
        // Validate cost
        if (count($affordList) && min($affordList) < 1) {
            throw new Exception(__('Not enough resources', 'stephino-rpg'));
        }
        
        // Spend the resources
        self::spend($costData);
        
        // Prepare the new level
        $islandStatueLevel = (int) $islandData[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_STATUE_LEVEL] + 1;
        
        // Prepare the result
        $result = Stephino_Rpg_Db::get()->modelIslands()->setStatueLevel(
            $islandId,
            $islandStatueLevel
        );
        
        return Stephino_Rpg_Renderer_Ajax::wrap(
            $result 
                ? $islandStatueLevel 
                : (int) $islandData[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_STATUE_LEVEL]
        );
    }
}

/* EOF */