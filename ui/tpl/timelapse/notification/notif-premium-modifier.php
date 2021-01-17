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

/* @var $notifData Stephino_Rpg_Config_PremiumModifier */
?>
<div class="col-12 text-center">
    <?php 
        // Prepare the name
        $i18nPremiumName = '<span data-effect="help" data-effect-args="' . Stephino_Rpg_Config_PremiumModifiers::KEY . ',' . $notifData->getId() . '">'
            . Stephino_Rpg_Utils_Lingo::escape($notifData->getName())
        . '</span>';
        echo sprintf(esc_html__('You have just activated the %s premium modifier', 'stephino-rpg'), $i18nPremiumName);
    ?>
    <br/>
    <?php 
        // Prepare the effect
        $i18nPremiumEffect = '<div class="res res-time">'
            . '<div class="icon"></div>'
            . '<span>'
                . '<span class="font-weight-bold">'
                    . $notifData->getDuration() . ' ' . esc_html(_n('hour', 'hours', $notifData->getDuration(), 'stephino-rpg'))
                . '</span>'
            . '</span>'
        . '</div>';
        echo sprintf(esc_html__('The effects of the premium modifier will last for %s', 'stephino-rpg'), $i18nPremiumEffect);
    ?>
</div>