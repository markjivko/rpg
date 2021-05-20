<?php
/**
 * Template:Dialog:Common Entity Prepare
 * 
 * @title      Common "entity missing" template
 * @desc       Template for showing needed entities for an action (spy/transport/colonize/attack)
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $entityPrepareCapability string */
if (!isset($entityPrepareCapability)) {
    $entityPrepareCapability = '';
}

/* @var $entityPrepareSingular string */
if (!isset($entityPrepareSingular)) {
    $entityPrepareSingular = '';
}

/* @var $entityPreparePlural string */
if (!isset($entityPreparePlural)) {
    $entityPreparePlural = '';
}

/* @var $entityPrepareNotAllowed string */
if (!isset($entityPrepareNotAllowed)) {
    $entityPrepareNotAllowed = '';
}

/* @var $entityConfig Stephino_Rpg_Config_Unit|Stephino_Rpg_Config_Ship */
$entityConfigs = Stephino_Rpg_Utils_Config::getEntitiesByCapability($entityPrepareCapability);
?>
<div class="col-12 p-2 text-center">
    <?php if (count($entityConfigs)):?>
        <div class="row">
            <div class="col-12">
                <?php echo (1 == count($entityConfigs) ? $entityPrepareSingular : $entityPreparePlural);?>
            </div>
        </div>
        <div class="row justify-content-center">
            <?php 
                foreach ($entityConfigs as $entityConfig):
                    if (!$entityConfig instanceof Stephino_Rpg_Config_Ship 
                        && !$entityConfig instanceof Stephino_Rpg_Config_Unit) {
                        continue;
                    }
                    
                    // Get the item card details
                    list($itemCardFn, $itemCardArgs) = Stephino_Rpg_Utils_Config::getItemCardAttributes($entityConfig, true);
            ?>
                <div class="col-12 col-md-6 col-lg-4 col-xl-3 text-center">
                    <div 
                        class="item-card framed mt-4" 
                        data-click="<?php echo $itemCardFn;?>"
                        data-click-args="<?php echo $itemCardArgs;?>"
                        data-effect="background" 
                        data-effect-args="<?php echo $entityConfig->keyCollection();?>,<?php echo $entityConfig->getId();?>">
                        <span 
                            data-effect="help"
                            data-effect-args="<?php echo $entityConfig->keyCollection();?>,<?php echo $entityConfig->getId();?>">
                            <?php echo $entityConfig->getName(true);?>
                        </span>
                    </div>
                </div>
            <?php endforeach;?>
        </div>
    <?php else: ?>
        <?php echo $entityPrepareNotAllowed;?>
    <?php endif;?>
</div>