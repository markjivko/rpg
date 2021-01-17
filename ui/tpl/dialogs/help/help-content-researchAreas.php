<?php
/**
 * Template:Dialog:Help Content
 * 
 * @title      Help Content - ResearchArea
 * @desc       Template for the help content - loaded both directly and with AJAX inside [data-role="content"]
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $configObject Stephino_Rpg_Config_ResearchArea */
?>
<div class="row p-2 text-center justify-content-center">
    <div class="col-12">
        <h5>
            <?php echo Stephino_Rpg_Config::get()->core()->getConfigResearchAreasName(true);?> &#8250; <b><?php echo $configObject->getName(true);?></b>
        </h5>
    </div>
</div>
<?php 
    require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
        Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_DESCRIPTION
    );
?>
<div class="col-12 p-2">
    <h6 class="heading"><span><?php echo esc_html__('Game rules', 'stephino-rpg');?></span></h6>
    <ul>
        <?php if (null !== $configObject->getBuilding()):?>
            <li>
                <?php echo esc_html__('Available in', 'stephino-rpg');?> 
                <span
                    data-effect="helpMenuItem"
                    data-effect-args="<?php echo Stephino_Rpg_Config_Buildings::KEY;?>,<?php echo $configObject->getBuilding()->getId();?>">
                    <?php echo $configObject->getBuilding()->getName(true);?>
                </span>
            </li>
        <?php endif;?>
            <li>
                <?php 
                    echo sprintf(
                        esc_html__('%s: affect all bases regardless of where they were completed', 'stephino-rpg'),
                        '<b>' . Stephino_Rpg_Config::get()->core()->getConfigResearchFieldsName(true) . '</b>'
                    );
                ?>
            </li>
    </ul>
</div>
<?php 
    // Load the fragment
    require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
        Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_REQUIREMENTS
    );
?>