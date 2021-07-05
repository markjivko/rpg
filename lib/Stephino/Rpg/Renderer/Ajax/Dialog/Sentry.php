<?php

/**
 * ThemeWarlock - Stephino_Rpg_Renderer_Ajax_Dialog_Sentry
 * 
 * @title      Dialog::Sentry
 * @desc       Sentry dialogs
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    ThemeWarlock
 * @since      TW 1.0
 */
class Stephino_Rpg_Renderer_Ajax_Dialog_Sentry extends Stephino_Rpg_Renderer_Ajax_Dialog {
    
    // Dialog templates
    const TEMPLATE_SENTRY_INFO              = 'sentry/sentry-info';
    const TEMPLATE_SENTRY_CUSTOMIZE         = 'sentry/sentry-customize';
    const TEMPLATE_SENTRY_CHALLENGE_LIST    = 'sentry/sentry-challenge-list';
    const TEMPLATE_SENTRY_CHALLENGE_PREPARE = 'sentry/sentry-challenge-prepare';
    
    // Request keys
    const REQUEST_SENTRY_CHALLENGE         = 'sentryChallenge';
    const REQUEST_SENTRY_CHALLENGE_USER_ID = 'sentryChallengeUserId';
    const REQUEST_SENTRY_CHALLENGE_PAGE    = 'sentryChallengePage';
    
    // JavaScript actions
    const JS_ACTION_PTF_ARENA_LIST     = 'ptfArenaList';
    const JS_ACTION_SENTRY_CHALLENGE_LIST = 'sentryChallengeList';
    
