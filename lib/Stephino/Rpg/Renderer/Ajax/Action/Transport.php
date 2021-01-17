<?php

/**
 * Stephino_Rpg_Renderer_Ajax_Action_Transport
 * 
 * @title      Action::Transport
 * @desc       Transport actions
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Renderer_Ajax_Action_Transport extends Stephino_Rpg_Renderer_Ajax_Action {

    // Request keys
    const REQUEST_TO_CITY_ID          = 'toCityId';
    const REQUEST_FROM_CITY_ID        = 'fromCityId';
    const REQUEST_TRANSPORTERS        = 'transporters';
    const REQUEST_TRANSPORT_ENTITIES  = 'transportEntities';
    const REQUEST_TRANSPORT_RESOURCES = 'transportResources';
    
    /**
     * Start a transport
     * 
     * @param array $data Data containing <ul>
     *     <li><b>fromCityId</b> (int) Source City ID</li>
     *     <li><b>toCityId</b> (int) Destination City ID</li>
     *     <li><b>transporters</b> (array) Transporters</li>
     *     <li><b>transportEntities</b> (array) Transport Entities</li>
     *     <li><b>transportResources</b> (array) Transport Resources</li>
     * </ul>
     * @return array
     * @throws Exception
     */
    public static function ajaxStart($data) {
        // Destination city info
        $destinationCityId = isset($data[self::REQUEST_TO_CITY_ID]) ? intval($data[self::REQUEST_TO_CITY_ID]) : null;
        if (null === $destinationCityInfo = Stephino_Rpg_Db::get()->tableCities()->getById($destinationCityId)) {
            throw new Exception(__('Destination does not exist', 'stephino-rpg'));
        }
        
        // Origin city info
        $originCityId = isset($data[self::REQUEST_FROM_CITY_ID]) ? intval($data[self::REQUEST_FROM_CITY_ID]) : null;

        // Prepare the transporters list
        $transporterList = self::getCityEntities(
            $originCityId, 
            array(
                Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_TRANSPORTER
            )
        );
        
        // The origin city has no transporters
        if (!is_array($transporterList)) {
            throw new Exception(__('No transporters available', 'stephino-rpg'));
        }
        
        // Validate the source and destination
        if ($destinationCityId == $originCityId) {
            throw new Exception(__('Origin and destination must differ', 'stephino-rpg'));
        }
        
        // Get the information and transporters
        list($originCityInfo, $originCityTransporters) = $transporterList;
        
        // Prepare the transporters payload
        $transportTransportersInfo = Stephino_Rpg_Db::get()->modelConvoys()->payloadFromEntities(
            isset($data[self::REQUEST_TRANSPORTERS]) ? $data[self::REQUEST_TRANSPORTERS] : array(),
            Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_Entities::KEY)->getData(),
            $originCityId
        );
        
        // Validate the transporters
        foreach (array_keys($transportTransportersInfo) as $trId) {
            if (!isset($originCityTransporters[$trId])) {
                throw new Exception(__('Entity cannot perform transport tasks', 'stephino-rpg'));
            }
        }
        
        // No transporterts
        if (!count($transportTransportersInfo)) {
            throw new Exception(__('No transporters available', 'stephino-rpg'));
        }
        
        // Get the entities payload
        $transportEntitiesInfo = Stephino_Rpg_Db::get()->modelConvoys()->payloadFromEntities(
            isset($data[self::REQUEST_TRANSPORT_ENTITIES]) ? $data[self::REQUEST_TRANSPORT_ENTITIES] : array(), 
            Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Support_Entities::KEY)->getData(), 
            $originCityId
        );
        
        // Get the resources payload
        $transportResourcesInfo = Stephino_Rpg_Db::get()->modelConvoys()->payloadFromResources(
            isset($data[self::REQUEST_TRANSPORT_RESOURCES]) ? $data[self::REQUEST_TRANSPORT_RESOURCES] : array(),
            Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData(),
            $originCityId
        );
        
        // Nothing to send
        if (!count($transportEntitiesInfo) && !count($transportResourcesInfo)) {
            throw new Exception(__('The transport payload cannot be empty', 'stephino-rpg'));
        }
        
        // No point in sending gold to self
        if ($originCityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_USER_ID] == $destinationCityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_USER_ID]) {
            unset($transportResourcesInfo[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD]);
        }

        // Get the maximum payload
        $payloadMaximum = 0;
        foreach ($transportTransportersInfo as $trId => $trData) {
            /* @var $transporterConfig Stephino_Rpg_Config_Ship */
            list(, $transporterConfig) = $originCityTransporters[$trId];
            $payloadMaximum += $transporterConfig->getAbilityTransportCapacity() * intval($trData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT]);
        }
        
        // Get the current payload
        $payloadCurrent = 0;
        
        // Go through the entities
        foreach ($transportEntitiesInfo as $teDbRow) {
            /* @var $entityConfig Stephino_Rpg_Config_Unit|Stephino_Rpg_Config_Ship */
            $entityConfig = Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT == $teDbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE]
                ? Stephino_Rpg_Config::get()->units()->getById($teDbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID])
                : Stephino_Rpg_Config::get()->ships()->getById($teDbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]);
            $payloadCurrent += $entityConfig->getTransportMass() * intval($teDbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT]);
        }
        
        // Add the resources
        foreach ($transportResourcesInfo as $resourceAmount) {
            $payloadCurrent += $resourceAmount;
        }
        
        // Over capacity
        if ($payloadCurrent > $payloadMaximum) {
            throw new Exception(__('Over capacity', 'stephino-rpg'));
        }
        
        // Reserve resources for transport
        Stephino_Rpg_Renderer_Ajax_Action::spend(
            Stephino_Rpg_Renderer_Ajax_Action::getResourceData($transportResourcesInfo),
            $originCityInfo
        );
        
        // Create the convoy
        $result = Stephino_Rpg_Db::get()->modelConvoys()->createTransport(
            $originCityId, 
            $destinationCityId, 
            $transportTransportersInfo,
            $transportEntitiesInfo,
            $transportResourcesInfo
        );
        
        // All done
        return Stephino_Rpg_Renderer_Ajax::wrap($result);
    }
}

/*EOF*/