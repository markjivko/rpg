<?php
/**
 * Template:Dialog:Help
 * 
 * @title      Help Fragment - Costs
 * @desc       Costs
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $configObject Stephino_Rpg_Config_Trait_Cost */

// Initialize the cost data
if (!isset($costData)) {
    $costData = Stephino_Rpg_Renderer_Ajax_Action::getCostData($configObject);
}
if (!isset($costTitle)) {
    $costTitle = esc_html__('Costs', 'stephino-rpg');
}
?>
<?php if (is_array($costData) && count($costData) || (is_callable(array($configObject, 'getCostTime')) && $configObject->getCostTime() > 0)):?>
    <div class="col-12">
        <h6 class="heading"><span><?php echo $costTitle; ?></span></h6>
        <div class="row no-gutters w-100 mb-4">
            <?php 
                foreach ($costData as $costInfo):
                    list($costInfoName, $polynomialBase, $costInfoKey) = $costInfo;
            ?>
                <div class="col-12 col-sm-6 res res-<?php echo $costInfoKey;?>">
                    <div class="icon" data-html="true" title="<?php echo $costInfoName;?>"></div>
                    <span
                        data-html="true" 
                        data-placement="bottom">
                        <?php
                            // Prepare the arguments
                            $polynomialDefinition = null;
                            if (is_callable(array($configObject, 'getCostPolynomial'))) {
                                $polynomialDefinition = $configObject->getCostPolynomial();
                            }
                            
                            // Load the fragment
                            require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
                                Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_POLY
                            );
                        ?>
                        <span class="d-lg-none"><?php echo $costInfoName;?></span>
                    </span>
                </div>
            <?php endforeach;?>
            <?php 
                if ($configObject instanceof Stephino_Rpg_Config_Item_Single
                    && in_array(Stephino_Rpg_Config_Trait_Cost::class, class_uses($configObject))
                    && is_callable(array($configObject, 'getCostTime'))): 
                    // Get the base
                    $polynomialBase = $configObject->getCostTime();
                
                    // Valid value provided
                    if ($polynomialBase > 0):
                        // Get the definition
                        $polynomialDefinition = Stephino_Rpg_Config::get()->core()->getSandbox() 
                            ? null
                            : $configObject->getCostTimePolynomial();
            ?>
                <div class="col-12 col-sm-6 col-lg res res-time">
                    <div class="icon"></div>
                    <span
                        data-html="true" 
                        data-placement="bottom">
                        <?php
                            // Format the output as HHH:MM:SS
                            $polyTime = true;
                            require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
                                Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_POLY
                            );
                        ?>
                    </span>
                </div>
            <?php endif; endif;?>
        </div>
    </div>
<?php endif;?>