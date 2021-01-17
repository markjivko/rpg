<?php

/**
 * ThemeWarlock - Stephino_Rpg_Renderer_Ajax_Dialog_User
 * 
 * @title      Dialog::User
 * @desc       User dialogs
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    ThemeWarlock
 * @since      TW 1.0
 */
class Stephino_Rpg_Renderer_Ajax_Dialog_User extends Stephino_Rpg_Renderer_Ajax_Dialog {

    // Dialog templates
    const TEMPLATE_INFO         = 'user/user-info';
    const TEMPLATE_TRADE        = 'user/user-trade';
    const TEMPLATE_CITIES       = 'user/user-cities';
    const TEMPLATE_LEADER_BOARD = 'user/user-leader-board';
    const TEMPLATE_ARENA_LIST   = 'user/user-arena-list';
    const TEMPLATE_ARENA_PLAY   = 'user/user-arena-play';
    const TEMPLATE_ARENA_EDIT   = 'user/user-arena-edit';
    
    // Request keys
    const REQUEST_USER_ID = 'userId';
    
    /**
     * View user profile
     * 
     * @param array $data Data containing <ul>
     * <li><b>userId</b> (int) User ID</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxInfo($data) {
        if (!isset($data[self::REQUEST_USER_ID])) {
            throw new Exception(__('User ID is mandatory', 'stephino-rpg'));
        }
        
        // Get the user information
        $userData = Stephino_Rpg_Db::get()->tableUsers()->getById($data[self::REQUEST_USER_ID]);
        
        // Invalid ID
        if (!is_array($userData)) {
            throw new Exception(__('User not found', 'stephino-rpg'));
        }
        
        // Get the user cities
        $userCitiesList = Stephino_Rpg_Db::get()->tableCities()->getByUser($data[self::REQUEST_USER_ID]);
        $userCities = is_array($userCitiesList) ? count($userCitiesList) : 0;
        
        // Store the name and description
        $userName = Stephino_Rpg_Utils_Lingo::getUserName($userData);
        $userDescription = Stephino_Rpg_Utils_Lingo::getUserDescription($userData);
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_INFO);
        
        // All done
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Profile', 'stephino-rpg') . ': ' . esc_html($userName),
            )
        );
    }
    
    /**
     * Trade Gem for Gold or Research Point
     * 
     * @param array $data Common Arguments
     * @throws Exception
     */
    public static function ajaxTrade($data) {
        $commonArgs = isset($data[self::REQUEST_COMMON_ARGS]) ? $data[self::REQUEST_COMMON_ARGS] : array();
        
        // Get the trade type
        $tradeType = current($commonArgs);
        
        // Get the resource name and ratio
        switch ($tradeType) {
            case Stephino_Rpg_Renderer_Ajax::RESULT_RES_GOLD:
                $tradeName = Stephino_Rpg_Config::get()->core()->getResourceGoldName();
                $tradeRatio = Stephino_Rpg_Config::get()->core()->getGemToGoldRatio();
                break;
                
            case Stephino_Rpg_Renderer_Ajax::RESULT_RES_RESEARCH:
                $tradeName = Stephino_Rpg_Config::get()->core()->getResourceResearchName();
                $tradeRatio = Stephino_Rpg_Config::get()->core()->getGemToResearchRatio();
                break;
            
            default:
                throw new Exception(__('Invalid trade type', 'stephino-rpg'));
        }
        
        // Prepare the user data
        $userData = null;
        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
            $userData = current(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData());
        }
        
        // Invalid user data
        if (!is_array($userData)) {
            throw new Exception(__('User not initialized', 'stephino-rpg'));
        }
        
