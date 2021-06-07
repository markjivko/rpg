<?php

/**
 * Stephino_Rpg_Db_Table_Queues
 * 
 * @title      Table:Queues
 * @desc       Holds the queues information
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Db_Table_Queues extends Stephino_Rpg_Db_Table {
    
    // Item types
    const ITEM_TYPE_BUILDING = 'b';
    const ITEM_TYPE_RESEARCH = 'r';
    const ITEM_TYPE_UNIT     = 'u';
    const ITEM_TYPE_SHIP     = 's';
    const ITEM_TYPE_PREMIUM  = 'p';
    
    /**
     * Allowed item types
     */
    const ITEM_TYPES = array(
        self::ITEM_TYPE_BUILDING,
        self::ITEM_TYPE_RESEARCH,
        self::ITEM_TYPE_UNIT,
        self::ITEM_TYPE_SHIP,
        self::ITEM_TYPE_PREMIUM,
    );
    
    /**
     * Queues Table Name
     */
    const NAME = 'queues';

    /**
     * Queue ID
     * 
     * @var int
     */
    const COL_ID = 'queue_id';
    
    /**
     * User ID
     * 
     * @var int
     */
    const COL_QUEUE_USER_ID = 'queue_user_id';
    
    /**
     * User ID
     * 
     * @var int
     */
    const COL_QUEUE_CITY_ID = 'queue_city_id';
    
    /**
     * Item Type
     * 
     * @var string <b>'b'</b> for Building, <b>'u'</b> for Units, <b>'s'</b> for Ships, <b>'r'</b> for Research, <b>'p'</b> for Premium
     */
    const COL_QUEUE_ITEM_TYPE  = 'queue_item_type';
    
    /**
     * Item Config ID
     * 
     * @var int
     */
    const COL_QUEUE_ITEM_ID  = 'queue_item_id';
    
    /**
     * Quantity
     * 
     * @var int
     */
    const COL_QUEUE_QUANTITY = 'queue_quantity';
    
    /**
     * Total queue duration in seconds (4 decimals precision)
     * 
     * @var float
     */
    const COL_QUEUE_DURATION = 'queue_duration';
    
    /**
     * Time
     * 
     * @var int UNIX timestamp
     */
    const COL_QUEUE_TIME = 'queue_time';
    
    /**
     * Table creation SQL statement
     * 
     * @return string
     */
    public function getCreateStatement() {
        return "CREATE TABLE `$this` (
    `" . self::COL_ID . "` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `" . self::COL_QUEUE_USER_ID . "` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_QUEUE_CITY_ID . "` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_QUEUE_ITEM_TYPE . "` char(1) NOT NULL DEFAULT 'b',
    `" . self::COL_QUEUE_ITEM_ID . "` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_QUEUE_QUANTITY . "` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_QUEUE_DURATION . "` decimal(24,4) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_QUEUE_TIME . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    UNIQUE KEY `" . self::COL_ID . "` (`" . self::COL_ID . "`)
);";
    }
    
    /**
     * Create a new queue
     * 
     * @param int    $userId        User ID
     * @param int    $cityId        City ID
     * @param string $itemType      Item Type
     * @param int    $itemId        Item ID
     * @param int    $itemQuantity  Queue quantity
     * @param float  $queueDuration Queue duration in seconds
     * @return int|null Queue ID or null on error
     */
    public function create($userId, $cityId, $itemType, $itemId, $itemQuantity, $queueDuration) {
        // Invalid item type
        if (!in_array($itemType, self::ITEM_TYPES)) {
            return null;
        }
        
        // Prepare the row data
        $rowData = array(
            self::COL_QUEUE_USER_ID   => abs((int) $userId),
            self::COL_QUEUE_CITY_ID   => abs((int) $cityId),
            self::COL_QUEUE_ITEM_TYPE => $itemType,
            self::COL_QUEUE_ITEM_ID   => abs((int) $itemId),
            self::COL_QUEUE_QUANTITY  => abs((int) $itemQuantity),
            self::COL_QUEUE_DURATION  => floatval($queueDuration),
            self::COL_QUEUE_TIME      => floatval($queueDuration) + time()
        );
        
        // Prepare the result
        $result = $this->getDb()->getWpDb()->query(
            Stephino_Rpg_Utils_Db::insert(
                $this->getTableName(), 
                $rowData
            )
        );
        
        // Insert
        if (false !== $result) {
            // Append the ID
            $rowData[self::COL_ID] = $this->getDb()->getWpDb()->insert_id;
            
            // Store in the time-lapse data set
            Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Queues::KEY)->addRow($rowData);
        }
        
        // Last insert ID
        return (false !== $result ? $this->getDb()->getWpDb()->insert_id : null);
    }
    
    /**
     * Delete queues by user ID
     * 
     * @param int $userId User ID
     * @return int|false The number of rows deleted or false on error
     */
    public function deleteByUser($userId) {
        return $this->getDb()->getWpDb()->query(
            Stephino_Rpg_Utils_Db::delete(
                $this->getTableName(),
                array(
                    self::COL_QUEUE_USER_ID => abs((int) $userId)
                )
            )
        );
    }
    
    /**
     * Get queues by user ID and item type
     * 
     * @param int    $userId   User ID
     * @param string $itemType Item Type
     * @return array[]|null
     */
    public function getByUserIdAndType($userId, $itemType) {
        if (!in_array($itemType, self::ITEM_TYPES)) {
            $itemType = self::ITEM_TYPE_BUILDING;
        }
        
        $result = $this->getDb()->getWpDb()->get_results(
            Stephino_Rpg_Utils_Db::selectAll(
                $this->getTableName(), 
                array(
                    self::COL_QUEUE_USER_ID   => abs((int) $userId),
                    self::COL_QUEUE_ITEM_TYPE => $itemType
                )
            ),
            ARRAY_A
        );
        
        return is_array($result) && count($result) ? $result : null;
    }
    
    /**
     * Get queues by user ID
     * 
     * @param int $userId User ID
     * @return array[]|null
     */
    public function getByUserId($userId) {
        $result = $this->getDb()->getWpDb()->get_results(
            Stephino_Rpg_Utils_Db::selectAll(
                $this->getTableName(), 
                array(
                    self::COL_QUEUE_USER_ID => abs((int) $userId)
                )
            ),
            ARRAY_A
        );
        
        return is_array($result) && count($result) ? $result : null;
    }
    
    /**
     * Remove an item from queue
     * 
     * @param int $queueId Queue ID
     * @return int|false The number of rows updated or false on error
     */
    public function unqueue($queueId) {
        return $this->deleteById($queueId);
    }
}

/*EOF*/