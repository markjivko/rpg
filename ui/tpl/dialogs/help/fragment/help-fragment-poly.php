<?php
/**
 * Template:Dialog:Help
 * 
 * @title      Help Fragment - Points preview
 * @desc       Points preview
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/**
 * @param int|null    $polynomialBase       Level 1 points
 * @param string|null $polynomialDefinition Polynomial JSON definition
 * @param boolean     $polyTime             (optional) Use a time format; default false
 * @param int         $polyMax              (optional) Maximum value; default null
 * @param boolean     $polyIgnoreCount      (optional) Ignore the count selector; default null
 */
if (isset($polynomialBase) && null !== $polynomialBase):
    // Prepare the definition array
    $polynomialArray = null;
    if(isset($polynomialDefinition) && is_string($polynomialDefinition) && strlen($polynomialDefinition)) {
        $polynomialArray = @json_decode($polynomialDefinition, true);
    }
    
    // Valid definition
    if (is_array($polynomialArray) 
        && isset($polynomialArray[Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC])
        && isset($polynomialArray[Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS])
        && is_array($polynomialArray[Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS])):?>
        <b>
            <span
                data-effect="poly"
                <?php if (Stephino_Rpg_Cache_User::get()->isGameMaster()):?>
                    data-html="true"
                    data-describe="true"
                <?php endif;?>
                <?php if (isset($polyTime) && $polyTime):?>
                    data-poly-time="true"
                <?php endif;?>
                <?php if(isset($polyMax) && null !== $polyMax):?>
                    data-poly-max="<?php echo $polyMax;?>"
                <?php $polyMax = null; endif;?>
                <?php if (isset($polyIgnoreCount) && null !== $polyIgnoreCount): ?>
                    data-poly-ignore-count="<?php echo ($polyIgnoreCount ? 'true' : 'false');?>"
                <?php $polyIgnoreCount = null; endif; ?>
                data-poly-base="<?php echo intval($polynomialBase);?>"
                data-poly-func="<?php echo $polynomialArray[Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC];?>"
                data-poly-arg-a="<?php 
                    echo isset($polynomialArray[Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS][Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS_A])
                        ? $polynomialArray[Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS][Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS_A]
                        : '';
                ?>"
                data-poly-arg-b="<?php 
                    echo isset($polynomialArray[Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS][Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS_B])
                        ? $polynomialArray[Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS][Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS_B]
                        : '';
                ?>"
                data-poly-arg-c="<?php 
                    echo isset($polynomialArray[Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS][Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS_C])
                        ? $polynomialArray[Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS][Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS_C]
                        : '';
                ?>"><?php echo (isset($polyTime) && $polyTime) ? Stephino_Rpg_Utils_Lingo::secondsGM($polynomialBase) : number_format($polynomialBase);?></span>
        </b>
    <?php else: ?>
        <?php if (isset($polynomialBase)):?>
            <b 
                class="text-muted"
                <?php if (isset($polyTime) && $polyTime):?>
                    data-poly-time="true"
                <?php endif;?>
                    <?php if(isset($polyMax) && null !== $polyMax):?>
                    data-poly-max="<?php echo $polyMax;?>"
                <?php $polyMax = null; endif;?>
                <?php if (isset($polyIgnoreCount) && null !== $polyIgnoreCount): ?>
                    data-poly-ignore-count="<?php echo ($polyIgnoreCount ? 'true' : 'false');?>"
                <?php $polyIgnoreCount = null; endif; ?>
                data-effect="poly"
                data-poly-base="<?php echo $polynomialBase;?>"
                data-poly-func="<?php echo Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC_CONSTANT;?>">
                <?php echo (isset($polyTime) && $polyTime) ? Stephino_Rpg_Utils_Lingo::secondsGM($polynomialBase) : number_format($polynomialBase);?>
            </b>
        <?php endif;?>
    <?php endif; ?>
<?php 
    endif;
    
    // Reset the flag
    $polyTime = false;
?>