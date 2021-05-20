<?php
/**
 * Template:Dialog:Common Entity Military
 * 
 * @title      Common entity military template
 * @desc       Template for entity military points
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $entityCount int */
/* @var $entityConfig Stephino_Rpg_Config_Unit|Stephino_Rpg_Config_Ship */
/* @var $entityDisbandMode boolean */
if (!isset($entityDisbandMode)) {
    $entityDisbandMode = false;
}       
/* @var $entityLayoutGarrison boolean */
if (!isset($entityLayoutGarrison)) {
    $entityLayoutGarrison = false;
}
// Prepare the military capabiliies
$entityAttackPoints = $entityConfig->getCivilian() 
    ? 0 
    : $entityCount * ($entityConfig->getDamage() * $entityConfig->getAmmo()) * ($entityDisbandMode ? -1 : 1);
$entityDefensePoints = $entityConfig->getCivilian() 
    ? 0 
    : $entityCount * ($entityConfig->getArmour() * $entityConfig->getAgility()) * ($entityDisbandMode ? -1 : 1);
?>
<?php if (!$entityLayoutGarrison):?><div class="row mb-4 justify-content-center"><?php endif;?>
    <div class="<?php echo ($entityLayoutGarrison ? 'col-12 col-lg-6' : 'col-6 col-lg-4');?>">
        <div class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_MIL_ATTACK;?>"
            title="<?php echo number_format($entityAttackPoints);?>"
            data-html="true">
            <div class="icon"></div>
            <span>
                <?php echo Stephino_Rpg_Config::get()->core()->getMilitaryAttackName(true);?>: <b><?php
                    echo Stephino_Rpg_Utils_Lingo::isuFormat($entityAttackPoints);
                ?></b>
            </span>
        </div>
    </div>
    <div class="<?php echo ($entityLayoutGarrison ? 'col-12 col-lg-6' : 'col-6 col-lg-4');?>">
        <div class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_MIL_DEFENSE;?>"
            title="<?php echo number_format($entityDefensePoints);?>"
            data-html="true">
            <div class="icon"></div>
            <span>
                <?php echo Stephino_Rpg_Config::get()->core()->getMilitaryDefenseName(true);?>: <b><?php
                    echo Stephino_Rpg_Utils_Lingo::isuFormat($entityDefensePoints);
                ?></b>
            </span>
        </div>
    </div>
<?php if (!$entityLayoutGarrison):?></div><?php endif;?>