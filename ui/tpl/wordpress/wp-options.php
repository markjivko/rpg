<?php
/**
 * Template:Options
 * 
 * @title      Options template
 * @desc       Template for the "Game Mechanics" page
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();
?>
<!--[if lt IE 10]><meta http-equiv="Refresh" content="0; url=<?php echo Stephino_Rpg_Utils_Lingo::escape(get_dashboard_url());?>"><![endif]-->
<!-- stephino-rpg -->
<div class="content-loading">
    <div class="loading-text" style="font-size: 30px; color: #ffffff; line-height: 40px;"><?php echo esc_html__('Loading...', 'stephino-rpg');?></div>
    <div class="left-door">
        <div class="logo-holder" style="opacity: 0;">
            <div class="logo"></div>
            <div class="gradient-holder">
                <div class="gradient"></div>
            </div>
        </div>
    </div>
    <div class="right-door">
        <div class="vertical-bar"></div>
    </div>
</div>
<div class="content" style="display: none;">
    <div role="info-badge">
        <div class="icon"></div>
        <span class="message"></span>
    </div>
    <div class="row" data-role="header">
        <div class="col-12 col-sm-2"></div>
        <div class="col-12 col-sm-10 banner">
            <div class="logo"></div>
            <div class="info">Stephino RPG 
                <span class="version">v. <?php echo Stephino_Rpg::PLUGIN_VERSION;?>
                    <?php if (Stephino_Rpg::get()->isDemo() && !is_super_admin()):?><b>DEMO</b><?php endif;?>
                    <?php if (!Stephino_Rpg::get()->isPro()):?><b><?php echo esc_html__('LOCKED', 'stephino-rpg');?></b><?php endif;?>
                </span>
                <?php echo esc_html__('Game Mechanics', 'stephino-rpg');?>
                <?php if (!Stephino_Rpg::get()->isPro() && strlen(Stephino_Rpg::PLUGIN_URL_PRO)):?>
                    <a class="btn btn-default ml-2" href="<?php echo esc_url(Stephino_Rpg::PLUGIN_URL_PRO);?>"><?php echo esc_html__('Unlock Game', 'stephino-rpg');?> &#x1F513;</a>
                <?php endif;?>
            </div>
            <div class="glow"></div>
        </div>
    </div>
    <div class="row" data-role="content">
        <div class="col-12 col-sm-2">
            <ul class="nav nav-pills flex-column" role="tablist"></ul>
        </div>
        <div class="col-12 col-sm-10">
            <div class="tab-content" data-role="tab-panes"></div>
        </div>
    </div>
</div>
<!-- /stephino-rpg -->