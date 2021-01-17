<?php
/**
 * Template:Dialog:Island
 * 
 * @title      Island dialog
 * @desc       Template for empty island lots
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();
?>
<div class="row">
    <div class="col-12 text-center">
        <div class="framed">
            <img class="city-icon" src="<?php echo Stephino_Rpg_Utils_Lingo::escape($islandIconUrl);?>"/>
        </div>
        <?php if (count(Stephino_Rpg_Renderer_Ajax_Action::getEntityConfigs(Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_COLONIZER))):?>
            <button
                class="btn w-100"
                data-click="cityColonizeReviewButton"
                data-click-args="<?php echo $islandId;?>,<?php echo $islandSlot;?>">
                <span><?php echo esc_html__('Colonize', 'stephino-rpg');?></span>
            </button>
        <?php endif;?>
    </div>
</div>