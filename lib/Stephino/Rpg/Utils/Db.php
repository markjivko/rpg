<?php
/**
 * Stephino_Rpg_Utils_Db
 * 
 * @title     Utils:DB
 * @desc      DataBase string utils
 * @copyright (c) 2021, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Utils_Db {

    /**
     * Regular expression to sanitize table and column names
     */
    const REGEX_SANITIZE_NAMES = '%\W+%i';
    
    /**
     * MySQL Real Escape String<ul>
     * <li><b>null</b> becomes the string NULL</li>
     * <li><b>true</b> becomes the string NOT NULL</li>
     * <li><b>int</b> is not escaped</li>
     * <li><b>float</b> becomes 4 digit string representations</li>
     * <li><b>string|mixed</b> is escaped using mysqli_real_escape_string</li>
     * </ul>
     * 
     * @param mixed $value Value to escape
     * @return string Escaped string
     */
    public static function escape($value) {
        switch (true) {
            case is_null($value):
                $result = 'NULL';
                break;
            
            case true === $value:
                $result = 'NOT NULL';
                break;
            
            case is_int($value):
                $result = $value;
                break;
            
            case is_float($value):
                $result = sprintf("'%.4f'", $value);
                break;
            
            default:
                // MySQL Real Escape by reference
                Stephino_Rpg_Db::get()->getWpDb()->escape_by_ref($value);

                // Remove WP's hammer approach to security
                $result = "'" . Stephino_Rpg_Db::get()->getWpDb()->remove_placeholder_escape($value) . "'";
        }
        
        return $result;
    }

    /**
     * Select all entries in the table that satisfy a simple AND where clause
     * 
     * @param string     $tableName      Table name
     * @param array|null $andWhereClause (optional) Simple AND where clause associative array; default <b>null</b>
     * @param int        $limitCount     (optional) Query limit; default <b>null</b>
     * @param int        $limitOffset    (optional) Query limit offset; default <b>null</b>
     * @param string     $orderBy        (optional) Query order by column; default <b>null</b>
     * @param boolean    $orderAsc       (optional) Query order ascending; default <b>true</b>
     * @return string|null Select * statement or null on error
     */
    public static function selectAll($tableName, $andWhereClause = null, $limitCount = null, $limitOffset = null, $orderBy = null, $orderAsc = true) {
        $query = null;
        
        do {
            // Validate the table name
            if (!strlen($tableName = preg_replace(self::REGEX_SANITIZE_NAMES, '', $tableName))) {
                break;
            }
            
            // Validate AND where clause
            if (is_array($andWhereClause)) {
                if (null === $stringWhereClause = self::_parseWhereClause($andWhereClause)) {
                    break;
                }
            }
            
            // Sanitize the order by argument
            if (null !== $orderBy) {
                if (!strlen($orderBy = preg_replace(self::REGEX_SANITIZE_NAMES, '', $orderBy))) {
                    $orderBy = null;
                }
            }
            
            // Prepare the query
            $query = "SELECT * FROM `$tableName`"
                . (is_array($andWhereClause) ? (' ' . PHP_EOL . "WHERE $stringWhereClause") : '')
                . (null !== $orderBy
                    ? (' ' . PHP_EOL . "  ORDER BY `$orderBy` " . ($orderAsc ? 'ASC' : 'DESC'))
                    : ''
                )
                . (null !== $limitCount
                    ? (' ' . PHP_EOL . '  LIMIT '. (null !== $limitOffset ? (intval($limitOffset) . ', ') : '') . intval($limitCount))
                    : ''
                );
            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debugParent($query . PHP_EOL);
            
        } while(false);
        
        // Log the errors
        Stephino_Rpg_Log::check() && null === $query && Stephino_Rpg_Log::errorParent(__METHOD__, func_get_args());
        
        return $query;
    }
    
    /**
     * Update entries in the table that satisfy a simple AND where clause
     * 
     * @param string $tableName      Table name
     * @param array  $columnValues   Associative array of {column name} => {column value} pairs
     * @param array  $andWhereClause Simple AND where clause associative array
     * @return string|null Single update statement or null on error
     */
    public static function update($tableName, $columnValues, $andWhereClause) {
        $query = null;
        
        do {
            // Validate the table name
            if (!strlen($tableName = preg_replace(self::REGEX_SANITIZE_NAMES, '', $tableName))) {
                break;
            }
            
            // Validate columns values
            if (null === $stringTablePairs = self::_parsePairs($columnValues, true)) {
                break;
            }
            
            // Validate AND where clause
            if (null === $stringWhereClause = self::_parseWhereClause($andWhereClause)) {
                break;
            }
            
            // Prepare the query
            $query = "UPDATE `$tableName` SET " . PHP_EOL
                . "$stringTablePairs " . PHP_EOL 
                . "WHERE $stringWhereClause";
            
            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debugParent($query . PHP_EOL);
            
        } while(false);
        
        // Log the errors
        Stephino_Rpg_Log::check() && null === $query && Stephino_Rpg_Log::errorParent(__METHOD__, func_get_args());
        
        return $query;
    }
    
    /**
     * Prepare a multi-update by ID statement
     * 
     * @param string $tableName     Table name
     * @param string $tableIdColumn Table identifier column name
     * @param array  $fieldsArray   Fields information. Associative array of<ul>
     * <li><b>(int)</b> ID => array(<ul>
     *     <li><b>(string)</b> column name => <b>(string|float)</b> column value</li>
     * </ul>)
     * </li>
     * </ul>
     * @param boolean $logNulls     (optional) Log cases where a NULL value must be returned (empty field arrays); default <b>true</b>
     * @return string|null Multi-update statement or null on error
     */
    public static function multiUpdate($tableName, $tableIdColumn, $fieldsArray, $logNulls = true) {
        $query = null;
        
        do {
            // Not a valid input
            if (!is_array($fieldsArray)) {
                break;
            }
            
            // Clean-up
            foreach ($fieldsArray as $id => $fields) {
                if (!is_numeric($id) || !is_array($fields) || !count($fields)) {
                    unset($fieldsArray[$id]);
                }
            }
            
            // Nothing to update
            if (!count($fieldsArray)) {
                break;
            }
            reset($fieldsArray);
            
            // Clean-up the table name and primary key column
            $tableName = preg_replace(self::REGEX_SANITIZE_NAMES, '', $tableName);
            $tableIdColumn = preg_replace(self::REGEX_SANITIZE_NAMES, '', $tableIdColumn);
            
            // Invalid values
            if (!strlen($tableName) || !strlen($tableIdColumn)) {
                break;
            }
            
            // One update
            if (1 === count($fieldsArray)) {
                $queryId = (int) current(array_keys($fieldsArray));
                $queryFields = current($fieldsArray);
                
                // Validate the fields
                if ($queryId <= 0 || !is_array($queryFields) || !count($queryFields)) {
                    break;
                }
                
                // Prepare the query
                $query = "UPDATE `$tableName` SET " . PHP_EOL;
                
                // Prepare the sets
                $querySets = array();
                foreach ($queryFields as $columnName => $columnValue) {
                    $sanitName = preg_replace(self::REGEX_SANITIZE_NAMES, '', $columnName);
                    
                    // Prepare the operator
                    $operator = is_null($columnValue) || true === $columnName ? 'IS' : '=';
                    
                    // Append the query set
                    $querySets[] = "  `$sanitName` $operator " . self::escape($columnValue);
                }
                
                // Append the columns to the query
                $query .= implode(', ' . PHP_EOL, $querySets) . ' ' . PHP_EOL;
                
                // Append the where clause
                $query .= "WHERE `$tableIdColumn` = $queryId";
            } else {
                // Multi-update; prepare the cases
                $columnCases = array();

                // Prepare the ids list
                $idsList = array();

                // Parse the field information
                foreach ($fieldsArray as $id => $fields) {
                    $id = abs((int) $id);
                    
                    // Store the ID that needs an update
                    $idsList[] = $id;

                    // Go through the fields
                    foreach ($fields as $columnName => $columnValue) {
                        // Remove invalid characters
                        if (strlen($columnName = preg_replace(self::REGEX_SANITIZE_NAMES, '', $columnName))) {
                            // Initialize the switch array
                            if (!isset($columnCases[$columnName])) {
                                $columnCases[$columnName] = array();
                            }

                            // Store the case
                            $columnCases[$columnName][$id] = self::escape($columnValue);
                        }
                    }
                }

                // No IDs modified
                if (!count($idsList) || !count($columnCases)) {
                    break;
                }

                // Prepare the query
                $query = "UPDATE `$tableName` SET " . PHP_EOL;

                // Prepare the switches
                $switches = array();

                // Go through the cases
                foreach ($columnCases as $columnName => $casesIdValue) {
                    // Prepare the switch statement
                    $switch = "`$columnName` = CASE `$tableIdColumn` " . PHP_EOL;

                    // Go through the cases
                    foreach ($casesIdValue as $caseId => $caseValue) {
                        $switch .= "  WHEN $caseId THEN $caseValue " . PHP_EOL;
                    }

                    // Close the switch statement
                    $switch .= "  ELSE `$columnName` END";

                    // Append to the list
                    $switches[] = $switch;
                }

                // Append the switches to the query
                $query .= implode(', ' . PHP_EOL, $switches) . ' ' . PHP_EOL;

                // Prepare the ids list
                $idsListString = implode(', ', $idsList);

                // Append the where clause
                $query .= "WHERE `$tableIdColumn` IN ($idsListString)";
            }

            // Log the multi-update query
            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debugParent($query . PHP_EOL);
            
        } while(false);
        
        // Log the errors
        Stephino_Rpg_Log::check() && null === $query && $logNulls && Stephino_Rpg_Log::errorParent(__METHOD__, func_get_args());
        
        return $query;
    }
    
    /**
     * Delete all entries in the table that satisfy a simple AND where clause
     * 
     * @param string $tableName      Table name
     * @param array  $andWhereClause Simple AND where clause associative array
     * @return string|null Single delete statement or null on error
     */
    public static function delete($tableName, $andWhereClause) {
        $query = null;
        
        do {
            // Validate the table name
            if (!strlen($tableName = preg_replace(self::REGEX_SANITIZE_NAMES, '', $tableName))) {
                break;
            }
            
            // Validate the where clause
            if (null === $stringWhereClause = self::_parseWhereClause($andWhereClause)) {
                break;
            }
            
            // Prepare the query
            $query = "DELETE FROM `$tableName` " . PHP_EOL
                . "WHERE $stringWhereClause";
            
            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debugParent($query . PHP_EOL);
        } while(false);
        
        // Log the errors
        Stephino_Rpg_Log::check() && null === $query && Stephino_Rpg_Log::errorParent(__METHOD__, func_get_args());
        
        return $query;
    }
    
    /**
     * Prepare a multi-delete by ID statement
     * 
     * @param string $tableName     Table name
     * @param string $tableIdColumn Table identifier column name
     * @param array  $idsList       List of IDs
     * @return string|null Multi-delete statement or null on error
     */
    public static function multiDelete($tableName, $tableIdColumn, $idsList) {
        $query = null;
        
        do {
            // Prepare the IDs list
            if (!is_array($idsList)) {
                break;
            }
            
            // Clean-up the table name and primary key column
            $tableName = preg_replace(self::REGEX_SANITIZE_NAMES, '', $tableName);
            $tableIdColumn = preg_replace(self::REGEX_SANITIZE_NAMES, '', $tableIdColumn);
            
            // Invalid values
            if (!strlen($tableName) || !strlen($tableIdColumn)) {
                break;
            }

            // Convert everything to integers and escape
            $idsList = array_unique(
                array_map(
                    function($item) {
                        return abs((int) $item);
                    }, 
                    array_filter(
                        $idsList, 
                        function($item) {
                            return is_numeric($item);
                        }
                    )
                )
            );

            // No valid values provided
            if (!count($idsList)) {
                break;
            }

            // Prepare the ids list
            $idsListString = implode(', ', $idsList);

            // Prepare the query
            $query = "DELETE FROM `$tableName` " . PHP_EOL;

            // Append the where clause
            $query .= "WHERE `$tableIdColumn` IN ($idsListString)";
            
            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debugParent($query . PHP_EOL);
        } while(false);
        
        // Log the errors
        Stephino_Rpg_Log::check() && null === $query && Stephino_Rpg_Log::errorParent(__METHOD__, func_get_args());
        
        return $query;
    }
    
    /**
     * Prepare a single-row DB insert
     * 
     * @param string $tableName    Table name
     * @param array  $columnValues Associative array of {column name} => {column value}
     * @return string|null Single insert statement or null on error
     */
    public static function insert($tableName, $columnValues) {
        $query = null;
        
        do {
            // Validate the table name
            if (!strlen($tableName = preg_replace(self::REGEX_SANITIZE_NAMES, '', $tableName))) {
                break;
            }
            
            // Get the columns and values
            list($tableColumns, $tableValues) = self::_parsePairs($columnValues);
            if (!is_array($tableColumns) || !is_array($tableValues)) {
                break;
            }
            
            // Prepare the query
            $query = "INSERT INTO `$tableName` (" . implode(', ', $tableColumns) . ")" . PHP_EOL 
                . "  VALUES (" . implode(', ', $tableValues) . ")";
            
            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debugParent($query . PHP_EOL);
        } while(false);
        
        // Log the errors
        Stephino_Rpg_Log::check() && null === $query && Stephino_Rpg_Log::errorParent(__METHOD__, func_get_args());
        
        return $query;
    }
    
    /**
     * Prepare a multi-insert statement
     * 
     * @param string $tableName Table name
     * @param array  $rowsArray  List of rows; each row must be the same length, associative
     * @return string|null Multi-insert statement or null on error
     */
    public static function multiInsert($tableName, $rowsArray) {
        $query = null;
        
        do {
            // Validate the table name
            if (!strlen($tableName = preg_replace(self::REGEX_SANITIZE_NAMES, '', $tableName))) {
                break;
            }
            
            // Validate the rows list
            if (!is_array($rowsArray) || !count($rowsArray)) {
                break;
            }

            // Prepare the column names
            $columnNames = array();

            // Prepare the colun values
            $columnValuesStrings = array();

            // Go through the rows
            foreach ($rowsArray as $row) {
                if (!is_array($row)) {
                    break 2;
                }

                // Define the colun names for the first time
                if (!count($columnNames)) {
                    foreach (array_keys($row) as $columnName) {
                        // Remove unwanted characters
                        $columnName = preg_replace(self::REGEX_SANITIZE_NAMES, '', $columnName);

                        // Invalid column
                        if (!strlen($columnName)) {
                            break 3;
                        }

                        // Store the column name
                        $columnNames[] = "`$columnName`";
                    }
                }

                // Append the value set
                $columnValuesStrings[] = '(' 
                    . implode(
                        ', ', 
                        array_map(
                            function($columnValue) {
                                return self::escape($columnValue);
                            }, 
                            $row
                        )
                    ) 
                    . ')';
            }

            // Prepare the colun names string
            $columnNamesString = '(' . implode(', ', $columnNames) . ')';

            // Prepare the query
            $query = "INSERT INTO `$tableName` $columnNamesString" . PHP_EOL;

            // Append the where clause
            $query .= "  VALUES " . implode(',' . PHP_EOL . '  ', $columnValuesStrings);

            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debugParent($query . PHP_EOL);
        } while(false);
        
        // Log the errors
        Stephino_Rpg_Log::check() && null === $query && Stephino_Rpg_Log::errorParent(__METHOD__, func_get_args());
        
        return $query;
    }
    
    /**
     * Parse and escape table column-value pairs
     * 
     * @param array   $columnValues Associative array of {column name} => {column value}
     * @param boolean $returnString Return a string instead of unassociative array
     * @return array|string|null Array of <ul>
     * <li><b>array|null</b> Table Columns</li>
     * <li><b>array|null</b> Table Values</li>
     * </ul> OR <b>string/null</b> MySQL "{column name} = {column value}, ..." statement if the <b>$returnString</b> parameter is true
     */
    protected static function _parsePairs($columnValues, $returnString = false) {
        // Prepare the result
        $result = array(
            // Columns
            null, 
            // Values
            null
        );
        
        do {
            // Invalid pairs
            if (!is_array($columnValues) || !count($columnValues)) {
                break;
            }
            
            // Prepare the table columns
            $tableColumns = array_filter(
                array_map(
                    function($columnName) {
                        // Remove unwanted characters
                        $columnName = preg_replace(self::REGEX_SANITIZE_NAMES, '', $columnName);

                        // Filter-out invalid column names
                        return strlen($columnName) ? $columnName : false;
                    },
                    array_keys($columnValues)
                )
            );

            // Invalid columns detected
            if (count($columnValues) !== count($tableColumns)) {
                break;
            }

            // Prepare the table values
            $result = array(
                $tableColumns,
                array_map(
                    function($columnValue) {
                        return self::escape($columnValue);
                    }, 
                    array_values($columnValues)
                )
            );
        } while(false);
        
        // Prepare the result as a string
        if ($returnString) {
            if (!is_array($result[1])) {
                $result = null;
            } else {
                // Prepare the table pairs
                $tablePairs = array();
                
                // Append the column name - column value
                foreach ($result[1] as $tvKey => $tvValue) {
                    // Prepare the operator
                    $operator = in_array(strtolower($tvValue), array('null', 'not null')) ? 'IS' : '=';
                    
                    // Append the pair
                    $tablePairs[] = "  `" . $result[0][$tvKey] . "` $operator $tvValue";
                }
                
                // Store the result as a string list
                $result = implode(', ' . PHP_EOL, $tablePairs);
            }
        }
        
        return $result;
    }
    
    /**
     * Prepare and escape a simple AND where clause; if the value is an array, it will be treated as an "IN" statement
     * 
     * @param array $andWhereClause Associative array of {column name} => {column value} pairs
     * @return string|null "{column name} = {column value} [... AND {column name} = {column value}]" or null on error
     */
    protected static function _parseWhereClause($andWhereClause) {
        $whereClauseString = null;
        
        do {
            // Invalid where clause array
            if (!is_array($andWhereClause) || !count($andWhereClause)) {
                break;
            }
            
            // Prepare the table columns
            $tableColumns = array_filter(
                array_map(
                    function($columnName) {
                        // Remove unwanted characters
                        $columnName = preg_replace(self::REGEX_SANITIZE_NAMES, '', $columnName);
                        
                        // Filter-out invalid column names
                        return strlen($columnName) ? $columnName : false;
                    },
                    array_keys($andWhereClause)
                )
            );
                    
            // Invalid columns detected
            if (count($andWhereClause) !== count($tableColumns)) {
                break;
            }
            
            // Prepare the table values (unassociative array)
            $tablePairs = array();
            foreach (array_values($andWhereClause) as $wcKey => $whValue) {
                if (is_array($whValue)) {
                    // Escape individual items
                    $whValueEscaped = array_map(
                        function($item) {
                            return self::escape($item);
                        }, 
                        $whValue
                    );
                        
                    // IN statement
                    $tablePairs[] = "`" . $tableColumns[$wcKey] . "` IN ( " . implode(', ', $whValueEscaped) . " )";
                } else {
                    // Prepare the operator
                    $operator = is_null($whValue) || true === $whValue ? 'IS' : '=';
                    
                    // Regular equality statement
                    $tablePairs[] = "`" . $tableColumns[$wcKey] . "` $operator " . self::escape($whValue);
                }
            }
            $whereClauseString = implode(' AND ', $tablePairs);
        } while(false);
        
        return $whereClauseString;
    }
}

/* EOF */