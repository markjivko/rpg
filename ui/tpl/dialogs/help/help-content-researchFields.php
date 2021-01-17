<?php
/**
 * Template:Dialog:Help Content
 * 
 * @title      Help Content - ResearchField
 * @desc       Template for the help content - loaded both directly and with AJAX inside [data-role="content"]
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $configObject Stephino_Rpg_Config_ResearchField */
$costData = Stephino_Rpg_Renderer_Ajax_Action::getCostData($configObject);
$productionData = Stephino_Rpg_Renderer_Ajax_Action::getProductionData($configObject);

?>
<div class="row p-2 text-center justify-content-center">
    <div class="col-12">
        <h5>
            <?php echo Stephino_Rpg_Config::get()->core()->getConfigResearchFieldsName(true);?> &#8250; <b><?php echo $configObject->getName(true);?></b>
        </h5>
    </div>
    <?php 
        if (
            $configObject->getLevelsEnabled()
            && (count($costData) || count($productionData))
        ):
    ?>
    <div class="col-9">
        <div class="label research-field-level-number">
            <span>
                <?php echo esc_html__('Level', 'stephino-rpg');?>
                <input 
                    type="number"
                    autocomplete="off"
                    min="1" 
                    value="1" 
                    data-role="poly-level" 
                    title="<?php echo esc_attr__('Use this to preview costs, production etc.', 'stephino-rpg');?>" 
                    class="form-control" />
            </span>
        </div>
    </div>
    <?php endif;?>
</div>
<?php 
    require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
        Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_DESCRIPTION
    );
?>
<div class="col-12 p-2">
    <h6 class="heading"><span><?php echo esc_html__('Properties', 'stephino-rpg');?></span></h6>
    <ul>
        <?php if (null !== $configObject->getResearchArea() && null !== $configObject->getResearchArea()->getBuilding()):?>
            <li>
                <?php echo esc_html__('Available in', 'stephino-rpg');?> 
                <span
                    data-effect="helpMenuItem"
                    data-effect-args="<?php echo Stephino_Rpg_Config_Buildings::KEY;?>,<?php echo $configObject->getResearchArea()->getBuilding()->getId();?>">
                    <?php echo $configObject->getResearchArea()->getBuilding()->getName(true);?>
                </span>
            </li>
        <?php endif;?>
        <li>
            <?php 
                echo $configObject->getLevelsEnabled()
                    ? esc_html__('This can be upgraded multiple times', 'stephino-rpg')
                    : esc_html__('This can be upgraded only once', 'stephino-rpg');
            ?>
        </li>
    </ul>
</div>
<?php 
    require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
        Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_ABUNDANCE
    );
?>
<?php 
    require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
        Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_DISCOUNTS
    );
?>
<?php 
    require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
        Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_UNLOCKS
    );
?>
<?php 
    require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
        Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_REQUIREMENTS
    );
?>
<?php 
    require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
        Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_PRODUCTION
    );
?>
<?php 
    require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
        Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_COSTS
    );
?>