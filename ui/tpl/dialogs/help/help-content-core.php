<?php
/**
 * Template:Dialog:Help Content
 * 
 * @title      Help Content - Core
 * @desc       Template for the help content - loaded both directly and with AJAX inside [data-role="content"]
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $configObject Stephino_Rpg_Config_Core */
/* @var $itemId string */
?>
<div class="col-12 p-2 text-center">
    <h4>
        <span data-click="dialog" data-click-args="dialogSettingsAbout"><?php echo Stephino_Rpg_Utils_Lingo::getGameName();?></span>
    </h4>
    <?php if (!Stephino_Rpg_Utils_Themes::getActive()->isDefault()):?>
        <h6><?php echo esc_html__('Customized by', 'stephino-rpg');?> <?php echo esc_html(get_bloginfo('name'));?></h6>
    <?php endif;?>
</div>
<?php 
    require Stephino_Rpg_Renderer_Ajax_Dialog_Help::dialogTemplatePath(
        Stephino_Rpg_Renderer_Ajax_Dialog_Help::TEMPLATE_FRAGMENT_DESCRIPTION
    );
?>
<div class="col-12 p-2 <?php echo (Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_GAME_ADMIN == $itemId ? 'framed active' : '');?>">
    <h6 class="heading"><span><?php echo esc_html__('Game admins', 'stephino-rpg');?></span></h6>
    <ul>
        <?php if (Stephino_Rpg_Cache_User::get()->isGameAdmin()):?>
            <li><b><?php echo esc_html__('You are a game admin', 'stephino-rpg');?>!</b></li>
        <?php endif;?>
        <li><?php echo esc_html__('They decide the rules of the game, as they are presented here', 'stephino-rpg');?></li>
        <li>
            <?php 
                echo sprintf(
                    esc_html__('They have full control over all players and %s', 'stephino-rpg'),
                    '<span'
                        . ' data-effect="helpMenuItem"'
                        . ' data-effect-args="' . Stephino_Rpg_Config_Core::KEY . ',' . Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_GAME_MASTER . '">'
                            . esc_html__('Game masters', 'stephino-rpg')
                    . '</span>'
                );
            ?>
        </li>
        <li><?php echo esc_html__('They cannot be demoted', 'stephino-rpg');?></li>
    </ul>
</div>
<div class="col-12 p-2 <?php echo (Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_GAME_MASTER == $itemId ? 'framed active' : '');?>">
    <h6 class="heading"><span><?php echo esc_html__('Game masters', 'stephino-rpg');?></span></h6>
    <ul>
        <?php if (Stephino_Rpg_Cache_User::get()->isGameMaster()):?>
            <li><b><?php echo esc_html__('You are a game master', 'stephino-rpg');?>!</b></li>
        <?php endif;?>
        <li><?php echo esc_html__('They get access to extra information about players', 'stephino-rpg');?></li>
        <li>
            <?php 
                echo $configObject->getGmPromote()
                    ? esc_html__('They promote and demote other players and game masters, including themselves', 'stephino-rpg')
                    : esc_html__('They cannot promote or demote other players', 'stephino-rpg');
            ?>
        </li>
        <?php if ($configObject->getChatRoom() && $configObject->getGmModChat()):?>
            <li><?php echo esc_html__('They can moderate the chat room', 'stephino-rpg');?></li>
        <?php endif;?>
        <?php if ($configObject->getPtfEnabled() && $configObject->getGmModPtfs()):?>
            <li>
                <span 
                    data-effect="helpMenuItem"
                    data-effect-args="<?php echo Stephino_Rpg_Config_Core::KEY;?>,<?php echo Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_GAME_ARENA;?>">
                    <?php echo esc_html__('Game arena', 'stephino-rpg');?>
                </span>: <?php echo esc_html__('They can review, edit and delete games', 'stephino-rpg');?>
            </li>
        <?php endif;?>
        <?php if ($configObject->getConsoleEnabled() && $configObject->getGmCli()):?>
            <li>
                <span
                    data-effect="helpMenuItem"
                    data-effect-args="<?php echo Stephino_Rpg_Config_Core::KEY;?>,<?php echo Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_CONSOLE;?>">
                    <?php echo esc_html__('Game console', 'stephino-rpg');?>
                </span>: <?php echo esc_html__('They can control all game resources, including the flow of time', 'stephino-rpg');?>
            </li>
        <?php endif;?>
    </ul>
