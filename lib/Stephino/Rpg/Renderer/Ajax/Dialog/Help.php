<?php

/**
 * ThemeWarlock - Stephino_Rpg_Renderer_Ajax_Dialog_Help
 * 
 * @title      Dialog::Help
 * @desc       Help dialogs
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    ThemeWarlock
 * @since      TW 1.0
 */
class Stephino_Rpg_Renderer_Ajax_Dialog_Help extends Stephino_Rpg_Renderer_Ajax_Dialog {
    
    // Dialog templates
    const TEMPLATE_MAIN                  = 'help/help';
    const TEMPLATE_MAIN_PREFIX           = 'help/help-content';
    const TEMPLATE_FRAGMENT_DESCRIPTION  = 'help/fragment/help-fragment-description';
    const TEMPLATE_FRAGMENT_COSTS        = 'help/fragment/help-fragment-costs';
    const TEMPLATE_FRAGMENT_POLY         = 'help/fragment/help-fragment-poly';
    const TEMPLATE_FRAGMENT_ABUNDANCE    = 'help/fragment/help-fragment-abundance';
    const TEMPLATE_FRAGMENT_PRODUCTION   = 'help/fragment/help-fragment-production';
    const TEMPLATE_FRAGMENT_REQUIREMENTS = 'help/fragment/help-fragment-requirements';
    const TEMPLATE_FRAGMENT_DISCOUNTS    = 'help/fragment/help-fragment-discounts';
    const TEMPLATE_FRAGMENT_UNLOCKS      = 'help/fragment/help-fragment-unlocks';
    
    // Request keys
    const REQUEST_ITEM_TYPE = 'itemType';
    const REQUEST_ITEM_ID   = 'itemId';
    
    // Core sections (to be highlighted with ajax calls)
    const CORE_SECTION_RULES        = 'rules';
    const CORE_SECTION_SCORE        = 'score';
    const CORE_SECTION_ROBOTS       = 'robots';
    const CORE_SECTION_GAME_ARENA   = 'game-arena';
    const CORE_SECTION_CONSOLE      = 'console';
    const CORE_SECTION_GAME_RES     = 'game-res';
    const CORE_SECTION_CITY_RES     = 'city-res';
    const CORE_SECTION_CITY_METRICS = 'city-metrics';
    
    /**
     * Show the help menu
     * 
     * @param array $data Data containing <ul>
     * <li><b>itemType</b> (string) Item type</li>
     * <li><b>itemId</b> (string|int) Item ID</li>
     * </ul>
     */
    public static function ajaxMenu($data) {
        // Get the item type and ID
        $itemType = isset($data[self::REQUEST_ITEM_TYPE]) ? $data[self::REQUEST_ITEM_TYPE] : null;
        $itemId = isset($data[self::REQUEST_ITEM_ID]) ? $data[self::REQUEST_ITEM_ID] : null;
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_MAIN);
        
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Codex', 'stephino-rpg'),
            )
        );
    }
    
    /**
     * Get the help information for a specific item
     * 
     * @param array $data Data containing <ul>
     * <li><b>itemType</b> (string) Item type</li>
     * <li><b>itemId</b> (string|int) Item ID</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxItem($data) {
        Stephino_Rpg_Renderer_Ajax::setModalSize(Stephino_Rpg_Renderer_Ajax::MODAL_SIZE_LARGE);
        
        // Get the item type and ID
        $itemType = isset($data[self::REQUEST_ITEM_TYPE]) ? $data[self::REQUEST_ITEM_TYPE] : null;
        $itemId = isset($data[self::REQUEST_ITEM_ID]) ? trim($data[self::REQUEST_ITEM_ID]) : null;
        
        // Validate the type
        $allConfigs = Stephino_Rpg_Config::get()->all();
        if (!isset($allConfigs[$itemType]) 
            || (!$allConfigs[$itemType] instanceOf Stephino_Rpg_Config_Item_Collection
                && !$allConfigs[$itemType] instanceOf Stephino_Rpg_Config_Core)
        ) {
            throw new Exception(__('Invalid help item type', 'stephino-rpg'));
        }
        
        // Validate the ID
        if ($allConfigs[$itemType] instanceOf Stephino_Rpg_Config_Core) {
            $configObject = $allConfigs[$itemType];
        } else {
            $configObject = $allConfigs[$itemType]->getById(abs((int) $itemId));
        }
        if (null === $configObject) {
            throw new Exception(__('Invalid help item ID', 'stephino-rpg'));
        }
        
        require self::dialogTemplatePath(self::TEMPLATE_MAIN_PREFIX . '-' . $itemType);
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Item', 'stephino-rpg') . ' ' . $itemType . ' #' . $itemId,
            )
        );
    }
}

/*EOF*/