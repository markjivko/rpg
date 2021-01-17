<?php
/**
 * Template:Dialog:User Arena
 * 
 * @title      User Arena dialog - List
 * @desc       Template for listing available platformers
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $userData array */
/* @var $ptfsList array */
/* @var $canCreate boolean */
?>
<div class="col-12 framed p-4">
    <div class="row">
        <div class="col-12 text-right mb-4">
            <span 
                data-effect="help"
                data-effect-args="<?php echo Stephino_Rpg_Config_Core::KEY;?>,<?php echo Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_GAME_ARENA;?>">
                <?php echo esc_html__('Game arena', 'stephino-rpg');?>
            </span>
        </div>
    </div>
    <div class="row">
        <?php if (!count($ptfsList)):?>
            <div class="col-12 text-center">
                <?php echo esc_html__('There are no games available', 'stephino-rpg');?>
            </div>
        <?php else:?>
            <?php foreach ($ptfsList as $ptfRow):?>
                <div class="col-4 col-sm-3 col-lg-2" data-click="dialog" data-click-args="dialogUserArenaPlay,<?php echo (int) $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_ID];?>">
                    <div class="ptf-card">
                        <div data-effect="ptfPreview" data-effect-args="<?php echo implode(',', $ptfRow[Stephino_Rpg_Db_Model_Ptfs::PTF_EXTRA_PREVIEW]);?>"></div>
                        <div data-role="ptf-name"><?php echo Stephino_Rpg_Utils_Lingo::escape($ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_NAME]);?></div>
                        <div data-role="ptf-rating" 
                             data-score="<?php echo number_format($ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_RATING], 2);?>" 
                             data-count="<?php echo number_format($ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_RATING_COUNT]);?>"></div>
                    </div>
                </div>
            <?php endforeach;?> 
        <?php endif;?>
        <?php if ($canCreate) :?>
            <div class="col-12">
                <button 
                    class="btn w-100"
                    data-click="dialog"
                    data-click-args="dialogUserArenaEdit">
                    <span><?php echo esc_html__('Create game', 'stephino-rpg');?></span>
                </button>
            </div>
        <?php endif;?>
    </div>
</div>