<?php

/**
 * ThemeWarlock - Stephino_Rpg_Renderer_Ajax_Dialog_City
 * 
 * @title      Dialog::City
 * @desc       City dialogs
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    ThemeWarlock
 * @since      TW 1.0
 */
class Stephino_Rpg_Renderer_Ajax_Dialog_City extends Stephino_Rpg_Renderer_Ajax_Dialog {
    
    // Dialog templates
    const TEMPLATE_QUEUES       = 'city/city-queues';
    const TEMPLATE_RENAME       = 'city/city-rename';
    const TEMPLATE_ADVISOR      = 'city/city-advisor';
    const TEMPLATE_MOVE_CAPITAL = 'city/city-move-capital';
    const TEMPLATE_GARRISON     = 'city/city-garrison';
    const TEMPLATE_INFO         = 'city/city-info';
    const TEMPLATE_STAGES       = 'city/city-stages';
    const TEMPLATE_GOVERNMENT   = 'city/city-government';
    const TEMPLATE_WORKFORCE    = 'city/city-workforce';
    
    // Result tags
    const RESULT_CITY_CONFIG_ID = 'cityConfigId';
    
    /**
     * Show the city renaming dialog
     * 
     * @param array $data Data containing <ul>
     * <li><b>cityId</b> (int) City ID</li>
     * </ul>
     */
    public static function ajaxRename($data) {
        // Prepare the city ID
        $cityId = isset($data[self::REQUEST_CITY_ID]) ? intval($data[self::REQUEST_CITY_ID]) : null;
        
        // Get our city information
        $cityInfo = Stephino_Rpg_Renderer_Ajax_Action::getCityInfo($cityId);
        
        // Get the city configuration
        $cityConfig = Stephino_Rpg_Config::get()->cities()->getById(
            $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_CONFIG_ID]
        );
        
