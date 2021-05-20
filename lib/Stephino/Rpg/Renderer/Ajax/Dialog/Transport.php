<?php

/**
 * ThemeWarlock - Stephino_Rpg_Renderer_Ajax_Dialog_Transport
 * 
 * @title      Dialog::Transport
 * @desc       Transport dialogs
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    ThemeWarlock
 * @since      TW 1.0
 */
class Stephino_Rpg_Renderer_Ajax_Dialog_Transport extends Stephino_Rpg_Renderer_Ajax_Dialog {
    
    // Dialog templates
    const TEMPLATE_PREPARE = 'transport/transport-prepare';
    const TEMPLATE_REVIEW  = 'transport/transport-review';
    
    // Request keys
    const REQUEST_TO_CITY_ID          = 'toCityId';
    const REQUEST_FROM_CITY_ID        = 'fromCityId';
    const REQUEST_TRANSPORTERS        = 'transporters';
    const REQUEST_TRANSPORT_ENTITIES  = 'transportEntities';
    const REQUEST_TRANSPORT_RESOURCES = 'transportResources';
    
    /**
     * Show the transport preparation dialog
     * 
     * @param array $data Data containing <ul>
     * <li><b>commonArgs</b> array((int) City ID)</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxPrepare($data) {
        if (!is_array($commonArgs = isset($data[self::REQUEST_COMMON_ARGS]) ? $data[self::REQUEST_COMMON_ARGS] : array())) {
            $commonArgs = array();
        }
        
        // Prepare the destination city ID
        $destinationCityId = current($commonArgs);
        
        // Get the destination city information
        if (null === $destinationCityInfo = Stephino_Rpg_Db::get()->tableCities()->getById($destinationCityId)) {
            throw new Exception(__('Destination does not exist', 'stephino-rpg'));
        }
        
        // Prepare the transporters list
        $transporterList = Stephino_Rpg_Renderer_Ajax_Action::getAllEntities(
            $destinationCityId, 
            array(
                Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_TRANSPORTER
            ),
            true
        );
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_PREPARE);
        
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Prepare transport', 'stephino-rpg'),
            )
        );
    }
    
    /**
     * Show the transport review dialog
     * 
     * @param array $data Data containing <ul>
     *     <li><b>fromCityId</b> (int) Source City ID</li>
     *     <li><b>toCityId</b> (int) Destination City ID</li>
     *     <li><b>transporters</b> (array) Transporters</li>
     * </ul>
     */
    public static function ajaxReview($data) {
        Stephino_Rpg_Renderer_Ajax::setModalSize(Stephino_Rpg_Renderer_Ajax::MODAL_SIZE_LARGE);
        
        // Origin city info
        $originCityId = isset($data[self::REQUEST_FROM_CITY_ID]) ? intval($data[self::REQUEST_FROM_CITY_ID]) : null;
        if (null === $originCityInfo = Stephino_Rpg_Db::get()->tableCities()->getById($originCityId)) {
            throw new Exception(__('Origin does not exist', 'stephino-rpg'));
        }
        
        // Destination city info
        $destinationCityId = isset($data[self::REQUEST_TO_CITY_ID]) ? intval($data[self::REQUEST_TO_CITY_ID]) : null;
        if (null === $destinationCityInfo = Stephino_Rpg_Db::get()->tableCities()->getById($destinationCityId)) {
            throw new Exception(__('Destination does not exist', 'stephino-rpg'));
        }
        
        // Prepare the transporters payload
        $transporterPayload = Stephino_Rpg_Db::get()->modelConvoys()->payloadFromEntities(
            isset($data[self::REQUEST_TRANSPORTERS]) ? $data[self::REQUEST_TRANSPORTERS] : array(),
            Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_Entities::KEY)->getData(),
            $originCityId
        );
        
        // No transporters selected
        if (!count($transporterPayload)) {
            throw new Exception(__('You cannot send goods without transporters', 'stephino-rpg'));
        }
        
        // Get the total payload capacity
        $transporterCapacity = 0;
        foreach ($transporterPayload as $entityRow) {
            $transporterConfig = Stephino_Rpg_Config::get()->ships()->getById($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]);
            if (null !== $transporterConfig) {
                $transporterCapacity += $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT] * $transporterConfig->getAbilityTransportCapacity();
            }
        }
        
        // Exclude transporters from the cargo
        $allCityEntities = Stephino_Rpg_Renderer_Ajax_Action::getCityEntities(
            $originCityId, 
            array(
                Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_TRANSPORTER
            ),
            false
        );
        
        // Nothing found
        if (null === $allCityEntities) {
            throw new Exception(__('No entities in garrison', 'stephino-rpg'));
        }
        
        // Prepare the transferable resources
        $cityResources = Stephino_Rpg_Renderer_Ajax_Action::getResourceData();
        
        // No point in sending user resources to self
        if ($originCityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_USER_ID] == $destinationCityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_USER_ID]) {
            unset($cityResources[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD]);
            unset($cityResources[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH]);
            unset($cityResources[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM]);
        }
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_REVIEW);
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Review transport', 'stephino-rpg'),
            )
        );
    }
}

/*EOF*/