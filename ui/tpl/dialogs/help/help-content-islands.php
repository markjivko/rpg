<?php
/**
 * Template:Dialog:Help Content
 * 
 * @title      Help Content - Island
 * @desc       Template for the help content - loaded both directly and with AJAX inside [data-role="content"]
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $configObject Stephino_Rpg_Config_Island */
$citySlotsArray = @json_decode($configObject->getCitySlots(), true);
$citySlotsCount = is_array($citySlotsArray) ? count($citySlotsArray) : 0;
$costData = Stephino_Rpg_Renderer_Ajax_Action::getCostData($configObject);
$costTitle = esc_html__('Colonization costs', 'stephino-rpg');
?>
<div class="row p-2 text-center justify-content-center">
    <div class="col-12">
        <h5>
            <?php echo Stephino_Rpg_Config::get()->core()->getConfigIslandsName(true);?> &#8250; <b><?php echo $configObject->getName(true);?></b>
        </h5>
    </div>
    <?php if ($citySlotsCount && (count($costData) || $configObject->getCostTime() > 0)):?>
        <div class="col-9">
            <div class="label item-level-number">
                <span>
                    <?php echo esc_html__('Empire size', 'stephino-rpg');?>
                    <input 
                        type="number" 
                        autocomplete="off"
                        min="1" 
                        value="1" 
                        data-role="poly-level" 
                        title="<?php echo esc_attr__('Use this to preview colonization costs', 'stephino-rpg');?>" 
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
<div class="col-12">
    <h6 class="heading"><span><?php echo esc_html__('Geography', 'stephino-rpg');?></span></h6>
    <div class="col-12 p-2">
        <p>
            <?php 
                if ($citySlotsCount > 0) {
                    echo sprintf(
                        esc_html__('This can host a maximum of %s', 'stephino-rpg'),
                        '<b>' . $citySlotsCount . '</b> ' . Stephino_Rpg_Config::get()->core()->getConfigCitiesName(true)
                    );
                } else {
                    echo esc_html__('Cannot be colonized', 'stephino-rpg');
                }
            ?>
        </p>
    </div>
</div>
<?php 
    if ($citySlotsCount) {
        require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
            Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_COSTS
        );
    }
?>
<div class="col-12">
    <h6 class="heading"><span><?php echo esc_html__('Natural resources', 'stephino-rpg');?></span></h6>
    <div class="col-12">
        <span class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_EXTRA_1;?>">
            <div class="icon"></div>
            <span>
                <b><?php echo Stephino_Rpg_Config::get()->core()->getResourceExtra1Name(true);?></b>
                <?php echo esc_html__('abundance', 'stephino-rpg');?>: <b><?php echo $configObject->getResourceExtra1Abundance();?></b>%
            </span>
        </span>
    </div>
    <div class="col-12">
        <span class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_EXTRA_2;?>">
            <div class="icon"></div>
            <span>
                <b><?php echo Stephino_Rpg_Config::get()->core()->getResourceExtra2Name(true);?></b>
                <?php echo esc_html__('abundance', 'stephino-rpg');?>: <b><?php echo $configObject->getResourceExtra2Abundance();?></b>%
            </span>
        </span>
    </div>
</div>