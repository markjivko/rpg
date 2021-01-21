<?php
/**
 * Template:Console:Help
 * 
 * @title      Console template - ListCityEntities
 * @desc       Template for the Console:ListCityEntities command
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();
?>
<ul>
    <li>
        <b>Units: </b>
        <?php if (is_array($unitConfigs) && count($unitConfigs)):?>
            <ul>
                <?php foreach ($unitConfigs as /*@var $unitConfig Stephino_Rpg_Config_Unit*/ $unitConfig):?>
                    <li>
                        <b><?php echo $unitConfig->getId();?></b> (<i><?php echo $unitConfig->getName(true);?></i>): 
                        <?php if (isset($unitList[$unitConfig->getId()])):?>
                            <b><?php echo $unitList[$unitConfig->getId()][Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT];?></b>
                        <?php else:?>
                            <i>not recruited</i>
                        <?php endif;?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else:?>
            <i>not configured</i>
        <?php endif;?>
    </li>
    <li>
        <b>Ships: </b>
        <?php if (is_array($shipConfigs) && count($shipConfigs)):?>
            <ul>
                <?php foreach ($shipConfigs as /*@var $shipConfig Stephino_Rpg_Config_Unit*/ $shipConfig):?>
                    <li>
                        <b><?php echo $shipConfig->getId();?></b> (<i><?php echo $shipConfig->getName(true);?></i>): 
                        <?php if (isset($shipList[$shipConfig->getId()])):?>
                            <b><?php echo $shipList[$shipConfig->getId()][Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT];?></b>
                        <?php else:?>
                            <i>not built</i>
                        <?php endif;?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else:?>
            <i>not configured</i>
        <?php endif;?>
    </li>
</ul>