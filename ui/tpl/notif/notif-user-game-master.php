<?php
/**
 * Template:Message:User GM toggle
 * 
 * @title      Player promotion/demotion
 * @desc       Template for game master status change
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

if (Stephino_Rpg_Db_Model_Messages::isValidNotifData($notifData, 1)):
    /* @var $gameMaster boolean */
    list($gameMaster) = $notifData;
?>
    <div class="col-12">
        <div class="row justify-content-center">
            <div class="col-12">
                <?php 
                    if ($gameMaster) {
                        echo __('Congratulations! You are now a Game Master.', 'stephino-rpg');
                    } else {
                        echo __('You are no longer a Game Master.', 'stephino-rpg');
                    }
                ?>
            </div>
        </div>
    </div>
<?php endif;?>