<?php
/**
 * Template:Dialog:User Trade
 * 
 * @title      User Trade dialog
 * @desc       Template for trading gem for gold or research points
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();
?>
<div class="col-12 framed p-4" data-role="trading">
    <?php if ($userData[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM] <= 0):?>
        <div class="text-center">
            <?php 
                echo sprintf(
                    esc_html__(
                        'You have no %s to trade', 
                        'stephino-rpg'
                    ),
                    '<b>' . esc_html(Stephino_Rpg_Config::get()->core()->getResourceGemName(true)) . '</b>'
                );
            ?>
        </div>
        <?php if (count(Stephino_Rpg_Config::get()->premiumPackages()->getAll())): ?>
            <div class="col-12">
                <button 
                    class="btn btn-default w-100" 
                    data-click="dialog" 
                    data-click-args="dialogPremiumPackageList">
                    <span><?php echo esc_html__('Buy', 'stephino-rpg');?> <b><?php echo $resInfoName;?></b></span>
                </button>
            </div>
        <?php endif;?>
    <?php else:?>
        <div class="row no-gutters col-12">
            <h5>
                <span
                    data-effect="help"
                    data-effect-args="<?php echo Stephino_Rpg_Config_Core::KEY;?>,<?php echo Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_RULES;?>">
                    <b>1.00</b> <?php echo htmlentities(Stephino_Rpg_Config::get()->core()->getResourceGemName(true));?>
                    <?php echo esc_html__('to', 'stephino-rpg');?> 
                    <b><?php echo number_format($tradeRatio, 2);?></b> <?php echo htmlentities($tradeName);?>
                </span>
            </h5>
        </div>
        <div class="row no-gutters col-12">
            <input 
                type="range" 
                data-change="userTradePreview"
                data-change-args="<?php echo $tradeRatio;?>,<?php echo $tradeType;?>"
                data-preview="true"
                data-preview-label="<?php echo htmlentities(Stephino_Rpg_Config::get()->core()->getResourceGemName(true));?>"
                name="rate" 
                value="0" 
                min="0" 
                max="<?php echo $userData[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM];?>" />
        </div>
        <div class="row no-gutters col-12 justify-content-center" data-role="div-preview">
            <div class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_GEM;?>">
                <div class="icon"></div>
                <span data-role="preview-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_GEM;?>">0</span>
            </div>
            <div class="res res-div">
                <span>&#187;</span>
            </div>
            <div class="res res-<?php echo $tradeType;?>">
                <div class="icon"></div>
                <span data-role="preview-<?php echo $tradeType;?>">0</span>
            </div>
        </div>
        <div class="row no-gutters col-12">
            <button 
                class="btn w-100 btn-warning d-none"
                data-click="userTrade"
                data-click-args="<?php echo $tradeType;?>">
                <span>
                    <b><?php echo esc_html__('Buy', 'stephino-rpg');?></b>
                    <span data-role="preview-<?php echo $tradeType;?>">0</span> 
                    <?php echo htmlentities($tradeName);?>
                </span>
            </button>
        </div>
    <?php endif;?>
</div>