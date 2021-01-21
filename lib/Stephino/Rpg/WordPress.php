<?php
/**
 * Stephino_Rpg_WordPress
 * 
 * @title      WordPress integration
 * @desc       Handle the plugin's WordPress actions/hooks
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_WordPress {
    
    const USER_META_NICKNAME    = 'nickname';
    const USER_META_DESCRIPTION = 'description';
    const USER_META_PASSWORD    = 'password';
    
    /**
     * Register the Cron actions for Robots
     */
    public static function registerRobotsCron() {
        // Init action
        add_action('init', function() {
            global $pagenow;

            do {
                // Never run the robots cron in the Game area
                if ('admin.php' === $pagenow && Stephino_Rpg::PLUGIN_SLUG === Stephino_Rpg_Utils_Sanitizer::getPage()) {
                    break;
                } 
                
                // Public pages (authenticated or guest)
                if (!is_admin() && !Stephino_Rpg::PLUGIN_CRON_PUBLIC) {
                    break;
                }

                // Run the time-lapse for robots
                Stephino_Rpg_Task_Cron::robots();
                    
                // Store hourly stats
                Stephino_Rpg_Task_Cron::statistics();
            } while(false);
        });
    }
    
    /**
     * Register the plugin pages plus:
     * <ul>
     *     <li>i18n</li>
     *     <li>Nickname and Bio sanitization</li>
     *     <li>Login Redirect</li>
     *     <li>Registration Auto Login</li>
     *     <li>Admin Menu</li>
     *     <li>Remove other plugin notices</li>
     *     <li>Plugin Links</li>
     *     <li>Login Form</li>
     * </ul>
     */
    public static function registerPages() {
        add_action('plugins_loaded', function() {
            load_plugin_textdomain('stephino-rpg', false, 'stephino-rpg/languages');
        });
        
        // Sanitize the nick-name and bio
        add_filter('sanitize_user_meta_nickname', function($metaValue) {return Stephino_Rpg_Utils_Lingo::cleanup(preg_replace('%\W+%i', '', $metaValue));}, 10, 5);
        add_filter('sanitize_user_meta_description', function($metaValue) {return Stephino_Rpg_Utils_Lingo::cleanup($metaValue);}, 10, 5);
        
        // Login Redirect
        add_filter('login_redirect', function ($redirectTo, $request, $user) {
            if (null !== $user && property_exists($user, 'roles') && is_array($user->roles)) {
                if (in_array('subscriber', $user->roles)) {
                    return Stephino_Rpg_Utils_Media::getAdminUrl(true, false);
                }
            }

            // Default URL
            return $redirectTo;
        }, 10, 3);
        
        // Register Auto Login
        add_action('user_register', function($userId) {
            if (Stephino_Rpg_Utils_Sanitizer::getLogin() && null !== $redirectUrl = Stephino_Rpg_Utils_Sanitizer::getRedirectTo()) {
                // Log in
                wp_set_current_user($userId);
                wp_set_auth_cookie($userId);
                
                // Redirect the user
                wp_new_user_notification($userId, null, 'both');
                wp_redirect($redirectUrl);
                exit();
            }
        });

        // Admin Menu
        add_action('admin_menu', function () {
            self::_addMenu();
        });
        
        // Admin Menu - Multisite
        add_action('network_admin_menu', function () {
            if (is_multisite() && (is_plugin_active_for_network(Stephino_Rpg::PLUGIN_SLUG) || is_network_only_plugin(Stephino_Rpg::PLUGIN_SLUG))) {
                self::_addMenu();
            }
        });
        
        // Admin Bar Menu
        add_action('wp_before_admin_bar_render', function () {
            global $wp_admin_bar;
            if (is_admin_bar_showing()) {
                // Game
                $wp_admin_bar->add_node(array(
                    'id'    => Stephino_Rpg::PLUGIN_SLUG,
                    'title' => '<img class="ab-icon" style="margin-top: 2px;" src="' . Stephino_Rpg_Utils_Media::getPluginsUrl() . '/ui/img/icon.svg"/>' 
                        . esc_html__('Play', 'stephino-rpg') . ' <span>' . esc_html(Stephino_Rpg_Utils_Lingo::getGameName()) . '</span>',
                    'href'  => Stephino_Rpg_Utils_Media::getAdminUrl(),
                ));

                if (is_super_admin()) {
                    // Dashboard
                    $wp_admin_bar->add_node(array(
                        'id'     => Stephino_Rpg::PLUGIN_SLUG . '-' . Stephino_Rpg_Renderer_Html::TEMPLATE_DASHBOARD,
                        'title'  => esc_html__('Dashboard', 'stephino-rpg'),
                        'href'   => Stephino_Rpg_Utils_Media::getAdminUrl() . '-' . Stephino_Rpg_Renderer_Html::TEMPLATE_DASHBOARD,
                        'parent' => Stephino_Rpg::PLUGIN_SLUG,
                    ));
                    
                    // Game Mechanics
                    $wp_admin_bar->add_node(array(
                        'id'     => Stephino_Rpg::PLUGIN_SLUG . '-' . Stephino_Rpg_Renderer_Html::TEMPLATE_OPTIONS,
                        'title'  => esc_html__('Game Mechanics', 'stephino-rpg') . (Stephino_Rpg::get()->isPro() ? '' : ' &#x1F512;'),
                        'href'   => Stephino_Rpg_Utils_Media::getAdminUrl() . '-' . Stephino_Rpg_Renderer_Html::TEMPLATE_OPTIONS,
                        'parent' => Stephino_Rpg::PLUGIN_SLUG,
                    ));
                }
            }
        }, 999);
        
        // Notices
        add_action('admin_notices', function() {
            self::_handleNotice();
        });
        
        // Plugin links
        add_filter('plugin_action_links_stephino-rpg/stephino-rpg.php', function($links) {
            do {
                // Cannot install plugins or plugin unlocked
                if (!current_user_can('install_plugins') || Stephino_Rpg::get()->isPro()) {
                    break;
                }
                
                if (strlen(Stephino_Rpg::PLUGIN_URL_PRO)) {
                    // Prepare the unlock text
                    $unlockText = class_exists('Stephino_Rpg_Pro') && version_compare(Stephino_Rpg_Pro::PLUGIN_VERSION, Stephino_Rpg::PLUGIN_VERSION_PRO) < 0
                        ? sprintf(esc_html__('Upgrade Pro to version %s', 'stephino-rpg'), Stephino_Rpg::PLUGIN_VERSION_PRO . '+')
                        : esc_html__('Unlock Game', 'stephino-rpg');

                    // Append the install URL
                    $links[] =  '<a target="_blank" href="' . esc_url(Stephino_Rpg::PLUGIN_URL_PRO) . '"><b>' . esc_html($unlockText) . '</b></a>';
                }
            } while(false);
            
            return $links;
        }, 10, 5);
        
        // Login form
        add_action('login_enqueue_scripts', function() {
            if (Stephino_Rpg_Utils_Sanitizer::getLogin()) {
                wp_enqueue_style(
                    Stephino_Rpg::PLUGIN_SLUG . '-wp-login', 
                    Stephino_Rpg_Utils_Media::getPluginsUrl() . '/ui/css/wordpress/wp-login.css',
                    array(),
                    Stephino_Rpg::PLUGIN_VERSION
                );
                wp_enqueue_script(
                    Stephino_Rpg::PLUGIN_SLUG . '-wp-login', 
                    Stephino_Rpg_Utils_Media::getPluginsUrl() . '/ui/js/wordpress/wp-login.js',
                    array('jquery'),
                    Stephino_Rpg::PLUGIN_VERSION
                );
                wp_localize_script(
                    Stephino_Rpg::PLUGIN_SLUG . '-wp-login', 
                    Stephino_Rpg::PLUGIN_VARNAME . '_data', array(
                        'wp_url'    => esc_url(Stephino_Rpg::PLUGIN_URL_WORDPRESS),
                        'res_url'   => Stephino_Rpg_Utils_Media::getPluginsUrl(),
                        'ajax_url'  => Stephino_Rpg_Utils_Media::getAdminUrl(true, false),
                        'theme_url' => Stephino_Rpg_Utils_Media::getPluginsUrl() . '/themes/' . Stephino_Rpg_Config::get()->core()->getTheme(),
                        'game_url'  => Stephino_Rpg_Utils_Media::getAdminUrl(),
                        'game_ver'  => Stephino_Rpg_Utils_Media::getPwaVersion(false, false),
                        'game_desc' => Stephino_Rpg_Config::get()->core()->getDescription(),
                        'game_name' => Stephino_Rpg_Config::get()->core()->getName(true),
                        'app_name'  => Stephino_Rpg_Utils_Lingo::getGameName(),
                        'is_demo'   => Stephino_Rpg::get()->isDemo(),
                        'is_pro'    => Stephino_Rpg::get()->isPro(),
                        'i18n'      => array(
                            'label_log_in' => esc_html__('Log In', 'stephino-rpg'),
                            'label_or'     => strtoupper(esc_html__('or', 'stephino-rpg')),
                        )
                    )
                );
            }
        }, 999);
    }
    
    /**
     * Remove other plugin's scripts and styles
     * 
     * @global string     $pagenow    Current page
     * @global WP_Scripts $wp_scripts Scripts
     * @global WP_Styles  $wp_styles  Styles
     */
    public static function _dequeueOtherScripts() {
        // check if you are on the certain page
        global $pagenow, $wp_scripts, $wp_styles;

        // Nothing else here
        if ('admin.php' === $pagenow) {
            if (Stephino_Rpg::PLUGIN_SLUG === Stephino_Rpg_Utils_Sanitizer::getPage()) {
                // Prepare the regex
                $inclusionRegEx = '%\/' . preg_quote(Stephino_Rpg::PLUGIN_SLUG) . '\/ui\/%i';
                
                // Go through the scripts
                foreach($wp_scripts->queue as $handle) {
                    if (isset($wp_scripts->registered[$handle]) && is_string($wp_scripts->registered[$handle]->src) && !preg_match($inclusionRegEx, $wp_scripts->registered[$handle]->src)) {
                        unset($wp_scripts->registered[$handle]);
                    }
                }
                
                // Go throught the styles
                foreach($wp_styles->queue as $handle) {
                    if (isset($wp_styles->registered[$handle]) && is_string($wp_styles->registered[$handle]->src) && !preg_match($inclusionRegEx, $wp_styles->registered[$handle]->src)) {
                        unset($wp_styles->registered[$handle]);
                    }
                }
            }
        }
        
        // Remove Visual Composer templates
        remove_action('admin_footer', 'vc_loop_include_templates');
        remove_action('admin_footer', 'vc_options_include_templates');
    }
    
    /**
     * Register shortcodes/Gutenberg blocks/widgets
     */
    public static function registerWidgets() {
        add_action('init', function() {
            /**
             * Embedded game
             */
            $gameRender = function() {
                return '<div data-role="stephino-rpg-embed" style="position:relative;overflow:hidden;">'
                    . '<svg viewBox="0 0 1920 1000" style="display:block;width:100%;position:relative;z-index:0;"></svg>'
                    . sprintf(
                        '<%1$s src="%2$s" allowfullscreen="true" referrerpolicy="same-origin" style="%3$s"></%1$s>',
                            'iframe', 
                            esc_attr(Stephino_Rpg_Utils_Media::getAdminUrl(true, false)),
                            'position:absolute;top:0;left:0;width:100%;height:100%;border:none;z-index:1;background:#23282d;'
                    )
                    . (
                        Stephino_Rpg_Config::get()->core()->getShowWpLink()
                            ? sprintf(
                                '<%1$s href="%2$s" rel="external" style="%3$s">%4$s</%1$s>',
                                    'a', 
                                    esc_attr(Stephino_Rpg::PLUGIN_URL_WORDPRESS),
                                    'position:absolute;z-index:0;',
                                    esc_html('WordPress Game - Stephino RPG')
                            )
                            : ''
                    )
                . '</div>';
            };

            // Register the Gutenberg Script
            wp_register_script(
                'gutenberg-stephino-rpg', 
                Stephino_Rpg_Utils_Media::getPluginsUrl() . '/ui/js/wordpress/wp-gutenberg.js', 
                array('wp-blocks', 'wp-element', 'wp-editor', 'wp-i18n')
            );
            wp_set_script_translations('gutenberg-stephino-rpg', 'stephino-rpg');

            // Define the Gutenberg Block
            register_block_type('stephino-rpg/game', array(
                'editor_script'   => 'gutenberg-stephino-rpg',
                'render_callback' => $gameRender
            ));

            // Define the shortcode
            add_shortcode('stephino-rpg', $gameRender);
        });
    }
    
    /**
     * Perform changes to the page meta data
     */
    public static function metaChages() {
        global $pagenow;
        if ('admin.php' === $pagenow) {
            switch (Stephino_Rpg_Utils_Sanitizer::getPage()) {
                // Game
                case Stephino_Rpg::PLUGIN_SLUG:
                    // Game Metadata
                    add_action('admin_head', function() {
                        // Remove all notices
                        remove_all_actions('admin_notices');
                        
                        // Prepare the PWA Manifest
                        echo '<link rel="manifest" id="stephino_rpg_manifest" />';
                    }, 1);

                    // Game Title
                    add_filter('admin_title', function($a, $b){
                        return Stephino_Rpg_Utils_Lingo::getGameName();
                    }, 10, 2);
                    break;

                // Options
                case Stephino_Rpg::PLUGIN_SLUG . '-' . Stephino_Rpg_Renderer_Html::TEMPLATE_OPTIONS:
                case Stephino_Rpg::PLUGIN_SLUG . '-' . Stephino_Rpg_Renderer_Html::TEMPLATE_DASHBOARD:
                    // Metadata
                    add_action('admin_head', function() {
                        echo '<link rel="shortcut icon" type="image/png" href="' . esc_attr(Stephino_Rpg_Utils_Media::getPluginsUrl() . '/ui/img/icon.png') . '" />';
                    });
                    break;
            }
        }
    }
    
    /**
     * Register the AJAX handler
     */
    public static function registerAjax() {
        // AJAX form
        add_action('wp_ajax_nopriv_' . Stephino_Rpg::PLUGIN_VARNAME, array(Stephino_Rpg_Renderer::class, Stephino_Rpg_Renderer::INTERFACE_AJAX));
        add_action('wp_ajax_' . Stephino_Rpg::PLUGIN_VARNAME, array(Stephino_Rpg_Renderer::class, Stephino_Rpg_Renderer::INTERFACE_AJAX));
    }
    
    /**
     * Create the menu structure
     */
    protected static function _addMenu() {
        // Game
        add_menu_page(
            Stephino_Rpg_Utils_Lingo::getGameName(), 
            esc_html__('Play', 'stephino-rpg') . ' <span>' . esc_html(Stephino_Rpg_Utils_Lingo::getGameName()) . '</span>', 
            'read', // Subscriber
            Stephino_Rpg::PLUGIN_SLUG, 
            array(Stephino_Rpg_Renderer::class, Stephino_Rpg_Renderer::INTERFACE_HTML),
            'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBzdGFuZGFsb25lPSJubyI/Pgo8IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDIwMDEwOTA0Ly9FTiIgImh0dHA6Ly93d3cudzMub3JnL1RSLzIwMDEvUkVDLVNWRy0yMDAxMDkwNC9EVEQvc3ZnMTAuZHRkIj4KPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZlcnNpb249IjEuMCIgd2lkdGg9IjE1Ljk4NDAwMHB0IiBoZWlnaHQ9IjE1Ljk4NDAwMHB0IiB2aWV3Qm94PSIwIDAgMTUuOTg0MDAwIDE1Ljk4NDAwMCIgcHJlc2VydmVBc3BlY3RSYXRpbz0ieE1pZFlNaWQgbWVldCI+CiAgPG1ldGFkYXRhPkNvcHlyaWdodCAoYykgMjAxOSBTdGVwaGlubywgaHR0cDovL3N0ZXBoaW5vLmNvbTwvbWV0YWRhdGE+CiAgPGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMC4wMDAwMDAsMTUuOTg0MDAwKSBzY2FsZSgwLjAwMzEyMiwtMC4wMDMxMjIpIiBmaWxsPSIjMzMzMzMzIiBzdHJva2U9Im5vbmUiPgogICAgPGc+CiAgICAgIDxwYXRoIGQ9Ik0zMjc2IDQ1ODkgYzIgLTQgLTEzIC02IC0zNCAtNCAtMzQgMiAtMTEwIC01IC0yMTcgLTIxIC02NSAtOSAtMTkxIC00MyAtMjczIC03MyAtNzYgLTI4IC0yMDggLTg4IC0yMjcgLTEwMyAtNSAtNSAtMzcgLTI2IC03MSAtNDcgLTgzIC01MiAtOTYgLTY0IC0xODkgLTE1NiAtMTc3IC0xNzUgLTI2NiAtMzY1IC0yNjkgLTU3MiAtMSAtMjkgLTQgLTQ5IC03IC00NSAtMyA0IC0yNiAzOSAtNTIgNzcgLTEwOCAxNjcgLTI0NSAyOTQgLTM5MiAzNjcgLTcxIDM1IC0xODIgNzQgLTE5MCA2NiAtMyAtMyA2IC0yOCAyMCAtNTcgNjggLTE0MCA5NyAtMjk1IDY3IC0zNTIgLTkgLTE2IC0yOSAtMzAgLTU5IC0zOSAtMTM1IC0zOSAtMjU3IC0xMzAgLTMwMyAtMjI2IC0xNiAtMzIgLTI4IC00MyAtNjAgLTUzIC02MyAtMTkgLTcyIC0zOSAtNDggLTExOCA0MCAtMTM0IDEyMCAtMjA4IDI0NCAtMjI3IDE2MCAtMjQgMjUwIC02NCAyODggLTEyOCAxMiAtMTkgMzUgLTU1IDUyIC03OSA2MiAtODkgODYgLTE5NSA2NCAtMjkwIC03IC0zMCAtMTQgLTYzIC0xNiAtNzQgLTUgLTI5IC0xMiAtNjkgLTI5IC0xNTUgLTIwIC0xMDYgLTI3IC0xNDMgLTMwIC0xNjAgLTEgLTggLTUgLTI2IC04IC00MCAtNCAtMTQgLTkgLTM3IC0xMiAtNTMgLTEwIC01MyAtMTYgLTgyIC0yNSAtMTE3IC01IC0xOSAtMTEgLTUxIC0xNCAtNzAgLTMgLTE5IC0yNCAtMTExIC00NiAtMjA1IC0yMyAtOTMgLTQzIC0xNzcgLTQ1IC0xODUgLTM4IC0xNjkgLTE5MiAtNjc5IC0yMDkgLTY5OCAtMiAtMSAtMTYgNCAtMzIgMTMgLTMwIDE2IC03MSAxOSAtOTggOSAtMjEgLTggLTExNiAtMTI4IC0xMTYgLTE0NiAwIC0yOCAzNiAtMzQgMjA5IC0zNCBsMTczIDEgNDIgNDYgYzEwMCAxMTEgMjI3IDMzNCAzMTIgNTUyIDIxIDUzIDQxIDk3IDQ0IDk3IDMgMCA1IC0xOSA1IC00MyAwIC01NCAyOCAtMTk1IDU0IC0yNzEgMzcgLTEwNiAxMDcgLTIxMCAxNzkgLTI2NyAyNCAtMTkgNDkgLTM5IDU2IC00NCAyOSAtMjcgMTYxIC04MSAyNDEgLTEwMCA0OCAtMTEgNTcgLTEzIDEyMCAtMTkgODUgLTEwIDk0IC0xMSAxMDAgLTE4IDQgLTMgMjAgLTIgMzYgMiAxOCA1IDI3IDUgMjQgLTEgLTggLTEyIDIgLTEyIDI1IDEgMTEgNiAyMSA2IDI0IDEgNyAtMTAgOTggLTcgMTE2IDQgOCA1IDExIDQgNyAtMSAtMyAtNSAzIC0xMCAxMyAtMTAgMTAgMCAxNyAzIDE0IDcgLTIgNCA5IDcgMjQgNyA0MSAwIDE2NSAxMyAxNzMgMTggNCAyIDI0IDYgNDUgOSAyMiAyIDY2IDEyIDk5IDIxIDMzIDggNjcgMTcgNzUgMTkgOTEgMjEgMjU2IDEwMCAzNzEgMTc3IDIxMCAxMzkgMzc5IDM2NSA0NTMgNjAzIDgzIDI2NiA3NiA2MzEgLTE1IDg3MCAtMTMgMzMgLTI0IDYyIC0yNCA2NSAtMyAyNCAtOTggMTgyIC0xNDkgMjUwIC05NiAxMjcgLTIxMCAyMzUgLTQ4NiA0NTkgLTI5NiAyNDEgLTQwOCAzOTEgLTQ1NSA2MTYgLTEzIDYyIC0xNCAxODMgLTEgMjMzIDUzIDE5OSAyNDAgMzM1IDQ2MSAzMzMgNzkgLTEgOTAgLTMgMTYwIC0yNiAxMjggLTQzIDIyOSAtMTQ3IDI1MCAtMjU3IDYgLTMwIDIgLTYxIC0yMCAtMTU4IC0xNiAtNzMgLTk0IC0xNzIgLTE3OCAtMjI4IC00MSAtMjcgLTM4IC00MCAxNSAtNjggNTkgLTMwIDEzOCAtMjMgMjk1IDI3IDEzOCA0NCAyMDUgODcgMjY1IDE2OSAzMCA0MSA2MCAxMjEgNjEgMTY4IDEgMTcgNCAzMiA3IDMyIDQgMCA2IDMyIDYgNzAgMCAzOSAtMyA3MCAtNiA3MCAtMyAwIC02IDE5IC03IDQyIC04IDEzMiAtMTA3IDI5NSAtMjI5IDM3OCAtOTAgNjEgLTI2MCAxMjIgLTM3NiAxMzUgLTI5IDMgLTY2IDggLTgzIDEwIC0xNiAyIC01MiA0IC03OCA0IC0zNiAxIC00NiA0IC00MSAxNCA2IDEwIDQgMTAgLTcgMCAtNyAtNyAtMjIgLTEzIC0zMyAtMTMgLTE3IDAgLTE4IDIgLTYgMTAgMTIgNyAxMCA5IC03IDcgLTEyIDAgLTIwIC00IC0xNyAtOHogbS0xNjkzIC0xMTExIGM4MSAtNTUgNzggLTY5IC0xMyAtODcgLTU3IC0xMCAtMjc1IC05IC0yODYgMiAtMTAgMTAgNTcgODMgOTEgMTAxIDE3IDggNDYgMTggNjUgMjEgMTkgMyAzNiA4IDM4IDEwIDcgNiA2OSAtMjIgMTA1IC00N3ogbTUxNyAtMjc5IGMwIC0xNyA4MCAtMTMyIDE0NSAtMjA5IDE2IC0xOSAxNDMgLTE1MCAyODIgLTI5MCAyODcgLTI4OSAyOTIgLTI5NSAzNzQgLTQwOCAxMDQgLTE0MiAyMDIgLTM1MyAyMjMgLTQ4MiAyNyAtMTU3IDI3IC0zNTEgMiAtNDc1IC0xNyAtODEgLTM0IC0xMzIgLTczIC0yMTUgLTU3IC0xMTkgLTE0MyAtMjEwIC0yNDUgLTI1OCAtMjUyIC0xMjAgLTU4MiAtMzMgLTcxMCAxODggLTQ2IDc4IC02OCAxNzggLTY0IDI5MyAzIDEwNSA0IDExMSAzMSAyMDUgMzAgMTAwIDkwIDE3OSAxODkgMjQzIDU0IDM1IDE3MSA4MSAyNDQgOTQgMTUgMyAzOCA3IDUxIDEwIDEzIDIgNDEgNSA2MiA3IDUxIDMgMzAgMzIgLTYwIDgzIC04OSA1MSAtMjc1IDcxIC0zNzYgNDEgLTkyIC0yOCAtMjU0IC0xNDMgLTI5MSAtMjA2IC0xNCAtMjUgLTE0IC0yNSAtMTAgMTAgMiAxOSAxMSA2NyAyMCAxMDUgMTYgNzIgMjMgMTE2IDMyIDE5NSAzIDI1IDcgNTYgOSA3MCAyIDE0IDcgNDUgMTAgNzAgMyAyNSA4IDYxIDEwIDgwIDMgMTkgOCA1NSAxMSA4MCA1IDQ3IDggNjggMTkgMTM1IDYgNDEgOSA2MiAyMCAxNTAgMyAyOCAxMiA5MyAyMCAxNDUgMTUgMTAwIDIzIDE1OSAzMCAyMjQgMyAyMSA3IDU3IDExIDgwIDQgMjMgNyA0NyA3IDUzIDAgOSA0IDEwIDE0IDIgNyAtNiAxMyAtMTUgMTMgLTIweiIvPgogICAgPC9nPgogIDwvZz4KPC9zdmc+Cg=='
        );
        
        // Dashboard
        add_submenu_page(
            Stephino_Rpg::PLUGIN_SLUG, 
            Stephino_Rpg_Utils_Lingo::getGameName() . ' - ' . esc_html__('Dashboard', 'stephino-rpg'),
            esc_html__('Dashboard', 'stephino-rpg'),
            'activate_plugins', // Admin
            Stephino_Rpg::PLUGIN_SLUG . '-' . Stephino_Rpg_Renderer_Html::TEMPLATE_DASHBOARD, 
            array(Stephino_Rpg_Renderer::class, Stephino_Rpg_Renderer::INTERFACE_HTML)
        );
        
        // Game Mechanics
        add_submenu_page(
            Stephino_Rpg::PLUGIN_SLUG, 
            Stephino_Rpg_Utils_Lingo::getGameName() . ' - ' . esc_html__('Game Mechanics', 'stephino-rpg'),
            esc_html__('Game Mechanics', 'stephino-rpg') . (Stephino_Rpg::get()->isPro() ? '' : ' &#x1F512;'),
            Stephino_Rpg::get()->isDemo() ? 'read' : 'activate_plugins', // Subscriber OR Admin
            Stephino_Rpg::PLUGIN_SLUG . '-' . Stephino_Rpg_Renderer_Html::TEMPLATE_OPTIONS, 
            array(Stephino_Rpg_Renderer::class, Stephino_Rpg_Renderer::INTERFACE_HTML)
        );
    }
    
    /**
     * Show the notice dialog
     */
    protected static function _handleNotice() {
        do {
            // Cannot install plugins or plugin unlocked
            if (!current_user_can('install_plugins') || Stephino_Rpg::get()->isPro()) {
                break;
            }
            
            // Get the cookie version
            $unlockNoticeVersion = isset($_COOKIE) && isset($_COOKIE['stephino-rpg-unlock-notice']) 
                ? sanitize_text_field(wp_unslash($_COOKIE['stephino-rpg-unlock-notice'])) 
                : '';

            // Notification shown in the last 28 days for the latest version
            if (!empty($unlockNoticeVersion) && version_compare($unlockNoticeVersion, Stephino_Rpg::PLUGIN_VERSION, '>=' )) {
                break;
            }
            
            // The PRO link is missing
            if (!strlen(Stephino_Rpg::PLUGIN_URL_PRO)) {
                break;
            }
            
            // Show the notice
            require_once STEPHINO_RPG_ROOT . '/ui/tpl/wordpress/wp-notice.php';
        } while(false);
    }
}

/*EOF*/