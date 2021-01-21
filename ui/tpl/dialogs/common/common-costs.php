<?php
/**
 * Template:Dialog:Common Costs
 * 
 * @title      Common costs template
 * @desc       Template for building/entity costs
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

// Research Area/Research Field etc. - no city data provided
if (!isset($cityData)) {
    // Prepare the city data; any city will do, as we'll be spending research points and gold (at the user level)
    $cityData = null;
    if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
        $cityData = current(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData());
    }
}

// Refund mode
if (!isset($costRefundMode)) {
    $costRefundMode = false;
}
if (!isset($costRefundPercent)) {
    $costRefundPercent = 0;
}
if (!isset($costTitle)) {
    $costTitle = false;
}
if (!isset($costData) || !is_array($costData)) {
    $costData = array();
}
if (!isset($costTime) || !is_numeric($costTime)) {
    $costTime = 0;
}
if (!isset($costDiscount)) {
    $costDiscount = null;
}
if (!isset($costTimeContraction)) {
    $costTimeContraction = null;
}
?>
<?php 
    if (
        is_array($cityData)
        && 
        ((count($costData) && (!$costRefundMode || $costRefundPercent > 0)) || !$costRefundMode)
    ):
?>
    <div class="row">
        <?php if (false !== $costTitle):?>
            <div class="col-12">
                <h5><span><?php echo $costTitle;?></span></h5>
            </div>
        <?php endif;?>
        <div class="col-12 row no-gutters mb-4 justify-content-center">
            <?php 
                foreach ($costData as $costKey => $costInfo):
                    list($costInfoName, $costInfoValue, $costInfoKey) = $costInfo;

                    // Adjust the return
                    if ($costRefundMode) {
                        $costInfoValue *= $costRefundPercent / 100;
                    }
            ?>
                <div class="col-6 col-lg-4 res res-<?php echo $costInfoKey;?>" data-click="resource" data-click-args="<?php echo $costInfoKey;?>">
                    <div class="icon" data-html="true" title="<?php echo Stephino_Rpg_Utils_Lingo::escape($costInfoName);?>"></div>
                    <span
                        data-html="true" 
                        title="<?php echo number_format($costInfoValue, 1);?>"
                        data-placement="bottom"
                        <?php if (!$costRefundMode && isset($cityData[$costKey]) && $cityData[$costKey] < $costInfoValue):?>
                            class="text-danger"
                        <?php endif;?>>
                        <?php echo Stephino_Rpg_Utils_Lingo::isuFormat($costInfoValue);?>
                        <span class="d-lg-none"><?php echo $costInfoName;?></span>
                    </span>
                </div>
            <?php endforeach;?>
            <?php if (!$costRefundMode):?>
                <div class="col-6 col-lg-4 res res-time">
                    <div class="icon" data-html="true" title="<?php echo esc_attr__('Time', 'stephino-rpg');?>"></div>
                    <span
                        data-html="true" 
                        data-placement="bottom">
                        <?php echo $costTime > 0 ? Stephino_Rpg_Utils_Lingo::secondsGM($costTime) : esc_html__('instant', 'stephino-rpg');?>
                    </span>
                </div>
            <?php endif;?>
        </div>
        <div class="col-12 row no-gutters mb-4">
            <?php 
                if (null !== $costDiscount):
                    list($discountPercent, $discountAjaxKey, $discountConfigId) = $costDiscount;
            ?>
                    <div class="badge badge-default">
                        <?php if (Stephino_Rpg_Config_PremiumModifiers::KEY == $discountAjaxKey):?>
                            &#11088;
                        <?php endif;?>
                        <span 
                            data-effect="help"
                            data-effect-args="<?php echo $discountAjaxKey;?>,<?php echo $discountConfigId;?>">
                            <b><?php echo $discountPercent;?></b>% <?php echo esc_html__('discount applied', 'stephino-rpg');?>
                        </span>
                    </div>
            <?php endif;?>
            <?php 
                if (is_array($costTimeContraction) && count($costTimeContraction) >= 2):
                    list($ctcAmount, $ctcPremiumConfigId) = $costTimeContraction;
                    if ($ctcAmount > 1 && $ctcPremiumConfigId > 0):
            ?>
                <div class="badge badge-default">
                    &#11088;
                    <span 
                        data-effect="help"
                        data-effect-args="<?php echo Stephino_Rpg_Config_PremiumModifiers::KEY;?>,<?php echo $ctcPremiumConfigId;?>">
                        <b><?php echo $ctcAmount;?>&times;</b> <?php echo esc_html__('faster than normal', 'stephino-rpg');?>
                    </span>
                </div>
            <?php endif; endif;?>
        </div>
    </div>
<?php endif;?>