        // Invalid city configuration
        if (null === $cityConfig) {
            throw new Exception(
                sprintf(
                    __('Invalid configuration (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigCityName()
                )
            );
        }
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_RENAME);
        
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_IS_CAPITAL] 
                    ? __('Rename Metropolis', 'stephino-rpg') 
                    : __('Rename', 'stephino-rpg'),
            ),
            $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_ID]
        );
    }
    
    /**
     * Show the city advisor dialog
     * 
     * @param array $data Data containing <ul>
     * <li><b>cityId</b> (int) City ID</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxAdvisor($data) {
        // Prepare the city ID
        $cityId = isset($data[self::REQUEST_CITY_ID]) ? intval($data[self::REQUEST_CITY_ID]) : null;
        
        // Get our city information
        $cityInfo = Stephino_Rpg_Renderer_Ajax_Action::getCityInfo($cityId);
        
        // Get the city configuration
        $cityConfig = Stephino_Rpg_Config::get()->cities()->getById(
            $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_CONFIG_ID]
        );
        
        // Invalid city configuration
        if (null === $cityConfig) {
            throw new Exception(
                sprintf(
                    __('Invalid configuration (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigCityName()
                )
            );
        }
        
        // Get the Metropolis movement cost data
        $costData = Stephino_Rpg_Renderer_Ajax_Action::getCostData(
            $cityConfig, 
            $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL] - 1
        );
        
        // Get the government configuration
        $governmentConfig = Stephino_Rpg_Config::get()->governments()->getById(
            $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_GOVERNMENT_CONFIG_ID]
        );
        
        // Get the next item to unlock
        $unlockNext = Stephino_Rpg_Renderer_Ajax_Action::getUnlockNext(
            $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_ID]
        );
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_ADVISOR);
        
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Information', 'stephino-rpg'),
            ),
            $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_ID]
        );
    }
    
    /**
     * Move the Metropolis dialog
     * 
     * @param array $data Data containing <ul>
     * <li><b>cityId</b> (int) City ID</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxMoveCapital($data) {
        // Prepare the city ID
        $cityId = isset($data[self::REQUEST_CITY_ID]) ? intval($data[self::REQUEST_CITY_ID]) : null;
        
        // Get our city information
        $cityInfo = Stephino_Rpg_Renderer_Ajax_Action::getCityInfo($cityId);
        
        // Already the Metropolis
        if ($cityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_IS_CAPITAL]) {
            throw new Exception(__('This is already your empire\'s metropolis', 'stephino-rpg'));
        }
        
        // Get the city configuration
        $cityConfig = Stephino_Rpg_Config::get()->cities()->getById(
            $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_CONFIG_ID]
        );
        
        // Invalid city configuration
        if (null === $cityConfig) {
            throw new Exception(
                sprintf(
                    __('Invalid configuration (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigCityName()
                )
            );
        }
        
        // Get the Metropolis movement cost data
        $costData = Stephino_Rpg_Renderer_Ajax_Action::getCostData(
            $cityConfig, 
            $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL] - 1
        );
        
        // Metropolis cannot be changed
        if (!count($costData)) {
            throw new Exception(__('Metropolis cannot be changed', 'stephino-rpg'));
        }
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_MOVE_CAPITAL);
        
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Move metropolis', 'stephino-rpg'),
            ),
            $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_ID]
        );
    }
    
    /**
     * List all entities garrisoned in a city and military buildings
     * 
     * @param array $data Data containing <ul>
     * <li><b>cityId</b> (int) City ID</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxGarrison($data) {
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
        
        // Store the building levels
        $buildingLevels = array();
        
        // Prepare the military buildings
        $militaryBuildings = array();
        if (is_array($cityData)) {
            foreach (Stephino_Rpg_Config::get()->buildings()->getAll() as $buildingConfig) {
                list($buildingData) = Stephino_Rpg_Renderer_Ajax_Action::getBuildingInfo(
                    $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID], 
                    $buildingConfig->getId()
                );
                
                // Store this building's level
                if (is_array($buildingData) && $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL] > 0) {
                    $buildingLevels[$buildingConfig->getId()] = (int) $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL];
                }
                
                // Military building
                if ($buildingConfig->getAttackPoints() > 0 || $buildingConfig->getDefensePoints() > 0) {
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
        require self::dialogTemplatePath(self::TEMPLATE_GARRISON);
        
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Garrison', 'stephino-rpg'),
            ),
            $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]
        );
    }
    
    /**
     * Manage a city's workforce
     * 
     * @param array $data Data containing <ul>
     * <li><b>cityId</b> (int) City ID</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxWorkforce($data) {
        Stephino_Rpg_Renderer_Ajax::setModalSize(Stephino_Rpg_Renderer_Ajax::MODAL_SIZE_LARGE);
        
        // Prepare the city ID
        $cityId = isset($data[self::REQUEST_CITY_ID]) ? intval($data[self::REQUEST_CITY_ID]) : null;
        
        // Get our city information
        $cityData = Stephino_Rpg_Renderer_Ajax_Action::getCityInfo($cityId);
        
        // Workers enabled
        $workersUsed = false;
        foreach (Stephino_Rpg_Config::get()->buildings()->getAll() as $buildingConfig) {
            if ($buildingConfig->getUseWorkers()) {
                $workersUsed = true;
                break;
            }
        }
        
        // No workers used
        if (!$workersUsed) {
            throw new Exception(__('Workers are not needed', 'stephino-rpg'));
        }
        
        require self::dialogTemplatePath(self::TEMPLATE_WORKFORCE);
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Workforce', 'stephino-rpg'),
            ),
            $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]
        );
    }
    
    /**
     * Show the city stages dialog
     * 
     * @param array $data Data containing <ul>
     * <li><b>commonArgs</b> array((int) City ID)</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxStages($data) {
        Stephino_Rpg_Renderer_Ajax::setModalSize(Stephino_Rpg_Renderer_Ajax::MODAL_SIZE_LARGE);
        
        if (!is_array($commonArgs = isset($data[self::REQUEST_COMMON_ARGS]) ? $data[self::REQUEST_COMMON_ARGS] : array())) {
            $commonArgs = array();
        }
        
        // Prepare the city ID
        $cityId = current($commonArgs);
        
        // Get our city information
        $cityInfo = Stephino_Rpg_Renderer_Ajax_Action::getCityInfo($cityId);
        
        // Get the next item to unlock
        list($buildingLevels, $researchFieldLevels, $unlockStages) = Stephino_Rpg_Renderer_Ajax_Action::getUnlockStages(
            $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_ID]
        );
        
        // Nothing to unlock
        if (!count($unlockStages)) {
            throw new Exception(__('No stages to unlock', 'stephino-rpg'));
        }
        
        require self::dialogTemplatePath(self::TEMPLATE_STAGES);
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Progress', 'stephino-rpg'),
            ),
            $cityId
        );
    }
    
    /**
     * Show the city information dialog
     * 
     * @param array $data Data containing <ul>
     * <li><b>commonArgs</b> array((int) City ID)</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxInfo($data) {
        if (!is_array($commonArgs = isset($data[self::REQUEST_COMMON_ARGS]) ? $data[self::REQUEST_COMMON_ARGS] : array())) {
            $commonArgs = array();
        }
        
        // Prepare the city ID
        $cityId = current($commonArgs);
        
        // Get the city information
        if (null === $cityData = Stephino_Rpg_Db::get()->tableCities()->getById($cityId)) {
            throw new Exception(__('Not found', 'stephino-rpg'));
        }

        // Get the user information
        if (null === $userInfo = Stephino_Rpg_Db::get()->tableUsers()->getById($cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_USER_ID])) {
            throw new Exception(__('Has no owner', 'stephino-rpg'));
        }
        
        // Our city
        $cityOwn = ($userInfo[Stephino_Rpg_Db_Table_Users::COL_ID] == Stephino_Rpg_TimeLapse::get()->userId());
        
        // Prepare the city icon bkg URL
        $cityIconUrl = Stephino_Rpg_Utils_Media::getClosestBackgroundUrl(
            Stephino_Rpg_Config_Cities::KEY,
            $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_CONFIG_ID],
            $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL]
        );
        
        // Get the city configuration
        $cityConfig = Stephino_Rpg_Config::get()->cities()->getById($cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_CONFIG_ID]);
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_INFO);
        
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE          => Stephino_Rpg_Utils_Lingo::getCityName($cityData),
                self::RESULT_CITY_CONFIG_ID => (int) $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_CONFIG_ID],
            ),
            $cityId
        );
    }
    
    /**
     * Show the city government dialog
     * 
     * @param array $data Data containing <ul>
     * <li><b>commonArgs</b> array((int) City ID)</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxGovernmentInfo($data) {
        if (!is_array($commonArgs = isset($data[self::REQUEST_COMMON_ARGS]) ? $data[self::REQUEST_COMMON_ARGS] : array())) {
            $commonArgs = array();
        }
        
        // No governments to choose from
        if (!count(Stephino_Rpg_Config::get()->governments()->getAll())) {
            throw new Exception(Stephino_Rpg_Config::get()->core()->getConfigGovernmentsName() . ' are not supported');
        }
        
        // Prepare the city ID
        $cityId = current($commonArgs);
        
        // Get our city information
        $cityInfo = Stephino_Rpg_Renderer_Ajax_Action::getCityInfo($cityId);
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_GOVERNMENT);
        
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => Stephino_Rpg_Config::get()->core()->getConfigGovernmentsName(),
            ),
            $cityId
        );
    }
    
    /**
     * Get  all the queues in this city
     * 
     * @param array $data Data containing <ul>
     * <li><b>cityId</b> (int) City ID</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxQueuesList($data) {
        // Prepare the city ID
        $cityId = isset($data[self::REQUEST_CITY_ID]) ? intval($data[self::REQUEST_CITY_ID]) : null;
        
        // Get our city information
        $cityInfo = Stephino_Rpg_Renderer_Ajax_Action::getCityInfo($cityId);
        
        // Prepare the data
        $queueData = array(
            Stephino_Rpg_Db_Model_Buildings::NAME => array(),
            Stephino_Rpg_Db_Model_Entities::NAME => array(),
            Stephino_Rpg_Db_Model_ResearchFields::NAME => array(),
        );
        
        // Get the queues
        $cityQueues = Stephino_Rpg_Renderer_Ajax::getQueues(
            $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_ID],
            false,
            false
        );
        if (is_array($cityQueues)) {
            foreach ($cityQueues as $dbRow) {
                switch ($dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_TYPE]){
                    case Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_BUILDING:
                        $queueData[Stephino_Rpg_Db_Model_Buildings::NAME][] = $dbRow;
                        break;
                    
                    case Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_UNIT:
                    case Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_SHIP:
                        $queueData[Stephino_Rpg_Db_Model_Entities::NAME][] = $dbRow;
                        break;
                    
                    case Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_RESEARCH:
                        $queueData[Stephino_Rpg_Db_Model_ResearchFields::NAME][] = $dbRow;
                        break;
                }
            }
        }
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_QUEUES);
        
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Queues', 'stephino-rpg'),
            ),
            $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_ID]
        );
    }
}

/*EOF*/