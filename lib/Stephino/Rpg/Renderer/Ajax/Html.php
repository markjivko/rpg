<?php

/**
 * Stephino_Rpg_Renderer_Ajax_Html
 * 
 * @title      HTML Renderer
 * @desc       Create the HTML pages
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Renderer_Ajax_Html {

    // Symbols and decorations
    const SYMBOL_CAPITAL = '&#9733;';
    
    /**
     * Get the HTML pages
     * 
     * @return string
     */
    public static function ajaxHtml() {
        return self::_renderForView(
            Stephino_Rpg_Utils_Sanitizer::getView(false), 
            Stephino_Rpg_Utils_Sanitizer::getViewData()
        );
    }
    
    /**
     * Get the HTML page content for this view
     * 
     * @param string $viewName View Name
     * @param mixed  $viewData View Data
     * @return string
     */
    protected static function _renderForView($viewName, $viewData) {
        // Set the title
        add_filter('document_title_parts', function($title) use ($viewName) {
            $title['title'] = Stephino_Rpg_Utils_Lingo::getGameName() 
                . (Stephino_Rpg_Renderer_Ajax::VIEW_PWA == $viewName ? ' - ' . esc_html__('Offline', 'stephino-rpg') : '');
            return $title;
        });
        
        // Prepare the animation rules identifier
        $animationIdentifier = null;
        
        // Get the user ID
        if (null === $userId = Stephino_Rpg_TimeLapse::get()->userId()) {
            // Initialize the player
            Stephino_Rpg_Task_Cron::player();
            
            // Re-initialize the time-lapse data
            $userId = Stephino_Rpg_TimeLapse::get(true)->userId();
        }
        
        // Validate the view data
        switch($viewName) {
            case Stephino_Rpg_Renderer_Ajax::VIEW_WORLD:
                // Get the coordinates
                $coordinates = array_values(
                    array_filter(
                        array_map(
                            function($item) {
                                return is_numeric($item) ? intval($item) : null;
                            }, 
                            explode('x', strval($viewData))
                        ), 
                        function($item) {
                            return null !== $item;
                        }
                    )
                );
                
                do {
                    // Check the island exists at these coordinates
                    if (2 === count($coordinates) && ($coordinates[0] != 0 || $coordinates[1] != 0)) {
                        // Get the island ID
                        $islandId = Stephino_Rpg_Utils_Math::getSnakeLength($coordinates[0], $coordinates[1]);
                        
                        // Island not found
                        if (null === Stephino_Rpg_Db::get()->tableIslands()->getById($islandId)) {
                            throw new Exception(__('Invalid coordinates', 'stephino-rpg'));
                        }
                        
                        // Found an island at coordinates, stop here
                        break;
                    }

                    // Prepare the default
                    $coordinates = array(0, 0);

                    // Get the Metropolis ID
                    if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
                        foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $buildingData) {
                            if ($buildingData[Stephino_Rpg_Db_Table_Cities::COL_CITY_IS_CAPITAL]) {
                                // Get the Metropolis island coordinates
                                $coordinates = Stephino_Rpg_Utils_Math::getSnakePoint(
                                    intval($buildingData[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID])
                                );
                                break;
                            }
                        }
                    }
                } while(false);
                
                // Save the data
                $viewData = $coordinates;
                break;

            case Stephino_Rpg_Renderer_Ajax::VIEW_ISLAND:
                // Integer
                $viewData = intval($viewData);
                
                // Invalid island
                if (null === $islandData = Stephino_Rpg_Db::get()->tableIslands()->getById($viewData)) {
                    throw new Exception(
                        sprintf(
                            __('Invalid ID (%s)', 'stephino-rpg'),
                            Stephino_Rpg_Config::get()->core()->getConfigIslandName()
                        )
                    );
                }
                
                // Set the island animation identifier
                $animationIdentifier = $islandData[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_CONFIG_ID];
                break;
            
            case Stephino_Rpg_Renderer_Ajax::VIEW_CITY:
                // Integer
                $viewData = intval($viewData);
                
                // Prepare the city data
                $cityData = null;
                
                // Get the city data
                if (null !== Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY) 
                    && is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData())) {
                    foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->getData() as $buildingData) {
                        if (0 === $viewData && $buildingData[Stephino_Rpg_Db_Table_Cities::COL_CITY_IS_CAPITAL]) {
                            $viewData = intval($buildingData[Stephino_Rpg_Db_Table_Cities::COL_ID]);
                        }
                        if (0 !== $viewData && null === $cityData && $viewData == $buildingData[Stephino_Rpg_Db_Table_Cities::COL_ID]) {
                            $cityData = $buildingData;
                        }
                        if (0 !== $viewData && null !== $cityData) {
                            break;
                        }
                    }
                }
                
                // Invalid city
                if (null === $cityData) {
                    throw new Exception(
                        sprintf(
                            __('Invalid ID (%s)', 'stephino-rpg'),
                            Stephino_Rpg_Config::get()->core()->getConfigCityName()
                        )
                    );
                }
                
                // Does not belong to us
                if ($cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_USER_ID] != $userId) {
                    throw new Exception(__('You can only visit our own empire', 'stephino-rpg'));
                }
                
                // Set the city animation identifier
                $animationIdentifier = $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_CONFIG_ID];
                break;
        }
        
        // Animations and game view CSS served by AJAX
        $cssUrlParams = array(
            Stephino_Rpg_Renderer_Ajax::CALL_METHOD => Stephino_Rpg_Renderer_Ajax::CONTROLLER_CSS,
            Stephino_Rpg_Renderer_Ajax::CALL_VIEW   => $viewName,
        );
        if (null !== $animationIdentifier) {
            $cssUrlParams[Stephino_Rpg_Renderer_Ajax::CALL_CONFIG_ID] = $animationIdentifier;
        }

        // JS scripts served by AJAX 
        $jsUrlParams = array(
            Stephino_Rpg_Renderer_Ajax::CALL_METHOD => Stephino_Rpg_Renderer_Ajax::CONTROLLER_JS,
            Stephino_Rpg_Renderer_Ajax::CALL_VIEW   => $viewName,
        );
        
        // Prepare the attributes string
        $viewAttrs = '';
        
        /**
         * Reduce an associative array to a string of HTML attributes
         * 
         * @example 'attr="foo" attr-two="bar"'
         * @param array $attributes Associative array
         * @return string List of HTML attributes
         */
        $reduceAttributes = function($attributes) {
            $pairs = array();
            foreach ($attributes as $attrName => $attrValue) {
                $pairs[] = $attrName . '="' . $attrValue . '"';
            }
            return implode(' ', $pairs);
        };
        switch ($viewName) {
            case Stephino_Rpg_Renderer_Ajax::VIEW_CITY:
                $islandCoords = Stephino_Rpg_Utils_Math::getSnakePoint(
                    intval($cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID])
                );
                $viewAttrs = $reduceAttributes(array(
                    'island-id'      => (int) $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID],
                    'island-x'       => $islandCoords[0],
                    'island-y'       => $islandCoords[1],
                    'city-id'        => (int) $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID],
                    'city-config-id' => (int) $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_CONFIG_ID],
                ));
                break;

            case Stephino_Rpg_Renderer_Ajax::VIEW_ISLAND:
                $islandCoords = Stephino_Rpg_Utils_Math::getSnakePoint(
                    intval($islandData[Stephino_Rpg_Db_Table_Islands::COL_ID])
                );
                $viewAttrs = $reduceAttributes(array(
                    'island-id' => (int) $islandData[Stephino_Rpg_Db_Table_Islands::COL_ID],
                    'island-x'  => $islandCoords[0],
                    'island-y'  => $islandCoords[1],
                ));
                break;

            case Stephino_Rpg_Renderer_Ajax::VIEW_WORLD:
                $viewAttrs = $reduceAttributes(array(
                    'data-start-x' => $viewData[0],
                    'data-start-y' => $viewData[1],
                ));
                break;
        }
        
        // jQuery needed
        wp_enqueue_script('jquery');
        
        // Dequeue other themes' and plugins' scripts and styles
        add_action('wp_enqueue_scripts', function() {
            // Remove emojis
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            remove_action('admin_print_scripts', 'print_emoji_detection_script');
            remove_action('wp_print_styles', 'print_emoji_styles');
            remove_action('admin_print_styles', 'print_emoji_styles');	
    
            // Remove redundant scripts
            foreach(wp_scripts()->registered as $wpScript) {
                if (!preg_match('%^(?:jquery|underscore|backbone|' . Stephino_Rpg::PLUGIN_SLUG . ')\b%', $wpScript->handle)) {
                    wp_deregister_script($wpScript->handle);
                }
            }
            
            // Remove redundant styles
            foreach (wp_styles()->registered as $wpStyle) {
                if (!preg_match('%^(?:' . Stephino_Rpg::PLUGIN_SLUG . ')\b%', $wpStyle->handle)) {
                    wp_deregister_style($wpStyle->handle);
                }
            }
        }, 9999);
        
        // Prepare the chat room data
        $gameChatData = null;
        if (Stephino_Rpg_Renderer_Ajax::VIEW_PTF == $viewName) {
            wp_enqueue_style(
                Stephino_Rpg::PLUGIN_SLUG,
                Stephino_Rpg_Utils_Media::getPluginsUrl() . '/' . Stephino_Rpg::FOLDER_UI_CSS . '/bootstrap.css',
                array(), 
                Stephino_Rpg::PLUGIN_VERSION
            );
            wp_enqueue_script(
                Stephino_Rpg::PLUGIN_SLUG,
                Stephino_Rpg_Utils_Media::getPluginsUrl() . '/' . Stephino_Rpg::FOLDER_UI_JS . '/phaser.js',
                array(), 
                Stephino_Rpg::PLUGIN_VERSION
            );
        } else {
            wp_enqueue_script(
                Stephino_Rpg::PLUGIN_SLUG,
                Stephino_Rpg_Utils_Media::getPluginsUrl() . '/' . Stephino_Rpg::FOLDER_UI_JS . '/stephino.js',
                array(), 
                Stephino_Rpg::PLUGIN_VERSION
            );
            wp_enqueue_style(
                Stephino_Rpg::PLUGIN_SLUG . '-style-game',
                Stephino_Rpg_Utils_Media::getAdminUrl(true) . '&' . http_build_query(array(
                    Stephino_Rpg_Renderer_Ajax::CALL_METHOD  => Stephino_Rpg_Renderer_Ajax::CONTROLLER_CSS,
                    Stephino_Rpg_Renderer_Ajax::CALL_VERSION => Stephino_Rpg::PLUGIN_VERSION,
                )),
                array(), 
                Stephino_Rpg::PLUGIN_VERSION
            );
            if (Stephino_Rpg_Config::get()->core()->getChatroom()
                && strlen(Stephino_Rpg_Config::get()->core()->getFirebaseProjectId())
                && strlen(Stephino_Rpg_Config::get()->core()->getFirebaseDatabaseUrl())
                && strlen(Stephino_Rpg_Config::get()->core()->getFirebaseWebApiKey())) {

                // Get the user information
                $userInfo = Stephino_Rpg_TimeLapse::get()->userData();

                // Valid
                if (is_array($userInfo)) {
                    // Store the credentials
                    $gameChatData = array(
                        Stephino_Rpg_Config::get()->core()->getFirebaseProjectId(),
                        Stephino_Rpg_Config::get()->core()->getFirebaseDatabaseUrl(),
                        Stephino_Rpg_Config::get()->core()->getFirebaseWebApiKey(),
                        (int) $userInfo[Stephino_Rpg_Db_Table_Users::COL_ID],
                        Stephino_Rpg_Utils_Lingo::getUserName($userInfo),
                    );

                    // Include the necessary components
                    foreach (array('firebase-app', 'firebase-database', 'firebase-auth') as $firebasePart) {
                        wp_enqueue_script(
                            Stephino_Rpg::PLUGIN_SLUG . '-' . $firebasePart,
                            'https://www.gstatic.com/firebasejs/' . Stephino_Rpg::PLUGIN_VERSION_FIREBASE . '/' . $firebasePart . '.js',
                            array(),
                            null
                        );
                    }
                }
            }
        }
        
        // View CSS
        if (Stephino_Rpg_Renderer_Ajax::VIEW_PWA != $viewName) {
            wp_enqueue_style(
                Stephino_Rpg::PLUGIN_SLUG . '-style-game-' . $viewName,
                Stephino_Rpg_Utils_Media::getAdminUrl(true) . '&' . http_build_query($cssUrlParams),
                array(), 
                Stephino_Rpg::PLUGIN_VERSION
            );
        }
        
        // Add the inline script
        wp_add_inline_script(
            Stephino_Rpg::PLUGIN_SLUG, 
            'var stephino_rpg_data = ' . json_encode(array(
                    'game_chat'      => $gameChatData,
                    'game_url'       => Stephino_Rpg_Utils_Media::getAdminUrl(),
                    'ajax_url'       => Stephino_Rpg_Utils_Media::getAdminUrl(true, false),
                    'game_ver'       => Stephino_Rpg_Utils_Media::getPwaVersion(false, false),
                    'game_lang'      => Stephino_Rpg_Config::lang(),
                    'res_url'        => Stephino_Rpg_Utils_Media::getPluginsUrl(),
                    'theme_slug'     => Stephino_Rpg_Utils_Themes::getActive()->getThemeSlug(),
                    'app_name'       => Stephino_Rpg_Utils_Lingo::getGameName(),
                    'events_sprite'  => Stephino_Rpg_Utils_Media::getEventsSprite(),
                    'discord_url'    => esc_url(Stephino_Rpg::PLUGIN_URL_DISCORD),
                    'symbol_capital' => Stephino_Rpg_Renderer_Ajax_Html::SYMBOL_CAPITAL,
                    'is_admin'       => Stephino_Rpg_Cache_User::get()->isGameMaster(),
                    'is_demo'        => Stephino_Rpg::get()->isDemo(),
                    'is_pro'         => Stephino_Rpg::get()->isPro(),
                    'ptf_size'       => Stephino_Rpg_Db::get()->modelPtfs()->getViewBox(),
                    'i18n'           => array(
                        'city_workers_updated' => esc_html__('Updated workers', 'stephino-rpg'),
                        'ajax_timeout'         => esc_html__('Request timed out. Please try again later.', 'stephino-rpg'),
                        'ajax_no_net_title'    => esc_html__('No Internet', 'stephino-rpg'),
                        'ajax_no_net_content'  => esc_html__('Please check your Internet connection and try again!', 'stephino-rpg'),
                        'console_help'         => esc_html__('Type "%s" to list all available commands', 'stephino-rpg'),
                        'console_hint'         => esc_html__('Type a command and press Enter', 'stephino-rpg'),
                        'chat_hint'            => esc_html__('Type and press Enter', 'stephino-rpg'),
                        'chat_title'           => esc_html__('Chat Room', 'stephino-rpg'),
                        'chat_welcome'         => esc_html__('Welcome to our chat room!', 'stephino-rpg'),
                        'chat_discord'         => esc_html__('For more features and feedback, please visit %s', 'stephino-rpg'),
                        'paypal_validating'    => esc_html__('Validating payment...', 'stephino-rpg'),
                        'paypal_preparing'     => esc_html__('Preparing payment...', 'stephino-rpg'),
                        'pwa_installed'        => esc_html__('App installed successfully!', 'stephino-rpg'),
                        'tutorial_step'        => esc_html__('Step', 'stephino-rpg'),
                        'msg_diplomacy'        => esc_html__('Diplomacy', 'stephino-rpg'),
                        'msg_economy'          => esc_html__('Economy', 'stephino-rpg'),
                        'msg_military'         => esc_html__('Military', 'stephino-rpg'),
                        'msg_research'         => esc_html__('Research', 'stephino-rpg'),
                        'msg_close'            => esc_html__('Close message', 'stephino-rpg'),
                        'nav_prev'             => esc_html__('Previous', 'stephino-rpg'),
                        'nav_next'             => esc_html__('Next', 'stephino-rpg'),
                        'acc_del_toast'        => esc_html__('Sorry to see you go!', 'stephino-rpg'),
                        'acc_del_confirm'      => esc_html__('Please type "%s" to continue. This action is permanent and irreversible!', 'stephino-rpg'),
                        'acc_logout'           => esc_html__('See you soon!', 'stephino-rpg'),
                        'modal_loading'        => esc_html__('Loading...', 'stephino-rpg'),
                        'modal_error'          => esc_html__('Error', 'stephino-rpg'),
                        'formula_constant'     => esc_html__('Constant', 'stephino-rpg'),
                        'formula_no_change'    => esc_html__('No change', 'stephino-rpg'),
                        'formula_linear'       => esc_html__('Linear', 'stephino-rpg'),
                        'formula_linear_inv'   => esc_html__('Linear multiplicative inverse', 'stephino-rpg'),
                        'formula_quad'         => esc_html__('Quadratic', 'stephino-rpg'),
                        'formula_quad_inv'     => esc_html__('Quadratic multiplicative inverse', 'stephino-rpg'),
                        'formula_exp'          => esc_html__('Exponential', 'stephino-rpg'),
                        'formula_exp_inv'      => esc_html__('Exponential multiplicative inverse', 'stephino-rpg'),
                    )
                )) . ';',
            'before'
        );
        
        // Default interface
        if (Stephino_Rpg_Renderer_Ajax::VIEW_PWA == $viewName) {
            // Offline mode (PWA)
            wp_add_inline_script(
                Stephino_Rpg::PLUGIN_SLUG . '-script-game',
                'document.addEventListener(\'DOMContentLoaded\', function() {'
                    . 'window.setTimeout(function() {window.location.reload();}, 7500);'
                    . 'stephino_rpg_tools && stephino_rpg_tools.toast.show(' 
                        . json_encode(esc_html__('Internet connection lost', 'stephino-rpg')) 
                    . ', false);'
                    . 'window.setTimeout(function() {'
                        . 'stephino_rpg_tools && stephino_rpg_tools.toast.show('
                            . json_encode(esc_html__('Retrying in 3 seconds...', 'stephino-rpg')) 
                        . ', false);}'
                    . ', 4500);'
                . '});'
            );
        } else {
            wp_enqueue_script(
                Stephino_Rpg::PLUGIN_SLUG . '-script-game',
                Stephino_Rpg_Utils_Media::getAdminUrl(true) . '&' . http_build_query($jsUrlParams),
                array(), 
                Stephino_Rpg::PLUGIN_VERSION
            );
        }
        
        // Prepare the buffer
        ob_start();
        
        // Prepare the template name
        $templateName = Stephino_Rpg_Renderer_Ajax::VIEW_PTF == $viewName
            ? Stephino_Rpg_Renderer_Html::TEMPLATE_PTF
            : Stephino_Rpg_Renderer_Html::TEMPLATE_GAME;
        
        // Load the game template
        if (is_file($templatePath = STEPHINO_RPG_ROOT . '/' . Stephino_Rpg::FOLDER_UI_TPL . '/wordpress/wp-' . $templateName . '.php')) {
            require $templatePath;
        }
        
        return ob_get_clean();
    }
}

/*EOF*/