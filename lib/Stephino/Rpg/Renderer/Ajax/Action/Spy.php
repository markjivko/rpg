<?php

/**
 * Stephino_Rpg_Renderer_Ajax_Action_Spy
 * 
 * @title      Action::Spy
 * @desc       Spy actions
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Renderer_Ajax_Action_Spy extends Stephino_Rpg_Renderer_Ajax_Action {

    // Request keys
    const REQUEST_TO_CITY_ID     = 'toCityId';
    const REQUEST_SPY_CITY_ID    = 'spyCityId';
    const REQUEST_SPY_ENTITY_ID  = 'spyEntityId';
    
    /**
     * Start a spy mission
     * 
     * @param array $data Data containing <ul>
     *     <li><b>fromCityId</b> (int) Source City ID</li>
     *     <li><b>toCityId</b> (int) Destination City ID</li>
     * </ul>
     * @return array
     * @throws Exception
     */
    public static function ajaxStart($data) {
        // Prepare the origin city ID
        $originCityId = isset($data[self::REQUEST_SPY_CITY_ID]) ? intval($data[self::REQUEST_SPY_CITY_ID]) : null;
        $originEntityId = isset($data[self::REQUEST_SPY_ENTITY_ID]) ? intval($data[self::REQUEST_SPY_ENTITY_ID]) : null;
        
        // Prepare the destination city ID
        $destinationCityId = isset($data[self::REQUEST_TO_CITY_ID]) ? intval($data[self::REQUEST_TO_CITY_ID]) : null;
        
        // Get the destination city information
        if (null === $destinationCityInfo = Stephino_Rpg_Db::get()->tableCities()->getById($destinationCityId)) {
            throw new Exception(__('Destination does not exist', 'stephino-rpg'));
        }
        
        // Prepare the spies list; nothing to exclude
        $spiesList = self::getAllEntities(
            $destinationCityId, 
            array(
                Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_SPY
            ),
            true
        );
        
        // City has no spies
        if (!isset($spiesList[$originCityId])) {
            throw new Exception(__('Spy not available', 'stephino-rpg'));
        }

        // This spy does not exist
        if (!isset($spiesList[$originCityId][1][$originEntityId])) {
            throw new Exception(__('Spy not initialized', 'stephino-rpg'));
        }
        
        // Get the spy information
        list($entityDbRow) = $spiesList[$originCityId][1][$originEntityId];
        
        // No more spies left
        if ($entityDbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT] < 1) {
            throw new Exception(__('Spy not available', 'stephino-rpg'));
        }
        
        // Create the convoy
        $result = Stephino_Rpg_Db::get()->modelConvoys()->createSpy(
            $originCityId, 
            $originEntityId,
            $destinationCityId
        );
        
        return Stephino_Rpg_Renderer_Ajax::wrap($result);
    }
}

/*EOF*/