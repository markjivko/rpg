<?php
/**
 * Template:Dialog:Entity
 * 
 * @title      Entity disband dialog
 * @desc       Template for disbanding entities
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $entityConfig Stephino_Rpg_Config_Unit|Stephino_Rpg_Config_Ship */
?>
<div data-role="disband">
    <div class="row mt-0 framed p-0">
        <div data-effect="parallax" data-effect-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>">
        </div>
        <div class="page-help">
            <span 
                data-effect="help"
                data-effect-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>">
                <?php echo $entityConfig->getName(true);?>
            </span>
        </div>
    </div>
    <?php if ($entityConfig->getDisbandable() && $entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT] > 0):?>
        <div class="framed">
            <div class="row no-gutters">
                <input 
                    type="range"
                    min="0"
                    max="<?php echo $entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT];?>"
                    value="0"
                    data-change="entityDisbandPreview"
                    data-change-args="<?php echo $entityConfig->getDisbandablePopulation();?>"
                    data-preview="true"
                    data-preview-label="/ <?php echo Stephino_Rpg_Utils_Lingo::isuFormat($entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT]);?>"
                    data-preview-label-title="<b><?php echo number_format($entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT]) . '</b>';?>" />
            </div>
            <button 
                class="btn btn-warning w-100 d-none"
                data-click="entityDisband"
                data-click-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>">
                <?php if ($entityConfig->getDisbandablePopulation() > 0):?>
                    <span><?php
                        echo sprintf(
                            esc_html__('Disband for %s', 'stephino-rpg'),
                            '<b data-role="populationReward">0</b> ' . Stephino_Rpg_Config::get()->core()->getMetricPopulationName(true)
                        );
                    ?></span>
                <?php else:?>
                    <span><?php echo esc_html__('Disband', 'stephino-rpg');?></span>
                <?php endif;?>
            </button>
        </div>
    <?php else:?>
        <div class="framed">
            <span class="alert alert-warning"><?php echo esc_html__('No entities to disband', 'stephino-rpg');?></span>
        </div>
    <?php endif; ?>
</div>