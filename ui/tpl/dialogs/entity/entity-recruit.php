<?php
/**
 * Template:Dialog:Entity
 * 
 * @title      Entity recruitment dialog
 * @desc       Template for recruiting entities
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $entityConfig Stephino_Rpg_Config_Unit|Stephino_Rpg_Config_Ship */
?>
<div class="row mt-0 framed p-0">
    <div data-effect="parallax" data-effect-args="<?php echo $entityConfig->keyCollection();?>,<?php echo $entityConfig->getId();?>">
    </div>
    <div class="page-help">
        <span 
            data-effect="help"
            data-effect-args="<?php echo $entityConfig->keyCollection();?>,<?php echo $entityConfig->getId();?>">
            <?php echo $entityConfig->getName(true);?>
        </span>
    </div>
</div>
<div class="framed">
    <div data-role="totalEffect">
        <?php 
            // Prepare the cost template
            $costTitle = Stephino_Rpg_Config_Units::KEY == $entityConfig->keyCollection() 
                ? esc_html__('Recruitment cost', 'stephino-rpg') 
                : esc_html__('Construction cost', 'stephino-rpg');
            $costDiscount = Stephino_Rpg_Renderer_Ajax_Action::getDiscount($entityConfig);
            $costTimeContraction = Stephino_Rpg_Renderer_Ajax_Action::getTimeContraction($entityConfig);
            require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_COSTS
            );
            
            // Get the entity production data
            $productionData = Stephino_Rpg_Renderer_Ajax_Action::getProductionData(
                $entityConfig,
                $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL],
                $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID],
                $entityCount
            );
            if (count($productionData)) {
                $productionTitle = __('Garrison effect', 'stephino-rpg');
                require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                    Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_PRODUCTION
                );
            }
            
            // Show the military points
            require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_ENTITY_MILITARY
            );
        ?>
    </div>
    <?php 
        require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
            Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_REQUIREMENTS
        );
    ?>
    <?php if ($requirementsMet):?>
        <?php if (min($affordList) > 0):?>
            <div class="row no-gutters">
                <input 
                    type="range"
                    min="1"
                    max="<?php echo min($affordList);?>"
                    value="1"
                    data-change="entityQueuePreview"
                    data-change-args="<?php echo $entityConfig->keyCollection();?>,<?php echo $entityConfig->getId();?>"
                    data-preview="true"
                    data-preview-label="/ <?php echo Stephino_Rpg_Utils_Lingo::isuFormat(min($affordList));?>"
                    data-preview-label-title="<b><?php echo number_format(min($affordList)) . '</b>';?>" />
            </div>
            <button 
                class="btn btn-warning w-100"
                data-click="entityQueue"
                data-click-args="<?php echo $entityConfig->keyCollection();?>,<?php echo $entityConfig->getId();?>,1">
                <span><?php 
                    echo (Stephino_Rpg_Config_Units::KEY == $entityConfig->keyCollection() 
                        ? esc_html__('Recruit', 'stephino-rpg') 
                        : esc_html__('Build', 'stephino-rpg'));
                ?></span>
            </button>
        <?php else:?>
            <div class="d-flex justify-content-center">
                <span class="badge badge-warning"><?php echo esc_html__('Not enough resources', 'stephino-rpg');?></span>
            </div>
        <?php endif; ?>
    <?php endif;?>
</div>