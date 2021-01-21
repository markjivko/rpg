<?php
/**
 * Template:Dialog:Help Content
 * 
 * @title      Help Content - Buildings
 * @desc       Template for the help content - loaded both directly and with AJAX inside [data-role="content"]
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $configObject Stephino_Rpg_Config_Building */
$costData = Stephino_Rpg_Renderer_Ajax_Action::getCostData($configObject);
$productionData = Stephino_Rpg_Renderer_Ajax_Action::getProductionData($configObject);
$marketplaceData = Stephino_Rpg_Renderer_Ajax_Action::getMarketplaceData($configObject);

// Get the dependencies
$depUnits = array_filter(
    Stephino_Rpg_Config::get()->units()->getAll(), 
    function ($item) use($configObject) {
        return null !== $item->getBuilding() && $configObject->getId() == $item->getBuilding()->getId();
    }
);
$depShips = array_filter(
    Stephino_Rpg_Config::get()->ships()->getAll(), 
    function ($item) use($configObject) {
        return null !== $item->getBuilding() && $configObject->getId() == $item->getBuilding()->getId();
    }
);
$depResearchAreas = array_filter(
    Stephino_Rpg_Config::get()->researchAreas()->getAll(), 
    function ($item) use($configObject) {
        return null !== $item->getBuilding() && $configObject->getId() == $item->getBuilding()->getId();
    }
);
?>
<div class="row p-2 text-center justify-content-center">
    <div class="col-12">
        <h5>
            <?php echo Stephino_Rpg_Config::get()->core()->getConfigBuildingsName(true);?> &#8250; <b><?php echo $configObject->getName(true);?></b>
            <?php if ($configObject->isMainBuilding()):?>
            (<?php echo esc_html__('main', 'stephino-rpg');?>)
            <?php endif;?>
        </h5>
    </div>
    <?php 
        if (
            count($costData) || count($productionData)
            || $configObject->getUseWorkers()
            || null !== $configObject->getAttackPoints() 
            || null !== $configObject->getDefensePoints()
        ):
    ?>
        <div class="col-9">
            <div class="label item-level-number">
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
    <?php endif;?>
</div>
<?php 
    require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
        Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_DESCRIPTION
    );
?>
<?php if (is_array($marketplaceData) && count($marketplaceData)):?>
    <div class="col-12">
        <h6 class="heading"><span><?php echo esc_html__('Market sell rates', 'stephino-rpg');?></span></h6>
        <div class="col-12 p-2">
            <p>
                <?php echo esc_html__('You can sell resources at the exchange rates below', 'stephino-rpg');?>
                <?php if (Stephino_Rpg_Config::get()->core()->getMarketGain() > 0):?>
                    <br/>
                    <?php
                        echo sprintf(
                            esc_html__('Buying resources costs %s more', 'stephino-rpg'),
                            '<b>' . Stephino_Rpg_Config::get()->core()->getMarketGain() . '</b>%'
                        );
                    ?>
                <?php endif;?>
            </p>
        </div>
        <div class="row no-gutters w-100 mb-4">
            <?php 
                foreach ($marketplaceData as $marketKey => $marketInfo):
                    list($marketInfoName, $polynomialBase, $marketInfoKey) = $marketInfo;
            ?>
                <div class="col-12 col-sm-6 res res-<?php echo $marketInfoKey;?>">
                    <div class="icon" data-html="true" title="<b>1</b> <?php echo Stephino_Rpg_Utils_Lingo::escape($marketInfoName);?>"></div>
                    <span
                        data-html="true" 
                        data-placement="bottom">
                        <?php
                            // Prepare the arguments
                            $polynomialDefinition = Stephino_Rpg_Config::get()->core()->getMarketPolynomial();
                            
                            // Load the fragment
                            require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
                                Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_POLY
                            );
                        ?>
                        <?php echo Stephino_Rpg_Config::get()->core()->getResourceGoldName(true);?>
                    </span>
                </div>
            <?php endforeach;?>
        </div>
    </div>
