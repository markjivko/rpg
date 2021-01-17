<?php

/**
 * Stephino_Rpg_Db_Table_ReasearchFields
 * 
 * @title      Table:ReasearchFields
 * @desc       Holds the users information
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Db_Table_ResearchFields extends Stephino_Rpg_Db_Table {
    
    /**
     * Research Fields Table Name
     */
    const NAME = 'research_fields';
    
    /**
     * Research Field ID
     * 
     * @var int
     */
    const COL_ID = 'research_field_id';
    
    /**
     * User ID
     * 
     * @var int
     */
    const COL_RESEARCH_FIELD_USER_ID = 'research_field_user_id';
    
    /**
     * Configuration ID
     * 
     * @var int
     */
    const COL_RESEARCH_FIELD_CONFIG_ID = 'research_field_config_id';
    
    /**
     * Level
     * 
     * @var int
     */
    const COL_RESEARCH_FIELD_LEVEL = 'research_field_level';
    
    /**
     * Table creation SQL statement
     * 
     * @return string
     */
    public function getCreateStatement() {
        return "CREATE TABLE `$this` (
    `" . self::COL_ID . "` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `" . self::COL_RESEARCH_FIELD_USER_ID . "` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_RESEARCH_FIELD_CONFIG_ID . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `" . self::COL_RESEARCH_FIELD_LEVEL . "` int(11) UNSIGNED NOT NULL DEFAULT '0',
    UNIQUE KEY `" . self::COL_ID . "` (`" . self::COL_ID . "`)
);";
    }
    
    /**
     * Create a Research Field
     * 
     * @param int $userId                User ID
     * @param int $researchFieldConfigId Research Field Configuration ID
     * @param int $researchFieldLevel    Research Field Level
     * @return int|null New Research Field ID or null on error
     */
    public function create($userId, $researchFieldConfigId, $researchFieldLevel) {
        // Execute the query
        $result = $this->getDb()->getWpDb()->insert(
            $this->getTableName(), 
            array(
                self::COL_RESEARCH_FIELD_USER_ID   => abs((int) $userId),
                self::COL_RESEARCH_FIELD_CONFIG_ID => abs((int) $researchFieldConfigId),
                self::COL_RESEARCH_FIELD_LEVEL     => abs((int) $researchFieldLevel),
            )
        );

        // Get the new entity ID
        return (false === $result ? null : $this->getDb()->getWpDb()->insert_id);
    }
    
    /**
     * Get a Research Field by User ID and Research Configuration ID
     * 
     * @param int $userId                User ID
     * @param int $researchFieldConfigId Research Field Configuration ID
     * @return array|null Research Field Row or null on error
     */
    public function getByUserAndConfig($userId, $researchFieldConfigId) {
        return $this->getDb()->getWpDb()->get_row(
            "SELECT * FROM `$this`"
            . " WHERE `" . self::COL_RESEARCH_FIELD_USER_ID . "` = '" . intval($userId) . "'"
            . " AND `" . self::COL_RESEARCH_FIELD_CONFIG_ID . "` = '" . intval($researchFieldConfigId) . "'",
            ARRAY_A
        );
    }
    
    /**
     * Get all Research Fields belonging to a user
     * 
     * @param int $userId User ID
     * @return array|null Research Fields or null
     */
    public function getByUser($userId) {
        $result = $this->getDb()->getWpDb()->get_results(
            "SELECT * FROM `$this`"
            . " WHERE `" . self::COL_RESEARCH_FIELD_USER_ID . "` = '" . abs((int) $userId) . "'",
            ARRAY_A
        );
        return is_array($result) && count($result) ? $result : null;
    }
    
    /**
     * Delete research fields by user ID
     * 
     * @param int $userId User ID
     * @return int|false The number of rows deleted or false on error
     */
    public function deleteByUser($userId) {
        return $this->getDb()->getWpDb()->delete(
            $this->getTableName(),
            array(
                self::COL_RESEARCH_FIELD_USER_ID => abs((int) $userId),
            )
        );
    }
    
}

/*EOF*/