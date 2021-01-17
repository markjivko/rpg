<?php
/**
 * Template:Dialog:Settings
 * 
 * @title      Settings dialog
 * @desc       Template for the Settings dialog
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

?>
<?php if (Stephino_Rpg::get()->isPro() || is_super_admin()):?>
    <div data-name="<?php echo Stephino_Rpg_Cache_User::KEY_VOL_MUSIC;?>" class="row">
        <div class="col-6 col-lg-4">
            <label for="input_<?php echo Stephino_Rpg_Cache_User::KEY_VOL_MUSIC;?>">
                <h4 class="text-left p-0 mb-0"><?php echo esc_html__('Music', 'stephino-rpg');?></h4>
                <div class="param-desc"><?php echo esc_html__('Set the volume for the game soundtrack', 'stephino-rpg');?></div>
            </label>
        </div>
        <div class="col-12 col-lg-8 param-input">
            <?php if (Stephino_Rpg::get()->isPro()):?>
                <input 
                    type="range" 
                    name="<?php echo Stephino_Rpg_Cache_User::KEY_VOL_MUSIC;?>" 
                    id="input_<?php echo Stephino_Rpg_Cache_User::KEY_VOL_MUSIC;?>"
                    value="<?php echo $gameSettings[Stephino_Rpg_Cache_User::KEY_VOL_MUSIC];?>" 
                    min="0" 
                    max="100" 
                    data-change="settingsUpdate" />
            <?php else:?>
                <span>
                    &#x1F512; <?php 
                        echo sprintf(
                            esc_html__('Music, Ambience, Sounds (%s) and UI sounds are unlocked with the PRO version', 'stephino-rpg'),
                            Stephino_Rpg_Config::get()->core()->getConfigBuildingsName(true)
                        );
                    ?>
                </span>
            <?php endif;?>
        </div>
    </div>
<?php endif;?>
<?php if(Stephino_Rpg::get()->isPro()):?>
    <div data-name="<?php echo Stephino_Rpg_Cache_User::KEY_VOL_BKG;?>" class="row">
        <div class="col-6 col-lg-4">
            <label for="input_<?php echo Stephino_Rpg_Cache_User::KEY_VOL_BKG;?>">
                <h4 class="text-left p-0 mb-0"><?php echo esc_html__('Ambience', 'stephino-rpg');?></h4>
                <div class="param-desc"><?php echo esc_html__('Set the volume for the environment sounds', 'stephino-rpg');?></div>
            </label>
        </div>
        <div class="col-12 col-lg-8 param-input">
            <input 
                type="range" 
                name="<?php echo Stephino_Rpg_Cache_User::KEY_VOL_BKG;?>" 
                id="input_<?php echo Stephino_Rpg_Cache_User::KEY_VOL_BKG;?>"
                value="<?php echo $gameSettings[Stephino_Rpg_Cache_User::KEY_VOL_BKG];?>" 
                min="0" 
                max="100" 
                data-change="settingsUpdate" />
        </div>
    </div>
    <div data-name="<?php echo Stephino_Rpg_Cache_User::KEY_VOL_CELLS;?>" class="row">
        <div class="col-6 col-lg-4">
            <label for="input_<?php echo Stephino_Rpg_Cache_User::KEY_VOL_CELLS;?>">
                <h4 class="text-left p-0 mb-0"><?php 
                    echo sprintf(
                        esc_html__('Sounds (%s)', 'stephino-rpg'),
                        Stephino_Rpg_Config::get()->core()->getConfigBuildingsName(true)
                    );
                ?></h4>
                <div class="param-desc"><?php 
                    echo sprintf(
                        esc_html__('Set the volume for the sound effects - %s', 'stephino-rpg'),
                        Stephino_Rpg_Config::get()->core()->getConfigBuildingsName(true)
                    );
                ?></div>
            </label>
        </div>
        <div class="col-12 col-lg-8 param-input">
            <input 
                type="range" 
                name="<?php echo Stephino_Rpg_Cache_User::KEY_VOL_CELLS;?>" 
                id="input_<?php echo Stephino_Rpg_Cache_User::KEY_VOL_CELLS;?>"
                value="<?php echo $gameSettings[Stephino_Rpg_Cache_User::KEY_VOL_CELLS];?>" 
                min="0" 
                max="100" 
                data-change="settingsUpdate" />
        </div>
    </div>
    <div data-name="<?php echo Stephino_Rpg_Cache_User::KEY_VOL_EVENTS;?>" class="row">
        <div class="col-6 col-lg-4">
            <label for="input_<?php echo Stephino_Rpg_Cache_User::KEY_VOL_EVENTS;?>">
                <h4 class="text-left p-0 mb-0"><?php echo esc_html__('UI sounds', 'stephino-rpg');?></h4>
                <div class="param-desc"><?php echo esc_html__('Set the volume for taps, clicks and other interactions', 'stephino-rpg');?></div>
            </label>
        </div>
        <div class="col-12 col-lg-8 param-input">
            <input 
                type="range" 
                name="<?php echo Stephino_Rpg_Cache_User::KEY_VOL_EVENTS;?>" 
                id="input_<?php echo Stephino_Rpg_Cache_User::KEY_VOL_EVENTS;?>"
                value="<?php echo $gameSettings[Stephino_Rpg_Cache_User::KEY_VOL_EVENTS];?>" 
                min="0" 
                max="100" 
                data-change="settingsUpdate" />
        </div>
    </div>
<?php endif;?>
<div data-name="more" class="row">
    <div class="col-6 col-lg-4">
        <label for="input-more">
            <h4 class="text-left p-0 mb-0"><?php echo esc_html__('More actions', 'stephino-rpg');?></h4>
        </label>
    </div>
    <div class="col-12 col-lg-8 param-input">
        <div data-effect="pwaInstall"><?php echo esc_html__('Install app', 'stephino-rpg');?></div>
        <button class="btn btn-info w-100" data-click="helpDialog" data-click-args="<?php echo Stephino_Rpg_Config_Core::KEY;?>,0">
            <span><?php echo esc_html__('Help', 'stephino-rpg');?></span>
        </button>
        <?php if (strlen(Stephino_Rpg::PLUGIN_URL_DISCORD)):?>
            <a class="btn btn-info w-100" target="_blank" href="<?php echo esc_url(Stephino_Rpg::PLUGIN_URL_DISCORD);?>">
                <span><?php echo esc_html__('Feedback', 'stephino-rpg');?></span>
            </a>
        <?php endif;?>
        <button class="btn btn-info w-100" data-click="userViewProfile" data-click-args="<?php echo Stephino_Rpg_TimeLapse::get()->userId();?>">
            <span><?php echo esc_html__('Profile', 'stephino-rpg');?></span>
        </button>
        <button class="btn btn-info w-100" data-click="settingsViewCredits">
            <span><?php echo esc_html__('Credits', 'stephino-rpg');?></span>
        </button>
        <button class="btn btn-info w-100" data-click="settingsLogOut">
            <span><?php echo esc_html__('Log Out', 'stephino-rpg');?></span>
        </button>
        <?php if (!is_super_admin()):?>
            <button class="btn btn-danger w-100" data-click="settingsDeleteAccount">
                <span><?php echo esc_html__('Delete account', 'stephino-rpg');?></span>
            </button>
        <?php endif;?>
    </div>
</div>
<?php if (Stephino_Rpg::get()->isDemo() || is_super_admin()): ?>
    <div data-name="admin" class="row">
        <hr />
        <div class="col-6 col-lg-4">
            <label for="input-admin">
                <h4 class="text-left p-0 mb-0">
                    <?php echo esc_html__('Admin', 'stephino-rpg');?>
                    <?php if (Stephino_Rpg::get()->isDemo() && !is_super_admin()):?>
                        (<?php echo esc_html__('demo', 'stephino-rpg');?>)
                    <?php endif;?>
                </h4>
                <div class="param-desc">Stephino RPG <?php echo esc_html__('Game Mechanics', 'stephino-rpg');?></div>
            </label>
        </div>
        <div class="col-12 col-lg-8 param-input">
            <a class="btn btn-info w-100" target="_blank" href="<?php echo Stephino_Rpg_Utils_Lingo::escape(Stephino_Rpg_Utils_Media::getAdminUrl() . '-' . Stephino_Rpg_Renderer_Html::TEMPLATE_OPTIONS);?>">
                <span><?php echo esc_html__('Game Mechanics', 'stephino-rpg');?></span>
            </a>
        </div>
    </div>
    <?php if(Stephino_Rpg_Config::get()->core()->getConsoleEnabled()):?>
        <div data-name="admin-console" class="row">
            <div class="col-6 col-lg-4">
                <label for="input-admin-console">
                    <h4 class="text-left p-0 mb-0">
                        <span 
                            data-effect="help"
                            data-effect-args="<?php echo Stephino_Rpg_Config_Core::KEY;?>,<?php echo Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_CONSOLE;?>">
                            <?php echo esc_html__('Console', 'stephino-rpg');?>
                            <?php if (Stephino_Rpg::get()->isDemo() && !is_super_admin()):?>
                                (<?php echo esc_html__('demo', 'stephino-rpg');?>)
                            <?php endif;?>
                        </span>
                    </h4>
                    <div class="param-desc"><?php echo sprintf(esc_html__('Show the in-game console %s', 'stephino-rpg'), '(<b>Alt+Ctrl+C</b>)');?></div>
                </label>
            </div>
            <div class="col-12 col-lg-8 param-input">
                <button class="btn btn-info w-100" data-click="settingsShowConsole">
                    <span><?php echo esc_html__('Command Line Interface', 'stephino-rpg');?></span>
                </button>
            </div>
        </div>
    <?php endif;?>
    <?php if (!Stephino_Rpg::get()->isPro() && is_super_admin()): ?>
        <div data-name="admin-buy" class="row">
            <div class="col-6 col-lg-4">
                <label for="input-admin-buy">
                    <h4 class="text-left p-0 mb-0"><?php echo esc_html__('Unlock Game', 'stephino-rpg');?></h4>
                    <div class="param-desc"><?php 
                        echo sprintf(
                            esc_html__('Buy %s to unlock the Game Mechanics, enable PayPal micro-transactions and more!', 'stephino-rpg'),
                            '<b>Stephino RPG Pro</b>'
                        );
                    ?></div>
                </label>
            </div>
            <div class="col-12 col-lg-8 param-input">
                <?php if (strlen(Stephino_Rpg::PLUGIN_URL_PRO)):?>
                    <a class="btn btn-warning w-100" target="_blank" href="<?php echo esc_url(Stephino_Rpg::PLUGIN_URL_PRO);?>">
                        <span><?php echo esc_html__('Unlock Game', 'stephino-rpg');?></span>
                    </a>
                <?php endif;?>
            </div>
        </div>
    <?php endif;?>
<?php endif;?>
<?php if (!is_super_admin() && Stephino_Rpg_Config::get()->core()->getShowWpLink()): ?>
    <div data-name="admin-download" class="row">
        <hr />
        <div class="col-6 col-lg-4">
            <label for="input-admin-download">
                <h4 class="text-left p-0 mb-0"><?php echo esc_html__('Free Install', 'stephino-rpg');?></h4>
                <div class="param-desc"><?php echo esc_html__('Install this WordPress plugin for free and host your own game!', 'stephino-rpg');?></div>
            </label>
        </div>
        <div class="col-12 col-lg-8 param-input">
            <a class="btn btn-warning w-100" target="_blank" href="<?php echo esc_url(Stephino_Rpg::PLUGIN_URL_WORDPRESS);?>">
                <span><?php echo esc_html__('Free Install', 'stephino-rpg');?></span>
            </a>
        </div>
    </div>
<?php endif; ?>