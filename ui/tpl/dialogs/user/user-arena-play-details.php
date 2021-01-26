<?php
/**
 * Template:Dialog:User Arena
 * 
 * @title      User Arena dialog - Play Details
 * @desc       Template for playing a platformer
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $ptfId int*/
/* @var $ptfOwn boolean*/
/* @var $ptfRow array*/
/* @var $authorId int*/
/* @var $authorName string*/
$canDelete = (0 !== $authorId && (is_super_admin() || $ptfOwn));
?>
<div class="col-12 framed p-4">
    <h4>
        <?php if ($ptfOwn && Stephino_Rpg_Db_Table_Ptfs::PTF_VISIBILITY_PRIVATE === $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_VISIBILITY]): ?>
            <i><?php echo esc_html__('Private', 'stephino-rpg');?></i> &#x25C9;
        <?php endif;?>
        <?php echo esc_html($ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_NAME]);?>
        <span title="<?php echo esc_attr__('Width and height', 'stephino-rpg');?>">
            (<?php echo $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_WIDTH];?>&times;<?php echo $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_HEIGHT];?>)
        </span>
        <?php if (0 !== $authorId):?>
            <?php echo esc_html__('by', 'stephino-rpg');?>
            <span 
                data-click="dialog" 
                data-click-args="dialogUserArenaList,<?php echo (int) $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_USER_ID];?>">
                <span><b><?php echo esc_html($authorName);?></b></span>
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
    <div class="row">
        <?php if ($ptfOwn): ?>
            <div class="col-<?php echo ($ptfOwn && $canDelete ? 6 : 12);?>">
                <button 
                    class="btn w-100"
                    data-click="dialogNH"
                    data-click-args="dialogUserArenaEdit,<?php echo $ptfId;?>">
                    <span><?php echo esc_html__('Edit game', 'stephino-rpg');?></span>
                </button>
            </div>
        <?php endif; ?>
        <?php if ($canDelete):?>
            <div class="col-<?php echo ($ptfOwn && $canDelete ? 6 : 12);?>">
                <button 
                    class="btn btn-warning w-100"
                    data-click="ptfArenaDelete"
                    data-click-args="<?php echo $ptfId;?>">
                    <span><?php echo esc_html__('Delete game', 'stephino-rpg');?></span>
                </button>
            </div>
        <?php endif;?>
    </div>
</div>