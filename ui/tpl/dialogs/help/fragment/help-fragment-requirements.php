<?php
/**
 * Template:Dialog:Help
 * 
 * @title      Help Fragment - Requirements
 * @desc       Requirements
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $configObject Stephino_Rpg_Config_Trait_Requirement */
?>
<?php if (null !== $configObject->getRequiredBuilding() || null !== $configObject->getRequiredResearchField()): ?>
    <div class="col-12">
        <h6 class="heading"><span><?php echo esc_html__('Requirements', 'stephino-rpg');?></span></h6>
        <?php if (null !== $configObject->getRequiredBuilding()):?>
            <span class="badge badge-primary">
                <span 
                    data-effect="helpMenuItem"
                    data-effect-args="<?php echo Stephino_Rpg_Config_Buildings::KEY;?>,<?php echo $configObject->getRequiredBuilding()->getId();?>">
                    <?php echo $configObject->getRequiredBuilding()->getName(true);?>
                </span>
                <?php echo esc_html__('level', 'stephino-rpg');?> <b><?php echo $configObject->getRequiredBuildingLevel();?></b>
            </span>
        <?php endif;?>
        <?php if (null !== $configObject->getRequiredResearchField()):?>
            <span class="badge badge-primary">
                <span 
                    data-effect="helpMenuItem"
                    data-effect-args="<?php echo Stephino_Rpg_Config_ResearchFields::KEY;?>,<?php echo $configObject->getRequiredResearchField()->getId();?>">
                    <?php echo $configObject->getRequiredResearchField()->getName(true);?>
                </span>
                <?php if ($configObject->getRequiredResearchField()->getLevelsEnabled()):?>
                level <b><?php echo $configObject->getRequiredResearchFieldLevel();?></b>
                <?php endif;?>
            </span>
        <?php endif;?>
    </div>
<?php endif; ?>