<?php
/**
 * Template:Timelapse:Military
 * 
 * @title      Timelapse template - Military
 * @desc       Template for the Military messages
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/**
 * @param int    $userId   User ID
 * @param string $itemType Item Type 
 * @param int    $itemId   Item ID
 * @param array  $itemData Item Data
 */
?>
<div class="col-12 p-2 framed">
    <?php switch($itemType): case Stephino_Rpg_TimeLapse_Convoys::ACTION_SPY: ?>
        <?php list($transportReturned, $payloadArray, $convoyRow) = $itemData;?>
        <div class="col-12 p-2 text-center">
            <h5>
                <?php if (!$transportReturned):?>
                    <?php if ($userId == $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID] && !is_array($payloadArray)):?>
                        <?php 
                            echo sprintf(
                                esc_html__('A spy from %s tried to infiltrate %s', 'stephino-rpg'),
                                '<span data-click="dialog" data-click-args="dialogCityInfo,' . $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_CITY_ID] . '">'
                                    . Stephino_Rpg_Db::get()->modelCities()->getName($convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_CITY_ID])
                                . '</span>',
                                '<span data-click="dialog" data-click-args="dialogCityInfo,' . $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID] . '">'
                                    . Stephino_Rpg_Db::get()->modelCities()->getName($convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID])
                                . '</span>'
                            );
                        ?>
                    <?php else :?>
                        <?php if (is_array($payloadArray)):?>
                            <?php 
                                echo sprintf(
                                    esc_html__('Our spy managed to infiltrate into %s and will return to %s with details', 'stephino-rpg'),
                                    '<span data-click="dialog" data-click-args="dialogCityInfo,' . $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID] . '">'
                                        . Stephino_Rpg_Db::get()->modelCities()->getName($convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID])
                                    . '</span>',
                                    '<span data-click="dialog" data-click-args="dialogCityInfo,' . $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_CITY_ID] . '">'
                                        . Stephino_Rpg_Db::get()->modelCities()->getName($convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_CITY_ID])
                                    . '</span>'
                                );
                            ?>
                        <?php else:?>
                            <?php 
                                echo sprintf(
                                    esc_html__('Our spy was caught in %s', 'stephino-rpg'),
                                    '<span data-click="dialog" data-click-args="dialogCityInfo,' . $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID] . '">'
                                        . Stephino_Rpg_Db::get()->modelCities()->getName($convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID])
                                    . '</span>'
                                );
                            ?>
                        <?php endif;?>
                    <?php endif;?>
                <?php else:?>
                    <?php 
                        echo sprintf(
                            esc_html__('Spy mission report for %s', 'stephino-rpg'),
                            '<span data-click="dialog" data-click-args="dialogCityInfo,' . $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID] . '">'
                                . Stephino_Rpg_Db::get()->modelCities()->getName($convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID])
                            . '</span>'
                        );
                    ?>
                <?php endif;?>
            </h5>
        </div>
        <?php if ($transportReturned):?>
            <?php if (is_array($payloadArray) 
                && isset($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_DATA])
                && is_array($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_DATA])):
                    $resourceExact = false;
                    $resourcesList = $payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_DATA][0];
                    require Stephino_Rpg_TimeLapse::getTemplatePath(
                        Stephino_Rpg_TimeLapse::TEMPLATE_COMMON_LIST_RESOURCES
                    );
                    if (count($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_DATA][1])) {
                        $entityCountExact = false;
                        $entitiesList = $payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_DATA][1];
                        require Stephino_Rpg_TimeLapse::getTemplatePath(
                            Stephino_Rpg_TimeLapse::TEMPLATE_COMMON_LIST_ENTITIES
                        );
                    }
                ?>
            <?php else:?>
                <div class="row justify-content-center text-center">
                    <i><?php echo esc_html__('Could not gather any information', 'stephino-rpg');?></i>
                </div>
            <?php endif;?>
        <?php endif;?>
    <?php break; case Stephino_Rpg_TimeLapse_Convoys::ACTION_ATTACK: ?>
        <?php list($attacker, $attackStatus, $payloadArray, $convoyRow) = $itemData;?>
        <div class="col-12 p-2 text-center">
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
                <div 
                    class="icon-attack icon-attack-<?php echo $attackStatus;?>"
                    data-effect="sound"
                    data-effect-args="<?php echo $convoySoundName;?>">
                </div>
                <?php echo $titleText;?>
            </h4>
        </div>
        <div class="col-12 p-2 text-center">
            <h5>
                <?php if ($attacker):?>
                    <?php 
                        echo sprintf(
                            esc_html__('Our army from %s has attacked %s', 'stephino-rpg'),
                            '<span data-click="dialog" data-click-args="dialogCityInfo,' . $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_CITY_ID] . '">'
                                . Stephino_Rpg_Db::get()->modelCities()->getName($convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_CITY_ID])
                            . '</span>',
                            '<span data-click="dialog" data-click-args="dialogCityInfo,' . $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID] . '">'
                                . Stephino_Rpg_Db::get()->modelCities()->getName($convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID])
                            . '</span>'
                        );
                    ?>
                <?php else:?>
                    <?php 
                        echo sprintf(
                            esc_html__('A rival army from %s has attacked %s', 'stephino-rpg'),
                            '<span data-click="dialog" data-click-args="dialogCityInfo,' . $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_CITY_ID] . '">'
                                . Stephino_Rpg_Db::get()->modelCities()->getName($convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_CITY_ID])
                            . '</span>',
                            '<span data-click="dialog" data-click-args="dialogCityInfo,' . $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID] . '">'
                                . Stephino_Rpg_Db::get()->modelCities()->getName($convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID])
                            . '</span>'
                        );
                    ?>
                <?php endif;?>
            </h5>
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
                require Stephino_Rpg_TimeLapse::getTemplatePath(
                    Stephino_Rpg_TimeLapse::TEMPLATE_COMMON_LIST_ENTITIES
                );
            ?>
        <?php endif;?>
    <?php break; case Stephino_Rpg_TimeLapse_Convoys::ACTION_RETURN: ?>
        <?php list(, , $payloadArray, $convoyRow) = $itemData;?>
        <div class="col-12 p-2 text-center">
            <h5>
                <?php 
                    echo sprintf(
                        esc_html__('Army has returned to %s from attack on %s', 'stephino-rpg'),
                        '<span data-click="dialog" data-click-args="dialogCityInfo,' . $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_CITY_ID] . '">'
                            . Stephino_Rpg_Db::get()->modelCities()->getName($convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_CITY_ID])
                        . '</span>',
                        '<span data-click="dialog" data-click-args="dialogCityInfo,' . $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID] . '">'
                            . Stephino_Rpg_Db::get()->modelCities()->getName($convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID])
                        . '</span>'
                    );
                ?>
            </h5>
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
                require Stephino_Rpg_TimeLapse::getTemplatePath(
                    Stephino_Rpg_TimeLapse::TEMPLATE_COMMON_LIST_ENTITIES
                );
            ?>
        <?php endif;?>
        <?php if (is_array($payloadArray) 
                && isset($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES])
                && is_array($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES])
                && count($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES])):?>
            <div class="col-12">
                <h6 class="heading"><span><?php echo esc_html__('Loot', 'stephino-rpg');?></span></h6>
            </div>
            <?php
                $resourcesList = $payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES];
                require Stephino_Rpg_TimeLapse::getTemplatePath(
                    Stephino_Rpg_TimeLapse::TEMPLATE_COMMON_LIST_RESOURCES
                );
            ?>
        <?php endif;?>
    <?php break; default: ?>
        <div class="col-12 p-2 text-center">
            <?php echo esc_html__('Unknown military action', 'stephino-rpg');?>
            <?php Stephino_Rpg_Log::warning($itemType, $itemData);?>
        </div>
    <?php break; endswitch; ?>
</div>