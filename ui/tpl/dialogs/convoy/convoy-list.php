<?php
/**
 * Template:Dialog:Convoy List
 * 
 * @title      Convoy List dialog
 * @desc       Template for the convoy list dialog
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

// Prepare the labels
$unknownCityLabel = sprintf(
    esc_html__('%s: Unknown', 'stephino-rpg'), 
    Stephino_Rpg_Config::get()->core()->getConfigCityName(true)
);
$unknownIslandLabel = sprintf(
    esc_html__('%s: Unknown', 'stephino-rpg'), 
    Stephino_Rpg_Config::get()->core()->getConfigIslandName(true)
);
?>
<?php if (is_array($convoyList) && count($convoyList)):?>
    <?php 
        foreach($convoyList as $convoyRow):
            $fromCityInfo = Stephino_Rpg_Db::get()->tableCities()->getById($convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_FROM_CITY_ID]);
            $toCityInfo = Stephino_Rpg_Db::get()->tableCities()->getById($convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_CITY_ID]);
    ?>
    <div class="col-12 p-4 framed">
        <h5 class="mb-4">
            <span>
                <?php switch($convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TYPE]): case Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_ATTACK: ?>
                        <?php
                            echo sprintf(
                                $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_RETREAT_TIME]
                                    ? esc_html__('Army returing to %s from %s', 'stephino-rpg')
                                    : esc_html__('Army from %s to %s', 'stephino-rpg'),
                                is_array($fromCityInfo)
                                    ? '<span data-click="dialog" data-click-args="dialogCityInfo,' . $fromCityInfo[Stephino_Rpg_Db_Table_Cities::COL_ID] . '">'
                                            . Stephino_Rpg_Utils_Lingo::getCityName($fromCityInfo)
                                        . '</span>'
                                    : $unknownCityLabel,
                                is_array($toCityInfo)
                                    ? '<span data-click="dialog" data-click-args="dialogCityInfo,' . $toCityInfo[Stephino_Rpg_Db_Table_Cities::COL_ID] . '">'
                                            . Stephino_Rpg_Utils_Lingo::getCityName($toCityInfo)
                                        . '</span>'
                                    : $unknownCityLabel
                            );
                        ?>
                    <?php break; case Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_TRANSPORTER: ?>
                        <?php
                            echo sprintf(
                                $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_RETREAT_TIME]
                                    ? esc_html__('Transport returning to %s from %s', 'stephino-rpg')
                                    : esc_html__('Transport from %s to %s', 'stephino-rpg'),
                                is_array($fromCityInfo)
                                    ? '<span data-click="dialog" data-click-args="dialogCityInfo,' . $fromCityInfo[Stephino_Rpg_Db_Table_Cities::COL_ID] . '">'
                                            . Stephino_Rpg_Utils_Lingo::getCityName($fromCityInfo)
                                        . '</span>'
                                    : $unknownCityLabel,
                                is_array($toCityInfo)
                                    ? '<span data-click="dialog" data-click-args="dialogCityInfo,' . $toCityInfo[Stephino_Rpg_Db_Table_Cities::COL_ID] . '">'
                                            . Stephino_Rpg_Utils_Lingo::getCityName($toCityInfo)
                                        . '</span>'
                                    : $unknownCityLabel
                            );
                        ?>
                    <?php break; case Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_SPY: ?>
                        <?php
                            echo sprintf(
                                $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_RETREAT_TIME]
                                    ? esc_html__('Spy returning to %s from %s', 'stephino-rpg')
                                    : esc_html__('Spy from %s to %s', 'stephino-rpg'),
                                is_array($fromCityInfo)
                                    ? '<span data-click="dialog" data-click-args="dialogCityInfo,' . $fromCityInfo[Stephino_Rpg_Db_Table_Cities::COL_ID] . '">'
                                            . Stephino_Rpg_Utils_Lingo::getCityName($fromCityInfo)
                                        . '</span>'
                                    : $unknownCityLabel,
                                is_array($toCityInfo)
                                    ? '<span data-click="dialog" data-click-args="dialogCityInfo,' . $toCityInfo[Stephino_Rpg_Db_Table_Cities::COL_ID] . '">'
                                            . Stephino_Rpg_Utils_Lingo::getCityName($toCityInfo)
                                        . '</span>'
                                    : $unknownCityLabel
                            );
                        ?>
                    <?php break; case Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_COLONIZER: ?>
                        <?php 
                            // Get the island data
                            $islandData = null;
                            try {
                                list($islandData) = Stephino_Rpg_Renderer_Ajax_Action::getIslandInfo($convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TO_ISLAND_ID]);
                            } catch (Exception $ex) {}
                            echo sprintf(
                                esc_html__('Colonizer from %s to vacant lot on %s', 'stephino-rpg'),
                                is_array($fromCityInfo)
                                    ? '<span data-click="dialog" data-click-args="dialogCityInfo,' . $fromCityInfo[Stephino_Rpg_Db_Table_Cities::COL_ID] . '">'
                                            . Stephino_Rpg_Utils_Lingo::getCityName($fromCityInfo)
                                        . '</span>'
                                    : $unknownCityLabel,
                                null !== $islandData
                                    ? '<span data-click="navigate" data-click-args="island,' . $islandData[Stephino_Rpg_Db_Table_Islands::COL_ID] . '">'
                                            . $islandData[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_NAME]
                                        . '</span>'
                                    : '<b>' . $unknownIslandLabel . '</b>'
                            );
                        ?>
                    <?php break; default: ?>
                        <?php
                            echo sprintf(
                                $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_RETREAT_TIME]
                                    ? esc_html__('Convoy returning to %s from %s', 'stephino-rpg')
                                    : esc_html__('Convoy from %s to %s', 'stephino-rpg'),
                                is_array($fromCityInfo)
                                    ? '<span data-click="dialog" data-click-args="dialogCityInfo,' . $fromCityInfo[Stephino_Rpg_Db_Table_Cities::COL_ID] . '">'
                                            . Stephino_Rpg_Utils_Lingo::getCityName($fromCityInfo)
                                        . '</span>'
                                    : $unknownCityLabel,
                                is_array($toCityInfo)
                                    ? '<span data-click="dialog" data-click-args="dialogCityInfo,' . $toCityInfo[Stephino_Rpg_Db_Table_Cities::COL_ID] . '">'
                                            . Stephino_Rpg_Utils_Lingo::getCityName($toCityInfo)
                                        . '</span>'
                                    : $unknownCityLabel
                            );
                        ?>
                <?php break; endswitch; ?>
            </span>
        </h5>
        <div class="row col-12">
            <div class="col-12 col-md-10">
                <?php 
                    // Get the time left
                    $timeLeft = ($convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_RETREAT_TIME] > 0 
                        ? $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_RETREAT_TIME]
                        : $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TIME]) - time();

                    // Get the total time to the attack
                    $timeTotal = $convoyRow[Stephino_Rpg_Db_Table_Convoys::COL_CONVOY_TRAVEL_DURATION];
                ?>
                <div 
                    data-effect="countdownBar" 
                    data-effect-args="<?php echo ($timeLeft . ',' . $timeTotal);?>">
                </div>
            </div>
            <div class="col-12 col-md-2 text-center">
                <span 
                    data-effect="countdownTime" 
                    data-effect-args="<?php echo ($timeLeft . ',' . $timeTotal);?>">
                </span>
            </div>
        </div>
    </div>
    <?php endforeach;?>
<?php else :?>
    <div class="col-12 p-4 framed text-center">
        <?php echo esc_html__('You have no active convoys', 'stephino-rpg');?>
    </div>
<?php endif;?>