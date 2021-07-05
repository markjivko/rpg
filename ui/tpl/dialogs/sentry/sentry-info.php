<?php
/**
 * Template:Dialog:Sentry
 * 
 * @title      Sentry dialog - Info
 * @desc       Template for sentry information
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $userData array */
/* @var $opponentData array|null */
/* @var $sentryOwnerData array */
/* @var $sentryChallenge string|null */
/* @var $sentryConvoy array|null */
/* @var $sentryOwn boolean */
$sentryLevels           = Stephino_Rpg_Db::get()->modelSentries()->getLevels($sentryOwnerData);
$sentryLabels           = Stephino_Rpg_Db::get()->modelSentries()->getLabels();
$sentryLabelsAction     = Stephino_Rpg_Db::get()->modelSentries()->getLabels(true);
$sentryChallengeColumns = Stephino_Rpg_Db::get()->modelSentries()->getColumns();
$sentryChallengeIcons   = Stephino_Rpg_Db::get()->modelSentries()->getIcons();
?>
<div class="col-12 framed p-4">
    <div class="row align-items-center justify-content-end">
        <?php if ($sentryOwn):?>
            <button 
                class="btn btn-default" 
                data-click="dialog" 
                data-click-args="dialogSentryCustomize">
                <span><?php echo esc_html__('Customize', 'stephino-rpg');?></span>
            </button>
        <?php endif;?>
        <?php if (is_array($opponentData)):?>
            <div 
                data-effect="sentryVs" 
                data-effect-args="<?php 
                    echo implode(',', array_map('intval', array(
                        $userData[Stephino_Rpg_Db_Table_Users::COL_ID],
                        $userData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_VERSION],
                        $opponentData[Stephino_Rpg_Db_Table_Users::COL_ID],
                        $opponentData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_VERSION],
                        0
                    )));
                ?>"></div>
        <?php else:?>
            <div data-role="sentry-frame"
                data-effect="sentryBackground" 
                data-effect-args="<?php echo (int) $sentryOwnerData[Stephino_Rpg_Db_Table_Users::COL_ID];?>,<?php echo (int) $sentryOwnerData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_VERSION];?>"></div>
        <?php endif;?>
    </div>
    <div class="row align-items-center">
        <h5 class="d-flex justify-content-center">
            <?php if ($sentryOwn):?>
                <?php if (is_array($opponentData)):?>
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
                <?php else:?>
                    <div class="col-12 col-md-6">
                        <input 
                            type="text" 
                            autocomplete="off"
                            class="form-control text-center" 
                            data-change="sentryRename" 
                            data-effect="charCounter"
                            maxlength="<?php echo Stephino_Rpg_Db_Model_Sentries::MAX_LENGTH_NAME;?>"
                            value="<?php echo esc_attr($sentryOwnerData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_NAME]); ?>" />
                    </div>
                <?php endif;?>
            <?php else:?>
                <span 
                    data-effect="help"
                    data-effect-args="<?php echo Stephino_Rpg_Config_Core::KEY;?>,<?php echo Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_SENTRIES;?>">
                    <?php echo esc_html($sentryOwnerData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_NAME]);?>
                </span>
                &bullet; 
                <span
                    data-click="userViewProfile"
                    data-click-args="<?php echo (int) $sentryOwnerData[Stephino_Rpg_Db_Table_Users::COL_ID];?>">
                    <b><?php echo Stephino_Rpg_Utils_Lingo::getUserName($sentryOwnerData);?></b>
                </span>
            <?php endif;?>
        </h5>
        <div class="col-12 row no-gutters bg-dark mb-2 text-center">
            <?php 
                foreach ($sentryLabels as $challengeType => $challengeLabel):
                    $challengeColumn = $sentryChallengeColumns[$challengeType];
                    $challengeIcon   = $sentryChallengeIcons[$challengeType];
            ?>
                <div class="col-12 col-lg">
                    <div class="res res-<?php echo $challengeIcon;?>" title="<?php echo $sentryOwnerData[$challengeColumn];?>" data-html="true">
                        <div class="icon"></div>
                        <span><?php echo esc_html($challengeLabel);?>: <b><?php echo $sentryOwnerData[$challengeColumn];?></b></span>
                    </div>
                </div>
            <?php endforeach;?>
            <?php if (!$sentryOwn): $ourData = Stephino_Rpg_TimeLapse::get()->userData();?>
                <div class="col-12 text-center mb-2">
                    <?php echo esc_html__('Chance of success', 'stephino-rpg');?>: <b><?php 
                        echo number_format(
                            Stephino_Rpg_Db::get()->modelSentries()->getSuccessRate(
                                (int) $ourData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_LEVEL_ATTACK], 
                                (int) $ourData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_LEVEL_DEFENSE], 
                                (int) $sentryOwnerData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_LEVEL_ATTACK], 
                                (int) $sentryOwnerData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_LEVEL_DEFENSE]
                            ), 
                            2
                        );
                    ?>%</b>
                </div>
            <?php endif;?>
        </div>
    </div>
