<?php

/**
 * ThemeWarlock - Stephino_Rpg_Renderer_Ajax_Dialog_Messages
 * 
 * @title      Dialog::Messages
 * @desc       Messages dialogs
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    ThemeWarlock
 * @since      TW 1.0
 */
class Stephino_Rpg_Renderer_Ajax_Dialog_Messages extends Stephino_Rpg_Renderer_Ajax_Dialog {

    // Dialog templates
    const TEMPLATE_LIST = 'messages/messages-list';
    const TEMPLATE_READ = 'messages/messages-read';
    
    // Request keys
    const REQUEST_MESSAGE_ID   = 'messageId';
    const REQUEST_MESSAGE_TYPE = 'messageType';
    
    /**
     * Message list
     * 
     * @param array $data Data containing <ul>
     * <li><b>messageType</b> (string) Message type</li>
     * </ul>
     * @return array Array of messages
     * @throws Exception
     */
    public static function ajaxList($data) {
        // Message cleanup
        Stephino_Rpg_Db::get()->modelMessages()->prune(
            Stephino_Rpg_TimeLapse::get()->userId()
        );
        
        // Prepare the message type
        $messageType = isset($data[self::REQUEST_MESSAGE_TYPE]) ? $data[self::REQUEST_MESSAGE_TYPE] : null;
        if (!in_array($messageType, Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPES)) {
            $messageType = Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_DIPLOMACY;
        }

        // Get the message data
        $messageData = Stephino_Rpg_Db::get()->tableMessages()->getAllByType(
            Stephino_Rpg_TimeLapse::get()->userId(),
            $messageType
        );

        // Prepare the title the title
        $dialogTitle = __('Messages', 'stephino-rpg');
        switch ($messageType) {
            case Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_DIPLOMACY:
                $dialogTitle = __('Diplomacy', 'stephino-rpg');
                break;

            case Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_MILITARY:
                $dialogTitle = __('Military', 'stephino-rpg');
                break;

            case Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_ECONOMY:
                $dialogTitle = __('Economy', 'stephino-rpg');
                break;

            case Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_RESEARCH:
                $dialogTitle = __('Research', 'stephino-rpg');
                break;
        }
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_LIST);
        
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => $dialogTitle,
                self::RESULT_DATA  => $messageData,
            )
        );
    }
    
    /**
     * Display the content of a message
     * 
     * @param array $data Data containing <ul>
     * <li><b>messageType</b> (string) Message type</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxRead($data) {
        // Get a full message
        $messageId = isset($data[self::REQUEST_MESSAGE_ID]) ? intval($data[self::REQUEST_MESSAGE_ID]) : null;
        
        // Store the message data
        $messageData = Stephino_Rpg_Db::get()->tableMessages()->getMessage(
            Stephino_Rpg_TimeLapse::get()->userId(),
            $messageId
        );

        // Invalid result
        if (!is_array($messageData)) {
            throw new Exception(__('Message not found', 'stephino-rpg'));
        }
        
        // Mark as read
        if (!intval($messageData[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_READ])) {
            // Update the database
            Stephino_Rpg_Db::get()->tableMessages()->markRead(
                Stephino_Rpg_TimeLapse::get()->userId(),
                $messageId
            );

            // Store the "read" flag in the current result as well
            $messageData[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_READ] = 1;
        }

        // Store the title
        $dialogTitle = $messageData[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_SUBJECT];
        
        // Load the template
        require self::dialogTemplatePath(self::TEMPLATE_READ);
        
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => $dialogTitle,
                self::RESULT_DATA  => $messageData,
            )
        );
    }
}

/*EOF*/