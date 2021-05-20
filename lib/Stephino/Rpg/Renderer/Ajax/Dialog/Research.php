<?php

/**
 * ThemeWarlock - Stephino_Rpg_Renderer_Ajax_Dialog_Research
 * 
 * @title      Dialog::Research
 * @desc       Research dialogs
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    ThemeWarlock
 * @since      TW 1.0
 */
class Stephino_Rpg_Renderer_Ajax_Dialog_Research extends Stephino_Rpg_Renderer_Ajax_Dialog {
    
    // Dialog templates
    const TEMPLATE_INFO = 'research/research-info';
    
    // Request keys
    const REQUEST_RESEARCH_AREA_CONFIG_ID  = 'researchAreaConfigId';
    const REQUEST_RESEARCH_FIELD_CONFIG_ID = 'researchFieldConfigId';
    
    /**
     * Show research area information
     * 
     * @param array $data Data containing 
     * <ul>
     *     <li><b>cityId</b> (int) City ID</li>
     *     <li><b>researchAreaConfigId</b> (int) Research area Configuration ID</li>
     *     <li><b>researchFieldConfigId</b> (int) Research field Configuration ID</li>
     * </ul>
     * @return array
     * @throws Exception
     */
    public static function ajaxInfo($data) {
        // Get the city ID
        $cityId = isset($data[self::REQUEST_CITY_ID]) ? intval($data[self::REQUEST_CITY_ID]) : 0;
        
        // Get the Research Area Configuration ID
        $researchAreaConfigId = isset($data[self::REQUEST_RESEARCH_AREA_CONFIG_ID]) ? intval($data[self::REQUEST_RESEARCH_AREA_CONFIG_ID]) : 0;
        $researchFieldConfigId = isset($data[self::REQUEST_RESEARCH_FIELD_CONFIG_ID]) ? intval($data[self::REQUEST_RESEARCH_FIELD_CONFIG_ID]) : 0;
        
        /* @var $researchAreaConfig Stephino_Rpg_Config_ResearchArea */
        /* @var $researchFieldConfigs Stephino_Rpg_Config_ResearchField */
        list(
            $researchAreaConfig, 
            $researchFieldConfigs, 
            $researchFieldData,
            $researchQueue,
            $researchCostData,
            $researchAffordList
        ) = Stephino_Rpg_Renderer_Ajax_Action::getResearchAreaInfo($researchAreaConfigId);
        
        // Requirements not met
        try {
            Stephino_Rpg_Renderer_Ajax_Action::getRequirements($researchAreaConfig, $cityId, true);
        } catch (Exception $exc) {
            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::warning($exc->getMessage());
            $researchAreaBuilding = $researchAreaConfig->getBuilding();
            
            // Redirect to the parent building
            return Stephino_Rpg_Renderer_Ajax_Dialog_Building::ajaxInfo(array(
                self::REQUEST_CITY_ID                                                  => $cityId,
                Stephino_Rpg_Renderer_Ajax_Dialog_Building::REQUEST_BUILDING_CONFIG_ID => null !== $researchAreaBuilding
                    ? $researchAreaBuilding->getId()
                    : 0
            ));
        }
        
        // Get the city data
        $cityData = Stephino_Rpg_Renderer_Ajax_Action::getCityInfo($cityId);
        
        // Time contraction
        $costTimeContraction = Stephino_Rpg_Db::get()->modelPremiumModifiers()->getTimeContraction(
            Stephino_Rpg_Db_Model_ResearchFields::NAME
        );
        
        // Prepare the research time cost
        $researchCostTime = array();
        foreach ($researchFieldConfigs as $rfId => $researchFieldConfig) {
            $researchCostTime[$rfId] = Stephino_Rpg_Db::get()->modelResearchFields()->getResearchTime(
                $researchFieldConfig,
                isset($researchFieldData[$rfId])
                && isset($researchFieldData[$rfId][Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_LEVEL]) 
                    ? $researchFieldData[$rfId][Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_LEVEL]
                    : 0
            );
        }
        
        // Initialize the production data
        $researchProductionData = array();
        foreach ($researchFieldConfigs as $researchFieldConfig) {
            $researchProductionData[$researchFieldConfig->getId()] = Stephino_Rpg_Renderer_Ajax_Action::getProductionData(
                $researchFieldConfig, 
                isset($researchFieldData[$researchFieldConfig->getId()])
                    ? (
                        $researchFieldConfig->getLevelsEnabled()
                            ? $researchFieldData[$researchFieldConfig->getId()][Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_LEVEL]
                            : $researchFieldData[$researchFieldConfig->getId()][Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_LEVEL] > 0
                    )
                    : 0,
                $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID]
            );
        }
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_INFO);
        
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => $researchAreaConfig->getName(),
            ),
            $cityId
        );
    }
}

/*EOF*/