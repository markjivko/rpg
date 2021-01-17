<?php
/**
 * Template:Dialog:Building
 * 
 * @title      Building dialog
 * @desc       Template for the building dialog
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $coreConfig Stephino_Rpg_Config_Core */
/* @var $buildingConfig Stephino_Rpg_Config_Building */
/* @var $cityConfig Stephino_Rpg_Config_City */
/* @var $unitConfig Stephino_Rpg_Config_Unit */
/* @var $shipConfig Stephino_Rpg_Config_Ship */

// Get the Research Field requirements
list($requirements, $requirementsMet) = Stephino_Rpg_Renderer_Ajax_Action::getRequirements(
    $buildingConfig, 
    $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]
);
?>
<div class="row mt-0 framed p-0">
    <div data-effect="parallax" <?php if (!$requirementsMet):?>class="disabled"<?php endif;?> data-effect-args="<?php echo Stephino_Rpg_Config_Buildings::KEY;?>,<?php echo $buildingConfig->getId();?>"></div>
    <div class="page-help">
        <span 
            data-effect="help"
            data-effect-args="<?php echo Stephino_Rpg_Config_Buildings::KEY;?>,<?php echo $buildingConfig->getId();?>">
            <?php echo $buildingConfig->getName(true);?>
        </span>
    </div>
</div>
<?php if ($requirementsMet):?>
    <div class="row item-level" data-uc="<?php echo (null === $queueData ? 'false' : 'true');?>">
        <div class="item-level-badge col-12 col-lg-8">
            <?php if (is_array($buildingData) && $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL] > 0):?>
                <div class="label item-level-number">
                    <span>
                        <?php echo esc_html__('Level', 'stephino-rpg');?> <b><?php echo ($buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL]);?></b>
                    </span>
                </div>
            <?php endif;?>
            <?php if (null !== $queueData):?>
                <button class="item-level-uc-cancel btn btn-warning" data-click="buildingUpgradeCancelDialog">
                    <span>
                        <span 
                            data-effect="countdownTime" 
                            data-effect-args="<?php 
                                echo ($queueData[Stephino_Rpg_Renderer_Ajax_Action_Building::DATA_QUEUE_TIME_LEFT] . ',' . $queueData[Stephino_Rpg_Renderer_Ajax_Action_Building::DATA_QUEUE_TIME_TOTAL]);
                            ?>">
                        </span>
                        &#11045; <b><?php echo esc_html__('Stop', 'stephino-rpg');?></b>
                    </span>
                </button>
                <div 
                    data-effect="sound"
                    data-effect-args="queueBuilding">
                </div>
            <?php else:?>
                <button class="item-level-upgrade btn btn-default" data-click="buildingUpgradeDialog">
                    <span><?php echo (is_array($buildingData) ? esc_html__('Upgrade', 'stephino-rpg') : esc_html__('Build', 'stephino-rpg'));?></span>
                </button>
            <?php endif;?>
            <?php if (is_array($buildingData) && $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL] > 0 && $buildingConfig->isMarketBuilding()):?>
                <button class="item-level-upgrade btn btn-default" data-click="buildingTradeDialog">
                    <span><?php echo esc_html__('Trade', 'stephino-rpg');?></span>
                </button>
            <?php endif;?>
            <?php 
                if ($buildingConfig->isMainBuilding()):
                    $workersUsed = false;
                    foreach (Stephino_Rpg_Config::get()->buildings()->getAll() as $altBuildingConfig) {
                        if ($altBuildingConfig->getUseWorkers()) {
                            $workersUsed = true;
                            break;
                        }
                    }
                    if ($workersUsed):
            ?>
                <button class="item-level-upgrade btn btn-default" data-click="cityWorkforceDialog">
                    <span><?php echo esc_html__('Workforce', 'stephino-rpg');?></span>
                </button>
            <?php endif;endif;?>
        </div>
        <?php if (null !== $queueData):?>
            <div 
                class="item-level-uc-bar" 
                title="<?php echo esc_attr__('Progress', 'stephino-rpg');?>" 
                data-effect="countdownBar" 
                data-effect-args="<?php echo ($queueData[Stephino_Rpg_Renderer_Ajax_Action_Building::DATA_QUEUE_TIME_LEFT] . ',' . $queueData[Stephino_Rpg_Renderer_Ajax_Action_Building::DATA_QUEUE_TIME_TOTAL]);?>">
            </div>
        <?php endif;?>
    </div>
    <?php if (is_array($buildingData) && $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL] > 0):?>
        <?php 
            require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                Stephino_Rpg_Renderer_Ajax_Dialog_Building::TEMPLATE_INFO_METRICS
            );
        ?>
        <?php if (isset($productionData) && is_array($productionData) && count($productionData)):?>
            <div class="framed">
                <div data-role="totalProduction">
                    <?php 
                        require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                            Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_PRODUCTION
                        );
                    ?>
                </div>
                <?php if (isset($workersCapacity) && $workersCapacity > 0): ?>
                    <div class="col-12 row no-gutters mb-4">
                        <input 
                            type="range"
                            min="0"
                            max="<?php echo $workersAvailable;?>"
                            value="<?php echo $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_WORKERS];?>"
                            data-change="buildingWorkers"
                            data-preview="true"
                            data-preview-label="/ <?php echo Stephino_Rpg_Utils_Lingo::isuFormat($workersCapacity);?>"
                            data-preview-label-title="<b><?php echo number_format($workersCapacity) . '</b>';?> <?php echo esc_attr__('workers', 'stephino-rpg');?>" />
                    </div>
                    <?php if ($workersAvailable < $workersCapacity):?>
                        <div class="col-12 row no-gutters mb-4">
                            <button class="item-level-upgrade btn btn-default w-100" data-click="cityWorkforceDialog">
                                <span><?php echo esc_html__('Workforce overview', 'stephino-rpg');?></span>
                            </button>
                        </div>
                    <?php endif;?>
                <?php endif;?>
                 <?php if ($buildingConfig->isMainBuilding() && !$buildingConfig->getUseWorkers()):?>
                    <div 
                        class="w-100 mb-3"
                        data-html="true"
                        title="<?php 
                            echo sprintf(
                                esc_attr__('Dependant on population, %s of %s by base level', 'stephino-rpg'),
                                '<b>' . number_format($cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION]) . '</b>',
                                '<b>' . number_format($cityMaxPopulation) . '</b>'
                            );
                        ?>"
                        data-effect="staticBar" 
                        data-effect-args="<?php echo (round($cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION]) . ',' . round($cityMaxPopulation));?>"
                        data-click="helpDialog"
                        data-click-args="<?php echo Stephino_Rpg_Config_Cities::KEY;?>,<?php echo $cityConfig->getId();?>">
                    </div>
                <?php endif;?>
            </div>
        <?php endif;?>
        <?php 
            require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                Stephino_Rpg_Renderer_Ajax_Dialog_Building::TEMPLATE_INFO_RESEARCH_AREAS
            );
        ?>
        <?php 
            if (count($unitsData)) {
                $entitiesData = $unitsData;
                require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                    Stephino_Rpg_Renderer_Ajax_Dialog_Building::TEMPLATE_INFO_ENTITIES
                );
            }
        ?>
        <?php 
            if (count($shipsData)) {
                $entitiesData = $shipsData;
                require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                    Stephino_Rpg_Renderer_Ajax_Dialog_Building::TEMPLATE_INFO_ENTITIES
                );
            }
        ?>
    <?php endif;?>
<?php else:?>
    <div class="framed col-12">
        <?php
            require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_REQUIREMENTS
            );
        ?>
    </div>
<?php endif; ?>