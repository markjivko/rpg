<?php
/**
 * Template:Dialog:Action:TutorialRewards
 * 
 * @title      Tutorial Rewards dialog
 * @desc       Template for the rewards received at a Tutorial Checkpoint
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $tutorialObject Stephino_Rpg_Config_Tutorial */
list($tutorialObject, $resourcesList) = $notifData;

/* @var $resourcesList Resources list */
if (isset($resourcesList) && is_array($resourcesList)):
?>
    <div class="row text-center">
        <div class="col-12 p-2">
            <h5><?php echo esc_html__('Congratulations!', 'stephino-rpg');?></h5>
        </div>
    </div>
    <div class="row justify-content-center text-center">
        <div class="col-12">
            <?php 
                echo sprintf(
                    esc_html__('For reaching step %s of the tutorial, you have received', 'stephino-rpg'),
                    '<b>' . $tutorialObject->getId() . '</b> (<i>' . $tutorialObject->getName() . '</i>)'
                );
            ?>
        </div>
        <?php 
            foreach(Stephino_Rpg_Renderer_Ajax_Action::getResourceData($resourcesList) as $resourceKey => list($resName, $resValue, $resAjaxKey)):
                if ($resValue > 0):
        ?>
            <div class="col-6 col-lg-4">
                <div class="res res-<?php echo $resAjaxKey;?>">
                    <div class="icon"></div>
                    <span>
                        <b><?php echo number_format($resValue);?></b>
                        <?php echo $resName;?>
                    </span>
                </div>
            </div>
        <?php endif; endforeach;?>
    </div>
<?php endif;?>