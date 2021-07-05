<?php

/**
 * Stephino_Rpg_Db_Table_Messages
 * 
 * @title      Table:Messages
 * @desc       Holds the messages
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Db_Table_Messages extends Stephino_Rpg_Db_Table {
    
    // Messages
    const MESSAGE_TYPE_DIPLOMACY = 'd';
    const MESSAGE_TYPE_ECONOMY   = 'e';
    const MESSAGE_TYPE_MILITARY  = 'm';
    const MESSAGE_TYPE_RESEARCH  = 'r';
    const MESSAGE_TYPE_INVOICE   = 'i';
    
    // Allowed message types
    const MESSAGE_TYPES = array(
        self::MESSAGE_TYPE_RESEARCH,
        self::MESSAGE_TYPE_ECONOMY,
        self::MESSAGE_TYPE_DIPLOMACY,
        self::MESSAGE_TYPE_MILITARY,
        self::MESSAGE_TYPE_INVOICE,
    );
    
    /**
     * Messages Table Name
     */
    const NAME = 'messages';
    
    /**
     * Message ID
     * 
     * @var int
     */
    const COL_ID = 'message_id';
    
    /**
     * To
     * 
     * @var int User ID
     */
    const COL_MESSAGE_TO = 'message_to';
    
    /**
     * From
     * 
     * @var int User ID
     */
    const COL_MESSAGE_FROM = 'message_from';
    
    /**
     * Message type
     * 
     * @var string <ul>
     *     <li><b>'d'</b> for Diplomacy</li>
     *     <li><b>'r'</b> for Research</li>
     *     <li><b>'e'</b> for Economy</li>
     *     <li><b>'m'</b> for Military</li>
     *     <li><b>'i'</b> for Invoice</li>
     * </ul>
     */
    const COL_MESSAGE_TYPE = 'message_type';
    
    /**
     * Subject
     * 
     * @var string
     */
    const COL_MESSAGE_SUBJECT = 'message_subject';
    
    /**
     * Content
     * 
     * @var string
     */
    const COL_MESSAGE_CONTENT = 'message_content';
    
    /**
     * Read
     * 
     * @var int 0|1, default 0
     */
    const COL_MESSAGE_READ = 'message_read';

    /**
     * Delivery Time
     * 
     * @var int UNIX timestamp
     */
    const COL_MESSAGE_TIME = 'message_time';
    
    /**
     * Table creation SQL statement
     * 
     * @return string
     */
    public function getCreateStatement() {
        return "CREATE TABLE `$this` (
    `" . self::COL_ID . "` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `" . self::COL_MESSAGE_TO . "` bigint(20) UNSIGNED NOT NULL,
    `" . self::COL_MESSAGE_FROM . "` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_MESSAGE_TYPE . "` char(1) NOT NULL,
    `" . self::COL_MESSAGE_SUBJECT . "` varchar(128) NOT NULL DEFAULT '',
    `" . self::COL_MESSAGE_CONTENT . "` text NOT NULL DEFAULT '',
    `" . self::COL_MESSAGE_READ . "` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_MESSAGE_TIME . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    UNIQUE KEY `" . self::COL_ID . "` (`" . self::COL_ID . "`), 
    KEY `" . self::COL_MESSAGE_TO . "` (`" . self::COL_MESSAGE_TO . "`), 
    KEY `" . self::COL_MESSAGE_FROM . "` (`" . self::COL_MESSAGE_FROM . "`)
);";
    }
    
    /**
     * Create a message
     * 
     * @param int     $from    Sender user ID
     * @param int     $to      Recipient user ID
     * @param string  $type    Message type, one of <ul>
     * <li>Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_DIPLOMACY</li>
     * <li>Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_RESEARCH</li>
     * <li>Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_ECONOMY</li>
     * <li>Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_MILITARY</li>
     * <li>Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_INVOICE</li>
     * </ul>
     * @param string  $subject Message Subject
     * @param string  $content Message Content
     * @param boolean $isRead  (optional) Mark the message as read? default <b>false</b>
     * @param int     $time    (optional) Message time; default <b>null</b>, fallback to current time
     * @return int|null New Message ID or Null on error
     */
    public function create($from, $to, $type, $subject, $content, $isRead = false, $time = null) {
        // Invalid message type
        if (!in_array($type, self::MESSAGE_TYPES)) {
            $type = self::MESSAGE_TYPE_DIPLOMACY;
        }
        
        // Prepare the result
        $result = $this->getDb()->getWpDb()->query(
            Stephino_Rpg_Utils_Db::insert(
            $this->getTableName(), 
                array(
                    self::COL_MESSAGE_TO      => abs((int) $to),
                    self::COL_MESSAGE_FROM    => abs((int) $from),
                    self::COL_MESSAGE_TYPE    => $type,
                    self::COL_MESSAGE_SUBJECT => trim($subject),
                    self::COL_MESSAGE_CONTENT => trim($content),
                    self::COL_MESSAGE_READ    => $isRead ? 1 : 0,
                    self::COL_MESSAGE_TIME    => null === $time ? time() : abs((int) $time),
                )
            )
        );
        
        // Get the new city ID
        return (false !== $result ? $this->getDb()->getWpDb()->insert_id : null);
    }
    
    /**
     * Create multiple messages with one insert statement
     * 
     * @param array $payload  Array of message arrays. <br/>
     * The following keys are mandatory (and not empty) for each message array:<ul>
     * <li>Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_TO</li>
     * <li>Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_TYPE</li>
     * <li>Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_SUBJECT</li>
     * <li>Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_CONTENT</li>
     * </ul>
     * @param int   $senderId (optional) Sender ID for messages that lack the <b>Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_FROM</b> key; default <b>0</b>
     * @return int|false Number of rows affected or false on error
     */
    public function createMultiple($payload, $senderId = 0) {
        // Prepare the result
        $result = false;
        
        // Seemingly valid payload
        if (is_array($payload)) {
            // Fallback message time
            $messageTime = time();
            
            // Prepare the allowed data keys
            $allowedKeys = array(
                self::COL_MESSAGE_TO,
                self::COL_MESSAGE_FROM,
                self::COL_MESSAGE_TYPE,
                self::COL_MESSAGE_SUBJECT,
                self::COL_MESSAGE_CONTENT,
                self::COL_MESSAGE_READ,
                self::COL_MESSAGE_TIME,
            );
            
            // Sanitize
            foreach($payload as $key => &$data) {
                do {
                    if (is_array($data)) {
                        // Only these keys are allowed
                        foreach(array_keys($data) as $dataKey) {
                            if (!in_array($dataKey, $allowedKeys)) {
                                unset($data[$dataKey]);
                            }
                        }
                        
                        // Valid payload
                        if (isset($data[self::COL_MESSAGE_TO])
                            && isset($data[self::COL_MESSAGE_TYPE])
                            && in_array($data[self::COL_MESSAGE_TYPE], self::MESSAGE_TYPES)
                            && isset($data[self::COL_MESSAGE_SUBJECT])
                            && isset($data[self::COL_MESSAGE_CONTENT])) {
                            // Clean-up
                            $data[self::COL_MESSAGE_TO] = abs((int) $data[self::COL_MESSAGE_TO]);
                            $data[self::COL_MESSAGE_FROM] = abs((int) isset($data[self::COL_MESSAGE_FROM]) ? $data[self::COL_MESSAGE_FROM] : $senderId);
                            $data[self::COL_MESSAGE_SUBJECT] = trim($data[self::COL_MESSAGE_SUBJECT]);
                            $data[self::COL_MESSAGE_CONTENT] = trim($data[self::COL_MESSAGE_CONTENT]);
                            $data[self::COL_MESSAGE_READ] = isset($data[self::COL_MESSAGE_READ]) && $data[self::COL_MESSAGE_READ] ? 1 : 0;
                            $data[self::COL_MESSAGE_TIME] = isset($data[self::COL_MESSAGE_TIME]) ? abs((int) $data[self::COL_MESSAGE_TIME]) : $messageTime;
                            
                            // Never send an empty message
                            if (strlen($data[self::COL_MESSAGE_SUBJECT]) && $data[self::COL_MESSAGE_CONTENT]) {
                                break;
                            }
                        }
                    }
                    
                    // Invalid payload
                    unset($payload[$key]);
                } while(false);
            }
            
            // Get the multi-insert
            if (count($payload) && null !== $multiInsert = Stephino_Rpg_Utils_Db::multiInsert(
                    $this->getTableName(), 
                    $payload
                )) {
                $result = $this->getDb()->getWpDb()->query($multiInsert);
            }
        }
        
        return $result;
    }
    
    /**
     * Get an invoice
     * 
     * @param int    $userId    User ID
     * @param string $paymentId Payment ID
     * @return array|null
     */
    public function getInvoice($userId, $paymentId) {
        return $this->getDb()->getWpDb()->get_row(
            Stephino_Rpg_Utils_Db::selectAll(
                $this->getTableName(), 
                array(
                    self::COL_MESSAGE_FROM    => abs((int) $userId),
                    self::COL_MESSAGE_TYPE    => self::MESSAGE_TYPE_INVOICE,
                    self::COL_MESSAGE_TO      => 0,
                    self::COL_MESSAGE_SUBJECT => preg_replace('%[^\w\-]+%i', '', $paymentId),
                )
            ), 
            ARRAY_A
        );
    }
    
    /**
     * Get all invoices
     * 
     * @param int $startTime Start time (UNIX timestamp)
     * @param int $endTime   End time (UNIZ timestamp)
     * @return array|null
     */
    public function getAllInvoices($startTime, $endTime) {
        // Prepare the query
        $query = "SELECT * FROM `$this` " . PHP_EOL
            . "WHERE `" . self::COL_MESSAGE_TYPE . "` = '" . self::MESSAGE_TYPE_INVOICE . "'"
                . " AND `" . self::COL_MESSAGE_TO  . "` = 0"
                . " AND `" . self::COL_MESSAGE_TIME  . "` >= " . abs((int) $startTime)
                . " AND `" . self::COL_MESSAGE_TIME  . "` <= " . abs((int) $endTime);
        Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($query . PHP_EOL);
        
        // Get the result
        $result = $this->getDb()->getWpDb()->get_results($query, ARRAY_A);
        
        return is_array($result) && count($result) ? $result : null;
    }
    
    /**
     * Remove pending invoices
     * 
     * @param int $userId User ID
     * @return int|false Number of rows affected or false on error
     */
    public function deleteInvoicePending($userId) {
        return $this->getDb()->getWpDb()->query(
            Stephino_Rpg_Utils_Db::delete(
                $this->getTableName(), 
                array(
                    self::COL_MESSAGE_FROM => abs((int) $userId),
                    self::COL_MESSAGE_TYPE => self::MESSAGE_TYPE_INVOICE,
                    self::COL_MESSAGE_TO   => 0,
                    self::COL_MESSAGE_READ => 0
                )
            )
        );
    }
    
    /**
     * Get the number of messages sent by this user in the past 24 hours
     * 
     * @param int $userId Sender User ID
     * @return int Number
     */
    public function getSentRecent($userId) {
        $result = 0;
        
        // Sanitize the user ID
        $userId = abs((int) $userId);
        if ($userId) {
            // Prepare the query
            $query = "SELECT COUNT(`" . self::COL_ID . "`) as `count` FROM `$this` " . PHP_EOL
                . "WHERE `" . self::COL_MESSAGE_FROM . "` = $userId"
                    . " AND `" . self::COL_MESSAGE_TIME . "` >= " . (time() - 86400);
            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($query . PHP_EOL);
            
            // Get the DB row
            $dbRow = $this->getDb()->getWpDb()->get_row($query, ARRAY_A);

            // Valid result
            if (is_array($dbRow) && isset($dbRow['count'])) {
                $result = abs((int) $dbRow['count']);
            }
        }
        
        return $result;
    }
    
    /**
     * Get all messages of a certain type for the current user
     * 
     * @param int    $userId      Recipient user ID
     * @param string $messageType Message type
     * @param int    $limitCount  (optional) Limit count; default <b>null</b>
     * @param int    $limitOffset (optional) Limit offset; default <b>null</b>
     * @return array
     */
    public function getInboxByType($userId, $messageType, $limitCount = null, $limitOffset = null) {
        $result = array();
        
        // Sanitize the message type
        if (!in_array($messageType, self::MESSAGE_TYPES)) {
            $messageType = self::MESSAGE_TYPE_DIPLOMACY;
        }
        
        // Sanitize the limit
        if (null !== $limitCount) {
            $limitCount = abs((int) $limitCount);
        }
        if (null !== $limitOffset) {
            $limitOffset = abs((int) $limitOffset);
        }
        
        // Get the list of unread messages
        $result = $this->getDb()->getWpDb()->get_results(
            Stephino_Rpg_Utils_Db::selectAll(
                $this->getTableName(),
                array(
                    self::COL_MESSAGE_TO   => abs((int) $userId),
                    self::COL_MESSAGE_TYPE => $messageType
                ),
                $limitCount,
                $limitOffset,
                self::COL_MESSAGE_TIME,
                false
            ), 
            ARRAY_A
        );
        
        return is_array($result) ? $result : array();
    }
    
    /**
     * Get all the messages this user has received (count)
     * 
     * @param int    $userId      User ID
     * @param string $messageType Message type
     * @return int Number of messages by type
     */
    public function getInboxCountByType($userId, $messageType) {
        $result = 0;
        
        // Sanitize the message type
        if (!in_array($messageType, self::MESSAGE_TYPES)) {
            $messageType = self::MESSAGE_TYPE_DIPLOMACY;
        }
        
        // Sanitize the user ID
        $userId = abs((int) $userId);
        if ($userId > 0) {
            // Get the number of messages by type
            $query = "SELECT COUNT(`" . self::COL_ID . "`) as `count` FROM `$this` " . PHP_EOL
                . "WHERE `" . self::COL_MESSAGE_TO . "` = " . abs((int) $userId)
                    . " AND `" . self::COL_MESSAGE_TYPE . "` = '$messageType'";
            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($query . PHP_EOL);
            
            $dbRow = $this->getDb()->getWpDb()->get_row($query, ARRAY_A);

            // Valid result
            if (is_array($dbRow) && isset($dbRow['count'])) {
                $result = abs((int) $dbRow['count']);
            }
        }
        
        return $result;
    }
    
    /**
     * Get the message by ID for the current user, including the sender information (game and WordPress Users data)
     * 
     * @param int $userId    Recipient user ID
     * @param int $messageId Message ID
     * @return array|null
     */
    public function getInboxMessage($userId, $messageId) {
        $tableUsers = $this->getDb()->tableUsers()->getTableName();
        $dbPrefix = $this->getDb()->getWpDb()->prefix;
        
        // Prepare the query
        $query = "SELECT * FROM `$this`" . PHP_EOL
            . "  LEFT JOIN `$tableUsers`"
                . " ON `$tableUsers`.`" . Stephino_Rpg_Db_Table_Users::COL_ID . "` = `$this`.`" . self::COL_MESSAGE_FROM . "`" . PHP_EOL
            . "  LEFT JOIN `{$dbPrefix}users`" 
                . " ON `{$dbPrefix}users`.`ID` = `$tableUsers`.`" . Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID . "` " . PHP_EOL
            . "WHERE `" . self::COL_MESSAGE_TO . "` = " . abs((int) $userId)
                . " AND `" . self::COL_ID . "` = " . abs((int) $messageId) . PHP_EOL
            . "  ORDER BY `" . self::COL_MESSAGE_TIME . "` DESC";
        Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($query . PHP_EOL);
        
        return $this->getDb()->getWpDb()->get_row($query, ARRAY_A);
    }
    
    /**
     * Get unread messages for the current user
     * 
     * @param int $userId Recipient User ID
     * @return array
     */
    public function getInboxAllUnread($userId) {
        return $this->getDb()->getWpDb()->get_results(
            Stephino_Rpg_Utils_Db::selectAll(
                $this->getTableName(),
                array(
                    self::COL_MESSAGE_TO   => abs((int) $userId),
                    self::COL_MESSAGE_READ => 0
                ),
                null,
                null,
                self::COL_MESSAGE_TIME,
                false
            ), 
            ARRAY_A
        );
    }
    
    /**
     * Mark an inbox message as read
     * 
     * @param int $userId    Recipient ID
     * @param int $messageId Message ID
     * @return int|false The number of rows updated or false on error
     */
    public function readInboxMessage($userId, $messageId) {
        return $this->getDb()->getWpDb()->query(
            Stephino_Rpg_Utils_Db::update(
                $this->getTableName(), 
                array(
                    self::COL_MESSAGE_READ => 1
                ), 
                array(
                    self::COL_MESSAGE_TO => abs((int) $userId),
                    self::COL_ID         => abs((int) $messageId)
                )
            )
        );
    }
    
    /**
     * Delete all messages (inbox and sent) for this user, including user invoices (right to be forgotten)
     * 
     * @param int $userId User ID
     * @return int|false Number of rows deleted or false on error
     */
    public function deleteAll($userId) {
        $query = "DELETE FROM `$this` " . PHP_EOL
            . "WHERE `" . self::COL_MESSAGE_TO . "` = " . abs((int) $userId)
                . " OR `" . self::COL_MESSAGE_FROM . "` = " . abs((int) $userId);
        Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($query . PHP_EOL);
        
        return $this->getDb()->getWpDb()->query($query);
    }
    
    /**
     * Delete all messages (inbox and sent) for all users older than this number of days, except for invoices
     * 
     * @param int $days Maximum message age in days
     * @return int|false Number of rows deleted or false on error
     */
    public function deleteAllExpired($days) {
        $query = "DELETE FROM `$this` " . PHP_EOL
            . "WHERE `" . self::COL_MESSAGE_TYPE . "` != '" . self::MESSAGE_TYPE_INVOICE . "'"
                . " AND `" . self::COL_MESSAGE_TIME . "` <= " . (time() - 86400 * abs((int) $days));
        Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($query . PHP_EOL);
        
        return $this->getDb()->getWpDb()->query($query);
    }
    
    /**
     * Delete all this user's inbox messages that overflow the inbox size limit
     * 
     * @param int $userId     User ID
     * @param int $inboxLimit Inbox size limit
     */
    public function deleteInboxOverflow($userId, $inboxLimit) {
        $query = "DELETE FROM `$this` " . PHP_EOL
            . "WHERE (" 
                . " `" . self::COL_MESSAGE_TO . "` = " . abs((int) $userId)
                . " AND `" . self::COL_ID . "` NOT IN"
                . " (SELECT `" . self::COL_ID . "` FROM"
                    . " (SELECT `" . self::COL_ID . "` FROM `$this`"
                        . " WHERE `" . self::COL_MESSAGE_TO . "` = " . abs((int) $userId)
                        . " ORDER BY `" . self::COL_MESSAGE_TIME . "` DESC"
                        . " LIMIT " . abs((int) $inboxLimit)
                    . " ) x"
                . " )"
            . " )";
        Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($query . PHP_EOL);
        
        return $this->getDb()->getWpDb()->query($query);
    }
    
    /**
     * Delete all inbox messages created by the system for this user by message type<br/>
     * For the "Diplomacy" inbox, skip "notif-diplomacy-discovery" messages
     * 
     * @param int    $userId      User ID
     * @param string $messageType Message type
     * @return int|false Number of rows deleted or false on error
     */
    public function deleteInboxByType($userId, $messageType) {
        if (!in_array($messageType, self::MESSAGE_TYPES)) {
            $messageType = self::MESSAGE_TYPE_DIPLOMACY;
        }
        
        // Prepare the query
        $query = "DELETE FROM `$this` " . PHP_EOL
            . "WHERE `" . self::COL_MESSAGE_TO . "` = " . abs((int) $userId)
            . " AND `" . self::COL_MESSAGE_FROM . "` = 0"
            . " AND `" . self::COL_MESSAGE_TYPE . "` = '$messageType'"
            . (self::MESSAGE_TYPE_DIPLOMACY === $messageType 
                ? (" AND `" . self::COL_MESSAGE_SUBJECT . "` != '" . Stephino_Rpg_Db_Model_Messages::TEMPLATE_TIMELAPSE_NOTIF_DIPLOMACY_DISCOVERY . "'" )
                : ""
            );
        Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($query . PHP_EOL);
        
        return $this->getDb()->getWpDb()->query($query);
    }
    
    /**
     * Delete a received message
     * 
     * @param int $userId    User ID
     * @param int $messageId Message ID
     * @return int|false The number of rows deleted or false on error
     */
    public function deleteInboxById($userId, $messageId) {
        return $this->getDb()->getWpDb()->query(
            Stephino_Rpg_Utils_Db::delete(
                $this->getTableName(), 
                array(
                    self::COL_MESSAGE_TO => abs((int) $userId),
                    self::COL_ID         => abs((int) $messageId)
                )
            )
        );
    }
}

/*EOF*/