<?php
/**
 * Template:Dialog:Building Upgrade
 * 
 * @title      Building Upgrade dialog
 * @desc       Confirm building upgrade
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $buildingConfig Stephino_Rpg_Config_Building */
$buildingLevel = is_array($buildingData) ? intval($buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL]) : 0;
?>
<div class="framed col-12">
    <?php 
        // Prepare the title
        $costTitle = sprintf(
            0 == $buildingLevel 
                ? esc_html__('Build costs for level %s', 'stephino-rpg') 
                : esc_html__('Upgrade costs for level %s', 'stephino-rpg'),
            '<b>' . ($buildingLevel + 1) . '</b>'
        );
        $costDiscount = Stephino_Rpg_Renderer_Ajax_Action::getDiscount($buildingConfig);
        $costTimeContraction = Stephino_Rpg_Renderer_Ajax_Action::getTimeContraction($buildingConfig);
        require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
            Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_COSTS
        );
    ?>
    <?php 
        require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
            Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_REQUIREMENTS
        );
    ?>
    <div class="row">
        <div
            <?php if ($requirementsMet):?>
                class="col-6"
            <?php else:?>
                class="col-12"
            <?php endif;?>>
            <button class="btn w-100" data-click="goBack">
                <span>
                    <b><?php echo esc_html__('Go Back', 'stephino-rpg');?></b>
                </span>
            </button>
        </div>
        <?php if ($requirementsMet):?>
            <div class="col-6">
                <button 
                    class="btn btn-warning w-100" 
                    data-click="buildingUpgrade">
                    <span>
                        <b><?php echo (0 == $buildingLevel ? esc_html__('Build', 'stephino-rpg') : esc_html__('Upgrade', 'stephino-rpg'));?></b>
                    </span>
                </button>
            </div>
        <?php endif;?>
    </div>
</div>