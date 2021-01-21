<?php
/**
 * Stephino_Rpg_TimeLapse_Support_ResearchFields
 * 
 * @title      Time-Lapse::Support::ResearchFields
 * @desc       Manage the support ResearchFields time-lapse
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_TimeLapse_Support_ResearchFields extends Stephino_Rpg_TimeLapse_Abstract {

    /**
     * Time-Lapse::Support::ResearchFields
     */
    const KEY = 'Support_ResearchFields';
    
    /**
     * Stepper
     * 
     * @param int $checkPointTime  UNIX timestamp
     * @param int $checkPointDelta Time difference in seconds from the last timestamp
     */
    public function step($checkPointTime, $checkPointDelta) {
        $this->_stepTime = $checkPointTime;
        
        // Nothing to do - Support structure
    }
    
    /**
     * Get the definition for removals on save()
     * 
     * @return array
     */
    protected function _getDeleteStructure() {
        // Research fields cannot be removed by time-lapse
        return array();
    }

    /**
     * Get the table structure that needs to be updated on save()
     * 
     * @return array
     */
    protected function _getUpdateStructure() {
        return array(
            $this->getDb()->tableResearchFields()->getTableName() => array(
                Stephino_Rpg_Db_Table_ResearchFields::COL_ID,
                array(
                    Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_LEVEL,
                ),
            ),
        );
    }

    /**
     * Initialize the worker data
     * 
     * @return array
     */
    protected function _initData() {
        return $this->getDb()->getWpdb()->get_results(
            "SELECT * FROM `" . $this->getDb()->tableResearchFields() . "`"
            . " WHERE `" . $this->getDb()->tableResearchFields() . "`.`" . Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_USER_ID . "` = '" . $this->_userId . "'",
            ARRAY_A
        );
    }
}

/*EOF*/