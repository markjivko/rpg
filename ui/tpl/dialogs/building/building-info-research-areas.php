<?php
/**
 * Template:Dialog:Building
 * 
 * @title      Building-Research AReas
 * @desc       Template for the building research areas fragment
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $researchAreaConfigs Stephino_Rpg_Config_ResearchArea[] */
?>
<?php if (isset($researchAreaConfigs) && is_array($researchAreaConfigs) && count($researchAreaConfigs)):?>
    <?php 
        /* @var $researchAreaConfig Stephino_Rpg_Config_ResearchArea */
        foreach ($researchAreaConfigs as $researchAreaConfig):
            // Get the Research Area requirements
            list($requirements, $requirementsMet) = Stephino_Rpg_Renderer_Ajax_Action::getRequirements(
                $researchAreaConfig, 
                $buildingData[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_CITY_ID]
            );
    ?>
        <div class="col-12 framed">
            <h5>
                <span 
                    data-effect="help"
                    data-effect-args="<?php echo Stephino_Rpg_Config_ResearchAreas::KEY;?>,<?php echo $researchAreaConfig->getId();?>">
                    <?php echo $researchAreaConfig->getName(true);?>
                </span>
            </h5>
            <div class="row align-items-center mb-4">
                <div class="col-12 col-lg-3 text-center">
                    <div 
                        class="building-research-icon framed mt-4 <?php if (!$requirementsMet):?>disabled<?php endif;?>" 
                        data-click="helpDialog"
                        data-click-args="<?php echo Stephino_Rpg_Config_ResearchAreas::KEY;?>,<?php echo $researchAreaConfig->getId();?>"
                        data-effect="background" 
                        data-effect-args="<?php echo Stephino_Rpg_Config_ResearchAreas::KEY;?>,<?php echo $researchAreaConfig->getId();?>">
                        <?php 
                            list(
                                $researchAreaConfig, 
                                $researchFieldConfigs, 
                                $researchFieldData
                            ) = Stephino_Rpg_Renderer_Ajax_Action::getResearchAreaInfo($researchAreaConfig->getId());
                            $researchFieldsTotal = count($researchFieldConfigs);
                            $researchFieldsDone = 0;
                            foreach ($researchFieldData as $rfId => $rfRow) {
                                if ($rfRow[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_LEVEL] > 0) {
                                    $researchFieldsDone++;
                                }
                            }
                        ?>
                        <span class="label">
                            <span>
                                <b><?php echo $researchFieldsDone;?></b> / <b><?php echo $researchFieldsTotal;?></b>
                            </span>
                        </span>
                    </div>
                </div>
                <div class="col-12 col-lg-9">
                    <div class="card card-body bg-dark mb-4">
                        <?php if (strlen($researchAreaConfig->getDescription())):?>
                            <?php echo Stephino_Rpg_Utils_Lingo::markdown($researchAreaConfig->getDescription());?>
                        <?php else:?>
                            <i class="w-100 text-center"><?php echo esc_html__('no details available', 'stephino-rpg');?></i>
                        <?php endif;?>
                    </div>
                    <?php 
                        require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
                            Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_REQUIREMENTS
                        );
                    ?>
                </div>
                <?php if ($requirementsMet):?>
                    <div class="col-12">
                        <button 
                            class="btn btn-default w-100" 
                            data-click="researchAreaInfo" 
                            data-click-args="<?php echo $researchAreaConfig->getId();?>">
                            <span><?php echo esc_html__('Explore', 'stephino-rpg');?> <b><?php echo $researchAreaConfig->getName(true);?></b></span>
                        </button>
                    </div>
                <?php endif;?>
            </div>
        </div>
    <?php endforeach;?>
<?php endif;?>