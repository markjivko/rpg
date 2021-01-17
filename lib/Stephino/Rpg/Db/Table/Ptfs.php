<?php

/**
 * Stephino_Rpg_Db_Table_Ptfs
 * 
 * @title      Table:Ptfs
 * @desc       Holds the platformer definitions
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Db_Table_Ptfs extends Stephino_Rpg_Db_Table {
    
    // Platformer visibility
    const PTF_VISIBILITY_PUBLIC  = 'l';
    const PTF_VISIBILITY_PRIVATE = 'p';
    
    // Minimum dimentions
    const PTF_MIN_WIDTH  = 26;
    const PTF_MIN_HEIGHT = 15;
    const PTF_MAX_WIDTH  = 250;
    const PTF_MAX_HEIGHT = 150;
    
    // Allowed visibility values
    const PTF_VISIBILITIES = array(
        self::PTF_VISIBILITY_PRIVATE,
        self::PTF_VISIBILITY_PUBLIC,
    );
    
    /**
     * Platformers Table Name
     */
    const NAME = 'ptfs';
    
    /**
     * Platformer ID
     * 
     * @var int
     */
    const COL_ID = 'ptf_id';
    
    /**
     * Platformer Author
     * 
     * @var int Author ID
     */
    const COL_PTF_USER_ID = 'ptf_user_id';
    
    /**
     * Platformer name
     * 
     * @var string
     */
    const COL_PTF_NAME = 'ptf_name';
    
    /**
     * Platformer content
     * 
     * @var string
     */
    const COL_PTF_CONTENT = 'ptf_content';
    
    /**
     * Platformer width
     * 
     * @var int
     */
    const COL_PTF_WIDTH = 'ptf_width';
    
    /**
     * Platformer height
     * 
     * @var int
     */
    const COL_PTF_HEIGHT = 'ptf_height';
    
    /**
     * Platformer version
     * 
     * @var int
     */
    const COL_PTF_VERSION = 'ptf_version';
    
    /**
     * Platformer Creation Time
     * 
     * @var int UNIX Timestamp
     */
    const COL_PTF_CREATED_TIME = 'ptf_created_time';
    
    /**
     * Platformer Modification Time
     * 
     * @var int UNIX Timestamp
     */
    const COL_PTF_MODIFIED_TIME = 'ptf_modified_time';

    /**
     * Total number of times the platformer was started
     * 
     * @var int Platformer Started
     */
    const COL_PTF_STARTED = 'ptf_started';
    
    /**
     * Total number of times the platformer was completed (win/fail)
     * 
     * @var int Platformer Finished
     */
    const COL_PTF_FINISHED = 'ptf_finished';
    
    /**
     * Total number of times the platformer was won
     * 
     * @var int Platformer Victories
     */
    const COL_PTF_FINISHED_WON = 'ptf_finished_won';
    
    /**
     * Average rating [0,5]
     * 
     * @var float Rating value
     */
    const COL_PTF_RATING = 'ptf_rating';
    
    /**
     * Total number of ratings
     * 
     * @var int Ratings
     */
    const COL_PTF_RATING_COUNT = 'ptf_rating_count';
    
    /**
     * Platformer visibility
     * 
     * @var string <ul>
     *     <li><b>'l'</b> for Public (Live)</li>
     *     <li><b>'p'</b> for Private</li>
     * </ul>
     */
    const COL_PTF_VISIBILITY = 'ptf_visibility';
    
    /**
     * Table creation SQL statement
     * 
     * @return string
     */
    public function getCreateStatement() {
        return "CREATE TABLE `$this` (
    `" . self::COL_ID . "` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `" . self::COL_PTF_USER_ID . "` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_PTF_NAME . "` varchar(64) NOT NULL DEFAULT '',
    `" . self::COL_PTF_CONTENT . "` text NOT NULL DEFAULT '',
    `" . self::COL_PTF_WIDTH . "` int(3) UNSIGNED NOT NULL DEFAULT '" . self::PTF_MIN_WIDTH . "',
    `" . self::COL_PTF_HEIGHT . "` int(3) UNSIGNED NOT NULL DEFAULT '" . self::PTF_MIN_HEIGHT . "',
    `" . self::COL_PTF_VERSION . "` int(11) UNSIGNED NOT NULL DEFAULT '1',
    `" . self::COL_PTF_CREATED_TIME . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_PTF_MODIFIED_TIME . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_PTF_STARTED . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_PTF_FINISHED . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_PTF_FINISHED_WON . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_PTF_RATING . "` decimal(6,4) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_PTF_RATING_COUNT . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_PTF_VISIBILITY . "` char(1) NOT NULL DEFAULT '" . self::PTF_VISIBILITY_PRIVATE . "',
    UNIQUE KEY `" . self::COL_ID . "` (`" . self::COL_ID . "`)
);";
    }
    
    /**
     * Create an empty platformer
     * 
     * @param int    $userId        User ID [1,]
     * @param string $name          Platformer name [3,64] characters long
     * @param int    $tileSetWidth  Platformer width [self::PTF_MIN_WIDTH,self::PTF_MAX_WIDTH]
     * @param int    $tileSetHeight Platformer height [self::PTF_MIN_HEIGHT,self::PTF_MAX_HEIGHT]
     * @param array  $tileSetC      <b>Compressed</b> tile set, array of integers
     * @return int|null New Platformer ID or Null on error
     */
    public function create($userId, $name, $tileSetWidth, $tileSetHeight, $tileSetC) {
        $result = false;
        
        // Clean-up the user ID
        $userId = abs((int) $userId);
        if ($userId >= 1) {
            $timestamp = time();

            // Prepare the result
            $result = $this->getDb()->getWpDb()->insert(
                $this->getTableName(), 
                array(
                    self::COL_PTF_USER_ID => $userId,
                    self::COL_PTF_NAME      => $name,
                    self::COL_PTF_WIDTH     => $tileSetWidth,
                    self::COL_PTF_HEIGHT    => $tileSetHeight,
                    self::COL_PTF_CONTENT   => json_encode($tileSetC),
                    self::COL_PTF_CREATED_TIME   => $timestamp,
                    self::COL_PTF_MODIFIED_TIME  => $timestamp,
                )
            );
        }
        
        // Get the new city ID
        return (false !== $result ? $this->getDb()->getWpDb()->insert_id : null);
    }
    
    /**
     * Delete all platformers belonging to this user
     * 
     * @param int $userId User ID
     * @return int|false Number of rows deleted or false on error
     */
    public function deleteByUser($userId) {
        return $this->getDb()->getWpDb()->query(
            "DELETE FROM `$this`"
            . " WHERE  `" . self::COL_PTF_USER_ID . "` = '" . abs((int) $userId) . "'"
        );
    }
    
    /**
     * Get a list of all platformers
     * 
     * @param boolean $preDefined (optional) Get the pre-defined platformers only; default <b>false</b>
     * @return array List of platformers; array may be empty
     */
    public function getAll($preDefined = false) {
        $result = $this->getDb()->getWpDb()->get_results(
            "SELECT * FROM `" . $this->getTableName() . "`"
            . ($preDefined ? " WHERE `" . self::COL_PTF_USER_ID . "` = '0'" : ''),
            ARRAY_A
        );
        
        return is_array($result) ? $result : array();
    }
    
    /**
     * Get all the platformers this user can play:<ul>
     * <li>Own platformers (live or private)</li>
     * <li>Other live platformers</li>
     * </ul>
     * 
     * @param int $userId User ID
     * @return array List of platformers; array may be empty
     */
    public function getForUserId($userId) {
        $result = array();
        
        // Sanitize the user ID
        $userId = abs((int) $userId);
        
        // User 0 is reserved for pre-defined platformers
        if ($userId > 0) {
            $result = $this->getDb()->getWpDb()->get_results(
                "SELECT * FROM `" . $this->getTableName() . "`"
                . " WHERE `" . self::COL_PTF_USER_ID . "` = '$userId'"
                . " OR `" . self::COL_PTF_VISIBILITY. "` = '" . self::PTF_VISIBILITY_PUBLIC . "'",
                ARRAY_A
            );
        }
        
        return is_array($result) ? $result : array();
    }
    
    /**
     * Get all the platformers this user has authored
     * 
     * @param int $userId User ID
     * @return array List of platformers; array may be empty
     */
    public function getByUserId($userId) {
        $result = array();
        
        // Sanitize the user ID
        $userId = abs((int) $userId);
        
        // User 0 is reserved for pre-defined platformers
        if ($userId > 0) {
            $result = $this->getDb()->getWpDb()->get_results(
                "SELECT * FROM `" . $this->getTableName() . "`"
                . " WHERE `" . self::COL_PTF_USER_ID . "` = '$userId'",
                ARRAY_A
            );
        }
        
        return is_array($result) ? $result : array();
    }
}

/*EOF*/