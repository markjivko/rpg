<?php
/**
 * Template:Dialog:Help Content
 * 
 * @title      Help Content - Cities
 * @desc       Template for the help content - loaded both directly and with AJAX inside [data-role="content"]
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $configObject Stephino_Rpg_Config_City */
?>
<div class="row p-2 text-center justify-content-center">
    <div class="col-12">
        <h5>
            <?php echo Stephino_Rpg_Config::get()->core()->getConfigCitiesName(true);?> &#8250; <b><?php echo $configObject->getName(true);?></b>
        </h5>
    </div>
    <div class="col-9">
        <div class="label government-level-number">
            <span>
                <?php echo esc_html__('Level', 'stephino-rpg');?>
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
</div>
<?php 
    require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
        Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_DESCRIPTION
    );
?>
<?php 
    $costTitle = esc_html__('Metropolis change costs', 'stephino-rpg');
    $costData = Stephino_Rpg_Renderer_Ajax_Action::getCostData($configObject);
    require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
        Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_COSTS
    );
?>
<?php if (!count($costData)):?>
    <div class="col-12">
        <h6 class="heading"><span><?php echo esc_html__('Metropolis embargo', 'stephino-rpg');?></span></h6>
        <div class="row no-gutters w-100 mb-4">
            <p>
                <?php 
                    echo sprintf(
                        esc_html__('You may not choose this type of %s as your empire\'s metropolis', 'stephino-rpg'),
                        Stephino_Rpg_Config::get()->core()->getConfigCityName(true)
                    );
                ?><br/>
                <?php echo esc_html__('It may only be randomly delegated as your metropolis at the start of the game', 'stephino-rpg');?>
            </p>
        </div>
    </div>
<?php endif;?>
<div class="col-12">
    <h6 class="heading">
        <span><?php 
            echo sprintf(
                esc_html__('Metrics: %s', 'stephino-rpg'),
                Stephino_Rpg_Config::get()->core()->getConfigCityName(true)
            );
        ?></span>
    </h6>
    <div class="row no-gutters w-100 mb-4">
        <div class="col-12 res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_MTR_POPULATION;?>">
            <div class="icon"></div>
            <span
                data-html="true" 
                data-placement="bottom">
                <?php echo esc_html__('Maximum', 'stephino-rpg');?> <b><?php echo Stephino_Rpg_Config::get()->core()->getMetricPopulationName(true);?></b>: 
                <?php
                    // Prepare the arguments
                    $polynomialBase = $configObject->getMaxPopulation();
                    $polynomialDefinition = $configObject->getMaxPopulationPolynomial();

                    // Load the fragment
                    require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
                        Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_POLY
                    );
                ?>
            </span>
        </div>
        <div class="col-12 res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_MTR_STORAGE;?>">
            <div class="icon"></div>
            <span
                data-html="true" 
                data-placement="bottom">
                <?php echo esc_html__('Maximum', 'stephino-rpg');?> <b><?php echo Stephino_Rpg_Config::get()->core()->getMetricStorageName(true);?></b>:
                <?php
                    // Prepare the arguments
                    $polynomialBase = $configObject->getMaxStorage();
                    $polynomialDefinition = $configObject->getMaxStoragePolynomial();

                    // Load the fragment
                    require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
                        Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_POLY
                    );
                ?>
            </span>
        </div>
        <div class="col-12 res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_MTR_SATISFACTION;?>">
            <div class="icon"></div>
            <span
                data-html="true" 
                data-placement="bottom">
                <?php echo esc_html__('Baseline', 'stephino-rpg');?> <b><?php echo Stephino_Rpg_Config::get()->core()->getMetricSatisfactionName(true);?></b>:
                <?php
                    // Prepare the arguments
                    $polynomialBase = $configObject->getSatisfaction();
                    $polynomialDefinition = $configObject->getSatisfactionPolynomial();

                    // Load the fragment
                    require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
                        Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_POLY
                    );
                ?>
            </span>
        </div>
    </div>
</div>