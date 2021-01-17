<?php
/**
 * Template:Dialog:User Arena
 * 
 * @title      User Arena dialog - Play
 * @desc       Template for playing a platformer
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $ptfId int*/
/* @var $nextPtfId int|null*/
/* @var $ptfOwn boolean*/
?>
<div data-role="ptf-embed" class="framed" data-effect="ptfListener" data-effect-args="<?php echo $ptfId;?>" style="position: relative; overflow: hidden;">
    <svg viewBox="0 0 <?php echo Stephino_Rpg_Db::get()->modelPtfs()->getViewBox(true);?>" style="display: block; width: 100%; position: relative; z-index: 0;"></svg>
    <iframe 
        src="<?php 
            echo esc_attr(Stephino_Rpg_Utils_Media::getAdminUrl(true) . '&' . http_build_query(array(
                Stephino_Rpg_Renderer_Ajax::CALL_VIEW      => Stephino_Rpg_Renderer_Ajax::VIEW_PTF,
                Stephino_Rpg_Renderer_Ajax::CALL_VIEW_DATA => $ptfId
            )));
        ?>" 
        allowfullscreen="true" 
        referrerpolicy="same-origin" 
        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none; z-index: 1; overflow: hidden; background: transparent;"></iframe>
</div>
<?php if ($ptfOwn):?>
<div class="col-12 framed p-4">
    <button 
        class="btn w-100"
        data-click="dialogNH"
        data-click-args="dialogUserArenaEdit,<?php echo $ptfId;?>">
        <span><?php echo esc_html__('Edit game', 'stephino-rpg');?></span>
    </button>
</div>
<?php endif; ?>
<div class="framed active row" data-role="ptf-popup">
    <span class="col-12">
        <span class="success">
            <h4><?php echo esc_html__('Congratulations!', 'stephino-rpg');?></h4>
            <div class="ptf-reward text-center mb-2 d-none">
                <span class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_GEM;?>">
                <div class="icon"></div>
                    <span>
                        <?php echo esc_html__('You have earned', 'stephino-rpg');?> <span class="value"></span> <b><?php echo Stephino_Rpg_Config::get()->core()->getResourceGemName(true);?></b>
                    </span>
                </span>
            </div>
        </span>
        <span class="failure">
            <h4><?php echo esc_html__('Game over', 'stephino-rpg');?></h4>
        </span>
    </span>
    <span class="col">
        <button 
            class="btn w-100"
            data-click="dialogNH"
            data-click-args="dialogUserArenaPlay,<?php echo $ptfId;?>">
            <span><?php echo esc_html__('Play again', 'stephino-rpg');?></span>
        </button>
    </span>
    <?php if (null !== $nextPtfId):?>
        <span class="col">
            <button 
                class="btn btn-warning w-100"
                data-click="dialogNH"
                data-click-args="dialogUserArenaPlay,<?php echo $nextPtfId;?>">
                <span><?php echo esc_html__('Next game', 'stephino-rpg');?></span>
            </button>
        </span>
    <?php endif;?>
</div>