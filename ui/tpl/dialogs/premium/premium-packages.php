<?php
/**
 * Template:Dialog:Premium
 * 
 * @title      Premium packages dialog
 * @desc       Template for the premium packages
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $premiumPackageConfig Stephino_Rpg_Config_PremiumPackage */
?>
<div class="col-12">
    <div class="row align-items-center justify-content-center" data-role="premium-packages">
        <?php foreach (Stephino_Rpg_Config::get()->premiumPackages()->getAll() as $premiumPackageConfig):?>
            <div class="col-12 col-lg-8 col-xl-4 framed">
                <div class="col-12">
                    <h3>
                        <span><?php echo $premiumPackageConfig->getName(true);?></span>
                    </h3>
                </div>
                <div 
                    class="col-12 p-2 m-0 row justify-content-center" 
                    data-html="true" 
                    title="<?php echo number_format($premiumPackageConfig->getGem()) . ' ' . Stephino_Rpg_Config::get()->core()->getResourceGemName(true);?>">
                    <div class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_GEM;?> res-premium-table mb-4">
                        <div class="icon"></div>
                        <span>
                            <b><?php echo number_format($premiumPackageConfig->getGem());?></b>
                        </span>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card card-body bg-dark mb-4 text-center">
                        <?php if (strlen($premiumPackageConfig->getDescription())):?>
                            <?php echo Stephino_Rpg_Utils_Lingo::markdown($premiumPackageConfig->getDescription());?>
                        <?php else:?>
                            <i class="w-100 text-center"><?php echo esc_html__('no information', 'stephino-rpg');?></i>
                        <?php endif;?>
                    </div>
                </div>
                <?php if ($premiumPackageConfig->getCostGold()):?>
                    <div class="col-12">
                        <button 
                            <?php if (floor($userData[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD]) < $premiumPackageConfig->getCostGold()):?>
                                disabled="disabled"
                            <?php endif;?>
                            class="btn btn-danger w-100" 
                            data-html="true"
                            title="<b><?php echo number_format($premiumPackageConfig->getCostGold()) . '</b> ' . Stephino_Rpg_Config::get()->core()->getResourceGoldName(true);?>"
                            data-click="premiumPackageBuy" 
                            data-click-args="<?php echo $premiumPackageConfig->getId();?>,<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_GOLD;?>">
                            <span>
                                <div class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_GOLD;?>">
                                    <div class="icon"></div>
                                    <span>
                                        <b><?php echo Stephino_Rpg_Utils_Lingo::isuFormat($premiumPackageConfig->getCostGold());?></b>
                                    </span>
                                </div>
                            </span>
                        </button>
                    </div>
                <?php endif;?>
                <?php if ($premiumPackageConfig->getCostGold() && $premiumPackageConfig->getCostResearch()):?>
                    <div class="col-12 text-center">&dash; <?php echo esc_html__('OR', 'stephino-rpg');?> &dash;</div>
                <?php endif;?>
                <?php if ($premiumPackageConfig->getCostResearch()):?>
                    <div class="col-12">
                        <button 
                            <?php if (floor($userData[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH]) < $premiumPackageConfig->getCostResearch()):?>
                                disabled="disabled"
                            <?php endif;?>
                            class="btn btn-danger w-100" 
                            data-html="true"
                            title="<b><?php echo number_format($premiumPackageConfig->getCostResearch()) . '</b> ' . Stephino_Rpg_Config::get()->core()->getResourceResearchName(true);?>"
                            data-click="premiumPackageBuy" 
                            data-click-args="<?php echo $premiumPackageConfig->getId();?>,<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_RESEARCH;?>">
                            <span>
                                <div class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_RESEARCH;?>">
                                    <div class="icon"></div>
                                    <span>
                                        <b><?php echo Stephino_Rpg_Utils_Lingo::isuFormat($premiumPackageConfig->getCostResearch());?></b>
                                    </span>
                                </div>
                            </span>
                        </button>
                    </div>
                <?php endif;?>
                <?php if (Stephino_Rpg::get()->isPro() 
                    && ($premiumPackageConfig->getCostGold() || $premiumPackageConfig->getCostResearch()) 
                    && $premiumPackageConfig->getCostFiat() 
                    && null !== Stephino_Rpg_Config::get()->core()->getPayPalClientId()):?>
                    <div class="col-12 text-center">&dash; <?php echo esc_html__('OR', 'stephino-rpg');?> &dash;</div>
                <?php endif;?>
                <?php if (Stephino_Rpg::get()->isPro() && $premiumPackageConfig->getCostFiat() && null !== Stephino_Rpg_Config::get()->core()->getPayPalClientId()):?>
                    <div class="col-12">
                        <button 
                            class="btn btn-danger w-100" 
                            data-html="true"
                            title="<b><?php echo number_format($premiumPackageConfig->getCostFiat(), 2);?></b> <?php 
                                echo Stephino_Rpg_Db_Model_Invoices::CURRENCIES[Stephino_Rpg_Config::get()->core()->getPayPalCurrency()];
                            ?>"
                            data-click="premiumPackageBuy" 
                            data-click-args="<?php echo $premiumPackageConfig->getId();?>,<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_FIAT;?>">
                            <span><b><?php echo Stephino_Rpg_Config::get()->core()->getPayPalCurrency();?> <?php echo Stephino_Rpg_Utils_Lingo::currency($premiumPackageConfig->getCostFiat());?></b> (PayPal)</span>
                        </button>
                    </div>
                <?php endif;?>
                <?php if (!$premiumPackageConfig->getCostGold() 
                    && !$premiumPackageConfig->getCostResearch() 
                    && (!$premiumPackageConfig->getCostFiat() || null === Stephino_Rpg_Config::get()->core()->getPayPalClientId())):?>
                    <div class="col-12 text-center"><i><?php echo esc_html__('Coming soon...', 'stephino-rpg');?></i></div>
                <?php endif;?>
            </div>
        <?php endforeach; ?>
    </div>
</div>