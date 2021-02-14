<?php
/**
 * Template:Dialog:Language
 * 
 * @title      Language dialog
 * @desc       Template for the Language dialog
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

?>
<div class="col-12 p-2">
    <div class="advisor"></div>
    <div class="card card-body bg-dark text-center">
        <?php echo __('Please select your language', 'stephino-rpg');?>
        <?php foreach(Stephino_Rpg_Utils_Lingo::getLanguages() as $langKey => $langValue):?>
            <button 
                class="btn btn-default w-100"
                data-click="settingsSetLanguage"
                data-click-args="<?php echo $langKey;?>">
                <span><?php echo esc_html($langValue);?></span>
            </button>
        <?php endforeach;?>
    </div>
</div>