        // Not enough resources
        if (floor($userData[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM]) <= 0) {
            throw new Exception(
                sprintf(
                    __('Insufficient resources (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getResourceGemName()
                )
            );
        }
        
        // Trading not allowed
        if ($tradeRatio <= 0) {
            throw new Exception(
                sprintf(
                    __('Buying %s is not allowed', 'stephino-rpg'),
                    $tradeName
                )
            );
        }
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_TRADE);
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Trading', 'stephino-rpg'),
            )
        );
    }
    
    /**
     * Platformer Arena
     * 
     * @throws Exception
     */
    public static function ajaxArenaList() {
        if (!Stephino_Rpg_Config::get()->core()->getPtfEnabled()) {
            throw new Exception(__('The arena is not available', 'stephino-rpg'));
        }
        
        // Prepare the user data
        $userData = null;
        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
            $userData = current(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData());
        }
        
        // Invalid user data
        if (!is_array($userData)) {
            throw new Exception(__('User not initialized', 'stephino-rpg'));
        }
        
        // Get the current user ID
        $userId = Stephino_Rpg_TimeLapse::get()->userId();
        
        // Get the platformers this user has access to
        $ptfsList = Stephino_Rpg_Db::get()->modelPtfs()->getForUserId($userId);
        
        // The user can create new platformers
        $canCreate = true;
        if (Stephino_Rpg_Config::get()->core()->getPtfAuthorLimit() > 0) {
            $authorPlatformers = Stephino_Rpg_Db::get()->tablePtfs()->getByUserId($userId);
            if (count($authorPlatformers) >= Stephino_Rpg_Config::get()->core()->getPtfAuthorLimit()) {
                $canCreate = false;
            }
        }

        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_ARENA_LIST);
        Stephino_Rpg_Renderer_Ajax::setModalSize(true);
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Game arena', 'stephino-rpg'),
            )
        );
    }
    
    /**
     * Play a platformer
     * 
     * @param array $data Common Arguments
     * @throws Exception
     */
    public static function ajaxArenaPlay($data) {
        if (!Stephino_Rpg_Config::get()->core()->getPtfEnabled()) {
            throw new Exception(__('The arena is not available', 'stephino-rpg'));
        }
        $commonArgs = isset($data[self::REQUEST_COMMON_ARGS]) ? $data[self::REQUEST_COMMON_ARGS] : array();
        
        // Get the current user ID
        $userId = Stephino_Rpg_TimeLapse::get()->userId();
        
        // Get the platformer ID
        $ptfId = current($commonArgs);
        
        // Invalid platformer
        if (!is_array($ptfRow = Stephino_Rpg_Db::get()->tablePtfs()->getById($ptfId))) {
            throw new Exception(__('Game not found', 'stephino-rpg'));
        }
        
        // Validate the tiles
        if (!is_array(Stephino_Rpg_Db::get()->modelPtfs()->getTileSet($ptfRow))) {
            throw new Exception(__('Invalid game', 'stephino-rpg'));
        }
        
        // Mark the start of the play
        Stephino_Rpg_Db::get()->modelPtfs()->play($ptfId, $userId);
            
        // Get the next platformer ID
        $nextPtfId = Stephino_Rpg_Db::get()->modelPtfs()->getNextId($userId, $ptfId);
        
        // This is my platformer
        $ptfOwn = ($userId === (int) $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_USER_ID]);
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_ARENA_PLAY);
        Stephino_Rpg_Renderer_Ajax::setModalSize(true);
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Playing', 'stephino-rpg') . ': ' . esc_html($ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_NAME]),
            )
        );
    }
    
    /**
     * Create/Edit a platformer
     * 
     * @param array $data Common Arguments
     * @throws Exception
     */
    public static function ajaxArenaEdit($data) {
        if (!Stephino_Rpg_Config::get()->core()->getPtfEnabled()) {
            throw new Exception(__('The arena is not available', 'stephino-rpg'));
        }
        $commonArgs = isset($data[self::REQUEST_COMMON_ARGS]) ? $data[self::REQUEST_COMMON_ARGS] : array();
        
        // Get the current user ID
        $userId = Stephino_Rpg_TimeLapse::get()->userId();
        
        // Get the platformer ID
        $ptfId = abs((int) current($commonArgs));
        
        // Attempting to create a new platformer
        if (0 === $ptfId) {
            // Prepare the new platformer
            $ptfId = Stephino_Rpg_Db::get()->modelPtfs()->create($userId);
            if (null === $ptfId) {
                throw new Exception(__('Could not create a new game', 'stephino-rpg'));
            }
        }
        
        // Get the platformer details
        if (!is_array($ptfRow = Stephino_Rpg_Db::get()->tablePtfs()->getById($ptfId))) {
            throw new Exception(__('Game not found', 'stephino-rpg'));
        }
        
        // Confirm the author
        if ($userId != $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_USER_ID]) {
            throw new Exception(__('You cannot edit this game', 'stephino-rpg'));
        }
        
        // Get the compressed tile set (always a JSON as pre-defined platformers cannot be edited)
        $tileSetC = @json_decode($ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_CONTENT], true);
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_ARENA_EDIT);
        Stephino_Rpg_Renderer_Ajax::setModalSize(true);
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Game Creator', 'stephino-rpg'),
            )
        );
    }
    
    /**
     * List all cities belonging to this user
     * 
     * @param array $data Data containing <ul>
     * <li><b>commonArgs</b> array((int) User ID)</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxCities($data) {
        if (!is_array($commonArgs = isset($data[self::REQUEST_COMMON_ARGS]) ? $data[self::REQUEST_COMMON_ARGS] : array())) {
            $commonArgs = array();
        }
        
        // Get the user ID
        $userId = intval(current($commonArgs));
        if ($userId <= 0) {
            throw new Exception(__('User ID is mandatory', 'stephino-rpg'));
        }
        
        // Get the user information
        $userData = Stephino_Rpg_Db::get()->tableUsers()->getById($userId);
        
        // Invalid ID
        if (!is_array($userData)) {
            throw new Exception(__('User not found', 'stephino-rpg'));
        }
        
        // Get the user name
        $userName = Stephino_Rpg_Utils_Lingo::getUserName($userData);
        
        // Get the user cities
        if (null === $userCitiesList = Stephino_Rpg_Db::get()->tableCities()->getByUser($userId)) {
            throw new Exception(__('User not initialized', 'stephino-rpg'));
        }
        
        // Get the islands
        $userIslandsList = Stephino_Rpg_Db::get()->tableIslands()->getByIds(
            array_map(
                function($cityRow) {
                    return (int) $cityRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID];
                }, 
                $userCitiesList
            )
        );

        // Inner join
        if (is_array($userIslandsList)) {
            $userCitiesList = array_map(
                function($cityRow) use($userIslandsList) {
                    foreach ($userIslandsList as $islandRow) {
                        if ($islandRow[Stephino_Rpg_Db_Table_Islands::COL_ID] == $cityRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID]) {
                            $cityRow = array_merge($cityRow, $islandRow);
                            break;
                        }
                    }
                    return $cityRow;
                }, 
                $userCitiesList
            );
        }
        
        // This is my empire
        $myEmpire = (Stephino_Rpg_TimeLapse::get()->userId() == $userId);
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_CITIES);
        
        // All done
        Stephino_Rpg_Renderer_Ajax::setModalSize(true);
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => Stephino_Rpg_Config::get()->core()->getConfigCitiesName(true) . ': ' . esc_html($userName),
            )
        );
    }
    
    /**
     * Leader board
     */
    public static function ajaxLeaderBoard() {
        // Prepare the user data
        $userData = null;
        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
            $userData = current(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData());
        }
        
        // Invalid user data
        if (!is_array($userData)) {
            throw new Exception(__('User not initialized', 'stephino-rpg'));
        }
        
        // Get the top players
        $mvpList = Stephino_Rpg_Db::get()->tableUsers()->getMVP(50);
        
        // Is this user in list?
        $userIsMvp = false;
        $userPlace = null;
        if (is_array($mvpList)) {
            foreach ($mvpList as $mvpKey => $mvpUser) {
                if ($mvpUser[Stephino_Rpg_Db_Table_Users::COL_ID] == $userData[Stephino_Rpg_Db_Table_Users::COL_ID]) {
                    $userIsMvp = true;
                    $userPlace = $mvpKey + 1;
                    break;
                }
            }
        }
        
        // Get the current user's place if not on podium
        if (null === $userPlace) {
            $userPlace = Stephino_Rpg_Db::get()->tableUsers()->getPlace(
                $userData[Stephino_Rpg_Db_Table_Users::COL_USER_SCORE]
            );
        }
        
        // Store the current time for online status
        $currentTime = time();
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_LEADER_BOARD);
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Leader board', 'stephino-rpg'),
            )
        );
    }
}

/* EOF */