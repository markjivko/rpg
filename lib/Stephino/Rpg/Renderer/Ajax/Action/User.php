<?php

/**
 * Stephino_Rpg_Renderer_Ajax_Action_User
 * 
 * @title      Action::User
 * @desc       User actions
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Renderer_Ajax_Action_User extends Stephino_Rpg_Renderer_Ajax_Action {

    // Request keys
    const REQUEST_TRADE_GEM      = 'tradeGem';
    const REQUEST_TRADE_TYPE     = 'tradeType';
    const REQUEST_PTF_ID         = 'ptfId';
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
        
        // All done
        return Stephino_Rpg_Renderer_Ajax::wrap($result);
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
        
        // Confirm the author
        if ($userId != $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_USER_ID]) {
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
        
        // Update the platformer
        $result = Stephino_Rpg_Db::get()->tablePtfs()->updateById(
            array(
                Stephino_Rpg_Db_Table_Ptfs::COL_PTF_NAME       => $ptfDef[Stephino_Rpg_Db_Model_Ptfs::PTF_DEF_NAME],
                Stephino_Rpg_Db_Table_Ptfs::COL_PTF_WIDTH      => $ptfDef[Stephino_Rpg_Db_Model_Ptfs::PTF_DEF_WIDTH],
                Stephino_Rpg_Db_Table_Ptfs::COL_PTF_HEIGHT     => $ptfDef[Stephino_Rpg_Db_Model_Ptfs::PTF_DEF_HEIGHT],
                Stephino_Rpg_Db_Table_Ptfs::COL_PTF_CONTENT    => json_encode($tileSetC),
                Stephino_Rpg_Db_Table_Ptfs::COL_PTF_VISIBILITY => $ptfVisibility,
                Stephino_Rpg_Db_Table_Ptfs::COL_PTF_VERSION    => $ptfVersion,
            ), 
            $ptfId
        );
        
        // Could not update
        if (false === $result) {
            throw new Exception(__('Could not update game. Please try again later.', 'stephino-rpg'));
        }
    }
    
    /**
     * Store the platformer finished result
     * 
     * @param array $data Data containing <ul>
     * <li><b>self::REQUEST_PTF_ID</b> (int) Platformer ID</li>
     * <li><b>self::REQUEST_PTF_WON</b> (boolean) Platformer Won</li>
     * </ul>
     */
    public static function ajaxPtfFinished($data) {
        if (!Stephino_Rpg_Config::get()->core()->getPtfEnabled()) {
            throw new Exception(__('The arena is not available', 'stephino-rpg'));
        }
        
        // Prepare the playe reward
        $playerReward = 0;
        
        // Get the current user ID
        $playerId = Stephino_Rpg_TimeLapse::get()->userId();
        
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

            // Get the rewards
            list($playerReward, $authorReward) = Stephino_Rpg_Db::get()->modelPtfs()->getRewards($playerId, $ptfId);

            // Player rewards
            Stephino_Rpg_Db::get()->modelPtfs()->reward($playerId, $playerReward);
            
            // Author royalties
            Stephino_Rpg_Db::get()->modelPtfs()->reward($authorId, $authorReward, $ptfId, $playerId);
        }
        
        // Mark the Finish
        Stephino_Rpg_Db::get()->modelPtfs()->play($ptfId, $playerId, false, $ptfWon);
        return $playerReward;
    }
}

/*EOF*/