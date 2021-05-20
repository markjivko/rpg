<?php

/**
 * ThemeWarlock - Stephino_Rpg_Renderer_Ajax_Dialog_Island
 * 
 * @title      Dialog::Island
 * @desc       Island dialogs
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    ThemeWarlock
 * @since      TW 1.0
 */
class Stephino_Rpg_Renderer_Ajax_Dialog_Island extends Stephino_Rpg_Renderer_Ajax_Dialog {
    
    // Dialog templates
    const TEMPLATE_INFO              = 'island/island-info';
    const TEMPLATE_UPGRADE           = 'island/island-upgrade';
    const TEMPLATE_COLONIZE_PREPARE  = 'island/island-colonize-prepare';
    const TEMPLATE_COLONIZE_REVIEW   = 'island/island-colonize-review';
    
    // Request keys
    const REQUEST_ISLAND_ID   = 'islandId';
    const REQUEST_ISLAND_SLOT = 'islandSlot';
    
    /**
     * Show the Island Statue dialog
     * 
     * @param array $data Data containing <ul>
     * <li><b>islandId</b> (int) Island ID</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxInfo($data) {
        // Prepare the island ID
        $islandId = isset($data[self::REQUEST_ISLAND_ID]) ? intval($data[self::REQUEST_ISLAND_ID]) : null;
        
        // Get the island information
        list(
            $islandData,
            $islandConfig,
            $islandStatueConfig,
            $colonizersList,
            $costData,
            $productionData,
            $affordList
        ) = Stephino_Rpg_Renderer_Ajax_Action::getIslandInfo($islandId);
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_INFO);
        
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => $islandData[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_NAME],
            )
        );
    }
    
    /**
     * Upgrade an island statue
     * 
     * @param array $data Data containing <ul>
     * <li><b>islandId</b> (int) Island ID</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxUpgrade($data) {
        // Prepare the island ID
        $islandId = isset($data[self::REQUEST_ISLAND_ID]) ? intval($data[self::REQUEST_ISLAND_ID]) : null;
        
        // Get the island information
        list(
            $islandData,
            $islandConfig,
            $islandStatueConfig,
            $colonizersList,
            $costData,
            $productionData,
            $affordList
        ) = Stephino_Rpg_Renderer_Ajax_Action::getIslandInfo($islandId);
        
        // Data not retrieved from time-lapse
        if (!isset($islandData[Stephino_Rpg_Db_Table_Users::COL_ID])) {
            throw new Exception(__('Upgrade not allowed', 'stephino-rpg'));
        }
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_UPGRADE);
        
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Upgrade', 'stephino-rpg'),
            )
        );
    }
    
    /**
     * Show the dialog for a vacant slot
     * 
     * @param array $data Data containing <ul>
     * <li><b>islandId</b> (int) Island ID</li>
     * <li><b>islandSlot</b> (int) Island Slot</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxColonizePrepare($data) {
        Stephino_Rpg_Renderer_Ajax::setModalSize(Stephino_Rpg_Renderer_Ajax::MODAL_SIZE_SMALL);
        
        // Prepare the island ID
        $islandId = isset($data[self::REQUEST_ISLAND_ID]) ? intval($data[self::REQUEST_ISLAND_ID]) : null;
        $islandSlot = isset($data[self::REQUEST_ISLAND_SLOT]) ? intval($data[self::REQUEST_ISLAND_SLOT]) : null;
        
        // Get the island information
        list(
            $islandData,
            $islandConfig,
            $islandStatueConfig,
            $colonizersList
        ) = Stephino_Rpg_Renderer_Ajax_Action::getIslandInfo($islandId);
        
        // Get the city index
        if (null === $cityIndex = Stephino_Rpg_Db::get()->modelIslands()->getCityIndex($islandConfig, $islandSlot)) {
            throw new Exception(__('Invalid lot', 'stephino-rpg'));
        }
        
        // This slot was occupied
        if (null !== $cityData = Stephino_Rpg_Db::get()->tableCities()->getByIslandAndIndex($islandId, $cityIndex)) {
            // Show the city dialog instead
            return Stephino_Rpg_Renderer_Ajax_Dialog_City::ajaxInfo(array(
                self::REQUEST_COMMON_ARGS => array($cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]),
            ));
        }
        
        // Prepare the city icon bkg URL
        $islandIconUrl = Stephino_Rpg_Utils_Media::getCommonBackgroundUrl(
            Stephino_Rpg_Config_Cities::KEY,
            Stephino_Rpg_Utils_Media::IMAGE_512_VACANT
        );
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_COLONIZE_PREPARE);
        
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Vacant slot', 'stephino-rpg'),
            )
        );
    }
    
    /**
     * Show the colonizer dialog
     * 
     * @param array $data Data containing <ul>
     * <li><b>islandId</b> (int) Island ID</li>
     * <li><b>islandSlot</b> (int) Island Slot</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxColonizeReview($data) {
        // Prepare the island ID
        $islandId = isset($data[self::REQUEST_ISLAND_ID]) ? intval($data[self::REQUEST_ISLAND_ID]) : null;
        $islandSlot = isset($data[self::REQUEST_ISLAND_SLOT]) ? intval($data[self::REQUEST_ISLAND_SLOT]) : null;
        
        // Get the island information
        list(
            $islandData,
            $islandConfig,
            $islandStatueConfig,
            $colonizersList
        ) = Stephino_Rpg_Renderer_Ajax_Action::getIslandInfo($islandId);
        
        // Get the city index
        if (null === $cityIndex = Stephino_Rpg_Db::get()->modelIslands()->getCityIndex($islandConfig, $islandSlot)) {
            throw new Exception(__('Invalid slot', 'stephino-rpg'));
        }
        
        // This slot was occupied
        if (null !== $cityData = Stephino_Rpg_Db::get()->tableCities()->getByIslandAndIndex($islandId, $cityIndex)) {
            // Show the city dialog instead
            return Stephino_Rpg_Renderer_Ajax_Dialog_City::ajaxInfo(array(
                self::REQUEST_CITY_ID => $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID],
            ));
        }
        
        // Get colonization costs
        $costData = Stephino_Rpg_Renderer_Ajax_Action::getCostData(
            $islandConfig,
            Stephino_Rpg_Renderer_Ajax_Action::getCitiesCount()
        );
        
        // Get the colonization time
        $colonizationTime = Stephino_Rpg_Db::get()->modelIslands()->getColonizationTime(
            $islandConfig,
            Stephino_Rpg_Renderer_Ajax_Action::getCitiesCount()
        );
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_COLONIZE_REVIEW);
        
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Colonize', 'stephino-rpg'),
            )
        );
    }
}

/*EOF*/