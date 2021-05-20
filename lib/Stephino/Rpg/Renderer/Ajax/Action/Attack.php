<?php

/**
 * Stephino_Rpg_Renderer_Ajax_Action_Attack
 * 
 * @title      Action::Attack
 * @desc       Attack actions
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Renderer_Ajax_Action_Attack extends Stephino_Rpg_Renderer_Ajax_Action {

    // Request keys
    const REQUEST_ATT_CITY_ID = 'attCityId';
    const REQUEST_DEF_CITY_ID = 'defCityId';
    const REQUEST_ARMY        = 'army';
    
    /**
     * Start an attack
     * 
     * @param array $data Data containing <ul>
     * <li><b>attCityId</b> (int) Attacking City ID</li>
     * <li><b>defCityId</b> (int) Defending City ID</li>
     * <li><b>army</b> (int)Army details</li>
     * </ul>
     * @return array
     * @throws Exception
     */
    public static function ajaxStart($data) {
        // Prepare the attacking city ID
        $attackerCityId = isset($data[self::REQUEST_ATT_CITY_ID]) ? intval($data[self::REQUEST_ATT_CITY_ID]) : null;
        $defenderCityId = isset($data[self::REQUEST_DEF_CITY_ID]) ? intval($data[self::REQUEST_DEF_CITY_ID]) : null;
        $army = isset($data[self::REQUEST_ARMY]) ? $data[self::REQUEST_ARMY] : null;
        
        // Must send attacks from our cities only
        self::getCityInfo($attackerCityId);
        
        // Create the attack
        $result = Stephino_Rpg_Db::get()->modelConvoys()->createAttack(
            $attackerCityId, 
            $defenderCityId, 
            $army
        );
        
        return Stephino_Rpg_Renderer_Ajax::wrap($result);
    }
}

/*EOF*/