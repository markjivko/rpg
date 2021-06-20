<?php
/**
 * Stephino_Rpg_Db_Model_Statistics
 * 
 * @title     Model:Statistics
 * @desc      Statistics Model
 * @copyright (c) 2021, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Db_Model_Statistics extends Stephino_Rpg_Db_Model {

    /**
     * Statistics Model Name
     */
    const NAME = 'statistics';
    
    /**
     * Export keys
     */
    const EXPORT_PRO          = 'pro';
    const EXPORT_CURRENCY     = 'currency';
    const EXPORT_DATE_YEAR    = 'date-year';
    const EXPORT_DATE_MONTH   = 'date-month';
    const EXPORT_REVENUE      = 'revenue';
    const EXPORT_REAL_TIME    = 'real-time';
    const EXPORT_USERS_ACTIVE = 'users-active';
    const EXPORT_USERS_TOTAL  = 'users-total';
    const EXPORT_LEADER_BOARD = 'leader-board';
    const EXPORT_TRANSACTIONS = 'transactions';
    const EXPORT_ANNOUNCEMENT = 'announcement';
    
    /**
     * Gather the statistics for the present day
     * Update the statistics for the previous day if before noon
     * Fill-out missing entries for the last month
     */
    public function gather() {
        Stephino_Rpg_Log::check() && Stephino_Rpg_Log::info('Gathering stats...');
        
        // Prepare the date array
        $dates = array(
            date('Y-m-d', strtotime('yesterday')), 
            date('Y-m-d')
        );

        // Gather the populated dates
        $dbDates = array();
        
        // Go through the logs
        if (is_array($dbRows = $this->getDb()->tableStatistics()->getByDate($dates[0], $dates[1]))) {
            foreach ($dbRows as $dbRow) {
                $dbDates[$dbRow[Stephino_Rpg_Db_Table_Statistics::COL_ID]] = $dbRow[Stephino_Rpg_Db_Table_Statistics::COL_STAT_DATE];
            }
            
            // Store the gap days
            $gapDates = array_diff($dates, $dbDates);
        } else {
            $gapDates = $dates;
        }
        
        // Prepare the user data
        $userTimestamps = $this->getDb()->tableUsers()->getTimestamps();
        
        /**
         * Get the active users at the specified date
         * 
         * @param string $date YYYY-MM-DD Date
         * @return int[] Active users by hour, from 0 to 23
         */
        $getUsersActive = function($date) use ($userTimestamps, $dbDates, $dbRows) {
            $result = array();
            
            // Stored data
            $storedData = array();
            if (false !== $dateDbId = array_search($date, $dbDates)) {
                foreach ($dbRows as $dbRow) {
                    if ($dateDbId == $dbRow[Stephino_Rpg_Db_Table_Statistics::COL_ID]) {
                        // Get the stored result
                        $storedResult = @json_decode($dbRow[Stephino_Rpg_Db_Table_Statistics::COL_STAT_USERS_ACTIVE], true);
                        if (is_array($storedResult)) {
                            for ($hour = 0; $hour <= 23; $hour++) {
                                $storedData[$hour] = isset($storedResult[$hour]) ? (int) $storedResult[$hour] : 0;
                            }
                        }
                        break;
                    }
                }
            }
            
            // Prepare the timestamp
            $timestamp = strtotime($date);
            
            // Go through the rows
            foreach ($userTimestamps as $dbRow) {
                // Get the user last AJAX tick
                $userTs = (int) $dbRow[Stephino_Rpg_Db_Table_Users::COL_USER_LAST_TICK_AJAX];
                
                // Prepare the hourly total
                for ($hour = 0; $hour <= 23; $hour++) {
                    if (!isset($result[$hour])) {
                        $result[$hour] = 0;
                    }
                    
                    // Prepare the interval
                    $startTs = $timestamp + (3600 * $hour);
                    $endTs = $timestamp + (3600 * ($hour + 1));
                    
                    // Very old user (back when USER_CREATED was missing) or user created at this hour
                    if (0 === $userTs || ($userTs >= $startTs && $userTs <= $endTs)) {
                        $result[$hour]++;
                    }
                }
            }
            
            // Sanitize the result, keeping higher values (for users who remain active for multiple hours)
            if (is_array($storedData)) {
                foreach ($result as $hour => $value) {
                    if (isset($storedData[$hour]) && $storedData[$hour] > $value) {
                        $result[$hour] = $storedData[$hour];
                    }
                }
            }
            return $result;
        };
        
        /**
         * Get the total users at the specified date
         * 
         * @param string $date YYYY-MM-DD Date
         * @return int[] Total users by hour, from 0 to 23
         */
        $getUsersTotal = function($date) use ($userTimestamps)  {
            $result = array();
            
            // Prepare the timestamp
            $timestamp = strtotime($date);
            
            // Go through the rows
            foreach ($userTimestamps as $dbRow) {
                // Get the user creation timestamp; might be 0 for older versions
                $userTs = (int) $dbRow[Stephino_Rpg_Db_Table_Users::COL_USER_CREATED];
                
                // Prepare the hourly total
                for ($hour = 0; $hour <= 23; $hour++) {
                    if (!isset($result[$hour])) {
                        $result[$hour] = 0;
                    }
                    
                    // Prepare the interval
                    $endTs = $timestamp + (3600 * ($hour + 1));
                    
                    // Very old user (back when USER_CREATED was missing) or user created before the end of this hour
                    if (0 === $userTs || $userTs <= $endTs) {
                        $result[$hour]++;
                    }
                }
            }
            return $result;
        };
        
        // Prepare the inserts
        $multiInsertArray = array();
        foreach($gapDates as $gapDate) {
            $multiInsertArray[] = array(
                Stephino_Rpg_Db_Table_Statistics::COL_STAT_DATE         => $gapDate,
                Stephino_Rpg_Db_Table_Statistics::COL_STAT_USERS_ACTIVE => json_encode($getUsersActive($gapDate)),
                Stephino_Rpg_Db_Table_Statistics::COL_STAT_USERS_TOTAL  => json_encode($getUsersTotal($gapDate)),
            );
        }
        if (count($multiInsertArray) 
            && null !== $multiInsertQuery = Stephino_Rpg_Utils_Db::multiInsert(
                $this->getDb()->tableStatistics()->getTableName(), 
                $multiInsertArray
            )) {
            $this->getDb()->getWpDb()->query($multiInsertQuery);
        }
        
        // Prepare the updates
        $multiUpdateArray = array();
        foreach ($dates as $dateKey => $dateValue) {
            // Skip recalc for yesterday
            if (0 === $dateKey && (int) date('G') > 12) {
                continue;
            }
            
            // Not handled by the multi-insert
            if (false !== $dateDbId = array_search($dateValue, $dbDates)) {
                $multiUpdateArray[$dateDbId] = array(
                    Stephino_Rpg_Db_Table_Statistics::COL_STAT_USERS_ACTIVE => json_encode($getUsersActive($dbDates[$dateDbId])),
                    Stephino_Rpg_Db_Table_Statistics::COL_STAT_USERS_TOTAL  => json_encode($getUsersTotal($dbDates[$dateDbId])),
                );
            }
        }
        if (count($multiUpdateArray) && null !== $multiUpdateQuery = Stephino_Rpg_Utils_Db::multiUpdate(
                $this->getDb()->tableStatistics()->getTableName(), 
                Stephino_Rpg_Db_Table_Statistics::COL_ID,
                $multiUpdateArray
            )) {
            $this->getDb()->getWpDb()->query($multiUpdateQuery);
        }
    }
    
    /**
     * Export the game statistics
     * 
     * @param string $statsType  (optional) Monthly reporting data set, one of <ul>
     * <li>Stephino_Rpg_Db_Model_Statistics::EXPORT_REVENUE</li>
     * <li>Stephino_Rpg_Db_Model_Statistics::EXPORT_USERS_ONLINE</li>
     * <li>Stephino_Rpg_Db_Model_Statistics::EXPORT_USERS_ACTIVE</li>
     * <li>Stephino_Rpg_Db_Model_Statistics::EXPORT_USERS_TOTAL</li>
     * <li>Stephino_Rpg_Db_Model_Statistics::EXPORT_TRANSACTIONS</li>
     * <li>Stephino_Rpg_Db_Model_Statistics::EXPORT_LEADER_BOARD</li>
     * </ul> Return all data sets if omitted
     * @param int    $statsYear  (optional) Reporting year; default: current year
     * @param int    $statsMonth (optional) Reporting month, [1-12]; default: current month
     * @return array Associative array of <ul>
     * <li>Stephino_Rpg_Db_Model_Statistics::EXPORT_PRO => (boolean) Pro version activated</li>
     * <li>Stephino_Rpg_Db_Model_Statistics::EXPORT_DATE_YEAR => (int) Reporting year</li>
     * <li>Stephino_Rpg_Db_Model_Statistics::EXPORT_DATE_MONTH => (int) Reporting month</li>
     * <li>Stephino_Rpg_Db_Model_Statistics::EXPORT_CURRENCY => (string) PayPal currency</li>
     * <li>[Stephino_Rpg_Db_Model_Statistics::EXPORT_REVENUE => (float) Monthly revenue]</li>
     * <li>[Stephino_Rpg_Db_Model_Statistics::EXPORT_TRANSACTIONS => (array) Transactions list]</li>
     * <li>[Stephino_Rpg_Db_Model_Statistics::EXPORT_LEADER_BOARD => (array) Leader board]</li>
     * <li>[Stephino_Rpg_Db_Model_Statistics::EXPORT_USERS_ONLINE => (int) Users online</li>
     * <li>[Stephino_Rpg_Db_Model_Statistics::EXPORT_USERS_ACTIVE => (array) Hourly active users]</li>
     * <li>[Stephino_Rpg_Db_Model_Statistics::EXPORT_USERS_TOTAL => (array) Hourly total users]</li>
     * <li>[Stephino_Rpg_Db_Model_Statistics::EXPORT_ANNOUNCEMENT => (string[]) Array of Announcement ID, Announcement Title, Announcement Content, Announcement remaining time in seconds]</li>
     * </ul>
     */
    public function export($statsType = null, $statsYear = null, $statsMonth = null) {
        // Sanitize the data set
        if (!in_array($statsType, array(
            self::EXPORT_REVENUE,
            self::EXPORT_REAL_TIME,
            self::EXPORT_USERS_ACTIVE,
            self::EXPORT_USERS_TOTAL,
            self::EXPORT_TRANSACTIONS,
            self::EXPORT_LEADER_BOARD,
            self::EXPORT_ANNOUNCEMENT,
        ))) {
            $statsType = null;
        }
        
        // Validate the year
        if (!is_int($statsYear) || $statsYear > (int) date('Y') || $statsYear <= 2000) {
            $statsYear = (int) date('Y');
        }
        
        // Validate the month
        if (!is_int($statsMonth) || $statsMonth < 1 || $statsMonth > 12) {
            $statsMonth = (int) date('m');
        }
        
        // Prepare the result
        $result = array(
            self::EXPORT_PRO        => Stephino_Rpg::get()->isPro(),
            self::EXPORT_DATE_YEAR  => $statsYear,
            self::EXPORT_DATE_MONTH => $statsMonth,
            self::EXPORT_CURRENCY   => Stephino_Rpg_Config::get()->core()->getPayPalCurrency(),
        );
        
        /**
         * Get the list of transactions
         * 
         * @param int $year  Reporting year
         * @param int $month Reporting month
         * @return array List of invoices; each item is an array with the following keys:<ul>
         * <li>Stephino_Rpg_Db_Model_Invoices::INVOICE_USER_ID      => (int) User ID</li>
         * <li>Stephino_Rpg_Db_Model_Invoices::INVOICE_USER_WP_ID   => (int) WordPress User ID</li>
         * <li>Stephino_Rpg_Db_Model_Invoices::INVOICE_USER_WP_NAME => (string) WordPress User Name</li>
         * <li>Stephino_Rpg_Db_Model_Invoices::INVOICE_USER_WP_ICON => (string) WordPress User Icon</li>
         * <li>Stephino_Rpg_Db_Model_Invoices::INVOICE_PAYMENT_ID   => (string) PayPal Payment ID</li>
         * <li>Stephino_Rpg_Db_Model_Invoices::INVOICE_PACKAGE_ID   => (int) Premium Package Configuration ID</li>
         * <li>Stephino_Rpg_Db_Model_Invoices::INVOICE_PACKAGE_NAME => (string) Premium Package Name</li>
         * <li>Stephino_Rpg_Db_Model_Invoices::INVOICE_VALUE        => (float) Fiat currency value</li>
         * <li>Stephino_Rpg_Db_Model_Invoices::INVOICE_CURRENCY     => (string) Fiat currency </li>
         * <li>Stephino_Rpg_Db_Model_Invoices::INVOICE_PAID         => (boolean) Invoice was paid</li>
         * <li>Stephino_Rpg_Db_Model_Invoices::INVOICE_DATE         => (string) Date in "YYYY-MM-DD HH:mm:ss" format</li>
         * </ul>
         */
        $exportTransactions = function($year, $month) {
            // Get the transactions list
            $transactionsList = $this->getDb()->modelInvoices()->getList($year, $month);
            
            // Get the user IDs
            $userIds = array_map(function($item) {
                return $item[Stephino_Rpg_Db_Model_Invoices::INVOICE_USER_ID];
            }, $transactionsList);
            
            // Get the corresponding WordPress IDs
            $userRows = $this->getDb()->tableUsers()->getByIds($userIds);
            
            // Append the extra keys
            return array_map(function($item) use($userRows) {
                // Get the user ID
                $userId = $item[Stephino_Rpg_Db_Model_Invoices::INVOICE_USER_ID];
                
                // Store the WordPress User ID
                $item[Stephino_Rpg_Db_Model_Invoices::INVOICE_USER_WP_ID] = isset($userRows[$userId]) 
                    ? $userRows[$userId][Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID]
                    : null;
                
                // Store the WordPress User Name
                $item[Stephino_Rpg_Db_Model_Invoices::INVOICE_USER_WP_NAME] = Stephino_Rpg_Utils_Lingo::getUserName(
                    isset($userRows[$userId]) ? $userRows[$userId] : array()
                );
                
                // Store the WordPress User Avatar
                $item[Stephino_Rpg_Db_Model_Invoices::INVOICE_USER_WP_ICON] = isset($userRows[$userId])
                    ? get_avatar($userRows[$userId][Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID], 96, 'wavatar')
                    : null;
                
                // Store the Premium Package Name
                $premiumPackage = Stephino_Rpg_Config::get()->premiumPackages()->getById(
                    $item[Stephino_Rpg_Db_Model_Invoices::INVOICE_PACKAGE_ID]
                );
                $item[Stephino_Rpg_Db_Model_Invoices::INVOICE_PACKAGE_NAME] = null !== $premiumPackage
                    ? $premiumPackage->getName()
                    : 'Unknown';
                return $item;
            }, $transactionsList);
        };
        
        /**
         * Get the leader board
         * 
         * @return array List of users; each item is an array with the following keys:<ul>
         * <li>Stephino_Rpg_Db_Table_Users::COL_ID                    => (int) User ID</li>
         * <li>Stephino_Rpg_Db_Table_Users::COL_USER_SCORE            => (int) Total Score</li>
         * <li>Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_VICTORIES => (int) Total Victories</li>
         * <li>Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_DRAWS     => (int) Total Impasses</li>
         * <li>Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_DEFEATS   => (int) Total Defeats</li>
         * <li>Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_DEFEATS   => (int) Total Defeats</li>
         * <li>Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID            => (int) WordPress User ID</li>
         * <li>Stephino_Rpg_Db_Model_Users::LEADER_BOARD_USER_WP_NAME => (string) WordPress User Name</li>
         * <li>Stephino_Rpg_Db_Model_Users::LEADER_BOARD_USER_WP_ICON => (string) WordPress User Icon</li>
         * </ul>
         */
        $exportLeaderBoard = function() {
            $result = array();
            
            do {
                // Prepare the users list
                $usersList = $this->getDb()->tableUsers()->getMVP();
                if (!is_array($usersList) || !count($usersList)) {
                    break;
                }
                
                // Prepare some extra keys
                $currentTime = time();
                $result = array_map(function($item) use($currentTime) {
                    // Store the original item keys
                    $itemKeys = array_keys($item);
                    
                    // Store the WordPress User Name
                    $item[Stephino_Rpg_Db_Model_Users::LEADER_BOARD_USER_WP_NAME] = Stephino_Rpg_Utils_Lingo::getUserName(
                        $item
                    );

                    // Store the WordPress User Avatar
                    $item[Stephino_Rpg_Db_Model_Users::LEADER_BOARD_USER_WP_ICON] = 
                        get_avatar($item[Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID], 96, 'wavatar');
                    
                    // Is the user live?
                    $item[Stephino_Rpg_Db_Model_Users::LEADER_BOARD_USER_LIVE] = (
                        $currentTime - $item[Stephino_Rpg_Db_Table_Users::COL_USER_LAST_TICK_AJAX] <= 900
                    );
                    
                    // When has the user joined?
                    $item[Stephino_Rpg_Db_Model_Users::LEADER_BOARD_USER_JOINED] = date(
                        'Y-m-d', 
                        $item[Stephino_Rpg_Db_Table_Users::COL_USER_CREATED]
                    );
                    
                    // Remove redundant values
                    foreach ($itemKeys as $itemKey) {
                        if (!in_array($itemKey, array(
                            Stephino_Rpg_Db_Table_Users::COL_ID,
                            Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID,
                            Stephino_Rpg_Db_Table_Users::COL_USER_SCORE,
                            Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_VICTORIES,
                            Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_DRAWS,
                            Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_DEFEATS,
                        ))) {
                            unset($item[$itemKey]);
                        }
                    }
                    return $item;
                }, $usersList);
                
            } while(false);
            
            return $result;
        };
        
        /**
         * Total revenue
         * 
         * @param int $year  Reporting year
         * @param int $month Reporting month
         * @return float Total revenue
         */
        $exportRevenue = function($year, $month) use ($exportTransactions) {
           $monthlyTotal = 0;
           if (Stephino_Rpg::get()->isPro()) {
               foreach ($exportTransactions($year, $month) as $invoice) {
                   if ($invoice[Stephino_Rpg_Db_Model_Invoices::INVOICE_PAID]) {
                       $monthlyTotal += $invoice[Stephino_Rpg_Db_Model_Invoices::INVOICE_VALUE];
                   }
               }
           } else {
               $monthlyTotal = mt_rand(55, 95) * 100.0;
           }
           return round($monthlyTotal, 2);
        };
        
        /**
         * Users active in the past hour
         * 
         * @return int Active users
         */
        $exportRealTime = function() {
            return $this->getDb()->tableUsers()->getActive(time() - 3600);
        };
        
        /**
         * Export Active Users or Total Users hourly statistics
         * 
         * @param int    $year       Reporting year
         * @param int    $month      Reporting month
         * @param string $columnName Column to export, one of <ul>
         * <li>Stephino_Rpg_Db_Table_Statistics::COL_STAT_USERS_ACTIVE</li>
         * <li>Stephino_Rpg_Db_Table_Statistics::COL_STAT_USERS_TOTAL</li>
         * </ul>
         * @return array Active or Total users, by hour
         */
        $exportUserStats = function($year, $month, $columnName) {
            if (!in_array($columnName, array(
                Stephino_Rpg_Db_Table_Statistics::COL_STAT_USERS_ACTIVE,
                Stephino_Rpg_Db_Table_Statistics::COL_STAT_USERS_TOTAL,
            ))) {
                $columnName = Stephino_Rpg_Db_Table_Statistics::COL_STAT_USERS_ACTIVE;
            }

            // Prepare the intervals
            $startDate = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
            $endDate = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' 
                . str_pad(cal_days_in_month(CAL_GREGORIAN, $month, $year), 2, '0', STR_PAD_LEFT);

            // Prepare the date for today
            $today = date('Y-m-d');
            $currentHour = (int) date('G');

            // Prepare the result
            $result = array();
            if (is_array($dataSet = $this->getDb()->tableStatistics()->getByDate($startDate, $endDate))) {
                foreach ($dataSet as $dbRow) {
                    // Get the hourly data
                    $hourlyData = @json_decode($dbRow[$columnName], true);

                    // Valid hourly data
                    if (is_array($hourlyData)) {
                        foreach ($hourlyData as $hour => $value) {
                            if ($today == $dbRow[Stephino_Rpg_Db_Table_Statistics::COL_STAT_DATE] && $hour > $currentHour) {
                                continue;
                            }

                            // Prepare the key
                            $resultKey = $dbRow[Stephino_Rpg_Db_Table_Statistics::COL_STAT_DATE] 
                                . ', ' . str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';

                            // Store the result
                            $result[$resultKey] = $value;
                        }
                    }
                }
            }

            // Only one entry
            if (1 === count($result)) {
                // Append a duplicate
                $result[preg_replace('%00$%', '30', $resultKey)] = $result[$resultKey];
            }
            return $result;
        };
        
        // Specific stat only
        if (null !== $statsType) {
            switch ($statsType) {
                case self::EXPORT_REVENUE:
                    $result[$statsType] = $exportRevenue($statsYear, $statsMonth);
                    break;
                
                case self::EXPORT_TRANSACTIONS:
                    $result[$statsType] = $exportTransactions($statsYear, $statsMonth);
                    break;
                
                case self::EXPORT_LEADER_BOARD:
                    $result[$statsType] = $exportLeaderBoard();
                    break;
                
                case self::EXPORT_REAL_TIME:
                    $result[$statsType] = $exportRealTime();
                    break;
                
                case self::EXPORT_USERS_ACTIVE:
                    $result[$statsType] = $exportUserStats(
                        $statsYear, 
                        $statsMonth, 
                        Stephino_Rpg_Db_Table_Statistics::COL_STAT_USERS_ACTIVE
                    );
                    break;
                
                case self::EXPORT_USERS_TOTAL:
                    $result[$statsType] = $exportUserStats(
                        $statsYear, 
                        $statsMonth,
                        Stephino_Rpg_Db_Table_Statistics::COL_STAT_USERS_TOTAL
                    );
                    break;
                
                case self::EXPORT_ANNOUNCEMENT:
                    $result[$statsType] = $this->getDb()->modelAnnouncement()->get();
                    break;
            }
        } else {
            $result = array_merge($result, array(
                self::EXPORT_REVENUE      => $exportRevenue($statsYear, $statsMonth),
                self::EXPORT_TRANSACTIONS => $exportTransactions($statsYear, $statsMonth),
                self::EXPORT_LEADER_BOARD => $exportLeaderBoard(),
                self::EXPORT_REAL_TIME    => $exportRealTime(),
                self::EXPORT_USERS_ACTIVE => $exportUserStats(
                    $statsYear, 
                    $statsMonth, 
                    Stephino_Rpg_Db_Table_Statistics::COL_STAT_USERS_ACTIVE
                ),
                self::EXPORT_USERS_TOTAL  => $exportUserStats(
                    $statsYear, 
                    $statsMonth, 
                    Stephino_Rpg_Db_Table_Statistics::COL_STAT_USERS_TOTAL
                ),
                self::EXPORT_ANNOUNCEMENT => $this->getDb()->modelAnnouncement()->get(),
            ));
        }
        
        return $result;
    }
}

/*EOF*/