</div>
<div class="col-12 framed p-4">
    <div class="row align-items-center">
        <?php if ($sentryOwn):?>
            <?php if (null === $sentryConvoy):?>
                <?php foreach ($sentryLabels as $sentryChallengeKey => $sentryChallengeName): ?>
                    <div class="col">
                        <button 
                            class="btn btn-default w-100" 
                            data-click="dialog" 
                            data-click-args="dialogSentryChallengeList,<?php echo $sentryChallengeKey;?>">
                            <span><?php echo esc_html($sentryLabelsAction[$sentryChallengeKey]);?> &utrif;</span>
                        </button>
                    </div>
                <?php endforeach;?>
            <?php else: 
                $timeLeft = (
                    (int) $sentryConvoy[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_RETREAT_TIME] > 0 
                        ? (int) $sentryConvoy[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_RETREAT_TIME]
                        : (int) $sentryConvoy[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TIME]
                    ) - time();
                $timeTotal = intval($sentryConvoy[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TRAVEL_DURATION]);
                $convoyTitle = (int) $sentryConvoy[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_RETREAT_TIME] > 0 
                    ? __('Returning home', 'stephino-rpg')
                    : __('Attacking', 'stephino-rpg')
            ?>
                <h5 class="mb-4"><span><?php echo $convoyTitle;?></span></h5>
                <div class="col-12 col-md-10">
                    <div 
                        data-effect="countdownBar" 
                        data-effect-args="<?php echo ($timeLeft . ',' . $timeTotal);?>">
                    </div>
                </div>
                <div class="col-12 col-md-2 text-center">
                    <span 
                        data-effect="countdownTime" 
                        data-effect-args="<?php echo ($timeLeft . ',' . $timeTotal);?>">
                    </span>
                </div>
            <?php endif;?>
        <?php elseif (null === $sentryConvoy && 0 === (int) $userData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_ACTIVE]): ?>
            <?php if (null !== $sentryChallenge): ?>
                <button 
                    class="btn btn-default w-100"
                    data-click="sentryChallengePrepare" 
                    data-click-args="<?php echo (int) $sentryOwnerData[Stephino_Rpg_Db_Table_Users::COL_ID];?>,<?php echo $sentryChallenge;?>">
                    <span><?php echo esc_html__('Fight', 'stephino-rpg');?> (<?php echo esc_html($sentryLabelsAction[$sentryChallenge]);?>)</span>
                </button>
            <?php else:?>
                <?php foreach ($sentryLabels as $sentryChallengeKey => $sentryChallengeName): ?>
                    <div class="col">
                        <button 
                            class="btn btn-default w-100" 
                            data-click="sentryChallengePrepare" 
                            data-click-args="<?php echo (int) $sentryOwnerData[Stephino_Rpg_Db_Table_Users::COL_ID];?>,<?php echo $sentryChallengeKey;?>">
                            <span><?php echo esc_html__('Fight', 'stephino-rpg');?> (<?php echo esc_html($sentryChallengeName);?> &utrif;)</span>
                        </button>
                    </div>
                <?php endforeach;?>
            <?php endif;?>
        <?php else:?>
            <div class="col-12 text-center align-items-center">
                <?php echo esc_html__('Not back from mission yet', 'stephino-rpg');?>
                <span data-effect="countdownTime" data-effect-args="0,3"></span>
            </div>
        <?php endif;?>
    </div>
</div>