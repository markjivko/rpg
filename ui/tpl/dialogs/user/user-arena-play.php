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
/* @var $nextPtfId int|null*/
/* @var $ptfOwn boolean*/
/* @var $ptfRow array*/
/* @var $authorId int*/
/* @var $authorName string*/
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
<div class="col-12 framed p-4">
    <h4>
        <?php if ($ptfOwn && Stephino_Rpg_Db_Table_Ptfs::PTF_VISIBILITY_PRIVATE === $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_VISIBILITY]): ?>
            <i><?php echo esc_html__('Private', 'stephino-rpg');?></i> &#x25C9;
        <?php endif;?>
        <?php echo esc_html($ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_NAME]);?> &#x25C9;
        <span title="<?php echo esc_attr__('Width and height', 'stephino-rpg');?>">
            <?php echo $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_WIDTH];?>&times;<?php echo $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_HEIGHT];?>
        </span>
        <?php if (0 !== $authorId):?>
            <span 
                data-click="dialog" 
                data-click-args="dialogUserArenaList,<?php echo (int) $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_USER_ID];?>">
                <span>
                    <?php echo esc_html__('by', 'stephino-rpg');?> <b><?php echo esc_html($authorName);?></b>
                </span>
            </span>
        <?php endif;?>
    </h4>
    <div class="row">
        <div class="col-12 col-lg-6">
            <span class="label" data-html="true" title="<?php 
                echo esc_attr(sprintf(
                    __('Started %s, finished %s', 'stephino-rpg'),
                    '<b>' . number_format($ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_STARTED] + 1) . '</b>&times;',
                    '<b>' . number_format($ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_FINISHED]) . '</b>&times;'
                ));
            ?>">
                <span>
                    &#x1f3c1; <?php echo esc_html__('Times played', 'stephino-rpg');?>: 
                    <b><?php echo Stephino_Rpg_Utils_Lingo::isuFormat($ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_FINISHED]);?></b>
                </span>
            </span>
        </div>
        <div class="col-12 col-lg-6">
            <span class="label" data-html="true" title="<?php 
                echo esc_attr(sprintf(
                    __('Won %s', 'stephino-rpg'),
                    '<b>' . number_format($ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_FINISHED_WON]) . '</b>&times;'
                ));
            ?>">
                <span>
                    &#x1f3c6; <?php echo esc_html__('Times won', 'stephino-rpg');?>:
                    <b><?php echo Stephino_Rpg_Utils_Lingo::isuFormat($ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_FINISHED_WON]);?></b>
                </span>
            </span>
        </div>
        <?php if (Stephino_Rpg_Config::get()->core()->getPtfRewardPlayer()):?>
            <div class="col-12 col-lg-6">
                <span class="label">
                    <span>
                        <span 
                            data-effect="help"
                            data-effect-args="<?php echo Stephino_Rpg_Config_Core::KEY;?>,<?php echo Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_GAME_ARENA;?>">
                            <?php echo esc_html__('Reward', 'stephino-rpg');?>:
                        </span>
                        <?php if ($ptfRow[Stephino_Rpg_Db_Model_Ptfs::PTF_EXTRA_REWARD]):?>
                            <b><?php echo number_format($ptfRow[Stephino_Rpg_Db_Model_Ptfs::PTF_EXTRA_REWARD]);?></b>
                            <?php echo Stephino_Rpg_Config::get()->core()->getResourceGemName(true);?>
                        <?php else:?>
                            <?php echo esc_html__('None', 'stephino-rpg');?>
                        <?php endif;?>
                    </span>
                </span>
            </div>
        <?php endif;?>
        <div class="col-12 col-lg-6 text-center">
            <span class="label" data-html="true" title="<?php 
                echo (
                    0 !== $authorId 
                    ? esc_attr(
                        __('Created', 'stephino-rpg') . ' ' 
                        . '<b>' . date('Y-m-d', (int) $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_CREATED_TIME]) . '</b><br/>'
                        . __('Modified', 'stephino-rpg') . ' '
                        . '<b>' . date('Y-m-d', (int) $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_MODIFIED_TIME]) . '</b>'
                    )
                    : ''
                );
            ?>">
                <span>
                    <?php echo esc_html__('Version', 'stephino-rpg');?>:
                    <b><?php echo number_format($ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_VERSION]);?></b>
                </span>
            </span>
        </div>
    </div>
    <?php if ($ptfOwn): ?>
        <button 
            class="btn w-100"
            data-click="dialogNH"
            data-click-args="dialogUserArenaEdit,<?php echo $ptfId;?>">
            <span><?php echo esc_html__('Edit game', 'stephino-rpg');?></span>
        </button>
    <?php endif; ?>
</div>
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