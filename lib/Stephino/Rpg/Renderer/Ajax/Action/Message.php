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
    const REQUEST_MESSAGE_ID = 'messageId';
    const REQUEST_MESSAGE_TO = 'messageTo';
    const REQUEST_MESSAGE_SUBJECT = 'messageSubject';
    const REQUEST_MESSAGE_CONTENT = 'messageContent';
    
    // Messaging maximum values
    const MAX_MESSAGE_SUBJECT_LENGTH = 200;
    const MAX_MESSAGE_CONTENT_LENGTH = 2000;
    
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
        $messageId = isset($data[self::REQUEST_MESSAGE_ID]) ? intval($data[self::REQUEST_MESSAGE_ID]) : null;
        
        // Invalid message
        if (null === $messageId) {
            throw new Exception(__('Message ID mandatory', 'stephino-rpg'));
        }
        
        // Delete the message
        $result = Stephino_Rpg_Db::get()->tableMessages()->deleteInboxMessage(
            Stephino_Rpg_TimeLapse::get()->userId(),
            $messageId
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
        if (strlen($messageSubject) > self::MAX_MESSAGE_SUBJECT_LENGTH) {
            throw new Exception(
                sprintf(
                    __('Subject maximum lenght of %d characters exceeded', 'stephino-rpg'),
                    self::MAX_MESSAGE_SUBJECT_LENGTH
                )
            );
        }
        
        // Message
        if (strlen($messageContent) > self::MAX_MESSAGE_CONTENT_LENGTH) {
            throw new Exception(
                sprintf(
                    __('Content maximum lenght of %d characters exceeded', 'stephino-rpg'),
                    self::MAX_MESSAGE_CONTENT_LENGTH
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