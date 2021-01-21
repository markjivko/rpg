<?php
/**
 * Template:Console:Help
 * 
 * @title      Console template - ListUserConvoys
 * @desc       Template for the Console:ListUserConvoys command
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
            <b>Convoy #<?php echo $dbRow[Stephino_Rpg_Db_Table_Convoys::COL_ID];?></b>
            <ul>
                <li>
                    <b>Type</b>: <?php echo $dbRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TYPE];?>
                </li>
                <li>
                    <b>Timing</b>
                    <ul>
                        <li>Arrival time: <b><?php echo date('Y-m-d H:i:s', $dbRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TIME]);?></b></li>
                        <?php if ($dbRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_RETREAT_TIME] > 0):?>
                            <li>Retreat time: <b><?php echo date('Y-m-d H:i:s', $dbRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_RETREAT_TIME]);?></b></li>
                        <?php endif;?>
                        <li>Travel duration: <b><?php echo $dbRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TRAVEL_DURATION];?>s</b></li>
                        <li>Travel speed: <b><?php echo ($dbRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TRAVEL_FAST] ? 'fast' : 'slow');?></b></li>
                    </ul>
                </li>
                <li>
                    <b>Origin</b>
                    <ul>
                        <li>User ID: <b><?php echo $dbRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_USER_ID];?></b></li>
                        <li>Island ID: <b><?php echo $dbRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_ISLAND_ID];?></b></li>
                        <li>City ID: <b><?php echo $dbRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_CITY_ID];?></b></li>
                    </ul>
                </li>
                <li>
                    <b>Destination</b>
                    <ul>
                        <li>User ID: <b><?php echo $dbRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_USER_ID];?></b></li>
                        <li>Island ID: <b><?php echo $dbRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_ISLAND_ID];?></b></li>
                        <li>City ID: <b><?php echo $dbRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID];?></b></li>
                    </ul>
                </li>
                <li>
                    <b>Payload</b>
                    <ul>
                        <pre><?php echo $dbRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_PAYLOAD];?></pre>
                    </ul>
                </li>
            </ul>
        </li>
    <?php endforeach; ?>
</ul>