<?php
/**
 * Template:Dialog:City
 * 
 * @title      City dialog
 * @desc       Template for the city government dialog
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

foreach (Stephino_Rpg_Config::get()->governments()->getAll() as $governmentConfig):
    // Get the production data
    $productionData = Stephino_Rpg_Renderer_Ajax_Action::getProductionData(
        $governmentConfig,
        $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL],
        $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID]
    );

    // Get the requirements
    list($requirements, $requirementsMet) = Stephino_Rpg_Renderer_Ajax_Action::getRequirements(
        $governmentConfig, 
        $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_ID]
    );

    // Get the cost data
    $costData = Stephino_Rpg_Renderer_Ajax_Action::getCostData(
        $governmentConfig, 
        $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL]
    );
?>
    <div class="framed <?php if($governmentConfig->getId() == $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_GOVERNMENT_CONFIG_ID]):?>active<?php endif;?>">
        <div class="col-12">
            <h5>
                <span>
                    <span 
                        data-effect="help"
                        data-effect-args="<?php echo Stephino_Rpg_Config_Governments::KEY;?>,<?php echo $governmentConfig->getId();?>">
                        <?php echo $governmentConfig->getName(true);?>
                    </span>
                </span>
            </h5>
        </div>
        <div class="col-12 row align-items-center m-0">
            <div class="card card-body bg-dark mb-4">
                <?php if (strlen($governmentConfig->getDescription())):?>
                    <?php echo Stephino_Rpg_Utils_Lingo::markdown($governmentConfig->getDescription());?>
                <?php else:?>
                    <i class="w-100 text-center"><?php echo esc_html__('no details available', 'stephino-rpg');?></i>
                <?php endif;?>
            </div>
        </div>
        <div class="col-12 row align-items-center m-0">
            <div class="col-12 col-lg-3 text-center">
                <div 
                    class="building-entity-icon framed mt-4 <?php if (!$requirementsMet):?>disabled<?php endif;?>" 
                    data-click="helpDialog"
                    data-click-args="<?php echo Stephino_Rpg_Config_Governments::KEY;?>,<?php echo $governmentConfig->getId();?>"
                    data-effect="background" 
                    data-effect-args="<?php echo Stephino_Rpg_Config_Governments::KEY;?>,<?php echo $governmentConfig->getId();?>">
                    <span 
                        data-effect="help"
                        data-effect-args="<?php echo Stephino_Rpg_Config_Governments::KEY;?>,<?php echo $governmentConfig->getId();?>">
                        <?php echo $governmentConfig->getName(true);?>
                    </span>
                    <?php if ($governmentConfig->getId() == $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_GOVERNMENT_CONFIG_ID]):?>
                        <span class="label">
                            <span><?php echo esc_html__('In power', 'stephino-rpg');?></span>
                        </span>
                    <?php endif;?>
                </div>
            </div>
            <div class="col-12 col-lg-9">
                <?php if ($requirementsMet):?>
                    <?php 
                        require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                            Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_PRODUCTION
                        );
                        if ($governmentConfig->getId() != $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_GOVERNMENT_CONFIG_ID]) {
                            $costTitle = esc_html__('Coup costs', 'stephino-rpg');
                            require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                                Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_COSTS
                            );
                        }
                    ?>
                <?php else:?>
                    <?php 
                        require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                            Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_REQUIREMENTS
                        );
                    ?>
                <?php endif;?>
            </div>
        </div>
        <?php if ($requirementsMet && $governmentConfig->getId() != $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_GOVERNMENT_CONFIG_ID]):?>
            <div class="col-12">
                <button 
                    class="btn btn-warning w-100" 
                    data-click="cityGovernmentSetButton" 
                    data-click-args="<?php echo $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_ID];?>,<?php echo $governmentConfig->getId();?>">
                    <span><?php
                        echo sprintf(
                            esc_html__('Institute %s', 'stephino-rpg'),
                            '<b>' . $governmentConfig->getName(true) . '</b>'
                        );
                    ?></span>
                </button>
            </div>
        <?php endif;?>
    </div>
<?php endforeach;?>