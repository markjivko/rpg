<?php
/**
 * Template:Dialog:User Arena
 * 
 * @title      User Arena dialog - Play
 * @desc       Template for playing a platformer
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $ptfId int*/
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
<div data-role="ptf-details">
    <?php require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(Stephino_Rpg_Renderer_Ajax_Dialog_User::TEMPLATE_ARENA_PLAY_DETAILS);?>
</div>