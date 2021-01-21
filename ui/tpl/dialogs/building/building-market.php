<?php
/**
 * Template:Dialog:Building Market
 * 
 * @title      Market building dialog
 * @desc       Template for the market building dialog
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $tradeRatioMin float */
/* @var $cityData array */
/* @var $buildingData array */
/* @var $resourceData array */
/* @var $buildingConfig Stephino_Rpg_Config_Building */
?>
<div 
    class="col-12 framed" 
    data-role="trade-buy" 
    data-range-submit="buildingTradeButton"
    data-range-preview-bar="gold-preview-bar"
    data-range-preview-label="gold-preview-label"
    data-range-max="<?php echo $cityData[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD];?>">
    <div class="col-12">
        <h5>
            <?php 
                echo sprintf(
                    esc_html__('Buy resources with %s', 'stephino-rpg'),
                    '<span data-effect="help" data-effect-args="' . Stephino_Rpg_Config_Buildings::KEY . ',' . $buildingConfig->getId() . '">'
                        . '<b>' . Stephino_Rpg_Config::get()->core()->getResourceGoldName(true) . '</b>'
                    . '</span>'
                );
            ?> 
        </h5>
    </div>
    <?php if (floor($cityData[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD]) <= $tradeRatioMin * (1 + Stephino_Rpg_Config::get()->core()->getMarketGain() / 100)):?>
        <div class="col-12 text-center mb-4">
            <?php 
                echo sprintf(
                    esc_html__('No %s to trade for resources', 'stephino-rpg'),
                    '<b>' . Stephino_Rpg_Config::get()->core()->getResourceGoldName(true) . '</b>'
                );
            ?>
        </div>
        <?php if (floor($buildingData[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM]) > 0):?>
            <div class="col-12">
                <button 
                    class="btn btn-default w-100" 
                    data-click="dialog" 
                    data-click-args="dialogUserTrade,<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_GOLD;?>">
                    <span><?php echo esc_html__('Buy', 'stephino-rpg');?> <b><?php echo Stephino_Rpg_Config::get()->core()->getResourceGoldName(true);?></b></span>
                </button>
            </div>
        <?php endif;?>
    <?php else:?>
        <div class="col-12">
            <?php 
                foreach ($resourceData as $resKey => list($resName, $resAjaxKey, $tradeRatio)):
                    // More expensive to buy
                    $tradeRatio *= (1 + Stephino_Rpg_Config::get()->core()->getMarketGain() / 100);
                
                    // Trade max
                    $tradeMax = floor($cityData[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD] / $tradeRatio);
                    
                    // Prepare the maximum storage available
                    $tradeStorageMax = floor($cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_STORAGE] - $cityData[$resKey]);
                    
                    // Cannot store more than this
                    if ($tradeMax > $tradeStorageMax) {
                        $tradeMax = $tradeStorageMax;
                    }
            ?>
                <div class="row">
                    <div class="col-12 col-lg-3">
                        <div class="res res-<?php echo $resAjaxKey;?>">
                            <div class="icon"></div>
                            <span>
                                <?php echo $resName;?> &#171; <b><?php echo number_format($tradeRatio, 2);?></b> <?php echo Stephino_Rpg_Config::get()->core()->getResourceGoldName(true);?>
                            </span>
                        </div>
                    </div>
                    <div class="col-12 col-lg-9">
                        <div class="row">
                            <input 
                                type="range" 
                                data-change="multiRange"
                                data-range-parent="trade-buy"
                                data-range-ratio="<?php echo $tradeRatio;?>"
                                data-preview="true"
                                data-preview-label="/ <?php echo Stephino_Rpg_Utils_Lingo::isuFormat($tradeMax);?>"
                                data-preview-label-title="<b><?php echo number_format($tradeMax) . '</b> ' . $resName;?>"
                                name="<?php echo $resKey;?>" 
                                value="0" 
                                min="0" 
                                max="<?php echo $tradeMax;?>" />
                        </div>
                    </div>
                </div>
            <?php endforeach;?>
        </div>
        <div class="col-12">
            <div class="row align-items-center">
                <div class="col-12 col-lg-9">
                    <div 
                        data-role="gold-preview-bar"
                        title="<?php 
                            echo sprintf(
                                esc_attr__('Available %s', 'stephino-rpg'), 
                                Stephino_Rpg_Config::get()->core()->getResourceGoldName(true)
                            );
                        ?>" 
                        data-effect="staticBar" 
                        data-effect-args="0,<?php echo $cityData[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD];?>">
                    </div>
                </div>
                <div class="col-12 col-lg-3">
                    <div class="label">
                        <span>
                            <b><span data-role="gold-preview-label">0</span></b> / 
                                <span
                                    data-html="true"
                                    title="<b><?php echo number_format($cityData[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD]);?></b>">
                                    <?php echo Stephino_Rpg_Utils_Lingo::isuFormat($cityData[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD]);?>
                                </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <button 
                class="btn w-100 btn-warning d-none"
                data-click="buildingTradeButton"
                data-click-args="buy">
                <span><?php
                    echo sprintf(
                        esc_html__('Buy resources with %s', 'stephino-rpg'),
                        '<b><span data-role="gold-preview-label">0</span> ' . Stephino_Rpg_Config::get()->core()->getResourceGoldName(true) . '</b>'
                    );
                ?></span>
            </button>
        </div>
    <?php endif;?>
