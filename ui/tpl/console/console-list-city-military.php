<?php
/**
 * Template:Console:Help
 * 
 * @title      Console template - ListCityMilitary
 * @desc       Template for the Console:ListCityMilitary command
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $militaryBuildings array */
/* @var $militaryBuildingsTotal array */
/* @var $militaryEntities array */
/* @var $militaryEntitiesTotal array */
?>
<b><?php echo Stephino_Rpg_Utils_Lingo::getCityName($cityData);?></b> military<br/>
<ul>
    <li>
        <b>City level <?php echo $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL];?>:</b>
        <?php if (count($militaryBuildings)):?>
            <b><?php echo number_format($militaryBuildingsTotal[Stephino_Rpg_Renderer_Ajax::RESULT_MIL_ATTACK]);?></b> attack,
            <b><?php echo number_format($militaryBuildingsTotal[Stephino_Rpg_Renderer_Ajax::RESULT_MIL_DEFENSE]);?></b> defense 
            <ul>
                <?php foreach ($militaryBuildings as $militaryBuilding):?>
                    <li>
                        <b><?php echo $militaryBuilding[Stephino_Rpg_Renderer_Ajax::RESULT_DATA]->getName(true);?></b> 
                        level <b><?php echo $militaryBuilding[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL];?></b>
                        (
                            <b><?php echo number_format($militaryBuilding[Stephino_Rpg_Renderer_Ajax::RESULT_MIL_ATTACK]);?></b> attack,
                            <b><?php echo number_format($militaryBuilding[Stephino_Rpg_Renderer_Ajax::RESULT_MIL_DEFENSE]);?></b> defense
                        )
                    </li>
                <?php endforeach;?>
            </ul>
        <?php else: ?>
            No military buildings in this city
        <?php endif; ?>
    </li>
    <li>
        <b>Garrison:</b> 
        <?php if (count($militaryEntities)):?>
            <b><?php echo number_format($militaryEntitiesTotal[Stephino_Rpg_Renderer_Ajax::RESULT_MIL_ATTACK]);?></b> attack,
            <b><?php echo number_format($militaryEntitiesTotal[Stephino_Rpg_Renderer_Ajax::RESULT_MIL_DEFENSE]);?></b> defense
            <ul>
                <?php foreach ($militaryEntities as $militaryEntity):?>
                    <li>
                        <b><?php echo $militaryEntity[Stephino_Rpg_Renderer_Ajax::RESULT_DATA]->getName(true);?></b>
                        &times; <b><?php echo $militaryEntity[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT];?></b>
                        (
                            <b><?php echo number_format($militaryEntity[Stephino_Rpg_Renderer_Ajax::RESULT_MIL_ATTACK]);?></b> attack,
                            <b><?php echo number_format($militaryEntity[Stephino_Rpg_Renderer_Ajax::RESULT_MIL_DEFENSE]);?></b> defense
                        )
                    </li>
                <?php endforeach;?>
            </ul>
        <?php else: ?>
            No entities garrisoned in this city
        <?php endif; ?>
    </li>
</ul>
<br/><b>TOTAL:</b>
<b><?php echo number_format(
    $militaryBuildingsTotal[Stephino_Rpg_Renderer_Ajax::RESULT_MIL_ATTACK] + 
    $militaryEntitiesTotal[Stephino_Rpg_Renderer_Ajax::RESULT_MIL_ATTACK]
);?></b> attack,
<b><?php echo number_format(
    $militaryBuildingsTotal[Stephino_Rpg_Renderer_Ajax::RESULT_MIL_DEFENSE] +
    $militaryEntitiesTotal[Stephino_Rpg_Renderer_Ajax::RESULT_MIL_DEFENSE]
);?></b> defense