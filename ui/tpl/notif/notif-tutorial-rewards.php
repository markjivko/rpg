<?php
/**
 * Template:Dialog:Action:TutorialRewards
 * 
 * @title      Tutorial Rewards dialog
 * @desc       Template for the rewards received at a Tutorial Checkpoint
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

if (Stephino_Rpg_Db_Model_Messages::isValidNotifData($notifData, 1)):
    /* @var $tutorialConfigId int */
    list($tutorialConfigId) = $notifData;

    $tutorialConfig = Stephino_Rpg_Config::get()
        ->tutorials()
        ->getById($tutorialConfigId);

    $tutorialName = null !== $tutorialConfig
        ? $tutorialConfig->getName(true)
        : esc_html__('Unknown', 'stephino-rpg');

    $resourcesList = null !== $tutorialConfig
        ? array(
            Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD     => $tutorialConfig->getTutorialCheckPointRewardGold(),
            Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH => $tutorialConfig->getTutorialCheckPointRewardResearch(),
            Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM      => $tutorialConfig->getTutorialCheckPointRewardGem(),
            Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA   => $tutorialConfig->getTutorialCheckPointRewardAlpha(),
            Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_BETA    => $tutorialConfig->getTutorialCheckPointRewardBeta(),
            Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_GAMMA   => $tutorialConfig->getTutorialCheckPointRewardGamma(),
            Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1 => $tutorialConfig->getTutorialCheckPointRewardExtra1(),
            Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2 => $tutorialConfig->getTutorialCheckPointRewardExtra2(),
        )
        : array();
?>
    <div class="row justify-content-center">
        <div class="col-12">
            <?php echo esc_html__('Congratulations!', 'stephino-rpg');?>
            <?php 
                echo sprintf(
                    esc_html__('For reaching step %s of the tutorial, you have received', 'stephino-rpg'),
                    '<b>' . intval($tutorialConfigId) . '</b> (<i>' . $tutorialName . '</i>)'
                );
            ?>
        </div>
        <?php 
            foreach(Stephino_Rpg_Renderer_Ajax_Action::getResourceData($resourcesList) as $resourceKey => list($resName, $resValue, $resAjaxKey)):
                if ($resValue > 0):
        ?>
            <div class="col-6">
                <div class="res res-<?php echo $resAjaxKey;?>">
                    <div class="icon"></div>
                    <span>
                        <b><?php echo number_format($resValue);?></b>
                        <?php echo esc_html($resName);?>
                    </span>
                </div>
            </div>
        <?php endif; endforeach;?>
    </div>
<?php endif;?>