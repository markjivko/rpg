<?php
/**
 * Template:Dialog:Resources
 * 
 * @title      Resources dialog
 * @desc       Template for the Resources dialog
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $resourcesCities array */
/* @var $currentResKey string */
?>
<?php if (isset($resourcesCities) && is_array($resourcesCities)):?>
    <?php foreach ($resourcesCities as $cityId => $cityData):?>
        <div class="framed mb-4">
            <h5>
                <span>
                    <?php echo Stephino_Rpg_Utils_Lingo::getCityName($cityData);?>
                </span>
            </h5>
            <div class="row no-gutters"
                 id="metrics-<?php echo $cityId;?>">
                <?php 
                    foreach ($cityData as $resKey => $resInfo): if (is_array($resInfo)): 
                        if (null === $currentCityId && !in_array($resKey, array(
                            Stephino_Rpg_Renderer_Ajax::RESULT_RES_GOLD,
                            Stephino_Rpg_Renderer_Ajax::RESULT_RES_GEM,
                            Stephino_Rpg_Renderer_Ajax::RESULT_RES_RESEARCH
                        ))) {
                            continue;
                        }
                        list($resInfoValue, $resInfoName, $resInfoMax, $resInfoKey) = $resInfo;
                ?>
                    <div
                        <?php if (null === $currentCityId && $resKey == Stephino_Rpg_Renderer_Ajax::RESULT_RES_GOLD):?>
                            class="col-12"
                        <?php else:?>
                            class="col-12 col-sm-6"
                        <?php endif;?>>
                        <button 
                            class="btn btn-default card-header w-100" 
                            id="metrics-button-<?php echo $cityId;?>-<?php echo $resKey;?>" 
                            aria-expanded="<?php echo ($currentResKey == $resKey ? 'true' : 'false');?>"
                            data-toggle="collapse" 
                            data-target="#collapse-<?php echo $cityId;?>-<?php echo $resKey;?>" 
                            aria-controls="collapse-<?php echo $cityId;?>-<?php echo $resKey;?>">
                            <span>
                                <div class="res res-<?php echo $resKey;?>">
                                    <div class="icon"></div>
                                    <span
                                        <?php if (null !== $resInfoMax && $resInfoValue >= $resInfoMax):?>
                                            class="text-warning"
                                        <?php endif;?>>
                                        <?php echo Stephino_Rpg_Utils_Lingo::isuFormat($resInfoValue);?>
                                    </span>
                                </div>
                            </span>
                        </button>
                        <div 
                            id="collapse-<?php echo $cityId;?>-<?php echo $resKey;?>" 
                            class="collapse<?php echo ($currentResKey == $resKey ? ' show' : '');?>"
                            aria-labelledby="metrics-button-<?php echo $cityId;?>-<?php echo $resKey;?>">
                            <div class="row card-body justify-content-center">
                                <?php if (!in_array($resKey, array(
                                    Stephino_Rpg_Renderer_Ajax::RESULT_RES_GOLD,
                                    Stephino_Rpg_Renderer_Ajax::RESULT_RES_GEM,
                                    Stephino_Rpg_Renderer_Ajax::RESULT_RES_RESEARCH
                                ))):?>
                                    <div 
                                        class="w-100 mb-3"
                                        data-html="true"
                                        title="<?php 
                                            echo esc_attr(sprintf(
                                                __('%s of %s total storage', 'stephino-rpg'), 
                                                '<b>' . number_format($resInfoValue) . '</b>',
                                                '<b>' . number_format($resInfoMax) . '</b>'
                                            ));
                                        ?>"
                                        <?php if (null !== $mainBuildingConfigId):?>
                                            data-click="buildingViewDialog"
                                            data-click-args="<?php echo $mainBuildingConfigId;?>"
                                        <?php endif;?>
                                        data-effect="staticBar" 
                                        data-effect-args="<?php echo (round($resInfoValue) . ',' . round($resInfoMax));?>">
                                    </div>
                                <?php endif;?>
                                <ul class="content">
                                    <?php 
                                        $metricInfo = Stephino_Rpg_Renderer_Ajax_Action::getModifierEffectInfo(
                                            $resInfoKey,
                                            $cityId
                                        );

                                        // Prepare the total metric
                                        $metricTotal = 0;
                                    ?>
                                    <?php if (count($metricInfo)):?>
                                        <?php 
                                            foreach($metricInfo as $bdSatConfigKey => $bdSatData): 
                                                foreach($bdSatData as $bdSatConfigId => $bdSatValue): 
                                                    $metricTotal += $bdSatValue[0];
                                        ?>
                                            <li>
                                                <span 
                                                    title="<?php echo ucfirst(preg_replace('%([A-Z])%', ' $1', $bdSatConfigKey));?>"
                                                    data-effect="help"
                                                    data-effect-args="<?php echo $bdSatConfigKey;?>,<?php echo $bdSatConfigId;?>">
                                                    <b><?php echo $bdSatValue[1];?></b>
                                                </span>: 
                                                <?php if (null !== $bdSatValue[2]):?>
                                                    <ul>
                                                        <li><b title="<?php echo number_format($bdSatValue[0]);?>"><?php echo Stephino_Rpg_Utils_Lingo::isuFormat($bdSatValue[0]);?></b> /h</li>
                                                        <li><?php echo esc_html__('from', 'stephino-rpg');?>
                                                            <?php switch($bdSatConfigKey): case Stephino_Rpg_Config_Buildings::KEY:?>
                                                                <b><?php echo round($bdSatValue[2] * 100, 0);?></b>% 
                                                                <?php
                                                                    $bdSatConfig = Stephino_Rpg_Config::get()->buildings()->getById($bdSatConfigId);
                                                                    echo (
                                                                        null !== $bdSatConfig && $bdSatConfig->isMainBuilding() && !$bdSatConfig->getUseWorkers() 
                                                                            ? Stephino_Rpg_Config::get()->core()->getMetricPopulationName(true) 
                                                                            : esc_html__('workforce', 'stephino-rpg')
                                                                    );
                                                                ?>
                                                            <?php break; 
                                                                case Stephino_Rpg_Config_Units::KEY: 
                                                                case Stephino_Rpg_Config_Ships::KEY: ?>
                                                                <b><?php echo round($bdSatValue[2], 0);?></b> <?php echo esc_html(_n('entity', 'entities', $bdSatValue[2], 'stephino-rpg'));?>
                                                            <?php break; endswitch;?>
                                                        </li>
                                                        <?php 
                                                            if (
                                                                (Stephino_Rpg_Renderer_Ajax::RESULT_RES_EXTRA_1 == $resKey
                                                                || Stephino_Rpg_Renderer_Ajax::RESULT_RES_EXTRA_2 == $resKey)
                                                                && null !== $bdSatValue[3]
                                                            ):
                                                                list($abundanceFactor, $abundanceConfigKey, $abundaceConfigId) = $bdSatValue[3];
                                                        ?>
                                                            <li>
                                                                <b><?php echo round($abundanceFactor * 100, 0);?></b>%
                                                                <span 
                                                                    data-effect="help"
                                                                    data-effect-args="<?php echo $abundanceConfigKey;?>,<?php echo $abundaceConfigId;?>">
                                                                    <b><?php echo esc_html__('abundance', 'stephino-rpg');?></b>
                                                                </span>
                                                            </li>
                                                        <?php endif;?>
                                                    </ul>
                                                <?php else:?>
                                                    <?php 
                                                        if (
                                                            (Stephino_Rpg_Renderer_Ajax::RESULT_RES_EXTRA_1 == $resKey
                                                            || Stephino_Rpg_Renderer_Ajax::RESULT_RES_EXTRA_2 == $resKey)
                                                            && null !== $bdSatValue[3]
                                                        ):
                                                            list($abundanceFactor, $abundanceConfigKey, $abundaceConfigId) = $bdSatValue[3];
                                                    ?>
                                                        <ul>
                                                            <li>
                                                                <b title="<?php echo number_format($bdSatValue[0]);?>"><?php echo Stephino_Rpg_Utils_Lingo::isuFormat($bdSatValue[0]);?></b> /h
                                                            </li>
                                                            <li>
                                                                <b><?php echo round($abundanceFactor * 100, 0);?></b>%
                                                                <span 
                                                                    data-effect="help"
                                                                    data-effect-args="<?php echo $abundanceConfigKey;?>,<?php echo $abundaceConfigId;?>">
                                                                    <b><?php echo esc_html__('abundance', 'stephino-rpg');?></b>
                                                                </span>
                                                            </li>
                                                        </ul>
                                                    <?php else:?>
                                                        <b title="<?php echo number_format($bdSatValue[0]);?>"><?php echo Stephino_Rpg_Utils_Lingo::isuFormat($bdSatValue[0]);?></b> /h
                                                    <?php endif;?>
                                                <?php endif;?>
                                            </li>
                                        <?php endforeach; endforeach;?>
                                    <?php endif;?>
                                </ul>
                                <div class="col-12 text-center">
                                    <span><?php echo esc_html__('TOTAL', 'stephino-rpg');?>: 
                                        <b
                                            title="<?php echo number_format($metricTotal);?>"
                                            <?php if ($metricTotal <= 0):?>
                                                class="text-danger"
                                            <?php endif;?>><?php echo Stephino_Rpg_Utils_Lingo::isuFormat($metricTotal);?></b> /h
                                    </span>
                                </div>
                                <?php 
                                    switch($resKey): 
                                        case Stephino_Rpg_Renderer_Ajax::RESULT_RES_GEM: 
                                            if (count(Stephino_Rpg_Config::get()->premiumPackages()->getAll())):
                                ?>
                                    <div class="col-12">
                                        <button 
                                            class="btn btn-default w-100" 
                                            data-click="dialog" 
                                            data-click-args="dialogPremiumPackageList">
                                            <span><?php echo esc_html__('Buy', 'stephino-rpg');?> <b><?php echo $resInfoName;?></b></span>
                                        </button>
                                    </div>
                                <?php 
                                            endif;
                                        break; 
                                    
                                    case Stephino_Rpg_Renderer_Ajax::RESULT_RES_GOLD:
                                    case Stephino_Rpg_Renderer_Ajax::RESULT_RES_RESEARCH:
                                        if (
                                            (Stephino_Rpg_Renderer_Ajax::RESULT_RES_GOLD == $resKey && Stephino_Rpg_Config::get()->core()->getGemToGoldRatio() > 0)
                                            || (Stephino_Rpg_Renderer_Ajax::RESULT_RES_RESEARCH == $resKey && Stephino_Rpg_Config::get()->core()->getGemToResearchRatio() > 0)
                                        ):
                                ?>
                                    <div class="col-12">
                                        <button 
                                            class="btn btn-default w-100" 
                                            data-click="dialog" 
                                            data-click-args="dialogUserTrade,<?php echo $resKey;?>">
                                            <span><?php echo esc_html__('Buy', 'stephino-rpg');?> <b><?php echo $resInfoName;?></b></span>
                                        </button>
                                    </div>
                                <?php endif; break; default:?>
                                    <?php if (null !== $resInfoMax && $resInfoValue >= $resInfoMax && null !== Stephino_Rpg_Config::get()->core()->getMainBuilding()):?>
                                        <div class="col-12">
                                            <button 
                                                class="btn btn-warning w-100 mb-3" 
                                                data-click="buildingViewDialog" 
                                                data-click-args="<?php echo Stephino_Rpg_Config::get()->core()->getMainBuilding()->getId();?>">
                                                <span><?php echo esc_html__('Upgrade', 'stephino-rpg');?> <b><?php echo Stephino_Rpg_Config::get()->core()->getMetricStorageName(true);?></b></span>
                                            </button>
                                        </div>
                                    <?php endif;?>
                                    <?php if (isset($tradingCities[$cityId])):?>
                                        <div class="col-12">
                                            <button 
                                                class="btn btn-default w-100" 
                                                data-click="buildingTradeDialog" 
                                                data-click-args="<?php echo $tradingBuilding->getId();?>">
                                                <span><?php echo esc_html__('Trade', 'stephino-rpg');?> <b><?php echo $resInfoName;?></b></span>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                <?php break; endswitch; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; endforeach;?>
            </div>
            <?php if ($currentCityId !== $cityId):?>
                <div class="row no-gutters">
                    <div class="col-12">
                        <button 
                            class="btn btn-default float-right" 
                            data-click="navigate" 
                            data-click-args="city,<?php echo $cityId;?>">
                            <span><?php echo esc_html__('Visit', 'stephino-rpg');?></span>
                        </button>
                    </div>
                </div>
            <?php endif;?>
        </div>
    <?php endforeach; ?>
<?php endif;?>