</div>
<?php if ($configObject->getSentryEnabled()):?>
    <div class="col-12 p-2 <?php echo (Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_SENTRIES == $itemId ? 'framed active' : '');?>">
        <h6 class="heading"><span><?php echo $configObject->getConfigSentriesName(true);?></span></h6>
        <ul>
            <li>
                <?php echo esc_html__('Challenge other players in order to improve your abilities', 'stephino-rpg');?>:
                <ul>
                    <?php foreach (Stephino_Rpg_Db::get()->modelSentries()->getLabels() as $labelName):?>
                    <li><?php echo esc_html($labelName);?></li>
                    <?php endforeach;?>
                </ul>
            </li>
            <li>
                <?php 
                    echo $configObject->getSentryMaxLevel() > 0
                        ? sprintf(
                            __('The maximum level you can achieve for each ability is %s', 'stephino-rpg'), 
                            '<b>' . $configObject->getSentryMaxLevel() . '</b>'
                        )
                        : __('There is no limit to how much you can improve your abilities', 'stephino-rpg');
                ?>
            </li>
            <?php if ($configObject->getSentryLootGold() > 0 || $configObject->getSentryLootResearch() > 0):?>
                <li>
                    <?php echo esc_html__('You can take loot from your enemies if you win the challenge', 'stephino-rpg');?>
                </li>
            <?php endif;?>
            <?php if ($configObject->getSentryReward() > 0):?>
                <li>
                    <?php echo sprintf(
                        esc_html__('You will also earn rewards (%s)', 'stephino-rpg'),
                        '<b>' . $configObject->getResourceGemName(true) . '</b>'
                    );?>
                </li>
            <?php endif;?>
            <?php if ($configObject->getSentryLootGold() > 0 || $configObject->getSentryLootResearch() > 0 || $configObject->getSentryReward() > 0):?>
                <li>
                    <?php 
                        echo sprintf(
                            esc_html__('%s chance of success or lower: you will receive the entire reward', 'stephino-rpg'),
                            '<b>50%</b>'
                        );?><br/>
                    <?php 
                        echo sprintf(
                            esc_html__('%s chance of success: your final reward will be reduced to %s', 'stephino-rpg'),
                            '<b>100%</b>',
                            '<b>' . $configObject->getSentryOPYield() . '%</b>'
                        );
                    ?>
                </li>
            <?php endif;?>
            <li><?php echo esc_html__('If leave your base when you are attacked you automatically lose', 'stephino-rpg');?></li>
        </ul>
    </div>
<?php endif;?>
<div class="col-12 p-2 <?php echo (Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_GAME_RES == $itemId ? 'framed active' : '');?>">
    <h6 class="heading"><span><?php echo esc_html__('Common resources', 'stephino-rpg');?></span></h6>
    <div class="col-12 text-center">
        <?php 
            echo sprintf(
                esc_html__('These resources are accessible everywhere', 'stephino-rpg'),
                $configObject->getConfigCitiesName(true)
            );
        ?>
    </div>
    <?php if (null !== $configObject->getResourceGoldDescription(true)):?>
        <div class="col-12">
            <span class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_GOLD;?>">
                <div class="icon"></div>
                <span>
                    <b><?php echo $configObject->getResourceGoldName(true);?></b>:
                    <?php echo $configObject->getResourceGoldDescription(true);?>
                </span>
            </span>
        </div>
    <?php endif;?>
    <?php if (null !== $configObject->getResourceGemDescription(true)):?>
        <div class="col-12">
            <span class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_GEM;?>">
                <div class="icon"></div>
                <span>
                    <b><?php echo $configObject->getResourceGemName(true);?></b>:
                    <?php echo $configObject->getResourceGemDescription(true);?>
                </span>
            </span>
        </div>
    <?php endif;?>
    <?php if (null !== $configObject->getResourceResearchDescription(true)):?>
        <div class="col-12">
            <span class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_RESEARCH;?>">
                <div class="icon"></div>
                <span>
                    <b><?php echo $configObject->getResourceResearchName(true);?></b>:
                    <?php echo $configObject->getResourceResearchDescription(true);?>
                </span>
            </span>
        </div>
    <?php endif;?>
