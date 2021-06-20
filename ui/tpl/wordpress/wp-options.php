<?php
/**
 * Template:Options
 * 
 * @title      Options template
 * @desc       Template for the "Game Mechanics" page
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();
?>
<!--[if lt IE 10]><meta http-equiv="refresh" content="0; url=<?php echo esc_attr(get_dashboard_url());?>"><![endif]-->
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
        <div class="col-12 col-sm-10 banner" lang="<?php echo Stephino_Rpg_Config::lang();?>">
            <div class="logo"></div>
            <div class="info">
                <?php echo Stephino_Rpg::PLUGIN_NAME;?> 
                <span class="version">v. <?php echo Stephino_Rpg::PLUGIN_VERSION;?>
                    <?php if (Stephino_Rpg::get()->isDemo() && !Stephino_Rpg_Cache_User::get()->isElevated()):?><b>DEMO</b><?php endif;?>
                </span>
                <?php echo Stephino_Rpg_Utils_Lingo::getOptionsLabel(true);?>
                <?php if (!Stephino_Rpg::get()->isPro() && strlen(Stephino_Rpg::PLUGIN_URL_PRO)):?>
                    <a class="btn btn-default ml-2" href="<?php echo esc_url(Stephino_Rpg::PLUGIN_URL_PRO);?>"><?php echo esc_html__('Unlock Game', 'stephino-rpg');?> &#x1F513;</a>
                <?php endif;?>
            </div>
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <span class="nav-link<?php if (Stephino_Rpg_Utils_Lingo::LANG_EN == Stephino_Rpg_Config::lang(true)):?> active<?php endif;?>" 
                        data-lang="<?php echo Stephino_Rpg_Utils_Lingo::LANG_EN;?>">
                        <?php echo esc_html__('Game Mechanics', 'stephino-rpg');?>
                    </span>
                </li>
                <?php if (count($allowedLanguages = Stephino_Rpg_Utils_Lingo::getLanguages()) > 1): ?>
                    <li class="nav-item dropdown">
                        <span class="nav-link dropdown-toggle<?php if (Stephino_Rpg_Utils_Lingo::LANG_EN != Stephino_Rpg_Config::lang(true)):?> active<?php endif;?>" 
                            data-toggle="dropdown" 
                            role="button" 
                            aria-haspopup="true" 
                            aria-expanded="false">
                            <?php echo esc_html__('Translations', 'stephino-rpg');?>
                        </span>
                        <div class="dropdown-menu">
                            <?php 
                                foreach ($allowedLanguages as $langKey => $langValue):
                                    if (Stephino_Rpg_Utils_Lingo::LANG_EN == $langKey) {
                                        continue;
                                    }
                            ?>
                                <span data-lang="<?php echo esc_attr($langKey);?>" class="dropdown-item<?php if ($langKey == Stephino_Rpg_Config::lang(true)):?> active<?php endif;?>">
                                    <?php echo esc_html($langValue);?>
                                </span>
                            <?php endforeach;?>
                        </div>
                    </li>
                <?php endif;?>
            </ul>
            <div class="glow"><span></span></div>
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