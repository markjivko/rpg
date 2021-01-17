<?php
/**
 * Stephino_Rpg_Utils_Db
 * 
 * @title     Utils:DB
 * @desc      DataBase string utils
 * @copyright (c) 2020, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Utils_Db {

    /**
     * Prepare a multi-update statement.
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
        // Prepare the cases
        $columnCases = array();
        
        // Prepare the ids list
        $idsList = array();
        
        // Parse the field information
        if (is_array($fieldsInfo)) {
            foreach ($fieldsInfo as $id => $fields) {
                if (is_numeric($id) && is_array($fields) && count($fields)) {
                    // Store the ID that needs an update
                    $idsList[] = "'$id'";

                    // Go through the fields
                    foreach ($fields as $columnName => $columnValue) {
                        // Remove invalid characters
                        $columnName = preg_replace('%\W+%', '', $columnName);
                        
                        // Initialize the switch array
                        if (!isset($columnCases[$columnName])) {
                            $columnCases[$columnName] = array();
                        }

                        // Store the case
                        $columnCases[$columnName][$id] = addslashes($columnValue);
                    }
                }
            }
        }
        
        // No IDs modified
        if (!count($idsList) || !count($columnCases)) {
            return null;
        }
        
        // Clean-up the table name and primary key column
        $tableName = preg_replace('%\W+%', '', $tableName);
        $tableIdColumn = preg_replace('%\W+%', '', $tableIdColumn);
        
        // Invalid values
        if (!strlen($tableName) || !strlen($tableIdColumn)) {
            return null;
        }
        
        // Prepare the query
        $query = "UPDATE `$tableName` SET" . PHP_EOL;
        
        // Prepare the switches
        $switches = array();
        
        // Go through the cases
        foreach ($columnCases as $columnName => $casesIdValue) {
            // Prepare the switch statement
            $switch = " `$columnName` = CASE `$tableIdColumn`" . PHP_EOL;
            
            // Go through the cases
            foreach ($casesIdValue as $caseId => $caseValue) {
                $switch .= "     WHEN '$caseId' THEN '$caseValue'" . PHP_EOL;
            }
            
            // Close the switch statement
            $switch .= "     ELSE `$columnName` END";
            
            // Append to the list
            $switches[] = $switch;
        }
        
        // Append the switches to the query
        $query .= implode(', ' . PHP_EOL, $switches) . PHP_EOL;
        
        // Prepare the ids list
        $idsListString = implode(', ', $idsList);
        
        // Append the where clause
        $query .= " WHERE `$tableIdColumn` IN ($idsListString)";
        
        // Log the multi-update query
        Stephino_Rpg_Log::debug($query);
        
        // All done
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
        // Clean-up the table name and primary key column
        $tableName = preg_replace('%\W+%', '', $tableName);
        $tableIdColumn = preg_replace('%\W+%', '', $tableIdColumn);
        
        // Invalid values
        if (!strlen($tableName) || !strlen($tableIdColumn)) {
            return null;
        }
        
        // Prepare the IDs list
        if (!is_array($idsList)) {
            return null;
        }
        
        // Convert everything to integers
        $idsList = array_map(function($item){
            return "'$item'";
        }, array_filter($idsList, function($item) {
            return is_numeric($item);
        }));
        
        // No valid values provided
        if (!count($idsList)) {
            return null;
        }
        
        // Prepare the ids list
        $idsListString = implode(', ', $idsList);
        
        // Prepare the query
        $query = "DELETE FROM `$tableName`" . PHP_EOL;
        
        // Append the where clause
        $query .= " WHERE `$tableIdColumn` IN ($idsListString)";
        
        // Log the multi-update query
        Stephino_Rpg_Log::debug($query);
        
        // All done
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
        // Validate the rows list
        if (!is_array($rowsList) || !count($rowsList)) {
            return null;
        }
        
        // Validate the table name
        $tableName = preg_replace('%\W+%i', '', $tableName);
        if (!strlen($tableName)) {
            return null;
        }
        
        // Prepare the column names
        $columnNames = array();
        
        // Prepare the colun values
        $columnValuesStrings = array();
        
        // Go through the rows
        foreach ($rowsList as $row) {
            // Must be a row
            if (!is_array($row)) {
                return null;
            }
            
            // Define the colun names for the first time
            if (!count($columnNames)) {
                foreach (array_keys($row) as $columnName) {
                    // Invalid column
                    if (!strlen($columnName)) {
                        return null;
                    }
                    
                    // Remove unwanted characters
                    $columnName = esc_sql($columnName);
                    
                    // Store the column name
                    $columnNames[] = "`$columnName`";
                }
            }
            
            // Append the value set
            $columnValuesStrings[] = '(' . implode(', ', array_map(function($columnValue) {
                // Escape the value
                $columnValue = esc_sql($columnValue);
                
                // Store in the final SQL form
                return "'$columnValue'";
            }, $row)) . ')';
        }
        
        // Prepare the colun names string
        $columnNamesString = '(' . implode(', ', $columnNames) . ')';
        
        // Prepare the query
        $query = "INSERT INTO `$tableName` $columnNamesString" . PHP_EOL;
        
        // Append the where clause
        $query .= " VALUES " . implode(',' . PHP_EOL . ' ', $columnValuesStrings);
        
        // Log the multi-update query
        Stephino_Rpg_Log::debug($query);
        
        // All done
        return $query;
    }
    
}

/* EOF */