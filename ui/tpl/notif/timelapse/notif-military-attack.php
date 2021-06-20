<?php
/**
 * Template:Timelapse:Military:Attack
 * 
 * @title      Timelapse template - Military
 * @desc       Template for the Military messages
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

if (Stephino_Rpg_Db_Model_Messages::isValidNotifData($notifData, 6)):
    /* @var $attacker boolean */
    /* @var $attackStatus string */
    /* @var $payloadArray array|null */
    /* @var $fromCityId int */
    /* @var $toCityId int */
    /* @var $cityWalls boolean */
    list($attacker, $attackStatus, $payloadArray, $fromCityId, $toCityId, $cityWalls) = $notifData;
?>
    <div class="col-12">
        <h4>
            <?php 
                $convoySoundName = 'attackDraw';
                switch ($attackStatus) {
                    case Stephino_Rpg_TimeLapse_Convoys::ATTACK_DEFEAT_RETREAT: 
                        $convoySoundName = 'attackDefeat';
                        break;

                    case Stephino_Rpg_TimeLapse_Convoys::ATTACK_VICTORY_EASY: 
                        $convoySoundName = 'attackVictory';
                        break;

                    case Stephino_Rpg_TimeLapse_Convoys::ATTACK_DEFEAT_CRUSHING: 
                        $convoySoundName = 'attackDefeat';
                        break;

                    case Stephino_Rpg_TimeLapse_Convoys::ATTACK_VICTORY_CRUSHING: 
                        $convoySoundName = 'attackVictory';
                        break;

                    case Stephino_Rpg_TimeLapse_Convoys::ATTACK_DEFEAT_HEROIC: 
                        $convoySoundName = 'attackDefeat';
                        break;

                    case Stephino_Rpg_TimeLapse_Convoys::ATTACK_VICTORY_HEROIC: 
                        $convoySoundName = 'attackVictory';
                        break;
                }
            ?>
            <div class="icon-attack icon-attack-<?php echo $attackStatus;?>" data-effect="sound" data-effect-args="<?php echo $convoySoundName;?>"></div>
            <?php 
                echo sprintf(
                    $cityWalls
                        ? __('%s: defenses remained intact', 'stephino-rpg')
                        : __('%s: defenses were destroyed', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigCityName(true)
                );
            ?>
        </h4>
    </div>
    <div class="col-12">
        <?php if ($attacker):?>
            <?php 
                echo sprintf(
                    esc_html__('Our army from %s has attacked %s', 'stephino-rpg'),
                    '<span data-click="dialog" data-click-args="dialogCityInfo,' . abs((int) $fromCityId) . '">'
                        . Stephino_Rpg_Db::get()->modelCities()->getName($fromCityId)
                    . '</span>',
                    '<span data-click="dialog" data-click-args="dialogCityInfo,' . abs((int) $toCityId) . '">'
                        . Stephino_Rpg_Db::get()->modelCities()->getName($toCityId)
                    . '</span>'
                );
            ?>
        <?php else:?>
            <?php 
                echo sprintf(
                    esc_html__('A rival army from %s has attacked %s', 'stephino-rpg'),
                    '<span data-click="dialog" data-click-args="dialogCityInfo,' . abs((int) $fromCityId) . '">'
                        . Stephino_Rpg_Db::get()->modelCities()->getName($fromCityId)
                    . '</span>',
                    '<span data-click="dialog" data-click-args="dialogCityInfo,' . abs((int) $toCityId) . '">'
                        . Stephino_Rpg_Db::get()->modelCities()->getName($toCityId)
                    . '</span>'
                );
            ?>
        <?php endif;?>
    </div>
    <?php if (is_array($payloadArray) 
            && isset($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES])
            && is_array($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES])
            && count($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES])) {
            $entitiesList = $payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES];
            $entitiesCityId = $attacker ? $fromCityId : $toCityId;
            require Stephino_Rpg_Db_Model_Messages::getTemplatePath(
                Stephino_Rpg_Db_Model_Messages::TEMPLATE_TIMELAPSE_LIST_ENTITIES
            );
        }
    ?>
    <?php 
        // Show the defender how much they were looted
        if (!$attacker && is_array($payloadArray) 
            && isset($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES])
            && is_array($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES])
            && count($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES])):?>
        <div class="col-12">
            <h6 class="heading"><span><?php echo esc_html__('Loss', 'stephino-rpg');?></span></h6>
        </div>
        <?php
            $resourcesList = $payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES];
            $resourceLost = true;
            require Stephino_Rpg_Db_Model_Messages::getTemplatePath(
                Stephino_Rpg_Db_Model_Messages::TEMPLATE_TIMELAPSE_LIST_RESOURCES
            );
        ?>
    <?php endif;?>
<?php endif;?>