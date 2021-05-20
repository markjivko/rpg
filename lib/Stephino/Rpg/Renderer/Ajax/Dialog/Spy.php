<?php

/**
 * ThemeWarlock - Stephino_Rpg_Renderer_Ajax_Dialog_Spy
 * 
 * @title      Dialog::Spy
 * @desc       Spy dialogs
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    ThemeWarlock
 * @since      TW 1.0
 */
class Stephino_Rpg_Renderer_Ajax_Dialog_Spy extends Stephino_Rpg_Renderer_Ajax_Dialog {
    
    // Dialog templates
    const TEMPLATE_REVIEW = 'spy/spy-review';
    
    // Request keys
    const REQUEST_TO_CITY_ID   = 'toCityId';
    const REQUEST_FROM_CITY_ID = 'fromCityId';
    
    /**
     * Send a spy dialog
     * 
     * @param array $data Empty array
     */
    public static function ajaxReview($data) {
        // Prepare the destination city ID
        $destinationCityId = isset($data[self::REQUEST_TO_CITY_ID]) ? intval($data[self::REQUEST_TO_CITY_ID]) : null;
        
        // Get the destination city information
        if (null === $destinationCityInfo = Stephino_Rpg_Db::get()->tableCities()->getById($destinationCityId)) {
            throw new Exception(__('Destination does not exist', 'stephino-rpg'));
        }
        
        // Prepare the spies list; nothing to exclude
        $spiesList = Stephino_Rpg_Renderer_Ajax_Action::getAllEntities(
            $destinationCityId, 
            array(
                Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_SPY
            ),
            true
        );
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_REVIEW);
        
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Send a spy', 'stephino-rpg'),
            )
        );
    }
}

/*EOF*/