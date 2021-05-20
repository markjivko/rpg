<?php
/**
 * Template:Dialog:City
 * 
 * @title      City dialog
 * @desc       Template for the Metropolis change dialog
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

?>
<?php 
    // Prepare the title
    $costTitle = sprintf(
        esc_html__('Metropolis change cost for base level %s', 'stephino-rpg'),
        '<b>' . $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL] . '</b>'
    );
    require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
        Stephino_Rpg_Renderer_Ajax_Dialog::TEMPLATE_COMMON_COSTS
    );
?>
<div class="row">
    <div
        class="col-6">
        <button class="btn w-100" data-click="goBack">
            <span>
                <b><?php echo esc_html__('Go Back', 'stephino-rpg');?></b>
            </span>
        </button>
    </div>
    <div class="col-6">
        <button 
            class="btn btn-warning w-100" 
            data-click="cityMoveCapitalButton">
            <span>
                <b><?php echo esc_html__('Move metropolis here', 'stephino-rpg');?></b>
            </span>
        </button>
    </div>
</div>