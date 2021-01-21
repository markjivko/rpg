<?php
/**
 * Template:Console:Help
 * 
 * @title      Console template - ListUserCities
 * @desc       Template for the Console:ListUserCities command
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();
?>
<ul>
    <?php foreach ($listArray as $dbRow):?>
        <li>
            <b><?php echo Stephino_Rpg_Utils_Lingo::getCityName($dbRow);?></b>
            <ul>
                <li>City ID: <b><?php echo $dbRow[Stephino_Rpg_Db_Table_Cities::COL_ID];?></b></li>
                <li>City Level: <b><?php echo $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL];?></b></li>
                <li>Island ID: <b><?php echo $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID];?></b></li>
                <li>Island Slot: <b><?php echo $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_INDEX];?></b></li>
            </ul>
        </li>
    <?php endforeach; ?>
</ul>