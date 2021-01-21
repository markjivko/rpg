<?php
/**
 * Template:Dialog:User Info
 * 
 * @title      User Info dialog
 * @desc       Template for the user information dialog
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $userCities int */
/* @var $userId int */
/* @var $userStats array[] */
?>
<div class="row align-items-center justify-content-center mt-2">
    <div class="col-6 col-lg-4">
        <div class="user-icon-frame">
            <?php if (is_numeric($userData[Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID])):?>
                <?php echo get_avatar($userData[Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID], 256);?>
            <?php else:?>
                <img src="<?php echo esc_attr(Stephino_Rpg_Utils_Media::getPluginsUrl() . '/themes/' . Stephino_Rpg_Config::get()->core()->getTheme() . '/img/ui/512-robot.png'); ?>" />
            <?php endif;?>
        </div>
        <h4 class="label w-100 footer-label">
            <span>
                <span title="<?php echo esc_attr__('Total score', 'stephino-rpg');?>: <b><?php echo number_format($userData[Stephino_Rpg_Db_Table_Users::COL_USER_SCORE]);?></b>" data-html="true">
                    <div class="icon-score"></div> 
                    <b><?php echo Stephino_Rpg_Utils_Lingo::isuFormat($userData[Stephino_Rpg_Db_Table_Users::COL_USER_SCORE]);?></b>
                </span>
            </span>
        </h4>
    </div>
    <div class="col-6 col-lg-4">
        <div class="row no-gutters">
            <div class="col-12">
                <div class="icon-attack icon-attack-<?php echo Stephino_Rpg_TimeLapse_Convoys::ATTACK_VICTORY_CRUSHING;?>"></div>
                <b><?php echo number_format($userData[Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_VICTORIES]);?></b>
                <?php echo esc_html(_n('victory', 'victories', $userData[Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_VICTORIES], 'stephino-rpg'));?>
            </div>
            <div class="col-12">
                <div class="icon-attack icon-attack-<?php echo Stephino_Rpg_TimeLapse_Convoys::ATTACK_VICTORY_BITTER;?>"></div>
                <b><?php echo number_format($userData[Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_DRAWS]);?></b>
                <?php echo esc_html(_n('draw', 'draws', $userData[Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_DRAWS], 'stephino-rpg'));?>
            </div>
            <div class="col-12">
                <div class="icon-attack icon-attack-<?php echo Stephino_Rpg_TimeLapse_Convoys::ATTACK_DEFEAT_CRUSHING;?>"></div>
                <b><?php echo number_format($userData[Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_DEFEATS]);?></b>
                <?php echo esc_html(_n('defeat', 'defeats', $userData[Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_DEFEATS], 'stephino-rpg'));?>
            </div>
            <div class="col-12">
                <button class="btn btn-info w-100" data-click="dialog" data-click-args="dialogUserCities,<?php echo intval($userData[Stephino_Rpg_Db_Table_Users::COL_ID]);?>">
                    <span>
                        <b><?php echo number_format($userCities);?></b>
                        <?php 
                            echo (
                                1 == $userCities 
                                    ? Stephino_Rpg_Config::get()->core()->getConfigCityName(true)
                                    : Stephino_Rpg_Config::get()->core()->getConfigCitiesName(true)
                            );
                        ?>
                    </span>
                </button>
            </div>
        </div>
    </div>
    <?php if ($userData[Stephino_Rpg_Db_Table_Users::COL_ID] != Stephino_Rpg_TimeLapse::get()->userId() && strlen($userDescription)):?>
        <div class="col-12 col-lg-8 mt-2">
            <div class="card card-body bg-dark">
                <?php 
                    echo esc_html(
                        strlen($userDescription) > Stephino_Rpg_Renderer_Ajax_Action_Settings::MAX_LENGTH_USER_DESC 
                            ? (substr($userDescription, 0, Stephino_Rpg_Renderer_Ajax_Action_Settings::MAX_LENGTH_USER_DESC) . '...')
                            : $userDescription
                    );
                ?>
            </div>
        </div>
    <?php endif;?>
