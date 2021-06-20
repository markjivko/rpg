<?php

/**
 * Stephino_Rpg_Renderer_Ajax_Action_Message
 * 
 * @title      Action::Message
 * @desc       Message actions
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Renderer_Ajax_Action_Message extends Stephino_Rpg_Renderer_Ajax_Action {

    // Request keys
    const REQUEST_MESSAGE_ID      = 'messageId';
    const REQUEST_MESSAGE_TO      = 'messageTo';
    const REQUEST_MESSAGE_TYPE    = 'messageType';
    const REQUEST_MESSAGE_SUBJECT = 'messageSubject';
    const REQUEST_MESSAGE_CONTENT = 'messageContent';
    
    /**
     * Delete a received message
     * 
     * @param array $data Data containing <ul>
     * <li><b>messageId</b> (int) Message ID</li>
     * </ul>
     * @return array
     * @throws Exception
     */
    public static function ajaxDelete($data) {
        // Get the message ID
        $messageId = isset($data[self::REQUEST_MESSAGE_ID]) ? intval($data[self::REQUEST_MESSAGE_ID]) : 0;
        
        // Invalid message ID
        if ($messageId <= 0) {
            throw new Exception(__('Message ID mandatory', 'stephino-rpg'));
        }
        
        // Delete the message
        $result = Stephino_Rpg_Db::get()->tableMessages()->deleteInboxById(
            Stephino_Rpg_TimeLapse::get()->userId(),
            $messageId
        );
        
        return Stephino_Rpg_Renderer_Ajax::wrap($result);
    }
    
    /**
     * Delete all messages of a certain type
     * 
     * @param array $data Data containing <ul>
     * <li><b>messageType</b> (string) Message Type</li>
     * </ul>
     * @return array
     * @throws Exception
     */
    public static function ajaxDeleteAll($data) {
        // Get the message type
        $messageType = isset($data[self::REQUEST_MESSAGE_TYPE]) ? trim($data[self::REQUEST_MESSAGE_TYPE]) : '';
        
        // Invalid message type
        if (!in_array($messageType, Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPES)) {
            throw new Exception(__('Invalid message type', 'stephino-rpg'));
        }
        
        // Delete the messages
        $result = Stephino_Rpg_Db::get()->tableMessages()->deleteInboxByType(
            Stephino_Rpg_TimeLapse::get()->userId(),
            $messageType
        );
        
        return Stephino_Rpg_Renderer_Ajax::wrap($result);
    }
    
    /**
     * Send a message
     * 
     * @param array $data Data containing <ul>
     * <li><b>messageTo</b> (int) Recipient user ID</li>
     * <li><b>messageSubject</b> (string) Message subject</li>
     * <li><b>messageContent</b> (string) Message content</li>
     * </ul>
     * @return array
     * @throws Exception
     */
    public static function ajaxCreate($data) {
        // Get the message details
        $messageTo = isset($data[self::REQUEST_MESSAGE_TO]) ? intval($data[self::REQUEST_MESSAGE_TO]) : 0;
        $messageSubject = isset($data[self::REQUEST_MESSAGE_SUBJECT]) ? trim($data[self::REQUEST_MESSAGE_SUBJECT]) : '';
        $messageContent = isset($data[self::REQUEST_MESSAGE_CONTENT]) ? trim($data[self::REQUEST_MESSAGE_CONTENT]) : '';
        
        // Validate
        if ($messageTo <= 0) {
            throw new Exception(__('Invalid recipient', 'stephino-rpg'));
        }
        
        // Subject too long
        if (strlen($messageSubject) > Stephino_Rpg_Db_Model_Messages::MAX_LENGTH_SUBJECT) {
            throw new Exception(
                sprintf(
                    __('Subject maximum lenght of %d characters exceeded', 'stephino-rpg'),
                    Stephino_Rpg_Db_Model_Messages::MAX_LENGTH_SUBJECT
                )
            );
        }
        
        // Message
        if (strlen($messageContent) > Stephino_Rpg_Db_Model_Messages::MAX_LENGTH_CONTENT) {
            throw new Exception(
                sprintf(
                    __('Content maximum lenght of %d characters exceeded', 'stephino-rpg'),
                    Stephino_Rpg_Db_Model_Messages::MAX_LENGTH_CONTENT
                )
            );
        }
        
        // Send the message
        $messageId = Stephino_Rpg_Db::get()->modelMessages()->contact(
            Stephino_Rpg_TimeLapse::get()->userId(),
            $messageTo,
            $messageSubject,
            $messageContent
        );
        
        return Stephino_Rpg_Renderer_Ajax::wrap($messageId);
    }
    
}

/*EOF*/