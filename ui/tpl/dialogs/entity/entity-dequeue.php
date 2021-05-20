<?php
/**
 * Template:Dialog:Entity
 * 
 * @title      Entity dequeue dialog
 * @desc       Template for removing entities from the queue
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $queueData array */
/* @var $entityConfig Stephino_Rpg_Config_Unit|Stephino_Rpg_Config_Ship */
?>
<div data-role="dequeue">
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
        <div data-role="totalEffect">
            <?php 
                $costTitle = esc_html__('Refund', 'stephino-rpg');
                $costDiscount = Stephino_Rpg_Renderer_Ajax_Action::getDiscount($entityConfig);
                $costRefundMode = true;
                $costRefundPercent = 100;
                require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                    Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_COSTS
                );
            ?>
        </div>
        <?php if ($queueData[Stephino_Rpg_Renderer_Ajax_Action_Entity::DATA_QUEUE_QUANTITY] > 0):?>
            <div class="row no-gutters">
                <input 
                    type="range"
                    min="1"
                    max="<?php echo $queueData[Stephino_Rpg_Renderer_Ajax_Action_Entity::DATA_QUEUE_QUANTITY];?>"
                    value="1"
                    data-change="entityQueuePreview"
                    data-change-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>,0"
                    data-preview="true"
                    data-preview-label="/ <?php echo Stephino_Rpg_Utils_Lingo::isuFormat($queueData[Stephino_Rpg_Renderer_Ajax_Action_Entity::DATA_QUEUE_QUANTITY]);?>"
                    data-preview-label-title="<b><?php echo number_format($queueData[Stephino_Rpg_Renderer_Ajax_Action_Entity::DATA_QUEUE_QUANTITY]) . '</b>';?>" />
            </div>
            <button 
                class="btn btn-warning w-100"
                data-click="entityQueue"
                data-click-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>,0">
                <span><?php echo esc_html__('Remove from queue', 'stephino-rpg');?></span>
            </button>
        <?php else:?>
            <span class="alert alert-warning"><?php echo esc_html__('This entity is not queued', 'stephino-rpg');?></span>
        <?php endif; ?>
    </div>
</div>