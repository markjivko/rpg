<?php
/**
 * Template:Dialog:Help Content
 * 
 * @title      Help Content - Units
 * @desc       Template for the help content - loaded both directly and with AJAX inside [data-role="content"]
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $configObject Stephino_Rpg_Config_Unit */
$costData = Stephino_Rpg_Renderer_Ajax_Action::getCostData($configObject);
$productionData = Stephino_Rpg_Renderer_Ajax_Action::getProductionData($configObject);

// Prepare the max values
$maxArmour = 0;
$maxAgility = 0;
$maxDamage = 0;
$maxAmmo = 0;
$maxLootBox = 0;
foreach (Stephino_Rpg_Config::get()->units()->getAll() as $unitConfig) {
    $unitConfig->getArmour() > $maxArmour && $maxArmour = $unitConfig->getArmour();
    $unitConfig->getAgility() > $maxAgility && $maxAgility = $unitConfig->getAgility();
    $unitConfig->getDamage() > $maxDamage && $maxDamage = $unitConfig->getDamage();
    $unitConfig->getAmmo() > $maxAmmo && $maxAmmo = $unitConfig->getAmmo();
    $unitConfig->getLootBox() > $maxLootBox && $maxLootBox = $unitConfig->getLootBox();
}
?>
<div class="row p-2 text-center justify-content-center no-gutters">
    <div class="col-12">
        <h5>
            <?php echo Stephino_Rpg_Config::get()->core()->getConfigUnitsName(true);?> &#8250; <b><?php echo $configObject->getName(true);?></b>
        </h5>
    </div>
    <?php if (null !== $configObject->getBuilding() && (count($costData) || count($productionData))):?>
        <div class="col-10">
            <div class="label unit-level-number">
                <span>
                    <span
                        data-effect="helpMenuItem"
                        data-effect-args="<?php echo Stephino_Rpg_Config_Buildings::KEY;?>,<?php echo $configObject->getBuilding()->getId();?>">
                        <?php echo $configObject->getBuilding()->getName(true);?>
                    </span>
                    <?php echo esc_html__('level', 'stephino-rpg');?>
                    <input 
                        type="number" 
                        autocomplete="off"
                        min="1" 
                        value="1" 
                        data-role="poly-level" 
                        title="<?php echo esc_attr__('Use this to preview individual costs, abilities etc.', 'stephino-rpg');?>" 
                        class="form-control" />
                </span>
            </div>
        </div>
        <div class="col-10">
            <div class="label unit-count-number">
                <span>
                    <?php echo esc_html__('Count', 'stephino-rpg');?>
                    <input 
                        type="number" 
                        autocomplete="off"
                        min="1" 
                        value="1" 
                        data-role="poly-count" 
                        title="<?php echo esc_attr__('Use this to preview group costs, abilities etc.', 'stephino-rpg');?>" 
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
<?php if ($configObject->getCivilian() && ($configObject->getAbilityColonize() || $configObject->getAbilitySpy())):?>
    <div class="col-12 p-2">
        <h6 class="heading"><span><?php echo esc_html__('Special abilities', 'stephino-rpg');?></span></h6>
        <ul>
            <?php if ($configObject->getAbilityColonize()):?>
                <li>
                    <b><?php echo esc_html__('Colonizer', 'stephino-rpg');?>:</b> <?php echo esc_html__('claim empty lots, expanding your empire', 'stephino-rpg');?>
                </li>
            <?php endif;?>
            <?php if ($configObject->getAbilitySpy()):?>
                <li>
                    <b><?php echo esc_html__('Spy', 'stephino-rpg');?>:</b> <?php echo esc_html__('gather information on rivals', 'stephino-rpg');?>
                </li>
                <li>
                    <?php 
                        // Prepare the arguments
                        $polynomialBase = $configObject->getSpySuccessRate();
                        $polynomialDefinition = $configObject->getSpySuccessRatePolynomial();
                        $polyMax = 100;
                        $polyIgnoreCount = true;
                        
                        // Load the fragment
                        ob_start();
                        require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
                            Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_POLY
                        );
                        $spySuccessPoly = ob_get_clean();
                        
                        echo sprintf(
                            esc_html__('The spy has a success probability of %s', 'stephino-rpg'),
                            '<b>' . $spySuccessPoly . '</b>%'
                        );
                    ?>
                </li>
            <?php endif;?>
        </ul>
    </div>