</div>
<?php if (Stephino_Rpg_Config::get()->core()->getPtfEnabled()):?>
    <div class="col-12 align-items-center mt-2 framed">
        <div class="col-12 text-center mb-2">
            <h4 
                data-effect="help"
                data-effect-args="<?php echo Stephino_Rpg_Config_Core::KEY;?>,<?php echo Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_GAME_ARENA;?>">
                <?php echo esc_html__('Game arena', 'stephino-rpg');?>
            </h4>
        </div>
        <div class="row col-12">
            <?php foreach($userStats as list($userStatValue, $userStatName)):?>
                <div class="col-12 col-lg-4" title="<?php echo number_format($userStatValue);?>">
                    <span class="label">
                        <span>
                            <b><?php echo number_format($userStatValue);?></b> <?php echo esc_html($userStatName);?>
                        </span>
                    </span>
                </div>
            <?php endforeach;?>
        </div>
        <?php if ($userStats[Stephino_Rpg_Renderer_Ajax_Dialog_User::PTF_STAT_CREATED][0] > 0):?>
            <button 
                class="btn btn-info w-100" data-click="dialog" data-click-args="dialogUserArenaList,<?php echo $userId;?>">
                <span><?php echo esc_html__('View games', 'stephino-rpg');?></span>
            </button>
        <?php endif;?>
    </div>
<?php endif;?>
<?php if ($userData[Stephino_Rpg_Db_Table_Users::COL_ID] != Stephino_Rpg_TimeLapse::get()->userId()):?>
    <?php if (Stephino_Rpg_Config::get()->core()->getMessageDailyLimit() > 0 && is_numeric($userData[Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID])): ?>
        <div class="row align-items-center mt-2">
            <div class="col-12">
                <div class="framed">
                    <h5>
                        <span><?php echo esc_html__('Contact', 'stephino-rpg');?></span>
                    </h5>
                    <div class="row no-gutters p-3">
                        <div data-role="label-success" class="d-none mb-2 p-2 w-100 text-center"><?php echo esc_html__('Message sent', 'stephino-rpg');?></div>
                        <div data-role="label-error" class="d-none badge badge-danger mb-2 p-2 w-100 text-center"></div>
                        <input 
                            type="text" 
                            autocomplete="off"
                            name="message-subject" 
                            class="form-control mb-2" 
                            placeholder="<?php echo esc_attr__('Subject', 'stephino-rpg');?> (<?php 
                                    echo sprintf(esc_attr__('max. %d characters', 'stephino-rpg'), Stephino_Rpg_Renderer_Ajax_Action_Message::MAX_MESSAGE_SUBJECT_LENGTH);
                            ?>)" 
                            maxlength="<?php echo Stephino_Rpg_Renderer_Ajax_Action_Message::MAX_MESSAGE_SUBJECT_LENGTH;?>" />
                        <textarea 
                            name="message-content" 
                            class="form-control mb-2" 
                            placeholder="<?php echo esc_html__('Message', 'stephino-rpg');?>) (<?php 
                                    echo sprintf(esc_attr__('max. %d characters', 'stephino-rpg'), Stephino_Rpg_Renderer_Ajax_Action_Message::MAX_MESSAGE_CONTENT_LENGTH);
                               ?>)" 
                            maxlength="<?php echo Stephino_Rpg_Renderer_Ajax_Action_Message::MAX_MESSAGE_CONTENT_LENGTH;?>"></textarea>
                        <button
                            class="btn btn-default w-100"
                            data-click="userSendMessage"
                            data-click-args="<?php echo $userData[Stephino_Rpg_Db_Table_Users::COL_ID];?>">
                            <span><?php echo esc_html__('Send', 'stephino-rpg');?></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif;?>
