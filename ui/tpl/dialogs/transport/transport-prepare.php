<?php
/**
 * Template:Dialog:Transport Prepare
 * 
 * @title      Transport Prepare dialog
 * @desc       Template for the transport preparation dialog
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $entityConfig Stephino_Rpg_Config_Ship */
?>
<?php if (count($transporterList)):?>
    <?php foreach($transporterList as list($cityData, $cityEntities)):?>
        <div class="col-12 framed" data-role="transport-city">
            <div class="col-12">
                <h5>
                    <span><?php echo Stephino_Rpg_Utils_Lingo::getCityName($cityData);?></span>
                </h5>
            </div>
            <div class="col-12">
                <?php foreach ($cityEntities as list($entityRow, $entityConfig)):?>
                    <?php 
                        if (null !== $entityConfig && $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CITY_ID] == $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]):
                            $entityKey = Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT == $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE]
                                ? Stephino_Rpg_Config_Units::KEY
                                : Stephino_Rpg_Config_Ships::KEY;
                    ?>
                        <div class="row">
                            <div class="entity col-6 col-lg-4">
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
                                        data-effect-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>">
                                        <?php echo $entityConfig->getName(true);?>
                                    </span>
                                </span>
                            </div>
                            <div class="col-12 col-lg-8">
                                <div class="row">
                                    <input 
                                        type="range" 
                                        data-change="cityTransportCapacityPreview"
                                        data-payload-capacity="<?php echo $entityConfig->getAbilityTransportCapacity();?>"
                                        data-payload-type="<?php echo Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES;?>"
                                        data-preview="true"
                                        name="<?php echo $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE] . '_' . $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID];?>" 
                                        value="0" 
                                        min="0" 
                                        max="<?php echo $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT];?>" />
                                </div>
                            </div>
                        </div>
                    <?php endif;?>
                <?php endforeach;?>
                <div class="row p-2 text-center">
                    <div class="col-12">
                        <?php echo esc_html__('Total capacity', 'stephino-rpg');?>: <b><span data-role="total-capacity">0</span></b>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <button 
                        class="btn w-100 d-none"
                        data-click="cityTransportReviewButton"
                        data-click-args="<?php echo $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID];?>,<?php echo $destinationCityInfo[Stephino_Rpg_Db_Table_Cities::COL_ID];?>">
                        <span><?php echo esc_html__('Prepare payload', 'stephino-rpg');?></span>
                    </button>
                </div>
            </div>
        </div>
    <?php endforeach;?>
<?php else: ?>
    <?php 
        require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
            Stephino_Rpg_Renderer_Ajax_Dialog_Transport::TEMPLATE_PREPARE_EMPTY
        );
    ?>
<?php endif;?>