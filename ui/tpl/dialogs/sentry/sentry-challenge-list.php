<?php
/**
 * Template:Dialog:Sentry
 * 
 * @title      Sentry dialog - Challenge list
 * @desc       Template for challenges list
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $userData array */
/* @var $sentryChallenge string */
/* @var $sentryChallengeData array */
$sentryLabels           = Stephino_Rpg_Db::get()->modelSentries()->getLabels();
$sentryLabelsAction     = Stephino_Rpg_Db::get()->modelSentries()->getLabels(true);
$sentryChallengeColumns = Stephino_Rpg_Db::get()->modelSentries()->getColumns();
$sentryChallengeIcons   = Stephino_Rpg_Db::get()->modelSentries()->getIcons();
?>
<div data-sentry-challenge-type="<?php echo $sentryChallenge;?>">
    <?php if (count($sentryChallengeData)):?>
        <div class="content align-items-center">
            <?php foreach ($sentryChallengeData as $opponentData):?>
                <div class="row no-gutters framed align-items-center text-center">
                    <div class="col-12 col-lg-3">
                        <div 
                            class="item-card framed mt-4" 
                            data-click="dialog"
                            data-click-args="dialogSentryInfo,<?php echo (int) $opponentData[Stephino_Rpg_Db_Table_Users::COL_ID];?>,<?php echo $sentryChallenge;?>"
                            data-effect="sentryBackground" 
                            data-effect-args="<?php echo (int) $opponentData[Stephino_Rpg_Db_Table_Users::COL_ID];?>,<?php echo (int) $opponentData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_VERSION];?>">
                            <span><?php echo Stephino_Rpg_Utils_Lingo::getUserName($opponentData);?></span>
                            <span class="label" data-html="true" title="<?php echo esc_attr($opponentData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_NAME]);?>">
                                <span>
                                    <?php echo esc_html($opponentData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_NAME]);?>
                                </span>
                            </span>
                        </div>
                    </div>
                    <div class="col-12 col-lg-9">
                        <div class="col-12 row no-gutters bg-dark mb-2">
                            <?php 
                                foreach ($sentryLabels as $challengeType => $challengeLabel):
                                    $challengeColumn = $sentryChallengeColumns[$challengeType];
                                    $challengeIcon   = $sentryChallengeIcons[$challengeType];
                            ?>
                                <div class="col-12 col-lg">
                                    <div class="res res-<?php echo $challengeIcon;?>" title="<?php echo $opponentData[$challengeColumn];?>" data-html="true">
                                        <div class="icon"></div>
                                        <span><?php echo esc_html($challengeLabel);?>: <b><?php echo $opponentData[$challengeColumn];?></b></span>
                                    </div>
                                </div>
                            <?php endforeach;?>
                            <div class="col-12 text-center mb-2">
                                <?php echo esc_html__('Chance of success', 'stephino-rpg');?>: <b><?php 
                                    echo number_format(
                                        Stephino_Rpg_Db::get()->modelSentries()->getSuccessRate(
                                            (int) $userData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_LEVEL_ATTACK], 
                                            (int) $userData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_LEVEL_DEFENSE], 
                                            (int) $opponentData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_LEVEL_ATTACK], 
                                            (int) $opponentData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_LEVEL_DEFENSE]
                                        ), 
                                        2
                                    );
                                ?>%</b>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <button 
                            class="btn btn-default w-100"
                            data-click="sentryChallengePrepare" 
                            data-click-args="<?php echo (int) $opponentData[Stephino_Rpg_Db_Table_Users::COL_ID];?>,<?php echo $sentryChallenge;?>">
                            <span><?php echo esc_html__('Fight', 'stephino-rpg');?> (<?php echo esc_html($sentryLabelsAction[$sentryChallenge]);?>)</span>
                        </button>
                    </div>
                </div>
            <?php endforeach;?>
        </div>
        <div class="col-12 text-center">
            <?php 
                require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                    Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_PAGINATION
                );
            ?>
        </div>
    <?php else:?>
        <div class="col-12 framed text-center align-items-center">
            <?php echo esc_html__('Searching for viable targets', 'stephino-rpg');?>
            <span data-effect="countdownTime" data-effect-args="0,3"></span>
        </div>
    <?php endif;?>
</div>