<?php
/**
 * Template:Timelapse:Notification - Premium Package
 * 
 * @title      Notification template
 * @desc       Notification template for premium packages
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $notifData Stephino_Rpg_Config_PremiumPackage */
?>
<div class="col-12 text-center">
    <?php 
        echo sprintf(
            esc_html__('Thank you for purchasing %s', 'stephino-rpg'),
            '<span data-click="dialog" data-click-args="dialogPremiumPackageList">'
                . Stephino_Rpg_Utils_Lingo::escape($notifData->getName())
            . '</span>'
        );
    ?>
    <br/>
    <?php 
        echo sprintf(
            esc_html__('You have acquired %s', 'stephino-rpg'),
            '<div class="res res-' . Stephino_Rpg_Renderer_Ajax::RESULT_RES_GEM . '">'
                . '<div class="icon"></div>'
                . '<span><b>' . number_format($notifData->getGem()) . '</b> ' . Stephino_Rpg_Config::get()->core()->getResourceGemName(true) . '</span>'
            . '</div>'
        );
    ?>
</div>