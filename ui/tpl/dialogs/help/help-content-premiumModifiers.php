<?php
/**
 * Template:Dialog:Help Content
 * 
 * @title      Help Content - PremiumModifier
 * @desc       Template for the help content - loaded both directly and with AJAX inside [data-role="content"]
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $configObject Stephino_Rpg_Config_PremiumModifier */
$costData = Stephino_Rpg_Renderer_Ajax_Action::getCostData($configObject);
$productionData = Stephino_Rpg_Renderer_Ajax_Action::getProductionData($configObject);
?>
<div class="row p-2 text-center justify-content-center">
    <div class="col-12">
        <h5>
            <?php echo esc_html__('Premium Modifiers', 'stephino-rpg');?> &#8250; <b><?php echo $configObject->getName(true);?></b>
        </h5>
    </div>
    <?php if (count($productionData)): ?>
        <div class="col-9">
            <div class="label premium-modifier-level-number">
                <span>
                    <?php echo esc_html__('Count', 'stephino-rpg');?>
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
    <h6 class="heading"><span><?php echo esc_html__('Duration', 'stephino-rpg');?></span></h6>    
    <?php 
        // Get the base
        $polynomialBase = $configObject->getDuration() * 3600;

        // Get the definition
        $polynomialDefinition = Stephino_Rpg_Utils_Config::getPolyJson(
            Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC_LINEAR, 
            1, 0, 1
        );
        
        // Format the output as HHH:MM:SS
        $polyTime = true;
    ?>
    <div class="res res-time">
        <div class="icon" data-html="true" title="<?php echo esc_attr__('Expiration time', 'stephino-rpg');?>"></div>
        <span
            data-html="true" 
            data-placement="bottom">
            <?php
                require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
                    Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_POLY
                );
            ?>
        </span>
    </div>
</div>
<?php if (null !== $configObject->getMaxQueueBuildings()
    || null !== $configObject->getMaxQueueEntities()
    || null !== $configObject->getMaxQueueResearchFields()):?>
    <div class="col-12 p-2">
        <h6 class="heading"><span><?php echo esc_html__('Maximum queues', 'stephino-rpg');?></span></h6>
        <div class="col-12">
            <div class="w-100 text-center p-2">
                <?php echo esc_html__('Overrides', 'stephino-rpg');?> 
                <span 
                    data-effect="helpMenuItem"
                    data-effect-args="<?php echo Stephino_Rpg_Config_Core::KEY;?>,<?php echo Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_RULES;?>">
                    <?php echo esc_html__('core settings', 'stephino-rpg');?>
                </span>
            </div>
            <ul>
                <?php if (null !== $configObject->getMaxQueueBuildings()):?>
                    <li>
                        <?php 
                            echo sprintf(
                                esc_html__('%s - Queues per %s', 'stephino-rpg'),
                                '<b>' . Stephino_Rpg_Config::get()->core()->getConfigBuildingsName(true) . '</b>',
                                Stephino_Rpg_Config::get()->core()->getConfigCityName(true)
                            );
                        ?>: <b><?php echo $configObject->getMaxQueueBuildings();?></b> 
                        <?php echo esc_html__('instead of', 'stephino-rpg');?> 
                        <b><?php echo Stephino_Rpg_Config::get()->core()->getMaxQueueBuildings();?></b>
                    </li>
                <?php endif;?>
                <?php if (null !== $configObject->getMaxQueueEntities()):?>
                    <li>
                        <?php 
                            echo sprintf(
                                esc_html__('%s - Queues per %s', 'stephino-rpg'),
                                '<b>' . Stephino_Rpg_Config::get()->core()->getConfigUnitsName(true) . '/'  . Stephino_Rpg_Config::get()->core()->getConfigShipsName(true) . '</b>',
                                Stephino_Rpg_Config::get()->core()->getConfigCityName(true)
                            );
                        ?>: <b><?php echo $configObject->getMaxQueueEntities();?></b>
                        <?php echo esc_html__('instead of', 'stephino-rpg');?> 
                        <b><?php echo Stephino_Rpg_Config::get()->core()->getMaxQueueEntities();?></b>
                    </li>
                <?php endif;?>
                <?php if (null !== $configObject->getMaxQueueResearchFields()):?>
                    <li>
                        <?php 
                        echo sprintf(
                                esc_html__('%s - Queues in %s (total)', 'stephino-rpg'),
                                '<b>' . Stephino_Rpg_Config::get()->core()->getConfigResearchFieldsName(true) . '</b>',
                                Stephino_Rpg_Config::get()->core()->getConfigCitiesName(true)
                            );
                        ?>: <b><?php echo $configObject->getMaxQueueResearchFields();?></b>
                        <?php echo esc_html__('instead of', 'stephino-rpg');?> 
                        <b><?php echo Stephino_Rpg_Config::get()->core()->getMaxQueueResearchFields();?></b>
                    </li>
                <?php endif;?>
            </ul>
        </div>
    </div>
<?php endif;?>
<?php if (null !== $configObject->getTimeContractionBuildings()
    || null !== $configObject->getTimeContractionEntities()
    || null !== $configObject->getTimeContractionResearchFields()):?>
    <div class="col-12 p-2">
        <h6 class="heading"><span><?php echo esc_html__('Time contraction', 'stephino-rpg');?></span></h6>
        <div class="col-12">
            <div class="w-100 text-center p-2">
                <?php echo esc_html__('Speed-up the expansion of your empire', 'stephino-rpg');?>
            </div>
            <ul>
                <?php if (null !== $configObject->getTimeContractionBuildings()):?>
                    <li>
                        <?php 
                            echo sprintf(
                                esc_html__('%s - construction', 'stephino-rpg'),
                                '<b>' . Stephino_Rpg_Config::get()->core()->getConfigBuildingsName(true) . '</b>'
                            );
                        ?>: <b><?php echo $configObject->getTimeContractionBuildings();?>&times;</b> <?php echo esc_html__('faster', 'stephino-rpg');?>
                    </li>
                <?php endif;?>
                <?php if (null !== $configObject->getTimeContractionEntities()):?>
                    <li>
                        <?php 
                            echo sprintf(
                                esc_html__('%s - production', 'stephino-rpg'),
                                '<b>' . Stephino_Rpg_Config::get()->core()->getConfigUnitsName(true) . '/' . Stephino_Rpg_Config::get()->core()->getConfigShipsName(true) . '</b>'
                            );
                        ?>: <b><?php echo $configObject->getTimeContractionEntities();?>&times;</b> <?php echo esc_html__('faster', 'stephino-rpg');?>
                    </li>
                <?php endif;?>
                <?php if (null !== $configObject->getTimeContractionResearchFields()):?>
                    <li>
                        <?php 
                            echo sprintf(
                                esc_html__('%s - completion', 'stephino-rpg'),
                                '<b>' . Stephino_Rpg_Config::get()->core()->getConfigResearchFieldsName(true) . '</b>'
                            );
                        ?>: <b><?php echo $configObject->getTimeContractionResearchFields();?>&times;</b> <?php echo esc_html__('faster', 'stephino-rpg');?>
                    </li>
                <?php endif;?>
            </ul>
        </div>
    </div>
<?php endif;?>
<?php 
    require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
        Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_ABUNDANCE
    );
?>
<?php 
    require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
        Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_DISCOUNTS
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