</div>
<div 
    class="col-12 framed" 
    data-range-submit="buildingTradeButton"
    data-range-preview-label="gold-preview-label"
    data-role="trade-sell">
    <div class="col-12">
        <h5>
            <?php 
                echo sprintf(
                    esc_html__('Sell resources for %s', 'stephino-rpg'),
                    '<span data-effect="help" data-effect-args="' . Stephino_Rpg_Config_Buildings::KEY . ',' . $buildingConfig->getId() . '">'
                        . '<b>' . Stephino_Rpg_Config::get()->core()->getResourceGoldName(true) . '</b>'
                    . '</span>'
                );
            ?> 
        </h5>
    </div>
    <div class="col-12">
        <?php 
            foreach ($resourceData as $resKey => list($resName, $resAjaxKey, $tradeRatio)):
                $tradeMax = floor($cityData[$resKey]);
        ?>
            <div class="row">
                <div class="col-12 col-lg-3">
                    <div class="res res-<?php echo $resAjaxKey;?>">
                        <div class="icon"></div>
                        <span>
                            <?php echo $resName;?> &#187; <b><?php echo number_format($tradeRatio, 2);?></b> <?php echo Stephino_Rpg_Config::get()->core()->getResourceGoldName(true);?>
                        </span>
                    </div>
                </div>
                <div class="col-12 col-lg-9">
                    <div class="row">
                        <input 
                            type="range" 
                            data-change="multiRange"
                            data-range-parent="trade-sell"
                            data-range-ratio="<?php echo $tradeRatio;?>"
                            data-preview="true"
                            data-preview-label="/ <?php echo Stephino_Rpg_Utils_Lingo::isuFormat($tradeMax);?>"
                            data-preview-label-title="<b><?php echo number_format($tradeMax) . '</b> ' . $resName;?>"
                            name="<?php echo $resKey;?>" 
                            value="0" 
                            min="0" 
                            max="<?php echo $tradeMax;?>" />
                    </div>
                </div>
            </div>
        <?php endforeach;?>
    </div>
    <div class="col-12">
        <button 
            class="btn w-100 btn-warning d-none"
            data-click="buildingTradeButton"
            data-click-args="sell">
            <span><?php
                echo sprintf(
                    esc_html__('Sell resources for %s', 'stephino-rpg'),
                    '<b><span data-role="gold-preview-label">0</span> ' . Stephino_Rpg_Config::get()->core()->getResourceGoldName(true) . '</b>'
                );
            ?></span>
        </button>
    </div>
</div>