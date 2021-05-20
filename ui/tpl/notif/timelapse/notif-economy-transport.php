<?php
/**
 * Template:Timelapse:Economy:Transport
 * 
 * @title      Timelapse template - Economy
 * @desc       Template for the Economy messages
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

if (Stephino_Rpg_Db_Model_Messages::isValidNotifData($notifData, 4)):
    /* @var $transportReturned boolean */
    /* @var $payloadArray array|null */
    /* @var $fromCityId int */
    /* @var $toCityId int */
    list($transportReturned, $payloadArray, $fromCityId, $toCityId) = $notifData;
?>
    <div class="col-12">
        <?php if ($transportReturned):?>
            <?php 
                echo sprintf(
                    esc_html__('Transport returned from %s to %s', 'stephino-rpg'),
                    '<span data-click="dialog" data-click-args="dialogCityInfo,' . $toCityId . '">'
                        . Stephino_Rpg_Db::get()->modelCities()->getName($toCityId)
                    . '</span>',
                    '<span data-click="dialog" data-click-args="dialogCityInfo,' . $fromCityId . '">'
                        . Stephino_Rpg_Db::get()->modelCities()->getName($fromCityId)
                    . '</span>'
                );
            ?>
        <?php else:?>
            <?php 
                echo sprintf(
                    esc_html__('Transport from %s arrived at %s', 'stephino-rpg'),
                    '<span data-click="dialog" data-click-args="dialogCityInfo,' . $fromCityId . '">'
                        . Stephino_Rpg_Db::get()->modelCities()->getName($fromCityId)
                    . '</span>',
                    '<span data-click="dialog" data-click-args="dialogCityInfo,' . $toCityId . '">'
                        . Stephino_Rpg_Db::get()->modelCities()->getName($toCityId)
                    . '</span>'
                );
            ?>
        <?php endif;?>
    </div>
    <?php if (isset($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES]) 
        && is_array($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES])
        && count($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES])) {
            $entitiesList = $payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES];
            $entitiesCityId = $transportReturned ? $fromCityId : $toCityId;
            require Stephino_Rpg_Db_Model_Messages::getTemplatePath(
                Stephino_Rpg_Db_Model_Messages::TEMPLATE_TIMELAPSE_LIST_ENTITIES
            );
        }
    ?>
    <?php if (isset($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES])
        && is_array($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES])
        && count($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES])) {
            $resourcesList = $payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES];
            require Stephino_Rpg_Db_Model_Messages::getTemplatePath(
                Stephino_Rpg_Db_Model_Messages::TEMPLATE_TIMELAPSE_LIST_RESOURCES
            );
        }
    ?>
<?php endif;?>