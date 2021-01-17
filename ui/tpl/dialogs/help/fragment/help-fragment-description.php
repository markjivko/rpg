<?php
/**
 * Template:Dialog:Help
 * 
 * @title      Help Fragment - Description
 * @desc       Description
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();
?>
<div class="col-12 p-2">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-9">
            <?php switch ($itemType): 
                case Stephino_Rpg_Config_Core::KEY:
                case Stephino_Rpg_Config_PremiumModifiers::KEY: 
                    /* No icons */ 
                    break; 
                
                default:
            ?>
                <div 
                    class="help-icon" 
                    <?php if (Stephino_Rpg::get()->isPro() && is_file(Stephino_Rpg_Config::get()->themePath(true) . '/img/story/' . $itemType . '/' . $configObject->getId() . '/512.mp4')): ?>
                        data-effect="video" 
                    <?php else:?>
                        data-effect="background" 
                    <?php endif;?>
                    data-effect-args="<?php echo $itemType;?>,<?php echo $configObject->getId();?>">
                </div>
            <?php endswitch;?>
        </div>
        <div class="col-12">
            <div class="card card-body bg-dark mb-4">
                <?php if (strlen($configObject->getDescription())):?>
                    <?php echo Stephino_Rpg_Utils_Lingo::markdown($configObject->getDescription());?>
                <?php else:?>
                    <i class="w-100 text-center"><?php echo esc_html__('no details available', 'stephino-rpg');?></i>
                <?php endif;?>
            </div>
        </div>
    </div>
</div>