    /**
     * Sentry information
     * 
     * @throws Exception
     */
    public static function ajaxInfo($data) {
        if (!Stephino_Rpg_Config::get()->core()->getSentryEnabled()) {
            throw new Exception(__('This feature is not available', 'stephino-rpg'));
        }
        
        // Prepare the common arguments
        if (!is_array($commonArgs = isset($data[self::REQUEST_COMMON_ARGS]) ? $data[self::REQUEST_COMMON_ARGS] : array())) {
            $commonArgs = array();
        }
        
        // This is our sentry
        $sentryOwn = true;
        $sentryChallenge = null;
        
        // Get my data
        if (!is_array($userData = Stephino_Rpg_TimeLapse::get()->userData())) {
            throw new Exception(__('User not initialized', 'stephino-rpg'));
        }
        
        // Prepare the sentry owner data
        $sentryOwnerData = $userData;
        
        // Custom user specified
        if (count($commonArgs)) {
            $userId = abs((int) current($commonArgs));
            
            // Mark the sentry as different from our own
            if ($userId !== Stephino_Rpg_TimeLapse::get()->userId()) {
                $sentryOwn = false;
                
                // Try to get the user data
                if (!is_array($sentryOwnerData = Stephino_Rpg_Db::get()->tableUsers()->getById($userId, true))) {
                    throw new Exception(__('User not found', 'stephino-rpg'));
                }
            }

            // Custom challenge
            if (isset($commonArgs[1]) && in_array($commonArgs[1], array_keys(Stephino_Rpg_Db::get()->modelSentries()->getColumns()))) {
                $sentryChallenge = $commonArgs[1];
            }
        }
        
        // Get the sentry convoy for this user
        $sentryConvoy = Stephino_Rpg_Db::get()->tableConvoys()->getSentryFromUser(
            (int) $sentryOwnerData[Stephino_Rpg_Db_Table_Users::COL_ID]
        );
        
        // Prepare optional just-in-time sentry updates
        $sentryOwnerUpdates = array();
        
        // Sentry name not initialized
        if (!strlen($sentryOwnerData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_NAME])) {
            $newSentryName = Stephino_Rpg_Utils_Lingo::generateSentryName();
            
            $sentryOwnerUpdates[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_NAME] = $newSentryName;
            $sentryOwnerData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_NAME]    = $newSentryName;
        }
        
        // Updates made
        if (count($sentryOwnerUpdates)) {
            Stephino_Rpg_Db::get()->tableUsers()->updateById(
                $sentryOwnerUpdates, 
                (int) $sentryOwnerData[Stephino_Rpg_Db_Table_Users::COL_ID]
            );
        }
        
        // Engaged in a batttle
        $opponentData = null;
        if ($sentryOwn && 0 !== (int) $userData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_ACTIVE]) {
            $opponentData = Stephino_Rpg_Db::get()->tableUsers()->getById(
                (int) $userData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_ACTIVE],
                true
            );
            self::setModalSize(self::MODAL_SIZE_LARGE);
        }
        
        require self::dialogTemplatePath(self::TEMPLATE_SENTRY_INFO);
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => Stephino_Rpg_Config::get()->core()->getConfigSentryName(true) . ': ' . __('Details', 'stephino-rpg'),
            )
        );
    }
    
    /**
     * Sentry customization
     * 
     * @throws Exception
     */
    public static function ajaxCustomize() {
        if (!Stephino_Rpg_Config::get()->core()->getSentryEnabled()) {
            throw new Exception(__('This feature is not available', 'stephino-rpg'));
        }
        
        // Get my data
        if (!is_array($userData = Stephino_Rpg_TimeLapse::get()->userData())) {
            throw new Exception(__('User not initialized', 'stephino-rpg'));
        }
        
        // Prepare the sentry design
        $sentryDesign = array(
            Stephino_Rpg_Utils_Media::getSentryAssets(),
            Stephino_Rpg_Utils_Media::getSentryDefinition(
                Stephino_Rpg_TimeLapse::get()->userId()
            )
        );
        
        require self::dialogTemplatePath(self::TEMPLATE_SENTRY_CUSTOMIZE);
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_DATA  => $sentryDesign,
                self::RESULT_TITLE => Stephino_Rpg_Config::get()->core()->getConfigSentryName(true) . ': ' . __('Customize', 'stephino-rpg'),
            )
        );
    }
    
    /**
     * Sentry challenge list
     * 
     * @param array $data Data containing either <b>self::REQUEST_COMMON_ARGS</b> OR
     * <ul>
     * <li><b>self::REQUEST_SENTRY_CHALLENGE</b> (string) Challenge type</li>
     * <li><b>self::REQUEST_SENTRY_CHALLENGE_PAGE</b> (int) Challenge list page</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxChallengeList($data) {
        if (!Stephino_Rpg_Config::get()->core()->getSentryEnabled()) {
            throw new Exception(__('This feature is not available', 'stephino-rpg'));
        }
        
        // Invalid user data
        if (!is_array($userData = Stephino_Rpg_TimeLapse::get()->userData())) {
            throw new Exception(__('User not initialized', 'stephino-rpg'));
        }
        
        // Sentry already in a challenge
        if (0 !== (int) $userData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_ACTIVE]) {
            throw new Exception(__('Challenge is in progress...', 'stephino-rpg'));
        }
        
        // Prepare the arguments
        $sentryChallenge = null;
        $sentryChallengePage = 1;
        
        // Called with data-click="dialog"
        if (isset($data[self::REQUEST_COMMON_ARGS]) && is_array($data[self::REQUEST_COMMON_ARGS])) {
            $sentryChallenge = current($data[self::REQUEST_COMMON_ARGS]);
        } else {
            // Called with data-effect/data-click sentryChallengeList
            $sentryChallenge = isset($data[self::REQUEST_SENTRY_CHALLENGE]) ? $data[self::REQUEST_SENTRY_CHALLENGE] : null;
            $sentryChallengePage = isset($data[self::REQUEST_SENTRY_CHALLENGE_PAGE]) ? $data[self::REQUEST_SENTRY_CHALLENGE_PAGE] : null;;
        }
        
        // Prepare the title
        $sentryActionLabels = Stephino_Rpg_Db::get()->modelSentries()->getLabels(true);
        
        // Invalid challenge type
        if (!isset($sentryActionLabels[$sentryChallenge])) {
            throw new Exception(__('Invalid challenge type', 'stephino-rpg'));
        }
        
        // Pagination data
        $pagination = (new Stephino_Rpg_Utils_Pagination(
            Stephino_Rpg_Db::get()->tableUsers()->getSentriesCount(
                Stephino_Rpg_TimeLapse::get()->userId()
            ),
            Stephino_Rpg_Config::get()->core()->getMessagePageSize(),
            $sentryChallengePage
        ))->setAction(self::JS_ACTION_SENTRY_CHALLENGE_LIST);
        
        // Get the challenge data
        $sentryChallengeData = Stephino_Rpg_Db::get()->modelSentries()->getList(
            Stephino_Rpg_TimeLapse::get()->userId(),
            $pagination->getSqlLimitCount(),
            $pagination->getSqlLimitOffset(),
            true
        );
        
        require self::dialogTemplatePath(self::TEMPLATE_SENTRY_CHALLENGE_LIST);
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => Stephino_Rpg_Config::get()->core()->getConfigSentryName(true) . ': ' . $sentryActionLabels[$sentryChallenge],
            )
        ); 
    }
    
    /**
     * 
     * @param array $data Data containing <ul>
     * <li><b>self::REQUEST_SENTRY_CHALLENGE</b> (string) Challenge type</li>
     * <li><b>self::REQUEST_SENTRY_CHALLENGE_USER_ID</b> (int) Challenge target user ID</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxChallengePrepare($data) {
        if (!Stephino_Rpg_Config::get()->core()->getSentryEnabled()) {
            throw new Exception(__('This feature is not available', 'stephino-rpg'));
        }
        
        // Called with data-effect/data-click sentryChallengePrepare
        $sentryChallenge = isset($data[self::REQUEST_SENTRY_CHALLENGE]) ? trim($data[self::REQUEST_SENTRY_CHALLENGE]) : '';
        $sentryChallengeUserId = isset($data[self::REQUEST_SENTRY_CHALLENGE_USER_ID]) ? abs((int) $data[self::REQUEST_SENTRY_CHALLENGE_USER_ID]) : 0;
        
        // Invalid user data
        if (!is_array($userData = Stephino_Rpg_TimeLapse::get()->userData())) {
            throw new Exception(__('User not initialized', 'stephino-rpg'));
        }
        
        // Sentry already in a challenge
        if (0 !== (int) $userData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_ACTIVE]) {
            throw new Exception(__('Challenge is in progress...', 'stephino-rpg'));
        }
        
        // Invalid challenge type
        if (!in_array($sentryChallenge, array_keys(Stephino_Rpg_Db::get()->modelSentries()->getColumns()))) {
            throw new Exception(__('Invalid challenge type', 'stephino-rpg'));
        }
        
        // Prepare the challange data
        if (!is_array($opponentData = Stephino_Rpg_Db::get()->tableUsers()->getById($sentryChallengeUserId, true))) {
            throw new Exception(__('Opponent not found', 'stephino-rpg'));
        }
        
        // Cannot challenge yourself
        if ((int) $opponentData[Stephino_Rpg_Db_Table_Users::COL_ID] === Stephino_Rpg_TimeLapse::get()->userId()) {
            throw new Exception(__('You cannot fight yourself', 'stephino-rpg'));
        }
        
        // Large modal
        self::setModalSize(self::MODAL_SIZE_LARGE);
        
        require self::dialogTemplatePath(self::TEMPLATE_SENTRY_CHALLENGE_PREPARE);
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                self::RESULT_TITLE => __('Challenge', 'stephino-rpg'),
            )
        ); 
    }
    
}

/*EOF*/