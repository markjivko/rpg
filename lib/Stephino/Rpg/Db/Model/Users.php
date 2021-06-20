<?php
/**
 * Stephino_Rpg_Db_Model_Users
 * 
 * @title     Model:Users
 * @desc      Users Model
 * @copyright (c) 2021, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Db_Model_Users extends Stephino_Rpg_Db_Model {

    /**
     * Users Model Name
     */
    const NAME = 'users';
    
    // Leader board constants
    const LEADER_BOARD_USER_WP_ICON = 'lb_user_wp_icon';
    const LEADER_BOARD_USER_WP_NAME = 'lb_user_wp_name';
    const LEADER_BOARD_USER_LIVE    = 'lb_user_live';
    const LEADER_BOARD_USER_JOINED  = 'lb_user_joined';
    
    // Maximum lengths
    const MAX_LENGTH_NAME = 60;
    const MAX_LENGTH_BIO  = 250;

    /**
     * Delete a user and all associated data: <ul>
     * <li>cities
     *     <ul>
     *         <li>islands (mark as not full)</li>
     *         <li>buildings
     *             <ul>
     *                 <li>entities</li>
     *             </ul>
     *         </li>
     *         <li>queues</li>
     *         <li>convoys</li>
     *      </ul>
     * </li>
     * <li>messages</li>
     * <li>research fields</li>
     * </ul>
     * 
     * @param int     $userId   User ID
     * @param boolean $removeWp (optional) Remove the WP account as well; default <b>false</b>
     * @throws Exception
     */
    public function delete($userId, $removeWp = false) {
        // Remove the Cities
        $this->getDb()->modelCities()->deleteByUser($userId);
        
        // Remove the Messages
        $this->getDb()->modelMessages()->deleteByUser($userId);
        
        // Remove Research Fields
        $this->getDb()->modelResearchFields()->deleteByUser($userId);
        
        // Remove platformers
        $this->getDb()->modelPtfs()->deleteByUser($userId);
        
        // Get the user data
        $userData = ($removeWp ? $this->getDb()->tableUsers()->getById($userId) : null);
        
        // Remove the user
        $this->getDb()->tableUsers()->deleteById($userId);
        
        // Get the WordPress user ID
        if (is_array($userData) && isset($userData[Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID])) {
            $wpUserId = abs((int) $userData[Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID]);
            
            // Attempting to remove a Super Admin account
            if (Stephino_Rpg_Cache_User::get()->isGameAdmin($wpUserId)) {
                throw new Exception(__('Super Admin accounts cannot be deleted', 'stephino-rpg'));
            }
        
            // Include required WordPress files
            include_once(ABSPATH . WPINC . '/post.php' ); // wp_delete_post
            include_once(ABSPATH . 'wp-admin/includes/bookmark.php' ); // wp_delete_link
            include_once(ABSPATH . 'wp-admin/includes/comment.php' ); // wp_delete_comment
            include_once(ABSPATH . 'wp-admin/includes/user.php' ); // wp_delete_user
            if (is_multisite()) {
                include_once(ABSPATH . WPINC . '/ms-functions.php' ); // remove_user_from_blog
                include_once(ABSPATH . 'wp-admin/includes/ms.php' ); // wpmu_delete_user
            }

            // Prepare the comments list
            $comments = $this->getDb()->getWpDb()->get_results(
                Stephino_Rpg_Utils_Db::selectAll(
                    $this->getDb()->getWpDb()->comments,
                    array(
                        'user_id' => $wpUserId
                    )
                ), 
                ARRAY_A
            );
            if (is_array($comments) && count($comments)) {
                foreach ($comments as $comment) {
                    wp_delete_comment($comment['comment_ID'], true);
                }
            }

            // Delete user
            if (is_multisite()) {
                wpmu_delete_user($wpUserId);
            } else {
                wp_delete_user($wpUserId);
            }
        }
    }
}

/* EOF */