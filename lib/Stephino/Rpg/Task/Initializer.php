<?php

/**
 * Stephino_Rpg_Task_Initializer
 * 
 * @title      Initializer
 * @desc       Initialization sequences for world and player
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Task_Initializer {

    /**
     * Store the user initialization event
     *
     * @var boolean
     */
    protected static $_hasInitializedUser = false;
    
    /**
     * Initialize the game world: <ul>
     * <li>Regenerate the CSS animation rules if necessary</li>
     * <li>Create the initial islands</li>
     * </ul>
     * 
     * @return boolean
     */
    public static function initWorld() {
        // Regenerate the CSS animation rules
        $animations = Stephino_Rpg_Cache_Game::getInstance()->getValue(Stephino_Rpg_Cache_Game::KEY_ANIMATIONS, array());
        if (!is_array($animations) || !count($animations)) {
            Stephino_Rpg_Renderer_Ajax_Css::generate();
        }
        
        // Prevent multiple runs
        if (Stephino_Rpg_Cache_Game::getInstance()->getValue(Stephino_Rpg_Cache_Game::KEY_WORLD_INIT, false)) {
            return false;
        }
        
        // Get the total number
        if (null !== $islandCount = Stephino_Rpg_Config::get()->core()->getInitialIslandsCount()) {
            // Add the islands
            for ($islandIndex = 1; $islandIndex <= $islandCount; $islandIndex++) {
                try {
                    // Create the island
                    Stephino_Rpg_Db::get()->modelIslands()->create();
                } catch (Exception $exc) {
                    // Nothing to do for now
                }
            }
        }
        
        // Mark the world as initalized
        Stephino_Rpg_Cache_Game::getInstance()->setValue(Stephino_Rpg_Cache_Game::KEY_WORLD_INIT, true);
        
        // All went well
        return true;
    }
    
    /**
     * Initialize the current user (authenticated) or a Robot: <ul>
     * <li>Create islands</li>
     * <li>Create and assign a city</li>
     * <li>Create and assign buildings</li>
     * <li>(human player) Create and initialize robot users</li>
     * </ul><br/>
     * 
     * @param int $robotId (optional) Robot; default <b>null</b>
     * @return int|false New user ID or false on error
     * @throws Exception
     */
    public static function initUser($robotId = null) {
        // Prepare the result
        $result = false;
        
        // Get the database object
        $db = Stephino_Rpg_Db::get($robotId);
        
        do {
            // Guest, move along
            if (null === $robotId && $db->getWpUserId() < 1) {
                break;
            }

            // Get the user data
            $userData = $db->tableUsers()->getData();
                
            // Already initialized
            if (null !== $userData) {
                // Store the user ID
                $userId = intval($userData[Stephino_Rpg_Db_Table_Users::COL_ID]);
                
                // Fully initialized the human account
                if (null === $robotId) {
                    Stephino_Rpg_Log::debug('Player #' . $userId . ' init: skipped');
                    $result = $userId;
                    break;
                }
            } else {
                // Add the user
                if (null === $userId = $db->tableUsers()->create()) {
                    throw new Exception(__('Could not create a new user', 'stephino-rpg'));
                }
            }
            
            // Prepare the log prefix
            $logPrefix = (null === $robotId ? ('Player #' . $userId) : ('Robot #' . $robotId)) . ' ';
            
            // Log the robot ID
            Stephino_Rpg_Log::info($logPrefix . ' init: start');
            
            try {
                // Spawn new islands for human players only
                if (null === $robotId && null !== Stephino_Rpg_Config::get()->core()->getInitialIslandsPerUser()) {
                    for ($islandIndex = Stephino_Rpg_Config::get()->core()->getInitialIslandsPerUser(); $islandIndex >= 1; $islandIndex--) {
                        $db->modelIslands()->create();
                    }
                }

                // Select a random island with vacant lots
                if (null === $islandInfo = $db->tableIslands()->getRandom()) {
                    // All islands are full, create a new one
                    $islandInfo = $db->modelIslands()->create();
                }
                
                /* @var $islandConfig Stephino_Rpg_Config_Island */
                list($islandId, $islandConfig) = $islandInfo;
                
                // Invalid island configuration
                if (null === $islandConfig) {
                    throw new Exception(
                        sprintf(
                            __('Invalid configuration (%s)', 'stephino-rpg'),
                            Stephino_Rpg_Config::get()->core()->getConfigIslandName()
                        )
                    );
                }
                
                /* @var $cityConfig Stephino_Rpg_Config_City */
                list($cityId, $cityConfig) = $db->modelCities()->create($userId, null !== $robotId, $islandId, true);
                
                // Human player
                if (null === $robotId) {
                    // Spawn bots
                    if (null !== Stephino_Rpg_Config::get()->core()->getInitialRobotsPerUser()) {
                        for ($robotIndex = Stephino_Rpg_Config::get()->core()->getInitialRobotsPerUser(); $robotIndex >= 1; $robotIndex--) {
                            try {
                                if (null !== $newRobotId = $db->tableUsers()->create(true)) {
                                    // Handle this on the same thread that created the human player account to avoid duplicates
                                    self::initUser($newRobotId);
                                }
                            } catch (Exception $exc) {}
                        }
                    }
                }
                
                // Initialized the user
                $result = $userId;
                Stephino_Rpg_Log::info($logPrefix . ' init: finish');
                
                // Store the event
                if (null === $robotId) {
                    self::$_hasInitializedUser = $result;
                }
            } catch (Exception $exc) {
                // Remove the user
                $db->modelUsers()->delete($userId);
                
                // Log the error
                Stephino_Rpg_Log::warning($logPrefix . ' init: failure');
                Stephino_Rpg_Log::warning($exc->getMessage(), $exc->getFile(), $exc->getLine());
                
                // Rethrow the exception
                throw $exc;
            }
        } while(false);
        
        // All done
        return $result;
    }
    
    /**
     * Was the current user initialized during this flow?
     * 
     * @return boolean
     */
    public static function hasInitialized() {
        return self::$_hasInitializedUser;
    }
}

/*EOF*/