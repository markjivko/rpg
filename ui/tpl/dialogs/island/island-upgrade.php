<?php
/**
 * Template:Dialog:Island Statue Upgrade
 * 
 * @title      Island Statue Upgrade dialog
 * @desc       Confirm island statue upgrade
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $islandConfig Stephino_Rpg_Config_Island */
/* @var $islandStatueConfig Stephino_Rpg_Config_IslandStatue */
?>
<?php 
    $costTitle = sprintf(
        esc_html__('Upgrade costs for level %s', 'stephino-rpg'),
        '<b>' . ($islandData[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_STATUE_LEVEL] + 1) . '</b>'
    );
    require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
        Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_COSTS
    );
?>
<div class="row">
    <div class="col-6">
        <button class="btn w-100" data-click="goBack">
            <span>
                <b><?php echo esc_html__('Go Back', 'stephino-rpg');?></b>
            </span>
        </button>
    </div>
    <div class="col-6">
        <button 
            class="btn btn-warning w-100" 
            data-click="islandStatueUpgradeButton">
            <span>
                <b><?php echo esc_html__('Upgrade', 'stephino-rpg');?></b>
            </span>
        </button>
    </div>
</div>