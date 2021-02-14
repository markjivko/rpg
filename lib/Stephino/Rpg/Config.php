<?php
/**
 * Stephino_Rpg_Config
 * 
 * @title      Configuration
 * @desc       Manage the RPG configuration
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Config {
    
    // Files
    const FILE_CONFIG = 'config.php';
    const FILE_I18N   = 'i18n.php';
    
    // Folders
    const FOLDER_THEMES = 'themes';
    
    // Default theme
    const THEME_DEFAULT = 'default';
    
    /**
     * 2-character user language
     * 
     * @var string
     */
    protected static $_lang;
    
    /**
     * Current user locale; defaults to en_US if $_lang is null
     * 
     * @var string
     */
    protected static $_locale;
    
    /**
     * Available configuration item classes
     * 
     * @var string[]
     */
    const CONFIG_ITEMS = array(
        Stephino_Rpg_Config_Core::class,
        Stephino_Rpg_Config_Governments::class,
        Stephino_Rpg_Config_Islands::class,
        Stephino_Rpg_Config_IslandStatues::class,
        Stephino_Rpg_Config_Cities::class,
        Stephino_Rpg_Config_Buildings::class,
        Stephino_Rpg_Config_Units::class,
        Stephino_Rpg_Config_Ships::class,
        Stephino_Rpg_Config_ResearchAreas::class,
        Stephino_Rpg_Config_ResearchFields::class,
        Stephino_Rpg_Config_Modifiers::class,
        Stephino_Rpg_Config_Tutorials::class,
        Stephino_Rpg_Config_PremiumModifiers::class,
        Stephino_Rpg_Config_PremiumPackages::class,
    );
    
    /**
     * Singleton instance of Stephino_Rpg_Config
     *
     * @var Stephino_Rpg_Config
     */
    protected static $_instance = null;
    
    /**
     * Configuration data
     *
     * @var Stephino_Rpg_Config_Item_Abstract[]
     */
    protected $_data = array();
    
    /**
     * Get a Singleton instance of Stephino_Rpg_Config
     * 
     * @return Stephino_Rpg_Config
     */
    public static function get() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * Get the current user language code (2 characters)
     * 
     * @param boolean $getLocale (optional) Get the locale ("en_US" format) instead; default <b>false</b>
     * @return string|null Lang mode: 2-letter lowercase string for current locale, null if English is used; Locale mode: locale string
     */
    public static function lang($getLocale = false) {
        if (!isset(self::$_lang)) {
            self::$_lang = null;
            self::$_locale = Stephino_Rpg_Utils_Lingo::LANG_EN;
            if (is_array($userData = Stephino_Rpg_TimeLapse::get()->userData())) {
                if (0 != $userData[Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID]) {
                    // Convert a locale ("en_US") to a 2-digit language code ("en")
                    $getCode = function($locale) {
                        return strtolower(
                            preg_replace(
                                '%^([a-z]+)_.*$%i', '$1', 
                                $locale
                            )
                        );
                    };
                    
                    // Get the current language code
                    $langCode = $getCode(get_user_locale());
                    
                    // Valid language found
                    if (strlen($langCode) && 'en' !== $langCode) {
                        // Get all available locales; "en" => "en_US"
                        $locales = array_combine(
                            array_map(
                                $getCode, 
                                array_keys(Stephino_Rpg_Utils_Lingo::ALLOWED_LANGS)
                            ),
                            array_keys(Stephino_Rpg_Utils_Lingo::ALLOWED_LANGS)
                        );
                        
                        if (isset($locales[$langCode])) {
                            self::$_lang   = $langCode;
                            self::$_locale = $locales[$langCode];
                        }
                    }
                }
            }
        }

        return $getLocale ? self::$_locale : self::$_lang;
    }
    
    /**
     * Get the definition (configuration structure)
     * 
     * @return array
     */
    public static function definition() { 
        return self::get()->_definition(!Stephino_Rpg::get()->isAdmin(), null !== self::lang());
    }
    
    /**
     * Export the current configuration to a JSON array
     * 
     * @param boolean $hideSensitive (optional) Hide sensitive fields; default <b>false</b>
     * @param boolean $prettyPrint   (optional) Pretty print; default <b>false</b>
     * @return string
     */
    public static function export($hideSensitive = false, $prettyPrint = false) {
        // Prepare the data
        $data = array_map(
            /* @var $configItem Stephino_Rpg_Config_Item_Abstract */
            function($configItem) use ($hideSensitive) {
                return $configItem->toArray($hideSensitive);
            }, 
            self::get()->_data
        );
        
        // Encode the data
        return json_encode($data, $prettyPrint ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : 0);
    }
    
    /**
     * Set the data by array
     * 
     * @param array $data Associative array
     */
    public static function set($data) {
        // Prepare the reflection
        $configReflection = new ReflectionClass(self::get());
        
        // Prepare the allowed methods
        $allowedMethods = array();
        foreach ($configReflection->getMethods(ReflectionMethod::IS_PUBLIC) as /* @var $publicMethod ReflectionMethod  */ $publicMethod) {
            if (!$publicMethod->isStatic()) {
                $allowedMethods[] = $publicMethod->getName();
            }
        }
        
        // Go through the configuration items
        foreach ($data as $configItem => $configData) {
            // Validate the method
            if (!in_array($configItem, $allowedMethods)) {
                continue;
            }
            
            // Get the object
            $configItemObject = self::get()->$configItem();
            
            // Single Item
            if ($configItemObject instanceof Stephino_Rpg_Config_Item_Single) {
                // Go through the data
                foreach ($configData as $dataKey => $dataValue) {
                    // Get the method name
                    $methodName = 'set' . ucfirst($dataKey);
                    
                    // Valid method found (Create, Update, Delete)
                    if (method_exists($configItemObject, $methodName)) {
                        $configItemObject->$methodName($dataValue);
                    }
                }
            } else if ($configItemObject instanceof Stephino_Rpg_Config_Item_Collection) {
                // Go through the single items
                foreach ($configData as $itemId => $dataArray) {
                    // Get the element (Update)
                    $configItemSingle = $configItemObject->getById($itemId);
                    
                    // Item not found (Create)
                    if (null === $configItemSingle) {
                        $configItemSingle = $configItemObject->add(array(), $itemId);
                    }
                    
                    // Valid object found
                    foreach ($dataArray as $dataKey => $dataValue) {
                        // Skip the ID
                        if ('id' === $dataKey) {
                            continue;
                        }

                        // Get the method name
                        $methodName = 'set' . ucfirst($dataKey);

                        // Valid method found
                        if (method_exists($configItemSingle, $methodName)) {
                            // Set the item
                            $configItemSingle->$methodName($dataValue);
                        }
                    }
                }
                
                // Prepare the IDs list
                $knownIds = array_keys($configData);
                
                // Missing IDs (Delete)
                foreach ($configItemObject->getAll() as $configItemSingle) {
                    if (!in_array($configItemSingle->getId(), $knownIds)) {
                        $configItemObject->delete($configItemSingle->getId());
                    }
                }
            }
        }
    }
    
    /**
     * Save the current game configuration
     * 
     * @throws Exception
     */
    public static function save() {
        if (!Stephino_Rpg::get()->isPro()) {
            throw new Exception(__('You need to unlock the game to save your changes', 'stephino-rpg'));
        }
        
        // WARNING! Without properly managing object dependencies the game will break
        Stephino_Rpg_Pro_Config::get()->save();
    }
    
    /**
     * Reset the game configuration
     */
    public static function reset() {
        if (!Stephino_Rpg::get()->isPro()) {
            throw new Exception(__('You need to unlock the game to reset the game settings', 'stephino-rpg'));
        }
        
        // WARNING! Without properly managing object dependencies the game will break
        Stephino_Rpg_Pro_Config::get()->reset(self::get()->_data);
    }

    /**
     * Get the default configuration array OR alter the provided configuration array with i18n labels
     * 
     * @param array|null $configData (optional) Saved configuration array (English); default <b>null</b> to use the default config instead
     * @param array|null $userLabels (optional) Saved user labels array (multilanguage); default <b>null</b>
     * @return array
     */
    public static function getI18n($configData = null, $userLabels = null) {
        // Get the default configuration file path
        if (is_array($configData)) {
            $result = $configData;
        } else {
            $configDefaultPath = STEPHINO_RPG_ROOT . '/' . self::FOLDER_THEMES . '/' . self::THEME_DEFAULT . '/' . self::FILE_CONFIG;
            
            // Get the configuration data
            $result = is_file($configDefaultPath)
                ? json_decode(
                    trim(
                        substr(
                            file_get_contents($configDefaultPath), 
                            15
                        )
                    ), 
                    true
                )
                : array();
            if (!is_array($result)) {
                $result = array();
            }
        }

        // Invalid format
        if (count($result)) {
            if (is_file($i18nPath = STEPHINO_RPG_ROOT . '/' . self::FOLDER_THEMES . '/' . self::THEME_DEFAULT . '/' . self::FILE_I18N)) {
                $stephino_rpg_i18n = null;
                
                // Load the file directly
                require $i18nPath;
                if (is_array($stephino_rpg_i18n)) {
                    /**
                     * Replace strings in an array at the "x.y.z" position
                     * 
                     * @param array  &$config Configuration array
                     * @param string  $key    Configuration key in "x.y.z" format
                     * @param string  $value  Value to set at provided location in $config array
                     */
                    $walk = function(&$config, $key, $value) use (&$walk) {
                        // Prepare the keys
                        $keys = explode('.', $key);
                        
                        // Get the current key
                        $keyCurrent = array_shift($keys);

                        // Valid config and key provided
                        if (isset($config[$keyCurrent])) {
                            // Reached the end of the line
                            if (!count($keys)) {
                                if (is_string($config[$keyCurrent])) {
                                    $config[$keyCurrent] = $value;
                                }
                            } else {
                                $walk($config[$keyCurrent], implode('.', $keys), $value);
                            }
                        }
                    };
                
                    // Replace internationalized values
                    foreach ($stephino_rpg_i18n as $i18nKey => $i18nValue) {
                        // Language override
                        if (is_array($userLabels) && isset($userLabels[$i18nKey])) {
                            $i18nValue = $userLabels[$i18nKey];
                        }
                        
                        // Valid key
                        if (false !== strpos($i18nKey, '.') && is_string($i18nValue)) {
                            $walk($result, $i18nKey, $i18nValue);
                        }
                    }
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Get the path to the current theme
     * 
     * @param boolean $usePro (optional) Whether to use the PRO plugin; default <b>false</b>
     * @return string
     */
    public function themePath($usePro = false) {
        // Avoid an error when called incorrectly
        $themeName = isset($this->_data[Stephino_Rpg_Config_Core::KEY])
            ? $this->core()->getTheme()
            : self::THEME_DEFAULT;
        
        // Get the configuration path
        return (Stephino_Rpg::get()->isPro() && $usePro ? STEPHINO_RPG_PRO_ROOT : STEPHINO_RPG_ROOT) . '/' . self::FOLDER_THEMES . '/' . $themeName;
    }
    
    /**
     * Get all the configuration objects
     * 
     * @return Stephino_Rpg_Config_Item_Abstract[]
     */
    public function all() {
        return $this->_data;
    }
    
    /**
     * Get the Core configuration
     * 
     * @return Stephino_Rpg_Config_Core
     */
    public function core() {
        return $this->_data[Stephino_Rpg_Config_Core::KEY];
    }
    
    /**
     * Get the Governments configuration
     * 
     * @return Stephino_Rpg_Config_Governments
     */
    public function governments() {
        return $this->_data[Stephino_Rpg_Config_Governments::KEY];
    }

    /**
     * Get the Islands configuration
     * 
     * @return Stephino_Rpg_Config_Islands
     */
    public function islands() {
        return $this->_data[Stephino_Rpg_Config_Islands::KEY];
    }

    /**
     * Get the Tutorials configuration
     * 
     * @return Stephino_Rpg_Config_Tutorials
     */
    public function tutorials() {
        return $this->_data[Stephino_Rpg_Config_Tutorials::KEY];
    }
    
    /**
     * Get the Island Statues configuration
     * 
     * @return Stephino_Rpg_Config_IslandStatues
     */
    public function islandStatues() {
        return $this->_data[Stephino_Rpg_Config_IslandStatues::KEY];
    }
    
    /**
     * Get the Cities configuration
     * 
     * @return Stephino_Rpg_Config_Cities
     */
    public function cities() {
        return $this->_data[Stephino_Rpg_Config_Cities::KEY];
    }
    
    /**
     * Get the Buildings configuration
     * 
     * @return Stephino_Rpg_Config_Buildings
     */
    public function buildings() {
        return $this->_data[Stephino_Rpg_Config_Buildings::KEY];
    }
    
    /**
     * Get the Units configuration
     * 
     * @return Stephino_Rpg_Config_Units
     */
    public function units() {
        return $this->_data[Stephino_Rpg_Config_Units::KEY];
    }
    
    /**
     * Get the Ships configuration
     * 
     * @return Stephino_Rpg_Config_Ships
     */
    public function ships() {
        return $this->_data[Stephino_Rpg_Config_Ships::KEY];
    }
    
    /**
     * Get the Research Areas configuration
     * 
     * @return Stephino_Rpg_Config_ResearchAreas
     */
    public function researchAreas() {
        return $this->_data[Stephino_Rpg_Config_ResearchAreas::KEY];
    }
    
    /**
     * Get the Research Fields configuration
     * 
     * @return Stephino_Rpg_Config_ResearchFields
     */
    public function researchFields() {
        return $this->_data[Stephino_Rpg_Config_ResearchFields::KEY];
    }
    
    /**
     * Get the Modifiers configuration
     * 
     * @return Stephino_Rpg_Config_Modifiers
     */
    public function modifiers() {
        return $this->_data[Stephino_Rpg_Config_Modifiers::KEY];
    }
    
    /**
     * Get the Premium Modifiers configuration
     * 
     * @return Stephino_Rpg_Config_PremiumModifiers
     */
    public function premiumModifiers() {
        return $this->_data[Stephino_Rpg_Config_PremiumModifiers::KEY];
    }
    
    /**
     * Get the Premium Packages configuration
     * 
     * @return Stephino_Rpg_Config_PremiumPackages
     */
    public function premiumPackages() {
        return $this->_data[Stephino_Rpg_Config_PremiumPackages::KEY];
    }
    
    /**
     * Stephino_Rpg_Config
     */
    protected function __construct() {
        do {
            // Pro Plugin Detected
            if (Stephino_Rpg::get()->isPro()) {
                Stephino_Rpg_Pro_Config::get()->init($this->_data);
                break;
            }
            
            // Get the default configuration, internationalized
            $configDefault = self::getI18n();
            
            // Initialize the values
            foreach (self::CONFIG_ITEMS as $configItemClass) {
                // Get the configuration item key
                $configItemKey = call_user_func(array($configItemClass, 'key'));

                // Properly defined
                if (false !== $configItemKey) {
                    $this->_data[$configItemKey] = new $configItemClass(
                        isset($configDefault[$configItemKey]) 
                            ? $configDefault[$configItemKey] 
                            : null
                    );
                }
            }
        } while(false);
    }
    
    /**
     * Get the configuration definition
     * 
     * @param boolean $hideSensitive (optional) Hide sensitive fields; default <b>false</b>
     * @param boolean $i18nOnly      (optional) Hide all but i18n strings; default <b>false</b>
     * @return array
     */
    protected function _definition($hideSensitive = false, $i18nOnly = false) {
        // Go through each configuration item
        $result = array_map(
            /* @var $configItem Stephino_Rpg_Config_Item_Abstract */
            function($configItem) use ($hideSensitive) {
                return $configItem->toDefinition($hideSensitive);
            }, 
            $this->_data
        );
        
        // Keep only the i18n parameters and values
        if ($i18nOnly) {
            if (is_file($i18nPath = STEPHINO_RPG_ROOT . '/' . self::FOLDER_THEMES . '/' . self::THEME_DEFAULT . '/' . self::FILE_I18N)) {
                $stephino_rpg_i18n = null;
                
                // Load the file directly
                require $i18nPath;
                if (is_array($stephino_rpg_i18n)) {
                    $stephino_rpg_i18n_keys = array_keys($stephino_rpg_i18n);
                    foreach ($result as $configSection => &$configInfo) {
                        foreach ($configInfo as $ciKey => &$ciValue) {
                            if (in_array($ciKey, array(Stephino_Rpg_Config_Item_Abstract::DEF_KEY_PARAMS, Stephino_Rpg_Config_Item_Abstract::DEF_KEY_VALUE))) {
                                if (Stephino_Rpg_Config_Core::KEY === $configSection) {
                                    foreach (array_keys($ciValue) as $configItem) {
                                        $keyPath = $configSection . '.' . $configItem;
                                        if (!isset($stephino_rpg_i18n[$keyPath])) {
                                            unset($ciValue[$configItem]);
                                        }
                                    }
                                } else {
                                    if (Stephino_Rpg_Config_Item_Abstract::DEF_KEY_VALUE === $ciKey) {
                                        foreach ($ciValue as $ciValueId => $ciValueData) {
                                            foreach (array_keys($ciValueData) as $configItem) {
                                                $keyPath = $configSection . '.' . $ciValueId . '.' . $configItem;
                                                if ('id' !== $configItem && !isset($stephino_rpg_i18n[$keyPath])) {
                                                    unset($ciValue[$ciValueId][$configItem]);
                                                    unset($configInfo[Stephino_Rpg_Config_Item_Abstract::DEF_KEY_PARAMS][$configItem]);
                                                }
                                            }
                                        }
                                    } else {
                                        foreach (array_keys($ciValue) as $configItem) {
                                            if ('id' !== $configItem) {
                                                $keyRegEx = '%^' . $configSection . '\.\d+\.' . $configItem . '$%';
                                                $keyMatch = false;
                                                foreach ($stephino_rpg_i18n_keys as $allowedKey) {
                                                    if (preg_match($keyRegEx, $allowedKey)) {
                                                        $keyMatch = true;
                                                        break;
                                                    }
                                                }
                                                if (!$keyMatch) {
                                                    unset($configInfo[Stephino_Rpg_Config_Item_Abstract::DEF_KEY_PARAMS][$configItem]);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return $result;
    }
}

/*EOF*/