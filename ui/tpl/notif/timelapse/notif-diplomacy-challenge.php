<?php
/**
 * Template:Timelapse:Diplomacy
 * 
 * @title      Timelapse template - Diplomacy
 * @desc       Template for the Diplomacy messages
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

if (Stephino_Rpg_Db_Model_Messages::isValidNotifData($notifData, 4)):
    /* @var $sentryReturned boolean */
    /* @var $payloadArray array (challenge type, levels, challenge successful) */
    /* @var $fromCityId int */
    /* @var $toCityId int */
    list($sentryReturned, $payloadArray, $fromCityId, $toCityId) = $notifData;

    // Prepare the challenge labels
    $challengeLabels = Stephino_Rpg_Db::get()->modelSentries()->getLabels();
    $challengeType = is_array($payloadArray) && isset($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_DATA])
        && is_array($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_DATA]) 
        && isset($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_DATA][0])
        ? $payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_DATA][0]
        : Stephino_Rpg_Db_Model_Sentries::CHALLENGE_ATTACK;
    $challengeLabel = isset($challengeLabels[$challengeType]) 
        ? $challengeLabels[$challengeType] 
        : $challengeLabels[Stephino_Rpg_Db_Model_Sentries::CHALLENGE_ATTACK];

    // Successful challenge
    $challengeSuccessful = is_array($payloadArray) && isset($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_DATA])
        && is_array($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_DATA]) 
        && isset($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_DATA][2])
        && $payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_DATA][2];
    
    // Prepare the sound
    $challengeSoundName = ($sentryReturned && $challengeSuccessful) || (!$sentryReturned && !$challengeSuccessful)
        ? 'attackVictory'
        : 'attackDefeat';
?>
    <div class="col-12">
        <div data-effect="sound" data-effect-args="<?php echo $challengeSoundName;?>"></div>
        <?php if ($sentryReturned):?>
            <?php 
                echo sprintf(
                    $challengeSuccessful
                        ? __('We were successful in defeating our opponent in %s and improved %s', 'stephino-rpg')
                        : __('We could not defeat our opponent in %s and improve %s', 'stephino-rpg'),
                    '<span data-click="dialog" data-click-args="dialogCityInfo,' . abs((int) $toCityId) . '">'
                        . Stephino_Rpg_Db::get()->modelCities()->getName($toCityId)
                    . '</span>',
                    '<b>' . esc_html($challengeLabel) . '</b>'
                );
            
                // Show the reward
                if ($challengeSuccessful && is_array($payloadArray) 
                    && isset($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES])
                    && is_array($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES])
                    && count($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES])):?>
                <div class="col-12">
                    <h6 class="heading"><span><?php echo esc_html__('Reward for challenge', 'stephino-rpg');?></span></h6>
                </div>
                <?php
                    $resourcesList = $payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES];
                    require Stephino_Rpg_Db_Model_Messages::getTemplatePath(
                        Stephino_Rpg_Db_Model_Messages::TEMPLATE_TIMELAPSE_LIST_RESOURCES
                    );
                ?>
            <?php endif;?>
        <?php else:?>
            <?php 
                echo sprintf(
                    !$challengeSuccessful
                        ? __('We were successful in defending against a challenger from %s', 'stephino-rpg')
                        : __('We could not defend against a challenger from %s', 'stephino-rpg'),
                    '<span data-click="dialog" data-click-args="dialogCityInfo,' . abs((int) $fromCityId) . '">'
                        . Stephino_Rpg_Db::get()->modelCities()->getName($fromCityId)
                    . '</span>'
                );
            
                // Show the defender how much they were looted
                if (!$challengeSuccessful && is_array($payloadArray) 
                    && isset($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES])
                    && is_array($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES])
                    && count($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES])):
                    
                    // Prepare the resources
                    $resourcesList = $payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES];
                    
                    // Hide the gem reward
                    unset($resourcesList[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM]);
                    if (count($resourcesList)):
            ?>
                <div class="col-12">
                    <h6 class="heading"><span><?php echo esc_html__('Loss', 'stephino-rpg');?></span></h6>
                </div>
                <?php
                    // Mark these resources as lost
                    $resourceLost = true;
                    require Stephino_Rpg_Db_Model_Messages::getTemplatePath(
                        Stephino_Rpg_Db_Model_Messages::TEMPLATE_TIMELAPSE_LIST_RESOURCES
                    );
                endif; 
            endif; ?>
        <?php endif;?>
    </div>
<?php endif;?>