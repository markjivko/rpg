<?php
/**
 * Template:Dialog:Building
 * 
 * @title      Building-Entities dialog
 * @desc       Template for the building entities
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $entitiesData array */
/* @var $entityConfig Stephino_Rpg_Config_Unit|Stephino_Rpg_Config_Ship */

// Prepare the max values
$maxArmour = 0;
$maxAgility = 0;
$maxDamage = 0;
$maxAmmo = 0;
$maxLootBox = 0;
foreach ($entitiesData as list($entityConfig)) {
    $entityConfig->getArmour() > $maxArmour && $maxArmour = $entityConfig->getArmour();
    $entityConfig->getAgility() > $maxAgility && $maxAgility = $entityConfig->getAgility();
    $entityConfig->getDamage() > $maxDamage && $maxDamage = $entityConfig->getDamage();
    $entityConfig->getAmmo() > $maxAmmo && $maxAmmo = $entityConfig->getAmmo();
    $entityConfig->getLootBox() > $maxLootBox && $maxLootBox = $entityConfig->getLootBox();
}
?>
<?php 
    foreach ($entitiesData as list($entityConfig, $entityDbRow, $entityQueueData)):
        // Prepare the entity key
        $entityKey = $entityConfig instanceof Stephino_Rpg_Config_Unit
            ? Stephino_Rpg_Config_Units::KEY
            : Stephino_Rpg_Config_Ships::KEY;

        // Get the entity count
        $entityCount = (null === $entityDbRow ? 0 : $entityDbRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT]);

        // Get the Research Area requirements
        list($requirements, $requirementsMet) = Stephino_Rpg_Renderer_Ajax_Action::getRequirements(
            $entityConfig, 
            $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CITY_ID]
        );
