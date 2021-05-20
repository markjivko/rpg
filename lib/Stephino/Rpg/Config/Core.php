<?php

/**
 * Stephino_Rpg_Config_Core
 * 
 * @title      Core
 * @desc       Holds the core game configuration
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Config_Core extends Stephino_Rpg_Config_Item_Single {

    // Core Labels
    use Stephino_Rpg_Config_Trait_Labels;
    
    /**
     * Serialization Key - Must be defined by each item individually
     */
    const KEY = 'core';
    
    // Default labels
    const DEFAULT_NAME                        = 'Stephino';
    const DEFAULT_LABEL_RES_GOLD              = 'Gold';
    const DEFAULT_LABEL_RES_GEM               = 'Gems';
    const DEFAULT_LABEL_RES_RESEARCH          = 'Research points';
    const DEFAULT_LABEL_RES_ALPHA             = 'Alpha';
    const DEFAULT_LABEL_RES_BETA              = 'Beta';
    const DEFAULT_LABEL_RES_GAMMA             = 'Gamma';
    const DEFAULT_LABEL_RES_EXTRA1            = 'X1';
    const DEFAULT_LABEL_RES_EXTRA2            = 'X2';
    const DEFAULT_LABEL_METRIC_POPULATION     = 'Population';
    const DEFAULT_LABEL_METRIC_SATISFACTION   = 'Satisfaction';
    const DEFAULT_LABEL_METRIC_STORAGE        = 'Storage';
    const DEFAULT_LABEL_MILITARY_ATTACK       = 'Attack';
    const DEFAULT_LABEL_MILITARY_DEFENSE      = 'Defense';
    const DEFAULT_LABEL_CONFIG_GOVERNMENT     = 'Government';
    const DEFAULT_LABEL_CONFIG_GOVERNMENTS    = 'Governments';
    const DEFAULT_LABEL_CONFIG_ISLAND         = 'Island';
    const DEFAULT_LABEL_CONFIG_ISLANDS        = 'Islands';
    const DEFAULT_LABEL_CONFIG_ISLAND_STATUE  = 'Island Statue';
    const DEFAULT_LABEL_CONFIG_ISLAND_STATUES = 'Island Statues';
    const DEFAULT_LABEL_CONFIG_CITY           = 'City';
    const DEFAULT_LABEL_CONFIG_CITIES         = 'Cities';
    const DEFAULT_LABEL_CONFIG_BUILDING       = 'Building';
    const DEFAULT_LABEL_CONFIG_BUILDINGS      = 'Buildings';
    const DEFAULT_LABEL_CONFIG_UNIT           = 'Unit';
    const DEFAULT_LABEL_CONFIG_UNITS          = 'Units';
    const DEFAULT_LABEL_CONFIG_SHIP           = 'Ship';
    const DEFAULT_LABEL_CONFIG_SHIPS          = 'Ships';
    const DEFAULT_LABEL_CONFIG_RES_FIELD      = 'Research Field';
    const DEFAULT_LABEL_CONFIG_RES_FIELDS     = 'Research Fields';
    const DEFAULT_LABEL_CONFIG_RES_AREA       = 'Research Area';
    const DEFAULT_LABEL_CONFIG_RES_AREAS      = 'Research Areas';
    
    /**
     * Low aggression: don't fight back, don't initiate attacks
     */
    const ROBOT_AGG_LOW    = 'low';
    
    /**
     * Medium aggression: fight back, don't initiate attacks
     */
    const ROBOT_AGG_MEDIUM = 'medium';
    
    /**
     * High aggression: fight back, initiate attacks
     */
    const ROBOT_AGG_HIGH   = 'high';
    
    /**
     * Game theme
     * 
     * @var string
     */
    protected $_theme = Stephino_Rpg_Theme::THEME_DEFAULT;

    /**
     * Game name
     * 
     * @var string|null
     */
    protected $_name = null;

    /**
     * Game description
     *
     * @var string|null
     */
    protected $_description = null;
    
    /**
     * Show the WordPress.org link
     * 
     * @var boolean
     */
    protected $_showWpLink = true;
    
    /**
     * Show About dialog on update
     * 
     * @var boolean
     */
    protected $_showAbout = true;
    
    /**
     * Show the Reload button on mobile devices
     *
     * @var boolean
     */
    protected $_showReloadButton = false;
    
    /**
     * Leader board size
     * 
     * @var int
     */
    protected $_leaderBoardSize = 25;
    
    /**
     * Message: Page Size
     * 
     * @var int
     */
    protected $_messagePageSize = 5;
    
    /**
     * Languages
     * 
     * @var string[]
     */
    protected $_languages = null;
    
    /**
     * Platformer Enabled
     * 
     * @var boolean
     */
    protected $_ptfEnabled = true;
    
    /**
     * Platformer Author Limit
     *
     * @var int
     */
    protected $_ptfAuthorLimit = 24;
    
    /**
     * Platformer Reward: Player
     * 
     * @var int|null
     */
    protected $_ptfRewardPlayer = 50;
    
    /**
     * Platformer Reward: Author
     * 
     * @var int|null
     */
    protected $_ptfRewardAuthor = 20;
    
    /**
     * Platformer Reward: Reset Time in hours
     * 
     * @var int|null
     */
    protected $_ptfRewardResetHours = 12;
    
    /**
     * Points earned for winning platformer levels
     * 
     * @var int
     */
    protected $_ptfScore = 5;
    
    /**
     * Maximum number of suspended games per player
     * 
     * @var int
     */
    protected $_ptfStrikes = 3;
    
    /**
     * Chat Room
     * 
     * @var boolean
     */
    protected $_chatRoom = false;
    
    /**
     * Firebase > Project settings > Project ID
     *
     * @var string|null
     */
    protected $_firebaseProjectId = null;
    
    /**
     * Firebase > Project settings > Web API Key
     *
     * @var string|null
     */
    protected $_firebaseWebApiKey = null;
    
    /**
     * Firebase > Realtime Database > URL
     *
     * @var string|null
     */
    protected $_firebaseDatabaseUrl = null;
    
    /**
     * Message: Daily send limit
     * 
     * @var int
     */
    protected $_messageDailyLimit = 10;
    
    /**
     * Message: Inbox size limit
     * 
     * @var int
     */
    protected $_messageInboxLimit = 100;
    
    /**
     * Message: Message age limit in days
     * 
     * @var int
     */
    protected $_messageMaxAge = 30;
    
    /**
     * Points earned for a defeat
     * 
     * @var int
     */
    protected $_scoreBattleDefeat = -60;

    /**
     * Points earned for a draw
     * 
     * @var int
     */
    protected $_scoreBattleDraw = 10;

    /**
     * Points earned for a victory
     * 
     * @var int
     */
    protected $_scoreBattleVictory = 900;

    /**
     * Points earned for upgrading a building
     * 
     * @var int
     */
    protected $_scoreQueueBuilding = 6;

    /**
     * Points earned for recruiting an entity
     * 
     * @var int
     */
    protected $_scoreQueueEntity = 1;

    /**
     * Points earned for finishing a research
     * 
     * @var int
     */
    protected $_scoreQueueResearch = 90;
    
    /**
     * PayPal Client ID
     * 
     * @var string|null
     */
    protected $_payPalClientId = null;
    
    /**
     * PayPal Client Secret
     * 
     * @var string|null
     */
    protected $_payPalClientSecret = null;
    
    /**
     * PayPal Currency
     * 
     * @var string
     */
    protected $_payPalCurrency = Stephino_Rpg_Db_Model_Invoices::CURRENCY_USD;
    
    /**
     * PayPal Sandbox mode
     * 
     * @var boolean
     */
    protected $_payPalSandbox = true;
    
    /**
     * Sandbox mode
     * 
     * @var boolean
     */
    protected $_sandbox = false;
    
    /**
     * Console Enabled flag
     *
     * @var boolean
     */
    protected $_consoleEnabled = false;
    
    /**
     * Robots aggression level
     *
     * @var string
     */
    protected $_robotsAggression = self::ROBOT_AGG_HIGH;
    
    /**
     * Robots fervor (between 5 and 100)
     * 
     * @var int
     */
    protected $_robotsFervor = 50;
    
    /**
     * Robots timeout: minimum number of hours to wait between attacks (between 1 and 168)
     * 
     * @var int
     */
    protected $_robotsTimeout = 48;

    /**
     * Robot time-lapses per request
     * 
     * @var int|null
     */
    protected $_robotTimeLapsesPerRequest = 5;
    
    /**
     * Time-Lapse cooldown
     * 
     * @var int
     */
    protected $_timeLapseCooldown = 10;
    
    /**
     * Cron interval (minutes)
     * 
     * @var int
     */
    protected $_cronInterval = 3;
    
    /**
     * Cron accuracy (hours)
     * 
     * @var int
     */
    protected $_cronAccuracy = 12;
    
    /**
     * Cron max age (days)
     * 
     * @var int
     */
    protected $_cronMaxAge = 90;
    
    /**
     * Islands Initial Count
     * 
     * @var int
     */
    protected $_initialIslandsCount = 100;

    /**
     * Initials Islands per player
     * 
     * @var int
     */
    protected $_initialIslandsPerUser = 3;
    
    /**
     * Initial Robots per player
     * 
     * @var int
     */
    protected $_initialRobotsPerUser = 3;
    
    /**
     * Player Resource Gold
     * 
     * @var int
     */
    protected $_initialUserResourceGold = 0;

    /**
     * Player Resource Gem
     * 
     * @var int|null
     */
    protected $_initialUserResourceGem = 0;
    
    /**
     * Player Resource Research
     * 
     * @var int
     */
    protected $_initialUserResourceResearch = 0;
    
    /**
     * City First Buildings
     * 
     * @var int[]|null Stephino_Rpg_Config_Building IDs
     */
    protected $_initialCityBuildings = null;
    
    /**
     * Travel Time
     * 
     * @var int
     */
    protected $_travelTime = 10;
    
    /**
     * Noob: City Levels Difference
     * 
     * @var int
     */
    protected $_noobLevels = 5;
    
    /**
     * Noob: Age
     * 
     * @var int
     */
    protected $_noobAge = 7;

    /**
     * Gem To Gold ratio
     * 
     * @var float
     */
    protected $_gemToGoldRatio = 0;

    /**
     * Gem To Research points ratio
     * 
     * @var float
     */
    protected $_gemToResearchRatio = 0;
    
    /**
     * Market enabled
     * 
     * @var boolean
     */
    protected $_marketEnabled = false;
    
    /**
     * Market building
     * 
     * @var int|null Stephino_Rpg_Config_Building ID
     */
    protected $_marketBuildingId = null;
    
    /**
     * Market: Polynomial
     * 
     * @var string|null
     */
    protected $_marketPolynomial = null;
        
    /**
     * Market: Gain
     * 
     * @var string|null
     */
    protected $_marketGain = 10;
    
    /**
     * Market: Resource Alpha to Gold ratio
     * 
     * @var float
     */
    protected $_marketResourceAlpha = 0;

    /**
     * Market: Resource Beta to Gold ratio
     * 
     * @var float
     */
    protected $_marketResourceBeta = 0;

    /**
     * Market: Resource Gamma to Gold ratio
     * 
     * @var float
     */
    protected $_marketResourceGamma = 0;

    /**
     * Market: Resource Extra1 to Gold ratio
     * 
     * @var float
     */
    protected $_marketResourceExtra1 = 0;

    /**
     * Market: Resource Extra2 to Gold ratio
     * 
     * @var float
     */
    protected $_marketResourceExtra2 = 0;
    
    /**
     * Main building
     * 
     * @var int|null Stephino_Rpg_Config_Building ID
     */
    protected $_mainBuildingId = null;

    /**
     * Metropolis Satisfaction Boost
     * 
     * @var int
     */
    protected $_capitalSatisfactionBonus = 10;
    
    /**
     * Maximum Queue Buildings
     * 
     * @var int
     */
    protected $_maxQueueBuildings = 3;
    
    /**
     * Maximum Queue Entities
     * 
     * @var int
     */
    protected $_maxQueueEntities = 3;
    
    /**
     * Maximum Queue Research Fields
     * 
     * @var int
     */
    protected $_maxQueueResearchFields = 3;
    
    /**
     * Get the list of mandatory buildings for a city
     * 
     * @return Stephino_Rpg_Config_Building[]
     */
    public function cityInitialBuildings() {
        // Initialize the building configs
        $buildingConfigs = array();
        
        // Add the main building
        if (null !== $this->getMainBuilding()) {
            $buildingConfigs[$this->getMainBuilding()->getId()] = $this->getMainBuilding();
        }
        
        // Add secondary buildings
        if (is_array($this->getInitialCityBuildings())) {
            foreach ($this->getInitialCityBuildings() as $buildingConfig) {
                if (!isset($buildingConfigs[$buildingConfig->getId()])) {
                    $buildingConfigs[$buildingConfig->getId()] = $buildingConfig;
                }
            }
        }
        return $buildingConfigs;
    }
    
    /**
     * Set the name of your game
     * 
     * @return string Name
     */
    public function getName($htmlOutput = false) {
        $result = (null === $this->_name ? self::DEFAULT_NAME : $this->_name);
        return $htmlOutput ? Stephino_Rpg_Utils_Lingo::escape($result) : $result;
    }

    /**
     * Set the game name
     * 
     * @param string $name Game name
     * @return Stephino_Rpg_Config_Core
     */
    public function setName($name) {
        $this->_name = Stephino_Rpg_Utils_Lingo::cleanup($name);

        return $this;
    }

    /**
     * Describe your game<br/>
     * <span class="info">MarkDown enabled</span>
     * 
     * @ref large
     * @return string Description
     */
    public function getDescription() {
        return $this->_description;
    }

    /**
     * Set the game description
     * 
     * @param string $description Game description
     * @return Stephino_Rpg_Config_Core
     */
    public function setDescription($description) {
        $this->_description = Stephino_Rpg_Utils_Lingo::cleanup($description);

        return $this;
    }
    
    /**
     * Show the "Free Install" WordPress.org link to your players 
     * and embed a link in the "Stephino RPG" Gutenberg block
     * 
     * @section User Interface
     * @return boolean Show WordPress Link
     */
    public function getShowWpLink() {
        return (boolean) $this->_showWpLink;
    }
    
    /**
     * Set the "Show WP Link" parameter
     * 
     * @param boolean $showWpLink Show the WordPress.org Link
     * @return Stephino_Rpg_Config_Core
     */
    public function setShowWpLink($showWpLink) {
        $this->_showWpLink = (boolean) $showWpLink;
        
        return $this;
    }
    
    /**
     * Show the Credits and current version's Changelog to your players whenever the game updates
     * 
     * @return boolean Updates Announcement
     */
    public function getShowAbout() {
        return (boolean) $this->_showAbout;
    }
    
    /**
     * Set the "Show About" parameter
     * 
     * @param boolean $showAbout Show the About dialog on update
     * @return Stephino_Rpg_Config_Core
     */
    public function setShowAbout($showAbout) {
        $this->_showAbout = (boolean) $showAbout;
        
        return $this;
    }
    
    /**
     * Show a reload button "↺" on mobile devices
     * 
     * @return boolean Mobile Reload
     */
    public function getShowReloadButton() {
        return (boolean) $this->_showReloadButton;
    }
    
    /**
     * Set the "Show Reload Button" parameter
     * 
     * @param boolean $showReloadButton Show Reload Button
     * @return Stephino_Rpg_Config_Core
     */
    public function setShowReloadButton($showReloadButton) {
        $this->_showReloadButton = (boolean) $showReloadButton;
        
        return $this;
    }
    
    /**
     * Number of players to show in the leader board
     * 
     * @default 25
     * @ref 5,500
     * @return int Leader board size
     */
    public function getLeaderBoardSize() {
        return null === $this->_leaderBoardSize ? 25 : $this->_leaderBoardSize;
    }
    
    /**
     * Set the "Leader Board Size" parameter
     * 
     * @param int $leaderBoardSize Leader Board Size
     * @return Stephino_Rpg_Config_Core
     */
    public function setLeaderBoardSize($leaderBoardSize) {
        $this->_leaderBoardSize = (null === $leaderBoardSize ? 25 : intval($leaderBoardSize));

        // Minimum and maximum
        if ($this->_leaderBoardSize < 5) {
            $this->_leaderBoardSize = 5;
        }
        if ($this->_leaderBoardSize > 500) {
            $this->_leaderBoardSize = 500;
        }

        return $this;
    }
    
    /**
     * Number of message to display per page
     * 
     * @default 5
     * @ref 3,50
     * @return int Messages: Page Size
     */
    public function getMessagePageSize() {
        return null === $this->_messagePageSize ? 5 : $this->_messagePageSize;
    }
    
    /**
     * Set the "Message Page Size" parameter
     * 
     * @param int|null $messagePageSize Message page size
     * @return Stephino_Rpg_Config_Core
     */
    public function setMessagePageSize($messagePageSize) {
        $this->_messagePageSize = (null === $messagePageSize ? 5 : intval($messagePageSize));
        
        // Minimum and maximum
        if ($this->_messagePageSize < 3) {
            $this->_messagePageSize = 3;
        }
        if ($this->_messagePageSize > 50) {
            $this->_messagePageSize = 50;
        }
        
        return $this;
    }
    
    /**
     * Players can choose one of these languages apart from English
     * 
     * @opt de_DE:Deutsche,es_ES:Español,fr_FR:Français,it_IT:Italiano,pt_BR:Português,ro_RO:Română,ru_RU:Русский
     * @return string[] Languages
     */
    public function getLanguages() {
        $result = $this->_languages;
        if (null === $result) {
            $result = array_filter(
                array_keys(Stephino_Rpg_Utils_Lingo::ALLOWED_LANGS),
                function($item) {
                    return Stephino_Rpg_Utils_Lingo::LANG_EN != $item;
                }
            );
        }
        
        return $result;
    }
    
    /**
     * Set the "Languages" parameter
     * 
     * @param int|null $languages Languages
     * @return Stephino_Rpg_Config_Core
     */
    public function setLanguages($languages) {
        // Re-initialize the languages
        $this->_languages = array();
        
        if (is_array($languages)) {
            $allowedValues = array_filter(
                array_keys(Stephino_Rpg_Utils_Lingo::ALLOWED_LANGS),
                function($item) {
                    return Stephino_Rpg_Utils_Lingo::LANG_EN != $item;
                }
            );
            
            foreach ($languages as $language) {
                if (in_array($language, $allowedValues)) {
                    $this->_languages[] = $language;
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Enable platformer mini-games, allowing users to earn rewards by playing and designing their own levels
     * 
     * @section User Content
     * @return boolean Platformer
     */
    public function getPtfEnabled() {
        return (boolean) $this->_ptfEnabled;
    }
    
    /**
     * Set the "PTF Enabled" parameter
     * 
     * @param boolean $ptfEnabled PTF Enabled
     * 
     * @return Stephino_Rpg_Config_Core
     */
    public function setPtfEnabled($ptfEnabled) {
        $this->_ptfEnabled = (boolean) $ptfEnabled;
        
        return $this;
    }
    
    /**
     * The maximum number of games a player can create<br/>
     * <b>0</b> means players cannot add new mini-games<br/>
     * Does not affect admins
     * 
     * @depends ptfEnabled
     * @default 24
     * @ref 0
     * @return int Platformer Author Limit
     */
    public function getPtfAuthorLimit() {
        return (null === $this->_ptfAuthorLimit ? 24 : $this->_ptfAuthorLimit);
    }
    
    /**
     * Set the "PTF Author Limit" parameter
     * 
     * @param int $ptfAuthorLimit PTF Author Limit
     * @return Stephino_Rpg_Config_Core
     */
    public function setPtfAuthorLimit($ptfAuthorLimit) {
        $this->_ptfAuthorLimit = (null === $ptfAuthorLimit ? 24 : intval($ptfAuthorLimit));
        
        // Minimum and maximum
        if ($this->_ptfAuthorLimit < 0) {
            $this->_ptfAuthorLimit = 0;
        }
        
        return $this;
    }
    
    /**
     * Players can earn a reward ({x}) for finishing a platformer game
     * 
     * @placeholder core.resourceGemName,Gems
     * @depends ptfEnabled
     * @default 50
     * @ref 0
     * @return int Platformer Reward: Player
     */
    public function getPtfRewardPlayer() {
        return (null === $this->_ptfRewardPlayer ? 50 : $this->_ptfRewardPlayer);
    }
    
    /**
     * Set the "PTF Reward Gems Player" parameter
     * 
     * @param int $ptfRewardGems PTF Reward Gems Player
     * @return Stephino_Rpg_Config_Core
     */
    public function setPtfRewardPlayer($ptfRewardGems) {
        $this->_ptfRewardPlayer = (null === $ptfRewardGems ? 50 : intval($ptfRewardGems));
        
        // Minimum and maximum
        if ($this->_ptfRewardPlayer < 0) {
            $this->_ptfRewardPlayer = 0;
        }
        
        return $this;
    }
    
    /**
     * Authors can earn a reward ({x}) every time one of their games are played
     * 
     * @placeholder core.resourceGemName,Gems
     * @depends ptfEnabled
     * @default 20
     * @ref 0
     * @return int Platformer Reward: Author
     */
    public function getPtfRewardAuthor() {
        return (null === $this->_ptfRewardAuthor ? 20 : $this->_ptfRewardAuthor);
    }
    
    /**
     * Set the "PTF Reward Gems Author" parameter
     * 
     * @param int $ptfRewardGems PTF Reward Gems Author
     * @return Stephino_Rpg_Config_Core
     */
    public function setPtfRewardAuthor($ptfRewardGems) {
        $this->_ptfRewardAuthor = (null === $ptfRewardGems ? 20 : intval($ptfRewardGems));
        
        // Minimum and maximum
        if ($this->_ptfRewardAuthor < 0) {
            $this->_ptfRewardAuthor = 0;
        }
        
        return $this;
    }
    
    /**
     * Players can earn rewards for playing the same game again after this many hours<br/>
     * <b>0</b> means the rewards are given only once
     * 
     * @depends ptfEnabled
     * @default 12
     * @ref 0,720
     * @return int Platformer Reward: Reset Time
     */
    public function getPtfRewardResetHours() {
        return (null === $this->_ptfRewardResetHours ? 12 : $this->_ptfRewardResetHours);
    }
    
    /**
     * Set the "PTF Reward Reset Hours" parameter
     * 
     * @param int $ptfRewardResetHours PTF Reward Reset Hours
     * @return Stephino_Rpg_Config_Core
     */
    public function setPtfRewardResetHours($ptfRewardResetHours) {
        $this->_ptfRewardResetHours = (null === $ptfRewardResetHours ? 12 : intval($ptfRewardResetHours));
        
        // Minimum and maximum
        if ($this->_ptfRewardResetHours < 0) {
            $this->_ptfRewardResetHours = 0;
        }
        if ($this->_ptfRewardResetHours > 720) {
            $this->_ptfRewardResetHours = 720;
        }
        
        return $this;
    }
    
    /**
     * Points earned for winning platformer levels along with the platformer reward
     *
     * @depends ptfEnabled
     * @default 5
     * @ref -5000,5000
     * @return int Platformer Points
     */
    public function getPtfScore() {
        return null === $this->_ptfScore ? 5 : $this->_ptfScore;
    }
    
    /**
     * Set the "PTF Score" parameter
     * 
     * @param int $ptfScore Score
     * @return Stephino_Rpg_Config_Core
     */
    public function setPtfScore($ptfScore) {
        $this->_ptfScore = (null === $ptfScore ? 5 : intval($ptfScore));

        // Minimum and maximum
        if ($this->_ptfScore < -5000) {
            $this->_ptfScore = -5000;
        }
        if ($this->_ptfScore > 5000) {
            $this->_ptfScore = 5000;
        }

        return $this;
    }
    
    /**
     * Maximum number of suspended games a player is allowed to have
     *
     * @depends ptfEnabled
     * @default 3
     * @ref 1,10
     * @return int Strikes policy
     */
    public function getPtfStrikes() {
        return null === $this->_ptfStrikes ? 3 : $this->_ptfStrikes;
    }
    
    /**
     * Set the "PTF Strikes" parameter
     * 
     * @param int $ptfStrikes Strikes
     * @return Stephino_Rpg_Config_Core
     */
    public function setPtfStrikes($ptfStrikes) {
        $this->_ptfStrikes = (null === $ptfStrikes ? 3 : intval($ptfStrikes));

        // Minimum and maximum
        if ($this->_ptfStrikes < 1) {
            $this->_ptfStrikes = 1;
        }
        if ($this->_ptfStrikes > 10) {
            $this->_ptfStrikes = 10;
        }

        return $this;
    }
    
    /**
     * Enable the chat room so users can interact in real-time with <b>Google Firebase</b><br/><br/>
     * <a class="info thickbox" href="/wp-content/plugins/stephino-rpg/ui/help/firebase-rules.html?ver=0.3.5&TB_iframe=true&width=980&height=800" target="_blank"><b>&#x1f449; Getting Started</b></a>
     * 
     * @sensitive true
     * @return boolean Chat Room
     */
    public function getChatRoom() {
        return (boolean) $this->_chatRoom;
    }
    
    /**
     * Set the "Chat Room" parameter
     * 
     * @param boolean $chatRoom Chat Room
     * @return Stephino_Rpg_Config_Core
     */
    public function setChatRoom($chatRoom) {
        $this->_chatRoom = (boolean) $chatRoom;
        
        return $this;
    }
    
    /**
     * Firebase > Project Settings > Project ID
     * 
     * @depends chatRoom
     * @sensitive true
     * @return string|null Chat Room: Project ID
     */
    public function getFirebaseProjectId() {
        return $this->_firebaseProjectId;
    }
    
    /**
     * Set the "Firebase Project ID" parameter
     * 
     * @param string|null $projectId Firebase Project ID
     * @return Stephino_Rpg_Config_Core
     */
    public function setFirebaseProjectId($projectId) {
        $this->_firebaseProjectId = Stephino_Rpg_Utils_Lingo::cleanup($projectId);

        return $this;
    }
    
    /**
     * Firebase > Project Settings > Web API Key
     * 
     * @depends chatRoom
     * @sensitive true
     * @return string|null Chat Room: Web API Key
     */
    public function getFirebaseWebApiKey() {
        return $this->_firebaseWebApiKey;
    }
    
    /**
     * Set the "Firebase Web API Key" parameter
     * 
     * @param string|null $webApiKey Firebase Web API Key
     * @return Stephino_Rpg_Config_Core
     */
    public function setFirebaseWebApiKey($webApiKey) {
        $this->_firebaseWebApiKey = Stephino_Rpg_Utils_Lingo::cleanup($webApiKey);

        return $this;
    }
    
    /**
     * Firebase > Realtime Database > URL
     * 
     * @depends chatRoom
     * @sensitive true
     * @return string|null Chat Room: Database URL
     */
    public function getFirebaseDatabaseUrl() {
        return $this->_firebaseDatabaseUrl;
    }
    
    /**
     * Set the "Firebase Database URL" parameter
     * 
     * @param string|null $databaseUrl Firebase Database URL
     * @return Stephino_Rpg_Config_Core
     */
    public function setFirebaseDatabaseUrl($databaseUrl) {
        $this->_firebaseDatabaseUrl = Stephino_Rpg_Utils_Lingo::cleanup($databaseUrl);

        return $this;
    }
    
    /**
     * Set the maximum number of messages a player can send to other players in a 24 hours interval<br/>
     * <b>0</b> means players cannot contact each other
     * 
     * @default 10
     * @ref 0,5000
     * @return int Messages: Anti-spam
     */
    public function getMessageDailyLimit() {
        return null === $this->_messageDailyLimit ? 10 : $this->_messageDailyLimit;
    }
    
    /**
     * Set the "Message Daily Limit" parameter
     * 
     * @param int|null $messageDailyLimit Message Daily Limit
     * @return Stephino_Rpg_Config_Core
     */
    public function setMessageDailyLimit($messageDailyLimit) {
        $this->_messageDailyLimit = (null === $messageDailyLimit ? 10 : intval($messageDailyLimit));
        
        // Minimum and maximum
        if ($this->_messageDailyLimit < 0) {
            $this->_messageDailyLimit = 0;
        }
        if ($this->_messageDailyLimit > 5000) {
            $this->_messageDailyLimit = 5000;
        }
        
        return $this;
    }
    
    /**
     * Set the maximum number of messages a player can have in the inbox
     * 
     * @default 100
     * @ref 10,1000
     * @return int Messages: Inbox limit
     */
    public function getMessageInboxLimit() {
        return null === $this->_messageInboxLimit ? 100 : $this->_messageInboxLimit;
    }
    
    /**
     * Set the "Message Inbox Limits" parameter
     * 
     * @param int|null $messageInboxLimit Message inbox limit
     * @return Stephino_Rpg_Config_Core
     */
    public function setMessageInboxLimit($messageInboxLimit) {
        $this->_messageInboxLimit = (null === $messageInboxLimit ? 100 : intval($messageInboxLimit));
        
        // Minimum and maximum
        if ($this->_messageInboxLimit < 10) {
            $this->_messageInboxLimit = 10;
        }
        if ($this->_messageInboxLimit > 1000) {
            $this->_messageInboxLimit = 1000;
        }
        
        return $this;
    }
    
    /**
     * Messages are automatically deleted after this number of days
     * 
     * @default 30
     * @ref 5,365
     * @return int Messages: Auto-delete
     */
    public function getMessageMaxAge() {
        return null === $this->_messageMaxAge ? 30 : $this->_messageMaxAge;
    }
    
    /**
     * Set the "Message Max Age" parameter
     * 
     * @param int|null $maxAge Message maximum age
     * @return Stephino_Rpg_Config_Core
     */
    public function setMessageMaxAge($maxAge) {
        $this->_messageMaxAge = (null === $maxAge ? 30 : intval($maxAge));
        
        // Minimum and maximum
        if ($this->_messageMaxAge < 5) {
            $this->_messageMaxAge = 5;
        }
        if ($this->_messageMaxAge > 365) {
            $this->_messageMaxAge = 365;
        }
        
        return $this;
    }
    
    /**
     * Points earned for a defeat
     *
     * @section User Score
     * @default -60
     * @ref -5000,5000
     * @return int Battle Points: Defeat
     */
    public function getScoreBattleDefeat() {
        return null === $this->_scoreBattleDefeat ? -60 : $this->_scoreBattleDefeat;
    }

    /**
     * Set the "Score Battle Defeat" parameter
     * 
     * @param int $scoreBattleDefeat Score
     * @return Stephino_Rpg_Config_Core
     */
    public function setScoreBattleDefeat($scoreBattleDefeat) {
        $this->_scoreBattleDefeat = (null === $scoreBattleDefeat ? -60 : intval($scoreBattleDefeat));

        // Minimum and maximum
        if ($this->_scoreBattleDefeat < -5000) {
            $this->_scoreBattleDefeat = -5000;
        }
        if ($this->_scoreBattleDefeat > 5000) {
            $this->_scoreBattleDefeat = 5000;
        }

        return $this;
    }

    /**
     * Points earned for a draw
     *
     * @default 10
     * @ref -5000,5000
     * @return int Battle Points: Draw
     */
    public function getScoreBattleDraw() {
        return null === $this->_scoreBattleDraw ? 10 : $this->_scoreBattleDraw;
    }

    /**
     * Set the "Score Battle Draw" parameter
     * 
     * @param int $scoreBattleDraw Score
     * @return Stephino_Rpg_Config_Core
     */
    public function setScoreBattleDraw($scoreBattleDraw) {
        $this->_scoreBattleDraw = (null === $scoreBattleDraw ? 10 : intval($scoreBattleDraw));

        // Minimum and maximum
        if ($this->_scoreBattleDraw < -5000) {
            $this->_scoreBattleDraw = -5000;
        }
        if ($this->_scoreBattleDraw > 5000) {
            $this->_scoreBattleDraw = 5000;
        }

        return $this;
    }

    /**
     * Points earned for a victory
     *
     * @default 900
     * @ref -5000,5000
     * @return int Battle Points: Victory
     */
    public function getScoreBattleVictory() {
        return null === $this->_scoreBattleVictory ? 900 : $this->_scoreBattleVictory;
    }

    /**
     * Set the "Score Battle Victory" parameter
     * 
     * @param int $scoreBattleVictory Score
     * @return Stephino_Rpg_Config_Core
     */
    public function setScoreBattleVictory($scoreBattleVictory) {
        $this->_scoreBattleVictory = (null === $scoreBattleVictory ? 900 : intval($scoreBattleVictory));

        // Minimum and maximum
        if ($this->_scoreBattleVictory < -5000) {
            $this->_scoreBattleVictory = -5000;
        }
        if ($this->_scoreBattleVictory > 5000) {
            $this->_scoreBattleVictory = 5000;
        }

        return $this;
    }

    /**
     * Points earned for a building upgrade multiplied by building level
     *
     * @default 6
     * @ref -5000,5000
     * @return int Queue Points: Building
     */
    public function getScoreQueueBuilding() {
        return null === $this->_scoreQueueBuilding ? 6 : $this->_scoreQueueBuilding;
    }

    /**
     * Set the "Score Queue Building" parameter
     * 
     * @param int $scoreQueueBuilding Score
     * @return Stephino_Rpg_Config_Core
     */
    public function setScoreQueueBuilding($scoreQueueBuilding) {
        $this->_scoreQueueBuilding = (null === $scoreQueueBuilding ? 6 : intval($scoreQueueBuilding));

        // Minimum and maximum
        if ($this->_scoreQueueBuilding < -5000) {
            $this->_scoreQueueBuilding = -5000;
        }
        if ($this->_scoreQueueBuilding > 5000) {
            $this->_scoreQueueBuilding = 5000;
        }

        return $this;
    }
    
    /**
     * Points earned for recruiting entities multiplied by entity count
     *
     * @default 1
     * @ref -5000,5000
     * @return int Queue Points: Unit/Ship
     */
    public function getScoreQueueEntity() {
        return null === $this->_scoreQueueEntity ? 1 : $this->_scoreQueueEntity;
    }

    /**
     * Set the "Score Queue Entity" parameter
     * 
     * @param int $scoreQueueEntity Score
     * @return Stephino_Rpg_Config_Core
     */
    public function setScoreQueueEntity($scoreQueueEntity) {
        $this->_scoreQueueEntity = (null === $scoreQueueEntity ? 1 : intval($scoreQueueEntity));

        // Minimum and maximum
        if ($this->_scoreQueueEntity < -5000) {
            $this->_scoreQueueEntity = -5000;
        }
        if ($this->_scoreQueueEntity > 5000) {
            $this->_scoreQueueEntity = 5000;
        }

        return $this;
    }

    /**
     * Points earned for finishing a research multiplied by research field level
     *
     * @default 90
     * @ref -5000,5000
     * @return int Queue Points: Research
     */
    public function getScoreQueueResearch() {
        return null === $this->_scoreQueueResearch ? 90 : $this->_scoreQueueResearch;
    }

    /**
     * Set the "Score Queue Research" parameter
     * 
     * @param int $scoreQueueResearch Score
     * @return Stephino_Rpg_Config_Core
     */
    public function setScoreQueueResearch($scoreQueueResearch) {
        $this->_scoreQueueResearch = (null === $scoreQueueResearch ? 90 : intval($scoreQueueResearch));

        // Minimum and maximum
        if ($this->_scoreQueueResearch < -5000) {
            $this->_scoreQueueResearch = -5000;
        }
        if ($this->_scoreQueueResearch > 5000) {
            $this->_scoreQueueResearch = 5000;
        }

        return $this;
    }
    
    /**
     * Get the client ID and secret from <a rel="noreferrer" target="_blank" class="info" href="https://developer.paypal.com/developer/applications/">here</a><br/>
     * Omitting the client ID or secret means that premium packages can only be acquired with <b>{x}</b>
     * 
     * @section Monetization
     * @placeholder core.resourceGoldName,Gold
     * @sensitive true
     * @return string|null PayPal: Client ID
     */
    public function getPayPalClientId() {
        return $this->_payPalClientId;
    }
    
    /**
     * Set the "PayPal Client ID" parameter
     * 
     * @param string|null $clientId PayPal Client ID
     * @return Stephino_Rpg_Config_Core
     */
    public function setPayPalClientId($clientId) {
        $this->_payPalClientId = Stephino_Rpg_Utils_Lingo::cleanup($clientId);
        
        return $this;
    }
    
    /**
     * Client Secret Token
     * 
     * @sensitive true
     * @return string|null PayPal: Client Secret
     */
    public function getPayPalClientSecret() {
       return $this->_payPalClientSecret; 
    }
    
    /**
     * Set the "PayPal Client Secret" parameter
     * 
     * @param string|null $clientSecret PayPal Client Secret
     * @return Stephino_Rpg_Config_Core
     */
    public function setPayPalClientSecret($clientSecret) {
        $this->_payPalClientSecret = Stephino_Rpg_Utils_Lingo::cleanup($clientSecret);
        
        return $this;
    }
    
    /**
     * Set the type of currency used for premium package purchases
     * 
     * @sensitive true
     * @default USD
     * @opt USD,EUR,GBP,AUD,BRL,CAD,CZK,DKK,HKD,HUF,INR,ILS,JPY,MYR,MXN,TWD,NZD,NOK,PHP,PLN,RUB,SGD,SEK,CHF,THB
     * @return string PayPal: Currency
     */
    public function getPayPalCurrency() {
        return null === $this->_payPalCurrency ? Stephino_Rpg_Db_Model_Invoices::CURRENCY_USD : $this->_payPalCurrency;
    }
    
    /**
     * Set the "PayPal Currency" parameter
     * 
     * @param string $currency Currency type
     * @return Stephino_Rpg_Config_Core
     */
    public function setPayPalCurrency($currency) {
        $this->_payPalCurrency = $currency;
        
        // Get the list of allowed currencies
        $allowedValues = Stephino_Rpg_Db_Model_Invoices::CURRENCIES;

        // Validate the currency
        if (!isset($allowedValues[$this->_payPalCurrency])) {
            $this->_payPalCurrency = Stephino_Rpg_Db_Model_Invoices::CURRENCY_USD;
        }
        
        return $this;
    }
    
    /**
     * PayPal Sandbox mode
     * 
     * @sensitive true
     * @return boolean PayPal: Sandbox
     */
    public function getPayPalSandbox() {
        return null === $this->_payPalSandbox ? true : $this->_payPalSandbox;
    }
    
    /**
     * Set the "PayPal Sandbox" parameter
     * 
     * @param boolean $payPalSandbox PayPal Sandbox
     * @return Stephino_Rpg_Config_Core
     */
    public function setPayPalSandbox($payPalSandbox) {
        $this->_payPalSandbox = (boolean) $payPalSandbox;
        
        return $this;
    }
    
    /**
     * In sandbox mode<ul>
     * <li>Build times are <b>constant</b></li>
     * <li>Research times are <b>constant</b></li>
     * <li>Entity recruitment times are <b>constant</b></li>
     * </ul>
     * 
     * @section Admin Tools
     * @return boolean Sandbox Mode
     */
    public function getSandbox() {
        return (boolean) $this->_sandbox;
    }
    
    /**
     * Set the "Sandbox" parameter
     * 
     * @param boolean $sandbox Sandbox
     * @return Stephino_Rpg_Config_Core
     */
    public function setSandbox($sandbox) {
        $this->_sandbox = (boolean) $sandbox;
        
        return $this;
    }
    
    /**
     * Enable the use of the game console for site Admins with <b>Alt+Ctrl+C<b/>
     * 
     * @return boolean Enable console
     */
    public function getConsoleEnabled() {
        return (null === $this->_consoleEnabled ? false : $this->_consoleEnabled);
    }
    
    /**
     * Set the "Console Enabled" parameter
     * 
     * @param boolean $enabled Console Enabled
     * @return Stephino_Rpg_Config_Core
     */
    public function setConsoleEnabled($enabled) {
        $this->_consoleEnabled = (boolean) $enabled;
        
        return $this;
    }

    /**
     * Set the robots aggression level.<br/><br/>
     * <ul>
     *     <li><b>low</b>: don't fight back, don't initiate attacks</li>
     *     <li><b>medium</b>: fight back, don't initiate attacks</li>
     *     <li><b>high</b>: fight back, initiate attacks</li>
     * </ul>
     * 
     * @section Artificial Intelligence
     * @opt low,medium,high
     * @return string Robots aggression
     */
    public function getRobotsAggression() {
        return $this->_robotsAggression;
    }

    /**
     * Set the "Robots Aggression" parameter
     * 
     * @param string|null $aggression Robots Aggression
     * @return Stephino_Rpg_Config_Core
     */
    public function setRobotsAggression($aggression) {
        // Validate the aggression level
        if (!in_array($aggression, array(self::ROBOT_AGG_LOW, self::ROBOT_AGG_MEDIUM, self::ROBOT_AGG_HIGH))) {
            $aggression = self::ROBOT_AGG_HIGH;
        }
        
        // Store it
        $this->_robotsAggression = $aggression;

        return $this;
    }
    
    /**
     * Set the robots fervor. The higher the number, the more actions robots make.
     * 
     * @default 50
     * @ref 5,100
     * @return int Robots fervor
     */
    public function getRobotsFervor() {
        return null === $this->_robotsFervor ? 50 : $this->_robotsFervor;
    }

    /**
     * Set the "Robots Fervor" parameter
     * 
     * @param int $robotsFervor Robots Fervor
     * @return Stephino_Rpg_Config_Core
     */
    public function setRobotsFervor($robotsFervor) {
        $this->_robotsFervor = intval($robotsFervor);

        // Minimum and maximum
        if ($this->_robotsFervor < 5) {
            $this->_robotsFervor = 5;
        }
        if ($this->_robotsFervor > 100) {
            $this->_robotsFervor = 100;
        }
        
        return $this;
    }
    
    /**
     * Set the minium number of hours to wait between attacks
     * 
     * @default 48
     * @ref 1,168
     * @return int Robots timeout
     */
    public function getRobotsTimeout() {
        return null === $this->_robotsTimeout ? 48 : $this->_robotsTimeout;
    }
    
    /**
     * Set the "Robots Timeout" parameter
     * 
     * @param int $robotsTimeout Robots Timeout
     * @return Stephino_Rpg_Config_Core
     */
    public function setRobotsTimeout($robotsTimeout) {
        $this->_robotsTimeout = intval($robotsTimeout);
        
        // Minimum and maximum
        if ($this->_robotsTimeout < 1) {
            $this->_robotsTimeout = 1;
        }
        if ($this->_robotsTimeout > 168) {
            $this->_robotsTimeout = 168;
        }
        
        return $this;
    }
    
    /**
     * Number of random robot time-lapse procedures to run with each request<br/>
     * <b>0</b> means all robots are disabled<br/>
     * <b>Decrease</b> this value to improve performance
     * 
     * @default 5
     * @ref 0,10
     * @return int Robot time-lapses per request
     */
    public function getRobotTimeLapsesPerRequest() {
        return null === $this->_robotTimeLapsesPerRequest ? 5 : $this->_robotTimeLapsesPerRequest;
    }

    /**
     * Set the "Robot Time-Lapses per Request" parameter
     * 
     * @param int $robotTimeLapsesPerRequest Robot Time-Lapses per Request
     * @return Stephino_Rpg_Config_Core
     */
    public function setRobotTimeLapsesPerRequest($robotTimeLapsesPerRequest) {
        $this->_robotTimeLapsesPerRequest = intval($robotTimeLapsesPerRequest);

        // Minimum and maximum
        if ($this->_robotTimeLapsesPerRequest < 0) {
            $this->_robotTimeLapsesPerRequest = 0;
        }
        if ($this->_robotTimeLapsesPerRequest > 10) {
            $this->_robotTimeLapsesPerRequest = 10;
        }
        
        return $this;
    }

    /**
     * Set the minimum number of seconds to pass between time-lapse procedures<br/>
     * A lower number means more frequent resource updates for the player making the game seem closer to real-time<br/>
     * <b>Increase</b> this value to improve performance
     * 
     * @section Performance
     * @default 10
     * @ref 5,120
     * @return int Time-lapse cooldown
     */
    public function getTimeLapseCooldown() {
        return null === $this->_timeLapseCooldown ? 10 : $this->_timeLapseCooldown;
    }

    /**
     * Set the "Time-Lapse Cooldown" parameter
     * 
     * @param int $timeLapseCooldownInSeconds Time-Lapse cooldown
     * @return Stephino_Rpg_Config_Core
     */
    public function setTimeLapseCooldown($timeLapseCooldownInSeconds) {
        $this->_timeLapseCooldown = intval($timeLapseCooldownInSeconds);

        // Minimum and maximum
        if ($this->_timeLapseCooldown < 5) {
            $this->_timeLapseCooldown = 5;
        }
        if ($this->_timeLapseCooldown > 120) {
            $this->_timeLapseCooldown = 120;
        }
        
        return $this;
    }
    
    /**
     * Set the cron interval in <b>minutes</b><br/>
     * This sets the discretization step when calculating resources<br/>
     * A lower value means more accurate resource updates but it may also lead to occasional CPU spikes for some users<br/>
     * <b>Increase</b> this value to improve performance
     * 
     * @default 3
     * @ref 1,60
     * @return int Cron: Interval
     */
    public function getCronInterval() {
        return null === $this->_cronInterval ? 3 : $this->_cronInterval;
    }
    
    /**
     * Set the "Cron interval" parameter
     * 
     * @param int $cronInterval Cron interval
     * @return Stephino_Rpg_Config_Core
     */
    public function setCronInterval($cronInterval) {
        $this->_cronInterval = intval($cronInterval);
        
        // Minimum and maxium
        if ($this->_cronInterval < 1) {
            $this->_cronInterval = 1;
        }
        if ($this->_cronInterval > 60) {
            $this->_cronInterval = 60;
        }
        
        return $this;
    }
    
    /**
     * Set the cron accuracy in <b>hours</b><br/>
     * Resources will be computed in <b>[Cron: Interval]</b> intervals for the first <b>[Cron: Accuracy]</b> hours<br/>
     * After this, the cron interval is increased gradually, degrading accuracy but improving performance<br/>
     * <b>Decrease</b> this value to improve performance
     * 
     * @default 12
     * @ref 1,48
     * @return int Cron: Accuracy
     */
    public function getCronAccuracy() {
        return null === $this->_cronAccuracy ? 12 : $this->_cronAccuracy;
    }
    
    /**
     * Set the "Cron accuracy" parameter
     * 
     * @param int $cronAccuracy Cron Accuracy
     * @return Stephino_Rpg_Config_Core
     */
    public function setCronAccuracy($cronAccuracy) {
        $this->_cronAccuracy = intval($cronAccuracy);
        
        // Minimum and maxium
        if ($this->_cronAccuracy < 1) {
            $this->_cronAccuracy = 1;
        }
        if ($this->_cronAccuracy > 48) {
            $this->_cronAccuracy = 48;
        }
        
        return $this;
    }
    
    /**
     * Set the cron maximum age in <b>days</b><br/>
     * Ignore all events (resources, queues, convoys etc.) older than this number of days<br>
     * <b>Decrease</b> this value to improve performance
     * 
     * @default 90
     * @ref 1,300
     * @return int Cron: Max. Age
     */
    public function getCronMaxAge() {
        return null === $this->_cronMaxAge ? 90 : $this->_cronMaxAge;
    }
    
    /**
     * Set the "Cron max age" parameter
     * 
     * @param int $cronMaxAge Cron Max Age
     * @return Stephino_Rpg_Config_Core
     */
    public function setCronMaxAge($cronMaxAge) {
        $this->_cronMaxAge = intval($cronMaxAge);
        
        // Minimum and maxium
        if ($this->_cronMaxAge < 1) {
            $this->_cronMaxAge = 1;
        }
        if ($this->_cronMaxAge > 300) {
            $this->_cronMaxAge = 300;
        }
        
        return $this;
    }
    
    /**
     * Number of islands created before the first player joins the game<br/>
     * <span class="info">Used after a game restart (Extra &gt; Restart)</span>
     * 
     * @section Game Initialization
     * @default 10
     * @ref 0,5000
     * @return int Islands at start
     */
    public function getInitialIslandsCount() {
        return null === $this->_initialIslandsCount ? 10 : $this->_initialIslandsCount;
    }

    /**
     * Set the "Init Islands Initial Count" parameter
     * 
     * @param int $initIslandsInitialCount Init Islands Initial Count
     * @return Stephino_Rpg_Config_Core
     */
    public function setInitialIslandsCount($initIslandsInitialCount) {
        $this->_initialIslandsCount = (null === $initIslandsInitialCount ? 10 : intval($initIslandsInitialCount));
        
        // Minimum and maximum
        if ($this->_initialIslandsCount < 0) {
            $this->_initialIslandsCount = 0;
        }
        if ($this->_initialIslandsCount > 5000) {
            $this->_initialIslandsCount = 5000;
        }
        
        return $this;
    }

    /**
     * Number of islands created with each new player
     * 
     * @default 2
     * @ref 1,100
     * @return int New islands per player
     */
    public function getInitialIslandsPerUser() {
        return null === $this->_initialIslandsPerUser ? 2 : $this->_initialIslandsPerUser;
    }

    /**
     * Set the "Init Islands Spawn Per User" parameter
     * 
     * @param int $initIslandsSpawnPerUser Init Islands Spawn Per User
     * @return Stephino_Rpg_Config_Core
     */
    public function setInitialIslandsPerUser($initIslandsSpawnPerUser) {
        $this->_initialIslandsPerUser = intval($initIslandsSpawnPerUser);

        // Minimum and maximum
        if ($this->_initialIslandsPerUser < 1) {
            $this->_initialIslandsPerUser = 1;
        }
        if ($this->_initialIslandsPerUser > 100) {
            $this->_initialIslandsPerUser = 100;
        }
        
        return $this;
    }
    
    /**
     * Number of robots spawned with each new player
     * 
     * @default 3
     * @ref 0,10
     * @return int Robots per player
     */
    public function getInitialRobotsPerUser() {
        return null === $this->_initialRobotsPerUser ? 3 : $this->_initialRobotsPerUser;
    }

    /**
     * Set the "Initial Robots Per User" parameter
     * 
     * @param int $initialRobotsPerUser Initial Robots per user
     * @return Stephino_Rpg_Config_Core
     */
    public function setInitialRobotsPerUser($initialRobotsPerUser) {
        $this->_initialRobotsPerUser = intval($initialRobotsPerUser);

        // Minimum and maximum
        if ($this->_initialRobotsPerUser < 0) {
            $this->_initialRobotsPerUser = 0;
        }
        if ($this->_initialRobotsPerUser > 10) {
            $this->_initialRobotsPerUser = 10;
        }
        
        return $this;
    }

    /**
     * The initial {x} alloted to a new player
     * 
     * @placeholder core.resourceGoldName,Gold
     * @default 0
     * @ref 0
     * @return int {x} at start
     */
    public function getInitialUserResourceGold() {
        return null === $this->_initialUserResourceGold ? 0 : $this->_initialUserResourceGold;
    }

    /**
     * Set the "Init User Resource Gold" parameter
     * 
     * @param int $initUserResourceGold Init User Resource Gold
     * @return Stephino_Rpg_Config_Core
     */
    public function setInitialUserResourceGold($initUserResourceGold) {
        $this->_initialUserResourceGold = intval($initUserResourceGold);

        // Minimum
        if ($this->_initialUserResourceGold < 0) {
            $this->_initialUserResourceGold = 0;
        }
        
        return $this;
    }

    /**
     * The initial {x} alloted a new player
     * 
     * @placeholder core.resourceGemName,Gems
     * @default 0
     * @ref 0
     * @return int {x} at start
     */
    public function getInitialUserResourceGem() {
        return null === $this->_initialUserResourceGem ? 0 : $this->_initialUserResourceGem;
    }

    /**
     * Set the "Init User Resource Gem" parameter
     * 
     * @param int $initUserResourceGem Init User Resource Gem
     * @return Stephino_Rpg_Config_Core
     */
    public function setInitialUserResourceGem($initUserResourceGem) {
        $this->_initialUserResourceGem = intval($initUserResourceGem);

        // Minimum
        if ($this->_initialUserResourceGem < 0) {
            $this->_initialUserResourceGem = 0;
        }
        
        return $this;
    }
    
    /**
     * The initial <b>{x}</b> alloted to a new player
     * 
     * @placeholder core.resourceResearchName,Research points
     * @default 0
     * @ref 0
     * @return int {x} at start
     */
    public function getInitialUserResourceResearch() {
        return null === $this->_initialUserResourceResearch ? 0 : $this->_initialUserResourceResearch;
    }

    /**
     * Set the "Init User Resource Research" parameter
     * 
     * @param int|null $initUserResourceResearch Init User Resource Research
     * @return Stephino_Rpg_Config_Core
     */
    public function setInitialUserResourceResearch($initUserResourceResearch) {
        $this->_initialUserResourceResearch = intval($initUserResourceResearch);

        // Minimum
        if ($this->_initialUserResourceResearch < 0) {
            $this->_initialUserResourceResearch = 0;
        }
        
        return $this;
    }
    
    /**
     * Buildings automatically built with each new city
     * 
     * @return Stephino_Rpg_Config_Building[]|null First buildings
     */
    public function getInitialCityBuildings() {
        // Prepare the result
        $result = array();
        
        // A valid array
        if (is_array($this->_initialCityBuildings)) {
            // Go through the IDs
            foreach ($this->_initialCityBuildings as $buildingID) {
                /* @var $building Stephino_Rpg_Config_Building */
                $building = Stephino_Rpg_Config::get()->buildings()->getById($buildingID);

                // Valid result
                if (null !== $building) {
                    $result[$buildingID] = $building;
                }
            }
        }
        
        // Final validation
        return count($result) ? $result : null;
    }

    /**
     * Set the "Init City First Buildings" parameter
     * 
     * @param int[]|null $ids Stephino_Rpg_Config_Building IDs
     * @return Stephino_Rpg_Config_Core
     */
    public function setInitialCityBuildings($ids) {
        if (!is_array($ids)) {
            $this->_initialCityBuildings = null;
        } else {
            // Convert to integers
            $ids = array_filter(array_map('intval', $ids));
            
            // Store the data
            $this->_initialCityBuildings = (!count($ids) ? null : $ids);
        }

        return $this;
    }

    /**
     * Set the number of seconds it takes to travel between two neighboring cities<br/>
     * The distance between islands is 10 times larger than that between cities<br/>
     * This time is reduced in half if all units can be assigned to ships (see Ships / Capacity)<br/>
     * Set to <b>0</b> to make travel instantaneous (not recommended)
     * 
     * @section Game Rules
     * @default 10
     * @ref 0,86400
     * @return int Travel time
     */
    public function getTravelTime() {
        return null === $this->_travelTime ? 10 : $this->_travelTime;
    }
    
    /**
     * Set the "Travel Time" parameter
     * 
     * @param int $travelTimeInSeconds Travel Time
     * @return Stephino_Rpg_Config_Core
     */
    public function setTravelTime($travelTimeInSeconds) {
        $this->_travelTime = (null === $travelTimeInSeconds ? 10 : intval($travelTimeInSeconds));
        
        // Minimum and maximum
        if ($this->_travelTime < 0) {
            $this->_travelTime = 0;
        }
        if ($this->_travelTime > 86400) {
            $this->_travelTime = 86400;
        }
        
        return $this;
    }
    
    /**
     * You cannot attack cities that are smaller than yours by this much
     * 
     * @default 5
     * @ref 1,25
     * @return int Noob: City Levels Difference
     */
    public function getNoobLevels() {
        return null === $this->_noobLevels ? 5 : $this->_noobLevels;
    }
    
    /**
     * Set the "Noob Levels" parameter
     * 
     * @param int $noobAge Noob Levels
     * @return Stephino_Rpg_Config_Core
     */
    public function setNoobLevels($noobLevels) {
        $this->_noobLevels = (null === $noobLevels ? 5 : intval($noobLevels));
        
        // Minimum and maximum
        if ($this->_noobLevels < 1) {
            $this->_noobLevels = 1;
        }
        if ($this->_noobLevels > 25) {
            $this->_noobLevels = 25;
        }
        
        return $this;
    }
    
    /**
     * You cannot attack players that are younger than this number of days
     * 
     * @default 7
     * @ref 1,90
     * @return int Noob: Player Account Age
     */
    public function getNoobAge() {
        return null === $this->_noobAge ? 7 : $this->_noobAge;
    }
    
    /**
     * Set the "Noob Age" parameter
     * 
     * @param int $noobAge Noob Age
     * @return Stephino_Rpg_Config_Core
     */
    public function setNoobAge($noobAge) {
        $this->_noobAge = (null === $noobAge ? 7 : intval($noobAge));
        
        // Minimum and maximum
        if ($this->_noobAge < 1) {
            $this->_noobAge = 1;
        }
        if ($this->_noobAge > 90) {
            $this->_noobAge = 90;
        }
        
        return $this;
    }
    
    /**
     * Set the price of {x} in {x2}<br/>
     * Set to <b>0</b> to disable trading<br/>
     * Does not require the <b>Market</b>
     * 
     * @placeholder core.resourceGemName,Gems,core.resourceGoldName,Gold
     * @default 0
     * @ref 0
     * @return float {x}-to-{x2} ratio
     */
    public function getGemToGoldRatio() {
        return (null === $this->_gemToGoldRatio ? 0 : $this->_gemToGoldRatio);
    }
    
    /**
     * Set the "Gem To Gold" parameter
     * 
     * @param float $gemToGoldRatio Gem To Gold ratio
     * @return Stephino_Rpg_Config_Core
     */
    public function setGemToGoldRatio($gemToGoldRatio) {
        $this->_gemToGoldRatio = floatval($gemToGoldRatio);
        
        if ($this->_gemToGoldRatio < 0) {
            $this->_gemToGoldRatio = 0;
        }
        
        return $this;
    }
    
    /**
     * Set the price of {x} in {x2}<br/>
     * Set to <b>0</b> to disable trading<br/>
     * Does not require the <b>Market</b>
     * 
     * @placeholder core.resourceGemName,Gems,core.resourceResearchName,Research points
     * @default 0
     * @ref 0
     * @return float {x}-to-{x2} ratio
     */
    public function getGemToResearchRatio() {
        return (null === $this->_gemToResearchRatio ? 0 : $this->_gemToResearchRatio);
    }
    
    /**
     * Set the "Gem To Research points" parameter
     * 
     * @param float $gemToResearchRatio Gem To Research points ratio
     * @return Stephino_Rpg_Config_Core
     */
    public function setGemToResearchRatio($gemToResearchRatio) {
        $this->_gemToResearchRatio = floatval($gemToResearchRatio);
        
        if ($this->_gemToResearchRatio < 0) {
            $this->_gemToResearchRatio = 0;
        }
        
        return $this;
    }

    /**
     * Enable the exchange of resources and {x}
     * 
     * @placeholder core.resourceGoldName,Gold
     * @return boolean Market
     */
    public function getMarketEnabled() {
        return (null === $this->_marketEnabled ? false : $this->_marketEnabled);
    }
    
    /**
     * Set the "Market Enabled" parameter
     * 
     * @param boolean $marketEnabled Enable the Market
     * @return Stephino_Rpg_Config_Core
     */
    public function setMarketEnabled($marketEnabled) {
        $this->_marketEnabled = (boolean) $marketEnabled;
        
        return $this;
    }
    
    /**
     * A building where players can trade resources for {x}<br/>
     * Not selecting a building means the market is disabled
     * 
     * @placeholder core.resourceGoldName,Gold
     * @depends marketEnabled
     * @return Stephino_Rpg_Config_Building|null Market building
     */
    public function getMarketBuilding() {
        return Stephino_Rpg_Config::get()->buildings()->getById($this->_marketBuildingId);
    }

    /**
     * Set the "Market Building" parameter
     * 
     * @param int|null $marketBuildingId Building Config ID
     */
    public function setMarketBuilding($marketBuildingId) {
        $this->_marketBuildingId = (null === $marketBuildingId ? null : intval($marketBuildingId));

        return $this;
    }
    
    /**
     * Market resource ratios factor<br/>
     * <br/>
     * <span class="info">
     *     The exchange rates change with the <b>market building level</b>
     * </span>
     * 
     * @depends marketEnabled
     * @return poly|null Market: polynomial
     */
    public function getMarketPolynomial() {
        return $this->_marketPolynomial;
    }
    
    /**
     * Set the "Market Polynomial" parameter
     * 
     * @param string|null Market Polynomial
     * @return Stephino_Rpg_Config_Core
     */
    public function setMarketPolynomial($marketPolynomial) {
        $this->_marketPolynomial = $this->_sanitizePoly($marketPolynomial);
        
        return $this;
    }
    
    /**
     * Set an exchange rate difference so that buying resources is more expensive than selling them
     * 
     * @depends marketEnabled
     * @default 10
     * @ref 0,100
     * @return int Market: Gain (%)
     */
    public function getMarketGain() {
        return (null === $this->_marketGain ? 10 : $this->_marketGain);
    }
    
    /**
     * Set the "Market Gain" parameter
     * 
     * @param int $marketGain Market Gain
     * @return Stephino_Rpg_Config_Core
     */
    public function setMarketGain($marketGain) {
        $this->_marketGain = intval($marketGain);
        
        // Limits
        if ($this->_marketGain < 0) {
            $this->_marketGain = 0;
        }
        if ($this->_marketGain > 100) {
            $this->_marketGain = 100;
        }
        
        return $this;
    }
    
    /**
     * Market: {x} to {x2} ratio<br/>
     * Set to <b>0</b> to disable trading
     * 
     * @depends marketEnabled
     * @placeholder core.resourceAlphaName,Alpha,core.resourceGoldName,Gold
     * @default 0
     * @ref 0
     * @return float Market: {x}-to-{x2} ratio
     */
    public function getMarketResourceAlpha() {
        return (null === $this->_marketResourceAlpha ? 0 : $this->_marketResourceAlpha);
    }

    /**
     * Set the "Market ResourceAlpha" parameter
     * 
     * @param float $marketResourceAlpha ResourceAlpha to Gold ratio
     * @return Stephino_Rpg_Config_Core
     */
    public function setMarketResourceAlpha($marketResourceAlpha) {
        $this->_marketResourceAlpha = floatval($marketResourceAlpha);

        if ($this->_marketResourceAlpha < 0) {
            $this->_marketResourceAlpha = 0;
        }

        return $this;
    }

    /**
     * Market: {x} to {x2} ratio<br/>
     * Set to <b>0</b> to disable trading
     * 
     * @depends marketEnabled
     * @placeholder core.resourceBetaName,Beta,core.resourceGoldName,Gold
     * @default 0
     * @ref 0
     * @return float Market: {x}-to-{x2} ratio
     */
    public function getMarketResourceBeta() {
        return (null === $this->_marketResourceBeta ? 0 : $this->_marketResourceBeta);
    }

    /**
     * Set the "Market ResourceBeta" parameter
     * 
     * @param float $marketResourceBeta ResourceBeta to Gold ratio
     * @return Stephino_Rpg_Config_Core
     */
    public function setMarketResourceBeta($marketResourceBeta) {
        $this->_marketResourceBeta = floatval($marketResourceBeta);

        if ($this->_marketResourceBeta < 0) {
            $this->_marketResourceBeta = 0;
        }

        return $this;
    }

    /**
     * Market: {x} to {x2} ratio<br/>
     * Set to <b>0</b> to disable trading
     * 
     * @depends marketEnabled
     * @placeholder core.resourceGammaName,Gamma,core.resourceGoldName,Gold
     * @default 0
     * @ref 0
     * @return float Market: {x}-to-{x2} ratio
     * 
     */
    public function getMarketResourceGamma() {
        return (null === $this->_marketResourceGamma ? 0 : $this->_marketResourceGamma);
    }

    /**
     * Set the "Market ResourceGamma" parameter
     * 
     * @param float $marketResourceGamma ResourceGamma to Gold ratio
     * @return Stephino_Rpg_Config_Core
     */
    public function setMarketResourceGamma($marketResourceGamma) {
        $this->_marketResourceGamma = floatval($marketResourceGamma);

        if ($this->_marketResourceGamma < 0) {
            $this->_marketResourceGamma = 0;
        }

        return $this;
    }

    /**
     * Market: {x} to {x2} ratio<br/>
     * Set to <b>0</b> to disable trading
     * 
     * @depends marketEnabled
     * @placeholder core.resourceExtra1Name,Extra resource 1,core.resourceGoldName,Gold
     * @default 0
     * @ref 0
     * @return float Market: {x}-to-{x2} ratio
     * 
     */
    public function getMarketResourceExtra1() {
        return (null === $this->_marketResourceExtra1 ? 0 : $this->_marketResourceExtra1);
    }

    /**
     * Set the "Market ResourceExtra1" parameter
     * 
     * @param float $marketResourceExtra1 ResourceExtra1 to Gold ratio
     * @return Stephino_Rpg_Config_Core
     */
    public function setMarketResourceExtra1($marketResourceExtra1) {
        $this->_marketResourceExtra1 = floatval($marketResourceExtra1);

        if ($this->_marketResourceExtra1 < 0) {
            $this->_marketResourceExtra1 = 0;
        }

        return $this;
    }

    /**
     * Market: {x} to {x2} ratio<br/>
     * Set to <b>0</b> to disable trading
     * 
     * @depends marketEnabled
     * @placeholder core.resourceExtra2Name,Extra resource 2,core.resourceGoldName,Gold
     * @default 0
     * @ref 0
     * @return float Market: {x}-to-{x2} ratio
     * 
     */
    public function getMarketResourceExtra2() {
        return (null === $this->_marketResourceExtra2 ? 0 : $this->_marketResourceExtra2);
    }

    /**
     * Set the "Market ResourceExtra2" parameter
     * 
     * @param float $marketResourceExtra2 ResourceExtra2 to Gold ratio
     * @return Stephino_Rpg_Config_Core
     */
    public function setMarketResourceExtra2($marketResourceExtra2) {
        $this->_marketResourceExtra2 = floatval($marketResourceExtra2);

        if ($this->_marketResourceExtra2 < 0) {
            $this->_marketResourceExtra2 = 0;
        }

        return $this;
    }

    /**
     * Main building in a city<br/>
     * The <b>city level</b> is <b>inherited</b> from this building<br/>
     * Not selecting a main building means your cities never grow
     * 
     * @return Stephino_Rpg_Config_Building|null Main building
     */
    public function getMainBuilding() {
        return Stephino_Rpg_Config::get()->buildings()->getById($this->_mainBuildingId);
    }

    /**
     * Set the "Main Building" parameter
     * 
     * @param int|null $mainBuildingId Building Config ID
     */
    public function setMainBuilding($mainBuildingId) {
        $this->_mainBuildingId = (null === $mainBuildingId ? null : intval($mainBuildingId));

        return $this;
    }

    /**
     * <b>{x}</b> bonus for metropolis
     * 
     * @placeholder core.metricSatisfactionName,Satisfaction
     * @default 10
     * @ref 0,100
     * @return int Metropolis bonus (%)
     */
    public function getCapitalSatisfactionBonus() {
        return null === $this->_capitalSatisfactionBonus ? 10 : $this->_capitalSatisfactionBonus;
    }
    
    /**
     * Set the "Metropolis Satisfaction Bonus" parameter
     * 
     * @param int $capitalSatisfactionBonus Metropolis satisfaction bonus
     * @return Stephino_Rpg_Config_Core
     */
    public function setCapitalSatisfactionBonus($capitalSatisfactionBonus) {
        $this->_capitalSatisfactionBonus = intval($capitalSatisfactionBonus);
        
        // Min and max
        if ($this->_capitalSatisfactionBonus < 0) {
            $this->_capitalSatisfactionBonus = 0;
        }
        if ($this->_capitalSatisfactionBonus > 100) {
            $this->_capitalSatisfactionBonus = 100;
        }
        
        return $this;
    }
    
    /**
     * Maximum number of simultaneous <b>building</b> upgrades in a city
     * 
     * @default 3
     * @ref 1
     * @return int Max. Queue: Buildings
     */
    public function getMaxQueueBuildings() {
        return (null === $this->_maxQueueBuildings ? 3 : $this->_maxQueueBuildings);
    }

    /**
     * Set the "Max Queue Buildings" parameter
     * 
     * @param int $maxQueueBuildings Max Queue Buildings
     * @return Stephino_Rpg_Config_Core
     */
    public function setMaxQueueBuildings($maxQueueBuildings) {
        $this->_maxQueueBuildings = intval($maxQueueBuildings);
        
        // Minimum
        if ($this->_maxQueueBuildings < 1) {
            $this->_maxQueueBuildings = 1;
        }
        
        return $this;
    }
    
    /**
     * Maximum number of simultaneous <b>entity</b> recruitment jobs in a city
     * 
     * @default 3
     * @ref 1
     * @return int Max. Queue: Units and Ships
     */
    public function getMaxQueueEntities() {
        return (null === $this->_maxQueueEntities ? 3 : $this->_maxQueueEntities);
    }

    /**
     * Set the "Max Queue Units" parameter
     * 
     * @param int $maxQueueEntities Max Queue Entities
     * @return Stephino_Rpg_Config_Core
     */
    public function setMaxQueueEntities($maxQueueEntities) {
        $this->_maxQueueEntities = intval($maxQueueEntities);

        // Minimum
        if ($this->_maxQueueEntities < 1) {
            $this->_maxQueueEntities = 1;
        }
        
        return $this;
    }

    /**
     * Maximum number of simultaneous <b>research field</b> activities in all cities
     * 
     * @default 3
     * @ref 1
     * @return int Max. Queue: Research fields
     */
    public function getMaxQueueResearchFields() {
        return (null === $this->_maxQueueResearchFields ? 3 : $this->_maxQueueResearchFields);
    }

    /**
     * Set the "Max Queue Research Fields" parameter
     * 
     * @param int $maxQueueResearchFields Max Queue Research Fields
     * @return Stephino_Rpg_Config_Core
     */
    public function setMaxQueueResearchFields($maxQueueResearchFields) {
        $this->_maxQueueResearchFields = intval($maxQueueResearchFields);

        // Minimum
        if ($this->_maxQueueResearchFields < 1) {
            $this->_maxQueueResearchFields = 1;
        }
        
        return $this;
    }
}

/*EOF*/