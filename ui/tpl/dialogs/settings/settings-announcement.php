<?php
/**
 * Template:Dialog:Announcement
 * 
 * @title      Announcement dialog
 * @desc       Template for the Announcement dialog
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $paragraphs string */
?>
<div class="col-12 p-2">
    <div class="advisor"></div>
    <div class="card card-body bg-dark">
        <?php echo $paragraphs;?>
    </div>
</div>