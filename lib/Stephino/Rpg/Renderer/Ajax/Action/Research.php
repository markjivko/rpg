<?php

/**
 * Stephino_Rpg_Renderer_Ajax_Action_Research
 * 
 * @title      Action::Research
 * @desc       Research actions
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Renderer_Ajax_Action_Research extends Stephino_Rpg_Renderer_Ajax_Action {

    // Request keys
    const REQUEST_RESEARCH_AREA_CONFIG_ID  = 'researchAreaConfigId';
    const REQUEST_RESEARCH_FIELD_CONFIG_ID = 'researchFieldConfigId';
    const REQUEST_RESEARCH_FIELD_QUEUE     = 'researchFieldQueue';
    
    /**
     * Queue a research field
     * 
     * @param array $data Data containing <ul>
     * <li><b>cityId</b> (int) City ID</li>
     * <li><b>researchFieldConfigId</b> (int) Research Field Configuration ID</li>
     * <li><b>researchFieldQueue</b> (boolean) Queue/dequeue action</li>
     * </ul>
     */
    public static function ajaxQueue($data) {
        $result = null;
        
        // Get the city ID
        $cityId = isset($data[self::REQUEST_CITY_ID]) ? intval($data[self::REQUEST_CITY_ID]) : 0;
        
        // Get the research field configuration ID
        $researchFieldConfigId = isset($data[self::REQUEST_RESEARCH_FIELD_CONFIG_ID]) ? intval($data[self::REQUEST_RESEARCH_FIELD_CONFIG_ID]) : 0;
        
        // Queue/dequeue the research field
        $researchFieldQueue = isset($data[self::REQUEST_RESEARCH_FIELD_QUEUE]) ? !!$data[self::REQUEST_RESEARCH_FIELD_QUEUE] : true;
        
        // Get the research field configuration
        if (null === $researchFieldConfig = Stephino_Rpg_Config::get()->researchFields()->getById($researchFieldConfigId)) {
            throw new Exception(
                sprintf(
                    __('Invalid ID (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigResearchFieldName()
                )
            );
        }
        
        // Get the research area
        if (null === $researchFieldConfig->getResearchArea()) {
            throw new Exception(
                sprintf(
                    __('Invalid configuration (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigResearchFieldName()
                )
            );
        }
        
        /* @var $researchAreaConfig Stephino_Rpg_Config_ResearchArea */
        /* @var $researchFieldConfigs Stephino_Rpg_Config_ResearchField[] */
        list(
            $researchAreaConfig, 
            $researchFieldConfigs, 
            $researchFieldData,
            $researchQueue,
            $researchCostData,
            $researchAffordList
        ) = self::getResearchAreaInfo($researchFieldConfig->getResearchArea()->getId());
        
        // Queue action
        if ($researchFieldQueue) {
            if (isset($researchQueue[$researchFieldConfigId])) {
                throw new Exception(__('Research in progress', 'stephino-rpg'));
            }
        
            // Validate cost
            if (count($researchAffordList[$researchFieldConfigId]) && min($researchAffordList[$researchFieldConfigId]) < 1) {
                throw new Exception(__('Not enough resources', 'stephino-rpg'));
            }

            // Validate Research Area requirements, throwing an Exception on error
            self::getRequirements($researchAreaConfig, null, true);
        
            // Get the production
            $researchFieldProduction = self::getProductionData(
                $researchFieldConfig, 
                isset($researchFieldData[$researchFieldConfig->getId()])
                    ? (
                        $researchFieldConfig->getLevelsEnabled()
                            ? $researchFieldData[$researchFieldConfig->getId()][Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_LEVEL]
                            : $researchFieldData[$researchFieldConfig->getId()][Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_LEVEL] > 0
                    )
                    : 0
            );

            // No production defined, so stop at max unlocks
            if (!count($researchFieldProduction)) {
                // Get the maximum level at which this r.f. unlocks an item
                $researchFieldMaxLevel = Stephino_Rpg_Utils_Config::getUnlocksMaxLevel($researchFieldConfig);

                // Max level reached
                if (isset($researchFieldData[$researchFieldConfigId]) && $researchFieldData[$researchFieldConfigId][Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_LEVEL] >= $researchFieldMaxLevel) {
                    throw new Exception(__('Research maximum reached', 'stephino-rpg'));
                }
            }
        
            // Spend resources; also validates Research Field requirements
            self::spend(
                $researchCostData[$researchFieldConfigId], 
                null, 
                1, 
                $researchFieldConfigs[$researchFieldConfigId]
            );

            // Enqueue Research Field
            $result = Stephino_Rpg_Db::get()->modelQueues()->queueResearchField(
                Stephino_Rpg_TimeLapse::get()->userId(),
                $researchFieldConfigId
            );
        } else {
            if (!isset($researchQueue[$researchFieldConfigId])) {
                throw new Exception(
                    sprintf(
                        __('Not found (%s)', 'stephino-rpg'),
                        Stephino_Rpg_Config::get()->core()->getConfigResearchFieldName()
                    )
                );
            }
            
            // Dequeue Research Field
            $result = Stephino_Rpg_Db::get()->modelQueues()->queueResearchField(
                Stephino_Rpg_TimeLapse::get()->userId(),
                $researchFieldConfigId,
                false
            );
            
            // Refund resources
            self::spend(
                $researchCostData[$researchFieldConfigId], 
                null, 
                1, 
                $researchFieldConfigs[$researchFieldConfigId],
                true
            );
        }
        
        // Create the construction queue
        return Stephino_Rpg_Renderer_Ajax::wrap(
            $result,
            $cityId
        );
    }
    
}

/*EOF*/