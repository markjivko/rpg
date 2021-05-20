<?php
/**
 * Stephino_Rpg_Renderer_Console
 * 
 * @title     Renderer:Console
 * @desc      Console commands
 * @copyright (c) 2021, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Renderer_Console {
    
    /**
     * Print working directory
     * Get the <b>island ID</b> and <b>city ID</b> in their respective views
     * Get the center island coordinates in the world view
     * @use `pwd`
     */
    protected static $_extraMethodPwd;
    
    /**
     * Clear the current console screen
     * Does not remove commands stored in history
     * @use `(clear|cls)`
     */
    protected static $_extraMethodClear;
    
    /**
     * Show recently used commands
     * Navigate using the &#x25B2; and &#x25BC; keys
     * A maximum of 10 commands are stored in history
     * `history`, `exit` and identical consecutive commands are ignored
     * @use `(history|#) [{command number}]`
     * @example `history`
     * @example `# 0`
     */
    protected static $_extraMethodHistory;
    
    /**
     * Exit the console
     * You can also use <b>Alt+Ctrl+C</b> to toggle the console
     * Current console output is kept until a page reload
     * @use `(exit|quit)`
     */
    protected static $_extraMethodExit;
    
    // Templates
    const TEMPLATE_HELP                 = 'console-help';
    const TEMPLATE_LIST_STAGES          = 'console-list-stages';
    const TEMPLATE_LIST_ISLAND_CITIES   = 'console-list-island-cities';
    const TEMPLATE_LIST_USER_CITIES     = 'console-list-user-cities';
    const TEMPLATE_LIST_USER_CONVOYS    = 'console-list-user-convoys';
    const TEMPLATE_LIST_USER_RESOURCES  = 'console-list-user-resources';
    const TEMPLATE_LIST_CITY_BUILDINGS  = 'console-list-city-buildings';
    const TEMPLATE_LIST_CITY_ENTITIES   = 'console-list-city-entities';
    const TEMPLATE_LIST_CITY_RESOURCES  = 'console-list-city-resources';
    const TEMPLATE_LIST_CITY_MILITARY   = 'console-list-city-military';
    const TEMPLATE_LIST_PTF             = 'console-list-ptf';
    const TEMPLATE_SET_USER_TIME_TRAVEL = 'console-set-user-time-travel';
    
    /**
     * Get a console template path
     * 
     * @param string $templateName Console template name
     * @return string|null
     */
    protected static function _getTemplatePath($templateName) {
        if (is_file($templatePath = STEPHINO_RPG_ROOT . '/' . Stephino_Rpg::FOLDER_UI_TPL . '/console/' . $templateName . '.php')) {
            return $templatePath;
        }
        return null;
    }
    
    /**
     * Output the help information for the current "cli" method and throw an Exception<br/>
     * Used for errors caused by missing or invalid argument types.
     * 
     * @param string $message (optional) Exception message; default <b>null</b>, throws "Invalid arguments"
     * @throws Exception
     */
    protected static function _throwHelp($message = null) {
        // Get the backtrace
        $backtrace = debug_backtrace(false, 2);
        
        // Prepare the cli name
        $cliMethodName = strtolower(
            trim(
                preg_replace(
                    array('%^cli%i', '%([A-Z]\B)%'), 
                    array('', '-$1'), 
                    $backtrace[1]['function']
                ),
                '- '
            )
        );
        
        // Show the help information
        self::cliHelp($cliMethodName);
        
        // Stop here
        throw new Exception(null === $message ? 'Invalid arguments' : $message);
    }
    
    /**
     * @use `(help|?) [{command}]` OR `{command} (--help|/?)`
     */
    public static function cliHelp($commandName = null) {
        // No recursion
        if ('help' == strtolower($commandName) || preg_match('%^\?%', $commandName)) {
            $commandName = null;
        }
        
        // Get a reflection class
        $reflection = new ReflectionClass(self::class);
        
        /**
         * Convert a DOC Comment into an array of HTML formatted lines
         * 
         * @param string $docComment Method DOC Comment
         * @return array
         */
        $getMethodDetails = function($docComment) {
            return preg_split(
                '%[\r\n]%', 
                trim(
                    preg_replace(
                        array('%(?:^\s*\/\*\*\s*$|^\s*\*\/\s*$|^\s*\*)%sm', '%^(\s*)(@\w+)%m', '%\{(.*?)\}%m', '%\`(.*?)\`%m'), 
                        array('', '$1<b>$2:</b>', '<b>{$1}</b>', '<i>`$1`</i>'), 
                        $docComment
                    )
                )
            );
        };
        
        // Describe all methods
        $methods = array_map(
            function($item) use($getMethodDetails) {
                /* @var $item ReflectionMethod */
                return array(
                    // CLI method name
                    strtolower(
                        trim(
                            preg_replace(
                                array('%^cli%i', '%([A-Z]\B)%'), 
                                array('', '-$1'), 
                                $item->getName()
                            ),
                            '- '
                        )
                    ),
                    // Array of HTML-formatted documentation lines
                    $getMethodDetails($item->getDocComment())
                );
            },
            array_values(
                array_filter(
                    $reflection->getMethods(ReflectionMethod::IS_STATIC),
                    function($item) {
                        return preg_match('%^cli[A-Z]%', $item->getName());
                    }
                )
            )
        );
            
        // Append the extra (JS-only) methods
        foreach($reflection->getProperties() as $reflectionProperty) {
            /* @var $reflectionProperty ReflectionProperty */
            if (preg_match('%^_extraMethod%', $reflectionProperty->getName())) {
                $methods[] = array(
                    // CLI method name
                    strtolower(
                        preg_replace(
                            '%^_extraMethod%', 
                            '', 
                            $reflectionProperty->getName()
                        )
                    ), 
                    // Array of HTML-formatted documentation lines
                    $getMethodDetails($reflectionProperty->getDocComment())
                );
            }
        }

        // Pad the result for output
        foreach($methods as &$methodData) {
            $methodData[1] = array_map(function($item) {
                return '<span class="console-output-row">' . $item . '</span>';
            }, $methodData[1]);
        }
        
        // Command name provided
        if (null !== $commandName) {
            switch ($commandName) {
                case '#':
                    $commandName = 'history';
                    break;
                
                case 'cls':
                    $commandName = 'clear';
                    break;
            }
            
            // Prepare the command info
            $commandInfo = null;
            foreach ($methods as $methodInfo) {
                list($cliMethodName, $cliMethodDetailsArray) = $methodInfo;
                if ($commandName == $cliMethodName) {
                    $commandInfo = implode('<br/>', $cliMethodDetailsArray);
                    break;
                }
            }
            
            // Invalid command name
            if (null === $commandInfo) {
                throw new Exception('Command `' . $commandName . '` not implemented');
            }
        }
        
        // Load the template
        if (null !== $templatePath = self::_getTemplatePath(self::TEMPLATE_HELP)) {
            require $templatePath;
        }
    }
    
    /**
     * List and validate game stages 
     * List and validate the building and research field requirement tree
     * 
     * @use `list-stages`
     */
    public static function cliListStages() {
        // Get the game stages as objects
        $unlockStages = Stephino_Rpg_Utils_Config::getUnlockStages(true);
        
        // Load the template
        if (null !== $templatePath = self::_getTemplatePath(self::TEMPLATE_LIST_STAGES)) {
            require $templatePath;
        }
    }
    
    /**
     * List cities by island
     * 
     * @use     `list-island-cities {island ID}`
     * @example `list-island-cities 1`
     * @see     Get the {island ID} by running `pwd` in the island view
     */
    public static function cliListIslandCities($islandId = null) {
        if (!is_numeric($islandId)) {
            self::_throwHelp('Invalid island ID');
        }
        
        // Get the island data
        $islandData = Stephino_Rpg_Db::get()->tableIslands()->getById(intval($islandId));
        
        // Invalid island
        if (!is_array($islandData)) {
            throw new Exception('Island not found');
        }
        
        // Prepare the island configuration
        $islandConfig = Stephino_Rpg_Config::get()
            ->islands()
            ->getById($islandData[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_CONFIG_ID]);
        
        // Invalid configuration
        if (null === $islandConfig) {
            throw new Exception(
                'Missing island configuration #' 
                . $islandData[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_CONFIG_ID]
            );
        }
        
        // City slots
        $citySlots = json_decode($islandConfig->getCitySlots(), true);
        
        // Invalid configuration
        if (!is_array($citySlots) || !count($citySlots)) {
            throw new Exception(
                'Invalid city slots configuration for island configuration #' 
                . $islandData[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_CONFIG_ID]
            );
        }
        
        // Get the list
        $listArray = Stephino_Rpg_Db::get()->tableCities()->getByIsland($islandId);
        
        // Prepare the island slots
        $islandSlots = array();
        for ($i = 0; $i <= count($citySlots) - 1; $i++) {
            // Prepare the city data
            $slotCityData = null;
            if (is_array($listArray)) {
                foreach ($listArray as $dbRow) {
                    if ($dbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_INDEX] == $i) {
                        $slotCityData = $dbRow;
                    }
                }
            }
            $islandSlots[$i] = $slotCityData;
        }
        
        // Load the template
        if (null !== $templatePath = self::_getTemplatePath(self::TEMPLATE_LIST_ISLAND_CITIES)) {
            require $templatePath;
        }
    }
    
    /**
     * List cities by user
     * 
     * @use     `list-user-cities {user ID}`
     * @example `list-user-cities 1`
     * @see     Get the {user ID} with `list-island-cities` or `whoami`
     */
    public static function cliListUserCities($userId = null) {
        if (!is_numeric($userId)) {
            self::_throwHelp('Invalid user ID');
        }
        
        // Invalid result
        if (!is_array($listArray = Stephino_Rpg_Db::get()->tableCities()->getByUser($userId))) {
            throw new Exception('No cities found for this user');
        }
        
        // Load the template
        if (null !== $templatePath = self::_getTemplatePath(self::TEMPLATE_LIST_USER_CITIES)) {
            require $templatePath;
        }
    }
    
    /**
     * List convoys by user
     * 
     * @use     `list-user-convoys {user ID}`
     * @example `list-user-convoys 1`
     * @see     Get the {user ID} with `list-island-cities` or `whoami`
     */
    public static function cliListUserConvoys($userId = null) {
        if (!is_numeric($userId)) {
            self::_throwHelp('Invalid user ID');
        }
        
        // Invalid result
        if (!is_array($listArray = Stephino_Rpg_Db::get()->tableConvoys()->getByUser($userId))) {
            throw new Exception('This user has no active convoys');
        }
        
        // Load the template
        if (null !== $templatePath = self::_getTemplatePath(self::TEMPLATE_LIST_USER_CONVOYS)) {
            require $templatePath;
        }
    }
    
    /**
     * Create an attack between two cities
     * 
     * @use     `set-user-attack {attacker city ID} {defender city ID} ((unit|ship) {entity ID} {entity count}...)`
     * @example `set-user-attack 1 2 unit 1 10`
     * @example `set-user-attack 1 2 unit 1 10 unit 2 5 ship 1 10`
     * @see     Get the {attacker|defender city ID} with `list-user-cities` or `pwd`
     * @see     Get the {entity ID} with `list-city-entities`
     */
    public static function cliSetUserAttack($attCityId = null, $defCityId = null) {
        // Attacker information
        if (!is_array(Stephino_Rpg_Db::get()->tableCities()->getById($attCityId))) {
            self::_throwHelp('Attacker city not found');
        }
        
        // Defender information
        if (!is_array(Stephino_Rpg_Db::get()->tableCities()->getById($defCityId))) {
            self::_throwHelp('Defender city not found');
        }
        
        // Get the convoy entities
        $attackArmy = array();
        
        // Get the method arguments
        $methodArguments = array_values(array_slice(func_get_args(), 2));
        do {
            if (count($methodArguments) < 3) {
                break;
            }
            
            // Get the entity type
            $entityType = array_shift($methodArguments);
            
            // Get the entity configuration id
            $entityConfigId = intval(array_shift($methodArguments));
            
            // Get the entity count
            $entityCount = intval(array_shift($methodArguments));
            
            // Validate the configuration
            $entityKey = null;
            if ($entityConfigId) {
                switch ($entityType) {
                    case Stephino_Rpg_Config_Unit::KEY:
                        $entityKey = Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT . '_' . $entityConfigId;
                        break;

                    case Stephino_Rpg_Config_Ship::KEY:
                        $entityKey = Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_SHIP . '_' . $entityConfigId;
                        break;
                }
            }
            
            // Invalid entity
            if (null === $entityKey) {
                self::_throwHelp('Invalid entity "' . $entityType . '" with ID ' . $entityConfigId);
            }
            
            // Invalid count
            if ($entityCount <= 0) {
                self::_throwHelp('Invalid entity "' . $entityType . '" #' . $entityConfigId . ' count');
            }
            
            // Store the entity
            $attackArmy[$entityKey] = $entityCount;
        } while (true);
        
        // Get the new convoy ID
        $newConvoyId = Stephino_Rpg_Db::get()->modelConvoys()->createAttack($attCityId, $defCityId, $attackArmy);
        echo 'Created attack #' . $newConvoyId;
    }
    
    /**
     * List user resources
     * 
     * @use     `list-user-resources {user ID}`
     * @example `list-user-resources 1`
     * @see     Get the {user ID} with `list-island-cities` or `whoami`
     */
    public static function cliListUserResources($userId = null) {
        if (!is_numeric($userId)) {
            self::_throwHelp('Invalid user ID');
        }
        
        // Get the core configuration
        $coreConfig = Stephino_Rpg_Config::get()->core();
        
        // Prepare the resource names
        $resourceNames = array(
            Stephino_Rpg_Renderer_Ajax::RESULT_RES_GOLD => array(
                $coreConfig->getResourceGoldName(), 
                Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD,
            ),
            Stephino_Rpg_Renderer_Ajax::RESULT_RES_RESEARCH => array(
                $coreConfig->getResourceResearchName(), 
                Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH,
            ),
            Stephino_Rpg_Renderer_Ajax::RESULT_RES_GEM => array(
                $coreConfig->getResourceGemName(), 
                Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM,
            ),
        );
        
        // Get the user data
        $userData = Stephino_Rpg_Db::get()->tableUsers()->getById($userId);
        
        // Invalid data
        if (!is_array($userData)) {
            throw new Exception('User does not exist');
        }
        
        // Load the template
        if (null !== $templatePath = self::_getTemplatePath(self::TEMPLATE_LIST_USER_RESOURCES)) {
            require $templatePath;
        }
    }
    
    /**
     * Set user resources
     * 
     * @use     `set-user-resource {user ID} (gold|gem|research) {resource value}`
     * @example `set-user-resource 1 gold 5000`
     * @see     Get the {user ID} with `list-island-cities` or `whoami`
     */
    public static function cliSetUserResource($userId = null, $resourceName = null, $resourceValue = null) {
        if (!is_numeric($userId)) {
            self::_throwHelp('Invalid user ID');
        }
        if (!is_numeric($resourceValue)) {
            self::_throwHelp('Invalid resource value');
        }
        if ($resourceValue < 0) {
            self::_throwHelp('Resource value must be a positive integer');
        }
        
        // Get the core configuration
        $coreConfig = Stephino_Rpg_Config::get()->core();
        
        // Prepare the resource names
        $resourceNames = array(
            Stephino_Rpg_Renderer_Ajax::RESULT_RES_GOLD => array(
                $coreConfig->getResourceGoldName(), 
                Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD,
            ),
            Stephino_Rpg_Renderer_Ajax::RESULT_RES_RESEARCH => array(
                $coreConfig->getResourceResearchName(), 
                Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH,
            ),
            Stephino_Rpg_Renderer_Ajax::RESULT_RES_GEM => array(
                $coreConfig->getResourceGemName(), 
                Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM,
            ),
        );
        
        // Validate the resource name
        if (!isset($resourceNames[$resourceName])) {
            self::_throwHelp('Invalid resource name');
        }
        
        // Get the field
        $resourceField = $resourceNames[$resourceName][1];
        
        // Execute the update
        $result = Stephino_Rpg_Db::get()->tableUsers()->updateById(
            array(
                $resourceField => $resourceValue
            ),
            $userId
        );
        
        // Invalid result
        if (false === $result) {
            throw new Exception('Could not update the value');
        }
        
        // Inform the user
        echo sprintf(
            'User <b>%d</b> now has <b>%s %s</b>.',
            $userId,
            number_format($resourceValue, 2),
            $resourceNames[$resourceName][0]
        );
    }
    
    /**
     * Travel into the future
     * 
     * @use     `set-user-time-travel {user ID} {seconds}`
     * @example `set-user-time-travel 1 60`
     * @see     Get the {user ID} with `list-island-cities` or `whoami`
     */
    public static function cliSetUserTimeTravel($userId = null, $seconds = null) {
        // Validate the input
        if (!is_numeric($userId)) {
            self::_throwHelp('Invalid user ID');
        }
        if (!is_numeric($seconds) || $seconds <= 0) {
            self::_throwHelp('Invalid time; expected a positive integer');
        }
        $seconds = intval($seconds);
        
        // Get the user data
        $userData = Stephino_Rpg_Db::get()->tableUsers()->getById($userId);
        if (!is_array($userData)) {
            throw new Exception('User not found');
        }
        
        // Update the last tick
        $userData[Stephino_Rpg_Db_Table_Users::COL_USER_LAST_TICK] -= $seconds;
        if (!Stephino_Rpg_Db::get()->tableUsers()->updateById(array(
            Stephino_Rpg_Db_Table_Users::COL_USER_LAST_TICK => $userData[Stephino_Rpg_Db_Table_Users::COL_USER_LAST_TICK]
        ), $userId)) {
            throw new Exception('Could not update last tick');
        }
        
        // Prepare the queues multi-update array
        $queuesMultiUpdate = array();
        if (is_array($queues = Stephino_Rpg_Db::get()->tableQueues()->getByUserId($userId))) {
            // Go through the queues list
            foreach ($queues as $queueRow) {
                $queuesMultiUpdate[$queueRow[Stephino_Rpg_Db_Table_Queues::COL_ID]] = array(
                    Stephino_Rpg_Db_Table_Queues::COL_QUEUE_TIME => $queueRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_TIME] - $seconds,
                );
            }
            
            // Get the query
            $multiUpdateQuery = Stephino_Rpg_Utils_Db::getMultiUpdate(
                $queuesMultiUpdate, 
                Stephino_Rpg_Db::get()->tableQueues()->getTableName(), 
                Stephino_Rpg_Db_Table_Queues::COL_ID
            );
            if (null === $multiUpdateQuery) {
                throw new Exception('Could not prepare queues query');
            }
            if (!Stephino_Rpg_Db::get()->getWpDb()->query($multiUpdateQuery)) {
                throw new Exception('Could not update queues');
            }
        }
        
        // Prepare the convoys multi-update array
        $convoysMultiUpdate = array();
        if (is_array($convoys = Stephino_Rpg_Db::get()->tableConvoys()->getByUser($userId))) {
            // Go through the convoys list
            foreach ($convoys as $convoyRow) {
                // Store the convoy ID
                $convoyId = $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_ID];
                
                // Initialize the update row
                $convoysMultiUpdate[$convoyId] = array();
                if (is_numeric($convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TIME])) {
                    $convoysMultiUpdate[$convoyId][Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TIME] = 
                        $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TIME] - $seconds;
                }
                if (is_numeric($convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_RETREAT_TIME])) {
                    $convoysMultiUpdate[$convoyId][Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_RETREAT_TIME] = 
                        $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_RETREAT_TIME] - $seconds;
                }
            }
            
            // Get the query
            $multiUpdateQuery = Stephino_Rpg_Utils_Db::getMultiUpdate(
                $convoysMultiUpdate, 
                Stephino_Rpg_Db::get()->tableConvoys()->getTableName(), 
                Stephino_Rpg_Db_Table_Convoys::COL_ID
            );
            if (null === $multiUpdateQuery) {
                throw new Exception('Could not prepare convoy update query');
            }
            if (!Stephino_Rpg_Db::get()->getWpDb()->query($multiUpdateQuery)) {
                throw new Exception('Could not update convoys');
            }
        }
        
        // Load the template
        if (null !== $templatePath = self::_getTemplatePath(self::TEMPLATE_SET_USER_TIME_TRAVEL)) {
            require $templatePath;
        }
    }
    
    /**
     * List city buildings
     * 
     * @use     `list-city-buildings {city ID}`
     * @example `list-city-buildings 1`
     * @see     Get the {city ID} with `list-user-cities` or `pwd`
     */
    public static function cliListCityBuildings($cityId = null) {
        if (!is_numeric($cityId)) {
            self::_throwHelp('Invalid city ID');
        }
        
        // Get the buildings configuration
        $buildingConfigs = Stephino_Rpg_Config::get()->buildings()->getAll();
        
        // Get the city data
        $cityData = Stephino_Rpg_Db::get()->tableBuildings()->getByCity($cityId);
        
        // Invalid data
        if (!is_array($cityData)) {
            throw new Exception('City does not exist');
        }
        
        // Prepare the configuration data
        $buildingList = array();
        foreach ($cityData as $buildingRow) {
            // Get the config id
            $buildingConfigId = $buildingRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID];
            
            // Store the data
            $buildingList[$buildingConfigId] = $buildingRow;
        }
        
        // Load the template
        if (null !== $templatePath = self::_getTemplatePath(self::TEMPLATE_LIST_CITY_BUILDINGS)) {
            require $templatePath;
        }
    }
    
    /**
     * Set city building level
     * Automatically creates the required buildings/research fields at the minimum required level if necessary
     * 
     * @use     `set-city-building {city ID} {building ID} {building level}`
     * @example `set-city-building 1 1 5`
     * @see     Get the {city ID} with `list-user-cities` or `pwd`
     * @see     Get the {building ID} with `list-city-buildings`
     */
    public static function cliSetCityBuilding($cityId = null, $buildingConfigId = null, $buildingLevel = null) {
        if (!is_numeric($cityId)) {
            self::_throwHelp('Invalid city ID');
        }
        if (!is_numeric($buildingConfigId)) {
            self::_throwHelp('Invalid building config ID');
        }
        if (!is_numeric($buildingLevel)) {
            self::_throwHelp('Invalid building level');
        }
        if ($buildingLevel <= 0) {
            self::_throwHelp('Building level must be a strictly positive integer');
        }

        // Get the building configuration
        $buildingConfig = Stephino_Rpg_Config::get()->buildings()->getById($buildingConfigId);
        $userId = self::_setLevelTree($cityId, $buildingConfig);
        
        // Set the level
        Stephino_Rpg_Db::get()->modelBuildings()->setLevel($cityId, $buildingConfigId, $buildingLevel);
        echo sprintf(
            'Building <b>%s</b> set to level <b>%d</b> for user <b>#%d</b>',
            $buildingConfig->getName(true),
            $buildingLevel,
            $userId
        );
    }
    
    /**
     * Set research field level
     * Automatically creates the required buildings/research fields at the minimum required level if necessary
     * 
     * @use     `set-city-research-field {city ID} {research field config ID} {research field level}`
     * @example `set-city-research-field 1 1 1`
     * @see     Get the {city ID} with `list-user-cities` or `pwd`
     */
    public static function cliSetCityResearchField($cityId = null, $researchFieldConfigId = null, $researchFieldLevel = null) {
        if (!is_numeric($cityId)) {
            self::_throwHelp('Invalid city ID');
        }
        if (!is_numeric($researchFieldConfigId)) {
            self::_throwHelp('Invalid research field config ID');
        }
        if (!is_numeric($researchFieldLevel)) {
            self::_throwHelp('Invalid research field level');
        }
        if ($researchFieldLevel <= 0) {
            self::_throwHelp('Research field level must be a strictly positive integer');
        }
        
        // Get the research field configuration
        $researchFieldConfig = Stephino_Rpg_Config::get()->researchFields()->getById($researchFieldConfigId);
        $userId = self::_setLevelTree($cityId, $researchFieldConfig);
        
        // Validate the reserach field level
        $maxResearchFieldLevel = Stephino_Rpg_Utils_Config::getUnlocksMaxLevel($researchFieldConfig);
        if ($maxResearchFieldLevel > 0 && $researchFieldLevel > $maxResearchFieldLevel) {
            $researchFieldLevel = $maxResearchFieldLevel;
        }
        
        // Set the level
        Stephino_Rpg_Db::get()->modelResearchFields()->setLevel($userId, $researchFieldConfigId, $researchFieldLevel);
        echo sprintf(
            'Research Field <b>%s</b> set to level <b>%d</b> for user <b>#%d</b>',
            $researchFieldConfig->getName(true),
            $researchFieldLevel,
            $userId
        );
    }
    
    /**
     * Set the level for all configuration dependencies
     * 
     * @param int                                                            $cityId       City DB Id
     * @param Stephino_Rpg_Config_Building|Stephino_Rpg_Config_ResearchField $configObject Building or Research Field object
     * @return int User ID
     * @throws Exception
     */
    protected static function _setLevelTree($cityId, $configObject) {
        // Get the user ID
        $userId = null;
        
        // Prepare the buildings
        $buildingLevels = array();
        $buildings = Stephino_Rpg_Db::get()->tableBuildings()->getByCity($cityId);
        if (is_array($buildings)) {
            foreach ($buildings as $dbRow) {
                $buildingLevels[(int) $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID]] = (int) $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL];
                if (null === $userId) {
                    $userId = (int) $dbRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_USER_ID];
                }
            }
        }
        
        // City not found
        if (null === $userId) {
            throw new Exception('City ' . $cityId . ' not initialized');
        }
                        
        // Prepare the research fields
        $researchFieldLevels = array();
        $researchFields = Stephino_Rpg_Db::get()->tableResearchFields()->getByUser($userId);
        if (is_array($researchFields)) {
            foreach ($researchFields as $dbRow) {
                $researchFieldLevels[(int) $dbRow[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_CONFIG_ID]] = (int) $dbRow[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_LEVEL];
            }
        }
        
        // Get the unlock tree
        $unlockTree = Stephino_Rpg_Utils_Config::getUnlockTree($configObject);
        foreach ($unlockTree as $unlockItem) {
            if ($unlockItem[0] instanceof Stephino_Rpg_Config_Building) {
                if (!isset($buildingLevels[$unlockItem[0]->getId()]) 
                    || $buildingLevels[$unlockItem[0]->getId()] < $unlockItem[1]) {
                    Stephino_Rpg_Db::get()->modelBuildings()->setLevel(
                        $cityId, 
                        $unlockItem[0]->getId(), 
                        $unlockItem[1]
                    );
                    echo sprintf(
                        '(required) Building <b>%s</b> set to level <b>%d</b><br/>',
                        $unlockItem[0]->getName(true),
                        $unlockItem[1]
                    );
                }
            } else {
                if (!isset($researchFieldLevels[$unlockItem[0]->getId()]) 
                    || $researchFieldLevels[$unlockItem[0]->getId()] < $unlockItem[1]) {
                    Stephino_Rpg_Db::get()->modelResearchFields()->setLevel(
                        $userId, 
                        $unlockItem[0]->getId(), 
                        $unlockItem[1]
                    );
                    echo sprintf(
                        '(required) Research Field <b>%s</b> set to level <b>%d</b><br/>',
                        $unlockItem[0]->getName(true),
                        $unlockItem[1]
                    );
                }
            }
        }
        
        return $userId;
    }
    
    /**
     * List city entities
     * 
     * @use     `list-city-entities {city ID}`
     * @example `list-city-entities 1`
     * @see     Get the {city ID} with `list-user-cities` or `pwd`
     */
    public static function cliListCityEntities($cityId = null) {
        if (!is_numeric($cityId)) {
            self::_throwHelp('Invalid city ID');
        }
        
        // Get the buildings configuration
        $unitConfigs = Stephino_Rpg_Config::get()->units()->getAll();
        $shipConfigs = Stephino_Rpg_Config::get()->ships()->getAll();
        
        // Get the entities data
        $entitiesData = Stephino_Rpg_Db::get()->tableEntities()->getByCity($cityId);
        
        // Prepare the configuration data
        $unitList = array();
        $shipList = array();
        if (is_array($entitiesData)) {
            foreach ($entitiesData as $entityRow) {
                // Get the config id
                $entityConfigId = $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID];

                // Store the data
                switch($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE]) {
                    case Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT:
                        $unitList[$entityConfigId] = $entityRow;
                        break;
                    
                    case Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_SHIP:
                        $shipList[$entityConfigId] = $entityRow;
                        break;
                }
            }
        }
        
        // Load the template
        if (null !== $templatePath = self::_getTemplatePath(self::TEMPLATE_LIST_CITY_ENTITIES)) {
            require $templatePath;
        }
    }
    
    /**
     * Set city entity count
     * 
     * @use     `set-city-entity {city ID} (unit|ship) {entity ID} {entity count}`
     * @example `set-city-entity 1 unit 1 5`
     * @see     Get the {city ID} with `list-user-cities` or `pwd`
     * @see     Get the {entity ID} with `list-city-entities`
     */
    public static function cliSetCityEntity($cityId = null, $entityTypeLiteral = null, $entityConfigId = null, $entityCount = null) {
        if (!is_numeric($cityId)) {
            self::_throwHelp('Invalid city ID');
        }
        if (!in_array($entityTypeLiteral, array(Stephino_Rpg_Config_Unit::KEY, Stephino_Rpg_Config_Ship::KEY))) {
            self::_throwHelp('Invalid entity type');
        }
        if (!is_numeric($entityConfigId)) {
            self::_throwHelp('Invalid entity ID');
        }
        if (!is_numeric($entityCount)) {
            self::_throwHelp('Invalid entity count');
        }
        if ($entityCount < 0) {
            self::_throwHelp('Entity count must be a positive integer');
        }
        
        // Get the entity configuration
        $entityConfiguration = null;
        $entityType = null;
        switch($entityTypeLiteral) {
            case Stephino_Rpg_Config_Unit::KEY:
                $entityType = Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT;
                $entityConfiguration = Stephino_Rpg_Config::get()->units()->getById($entityConfigId);
                break;
            
            case Stephino_Rpg_Config_Ship::KEY:
                $entityType = Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_SHIP;
                $entityConfiguration = Stephino_Rpg_Config::get()->ships()->getById($entityConfigId);
                break;
        }
        
        // Set the entity count
        Stephino_Rpg_Db::get()->modelEntities()->set($cityId, $entityType, $entityConfigId, $entityCount);
        echo sprintf(
            'Updated <b>%s</b> %s count to <b>%d</b>',
            $entityConfiguration->getName(true),
            $entityTypeLiteral,
            $entityCount
        );
    }
    
    /**
     * List city resources
     * 
     * @use     `list-city-resources {city ID}`
     * @example `list-city-resources 1`
     * @see     Get the {city ID} with `list-user-cities` or `pwd`
     */
    public static function cliListCityResources($cityId = null) {
        if (!is_numeric($cityId)) {
            self::_throwHelp('Invalid city ID');
        }
        
        // Get the core configuration
        $coreConfig = Stephino_Rpg_Config::get()->core();
        
        // Prepare the resource names
        $resourceNames = array(
            Stephino_Rpg_Renderer_Ajax::RESULT_RES_ALPHA => array(
                $coreConfig->getResourceAlphaName(), 
                Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA,
            ),
            Stephino_Rpg_Renderer_Ajax::RESULT_RES_BETA => array(
                $coreConfig->getResourceBetaName(), 
                Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_BETA,
            ),
            Stephino_Rpg_Renderer_Ajax::RESULT_RES_GAMMA => array(
                $coreConfig->getResourceGammaName(), 
                Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_GAMMA,
            ),
            Stephino_Rpg_Renderer_Ajax::RESULT_RES_EXTRA_1 => array(
                $coreConfig->getResourceExtra1Name(), 
                Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1,
            ),
            Stephino_Rpg_Renderer_Ajax::RESULT_RES_EXTRA_2 => array(
                $coreConfig->getResourceExtra2Name(), 
                Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2,
            ),
            Stephino_Rpg_Renderer_Ajax::RESULT_MTR_POPULATION => array(
                $coreConfig->getMetricPopulationName(), 
                Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION,
            ),
            Stephino_Rpg_Renderer_Ajax::RESULT_MTR_SATISFACTION => array(
                $coreConfig->getMetricSatisfactionName(), 
                Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_SATISFACTION,
            ),
            Stephino_Rpg_Renderer_Ajax::RESULT_MTR_STORAGE => array(
                $coreConfig->getMetricStorageName(), 
                Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_STORAGE,
            ),
        );
        
        // Get the user data
        $cityData = Stephino_Rpg_Db::get()->tableCities()->getById($cityId);
        
        // Invalid data
        if (!is_array($cityData)) {
            throw new Exception('City does not exist');
        }
        
        // Load the template
        if (null !== $templatePath = self::_getTemplatePath(self::TEMPLATE_LIST_CITY_RESOURCES)) {
            require $templatePath;
        }
    }
    
    /**
     * Set city resources
     * 
     * @use     `set-city-resource {city ID} (alpha|beta|gamma|extra1|extra2|mtr_storage) {resource value}`
     * @example `set-city-resource 1 alpha 5000`
     * @see     Get the {city ID} with `list-user-cities` or `pwd`
     */
    public static function cliSetCityResource($cityId = null, $resourceName = null, $resourceValue = null) {
        if (!is_numeric($cityId)) {
            self::_throwHelp('Invalid city ID');
        }
        if (!is_numeric($resourceValue)) {
            self::_throwHelp('Invalid resource value');
        }
        if ($resourceValue < 0) {
            self::_throwHelp('Resource value must be a positive integer');
        }
        
        // Get the core configuration
        $coreConfig = Stephino_Rpg_Config::get()->core();
        
        // Prepare the resource names
        $resourceNames = array(
            Stephino_Rpg_Renderer_Ajax::RESULT_RES_ALPHA => array(
                $coreConfig->getResourceAlphaName(), 
                Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_ALPHA,
            ),
            Stephino_Rpg_Renderer_Ajax::RESULT_RES_BETA => array(
                $coreConfig->getResourceBetaName(), 
                Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_BETA,
            ),
            Stephino_Rpg_Renderer_Ajax::RESULT_RES_GAMMA => array(
                $coreConfig->getResourceGammaName(), 
                Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_GAMMA,
            ),
            Stephino_Rpg_Renderer_Ajax::RESULT_RES_EXTRA_1 => array(
                $coreConfig->getResourceExtra1Name(), 
                Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_1,
            ),
            Stephino_Rpg_Renderer_Ajax::RESULT_RES_EXTRA_2 => array(
                $coreConfig->getResourceExtra2Name(), 
                Stephino_Rpg_Db_Table_Cities::COL_CITY_RESOURCE_EXTRA_2,
            ),
            Stephino_Rpg_Renderer_Ajax::RESULT_MTR_STORAGE => array(
                $coreConfig->getMetricStorageName(), 
                Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_STORAGE,
            ),
        );
        
        // Validate the resource name
        if (!isset($resourceNames[$resourceName])) {
            self::_throwHelp('Invalid resource name');
        }
        
        // Get the field
        $resourceField = $resourceNames[$resourceName][1];
        
        // Execute the update
        $newResourceValue = Stephino_Rpg_Db::get()->modelCities()->setResource(
            $cityId, $resourceField, $resourceValue
        );
        
        // Inform the user
        echo sprintf(
            'City <b>%d</b> now has <b>%s %s</b>.',
            $cityId,
            number_format($newResourceValue, 2),
            $resourceNames[$resourceName][0]
        );
    }
    
    /**
     * List city military
     * 
     * @use     `list-city-military {city ID}`
     * @example `list-city-military 1`
     * @see     Get the {city ID} with `list-user-cities` or `pwd`
     */
    public static function cliListCityMilitary($cityId = null) {
        if (!is_numeric($cityId)) {
            self::_throwHelp('Invalid city ID');
        }
        
        // Get the city data
        $cityData = Stephino_Rpg_Db::get()->tableCities()->getById($cityId);
        
        // Get the buildings
        $buildingsData = Stephino_Rpg_Db::get()->tableBuildings()->getByCity($cityId);
        
        // Invalid data
        if (!is_array($cityData) && !is_array($buildingsData)) {
            throw new Exception('City does not exist');
        }
        
        // Prepare the military buildings
        $militaryBuildings = array();
        $militaryBuildingsTotal = array(
            Stephino_Rpg_Renderer_Ajax::RESULT_MIL_ATTACK  => 0,
            Stephino_Rpg_Renderer_Ajax::RESULT_MIL_DEFENSE => 0,
        );
        foreach (Stephino_Rpg_Config::get()->buildings()->getAll() as $buildingConfig) {
            if ($buildingConfig->getAttackPoints() > 0 || $buildingConfig->getDefensePoints() > 0) {
                foreach ($buildingsData as $buildingData) {
                    if ($buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL] > 0
                        && $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID] == $buildingConfig->getId()) {
                        
                        // Get the production factor
                        $prodFactor = Stephino_Rpg_Renderer_Ajax_Action::getBuildingProdFactor(
                            Stephino_Rpg_Config::get()->cities()->getById($cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_CONFIG_ID]),
                            $buildingConfig, 
                            array_merge($cityData, $buildingData)
                        );

                        // Get the values
                        $militaryAttack = Stephino_Rpg_Utils_Config::getPolyValue(
                            $buildingConfig->getAttackPointsPolynomial(), 
                            $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL], 
                            $buildingConfig->getAttackPoints()
                        ) * $prodFactor;
                        $militaryDefense = Stephino_Rpg_Utils_Config::getPolyValue(
                            $buildingConfig->getDefensePointsPolynomial(), 
                            $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL], 
                            $buildingConfig->getDefensePoints()
                        ) * $prodFactor;
                        
                        // Store the building details
                        $militaryBuildings[] = array(
                            Stephino_Rpg_Renderer_Ajax::RESULT_MIL_ATTACK       => $militaryAttack,
                            Stephino_Rpg_Renderer_Ajax::RESULT_MIL_DEFENSE      => $militaryDefense,
                            Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL => (int) $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL],
                            Stephino_Rpg_Renderer_Ajax::RESULT_DATA             => $buildingConfig,
                        );
                        
                        // Store the totals
                        $militaryBuildingsTotal[Stephino_Rpg_Renderer_Ajax::RESULT_MIL_ATTACK]  += $militaryAttack;
                        $militaryBuildingsTotal[Stephino_Rpg_Renderer_Ajax::RESULT_MIL_DEFENSE] += $militaryDefense;
                        break;
                    }
                }
            }
        }
        
        // Prepare the entities
        $militaryEntities = array();
        $militaryEntitiesTotal = array(
            Stephino_Rpg_Renderer_Ajax::RESULT_MIL_ATTACK  => 0,
            Stephino_Rpg_Renderer_Ajax::RESULT_MIL_DEFENSE => 0,
        );
        $entitiesData = Stephino_Rpg_Db::get()->tableEntities()->getByCity($cityId);
        if (is_array($entitiesData)) {
            foreach ($entitiesData as $entityData) {
                $configEntity = Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT == $entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE]
                    ? Stephino_Rpg_Config::get()->units()->getById($entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID])
                    : Stephino_Rpg_Config::get()->ships()->getById($entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]);
                
                // Store the entity count
                $entityCount = (int) $entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT];
                if ($entityCount > 0) {
                    // Get the values
                    $militaryAttack = $entityCount * ($configEntity->getDamage() * $configEntity->getAmmo());
                    $militaryDefense = $entityCount * ($configEntity->getArmour() * $configEntity->getAgility());;

                    // Store the entity details
                    $militaryEntities[] = array(
                        Stephino_Rpg_Renderer_Ajax::RESULT_MIL_ATTACK    => $militaryAttack,
                        Stephino_Rpg_Renderer_Ajax::RESULT_MIL_DEFENSE   => $militaryDefense,
                        Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT => $entityCount,
                        Stephino_Rpg_Renderer_Ajax::RESULT_DATA          => $configEntity,
                    );

                    // Store the totals
                    $militaryEntitiesTotal[Stephino_Rpg_Renderer_Ajax::RESULT_MIL_ATTACK]  += $militaryAttack;
                    $militaryEntitiesTotal[Stephino_Rpg_Renderer_Ajax::RESULT_MIL_DEFENSE] += $militaryDefense;
                }
            }
        }
        
        // Load the template
        if (null !== $templatePath = self::_getTemplatePath(self::TEMPLATE_LIST_CITY_MILITARY)) {
            require $templatePath;
        }
    }
    
    /**
     * List PTFs
     * List all public user-generated platformer definitions
     * 
     * @use `list-ptf`
     */
    public static function cliListPtf() {
        // Get the user-generated levels
        $ptfData = array_filter(
            Stephino_Rpg_Db::get()->modelPtfs()->getForUserId(
                Stephino_Rpg_TimeLapse::get()->userId()
            ),
            function($ptfRow) {
                return 0 !== (int) $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_USER_ID];
            }
        );
        
        // No user-generated levels
        if (!count($ptfData)) {
            throw new Exception('No public user-generated levels available');
        }
        
        // Load the template
        if (null !== $templatePath = self::_getTemplatePath(self::TEMPLATE_LIST_PTF)) {
            require $templatePath;
        }
    }
    
    /**
     * Who am I?
     * 
     * @use `whoami`
     */
    public static function cliWhoami() {
        // Get the user information
        $userInfo = Stephino_Rpg_TimeLapse::get()->userData();
        
        // Invalid data
        if (!is_array($userInfo)) {
            throw new Exception('User not initialized');
        }
        
        // Inform the user
        echo sprintf(
            'User <b>#%d</b>, %s',
            $userInfo[Stephino_Rpg_Db_Table_Users::COL_ID],
            Stephino_Rpg_Utils_Lingo::getUserName($userInfo)
        );
    }
}

/*EOF*/