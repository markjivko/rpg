<?php
/**
 * Template: User contact
 * 
 * @title      User contact
 * @desc       Template used for direct messages between users; this content is parsed and stored verbatim in the DB (no i18n support)
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/**
 * @param array  $senderInfo
 * @param int    $senderId
 * @param string $messageContent
 */
?>
<div class="col-12">
    <?php echo Stephino_Rpg_Utils_Lingo::escape($messageContent);?>
    (&#x1F30D; <?php echo Stephino_Rpg_Utils_Lingo::getLanguage();?>)
</div>