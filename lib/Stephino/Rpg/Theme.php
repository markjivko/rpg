<?php

/**
 * Stephino_Rpg
 * 
 * @title      RPG Theme data
 * @desc       Entry points handler
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Theme {
    
    /**
     * Maximum name length
     */
    const MAX_LENGTH_NAME = 50;
    
    /**
     * Maximum description length
     */
    const MAX_LENGTH_DESCRIPTION = 200;
    
    /**
     * Default theme (pre-installed)
     */
    const THEME_DEFAULT = 'default';
    
    /**
     * Themes slug prefix ("stephino-rpg-{prefix}-{theme-name}")
     */
    const THEME_SLUG_PREFIX = 'theme';
    
    // Folders
    const FOLDER_AUDIO     = 'audio';
    const FOLDER_CSS       = 'css';
    const FOLDER_I18N      = 'i18n';
    const FOLDER_IMG       = 'img';
    const FOLDER_IMG_PH    = 'img/ph';
    const FOLDER_IMG_STORY = 'img/story';
    const FOLDER_IMG_UI    = 'img/ui';
    const FOLDER_TXT       = 'txt';
    
    // Fles
    const FILE_ABOUT        = 'about.json';
    const FILE_CONFIG       = 'config.json';
    const FILE_I18N         = 'i18n.php';
    const FILE_AUDIO_EVENTS = 'audio/events.json';
    const FILE_CSS_STYLE    = 'css/style.css';
    
    /**
     * Config.json keys
     */
    const CONFIG_THEME_NAME        = 'name';
    const CONFIG_THEME_DESCRIPTION = 'description';
    const CONFIG_KEYS = array(
        self::CONFIG_THEME_NAME,
        self::CONFIG_THEME_DESCRIPTION,
    );
    
    /**
     * About.json keys
     */
    const ABOUT_THEME_AUTHOR_NAME = 'themeAuthorName';
    const ABOUT_THEME_AUTHOR_URL  = 'themeAuthorUrl';
    const ABOUT_KEYS = array(
        self::ABOUT_THEME_AUTHOR_NAME,
        self::ABOUT_THEME_AUTHOR_URL,
    );
    
    /**
     * Singleton Theme instances
     * 
     * @var Stephino_Rpg_Theme[]|null[]
     */
    protected static $_instances = array();
    
    /**
     * Theme slug
     * 
     * @var string
     */
    protected $_themeSlug = null;
    
    /**
     * Theme path
     * 
     * @var string
     */
    protected $_themePath = null;
    
    /**
     * Theme URL
     * 
     * @var string
     */
    protected $_themeUrl = null;
    
    /**
     * Theme name
     * 
     * @var string
     */
    protected $_themeName = '';
    
    /**
     * Theme description
     * 
     * @var string
     */
    protected $_themeDescription = '';
    
    /**
     * Theme author name
     * 
     * @var string
     */
    protected $_themeAuthorName = '';
    
    /**
     * Theme author URL
     * 
     * @var string
     */
    protected $_themeAuthorUrl = '';
    
    /**
     * Get a Singleton instance if the theme slug is valid
     * 
     * @param string $themeSlug Theme slug
     * @return Stephino_Rpg_Theme|null
     */
    public static function get($themeSlug) {
        $themeSlug = trim($themeSlug);
        
        if (!isset(self::$_instances[$themeSlug])) {
            try {
                self::$_instances[$themeSlug] = new self($themeSlug);
            } catch (Exception $exc) {
                Stephino_Rpg_Log::check() && Stephino_Rpg_Log::warning(
                    'self::get(' . $themeSlug . '): ' . $exc->getMessage()
                );
                self::$_instances[$themeSlug] = null;
            }
        }
        
        return self::$_instances[$themeSlug];
    }
    
    /**
     * Load theme information
     * 
     * @param string $themeSlug Theme slug
     * @throws Exception
     */
    protected function __construct($themeSlug) {
        // Theme slug must be lowercase letters, numbers and dashes
        if (!preg_match('%[a-z\-\d]+%', $themeSlug)) {
            throw new Exception('Invalid theme slug');
        }
        
        // Store the slug
        $this->_themeSlug = $themeSlug;
        
        // Default theme
        if (self::THEME_DEFAULT === $themeSlug) {
            $this->_themePath = Stephino_Rpg::get()->isPro()
                ? (STEPHINO_RPG_PRO_ROOT. '/' . Stephino_Rpg::FOLDER_THEMES . '/' . self::THEME_DEFAULT)
                : (STEPHINO_RPG_ROOT. '/' . Stephino_Rpg::FOLDER_THEMES . '/' . self::THEME_DEFAULT);
            $this->_themeUrl  = Stephino_Rpg_Utils_Media::getPluginsUrl(
                Stephino_Rpg::get()->isPro()
            ) . '/' . Stephino_Rpg::FOLDER_THEMES . '/' . self::THEME_DEFAULT;
        } else {
            $uploadDir = wp_upload_dir();
            $this->_themePath = rtrim($uploadDir['basedir'], '/\\') . '/' . Stephino_Rpg::PLUGIN_SLUG . '/' . Stephino_Rpg::FOLDER_THEMES . '/' . $this->getThemeSlug();
            $this->_themeUrl  = rtrim($uploadDir['baseurl'], '/\\') . '/' . Stephino_Rpg::PLUGIN_SLUG . '/' . Stephino_Rpg::FOLDER_THEMES . '/' . $this->getThemeSlug();
            
            // Enforce HTTPS
            if (is_ssl()) {
                $this->_themeUrl = preg_replace('%^http\:\/\/%i', 'https://', $this->_themeUrl);
            }
        }
        
        // Prepare the main theme file path
        if (!file_exists($aboutFilePath = $this->getFilePath(self::FILE_ABOUT))) {
            throw new Exception('"' . self::FILE_ABOUT . '" file missing');
        }
        if (!is_array($aboutData = @json_decode(file_get_contents($aboutFilePath), true))) {
            throw new Exception('Invalid "' . self::FILE_ABOUT . '" file');
        }

        // Load the theme name and description from config.json
        if (file_exists($configFilePath = $this->getFilePath(self::FILE_CONFIG))) {
            if (is_array($configData = @json_decode(file_get_contents($configFilePath), true))) {
                if (isset($configData[Stephino_Rpg_Config_Core::KEY])) {
                    foreach (self::CONFIG_KEYS as $configKey) {
                        if (isset($configData[Stephino_Rpg_Config_Core::KEY][$configKey])) {
                            // Prepare the property
                            $themeProperty = "_theme" . ucfirst($configKey);
                            
                            // Store the value
                            $this->$themeProperty = $configData[Stephino_Rpg_Config_Core::KEY][$configKey];
                        }
                    }
                }
            }
        }
        
        // Load the translations
        if (null !== Stephino_Rpg_Config::lang()) {
            // Prepare the strings
            $i18nData = null;
            
            // Stored locally with gettext for the default theme
            if (self::THEME_DEFAULT === $themeSlug) {
                // Prepare the i18n file
                if (is_file($i18nPath = STEPHINO_RPG_ROOT . '/' . Stephino_Rpg::FOLDER_THEMES . '/' . self::THEME_DEFAULT . '/' . self::FILE_I18N)) {
                    require $i18nPath;

                    // Reload the array
                    $i18nData = $stephino_rpg_i18n;
                }
            } else {
                // Stored remotely for other themes
                if (file_exists($i18nPath = $this->getFilePath(self::FOLDER_I18N . '/config_' . Stephino_Rpg_Config::lang() . '.json'))) {
                    $i18nData = @json_decode(file_get_contents($i18nPath), true);
                }
            }
            
            // Valid data found for the current language
            if (is_array($i18nData)) {
                foreach (self::CONFIG_KEYS as $configKey) {
                    if (isset($i18nData[Stephino_Rpg_Config_Core::KEY . '.' . $configKey])) {
                        // Prepare the property
                        $themeProperty = "_theme" . ucfirst($configKey);

                        // Store the value
                        $this->$themeProperty = $i18nData[Stephino_Rpg_Config_Core::KEY . '.' . $configKey];
                    }
                }
            }
        }
        
        // Store the extra information from about.json
        foreach (self::ABOUT_KEYS as $aboutKey) {
            if (!isset($aboutData[$aboutKey]) || !is_string($aboutData[$aboutKey]) || !strlen($aboutData[$aboutKey])) {
                throw new Exception('"' . $aboutKey . '" tag missing from "' . self::FILE_ABOUT . '"');
            }
            
            // Prepare the property
            $themeProperty = "_$aboutKey";
            
            // Store the value
            $this->$themeProperty = $aboutData[$aboutKey];
        }
    }
    
    /**
     * For the default PRO theme, check if the requested resource should be loaded from stephino-rpg instead<br/>
     * "stephino-rpg" files take precedence over "stephino-rpg-pro" files
     * 
     * @param string $relativePath
     * @return boolean
     */
    protected function _isLocalResource($relativePath = null) {
        $pathTail = (null !== $relativePath ? ('/' . $relativePath) : '');
        
        return file_exists(STEPHINO_RPG_ROOT. '/' . Stephino_Rpg::FOLDER_THEMES . '/' . self::THEME_DEFAULT . $pathTail);
    }
    
    /**
     * Check the current configuration against local resources, adding and removing files and folders as needed<br/>
     * Only works if the current theme is active, non-default, the PRO plugin is present and the configuration language is English
     */
    public function checkResources() {
        // Game Mechanics save of active non-default theme
        if (!$this->isDefault() && $this->isActive() && null === Stephino_Rpg_Config::lang() && Stephino_Rpg::get()->isPro()) {
            // Prepare the IDs
            $configIds = array();
            
            // Fetch the collection IDs
            foreach(Stephino_Rpg_Config::get()->all() as $configSet) {
                if ($configSet instanceof Stephino_Rpg_Config_Item_Collection) {
                    foreach($configSet->getAll() as $configSetItem) {
                        $configCollection = $configSetItem->keyCollection();
                        if (!isset($configIds[$configCollection])) {
                            $configIds[$configCollection] = array();
                        }
                        $configIds[$configCollection][] = $configSetItem->getId();
                    }
                }
            }
            
            // Building audios
            if (is_array($buildingAudios = Stephino_Rpg_Utils_Folder::get()->fileSystem()->dirlist(
                $this->getFilePath(self::FOLDER_AUDIO . '/' . Stephino_Rpg_Config_Buildings::KEY),
                false
            ))) {
                $buildingAudiosIds = array();
                foreach ($buildingAudios as $fileInfo) {
                    if ('f' === $fileInfo['type']) {
                        $buildingId = intval(preg_replace('%\.\w+%', '', $fileInfo['name']));
                        if ($buildingId > 0 && !in_array($buildingId, $buildingAudiosIds)) {
                            $buildingAudiosIds[] = $buildingId;
                        }
                        
                        // Item removed
                        if (!in_array($buildingId, $configIds[Stephino_Rpg_Config_Buildings::KEY])) {
                            Stephino_Rpg_Utils_Folder::get()->fileSystem()->delete(
                                $this->getFilePath(self::FOLDER_AUDIO . '/' . Stephino_Rpg_Config_Buildings::KEY . '/' . $fileInfo['name'])
                            );
                        }
                    }
                }
                
                // Get the new buildings
                foreach (array_diff($configIds[Stephino_Rpg_Config_Buildings::KEY], $buildingAudiosIds) as $newBuildingId) {
                    foreach (array('webm', 'mp3') as $audioExtension) {
                        Stephino_Rpg_Utils_Folder::get()->fileSystem()->copy(
                            STEPHINO_RPG_PRO_ROOT . '/' . Stephino_Rpg::FOLDER_THEMES . '/' . self::THEME_DEFAULT . '/' . self::FOLDER_AUDIO . '/' . Stephino_Rpg_Config_Buildings::KEY . '/1.' . $audioExtension, 
                            $this->getFilePath(self::FOLDER_AUDIO . '/' . Stephino_Rpg_Config_Buildings::KEY . '/' . $newBuildingId . '.' . $audioExtension)
                        );
                    }
                }
            }
            
            /**
             * Add placeholder files as needed for this collection
             * 
             * @param string $configCollection
             */
            $updatePlaceholders = function($configCollection, $collectionId) {
                $collectionPath = $this->getFilePath(self::FOLDER_IMG_STORY . '/' . $configCollection . '/' . $collectionId);

                // Create the holder
                if (!Stephino_Rpg_Utils_Folder::get()->fileSystem()->is_dir($collectionPath)) {
                    Stephino_Rpg_Utils_Folder::get()->fileSystem()->mkdir($collectionPath);
                }
                
                // Prepare the search pattern
                $globPattern = Stephino_Rpg::FOLDER_THEMES . '/' . self::THEME_DEFAULT . '/' . self::FOLDER_IMG_STORY . '/' . $configCollection . '/1/*.*';
                
                // Prepare the placeholder files
                $phFiles = array();
                foreach (array_merge(
                    glob(STEPHINO_RPG_ROOT . '/' . $globPattern),
                    glob(STEPHINO_RPG_PRO_ROOT . '/' . $globPattern)
                ) as $phFilePath) {
                    // Ignore levels and video files
                    if (!preg_match('%(?:\.mp4|\-\d+\.\w+)$%i', $phFilePath)) {
                        $phFiles[basename($phFilePath)] = $phFilePath;
                    }
                }
                    
                // Add them as needed
                foreach ($phFiles as $phFileName => $phFilePath) {
                    if (!Stephino_Rpg_Utils_Folder::get()->fileSystem()->is_file($phDestinationFilePath = $collectionPath . '/' . $phFileName)) {
                        Stephino_Rpg_Utils_Folder::get()->fileSystem()->copy($phFilePath, $phDestinationFilePath);
                    }
                }
            };
            
            // Story elements
            foreach ($configIds as $configCollection => $configCollectionIds) {
                // Get the folders
                if (is_array($collectionFolders = Stephino_Rpg_Utils_Folder::get()->fileSystem()->dirlist(
                    $this->getFilePath(self::FOLDER_IMG_STORY . '/' . $configCollection)
                ))) {
                    // Initialize the local IDs
                    $collectionIds = array();
                    foreach ($collectionFolders as $folderInfo) {
                        if ('d' === $fileInfo['type']) {
                            $collectionId = intval($fileInfo['name']);
                            if ($collectionId > 0 && !in_array($collectionId, $collectionIds)) {
                                $collectionIds[] = $collectionId;
                            }
                            
                            // Configuration item removed
                            if (!in_array($collectionId, $configCollectionIds)) {
                                Stephino_Rpg_Utils_Folder::get()->fileSystem()->delete(
                                    $this->getFilePath(self::FOLDER_IMG_STORY . '/' . $configCollection . '/' . $fileInfo['name']),
                                    true
                                );
                            } else {
                                // Replace missing files with palceholders
                                $updatePlaceholders($configCollection, $collectionId);
                            }
                        }
                    }
                    
                    // Append new folders with default placeholders
                    foreach (array_diff($configCollectionIds, $collectionIds) as $newCollectionId) {
                        $updatePlaceholders($configCollection, $newCollectionId);
                    }
                }
            }
        }
    }
    
    /**
     * Activate the current theme
     * 
     * @throws Exception
     */
    public function activate() {
        if (!Stephino_Rpg::get()->isPro()) {
            throw new Exception(__('You need to unlock the game to activate themes', 'stephino-rpg'));
        }
        if ($this->isActive()) {
            throw new Exception(__('Theme already activated', 'stephino-rpg'));
        }
        
        // Get the configuration data
        $configData = null;
        if (is_file($this->getFilePath(self::FILE_CONFIG))) {
            $configData = json_decode(
                file_get_contents(
                    $this->getFilePath(self::FILE_CONFIG)
                ),
                true
            );
        }
        
        // Invalid configuration array
        if (!is_array($configData)) {
            throw new Exception('Theme missing "' . self::FILE_CONFIG . '"');
        }
        
        // Remove all game options
        Stephino_Rpg_Pro::get()->purge();
        
        // Store the theme to the game cache
        Stephino_Rpg_Utils_Themes::setActive($this->getThemeSlug());
        
        // Get the current language
        $currentLocale = Stephino_Rpg_Config::lang(true);
        
        // Set the default configuration
        Stephino_Rpg_Config::lang(false, Stephino_Rpg_Utils_Lingo::LANG_EN);
        Stephino_Rpg_Config::set($configData);
        Stephino_Rpg_Config::save();
        
        // Restore the current locale
        Stephino_Rpg_Config::lang(false, $currentLocale);
        
        // Outside theme, load config_{lang}.json files into DB
        if (!$this->isDefault()) {
            // Force English to load the json file fully into DB
            Stephino_Rpg_Utils_Lingo::setLocale(Stephino_Rpg_Utils_Lingo::LANG_EN);
            
            // Load secondary languages
            foreach (array_keys(Stephino_Rpg_Utils_Lingo::ALLOWED_LANGS) as $locale) {
                if (Stephino_Rpg_Utils_Lingo::LANG_EN === $locale) {
                    continue;
                }

                // Get the language code
                $langCode = preg_replace('%_\w+$%i', '', $locale);

                // Get the configuration data
                $userLabels = null;
                if (is_file($langPath = $this->getFilePath(self::FOLDER_I18N . '/config_' . $langCode . '.json'))) {
                    $userLabels = json_decode(file_get_contents($langPath), true);
                }

                // Set the language-specific configuration
                if (is_array($userLabels)) {
                    Stephino_Rpg_Config::lang(false, $locale);
                    Stephino_Rpg_Config::set(
                        Stephino_Rpg_Config::i18n(
                            $this->getThemeSlug(),
                            $configData,
                            $userLabels
                        )
                    );
                    Stephino_Rpg_Config::save();
                }
            }
            
            // Restore the locale
            Stephino_Rpg_Utils_Lingo::setLocale($currentLocale);
        }
        
        // Cache reset
        Stephino_Rpg_Cache_Game::get()->write(Stephino_Rpg_Cache_Game::KEY_MEDIA_CHANGED, time());
    }
    
    /**
     * Save the configuration for outside themes
     * 
     * @param string $i18nLang   (optional) I18n configuration language; default <b>null</b>
     * @param array  $i18nConfig (optional) I18n configuration array; default <b>[]</b>
     * @return boolean|null True on success, false on failure, null if cannot store the config locally
     * @throws Exception
     */
    public function store($i18nLang = null, $i18nConfig = array()) {
        if (Stephino_Rpg::get()->isPro() && $this->isActive() && !$this->isDefault()) {
            return Stephino_Rpg_Utils_Folder::get()->fileSystem()->put_contents(
                $this->getFilePath(
                    null === $i18nLang
                        ? self::FILE_CONFIG
                        : self::FOLDER_I18N . '/config_' . $i18nLang . '.json'
                ), 
                null === $i18nLang
                    // Do not store sensitive info in the theme config
                    ? Stephino_Rpg_Config::export(true, true)
                    : json_encode($i18nConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );
        }
    }
    
    /**
     * Duplicate the current theme
     * 
     * @param string $themeName Theme name
     * @return boolean
     * @throws Exception
     */
    public function duplicate($themeName) {
        // Validate the theme name
        if (!strlen($themeName)) {
            throw new Exception(__('Theme name is mandatory', 'stephino-rpg'));
        }
        if (strlen($themeName) > self::MAX_LENGTH_NAME) {
            throw new Exception(
                sprintf(
                    __('Theme name should have less than %d characters', 'stephino-rpg'),
                    self::MAX_LENGTH_NAME
                )
            );
        }
        
        // Prepare the new theme slug
        $themeSlug = Stephino_Rpg_Utils_Themes::getSlug($themeName);
        if (self::THEME_DEFAULT == $themeSlug || Stephino_Rpg::FOLDER_THEMES == $themeName || !strlen($themeSlug)) {
            throw new Exception(__('Choose another name for your theme', 'stephino-rpg'));
        }
        
        // Get the list of installed themes
        $installedThemes = Stephino_Rpg_Utils_Themes::getInstalled();
        
        // Theme slugs must be unique
        if (isset($installedThemes[$themeSlug])) {
            throw new Exception(__('Theme name already used', 'stephino-rpg'));
        }
        
        // Prepare the source folders
        $sourceDirs = array($this->_themePath);
        $configPath = $sourceDirs[0] . '/' . self::FILE_CONFIG;
        
        // If the theme is Default/PRO, overwrite remote resources with local ones
        if ($this->getThemeSlug() === self::THEME_DEFAULT && Stephino_Rpg::get()->isPro()) {
            $sourceDirs[] = STEPHINO_RPG_ROOT . '/' . Stephino_Rpg::FOLDER_THEMES . '/' . self::THEME_DEFAULT;
            $configPath = $sourceDirs[1] . '/' . self::FILE_CONFIG;
        }
        
        // Prepare the configuration data
        if (!Stephino_Rpg_Utils_Folder::get()->fileSystem()->is_file($configPath)) {
            throw new Exception(__('Parent theme missing configuration file', 'stephino-rpg'));
        }
        $configData = @json_decode(Stephino_Rpg_Utils_Folder::get()->fileSystem()->get_contents($configPath), true);
        
        // Validate and update the theme name
        if (!is_array($configData) || !isset($configData[Stephino_Rpg_Config_Core::KEY]) || !is_array($configData[Stephino_Rpg_Config_Core::KEY])) {
            throw new Exception(__('Invalid parent configuration file', 'stephino-rpg'));
        }
        $configData[Stephino_Rpg_Config_Core::KEY]['name'] = $themeName;
        
        // Prepare the destination folder
        $destinationDir = Stephino_Rpg_Utils_Folder::get()->baseDir() . '/' . Stephino_Rpg::PLUGIN_SLUG . '/' . Stephino_Rpg::FOLDER_THEMES . '/' . $themeSlug;

        // Prepare the parent directory
        if (!Stephino_Rpg_Utils_Folder::get()->fileSystem()->is_dir(dirname($destinationDir))) {
            if (!Stephino_Rpg_Utils_Folder::get()->fileSystem()->is_dir(dirname($destinationDir, 2))) {
                Stephino_Rpg_Utils_Folder::get()->fileSystem()->mkdir(dirname($destinationDir, 2));
            }
			if (!Stephino_Rpg_Utils_Folder::get()->fileSystem()->mkdir(dirname($destinationDir))) {
				throw new Exception(__('Could not create new theme directory', 'stephino-rpg'));
			}
		}
        
        // Move the files
        foreach ($sourceDirs as $sourceDir) {
            Stephino_Rpg_Utils_Folder::get()->copy($sourceDir, $destinationDir);
        }
        
        // Update the configuration
        Stephino_Rpg_Utils_Folder::get()->fileSystem()->put_contents(
            $destinationDir . '/' . self::FILE_CONFIG, 
            json_encode($configData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
        
        // Storing i18n data in local json files instead of i18n.php coupled with gettext
        if (Stephino_Rpg_Utils_Folder::get()->fileSystem()->is_file($destinationDir . '/' . self::FILE_I18N)) {
            Stephino_Rpg_Utils_Folder::get()->fileSystem()->delete($destinationDir . '/' . self::FILE_I18N);
        }
        if (!Stephino_Rpg_Utils_Folder::get()->fileSystem()->is_dir($destinationDir . '/' . self::FOLDER_I18N)) {
            Stephino_Rpg_Utils_Folder::get()->fileSystem()->mkdir($destinationDir . '/' . self::FOLDER_I18N);
        }
        
        // Convert gettext values to static .json files
        if ($this->isDefault()) {
            // Get the current user locale
            $currentLocale = Stephino_Rpg_Config::lang(true);
        
            // Prepare the i18n file
            $i18nPath = STEPHINO_RPG_ROOT . '/' . Stephino_Rpg::FOLDER_THEMES . '/' . self::THEME_DEFAULT . '/' . self::FILE_I18N;
            
            // Load secondary languages
            foreach (array_keys(Stephino_Rpg_Utils_Lingo::ALLOWED_LANGS) as $locale) {
                if (Stephino_Rpg_Utils_Lingo::LANG_EN === $locale) {
                    continue;
                }
                
                // Reload the text-domain
                Stephino_Rpg_Utils_Lingo::setLocale($locale);
                
                // Reload the array
                $stephino_rpg_i18n = null;
                require $i18nPath;
                
                // Export the values
                if (is_array($stephino_rpg_i18n)) {
                    // Get the language code
                    $langCode = preg_replace('%_\w+$%i', '', $locale);

                    // Create the translations file
                    Stephino_Rpg_Utils_Folder::get()->fileSystem()->put_contents(
                        $destinationDir . '/'. self::FOLDER_I18N . '/config_' . $langCode . '.json',
                        json_encode($stephino_rpg_i18n, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                    );
                }
            }
            
            // Restore the locale
            Stephino_Rpg_Utils_Lingo::setLocale($currentLocale);
        }
        
        // Update the about.json file
        return Stephino_Rpg_Utils_Folder::get()->fileSystem()->put_contents(
            $destinationDir . '/' . self::FILE_ABOUT, 
            json_encode(
                array(
                    self::ABOUT_THEME_AUTHOR_NAME => get_bloginfo('name'),
                    self::ABOUT_THEME_AUTHOR_URL  => get_bloginfo('url'),
                ), 
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            )
        );
    }
    
    /**
     * Remove the current theme
     * 
     * @return boolean
     */
    public function delete() {
        // Remove the theme folder
        $result = !$this->isDefault() && !$this->isActive()
            ? Stephino_Rpg_Utils_Folder::get()->fileSystem()->delete(
                Stephino_Rpg_Utils_Folder::get()->baseDir() . '/' . Stephino_Rpg::PLUGIN_SLUG . '/' . Stephino_Rpg::FOLDER_THEMES . '/' . $this->getThemeSlug(),
                true
            )
            : false;
        
        // Remove access to theme object
        if ($result) {
            unset(self::$_instances[$this->getThemeSlug()]);
        }
        
        return $result;
    }
    
    /**
     * Get an absolute URL to this file
     * 
     * @param string  $relativePath Relative file path
     * @param boolean $forceLocal   (optional) Get the URL of a local (default theme) file/folder instead; default <b>false</b>
     * @return string
     */
    public function getFileUrl($relativePath = null, $forceLocal = false) {
        $pathTail = (null !== $relativePath ? ('/' . $relativePath) : '');
        
        // Prepare the file URL
        $result = $this->_themeUrl . $pathTail;
        
        // Pro resources that are stored locally instead of stephino-rpg-pro
        if ($forceLocal || $this->getThemeSlug() === self::THEME_DEFAULT && Stephino_Rpg::get()->isPro() && $this->_isLocalResource($relativePath)) {
            $result = Stephino_Rpg_Utils_Media::getPluginsUrl() . '/' . Stephino_Rpg::FOLDER_THEMES . '/' . self::THEME_DEFAULT .  $pathTail;
        }
        
        return $result;
    }
    
    /**
     * Get an absolute path to this file
     * 
     * @param string  $relativePath (optional) Relative file path; default <b>null</b>
     * @param boolean $forceLocal   (optional) Get the path of a local (default theme) file/folder instead; default <b>false</b>
     * @return string
     */
    public function getFilePath($relativePath = null, $forceLocal = false) {
        // Clean-up the path
        $relativePath = Stephino_Rpg_Utils_Sanitizer::getMediaPath($relativePath);
        
        // Prepare the path ending
        $pathTail = strlen($relativePath) ? ('/' . $relativePath) : '';
        
        // Prepare the file path
        $result = $this->_themePath . $pathTail;
        
        // Pro resources that are stored locally instead of stephino-rpg-pro
        if ($forceLocal || ($this->getThemeSlug() === self::THEME_DEFAULT && Stephino_Rpg::get()->isPro() && $this->_isLocalResource($relativePath))) {
            $result = STEPHINO_RPG_ROOT . '/' . Stephino_Rpg::FOLDER_THEMES . '/' . self::THEME_DEFAULT . $pathTail;
        }
        
        return $result;
    }
    
    /**
     * Is this theme currently active?
     * 
     * @return boolean
     */
    public function isActive() {
        return $this->getThemeSlug() === Stephino_Rpg_Cache_Game::get()->read(Stephino_Rpg_Cache_Game::KEY_THEME, self::THEME_DEFAULT);
    }
    
    /**
     * Is this the default theme?
     * 
     * @return boolean
     */
    public function isDefault() {
        return self::THEME_DEFAULT == $this->getThemeSlug();
    }
    
    /**
     * Theme slug
     * 
     * @return string
     */
    public function getThemeSlug() {
        return $this->_themeSlug;
    }
    
    /**
     * Theme name
     * 
     * @return string
     */
    public function getName() {
        return $this->_themeName;
    }
    
    /**
     * Theme description
     * 
     * @return string
     */
    public function getDescription() {
        return $this->_themeDescription;
    }
    
    /**
     * Theme author name
     * 
     * @return string
     */
    public function getAuthor() {
        return $this->_themeAuthorName;
    }
    
    /**
     * Theme author URL
     * 
     * @return string
     */
    public function getAuthorUrl() {
        return $this->_themeAuthorUrl;
    }
}

/*EOF*/