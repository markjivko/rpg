<?php
/**
 * Template:Dialog:Help Content
 * 
 * @title      Help Content - Government
 * @desc       Template for the help content - loaded both directly and with AJAX inside [data-role="content"]
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $configObject Stephino_Rpg_Config_Government */
$costData = Stephino_Rpg_Renderer_Ajax_Action::getCostData($configObject);
$productionData = Stephino_Rpg_Renderer_Ajax_Action::getProductionData($configObject);
?>
<div class="row p-2 text-center justify-content-center">
    <div class="col-12">
        <h5>
            <?php echo Stephino_Rpg_Config::get()->core()->getConfigGovernmentsName(true);?> &#8250; <b><?php echo $configObject->getName(true);?></b>
        </h5>
    </div>
    <?php if (count($costData) || count($productionData)):?>
        <div class="col-9">
            <div class="label government-level-number">
                <span>
                    <?php 
                        echo sprintf(
                            esc_html__('%s: level', 'stephino-rpg'),
                            Stephino_Rpg_Config::get()->core()->getConfigCityName(true)
                        );
                    ?>
                    <input 
                        type="number" 
                        autocomplete="off"
                        min="1" 
                        value="1" 
                        data-role="poly-level" 
                        title="<?php echo esc_attr__('Use this to preview costs, production etc.', 'stephino-rpg');?>" 
                        class="form-control" />
                </span>
            </div>
        </div>
    <?php endif;?>
</div>
<?php 
    require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
        Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_DESCRIPTION
    );
?>
<div class="col-12 p-2">
    <h6 class="heading"><span><?php echo esc_html__('Game rules', 'stephino-rpg');?></span></h6>
    <ul>
        <li><?php 
            echo sprintf(
                esc_html__('%s: changes are instantaneous', 'stephino-rpg'),
                Stephino_Rpg_Config::get()->core()->getConfigGovernmentName(true)
            );
        ?></li>
        <?php if (count($costData) || count($productionData)):?>
            <li><?php 
                echo sprintf(
                    esc_html__('%s: changes are more expensive as the base advances', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigGovernmentName(true)
                );
            ?></li>
        <?php endif;?>
    </ul>
</div>
<?php 
    require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
        Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_REQUIREMENTS
    );
?>
<?php 
    require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
        Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_PRODUCTION
    );
?>
<?php 
    require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
        Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_COSTS
    );
?>