</div>
<div class="col-12 p-2 <?php echo (Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_CITY_RES == $itemId ? 'framed active' : '');?>">
    <h6 class="heading"><span><?php 
        echo sprintf(
            esc_html__('%s: resources', 'stephino-rpg'),
            $configObject->getConfigCityName(true)
        );
    ?></span></h6>
    <div class="col-12 text-center">
        <?php 
            echo sprintf(
                esc_html__('These resources are generated and stored in: %s', 'stephino-rpg'),
                $configObject->getConfigCitiesName(true)
            );
        ?>
    </div>
    <?php if (null !== $configObject->getResourceAlphaDescription(true)):?>
        <div class="col-12">
            <span class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_ALPHA;?>">
                <div class="icon"></div>
                <span>
                    <b><?php echo $configObject->getResourceAlphaName(true);?></b>:
                    <?php echo $configObject->getResourceAlphaDescription(true);?>
                </span>
            </span>
        </div>
    <?php endif;?>
    <?php if (null !== $configObject->getResourceBetaDescription(true)):?>
        <div class="col-12">
            <span class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_BETA;?>">
                <div class="icon"></div>
                <span>
                    <b><?php echo $configObject->getResourceBetaName(true);?></b>:
                    <?php echo $configObject->getResourceBetaDescription(true);?>
                </span>
            </span>
        </div>
    <?php endif;?>
    <?php if (null !== $configObject->getResourceGammaDescription(true)):?>
        <div class="col-12">
            <span class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_GAMMA;?>">
                <div class="icon"></div>
                <span>
                    <b><?php echo $configObject->getResourceGammaName(true);?></b>:
                    <?php echo $configObject->getResourceGammaDescription(true);?>
                </span>
            </span>
        </div>
    <?php endif;?>
    <?php if (null !== $configObject->getResourceExtra1Description(true)):?>
        <div class="col-12">
            <span class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_EXTRA_1;?>">
                <div class="icon"></div>
                <span>
                    <b><?php echo $configObject->getResourceExtra1Name(true);?></b>:
                    <?php echo $configObject->getResourceExtra1Description(true);?>
                </span>
            </span>
        </div>
    <?php endif;?>
    <?php if (null !== $configObject->getResourceExtra2Description(true)):?>
        <div class="col-12">
            <span class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_EXTRA_2;?>">
                <div class="icon"></div>
                <span>
                    <b><?php echo $configObject->getResourceExtra2Name(true);?></b>:
                    <?php echo $configObject->getResourceExtra2Description(true);?>
                </span>
            </span>
        </div>
    <?php endif;?>
</div>
<div class="col-12 p-2 <?php echo (Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_CITY_METRICS == $itemId ? 'framed active' : '');?>">
    <h6 class="heading">
        <span><?php 
            echo sprintf(
                esc_html__('%s: metrics', 'stephino-rpg'),
                $configObject->getConfigCityName(true)
            );
        ?></span>
    </h6>
    <div class="col-12 text-center">
        <?php 
            echo sprintf(
                esc_html__('%s: these metrics are not affected by workers', 'stephino-rpg'),
                $configObject->getConfigCityName(true)
            );
        ?>
    </div>
    <?php if (null !== $configObject->getMetricPopulationDescription(true)):?>
        <div class="col-12">
            <span class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_MTR_POPULATION;?>">
                <div class="icon"></div>
                <span>
                    <b><?php echo $configObject->getMetricPopulationName(true);?></b>:
                    <?php echo $configObject->getMetricPopulationDescription(true);?>
                </span>
            </span>
        </div>
    <?php endif;?>
    <?php if (null !== $configObject->getMetricSatisfactionDescription(true)):?>
        <div class="col-12">
            <span class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_MTR_SATISFACTION;?>">
                <div class="icon"></div>
                <span>
                    <b><?php echo $configObject->getMetricSatisfactionName(true);?></b>:
                    <?php echo $configObject->getMetricSatisfactionDescription(true);?>
                </span>
            </span>
        </div>
    <?php endif;?>
    <?php if (null !== $configObject->getMetricStorageDescription(true)):?>
        <div class="col-12">
            <span class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_MTR_STORAGE;?>">
                <div class="icon"></div>
                <span>
                    <b><?php echo $configObject->getMetricStorageName(true);?></b>:
                    <?php echo $configObject->getMetricStorageDescription(true);?>
                </span>
            </span>
        </div>
    <?php endif;?>
</div>
<?php if ($configObject->getConsoleEnabled() && (Stephino_Rpg::get()->isDemo() || Stephino_Rpg_Cache_User::get()->isElevated(Stephino_Rpg_Cache_User::PERM_CLI))):?>
<div class="col-12 p-2 <?php echo (Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_CONSOLE == $itemId ? 'framed active' : '');?>">
    <h6 class="heading"><span><?php echo esc_html__('Game console', 'stephino-rpg');?></span></h6>
    <ul>
        <li>
            <?php 
                echo sprintf(
                    esc_html__('You can toggle the game console visibility with %s', 'stephino-rpg'),
                    '<b>Alt+Ctrl+C</b>'
                );
            ?>
        </li>
        <li>
            <?php echo esc_html__('The console allows you a fine level of control over the game', 'stephino-rpg');?>
        </li>
    </ul>
