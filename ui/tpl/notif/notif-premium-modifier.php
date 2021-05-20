<?php
/**
 * Template:Timelapse:Notification - Premium Modifier
 * 
 * @title      Notification template
 * @desc       Notification template for premium modifiers
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

if (Stephino_Rpg_Db_Model_Messages::isValidNotifData($notifData, 1)):
    /* @var $premiumModifierConfigId int */
    list($premiumModifierConfigId) = $notifData;

    $premiumModifierConfig = Stephino_Rpg_Config::get()
        ->premiumModifiers()
        ->getById($premiumModifierConfigId);

    $premiumModifierName = null !== $premiumModifierConfig 
        ? $premiumModifierConfig->getName()
        : __('Unknown premium modifier', 'stephino-rpg');

    $premiumModifierDuration = null !== $premiumModifierConfig
        ? $premiumModifierConfig->getDuration()
        : 0;
?>
    <div class="col-12">
        <?php 
            // Prepare the name
            $i18nPremiumName = '<span data-effect="help" data-effect-args="' . Stephino_Rpg_Config_PremiumModifiers::KEY . ',' . abs((int) $premiumModifierConfigId) . '">'
                . Stephino_Rpg_Utils_Lingo::escape($premiumModifierName)
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
                        . $premiumModifierDuration . ' ' . esc_html(_n('hour', 'hours', $premiumModifierDuration, 'stephino-rpg'))
                    . '</span>'
                . '</span>'
            . '</div>';
            echo sprintf(esc_html__('The effects of the premium modifier will last for %s', 'stephino-rpg'), $i18nPremiumEffect);
        ?>
    </div>
<?php endif;?>