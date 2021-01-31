<?php

/**
 * Stephino_Rpg_Db
 * 
 * @title      Database Handler
 * @desc       Handle Database objects
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Db {

    /**
     * Tables
     */
    const TABLES = array(
        Stephino_Rpg_Db_Table_Users::class,
        Stephino_Rpg_Db_Table_Messages::class,
        Stephino_Rpg_Db_Table_Ptfs::class,
        Stephino_Rpg_Db_Table_Convoys::class,
        Stephino_Rpg_Db_Table_Queues::class,
        Stephino_Rpg_Db_Table_Islands::class,
        Stephino_Rpg_Db_Table_Cities::class,
        Stephino_Rpg_Db_Table_Buildings::class,
        Stephino_Rpg_Db_Table_Entities::class,
        Stephino_Rpg_Db_Table_ResearchFields::class,
        Stephino_Rpg_Db_Table_Statistics::class,
    );
    
    /**
     * Models
     */
    const MODELS = array(
        Stephino_Rpg_Db_Model_Users::class,
        Stephino_Rpg_Db_Model_Messages::class,
        Stephino_Rpg_Db_Model_Ptfs::class,
        Stephino_Rpg_Db_Model_Convoys::class,
        Stephino_Rpg_Db_Model_Queues::class,
        Stephino_Rpg_Db_Model_Islands::class,
        Stephino_Rpg_Db_Model_Cities::class,
        Stephino_Rpg_Db_Model_Buildings::class,
        Stephino_Rpg_Db_Model_Entities::class,
        Stephino_Rpg_Db_Model_ResearchFields::class,
        Stephino_Rpg_Db_Model_Statistics::class,
        Stephino_Rpg_Db_Model_Announcement::class,
        Stephino_Rpg_Db_Model_Invoices::class,
        Stephino_Rpg_Db_Model_PremiumModifiers::class,
    );

    /**
     * WordPress DataBase utilities
     * 
     * @var wpdb
     */
    protected $_wpDb = null;

    /**
     * Current instance's associated WordPress User ID
     * 
     * @var int
     */
    protected $_wpUserId = null;
    
    /**
     * MultiSite installation
     *
     * @var boolean
     */
    protected $_isMultisite = false;
    
    /**
     * Robot account ID
     * 
     * @var int|null
     */
    protected $_robotId = false;

    /**
     * Table prefix
     * 
     * @var string
     */
    protected $_tablePrefix = null;

    /**
     * Table instances
     * 
     * @var Stephino_Rpg_Db_Table[]
     */
    protected $_tableInstances = array();
    
    /**
     * Model instances
     * 
     * @var Stephino_Rpg_Db_Model[]
     */
    protected $_modelInstances = array();
    
    /**
     * Singleton instance of Stephino_Rpg_Db
     *
     * @var Stephino_Rpg_Db[]
     */
    protected static $_instances = array();
    
    /**
     * Database initialized
     * 
     * @var boolean
     */
    protected static $_init = false;
    
    /**
     * Get a DataBase instance
     * 
     * @param int|null $robotId  (optional) The direct Game User ID belongs to a robot 
     * (Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID is null); default <b>null</b>, meaning a human account
     * @param int|null $wpUserId (optional) WordPress User ID for the tables interactions; 
     * default <b>null</b>, auto-populated with get_current_user_id()
     * @return Stephino_Rpg_Db
     */
    public static function get($robotId = null, $wpUserId = null) {
        // Sanitize the user ID
        $wpUserId = is_numeric($wpUserId) ? intval($wpUserId) : 0;
        
        // Prepare the key
        $key = is_int($robotId) ? ('R' . $robotId) : ('H' . $wpUserId);
        
        // Add to cache
        if (!isset(self::$_instances[$key])) {
            self::$_instances[$key] = new self($wpUserId, is_int($robotId) ? intval($robotId) : null);
        }
        
        // Single-thread initialization
        if (!self::$_init) {
            self::$_init = true;
            
            // Not in uninstall mode
            if (!defined('WP_UNINSTALL_PLUGIN')) {
                // DataBase structure update
                if (Stephino_Rpg::PLUGIN_VERSION_DATABASE != Stephino_Rpg_Cache_Game::getInstance()->getValue(Stephino_Rpg_Cache_Game::KEY_VERSION_DB, '')) {
                    Stephino_Rpg_Cache_Game::getInstance()->setValue(
                        Stephino_Rpg_Cache_Game::KEY_VERSION_DB, 
                        Stephino_Rpg::PLUGIN_VERSION_DATABASE
                    );

                    // Get the Upgrade tool
                    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

                    // Prepare the queries
                    $queries = array_filter(
                        array_map(
                            function(/* @var $item Stephino_Rpg_Db_Table */ $item) {
                                return trim($item->getCreateStatement());
                            }, self::$_instances[$key]->_tableInstances
                        )
                    );

                    // Update the DataBase structure
                    dbDelta($queries);
                }

                // Game update
                if (Stephino_Rpg::PLUGIN_VERSION != Stephino_Rpg_Cache_Game::getInstance()->getValue(Stephino_Rpg_Cache_Game::KEY_VERSION, '')) {
                    Stephino_Rpg_Cache_Game::getInstance()->setValue(
                        Stephino_Rpg_Cache_Game::KEY_VERSION, 
                        Stephino_Rpg::PLUGIN_VERSION
                    );

                    // Update the platformer pre-defined levels
                    $ptfMd5 = md5_file(STEPHINO_RPG_ROOT . '/ui/js/ptf/' . Stephino_Rpg_Renderer_Ajax::FILE_PTF_LIST . '.json');
                    if ($ptfMd5 != Stephino_Rpg_Cache_Game::getInstance()->getValue(Stephino_Rpg_Cache_Game::KEY_VERSION_PTF, '')) {
                        Stephino_Rpg_Cache_Game::getInstance()->setValue(
                            Stephino_Rpg_Cache_Game::KEY_VERSION_PTF, 
                            $ptfMd5
                        );

                        // Reload the model
                        self::$_instances[$key]->modelPtfs()->reload();
                    }
                }
            }
        }
        
        return self::$_instances[$key];
    }

    /**
     * Initialize the tables and create/update them as necessary
     * 
     * @param int      $wpUserId (optional) WordPress User ID; default <b>0</b>, auto-populated with get_current_user_id()
     * @param int|null $robotId  (optional) Whether the current DB instance deals with a Robot account 
     */
    protected function __construct($wpUserId = 0, $robotId = null) {
        global $wpdb, $blog_id;

        // Store the robot Id
        $this->_robotId = $robotId;
        
        // Store the WordPress Database utilities object reference
        $this->_wpDb = $wpdb;

        // Set the WP user ID
        $this->_wpUserId = $wpUserId > 0 ? $wpUserId : get_current_user_id();
        
        // Store the multisite flag
        $this->_isMultisite = is_multisite();

        // Prepare the table prefix
        $this->_tablePrefix = $this->_wpDb->base_prefix . Stephino_Rpg::PLUGIN_VARNAME . '_';

        // MultiSite installation on a secondary site
        if ($this->_isMultisite && 1 != $blog_id) {
            $this->_tablePrefix .= $blog_id . "_";
        }

        // Initialize the table instances
        foreach (self::TABLES as $tableClassName) {
            // Get the table KEY
            if (null !== $tableKey = constant($tableClassName . '::NAME')) {
                $this->_tableInstances[$tableKey] = new $tableClassName($this);
            }
        }
        
        // Initialize the model instances
        foreach (self::MODELS as $modelClassName) {
            // Get the model KEY
            if (null !== $modelKey = constant($modelClassName . '::NAME')) {
                $this->_modelInstances[$modelKey] = new $modelClassName($this);
            }
        }
    }
    
    /**
     * <b>WARNING!</b><br/>
     * This method Drops <b>ALL</b> game tables!
     */
    public function purge() {
        // Go through all the tables
        foreach ($this->_tableInstances as $table) {
            $this->_wpDb->query('DROP TABLE `' . $table->getTableName() . '`');
        }
    }

    /**
     * Get the WordPress DataBase utilities
     * 
     * @return wpdb
     */
    public function getWpDb() {
        return $this->_wpDb;
    }

    /**
     * Get the current WordPress user ID
     * 
     * @return int
     */
    public function getWpUserId() {
        return $this->_wpUserId;
    }
    
    /**
     * Get whether this is a multisite installation
     * 
     * @return boolean
     */
    public function getMultisite() {
        return $this->_isMultisite;
    }
    
    /**
     * Get the current robot ID or null if in "Human" mode
     * 
     * @return int|null
     */
    public function getRobotId() {
        return $this->_robotId;
    }

    /**
     * Get the tables prefix
     * 
     * @return string
     */
    public function getPrefix() {
        return $this->_tablePrefix;
    }
    
    /**
     * The "Users" table
     * 
     * @return Stephino_Rpg_Db_Table_Users
     */
    public function tableUsers() {
        return $this->_tableInstances[Stephino_Rpg_Db_Table_Users::NAME];
    }

    /**
     * The "Messages" table
     * 
     * @return Stephino_Rpg_Db_Table_Messages
     */
    public function tableMessages() {
        return $this->_tableInstances[Stephino_Rpg_Db_Table_Messages::NAME];
    }

    /**
     * The "Platformers" table
     * 
     * @return Stephino_Rpg_Db_Table_Ptfs
     */
    public function tablePtfs() {
        return $this->_tableInstances[Stephino_Rpg_Db_Table_Ptfs::NAME];
    }

    /**
     * The "Convoys" table
     * 
     * @return Stephino_Rpg_Db_Table_Convoys
     */
    public function tableConvoys() {
        return $this->_tableInstances[Stephino_Rpg_Db_Table_Convoys::NAME];
    }

    /**
     * The "Queues" table
     * 
     * @return Stephino_Rpg_Db_Table_Queues
     */
    public function tableQueues() {
        return $this->_tableInstances[Stephino_Rpg_Db_Table_Queues::NAME];
    }

    /**
     * The "Islands" table
     * 
     * @return Stephino_Rpg_Db_Table_Islands
     */
    public function tableIslands() {
        return $this->_tableInstances[Stephino_Rpg_Db_Table_Islands::NAME];
    }

    /**
     * The "Cities" table
     * 
     * @return Stephino_Rpg_Db_Table_Cities
     */
    public function tableCities() {
        return $this->_tableInstances[Stephino_Rpg_Db_Table_Cities::NAME];
    }

    /**
     * The "Buildings" table
     * 
     * @return Stephino_Rpg_Db_Table_Buildings
     */
    public function tableBuildings() {
        return $this->_tableInstances[Stephino_Rpg_Db_Table_Buildings::NAME];
    }

    /**
     * The "Entities" table
     * 
     * @return Stephino_Rpg_Db_Table_Entities
     */
    public function tableEntities() {
        return $this->_tableInstances[Stephino_Rpg_Db_Table_Entities::NAME];
    }

    /**
     * The "ResearchFields" table
     * 
     * @return Stephino_Rpg_Db_Table_ResearchFields
     */
    public function tableResearchFields() {
        return $this->_tableInstances[Stephino_Rpg_Db_Table_ResearchFields::NAME];
    }

    /**
     * The "Statistics" table
     * 
     * @return Stephino_Rpg_Db_Table_Statistics
     */
    public function tableStatistics() {
        return $this->_tableInstances[Stephino_Rpg_Db_Table_Statistics::NAME];
    }
    
    /**
     * The "Users" model
     * 
     * @return Stephino_Rpg_Db_Model_Users
     */
    public function modelUsers() {
        return $this->_modelInstances[Stephino_Rpg_Db_Model_Users::NAME];
    }
    
    /**
     * The "Messages" model
     * 
     * @return Stephino_Rpg_Db_Model_Messages
     */
    public function modelMessages() {
        return $this->_modelInstances[Stephino_Rpg_Db_Model_Messages::NAME];
    }
    
    /**
     * The "Platformers" model
     * 
     * @return Stephino_Rpg_Db_Model_Ptfs
     */
    public function modelPtfs() {
        return $this->_modelInstances[Stephino_Rpg_Db_Model_Ptfs::NAME];
    }
    
    /**
     * The "Convoys" model
     * 
     * @return Stephino_Rpg_Db_Model_Convoys
     */
    public function modelConvoys() {
        return $this->_modelInstances[Stephino_Rpg_Db_Model_Convoys::NAME];
    }
    
    /**
     * The "Queues" model
     * 
     * @return Stephino_Rpg_Db_Model_Queues
     */
    public function modelQueues() {
        return $this->_modelInstances[Stephino_Rpg_Db_Model_Queues::NAME];
    }
    
    /**
     * The "Islands" model
     * 
     * @return Stephino_Rpg_Db_Model_Islands
     */
    public function modelIslands() {
        return $this->_modelInstances[Stephino_Rpg_Db_Model_Islands::NAME];
    }
    
    /**
     * The "Cities" model
     * 
     * @return Stephino_Rpg_Db_Model_Cities
     */
    public function modelCities() {
        return $this->_modelInstances[Stephino_Rpg_Db_Model_Cities::NAME];
    }
    
    /**
     * The "Buildings" model
     * 
     * @return Stephino_Rpg_Db_Model_Buildings
     */
    public function modelBuildings() {
        return $this->_modelInstances[Stephino_Rpg_Db_Model_Buildings::NAME];
    }
    
    /**
     * The "Entities" model
     * 
     * @return Stephino_Rpg_Db_Model_Entities
     */
    public function modelEntities() {
        return $this->_modelInstances[Stephino_Rpg_Db_Model_Entities::NAME];
    }
    
    /**
     * The "Invoices" model
     * 
     * @return Stephino_Rpg_Db_Model_Invoices
     */
    public function modelInvoices() {
        return $this->_modelInstances[Stephino_Rpg_Db_Model_Invoices::NAME];
    }
    
    /**
     * The "Premium Modifiers" model
     * 
     * @return Stephino_Rpg_Db_Model_PremiumModifiers
     */
    public function modelPremiumModifiers() {
        return $this->_modelInstances[Stephino_Rpg_Db_Model_PremiumModifiers::NAME];
    }
    
    /**
     * The "Research Fields" model
     * 
     * @return Stephino_Rpg_Db_Model_ResearchFields
     */
    public function modelResearchFields() {
        return $this->_modelInstances[Stephino_Rpg_Db_Model_ResearchFields::NAME];
    }
    
    /**
     * The "Statistics" model
     * 
     * @return Stephino_Rpg_Db_Model_Statistics
     */
    public function modelStatistics() {
        return $this->_modelInstances[Stephino_Rpg_Db_Model_Statistics::NAME];
    }
    
    /**
     * The "Announcement" model
     * 
     * @return Stephino_Rpg_Db_Model_Announcement
     */
    public function modelAnnouncement() {
        return $this->_modelInstances[Stephino_Rpg_Db_Model_Announcement::NAME];
    }
}

/*EOF*/