</div>
<?php endif;?>
<div class="col-12 p-2 <?php echo (Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_SCORE == $itemId ? 'framed active' : '');?>">
    <h6 class="heading"><span><?php echo esc_html__('Total score', 'stephino-rpg');?></span></h6>
    <div class="col-12 text-center">
        <?php echo esc_html__('Your total score is calculated as follows:', 'stephino-rpg');?>
    </div>
    <ul>
        <?php if (0 != $configObject->getScoreQueueBuilding()):?>
            <li>
                <?php 
                    echo sprintf(
                        esc_html__('%s for each upgrade (%s) multiplied by level', 'stephino-rpg'),
                        '<b>' . ($configObject->getScoreQueueBuilding() > 0 ? '+' : '') . $configObject->getScoreQueueBuilding() . '</b> ' 
                            . _n('point', 'points', $configObject->getScoreQueueBuilding(), 'stephino-rpg'),
                        $configObject->getConfigBuildingName(true)
                    );
                ?>
            </li>
        <?php endif;?>
        <?php if (0 != $configObject->getScoreQueueEntity()):?>
            <li>
                <?php 
                    echo sprintf(
                        esc_html__('%s for each entity recruited', 'stephino-rpg'),
                        '<b>' . ($configObject->getScoreQueueEntity() > 0 ? '+' : '') . $configObject->getScoreQueueEntity() . '</b> '
                            . _n('point', 'points', $configObject->getScoreQueueEntity(), 'stephino-rpg')
                    );
                ?>
            </li>
        <?php endif;?>
        <?php if (0 != $configObject->getScoreQueueResearch()):?>
            <li>
                <?php 
                    echo sprintf(
                        esc_html__('%s for each upgrade (%s) multiplied by level', 'stephino-rpg'),
                        '<b>' . ($configObject->getScoreQueueResearch() > 0 ? '+' : '') . $configObject->getScoreQueueResearch() . '</b> ' 
                            . _n('point', 'points', $configObject->getScoreQueueResearch(), 'stephino-rpg'),
                        $configObject->getConfigResearchFieldName(true)
                    );
                ?>
            </li>
        <?php endif;?>
        <?php if (0 != $configObject->getScoreBattleDefeat()):?>
            <li>
                <?php 
                    echo sprintf(
                        esc_html__('%s for each defeat', 'stephino-rpg'),
                        '<b>' . ($configObject->getScoreBattleDefeat() > 0 ? '+' : '') . $configObject->getScoreBattleDefeat() . '</b> '
                            . _n('point', 'points', $configObject->getScoreBattleDefeat(), 'stephino-rpg')
                    );
                ?>
            </li>
        <?php endif;?>
        <?php if (0 != $configObject->getScoreBattleDraw()):?>
            <li>
                <?php 
                    echo sprintf(
                        esc_html__('%s for each impasse', 'stephino-rpg'),
                        '<b>' . ($configObject->getScoreBattleDraw() > 0 ? '+' : '') . $configObject->getScoreBattleDraw() . '</b> '
                            . _n('point', 'points', $configObject->getScoreBattleDraw(), 'stephino-rpg')
                    );
                ?>
            </li>
        <?php endif;?>
        <?php if (0 != $configObject->getScoreBattleVictory()):?>
            <li>
                <?php 
                    echo sprintf(
                        esc_html__('%s for each victory', 'stephino-rpg'),
                        '<b>' . ($configObject->getScoreBattleVictory() > 0 ? '+' : '') . $configObject->getScoreBattleVictory() . '</b> '
                            . _n('point', 'points', $configObject->getScoreBattleVictory(), 'stephino-rpg')
                    );
                ?>
            </li>
        <?php endif;?>
        <?php if ($configObject->getPtfEnabled() && 0 != $configObject->getPtfScore()):?>
            <li>
                <?php 
                    echo sprintf(
                        esc_html__('%s for each game arena victory', 'stephino-rpg'),
                        '<b>' . ($configObject->getPtfScore() > 0 ? '+' : '') . $configObject->getPtfScore() . '</b> '
                            . _n('point', 'points', $configObject->getPtfScore(), 'stephino-rpg')
                    );
                ?>
            </li>
        <?php endif;?>
        <?php if ($configObject->getSentryEnabled() && 0 != $configObject->getSentryScore()):?>
            <li>
                <?php 
                    echo sprintf(
                        esc_html__('%s for each challenge you win (%s)', 'stephino-rpg'),
                        '<b>' . ($configObject->getSentryScore() > 0 ? '+' : '') . $configObject->getSentryScore() . '</b> '
                            . _n('point', 'points', $configObject->getSentryScore(), 'stephino-rpg'),
                        '<b>' . $configObject->getConfigSentriesName(true) . '</b>'
                    );
                ?>
            </li>
        <?php endif;?>
    </ul>
