<?php

/**
 * Stephino_Rpg_Task_Cron
 * 
 * @title      Cron Task
 * @desc       Heartbeat
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Task_Cron {

    /**
     * Run the robots time-lapses<br/>
     * A maximum of <b>config.core.robotTimeLapsesPerRequest</b> robot accounts 
     * are handled per player or per site visitor (identified by IP address)
     */
    public static function robots() {
        // Initalize the World
        Stephino_Rpg_Task_Initializer::initWorld();
        
        // Get the last executed robot cron time
        $lastCronTime = 0;
        if (Stephino_Rpg_TimeLapse::get()->userId()) {
            if (is_array($userData = Stephino_Rpg_TimeLapse::get()->userData())) {
                $lastCronTime = intval($userData[Stephino_Rpg_Db_Table_Users::COL_USER_LAST_TICK_ROBOT]);
            }
        } else {
            $transientKey = Stephino_Rpg::OPTION_CACHE . '_ltr_' . md5(
                isset($_SERVER['REMOTE_ADDR']) 
                    ? $_SERVER['REMOTE_ADDR'] 
                    : '1'
            );
            $lastCronTime = intval(get_site_transient($transientKey));
        }
        
        // Not more often than every x seconds
        if (time() - $lastCronTime >= Stephino_Rpg_Config::get()->core()->getTimeLapseCooldown()) {
            // Update the cache to prevent other processes from running this task
            if (Stephino_Rpg_TimeLapse::get()->userId()) {
                Stephino_Rpg_Db::get()->tableUsers()->updateById(
                    array(Stephino_Rpg_Db_Table_Users::COL_USER_LAST_TICK_ROBOT => time()), 
                    Stephino_Rpg_TimeLapse::get()->userId()
                );
            } else {
                set_site_transient($transientKey, time(), 3600);
            }

            // Get x random robots
            $robotRows = Stephino_Rpg_Db::get()->tableUsers()->getRandom(
                Stephino_Rpg_Config::get()->core()->getRobotTimeLapsesPerRequest(),
                true
            );
            if (is_array($robotRows)) {
                foreach ($robotRows as $robotRow) {
                    // Get ther robot id
                    $robotId = intval($robotRow[Stephino_Rpg_Db_Table_Users::COL_ID]);

                    // Log the robot ID
                    Stephino_Rpg_Log::check() && Stephino_Rpg_Log::info('Cron - Robot: id = ' . $robotId);

                    try {
                        // Set the workspace
                        Stephino_Rpg_TimeLapse::setWorkspace(null, $robotId);

                        // Run the time-lapse
                        Stephino_Rpg_TimeLapse::get()->run();
                    } catch(Exception $exc) {
                        Stephino_Rpg_Log::check() && Stephino_Rpg_Log::warning(
                            "Task_Cron.robots, robot #$robotId: {$exc->getMessage()}"
                        );
                    }
                }
            }

            // Reset the workspace
            Stephino_Rpg_TimeLapse::setWorkspace();
        }
    }
    
    /**
     * Run the current player time-lapse
     * 
     * @param boolean $ajaxOrigin   (optional) The task originated from an AJAX request; default <b>false</b>
     * @param boolean $dialogOrigin (optional) The task originated from an AJAX Dialog request; default <b>false</b>
     */
    public static function player($ajaxOrigin = false, $dialogOrigin = false) {
        // Initalize the World
        Stephino_Rpg_Task_Initializer::initWorld();
        
        // Initialize the current user (must be logged in)
        if ($userId = Stephino_Rpg_Task_Initializer::initUser()) {
            // Log the user ID
            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::info('Cron - Player: id = ' . $userId);
                    
            // Run the time-lapse for the current player
            Stephino_Rpg_TimeLapse::get()->run($ajaxOrigin, $dialogOrigin);
        }
    }
    
    /**
     * Gather hourly statistics
     */
    public static function statistics() {
        // Prepare the transient key
        $transientKey = Stephino_Rpg::OPTION_CACHE . '_stats';
        
        // Cache expired after 1 hour
        if (false === get_site_transient($transientKey)) {
            // Set a site-wide transient to avoid re-runs for 15 minutes
            set_site_transient($transientKey, time(), 900);
            
            // Gather the statistics
            Stephino_Rpg_Db::get()->modelStatistics()->gather();
        }
    }
}

/*EOF*/