<?php

/**
 * Stephino_Rpg_Db_Table_Ptfs
 * 
 * @title      Table:Ptfs
 * @desc       Holds the platformer definitions
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Db_Table_Ptfs extends Stephino_Rpg_Db_Table {
    
    // Platformer visibility
    const PTF_VISIBILITY_PUBLIC  = 'l';
    const PTF_VISIBILITY_PRIVATE = 'p';
    
    // Platformer review status
    const PTF_REVIEW_PENDING                     = 'p';
    const PTF_REVIEW_APPROVED                    = 'a';
    const PTF_REVIEW_REJECTED_TITLE_SPAM         = 'rts';
    const PTF_REVIEW_REJECTED_TITLE_INVALID      = 'rti';
    const PTF_REVIEW_REJECTED_CONTENT_SPAM       = 'rcs';
    const PTF_REVIEW_REJECTED_CONTENT_TOO_EASY   = 'rce';
    const PTF_REVIEW_REJECTED_CONTENT_IMPOSSIBLE = 'rci';
    const PTF_REVIEW_SUSPENDED                   = 's';
    
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
     * Platformer review
     * 
     * @var string <ul>
     *     <li><b>'a'</b> for approved</li>
     *     <li><b>'p'</b> for pending</li>
     *     <li><b>'rts'</b> for rejected: title spam</li>
     *     <li><b>'rti'</b> for rejected: title invalid</li>
     *     <li><b>'rcs'</b> for rejected: content spam</li>
     *     <li><b>'rce'</b> for rejected: content too easy</li>
     *     <li><b>'rci'</b> for rejected: content impossible</li>
     *     <li><b>'s'</b> for suspended</li>
     * </ul>
     */
    const COL_PTF_REVIEW = 'ptf_review';
    
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
    `" . self::COL_PTF_REVIEW . "` varchar(3) NOT NULL DEFAULT '" . self::PTF_REVIEW_APPROVED . "',
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
            $result = $this->getDb()->getWpDb()->query(
                Stephino_Rpg_Utils_Db::insert(
                    $this->getTableName(), 
                    array(
                        self::COL_PTF_USER_ID       => abs((int) $userId),
                        self::COL_PTF_NAME          => trim($name),
                        self::COL_PTF_WIDTH         => abs((int) $tileSetWidth),
                        self::COL_PTF_HEIGHT        => abs((int) $tileSetHeight),
                        self::COL_PTF_CONTENT       => json_encode($tileSetC),
                        self::COL_PTF_CREATED_TIME  => $timestamp,
                        self::COL_PTF_MODIFIED_TIME => $timestamp,
                        self::COL_PTF_REVIEW        => Stephino_Rpg_Cache_User::get()->isGameMaster()
                            ? self::PTF_REVIEW_APPROVED
                            : self::PTF_REVIEW_PENDING
                    )
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
            Stephino_Rpg_Utils_Db::delete(
                $this->getTableName(), 
                array(
                    self::COL_PTF_USER_ID => abs((int) $userId)
                )
            )
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
            Stephino_Rpg_Utils_Db::selectAll(
                $this->getTableName(), 
                $preDefined
                    ? array(
                        self::COL_PTF_USER_ID => 0
                    )
                    : null
            ),
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
     * @param int     $userId      User ID
     * @param boolean $viewAll     (optional) View all results (no review filtering); default <b>true</b>
     * @param string  $orderBy     (optional) Column to order by; default <b>null</b>
     * @param boolean $orderAsc    (optional) Order in ASC or DESC order; default <b>true</b>
     * @param int     $limitCount  (optional) Limit count; default <b>null</b>
     * @param int     $limitOffset (optional) Limit offset; default <b>null</b>
     * @return array List of platformers; array may be empty
     */
    public function getForUserId($userId, $viewAll = true, $orderBy = null, $orderAsc = true, $limitCount = null, $limitOffset = null) {
        $result = array();
        
        // Sanitize the user ID
        $userId = abs((int) $userId);
        
        // User 0 is reserved for pre-defined platformers
        if ($userId > 0) {
            // Sanitize the order column
            if (null !== $orderBy) {
                $columnsList = array_filter(
                    (new ReflectionClass($this))->getConstants(),
                    function($constantName) {
                        return preg_match('%^COL_%', $constantName);
                    },
                    ARRAY_FILTER_USE_KEY
                );
                if (!in_array($orderBy, $columnsList)) {
                    $orderBy = null;
                }
                $orderAsc = !!$orderAsc;
            }

            // Sanitize the limit
            if (null !== $limitCount) {
                $limitCount = abs((int) $limitCount);
            }
            if (null !== $limitOffset) {
                $limitOffset = abs((int) $limitOffset);
            }
            
            // Prepare the query
            $query = "SELECT * FROM `$this` " . PHP_EOL
                . "WHERE `" . self::COL_PTF_USER_ID . "` = $userId"
                    . " OR (" 
                        . " `" . self::COL_PTF_VISIBILITY. "` = '" . self::PTF_VISIBILITY_PUBLIC . "'" 
                        . ($viewAll ? '' : " AND `" . self::COL_PTF_REVIEW . "` = '" . self::PTF_REVIEW_APPROVED  . "'")
                    . " )"
                . (null !== $orderBy
                    ? ' ' . PHP_EOL . "  ORDER BY `$orderBy` " . ($orderAsc ? 'ASC' : 'DESC')
                    : ''
                )
                . (null !== $limitCount
                    ? ' ' . PHP_EOL . '  LIMIT ' . (null !== $limitOffset ? ($limitOffset . ', ') : '') . $limitCount
                    : ''
                );
            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($query . PHP_EOL);
            
            // Get the rows
            $result = $this->getDb()->getWpDb()->get_results($query, ARRAY_A);
        }
        
        return is_array($result) ? $result : array();
    }
    
    /**
     * Get all the platformers this user can play (count):<ul>
     * <li>Own platformers (live or private)</li>
     * <li>Other live platformers</li>
     * </ul>
     * 
     * @param int     $userId  User ID
     * @param boolean $viewAll (optional) View all results (no review filtering); default <b>true</b>
     * @return int Number of platformers
     */
    public function getCountForUserId($userId, $viewAll = true) {
        $result = 0;
        
        // Sanitize the user ID
        $userId = abs((int) $userId);
        if ($userId > 0) {
            $query = "SELECT COUNT(`" . self::COL_ID . "`) as `count` FROM `$this` " . PHP_EOL
                . "WHERE `" . self::COL_PTF_USER_ID  . "` = $userId"
                    . " OR (" 
                        . " `" . self::COL_PTF_VISIBILITY. "` = '" . self::PTF_VISIBILITY_PUBLIC . "'" 
                        . ($viewAll ? '' : " AND `" . self::COL_PTF_REVIEW . "` = '" . self::PTF_REVIEW_APPROVED . "'")
                    . " )";
            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($query . PHP_EOL);
            
            // Gt the DB row
            $dbRow = $this->getDb()->getWpDb()->get_row($query, ARRAY_A);

            // Valid result
            if (is_array($dbRow) && isset($dbRow['count'])) {
                $result = intval($dbRow['count']);
            }
        }
        
        return $result;
    }
    
    /**
     * Get all the platformers this user has authored
     * 
     * @param int     $userId      User ID
     * @param boolean $viewAll     (optional) View all results (no review filtering); default <b>true</b>
     * @param string  $orderBy     (optional) Column to order by; default <b>null</b>
     * @param boolean $orderAsc    (optional) Order in ASC or DESC order; default <b>true</b>
     * @param int     $limitCount  (optional) Limit count; default <b>null</b>
     * @param int     $limitOffset (optional) Limit offset; default <b>null</b>
     * @return array List of platformers; array may be empty
     */
    public function getByUserId($userId, $viewAll = true, $orderBy = null, $orderAsc = true, $limitCount = null, $limitOffset = null) {
        $result = array();
        
        // Sanitize the user ID
        $userId = abs((int) $userId);
        
        // User 0 is reserved for pre-defined platformers
        if ($userId > 0) {
            // Sanitize the order column
            if (null !== $orderBy) {
                $columnsList = array_filter(
                    (new ReflectionClass($this))->getConstants(),
                    function($constantName) {
                        return preg_match('%^COL_%', $constantName);
                    },
                    ARRAY_FILTER_USE_KEY
                );
                if (!in_array($orderBy, $columnsList)) {
                    $orderBy = null;
                }
                $orderAsc = !!$orderAsc;
            }

            // Sanitize the limit
            if (null !== $limitCount) {
                $limitCount = abs((int) $limitCount);
            }
            if (null !== $limitOffset) {
                $limitOffset = abs((int) $limitOffset);
            }
            
            // Get the rows
            $result = $this->getDb()->getWpDb()->get_results(
                Stephino_Rpg_Utils_Db::selectAll(
                    $this->getTableName(),
                    $viewAll
                        ? array(
                            self::COL_PTF_USER_ID => $userId,
                        )
                        : array(
                            self::COL_PTF_USER_ID => $userId,
                            self::COL_PTF_REVIEW  => self::PTF_REVIEW_APPROVED
                        ),
                    $limitCount,
                    $limitOffset,
                    $orderBy,
                    $orderAsc
                ), 
                ARRAY_A
            );
        }
        
        return is_array($result) ? $result : array();
    }
    
    /**
     * Get all the platformers this user has authored (count)
     * 
     * @param int     $userId  User ID
     * @param boolean $viewAll (optional) View all results (no review filtering); default <b>true</b>
     * @return int Number of platformers
     */
    public function getCountByUserId($userId, $viewAll = true) {
        $result = 0;
        
        // Sanitize the user ID
        $userId = abs((int) $userId);
        if ($userId > 0) {
            // Prepare the query
            $query = "SELECT COUNT(`" . self::COL_ID . "`) as `count` FROM `$this` " . PHP_EOL
                . "WHERE `" . self::COL_PTF_USER_ID  . "` = $userId" 
                    . ($viewAll ? '' : " AND `" . self::COL_PTF_REVIEW . "` = '" . self::PTF_REVIEW_APPROVED . "'");
            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($query . PHP_EOL);
            
            // Get the DB row
            $dbRow = $this->getDb()->getWpDb()->get_row($query, ARRAY_A);

            // Valid result
            if (is_array($dbRow) && isset($dbRow['count'])) {
                $result = intval($dbRow['count']);
            }
        }
        
        return $result;
    }
    
    /**
     * Get all the suspended platformers this user has authored (count)
     * 
     * @param int     $userId  User ID
     * @return int Number of platformers
     */
    public function getCountSuspended($userId) {
        $result = 0;
        
        // Sanitize the user ID
        $userId = abs((int) $userId);
        if ($userId > 0) {
            // Prepare the query
            $query = "SELECT COUNT(`" . self::COL_ID . "`) as `count` FROM `$this` " . PHP_EOL
                . "WHERE `" . self::COL_PTF_USER_ID  . "` = $userId" 
                    . " AND `" . self::COL_PTF_REVIEW  . "` = '" . self::PTF_REVIEW_SUSPENDED . "'";
            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($query . PHP_EOL);
            
            // Get the DB row
            $dbRow = $this->getDb()->getWpDb()->get_row($query, ARRAY_A);

            // Valid result
            if (is_array($dbRow) && isset($dbRow['count'])) {
                $result = intval($dbRow['count']);
            }
        }
        
        return $result;
    }
}

/*EOF*/