<?php
/**
 * Template:Dialog:Island
 * 
 * @title      Island info dialog
 * @desc       Template for island information
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $islandConfig Stephino_Rpg_Config_Island */
/* @var $islandStatueConfig Stephino_Rpg_Config_IslandStatue */
?>
<div class="row mt-0 framed p-0">
    <div data-effect="parallax" data-effect-args="<?php echo Stephino_Rpg_Config_IslandStatues::KEY;?>,<?php echo $islandStatueConfig->getId();?>"></div>
    <div class="page-help">
        <span 
            data-effect="help"
            data-effect-args="<?php echo Stephino_Rpg_Config_IslandStatues::KEY;?>,<?php echo $islandStatueConfig->getId();?>">
            <?php echo $islandStatueConfig->getName(true);?>
        </span>
        <?php echo esc_html__('on', 'stephino-rpg');?> 
        <span 
            data-effect="help"
            data-effect-args="<?php echo Stephino_Rpg_Config_Islands::KEY;?>,<?php echo $islandConfig->getId();?>">
            <?php echo $islandConfig->getName(true);?>
        </span>
    </div>
</div>
<?php if (count($costData) && count($productionData)):?>
    <div class="row item-level">
        <div class="item-level-badge col-9 col-lg-6">
            <?php if (null !== $islandData && isset($islandData[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_STATUE_LEVEL])):?>
                <div class="label item-level-number">
                    <span>
                        <?php echo esc_html__('Level', 'stephino-rpg');?> <b><?php echo ($islandData[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_STATUE_LEVEL]);?></b>
                    </span>
                </div>
                <?php if (isset($islandData[Stephino_Rpg_Db_Table_Users::COL_ID])):?>
                    <button 
                        class="btn btn-default" 
                        data-click="islandStatueUpgradeDialog">
                        <span><?php echo esc_html__('Upgrade', 'stephino-rpg');?></span>
                    </button>
                <?php endif;?>
            <?php endif;?>
        </div>
    </div>
<?php endif;?>
<div class="framed mb-4">
    <div class="row">
        <h5><span><?php echo esc_html__('Natural resources', 'stephino-rpg');?></span></h5>
    </div>
    <div class="row">
        <div class="col-6">
            <span class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_EXTRA_1;?>">
                <div class="icon"></div>
                <span>
                    <?php 
                        echo sprintf(
                            esc_html__('%s abundance: %s', 'stephino-rpg'),
                            '<b>' . Stephino_Rpg_Config::get()->core()->getResourceExtra1Name(true). '</b>',
                            '<b>' . $islandConfig->getResourceExtra1Abundance() . '</b>%'
                        );
                    ?>
                </span>
            </span>
        </div>
        <div class="col-6">
            <span class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_EXTRA_2;?>">
                <div class="icon"></div>
                <span>
                    <?php 
                        echo sprintf(
                            esc_html__('%s abundance: %s', 'stephino-rpg'),
                            '<b>' . Stephino_Rpg_Config::get()->core()->getResourceExtra2Name(true). '</b>',
                            '<b>' . $islandConfig->getResourceExtra2Abundance() . '</b>%'
                        );
                    ?>
                </span>
            </span>
        </div>
    </div>
</div>
<?php if (count($productionData)):?>
    <div class="framed">
        <?php 
            require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_PRODUCTION
            );
        ?>
    </div>
<?php endif;?>