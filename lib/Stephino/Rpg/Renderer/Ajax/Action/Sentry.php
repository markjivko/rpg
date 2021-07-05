<?php

/**
 * Stephino_Rpg_Renderer_Ajax_Action_Sentry
 * 
 * @title      Action::Sentry
 * @desc       Sentry actions
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Renderer_Ajax_Action_Sentry extends Stephino_Rpg_Renderer_Ajax_Action {
    
    // Request keys
    const REQUEST_SENTRY_CHALLENGE         = 'sentryChallenge';
    const REQUEST_SENTRY_CHALLENGE_USER_ID = 'sentryChallengeUserId';
    const REQUEST_SENTRY_NAME              = 'sentryName';
    const REQUEST_SENTRY_IMAGE             = 'sentryImage';
    const REQUEST_SENTRY_DEFINITION        = 'sentryDefinition';
    
    /**
     * Rename a sentry
     * 
     * @param array $data Data containing <ul>
     * <li><b>sentryName</b> (string) New sentry name</li>
     * </ul>
     * @return array
     */
    public static function ajaxRename($data) {
        if (!Stephino_Rpg_Config::get()->core()->getSentryEnabled()) {
            throw new Exception(__('This feature is not available', 'stephino-rpg'));
        }
        
        // Invalid user data
        if (!is_array($userData = Stephino_Rpg_TimeLapse::get()->userData())) {
            throw new Exception(__('User not initialized', 'stephino-rpg'));
        }
        
        // Prepare the sentry name
        $newSentryName = isset($data[self::REQUEST_SENTRY_NAME]) 
            ? trim($data[self::REQUEST_SENTRY_NAME]) 
            : $userData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_NAME];
        
        // Sanitize it, removing anything that is not a valid UTF-8 alpha-numeric character or space
        $newSentryName = trim(preg_replace(array('%[^ \d\p{L}]+%u', '%\s+%s'), array('', ' '), $newSentryName));
        
        // Cannot be an empty string, generate a new one
        if (!strlen($newSentryName)) {
            $newSentryName = Stephino_Rpg_Utils_Lingo::generateSentryName();
        }
        
        // Name is too long
        if (strlen($newSentryName) > Stephino_Rpg_Db_Model_Sentries::MAX_LENGTH_NAME) {
            throw new Exception(
                sprintf(
                    __('The name is too long, please keep it shorter than %d characters', 'stephino-rpg'), 
                    Stephino_Rpg_Db_Model_Sentries::MAX_LENGTH_NAME
                )
            );
        }
        
        // Update the sentry name
        if (false === Stephino_Rpg_Db::get()->tableUsers()->updateById(
            array(
                Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_NAME => $newSentryName
            ), 
            (int) $userData[Stephino_Rpg_Db_Table_Users::COL_ID]
        )) {
            throw new Exception(__('Could not update name, please try again later', 'stephino-rpg'));
        }
            
        return Stephino_Rpg_Renderer_Ajax::wrap(
            array(
                Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_NAME => $newSentryName
            )
        );
    }
    
    /**
     * Update a sentry image
     * 
     * @param array $data Data containing <ul>
     * <li><b>sentryImage</b> (string) Sentry image (base64)</li>
     * <li><b>sentryDefinition</b> (array) Sentry definition</li>
     * </ul>
     * @return array
     */
    public static function ajaxCustomize($data) {
        if (!Stephino_Rpg_Config::get()->core()->getSentryEnabled()) {
            throw new Exception(__('This feature is not available', 'stephino-rpg'));
        }
        
        // Invalid user data
        if (!is_array(Stephino_Rpg_TimeLapse::get()->userData())) {
            throw new Exception(__('User not initialized', 'stephino-rpg'));
        }
        
        // Prepare the image
        $sentryImage = isset($data[self::REQUEST_SENTRY_IMAGE]) ? trim($data[self::REQUEST_SENTRY_IMAGE]) : '';
        if (!preg_match('%^data\:image\/png\;base64,(.*)$%i', $sentryImage, $sentryImageMatch)) {
            throw new Exception(__('Image is missing', 'stephino-rpg'));
        }
        if (false === $sentryBinary = base64_decode($sentryImageMatch[1])) {
            throw new Exception(__('Image is missing', 'stephino-rpg'));
        }
        
        // Prepare the definition
        $sentryDefinition = isset($data[self::REQUEST_SENTRY_DEFINITION]) ? $data[self::REQUEST_SENTRY_DEFINITION] : array();
        if (!is_array($sentryDefinition)) {
            $sentryDefinition = array();
        }
        
        // Get the icon path, initializing the upload folder structure if needed
        $sentryIconPath = Stephino_Rpg_Db::get()->modelSentries()->getFilePath(
            Stephino_Rpg_TimeLapse::get()->userId(),
            Stephino_Rpg_Db_Model_Sentries::FILE_ICON,
            true
        );
        
        // Save the icon
        Stephino_Rpg_Utils_Folder::get()->fileSystem()->put_contents($sentryIconPath, $sentryBinary);
        
        // Save the definition
        Stephino_Rpg_Utils_Folder::get()->fileSystem()->put_contents(
            Stephino_Rpg_Db::get()->modelSentries()->getFilePath(
                Stephino_Rpg_TimeLapse::get()->userId(),
                Stephino_Rpg_Db_Model_Sentries::FILE_DEFINITION
            ),
            json_encode($sentryDefinition)
        );
        
        // Update the version
        Stephino_Rpg_Db::get()->tableUsers()->updateById(
            array(
                Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_VERSION => time()
            ), 
            Stephino_Rpg_TimeLapse::get()->userId()
        );
    }
    
    /**
     * Start a sentry challenge
     * 
     * @param array $data Data containing <ul>
     * <li><b>self::REQUEST_SENTRY_CHALLENGE</b> (string) Challenge type</li>
     * <li><b>self::REQUEST_SENTRY_CHALLENGE_USER_ID</b> (int) Challenge target user ID</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxChallengeStart($data) {
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
        
        // Cannot attack an inactive opponent
        if (0 !== (int) $opponentData[Stephino_Rpg_Db_Table_Users::COL_USER_SENTRY_ACTIVE]) {
            throw new Exception(__('Opponent cannot be attacked for now', 'stephino-rpg'));
        }
        
        // Prepare the sentry levels
        $sentryLevels = Stephino_Rpg_Db::get()->modelSentries()->getLevels($userData);
        
        // Prepare the cost data
        $costData = self::getCostDataSentry($sentryLevels[$sentryChallenge]);
        
        // Try to start the challenge
        self::spend($costData);
        
        // Prepare the challenge
        Stephino_Rpg_Db::get()->modelConvoys()->createSentryChallenge(
            Stephino_Rpg_TimeLapse::get()->userId(), 
            $sentryChallengeUserId, 
            $sentryChallenge
        );
    }
    
}

/*EOF*/