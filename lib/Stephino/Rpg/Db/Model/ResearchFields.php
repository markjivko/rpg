<?php
/**
 * Stephino_Rpg_Db_Model_ResearchFields
 * 
 * @title     Model:ResearchFields
 * @desc      ResearchFields Model
 * @copyright (c) 2021, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Db_Model_ResearchFields extends Stephino_Rpg_Db_Model {

    /**
     * Research Fields Model Name
     */
    const NAME = 'research_fields';

    /**
     * Create (or update) a research field by user ID
     * 
     * @param int $userId                User ID
     * @param int $researchFieldConfigId Research field Configuration ID
     * @param int $researchFieldLevel    Research field level
     * @return int Research Field Database ID
     * @throws Exception
     */
    public function setLevel($userId, $researchFieldConfigId, $researchFieldLevel) {
        // Validate the configuration
        if (null === Stephino_Rpg_Config::get()->researchFields()->getById($researchFieldConfigId)) {
            throw new Exception(
                sprintf(
                    __('Invalid configuration (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigResearchFieldName()
                )
            );
        }
        
        // Sanitize the level
        $researchFieldLevel = abs((int) $researchFieldLevel);
        
        // Prepare the Research Field Database ID
        $researchFieldDbId = null;
        
        // Get the research field data
        if (is_array($researchFieldRow = $this->getDb()->tableResearchFields()->getByUserAndConfig($userId, $researchFieldConfigId))) {
            $researchFieldDbId = $researchFieldRow[Stephino_Rpg_Db_Table_ResearchFields::COL_ID];
        }
        
        if (null === $researchFieldDbId) {
            // Attempt to create the research field
            $researchFieldDbId = $this->getDb()->tableResearchFields()->create(
                $userId,
                $researchFieldConfigId,
                $researchFieldLevel
            );
            if (null === $researchFieldDbId) {
                throw new Exception(
                    sprintf(
                        __('Could not create (%s)', 'stephino-rpg'),
                        Stephino_Rpg_Config::get()->core()->getConfigResearchFieldName()
                    )
                );
            }
        } else {
            // Attempt to update the research field
            if (false === $this->getDb()->tableResearchFields()->updateById(
                array(
                    Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_LEVEL => $researchFieldLevel,
                ), 
                $researchFieldDbId
            )) {
                throw new Exception(
                    sprintf(
                        __('Could not update (%s)', 'stephino-rpg'),
                        Stephino_Rpg_Config::get()->core()->getConfigResearchFieldName()
                    )
                );
            }
        }
        
        return $researchFieldDbId;
    }
    
    /**
     * Get the research time
     * 
     * @param Stephino_Rpg_Config_ResearchField $researchFieldConfig Research Field Configuration Object
     * @param int                                 $researchFieldLevel  Research Field Level; ignored and replaced with <b>1</b> if <b>levelsEnabled == false</b>
     * @param int                                 $userId              (optional) User ID; <b>ignored in time-lapse mode</b>
     * @param boolean                             $timelapseMode       (optional) Time-lapse mode; default <b>true</b>
     * @return int Time in seconds
     */
    public function getResearchTime($researchFieldConfig, $researchFieldLevel, $userId = null, $timelapseMode = true) {
        $result = Stephino_Rpg_Utils_Config::getPolyValue(
            Stephino_Rpg_Config::get()->core()->getSandbox() 
                ? null
                : $researchFieldConfig->getCostTimePolynomial(),
            $researchFieldConfig->getLevelsEnabled() ? abs((int) $researchFieldLevel) + 1 : 1, 
            $researchFieldConfig->getCostTime()
        );
        
        // Get the time contraction
        list($timeContraction) = $this->getDb()->modelPremiumModifiers()->getTimeContraction(
            Stephino_Rpg_Db_Model_ResearchFields::NAME, 
            $userId, 
            $timelapseMode
        );
        
        // Valid value
        if ($timeContraction > 1) {
            $result /= $timeContraction;
        }
        return (int) $result;
    }
    
    /**
     * Delete all research fields by user ID
     * 
     * @param int $userId User ID
     * @return int|false The number of rows deleted or false on error
     */
    public function deleteByUser($userId) {
        return $this->getDb()->tableResearchFields()->deleteByUser($userId);
    }
}

/* EOF */