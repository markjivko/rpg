<?php

/**
 * ThemeWarlock - Stephino_Rpg_Renderer_Ajax_Dialog_Entity
 * 
 * @title      Dialog::Entity
 * @desc       Entity dialogs
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    ThemeWarlock
 * @since      TW 1.0
 */
class Stephino_Rpg_Renderer_Ajax_Dialog_Entity extends Stephino_Rpg_Renderer_Ajax_Dialog {

    // Dialog templates
    const TEMPLATE_LIST    = 'entity/entity-list';
    const TEMPLATE_RECRUIT = 'entity/entity-recruit';
    const TEMPLATE_DISBAND = 'entity/entity-disband';
    const TEMPLATE_DEQUEUE = 'entity/entity-dequeue';
    
    // Request keys
    const REQUEST_ENTITY_KEY          = 'entityKey';
    const REQUEST_ENTITY_CONFIG_ID    = 'entityConfigId';
    const REQUEST_ENTITY_QUEUE_ACTION = 'entityQueueAction';
    
    // Queue actions
    const QUEUE_ACTION_RECRUIT = 'r';
    const QUEUE_ACTION_DISBAND = 'd';
    const QUEUE_ACTION_DEQUEUE = 'q';
    
    // Allowed queue actions
    const QUEUE_ACTIONS = array(
        self::QUEUE_ACTION_RECRUIT,
        self::QUEUE_ACTION_DISBAND,
        self::QUEUE_ACTION_DEQUEUE,
    );
    
    /**
     * Entity queue
     * 
     * @param array $data Data containing <ul>
     * <li><b>cityId</b> (int) City ID</li>
     * <li><b>buildingConfigId</b> (int) Building Configuration ID</li>
     * <li><b>entityKey</b> (string) Entity Type</li>
     * <li><b>entityConfigId</b> (int) Entity Configuration ID</li>
     * </ul>
     * @return array Array of building name and extra building information
     * @throws Exception
     */
    public static function ajaxQueue($data) {
        // Get the entity key
        $entityKey = isset($data[self::REQUEST_ENTITY_KEY]) ? $data[self::REQUEST_ENTITY_KEY] : null;
        
        // Get the entity information
        /* @var $entityConfig Stephino_Rpg_Config_Unit|Stephino_Rpg_Config_Ship */
        list(
            $entityData, 
            $entityConfig, 
            $cityData, 
            $buildingData, 
            $queueData, 
            $costData,
            $productionData,
            $affordList
        ) = Stephino_Rpg_Renderer_Ajax_Action::getEntityInfo(
            isset($data[self::REQUEST_CITY_ID]) ? intval($data[self::REQUEST_CITY_ID]) : null, 
            Stephino_Rpg_Renderer_Ajax_Action_Entity::getEntityType($entityKey), 
            isset($data[self::REQUEST_ENTITY_CONFIG_ID]) ? intval($data[self::REQUEST_ENTITY_CONFIG_ID]) : null
        );
        
        // Get the queue action
        $queueAction = isset($data[self::REQUEST_ENTITY_QUEUE_ACTION]) ? trim($data[self::REQUEST_ENTITY_QUEUE_ACTION]) : null;
        if (!in_array($queueAction, self::QUEUE_ACTIONS)) {
            $queueAction = self::QUEUE_ACTION_RECRUIT;
        }
        
        // Prepare the template and title
        $dialogTemplate = null;
        $dialogTitle = null;
        switch ($queueAction) {
            case self::QUEUE_ACTION_RECRUIT:
                $dialogTemplate = self::TEMPLATE_RECRUIT;
                $dialogTitle = __('Recruit', 'stephino-rpg');
                
                // Get the Unit/Ship requirements
                list($requirements, $requirementsMet) = Stephino_Rpg_Renderer_Ajax_Action::getRequirements(
                    $entityConfig,
                    $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]
                );

                // Prepare the recruitment time in seconds
                $costTime = Stephino_Rpg_Db::get()->modelEntities()->getRecruitTime(
                    $entityConfig, 
                    1, 
                    $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL]
                );
                break;
            
            case self::QUEUE_ACTION_DISBAND:
                $dialogTemplate = self::TEMPLATE_DISBAND;
                $dialogTitle = __('Disband', 'stephino-rpg');
                
                // Disband not allowed
                if (!$entityConfig->getDisbandable()) {
                    throw new Exception(__('Cannot disband this entity', 'stephino-rpg'));
                }
                break;
                
            case self::QUEUE_ACTION_DEQUEUE:
                $dialogTemplate = self::TEMPLATE_DEQUEUE;
                $dialogTitle = __('Dequeue', 'stephino-rpg');
                break;
        }
        
        // Show the dialog
        require self::dialogTemplatePath($dialogTemplate);
        
        // All done
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => $dialogTitle  . ': ' . $entityConfig->getName(),
            ),
            $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]
        );
    }
    
    /**
     * List all entities garrisoned in a city
     * 
     * @param array $data Data containing <ul>
     * <li><b>cityId</b> (int) City ID</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxList($data) {
        // Prepare the entities list
        $cityEntities = array();
        
        // Store the city ID
        $cityId = isset($data[self::REQUEST_CITY_ID]) ? intval($data[self::REQUEST_CITY_ID]) : null;
        
        // Get all city entities
        $allCityEntities = Stephino_Rpg_Renderer_Ajax_Action::getCityEntities($cityId, null, false);
        
        // Entities found
        if (null !== $allCityEntities) {
            list($cityData, $cityEntities) = $allCityEntities;
        } else {
            $cityData = Stephino_Rpg_Renderer_Ajax_Action::getCityInfo($cityId);
        }
        
        // Prepare the military buildings
        $militaryBuildings = array();
        if (is_array($cityData)) {
            foreach (Stephino_Rpg_Config::get()->buildings()->getAll() as $buildingConfig) {
                if ($buildingConfig->getAttackPoints() > 0 || $buildingConfig->getDefensePoints() > 0) {
                    list($buildingData) = Stephino_Rpg_Renderer_Ajax_Action::getBuildingInfo(
                        $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID], 
                        $buildingConfig->getId()
                    );
                    
                    // Building constructed
                    if (is_array($buildingData) && $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL] > 0) {
                        // Get the production factor
                        $prodFactor = Stephino_Rpg_Renderer_Ajax_Action::getBuildingProdFactor(
                            Stephino_Rpg_Config::get()->cities()->getById($cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_CONFIG_ID]),
                            $buildingConfig, 
                            $buildingData
                        );

                        // Store the building details
                        $militaryBuildings[$buildingConfig->getId()] = array(
                            Stephino_Rpg_Renderer_Ajax::RESULT_MIL_ATTACK => Stephino_Rpg_Utils_Config::getPolyValue(
                                $buildingConfig->getAttackPointsPolynomial(), 
                                $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL], 
                                $buildingConfig->getAttackPoints()
                            ) * $prodFactor,
                            Stephino_Rpg_Renderer_Ajax::RESULT_MIL_DEFENSE => Stephino_Rpg_Utils_Config::getPolyValue(
                                $buildingConfig->getDefensePointsPolynomial(), 
                                $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL], 
                                $buildingConfig->getDefensePoints()
                            ) * $prodFactor,
                            Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL => $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL],
                        );
                    }
                }
            }
        }
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_LIST);
        
        // All done
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Garrison', 'stephino-rpg'),
            ),
            $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]
        );
    }
}

/* EOF */