?>
    <div class="framed p-2">
        <div class="row no-gutters">
            <div class="col-12 p-2">
                <div class="col-12">
                    <h5>
                        <span>
                            <span 
                                data-effect="help"
                                data-effect-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>">
                                <?php echo $entityConfig->getName(true);?>
                            </span>
                        </span>
                    </h5>
                </div>
                <div class="row col-12 m-0 align-items-center">
                    <div class="col-12 col-lg-3 text-center">
                        <div 
                            class="building-entity-icon framed mt-4 <?php if (!$requirementsMet):?>disabled<?php endif;?>" 
                            data-click="helpDialog"
                            data-click-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>"
                            data-effect="background" 
                            data-effect-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>">
                            <span 
                                data-effect="help"
                                data-effect-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>">
                                <?php echo $entityConfig->getName(true);?>
                            </span>
                            <span class="label" data-html="true" title="&times; <?php echo number_format($entityCount);?>">
                                <span>
                                    &times; <b><?php echo Stephino_Rpg_Utils_Lingo::isuFormat($entityCount);?></b>
                                </span>
                            </span>
                        </div>
                        <?php if (null !== $entityQueueData):?>
                            <div class="col-12 text-center" data-html="true" title="&plus; <?php echo number_format($entityQueueData[Stephino_Rpg_Renderer_Ajax_Action_Entity::DATA_QUEUE_QUANTITY]);?>">
                                &plus;<b><?php echo Stephino_Rpg_Utils_Lingo::isuFormat($entityQueueData[Stephino_Rpg_Renderer_Ajax_Action_Entity::DATA_QUEUE_QUANTITY]);?></b> <?php echo esc_html__('in', 'stephino-rpg');?>
                                <span 
                                    data-effect="countdownTime" 
                                    data-effect-args="<?php echo ($entityQueueData[Stephino_Rpg_Renderer_Ajax_Action_Entity::DATA_QUEUE_TIME_LEFT] . ',' 
                                        . $entityQueueData[Stephino_Rpg_Renderer_Ajax_Action_Entity::DATA_QUEUE_TIME_TOTAL]);?>">
                                </span>
                                <span 
                                    class="w-100 text-center"
                                    data-click="entityDialog" 
                                    data-click-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>,<?php echo Stephino_Rpg_Renderer_Ajax_Dialog_Entity::QUEUE_ACTION_DEQUEUE;?>">
                                    <?php echo esc_html__('Dequeue', 'stephino-rpg');?>
                                </span>
                            </div>
                        <?php endif;?>
                    </div>
                    <div class="col-12 col-lg-9">
                        <?php if ($requirementsMet):?>
                            <?php if ($entityConfig->getCivilian()):?>
                                <ul class="mt-4">
                                    <li>
                                        <b><?php echo esc_html__('Civilian', 'stephino-rpg');?></b> - <?php echo esc_html__('cannot be sent to battle', 'stephino-rpg');?>
                                    </li>
                                    <?php if ($entityConfig instanceof Stephino_Rpg_Config_Unit):?>
                                        <?php if ($entityConfig->getAbilitySpy()):?>
                                            <li>
                                                <?php echo esc_html__('Spy success probability', 'stephino-rpg');?>: 
                                                <b><?php 
                                                    echo number_format(
                                                        Stephino_Rpg_Utils_Config::getPolyValue(
                                                            $entityConfig->getSpySuccessRatePolynomial(), 
                                                            null === $buildingData 
                                                                ? 1
                                                                : $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL], 
                                                            $entityConfig->getSpySuccessRate()
                                                        ),
                                                        2
                                                    );
                                                    ?></b>%
                                            </li>
                                        <?php endif;?>
                                    <?php else:?>
                                        <?php if ($entityConfig->getAbilityTransport()):?>
                                            <li data-html="true" title="<b><?php echo number_format($entityConfig->getAbilityTransportCapacity());?></b>">
                                                <?php echo esc_html__('Transporter capacity', 'stephino-rpg');?>: 
                                                <b><?php echo Stephino_Rpg_Utils_Lingo::isuFormat($entityConfig->getAbilityTransportCapacity());?></b>
                                            </li>
                                        <?php endif;?>
                                    <?php endif;?>
                                    <?php if ($entityConfig->getAbilityColonize()):?>
                                        <li>
                                            <b><?php echo esc_html__('Colonizer', 'stephino-rpg');?></b> - <?php echo esc_html__('can be used to expand your empire', 'stephino-rpg');?>
                                        </li>
                                    <?php endif;?>
                                    <?php if ($entityConfig instanceof Stephino_Rpg_Config_Unit || !$entityConfig->getAbilityTransport()):?>
                                        <li data-html="true" title="<b><?php echo number_format($entityConfig->getTransportMass());?></b> ">
                                            <b><?php echo esc_html__('Mass', 'stephino-rpg');?></b>: <b><?php echo Stephino_Rpg_Utils_Lingo::isuFormat($entityConfig->getTransportMass());?></b> 
                                        </li>
                                    <?php endif;?>
                                </ul>
                            <?php else:?>
                                <ul class="mt-4">
                                    <li>
                                        <b><?php echo esc_html__('Damage', 'stephino-rpg');?></b>: <b><?php echo $entityConfig->getDamage();?></b> / <?php echo $maxDamage;?>
                                        <div 
                                            data-effect="staticBar" 
                                            data-effect-args="<?php echo $entityConfig->getDamage();?>,<?php echo $maxDamage;?>">
                                        </div>
                                    </li>
                                    <li>
                                        <b><?php echo esc_html__('Ammo', 'stephino-rpg');?></b>: <b><?php echo $entityConfig->getAmmo();?></b> / <?php echo $maxAmmo;?>
                                        <div 
                                            data-effect="staticBar" 
                                            data-effect-args="<?php echo $entityConfig->getAmmo();?>,<?php echo $maxAmmo;?>">
                                        </div>
                                    </li>
                                    <li>
                                        <b><?php echo esc_html__('Armour', 'stephino-rpg');?></b>: <b><?php echo $entityConfig->getArmour();?></b> / <?php echo $maxArmour;?>
                                        <div 
                                            data-effect="staticBar" 
                                            data-effect-args="<?php echo $entityConfig->getArmour();?>,<?php echo $maxArmour;?>">
                                        </div>
                                    </li>
                                    <li>
                                        <b><?php echo esc_html__('Agility', 'stephino-rpg');?></b>: <b><?php echo $entityConfig->getAgility();?></b> / <?php echo $maxAgility;?>
                                        <div 
                                            data-effect="staticBar" 
                                            data-effect-args="<?php echo $entityConfig->getAgility();?>,<?php echo $maxAgility;?>">
                                        </div>
                                    </li>
                                    <li data-html="true" title="<b><?php echo number_format($entityConfig->getLootBox());?></b> / <b><?php echo number_format($maxLootBox);?></b>">
                                        <b><?php echo esc_html__('Loot Box', 'stephino-rpg');?></b>: 
                                        <?php if ($entityConfig->getLootBox()):?>
                                            <b><?php echo Stephino_Rpg_Utils_Lingo::isuFormat($entityConfig->getLootBox());?></b> / <?php echo Stephino_Rpg_Utils_Lingo::isuFormat($maxLootBox);?>
                                            <div 
                                                data-effect="staticBar" 
                                                data-effect-args="<?php echo $entityConfig->getLootBox();?>,<?php echo $maxLootBox;?>">
                                            </div>
                                        <?php else:?>
                                            <i><?php echo esc_html__('none', 'stephino-rpg');?></i>
                                        <?php endif;?>
                                    </li>
                                    <?php if (Stephino_Rpg_Config::get()->core()->getTravelTime() > 0 && $entityConfig instanceof Stephino_Rpg_Config_Ship):?>
                                        <li data-html="true" title="<b><?php echo number_format($entityConfig->getTroopCapacity());?></b> ">
                                            <b><?php echo esc_html__('Troops capacity', 'stephino-rpg');?></b>: <b><?php echo Stephino_Rpg_Utils_Lingo::isuFormat($entityConfig->getTroopCapacity());?></b> 
                                        </li>
                                    <?php endif;?>
                                    <li data-html="true" title="<b><?php echo number_format($entityConfig->getTransportMass());?></b> ">
                                        <b><?php echo esc_html__('Transported mass', 'stephino-rpg');?></b>: <b><?php echo Stephino_Rpg_Utils_Lingo::isuFormat($entityConfig->getTransportMass());?></b> 
                                    </li>
                                </ul>
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <div class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_MIL_ATTACK;?>">
                                            <div class="icon"></div>
                                            <span data-html="true" title="<?php echo esc_attr__('Damage', 'stephino-rpg');?> &times; <?php echo esc_attr__('Ammo', 'stephino-rpg');?>">
                                                <b><?php echo Stephino_Rpg_Config::get()->core()->getMilitaryAttackName(true);?></b>: <b><?php
                                                    echo number_format($entityConfig->getDamage() * $entityConfig->getAmmo());
                                                ?></b>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_MIL_DEFENSE;?>">
                                            <div class="icon"></div>
                                            <span data-html="true" title="<?php echo esc_attr__('Armour', 'stephino-rpg');?> &times; <?php echo esc_attr__('Agility', 'stephino-rpg');?>">
                                                <b><?php echo Stephino_Rpg_Config::get()->core()->getMilitaryDefenseName(true);?></b>: <b><?php
                                                    echo number_format($entityConfig->getArmour() * $entityConfig->getAgility());
                                                ?></b>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endif;?>
                        <?php 
                            else: 
                                require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                                    Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_REQUIREMENTS
                                );
                            endif;
                        ?>
                    </div>
                </div>
                <?php if ($requirementsMet && $entityCount > 0):
                    // Get the entity production data
                    $productionData = Stephino_Rpg_Renderer_Ajax_Action::getProductionData(
                        $entityConfig,
                        null === $buildingData ? 0 : $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL],
                        $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_ISLAND_ID],
                        $entityCount
                    );

                    if (count($productionData)):
                        $productionTitle = esc_html__('Garrison effect', 'stephino-rpg');
                ?>
                    <div class="col-12 mt-4">
                        <?php require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_PRODUCTION);?>
                    </div>
                <?php endif;endif;?>
                
                <div class="row col-12 m-0">
                    <?php if ($entityConfig->getDisbandable() && $entityCount > 0):?>
                        <div class="col-12 <?php if ($requirementsMet):?>col-lg-6<?php endif;?>">
                            <button 
                                class="btn btn-default w-100" 
                                data-click="entityDialog" 
                                data-click-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>,<?php echo Stephino_Rpg_Renderer_Ajax_Dialog_Entity::QUEUE_ACTION_DISBAND;?>">
                                <span><?php echo esc_html__('Disband', 'stephino-rpg');?></span>
                            </button>
                        </div>
                    <?php endif;?>
                    <?php if ($requirementsMet):?>
                        <div 
                            class="col-12 <?php if ($entityConfig->getDisbandable() && $entityCount > 0):?>col-lg-6<?php endif;?>">
                            <button 
                                class="btn btn-default w-100" 
                                data-click="entityDialog" 
                                data-click-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>,<?php echo Stephino_Rpg_Renderer_Ajax_Dialog_Entity::QUEUE_ACTION_RECRUIT;?>">
                                <?php if ($entityConfig instanceof Stephino_Rpg_Config_Unit):?>
                                    <span><?php echo esc_html__('Recruit', 'stephino-rpg');?></span>
                                <?php else:?>
                                    <span><?php echo esc_html__('Build', 'stephino-rpg');?></span>
                                <?php endif;?>
                            </button>
                        </div>
                    <?php endif;?>
                </div>
            </div>
        </div>
    </div>
<?php endforeach;?>