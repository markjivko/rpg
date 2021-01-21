<?php
/**
 * Stephino_Rpg_Db_Model_PremiumModifiers
 * 
 * @title     Model:Premium Modifiers
 * @desc      Premium Modifiers Model
 * @copyright (c) 2021, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Db_Model_PremiumModifiers extends Stephino_Rpg_Db_Model {

    /**
     * Premium Modifiers Model Name
     */
    const NAME = 'premium_modifiers';

    /**
     * Get maximum queue size
     * 
     * @param string  $queueType     Queue type, one of 
     * <ul>
     *     <li>Stephino_Rpg_Db_Model_Buildings::NAME</li>
     *     <li>Stephino_Rpg_Db_Model_Entities::NAME</li>
     *     <li>Stephino_Rpg_Db_Model_ResearchFields::NAME</li>
     * </ul>
     * @param int     $userId        (optional) User ID, <b>ignored in time-lapse mode</b>
     * @param boolean $timelapseMode (optional) Using the model in Time-Lapse mode; default <b>true</b>
     * @return array [<ul>
     *     <li>(int) <b>Max. queue</b></li>
     *     <li>(int) <b>Premium Modifier Configuration ID</b> or 0 if none applied</li>
     * </ul>]
     */
    public function getMaxQueue($queueType, $userId = null, $timelapseMode = true) {
        // Get the data set
        $queueRows = $timelapseMode
            ? Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Queues::KEY)->getData()
            : $this->getDb()->tableQueues()->getById($userId, true);
        
        // Initialize the maximum premium value
        $queueMax = 0;
        $queueMaxConfigId = 0;
        if (is_array($queueRows)) {
            foreach ($queueRows as $queueRow) {
                if (Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_PREMIUM == $queueRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_TYPE]) {
                    // Get the premium modifier
                    $premiumConfig = Stephino_Rpg_Config::get()->premiumModifiers()->getById(
                        $queueRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID]
                    );

                    // Valid modifier defined
                    if (null !== $premiumConfig) {
                        switch ($queueType) {
                            // Update the maximum queue set by active premium modifiers
                            case Stephino_Rpg_Db_Model_Buildings::NAME:
                                if ($premiumConfig->getMaxQueueBuildings() > $queueMax) {
                                    $queueMax = $premiumConfig->getMaxQueueBuildings();
                                    $queueMaxConfigId = $premiumConfig->getId();
                                }
                                break;

                            case Stephino_Rpg_Db_Model_Entities::NAME:
                                if ($premiumConfig->getMaxQueueEntities() > $queueMax) {
                                    $queueMax = $premiumConfig->getMaxQueueEntities();
                                    $queueMaxConfigId = $premiumConfig->getId();
                                }
                                break;

                            case Stephino_Rpg_Db_Model_ResearchFields::NAME:
                                if ($premiumConfig->getMaxQueueResearchFields() > $queueMax) {
                                    $queueMax = $premiumConfig->getMaxQueueResearchFields();
                                    $queueMaxConfigId = $premiumConfig->getId();
                                }
                                break;
                        }
                    }
                }
            }
        }
        
        // No premium defined
        if (0 == $queueMax) {
            $queueMaxConfigId = 0;
            switch($queueType) {
                case Stephino_Rpg_Db_Model_Buildings::NAME:
                    $queueMax = Stephino_Rpg_Config::get()->core()->getMaxQueueBuildings();
                    break;

                case Stephino_Rpg_Db_Model_Entities::NAME:
                    $queueMax = Stephino_Rpg_Config::get()->core()->getMaxQueueEntities();
                    break;

                case Stephino_Rpg_Db_Model_ResearchFields::NAME:
                    $queueMax = Stephino_Rpg_Config::get()->core()->getMaxQueueResearchFields();
                    break;
            }
        }
        return array($queueMax, $queueMaxConfigId);
    }
    
    /**
     * Get time contraction - a factor by which queue times are reduced
     * 
     * @param string  $queueType     Queue type, one of 
     * <ul>
     *     <li>Stephino_Rpg_Db_Model_Buildings::NAME</li>
     *     <li>Stephino_Rpg_Db_Model_Entities::NAME</li>
     *     <li>Stephino_Rpg_Db_Model_ResearchFields::NAME</li>
     * </ul>
     * @param int     $userId        (optional) User ID, <b>ignored in time-lapse mode</b>
     * @param boolean $timelapseMode (optional) Using the model in Time-Lapse mode; default <b>true</b>
     * @return array [<ul>
     *     <li>(int) <b>Time contraction</b>; 1 or lower means contraction disabled</li>
     *     <li>(int) <b>Premium Modifier Configuration ID</b> or <b>0</b> if none applied</li>
     * </ul>]
     */
    public function getTimeContraction($queueType, $userId = null, $timelapseMode = true) {
        // Get the data set
        $queueRows = $timelapseMode
            ? Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Queues::KEY)->getData()
            : $this->getDb()->tableQueues()->getById($userId, true);
        
        // Initialize the time contraction value
        $timeContraction = 0;
        $timeContractionConfigId = 0;
        if (is_array($queueRows)) {
            foreach ($queueRows as $queueRow) {
                if (Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_PREMIUM == $queueRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_TYPE]) {
                    // Get the premium modifier
                    $premiumConfig = Stephino_Rpg_Config::get()->premiumModifiers()->getById(
                        $queueRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID]
                    );

                    // Valid modifier defined
                    if (null !== $premiumConfig) {
                        switch ($queueType) {
                            // Update the maximum queue set by active premium modifiers
                            case Stephino_Rpg_Db_Model_Buildings::NAME:
                                if ($premiumConfig->getTimeContractionBuildings() > $timeContraction) {
                                    $timeContraction = $premiumConfig->getTimeContractionBuildings();
                                    $timeContractionConfigId = $premiumConfig->getId();
                                }
                                break;

                            case Stephino_Rpg_Db_Model_Entities::NAME:
                                if ($premiumConfig->getTimeContractionEntities() > $timeContraction) {
                                    $timeContraction = $premiumConfig->getTimeContractionEntities();
                                    $timeContractionConfigId = $premiumConfig->getId();
                                }
                                break;

                            case Stephino_Rpg_Db_Model_ResearchFields::NAME:
                                if ($premiumConfig->getTimeContractionResearchFields() > $timeContraction) {
                                    $timeContraction = $premiumConfig->getTimeContractionResearchFields();
                                    $timeContractionConfigId = $premiumConfig->getId();
                                }
                                break;
                        }
                    }
                }
            }
        }
        return array($timeContraction, $timeContractionConfigId);
    }
}

/* EOF */