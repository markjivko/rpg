<?php
/**
 * Template:Dialog:Transport Review
 * 
 * @title      Transport Review dialog
 * @desc       Template for the transport review dialog
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $entityConfig Stephino_Rpg_Config_Ship */
?>
<?php list($cityData, $cityEntities) = $allCityEntities; ?>
<div 
    class="col-12 framed" 
    data-role="transport-city" 
    data-range-submit="cityTransportButton"
    data-range-preview-bar="cargo-preview-bar"
    data-range-preview-label="cargo-preview-label"
    data-range-max="<?php echo $transporterCapacity;?>">
    <div class="col-12">
        <h5>
            <?php echo esc_html__('Transport from', 'stephino-rpg');?>
            <span
                data-click="dialog"
                data-click-args="dialogCityInfo,<?php echo $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID];?>">
                <?php echo Stephino_Rpg_Utils_Lingo::getCityName($cityData);?>
            </span>
            <?php echo esc_html__('to', 'stephino-rpg');?>
            <span
                data-click="dialog"
                data-click-args="dialogCityInfo,<?php echo $destinationCityInfo[Stephino_Rpg_Db_Table_Cities::COL_ID];?>">
                <?php echo Stephino_Rpg_Utils_Lingo::getCityName($destinationCityInfo);?>
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
    <?php foreach($transporterPayload as $entityRow):?>
        <div 
            class="d-none" 
            data-role="transporter" 
            data-entity-count="<?php echo $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT];?>"
            data-entity-type="<?php echo $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE];?>"
            data-entity-config="<?php echo $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID];?>">
        </div>
    <?php endforeach;?>
    <?php if (count($cityEntities)):?>
        <div class="col-12">
            <?php foreach ($cityEntities as list($entityRow, $entityConfig)):?>
                <?php 
                    if (null !== $entityConfig && $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CITY_ID] == $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]):
                        $entityKey = Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT == $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE]
                            ? Stephino_Rpg_Config_Units::KEY
                            : Stephino_Rpg_Config_Ships::KEY;
                ?>
                    <div class="row">
                        <div class="col-12 col-lg-3">
                            <div class="entity">
                                <div 
                                    class="icon" 
                                    data-click="helpDialog"
                                    data-click-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>"
                                    data-effect="background" 
                                    data-effect-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>">
                                </div>
                                <span>
                                    <span 
                                        data-effect="help"
                                        data-effect-args="<?php echo $entityKey?>,<?php echo $entityConfig->getId();?>">
                                        <?php echo $entityConfig->getName(true);?>
                                    </span>
                                    (<?php echo $entityConfig->getTransportMass();?> )
                                </span>
                            </div>
                        </div>
                        <div class="col-12 col-lg-9">
                            <div class="row">
                                <input 
                                    type="range" 
                                    data-change="multiRange"
                                    data-range-parent="transport-city"
                                    data-range-ratio="<?php echo $entityConfig->getTransportMass();?>"
                                    data-payload-type="<?php echo Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES;?>"
                                    data-preview="true"
                                    data-preview-label="/ <?php echo Stephino_Rpg_Utils_Lingo::isuFormat($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT]);?>"
                                    data-preview-label-title="<b><?php echo number_format($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT]) . '</b>';?>"
                                    name="<?php echo $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE] . '_' . $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID];?>" 
                                    value="0" 
                                    min="0" 
                                    max="<?php echo $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT];?>" />
                            </div>
                        </div>
                    </div>
                <?php endif;?>
            <?php endforeach;?>
        </div>
    <?php endif;?>
    <div class="col-12">
        <?php foreach ($cityResources as $resKey => list($resName, $resAjaxKey)):?>
            <?php if (isset($cityData[$resKey])):?>
                <div class="row">
                    <div class="col-12 col-lg-3">
                        <div class="res res-<?php echo $resAjaxKey;?>">
                            <div class="icon"></div>
                            <span>
                                <?php echo $resName;?>
                            </span>
                        </div>
                    </div>
                    <div class="col-12 col-lg-9">
                        <div class="row">
                            <input 
                                type="range" 
                                data-change="multiRange"
                                data-range-parent="transport-city"
                                data-range-ratio="1"
                                data-payload-type="<?php echo Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES;?>"
                                data-preview="true"
                                data-preview-label="/ <?php echo Stephino_Rpg_Utils_Lingo::isuFormat(floor($cityData[$resKey]));?>"
                                data-preview-label-title="<b><?php echo number_format(floor($cityData[$resKey])) . '</b>';?>"
                                name="<?php echo $resKey;?>" 
                                value="0" 
                                min="0" 
                                max="<?php echo round($cityData[$resKey], 0);?>" />
                        </div>
                    </div>
                </div>
            <?php endif;?>
        <?php endforeach;?>
    </div>
    <div class="col-12">
        <div class="row align-items-center">
            <div class="col-12 col-lg-9">
                <div 
                    data-role="cargo-preview-bar"
                    title="<?php echo esc_attr__('Total cargo', 'stephino-rpg');?>" 
                    data-effect="staticBar" 
                    data-effect-args="0,<?php echo $transporterCapacity;?>">
                </div>
            </div>
            <div class="col-12 col-lg-3">
                <div class="label">
                    <span>
                        <b><span data-role="cargo-preview-label">0</span></b> / <?php echo number_format($transporterCapacity);?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <button 
            class="btn w-100 btn-warning d-none"
            data-click="cityTransportButton"
            data-click-args="<?php echo $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID];?>,<?php echo $destinationCityInfo[Stephino_Rpg_Db_Table_Cities::COL_ID];?>">
            <span><?php echo esc_html__('Send goods', 'stephino-rpg');?></span>
        </button>
    </div>
</div>