<?php

/**
 * ThemeWarlock - Stephino_Rpg_Renderer_Ajax_Dialog_Attack
 * 
 * @title      Dialog::Attack
 * @desc       Attack dialogs
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    ThemeWarlock
 * @since      TW 1.0
 */
class Stephino_Rpg_Renderer_Ajax_Dialog_Attack extends Stephino_Rpg_Renderer_Ajax_Dialog {
    
    // Dialog templates
    const TEMPLATE_PREPARE = 'attack/attack-prepare';
    const TEMPLATE_REVIEW  = 'attack/attack-review';
    
    // Request keys
    const REQUEST_ATT_CITY_ID = 'attCityId';
    const REQUEST_DEF_CITY_ID = 'defCityId';
    const REQUEST_ARMY        = 'army';
    
    /**
     * Show the attack preparation dialog
     * 
     * @param array $data Data containing <ul>
     * <li><b>self::REQUEST_DEF_CITY_ID</b> (int) Defending City ID</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxPrepare($data) {
        // Prepare the defending city ID
        $defenderCityId = isset($data[self::REQUEST_DEF_CITY_ID]) ? intval($data[self::REQUEST_DEF_CITY_ID]) : null;
        
        // Get the defending city information
        if (null === $defenderCityInfo = Stephino_Rpg_Db::get()->tableCities()->getById($defenderCityId)) {
            throw new Exception(
                sprintf(
                    __('Not found (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigCityName()
                )
            );
        }
        
        // Find all entities that can form an army (no special abilities)
        $attackerCities = Stephino_Rpg_Renderer_Ajax_Action::getAllEntities(
            null,
            array(
                Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_TRANSPORTER,
                Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_SPY,
                Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_COLONIZER
            ),
            false
        );
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_PREPARE);
        
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Prepare army', 'stephino-rpg'),
            )
        );
    }
    
    /**
     * Show the attack preparation dialog
     * 
     * @param array $data Data containing <ul>
     * <li><b>attCityId</b> (int) Attacking City ID</li>
     * <li><b>defCityId</b> (int) Defending City ID</li>
     * <li><b>army</b> (int)Army details</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxReview($data) {
        // Prepare the attacking city ID
        $attackerCityId = isset($data[self::REQUEST_ATT_CITY_ID]) ? intval($data[self::REQUEST_ATT_CITY_ID]) : null;
        $defenderCityId = isset($data[self::REQUEST_DEF_CITY_ID]) ? intval($data[self::REQUEST_DEF_CITY_ID]) : null;
        $army = isset($data[self::REQUEST_ARMY]) ? $data[self::REQUEST_ARMY] : null;
        
        // Invalid attacker information
        $attackerCityData = Stephino_Rpg_Renderer_Ajax_Action::getCityInfo($attackerCityId);
        
        // Invalid defender information
        if (null === $defenderCityData = Stephino_Rpg_Db::get()->tableCities()->getById($defenderCityId)) {
            throw new Exception(
                sprintf(
                    __('Not found (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigCityName()
                )
            );
        }
        
        // Get the payload
        $attackerPayload = Stephino_Rpg_Db::get()->modelConvoys()->payloadFromEntities(
            $army, 
            Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_Entities::KEY)->getData(), 
            $attackerCityId
        );
        
        // Invalid payload
        if (!count($attackerPayload)) {
            throw new Exception(__('Your army has no troops', 'stephino-rpg'));
        }
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_REVIEW);
        
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Confirm attack', 'stephino-rpg'),
            )
        );
    }
}

/*EOF*/