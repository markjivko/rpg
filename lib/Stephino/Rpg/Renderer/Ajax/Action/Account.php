<?php

/**
 * Stephino_Rpg_Renderer_Ajax_Action_Account
 * 
 * @title      Action::Account
 * @desc       Account actions
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Renderer_Ajax_Action_Account extends Stephino_Rpg_Renderer_Ajax_Action {

    /**
     * Log out
     */
    public static function ajaxLogOut() {
        // Logout
        wp_logout();
    }
    
    /**
     * Permanently delete the current user account and all associated posts/links/comments
     * 
     * @throws Exception
     */
    public static function ajaxDelete() {
        if (null !== $userData = Stephino_Rpg_Db::get()->tableUsers()->getData()) {
            // Remove the user from the game completely
            Stephino_Rpg_Db::get()->modelUsers()->delete($userData[Stephino_Rpg_Db_Table_Users::COL_ID], true);
        }
        
        // Logout
        wp_logout();
    }
}

/*EOF*/