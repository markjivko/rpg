<?php
/**
 * Template:Dialog:Attack Prepare
 * 
 * @title      Attack Prepare dialog
 * @desc       Template for the attack preparation dialog
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $entityConfig Stephino_Rpg_Config_Unit|Stephino_Rpg_Config_Ship */
?>
<?php if (count($attackerCities)):?>
    <?php foreach($attackerCities as list($cityData, $cityEntities)):?>
        <div class="col-12 framed" data-role="attacking-city">
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
                            <div class="col-12 col-lg-3">
                                <div class="entity">
                                    <div 
                                        class="icon" 
                                        data-click="helpDialog"
                                        data-click-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>"
                                        data-effect="background" 
                                        data-effect-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>">
                                    </div>
                                    <span 
                                        data-effect="help"
                                        data-effect-args="<?php echo $entityKey?>,<?php echo $entityConfig->getId();?>">
                                        <?php echo $entityConfig->getName(true);?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-12 col-lg-9">
                                <div class="row">
                                    <input 
                                        type="range" 
                                        data-troop-capacity="<?php 
                                            echo $entityConfig instanceof Stephino_Rpg_Config_Ship
                                                ? $entityConfig->getTroopCapacity()
                                                : 0;
                                        ?>"
                                        data-troop-mass="<?php 
                                            echo $entityConfig instanceof Stephino_Rpg_Config_Unit
                                                ? $entityConfig->getTroopMass()
                                                : 0;
                                        ?>"
                                        data-loot-box="<?php echo $entityConfig->getLootBox();?>"
                                        data-points-attack="<?php echo $entityConfig->getDamage() * $entityConfig->getAmmo();?>"
                                        data-points-defense="<?php echo $entityConfig->getArmour() * $entityConfig->getAgility();?>"
                                        data-change="cityAttackPayloadChange"
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
            </div>
            <div class="col-12 row no-gutters d-none" data-role="army-stats">
                <?php if (Stephino_Rpg_Config::get()->core()->getTravelTime() > 0):?>
                    <div class="col-6 col-lg-4 mb-2">
                        <h5 class="heading"><span><?php 
                            echo sprintf(
                                esc_html__('%s: total capacity', 'stephino-rpg'),
                                Stephino_Rpg_Config::get()->core()->getConfigShipsName()
                            );
                        ?></span></h5>
                        <div class="col-12 text-center font-weight-bold" data-update="data-troop-capacity"></div>
                    </div>
                    <div class="col-6 col-lg-4 mb-2">
                        <h5 class="heading"><span><?php 
                            echo sprintf(
                                esc_html__('%s: total mass', 'stephino-rpg'),
                                Stephino_Rpg_Config::get()->core()->getConfigUnitsName()
                            );
                        ?></span></h5>
                        <div class="col-12 text-center font-weight-bold" data-update="data-troop-mass"></div>
                    </div>
                    <div class="col-6 col-lg-4 mb-2">
                        <h5 class="heading"><span><?php echo esc_html__('Army speed', 'stephino-rpg');?></span></h5>
                        <div class="col-12 text-center font-weight-bold" data-update="data-troop-speed"></div>
                    </div>
                <?php endif;?>
                <div class="col-6 col-lg-4 mb-2">
                    <h5 class="heading"><span><?php echo esc_html__('Total loot box size', 'stephino-rpg');?></span></h5>
                    <div class="col-12 text-center font-weight-bold" data-update="data-loot-box"></div>
                </div>
                <div class="col-6 col-lg-4 mb-2">
                    <h5 class="heading"><span><?php 
                        echo sprintf(
                            esc_html__('Total %s points', 'stephino-rpg'),
                            strtolower(Stephino_Rpg_Config::get()->core()->getMilitaryAttackName(true))
                        );
                    ?></span></h5>
                    <div class="col-12 text-center font-weight-bold" data-update="data-points-attack"></div>
                </div>
                <div class="col-6 col-lg-4 mb-2">
                    <h5 class="heading"><span><?php 
                        echo sprintf(
                            esc_html__('Total %s points', 'stephino-rpg'),
                            strtolower(Stephino_Rpg_Config::get()->core()->getMilitaryDefenseName(true))
                        );
                    ?></span></h5>
                    <div class="col-12 text-center font-weight-bold" data-update="data-points-defense"></div>
                </div>
            </div>
            <div class="col-12">
                <button 
                    class="btn w-100 d-none"
                    data-click="cityAttackReviewButton"
                    data-click-args="<?php echo $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID];?>,<?php echo $defenderCityInfo[Stephino_Rpg_Db_Table_Cities::COL_ID];?>">
                    <span><?php echo esc_html__('Review army', 'stephino-rpg');?></span>
                </button>
            </div>
        </div>
    <?php endforeach;?>
<?php else:?>
    <?php 
        require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
            Stephino_Rpg_Renderer_Ajax_Dialog_Attack::TEMPLATE_PREPARE_EMPTY
        );
    ?>
<?php endif;?>