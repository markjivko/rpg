<?php
/**
 * Template:Dialog:Entity
 * 
 * @title      Entity list dialog
 * @desc       Template for garrisoned entities
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $cityData array|null */
/* @var $cityEntities array|null */
$totalAttack = 0;
$totalDefense = 0;
?>
<?php if (is_array($cityEntities) && count($cityEntities)):?>
    <?php 
        foreach($cityEntities as list($entityRow, $entityConfig)):
            $entityKey = $entityConfig instanceof Stephino_Rpg_Config_Unit
                ? Stephino_Rpg_Config_Units::KEY
                : Stephino_Rpg_Config_Ships::KEY;
        
            // Prepare the military capabiliies
            $entityAttackPoints = $entityConfig->getCivilian() ? 0 : $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT] * (
                $entityConfig->getDamage() * $entityConfig->getAmmo()
            );
            $entityDefensePoints = $entityConfig->getCivilian() ? 0 : $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT] * (
                $entityConfig->getArmour() * $entityConfig->getAgility()
            );
            
            // Add to the totals
            $totalAttack += $entityAttackPoints;
            $totalDefense += $entityDefensePoints;
    ?>
    <div class="framed">
        <div class="col-12 row align-items-center m-0">
            <div class="col-12 col-lg-3 text-center"
                data-role="entity" 
                data-entity-count="<?php echo $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT];?>"
                data-entity-type="<?php echo $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE];?>"
                data-entity-config="<?php echo $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID];?>" >
                <div 
                    class="building-entity-icon framed mt-4" 
                    data-click="helpDialog"
                    data-click-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>"
                    data-effect="background" 
                    data-effect-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>">
                    <span 
                        data-effect="help"
                        data-effect-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>">
                        <?php echo $entityConfig->getName(true);?>
                    </span>
                    <span class="label" data-html="true" title="&times; <?php echo number_format($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT]);?>">
                        <span>
                            &times; <b><?php echo Stephino_Rpg_Utils_Lingo::isuFormat($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT]);?></b>
                        </span>
                    </span>
                </div>
            </div>
            <div class="col-12 col-lg-9 row">
                <div class="col-12 col-lg-6">
                    <div class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_MIL_ATTACK;?>"
                        title="<?php echo number_format($entityAttackPoints);?>"
                        data-html="true">
                        <div class="icon"></div>
                        <span>
                            <?php echo Stephino_Rpg_Config::get()->core()->getMilitaryAttackName(true);?>: <b><?php
                                echo Stephino_Rpg_Utils_Lingo::isuFormat($entityAttackPoints);
                            ?></b>
                        </span>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_MIL_DEFENSE;?>"
                        title="<?php echo number_format($entityDefensePoints);?>"
                        data-html="true">
                        <div class="icon"></div>
                        <span>
                            <?php echo Stephino_Rpg_Config::get()->core()->getMilitaryDefenseName(true);?>: <b><?php
                                echo Stephino_Rpg_Utils_Lingo::isuFormat($entityDefensePoints);
                            ?></b>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach;?>
