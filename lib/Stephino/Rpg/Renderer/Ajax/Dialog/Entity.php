<?php

/**
 * ThemeWarlock - Stephino_Rpg_Renderer_Ajax_Dialog_Entity
 * 
 * @title      Dialog::Entity
 * @desc       Entity dialogs
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    ThemeWarlock
 * @since      TW 1.0
 */
class Stephino_Rpg_Renderer_Ajax_Dialog_Entity extends Stephino_Rpg_Renderer_Ajax_Dialog {

    // Dialog templates
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
     * <li><b>entityKey</b> (string) Entity Type</li>
     * <li><b>entityConfigId</b> (int) Entity Configuration ID</li>
     * <li><b>entityQueueAction</b> (string) One of the self::QUEUE_ACTIONS</li>
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
                $dialogTitle = __('Cancel', 'stephino-rpg');
                break;
        }
        
        // Show the dialog
        require self::dialogTemplatePath($dialogTemplate);
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => $dialogTitle  . ': ' . $entityConfig->getName(),
            ),
            $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]
        );
    }
    
}

/* EOF */