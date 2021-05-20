<?php
/**
 * Template:Dialog:DeleteAccount
 * 
 * @title      Delete Account dialog
 * @desc       Template for the Delete Account dialog
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

?>
<div data-name="confirm" class="row">
    <div class="col-4">
        <label for="input-confirm">
            <h4><?php echo esc_html__('Are you sure?', 'stephino-rpg');?></h4>
            <div class="param-desc"><?php echo esc_html__('This action is permanent and cannot be undone.', 'stephino-rpg');?></div>
        </label>
    </div>
    <div class="col-8 param-input">
        <input 
            type="text" 
            autocomplete="off"
            class="form-control" 
            name="confirm" 
            id="input-confirm" 
            placeholder="<?php echo sprintf(esc_html__('Type %s to continue', 'stephino-rpg'), 'CONFIRM');?>"/>
    </div>
</div>
<div class="row">
    <div class="col">
        <button class="btn btn-danger" data-role="delete"><span><?php echo esc_html__('Delete my account', 'stephino-rpg');?></span></button>
    </div>
</div>