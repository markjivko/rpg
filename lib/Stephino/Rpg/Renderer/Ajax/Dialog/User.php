<?php

/**
 * ThemeWarlock - Stephino_Rpg_Renderer_Ajax_Dialog_User
 * 
 * @title      Dialog::User
 * @desc       User dialogs
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    ThemeWarlock
 * @since      TW 1.0
 */
class Stephino_Rpg_Renderer_Ajax_Dialog_User extends Stephino_Rpg_Renderer_Ajax_Dialog {

    // Dialog templates
    const TEMPLATE_INFO               = 'user/user-info';
    const TEMPLATE_TRADE              = 'user/user-trade';
    const TEMPLATE_CITIES             = 'user/user-cities';
    const TEMPLATE_LEADER_BOARD       = 'user/user-leader-board';
    const TEMPLATE_ARENA_LIST         = 'user/user-arena-list';
    const TEMPLATE_ARENA_LIST_PAGE    = 'user/user-arena-list-page';
    const TEMPLATE_ARENA_PLAY         = 'user/user-arena-play';
    const TEMPLATE_ARENA_PLAY_DETAILS = 'user/user-arena-play-details';
    const TEMPLATE_ARENA_EDIT         = 'user/user-arena-edit';
    
    // Request keys
    const REQUEST_USER_ID         = 'userId';
    const REQUEST_ARENA_CATEGORY  = 'arenaCategory';
    const REQUEST_ARENA_ORDER     = 'arenaOrder';
    const REQUEST_ARENA_PAGE      = 'arenaPage';
    const REQUEST_ARENA_AUTHOR_ID = 'arenaAuthorId';
    
    // Statistics
    const PTF_STAT_CREATED = 'gamesCreated';
    const PTF_STAT_PLAYED  = 'gamesPlayed';
    const PTF_STAT_WON     = 'gamesWon';
    
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
        
        // Store the user ID
        $userId = abs((int) $data[self::REQUEST_USER_ID]);
        
        // Invalid user
        if (!is_array($userData = Stephino_Rpg_Db::get()->tableUsers()->getById($userId))) {
            throw new Exception(__('User not found', 'stephino-rpg'));
        }
        
        // Get the user cities
        $userCitiesList = Stephino_Rpg_Db::get()->tableCities()->getByUser($userId);
        $userCities = is_array($userCitiesList) ? count($userCitiesList) : 0;
        
        // Store the name and description
        $userName = Stephino_Rpg_Utils_Lingo::getUserName($userData);
        $userDescription = Stephino_Rpg_Utils_Lingo::getUserDescription($userData);
        