<?php endif;?>
<?php if (count($depUnits) || count($depShips) || count($depResearchAreas)):?>
    <div class="col-12">
        <h6 class="heading"><span><?php echo esc_html__('Designation', 'stephino-rpg');?></span></h6>
        <div class="col-12">
            <ul>
                <?php foreach ($depUnits as $depUnit):?>
                    <li>
                        <?php echo Stephino_Rpg_Config::get()->core()->getConfigUnitName();?>: <span
                            data-effect="helpMenuItem"
                            data-effect-args="<?php echo Stephino_Rpg_Config_Units::KEY;?>,<?php echo $depUnit->getId();?>">
                            <?php echo $depUnit->getName(true);?>
                        </span>
                    </li>
                <?php endforeach;?>
                <?php foreach ($depShips as $depShip):?>
                    <li>
                        <?php echo Stephino_Rpg_Config::get()->core()->getConfigShipName();?>: <span
                            data-effect="helpMenuItem"
                            data-effect-args="<?php echo Stephino_Rpg_Config_Ships::KEY;?>,<?php echo $depShip->getId();?>">
                            <?php echo $depShip->getName(true);?>
                        </span>
                    </li>
                <?php endforeach;?>
                <?php foreach ($depResearchAreas as $depResearchArea):?>
                    <li>
                        <?php echo Stephino_Rpg_Config::get()->core()->getConfigResearchAreaName();?>: <span
                            data-effect="helpMenuItem"
                            data-effect-args="<?php echo Stephino_Rpg_Config_ResearchAreas::KEY;?>,<?php echo $depResearchArea->getId();?>">
                            <?php echo $depResearchArea->getName(true);?>
                        </span>
                    </li>
                <?php endforeach;?>
            </ul>
        </div>
    </div>
<?php endif;?>
<?php if ($configObject->isMainBuilding() || $configObject->getUseWorkers()):?>
    <div class="col-12">
        <h6 class="heading"><span><?php echo esc_html__('Workers', 'stephino-rpg');?></span></h6>
        <div class="col-12 p-2">
            <?php 
                if ($configObject->getUseWorkers()) {
                    // Prepare the arguments
                    $polynomialDefinition = $configObject->getWorkersCapacityPolynomial();
                    $polynomialBase = $configObject->getWorkersCapacity();

                    // Load the fragment
                    ob_start();
                    require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
                        Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_POLY
                    );
                    $buildingWorkersPoly = ob_get_clean();
                    
                    // Display the result
                    echo sprintf(
                        esc_html__('Requires %s workers to function at full capacity', 'stephino-rpg'),
                        '<b>' . $buildingWorkersPoly . '</b>'
                    );
                } else {
                    echo sprintf(
                        esc_html__('Requires maximum %s to function at full capacity', 'stephino-rpg'),
                        '<b>' . Stephino_Rpg_Config::get()->core()->getMetricPopulationName(true) . '</b>'
                    );
                }
            ?>
        </div>
    </div>
<?php endif; ?>
<?php 
    require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
        Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_UNLOCKS
    );
?>
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
<?php if (count($costData)):?>
    <div class="col-12">
        <h6 class="heading"><span><?php echo esc_html__('Cancellation policy', 'stephino-rpg');?></span></h6>
        <div class="col-12 p-2">
            <?php 
                if ($configObject->getRefundPercent() == 0) {
                    echo esc_html__('You will not be reimbursed when canceling an upgrade', 'stephino-rpg');
                } else {
                    echo sprintf(
                        esc_html__('You will receive a %s refund when canceling an upgrade', 'stephino-rpg'),
                        '<b>' . $configObject->getRefundPercent() . '</b>%'
                    );
                }
            ?>
        </div>
    </div>
<?php endif;?>
<?php if (null !== $configObject->getAttackPoints() || null !== $configObject->getDefensePoints()):?>
    <div class="col-12">
        <h6 class="heading"><span><?php echo esc_html__('Military capabilities', 'stephino-rpg');?></span></h6>
        <div class="row">
            <?php
                // Prepare the arguments
                $polynomialDefinition = $configObject->getAttackPointsPolynomial();
                if (null !== $polynomialBase = $configObject->getAttackPoints()):
            ?>
                <div class="col-12 col-md-6">
                    <div class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_MIL_ATTACK;?>">
                        <div class="icon"></div>
                        <span>
                            <?php 
                                echo sprintf(
                                    esc_html__('%s points', 'stephino-rpg'),
                                    Stephino_Rpg_Config::get()->core()->getMilitaryAttackName(true)
                                );
                            ?>: <?php
                                // Load the fragment
                                require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
                                    Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_POLY
                                );
                            ?>
                        </span>
                    </div>
                </div>
            <?php endif;?>
            <?php
                // Prepare the arguments
                $polynomialDefinition = $configObject->getDefensePointsPolynomial();
                if (null !== $polynomialBase = $configObject->getDefensePoints()):
            ?>
                <div class="col-12 col-md-6">
                    <div class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_MIL_DEFENSE;?>">
                        <div class="icon"></div>
                        <span>
                            <?php 
                                echo sprintf(
                                    esc_html__('%s points', 'stephino-rpg'),
                                    Stephino_Rpg_Config::get()->core()->getMilitaryDefenseName(true)
                                );
                            ?>: <?php
                                // Load the fragment
                                require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
                                    Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_POLY
                                );
                            ?>
                        </span>
                    </div>
                </div>
            <?php endif;?>
        </div>
    </div>
<?php endif;?>