</div>
<?php 
    if ($configObject->getPtfEnabled()):
        // Platformer has a reward
        $ptfHasRewad = (
            // Player reward
            $configObject->getPtfRewardPlayer() > 0
            || (
                // Author reward
                $configObject->getPtfAuthorLimit() > 0
                && $configObject->getPtfRewardAuthor() > 0
            )
        );
?>
<div class="col-12 p-2 <?php echo (Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_GAME_ARENA == $itemId ? 'framed active' : '');?>">
    <h6 class="heading"><span><?php echo esc_html__('Game arena', 'stephino-rpg');?></span></h6>
    <ul>
        <li><?php echo esc_html__('Objective: collect all the gems then pass through the gate', 'stephino-rpg');?></li>
        <?php if ($configObject->getPtfRewardPlayer() > 0):?>
            <li>
                <?php 
                    echo sprintf(
                        esc_html__('Win to get %s', 'stephino-rpg'),
                        '<b>' . $configObject->getPtfRewardPlayer() . '</b> ' . $configObject->getResourceGemName(true)
                    );
                ?>
            </li>
        <?php endif;?>
        <?php if ($configObject->getPtfAuthorLimit() > 0):?>
            <?php if ($configObject->getPtfRewardAuthor() > 0):?>
                <li>
                    <?php 
                        echo sprintf(
                            esc_html__('Earn %s each time a game you created is won', 'stephino-rpg'),
                            '<b>' . $configObject->getPtfRewardAuthor() . '</b> ' . $configObject->getResourceGemName(true)
                        );
                    ?>
                </li>
            <?php endif;?>
            <li>
                <?php 
                    echo sprintf(
                        esc_html__('Each player can create a maximum of %s', 'stephino-rpg'),
                        '<b>' . $configObject->getPtfAuthorLimit() . '</b> ' 
                            . esc_html(_n('game', 'games', $configObject->getPtfAuthorLimit(), 'stephino-rpg'))
                    );
                ?>
            </li>
        <?php endif;?>
        <?php if ($ptfHasRewad):?>
            <li>
                <?php 
                    if ($configObject->getPtfRewardResetHours() > 0) {
                        echo sprintf(
                            esc_html__('Rewards reset after %s', 'stephino-rpg'),
                            '<b>' . $configObject->getPtfRewardResetHours() . '</b> ' 
                                . esc_html(_n('hour', 'hours', $configObject->getPtfRewardResetHours(), 'stephino-rpg'))
                        );
                    } else {
                        echo esc_html__('Rewards are earned only once', 'stephino-rpg');
                    }
                ?>
            </li>
        <?php endif;?>
        <li><?php echo esc_html__('Players are not allowed to delete games', 'stephino-rpg');?></li>
        <li><?php 
            echo $configObject->getPtfStrikes() > 1
                ? sprintf(
                    esc_html__('Players are not allowed to edit or create games after %s strikes (suspended games)', 'stephino-rpg'),
                    '<b>' . $configObject->getPtfStrikes() . '</b>'
                )
                : __('Players are not allowed to edit or create new games after one of their games gets suspended', 'stephino-rpg');
        ?></li>
    </ul>
