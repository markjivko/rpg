<?php
/**
 * Template:Dialog:Entity
 * 
 * @title      Entity list dialog
 * @desc       Template for garrisoned entities
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $buildingLevels int[] */
/* @var $cityData array|null */
/* @var $cityEntities array|null */
$totalAttack = 0;
$totalDefense = 0;
?>
<?php if (is_array($cityEntities) && count($cityEntities)):?>
    <?php 
        foreach($cityEntities as list($entityRow, $entityConfig)):
            // Store the entity count
            $entityCount = (int) $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT];
        
            // Prepare the military capabiliies
            $entityAttackPoints = $entityConfig->getCivilian() ? 0 : $entityCount * (
                $entityConfig->getDamage() * $entityConfig->getAmmo()
            );
            $entityDefensePoints = $entityConfig->getCivilian() ? 0 : $entityCount * (
                $entityConfig->getArmour() * $entityConfig->getAgility()
            );
            
            // Add to the totals
            $totalAttack += $entityAttackPoints;
            $totalDefense += $entityDefensePoints;
            
            // Get the item card details
            list($itemCardFn, $itemCardArgs) = Stephino_Rpg_Utils_Config::getItemCardAttributes($entityConfig);
    ?>
    <div class="framed">
        <div class="row align-items-center">
            <div class="col-12 col-lg-3 text-center"
                data-role="entity" 
                data-entity-count="<?php echo $entityCount;?>"
                data-entity-type="<?php echo $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE];?>"
                data-entity-config="<?php echo $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID];?>" >
                <div 
                    class="item-card framed mt-4" 
                    data-click="<?php echo $itemCardFn;?>"
                    data-click-args="<?php echo $itemCardArgs;?>"
                    data-effect="background" 
                    data-effect-args="<?php echo $entityConfig->keyCollection();?>,<?php echo $entityConfig->getId();?>">
                    <span>
                        <?php echo $entityConfig->getName(true);?>
                    </span>
                    <span class="label" data-html="true" title="&times; <?php echo number_format($entityCount);?>">
                        <span>
                            &times; <b><?php echo Stephino_Rpg_Utils_Lingo::isuFormat($entityCount);?></b>
                        </span>
                    </span>
                </div>
            </div>
            <div class="col-12 col-lg-9 row no-gutters">
                <?php
                    $entityLayoutGarrison = true;
                    require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                        Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_ENTITY_MILITARY
                    );
                ?>
                <div class="col-12">
                    <?php if ($entityConfig->getDisbandable() && $entityCount > 0):?>
                        <button 
                            class="btn btn-default w-100" 
                            data-click="entityDialog" 
                            data-click-args="<?php echo $entityConfig->keyCollection();?>,<?php echo $entityConfig->getId();?>,<?php echo Stephino_Rpg_Renderer_Ajax_Dialog_Entity::QUEUE_ACTION_DISBAND;?>">
                            <span><?php echo esc_html__('Disband', 'stephino-rpg');?></span>
                        </button>
                    <?php endif;?>
                    <button 
                        class="btn btn-default w-100" 
                        data-click="entityDialog" 
                        data-click-args="<?php echo $entityConfig->keyCollection();?>,<?php echo $entityConfig->getId();?>,<?php echo Stephino_Rpg_Renderer_Ajax_Dialog_Entity::QUEUE_ACTION_RECRUIT;?>">
                        <?php if ($entityConfig instanceof Stephino_Rpg_Config_Unit):?>
                            <span><?php echo esc_html__('Recruit', 'stephino-rpg');?></span>
                        <?php else:?>
                            <span><?php echo esc_html__('Build', 'stephino-rpg');?></span>
                        <?php endif;?>
                    </button>
                </div>
            </div>
            <div class="col-12">
                <?php if ($entityCount > 0):
                    // Get the entity building configuration ID
                    $entityBuildingConfigId = null !== $entityConfig->getBuilding() 
                        ? $entityConfig->getBuilding()->getId()
                        : null;
                
                    // Get the entity building level
                    $entityBuildingLevel = isset($buildingLevels[$entityBuildingConfigId])
                        ? $buildingLevels[$entityBuildingConfigId]
                        : 0;
                    
                    // Get the entity production data
                    $productionData = Stephino_Rpg_Renderer_Ajax_Action::getProductionData(
                        $entityConfig,
                        $entityBuildingLevel,
                        $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID],
                        $entityCount
                    );

                    if (count($productionData)):
                        $productionTitle = false;
                ?>
                    <div class="col-12">
                        <?php require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_PRODUCTION);?>
                    </div>
                <?php endif;endif;?>
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
            
            // Get the item card details
            list($itemCardFn, $itemCardArgs) = Stephino_Rpg_Utils_Config::getItemCardAttributes($buildingConfig);
    ?>
        <div class="framed">
            <div class="row align-items-center">
                <div class="col-12 col-lg-3 text-center">
                    <div 
                        class="item-card framed mt-4" 
                        data-click="<?php echo $itemCardFn;?>"
                        data-click-args="<?php echo $itemCardArgs;?>"
                        data-effect="background" 
                        data-effect-args="<?php echo $buildingConfig->keyCollection();?>,<?php echo $buildingConfig->getId();?>">
                        <span>
                            <?php echo $buildingConfig->getName(true);?>
                        </span>
                        <span class="label">
                            <span>
                                <?php echo esc_html__('Level', 'stephino-rpg');?> <b><?php echo $buildingLevel;?></b>
                            </span>
                        </span>
                    </div>
                </div>
                <div class="col-12 col-lg-9 row no-gutters">
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
                    <div class="col-12">
                        <button 
                            class="btn btn-default w-100" 
                            data-click="buildingViewDialog" 
                            data-click-args="<?php echo $buildingConfig->getId();?>">
                            <span><?php 
                                echo sprintf(
                                    esc_html__('Visit %s', 'stephino-rpg'),
                                    Stephino_Rpg_Config::get()->core()->getConfigBuildingName(true)
                                );
                            ?></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif;?>
<h5><span><?php echo esc_html__('Total military capabilities', 'stephino-rpg');?></span></h5>
<div class="framed">
    <div class="row align-items-center">
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