<?php

/**
 * Stephino_Rpg_Db_Table_Users
 * 
 * @title      Table:Users
 * @desc       Holds the users information
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Db_Table_Users extends Stephino_Rpg_Db_Table {
    
    /**
     * Users Table Name
     */
    const NAME = 'users';
    
    /**
     * User ID
     * 
     * @var int
     */
    const COL_ID = 'user_id';
    
    /**
     * WordPress User ID
     * 
     * @var int|null Null for Robots
     */
    const COL_USER_WP_ID = 'user_wp_id';
    
    /**
     * Timestamp of user creation
     * 
     * @var int UNIX timestamp
     */
    const COL_USER_CREATED = 'user_created';
    
    /**
     * Gold
     * 
     * @var float
     */
    const COL_USER_RESOURCE_GOLD = 'user_resource_gold';
    
    /**
     * Research Points
     * 
     * @var float
     */
    const COL_USER_RESOURCE_RESEARCH = 'user_resource_research';
    
    /**
     * Gem
     * 
     * @var float
     */
    const COL_USER_RESOURCE_GEM = 'user_resource_gem';
    
    /**
     * Tutorial level
     * 
     * @var int
     */
    const COL_USER_TUTORIAL_LEVEL = 'user_tutorial_level';
    
    /**
     * Last time-lapse execution time
     * 
     * @var int UNIX timestamp
     */
    const COL_USER_LAST_TICK = 'user_last_tick';
    
    /**
     * Last time-lapse execution for robot crons
     * 
     * @var int UNIX timestamp
     */
    const COL_USER_LAST_TICK_ROBOT = 'user_last_tick_robot';
    
    /**
     * Last time-lapse execution time triggered by AJAX
     * 
     * @var int UNIX timestamp
     */
    const COL_USER_LAST_TICK_AJAX = 'user_last_tick_ajax';
    
    /**
     * Banned time
     * 
     * @var int UNIX timestamp
     */
    const COL_USER_BANNED = 'user_banned';
    
    /**
     * User settings
     * 
     * @var string JSON Object
     */
    const COL_USER_GAME_SETTINGS = 'user_game_settings';

    /**
     * User score
     * 
     * @var int
     */
    const COL_USER_SCORE = 'user_score';
    
    /**
     * Battles: Defeats
     * 
     * @var int
     */
    const COL_USER_BATTLE_DEFEATS = 'user_battle_defeats';
    
    /**
     * Battles: Draws
     * 
     * @var int
     */
    const COL_USER_BATTLE_DRAWS = 'user_battle_draws';
    
    /**
     * Battles: Victories
     * 
     * @var int
     */
    const COL_USER_BATTLE_VICTORIES = 'user_battle_victories';
    
    /**
     * PTF: Games played
     * 
     * @var int
     */
    const COL_USER_PTF_PLAYED = 'user_ptf_played';
    
    /**
     * PTF: Games won
     * 
     * @var int
     */
    const COL_USER_PTF_WON = 'user_ptf_won';
    
    /**
     * Current Game User data
     * 
     * @var array|null
     */
    protected $_userData = null;
    
    /**
     * Table creation SQL statement
     * 
     * @return string
     */
    public function getCreateStatement() {
        return "CREATE TABLE `$this` (
    `" . self::COL_ID . "` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `" . self::COL_USER_WP_ID . "` bigint(20) UNSIGNED,
    `" . self::COL_USER_CREATED . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_USER_SCORE . "` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_USER_BATTLE_VICTORIES . "` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_USER_BATTLE_DRAWS . "` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_USER_BATTLE_DEFEATS . "` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_USER_PTF_PLAYED . "` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_USER_PTF_WON . "` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_USER_RESOURCE_GOLD . "` decimal(24,4) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_USER_RESOURCE_RESEARCH . "` decimal(24,4) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_USER_RESOURCE_GEM . "` decimal(24,4) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_USER_TUTORIAL_LEVEL . "` int(4) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_USER_LAST_TICK . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_USER_LAST_TICK_ROBOT . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_USER_LAST_TICK_AJAX . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_USER_BANNED . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_USER_GAME_SETTINGS . "` text NOT NULL DEFAULT '',
    UNIQUE KEY `" . self::COL_ID . "` (`" . self::COL_ID . "`)
);";
    }

    /**
     * Create a new user; set the tutorial level to max. for robot accounts
     * 
     * @param boolean $isRobot (optional) Create a robot account; default <b>false</b>
     * @return int|null New User ID or Null on error
     */
    public function create($isRobot = false) {
        $currentTime = time();
        
        // Execute the query
        $insertResult = $this->getDb()->getWpDb()->query(
            Stephino_Rpg_Utils_Db::insert(
                $this->getTableName(), 
                array(
                    self::COL_USER_WP_ID             => ($isRobot ? null : ($this->getDb()->getRobotId() ? null : $this->getDb()->getWpUserId())),
                    self::COL_USER_RESOURCE_GOLD     => abs((int) Stephino_Rpg_Config::get()->core()->getInitialUserResourceGold()),
                    self::COL_USER_RESOURCE_RESEARCH => abs((int) Stephino_Rpg_Config::get()->core()->getInitialUserResourceResearch()),
                    self::COL_USER_RESOURCE_GEM      => abs((int) Stephino_Rpg_Config::get()->core()->getInitialUserResourceGem()),
                    self::COL_USER_CREATED           => $currentTime,
                    self::COL_USER_LAST_TICK         => $currentTime,
                    self::COL_USER_TUTORIAL_LEVEL    => ($isRobot ? count(Stephino_Rpg_Config::get()->tutorials()->getAll()) : 0),
                )
            )
        );

        return false === $insertResult ? null : $this->getDb()->getWpDb()->insert_id;
    }
    
    /**
     * Check which one of these users is a robot
     * 
     * @param int[] $userIds List of User IDs to check
     * @return int[] Array may be empty
     */
    public function filterRobots($userIds) {
        // Initialize the result
        $result = array();

        // Valid user IDs
        if (is_array($userIds) && count($userIds)) {
            $userIds = array_unique(
                array_map(
                    function($item) {
                        return abs((int) $item);
                    }, 
                    $userIds
                )
            );

            // Get the rows
            $robotRows = $this->getDb()->getWpDb()->get_results(
                Stephino_Rpg_Utils_Db::selectAll(
                    $this->getTableName(),
                    array(
                        self::COL_ID         => $userIds,
                        self::COL_USER_WP_ID => null
                    )
                ), 
                ARRAY_A
            );

            // Valid result
            if (is_array($robotRows) && count($robotRows)) {
                $result = array_map(
                    function($row) {
                        return abs((int) $row[self::COL_ID]);
                    }, 
                    $robotRows
                );
            }
        }
        
        return $result;
    }
    
    /**
     * Get a list of random users; optimized for large tables
     * 
     * @param int     $count     Result count, must be a positive integer
     * @param boolean $getRobots (optional) Get robot accounts; default <b>false</b>
     * @return array|null Array of rows or Null on error
     */
    public function getRandom($count, $getRobots = false) {
        // Prepare the result
        $result = null;
        
        // Sanitize the input
        $count = abs((int) $count);
        
        // Get the resouts
        if ($count > 0) {
            // Prepare the query
            $query = "SELECT MAX(`" . self::COL_ID . "`) AS `max` FROM `$this`";
            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($query . PHP_EOL);
            
            // Get the maximum number of users (robots and humans)
            $usersMax = $this->getDb()->getWpDb()->get_row($query, ARRAY_A);

            // Valid result
            if (is_array($usersMax) && isset($usersMax['max'])) {
                // Get the total number of rows
                $usersMaxId = intval($usersMax['max']);
                
                // Prepare the IDs list
                $userIds = array();
                
                // Get the robots per user
                $robotsPerUser = Stephino_Rpg_Config::get()->core()->getInitialRobotsPerUser() + 1;

                // Prepare the IDs list
                for ($i = 1; $i <= $count; $i++) {
                    $usersIdsStart = 1;
                    if ($usersMaxId > $robotsPerUser) {
                        $usersIdsStart = mt_rand(0, $usersMaxId - $robotsPerUser) + 1;
                    }
                    foreach (range($usersIdsStart, $usersIdsStart + $robotsPerUser - 1) as $rangeId) {
                        $userIds[] = $rangeId;
                    }
                }
                        
                // Get the result
                $dbRows = $this->getDb()->getWpDb()->get_results(
                    Stephino_Rpg_Utils_Db::selectAll(
                        $this->getTableName(),
                        array(
                            self::COL_ID         => array_unique($userIds),
                            self::COL_USER_WP_ID => $getRobots ? null : true,
                        )
                    ), 
                    ARRAY_A
                );
                
                // Local shuffling is much faster than MySQL's "ORDER BY RAND()"
                if (is_array($dbRows) && count($dbRows)) {
                    // Pre-shuffling the user IDs does not work as MySQL returns the results in order
                    shuffle($dbRows);
                    
                    // Implement the "LIMIT" locally
                    $result = array_slice($dbRows, 0, $count, false);
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Get the current game user information (cached)
     * 
     * @return array|null
     */
    public function getData() {
        // Check initialization
        return !$this->_init() ? null : $this->_userData;
    }

    /**
     * Get a user's place on the leader board by score
     * 
     * @param int $userScore User Score
     * @return int
     */
    public function getPlace($userScore) {
        // Prepare the result
        $result = 1;
        
        // Sanitize the user score
        $userScore = abs((int) $userScore);
        
        // Prepare the query
        $query = "SELECT COUNT(`" . self::COL_ID . "`) as `count` FROM `$this` " . PHP_EOL
            . "WHERE `" . self::COL_USER_WP_ID  . "` IS NOT NULL"
                . " AND `" . self::COL_USER_SCORE . "` > $userScore";
        Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($query . PHP_EOL);
        
        // Get the DB row
        $dbRow = $this->getDb()->getWpDb()->get_row($query, ARRAY_A);
        
        // Valid result
        if (is_array($dbRow) && isset($dbRow['count'])) {
            $result = intval($dbRow['count']) + 1;
        }
        
        return $result;
    }
    
    /**
     * Get the Most Valuable Players (humans) in descending order of total score
     * 
     * @param int $limitCount Limit
     * @return array|null
     */
    public function getMVP($limitCount = 100) {
        $result = null;
        $limitCount = abs((int) $limitCount);
        
        // Valid limit
        if ($limitCount > 0 ) {            
            $result = $this->getDb()->getWpDb()->get_results(
                Stephino_Rpg_Utils_Db::selectAll(
                    $this->getTableName(),
                    array(
                        self::COL_USER_WP_ID => true
                    ),
                    $limitCount,
                    null,
                    self::COL_USER_SCORE,
                    false
                ), 
                ARRAY_A
            );
        }
        
        return is_array($result) && count($result) ? $result : null;
    }
    
    /**
     * Get the total number of players that have performed an AJAX request after the provided timestamp
     * 
     * @param int $timestamp UNIX timestamp
     * @return int
     */
    public function getActive($timestamp) {
        // Prepare the result
        $result = 0;
        
        // Sanitize the timestamp
        $timeStamp = abs((int) $timestamp);
        
        // Prepare the query
        $query = "SELECT COUNT(`" . self::COL_ID . "`) as `count` FROM `$this` " . PHP_EOL
            . "WHERE `" . self::COL_USER_WP_ID  . "` IS NOT NULL"
                . " AND `" . self::COL_USER_LAST_TICK_AJAX . "` >= $timeStamp";
        Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($query . PHP_EOL);
        
        // Get the DB row
        $dbRow = $this->getDb()->getWpDb()->get_row($query, ARRAY_A);
        
        // Valid result
        if (is_array($dbRow) && isset($dbRow['count'])) {
            $result = intval($dbRow['count']);
        }
        
        return $result;
    }
    
    /**
     * Get the ID, Creation Timestamp and Last AJAX Tick Timestamp for all players (robots excluded)
     * 
     * @return array May be empty
     */
    public function getTimestamps() {
        // Prepare the query
        $query = "SELECT `" . self::COL_ID . "`, `" . self::COL_USER_CREATED . "`, `" . self::COL_USER_LAST_TICK_AJAX . "` "
            . "FROM `$this` " . PHP_EOL
            . "WHERE `" . self::COL_USER_WP_ID . "` IS NOT NULL";
        Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($query . PHP_EOL);
        
        // Get the result
        $result = $this->getDb()->getWpDb()->get_results($query, ARRAY_A);
        
        return is_array($result) ? $result : array();
    }
    
    /**
     * Initialize the current player dataset
     * 
     * @return boolean
     */
    protected function _init() {
        // Prepare the result
        $result = true;
        do {
            // Already initialized
            if (null !== $this->_userData) {
                break;
            }
            
            // Prepare the DB result
            $dbResult = $this->getDb()->getWpDb()->get_row(
                Stephino_Rpg_Utils_Db::selectAll(
                    $this->getTableName(),
                    // Robots do not have a corresponding WP ID
                    (null !== $this->getDb()->getRobotId())
                        ? array(
                            self::COL_ID         => $this->getDb()->getRobotId(),
                            self::COL_USER_WP_ID => null,
                        )
                        : array(
                            self::COL_USER_WP_ID => $this->getDb()->getWpUserId(),
                        )
                ), 
                ARRAY_A
            );

            // Valid user ID provided
            if (null !== $dbResult) {
                $this->_userData = $dbResult;
                break;
            }
            
            // Invalidate the data
            $this->_userData = null;
            
            // Nothing found
            $result = false;
        } while(false);
        
        return $result;
    }

    /**
     * Get the game settings for the current user
     * 
     * @return array
     */
    public function getGameSettings() {
        // Get the data
        $data = $this->getData();
        
        // Prepare the result
        $result = is_array($data) && isset($data[self::COL_USER_GAME_SETTINGS]) 
            ? json_decode($data[self::COL_USER_GAME_SETTINGS], true) 
            : array();
        
        // Not a valid array
        if (!is_array($result)) {
            $result = array();
        }
        
        return $result;
    }
    
    /**
     * Update the settings column
     * 
     * @param array $settings Game settings (pre-validated)
     * @param int   $userId   User ID
     * @return boolean True on success, false on error
     */
    public function setGameSettings($settings, $userId) {
        $result = false;
        
        do {
            // Validate the input
            if (!is_array($settings) || !is_int($userId) || $userId <= 0) {
                break;
            }

            // Update
            $result = boolval(
                $this->getDb()->getWpDb()->query(
                    Stephino_Rpg_Utils_Db::update(
                        $this->getTableName(), 
                        array(
                            self::COL_USER_GAME_SETTINGS => json_encode($settings)
                        ), 
                        array(
                            self::COL_ID => abs((int) $userId)
                        )
                    )
                )
            );
        } while(false);
        
        return $result;
    }
    
    /**
     * Append these resources to all players (robots excluded)
     * 
     * @param string $resourceName   Resource name, one of <ul>
     * <li>self::COL_USER_RESOURCE_GOLD</li>
     * <li>self::COL_USER_RESOURCE_RESEARCH</li>
     * <li>self::COL_USER_RESOURCE_GEM</li>
     * </ul>
     * @param int    $resourceValue  Resource value
     * @return int|false The number of rows deleted or false on error
     * @throws Exception
     */
    public function giftToAll($resourceName, $resourceValue) {
        $result = false;
        
        // Prepare the resource names
        $allowedResourceNames = array(
            self::COL_USER_RESOURCE_GOLD,
            self::COL_USER_RESOURCE_RESEARCH,
            self::COL_USER_RESOURCE_GEM,
        );
        if (!in_array($resourceName, $allowedResourceNames)) {
            throw new Exception(__('Invalid resource name', 'stephino-rpg'));
        }
        
        // Integer only
        $resourceValue = abs((int) $resourceValue);
        if ($resourceValue > 0) {
            // Prepare the query
            $query = "UPDATE `$this` SET " . PHP_EOL
                . "`$resourceName` = `$resourceName` + $resourceValue " . PHP_EOL
                . "WHERE `" . self::COL_USER_WP_ID . "` IS NOT NULL";
            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($query . PHP_EOL);
            
            // Store the result
            $result = $this->getDb()->getWpDb()->query($query);
        }
        
        return $result;
    }
}

/*EOF*/