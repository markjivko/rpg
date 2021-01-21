<?php
/**
 * Template:Dialog:Help
 * 
 * @title      Help Fragment - Production
 * @desc       Production
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $configObject Stephino_Rpg_Config_Trait_Modifier */

// Initialize the production data
if (!isset($productionData)) {
    $productionData = Stephino_Rpg_Renderer_Ajax_Action::getProductionData($configObject);
}
?>
<?php if (is_array($productionData) && count($productionData)):?>
    <div class="col-12">
        <h6 class="heading"><span><?php echo esc_html__('Production', 'stephino-rpg');?></span></h6>
        <div class="row no-gutters w-100 mb-4">
            <?php 
                foreach ($productionData as $prodKey => $prodInfo):
                    list($prodInfoName, $polynomialBase, $prodInfoKey) = $prodInfo;
            ?>
                <div class="col-12 col-sm-6 res res-<?php echo $prodInfoKey;?>">
                    <div class="icon" data-html="true" title="<?php echo $prodInfoName;?>"></div>
                    <span
                        data-html="true" 
                        data-placement="bottom">
                        <?php
                            // Prepare the arguments
                            $polynomialDefinition = $configObject->getModifierPolynomial();
                            
                            // Load the fragment
                            require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
                                Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_POLY
                            );
                        ?><?php if (Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_SATISFACTION != $prodKey):?>/h<?php endif;?>
                        <span class="d-lg-none"><?php echo $prodInfoName;?></span>
                    </span>
                </div>
            <?php endforeach;?>
        </div>
    </div>
<?php endif;?>