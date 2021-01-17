<?php

/**
 * Stephino_Rpg_Db_Table_Statistics
 * 
 * @title      Table:Statistics
 * @desc       Holds the game stats
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Db_Table_Statistics extends Stephino_Rpg_Db_Table {
    
    /**
     * Statistics Table Name
     */
    const NAME = 'statistics';
    
    /**
     * Statistics ID
     * 
     * @var int
     */
    const COL_ID = 'stat_id';
    
    /**
     * Statistics Date
     * 
     * @var date
     */
    const COL_STAT_DATE = 'stat_date';
    
    /**
     * Active users in JSON format, by hour
     * 
     * @var string
     */
    const COL_STAT_USERS_ACTIVE = 'stat_users_active';
    
    /**
     * Total users in JSON format, by hour
     * 
     * @var string
     */
    const COL_STAT_USERS_TOTAL = 'stat_users_total';
    
    /**
     * Table creation SQL statement
     * 
     * @return string
     */
    public function getCreateStatement() {
        return "CREATE TABLE `$this` (
    `" . self::COL_ID . "` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `" . self::COL_STAT_DATE . "` DATE NOT NULL,
    `" . self::COL_STAT_USERS_ACTIVE . "` text NOT NULL DEFAULT '',
    `" . self::COL_STAT_USERS_TOTAL . "` text NOT NULL DEFAULT '',
    UNIQUE KEY `" . self::COL_ID . "` (`" . self::COL_ID . "`)
);";
    }
    
    /**
     * Create an entry in the statistics table
     * 
     * @param string $reportDate    Report date in "YYYY-MM-DD" format
     * @param int[]  $playersActive Active players associative array
     * @param int[]  $playersTotal  Total players associative array
     * @return int|null New STats ID or Null on error
     */
    public function create($reportDate, $playersActive, $playersTotal) {
        $result = $this->getDb()->getWpDb()->insert(
            $this->getTableName(), 
            array(
                self::COL_STAT_DATE         => trim($reportDate),
                self::COL_STAT_USERS_ACTIVE => json_encode($playersActive),
                self::COL_STAT_USERS_TOTAL  => json_encode($playersTotal),
            )
        );
        
        // Get the new building ID
        return (false !== $result ? $this->getDb()->getWpDb()->insert_id : null);
    }
    
    /**
     * Get the statistics for this date interval
     * 
     * @param int $startDate Start date
     * @param int $endDate   End date
     * @return array|null
     */
    public function getByDate($startDate, $endDate) {
        // Prepare the dates
        $startDate = preg_replace('%[^\d\-]+%', '', trim($startDate));
        $endDate = preg_replace('%[^\d\-]+%', '', trim($endDate));
        
        // Get the rows
        $result = $this->getDb()->getWpDb()->get_results(
            "SELECT * FROM `$this`"
            . " WHERE ("
                . " `" . self::COL_STAT_DATE . "` >= '{$startDate}'"
                . " AND `" . self::COL_STAT_DATE . "` <= '{$endDate}'"
            . " ) ORDER BY `" . self::COL_STAT_DATE . "` ASC",
            ARRAY_A
        );

        return is_array($result) && count($result) ? $result : null;
    }
}

/*EOF*/