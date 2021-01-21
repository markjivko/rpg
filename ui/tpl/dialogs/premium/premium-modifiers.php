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

/* @var $premiumModifierConfig Stephino_Rpg_Config_PremiumModifier */
?>
<div class="row col-12 m-0 justify-content-center">
    <?php if (count(Stephino_Rpg_Config::get()->premiumPackages()->getAll())):?>
        <div class="col-12 framed">
            <button 
                class="btn btn-default w-100" 
                data-click="dialog" 
                data-click-args="dialogPremiumPackageList">
                <span>
                    <?php 
                        echo sprintf(
                            esc_html__('Get %s', 'stephino-rpg'),
                            '<b>' . Stephino_Rpg_Config::get()->core()->getResourceGemName(true) . '</b>'
                        );
                    ?>
                </span>
            </button>
        </div>
    <?php endif;?>
    <?php 
        foreach (Stephino_Rpg_Config::get()->premiumModifiers()->getAll() as $premiumModifierConfig):
            list($premiumModifierCount, $premiumModifierDuration, $premiuModifierRemaining) = array(1, null, null);
            if (isset($premiumEnabled[$premiumModifierConfig->getId()])) {
                list($premiumModifierCount, $premiumModifierDuration, $premiuModifierRemaining) = $premiumEnabled[$premiumModifierConfig->getId()];
            }
    ?>
        <div class="col-12 framed <?php if (isset($premiumModifierDuration)) {echo 'active';} ?>" data-role="premium-modifier">
            <div class="col-12">
                <h4>
                    <span
                        data-effect="help"
                        data-effect-args="<?php echo Stephino_Rpg_Config_PremiumModifiers::KEY;?>,<?php echo $premiumModifierConfig->getId();?>">
                        <?php echo $premiumModifierConfig->getName(true);?>
                    </span>
                    <?php if (isset($premiumModifierDuration)):?>
                        (<?php echo esc_html__('active', 'stephino-rpg');?>)
                    <?php endif;?>
                </h4>
            </div>
            <div class="col-12">
                <div class="card card-body bg-dark mb-4">
                    <?php if (strlen($premiumModifierConfig->getDescription())):?>
                        <?php echo Stephino_Rpg_Utils_Lingo::markdown($premiumModifierConfig->getDescription());?>
                    <?php else:?>
                        <i class="w-100 text-center"><?php echo esc_html__('no information', 'stephino-rpg');?></i>
                    <?php endif;?>
                </div>
            </div>
            <?php if ($premiumModifierConfig->getResourceExtra1Abundant() || $premiumModifierConfig->getResourceExtra2Abundant()):?>
                <div class="col-12 mb-4">
                    <h5><span><?php echo esc_html__('Resource abundance', 'stephino-rpg');?></span></h5>
                    <?php if ($premiumModifierConfig->getResourceExtra1Abundant()):?>
                        <div class="col-12">
                            <span class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_EXTRA_1;?>">
                                <div class="icon"></div>
                                <span>
                                    <?php 
                                        echo sprintf(
                                            esc_html__('%s: abundance of %s for all bases', 'stephino-rpg'),
                                            '<b>' . Stephino_Rpg_Config::get()->core()->getResourceExtra1Name(true). '</b>',
                                            '<b>100</b>%'
                                        );
                                    ?>
                                </span>
                            </span>
                        </div>
                    <?php endif;?>
                    <?php if ($premiumModifierConfig->getResourceExtra2Abundant()):?>
                        <div class="col-12">
                            <span class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_EXTRA_2;?>">
                                <div class="icon"></div>
                                <span>
                                    <?php 
                                        echo sprintf(
                                            esc_html__('%s: abundance of %s for all bases', 'stephino-rpg'),
                                            '<b>' . Stephino_Rpg_Config::get()->core()->getResourceExtra2Name(true). '</b>',
                                            '<b>100</b>%'
                                        );
                                    ?>
                                </span>
                            </span>
                        </div>
                    <?php endif;?>
                </div>
            <?php endif;
                // Prepare the discounts
                $validDiscountsBuildings = $premiumModifierConfig->getEnablesDiscountBuildings() && is_array($premiumModifierConfig->getDiscountBuildings());
                $validDiscountsUnits = $premiumModifierConfig->getEnablesDiscountUnits() && is_array($premiumModifierConfig->getDiscountUnits());
                $validDiscountsShips = $premiumModifierConfig->getEnablesDiscountShips() && is_array($premiumModifierConfig->getDiscountShips());
                if ($validDiscountsBuildings || $validDiscountsUnits || $validDiscountsShips): 
            ?>
                <div class="col-12 mb-4">
                    <h5><span><?php echo esc_html__('Discounts', 'stephino-rpg');?></span></h5>
                    <div class="col-12">
                        <p>
                            <?php echo esc_html__('A discount is applied to the following items:', 'stephino-rpg');?>
                        </p>
                        <ul>
                            <?php if ($validDiscountsBuildings): foreach ($premiumModifierConfig->getDiscountBuildings() as $buildingConfig):?>
                                <li>
                                    <span 
                                        data-effect="help"
                                        data-effect-args="<?php echo Stephino_Rpg_Config_Buildings::KEY;?>,<?php echo $buildingConfig->getId();?>">
                                        <?php echo $buildingConfig->getName(true); ?>
                                    </span>: <b>-<?php echo $premiumModifierConfig->getDiscountBuildingsPercent();?></b>%
                                </li>
                            <?php endforeach; endif;?>
                            <?php if ($validDiscountsUnits): foreach ($premiumModifierConfig->getDiscountUnits() as $unitConfig):?>
                                <li>
                                    <span 
                                        data-effect="help"
                                        data-effect-args="<?php echo Stephino_Rpg_Config_Units::KEY;?>,<?php echo $unitConfig->getId();?>">
                                        <?php echo $unitConfig->getName(true); ?>
                                    </span>: <b>-<?php echo $premiumModifierConfig->getDiscountUnitsPercent();?></b>%
                                </li>
                            <?php endforeach; endif;?>
                            <?php if ($validDiscountsShips): foreach ($premiumModifierConfig->getDiscountShips() as $shipConfig):?>
                                <li>
                                    <span 
                                        data-effect="help"
                                        data-effect-args="<?php echo Stephino_Rpg_Config_Ships::KEY;?>,<?php echo $shipConfig->getId();?>">
                                        <?php echo $shipConfig->getName(true); ?>
                                    </span>: <b>-<?php echo $premiumModifierConfig->getDiscountShipsPercent();?></b>%
                                </li>
                            <?php endforeach; endif;?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (null !== $premiumModifierConfig->getMaxQueueBuildings()
                || null !== $premiumModifierConfig->getMaxQueueEntities()
                || null !== $premiumModifierConfig->getMaxQueueResearchFields()):?>
                <div class="col-12 mb-4">
                    <h5><span><?php echo esc_html__('Maximum queues', 'stephino-rpg');?></span></h5>
                    <div class="col-12">
                        <p>
                            <?php echo esc_html__('Overrides', 'stephino-rpg');?> 
                            <span 
                                data-effect="help"
                                data-effect-args="<?php echo Stephino_Rpg_Config_Core::KEY;?>,<?php echo Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_RULES;?>">
                                <?php echo esc_html__('core settings', 'stephino-rpg');?>
                            </span>
                        </p>
                        <ul>
                            <?php if (null !== $premiumModifierConfig->getMaxQueueBuildings()):?>
                                <li>
                                    <?php 
                                        echo sprintf(
                                            esc_html__('%s - Queues per %s', 'stephino-rpg'),
                                            '<b>' . Stephino_Rpg_Config::get()->core()->getConfigBuildingsName(true) . '</b>',
                                            Stephino_Rpg_Config::get()->core()->getConfigCityName(true)
                                        );
                                    ?>: <b><?php echo $premiumModifierConfig->getMaxQueueBuildings();?></b> 
                                    <?php echo esc_html__('instead of', 'stephino-rpg');?> 
                                    <b><?php echo Stephino_Rpg_Config::get()->core()->getMaxQueueBuildings();?></b>
                                </li>
                            <?php endif;?>
                            <?php if (null !== $premiumModifierConfig->getMaxQueueEntities()):?>
                                <li>
                                    <?php 
                                        echo sprintf(
                                            esc_html__('%s - Queues per %s', 'stephino-rpg'),
                                            '<b>' . Stephino_Rpg_Config::get()->core()->getConfigUnitsName(true) . '/' . Stephino_Rpg_Config::get()->core()->getConfigShipsName(true) . '</b>',
                                            Stephino_Rpg_Config::get()->core()->getConfigCityName(true)
                                        );
                                    ?>: <b><?php echo $premiumModifierConfig->getMaxQueueEntities();?></b>
                                    <?php echo esc_html__('instead of', 'stephino-rpg');?> 
                                    <b><?php echo Stephino_Rpg_Config::get()->core()->getMaxQueueEntities();?></b>
                                </li>
                            <?php endif;?>
                            <?php if (null !== $premiumModifierConfig->getMaxQueueResearchFields()):?>
                                <li>
                                    <?php 
                                        echo sprintf(
                                            esc_html__('%s - Queues in %s (total)', 'stephino-rpg'),
                                            '<b>' . Stephino_Rpg_Config::get()->core()->getConfigResearchFieldsName(true) . '</b>',
                                            Stephino_Rpg_Config::get()->core()->getConfigCitiesName(true)
                                        );
                                    ?>: <b><?php echo $premiumModifierConfig->getMaxQueueResearchFields();?></b>
                                    <?php echo esc_html__('instead of', 'stephino-rpg');?> 
                                    <b><?php echo Stephino_Rpg_Config::get()->core()->getMaxQueueResearchFields();?></b>
                                </li>
                            <?php endif;?>
                        </ul>
                    </div>
                </div>
            <?php endif;?>
            <?php if (null !== $premiumModifierConfig->getTimeContractionBuildings()
                || null !== $premiumModifierConfig->getTimeContractionEntities()
                || null !== $premiumModifierConfig->getTimeContractionResearchFields()):?>
                <div class="col-12 mb-4">
                    <h5><span><?php echo esc_html__('Time contraction', 'stephino-rpg');?></span></h5>
                    <div class="col-12">
                        <p>
                            <?php echo esc_html__('Speed-up the expansion of your empire', 'stephino-rpg');?>
                        </p>
                        <ul>
                            <?php if (null !== $premiumModifierConfig->getTimeContractionBuildings()):?>
                                <li>
                                    <?php 
                                        echo sprintf(
                                            esc_html__('%s - construction', 'stephino-rpg'),
                                            Stephino_Rpg_Config::get()->core()->getConfigBuildingsName(true)
                                        );
                                    ?>: <b><?php echo $premiumModifierConfig->getTimeContractionBuildings();?>&times;</b> <?php echo esc_html__('faster', 'stephino-rpg');?>
                                </li>
                            <?php endif;?>
                            <?php if (null !== $premiumModifierConfig->getTimeContractionEntities()):?>
                                <li>
                                    <?php 
                                        echo sprintf(
                                            esc_html__('%s - production', 'stephino-rpg'),
                                            Stephino_Rpg_Config::get()->core()->getConfigUnitsName(true) . '/'
                                            . Stephino_Rpg_Config::get()->core()->getConfigShipsName(true)
                                        );
                                    ?>: <b><?php echo $premiumModifierConfig->getTimeContractionEntities();?>&times;</b> <?php echo esc_html__('faster', 'stephino-rpg');?>
                                </li>
                            <?php endif;?>
                            <?php if (null !== $premiumModifierConfig->getTimeContractionResearchFields()):?>
                                <li>
                                    <?php 
                                        echo sprintf(
                                            esc_html__('%s - completion', 'stephino-rpg'),
                                            Stephino_Rpg_Config::get()->core()->getConfigResearchFieldsName(true)
                                        );
                                    ?>: <b><?php echo $premiumModifierConfig->getTimeContractionResearchFields();?>&times;</b> <?php echo esc_html__('faster', 'stephino-rpg');?>
                                </li>
                            <?php endif;?>
                        </ul>
                    </div>
                </div>
            <?php endif;?>
            <div class="col-12" data-role="premium-modifier-details">
                <?php 
                    require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                        Stephino_Rpg_Renderer_Ajax_Dialog_Premium::TEMPLATE_MODIFIERS_DETAILS
                    );
                ?>
            </div>
            <?php if (!isset($premiumModifierDuration)):?>
                <div class="col-12">
                    <div class="row no-gutters">
                        <input 
                            type="range"
                            min="1"
                            max="10"
                            value="1"
                            data-change="premiumModifierPreview"
                            data-change-args="<?php echo $premiumModifierConfig->getId();?>"
                            data-preview="true" />
                    </div>
                    <button 
                        class="btn w-100"
                        data-click="premiumModifierBuy"
                        data-click-args="<?php echo $premiumModifierConfig->getId();?>">
                        <span><?php echo esc_html__('Activate premium modifier', 'stephino-rpg');?></span>
                    </button>
                </div>
            <?php endif;?>
        </div>
    <?php endforeach; ?>
    <?php if (!is_super_admin() && Stephino_Rpg_Config::get()->core()->getShowWpLink()): ?>
        <div class="col-12 framed mb-4">
            <div class="col-12 mb-4">
                <div class="card card-body bg-dark mb-4"><?php echo esc_html__('Install this WordPress plugin for free and host your own game!', 'stephino-rpg');?></div>
            </div>
            <a class="btn btn-warning w-100" target="_blank" href="<?php echo esc_url(Stephino_Rpg::PLUGIN_URL_WORDPRESS);?>">
                <span><?php echo esc_html__('Free Install', 'stephino-rpg');?></span>
            </a>
        </div>
    <?php endif;?>
</div>