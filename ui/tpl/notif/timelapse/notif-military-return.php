<?php
/**
 * Template:Timelapse:Military
 * 
 * @title      Timelapse template - Military
 * @desc       Template for the Military messages
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

if (Stephino_Rpg_Db_Model_Messages::isValidNotifData($notifData, 3)):
    /* @var $payloadArray array|null */
    /* @var $fromCityId int */
    /* @var $toCityId int */
    list($payloadArray, $fromCityId, $toCityId) = $notifData;
?>
    <div class="col-12">
        <?php 
            echo sprintf(
                esc_html__('Army has returned to %s from attack on %s', 'stephino-rpg'),
                '<span data-click="dialog" data-click-args="dialogCityInfo,' . abs((int) $fromCityId) . '">'
                    . Stephino_Rpg_Db::get()->modelCities()->getName($fromCityId)
                . '</span>',
                '<span data-click="dialog" data-click-args="dialogCityInfo,' . abs((int) $toCityId) . '">'
                    . Stephino_Rpg_Db::get()->modelCities()->getName($toCityId)
                . '</span>'
            );
        ?>
    </div>
    <?php if (is_array($payloadArray) 
            && isset($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES])
            && is_array($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES])
            && count($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES])) {
            $entitiesList = $payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES];
            $entitiesCityId = $fromCityId;
            require Stephino_Rpg_Db_Model_Messages::getTemplatePath(
                Stephino_Rpg_Db_Model_Messages::TEMPLATE_TIMELAPSE_LIST_ENTITIES
            );
        }
    ?>
    <?php if (is_array($payloadArray) 
            && isset($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES])
            && is_array($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES])
            && count($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES])):?>
        <div class="col-12">
            <h6 class="heading"><span><?php echo esc_html__('Loot', 'stephino-rpg');?></span></h6>
        </div>
        <?php
            $resourcesList = $payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES];
            require Stephino_Rpg_Db_Model_Messages::getTemplatePath(
                Stephino_Rpg_Db_Model_Messages::TEMPLATE_TIMELAPSE_LIST_RESOURCES
            );
        ?>
    <?php endif;?>
<?php endif;?>