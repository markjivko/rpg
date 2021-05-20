<?php
/**
 * Template:Dialog:Building Upgrade Cancel
 * 
 * @title      Building Upgrade Cancel dialog
 * @desc       Confirm building upgrade cancellation
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $buildingConfig Stephino_Rpg_Config_Building */
?>
<div class="framed">
    <div class="row">
        <div class="col-12">
            <h5><span><?php echo esc_html__('Refund Policy', 'stephino-rpg');?></span></h5>
            <?php if ($buildingConfig->getRefundPercent() == 0):?>
                <div class="row no-gutters w-100">
                    <div class="col-12 text-center p-2">
                        <?php echo esc_html__('You will not be reimbursed for canceling this upgrade', 'stephino-rpg');?>
                    </div>
                </div>
            <?php else:?>
                <div class="row no-gutters w-100">
                    <div class="col-12 text-center p-2">
                        <?php 
                            echo sprintf(
                                esc_html__('Cancel the upgrade and receive a %s refund', 'stephino-rpg'),
                                '<b>' . $buildingConfig->getRefundPercent() . '</b>%'
                            );
                        ?>
                    </div>
                </div>
                <?php 
                    // Refund mode
                    $costRefundMode = true;
                    $costRefundPercent = $buildingConfig->getRefundPercent();

                    // Show the table
                    require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                        Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_COSTS
                    );
                ?>
            <?php endif;?>
        </div>
        <div class="col-6">
            <button class="btn w-100" data-click="goBack">
                <span>
                    <b><?php echo esc_html__('Go Back', 'stephino-rpg');?></b>
                </span>
            </button>
        </div>
        <div class="col-6">
            <button class="btn btn-warning w-100" data-click="buildingUpgradeCancel">
                <span>
                    <b><?php echo esc_html__('Stop Upgrade', 'stephino-rpg');?></b>
                </span>
            </button>
        </div>
    </div>
</div>