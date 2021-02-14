<?php

/**
 * ThemeWarlock - Stephino_Rpg_Renderer_Ajax_Dialog_Settings
 * 
 * @title      Dialog::Settings
 * @desc       Settings dialogs
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    ThemeWarlock
 * @since      TW 1.0
 */
class Stephino_Rpg_Renderer_Ajax_Dialog_Settings extends Stephino_Rpg_Renderer_Ajax_Dialog {
    
    // Request keys
    const REQUEST_RES_KEY = 'resKey';
    
    // Dialog templates
    const TEMPLATE_INFO           = 'settings/settings-info';
    const TEMPLATE_RESOURCES      = 'settings/settings-resources';
    const TEMPLATE_ABOUT          = 'settings/settings-about';
    const TEMPLATE_ANNOUNCEMENT   = 'settings/settings-announcement';
    const TEMPLATE_LANGUAGE       = 'settings/settings-language';
    const TEMPLATE_DELETE_ACCOUNT = 'settings/settings-delete-account';
    
    /**
     * Show the resources dialog
     * 
     * @param array $data Data containing <ul>
     * <li><b>cityId</b> (int) Optional - Current City ID</li>
     * </ul>
     */
    public static function ajaxResources($data) {
        // Get current city ID
        $currentCityId = isset($data[self::REQUEST_CITY_ID]) ? intval($data[self::REQUEST_CITY_ID]) : null;
        $currentResKey = isset($data[self::REQUEST_RES_KEY]) ? trim($data[self::REQUEST_RES_KEY]) : null;
        
        // Prepare the city and global resources
        $resourcesCities = array();
        
        // Trading building created in these cities
        $tradingCities = array();
        $tradingBuilding = Stephino_Rpg_Config::get()->core()->getMarketBuilding();
        
        // Valid building data found
        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $dbRow) {
                // Get the city ID
                $cityId = intval($dbRow[Stephino_Rpg_Db_Table_Cities::COL_ID]);
                
                // Not the city we're looking for
                if (null !== $currentCityId && $cityId != $currentCityId) {
                    continue;
                }
                
                // Marketing building enabled
                if (!isset($tradingCities[$cityId]) && null !== $tradingBuilding
                    && $tradingBuilding->getId() == $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID]
                    && $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL] > 0) {
                    $tradingCities[$cityId] = Stephino_Rpg_Config::get()->core()->getMarketBuilding();
                }
                
                // Resources not defined
                if (!isset($resourcesCities[$cityId])) {
                    // Get the max storage
                    $maxStorage = $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_STORAGE];
                    
                    // Store the cities resources
                    $resourcesCities[$cityId] = array(
                        Stephino_Rpg_Db_Table_Cities::COL_CITY_IS_CAPITAL => $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_IS_CAPITAL],
                        Stephino_Rpg_Db_Table_Cities::COL_CITY_NAME       => $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_NAME],
                        Stephino_Rpg_Renderer_Ajax::RESULT_RES_GOLD => array(
                            $dbRow[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD],
                            Stephino_Rpg_Config::get()->core()->getResourceGoldName(),
                            null,
                            Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD
                        ),
                        Stephino_Rpg_Renderer_Ajax::RESULT_RES_RESEARCH => array(
                            $dbRow[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH],
                            Stephino_Rpg_Config::get()->core()->getResourceResearchName(),
                            null,
                            Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH
                        ),
                        Stephino_Rpg_Renderer_Ajax::RESULT_RES_GEM => array(
                            $dbRow[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM],
                            Stephino_Rpg_Config::get()->core()->getResourceGemName(),
                            null,
                            Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM
                        ),
                        Stephino_Rpg_Renderer_Ajax::RESULT_RES_ALPHA => array(
                            $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA],
                            Stephino_Rpg_Config::get()->core()->getResourceAlphaName(),
                            $maxStorage,
                            Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA
                        ),
                        Stephino_Rpg_Renderer_Ajax::RESULT_RES_BETA => array(
                            $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_BETA],
                            Stephino_Rpg_Config::get()->core()->getResourceBetaName(),
                            $maxStorage,
                            Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_BETA
                        ),
                        Stephino_Rpg_Renderer_Ajax::RESULT_RES_GAMMA => array(
                            $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_GAMMA],
                            Stephino_Rpg_Config::get()->core()->getResourceGammaName(),
                            $maxStorage,
                            Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_GAMMA
                        ),
                        Stephino_Rpg_Renderer_Ajax::RESULT_RES_EXTRA_1 => array(
                            $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1],
                            Stephino_Rpg_Config::get()->core()->getResourceExtra1Name(),
                            $maxStorage,
                            Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1
                        ),
                        Stephino_Rpg_Renderer_Ajax::RESULT_RES_EXTRA_2 => array(
                            $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2],
                            Stephino_Rpg_Config::get()->core()->getResourceExtra2Name(),
                            $maxStorage,
                            Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2
                        ),
                    );
                }
            }
        }
        
        // Invalid resources
        if (!count($resourcesCities)) {
            throw new Exception(__('Resources not initialized', 'stephino-rpg'));
        }
        
        // Get the main building configuration
        $mainBuildingConfigId = null;
        if (null !== Stephino_Rpg_Config::get()->core()->getMainBuilding()) {
            $mainBuildingConfigId = Stephino_Rpg_Config::get()->core()->getMainBuilding()->getId();
        }
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_RESOURCES);
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Resources', 'stephino-rpg'),
            ),
            $currentCityId
        );
    }
    
    /**
     * Show the settings dialog
     */
    public static function ajaxInfo() {
        // Get the game settings
        $gameSettings = Stephino_Rpg_Renderer_Ajax::getSettings();
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_INFO);
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Settings', 'stephino-rpg'),
            )
        );
    }
    
    /**
     * Delete account dialog
     */
    public static function ajaxDeleteAccount() {
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_DELETE_ACCOUNT);
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Delete Account', 'stephino-rpg'),
            )
        );
    }
    
    /**
     * Show the About dialog
     */
    public static function ajaxAbout() {
        // Store the changelog flag
        $changeLogFlag = false;
        if (Stephino_Rpg::PLUGIN_VERSION !== Stephino_Rpg_Cache_User::get()->read(Stephino_Rpg_Cache_User::KEY_CHL)) {
            Stephino_Rpg_Cache_User::get()->write(
                Stephino_Rpg_Cache_User::KEY_CHL, 
                Stephino_Rpg::PLUGIN_VERSION
            )->commit();
            $changeLogFlag = true;
        }
        
        // Get the HTMLs
        $about = Stephino_Rpg_About::html(true);
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_ABOUT);
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('About', 'stephino-rpg') . ' Stephino RPG',
            )
        );
    }
    
    /**
     * Show the language selection dialog
     */
    public static function ajaxLanguage() {
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_LANGUAGE);
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Language', 'stephino-rpg'),
            )
        );
    }
    
    /**
     * Show the announcement (if available) and mark it as read
     */
    public static function ajaxAnnouncement() {
        // Get the announcement
        $result = Stephino_Rpg_Db::get()->modelAnnouncement()->get(true);
        
        do {
            // Valid result that has not expired
            if (is_array($result) && $result[3] >= 0) {
                // Authenticated user
                if (Stephino_Rpg_TimeLapse::get()->userId()) {
                    // Have not read this announcement yet
                    if ($result[0] != Stephino_Rpg_Cache_User::get()->read(Stephino_Rpg_Cache_User::KEY_ANN)) {
                        // Mark it as read so it only pops-up once
                        Stephino_Rpg_Cache_User::get()->write(
                            Stephino_Rpg_Cache_User::KEY_ANN, 
                            $result[0]
                        )->commit();
                        
                        // Store the title
                        $title = esc_html($result[1]);
                        
                        // Store the paragraphs
                        $paragraphs = preg_replace(
                            '%<p\b%i', 
                            '<p data-effect="typewriter"', 
                            $result[2]
                        );
                        break;
                    }
                }
            }
            
            // Nothing to show
            $title = __('Announcements', 'stephino-rpg');
            $paragraphs = __('No announcements', 'stephino-rpg');
        } while(false);
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_ANNOUNCEMENT);
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => $title,
            )
        );
    }
}

/*EOF*/