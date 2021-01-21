<?php
/**
 * Template:Console:Help
 * 
 * @title      Console template - ListIslandCities
 * @desc       Template for the Console:ListIslandCities command
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();
?>
<ul>
    <?php foreach ($islandSlots as $slotIndex => $dbRow):?>
        <li>
            <?php if (null === $dbRow):?>
                Slot <b><?php echo $slotIndex;?>:</b> <i>vacant lot</i>
            <?php else:?>
                Slot <b><?php echo $slotIndex;?>:</b> <b><?php echo Stephino_Rpg_Utils_Lingo::getCityName($dbRow);?></b>
                <ul>
                    <li>City ID: <b><?php echo $dbRow[Stephino_Rpg_Db_Table_Cities::COL_ID];?></b></li>
                    <li>City Level: <b><?php echo $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL];?></b></li>
                    <li>User ID: <b><?php echo $dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_USER_ID];?></b></li>
                </ul>
            <?php endif;?>
        </li>
    <?php endforeach; ?>
</ul>