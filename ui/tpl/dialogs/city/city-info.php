<?php
/**
 * Template:Dialog:City
 * 
 * @title      City dialog
 * @desc       Template for the city information dialog
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $cityConfig Stephino_Rpg_Config_City */
?>
<div class="row align-items-center">
    <div class="col-6">
        <div>
            <img class="city-icon" src="<?php echo Stephino_Rpg_Utils_Lingo::escape($cityIconUrl);?>'"/>
        </div>
        <h4 class="label w-100 footer-label">
            <span>
                <?php echo esc_html__('Level', 'stephino-rpg');?> <b><?php echo $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL];?></b>
                <div
                    data-html="true" 
                    data-placement="bottom" 
                    title="<b><?php 
                        echo Stephino_Rpg_Config::get()->core()->getMetricPopulationName(true);
                    ?></b>: <?php 
                        echo number_format($cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION]);
                    ?>"
                    class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_MTR_POPULATION;?> m-auto">
                    <div class="icon"></div>
                    <span>
                        <?php echo number_format($cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION]);?>
                    </span>
                </div>
            </span>
        </h4>
    </div>
    <div class="col-6">
        <div class="framed">
            <h5>
                <span>
                    <?php if (null !== $cityConfig):?>
                        <span 
                            data-effect="help"
                            data-effect-args="<?php echo $cityConfig->keyCollection();?>,<?php echo $cityConfig->getId();?>">
                            <?php echo $cityConfig->getName(true);?>
                        </span>
                    <?php endif;?>
                </span>
            </h5>
            <div class="row no-gutters pb-3">
                <button
                    class="btn btn-default w-100"
                    data-click="userViewProfile"
                    data-click-args="<?php echo Stephino_Rpg_Utils_Lingo::escape($cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_USER_ID]);?>">
                    <span>
                        <?php if (Stephino_Rpg_TimeLapse::get()->userId() == $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_USER_ID]):?>
                            <?php echo esc_html__('View my profile', 'stephino-rpg');?>
                        <?php else:?>
                            <?php 
                                echo sprintf(
                                    esc_html__('Profile of %s', 'stephino-rpg'),
                                    '<b>' . esc_html(Stephino_Rpg_Utils_Lingo::getUserName($userInfo)) . '</b>'
                                );
                            ?> 
                        <?php endif;?>
                    </span>
                </button>
                <?php if ($cityOwn):?>
                    <button 
                        class="btn w-100" 
                        data-click="navigate" 
                        data-click-args="city,<?php echo $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID];?>">
                        <span><?php 
                            echo sprintf(
                                esc_html__('Visit %s', 'stephino-rpg'),
                                Stephino_Rpg_Config::get()->core()->getConfigCityName(true)
                            );
                        ?></span>
                    </button>
                <?php else:?>
                    <?php if (count(Stephino_Rpg_Utils_Config::getEntitiesByCapability(Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_ATTACK))):?>
                        <button
                            class="btn w-100"
                            data-click="cityAttackPrepareButton"
                            data-click-args="<?php echo Stephino_Rpg_Utils_Lingo::escape($cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]);?>">
                            <span><?php 
                                echo sprintf(
                                    esc_html__('Attack %s', 'stephino-rpg'),
                                    Stephino_Rpg_Config::get()->core()->getConfigCityName(true)
                                );
                            ?></span>
                        </button>
                    <?php endif;?>
                    <?php if (count(Stephino_Rpg_Utils_Config::getEntitiesByCapability(Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_SPY))):?>
                        <button
                            class="btn w-100"
                            data-click="citySpyReviewButton"
                            data-click-args="<?php echo Stephino_Rpg_Utils_Lingo::escape($cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]);?>">
                            <span><?php echo esc_html__('Send spy', 'stephino-rpg');?></span>
                        </button>
                    <?php endif;?>
                <?php endif;?>
                <?php if (count(Stephino_Rpg_Utils_Config::getEntitiesByCapability(Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_TRANSPORTER))):?>
                    <button 
                        class="btn w-100"
                        data-click="dialog"
                        data-click-args="dialogTransportPrepare,<?php echo Stephino_Rpg_Utils_Lingo::escape($cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]);?>">
                        <span><?php echo esc_html__('Send goods', 'stephino-rpg');?></span>
                    </button>
                <?php endif;?>
            </div>
        </div>
    </div>
</div>