<?php
/**
 * Template:Dialog:Common Requirements
 * 
 * @title      Common requirements template
 * @desc       Template for requirements
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/**
 * @var $requirements <ul>
 *     <li>
 *         Stephino_Rpg_Config_Building::KEY => [
 *         <ul>
 *             <li>(Stephino_Rpg_Config_Building|null) Required Building configuration object</li>
 *             <li>(int|null) Required Building Level</li>
 *             <li>(boolean) Requirement met</li>
 *         </ul>]
 *     </li>
 *     <li>
 *         Stephino_Rpg_Config_ResearchField::KEY => [
 *         <ul>
 *             <li>(Stephino_Rpg_Config_ResearchField|null) Research Field configuration object</li>
 *             <li>(int|null) Required Research Field Level</li>
 *             <li>(boolean) Requirement met</li>
 *         </ul>]
 *     </li>
 * </ul>
 */
?>
<?php 
    if (
        isset($requirements) 
        && (null !== $requirements[Stephino_Rpg_Config_Building::KEY][0] || null !== $requirements[Stephino_Rpg_Config_ResearchField::KEY][0])
        && (!$requirements[Stephino_Rpg_Config_Building::KEY][2] || !$requirements[Stephino_Rpg_Config_ResearchField::KEY][2])
    ): 
?>
    <div class="row">
        <div class="col-12">
            <h5><span><?php echo esc_html__('Requirements', 'stephino-rpg');?></span></h5>
        </div>
        <div class="col-12 mb-2 text-center">
            <?php if (null !== $requirements[Stephino_Rpg_Config_Building::KEY][0]):?>
                <span 
                    <?php if ($requirements[Stephino_Rpg_Config_Building::KEY][2]):?>
                        class="badge badge-primary"
                    <?php else:?>
                        class="badge badge-danger"
                    <?php endif;?>>
                    <?php if ($requirements[Stephino_Rpg_Config_Building::KEY][2]):?>
                        &#x2714;&#xFE0F;
                    <?php endif;?>
                    <span 
                        data-effect="help"
                        data-effect-args="<?php echo Stephino_Rpg_Config_Buildings::KEY;?>,<?php echo $requirements[Stephino_Rpg_Config_Building::KEY][0]->getId();?>">
                        <?php echo $requirements[Stephino_Rpg_Config_Building::KEY][0]->getName(true);?>
                    </span>
                    <?php if (null !== $requirements[Stephino_Rpg_Config_Building::KEY][1]):?>
                        <?php echo esc_html__('level', 'stephino-rpg');?> <b><?php echo $requirements[Stephino_Rpg_Config_Building::KEY][1];?></b>
                    <?php endif;?>
                </span>
            <?php endif;?>
            <?php if (null !== $requirements[Stephino_Rpg_Config_ResearchField::KEY][0]):?>
                <span 
                    <?php if ($requirements[Stephino_Rpg_Config_ResearchField::KEY][2]):?>
                        class="badge badge-primary"
                    <?php else:?>
                        class="badge badge-danger"
                    <?php endif;?>>
                    <?php if ($requirements[Stephino_Rpg_Config_ResearchField::KEY][2]):?>
                        &#x2714;&#xFE0F;
                    <?php endif;?>
                    <span 
                        data-effect="help"
                        data-effect-args="<?php echo Stephino_Rpg_Config_ResearchFields::KEY;?>,<?php echo $requirements[Stephino_Rpg_Config_ResearchField::KEY][0]->getId();?>">
                        <?php echo $requirements[Stephino_Rpg_Config_ResearchField::KEY][0]->getName(true);?>
                    </span>
                    <?php if (null !== $requirements[Stephino_Rpg_Config_ResearchField::KEY][1]):?>
                        <?php echo esc_html__('level', 'stephino-rpg');?> <b><?php echo $requirements[Stephino_Rpg_Config_ResearchField::KEY][1];?></b>
                    <?php endif;?>
                </span>
            <?php endif;?>
        </div>
    </div>
<?php endif; ?>