<?php
/**
 * Template:Dialog:City
 * 
 * @title      City dialog
 * @desc       Template for the city workforce dialog
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

// Get the maximum available workers
$maxWorkers = floor($cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION]);
$currentWorkers = 0;
?>
<div 
    class="col-12 framed" 
    data-role="city-workers" 
    data-range-preview-bar="assigned-workers-bar"
    data-range-preview-label="assigned-workers-label"
    data-range-max="<?php echo $maxWorkers;?>">
    <div class="col-12">
        <h5>
            <span>
                <?php echo esc_html__('Assign workers', 'stephino-rpg');?>
            </span>
        </h5>
    </div>
    <div class="col-12">
        <?php 
            // Found buildings that need workers
            $foundBuildings = false;
            foreach (Stephino_Rpg_Config::get()->buildings()->getAll() as $buildingConfig):
                if (!$buildingConfig->getUseWorkers()) {
                    continue;
                }

                // Get the building data
                $buildingData = null;
                try {
                    list($buildingData) = Stephino_Rpg_Renderer_Ajax_Action::getBuildingInfo(
                        $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID], 
                        $buildingConfig->getId()
                    );
                    
                    // Get the current workers
                    if (is_array($buildingData)) {
                        $currentWorkers += $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL] > 0
                            ? (int) $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_WORKERS]
                            : 0;
                    }
                } catch (Exception $exc) {
                    Stephino_Rpg_Log::check() && Stephino_Rpg_Log::error(
                        "tpl/dialogs/city/city-workforce, city #{$cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]}, Building ({$buildingConfig->getId()}): {$exc->getMessage()}"
                    );
                }
                
                // Building not found
                if (null === $buildingData) {
                    continue;
                }
                
                // Found at least one building
                if (!$foundBuildings) {
                    $foundBuildings = true;
                }
                
                // Maximum number of workers
                $workersCapacity = null === $buildingData ? 0 : Stephino_Rpg_Utils_Config::getPolyValue(
                    $buildingConfig->getWorkersCapacityPolynomial(),
                    $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL], 
                    $buildingConfig->getWorkersCapacity()
                );
        ?>
            <div class="row mb-2">
                <div class="col-12 col-lg-3">
                    <span 
                        data-effect="help"
                        data-effect-args="<?php echo Stephino_Rpg_Config_Buildings::KEY;?>,<?php echo $buildingConfig->getId();?>">
                        <b><?php echo Stephino_Rpg_Utils_Lingo::escape($buildingConfig->getName());?></b>
                    </span>
                    <?php if ((int) $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL] > 0):?>
                        <?php echo esc_html__('level', 'stephino-rpg');?> <b><?php echo $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL];?></b>
                    <?php endif;?>
                </div>
                <div class="col-12 col-lg-9 text-center">
                    <?php if ((int) $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL] <= 0):?>
                        <i><?php echo esc_html__('Under construction', 'stephino-rpg');?></i>
                    <?php else:?>
                        <div class="row">
                            <input 
                                type="range" 
                                data-change="multiRange"
                                data-range-parent="city-workers"
                                data-range-ratio="1"
                                data-range-callback="cityWorkforce"
                                data-preview="true"
                                data-preview-label="/ <?php echo Stephino_Rpg_Utils_Lingo::isuFormat($workersCapacity);?>"
                                data-preview-label-title="<b><?php echo number_format($workersCapacity) . '</b>';?> workers"
                                name="<?php echo Stephino_Rpg_Config_Buildings::KEY . '_' . $buildingConfig->getId() ;?>" 
                                value="<?php echo $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_WORKERS];?>" 
                                min="0" 
                                max="<?php echo $workersCapacity;?>" />
                        </div>
                    <?php endif;?>
                </div>
            </div>
        <?php endforeach;?>
        <?php if (!$foundBuildings):?>
            <div class="row mb-2">
                <div class="col-12 text-center">
                    <?php echo esc_html__('There is no need for workers yet', 'stephino-rpg');?>
                </div>
            </div>
        <?php endif;?>
    </div>
    <?php if ($foundBuildings):?>
        <div class="col-12">
            <div class="row align-items-center">
                <div class="col-12 col-lg-9">
                    <div 
                        data-role="assigned-workers-bar"
                        title="<?php echo esc_attr__('Assigned workers', 'stephino-rpg');?>" 
                        data-effect="staticBar" 
                        data-effect-args="<?php echo $currentWorkers;?>,<?php echo $maxWorkers;?>">
                    </div>
                </div>
                <div class="col-12 col-lg-3">
                    <div class="label">
                        <span>
                            <b><span data-role="assigned-workers-label"><?php echo Stephino_Rpg_Utils_Lingo::isuFormat($currentWorkers);?></span></b> / 
                            <span 
                                title="<?php echo number_format($maxWorkers);?>"
                                data-html="true">
                                <?php echo Stephino_Rpg_Utils_Lingo::isuFormat($maxWorkers);?>
                            </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    <?php endif;?>
    <div class="col-12">
        <button class="btn w-100" data-click="goBack">
            <span>
                <b><?php echo esc_html__('Go Back', 'stephino-rpg');?></b>
            </span>
        </button>
    </div>
</div>