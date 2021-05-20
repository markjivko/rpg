<?php
/**
 * Template:Dialog:Message
 * 
 * @title      Message content
 * @desc       Template for the message content
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $messageData array */
?>
<div class="col-12 mt-2">
    <h4 class="m-0">
        <?php if (0 !== (int) $messageData[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_FROM]): ?>
            &#x1F4AC;
            <b>
                <?php if (is_array($senderInfo = Stephino_Rpg_Db::get()->tableUsers()->getById($messageData[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_FROM], true))): ?>
                    <span
                        data-click="userViewProfile"
                        data-click-args="<?php echo abs((int) $messageData[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_FROM]);?>">
                        <?php echo Stephino_Rpg_Utils_Lingo::getUserName($senderInfo);?>
                    </span>
                <?php else:?>
                    <i><?php echo esc_html__('Unknown', 'stephino-rpg');?></i>
                <?php endif;?>:
            </b>
        <?php endif;?>
        <?php echo Stephino_Rpg_Utils_Lingo::escape($messageData[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_SUBJECT]);?>
    </h4>
</div>
<div class="col-12 text-center">
    <?php echo $messageData[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_CONTENT]; ?>
</div>