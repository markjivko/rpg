<?php
/**
 * Template:Dialog:User Leader Board
 * 
 * @title      User Leader Board dialog
 * @desc       Template for the leader board
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $leaderBoard array */
/* @var $userPlace int */
/* @var $currentTime int */
?>
<div class="col-12 framed p-4">
    <?php if (null === $leaderBoard):?>
        <?php echo esc_html__('The leader board did not initialize correctly, please try again', 'stephino-rpg');?>
    <?php else:?>
    <table class="table table-hover table-responsive-sm">
        <thead>
            <tr>
                <th width="50">#</th>
                <th width="200"><?php echo esc_html__('Nickname', 'stephino-rpg');?></th>
                <th width="150">
                    <span 
                        data-effect="help"
                        data-effect-args="<?php echo Stephino_Rpg_Config_Core::KEY;?>,<?php echo Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_SCORE;?>">
                        <?php echo esc_html__('Total score', 'stephino-rpg');?>
                    </span>
                </th>
                <?php if (Stephino_Rpg_Config::get()->core()->getSentryEnabled()):?>
                    <th>
                        <span 
                            data-effect="help"
                            data-effect-args="<?php echo Stephino_Rpg_Config_Core::KEY;?>,<?php echo Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_SENTRIES;?>">
                            <?php echo Stephino_Rpg_Config::get()->core()->getConfigSentryName(true);?>
                        </span>
                    </th>
                <?php endif;?>
                <th><?php echo esc_html__('Battles', 'stephino-rpg');?></th>
                <?php if (Stephino_Rpg_Config::get()->core()->getPtfEnabled()):?>
                    <th>
                        <span 
                            data-effect="help"
                            data-effect-args="<?php echo Stephino_Rpg_Config_Core::KEY;?>,<?php echo Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_GAME_ARENA;?>">
                            <?php echo esc_html__('Game arena', 'stephino-rpg');?>
                        </spa>
                    </th>
                <?php endif;?>
                <th><?php echo esc_html__('Online', 'stephino-rpg');?></th>
            </tr>
        </thead>
        <tbody>
            <?php 
                foreach ($leaderBoard as $lbKey => $lbUserData): 
                    $colSpan = 5;
                    Stephino_Rpg_Config::get()->core()->getPtfEnabled() && $colSpan++;
                    Stephino_Rpg_Config::get()->core()->getSentryEnabled() && $colSpan++;
            ?>
                <?php if (null === $lbUserData):?>
                    <tr><td colspan="<?php echo $colSpan;?>" class="text-center">...</td></tr>
                <?php else:?>
                    <tr <?php if ($lbKey + 1 === $userPlace):?>class="framed active"<?php endif;?>
                        data-click="userViewProfile"
                        data-click-args="<?php echo intval($lbUserData[Stephino_Rpg_Db_Table_Users::COL_ID]);?>">
                        <th width="50"><?php echo $lbKey + 1;?></td>
                        <td width="200" class="td-ellipsis"><?php echo Stephino_Rpg_Utils_Lingo::getUserName($lbUserData);?></td>
                        <td width="150">
                            <span data-role="score" title="<?php echo number_format($lbUserData[Stephino_Rpg_Db_Table_Users::COL_USER_SCORE]);?>">
                                <?php echo Stephino_Rpg_Utils_Lingo::isuFormat($lbUserData[Stephino_Rpg_Db_Table_Users::COL_USER_SCORE]);?>
                            </span>
                        </td>
                        <?php 
                            if (Stephino_Rpg_Config::get()->core()->getSentryEnabled()):
                                $sentryChallengeColumns = Stephino_Rpg_Db::get()->modelSentries()->getColumns();
                        ?>
                            <td>
                                <?php foreach (Stephino_Rpg_Db::get()->modelSentries()->getLabels() as $challengeType => $challengeLabel):?>
                                    <span title="<?php echo esc_attr($challengeLabel);?>" data-html="true"><?php 
                                        echo (Stephino_Rpg_Db_Model_Sentries::CHALLENGE_ATTACK != $challengeType ? ' / ' : '')
                                            . $lbUserData[$sentryChallengeColumns[$challengeType]];
                                    ?></span>
                                <?php endforeach;?>
                            </td>
                        <?php endif;?>
                        <td>
                            <span class="tb-v" title="<?php echo esc_attr__('Victories', 'stephino-rpg');?>">
                                <?php echo $lbUserData[Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_VICTORIES];?>
                            </span> /
                            <span class="tb-w" title="<?php echo esc_attr__('Impasses', 'stephino-rpg');?>">
                                <?php echo $lbUserData[Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_DRAWS];?>
                            </span> /
                            <span class="tb-d" title="<?php echo esc_attr__('Defeats', 'stephino-rpg');?>">
                                <?php echo $lbUserData[Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_DEFEATS];?>
                            </span>
                        </td>
                        <?php if (Stephino_Rpg_Config::get()->core()->getPtfEnabled()):?>
                            <td>
                                <span data-html="true" title="<?php echo esc_attr__('Times won', 'stephino-rpg');?>">
                                    &#x1f3c6; <?php echo Stephino_Rpg_Utils_Lingo::isuFormat($lbUserData[Stephino_Rpg_Db_Table_Users::COL_USER_PTF_WON]);?>
                                </span> /
                                <span data-html="true" title="<?php echo esc_html__('Times played', 'stephino-rpg');?>">
                                    &#x1f3c1; <?php echo Stephino_Rpg_Utils_Lingo::isuFormat($lbUserData[Stephino_Rpg_Db_Table_Users::COL_USER_PTF_PLAYED]);?>
                                </span>
                            </td>
                        <?php endif;?>
                        <td>
                            <?php if ($currentTime - $lbUserData[Stephino_Rpg_Db_Table_Users::COL_USER_LAST_TICK_AJAX] <= 900):?>
                                <span class="badge badge-success">&#x2713;</span>
                            <?php else:?>
                                <span class="badge">&#x2716;</span>
                            <?php endif;?>
                        </td>
                    </tr>
                <?php endif;?>
            <?php endforeach;?>
        </tbody>
    </table>
    <?php endif;?>
</div>