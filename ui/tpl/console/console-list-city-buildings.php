<?php
/**
 * Template:Console:Help
 * 
 * @title      Console template - ListCityBuildings
 * @desc       Template for the Console:ListCityBuildings command
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();
?>
<b>Buildings:</b>
<?php if (is_array($buildingConfigs) && count($buildingConfigs)):?>
    <ul>
        <?php foreach ($buildingConfigs as /*@var $buildingConfig Stephino_Rpg_Config_Building*/ $buildingConfig):?>
            <li>
                <b><?php echo $buildingConfig->getId();?></b> (<i><?php echo $buildingConfig->getName(true);?></i>): 
                <?php if (isset($buildingList[$buildingConfig->getId()])):?>
                    <ul>
                        <li>
                            Level: <b><?php echo $buildingList[$buildingConfig->getId()][Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL];?></b>
                        </li>
                        <li>
                            Workers: <b><?php echo $buildingList[$buildingConfig->getId()][Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_WORKERS];?></b>
                        </li>
                    </ul>
                <?php else:?>
                    <i>not built</i>
                <?php endif;?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else:?>
    <i>not configured</i>
<?php endif;?>