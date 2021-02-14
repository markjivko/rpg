<?php
/**
 * Stephino_Rpg_Db_Model_Messages
 * 
 * @title     Model:Messages
 * @desc      Messages Model
 * @copyright (c) 2021, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Db_Model_Messages extends Stephino_Rpg_Db_Model {

    /**
     * Messages Model Name
     */
    const NAME = 'messages';
    
    /**
     * Perform pruning tasks<ul>
     *     <li>Delete messages older than core.messageMaxAge for ALL players</li>
     *     <li>Delete older message over the than core.messageInboxLimit inbox limit for this user</li>
     * </ul>
     */
    public function prune($userId) {
        // Delete all old messages
        $this->getDb()->tableMessages()->deleteOlder(
            Stephino_Rpg_Config::get()->core()->getMessageMaxAge()
        );
        
        // Delete our messages over the inbox limit
        $this->getDb()->tableMessages()->deleteOverLimit(
            Stephino_Rpg_Config::get()->core()->getMessageInboxLimit(),
            $userId
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
        return $this->getDb()->tableMessages()->deleteByUser($userId);
    }
    
    /**
     * Send a message.<br/>
     * Loads the "timelapse-diplomacy" template automatically<br/>
     * Prevents message flooding and sending to robots
     * 
     * @param int     $senderId       Sender User ID
     * @param int     $recipientId    Recipient User ID
     * @param string  $messageSubject Message Subject
     * @param string  $messageContent Message Content
     * @param boolean $contentIsHtml  (optional) Message content should be treated as clean HTML, otherwise it will be parsed as plain text; default <b>false</b>
     * @return int|null New Message ID or Null on error
     * @throws Exception
     */
    public function send($senderId, $recipientId, $messageSubject, $messageContent, $contentIsHtml = false) {
        // Sanitization
        $senderId = abs((int) $senderId);
        $recipientId = abs((int) $recipientId);
        $messageSubject = Stephino_Rpg_Utils_Lingo::cleanup($messageSubject);
        if (!$contentIsHtml) {
            $messageContent = Stephino_Rpg_Utils_Lingo::cleanup($messageContent);
        }
        
        // Don't send to self
        if ($senderId == $recipientId) {
            throw new Exception(__('You cannot contact yourself', 'stephino-rpg'));
        }
        
        // Validation
        if (null === $messageSubject) {
            throw new Exception(__('Message subject missing', 'stephino-rpg'));
        }
        if (null === $messageContent) {
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
        
        // Prepare the sender information
        $senderInfo = null;
        
        // A human player
        if (0 !== $senderId) {
            if (!is_array($senderInfo = $this->getDb()->tableUsers()->getById($senderId))) {
                throw new Exception(__('Sender not found', 'stephino-rpg'));
            }
        
            // Get the recent messages count
            if ($this->getDb()->tableMessages()->getCountRecent($senderId) >= Stephino_Rpg_Config::get()->core()->getMessageDailyLimit()) {
                throw new Exception(__('Daily message limit reached', 'stephino-rpg'));
            }
        }
        
        // Start the buffer
        ob_start();

        try {
            // Load the template
            require Stephino_Rpg_TimeLapse::getTemplatePath(Stephino_Rpg_TimeLapse::TEMPLATE_DIPLOMACY);
        } catch (Exception $exc) {
            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::warning(
                "Db_Model_Messages.send, sender #$senderId, recipient #$recipientId: {$exc->getMessage()}"
            );
        }

        // Override the content with our template
        $messageContent = trim(preg_replace('%(?:[\r\n\t]+| {2,})%', ' ', ob_get_clean()));
        
        // Add the new message
        return $this->getDb()->tableMessages()->create(
            $senderId, 
            $recipientId, 
            Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_DIPLOMACY, 
            $messageSubject, 
            $messageContent
        );
    }
    
    /**
     * Send a notification
     * 
     * @param int    $recipientId    Recipient ID
     * @param string $messageSubject Notification subject
     * @param string $notifTemplate  Notification template, Stephino_Rpg_TimeLapse::TEMPLATE_NOTIF*
     * @param mixed  $notifData      Message data to pass along to the template
     * @throws Exception
     */
    public function sendNotification($recipientId, $messageSubject, $notifTemplate, $notifData) {
        // Invalid template
        if (!preg_match('%^notification\/%', $notifTemplate)) {
            throw new Exception(__('Invalid notification template', 'stephino-rpg'));
        }
        
        // Sent by the system
        $senderId = 0;
        
        // Sanitize recipient
        $recipientId = intval($recipientId);
        if ($recipientId <= 0) {
            throw new Exception(__('Invalid notification recipient ID', 'stephino-rpg'));
        }
        
        // Start the buffer
        ob_start();
        try {
            // Load the template
            require Stephino_Rpg_TimeLapse::getTemplatePath($notifTemplate);
        } catch (Exception $exc) {
            Stephino_Rpg_Log::check() && Stephino_Rpg_Log::warning(
                "Db_Model_Messages.sendNotification, recipient #$recipientId: {$exc->getMessage()}"
            );
        }

        // Get the notification text
        $messageContent = ob_get_clean();
        if (strlen($messageContent)) {
            // Start the buffer
            ob_start();

            try {
                // Load the template
                require Stephino_Rpg_TimeLapse::getTemplatePath(Stephino_Rpg_TimeLapse::TEMPLATE_DIPLOMACY);
            } catch (Exception $exc) {
                Stephino_Rpg_Log::check() && Stephino_Rpg_Log::warning(
                    "Db_Model_Messages.sendNotification, recipient #$recipientId: {$exc->getMessage()}"
                );
            }

            // Override the content with our template
            $messageContent = trim(preg_replace('%(?:[\r\n\t]+| {2,})%', ' ', ob_get_clean()));

            // Add the new message
            return $this->getDb()->tableMessages()->create(
                $senderId, 
                $recipientId, 
                Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_DIPLOMACY, 
                $messageSubject, 
                $messageContent
            );
        }
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