<?php endif;?>
<?php if (!$configObject->getCivilian()):?>
    <div class="col-12 p-2">
        <h6 class="heading"><span><?php echo esc_html__('Military capabilities', 'stephino-rpg');?></span></h6>
        <ul>
            <li>
                <b><?php echo esc_html__('Damage', 'stephino-rpg');?></b>: <b><?php echo $configObject->getDamage();?></b> / <?php echo $maxDamage;?>
                <div 
                    data-effect="staticBar" 
                    data-effect-args="<?php echo $configObject->getDamage();?>,<?php echo $maxDamage;?>">
                </div>
            </li>
            <li>
                <b><?php echo esc_html__('Ammo', 'stephino-rpg');?></b>: <b><?php echo $configObject->getAmmo();?></b> / <?php echo $maxAmmo;?>
                <div 
                    data-effect="staticBar" 
                    data-effect-args="<?php echo $configObject->getAmmo();?>,<?php echo $maxAmmo;?>">
                </div>
            </li>
            <li>
                <b><?php echo esc_html__('Armour', 'stephino-rpg');?></b>: <b><?php echo $configObject->getArmour();?></b> / <?php echo $maxArmour;?>
                <div 
                    data-effect="staticBar" 
                    data-effect-args="<?php echo $configObject->getArmour();?>,<?php echo $maxArmour;?>">
                </div>
            </li>
            <li>
                <b><?php echo esc_html__('Agility', 'stephino-rpg');?></b>: <b><?php echo $configObject->getAgility();?></b> / <?php echo $maxAgility;?>
                <div 
                    data-effect="staticBar" 
                    data-effect-args="<?php echo $configObject->getAgility();?>,<?php echo $maxAgility;?>">
                </div>
            </li>
            <li>
                <b><?php echo esc_html__('Loot Box', 'stephino-rpg');?></b>: 
                <?php if ($configObject->getLootBox()):?>
                    <b><?php echo number_format($configObject->getLootBox());?></b> / <?php echo number_format($maxLootBox);?>
                    <div 
                        data-effect="staticBar" 
                        data-effect-args="<?php echo $configObject->getLootBox();?>,<?php echo $maxLootBox;?>">
                    </div>
                <?php else:?>
                    <i><?php echo esc_html__('none', 'stephino-rpg');?></i>
                <?php endif;?>
            </li>
        </ul>
        <div class="row">
            <?php
                $polynomialDefinition = null;
                $polyIgnoreCount = false;
                $polynomialBase = $configObject->getDamage() * $configObject->getAmmo();
            ?>
            <div class="col-12 col-md-6">
                <div class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_MIL_ATTACK;?>">
                    <div class="icon"></div>
                    <span title="<?php echo esc_attr__('Damage', 'stephino-rpg') . ' &times; ' . esc_attr__('Ammo', 'stephino-rpg');?>">
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
            <?php 
                $polynomialBase = $configObject->getArmour() * $configObject->getAgility();
                $polynomialDefinition = null;
                $polyIgnoreCount = false;
            ?>
            <div class="col-12 col-md-6">
                <div class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_MIL_DEFENSE;?>">
                    <div class="icon"></div>
                    <span title="<?php echo esc_attr__('Armour', 'stephino-rpg') . ' &times; ' . esc_attr__('Agility', 'stephino-rpg');?>">
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
        </div>
    </div>
<?php endif;?>
<div class="col-12 p-2">
    <h6 class="heading"><span><?php echo esc_html__('Properties', 'stephino-rpg');?></span></h6>
    <ul>
        <?php if (null !== $configObject->getBuilding()):?>
            <li>
                <?php echo esc_html__('Recruited in', 'stephino-rpg');?>:
                <span
                    data-effect="helpMenuItem"
                    data-effect-args="<?php echo Stephino_Rpg_Config_Buildings::KEY;?>,<?php echo $configObject->getBuilding()->getId();?>">
                    <?php echo $configObject->getBuilding()->getName(true);?>
                </span>
            </li>
            <li>
                <b><?php echo esc_html__('Mass', 'stephino-rpg');?></b>: 
                <?php if (!$configObject->getCivilian()):?>
                    <?php if (Stephino_Rpg_Config::get()->core()->getTravelTime() > 0 && $configObject->getTroopMass()): ?>
                        <b><?php echo $configObject->getTroopMass();?> </b>
                    <?php else:?>
                        <b><?php echo esc_html__('ignored', 'stephino-rpg');?></b>
                    <?php endif;?>
                    <?php echo esc_html__('when part of a military convoy', 'stephino-rpg');?>,
                <?php endif;?>
                <b><?php echo $configObject->getTransportMass();?> </b>
                <?php 
                    echo sprintf(
                        esc_html__('when carried with a transporter (%s) between %s', 'stephino-rpg'),
                        Stephino_Rpg_Config::get()->core()->getConfigShipName(true),
                        Stephino_Rpg_Config::get()->core()->getConfigCitiesName(true)
                    );
                ?>
            </li>
            <li>
                <?php if ($configObject->getDisbandable()):?>
                    <b><?php echo esc_html__('Disbandable', 'stephino-rpg');?></b>: 
                    <?php 
                        echo sprintf(
                            esc_html__('generates %s when disbanded', 'stephino-rpg'),
                            '+<b>' . $configObject->getDisbandablePopulation() . '</b> ' . Stephino_Rpg_Config::get()->core()->getMetricPopulationName(true)
                        );
                    ?>
                <?php else:?>
                    <?php echo esc_html__('Not disbandable', 'stephino-rpg');?>
                <?php endif;?>
            </li>
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