<?php else:?>
    <div class="row align-items-center mt-2">
        <div class="col-12">
            <div class="framed">
                <div data-name="<?php echo Stephino_Rpg_WordPress::USER_META_NICKNAME;?>" class="row p-3">
                    <div class="col-6 col-lg-4">
                        <label for="input-<?php echo Stephino_Rpg_WordPress::USER_META_NICKNAME;?>">
                            <h4 class="text-left p-0 mb-0"><?php echo esc_html__('Nickname', 'stephino-rpg');?> <span class="text-danger">*</span></h4>
                            <div class="param-desc"><?php echo esc_html__('Choose a nickname (no spaces)', 'stephino-rpg');?></div>
                        </label>
                    </div>
                    <div class="col-12 col-lg-8 param-input">
                        <input 
                            type="text"
                            autocomplete="off"
                            class="form-control" 
                            data-effect="charCounter"
                            maxlength="<?php echo Stephino_Rpg_Renderer_Ajax_Action_Settings::MAX_LENGTH_USER_NAME;?>"
                            data-change="settingsUpdate" 
                            name="<?php echo Stephino_Rpg_WordPress::USER_META_NICKNAME;?>" 
                            id="input-<?php echo Stephino_Rpg_WordPress::USER_META_NICKNAME;?>" 
                            value="<?php echo Stephino_Rpg_Utils_Lingo::getUserName($userData);?>" />
                    </div>
                </div>
                <div data-name="<?php echo Stephino_Rpg_WordPress::USER_META_DESCRIPTION;?>" class="row p-3">
                    <div class="col-6 col-lg-4">
                        <label for="input-<?php echo Stephino_Rpg_WordPress::USER_META_DESCRIPTION;?>">
                            <h4 class="text-left p-0 mb-0"><?php echo esc_html__('Bio', 'stephino-rpg');?></h4>
                            <div class="param-desc"><?php echo esc_html__('Describe yourself (optional)', 'stephino-rpg');?></div>
                        </label>
                    </div>
                    <div class="col-12 col-lg-8 param-input">
                        <textarea 
                            class="form-control" 
                            rows="3" 
                            data-effect="charCounter"
                            maxlength="<?php echo Stephino_Rpg_Renderer_Ajax_Action_Settings::MAX_LENGTH_USER_DESC;?>"
                            data-change="settingsUpdate" 
                            name="<?php echo Stephino_Rpg_WordPress::USER_META_DESCRIPTION;?>" 
                            id="input-<?php echo Stephino_Rpg_WordPress::USER_META_DESCRIPTION;?>"><?php 
                                echo esc_html(
                                    substr(
                                        Stephino_Rpg_Utils_Lingo::getUserDescription($userData), 
                                        0, 
                                        Stephino_Rpg_Renderer_Ajax_Action_Settings::MAX_LENGTH_USER_DESC
                                    )
                                );
                            ?></textarea>
                    </div>
                </div>
                <div data-name="profile" class="row p-3">
                    <div class="col-6 col-lg-4">
                        <label for="input-<?php echo Stephino_Rpg_WordPress::USER_META_PASSWORD;?>">
                            <h4 class="text-left p-0 mb-0"><?php echo esc_html__('Password', 'stephino-rpg');?></h4>
                            <div class="param-desc"><?php echo esc_html__('Set a new password for your account', 'stephino-rpg');?></div>
                        </label>
                    </div>
                    <div class="col-12 col-lg-8 param-input">
                        <input 
                            type="text"
                            autocomplete="off"
                            class="form-control" 
                            name="<?php echo Stephino_Rpg_WordPress::USER_META_PASSWORD;?>" 
                            id="input-<?php echo Stephino_Rpg_WordPress::USER_META_PASSWORD;?>" 
                            value="" />
                        <button class="btn btn-info w-100" data-click="settingsChangePassword">
                            <span><?php echo esc_html__('Change password', 'stephino-rpg');?></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif;?>