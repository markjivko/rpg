<?php

/**
 * Stephino_Rpg_Db_Table
 * 
 * @title      Table
 * @desc       Table object perform changes to the database directly with no business logic
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
abstract class Stephino_Rpg_Db_Table {
    
    /**
     * Table Name
     */
    const NAME = '';
    
    /**
     * ID
     * 
     * @var int
     */
    const COL_ID = 'id';
    
    /**
     * Stephino_Rpg_Db reference
     * 
     * @var Stephino_Rpg_Db
     */
    private $_db = null;
    
    /**
     * Object cache
     *
     * @var array[]
     */
    protected $_cache = array();
    
    /**
     * Get the CREATE SQL statement with the __TABLE__ placeholder
     * 
     * @return string
     */
    abstract public function getCreateStatement();
    
    /**
     * Table definition
     * 
     * @param Stephino_Rpg_Db $dbObject
     */
    public function __construct(Stephino_Rpg_Db $dbObject) {
        $this->_db = $dbObject;
    }
    
    /**
     * Get the current Table Name
     * 
     * @return string
     */
    public function __toString() {
        return $this->getTableName();
    }
    
    /**
     * Get the DataBase object
     * 
     * @return Stephino_Rpg_Db
     */
    protected function getDb() {
        return $this->_db;
    }
    
    /**
     * Get the current table name using late static binding
     * 
     * @return string
     */
    public final function getTableName() {
        return $this->getDb()->getPrefix() . static::NAME;
    }
    
    /**
     * Get the object info by ID; caching supported
     * 
     * @param int     $objectId Object ID
     * @param boolean $useCache (optional) Store the value in cache; when disabled, overrides cached values; default <b>false</b>
     * @return array|null
     */
    public final function getById($objectId, $useCache = false) {
        // Sanitize the id
        $objectId = abs((int) $objectId);
        
        // Cache not created
        if (!$useCache || !isset($this->_cache[$objectId])) {
            $this->_cache[$objectId] = $this->getDb()->getWpDb()->get_row(
                Stephino_Rpg_Utils_Db::selectAll(
                    $this->getTableName(),
                    array(
                        static::COL_ID => $objectId
                    )
                ), 
                ARRAY_A
            );
        }
        
        return $this->_cache[$objectId];
    }
    
    /**
     * Get specific entities by IDs
     * 
     * Get the object info by ID; caching supported
     * @param int[]   $objectIds Object IDs
     * @param boolean $useCache  (optional) Store the values in cache; when disabled, overrides cached values; default <b>false</b>
     * @return array|null
     */
    public function getByIds($objectIds, $useCache = false) {
        if (!is_array($objectIds) || !count($objectIds)) {
            return null;
        }
        
        // Prepare the result
        $result = array();
        
        // Prepare the values to look for
        $objectIdsNeeded = array();
        
        // Go through the data
        foreach ($objectIds as $objectId) {
            // Sanitize the element
            $objectId = abs((int) $objectId);
            
            // Cache check
            if ($useCache && isset($this->_cache[$objectId])) {
                $result[$objectId] = $this->_cache[$objectId];
            } else {
                // Cache disabled or failed
                $objectIdsNeeded[] = $objectId;
            }
        }
        
        // Need to fetch more data
        $objectIdsNeeded = array_unique($objectIdsNeeded);
        if (count($objectIdsNeeded)) {
            // Get the results
            $dbResults = $this->getDb()->getWpDb()->get_results(
                Stephino_Rpg_Utils_Db::selectAll(
                    $this->getTableName(),
                    array(
                        static::COL_ID => $objectIdsNeeded
                    )
                ), 
                ARRAY_A
            );
            
            // Valid data found
            if (is_array($dbResults) && count($dbResults)) {
                foreach ($dbResults as $dbRow) {
                    // Table structure changed
                    if (!isset($dbRow[static::COL_ID])) {
                        $result = null;
                        break;
                    }
                    
                    // Prepare the item ID
                    $dbItemId = abs((int) $dbRow[static::COL_ID]);
                    
                    // Append to the results
                    $result[$dbItemId] = $dbRow;
                    
                    // Store in cache as well
                    if ($useCache) {
                        $this->_cache[$dbItemId] = $dbRow;
                    }
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Update a row by ID
     * 
     * @param array $columnValues Associative array of {column name} => {column value} pairs
     * @param int   $id           Table ID column value
     * @return int|false The number of rows updated, or false on error.
     */
    public function updateById($columnValues, $id) {
        $result = false;
        
        do {
            // Must be an array
            if (!is_array($columnValues)) {
                break;
            }

            // Update the elements
            $result = $this->getDb()->getWpDb()->query(
                Stephino_Rpg_Utils_Db::update(
                    $this->getTableName(), 
                    $columnValues, 
                    array(
                        static::COL_ID => abs((int) $id)
                    )
                )
            );
        } while(false);
        
        return $result;
    }
    
    /**
     * Delete a row by ID
     * 
     * @param int $id Table ID column value
     * @return int|false The number of rows updated, or false on error.
     */
    public function deleteById($id) {
        return $this->getDb()->getWpDb()->query(
            Stephino_Rpg_Utils_Db::delete(
                $this->getTableName(), 
                array(
                    static::COL_ID => abs((int) $id)
                )
            )
        );
    }
}

/*EOF*/