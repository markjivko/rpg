<?php
/**
 * Template:Console:Help
 * 
 * @title      Console template - ListStages
 * @desc       Template for the Console:ListStages command
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $item Stephino_Rpg_Config_Trait_Requirement */
?>
<ul>
    <?php foreach ($unlockStages as $stageKey => $unlockStage):?>
        <li>
            <b>Stage <?php echo ($stageKey + 1);?></b>
            <ul>
                <?php foreach ($unlockStage as $item):?>
                    <li>
                        <?php echo $item->getName();?> <i>(<?php echo constant(get_class($item) . '::KEY');?> #<?php echo $item->getId();?>)</i>
                        <?php if (null !== $item->getRequiredBuilding() || null !== $item->getRequiredResearchField()):?>
                            <br/>Requires:
                            <ul>
                                <?php if (null !== $item->getRequiredBuilding()):?>
                                    <li>
                                        <?php echo $item->getRequiredBuilding()->getName();?> <i>(<?php echo Stephino_Rpg_Config_Building::KEY;?> #<?php echo $item->getRequiredBuilding()->getId();?>)</i>
                                        level <b><?php echo $item->getRequiredBuildingLevel();?></b>
                                    </li>
                                <?php endif;?>
                                <?php if (null !== $item->getRequiredResearchField()):?>
                                    <li>
                                        <?php echo $item->getRequiredResearchField()->getName();?> <i>(<?php echo Stephino_Rpg_Config_ResearchField::KEY;?> #<?php echo $item->getRequiredResearchField()->getId();?>)</i>
                                        <?php if ($item->getRequiredResearchField()->getLevelsEnabled()):?>
                                            level <b><?php echo $item->getRequiredResearchFieldLevel();?></b>
                                        <?php endif;?>
                                    </li>
                                <?php endif;?>
                            </ul>
                        <?php endif;?>
                    </li>
                <?php endforeach;?>
            </ul>
        </li>
    <?php endforeach; ?>
</ul>