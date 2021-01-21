<?php
/**
 * Stephino_Rpg_Utils_Db
 * 
 * @title     Utils:DB
 * @desc      DataBase string utils
 * @copyright (c) 2021, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Utils_Db {

    /**
     * MySQL Real Escape String
     * 
     * @param string $string String to escape
     * @return string Escaped string
     */
    public static function escape($string) {
        if (is_float($string)) {
            // At most 4 digits precision
            $result = sprintf('%.4f', $string);
        } else {
            // MySQL Real Escape by reference
            Stephino_Rpg_Db::get()->getWpDb()->escape_by_ref($string);
            
            // Remove WP's hammer approach to security
            $result = Stephino_Rpg_Db::get()->getWpDb()->remove_placeholder_escape($string);
        }
        
        return $result;
    }
    
    /**
     * Prepare a multi-update statement
     * 
     * @param array  $fieldsInfo    Fields information. Associative array of<ul>
     * <li><b>(int)</b> ID => array(<ul>
     *     <li><b>(string)</b> column name => <b>(string|float)</b> column value</li>
     * </ul>)
     * </li>
     * </ul>
     * @param string $tableName     Table name
     * @param string $tableIdColumn Table primary key name
     * @return string|null Multi-update statement or null on error
     */
    public static function getMultiUpdate($fieldsInfo, $tableName, $tableIdColumn) {
        $query = null;
        
        do {
            // Not a valid input
            if (!is_array($fieldsInfo)) {
                break;
            }
            
            // Clean-up
            foreach ($fieldsInfo as $id => $fields) {
                if (!is_numeric($id) || !is_array($fields) || !count($fields)) {
                    unset($fieldsInfo[$id]);
                }
            }
            
            // Nothing to update
            if (!count($fieldsInfo)) {
                break;
            }
            
            // Clean-up the table name and primary key column
            $tableName = preg_replace('%\W+%', '', $tableName);
            $tableIdColumn = preg_replace('%\W+%', '', $tableIdColumn);
            
            // Invalid values
            if (!strlen($tableName) || !strlen($tableIdColumn)) {
                break;
            }
            
            // One update
            if (1 === count($fieldsInfo)) {
                $queryId = (int) current(array_keys($fieldsInfo));
                $queryFields = current($fieldsInfo);
                
                // Validate the fields
                if ($queryId <= 0 || !is_array($queryFields) || !count($queryFields)) {
                    break;
                }
                
                // Prepare the query
                $query = "UPDATE `$tableName` SET " . PHP_EOL;
                
                // Prepare the sets
                $querySets = array();
                foreach ($queryFields as $columnName => $columnValue) {
                    $sanitName = preg_replace('%\W+%', '', $columnName);
                    $sanitValue = self::escape($columnValue);
                    $querySets[] = "  `$sanitName` = '$sanitValue'";
                }
                
                // Append the columns to the query
                $query .= implode(', ' . PHP_EOL, $querySets) . ' ' . PHP_EOL;
                
                // Append the where clause
                $query .= "WHERE `$tableIdColumn` = '$queryId'";
            } else {
                // Multi-update; prepare the cases
                $columnCases = array();

                // Prepare the ids list
                $idsList = array();

                // Parse the field information
                foreach ($fieldsInfo as $id => $fields) {
                    // Store the ID that needs an update
                    $idsList[] = "'$id'";

                    // Go through the fields
                    foreach ($fields as $columnName => $columnValue) {
                        // Remove invalid characters
                        if (strlen($columnName = preg_replace('%\W+%', '', $columnName))) {
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
                        $switch .= "  WHEN '$caseId' THEN '$caseValue' " . PHP_EOL;
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
            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($query);
            
        } while(false);
        
        return $query;
    }
    
    /**
     * Prepare a multi-delete statement
     * 
     * @param array  $idsList       List of IDs
     * @param string $tableName     Table name
     * @param string $tableIdColumn Table identifier column name
     * @return string|null Multi-delete statement or null on error
     */
    public static function getMultiDelete($idsList, $tableName, $tableIdColumn) {
        $query = null;
        
        do {
            // Prepare the IDs list
            if (!is_array($idsList)) {
                break;
            }
            
            // Clean-up the table name and primary key column
            $tableName = preg_replace('%\W+%', '', $tableName);
            $tableIdColumn = preg_replace('%\W+%', '', $tableIdColumn);
            
            // Invalid values
            if (!strlen($tableName) || !strlen($tableIdColumn)) {
                break;
            }

            // Convert everything to integers and escape
            $idsList = array_unique(
                array_map(
                    function($item) {
                        return "'" . abs((int) $item) . "'";
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

            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($query);
        } while(false);
        
        return $query;
    }
    
    /**
     * Prepare a multi-insert statement
     * 
     * @param array  $rowsList  List of rows; each row must be the same length, associative
     * @param string $tableName Table name
     * @return string|null Multi-insert statement or null on error
     */
    public static function getMultiInsert($rowsList, $tableName) {
        $query = null;
        
        do {
            // Validate the rows list
            if (!is_array($rowsList) || !count($rowsList)) {
                break;
            }

            // Validate the table name
            if (!strlen($tableName = preg_replace('%\W+%i', '', $tableName))) {
                break;
            }

            // Prepare the column names
            $columnNames = array();

            // Prepare the colun values
            $columnValuesStrings = array();

            // Go through the rows
            foreach ($rowsList as $row) {
                if (!is_array($row)) {
                    break 2;
                }

                // Define the colun names for the first time
                if (!count($columnNames)) {
                    foreach (array_keys($row) as $columnName) {
                        // Remove unwanted characters
                        $columnName = preg_replace('%\W+%', '', $columnName);

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
                                // Escape the value
                                $columnValue = self::escape($columnValue);

                                // Store in the final SQL form
                                return "'$columnValue'";
                            }, 
                            $row
                        )
                    ) 
                    . ')';
            }

            // Prepare the colun names string
            $columnNamesString = '(' . implode(', ', $columnNames) . ')';

            // Prepare the query
            $query = "INSERT INTO `$tableName` $columnNamesString " . PHP_EOL;

            // Append the where clause
            $query .= "VALUES " . implode(',' . PHP_EOL . '  ', $columnValuesStrings);

            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::debug($query);
        } while(false);
        
        return $query;
    }
    
}

/* EOF */