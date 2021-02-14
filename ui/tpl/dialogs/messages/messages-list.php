<?php
/**
 * Template:Dialog:Message
 * 
 * @title      Message list dialog
 * @desc       Template for listing messages
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

?>
<div data-role="message-icon" data-msg-type="<?php echo $messageType;?>"></div>
<?php if (is_array($messageData) && count($messageData)):?>
    <div data-role="message-holder">
        <?php 
            foreach($messageData as $message):
        ?>
            <div class="row col-12 no-gutters framed align-items-center message">
                <div class="col-6 col-lg-8 order-first">
                    <button 
                        class="btn w-100 <?php echo ($message[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_READ] ? 'btn-default' : 'btn-danger');?>"
                        data-click="messageRead" data-click-multi="true"
                        data-click-args="<?php echo $messageType;?>,<?php echo $message[Stephino_Rpg_Db_Table_Messages::COL_ID];?>">
                        <span>
                            <?php echo Stephino_Rpg_Utils_Lingo::escape($message[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_SUBJECT]);?>
                        </span>
                    </button>
                </div>
                <div class="col-6 col-lg-4 text-right">
                    <span class="text-small">
                        <?php echo date('M j, H:i', $message[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_TIME]);?>
                    </span>
                    <button 
                        class="dot dot-warning" 
                        title="<?php echo esc_attr__('Delete message', 'stephino-rpg');?>"
                        data-click="messageDelete" 
                        data-click-args="<?php echo $message[Stephino_Rpg_Db_Table_Messages::COL_ID];?>">
                        &times;
                    </button>
                </div>
                <div class="col-12" data-role="message-content"></div>
            </div>
        <?php endforeach;?>
        <div class="col-12 text-center">
            <?php 
                require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                    Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_PAGINATION
                );
            ?>
        </div>
    </div>
<?php else:?>
    <div class="col-12 text-center framed">
        <?php echo esc_html__('You have no messages', 'stephino-rpg');?>
    </div>
<?php endif;?>