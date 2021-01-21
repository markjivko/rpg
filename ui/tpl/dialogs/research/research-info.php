<?php
/**
 * Template:Dialog:Research Area
 * 
 * @title      Research area dialog
 * @desc       Template for the building research area dialog
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $researchAreaConfig Stephino_Rpg_Config_ResearchArea */
/* @var $researchFieldConfig Stephino_Rpg_Config_ResearchField */
?>
<div class="row mt-0 framed p-0">
    <div data-effect="parallax" data-effect-args="<?php echo Stephino_Rpg_Config_ResearchAreas::KEY;?>,<?php echo $researchAreaConfig->getId();?>"></div>
    <div class="page-help">
        <span 
            data-effect="help"
            data-effect-args="<?php echo Stephino_Rpg_Config_ResearchAreas::KEY;?>,<?php echo $researchAreaConfig->getId();?>">
            <?php echo $researchAreaConfig->getName(true);?>
        </span>
    </div>
</div>
<?php if (count($researchFieldConfigs)):?>
    <?php 
        foreach ($researchFieldConfigs as $rfId => $researchFieldConfig): 
            // Get the Research Field requirements
            list($requirements, $requirementsMet) = Stephino_Rpg_Renderer_Ajax_Action::getRequirements(
                $researchFieldConfig, 
                $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID]
            );
        
            // Reached the maximum level
            $researchFieldMaxLevelReached = false;
            
            // No production defined, so stop at max unlocks
            if (!count($researchProductionData[$researchFieldConfig->getId()])) {
                // Get the maximum level at which this r.f. unlocks an item
                $researchFieldMaxLevel = Stephino_Rpg_Utils_Config::getUnlocksMaxLevel($researchFieldConfig);
                
                // Max level reached
                if (isset($researchFieldData[$rfId]) && $researchFieldData[$rfId][Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_LEVEL] >= $researchFieldMaxLevel) {
                    $researchFieldMaxLevelReached = true;
                }
            }
    ?>
        <div class="framed <?php if ($rfId == $researchFieldConfigId):?>active<?php endif;?>">
            <div class="col-12">
                <h5>
                    <span 
                        data-effect="help"
                        data-effect-args="<?php echo Stephino_Rpg_Config_ResearchFields::KEY;?>,<?php echo $researchFieldConfig->getId();?>">
                        <?php echo $researchFieldConfig->getName(true);?>
                    </span>
                </h5>
            </div>
            <div class="col-12 row align-items-center m-0">
                <div class="col-12 col-lg-3 text-center">
                    <div 
                        class="building-research-icon framed mt-4 <?php if (!$requirementsMet):?>disabled<?php endif;?>" 
                        data-click="helpDialog"
                        data-click-args="<?php echo Stephino_Rpg_Config_ResearchFields::KEY;?>,<?php echo $researchFieldConfig->getId();?>"
                        data-effect="background" 
                        data-effect-args="<?php echo Stephino_Rpg_Config_ResearchFields::KEY;?>,<?php echo $researchFieldConfig->getId();?>">
                        <?php if (isset($researchFieldData[$rfId]) && $researchFieldData[$rfId][Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_LEVEL] > 0):?>
                            <span class="label">
                                <span>
                                    <?php if ($researchFieldMaxLevelReached):?>
                                        &#x2714;&#xFE0F;
                                    <?php endif;?>
                                    <?php if ($researchFieldConfig->getLevelsEnabled()):?>
                                        <?php echo esc_html__('Level', 'stephino-rpg');?> <b><?php echo $researchFieldData[$rfId][Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_LEVEL];?></b>
                                    <?php else:?>
                                        <b><?php echo esc_html__('Done', 'stephino-rpg');?></b> 
                                    <?php endif;?>
                                </span>
                            </span>
                        <?php endif;?>
                    </div>
                    <?php if (isset($researchQueue[$rfId]) && is_array($researchQueue[$rfId])):?>
                        <div class="col-12 text-center">
                            <?php if ($researchFieldConfig->getLevelsEnabled()):?>
                                &plus;<b><?php echo $researchQueue[$rfId][Stephino_Rpg_Renderer_Ajax_Action_Research::DATA_QUEUE_QUANTITY];?></b>
                            <?php else:?>
                                <?php echo esc_html__('Done', 'stephino-rpg');?>
                            <?php endif;?> in
                            <span 
                                data-effect="countdownTime" 
                                data-effect-args="<?php echo ($researchQueue[$rfId][Stephino_Rpg_Renderer_Ajax_Action_Research::DATA_QUEUE_TIME_LEFT] . ',' 
                                    . $researchQueue[$rfId][Stephino_Rpg_Renderer_Ajax_Action_Research::DATA_QUEUE_TIME_TOTAL]);?>">
                            </span>
                        </div>
                    <?php endif;?>
                </div>
                <div class="col-12 col-lg-9">
                    <div class="card card-body bg-dark mb-4">
                        <?php if (strlen($researchFieldConfig->getDescription())):?>
                            <?php echo Stephino_Rpg_Utils_Lingo::markdown($researchFieldConfig->getDescription());?>
                        <?php else:?>
                            <i class="w-100 text-center"><?php echo esc_html__('no details available', 'stephino-rpg');?></i>
                        <?php endif;?>
                        <?php if ($researchFieldConfig->getResourceExtra1Abundant() || $researchFieldConfig->getResourceExtra2Abundant()):?>
                            <hr/>
                            <?php if ($researchFieldConfig->getResourceExtra1Abundant()):?>
                                <div class="col-12">
                                    <span class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_EXTRA_1;?>">
                                        <div class="icon"></div>
                                        <span>
                                            <?php 
                                                echo sprintf(
                                                    esc_html__('Sets %s abundance to %s for all bases', 'stephino-rpg'),
                                                    '<b>' . Stephino_Rpg_Config::get()->core()->getResourceExtra1Name(true). '</b>',
                                                    '<b>100</b>%'
                                                );
                                            ?>
                                        </span>
                                    </span>
                                </div>
                            <?php endif;?>
                            <?php if ($researchFieldConfig->getResourceExtra2Abundant()):?>
                                <div class="col-12">
                                    <span class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_EXTRA_2;?>">
                                        <div class="icon"></div>
                                        <span>
                                            <?php 
                                                echo sprintf(
                                                    esc_html__('Sets %s abundance to %s for all bases', 'stephino-rpg'),
                                                    '<b>' . Stephino_Rpg_Config::get()->core()->getResourceExtra2Name(true). '</b>',
                                                    '<b>100</b>%'
                                                );
                                            ?>
                                        </span>
                                    </span>
                                </div>
                            <?php endif;?>
                        <?php endif;?>
                    </div>
                </div>
            </div>
            <div class="col-12 align-items-center m-0">
                <?php if ($requirementsMet):?>
                    <div data-role="totalProduction">
                        <?php 
                            $productionData = isset($researchProductionData[$rfId]) ? $researchProductionData[$rfId] : null;
                            require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                                Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_PRODUCTION
                            );
                        ?>
                    </div>
                    <?php if (!$researchFieldMaxLevelReached && (!isset($researchQueue[$rfId]) || !is_array($researchQueue[$rfId]))):?>
                        <div data-role="totalCost">
                            <?php 
                                $costTitle = esc_html__('Research cost', 'stephino-rpg');
                                $costData = isset($researchCostData[$rfId]) ? $researchCostData[$rfId] : null;
                                $costTime = isset($researchCostTime[$rfId]) ? $researchCostTime[$rfId] : null;
                                require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                                    Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_COSTS
                                );
                            ?>
                        </div>
                    <?php endif;?>
                <?php 
                    else:
                        require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                            Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_REQUIREMENTS
                        );
                    endif;
                ?>
            </div>
            <div class="col-12">
                <?php if (isset($researchQueue[$rfId]) && is_array($researchQueue[$rfId])):?>
                    <div class="col">
                        <button 
                            class="btn btn-warning w-100" 
                            data-click="researchFieldQueue" 
                            data-click-args="<?php echo $researchFieldConfig->getId();?>,0">
                            <span><?php echo esc_html__('Cancel', 'stephino-rpg');?></span>
                        </button>
                    </div>
                <?php else:?>
                    <?php if (!$researchFieldMaxLevelReached && $requirementsMet && (!isset($researchFieldData[$rfId]) || $researchFieldConfig->getLevelsEnabled())): ?>
                        <div class="col">
                            <button 
                                class="btn btn-warning w-100" 
                                data-click="researchFieldQueue" 
                                data-click-args="<?php echo $researchFieldConfig->getId();?>">
                                <span>
                                    <?php if (isset($researchFieldData[$rfId]) && $researchFieldData[$rfId][Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_LEVEL] > 0):?>
                                        <?php echo esc_html__('Research', 'stephino-rpg');?>
                                    <?php else:?>
                                        <?php echo esc_html__('Start', 'stephino-rpg');?>
                                    <?php endif;?>
                                </span>
                            </button>
                        </div>
                    <?php endif;?>
                <?php endif;?>
            </div>
        </div>
    <?php endforeach;?>
<?php else :?>
<div class="row no-gutters framed">
    <div class="alert alert-warning">
        <?php 
            echo sprintf(
                esc_html__('%: none defined for this %s', 'stephino-rpg'),
                Stephino_Rpg_Config::get()->core()->getConfigResearchFieldsName(true),
                Stephino_Rpg_Config::get()->core()->getConfigResearchAreaName(true)
            );
        ?>
    </div>
</div>
<?php endif;?>