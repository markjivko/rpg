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

if (Stephino_Rpg_Db_Model_Messages::isValidNotifData($notifData, 5)):
    /* @var $attacker boolean */
    /* @var $attackStatus string */
    /* @var $payloadArray array|null */
    /* @var $fromCityId int */
    /* @var $toCityId int */
    list($attacker, $attackStatus, $payloadArray, $fromCityId, $toCityId) = $notifData;
?>
    <div class="col-12">
        <h4>
            <?php 
                $titleText = esc_html__('Draw', 'stephino-rpg');
                $convoySoundName = 'attackDraw';
                switch ($attackStatus) {
                    case Stephino_Rpg_TimeLapse_Convoys::ATTACK_DEFEAT_RETREAT: 
                        $titleText = esc_html__('Retreat', 'stephino-rpg');
                        $convoySoundName = 'attackDefeat';
                        break;

                    case Stephino_Rpg_TimeLapse_Convoys::ATTACK_VICTORY_EASY: 
                        $titleText = esc_html__('Easy victory', 'stephino-rpg');
                        $convoySoundName = 'attackVictory';
                        break;

                    case Stephino_Rpg_TimeLapse_Convoys::ATTACK_DEFEAT_CRUSHING: 
                        $titleText = esc_html__('Crushing defeat', 'stephino-rpg');
                        $convoySoundName = 'attackDefeat';
                        break;

                    case Stephino_Rpg_TimeLapse_Convoys::ATTACK_VICTORY_CRUSHING: 
                        $titleText = esc_html__('Crushing victory', 'stephino-rpg');
                        $convoySoundName = 'attackVictory';
                        break;

                    case Stephino_Rpg_TimeLapse_Convoys::ATTACK_DEFEAT_HEROIC: 
                        $titleText = esc_html__('Heroic defeat', 'stephino-rpg');
                        $convoySoundName = 'attackDefeat';
                        break;

                    case Stephino_Rpg_TimeLapse_Convoys::ATTACK_VICTORY_HEROIC: 
                        $titleText = esc_html__('Heroic victory', 'stephino-rpg');
                        $convoySoundName = 'attackVictory';
                        break;

                    case Stephino_Rpg_TimeLapse_Convoys::ATTACK_DEFEAT_BITTER: 
                        $titleText = esc_html__('Bitter defeat', 'stephino-rpg');
                        break;

                    case Stephino_Rpg_TimeLapse_Convoys::ATTACK_VICTORY_BITTER: 
                        $titleText = esc_html__('Bitter victory', 'stephino-rpg');
                        break;
                }
            ?>
            <div class="icon-attack icon-attack-<?php echo $attackStatus;?>" data-effect="sound" data-effect-args="<?php echo $convoySoundName;?>"></div>
            <?php echo $titleText;?>
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
            && count($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES])):
             ?>
        <div class="col-12">
            <h6 class="heading"><span><?php echo esc_html__('Army', 'stephino-rpg');?></span></h6>
        </div>
        <?php 
            $entitiesList = $payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES];
            $entitiesCityId = $attacker ? $fromCityId : $toCityId;
            require Stephino_Rpg_Db_Model_Messages::getTemplatePath(
                Stephino_Rpg_Db_Model_Messages::TEMPLATE_TIMELAPSE_LIST_ENTITIES
            );
        ?>
    <?php endif;?>
<?php endif;?>