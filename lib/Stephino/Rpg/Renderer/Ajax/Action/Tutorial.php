<?php

/**
 * Stephino_Rpg_Renderer_Ajax_Action_Tutorial
 * 
 * @title      Action::Tutorial
 * @desc       Tutorial actions
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Renderer_Ajax_Action_Tutorial extends Stephino_Rpg_Renderer_Ajax_Dialog {

    // Request keys
    const REQUEST_TUTORIAL_ID = 'tutorialId';
    
    /**
     * Tutorial - Next step
     * 
     * @param array $data Data containing <ul>
     * <li><b>tutorialId</b> (int) Tutorial level ID</li>
     * </ul>
     * @return array|null
     */
    public static function ajaxNext($data) {
        $result = null;
        
        do {
            // Get the specified tutorial level
            $tutorialLevel = isset($data[self::REQUEST_TUTORIAL_ID]) ? intval($data[self::REQUEST_TUTORIAL_ID]) : 0;

            // Prepare the resources worker
            $resourcesWorker = Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY);

            // Get the latest from the DB
            if(!is_array($resourcesWorker->getData())) {
                break;
            }
            if (!is_array($dbRow = current($resourcesWorker->getData()))) {
                break;
            }

            // Invalid tutorial level, get the last one from the database
            if (null === $tutorialObject = Stephino_Rpg_Config::get()->tutorials()->getById($tutorialLevel)) {
                $tutorialLevel = intval($dbRow[Stephino_Rpg_Db_Table_Users::COL_USER_TUTORIAL_LEVEL]) + 1;
                if (null === $tutorialObject = Stephino_Rpg_Config::get()->tutorials()->getById($tutorialLevel)) {
                    break;
                }
            }

            // Tried to re-run the one of the last checkpoints
            if ($tutorialLevel <= intval($dbRow[Stephino_Rpg_Db_Table_Users::COL_USER_TUTORIAL_LEVEL])) {
                break;
            }

            // Prepare the user updates (tutorial level - in case of a checkpoint, rewards)
            $updatesUser = array();

            // Prepare the city updates (rewards)
            $updatesCity = array();

            // Get the payload
            self::_getRewardsPayload($tutorialObject, $updatesUser, $updatesCity);

            // Update the user resources
            if (count($updatesUser)) {
                if (null !== $multiUpdateQuery = Stephino_Rpg_Utils_Db::multiUpdate(
                    Stephino_Rpg_Db::get()->tableUsers()->getTableName(), 
                    Stephino_Rpg_Db_Table_Users::COL_ID,
                    $updatesUser
                )) {
                    Stephino_Rpg_Db::get()->getWpDb()->query($multiUpdateQuery);
                }
            }

            // Update the city resources
            if (count($updatesCity)) {
                if (null !== $multiUpdateQuery = Stephino_Rpg_Utils_Db::multiUpdate(
                    Stephino_Rpg_Db::get()->tableCities()->getTableName(), 
                    Stephino_Rpg_Db_Table_Cities::COL_ID,
                    $updatesCity
                )) {
                    Stephino_Rpg_Db::get()->getWpDb()->query($multiUpdateQuery);
                }
            }

            // Automatically advance to the next step; the "wrap" method will handle next step validation
            Stephino_Rpg_Renderer_Ajax::setTutorialStep($tutorialObject->getId() + 1);

            // Wrap the result
            $result = Stephino_Rpg_Renderer_Ajax::wrap(array());
        } while(false);
        
        return $result;
    }
    
    /**
     * Tutorial - Skip
     * 
     * @return array|null
     */
    public static function ajaxSkip() {
        $result = null;
        
        do {
            // Prepare the resources worker
            $resourcesWorker = Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY);

            // Get the latest from the DB
            if(!is_array($resourcesWorker->getData())) {
                break;
            }
            if (!is_array($dbRow = current($resourcesWorker->getData()))) {
                break;
            }

            // Prepare the user updates (tutorial level - in case of a checkpoint, rewards)
            $updatesUser = array();

            // Prepare the city updates (rewards)
            $updatesCity = array();

            // Get the payload
            $tutorialObject = null;
            foreach (Stephino_Rpg_Config::get()->tutorials()->getAll() as $tutorialObject) {
                if (!$tutorialObject->getTutorialIsCheckPoint()) {
                    continue;
                }
                self::_getRewardsPayload($tutorialObject, $updatesUser, $updatesCity, true);
            }

            // Update the user resources
            if (count($updatesUser)) {
                if (null !== $multiUpdateQuery = Stephino_Rpg_Utils_Db::multiUpdate(
                    Stephino_Rpg_Db::get()->tableUsers()->getTableName(), 
                    Stephino_Rpg_Db_Table_Users::COL_ID,
                    $updatesUser
                )) {
                    Stephino_Rpg_Db::get()->getWpDb()->query($multiUpdateQuery);
                }
            }

            // Update the city resources
            if (count($updatesCity)) {
                if (null !== $multiUpdateQuery = Stephino_Rpg_Utils_Db::multiUpdate(
                    Stephino_Rpg_Db::get()->tableCities()->getTableName(), 
                    Stephino_Rpg_Db_Table_Cities::COL_ID,
                    $updatesCity
                )) {
                    Stephino_Rpg_Db::get()->getWpDb()->query($multiUpdateQuery);
                }
            }

            // Automatically advance to the next step; the "wrap" method will handle next step validation
            Stephino_Rpg_Renderer_Ajax::setTutorialStep(null !== $tutorialObject ? $tutorialObject->getId() + 1 : 1);
            $result = Stephino_Rpg_Renderer_Ajax::wrap(array());
        } while(false);
        
        return $result;
    }
    
    /**
     * Prepare the update payloads for the User and City tables
     * 
     * @param Stephino_Rpg_Config_Tutorial $tutorialConfig Tutorial Level object
     * @param array                        $updatesUser    User updates
     * @param array                        $updatesCity    City updates
     * @param boolean                      $skipMode       (optional) Collect the rewards in Skip mode; default <b>false</b>
     */
    protected static function _getRewardsPayload(Stephino_Rpg_Config_Tutorial $tutorialConfig, &$updatesUser, &$updatesCity, $skipMode = false) {
        do {
            // Checkpoint
            if ($tutorialConfig->getTutorialIsCheckPoint()) {
                // Prepare the resources worker
                $resourcesWorker = Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY);

                // Get the latest from the DB
                if(!is_array($resourcesWorker->getData())) {
                    break;
                }
                if (!is_array($dbRow = current($resourcesWorker->getData()))) {
                    break;
                }

                // Store the current user ID
                $userId = Stephino_Rpg_TimeLapse::get()->userId();

                // Store the current city ID
                $cityId = $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CITY_ID];

                // Prepare the maximum storage
                $maxStorage = floatval($dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_STORAGE]);

                // Initialize the live table update
                if (!isset($updatesUser[$userId])) {
                    $updatesUser[$userId] = array();
                }

                // Store the CheckPoint
                $updatesUser[$userId][Stephino_Rpg_Db_Table_Users::COL_USER_TUTORIAL_LEVEL] = $tutorialConfig->getId();

                // No rewards in skip mode
                if ($skipMode && !$tutorialConfig->getTutorialRewardOnSkip()) {
                    break;
                }

                // Prepare the reward payload
                $resourcesList = array(
                    Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD     => $tutorialConfig->getTutorialCheckPointRewardGold(),
                    Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH => $tutorialConfig->getTutorialCheckPointRewardResearch(),
                    Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM      => $tutorialConfig->getTutorialCheckPointRewardGem(),
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA   => $tutorialConfig->getTutorialCheckPointRewardAlpha(),
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_BETA    => $tutorialConfig->getTutorialCheckPointRewardBeta(),
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_GAMMA   => $tutorialConfig->getTutorialCheckPointRewardGamma(),
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1 => $tutorialConfig->getTutorialCheckPointRewardExtra1(),
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2 => $tutorialConfig->getTutorialCheckPointRewardExtra2(),
                );

                // Reward resources available
                if (array_sum($resourcesList) > 0) {
                    Stephino_Rpg_Db::get()->modelMessages()->notify(
                        Stephino_Rpg_TimeLapse::get()->userId(), 
                        Stephino_Rpg_Db_Model_Messages::TEMPLATE_NOTIF_TUTORIAL_REWARDS, 
                        array(
                            // tutorialConfigId
                            $tutorialConfig->getId()
                        ),
                        Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_ECONOMY
                    );

                    // Go through the rewards table
                    foreach ($resourcesList as $columnName => $rewardAmount) {
                        // Item not set or invalid
                        if (null === $rewardAmount || $rewardAmount < 0) {
                            continue;
                        }

                        // Prepare the new column value
                        $columnValue = floatval($dbRow[$columnName]) + $rewardAmount;

                        // Handle the resources allocation
                        switch ($columnName) {
                            case Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD:
                            case Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH:
                            case Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM:
                                // Update the references
                                $resourcesWorker->updateRef(
                                    Stephino_Rpg_Db_Table_Users::COL_ID, 
                                    $userId, 
                                    $columnName, 
                                    $columnValue
                                );

                                // Store the values that need updating
                                $updatesUser[$userId][$columnName] = $columnValue;
                                break;

                            default:
                                // Max storage
                                if ($columnValue > $maxStorage) {
                                    $columnValue = $maxStorage;
                                }

                                // Initialize the live table update
                                if (!isset($updatesCity[$cityId])) {
                                    $updatesCity[$cityId] = array();
                                }

                                // Update the references
                                $resourcesWorker->updateRef(
                                    Stephino_Rpg_Db_Table_Cities::COL_ID, 
                                    $cityId, 
                                    $columnName, 
                                    $columnValue
                                );

                                // Store the values that need updating
                                $updatesCity[$cityId][$columnName] = $columnValue;
                        }
                    }
                }
            }
        } while(false);
    }
}

/*EOF*/