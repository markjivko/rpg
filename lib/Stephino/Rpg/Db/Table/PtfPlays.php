<?php

/**
 * Stephino_Rpg_Db_Table_PtfPlays
 * 
 * @title      Table:Ptfs
 * @desc       Holds the platformer plays data
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Db_Table_PtfPlays extends Stephino_Rpg_Db_Table {
    
    /**
     * Platformer Plays Table Name
     */
    const NAME = 'ptf_plays';
    
    /**
     * Platformer Play ID
     * 
     * @var int
     */
    const COL_ID = 'ptf_play_id';
    
    /**
     * Platformer
     * 
     * @var int Platformer ID
     */
    const COL_PTF_PLAY_PTF_ID = 'ptf_play_ptf_id';
    
    /**
     * Platformer Player
     * 
     * @var int Player ID
     */
    const COL_PTF_PLAY_USER_ID = 'ptf_play_user_id';
    
    /**
     * Times the platformer was started
     * 
     * @var int Started
     */
    const COL_PTF_PLAY_STARTED = 'ptf_play_started';
    
    /**
     * Last time the platformer was started
     * 
     * @var int UNIX Timestamp
     */
    const COL_PTF_PLAY_STARTED_TIME = 'ptf_play_started_time';
    
    /**
     * Times the platformer was finished (win + lose)
     * 
     * @var int Finished
     */
    const COL_PTF_PLAY_FINISHED = 'ptf_play_finished';
    
    /**
     * Number of times the platformer was won
     * 
     * @var int Victories
     */
    const COL_PTF_PLAY_WON = 'ptf_play_won';
    
    /**
     * Last time the platformer was won
     * 
     * @var int UNIX Timestamp
     */
    const COL_PTF_PLAY_WON_TIME = 'ptf_play_won_time';
    
    /**
     * Average rating [0,5]
     * 
     * @var float Rating value
     */
    const COL_PTF_PLAY_RATING = 'ptf_play_rating';
    
    /**
     * Total number of ratings
     * 
     * @var int Ratings
     */
    const COL_PTF_PLAY_RATING_COUNT = 'ptf_play_rating_count';
    
    /**
     * Table creation SQL statement
     * 
     * @return string
     */
    public function getCreateStatement() {
        return "CREATE TABLE `$this` (
    `" . self::COL_ID . "` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `" . self::COL_PTF_PLAY_PTF_ID . "` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_PTF_PLAY_USER_ID . "` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_PTF_PLAY_STARTED . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_PTF_PLAY_STARTED_TIME . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_PTF_PLAY_FINISHED . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_PTF_PLAY_WON . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_PTF_PLAY_WON_TIME . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_PTF_PLAY_RATING . "` decimal(6,4) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_PTF_PLAY_RATING_COUNT . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    UNIQUE KEY `" . self::COL_ID . "` (`" . self::COL_ID . "`)
);";
    }
    
    /**
     * Create a new play, initializing the "started" and "started time" values
     * 
     * @param int $ptfId       Platformer Id
     * @param int $userId      User Id
     * @param int $currentTime (optional) Current time; defaults to time(); default <b>null</b>
     * @return int|null
     */
    public function create($ptfId, $userId, $currentTime = null) {
        if (null === $currentTime) {
            $currentTime = time();
        }
        
        // Prepare the insert data
        $insertData = array(
            self::COL_PTF_PLAY_PTF_ID       => abs((int) $ptfId),
            self::COL_PTF_PLAY_USER_ID      => abs((int) $userId),
            self::COL_PTF_PLAY_STARTED      => 1,
            self::COL_PTF_PLAY_STARTED_TIME => $currentTime,
        );
        
        // Create the entry
        $result = $this->getDb()->getWpDb()->insert($this->getTableName(), $insertData);
        
        // Get the new city ID
        return (false !== $result ? $this->getDb()->getWpDb()->insert_id : null);
    }
    
    /**
     * Get the PTFs this user has played
     * 
     * @param int $userId User ID
     * @return array May be empty
     */
    public function getByUserId($userId) {
        $result = array();
        
        // Sanitize the user ID
        $userId = abs((int) $userId);
        
        // User 0 is reserved for pre-defined platformers
        if ($userId > 0) {
            $result = $this->getDb()->getWpDb()->get_results(
                "SELECT * FROM `" . $this->getTableName() . "`"
                . " WHERE `" . self::COL_PTF_PLAY_USER_ID . "` = '$userId'",
                ARRAY_A
            );
        }
        
        return is_array($result) ? $result : array();
    }
    
    /**
     * Get this specific ptf played by this user
     * 
     * @param int $userId User ID
     * @param int $ptfId  Platformer ID
     * @return array|null
     */
    public function getByUserAndPtf($userId, $ptfId) {
        $result = null;
        
        // Sanitize the IDs
        $userId = abs((int) $userId);
        $ptfId = abs((int) $ptfId);
        
        // User 0 is reserved for pre-defined platformers
        if ($userId > 0 && $ptfId > 0) {
            $result = $this->getDb()->getWpDb()->get_row(
                "SELECT * FROM `" . $this->getTableName() . "`"
                . " WHERE `" . self::COL_PTF_PLAY_USER_ID . "` = '$userId'"
                . " AND `" . self::COL_PTF_PLAY_PTF_ID . "` = '$ptfId'",
                ARRAY_A
            );
        }
        
        return $result;
    }
}

/*EOF*/