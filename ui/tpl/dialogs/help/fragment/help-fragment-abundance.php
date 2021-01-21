<?php
/**
 * Template:Dialog:Help
 * 
 * @title      Help Fragment - Resource Abundance
 * @desc       Resource Abundance
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $configObject Stephino_Rpg_Config_Trait_XResourceAbundance */
?>
<?php if ($configObject->getResourceExtra1Abundant() || $configObject->getResourceExtra2Abundant()):?>
    <div class="col-12 p-2">
        <h6 class="heading"><span><?php echo esc_html__('Resource abundance', 'stephino-rpg');?></span></h6>
        <?php if ($configObject->getResourceExtra1Abundant()):?>
            <div class="col-12">
                <span class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_EXTRA_1;?>">
                    <div class="icon"></div>
                    <span>
                        <b><?php echo Stephino_Rpg_Config::get()->core()->getResourceExtra1Name(true);?></b>
                        <?php 
                            echo sprintf(
                                esc_html__('abundance of %s for all bases', 'stephino-rpg'),
                                '<b class="text-muted">100</b>%'
                            );
                        ?>
                    </span>
                </span>
            </div>
        <?php endif;?>
        <?php if ($configObject->getResourceExtra2Abundant()):?>
            <div class="col-12">
                <span class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_EXTRA_2;?>">
                    <div class="icon"></div>
                    <span>
                        <b><?php echo Stephino_Rpg_Config::get()->core()->getResourceExtra2Name(true);?></b>
                        <?php 
                            echo sprintf(
                                esc_html__('abundance of %s for all bases', 'stephino-rpg'),
                                '<b class="text-muted">100</b>%'
                            );
                        ?>
                    </span>
                </span>
            </div>
        <?php endif;?>
    </div>
<?php endif;?>