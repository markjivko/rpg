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
     * Get the definition (configuration structure)
     * 
     * @return array
     */
    public static function definition() { 
        return self::get()->_definition(!is_super_admin());
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
        // Validate the requirements tree
        Stephino_Rpg_Utils_Config::getUnlockStages();
        
        // Validate the dependencies
        foreach(self::get()->all() as $configSet) {
            if ($configSet instanceof Stephino_Rpg_Config_Item_Collection) {
                foreach($configSet->getAll() as $configSetItem) {
                    /* @var $configSetItem Stephino_Rpg_Config_Trait_Requirement */
                    if ($configSetItem instanceof Stephino_Rpg_Config_Item_Single
                        && in_array(Stephino_Rpg_Config_Trait_Requirement::class, class_uses($configSetItem))) {
                        switch (true) {
                            case $configSetItem instanceof Stephino_Rpg_Config_ResearchField:
                                if (null === $configSetItem->getResearchArea()) {
                                    throw new Exception('Dependency: Research Field #' . $configSetItem->getId() . ' missing Research Area');
                                }
                                break;
                                
                            case $configSetItem instanceof Stephino_Rpg_Config_ResearchArea:
                                if (null !== $configSetItem->getRequiredResearchField()
                                    && null !== $configSetItem->getRequiredResearchField()->getResearchArea()
                                    && $configSetItem->getRequiredResearchField()->getResearchArea()->getId() == $configSetItem->getId()
                                    ) {
                                    throw new Exception('Dependency: infinite recursion on [<b>Research Area #' . $configSetItem->getId() . ' › Research Field #' . $configSetItem->getRequiredResearchField()->getId() . '</b>]');
                                }
                            case $configSetItem instanceof Stephino_Rpg_Config_Unit:
                            case $configSetItem instanceof Stephino_Rpg_Config_Ship:
                                if (null === $configSetItem->getBuilding()) {
                                    // Prepare the item name
                                    $itemName = ucwords(preg_replace('%([A-Z])%', ' $1', constant(get_class($configSetItem) . '::KEY')));
                                    
                                    // Stop here
                                    throw new Exception('Dependency: ' . $itemName . ' #' . $configSetItem->getId() . ' missing Building');
                                }
                                break;
                        }
                    }
                }
            }
        }
        
        // Save
        update_option(Stephino_Rpg::OPTION_CONFIG, self::export());
    }
    
    /**
     * Reset the game configuration
     */
    public static function reset() {
        // Force a re-initialization, using the default theme
        self::get()->init(true);
        
        // Save the data
        update_option(Stephino_Rpg::OPTION_CONFIG, self::export());
    }

    /**
     * Get the default configuration array
     * 
     * @return array
     */
    public static function getDefault() {
        // Get the default configuration file path
        $configDefaultPath = implode(
            '/', 
            array(
                STEPHINO_RPG_ROOT, 
                self::FOLDER_THEMES,
                self::THEME_DEFAULT,
                self::FILE_CONFIG
            )
        );
        
        // Get the configuration data
        $configDefault = is_file($configDefaultPath)
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

        // Invalid format
        if (!is_array($configDefault)) {
            $configDefault = array();
        } else {
            if (is_file($i18nPath = dirname($configDefaultPath) . '/' . self::FILE_I18N)) {
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
                        if (false !== strpos($i18nKey, '.') && is_string($i18nValue)) {
                            $walk($configDefault, $i18nKey, $i18nValue);
                        }
                    }
                }
            }
        }
        
        return $configDefault;
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
        $this->init();
    }
    
    /**
     * Initialize the data objects
     * 
     * @param &array  $dataSet Data Set
     * @param boolean $reset   (optional) Reset the configuration to the default values; default <b>false</b>
     */
    public function init($reset = false) {
        // Get the saved options
        $configContents = $reset
            ? ''
            : get_option(Stephino_Rpg::OPTION_CONFIG, '');

        // Convert to an array
        $configData = strlen($configContents) 
            ? json_decode($configContents, true) 
            : self::getDefault();
        
        // Invalid format
        if (!is_array($configData)) {
            $configData = array();
        }
        
        // Initialize the values
        foreach (self::CONFIG_ITEMS as $configItemClass) {
            // Get the configuration item key
            $configItemKey = call_user_func(array($configItemClass, 'key'));
            
            // Properly defined
            if (false !== $configItemKey) {
                $this->_data[$configItemKey] = new $configItemClass(
                    isset($configData[$configItemKey]) 
                        ? $configData[$configItemKey] 
                        : null
                );
            }
        }
    }
    
    /**
     * Get the configuration definition
     * 
     * @param boolean $hideSensitive (optional) Hide sensitive fields; default <b>false</b>
     * @return array
     */
    protected function _definition($hideSensitive = false) {
        // Go through each configuration item
        return array_map(
            /* @var $configItem Stephino_Rpg_Config_Item_Abstract */
            function($configItem) use ($hideSensitive) {
                return $configItem->toDefinition($hideSensitive);
            }, 
            $this->_data
        );
    }
}

/*EOF*/