<?php

/**
 * Stephino_Rpg_Renderer_Ajax_Action_Tutorial
 * 
 * @title      Action::Tutorial
 * @desc       Tutorial actions
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
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
            if(!is_array($dbRow = current(is_array($resourcesWorker->getData()) ? $resourcesWorker->getData() : array()))) {
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
                if (null !== $multiUpdateQuery = Stephino_Rpg_Utils_Db::getMultiUpdate(
                    $updatesUser, 
                    Stephino_Rpg_Db::get()->tableUsers()->getTableName(), 
                    Stephino_Rpg_Db_Table_Users::COL_ID
                )) {
                    Stephino_Rpg_Db::get()->getWpDb()->query($multiUpdateQuery);
                }
            }

            // Update the city resources
            if (count($updatesCity)) {
                if (null !== $multiUpdateQuery = Stephino_Rpg_Utils_Db::getMultiUpdate(
                    $updatesCity, 
                    Stephino_Rpg_Db::get()->tableCities()->getTableName(), 
                    Stephino_Rpg_Db_Table_Cities::COL_ID
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
            if(!is_array($dbRow = current(is_array($resourcesWorker->getData()) ? $resourcesWorker->getData() : array()))) {
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
                if (null !== $multiUpdateQuery = Stephino_Rpg_Utils_Db::getMultiUpdate(
                    $updatesUser, 
                    Stephino_Rpg_Db::get()->tableUsers()->getTableName(), 
                    Stephino_Rpg_Db_Table_Users::COL_ID
                )) {
                    Stephino_Rpg_Db::get()->getWpDb()->query($multiUpdateQuery);
                }
            }

            // Update the city resources
            if (count($updatesCity)) {
                if (null !== $multiUpdateQuery = Stephino_Rpg_Utils_Db::getMultiUpdate(
                    $updatesCity, 
                    Stephino_Rpg_Db::get()->tableCities()->getTableName(), 
                    Stephino_Rpg_Db_Table_Cities::COL_ID
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
     * @param Stephino_Rpg_Config_Tutorial $tutorialObject Tutorial Level object
     * @param array                        $updatesUser    User updates
     * @param array                        $updatesCity    City updates
     * @param boolean                      $skipMode       (optional) Collect the rewards in Skip mode; default <b>false</b>
     */
    protected static function _getRewardsPayload(Stephino_Rpg_Config_Tutorial $tutorialObject, &$updatesUser, &$updatesCity, $skipMode = false) {
        do {
            // Checkpoint
            if ($tutorialObject->getTutorialIsCheckPoint()) {
                // Prepare the resources worker
                $resourcesWorker = Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY);

                // Get the latest from the DB
                if(!is_array($dbRow = current(is_array($resourcesWorker->getData()) ? $resourcesWorker->getData() : array()))) {
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
                $updatesUser[$userId][Stephino_Rpg_Db_Table_Users::COL_USER_TUTORIAL_LEVEL] = $tutorialObject->getId();

                // No rewards in skip mode
                if ($skipMode && !$tutorialObject->getTutorialRewardOnSkip()) {
                    break;
                }

                // Prepare the reward payload
                $resourcesList = array(
                    Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD     => $tutorialObject->getTutorialCheckPointRewardGold(),
                    Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH => $tutorialObject->getTutorialCheckPointRewardResearch(),
                    Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM      => $tutorialObject->getTutorialCheckPointRewardGem(),
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA   => $tutorialObject->getTutorialCheckPointRewardAlpha(),
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_BETA    => $tutorialObject->getTutorialCheckPointRewardBeta(),
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_GAMMA   => $tutorialObject->getTutorialCheckPointRewardGamma(),
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1 => $tutorialObject->getTutorialCheckPointRewardExtra1(),
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2 => $tutorialObject->getTutorialCheckPointRewardExtra2(),
                );

                // Reward resources available
                if (array_sum($resourcesList) > 0) {
                    Stephino_Rpg_Db::get()->modelMessages()->sendNotification(
                        Stephino_Rpg_TimeLapse::get()->userId(), 
                        esc_html__('Tutorial reward', 'stephino-rpg'), 
                        Stephino_Rpg_TimeLapse::TEMPLATE_NOTIF_TUTORIAL_REWARDS, 
                        array($tutorialObject, $resourcesList)
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