</div>
<?php endif;?>
<div class="col-12 p-2 <?php echo (Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_ROBOTS == $itemId ? 'framed active' : '');?>">
    <h6 class="heading"><span><?php echo esc_html__('Robots', 'stephino-rpg');?></span></h6>
    <ul>
        <?php if ($configObject->getRobotTimeLapsesPerRequest() > 0):?>
            <li>
                <?php
                    if ($configObject->getInitialRobotsPerUser() > 0) {
                        echo sprintf(
                            esc_html__('%s new %s are spawned for each player', 'stephino-rpg'),
                            '<b>' . $configObject->getInitialRobotsPerUser() . '</b>',
                            _n('robot', 'robots', $configObject->getInitialRobotsPerUser(), 'stephino-rpg')
                        );
                    } else {
                        echo esc_html__('No new robots are spawned with new players', 'stephino-rpg');
                    }
                ?>
            </li>
            <li>
                <?php 
                    echo sprintf(
                        esc_html__('Robots wait for %s between attacks', 'stephino-rpg'),
                        '<b>' . $configObject->getRobotsTimeout() . '</b> ' 
                            . esc_html(_n('hour', 'hours', $configObject->getRobotsTimeout(), 'stephino-rpg'))
                    );
                ?>
            </li>
            <li>
                <?php echo esc_html__('Robots aggression', 'stephino-rpg');?>: 
                <b><?php 
                    switch ($configObject->getRobotsAggression()) {
                        case Stephino_Rpg_Config_Core::ROBOT_AGG_LOW:
                            echo __('Low', 'stephino-rpg');
                            break;

                        case Stephino_Rpg_Config_Core::ROBOT_AGG_MEDIUM:
                            echo __('Medium', 'stephino-rpg');
                            break;

                        case Stephino_Rpg_Config_Core::ROBOT_AGG_HIGH:
                            echo __('High', 'stephino-rpg');
                            break;

                    }
                ?></b> - <?php 
                    switch ($configObject->getRobotsAggression()) {
                        case Stephino_Rpg_Config_Core::ROBOT_AGG_LOW:
                            echo __('do not fight back, do not initiate attacks', 'stephino-rpg');
                            break;

                        case Stephino_Rpg_Config_Core::ROBOT_AGG_MEDIUM:
                            echo __('fight back, do not initiate attacks', 'stephino-rpg');
                            break;

                        case Stephino_Rpg_Config_Core::ROBOT_AGG_HIGH:
                            echo __('fight back, initiate attacks', 'stephino-rpg');
                            break;

                    }
                ?>
            </li>
        <?php if ($configObject->getSentryEnabled() && Stephino_Rpg_Config_Core::ROBOT_AGG_LOW !== $configObject->getRobotsAggression()):?>
            <li>
                <?php 
                    echo sprintf(
                        esc_html__('Robots will challenge other players with %s'),
                        '</b>' . $configObject->getConfigSentriesName(true) . '</b>'
                    );
                ?>
            </li>
        <?php endif;?>
            <li>
                <?php echo esc_html__('The fervor of robots', 'stephino-rpg');?>: <b><?php echo $configObject->getRobotsFervor();?>%</b>
            </li>
        <?php else:?>
            <li><?php echo esc_html__('Robots are disabled', 'stephino-rpg');?></li>
        <?php endif;?>
    </ul>
