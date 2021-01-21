<?php

/**
 * Stephino_Rpg_Renderer_Ajax_Js
 * 
 * @title      JS Renderer - delivered through AJAX
 * @desc       Creates CSS animations
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Renderer_Ajax_Js {

    /**
     * Get the game JS
     * 
     * @return string|null
     */
    public static function ajaxJs() {
        // Invalid view name
        if (null === $viewName = Stephino_Rpg_Utils_Sanitizer::getView()) {
            return null;
        }
        
        // Animation and Game CSS
        return Stephino_Rpg_Renderer_Ajax_Js::_renderForView($viewName);
    }
    
    /**
     * Get the JS scripts for this view
     * 
     * @param string $view       View name
     * @return string|null JS code
     */
    protected static function _renderForView($view) {
        // Platformer
        if (Stephino_Rpg_Renderer_Ajax::VIEW_PTF == $view) {
            return self::_getGamePtf();
        }
            
        // Prepare the result
        $result = '';

        // Valid view
        if (in_array($view, Stephino_Rpg_Renderer_Ajax::AVAILABLE_VIEWS)) {
            $result .= self::_getGameJs($view);
        }
        
        // Get the plugin version
        $pluginVersion = Stephino_Rpg::PLUGIN_VERSION;
        
        // Copyright year
        $year = date('Y');
        
        // Prepare the title
        $title = Stephino_Rpg_Renderer_Ajax::VIEW_PWA == $view
            ? 'Progressive Web App (SSL needed)'
            : 'Game UI';
        $description = Stephino_Rpg_Renderer_Ajax::VIEW_PWA == $view
            ? 'Detect offline mode and reduce server load'
            : 'Handle CRUD operations and other interactions';
        $view = ucfirst($view);
        
        // Prepare the header
        $jsHeader = <<<"JS"
/**
 * Stephino Rpg JS
 * 
 * @id         $view
 * @title      $title
 * @desc       $description
 * @copyright  (c) $year, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @version    $pluginVersion            
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
JS;

        return strlen($result) ? ($jsHeader . PHP_EOL . $result . PHP_EOL . PHP_EOL . '/*EOF*/') : null;
    }
    
    /**
     * Get the game JS functionality
     * 
     * @param string $view Sanitized view name
     * @return string JS contents
     */
    protected static function _getGameJs($view) {
        // Assume no result
        $result = '';
        
        do {
            // Progressive Web Apps
            if (Stephino_Rpg_Renderer_Ajax::VIEW_PWA == $view) {
                $result = self::_getGamePwa();
                break;
            }
            
            // Get the game JS paths
            $gamePath = STEPHINO_RPG_ROOT . '/ui/js/game/' . Stephino_Rpg_Renderer_Ajax::FILE_COMMON . '.js';
            $gameViewPath = STEPHINO_RPG_ROOT . '/ui/js/game/' . $view . '.js';
            
            // Both files found
            if (is_file($gamePath) && is_file($gameViewPath)) {
                $result = file_get_contents($gamePath) . PHP_EOL . PHP_EOL . file_get_contents($gameViewPath);
            }
            
        } while(false);
        
        return $result;
    }
    
    /**
     * Get the PWA file
     * 
     * @return string
     */
    protected static function _getGamePwa() {
        $result = '';
        
        // PWA file defined
        if (is_file($pwaPath = STEPHINO_RPG_ROOT . '/ui/js/pwa/pwa-worker.js')) {
            // Prepare the root using relative plugin url (starting with one forward slash "/")
            $urlRoot = Stephino_Rpg_Utils_Media::getPluginsUrl();
            
            // Prepare the offline file path
            $offlineFile = Stephino_Rpg_Utils_Media::getAdminUrl(true) . '&view=' . Stephino_Rpg_Renderer_Ajax::VIEW_PWA;
            
            // Prepare the list of files to cache
            $filesToCache = array(
                // Offline file
                $offlineFile,
                
                // Offline (+main) CSS
                Stephino_Rpg_Utils_Media::getAdminUrl(true) . '&' . http_build_query(array(
                    Stephino_Rpg_Renderer_Ajax::CALL_METHOD  => Stephino_Rpg_Renderer_Ajax::CONTROLLER_CSS,
                    Stephino_Rpg_Renderer_Ajax::CALL_VERSION => Stephino_Rpg::PLUGIN_VERSION,
                )),
                
                // Offline (+main) JS
                Stephino_Rpg_Utils_Media::getPluginsUrl() . '/ui/js/stephino.js?' . http_build_query(array(
                    Stephino_Rpg_Renderer_Ajax::CALL_VERSION => Stephino_Rpg::PLUGIN_VERSION
                )),
                
                // Offline resources
                $urlRoot . '/ui/img/badge-error.gif',
                $urlRoot . '/ui/img/badge-success.gif',
                $urlRoot . '/ui/img/icon.png',
                $urlRoot . '/ui/img/icon.svg',
                $urlRoot . '/ui/img/signature.png',
            );
            
            // Prepare the file iterators
            $iterators = array(
                $urlRoot => new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator(
                        Stephino_Rpg_Config::get()->themePath(), 
                        RecursiveDirectoryIterator::SKIP_DOTS
                    ), 
                    RecursiveIteratorIterator::SELF_FIRST
                )
            );
            
            // Prepare the PRO files iterator
            if (Stephino_Rpg::get()->isPro()) {
                $iterators[Stephino_Rpg_Utils_Media::getPluginsUrl(true)] = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator(
                        Stephino_Rpg_Config::get()->themePath(true), 
                        RecursiveDirectoryIterator::SKIP_DOTS
                    ), 
                    RecursiveIteratorIterator::SELF_FIRST
                );
            }

            // Cache theme files
            foreach ($iterators as $iteratorRootUrl => $iterator) {
                foreach ($iterator as $item) {
                    if (!$item->isDir() && preg_match('%\.(png|jpe?g|gif|svg|mp[34]|web[mp])$%i', $item)) {
                        // Get the basename once
                        $itemBasename = basename($item);

                        // Store the file in cache
                        $filesToCache[] = $iteratorRootUrl . '/themes/' . Stephino_Rpg_Config::get()->core()->getTheme() . '/' . $iterator->getSubPathName();
                    }
                }
            }
            
            // Update the result
            $result = str_replace(
                array(
                    '__FILES__',
                    '__VERSION__',
                    '__OFFLINE_FILE__',
                ), 
                array(
                    json_encode($filesToCache, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                    Stephino_Rpg_Utils_Media::getPwaVersion(true),
                    $offlineFile,
                ), 
                file_get_contents($pwaPath)
            );
        }
        
        return $result;
    }
    
    /**
     * Get the Platformer JS or the JSON tile map file if the viewData GET parameter was set
     * 
     * @return string
     */
    protected static function _getGamePtf() {
        $result = '';
        
        // Get a JSON definition
        if (null !== $gameId = Stephino_Rpg_Utils_Sanitizer::getViewData()) {
            $result = json_encode(Stephino_Rpg_Db::get()->modelPtfs()->getTileMap((int) $gameId));
        } else {
            // Get the main game script
            if (is_file($ptfPath = STEPHINO_RPG_ROOT . '/ui/js/ptf/' . Stephino_Rpg_Renderer_Ajax::FILE_PTF_MAIN . '.js')) {
                $result = file_get_contents($ptfPath);
            }
        }
        return $result;
    }
}

/*EOF*/