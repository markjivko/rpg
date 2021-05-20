<?php
/**
 * Template:Dialog:City
 * 
 * @title      City dialog
 * @desc       Template for the city renaming dialog
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var array|null $unlockNext */
?>
<div class="framed">
    <div class="col-12 row no-gutters">
        <input 
            type="text" 
            autocomplete="off"
            id="input-city-name" 
            class="form-control" 
            data-change="cityRename" 
            data-effect="charCounter"
            name="city-name" 
            maxlength="<?php echo Stephino_Rpg_Renderer_Ajax_Action_City::MAX_LENGTH_CITY_NAME;?>"
            value="<?php echo esc_attr($cityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_NAME]); ?>" />
    </div>
</div>