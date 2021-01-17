<?php
/**
 * Template:Dialog:Message
 * 
 * @title      Message content
 * @desc       Template for the message content
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

?>
<?php if(is_array($messageData)):?>
    <div class="col-12 text-center p-2">
        <h5>
            <span>
                <?php echo Stephino_Rpg_Utils_Lingo::escape($messageData[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_SUBJECT]);?>
            </span>
        </h5>
    </div>
    <div class="col-12">
        <?php echo $messageData[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_CONTENT]; ?>
    </div>
<?php else: ?>
    <div class="col-12 text-center">
        <?php echo esc_html__('Message not found', 'stephino-rpg');?>
    </div>
<?php endif;?>