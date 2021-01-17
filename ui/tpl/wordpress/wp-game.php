<?php
/**
 * Template:Game
 * 
 * @title      Game template
 * @desc       Template for the game pages
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();
?><!doctype html>
<html <?php language_attributes(); ?>>
    <head>
        <?php wp_head(); ?>
        <link rel="shortcut icon" type="image/png" href="<?php echo esc_attr(Stephino_Rpg_Utils_Media::getPluginsUrl() . '/ui/img/icon.png'); ?>" />
        <link rel="manifest" id="stephino_rpg_manifest" />
        <link rel="apple-touch-icon" href="<?php echo esc_attr(Stephino_Rpg_Utils_Media::getPluginsUrl() . '/themes/' . Stephino_Rpg_Config::get()->core()->getTheme() . '/img/ui/192.png'); ?>">
        <!--[if lt IE 10]><meta http-equiv="Refresh" content="0; url=<?php echo esc_attr(get_dashboard_url());?>"><![endif]-->
        <meta charset="utf-8" />
        <meta http-equiv="x-ua-compatible" content="IE=edge">
        <meta name="viewport" content="initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, shrink-to-fit=no" />
        <meta name="mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="theme-color" content="#e0d3a5" />
        <meta name="author" content="Mark Jivko" />
        <meta name="copyright" content="(c) 2020, Stephino" />
        <meta name="generator" content="<?php echo Stephino_Rpg::PLUGIN_SLUG;?>" />
        <meta name="version" content="<?php echo Stephino_Rpg::PLUGIN_VERSION;?>" />
        <meta name="description" content="<?php echo esc_html__('The first-ever RPG developed for WordPress', 'stephino-rpg');?>" />
    </head>
    <body class="view-<?php echo $viewName;?>" oncontextmenu="return false">
        <!-- Info Badge -->
        <div role="info-badge">
            <div class="icon"></div>
            <span class="message"></span>
        </div>
        
        <!-- Loading layer -->
        <div class="loading">
            <div class="loading-text"><?php 
                echo (Stephino_Rpg_Renderer_Ajax::VIEW_PWA == $viewName)
                    ? esc_html__('Offline', 'stephino-rpg')
                    : esc_html__('Loading...', 'stephino-rpg');
            ?></div>
        </div>
        
        <!-- Landscape layer -->
        <div data-role="info-landscape">
            <div></div>
            <span><?php echo esc_html__('Please rotate your device to landscape', 'stephino-rpg');?></span>
        </div>
        
        <!-- Top bar -->
<?php if (Stephino_Rpg_Renderer_Ajax::VIEW_PWA != $viewName):?>
        <div class="top-bar container-fluid">
            <div class="row no-gutters">
<?php if (Stephino_Rpg_Renderer_Ajax::VIEW_CITY === $viewName):?>
                <div class="col-9 col-lg-10 bar-resources">
                    <?php if (Stephino_Rpg_Config::get()->core()->getShowReloadButton()):?>
                        <div data-role="refresh" title="<?php echo esc_html__('Refresh', 'stephino-rpg');?>"></div>
                    <?php endif;?>
                    <div data-role="fullscreen" title="<?php echo esc_html__('Fullscreen', 'stephino-rpg');?>"></div>
                    <span data-role="title"><?php echo Stephino_Rpg_Utils_Lingo::getCityName($cityData);?></span>
                    <div class="row no-gutters">
                        <div class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_ALPHA;?>"></div>
                        <div class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_BETA;?>"></div>
                        <div class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_GAMMA;?>"></div>
                        <div class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_EXTRA_1;?>"></div>
                        <div class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_EXTRA_2;?>"></div>
                    </div>
                </div>
                <div class="col-3 col-lg-2 bar-settings text-right">
                    <div data-role="convoys" title="<?php echo esc_html__('Convoys', 'stephino-rpg');?>"><span></span></div>
                    <div data-role="entities" title="<?php echo esc_html__('Garrison', 'stephino-rpg');?>"><span>0</span></div>
                    <div data-role="settings" title="<?php echo esc_html__('Settings', 'stephino-rpg');?>"></div>
                </div>
<?php else:?>
                <div class="col">
                    <?php if (Stephino_Rpg_Config::get()->core()->getShowReloadButton()):?>
                        <div data-role="refresh" title="<?php echo esc_html__('Refresh', 'stephino-rpg');?>"></div>
                    <?php endif;?>
                    <div data-role="fullscreen" title="<?php echo esc_html__('Fullscreen', 'stephino-rpg');?>"></div>
                    <span data-role="title"><?php 
                        echo (Stephino_Rpg_Renderer_Ajax::VIEW_ISLAND === $viewName)
                            ? $islandData[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_NAME]
                            : Stephino_Rpg_Config::get()->core()->getName(true);
                    ?></span>
                </div>
                <div class="col bar-settings text-right">
                    <div data-role="convoys" title="<?php echo esc_html__('Convoys', 'stephino-rpg');?>"><span></span></div>
                    <div data-role="settings" title="<?php echo esc_html__('Settings', 'stephino-rpg');?>"></div>
                </div>
<?php endif;?>
            </div>
        </div>

        <!-- Map -->
        <div data-role="map-holder" <?php echo $atts; ?>>
            <div data-role="map" class="map"></div>
        </div>

        <!-- Bottom Bar -->
        <div class="bottom-bar container-fluid">
            <div class="row">
                <div class="col bar-info">
                    <?php if (count(Stephino_Rpg_Config::get()->premiumModifiers()->getAll())):?>
                        <div data-role="premium-modifiers" data-html="true" title="&#11088; <?php echo esc_html__('Modifiers', 'stephino-rpg');?>"><span></span></div>
                    <?php endif;?>
                    <span class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_GEM;?>"></span>
                    <span class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_GOLD;?>"></span>
                    <span class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_RESEARCH;?>"></span>
                </div>
                <div class="col bar-dome">
                    <div class="dome"></div>
                </div>
                <div class="col bar-extra">
                    <?php if (Stephino_Rpg_Config::get()->core()->getPtfEnabled()):?>
                        <div data-role="arena" data-btn-dialog="dialogUserArenaList" title="<?php echo esc_html__('Game arena', 'stephino-rpg');?>"></div>
                    <?php endif;?>
                    <div data-role="leader-board" data-btn-dialog="dialogUserLeaderBoard" title="<?php echo esc_html__('Leader board', 'stephino-rpg');?>"></div>
                    <?php if (Stephino_Rpg_Renderer_Ajax::VIEW_CITY === $viewName):?>
                        <div data-role="queues" title="<?php echo esc_html__('Queues', 'stephino-rpg');?>"><span>0</span></div>
                    <?php endif;?>
                    <?php if (Stephino_Rpg_Config::get()->core()->getChatroom()):?>
                        <div data-role="chat-room-toggle" title="<?php echo esc_html__('Chat Room', 'stephino-rpg');?>"></div>
                    <?php endif;?>
                    <div data-role="messages-holder-toggle" title="<?php echo esc_html__('Messages', 'stephino-rpg');?>"><span class="messages">M</span></div>
                    <div data-role="messages-holder"></div>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <div id="modal-template" class="modal" role="dialog" data-backdrop="static">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header no-gutters">
                        <div class="modal-header-before"></div>
                        <div class="modal-header-center col row no-gutters">
                            <div class="col modal-header-pad-left"></div>
                            <div class="col-10 col-sm-8 col-lg-4 modal-header-pad">
                                <div class="container d-flex h-100">
                                    <div class="row no-gutters w-100 justify-content-center align-self-center text-center">
                                        <h5 class="modal-title"></h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col modal-header-pad-right"></div>
                        </div>
                        <div class="modal-header-after">
                            <div data-role="nav-back"><span>&#8249;</span></div>
                            <button type="button" class="close" data-dismiss="modal">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    </div>
                    <div class="modal-body"></div>
                    <div class="modal-footer no-gutters">
                        <div class="modal-footer-before"></div>
                        <div class="modal-footer-center col row no-gutters">
                            <div class="col modal-footer-pad-left"></div>
                            <div class="col-10 col-sm-8 col-lg-4 modal-footer-pad">
                                <a target="_blank" href="<?php echo esc_url(Stephino_Rpg::PLUGIN_URL_WORDPRESS);?>">
                                    v. <?php echo Stephino_Rpg_Utils_Media::getPwaVersion(false, false);?>
                                </a>
                            </div>
                            <div class="col modal-footer-pad-right"></div>
                        </div>
                        <div class="modal-footer-after"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tutorial -->
        <div data-role="tutorial-dialog" class="collapsed col-11 col-sm-10 col-md-6 col-lg-4 col-xl-3">
            <div class="icon"></div>
            <div class="steps" data-placement="bottom">
                <div class="steps-bar"></div>
            </div>
            <div class="title"></div>
            <div class="content"></div>
            <div class="action-area">
                <button class="btn btn-default" data-role="next"><span><?php echo esc_html__('Next', 'stephino-rpg');?></span></button>
                <button class="btn btn-default" data-role="skip"><span><?php echo esc_html__('Skip tutorial', 'stephino-rpg');?></span></button>
            </div>
            <div data-role="toggle"></div>
        </div>
        <div data-role="tutorial-marker"><div><div></div></div></div>
<?php endif;?>
    </body>
</html>