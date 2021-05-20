<?php

/**
 * Stephino_Rpg_Renderer_Ajax_Action_User
 * 
 * @title      Action::User
 * @desc       User actions
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Renderer_Ajax_Action_User extends Stephino_Rpg_Renderer_Ajax_Action {

    // Request keys
    const REQUEST_USER_ID        = 'userId';
    const REQUEST_TRADE_GEM      = 'tradeGem';
    const REQUEST_TRADE_TYPE     = 'tradeType';
    const REQUEST_PTF_ID         = 'ptfId';
    const REQUEST_PTF_REVIEW     = 'ptfReview';
    const REQUEST_PTF_RATING     = 'ptfRating';
    const REQUEST_PTF_WON        = 'ptfWon';
    const REQUEST_PTF_TILE_SET   = 'ptfTileSet';
    const REQUEST_PTF_NAME       = 'ptfName';
    const REQUEST_PTF_WIDTH      = 'ptfWidth';
    const REQUEST_PTF_HEIGHT     = 'ptfHeight';
    const REQUEST_PTF_VISIBILITY = 'ptfVisibility';
    
    /**
     * Trade Gem for Gold or Research Point
     * 
     * @param array $data Data containing <ul>
     * <li><b>self::REQUEST_TRADE_GEM</b> (int) Gem to trade</li>
     * <li><b>self::REQUEST_TRADE_TYPE</b> (string) Trade type</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxTrade($data) {
        // Get the gem
        $resGem = isset($data[self::REQUEST_TRADE_GEM]) ? intval($data[self::REQUEST_TRADE_GEM]) : 0;
        
        // Get the trade type
        $tradeType = isset($data[self::REQUEST_TRADE_TYPE]) ? trim($data[self::REQUEST_TRADE_TYPE]) : '';
        
        // Get the resource key and ratio
        switch ($tradeType) {
            case Stephino_Rpg_Renderer_Ajax::RESULT_RES_GOLD:
                $tradeKey = Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD;
                $tradeRatio = Stephino_Rpg_Config::get()->core()->getGemToGoldRatio();
                break;
                
            case Stephino_Rpg_Renderer_Ajax::RESULT_RES_RESEARCH:
                $tradeKey = Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH;
                $tradeRatio = Stephino_Rpg_Config::get()->core()->getGemToResearchRatio();
                break;
            
            default:
                throw new Exception(__('Invalid trade type', 'stephino-rpg'));
        }
        
        // Trading is disabled
        if ($tradeRatio <= 0) {
            throw new Exception(__('Trading is disabled', 'stephino-rpg'));
        }
        
        // Invalid gem count
        if ($resGem <= 0) {
            throw new Exception(__('Invalid quantity', 'stephino-rpg'));
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
        
        // Not enough gem
        if ($userData[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM] < $resGem) {
            throw new Exception(
                sprintf(
                    __('Insufficient resources (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getResourceGemName()
                )
            );
        }
        
        // Get the new values
        $newValues = array(
            Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM => $userData[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM] - $resGem,
            $tradeKey => $userData[$tradeKey] + $resGem * $tradeRatio
        );

        // Update the database
        $result = Stephino_Rpg_Db::get()->tableUsers()->updateById(
            $newValues, 
            $userData[Stephino_Rpg_Db_Table_Users::COL_ID]
        );
        
        // Update the time-lapse references (for the wrap method to work)
        if (false !== $result) {
            foreach ($newValues as $nvKey => $nvValue) {
                Stephino_Rpg_TimeLapse::get()
                    ->worker(Stephino_Rpg_TimeLapse_Resources::KEY)
                    ->updateRef(
                        Stephino_Rpg_Db_Table_Users::COL_ID, 
                        $userData[Stephino_Rpg_Db_Table_Users::COL_ID], 
                        $nvKey, 
                        $nvValue
                    );
            }
        }
        
        return Stephino_Rpg_Renderer_Ajax::wrap($result);
    }
    
    /**
     * Toggle game master status for a player
     * 
     * @param array $data Data containing <ul>
     * <li><b>self::REQUEST_USER_ID</b> (int) User ID</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxToggleGm($data) {
        // Only GMs can promote game masters
        if (!Stephino_Rpg_Cache_User::get()->isGameMaster()) {
            throw new Exception(__('You cannot promote or demote other players', 'stephino-rpg'));
        }
        
        // Get the user ID
        $userId = isset($data[self::REQUEST_USER_ID]) ? abs((int) $data[self::REQUEST_USER_ID]) : 0;
        
        // Invalid user id
        if ($userId <= 0) {
            throw new Exception(__('Invalid user ID', 'stephino-rpg'));
        }
        
        // Get the user data
        $userData = Stephino_Rpg_Db::get()->tableUsers()->getById($userId);
        if (!is_array($userData)) {
            throw new Exception(__('Invalid user', 'stephino-rpg'));
        }
        
        // Cannot promote/demote robots
        if ((int) $userData[Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID] <= 0) {
            throw new Exception(__('Robots cannot be promoted to game masters', 'stephino-rpg'));
        }
        
        // Cannot promote/demote super-admins
        if (Stephino_Rpg_Cache_User::get()->isGameAdmin((int) $userData[Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID])) {
            throw new Exception(__('Super-admins cannot be demoted', 'stephino-rpg'));
        }
        
        // Get the game settings
        $gameSettings = isset($userData[Stephino_Rpg_Db_Table_Users::COL_USER_GAME_SETTINGS])
            ? json_decode($userData[Stephino_Rpg_Db_Table_Users::COL_USER_GAME_SETTINGS], true)
            : array();
        
        // Sanitize
        if (!is_array($gameSettings)) {
            $gameSettings = array();
        }
        
        // Toggle the Game Master flag
        $gameSettings[Stephino_Rpg_Cache_User::KEY_GAME_MASTER] = !isset($gameSettings[Stephino_Rpg_Cache_User::KEY_GAME_MASTER])
            || !$gameSettings[Stephino_Rpg_Cache_User::KEY_GAME_MASTER];
        
        // Send the notification
        Stephino_Rpg_Db::get()->modelMessages()->notify(
            $userId, 
            Stephino_Rpg_Db_Model_Messages::TEMPLATE_NOTIF_USER_GAME_MASTER,
            array(
                // gameMaster
                $gameSettings[Stephino_Rpg_Cache_User::KEY_GAME_MASTER]
            )
        );
        
        // Update the game settings
        return Stephino_Rpg_Db::get()->tableUsers()->setGameSettings($gameSettings, $userId);
    }
    
    /**
     * Delete a platformer game
     * 
     * @param array $data Data containing <ul>
     * <li><b>self::REQUEST_PTF_ID</b> (int) Platformer ID</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxPtfDelete($data) {
        if (!Stephino_Rpg_Config::get()->core()->getPtfEnabled()) {
            throw new Exception(__('The arena is not available', 'stephino-rpg'));
        }
        
        // Only admins can delete games
        if (!Stephino_Rpg_Cache_User::get()->isGameMaster()) {
            throw new Exception(__('You cannot delete this game', 'stephino-rpg'));
        }
        
        // Get the platformer ID
        $ptfId = isset($data[self::REQUEST_PTF_ID]) ? intval($data[self::REQUEST_PTF_ID]) : 0;
        
        // Invalid platformer ID
        if ($ptfId <= 0) {
            throw new Exception(__('Invalid game ID', 'stephino-rpg'));
        }
        
        // Invalid platformer
        if (!is_array(Stephino_Rpg_Db::get()->tablePtfs()->getById($ptfId))) {
            throw new Exception(__('Game not found', 'stephino-rpg'));
        }
        
        return Stephino_Rpg_Db::get()->tablePtfs()->deleteById($ptfId);
    }
    
    /**
     * Rate a platformer game
     * 
     * @param array $data Data containing <ul>
     * <li><b>self::REQUEST_PTF_ID</b> (int) Platformer ID</li>
     * <li><b>self::REQUEST_PTF_RATING</b> (int) Platformer Rating (1 to 5)</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxPtfRate($data) {
        if (!Stephino_Rpg_Config::get()->core()->getPtfEnabled()) {
            throw new Exception(__('The arena is not available', 'stephino-rpg'));
        }
        
        // Get the platformer ID
        $ptfId = isset($data[self::REQUEST_PTF_ID]) ? intval($data[self::REQUEST_PTF_ID]) : 0;
        $ptfRating = isset($data[self::REQUEST_PTF_RATING]) ? intval($data[self::REQUEST_PTF_RATING]) : 0;
        
        // Invalid rating
        if ($ptfRating < 1 || $ptfRating > 5) {
            throw new Exception(__('Invalid rating', 'stephino-rpg'));
        }
        
        // Invalid platformer ID
        if ($ptfId <= 0) {
            throw new Exception(__('Invalid game ID', 'stephino-rpg'));
        }
        
        // Invalid platformer
        if (!is_array($ptfRow = Stephino_Rpg_Db::get()->tablePtfs()->getById($ptfId))) {
            throw new Exception(__('Game not found', 'stephino-rpg'));
        }
        
        // Get the game data
        $ratingCount = abs((int) $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_RATING_COUNT]);
        $ratingValue = round($ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_RATING], 4);
        
        // Get my past ratings
        $userRatings = Stephino_Rpg_Cache_User::get()->read(Stephino_Rpg_Cache_User::KEY_PTF_RATES, array());
        if (!is_array($userRatings)) {
            $userRatings = array();
        }
        
        // Have not rated this game yet
        if (!isset($userRatings[$ptfId])) {
            // Prepend the rating; store a maximum of 50 values
            $userRatings = array_slice(array($ptfId => $ptfRating) + $userRatings, 0, 50, true);
            
            // PTF new rating
            $ratingValue = round(($ratingCount * $ratingValue + $ptfRating) / ($ratingCount + 1), 4);
            $ratingCount++;
        } else {
            // PTF ammended rating
            if ($ratingCount > 0) {
                $ratingValue = round(($ratingCount * $ratingValue - $userRatings[$ptfId] + $ptfRating) / $ratingCount, 4);
            }
            
            // Update the rating
            $userRatings[$ptfId] = $ptfRating;
        }
        
        // Update the cache
        Stephino_Rpg_Cache_User::get()
            ->write(Stephino_Rpg_Cache_User::KEY_PTF_RATES, $userRatings)
            ->commit();
        
        // Update the game
        Stephino_Rpg_Db::get()->tablePtfs()->updateById(
            array(
                Stephino_Rpg_Db_Table_Ptfs::COL_PTF_RATING       => $ratingValue,
                Stephino_Rpg_Db_Table_Ptfs::COL_PTF_RATING_COUNT => $ratingCount,
            ),
            $ptfId
        );
        
        // Message
        echo sprintf(
            __('Rated %s', 'stephino-rpg'),
            '<b>' . $ptfRating . '</b> ' . _n('star', 'stars', $ptfRating, 'stephino-rpg')
        );
    }
    
    /**
     * Review a platformer game
     * 
     * @param array $data Data containing <ul>
     * <li><b>self::REQUEST_PTF_ID</b> (int) Platformer ID</li>
     * <li><b>self::REQUEST_PTF_REVIEW</b> (string) Platformer Review Flag</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxPtfReview($data) {
        if (!Stephino_Rpg_Config::get()->core()->getPtfEnabled()) {
            throw new Exception(__('The arena is not available', 'stephino-rpg'));
        }
        
        // Only admins can review games
        if (!Stephino_Rpg_Cache_User::get()->isGameMaster()) {
            throw new Exception(__('You cannot review this game', 'stephino-rpg'));
        }
        
        // Get the platformer ID
        $ptfId = isset($data[self::REQUEST_PTF_ID]) ? intval($data[self::REQUEST_PTF_ID]) : 0;
        $ptfReview = isset($data[self::REQUEST_PTF_REVIEW]) ? trim($data[self::REQUEST_PTF_REVIEW]) : null;
        
        // Invalid platformer ID
        if ($ptfId <= 0) {
            throw new Exception(__('Invalid game ID', 'stephino-rpg'));
        }
        
        // Invalid review
        if (null === $ptfLabel = Stephino_Rpg_Db::get()->modelPtfs()->getReviewLabels($ptfReview)) {
            throw new Exception(__('Invalid review', 'stephino-rpg'));
        }
        
        // Invalid platformer
        if (!is_array($ptfRow = Stephino_Rpg_Db::get()->tablePtfs()->getById($ptfId))) {
            throw new Exception(__('Game not found', 'stephino-rpg'));
        }
        
        // Prepare the visibility
        $ptfVisibility = Stephino_Rpg_Db_Table_Ptfs::PTF_VISIBILITY_PRIVATE;
        if (in_array($ptfReview, array(
            Stephino_Rpg_Db_Table_Ptfs::PTF_REVIEW_PENDING,
            Stephino_Rpg_Db_Table_Ptfs::PTF_REVIEW_APPROVED,
            Stephino_Rpg_Db_Table_Ptfs::PTF_REVIEW_SUSPENDED,
        )) || 0 == $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_USER_ID]) {
            $ptfVisibility = Stephino_Rpg_Db_Table_Ptfs::PTF_VISIBILITY_PUBLIC;
        }
        
        // Update the platformer
        $result = Stephino_Rpg_Db::get()->tablePtfs()->updateById(
            array(
                Stephino_Rpg_Db_Table_Ptfs::COL_PTF_REVIEW     => $ptfReview,
                Stephino_Rpg_Db_Table_Ptfs::COL_PTF_VISIBILITY => $ptfVisibility,
            ), 
            $ptfId
        );
        
        // Could not update
        if (false === $result) {
            throw new Exception(__('Could not review game. Please try again later.', 'stephino-rpg'));
        }
        
        // Send message to players only
        if (0 !== (int) $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_USER_ID]) {
            // Get the number of suspended games
            $ptfSuspended = null;
            if (Stephino_Rpg_Db_Table_Ptfs::PTF_REVIEW_SUSPENDED === $ptfReview) {
                $ptfSuspended = Stephino_Rpg_Db::get()->tablePtfs()->getCountSuspended(
                    $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_USER_ID]
                );
            }

            // Send the notification
            Stephino_Rpg_Db::get()->modelMessages()->notify(
                $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_USER_ID], 
                Stephino_Rpg_Db_Model_Messages::TEMPLATE_NOTIF_PTF_REVIEW,
                array(
                    $ptfReview,
                    // ptfId
                    $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_ID],
                    // ptfName
                    $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_NAME],
                    $ptfSuspended
                )
            );
        }
        
        return $result;
    }
    
    /**
     * Save a platformer data
     * 
     * @param array $data Data containing <ul>
     * <li><b>self::REQUEST_PTF_ID</b> (int) Platformer ID</li>
     * <li><b>self::REQUEST_PTF_TILE_SET</b> (array) <b>Compressed</b> tiles set array</li>
     * <li><b>self::REQUEST_PTF_WIDTH</b> (int) Platformer Width (blocks)</li>
     * <li><b>self::REQUEST_PTF_HEIGHT</b> (int) Platformer Height (blocks)</li>
     * <li><b>self::REQUEST_PTF_NAME</b> (string) Platformer Name</li>
     * <li><b>self::REQUEST_PTF_VISIBILITY</b> (string) Platformer Visibility</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxPtfSave($data) {
        if (!Stephino_Rpg_Config::get()->core()->getPtfEnabled()) {
            throw new Exception(__('The arena is not available', 'stephino-rpg'));
        }
        
        // Get the current user ID
        $userId = Stephino_Rpg_TimeLapse::get()->userId();
        
        // Account suspended
        if (Stephino_Rpg_Db::get()->modelPtfs()->playerIsSuspended($userId, Stephino_Rpg_Cache_User::get()->isGameMaster())) {
            throw new Exception(__('Your game arena publisher account was suspended', 'stephino-rpg'));
        }
        
        // Get the platformer ID
        $ptfId = isset($data[self::REQUEST_PTF_ID]) ? intval($data[self::REQUEST_PTF_ID]) : 0;
        
        // Invalid platformer ID
        if ($ptfId <= 0) {
            throw new Exception(__('Invalid game ID', 'stephino-rpg'));
        }
        
        // Invalid platformer
        if (!is_array($ptfRow = Stephino_Rpg_Db::get()->tablePtfs()->getById($ptfId))) {
            throw new Exception(__('Game not found', 'stephino-rpg'));
        }
        
        // Editing rights
        if (!Stephino_Rpg_Db::get()->modelPtfs()->playerCanEdit(
            $userId, 
            Stephino_Rpg_Cache_User::get()->isGameMaster(),
            $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_USER_ID], 
            $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_REVIEW]
        )) {
            throw new Exception(__('You cannot edit this game', 'stephino-rpg'));
        }
        
        // Get the compressed tileset
        $tileSetC = isset($data[self::REQUEST_PTF_TILE_SET]) ? $data[self::REQUEST_PTF_TILE_SET] : array();
        if (!is_array($tileSetC)) {
            throw new Exception(__('Invalid request', 'stephino-rpg'));
        }
        
        // Get the platformer visibility
        $ptfVisibility = isset($data[self::REQUEST_PTF_VISIBILITY]) 
            ? trim($data[self::REQUEST_PTF_VISIBILITY])
            : $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_VISIBILITY];
        if (!in_array($ptfVisibility, Stephino_Rpg_Db_Table_Ptfs::PTF_VISIBILITIES)) {
            throw new Exception(__('Invalid game visibility', 'stephino-rpg'));
        }
        
        // Get the new platformer version
        $ptfVersion = intval($ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_VERSION]) + 1;
        
        // Sanitize the platformer definition and tile set
        $ptfDef = Stephino_Rpg_Db::get()->modelPtfs()->sanitize(
            array(
                Stephino_Rpg_Db_Model_Ptfs::PTF_DEF_VERSION => $ptfVersion,
                Stephino_Rpg_Db_Model_Ptfs::PTF_DEF_NAME    => isset($data[self::REQUEST_PTF_NAME]) 
                    ? trim($data[self::REQUEST_PTF_NAME]) 
                    : $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_NAME],
                Stephino_Rpg_Db_Model_Ptfs::PTF_DEF_WIDTH   => isset($data[self::REQUEST_PTF_WIDTH]) 
                    ? intval($data[self::REQUEST_PTF_WIDTH]) 
                    : Stephino_Rpg_Db_Table_Ptfs::PTF_MIN_WIDTH,
                Stephino_Rpg_Db_Model_Ptfs::PTF_DEF_HEIGHT  => isset($data[self::REQUEST_PTF_HEIGHT]) 
                    ? intval($data[self::REQUEST_PTF_HEIGHT]) 
                    : Stephino_Rpg_Db_Table_Ptfs::PTF_MIN_HEIGHT,
            ), 
            $tileSetC,
            true
        );
        
        // Prepare the update data
        $ptfUpdateData = array(
            Stephino_Rpg_Db_Table_Ptfs::COL_PTF_NAME       => $ptfDef[Stephino_Rpg_Db_Model_Ptfs::PTF_DEF_NAME],
            Stephino_Rpg_Db_Table_Ptfs::COL_PTF_WIDTH      => $ptfDef[Stephino_Rpg_Db_Model_Ptfs::PTF_DEF_WIDTH],
            Stephino_Rpg_Db_Table_Ptfs::COL_PTF_HEIGHT     => $ptfDef[Stephino_Rpg_Db_Model_Ptfs::PTF_DEF_HEIGHT],
            Stephino_Rpg_Db_Table_Ptfs::COL_PTF_CONTENT    => json_encode($tileSetC),
            Stephino_Rpg_Db_Table_Ptfs::COL_PTF_VISIBILITY => $ptfVisibility,
            Stephino_Rpg_Db_Table_Ptfs::COL_PTF_VERSION    => $ptfVersion,
        );
        
        // Submit for re-review; got to here if the game was not suspended
        if (!Stephino_Rpg_Cache_User::get()->isGameMaster()) {
            $ptfUpdateData[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_REVIEW] = Stephino_Rpg_Db_Table_Ptfs::PTF_REVIEW_PENDING;
        }
        
        // Update the platformer
        $result = Stephino_Rpg_Db::get()->tablePtfs()->updateById($ptfUpdateData, $ptfId);
        
        // Could not update
        if (false === $result) {
            throw new Exception(__('Could not update game. Please try again later.', 'stephino-rpg'));
        }
        return $result;
    }
    
    /**
     * Mark the start of a platformer game; optionally, echo the current game's details fragment
     * 
     * @param array $data Data containing <ul>
     * <li><b>self::REQUEST_PTF_ID</b> (int) Platformer ID</li>
     * </ul>
     * @param boolean $showFragment (optional) Echo the game page details fragment; default <b>true</b>
     * @throws Exception
     * @return int|array Platformer ID if <b>$showFragment</b>, array of <ul>
     * <li>(array) ptfRow</li>
     * <li>(boolean) ptfOwn</li>
     * <li>(int) authorId</li>
     * <li>(string) authorName</li>
     * </ul> otherwise
     */
    public static function ajaxPtfStarted($data, $showFragment = true) {
        if (!Stephino_Rpg_Config::get()->core()->getPtfEnabled()) {
            throw new Exception(__('The arena is not available', 'stephino-rpg'));
        }
        
        // Get the current user ID
        $userId = Stephino_Rpg_TimeLapse::get()->userId();
        
        // Get the platformer ID
        $ptfId = isset($data[self::REQUEST_PTF_ID]) ? intval($data[self::REQUEST_PTF_ID]) : 0;
        
        // Invalid platformer
        if (!is_array($ptfRow = Stephino_Rpg_Db::get()->modelPtfs()->getById($ptfId, $userId))) {
            throw new Exception(__('Game not found', 'stephino-rpg'));
        }
        
        // Validate the tiles
        if (!is_array(Stephino_Rpg_Db::get()->modelPtfs()->getTileSet($ptfRow))) {
            throw new Exception(__('Invalid game', 'stephino-rpg'));
        }
        
        // This is my platformer
        $ptfOwn = ($userId === (int) $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_USER_ID]);
        
        // Get the author name
        $authorId = (int) $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_USER_ID];
        $authorName = $authorId > 0 
            ? Stephino_Rpg_Utils_Lingo::getUserName(Stephino_Rpg_Db::get()->tableUsers()->getById($authorId))
            : null;
        
        // Show the game arena details fragment
        if ($showFragment) {
            // Editing rights
            $ptfEditable = Stephino_Rpg_Db::get()->modelPtfs()->playerCanEdit(
                $userId, 
                Stephino_Rpg_Cache_User::get()->isGameMaster(),
                $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_USER_ID], 
                $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_REVIEW]
            );
            
            require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                Stephino_Rpg_Renderer_Ajax_Dialog_User::TEMPLATE_ARENA_PLAY_DETAILS
            );
        }
        
        // Mark the start of the game
        Stephino_Rpg_Db::get()->modelPtfs()->play($ptfId);
        return $showFragment 
            ? $ptfId 
            // Pass along information needed in the fragment details to the parent
            : array($ptfRow, $ptfOwn, $authorId, $authorName);
    }
    
    /**
     * Store the platformer finished result
     * 
     * @param array $data Data containing <ul>
     * <li><b>self::REQUEST_PTF_ID</b> (int) Platformer ID</li>
     * <li><b>self::REQUEST_PTF_WON</b> (boolean) Platformer Won</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxPtfFinished($data) {
        if (!Stephino_Rpg_Config::get()->core()->getPtfEnabled()) {
            throw new Exception(__('The arena is not available', 'stephino-rpg'));
        }
        
        // Prepare the playe reward
        $playerReward = 0;
        
        // Get the current user ID
        $userData = Stephino_Rpg_TimeLapse::get()->userData();
        $playerId = (int) $userData[Stephino_Rpg_Db_Table_Users::COL_ID];
        
        // Get the platformer ID
        $ptfId = isset($data[self::REQUEST_PTF_ID]) ? intval($data[self::REQUEST_PTF_ID]) : 0;

        // Not the win scenario
        $ptfWon = isset($data[self::REQUEST_PTF_ID]) ? boolval($data[self::REQUEST_PTF_WON]) : false;

        // No reward available
        if ($ptfWon) {
            // Invalid platformer ID
            if ($ptfId <= 0) {
                throw new Exception(__('Invalid game ID', 'stephino-rpg'));
            }

            // Invalid platformer
            if (!is_array($ptfRow = Stephino_Rpg_Db::get()->tablePtfs()->getById($ptfId))) {
                throw new Exception(__('Game not found', 'stephino-rpg'));
            }

            // Get the author ID
            $authorId = (int) $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_USER_ID];

            // No reward
            $playerReward = 0;
            
            // Don't profit playing own games
            if ($playerId != $authorId) {
                // Get the rewards
                list($playerReward, $authorReward) = Stephino_Rpg_Db::get()->modelPtfs()->getRewards($playerId, $ptfId);
            
                // Player rewards
                Stephino_Rpg_Db::get()->modelPtfs()->reward($playerId, $playerReward);
                
                // Author royalties
                Stephino_Rpg_Db::get()->modelPtfs()->reward($authorId, $authorReward, $ptfId, $playerId);
            }
            
            // Total score
            if (0 != Stephino_Rpg_Config::get()->core()->getPtfScore()) {
                Stephino_Rpg_Db::get()->tableUsers()->updateById(
                    array(
                        Stephino_Rpg_Db_Table_Users::COL_USER_SCORE => 
                            $userData[Stephino_Rpg_Db_Table_Users::COL_USER_SCORE] 
                            + Stephino_Rpg_Config::get()->core()->getPtfScore()
                    ), 
                    $playerId
                );
            }
        }
        
        // Mark the Finish
        Stephino_Rpg_Db::get()->modelPtfs()->play($ptfId, false, $ptfWon);
        return $playerReward;
    }
}

/*EOF*/