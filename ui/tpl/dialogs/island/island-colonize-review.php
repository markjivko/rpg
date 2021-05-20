<?php
/**
 * Template:Dialog:Island
 * 
 * @title      Island slot colonization dialog
 * @desc       Template for colonization review
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $entityConfig Stephino_Rpg_Config_Unit|Stephino_Rpg_Config_Ship */
?>
<?php if (count($colonizersList)):?>
    <?php foreach ($colonizersList as $colonizerCityId => list($cityData, $cityEntities)):?>
        <div class="col-12 framed">
            <div class="row">
                <h5>
                    <?php echo esc_html__('Colonize from', 'stephino-rpg');?>
                    <span
                        data-click="dialog"
                        data-click-args="dialogCityInfo,<?php echo $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID];?>">
                        <?php echo Stephino_Rpg_Utils_Lingo::getCityName($cityData);?>
                    </span>
                </h5>
            </div>
            <?php 
                // Get the travel time
                list(, $travelTime) = Stephino_Rpg_Db::get()->modelConvoys()->getTravelInfo(
                    $cityData, 
                    array(
                        Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID    => $islandId,
                        Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_INDEX => $cityIndex,
                    )
                );
                
                // Prepare the final time costs
                $costTime = $colonizationTime + $travelTime;

                // Set the section title
                $costTitle = esc_html__('Colonization costs', 'stephino-rpg');
                require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                    Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_COSTS
                );
            ?>
            <?php 
                foreach ($cityEntities as list($entityRow, $entityConfig)):
                    // Get the item card details
                    list($itemCardFn, $itemCardArgs) = Stephino_Rpg_Utils_Config::getItemCardAttributes($entityConfig);
            ?>
            <div class="row justify-content-center">
                <div class="col-12 text-center">
                    <div 
                        class="item-card framed mt-4" 
                        data-click="<?php echo $itemCardFn;?>"
                        data-click-args="<?php echo $itemCardArgs;?>"
                        data-effect="background" 
                        data-effect-args="<?php echo $entityConfig->keyCollection();?>,<?php echo $entityConfig->getId();?>">
                        <span>
                            <?php echo $entityConfig->getName(true);?>
                        </span>
                    </div>
                    <button
                        class="btn btn-warning w-100"
                        data-click="cityColonizeButton"
                        data-click-args="<?php echo $islandId;?>,<?php echo $islandSlot;?>,<?php echo $colonizerCityId;?>,<?php echo $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ID];?>">
                        <span><?php echo esc_html__('Send', 'stephino-rpg');?> <b><?php echo $entityConfig->getName(true);?></b></span>
                    </button>
                </div>
            </div>
            <?php endforeach;?>
        </div>
    <?php endforeach;?>
<?php else:?>
    <?php 
        // Template variables
        $entityPrepareCapability = Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_COLONIZER;
        $entityPrepareSingular   = esc_html__('Expand your empire with:', 'stephino-rpg');
        $entityPreparePlural     = esc_html__('Expand your empire with one of the following:', 'stephino-rpg');
        $entityPrepareNotAllowed = esc_html__('This game does not allow colonization', 'stephino-rpg');

        require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
            Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_ENTITY_PREPARE
        );
    ?>
<?php endif; ?>