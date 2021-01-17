<?php
/**
 * Template:Timelapse:List Entities
 * 
 * @title      Timelapse template - List entities
 * @desc       Template fragment
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $entityCountExact Show the exact unit count an ISU aproximation*/
if (!isset($entityCountExact)) {
    $entityCountExact = true;
}
/* @var $entitiesList Entities list */
if (isset($entitiesList) && is_array($entitiesList)):
?>
    <div class="row justify-content-center text-center">
        <?php 
            foreach ($entitiesList as $entityData):
                $entityConfig = Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT == $entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE]
                    ? Stephino_Rpg_Config::get()->units()->getById($entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID])
                    : Stephino_Rpg_Config::get()->ships()->getById($entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]);
                $entityKey = Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT == $entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE]
                    ? Stephino_Rpg_Config_Units::KEY
                    : Stephino_Rpg_Config_Ships::KEY;
                if (null === $entityConfig) {
                    continue;
                }
                if (false !== $entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT] && $entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT] <= 0) {
                    continue;
                }
        ?>
            <div class="col-6 col-lg-4">
                <div class="entity">
                    <div 
                        class="icon" 
                        data-click="helpDialog"
                        data-click-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>"
                        data-effect="background" 
                        data-effect-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>">
                    </div>
                    <span>
                        <span 
                            data-effect="help"
                            data-effect-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>">
                            <b>
                                <?php /* false set in Stephino_Rpg_TimeLapse_Convoys::_spy() */ if (false !== $entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT]):?>
                                    <?php 
                                        echo (
                                            $entityCountExact 
                                                ? number_format($entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT]) 
                                                : Stephino_Rpg_Utils_Lingo::isuFormat(
                                                    // Replace all digits except the first with zeros
                                                    (int) preg_replace(
                                                        '%(?<!^)\d%', 
                                                        '0', 
                                                        round($entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT], 0)
                                                    ), 
                                                    0
                                                )
                                        );
                                    ?>
                                <?php else:?>
                                    &#x1F6AB;
                                <?php endif;?>
                            </b> 
                            &times; 
                            <?php echo $entityConfig->getName(true);?>
                        </span>
                    </span>
                </div>
            </div>
        <?php endforeach;?>
    </div>
<?php endif;?>