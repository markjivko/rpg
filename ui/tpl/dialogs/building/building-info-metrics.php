<?php
/**
 * Template:Dialog:Building
 * 
 * @title      Building-Metrics
 * @desc       Template for the building metrics fragment
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $cityConfig Stephino_Rpg_Config_City */
/* @var $coreConfig Stephino_Rpg_Config_Core */
/* @var $buildingConfig Stephino_Rpg_Config_Building */
/* @var $governmentConfig Stephino_Rpg_Config_Government */
?>
<?php if ($buildingConfig->isMainBuilding()):?>
    <div class="framed">
        <div class="row no-gutters justify-content-center">
            <div class="col-6" id="government">
                <div class="card mb-4">
                    <h5><span><?php echo $coreConfig->getConfigGovernmentName(true);?></span></h5>
                    <button 
                        class="btn btn-default card-header" 
                        id="button-government" 
                        data-toggle="collapse" 
                        data-target="#collapse-government" 
                        aria-expanded="false" 
                        aria-controls="collapse-government">
                        <span>
                            <?php if (isset($governmentConfig) && null !== $governmentConfig):?>
                                <b><?php echo $governmentConfig->getName(true);?></b>
                            <?php else:?>
                                <i><?php 
                                    echo sprintf(
                                        esc_html__('%s: not set', 'stephino-rpg'),
                                        $coreConfig->getConfigGovernmentName(true)
                                    );
                                ?></i>
                            <?php endif;?>
                        </span>
                    </button>
                    <div 
                        id="collapse-government" 
                        aria-labelledby="button-government" 
                        data-parent="#government"
                        class="collapse">
                        <div class="row card-body justify-content-center">
                            <div class="col-12">
                                <button 
                                    class="btn btn-default w-100"
                                    data-click="dialog" 
                                    data-click-args="dialogCityGovernmentInfo,<?php echo $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID];?>">
                                    <span>
                                        <?php if (isset($governmentConfig) && null !== $governmentConfig):?>
                                            <?php 
                                                echo sprintf(
                                                    esc_html__('Change %s', 'stephino-rpg'),
                                                    $coreConfig->getConfigGovernmentName(true)
                                                );
                                            ?>
                                        <?php else:?>
                                            <?php 
                                                echo sprintf(
                                                    esc_html__('Set %s', 'stephino-rpg'),
                                                    $coreConfig->getConfigGovernmentName(true)
                                                );
                                            ?>
                                        <?php endif;?>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php foreach (array(
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_SATISFACTION => array(
                        $coreConfig->getMetricSatisfactionName(true),
                        Stephino_Rpg_Renderer_Ajax::RESULT_MTR_SATISFACTION
                    ),
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION => array(
                        $coreConfig->getMetricPopulationName(true),
                        Stephino_Rpg_Renderer_Ajax::RESULT_MTR_POPULATION
                    ),
                    Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_STORAGE => array(
                        $coreConfig->getMetricStorageName(true),
                        Stephino_Rpg_Renderer_Ajax::RESULT_MTR_STORAGE
                    ),
                ) as $mbdKey => $mbdInfo):
                
                // Get the growth factor
                $growthFactor = Stephino_Rpg_Db::get()->modelCities()->getGrowthFactor(
                    $buildingData[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION],
                    $buildingData[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_SATISFACTION]
                );
            ?>
                <div
                    class="col-6" 
                    id="metrics-<?php echo $mbdInfo[1];?>">
                    <div class="card mb-4">
                        <h5>
                            <span 
                                data-effect="help"
                                data-effect-args="<?php echo Stephino_Rpg_Config_Core::KEY;?>,<?php echo Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_CITY_METRICS;?>">
                                <?php echo $mbdInfo[0];?>
                            </span>
                        </h5>
                        <button 
                            class="btn btn-default card-header" 
                            id="metrics-button-<?php echo $mbdInfo[1];?>" 
                            data-toggle="collapse" 
                            data-target="#collapse-<?php echo $mbdInfo[1];?>" 
                            aria-expanded="false" 
                            aria-controls="collapse-<?php echo $mbdInfo[1];?>">
                            <span>
                                <div class="res res-<?php echo $mbdInfo[1];?>">
                                    <div class="icon"></div>
                                    <span
                                        <?php if (Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_SATISFACTION == $mbdKey):?>
                                            <?php switch (true): case ($buildingData[$mbdKey] <= 50): ?>
                                                class="text-danger"
                                            <?php break; case ($buildingData[$mbdKey] <= 75): ?>
                                                class="text-warning"
                                            <?php break; case ($buildingData[$mbdKey] <= 100): ?>
                                                class="text-info"
                                            <?php break; endswitch;?>
                                        <?php endif;?>>
                                        <b><?php echo Stephino_Rpg_Utils_Lingo::isuFormat($buildingData[$mbdKey]);?></b><?php 
                                            if (Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION == $mbdKey
                                                || Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_STORAGE == $mbdKey) {
                                                $mbdMaxValue = Stephino_Rpg_Utils_Config::getPolyValue(
                                                    Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION == $mbdKey 
                                                        ? $cityConfig->getMaxPopulationPolynomial()
                                                        : $cityConfig->getMaxStoragePolynomial(),
                                                    $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL], 
                                                    Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION == $mbdKey 
                                                        ? $cityConfig->getMaxPopulation()
                                                        : $cityConfig->getMaxStorage()
                                                ); 
                                                echo ' / ' . Stephino_Rpg_Utils_Lingo::isuFormat(
                                                    $mbdMaxValue, 
                                                    1, 
                                                    Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION == $mbdKey
                                                );
                                            }
                                         ?>
                                    </span>
                                </div>
                            </span>
                        </button>
                        <div 
                            id="collapse-<?php echo $mbdInfo[1];?>" 
                            aria-labelledby="metrics-button-<?php echo $mbdInfo[1];?>" 
                            data-parent="#metrics-<?php echo $mbdInfo[1];?>"
                            class="collapse">
                            <div class="row card-body justify-content-center">
                                <?php if (Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_SATISFACTION != $mbdKey):?>
                                    <div 
                                        class="w-100 mb-3"
                                        data-html="true"
                                        title="<?php 
                                            echo sprintf(
                                                esc_attr__('%s of %s by base level', 'stephino-rpg'),
                                                '<b>' . number_format($buildingData[$mbdKey]) . '</b>',
                                                '<b>' . number_format($mbdMaxValue) . '</b>'
                                            );
                                        ?>"
                                        data-effect="staticBar" 
                                        data-effect-args="<?php echo (round($buildingData[$mbdKey]) . ',' . round($mbdMaxValue));?>"
                                        data-click="helpDialog"
                                        data-click-args="<?php echo Stephino_Rpg_Config_Cities::KEY;?>,<?php echo $cityConfig->getId();?>">
                                    </div><br/>
                                <?php else: ?>
                                    <div 
                                        class="w-100 mb-3"
                                        data-html="true"
                                        title="<?php echo esc_attr__('Growth factor', 'stephino-rpg');?>: <b><?php echo number_format($growthFactor, 2);?></b>"
                                        data-effect="staticBar" 
                                        data-effect-args="<?php echo (round(($growthFactor + 1) * 20) . ',60');?>"
                                        data-click="helpDialog"
                                        data-click-args="<?php echo Stephino_Rpg_Config_Core::KEY;?>,<?php echo Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_RULES;?>">
                                    </div><br/>
                                <?php endif;?>
                                <ul class="content">
                                    <?php 
                                        $metricInfo = Stephino_Rpg_Renderer_Ajax_Action::getModifierEffectInfo(
                                            $mbdKey,
                                            $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]
                                        );

                                        // Prepare the total metric
                                        $metricTotal = 0;
                                    ?>
                                    <?php if (count($metricInfo)):?>
                                        <?php 
                                            foreach($metricInfo as $bdSatConfigKey => $bdSatData): 
                                                foreach($bdSatData as $bdSatConfigId => $bdSatValue): 
                                                    $metricTotal += $bdSatValue[0];
                                                    if (isset($bdSatValue[2]) && $bdSatValue[2] <= 0) {
                                                        continue;
                                                    }
                                        ?>
                                            <li>
                                                <span 
                                                    title="<?php echo ucfirst(preg_replace('%([A-Z])%', ' $1', $bdSatConfigKey));?>"
                                                    data-effect="help"
                                                    data-effect-args="<?php echo $bdSatConfigKey;?>,<?php echo $bdSatConfigId;?>">
                                                    <b><?php echo $bdSatValue[1];?></b>
                                                </span>: 
                                                <?php if (isset($bdSatValue[2])):?>
                                                    <ul>
                                                        <li title="<?php echo number_format($bdSatValue[0]);?>">
                                                            <?php echo Stephino_Rpg_Utils_Lingo::isuFormat($bdSatValue[0]);?>
                                                            <?php if (Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_SATISFACTION != $mbdKey):?>/h<?php endif;?>
                                                        </li>
                                                        <li><?php echo esc_html__('from', 'stephino-rpg');?>
                                                            <?php switch($bdSatConfigKey): case Stephino_Rpg_Config_Buildings::KEY:?>
                                                                <b><?php echo round($bdSatValue[2] * 100, 1);?></b>%
                                                                <?php
                                                                    $bdSatConfig = Stephino_Rpg_Config::get()->buildings()->getById($bdSatConfigId);
                                                                    echo (
                                                                        null !== $bdSatConfig && $bdSatConfig->isMainBuilding() && !$bdSatConfig->getUseWorkers() 
                                                                            ? $coreConfig->getMetricPopulationName(true) 
                                                                            : esc_html__('workforce', 'stephino-rpg')
                                                                    );
                                                                ?>
                                                            <?php break; 
                                                                case Stephino_Rpg_Config_Units::KEY: 
                                                                case Stephino_Rpg_Config_Ships::KEY: ?>
                                                                <b><?php echo round($bdSatValue[2], 0);?></b> <?php echo esc_html(_n('entity', 'entities', $bdSatValue[2], 'stephino-rpg'));?>
                                                            <?php break; endswitch;?>
                                                        </li>
                                                    </ul>
                                                <?php else:?>
                                                    <span title="<?php echo number_format($bdSatValue[0]);?>"><?php echo Stephino_Rpg_Utils_Lingo::isuFormat($bdSatValue[0]);?></span>
                                                    <?php if (Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_SATISFACTION != $mbdKey):?>/h<?php endif;?>
                                                <?php endif;?>
                                            </li>
                                        <?php endforeach; endforeach;?>
                                    <?php endif;?>
                                    <?php if (Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_SATISFACTION == $mbdKey): ?>
                                        <?php 
                                            // Metropolis bonus
                                            if ($cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_IS_CAPITAL] && $coreConfig->getCapitalSatisfactionBonus() > 0): 
                                                // Update the metric
                                                $metricTotal *= (1 + $coreConfig->getCapitalSatisfactionBonus() / 100);
                                        ?>
                                            <li>
                                                <b><?php echo esc_html__('Metropolis bonus', 'stephino-rpg');?></b>: &plus;<?php echo $coreConfig->getCapitalSatisfactionBonus();?>%
                                            </li>
                                        <?php endif;?>
                                    <?php endif;?>
                                    <?php if (Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION == $mbdKey):?>
                                        <?php 
                                            // Update the metric
                                            $metricTotal *= $growthFactor;
                                        ?>
                                        <li
                                            <?php switch (true): case ($growthFactor <= 0): ?>
                                                class="text-danger"
                                            <?php break; case ($growthFactor <= 0.5):?>
                                                class="text-warning"
                                            <?php break; case ($growthFactor <= 1):?>
                                                class="text-info"
                                            <?php break; endswitch;?>>
                                            <span
                                                data-effect="help"
                                                data-effect-args="<?php echo Stephino_Rpg_Config_Core::KEY;?>,<?php echo Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_RULES;?>">
                                                <?php echo esc_html__('Growth factor', 'stephino-rpg');?>
                                            </span>: &times;<?php echo number_format($growthFactor, 2);?>
                                        </li>
                                    <?php endif;?>
                                </ul>
                                <div class="col-12 text-center">
                                    <span
                                        <?php switch ($mbdKey): case Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_SATISFACTION:?>
                                            <?php if($metricTotal <= 0):?>
                                                title="<?php echo esc_attr__('Minimum reached: values under 0 are ignored', 'stephino-rpg');?>"
                                            <?php endif;?>
                                        <?php break; case Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION:?>
                                            <?php if ($buildingData[$mbdKey] >= $mbdMaxValue):?>
                                                <?php if ($buildingConfig->getUseWorkers()):?>
                                                    class="text-warning"
                                                    title="<?php echo esc_attr__('Maximum reached: migration stopped', 'stephino-rpg');?>"
                                                <?php else:?>
                                                    class="text-success"
                                                    title="<?php echo esc_attr__('Maximum reached: migration stopped and production at full capacity', 'stephino-rpg');?>"
                                                <?php endif;?>
                                            <?php elseif($metricTotal <= 0): ?>
                                                title="<?php 
                                                    echo sprintf(
                                                        esc_attr__('Fix %s to prevent further %s loss', 'stephino-rpg'),
                                                        $coreConfig->getMetricSatisfactionName(true),
                                                        $coreConfig->getMetricPopulationName(true)
                                                    );
                                                ?>"
                                            <?php endif;?>
                                        <?php break; case Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_STORAGE:?>
                                            <?php if ($buildingData[$mbdKey] >= $mbdMaxValue):?>
                                                class="text-warning"
                                                title="<?php echo esc_attr__('Maximum reached: expansion stopped', 'stephino-rpg');?>"
                                            <?php endif;?>
                                        <?php break; endswitch;?>>
                                        <?php if (Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_SATISFACTION != $mbdKey):?>
                                            <?php echo esc_html__('TOTAL', 'stephino-rpg');?>: <b
                                                title="<?php echo number_format($metricTotal);?>"
                                                <?php if ($metricTotal <= 0):?>
                                                    class="text-danger"
                                                <?php endif;?>>
                                                <?php echo Stephino_Rpg_Utils_Lingo::isuFormat($metricTotal);?>
                                            </b> /h
                                        <?php endif;?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach;?>
        </div>
    </div>
<?php endif;?>