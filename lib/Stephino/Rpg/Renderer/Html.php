<?php

/**
 * Stephino_Rpg_Renderer_Html
 * 
 * @title      HTML Renderer - delivered thorugh AJAX
 * @desc       Delivers the HTML pages
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Renderer_Html {
    
    // HTML
    const METHOD_PREFIX = 'html';
    const METHOD_GAME   = 'game';
    
    // Templates
    const TEMPLATE_GAME      = 'game';
    const TEMPLATE_PTF       = 'ptf';
    const TEMPLATE_DASHBOARD = 'dashboard';
    const TEMPLATE_THEMES    = 'themes';
    const TEMPLATE_OPTIONS   = 'options';
    
    /**
     * List of allowed templates
     * 
     * @var string[]
     */
    protected static $_allowedTemplates = array(
        self::TEMPLATE_DASHBOARD,
        self::TEMPLATE_THEMES,
        self::TEMPLATE_OPTIONS,
    );
    
    /**
     * Dashboard
     */
    public static function htmlDashboard() {
        // Admin scripts
        foreach (array('jquery', 'jquery-ui-draggable', 'jquery-ui-droppable') as $scriptName) {
            wp_enqueue_script($scriptName);
        }
        self::_enqueueScripts(
            array('bootstrap', 'wp-dashboard'), 
            array('wp-dashboard'),
            array(
                'error_ajax' => esc_html__('Please try again later', 'stephino-rpg'),
            )
        );
        
        // Gather the stats
        Stephino_Rpg_Db::get()->modelStatistics()->gather();
        
        // Load the template
        self::_loadTemplate(self::TEMPLATE_DASHBOARD);
    }
    
    /**
     * Themes
     */
    public static function htmlThemes() {
        // Admin scripts
        foreach (array('jquery', 'jquery-ui-draggable', 'jquery-ui-droppable') as $scriptName) {
            wp_enqueue_script($scriptName);
        }
        self::_enqueueScripts(
            array('bootstrap', 'wp-themes'), 
            array('wp-themes'),
            array(
                'error_ajax'   => esc_html__('Please try again later', 'stephino-rpg'),
                'label_upload' => esc_html__('Upload', 'stephino-rpg'),
                'label_save'   => esc_html__('Save', 'stephino-rpg'),
                'warning_file' => esc_html__('File not found', 'stephino-rpg'),
            )
        );
        
        // Load the template
        self::_loadTemplate(self::TEMPLATE_THEMES);
    }
    
    /**
     * Game mechanics
     */
    public static function htmlOptions() {
        // Admin scripts
        foreach (array('jquery', 'jquery-ui-draggable', 'jquery-ui-droppable') as $scriptName) {
            wp_enqueue_script($scriptName);
        }
        
        // Thickbox
        add_thickbox();
        
        // Functionality
        self::_enqueueScripts(
            array('bootstrap', 'wp-options'), 
            array('wp-options'),
            // Intentionally skipping i18n for the admin strings
            array(
                'save_working'          => 'Working...',
                'save_error'            => 'Could not save. Please refresh and try again.',
                'save_success'          => 'Saved successfully!',
                'slot_tap_single'       => 'Click or tap to select one slot',
                'slot_tap_multiple'     => 'Click or tap to toggle multiple slots',
                'slot_drag'             => 'Drag the %s slots to the preferred location',
                'number_odd'            => 'Odd number',
                'number_even'           => 'Even number',
                'action_area'           => 'Drag the cursor to define the mapped area. Click once to delete the path.',
                'level'                 => 'level',
                'level_main_building'   => 'main building level',
                'level_city'            => 'city level',
                'level_empire'          => 'empire size (level)',
                'poly_header'           => 'Base values are multiplied with f(x)',
                'poly_function'         => 'polynomial',
                'poly_m_inverse'        => 'multiplicative inverse',
                'formula_constant'      => 'Constant',
                'formula_no_change'     => 'No change',
                'formula_linear'        => 'Linear',
                'formula_quad'          => 'Quadratic',
                'formula_exp'           => 'Exponential',
                'label_anim'            => 'Click or tap to add and edit keyframes, drag to move keyframes to the desired slot. Each slot is 32x32.',
                'label_anim_sprites'    => 'Sprite file names are numeric, i.e. "0.png", "1.png" etc.',
                'label_anim_location'   => 'Sprites are stored in %s',
                'label_anim_theme'      => 'current theme',
                'label_multiselect'     => 'Ctrl + click or tap to select/deselect entries',
                'label_default'         => 'Default',
                'anim_title'            => 'Animation',
                'anim_sliding'          => 'Sliding',
                'anim_z_index'          => 'Z-index',
                'anim_opacity'          => 'Opacity',
                'anim_sprite'           => 'Sprite row',
                'anim_flip_x'           => 'Flip on X',
                'anim_flip_y'           => 'Flip on Y',
                'extra_export_button'   => 'Download JSON',
                'extra_export_title'    => 'Configuration: Export',
                'extra_export_content'  => 'Export the current game configuration object (.json)',
                'extra_import_button'   => 'Upload JSON',
                'extra_import_title'    => 'Configuration: Import',
                'extra_import_content'  => 'Import a game configuration object (.json)',
                'extra_reset_button'    => 'RESET',
                'extra_reset_title'     => 'Configuration: Reset',
                'extra_reset_content'   => 'Reset the game configuration to default values',
                'confirm_reset'         => 'Are you sure you want to reset the game configuration?',
                'extra_restart_button'  => 'RESTART',
                'extra_restart_title'   => 'Restart Game',
                'extra_restart_content' => 'Restart the game, erasing all game progress for all players',
                'confirm_restart'       => 'Are you sure you want to restart the game?',
            )
        );              
        
        // Load the template
        self::_loadTemplate(self::TEMPLATE_OPTIONS);
    }
    
    /**
     * Game interface
     */
    public static function htmlGame() {
        // Admin scripts
        wp_enqueue_script('jquery');
        self::_enqueueScripts(
            array('wp-game'), 
            array('stephino', 'wp-game')
        );
        
        // Prepare the parameters
        $gameParams = array_filter(array(
            Stephino_Rpg_Renderer_Ajax::CALL_VIEW      => Stephino_Rpg_Utils_Sanitizer::getView(),
            Stephino_Rpg_Renderer_Ajax::CALL_VIEW_DATA => Stephino_Rpg_Utils_Sanitizer::getViewData(),
        ), function($item) {
            return null !== $item;
        });

        // Prepare the page
        $viewData = Stephino_Rpg_Utils_Media::getAdminUrl(true, false) . (count($gameParams) ? ('&' . http_build_query($gameParams)) : '');
        
        // Prepare the HTML
        echo sprintf(
            '<%1$s data-role="stephino-rpg-frame" src="%3$s" allowfullscreen="true" referrerpolicy="same-origin"></%1$s><%2$s data-role="stephino-rpg-glow"></%2$s>',
            'iframe', 'div', esc_attr($viewData)
        );
    }
    
    /**
     * Load a template
     * 
     * @param string $templateName Template name
     * @param string $viewName      (optional) Template name; default <b>null</b>
     * @param mixed  $viewData      (optional) Template data; default <b>null</b>
     */
    protected static function _loadTemplate($templateName, $viewName = null, $viewData = null) {
        if (in_array($templateName, self::$_allowedTemplates)) {
            if (is_file($templatePath = STEPHINO_RPG_ROOT . '/' . Stephino_Rpg::FOLDER_UI_TPL . '/wordpress/wp-' . $templateName . '.php')) {
                require $templatePath;
            }
        }
    }
    
    /**
     * Enqueue CSS and JS scripts with the given names. You must pass at least one JS script to initialize the "stephino_rpg_data" JS data variable.
     * 
     * @param string[] $cssList     (optional) CSS Scripts; default <b>empty array</b>
     * @param string[] $jsList      (optional) JS Scripts; default <b>empty array</b>
     * @param string[] $i18nStrings (optional) Associative array of translated strings; default <b>empty array</b>
     * @return boolean Whether the JS data variable was initialized
     */
    protected static function _enqueueScripts(Array $cssList = array(), Array $jsList = array(), $i18nStrings = array()) {
        // Enqueue the CSS
        if (count($cssList)) {
            foreach ($cssList as $scriptName) {
                $scriptPath = (0 === strpos($scriptName, 'wp-') ? ('wordpress/' . $scriptName) : $scriptName);
                
                // Add the style
                wp_enqueue_style(
                    Stephino_Rpg::PLUGIN_SLUG . '-style-' . $scriptName, 
                    Stephino_Rpg_Utils_Media::getPluginsUrl() . '/' . Stephino_Rpg::FOLDER_UI_CSS . '/' . $scriptPath . '.css', 
                    array(), 
                    Stephino_Rpg::PLUGIN_VERSION
                );
            }
        }

        // Prepare the localization flag
        $addedData = false;
            
        // Enqueue the JS
        if (count($jsList)) {
            // Go through the list
            foreach ($jsList as $scriptName) {
                $scriptPath = (0 === strpos($scriptName, 'wp-') ? ('wordpress/' . $scriptName) : $scriptName);
                
                // Add the script
                wp_enqueue_script(
                    Stephino_Rpg::PLUGIN_SLUG . '-js-' . $scriptName, 
                    Stephino_Rpg_Utils_Media::getPluginsUrl() . '/' . Stephino_Rpg::FOLDER_UI_JS . '/' . $scriptPath . '.js', 
                    array(), 
                    Stephino_Rpg::PLUGIN_VERSION, 
                    true
                );
                
                // Need to add our variables
                if (!$addedData) {
                    // Localize the first script form the list
                    wp_localize_script(
                        Stephino_Rpg::PLUGIN_SLUG . '-js-' . $scriptName, 
                        Stephino_Rpg::PLUGIN_VARNAME . '_data',
                        array(
                            'res_url'    => Stephino_Rpg_Utils_Media::getPluginsUrl(),
                            'ajax_url'   => Stephino_Rpg_Utils_Media::getAdminUrl(true, false),
                            'theme_slug' => Stephino_Rpg_Utils_Themes::getActive()->getThemeSlug(),
                            'game_url'   => Stephino_Rpg_Utils_Media::getAdminUrl(),
                            'game_ver'   => Stephino_Rpg_Utils_Media::getPwaVersion(false, false),
                            'game_lang'  => Stephino_Rpg_Config::lang(),
                            'app_name'   => Stephino_Rpg_Utils_Lingo::getGameName(),
                            'is_admin'   => Stephino_Rpg_Cache_User::get()->isGameMaster(),
                            'is_demo'    => Stephino_Rpg::get()->isDemo(),
                            'is_pro'     => Stephino_Rpg::get()->isPro(),
                            'i18n'       => $i18nStrings
                        )
                    );
                    
                    // Mark the task
                    $addedData = true;
                }
            }
        }
        
        return $addedData;
    }
}

/*EOF*/