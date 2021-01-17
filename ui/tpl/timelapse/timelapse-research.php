<?php
/**
 * Template:Timelapse:Research
 * 
 * @title      Timelapse template - Research
 * @desc       Template for the research messages
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/**
 * @param int                                 $userId              User ID
 * @param string                              $itemType            Item Type
 * @param int                                 $itemId              Item ID
 * @param array                               $itemData            Item Data
 * @param Stephino_Rpg_Config_ResearchField $researchFieldConfig Research Field Configuration
 * @param Stephino_Rpg_Config_ResearchArea  $researchAreaConfig  Research Area configuration
 */
$researchFieldConfig = Stephino_Rpg_Config::get()->researchFields()->getById(
    $itemData[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_CONFIG_ID]
);
$researchAreaConfig = $researchFieldConfig->getResearchArea();
?>
<?php if (null !== $researchAreaConfig):?>
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
<?php endif;?>
<div class="col-12 p-2 text-center">
    <h5>
        <?php 
            $i18nResearchName = '<span data-click="helpDialog" data-click-args="' . Stephino_Rpg_Config_ResearchFields::KEY . ',' . $researchFieldConfig->getId() . '">'
                . $researchFieldConfig->getName(true)
            . '</span>';
            $i18nResearchLevel = '<b>' . $itemData[Stephino_Rpg_Db_Table_ResearchFields::COL_RESEARCH_FIELD_LEVEL] . '</b>';
            echo $researchFieldConfig->getLevelsEnabled()
                ? sprintf(esc_html__('Research %s level %s complete', 'stephino-rpg'), $i18nResearchName, $i18nResearchLevel)
                : sprintf(esc_html__('Research %s complete', 'stephino-rpg'), $i18nResearchName);
        ?>
    </h5>
</div>