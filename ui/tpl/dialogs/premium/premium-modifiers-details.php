<?php
/**
 * Template:Dialog:Premium
 * 
 * @title      Premium modifiers dialog
 * @desc       Template for the premium modifiers
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

// Default premium modifier level
if (!isset($premiumModifierCount)) {
    $premiumModifierCount = 1;
}

/* @var $premiumModifierConfig Stephino_Rpg_Config_PremiumModifier */
if (isset($premiumModifierConfig) && $premiumModifierConfig instanceof Stephino_Rpg_Config_PremiumModifier):
?>
    <?php 
        if (null !== $premiumModifierConfig->getModifier()) {
            $productionData = Stephino_Rpg_Renderer_Ajax_Action::getProductionData($premiumModifierConfig, $premiumModifierCount);

            if (count($productionData)) {
                $productionTitle = esc_html__('Production in all bases', 'stephino-rpg');
                require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                    Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_PRODUCTION
                );
            };
        }
        
        // Prepare the cost layout
        if (!isset($premiumModifierDuration)) {
            $costTitle = esc_html__('Cost', 'stephino-rpg');
            $costData = Stephino_Rpg_Renderer_Ajax_Action::getCostData(
                $premiumModifierConfig,
                $premiumModifierCount - 1
            );
            require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_COSTS
            );
        }
    ?>
    <div class="col-12 justify-content-center text-center mb-4">
        <div class="res res-time">
            <div class="icon"></div>
            <span>
                <?php if (isset($premiuModifierRemaining) && isset($premiumModifierDuration)):?>
                    <?php echo esc_html__('Remaining', 'stephino-rpg');?>:
                    <span 
                        class="font-weight-bold"
                        data-effect="countdownTime" 
                        data-effect-args="<?php echo ($premiuModifierRemaining . ',' . $premiumModifierDuration);?>">
                    </span>
                <?php else:?>
                    <?php echo esc_html__('Duration', 'stephino-rpg');?>: <b><?php echo $premiumModifierCount * $premiumModifierConfig->getDuration();?></b>
                    <?php echo esc_html(_n('hour', 'hours', $premiumModifierCount * $premiumModifierConfig->getDuration(), 'stephino-rpg'));?>
                <?php endif;?>
            </span>
        </div>
    </div>
    <?php if (isset($premiuModifierRemaining) && isset($premiumModifierDuration)):?>
        <div class="col-12 justify-content-center text-center mb-4">
            <div 
                class="item-level-uc-bar" 
                title="<?php echo esc_attr__('Premium modifier expiration time', 'stephino-rpg');?>" 
                data-effect="countdownBar" 
                data-effect-args="<?php echo ($premiuModifierRemaining . ',' . $premiumModifierDuration);?>">
            </div>
        </div>
    <?php endif;?>
<?php endif;
    // Reset the level for next reuse
    $premiumModifierCount = 1;
?>