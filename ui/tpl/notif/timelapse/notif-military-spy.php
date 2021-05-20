<?php
/**
 * Template:Timelapse:Military:Spy
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
    /* @var $transportReturned boolean */
    /* @var $payloadArray array|null */
    /* @var $fromCityId int */
    /* @var $toCityId int */
    /* @var $currentUser boolean */
    list($transportReturned, $payloadArray, $fromCityId, $toCityId, $currentUser) = $notifData;
?>
    <div class="col-12">
        <?php if (!$transportReturned):?>
            <?php if ($currentUser && !is_array($payloadArray)):?>
                <?php 
                    echo sprintf(
                        esc_html__('A spy from %s tried to infiltrate %s', 'stephino-rpg'),
                        '<span data-click="dialog" data-click-args="dialogCityInfo,' . abs((int) $fromCityId) . '">'
                            . Stephino_Rpg_Db::get()->modelCities()->getName($fromCityId)
                        . '</span>',
                        '<span data-click="dialog" data-click-args="dialogCityInfo,' . abs((int) $toCityId) . '">'
                            . Stephino_Rpg_Db::get()->modelCities()->getName($toCityId)
                        . '</span>'
                    );
                ?>
            <?php else :?>
                <?php if (is_array($payloadArray)):?>
                    <?php 
                        echo sprintf(
                            esc_html__('Our spy managed to infiltrate into %s and will return to %s with details', 'stephino-rpg'),
                            '<span data-click="dialog" data-click-args="dialogCityInfo,' . abs((int) $toCityId) . '">'
                                . Stephino_Rpg_Db::get()->modelCities()->getName($toCityId)
                            . '</span>',
                            '<span data-click="dialog" data-click-args="dialogCityInfo,' . abs((int) $fromCityId) . '">'
                                . Stephino_Rpg_Db::get()->modelCities()->getName($fromCityId)
                            . '</span>'
                        );
                    ?>
                <?php else:?>
                    <?php 
                        echo sprintf(
                            esc_html__('Our spy was caught in %s', 'stephino-rpg'),
                            '<span data-click="dialog" data-click-args="dialogCityInfo,' . abs((int) $toCityId) . '">'
                                . Stephino_Rpg_Db::get()->modelCities()->getName($toCityId)
                            . '</span>'
                        );
                    ?>
                <?php endif;?>
            <?php endif;?>
        <?php else:?>
            <?php 
                echo sprintf(
                    esc_html__('Spy mission report for %s', 'stephino-rpg'),
                    '<span data-click="dialog" data-click-args="dialogCityInfo,' . abs((int) $toCityId) . '">'
                        . Stephino_Rpg_Db::get()->modelCities()->getName($toCityId)
                    . '</span>'
                );
            ?>
        <?php endif;?>
    </div>
    <?php if ($transportReturned):?>
        <?php if (is_array($payloadArray) 
            && isset($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_DATA])
            && is_array($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_DATA])):
                $resourceExact = false;
                $resourcesList = $payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_DATA][0];
                require Stephino_Rpg_Db_Model_Messages::getTemplatePath(
                    Stephino_Rpg_Db_Model_Messages::TEMPLATE_TIMELAPSE_LIST_RESOURCES
                );
                if (count($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_DATA][1])) {
                    $entityCountExact = false;
                    $entitiesList = $payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_DATA][1];
                    $entitiesCityId = $fromCityId;
                    require Stephino_Rpg_Db_Model_Messages::getTemplatePath(
                        Stephino_Rpg_Db_Model_Messages::TEMPLATE_TIMELAPSE_LIST_ENTITIES
                    );
                }
            ?>
        <?php else:?>
            <div class="row justify-content-center">
                <i><?php echo esc_html__('Could not gather any information', 'stephino-rpg');?></i>
            </div>
        <?php endif;?>
    <?php endif;?>
<?php endif;?>