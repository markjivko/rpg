<?php
/**
 * Template:Dialog:Help
 * 
 * @title      Help Fragment - Discounts
 * @desc       Discounts
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $configObject Stephino_Rpg_Config_Trait_Discount */
$validDiscountsBuildings = $configObject->getEnablesDiscountBuildings() && is_array($configObject->getDiscountBuildings());
$validDiscountsUnits = $configObject->getEnablesDiscountUnits() && is_array($configObject->getDiscountUnits());
$validDiscountsShips = $configObject->getEnablesDiscountShips() && is_array($configObject->getDiscountShips());
if ($validDiscountsBuildings || $validDiscountsUnits || $validDiscountsShips): ?>
    <div class="col-12">
        <h6 class="heading"><span><?php echo esc_html__('Discounts', 'stephino-rpg');?></span></h6>
        <div class="col-12">
            <p><?php echo esc_html__('A discount is applied to the following items:', 'stephino-rpg');?></p>
            <ul>
                <?php if ($validDiscountsBuildings): foreach ($configObject->getDiscountBuildings() as $buildingConfig):?>
                    <li>
                        <span 
                            data-effect="helpMenuItem"
                            data-effect-args="<?php echo Stephino_Rpg_Config_Buildings::KEY;?>,<?php echo $buildingConfig->getId();?>">
                            <?php echo $buildingConfig->getName(true); ?>
                        </span>: <b class="text-muted">-<?php echo $configObject->getDiscountBuildingsPercent();?></b>%
                    </li>
                <?php endforeach; endif;?>
                <?php if ($validDiscountsUnits): foreach ($configObject->getDiscountUnits() as $unitConfig):?>
                    <li>
                        <span 
                            data-effect="helpMenuItem"
                            data-effect-args="<?php echo Stephino_Rpg_Config_Units::KEY;?>,<?php echo $unitConfig->getId();?>">
                            <?php echo $unitConfig->getName(true); ?>
                        </span>: <b class="text-muted">-<?php echo $configObject->getDiscountUnitsPercent();?></b>%
                    </li>
                <?php endforeach; endif;?>
                <?php if ($validDiscountsShips): foreach ($configObject->getDiscountShips() as $shipConfig):?>
                    <li>
                        <span 
                            data-effect="helpMenuItem"
                            data-effect-args="<?php echo Stephino_Rpg_Config_Ships::KEY;?>,<?php echo $shipConfig->getId();?>">
                            <?php echo $shipConfig->getName(true); ?>
                        </span>: <b class="text-muted">-<?php echo $configObject->getDiscountShipsPercent();?></b>%
                    </li>
                <?php endforeach; endif;?>
            </ul>
        </div>
    </div>
<?php endif; ?>