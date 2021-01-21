<?php
/**
 * Template:Dialog:City
 * 
 * @title      City dialog
 * @desc       Template for the city stages dialog
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var array $unlockStages */
/* @var array $buildingLevels */
/* @var array $researchFieldLevels */
?>
<div class="framed" data-role="stage">
    <div class="col-12 w-100">
        <div class="row">
            <?php 
                foreach ($unlockStages as $unlockStageKey => $unlockStage): 
                    foreach ($unlockStage as $unlockObject):
                        // Building requirements
                        $itemReqB = true;
                        if (null !== $unlockObject->getRequiredBuilding()) {
                            if (!isset($buildingLevels[$unlockObject->getRequiredBuilding()->getId()])
                                || $buildingLevels[$unlockObject->getRequiredBuilding()->getId()] < $unlockObject->getRequiredBuildingLevel()) {
                                $itemReqB = false;
                            }
                        }

                        // Research field requirements
                        $itemReqRF = true;
                        if (null !== $unlockObject->getRequiredResearchField()) {
                            if (!isset($researchFieldLevels[$unlockObject->getRequiredResearchField()->getId()])
                                || $researchFieldLevels[$unlockObject->getRequiredResearchField()->getId()] < $unlockObject->getRequiredResearchFieldLevel()) {
                                $itemReqRF = false;
                            }
                        }

                        // Prepare the current level (null for not available, -1 for not created, 0 for under construction)
                        $unlockCurrentLevel = null;

                        // Prepare the ajax key
                        $unlockKey = null;
                        switch (true) {
                            case $unlockObject instanceof Stephino_Rpg_Config_Building:
                                if (isset($buildingLevels[$unlockObject->getId()])) {
                                    $unlockCurrentLevel = $buildingLevels[$unlockObject->getId()];
                                } else {
                                    $unlockCurrentLevel = -1;
                                }
                                $unlockKey = Stephino_Rpg_Config_Buildings::KEY;
                                break;

                            case $unlockObject instanceof Stephino_Rpg_Config_ResearchField:
                                if (isset($researchFieldLevels[$unlockObject->getId()])) {
                                    $unlockCurrentLevel = $researchFieldLevels[$unlockObject->getId()];
                                } else {
                                    $unlockCurrentLevel = -1;
                                }
                                $unlockKey = Stephino_Rpg_Config_ResearchFields::KEY;
                                break;

                            case $unlockObject instanceof Stephino_Rpg_Config_ResearchArea:
                                $unlockKey = Stephino_Rpg_Config_ResearchAreas::KEY;
                                break;

                            case $unlockObject instanceof Stephino_Rpg_Config_Government:
                                $unlockKey = Stephino_Rpg_Config_Governments::KEY;
                                break;

                            case $unlockObject instanceof Stephino_Rpg_Config_Unit:
                                $unlockKey = Stephino_Rpg_Config_Units::KEY;
                                break;

                            case $unlockObject instanceof Stephino_Rpg_Config_Ship:
                                $unlockKey = Stephino_Rpg_Config_Ships::KEY;
                                break;
                        }
            ?>
                <div class="col-12 col-md-6 col-lg-3 col-xl-2 text-center">
                    <div 
                        class="building-entity-icon framed mt-4 <?php if (!$itemReqB || !$itemReqRF):?>disabled<?php endif;?> <?php if ($unlockCurrentLevel < 0):?>active<?php endif;?>" 
                        data-html="true"
                        title="<?php echo Stephino_Rpg_Utils_Lingo::escape($unlockObject->getName());?>"
                        data-click="helpDialog"
                        data-click-args="<?php echo $unlockKey;?>,<?php echo $unlockObject->getId();?>"
                        data-effect="background" 
                        data-effect-args="<?php echo $unlockKey;?>,<?php echo $unlockObject->getId();?>">
                        <?php if ($itemReqB && $itemReqRF && null !== $unlockCurrentLevel):?>
                            <div class="level">
                                <?php switch(true): case ($unlockCurrentLevel < 0): ?>
                                    <?php echo esc_html__('missing', 'stephino-rpg');?>
                                <?php break; case (0 == $unlockCurrentLevel):?>
                                    &#8230;
                                <?php break; default:?>
                                    <?php echo $unlockCurrentLevel;?>
                                <?php endswitch;?>
                            </div>
                        <?php endif;?>
                        <span class="label">
                            <span>
                                <?php echo Stephino_Rpg_Utils_Lingo::escape($unlockObject->getName());?>
                            </span>
                        </span>
                    </div>
                </div>
            <?php endforeach; endforeach;?>
        </div>
    </div>
</div>