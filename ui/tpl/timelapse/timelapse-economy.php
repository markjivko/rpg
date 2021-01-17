<?php
/**
 * Template:Timelapse:Economy
 * 
 * @title      Timelapse template - Economy
 * @desc       Template for the Economy messages
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
<div class="col-12">
    <?php switch($itemType): case Stephino_Rpg_TimeLapse_Convoys::ACTION_TRANSPORT: ?>
        <?php list($transportReturned, $payloadArray, $convoyRow) = $itemData;?>
        <div class="col-12 p-2 text-center">
            <h5>
                <?php if ($transportReturned):?>
                    <?php 
                        echo sprintf(
                            esc_html__('Transport returned from %s to %s', 'stephino-rpg'),
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
                            esc_html__('Transport from %s arrived at %s', 'stephino-rpg'),
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
        <?php if (isset($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES]) 
            && is_array($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES])
            && count($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES])) {
                $entitiesList = $payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES];
                require Stephino_Rpg_TimeLapse::getTemplatePath(
                    Stephino_Rpg_TimeLapse::TEMPLATE_COMMON_LIST_ENTITIES
                );
            }
        ?>
        <?php if (isset($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES])
            && is_array($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES])
            && count($payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES])) {
                $resourcesList = $payloadArray[Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES];
                require Stephino_Rpg_TimeLapse::getTemplatePath(
                    Stephino_Rpg_TimeLapse::TEMPLATE_COMMON_LIST_RESOURCES
                );
            }
        ?>
        <?php break; case Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_BUILDING: ?>
            <?php $buildingConfig = Stephino_Rpg_Config::get()->buildings()->getById($itemData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CONFIG_ID]);?>
            <div class="row mt-0 framed p-0">
                <div data-effect="parallax" data-effect-args="<?php echo Stephino_Rpg_Config_Buildings::KEY;?>,<?php echo $buildingConfig->getId();?>"></div>
                <div class="page-help">
                    <span 
                        data-effect="help"
                        data-effect-args="<?php echo Stephino_Rpg_Config_Buildings::KEY;?>,<?php echo $buildingConfig->getId();?>">
                        <?php echo $buildingConfig->getName(true);?>
                    </span>
                </div>
            </div>
            <div class="col-12 p-2 text-center">
                <h5 class="mt-2">
                    <?php 
                        echo (
                            $itemData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL] <= 1 
                                ? sprintf(
                                    esc_html__('%s constructed in %s', 'stephino-rpg'),
                                    '<b>' . $buildingConfig->getName(true) . '</b>',
                                    '<span data-click="dialog" data-click-args="dialogCityInfo,' . $itemData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CITY_ID] . '">'
                                        . Stephino_Rpg_Db::get()->modelCities()->getName($itemData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CITY_ID])
                                    . '</span>'
                                )
                                : sprintf(
                                    esc_html__('%s upgraded to level %s in %s', 'stephino-rpg'),
                                    '<b>' . $buildingConfig->getName(true) . '</b>',
                                    $itemData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL],
                                    '<span data-click="dialog" data-click-args="dialogCityInfo,' . $itemData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CITY_ID] . '">'
                                        . Stephino_Rpg_Db::get()->modelCities()->getName($itemData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CITY_ID])
                                    . '</span>'
                                )
                        );
                    ?>
                    
                </h5>
            </div>
        <?php break; case Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_UNIT: case Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_SHIP: ?>
            <?php
                // Entities also provide the number of new recruits
                list($entityData) = $itemData;
                
                // Get the entity configuration
                $entityConfig = Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_UNIT == $itemType
                    ? Stephino_Rpg_Config::get()->units()->getById($entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID])
                    : Stephino_Rpg_Config::get()->ships()->getById($entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]);

                // Get the entity key
                $entityKey = Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_UNIT == $itemType
                    ? Stephino_Rpg_Config_Units::KEY
                    : Stephino_Rpg_Config_Ships::KEY;
            ?>
            <div class="row mt-0 framed p-0">
                <div data-effect="parallax" data-effect-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>"></div>
                <div class="page-help">
                    <span 
                        data-effect="help"
                        data-effect-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>">
                        <?php echo $entityConfig->getName(true);?>
                    </span>
                </div>
            </div>
            <div class="col-12 p-2 text-center">
                <h5 class="mt-2">
                    <?php 
                        echo sprintf(
                            Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_UNIT == $itemType
                                ? esc_html__('%s recruitment finished in %s', 'stephino-rpg')
                                : esc_html__('%s construction finished in %s', 'stephino-rpg'),
                            '<b>' . $entityConfig->getName(true) . '</b>',
                            '<span data-click="dialog" data-click-args="dialogCityInfo,' . $entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CITY_ID] . '">'
                                . Stephino_Rpg_Db::get()->modelCities()->getName($entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CITY_ID])
                            . '</span>'
                        );
                    ?>
                </h5>
            </div>
        <?php break; default: ?>
        <div class="col-12 p-2 text-center">
            <?php echo esc_html__('Unknown economic action', 'stephino-rpg');?>
            <?php Stephino_Rpg_Log::warning($itemType, $itemData);?>
        </div>
    <?php break; endswitch; ?>
</div>