<?php
/**
 * Template:Dialog:Common Production
 * 
 * @title      Common production template
 * @desc       Template for production details
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $productionData array */
if (!isset($productionTitle)) {
    $productionTitle = esc_html__('Production', 'stephino-rpg');
}
if (!isset($productionTitleHelp)) {
    $productionTitleHelp = Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_CITY_RES;
}
if (!isset($militaryTitle)) {
    $militaryTitle = esc_html__('Military capabilities', 'stephino-rpg');
}

// Refund mode
if (!isset($productionRefundMode)) {
    $productionRefundMode = false;
}

// Hourly mode
if (!isset($productionHourly)) {
    $productionHourly = true;
}

// Hide zero values
if (!isset($productionHideZero)) {
    $productionHideZero = false;
}
?>
<?php 
    if (isset($productionData) && count($productionData)):
        $splitProduction = array();
    
        // Split the production data
        foreach (array_keys($productionData) as $prodKey) {
            $splitProdKey = (int) in_array($prodKey, array(
                Stephino_Rpg_Config_Building::RES_MILITARY_ATTACK, 
                Stephino_Rpg_Config_Building::RES_MILITARY_DEFENSE
            ));
            if (!isset($splitProduction[$splitProdKey])) {
                $splitProduction[$splitProdKey] = array();
            }
            $splitProduction[$splitProdKey][$prodKey] = $productionData[$prodKey];
        }
?>
    <div class="row">
        <?php 
            foreach ($splitProduction as $splitProdKey => $splitProdValue):
                // Prepare the section title
                $splitProdTitle = $splitProdKey ? $militaryTitle : $productionTitle;
        ?>
            <?php if (false !== $splitProdTitle):?>
                <div class="col-12">
                    <h5>
                        <?php if ($splitProdKey):?>
                            <span><?php echo $splitProdTitle;?></span>
                        <?php else:?>
                            <?php if (is_string($productionTitleHelp) && strlen($productionTitleHelp)):?>
                                <span 
                                    data-effect="help"
                                    data-effect-args="<?php echo Stephino_Rpg_Config_Core::KEY;?>,<?php echo $productionTitleHelp;?>">
                                    <?php echo $splitProdTitle;?>
                                </span>
                            <?php else:?>
                                <?php echo $splitProdTitle;?>
                            <?php endif;?>
                        <?php endif;?>
                    </h5>
                </div>
            <?php endif;?>
            <div class="col-12 row no-gutters mb-4 justify-content-center">
                <?php 
                    foreach ($splitProdValue as $prodKey => $prodInfo):
                        list($prodName, $prodValue, $prodAjaxKey, $prodAbundance) = $prodInfo;
                        if ($productionRefundMode) {
                            $prodValue *= -1;
                        }
                        if ($productionHideZero && 0 == $prodValue) {
                            continue;
                        }
                ?>
                    <div class="col-6 col-lg-4 res res-<?php echo $prodAjaxKey;?>">
                        <div class="icon" data-html="true" title="<?php echo esc_attr($prodName);?>"></div>
                        <span>
                            <?php 
                                if (null !== $prodAbundance):
                                    list($abundanceFactor, $abundanceConfigKey, $abundaceConfigId) = $prodAbundance;
                            ?>
                                <span 
                                    title="<b><?php echo number_format($prodValue) . ' ' . $prodName;?></b>, <?php echo round($abundanceFactor * 100, 0);?>% <?php echo esc_attr__('abundance', 'stephino-rpg');?>"
                                    data-html="true"
                                    data-effect="help"
                                    data-effect-args="<?php echo $abundanceConfigKey;?>,<?php echo $abundaceConfigId;?>">
                                    <b><?php echo Stephino_Rpg_Utils_Lingo::isuFormat($prodValue);?></b>
                                </span><?php if ($productionHourly):?>&nbsp;/h <?php endif;?><span class="d-lg-none"><?php echo $prodName;?></span>
                            <?php else:?>
                                <span 
                                    title="<b><?php echo number_format($prodValue) . ' ' . $prodName;?></b>"
                                    data-html="true">
                                    <b><?php echo Stephino_Rpg_Utils_Lingo::isuFormat($prodValue);?></b><?php 
                                        if (!in_array($prodKey, array(
                                            Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_SATISFACTION,
                                            Stephino_Rpg_Config_Building::RES_MILITARY_ATTACK,
                                            Stephino_Rpg_Config_Building::RES_MILITARY_DEFENSE,
                                        ))):
                                    ?><?php if ($productionHourly):?>&nbsp;/h<?php endif;?><?php endif;?>
                                </span>
                                <span class="d-lg-none"><?php echo $prodName;?></span>
                            <?php endif;?>
                        </span>
                    </div>
                <?php endforeach;?>
            </div>
        <?php endforeach;?>
    </div>
<?php endif;?>