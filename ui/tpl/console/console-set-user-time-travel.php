<?php
/**
 * Template:Console:Help
 * 
 * @title      Console template - SetUserTimeTravel
 * @desc       Template for the Console:SetUserTimeTravel command
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();
$currentTime = time();
?>
<ul>
    <li>
        <b>Last Tick</b>: <i><?php echo number_format($currentTime - $userData[Stephino_Rpg_Db_Table_Users::COL_USER_LAST_TICK]);?></i> seconds ago
    </li>
    <?php if (!count($queuesMultiUpdate)):?>
        <li><b>No Queues</b></li>
    <?php else: 
        foreach ($queuesMultiUpdate as $queueId => $queueData):
            foreach ($queues as $queueRow) {
                if ($queueRow[Stephino_Rpg_Db_Table_Queues::COL_ID] == $queueId) {
                    break;
                }
            }
    ?>
        <li>
            <b>Queue #<?php echo $queueId;?></b>
            <ul>
                <li><i><?php echo number_format($queueData[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_TIME] - $currentTime); ?></i> seconds left</li>
                <li>Duration: <?php echo number_format($queueRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_DURATION]); ?> seconds</li>
                <li>City id: <?php echo $queueRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_CITY_ID]; ?></li>
                <li>Item type: <?php echo $queueRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_TYPE]; ?></li>
                <li>Item id: <?php echo $queueRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID]; ?></li>
                <li>Quantity: <?php echo $queueRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_QUANTITY]; ?></li>
            </ul>
        </li>
    <?php endforeach; endif; ?>
    <?php if (!count($convoysMultiUpdate)):?>
        <li><b>No Convoys</b></li>
    <?php else: 
        foreach ($convoysMultiUpdate as $convoyId => $convoyData):
            foreach ($convoys as $convoyRow) {
                if ($convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_ID] == $convoyId) {
                    break;
                }
            }
    ?>
        <li>
            <b>Convoy #<?php echo $convoyId;?></b>
            <ul>
                <li>
                    Advance: <i><?php echo isset($convoyData[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TIME]) 
                        ? (number_format($convoyData[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TIME] - $currentTime) . ' seconds left')
                        : '---'; 
                    ?></i>
                </li>
                <li>
                    Return: <i><?php echo isset($convoyData[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_RETREAT_TIME]) 
                        ? (number_format($convoyData[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_RETREAT_TIME] - $currentTime) . ' seconds left')
                        : '---'; 
                    ?></i>
                </li>
                <li>Duration: <?php echo number_format($convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TRAVEL_DURATION]);?> seconds</li>
                <li>Type: <?php echo $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TYPE];?></li>
                <li>
                    From: user id <?php echo $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_USER_ID];?>, 
                    island id <?php echo $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_ISLAND_ID];?>,
                    city id <?php echo $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_CITY_ID];?>
                </li>
                <li>
                    To: user id <?php echo $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_USER_ID];?>, 
                    island id <?php echo $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_ISLAND_ID];?>,
                    city id <?php echo $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID];?>
                </li>
            </ul>
        </li>
    <?php endforeach; endif; ?>
</ul>