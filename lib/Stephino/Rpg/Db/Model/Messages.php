<?php
/**
 * Stephino_Rpg_Db_Model_Messages
 * 
 * @title     Model:Messages
 * @desc      Messages Model
 * @copyright (c) 2021, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Db_Model_Messages extends Stephino_Rpg_Db_Model {

    // Notification templates
    const TEMPLATE_COMMON_INVALID_DATA                 = 'common/invalid-data';
    const TEMPLATE_NOTIF_PTF_AUTHOR_REWARD             = 'notif-ptf-author-reward';
    const TEMPLATE_NOTIF_PTF_REVIEW                    = 'notif-ptf-review';
    const TEMPLATE_NOTIF_PREMIUM_MODIFIER              = 'notif-premium-modifier';
    const TEMPLATE_NOTIF_PREMIUM_PACKAGE               = 'notif-premium-package';
    const TEMPLATE_NOTIF_TUTORIAL_REWARDS              = 'notif-tutorial-rewards';
    const TEMPLATE_NOTIF_USER_GAME_MASTER              = 'notif-user-game-master';
    const TEMPLATE_NOTIF_USER_CONTACT                  = 'notif-user-contact';
    const TEMPLATE_TIMELAPSE_LIST_ENTITIES             = 'timelapse/list-entities';
    const TEMPLATE_TIMELAPSE_LIST_RESOURCES            = 'timelapse/list-resources';
    const TEMPLATE_TIMELAPSE_NOTIF_RESEARCH_DONE       = 'timelapse/notif-research-done';
    const TEMPLATE_TIMELAPSE_NOTIF_RESEARCH_UNLOCK     = 'timelapse/notif-research-unlock';
    const TEMPLATE_TIMELAPSE_NOTIF_ECONOMY_TRANSPORT   = 'timelapse/notif-economy-transport';
    const TEMPLATE_TIMELAPSE_NOTIF_ECONOMY_BUILDING    = 'timelapse/notif-economy-building';
    const TEMPLATE_TIMELAPSE_NOTIF_ECONOMY_ENTITY      = 'timelapse/notif-economy-entity';
    const TEMPLATE_TIMELAPSE_NOTIF_MILITARY_ATTACK     = 'timelapse/notif-military-attack';
    const TEMPLATE_TIMELAPSE_NOTIF_MILITARY_SPY        = 'timelapse/notif-military-spy';
    const TEMPLATE_TIMELAPSE_NOTIF_MILITARY_RETURN     = 'timelapse/notif-military-return';
    const TEMPLATE_TIMELAPSE_NOTIF_DIPLOMACY_COLONY    = 'timelapse/notif-diplomacy-colony';
    const TEMPLATE_TIMELAPSE_NOTIF_DIPLOMACY_PREMIUM   = 'timelapse/notif-diplomacy-premium';
    const TEMPLATE_TIMELAPSE_NOTIF_DIPLOMACY_DISCOVERY = 'timelapse/notif-diplomacy-discovery';
    
    /**
     * Available notification templates
     * 
     * @var string[]
     */
    protected $_notifTemplates = array();
    
    /**
     * Template helper: Get the notification template path
     * 
     * @param string $templateName Time-lapse template name
     * @return string|null
     */
    public static function getTemplatePath($templateName) {
        if (!is_file($templatePath = STEPHINO_RPG_ROOT . '/' . Stephino_Rpg::FOLDER_UI_TPL . '/notif/' . $templateName . '.php')) {
            throw new Exception('Notification template "' . $templateName . '" not found');
        }
        return $templatePath;
    }
    
    /**
     * Template helper: Validate the notification data; on error output the "common/invalid-data" template
     * 
     * @param array $notifData           Notification data array
     * @param int   $notifNumOfArguments Expected number of arguments; default <b>1</b>
     * @return boolean
     */
    public static function isValidNotifData($notifData, $notifNumOfArguments = 1) {
        $result = true;
        
        if (!is_array($notifData) || count($notifData) < $notifNumOfArguments) {
            require self::getTemplatePath(self::TEMPLATE_COMMON_INVALID_DATA);
            
            $result = false;
        }
        
        return $result;
    }
    
    /**
     * Messages
     * 
     * @param \Stephino_Rpg_Db $dbObject
     */
    public function __construct(\Stephino_Rpg_Db $dbObject) {
        parent::__construct($dbObject);
        
        // Store the available notification templates
        foreach ((new ReflectionClass($this))->getConstants() as $constantName => $constantValue) {
            if (preg_match('%^TEMPLATE_%', $constantName)) {
                $this->_notifTemplates[] = $constantValue;
            }
        }
    }
    
    /**
     * Messages Model Name
     */
    const NAME = 'messages';
    
    /**
     * Subject for Platformer author reward
     * 
     * @return string
     */
    protected function _subjectNotifPtfAuthorReward() {
        return __('Royalties', 'stephino-rpg');
    }
    
    /**
     * Subject for Platformer reviews
     * 
     * @param string $ptfReview Platformer review status
     * @return string
     */
    protected function _subjectNotifPtfReview($ptfReview = null) {
        return Stephino_Rpg_Db_Table_Ptfs::PTF_REVIEW_PENDING == $ptfReview
            ? __('Your game is under review', 'stephino-rpg')
            : __('Your game was reviewed', 'stephino-rpg');
    }
    
    /**
     * Subject for premium modifier activation
     * 
     * @param int $premiumModifierConfigId Premium modifier configuration ID
     * @return string
     */
    protected function _subjectNotifPremiumModifier($premiumModifierConfigId = 0) {
        // Get the configuration object
        $premiumModifierConfig = Stephino_Rpg_Config::get()
            ->premiumModifiers()
            ->getById($premiumModifierConfigId);
        
        // Get the configuration name
        $premiumModifierName = null !== $premiumModifierConfig 
            ? $premiumModifierConfig->getName(true)
            : __('Unknown premium modifier', 'stephino-rpg');
        
        return $premiumModifierName . ': ' . __('activated', 'stephino-rpg');
    }
    
    /**
     * Subject for premium package activation
     * 
     * @param int $premiumPackageConfigId Premium package configuration ID
     * @return string
     */
    protected function _subjectNotifPremiumPackage($premiumPackageConfigId = 0) {
        // Get the configuration object
        $premiumPackageConfig = Stephino_Rpg_Config::get()
            ->premiumPackages()
            ->getById($premiumPackageConfigId);
        
        // Get the configuration name
        $premiumPackageName = null !== $premiumPackageConfig
            ? $premiumPackageConfig->getName(true)
            : __('Unknown premium package', 'stephino-rpg');
        
        return $premiumPackageName . ': ' . __('acquired', 'stephino-rpg');
    }
    
    /**
     * Subject for tutorial rewards
     * 
     * @return string
     */
    protected function _subjectNotifTutorialRewards() {
        return __('Tutorial reward', 'stephino-rpg');
    }
    
    /**
     * Subject for Game Master toggling
     * 
     * @return string
     */
    protected function _subjectNotifUserGameMaster($gameMaster = false) {
        return $gameMaster
            ? __('Promoted', 'stephino-rpg')
            : __('Demoted', 'stephino-rpg');
    }
    
    /**
     * Subject for timelapse: research field completed
     * 
     * @param int $researchFieldConfigId
     * @return string
     */
    protected function _subjectTimelapseNotifResearchDone($researchFieldConfigId = 0) {
        // Get the configuration object
        $reasearchFieldConfig = Stephino_Rpg_Config::get()
            ->researchFields()
            ->getById($researchFieldConfigId);
        
        // Get the configuration name
        $researchFieldName = null !== $reasearchFieldConfig
            ? $reasearchFieldConfig->getName(true)
            : __('Unknown', 'stephino-rpg');
        
        return $researchFieldName . ': ' . __('research complete', 'stephino-rpg');
    }
    
    /**
     * Subject for timelapse: item unlocked
     * 
     * @param array[] $unlockObjects
     */
    protected function _subjectTimelapseNotifResearchUnlock($unlockObjects = array()) {
        return _n('New item unlocked', 'New items unlocked', count($unlockObjects), 'stephino-rpg');
    }
    
    /**
     * Subject for timelapse: economy: transport
     * 
     * @param boolean $transportReturned
     * @return string
     */
    protected function _subjectTimelapseNotifEconomyTransport($transportReturned = true) {
        return $transportReturned
            ? __('Transporter returned', 'stephino-rpg')
            : __('Goods delivered', 'stephino-rpg');
    }
    
    /**
     * Subject for timelapse: economy: building
     * 
     * @param int $buildingConfigId
     * @return string
     */
    protected function _subjectTimelapseNotifEconomyBuilding($buildingConfigId = 0) {
        // Get the configuration object
        $buildingConfig = Stephino_Rpg_Config::get()
            ->buildings()
            ->getById($buildingConfigId);
        
        // Get the configuration name
        $buildingName = null !== $buildingConfig
            ? $buildingConfig->getName(true)
            : __('Unknown', 'stephino-rpg');
        
        return $buildingName . ': ' . __('upgraded', 'stephino-rpg');
    }
    
    /**
     * Subject for timelapse: economy: entity
     * 
     * @param int    $entityConfigId
     * @param string $entityType
     * @return string
     */
    protected function _subjectTimelapseNotifEconomyEntity($entityConfigId = 0, $entityType = null) {
        // Get the configuration object
        $entityConfig = Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_UNIT === $entityType
            ? Stephino_Rpg_Config::get()->units()->getById($entityConfigId)
            : Stephino_Rpg_Config::get()->ships()->getById($entityConfigId);
        
        // Get the configuration name
        $entityName = null !== $entityConfig
            ? $entityConfig->getName(true)
            : __('Unknown', 'stephino-rpg');
        
        return $entityName . ': ' . (
            Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_UNIT === $entityType
                ? __('recruited', 'stephino-rpg')
                : __('built', 'stephino-rpg')
        );
    }
    
    /**
     * Subject for timelapse: military: attack
     * 
     * @param boolean $attacker
     * @param string  $attackStatus
     * @return string
     */
    protected function _subjectTimelapseNotifMilitaryAttack($attacker = false, $attackStatus = null) {
        switch ($attackStatus) {
            case Stephino_Rpg_TimeLapse_Convoys::ATTACK_DEFEAT_RETREAT: 
                $result = __('Retreat', 'stephino-rpg');
                break;

            case Stephino_Rpg_TimeLapse_Convoys::ATTACK_DEFEAT_CRUSHING: 
                $result = __('Crushing Defeat', 'stephino-rpg');
                break;

            case Stephino_Rpg_TimeLapse_Convoys::ATTACK_VICTORY_CRUSHING: 
                $result = __('Crushing Victory', 'stephino-rpg');
                break;

            case Stephino_Rpg_TimeLapse_Convoys::ATTACK_DEFEAT_HEROIC: 
                $result = __('Heroic Defeat', 'stephino-rpg');
                break;

            case Stephino_Rpg_TimeLapse_Convoys::ATTACK_VICTORY_HEROIC: 
                $result = __('Heroic Victory', 'stephino-rpg');
                break;

            case Stephino_Rpg_TimeLapse_Convoys::ATTACK_DEFEAT_BITTER: 
                $result = __('Bitter Defeat', 'stephino-rpg');
                break;

            case Stephino_Rpg_TimeLapse_Convoys::ATTACK_VICTORY_BITTER: 
                $result = __('Bitter Victory', 'stephino-rpg');
                break;
            
            default: 
                $result = __('Unknown', 'stephino-rpg');
        }
        
        return $result;
    }
    
    /**
     * Subject for timelapse: military: spy
     * 
     * @param boolean    $transportReturned
     * @param array|null $payloadArray
     * @param int        $fromCityId
     * @param int        $toCityId
     * @param boolean    $currentUser
     * @return string
     */
    protected function _subjectTimelapseNotifMilitarySpy($transportReturned = false, $payloadArray = array(), $fromCityId = 0, $toCityId = 0, $currentUser = false) {
        if (!$transportReturned) {
            if ($currentUser && !is_array($payloadArray)) {
                $result = __('We caught a spy', 'stephino-rpg');
            } else {
                $result = is_array($payloadArray) 
                    ? __('Spy mission successful', 'stephino-rpg') 
                    : __('Spy mission failed', 'stephino-rpg');
            }
        } else {
            $result = __('Our spy has returned', 'stephino-rpg');
        }
        
        return $result;
    }
    
    /**
     * Subject for timelapse: military: return
     * 
     * @return string
     */
    protected function _subjectTimelapseNotifMilitaryReturn() {
        return __('Our troops have returned', 'stephino-rpg');
    }
    
    /**
     * Subject for timelapse: diplomacy: colony
     * 
     * @return string
     */
    protected function _subjectTimelapseNotifDiplomacyColony() {
        return __('Colonization', 'stephino-rpg');
    }
    
    /**
     * Subject for timelapse: diplomacy: premium
     * 
     * @return string
     */
    protected function _subjectTimelapseNotifDiplomacyPremium() {
        return __('Premium modifier expired', 'stephino-rpg');
    }
    
    /**
     * Subject for timelapse: diplomacy: discovery
     * 
     * @return string
     */
    protected function _subjectTimelapseNotifDiplomacyDiscovery() {
        return __('Discovery', 'stephino-rpg');
    }
    
    /**
     * Convert message data to readable text<br/>
     * Replaces the Subject (a notification template string) with i18n text by calling the corresponding callable<br/>
     * Replaces the Content (a JSON-encoded payload array) with i18n text by loading the appropriate template by message type and notification template<br/>
     * In case of messages sent with <b>::contact()</b> no action is performed
     * 
     * @param array|null &$messageData Message DB row
     * @param boolean    $pruneContent (optional) Discard the message content; default <b>false</b>
     */
    protected function _parse(&$messageData, $pruneContent = false) {
        if (is_array($messageData) 
            && isset($messageData[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_SUBJECT])
            && isset($messageData[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_CONTENT])) {
            $notifTemplate = $messageData[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_SUBJECT];
            
            // Subject matches a notification template
            if (in_array($notifTemplate, $this->_notifTemplates)) {
                // Content appears to be a JSON array; this is not possible for messages sent with ::contact() as those are wrapped in HTML tags
                if (0 === strpos($messageData[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_CONTENT], '[')) {
                    // Content is a valid JSON array
                    if (is_array($notifData = @json_decode($messageData[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_CONTENT], true))) {
                        if ($pruneContent) {
                            unset($messageData[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_CONTENT]);
                        } else {
                            try {
                                // Start the buffer
                                ob_start();

                                // Load the template
                                require self::getTemplatePath($notifTemplate);
                            } catch (Exception $exc) {
                                Stephino_Rpg_Log::check() && Stephino_Rpg_Log::warning(
                                    "Db_Model_Messages._parse, template $notifTemplate: {$exc->getMessage()}"
                                );
                            }
                        
                            // Parse the message content in the current user language
                            $messageData[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_CONTENT] = trim(
                                preg_replace(
                                    '%(?:[\r\n\t]+| {2,})%', 
                                    ' ', 
                                    ob_get_clean()
                                )
                            );
                        }
                        
                        // Prepare the subject callback method
                        $subjectCallback = '_subject' . implode(
                            '', 
                            array_map(
                                'ucfirst', 
                                preg_split('%[\-\/]%', $notifTemplate)
                            )
                        );

                        // Get the message subject
                        if (method_exists($this, $subjectCallback)) {
                            $messageData[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_SUBJECT] = call_user_func_array(
                                array($this, $subjectCallback),
                                $notifData
                            );
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Fetch a message, loading the appropriate template with i18n support or displaying directly (in case of messages sent with <b>::contact()</b>)
     * 
     * @param int     $userId     User ID
     * @param int     $messageId  Message ID
     * @param boolean $markAsRead (optional) Mark the message as read; default <b>false</b>
     */
    public function fetch($userId, $messageId, $markAsRead = false) {
        $messageData = $this->getDb()
            ->tableMessages()
            ->getInboxMessage($userId, $messageId);
        
        // Valid data
        if (is_array($messageData)) {
            // Mark as read
            if ($markAsRead && 0 === (int) $messageData[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_READ]) {
                $this->getDb()
                    ->tableMessages()
                    ->readInboxMessage($userId, $messageId);

                // Store the "read" flag in the current result as well
                $messageData[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_READ] = 1;
            }
            
            // Parse the message data into readable text
            $this->_parse($messageData);
        }
        
        return $messageData;
    }
    
    /**
     * Get all messages of a certain type for the current user
     * 
     * @param int     $userId       Recipient user ID
     * @param string  $messageType  Message type
     * @param int     $limitCount   (optional) Limit count; default <b>null</b>
     * @param int     $limitOffset  (optional) Limit offset; default <b>null</b>
     * @param boolean $pruneContent (optional) Remove the message content; default <b>true</b>
     * @return array
     */
    public function fetchByType($userId, $messageType, $limitCount = null, $limitOffset = null, $pruneContent = true) {
        // Get all messages
        $messages = $this->getDb()->tableMessages()->getInboxByType(
            $userId,
            $messageType,
            $limitCount,
            $limitOffset
        );
        
        // Parse the messages
        foreach ($messages as &$messageData) {
            $this->_parse($messageData, $pruneContent);
        }
        
        return $messages;
    }
    
    /**
     * Get the unread messages without the message content
     * 
     * @param int $userId Recipient User ID
     * @return array
     */
    public function fetchUnread($userId) {
        $messages = $this->getDb()->tableMessages()->getInboxAllUnread(
            $userId
        );
        
        // Parse the messages
        foreach ($messages as &$messageData) {
            $this->_parse($messageData, true);
        }
        
        return $messages;
    }
    
    /**
     * Perform pruning tasks<ul>
     *     <li>Delete messages older than core.messageMaxAge for ALL players</li>
     *     <li>Delete older message over the than core.messageInboxLimit inbox limit for this user</li>
     * </ul>
     */
    public function prune($userId) {
        // Delete all old messages
        $this->getDb()->tableMessages()->deleteAllExpired(
            Stephino_Rpg_Config::get()->core()->getMessageMaxAge()
        );
        
        // Delete user's messages over the inbox limit
        $this->getDb()->tableMessages()->deleteInboxOverflow(
            $userId,
            Stephino_Rpg_Config::get()->core()->getMessageInboxLimit()
        );
    }
    
    /**
     * Delete all user's messages
     * 
     * @param int $userId User ID
     * @return array|null
     */
    public function deleteByUser($userId) {
        // Delete all messages by user
        return $this->getDb()->tableMessages()->deleteAll($userId);
    }
    
    /**
     * Send a message from a player to another verbatim<br/>
     * Uses the "timelapse-diplomacy" template<br/>
     * Prevents message flooding and sending to robots<br/>
     * The message content is HTML encoded if not sent by the system (sender ID 0)
     * 
     * @param int     $senderId       Sender User ID
     * @param int     $recipientId    Recipient User ID
     * @param string  $messageSubject Message Subject
     * @param string  $messageContent Message Content
     * @return int|null New Message ID or Null on error
     * @throws Exception
     */
    public function contact($senderId, $recipientId, $messageSubject, $messageContent) {
        // Sanitization
        $senderId = abs((int) $senderId);
        $recipientId = abs((int) $recipientId);
        $messageSubject = Stephino_Rpg_Utils_Lingo::cleanup($messageSubject);
        
        // Don't send to self
        if ($senderId == $recipientId) {
            throw new Exception(__('You cannot contact yourself', 'stephino-rpg'));
        }
        
        // Validation
        if (!strlen($messageSubject)) {
            throw new Exception(__('Message subject missing', 'stephino-rpg'));
        }
        if (!strlen($messageContent)) {
            throw new Exception(__('Message content missing', 'stephino-rpg'));
        }
        
        // Get the user information
        if (!is_array($recipientData = $this->getDb()->tableUsers()->getById($recipientId))) {
            throw new Exception(__('Recipient not found', 'stephino-rpg'));
        }
        
        // No robots
        if (!is_numeric($recipientData[Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID])) {
            throw new Exception(__('You cannot contact robots', 'stephino-rpg'));
        }
        
        // Invalid player
        if (!is_array($senderInfo = $this->getDb()->tableUsers()->getById($senderId))) {
            throw new Exception(__('Sender not found', 'stephino-rpg'));
        }

        // Get the recent messages count
        if ($this->getDb()->tableMessages()->getSentRecent($senderId) >= Stephino_Rpg_Config::get()->core()->getMessageDailyLimit()) {
            throw new Exception(__('Daily message limit reached', 'stephino-rpg'));
        }
        
        // Start the buffer
        ob_start();

        try {
            // Load the template
            require self::getTemplatePath(self::TEMPLATE_NOTIF_USER_CONTACT);
        } catch (Exception $exc) {
            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::warning(
                "Db_Model_Messages.send, sender #$senderId, recipient #$recipientId: {$exc->getMessage()}"
            );
        }

        // Override the content with our template
        $messageContent = trim(preg_replace('%(?:[\r\n\t]+| {2,})%', ' ', ob_get_clean()));
        
        // Store the message verbatim (no i18n support for direct messages between players)
        return $this->getDb()->tableMessages()->create(
            $senderId, 
            $recipientId, 
            Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_DIPLOMACY, 
            $messageSubject, 
            $messageContent
        );
    }
    
    /**
     * Send a notification from the system<br/>
     * The messages is composed JIT when read by players to ensure i18n<br/>
     * The <b>$notifData</b> is stored as the message content in JSON format
     * 
     * @param int    $recipientId   Recipient ID
     * @param string $notifTemplate Notification content template name
     * @param array  $notifData     Notification data to pass along to the subject callback method and template
     * @param string $messageType   (optional) Message type, one of <ul>
     * <li>Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_DIPLOMACY</li>
     * <li>Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_RESEARCH</li>
     * <li>Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_ECONOMY</li>
     * <li>Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_MILITARY</li>
     * </ul>default <b>Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_DIPLOMACY</b>
     * @throws Exception
     */
    public function notify($recipientId, $notifTemplate, $notifData = array(), $messageType = Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_DIPLOMACY) {
        // Sanitize recipient
        $recipientId = abs((int) $recipientId);
        
        // Invalid template
        if (!in_array($notifTemplate, $this->_notifTemplates)) {
            throw new Exception(__('Invalid notification template', 'stephino-rpg'));
        }
        
        // Sanitize notification data
        if (!is_array($notifData)) {
            $notifData = array($notifData);
        }
        
        // Store the notification
        return $this->getDb()->tableMessages()->create(
            0, 
            $recipientId, 
            $messageType, 
            $notifTemplate, 
            json_encode($notifData)
        );
    }
    
    /**
     * Create multiple messages<br/>
     * Message content is assumed HTML-ready<br/>
     * Prevents sending messages to robots<br/>
     * Does <b>NOT</b> handle flooding, use with caution!
     * 
     * @param array $payload  Array of message arrays. <br/>
     * The following keys are mandatory (and not empty) for each message array:<ul>
     * <li>Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_TO</li>
     * <li>Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_TYPE</li>
     * <li>Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_SUBJECT</li>
     * <li>Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_CONTENT</li>
     * </ul>
     * @param int   $senderId (optional) Sender ID for messages that lack the <b>Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_FROM</b> key; default <b>0</b>
     * @return int|false Number of rows affected or false on error
     */
    public function sendMultiple($payload, $senderId = 0) {
        // Get the recipient IDs
        $recipientIds = array();
        if (is_array($payload)) {
            foreach ($payload as $key => $data) {
                if (isset($data[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_TO])) {
                    $recipientIds[] = $data[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_TO];
                }
            }
        }
        
        // Get the robot accounts
        $robotIds = $this->getDb()->tableUsers()->filterRobots(array_unique($recipientIds));
        
        // Eliminate robot and self messages from the payload
        if (is_array($payload)) {
            foreach ($payload as $key => $data) {
                // Recipient not set or a robot or self
                if (!isset($data[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_TO])
                    || in_array($data[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_TO], $robotIds)
                    || $data[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_TO] == $senderId) {
                    // Remove the message
                    unset($payload[$key]);
                }
            }
        }
        
        // Multi-insert
        return $this->getDb()->tableMessages()->createMultiple($payload, $senderId);
    }
    
}

/* EOF */