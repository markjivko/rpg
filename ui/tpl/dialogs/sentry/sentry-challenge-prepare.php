<?php
/**
 * Template:Dialog:Sentry
 * 
 * @title      Sentry dialog - Prepare
 * @desc       Template for challenge between two sentries
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $userData array */
/* @var $opponentData array */
/* @var $sentryChallenge string */
$sentryLevels           = Stephino_Rpg_Db::get()->modelSentries()->getLevels($userData);
$sentryLabels           = Stephino_Rpg_Db::get()->modelSentries()->getLabels();
$sentryLabelsAction     = Stephino_Rpg_Db::get()->modelSentries()->getLabels(true);
$sentryChallengeColumns = Stephino_Rpg_Db::get()->modelSentries()->getColumns();
$sentryChallengeIcons   = Stephino_Rpg_Db::get()->modelSentries()->getIcons();
$successRate = Stephino_Rpg_Db::get()->modelSentries()->getSuccessRate(
    (int) $userData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_LEVEL_ATTACK], 
    (int) $userData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_LEVEL_DEFENSE], 
    (int) $opponentData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_LEVEL_ATTACK], 
    (int) $opponentData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_LEVEL_DEFENSE]
);
$sentryYield = Stephino_Rpg_Db::get()->modelSentries()->getYield($successRate);
?>
<div class="col-12 framed p-4">
    <div class="row align-items-center">
        <div 
            data-effect="sentryVs" 
            data-effect-args="<?php 
                echo implode(',', array_map('intval', array(
                    $userData[Stephino_Rpg_Db_Table_Users::COL_ID],
                    $userData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_VERSION],
                    $opponentData[Stephino_Rpg_Db_Table_Users::COL_ID],
                    $opponentData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_VERSION],
                )));
            ?>"></div>
    </div>
    <div class="row align-items-center">
        <h5>
            <?php echo esc_html($userData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_NAME]);?> &bullet;
            <span
                data-click="userViewProfile"
                data-click-args="<?php echo (int) $userData[Stephino_Rpg_Db_Table_Users::COL_ID];?>">
                <b><?php echo Stephino_Rpg_Utils_Lingo::getUserName($userData);?></b>
            </span>
            vs.
            <?php echo esc_html($opponentData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_NAME]);?> &bullet;
            <span
                data-click="userViewProfile"
                data-click-args="<?php echo (int) $opponentData[Stephino_Rpg_Db_Table_Users::COL_ID];?>">
                <b><?php echo Stephino_Rpg_Utils_Lingo::getUserName($opponentData);?></b>
            </span>
        </h5>
        <div class="col-12 row no-gutters bg-dark mb-2 text-center">
            <?php 
                foreach ($sentryLabels as $challengeType => $challengeLabel):
                    $challengeColumn = $sentryChallengeColumns[$challengeType];
                    $challengeIcon   = $sentryChallengeIcons[$challengeType];
            ?>
                <div class="col-12 col-lg">
                    <div class="res res-<?php echo $challengeIcon;?>" title="<?php echo $userData[$challengeColumn];?> vs.<?php echo $opponentData[$challengeColumn];?>" data-html="true">
                        <div class="icon"></div>
                        <span><?php echo esc_html($challengeLabel);?>: <b><?php echo $userData[$challengeColumn];?></b> / <?php echo $opponentData[$challengeColumn];?></span>
                    </div>
                </div>
            <?php endforeach;?>
            <div class="col-12 text-center mb-2">
                <?php echo esc_html__('Chance of success', 'stephino-rpg');?>: <b><?php echo number_format($successRate, 2);?>%</b>,
                <span 
                    data-effect="help"
                    data-effect-args="<?php echo Stephino_Rpg_Config_Core::KEY;?>,<?php echo Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_SENTRIES;?>">
                    <?php echo esc_html__('Yield', 'stephino-rpg');?>: <b><?php echo number_format($sentryYield, 2);?>%</b>
                </span>
            </div>
        </div>
    </div>
</div>
<div class="row no-gutters framed p-4">
    <h4>
        <div class="res res-<?php echo $sentryChallengeIcons[$sentryChallenge];?>">
            <div class="icon"></div>
            <span><?php echo esc_html($sentryLabels[$sentryChallenge]);?> <?php echo esc_html__('level', 'stephino-rpg');?> <b><?php echo $sentryLevels[$sentryChallenge];?> &utrif;</b></span>
        </div>
    </h4>
    <?php 
        // Prepare the production/looting data
        $productionData = Stephino_Rpg_Renderer_Ajax_Action::getProductionDataSentry(
            isset($sentryLevels[Stephino_Rpg_Db_Model_Sentries::CHALLENGE_LOOTING])
                ? $sentryLevels[Stephino_Rpg_Db_Model_Sentries::CHALLENGE_LOOTING]
                : 0,
            $sentryLevels[$sentryChallenge],
            $successRate
        );

        // Prepare the production flag
        $productionReady = false;
        foreach ($productionData as $productionValue) {
            if ($productionValue[1] > 0) {
                $productionReady = true;
                break;
            }
        }

        // Makes sense to show 
        if ($productionReady):
    ?>
        <div class="col-12 col-xl bg-dark">
            <?php 
                // Prepare the production/looting title
                $productionTitle = $productionData[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM][1] > 0
                    ? (
                        isset($sentryLevels[Stephino_Rpg_Db_Model_Sentries::CHALLENGE_LOOTING]) 
                            ? (esc_html__('Maximum loot', 'stephino-rpg') . ' &amp; ' . esc_html__('Challenge reward', 'stephino-rpg'))
                            : esc_html__('Challenge reward', 'stephino-rpg')
                    )
                    : esc_html__('Maximum loot', 'stephino-rpg');
                $productionTitleHelp = false;
                $productionHourly = false;
                $productionHideZero = true;

                require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                    Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_PRODUCTION
                );
                ?>
        </div>
    <?php endif;?>
    <div class="col-12 col-xl">
        <?php 
            // Prepare the cost data
            $costData = Stephino_Rpg_Renderer_Ajax_Action::getCostDataSentry($sentryLevels[$sentryChallenge]);

            // Prepare the challenge duration
            $costTime = Stephino_Rpg_Utils_Config::getPolyValue(
                Stephino_Rpg_Config::get()->core()->getSentryCostTimePoly(),
                $sentryLevels[$sentryChallenge] + 1, 
                Stephino_Rpg_Config::get()->core()->getSentryCostTime()
            );

            // Prepare the cost title
            $costTitle = esc_html__('Cost of challenge', 'stephino-rpg');
            require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_COSTS
            );
        ?>
    </div>
    <?php if ($costAfforded && 0 === (int) $opponentData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_ACTIVE]): ?>
        <div class="col-12 mt-4">
            <button 
                class="btn btn-warning w-100"
                data-click="sentryChallengeStart" 
                data-click-args="<?php echo (int) $opponentData[Stephino_Rpg_Db_Table_Users::COL_ID];?>,<?php echo $sentryChallenge;?>">
                <span><?php echo esc_html__('Fight', 'stephino-rpg');?> (<?php echo esc_html($sentryLabelsAction[$sentryChallenge]);?>)</span>
            </button>
        </div>
    <?php endif;?>
</div>