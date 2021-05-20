<?php
/**
 * Stephino_Rpg_TimeLapse_Support_Entities
 * 
 * @title      Time-Lapse::Support::Entities
 * @desc       Manage the support Entities time-lapse
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_TimeLapse_Support_Entities extends Stephino_Rpg_TimeLapse_Abstract {

    /**
     * Time-Lapse::Support::Entities
     */
    const KEY = 'Support_Entities';
    
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
        // Entities cannot be removed by time-lapse
        return array();
    }

    /**
     * Get the table structure that needs to be updated on save()
     * 
     * @return array
     */
    protected function _getUpdateStructure() {
        return array(
            $this->getDb()->tableEntities()->getTableName() => array(
                Stephino_Rpg_Db_Table_Entities::COL_ID,
                array(
                    Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT,
                )
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
            "SELECT * FROM `" . $this->getDb()->tableEntities() . "`"
            . " WHERE `" . $this->getDb()->tableEntities() . "`.`" . Stephino_Rpg_Db_Table_Entities::COL_ENTITY_USER_ID . "` = '" . $this->_userId . "'",
            ARRAY_A
        );
    }
}

/*EOF*/