<?php else:?>
    <div class="framed col-12 w-100">
        <div class="row justify-content-center">
            <i><?php 
                echo sprintf(
                    esc_html__('There are no %s or %s garrisoned in this %s', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigUnitsName(true),
                    Stephino_Rpg_Config::get()->core()->getConfigShipsName(true),
                    Stephino_Rpg_Config::get()->core()->getConfigCityName(true)
                );
            ?></i>   
        </div>
    </div>
<?php endif;?>
<?php if (count($militaryBuildings)):?>
    <h5><span><?php 
        echo sprintf(
            esc_html__('%s (military)', 'stephino-rpg'),
            Stephino_Rpg_Config::get()->core()->getConfigBuildingsName(true)
        );
    ?></span></h5>
    <?php 
        foreach ($militaryBuildings as $militaryBuildingConfigId => $militaryBuilding):
            // Get the building configuration
            $buildingConfig = Stephino_Rpg_Config::get()->buildings()->getById($militaryBuildingConfigId);

            // Get the building level
            $buildingLevel = $militaryBuilding[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL];
            
            // Add to the totals
            $totalAttack += $militaryBuilding[Stephino_Rpg_Renderer_Ajax::RESULT_MIL_ATTACK];
            $totalDefense += $militaryBuilding[Stephino_Rpg_Renderer_Ajax::RESULT_MIL_DEFENSE];
    ?>
        <div class="framed">
            <div class="col-12 row align-items-center m-0">
                <div class="col-12 col-lg-3 text-center">
                    <div 
                        class="building-entity-icon framed mt-4" 
                        data-click="helpDialog"
                        data-click-args="<?php echo Stephino_Rpg_Config_Buildings::KEY;?>,<?php echo $buildingConfig->getId();?>"
                        data-effect="background" 
                        data-effect-args="<?php echo Stephino_Rpg_Config_Buildings::KEY;?>,<?php echo $buildingConfig->getId();?>">
                        <span 
                            data-effect="help"
                            data-effect-args="<?php echo Stephino_Rpg_Config_Buildings::KEY;?>,<?php echo $buildingConfig->getId();?>">
                            <?php echo $buildingConfig->getName(true);?>
                        </span>
                        <span class="label">
                            <span>
                                <?php echo esc_html__('Level', 'stephino-rpg');?> <b><?php echo $buildingLevel;?></b>
                            </span>
                        </span>
                    </div>
                </div>
                <div class="col-12 col-lg-9 row">
                    <div class="col-12 col-lg-6">
                        <div class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_MIL_ATTACK;?>"
                            title="<?php echo number_format($militaryBuilding[Stephino_Rpg_Renderer_Ajax::RESULT_MIL_ATTACK]);?>"
                            data-html="true">
                            <div class="icon"></div>
                            <span>
                                <?php echo Stephino_Rpg_Config::get()->core()->getMilitaryAttackName(true);?>: <b><?php
                                    echo Stephino_Rpg_Utils_Lingo::isuFormat($militaryBuilding[Stephino_Rpg_Renderer_Ajax::RESULT_MIL_ATTACK]);
                                ?></b>
                            </span>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_MIL_DEFENSE;?>"
                            title="<?php echo number_format($militaryBuilding[Stephino_Rpg_Renderer_Ajax::RESULT_MIL_DEFENSE]);?>"
                            data-html="true">
                            <div class="icon"></div>
                            <span>
                                <?php echo Stephino_Rpg_Config::get()->core()->getMilitaryDefenseName(true);?>: <b><?php
                                    echo Stephino_Rpg_Utils_Lingo::isuFormat($militaryBuilding[Stephino_Rpg_Renderer_Ajax::RESULT_MIL_DEFENSE]);
                                ?></b>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif;?>
<h5><span><?php echo esc_html__('Total military capabilities', 'stephino-rpg');?></span></h5>
<div class="framed">
    <div class="col-12 row align-items-center m-0">
        <div class="col-12 col-lg-6">
            <div class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_MIL_ATTACK;?>"
                title="<?php echo number_format($totalAttack);?>"
                data-html="true">
                <div class="icon"></div>
                <span>
                    <?php echo Stephino_Rpg_Config::get()->core()->getMilitaryAttackName(true);?>: <b><?php
                        echo Stephino_Rpg_Utils_Lingo::isuFormat($totalAttack);
                    ?></b>
                </span>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_MIL_DEFENSE;?>"
                title="<?php echo number_format($totalDefense);?>"
                data-html="true">
                <div class="icon"></div>
                <span>
                    <?php echo Stephino_Rpg_Config::get()->core()->getMilitaryDefenseName(true);?>: <b><?php
                        echo Stephino_Rpg_Utils_Lingo::isuFormat($totalDefense);
                    ?></b>
                </span>
            </div>
        </div>
    </div>
</div>