        // Get the user stats
        $userStats = array(
            self::PTF_STAT_CREATED => array(
                count(Stephino_Rpg_Db::get()->modelPtfs()->getByUserId($userId)),
                __('Created', 'stephino-rpg'),
            ),
            self::PTF_STAT_WON => array(
                is_array($userData)
                    ? (int) $userData[Stephino_Rpg_Db_Table_Users::COL_USER_PTF_WON]
                    : 0,
                __('Won', 'stephino-rpg'),
            ),
            self::PTF_STAT_PLAYED => array(
                is_array($userData)
                    ? (int) $userData[Stephino_Rpg_Db_Table_Users::COL_USER_PTF_PLAYED]
                    : 0,
                __('Played', 'stephino-rpg'),
            ),
        );
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_INFO);
        
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Profile', 'stephino-rpg') . ': ' . esc_html($userName),
            )
        );
    }
    
    /**
     * Trade Gem for Gold or Research Point
     * 
     * @param array $data Data containing <ul>
     * <li><b>commonArgs</b> array((string) Trade type)</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxTrade($data) {
        if (!is_array($commonArgs = isset($data[self::REQUEST_COMMON_ARGS]) ? $data[self::REQUEST_COMMON_ARGS] : array())) {
            $commonArgs = array();
        }
        
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
     * Get the PTF Arena games list
     * 
     * @param array $data Data containing <ul>
     * <li><b>self::REQUEST_ARENA_CATEGORY</b> (string) Arena Category</li>
     * <li><b>self::REQUEST_ARENA_ORDER</b> (boolean) Order ASC or DESC</li>
     * </ul>
     */
    public static function ajaxArenaListPage($data) {
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
        
        // Get the user input
        $arenaPageCategory = isset($data[self::REQUEST_ARENA_CATEGORY]) 
            ? trim($data[self::REQUEST_ARENA_CATEGORY]) 
            : null;
        $arenaPageOrder = isset($data[self::REQUEST_ARENA_ORDER]) 
            ? !!$data[self::REQUEST_ARENA_ORDER] 
            : false;
        $arenaPageNumber = isset($data[self::REQUEST_ARENA_PAGE]) 
            ? abs((int) $data[self::REQUEST_ARENA_PAGE])
            : 1;
        $arenaAuthorId = isset($data[self::REQUEST_ARENA_AUTHOR_ID]) 
            ? abs((int) $data[self::REQUEST_ARENA_AUTHOR_ID])
            : 0;
        
        // Reverse ordering
        if (Stephino_Rpg_Db_Table_Ptfs::COL_PTF_USER_ID === $arenaPageCategory) {
            $arenaPageOrder = !$arenaPageOrder;
        }
        
        // Pagination data
        $pagination = (new Stephino_Rpg_Utils_Pagination(
            $arenaAuthorId > 0
                ? Stephino_Rpg_Db::get()->tablePtfs()->getCountByUserId($arenaAuthorId)
                : Stephino_Rpg_Db::get()->tablePtfs()->getCountForUserId($userId),
            Stephino_Rpg_Db_Model_Ptfs::PAGINATION_ITEMS_PER_PAGE,
            $arenaPageNumber
        ))->setAction('ptfArenaList');
        
        // Get the data
        $ptfsList = $arenaAuthorId > 0
            ? Stephino_Rpg_Db::get()->modelPtfs()->getByUserId(
                $arenaAuthorId,
                $arenaPageCategory,
                $arenaPageOrder,
                $pagination->getSqlLimitCount(),
                $pagination->getSqlLimitOffset()
            )
            : Stephino_Rpg_Db::get()->modelPtfs()->getForUserId(
                $userId,
                $arenaPageCategory,
                $arenaPageOrder,
                $pagination->getSqlLimitCount(),
                $pagination->getSqlLimitOffset()
            );
        
        // Load the template
        require self::dialogTemplatePath(self::TEMPLATE_ARENA_LIST_PAGE);
        return Stephino_Rpg_Renderer_Ajax::wrap(true);
    }
    
    /**
     * Platformer Arena
     * 
     * @param array $data Data containing <ul>
     * <li><b>commonArgs</b> array((int) Author ID)</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxArenaList($data) {
        Stephino_Rpg_Renderer_Ajax::setModalSize(Stephino_Rpg_Renderer_Ajax::MODAL_SIZE_LARGE);
        
        if (!Stephino_Rpg_Config::get()->core()->getPtfEnabled()) {
            throw new Exception(__('The arena is not available', 'stephino-rpg'));
        }
        
        if (!is_array($commonArgs = isset($data[self::REQUEST_COMMON_ARGS]) ? $data[self::REQUEST_COMMON_ARGS] : array())) {
            $commonArgs = array();
        }
        
        // Invalid user data
        if (null === $userId = Stephino_Rpg_TimeLapse::get()->userId()) {
            throw new Exception(__('User not initialized', 'stephino-rpg'));
        }
        
        // Get the author ID
        $authorId = abs((int) current($commonArgs));
        $authorName = $authorId > 0 
            ? Stephino_Rpg_Utils_Lingo::getUserName(Stephino_Rpg_Db::get()->tableUsers()->getById($authorId))
            : null;
        
        // The user can create new platformers
        $userCanCreate = true;
        
        // Player authorship limits
        if (!is_super_admin()) {
            if (0 === Stephino_Rpg_Config::get()->core()->getPtfAuthorLimit()) {
                $userCanCreate = false;
            } else {
                if (Stephino_Rpg_Db::get()->tablePtfs()->getCountByUserId($userId) >= Stephino_Rpg_Config::get()->core()->getPtfAuthorLimit()) {
                    $userCanCreate = false;
                }
            }
        }

        require self::dialogTemplatePath(self::TEMPLATE_ARENA_LIST);
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => $authorId > 0
                    ? __('Author page', 'stephino-rpg')
                    : __('Game arena', 'stephino-rpg'),
            )
        );
    }
    
    /**
     * Play a platformer
     * 
     * @param array $data Data containing <ul>
     * <li><b>commonArgs</b> array((int) Platformer ID)</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxArenaPlay($data) {
        Stephino_Rpg_Renderer_Ajax::setModalSize(Stephino_Rpg_Renderer_Ajax::MODAL_SIZE_LARGE);
        
        if (!Stephino_Rpg_Config::get()->core()->getPtfEnabled()) {
            throw new Exception(__('The arena is not available', 'stephino-rpg'));
        }
        
        if (!is_array($commonArgs = isset($data[self::REQUEST_COMMON_ARGS]) ? $data[self::REQUEST_COMMON_ARGS] : array())) {
            $commonArgs = array();
        }
        
        // Get the platformer ID
        $ptfId = abs((int) current($commonArgs));
        
        // Start the game
        list($ptfRow, $ptfOwn, $authorId, $authorName) = Stephino_Rpg_Renderer_Ajax_Action_User::ajaxPtfStarted(
            array(
                Stephino_Rpg_Renderer_Ajax_Action_User::REQUEST_PTF_ID => $ptfId
            ),
            false
        );
        
        require self::dialogTemplatePath(self::TEMPLATE_ARENA_PLAY);
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Playing', 'stephino-rpg') . ': ' . esc_html($ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_NAME]),
            )
        );
    }
    
    /**
     * Create/Edit a platformer
     * 
     * @param array $data Data containing <ul>
     * <li><b>commonArgs</b> array((int) Platformer ID)</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxArenaEdit($data) {
        Stephino_Rpg_Renderer_Ajax::setModalSize(Stephino_Rpg_Renderer_Ajax::MODAL_SIZE_LARGE);
        
        if (!Stephino_Rpg_Config::get()->core()->getPtfEnabled()) {
            throw new Exception(__('The arena is not available', 'stephino-rpg'));
        }
        
        if (!is_array($commonArgs = isset($data[self::REQUEST_COMMON_ARGS]) ? $data[self::REQUEST_COMMON_ARGS] : array())) {
            $commonArgs = array();
        }
        
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
        
        require self::dialogTemplatePath(self::TEMPLATE_ARENA_EDIT);
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
        Stephino_Rpg_Renderer_Ajax::setModalSize(Stephino_Rpg_Renderer_Ajax::MODAL_SIZE_LARGE);
        
        if (!is_array($commonArgs = isset($data[self::REQUEST_COMMON_ARGS]) ? $data[self::REQUEST_COMMON_ARGS] : array())) {
            $commonArgs = array();
        }
        
        // Get the user ID
        $userId = abs((int) current($commonArgs));
        if ($userId < 1) {
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
        
        require self::dialogTemplatePath(self::TEMPLATE_CITIES);
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
        Stephino_Rpg_Renderer_Ajax::setModalSize(Stephino_Rpg_Renderer_Ajax::MODAL_SIZE_LARGE);
        
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
        $leaderBoard = Stephino_Rpg_Db::get()->tableUsers()->getMVP(
            Stephino_Rpg_Config::get()->core()->getLeaderBoardSize()
        );
        
        // Is this user in list?
        $userPlace = null;
        if (is_array($leaderBoard)) {
            foreach ($leaderBoard as $mvpKey => $mvpUser) {
                if ($mvpUser[Stephino_Rpg_Db_Table_Users::COL_ID] == $userData[Stephino_Rpg_Db_Table_Users::COL_ID]) {
                    $userPlace = $mvpKey + 1;
                    break;
                }
            }
            
            // Outside the list
            if (null === $userPlace) {
                $userPlace = Stephino_Rpg_Db::get()->tableUsers()->getPlace(
                    $userData[Stephino_Rpg_Db_Table_Users::COL_USER_SCORE]
                );
                $leaderBoard[] = null;
                $leaderBoard[$userPlace - 1] = $userData;
            }
        }
        
        // Store the current time for online status check
        $currentTime = time();
        
        require self::dialogTemplatePath(self::TEMPLATE_LEADER_BOARD);
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Leader board', 'stephino-rpg'),
            )
        );
    }
}

/* EOF */