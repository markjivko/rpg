<?php
/**
 * Template:Dialog:Spy Review
 * 
 * @title      Spy Review dialog
 * @desc       Template for the spy review dialog
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $entityConfig Stephino_Rpg_Config_Unit */
?>
<?php if (count($spiesList)):?>
    <?php foreach ($spiesList as $cityId => list($cityData, $cityEntities)):?>
        <div class="col-12 framed">
            <div class="row">
                <h5>
                    <?php echo esc_html__('From', 'stephino-rpg');?>
                    <span
                        data-click="dialog"
                        data-click-args="dialogCityInfo,<?php echo $cityId;?>">
                        <?php echo Stephino_Rpg_Utils_Lingo::getCityName($cityData);?>
                    </span>
                </h5>
                <?php 
                    list(, $convoyTravelTime) = Stephino_Rpg_Db::get()->modelConvoys()->getTravelInfo(
                        $cityData, 
                        $destinationCityInfo
                    );
                    if ($convoyTravelTime > 0):
                ?>
                    <div class="col-12 text-center">
                        <div class="res res-time">
                            <div class="icon" data-html="true" title="<?php echo esc_attr__('Travel time', 'stephino-rpg');?>"></div>
                            <span
                                data-html="true" 
                                title="<?php echo Stephino_Rpg_Utils_Lingo::secondsHR($convoyTravelTime);?>">
                                <?php echo Stephino_Rpg_Utils_Lingo::secondsGM($convoyTravelTime);?>
                            </span>
                        </div>
                    </div>
                <?php endif;?>
            </div>
            <div class="row justify-content-center">
                <?php foreach ($cityEntities as $entityId => list($entityRow, $entityConfig, $buildingRow)):?>
                    <div class="col-6 col-lg-4 text-center">
                        <div 
                            class="building-entity-icon framed mt-4" 
                            data-click="helpDialog"
                            data-click-args="<?php echo Stephino_Rpg_Config_Units::KEY;?>,<?php echo $entityConfig->getId();?>"
                            data-effect="background" 
                            data-effect-args="<?php echo Stephino_Rpg_Config_Units::KEY;?>,<?php echo $entityConfig->getId();?>">
                            <span 
                                data-effect="help"
                                data-effect-args="<?php echo Stephino_Rpg_Config_Units::KEY;?>,<?php echo $entityConfig->getId();?>">
                                <?php echo $entityConfig->getName(true);?>
                            </span>
                        </div>
                        <p>
                            <?php echo esc_html__('Success rate', 'stephino-rpg');?>: <b><?php 
                                $spySuccessRate = Stephino_Rpg_Utils_Config::getPolyValue(
                                    $entityConfig->getSpySuccessRatePolynomial(), 
                                    null === $buildingRow 
                                        ? 1
                                        : $buildingRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL], 
                                    $entityConfig->getSpySuccessRate()
                                );

                                // Limits
                                if ($spySuccessRate < 1) {
                                    $spySuccessRate = 1;
                                }
                                if ($spySuccessRate > 100) {
                                    $spySuccessRate = 100;
                                }
                                echo number_format($spySuccessRate, 2);
                            ?></b>%
                        </p>
                        <button
                            class="btn btn-warning w-100"
                            data-click="citySpyButton"
                            data-click-args="<?php echo $destinationCityId;?>,<?php echo $cityId;?>,<?php echo $entityId;?>">
                            <span><?php echo esc_html__('Send', 'stephino-rpg');?> <b><?php echo $entityConfig->getName(true);?></b></span>
                        </button>
                    </div>
                <?php endforeach;?>
            </div>
        </div>
    <?php endforeach;?>
<?php else: ?>
    <?php 
        require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
            Stephino_Rpg_Renderer_Ajax_Dialog_Spy::TEMPLATE_REVIEW_EMPTY
        );
    ?>
<?php endif;?>