</div>
<div class="col-12 p-2 <?php echo (Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_RULES == $itemId ? 'framed active' : '');?>">
    <h6 class="heading"><span><?php echo esc_html__('Game rules', 'stephino-rpg');?></span></h6>
    <ul>
        <li><?php echo esc_html__('Resource gathering may require workers', 'stephino-rpg');?></li>
        <?php if (count($configObject->cityInitialBuildings())):?>
            <li>
                <?php if (count($configObject->cityInitialBuildings()) > 1):?>
                    <?php 
                        echo sprintf(
                            esc_html__('%s: when newly founded, they get the following %s:', 'stephino-rpg'),
                            $configObject->getConfigCitiesName(true),
                            $configObject->getConfigBuildingsName(true)
                        );
                    ?>
                    <ul>
                        <?php foreach ($configObject->cityInitialBuildings() as $buildingConfig):?>
                        <li>
                            <span 
                                data-effect="helpMenuItem"
                                data-effect-args="<?php echo $buildingConfig->keyCollection();?>,<?php echo $buildingConfig->getId();?>">
                                <?php echo $buildingConfig->getName(true);?>
                            </span>
                            <?php if ($buildingConfig->isMainBuilding()):?>
                                (<?php echo esc_html__('main', 'stephino-rpg');?>)
                            <?php endif;?>
                        </li>
                        <?php endforeach;?>
                    </ul>
                <?php 
                    else: 
                        $cityInitialBuilding = current($configObject->cityInitialBuildings());
                ?>
                    <?php 
                        echo sprintf(
                            esc_html__('%s: Initialized with a level 1 %s', 'stephino-rpg'),
                            $configObject->getConfigCitiesName(true),
                            '<span data-effect="helpMenuItem" data-effect-args="' . $cityInitialBuilding->keyCollection() . ',' . $cityInitialBuilding->getId() . '">'
                                . $cityInitialBuilding->getName(true)
                            . '</span>'
                        );
                    ?>
                    <?php if ($cityInitialBuilding->isMainBuilding()):?>
                        (<?php echo esc_html__('main', 'stephino-rpg');?>)
                    <?php endif;?>
                <?php endif; ?>
            </li>
        <?php endif;?>
        <li>
            <?php 
                if ($configObject->getMessageDailyLimit() <= 0) {
                    echo esc_html__('Contact between players is forbidden', 'stephino-rpg');
                } else {
                    echo sprintf(
                        esc_html__('Each player can send a maximum of %s %s per day', 'stephino-rpg'),
                        '<b>' . $configObject->getMessageDailyLimit() . '</b>',
                        _n('message', 'messages', $configObject->getMessageDailyLimit(), 'stephino-rpg')
                    );
                }
            ?>
        </li>
        <li>
            <?php 
                echo sprintf(
                    esc_html__('Your inbox can hold a maximum of %s messages', 'stephino-rpg'),
                    '<b>' . $configObject->getMessageInboxLimit() . '</b>'
                );
            ?>
        </li>
        <li>
            <?php
                echo sprintf(
                    esc_html__('Messages are automatically deleted after %s %s', 'stephino-rpg'),
                    '<b>' . $configObject->getMessageMaxAge() . '</b>',
                    _n('day', 'days', $configObject->getMessageMaxAge(), 'stephino-rpg')
                );
            ?>
        </li>
        <li>
            <?php 
                if ($configObject->getTravelTime() > 0) {
                    echo sprintf(
                        esc_html__('The distance between %s is traveled in %s seconds', 'stephino-rpg'),
                        $configObject->getConfigCitiesName(true),
                        '<b>' . $configObject->getTravelTime() . '</b>'
                    );
                } else {
                    echo esc_html__('All travel is instantaneous', 'stephino-rpg');
                }
            ?>
        </li>
        <li>
            <?php 
                if ($configObject->getInitialIslandsCount() > 0) {
                    echo sprintf(
                        esc_html__('The game world was initialized with %s %s (empty)', 'stephino-rpg'),
                        '<b>' . $configObject->getInitialIslandsCount() . '</b>',
                        1 == $configObject->getInitialIslandsCount()
                            ? $configObject->getConfigIslandName()
                            : $configObject->getConfigIslandsName()
                    );
                } else {
                    echo sprintf(
                        esc_html__('The game world was initialized with no %s', 'stephino-rpg'),
                        $configObject->getConfigIslandsName()
                    );
                }
            ?>
        </li>
        <li>
            <?php 
                if ($configObject->getInitialIslandsPerUser() > 0) {
                    echo sprintf(
                        esc_html__('%s new %s are created for each player', 'stephino-rpg'),
                        '<b>' . $configObject->getInitialIslandsPerUser() . '</b>',
                        $configObject->getConfigIslandsName()
                    );
                } else {
                    echo sprintf(
                        esc_html__('No new %s are created with new players', 'stephino-rpg'),
                        $configObject->getConfigIslandsName()
                    );
                }
            ?>
        </li>
        <li>
            <?php 
                if ($configObject->getRobotTimeLapsesPerRequest() > 0) {
                    echo sprintf(
                        esc_html__('Each player action triggers %s %s by robots', 'stephino-rpg'),
                        '<b>' . $configObject->getRobotTimeLapsesPerRequest() . '</b>',
                        _n('action', 'actions', $configObject->getRobotTimeLapsesPerRequest(), 'stephino-rpg')
                    );
                } else {
                    echo esc_html__('All robots are inactive', 'stephino-rpg');
                }
            ?>
        </li>
        <?php if (Stephino_Rpg_Cache_User::get()->isElevated()):?>
            <li>
                <?php 
                    echo sprintf(
                        esc_html__('The cool-down period between time-lapse procedures is %s seconds for each player', 'stephino-rpg'),
                        '<b>' . $configObject->getTimeLapseCooldown() . '</b>'
                    );
                ?>
            </li>
        <?php endif;?>
        <li>
            <?php 
                echo sprintf(
                    esc_html__('%s needs to be higher than %s to ensure growth of our bases (%s)', 'stephino-rpg'),
                    '<b>' . $configObject->getMetricSatisfactionName(true) . '</b>',
                    '<b>' . $configObject->getMetricPopulationName(true) . '</b>',
                    $configObject->getConfigCitiesName(true)
                );
            ?>
        </li>
        <li><?php echo esc_html__('The growth factor is calculated as follows', 'stephino-rpg');?>:<br/>
            <ul>
                <li>
                    <b>-1</b> <?php echo esc_html__('if', 'stephino-rpg');?> 
                    <b><?php echo $configObject->getMetricSatisfactionName(true);?></b> = <b>0</b>
                </li>
                <li>
                    <b>+0</b> <?php echo esc_html__('if', 'stephino-rpg');?> 
                    <b><?php echo $configObject->getMetricSatisfactionName(true);?></b> = <b><?php echo $configObject->getMetricPopulationName(true);?></b>
                </li>
                <li>
                    <b>+1</b> <?php echo esc_html__('if', 'stephino-rpg');?> 
                    <b><?php echo $configObject->getMetricSatisfactionName(true);?></b> = <b>2 &times; <?php echo $configObject->getMetricPopulationName(true);?></b>
                </li>
                <li>
                    <b>+2</b> <?php echo esc_html__('if', 'stephino-rpg');?> 
                    <b><?php echo $configObject->getMetricSatisfactionName(true);?></b> &gt;= <b>3 &times; <?php echo $configObject->getMetricPopulationName(true);?></b>
                </li>
            </ul>
        </li>
        <li>
            <?php
                echo sprintf(
                    esc_html__('Metropolises are marked with "%s"', 'stephino-rpg'),
                    '<b>' . Stephino_Rpg_Renderer_Ajax_Html::SYMBOL_CAPITAL . '</b>'
                );
            ?>
        </li>
        <?php if ($configObject->getCapitalSatisfactionBonus() > 0):?>
            <li>
                <?php 
                    echo sprintf(
                        esc_html__('Metropolises get a %s boost of %s', 'stephino-rpg'),
                        '<b>' . $configObject->getMetricSatisfactionName(true) . '</b>',
                        '<b>' . $configObject->getCapitalSatisfactionBonus() . '</b>%'
                    );
                ?>
            </li>
        <?php endif;?>
        <?php if ($configObject->getInitialUserResourceGold() || $configObject->getInitialUserResourceGem() || $configObject->getInitialUserResourceResearch()):?>
            <li>
                <?php 
                    echo sprintf(
                        esc_html__('Players get the following resources with their first base (%s)', 'stephino-rpg'),
                        $configObject->getConfigCityName()
                    );
                ?>:
                <ul>
                    <?php if ($configObject->getInitialUserResourceGold() > 0):?>
                        <li><b><?php echo $configObject->getInitialUserResourceGold();?></b> <?php echo $configObject->getResourceGoldName(true);?></li>
                    <?php endif;?>
                    <?php if ($configObject->getInitialUserResourceGem() > 0):?>
                        <li><b><?php echo $configObject->getInitialUserResourceGem();?></b> <?php echo $configObject->getResourceGemName(true);?></li>
                    <?php endif;?>
                    <?php if ($configObject->getInitialUserResourceResearch() > 0):?>
                        <li><b><?php echo $configObject->getInitialUserResourceResearch();?></b> <?php echo $configObject->getResourceResearchName(true);?></li>
                    <?php endif;?>
                </ul>
            </li>
        <?php endif;?>
        <?php if ($configObject->getGemToGoldRatio() > 0):?>
            <li>
                <?php 
                    echo sprintf(
                        esc_html__('Players can trade %s for %s', 'stephino-rpg'),
                        '<b>' . number_format(1, 2) . '</b> ' . $configObject->getResourceGemName(true),
                        '<b>' . number_format($configObject->getGemToGoldRatio(), 2) . '</b> ' . $configObject->getResourceGoldName(true)
                    );
                ?>
            </li>
        <?php endif;?>
        <?php if ($configObject->getGemToResearchRatio() > 0):?>
            <li>
                <?php 
                    echo sprintf(
                        esc_html__('Players can trade %s for %s', 'stephino-rpg'),
                        '<b>' . number_format(1, 2) . '</b> ' . $configObject->getResourceGemName(true),
                        '<b>' . number_format($configObject->getGemToResearchRatio(), 2) . '</b> ' . $configObject->getResourceResearchName(true)
                    );
                ?>
            </li>
        <?php endif;?>
        <li>
            <?php echo esc_html__('Maximum queues', 'stephino-rpg');?>:
            <ul>
                <li>
                    <?php 
                        echo sprintf(
                            esc_html__('%s - Queues per %s', 'stephino-rpg'),
                            '<b>' . $configObject->getConfigBuildingsName(true) . '</b>',
                            $configObject->getConfigCityName(true)
                        );
                    ?>: <b><?php echo $configObject->getMaxQueueBuildings();?></b>
                </li>
                <li>
                    <?php 
                        echo sprintf(
                            esc_html__('%s - Queues per %s', 'stephino-rpg'),
                            '<b>' . $configObject->getConfigUnitsName(true) . '/' . $configObject->getConfigShipsName(true) . '</b>',
                            $configObject->getConfigCityName(true)
                        );
                    ?>: <b><?php echo $configObject->getMaxQueueEntities();?></b>
                </li>
                <li>
                    <?php 
                        echo sprintf(
                            esc_html__('%s - Queues in %s (total)', 'stephino-rpg'),
                            '<b>' . $configObject->getConfigResearchFieldsName(true) . '</b>',
                            $configObject->getConfigCitiesName(true)
                        );
                    ?>: <b><?php echo $configObject->getMaxQueueResearchFields();?></b>
                </li>
            </ul>
        </li>
    </ul>
</div>