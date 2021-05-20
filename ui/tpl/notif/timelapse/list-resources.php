<?php
/**
 * Template:Timelapse:List Resources
 * 
 * @title      Timelapse template - List Resources
 * @desc       Template fragment
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $resourceExact Show the exact resource value or an ISU aproximation; if set to false, hide gold, research and gem */
/* @var $resourcesList Resources list */
if (!isset($resourceExact)) {
    $resourceExact = true;
}
if (isset($resourcesList) && is_array($resourcesList)):
?>
    <div class="row justify-content-center">
        <?php 
            $cityResources = Stephino_Rpg_Renderer_Ajax_Action::getResourceData($resourcesList);
            foreach($cityResources as $resourceKey => list($resName, $resValue, $resAjaxKey)):
                // Never disclose top-level resources
                if (!$resourceExact && in_array($resourceKey, array(
                    Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD,
                    Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH,
                    Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM,
                ))) {
                    continue;
                }
                
                // Don't display resources
                if (false !== $resValue && $resValue <= 0) {
                    continue;
                }
        ?>
            <div class="col-6">
                <div class="res res-<?php echo $resAjaxKey;?>">
                    <div class="icon"></div>
                    <span>
                        <b>
                            <?php /* false set in Stephino_Rpg_TimeLapse_Convoys::_spy() */ if (false !== $resValue):?>
                                <?php 
                                    echo (
                                        $resourceExact 
                                            ? number_format($resValue) 
                                            : Stephino_Rpg_Utils_Lingo::isuFormat(
                                                // Replace all digits except the first with zeros
                                                (int) preg_replace(
                                                    '%(?<!^)\d%', 
                                                    '0', 
                                                    round($resValue, 0)
                                                ), 
                                                0
                                            )
                                    );
                                ?>
                            <?php else:?>
                                &#x1F6AB;
                            <?php endif;?>
                        </b>
                        <?php echo $resName;?>
                    </span>
                </div>
            </div>
        <?php endforeach;?>
    </div>
<?php 
    endif;
    $resourceExact = true;
?>