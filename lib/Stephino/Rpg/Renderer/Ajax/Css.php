<?php

/**
 * Stephino_Rpg_Renderer_Ajax_Css
 * 
 * @title      CSS Animations Renderer - delivered through AJAX
 * @desc       Creates CSS animations
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Renderer_Ajax_Css {

    /**
     * Vendor prefix for Chrome, Safari, newest versions of Opera, almost all
     * iOS browsers (including Firefox for iOS)
     */
    const VENDOR_WEBKIT = 'webkit';
    
    /**
     * Vendor prefix for Internet Explorer and Microsoft Edge
     */
    const VENDOR_MS = 'ms';
    
    /**
     * Vendor prefix for Firefox
     */
    const VENDOR_MOZ = 'moz';
    
    /**
     * Vendor prefix for Opera
     */
    const VENDOR_O = 'o';
    
    // Animation names
    const ANIMATION_NAME_WORLD  = 'w';
    const ANIMATION_NAME_ISLAND = 'i';
    const ANIMATION_NAME_CITY   = 'c';
    
    // Animation qualifiers
    const ANIMATION_QUALIFIER_UC     = 'uc';
    const ANIMATION_QUALIFIER_VACANT = 'v';
    
    /**
     * Get the animations and game CSS rules
     * 
     * @return string|null
     */
    public static function ajaxCss() {
        // Game rules (not view-specific)
        if (null === $viewName = Stephino_Rpg_Utils_Sanitizer::getView()) {
            return self::_getGameCss();
        }
        
        // Get the config ID; validate configuration ID below
        $identifier = Stephino_Rpg_Utils_Sanitizer::getConfigId();
        
        // Invalid identifier
        if (in_array($viewName, array(Stephino_Rpg_Renderer_Ajax::VIEW_CITY, Stephino_Rpg_Renderer_Ajax::VIEW_ISLAND)) && ($identifier === null || $identifier <= 0)) {
            return null;
        }
        
        // Animation and Game CSS
        return self::_renderForView($viewName, $identifier);
    }
    
    /**
     * Get the cached CSS animations and core Game rules for this view
     * 
     * @param string $view       View name
     * @param string $identifier (optional) Get part of the cached rules; default <b>null</b>
     * @return string|null CSS animations
     */
    protected static function _renderForView($view, $identifier = null) {
        // Prepare the result
        $result = '';

        // Valid view
        if (in_array($view, Stephino_Rpg_Renderer_Ajax::AVAILABLE_VIEWS)) {
            // Get the cache
            $cachedAnimations = Stephino_Rpg_Cache_Game::get()->read(Stephino_Rpg_Cache_Game::KEY_ANIMATIONS, array());

            // Valid animation found
            if (is_array($cachedAnimations) && isset($cachedAnimations[$view])) {
                if (null === $identifier) {
                    if (is_string($cachedAnimations[$view]) && strlen($cachedAnimations[$view])) {
                        $result = $cachedAnimations[$view];
                    }
                } else {
                    if (is_array($cachedAnimations[$view]) && isset($cachedAnimations[$view][$identifier]) && is_string($cachedAnimations[$view][$identifier]) && strlen($cachedAnimations[$view][$identifier])) {
                        $result = $cachedAnimations[$view][$identifier];
                    }
                }
            }
            
            // Append the localized game CSS rules
            $result .= PHP_EOL . PHP_EOL . self::_getGameCss($view);
        }

        // Get the plugin details
        $pluginName = Stephino_Rpg::PLUGIN_NAME;
        $pluginVersion = Stephino_Rpg::PLUGIN_VERSION;
        
        // Copyright year
        $year = date('Y');
        
        // Prepare the ID
        $view = ucfirst($view);
        $cssId = $view . (null !== $identifier ? '::' . $identifier : '');
        
        // Prepare the header
        $cssHeader = <<<"CSS"
/**
 * $pluginName CSS - $view
 * 
 * @id         $cssId
 * @title      Game Renderer
 * @desc       Optimized CSS animations and layout rules
 * @copyright  (c) $year, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @version    $pluginVersion
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
[anim]{will-change:transform,opacity,z-index;position:absolute;overflow:hidden;}[anim]>div{will-change:transform,top;position:absolute;}.no-anim [anim],.no-anim [anim]>div{display:none;-webkit-animation:none;animation:none;}
CSS;

        return strlen($result) ? ($cssHeader . $result) : null;
    }
    
    /**
     * Generate the animations CSS rules for all available views and store them in cache
     */
    public static function generate() {
        // Prepare the data
        $data = array();

        // Go through the available views
        foreach (Stephino_Rpg_Renderer_Ajax::AVAILABLE_VIEWS as $view) {
            // Prepare the view result
            $data[$view] = '';

            // Prepare the animation method
            if (is_callable(array(get_class(), $viewMethod = '_getAnimation' . ucfirst($view)))) {
                // Get the animation
                $data[$view] = call_user_func(array(get_class(), $viewMethod));
            }
        }

        // Store in cache
        Stephino_Rpg_Cache_Game::get()->write(Stephino_Rpg_Cache_Game::KEY_ANIMATIONS, $data);
        
        // Force cache reset and PWA app version change, forcing local storage reset
        Stephino_Rpg_Cache_Game::get()->write(Stephino_Rpg_Cache_Game::KEY_MEDIA_CHANGED, time());
    }
    
    /**
     * Get the localized game CSS
     * 
     * @param string $view (optional) Specific game CSS file to load; default <b>null</b> for <b>{Stephino_Rpg_Renderer_Ajax::COMMON_FILE_UI}.css</b>
     * @return string CSS contents
     */
    protected static function _getGameCss($view = null) {
        if (Stephino_Rpg_Renderer_Ajax::VIEW_PTF == $view) {
            $cssPath = STEPHINO_RPG_ROOT . '/' . Stephino_Rpg::FOLDER_UI_CSS . '/ptf/' . Stephino_Rpg_Renderer_Ajax::FILE_PTF_MAIN . '.css';
        } else {
            $cssPath = STEPHINO_RPG_ROOT . '/' . Stephino_Rpg::FOLDER_UI_CSS . '/game/' . (null == $view ? Stephino_Rpg_Renderer_Ajax::FILE_COMMON : $view) . '.css';
        }
        
        // Get the game CSS path
        if (is_file($cssPath)) {
            // Prepare the variables
            $variables = array(
                '__ROOT_URL__'  => Stephino_Rpg_Utils_Media::getPluginsUrl(),
                '__MEDIA_URL__' => Stephino_Rpg_Utils_Media::getAdminUrl(true, false) . '&' . http_build_query(array(
                    Stephino_Rpg_Renderer_Ajax::CALL_METHOD     => Stephino_Rpg_Renderer_Ajax::CONTROLLER_MEDIA,
                    Stephino_Rpg_Renderer_Ajax::CALL_MEDIA_PATH => '',
                ))
            );

            // Prepare the CSS script
            $cssContents = file_get_contents($cssPath);
            
            // The platformer CSS is simplistic
            if (Stephino_Rpg_Renderer_Ajax::VIEW_PTF != $view) {
                // Append the current theme's style - included with all views
                if (null !== $view) {
                    if (is_file($cssThemePath = Stephino_Rpg_Utils_Themes::getActive()->getFilePath(Stephino_Rpg_Theme::FILE_CSS_STYLE))) {
                        $cssContents .= PHP_EOL . PHP_EOL . file_get_contents($cssThemePath);
                    }
                } else {
                    // Prepend the Twitter's Bootstrap
                    if (is_file($cssBootstrapPath = STEPHINO_RPG_ROOT . '/' . Stephino_Rpg::FOLDER_UI_CSS . '/bootstrap.css')) {
                        $cssContents = file_get_contents($cssBootstrapPath) . PHP_EOL . PHP_EOL . $cssContents;
                    }
                }
            }
            
            // Replace the variables
            return str_replace(array_keys($variables), array_values($variables), $cssContents);
        }
        
        // Game file(s) removed by user
        return '';
    }

    /**
     * Get the animations for the World view
     * 
     * @return string
     */
    protected static function _getAnimationWorld() {
        // Prepare the result
        $result = '';

        // Go through the islands
        foreach (Stephino_Rpg_Config::get()->islands()->getAll() as $islandData) {
            // Create the animation rules
            $result .= self::_getAnimation(
                Stephino_Rpg_Renderer_Ajax_Cells::CELL_DATA_TYPE_ISLAND, 
                $islandData->getId(), 
                self::ANIMATION_NAME_WORLD, 
                json_decode($islandData->getWorldAnimations(), true)
            );
            
            // Add the backgrounds
            $result .= self::_getBackground(
                Stephino_Rpg_Renderer_Ajax_Cells::CELL_DATA_TYPE_ISLAND, 
                $islandData->getId()
            );
        }
        
        // Get the island statues
        foreach (Stephino_Rpg_Config::get()->islandStatues()->getAll() as $islandStatueData) {
            // Add the backgrounds
            $result .= self::_getBackground(
                Stephino_Rpg_Renderer_Ajax_Cells::CELL_DATA_TYPE_STATUE, 
                $islandStatueData->getId()
            );
        }
        
        return $result;
    }

    /**
     * Get the animations for the Island view
     * 
     * @return string
     */
    protected static function _getAnimationIsland() {
        // Prepare the result
        $result = array();

        // Go through the islands
        foreach (Stephino_Rpg_Config::get()->islands()->getAll() as $islandData) {
            // Prepare the island specific rules
            $islandRules = '';

            // Get the island animations
            $islandRules .= self::_getAnimation(
                Stephino_Rpg_Renderer_Ajax_Cells::CELL_DATA_TYPE_ISLAND, 
                $islandData->getId(), 
                self::ANIMATION_NAME_ISLAND, 
                json_decode($islandData->getIslandAnimations(), true)
            );

            // Get the vacant lot animations
            $islandRules .= self::_getAnimation(
                Stephino_Rpg_Renderer_Ajax_Cells::CELL_DATA_TYPE_ISLAND, 
                $islandData->getId() . '-' . self::ANIMATION_QUALIFIER_VACANT, 
                self::ANIMATION_NAME_ISLAND, 
                json_decode($islandData->getVacantLotAnimations(), true)
            );

            // Get all island statue animations
            foreach (Stephino_Rpg_Config::get()->islandStatues()->getAll() as $islandStatueData) {
                $islandRules .= self::_getAnimation(
                    Stephino_Rpg_Renderer_Ajax_Cells::CELL_DATA_TYPE_STATUE, 
                    $islandStatueData->getId(),
                    self::ANIMATION_NAME_ISLAND, 
                    json_decode($islandStatueData->getIslandAnimations(), true)
                );
            }

            // Get all cities animations
            foreach (Stephino_Rpg_Config::get()->cities()->getAll() as $cityData) {
                if (is_array($islandAnimations = json_decode($cityData->getIslandAnimations(), true))) {
                    foreach ($islandAnimations as $cityLevel => $cityAnimationData) {
                        $islandRules .= self::_getAnimation(
                            Stephino_Rpg_Renderer_Ajax_Cells::CELL_DATA_TYPE_CITY, 
                            $cityData->getId() . '-' . $cityLevel, 
                            self::ANIMATION_NAME_ISLAND, 
                            $cityAnimationData
                        );
                    }
                }
            }

            // Store the rules
            $result[$islandData->getId()] = $islandRules;
        }

        return $result;
    }

    /**
     * Get the animations for the City view
     * 
     * @return string
     */
    protected static function _getAnimationCity() {
        // Prepare the result
        $result = array();

        // Go through the cities
        foreach (Stephino_Rpg_Config::get()->cities()->getAll() as $configCity) {
            // Prepare the city specific rules
            $cityRules = '';

            // Get the city animations
            $cityRules .= self::_getAnimation(
                Stephino_Rpg_Renderer_Ajax_Cells::CELL_DATA_TYPE_CITY, 
                $configCity->getId(), 
                self::ANIMATION_NAME_CITY, 
                json_decode($configCity->getCityAnimations(), true)
            );
            
            // Get the under construction animations
            $cityRules .= self::_getAnimation(
                Stephino_Rpg_Renderer_Ajax_Cells::CELL_DATA_TYPE_CITY, 
                $configCity->getId() . '-' . self::ANIMATION_QUALIFIER_UC, 
                self::ANIMATION_NAME_CITY, 
                json_decode($configCity->getUnderConstructionAnimations(), true)
            );

            // Get all buildings animations
            foreach (Stephino_Rpg_Config::get()->buildings()->getAll() as $buildingData) {
                // Go through the levels
                if (is_array($levelData = json_decode($buildingData->getCityAnimations(), true))) {
                    // Add animations for each level
                    foreach ($levelData as $level => $data) {
                        $cityRules .= self::_getAnimation(
                            Stephino_Rpg_Renderer_Ajax_Cells::CELL_DATA_TYPE_BUILDING, 
                            $buildingData->getId() . '-' . $level, 
                            self::ANIMATION_NAME_CITY, 
                            $data
                        );
                    }
                }
            }

            // Store the rules
            $result[$configCity->getId()] = $cityRules;
        }

        return $result;
    }

    /**
     * Prepare common background rules
     * 
     * @param string $configFolder  Configuration item folder name
     * @param int    $configId      (optional) Configuration item ID; default <b>Stephino_Rpg_Utils_Media::FOLDER_COMMON</b> 
     * @return string
     */
    protected static function _getBackground($configFolder, $configId = null) {
        if (null === $configId) {
            $configId = Stephino_Rpg_Utils_Media::FOLDER_COMMON;
        }
        
        // Prepare the known common backgrounds
        $files = array(
            Stephino_Rpg_Renderer_Ajax_Cells::CELL_DATA_TYPE_ISLAND => array(
                '.island' => '512.png',
            ),
            Stephino_Rpg_Renderer_Ajax_Cells::CELL_DATA_TYPE_STATUE => array(
                '.statue' => '512-above.png',
            ),
        );
        
        // Get the result
        $result = '';
        if (isset($files[$configFolder])) {
            foreach ($files[$configFolder] as $cssClassName => $cssFileName) {
                // Prepare the final class name
                $finalClassName = is_numeric($configId) ? "$cssClassName-$configId" : $cssClassName; 
                
                // Get the file URL
                $fileUrl = Stephino_Rpg_Utils_Themes::getActive()->getFileUrl('img/story/' . $configFolder . '/' . $configId . '/' . $cssFileName);
                
                // Append the rule
                $result .= "$finalClassName{"
                    . "background: url(\"$fileUrl\") no-repeat transparent;"
                . "}";
            }
        }
        return $result;
    }
    
    /**
     * Prepare the animation CSS for this configuration object
     * 
     * @param string     $configFolder  Configuration item folder name
     * @param int        $configId      Configuration item ID
     * @param string     $animationName Animation name
     * @param array|null $animationData Animation data
     */
    protected static function _getAnimation($configFolder, $configId, $animationName, $animationData) {
        // Prepare the animations
        $result = array();

        // Valid animation
        if (is_array($animationData)) {
            foreach ($animationData as $animationId => $animationDataValue) {
                if (
                    is_array($animationDataValue) 
                    && isset($animationDataValue[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_FRAME_WIDTH]) 
                    && isset($animationDataValue[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_FRAME_HEIGHT]) 
                    && isset($animationDataValue[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_FRAME_DURATION]) 
                    && isset($animationDataValue[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_FRAME_STEPS]) 
                    && isset($animationDataValue[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_FRAME_SPRITE]) 
                    && isset($animationDataValue[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES]) 
                    && is_array($animationDataValue[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES])) {

                    // Prepare the animation name
                    $selectorName = $configFolder . '-' . $configId . '-' . $animationName . '-' . $animationId;

                    // Prepare the total animation length
                    $walkYDuration = 0;
                    $walkYRows = 0;
                    foreach ($animationDataValue[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES] as $keyFrame) {
                        if (isset($keyFrame[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_TRANSITION])) {
                            $walkYDuration += $keyFrame[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_TRANSITION];
                        }
                        if (isset($keyFrame[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_ROW])) {
                            if ($keyFrame[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_ROW] > $walkYRows) {
                                $walkYRows = $keyFrame[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_ROW];
                            }
                        }
                    }

                    // Convert from index (starting from 0) to count (starting from 1)
                    $walkYRows++;

                    // Prepare the core CSS rules
                    $selectorRules = array(
                        // z-index will force painting, changes rarely (max. once per "animation keyframe")
                        'width' => $animationDataValue[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_FRAME_WIDTH] . 'px',
                        'height' => $animationDataValue[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_FRAME_HEIGHT] . 'px',
                        'animation' => implode(', ', array(
                            // Quirks: translate, scale, z-index, opacity
                            $selectorName . '-q '
                            . $walkYDuration . 'ms '
                            . 'steps(2000) infinite',
                        ))
                    );
                    
                    // Get the file URL
                    $fileUrl = Stephino_Rpg_Utils_Themes::getActive()->getFileUrl(
                        'img/story/' . $configFolder . '/sprites/' . $animationDataValue[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_FRAME_SPRITE] . '.png'
                    );
        
                    // Prepare the core background CSS rules
                    $selectorBkgRules = array(
                        // top will force painting, changes rarely (max. once per "animation keyframe")
                        'width' => (
                            $animationDataValue[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_FRAME_WIDTH] 
                            * $animationDataValue[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_FRAME_STEPS]
                        ) . 'px',
                        'height' => (
                            $animationDataValue[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_FRAME_HEIGHT] 
                            * $walkYRows
                        ) . 'px',
                        'background' => 'url("' . $fileUrl . '") 0 0 no-repeat transparent',
                        'animation' => implode(', ', array(
                            // X: illusion of animation
                            $selectorName . '-x '
                            . ($animationDataValue[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_FRAME_DURATION]) . 'ms '
                            . 'steps(' 
                                . $animationDataValue[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_FRAME_STEPS] 
                            . ') infinite',
                            // Y: sprite strip switch
                            $selectorName . '-y '
                            . $walkYDuration . 'ms '
                            . 'steps(2000) infinite',
                        ))
                    );

                    // Prepare the walk-x keyframes
                    $walkXKeyframes = array(
                        '0%' => array(
                            'transform' => 'translate(0px, 0px)'
                        ),
                        '100%' => array(
                            'transform' => 'translate(' . (
                                - $animationDataValue[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_FRAME_STEPS] 
                                * $animationDataValue[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_FRAME_WIDTH]
                            ) . 'px, 0px)'
                        ),
                    );

                    // Prepare the walk-y keyframes
                    $walkYKeyframes = array();

                    // Prepare the walk-q keyframes
                    $walkQKeyframes = array();
                    foreach ($animationDataValue[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES] as $keyFrameId => $keyFrame) {
                        if (
                            isset($keyFrame[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_X]) 
                            && isset($keyFrame[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_Y]) 
                            && isset($keyFrame[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_ROW]) 
                            && isset($keyFrame[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_FLIP_X]) 
                            && isset($keyFrame[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_FLIP_Y]) 
                            && isset($keyFrame[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_TRANSITION]) 
                            && isset($keyFrame[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_ZINDEX]) 
                            && isset($keyFrame[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_OPACITY])) {

                            // Get the elapsed time
                            $walkYElapsedTime = 0;
                            for ($previousKeyframeId = 1; $previousKeyframeId < $keyFrameId; $previousKeyframeId++) {
                                if (
                                    isset($animationDataValue[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES][$previousKeyframeId]) 
                                    && isset($animationDataValue[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES][$previousKeyframeId][Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_TRANSITION])) {
                                    $walkYElapsedTime += $animationDataValue[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES][$previousKeyframeId][Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_TRANSITION];
                                }
                            }

                            // Get the percentage
                            $walkYPercentage = round($walkYElapsedTime / $walkYDuration * 100, 2);

                            // Prepare the transitions
                            $walkQRules = array(
                                'opacity' => round($keyFrame[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_OPACITY] / 100, 2),
                                'z-index' => $keyFrame[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_ZINDEX],
                                'transform' => 'translate('
                                    . (32 * $keyFrame[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_X]) . 'px, '
                                    . (32 * $keyFrame[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_Y]) . 'px'
                                . ') scale('
                                    . ($keyFrame[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_FLIP_X] ? '-1' : '1')
                                    . ', '
                                    . ($keyFrame[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_FLIP_Y] ? '-1' : '1')
                                . ')',
                            );

                            // Change the strip
                            $walkYRules = array(
                                'top' => (
                                    - $keyFrame[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_ROW] 
                                    * $animationDataValue[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_FRAME_HEIGHT]
                                ) . 'px'
                            );

                            // Store the walk-q
                            $walkQKeyframes[$walkYPercentage . '%'] = $walkQRules;

                            // Store the walk-y rules
                            $walkYKeyframes[$walkYPercentage . '%'] = $walkYRules;

                            // Keep the top unchanged between transitions
                            $walkYPercentageNext = round(
                                (
                                    $walkYElapsedTime 
                                    + $keyFrame[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_TRANSITION]
                                ) / $walkYDuration * 100, 
                                2
                            );

                            // Reached the end
                            if ($walkYPercentageNext == 100) {
                                // Store the rules
                                $walkQKeyframes[$walkYPercentageNext . '%'] = array(
                                    'opacity' => round($keyFrame[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_OPACITY] / 100, 2),
                                    'transform' => 'translate('
                                        . (32 * $keyFrame[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_X]) . 'px, '
                                        . (32 * $keyFrame[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_Y]) . 'px'
                                    . ') scale('
                                        . ($keyFrame[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_FLIP_X] ? '-1' : '1')
                                        . ', '
                                        . ($keyFrame[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_FLIP_Y] ? '-1' : '1')
                                    . ')',
                                );
                                $walkYKeyframes[$walkYPercentageNext . '%'] = array(
                                    'top' => $walkYRules['top'],
                                );
                            } else {
                                // Prepare the next keyframe
                                $keyframeNext = $animationDataValue[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES][$keyFrameId + 1];

                                // Store the rules
                                $walkQKeyframes[($walkYPercentageNext - 0.01) . '%'] = array(
                                    'opacity' => round($keyFrame[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_OPACITY] / 100, 2),
                                    'transform' => 'translate('
                                        . (32 * $keyframeNext[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_X]) . 'px, '
                                        . (32 * $keyframeNext[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_Y]) . 'px'
                                    . ') scale('
                                        . ($keyFrame[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_FLIP_X] ? '-1' : '1')
                                        . ', '
                                        . ($keyFrame[Stephino_Rpg_Config_Item_Single::TYPE_ANIM_KEYFRAMES_FLIP_Y] ? '-1' : '1')
                                    . ')',
                                );
                                $walkYKeyframes[($walkYPercentageNext - 0.01) . '%'] = array(
                                    'top' => $walkYRules['top'],
                                );
                            }
                        }
                    }

                    // Prepare the CSS rules
                    $animationRules = '';

                    // Add the selector rules
                    $animationRules .= '[anim="' . $selectorName . '"]{';
                    foreach ($selectorRules as $ruleName => $ruleValue) {
                        foreach (self::_getVendorPrefixes($ruleName) as $vendorPrefix) {
                            $animationRules .= '-' . $vendorPrefix . '-' . $ruleName . ':' . $ruleValue . ';';
                        }
                        $animationRules .= $ruleName . ':' . $ruleValue . ';';
                    }
                    $animationRules .= '}';

                    // Add the selector background rules
                    $animationRules .= '[anim="' . $selectorName . '"]>div{';
                    foreach ($selectorBkgRules as $ruleName => $ruleValue) {
                        foreach (self::_getVendorPrefixes($ruleName) as $vendorPrefix) {
                            $animationRules .= '-' . $vendorPrefix . '-' . $ruleName . ':' . $ruleValue . ';';
                        }
                        $animationRules .= $ruleName . ':' . $ruleValue . ';';
                    }
                    $animationRules .= '}';

                    // Add the keyframes
                    foreach (array('x', 'y', 'q') as $keyframeType) {
                        // Prepare the variable name
                        $keyframeVarName = 'walk' . strtoupper($keyframeType) . 'Keyframes';

                        // Invalid key
                        if (!isset($$keyframeVarName)) {
                            continue;
                        }

                        // Prepare the vendor prefixes
                        foreach (array(self::VENDOR_WEBKIT, '') as $vendorPrefix) {
                            // Open the keyframe definition
                            $animationRules .= '@' . (strlen($vendorPrefix) ? '-' . $vendorPrefix . '-' : '') . 'keyframes ' . $selectorName . '-' . $keyframeType . '{';
                            foreach ($$keyframeVarName as $kfPosition => $kfRules) {
                                // Open the KF position
                                $animationRules .= $kfPosition . '{';
                                foreach ($kfRules as $kfRuleName => $kvRuleValue) {
                                    foreach (self::_getVendorPrefixes($kfRuleName) as $kVendorPrefix) {
                                        $animationRules .= '-' . $kVendorPrefix . '-' . $kfRuleName . ':' . $kvRuleValue . ';';
                                    }
                                    $animationRules .= $kfRuleName . ':' . $kvRuleValue . ';';
                                }

                                // Close the KF position
                                $animationRules .= '}';
                            }

                            // Close the keyframe definition
                            $animationRules .= '}';
                        }
                    }

                    // Append the animation rules to the final result
                    $result[] = trim($animationRules);
                }
            }
        }
        
        return implode(' ', $result);
    }
    
    /**
     * Get the vendor prefixes for this rule
     * 
     * @param string $cssRule
     * @return string[] List of vendor prefixes
     */
    protected static function _getVendorPrefixes($cssRule) {
        $result = array();
        
        switch (strtolower($cssRule)) {
            case 'transform':
                $result[] = self::VENDOR_WEBKIT;
                $result[] = self::VENDOR_MS;
                break;
            
            case 'animation':
                $result[] = self::VENDOR_WEBKIT;
                break;
        }
        
        return $result;
    }
}

/*EOF*/