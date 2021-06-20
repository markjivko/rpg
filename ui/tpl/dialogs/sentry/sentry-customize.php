<?php
/**
 * Template:Dialog:Sentry
 * 
 * @title      Sentry dialog - Customize
 * @desc       Template for sentry customization
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $userData array */
?>
<div class="col-12 p-4">
    <div class="framed" data-effect="sentryCustomizer">
        <svg viewBox="0 0 512 512" style="display: block; width: 100%; position: relative; z-index: 0;"></svg>
    </div>
    <button class="btn btn-default w-100" data-click="sentryCustomizer" data-click-args="save">
        <span><?php echo esc_html__('Save', 'stephino-rpg');?></span>
    </button>
</div>