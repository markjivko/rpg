<?php
/**
 * Template:Ptf:Author Reward
 * 
 * @title      Platformer author reward
 * @desc       Template for author royalties
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $ptfRow array */
/* @var $playerId int */
/* @var $playerName string */
/* @var $rewardGems int */
list($ptfRow, $playerId, $playerName, $rewardGems) = $notifData;
?>
<div class="row justify-content-center text-center">
    <div class="col-12">
        <?php 
            echo sprintf(
                esc_html__('%s has won your game, %s', 'stephino-rpg'),
                '<span data-click="userViewProfile" data-click-args="' . intval($playerId) . '"><b>' . Stephino_Rpg_Utils_Lingo::escape($playerName) . '</b></span>',
                '<span data-click="dialog" data-click-args="dialogUserArenaPlay,' . intval($ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_ID]) . '">'
                    . Stephino_Rpg_Utils_Lingo::escape($ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_NAME])
                . '</span>'
            );
        ?>
    </div>
    <div class="col-12">
        <span class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_GEM;?>">
            <div class="icon"></div>
            <span>
                <?php echo esc_html__('You have earned', 'stephino-rpg');?> <b><?php echo intval($rewardGems);?></b> <?php echo Stephino_Rpg_Config::get()->core()->getResourceGemName(true);?>
            </span>
        </span>
    </div>
</div>