<?php
/**
 * Template:Dialog:Entity
 * 
 * @title      Entity recruitment dialog
 * @desc       Template for recruiting entities
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

$entityKey = $entityConfig instanceof Stephino_Rpg_Config_Unit
    ? Stephino_Rpg_Config_Units::KEY
    : Stephino_Rpg_Config_Ships::KEY;
?>
<div class="row mt-0 framed p-0">
    <div data-effect="parallax" data-effect-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>">
    </div>
    <div class="page-help">
        <span 
            data-effect="help"
            data-effect-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>">
            <?php echo $entityConfig->getName(true);?>
        </span>
    </div>
</div>
<div class="framed">
    <div data-role="totalCost">
        <?php 
            $costTitle = Stephino_Rpg_Config_Units::KEY == $entityKey 
                ? esc_html__('Recruitment cost', 'stephino-rpg') 
                : esc_html__('Construction cost', 'stephino-rpg');
            $costDiscount = Stephino_Rpg_Renderer_Ajax_Action::getDiscount($entityConfig);
            $costTimeContraction = Stephino_Rpg_Renderer_Ajax_Action::getTimeContraction($entityConfig);
            require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_COSTS
            );
        ?>
    </div>
</div>
<?php 
    require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
        Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_REQUIREMENTS
    );
?>
<?php if ($requirementsMet):?>
    <div class="framed">
        <?php if (min($affordList) > 0):?>
            <div class="row no-gutters">
                <input 
                    type="range"
                    min="1"
                    max="<?php echo min($affordList);?>"
                    value="1"
                    data-change="entityCostPreview"
                    data-change-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>"
                    data-preview="true"
                    data-preview-label="/ <?php echo Stephino_Rpg_Utils_Lingo::isuFormat(min($affordList));?>"
                    data-preview-label-title="<b><?php echo number_format(min($affordList)) . '</b>';?>" />
            </div>
            <button 
                class="btn btn-warning w-100"
                data-click="entityQueue"
                data-click-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>,1">
                <span><?php 
                    echo (Stephino_Rpg_Config_Units::KEY == $entityKey 
                        ? esc_html__('Recruit', 'stephino-rpg') 
                        : esc_html__('Build', 'stephino-rpg'));
                ?></span>
            </button>
        <?php else:?>
            <div class="d-flex justify-content-center">
                <span class="badge badge-warning"><?php echo esc_html__('Not enough resources', 'stephino-rpg');?></span>
            </div>
        <?php endif; ?>
    </div>
<?php endif;?>