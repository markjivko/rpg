<?php
/**
 * Template:Timelapse:Diplomacy
 * 
 * @title      Timelapse template - Diplomacy
 * @desc       Template for the Diplomacy messages
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

if (Stephino_Rpg_Db_Model_Messages::isValidNotifData($notifData, 3)):
    /* @var $premiumModifierConfigId int */
    /* @var $premiumDuration int */
    /* @var $premiumQuantity int */
    list($premiumModifierConfigId, $premiumDuration, $premiumQuantity) = $notifData;
    $premiumModifierConfig = Stephino_Rpg_Config::get()->premiumModifiers()->getById(
        $premiumModifierConfigId
    );
?>
    <div class="col-12">
        <?php 
            // Prepare the expiration time in days
            $expireDays = intval(abs($premiumDuration) / 86400);
            $premiumModiferExpire = '<div class="res res-time">'
                . '<div class="icon"></div>'
                    . '<span><span class="font-weight-bold">'
                        . $expireDays . ' ' . esc_html(_n('day', 'days', $expireDays, 'stephino-rpg'))
                    . '</span></span>'
                . '</div>';

            // Show the message
            echo (
                null !== $premiumModifierConfig
                    ? sprintf(
                        esc_html__('Premium modifier %s has expired after %s', 'stephino-rpg'),
                        '<b><span data-effect="help" data-effect-args="' . $premiumModifierConfig->keyCollection() . ',' . $premiumModifierConfig->getId() . '">'
                            . $premiumModifierConfig->getName(true)
                            . '</span></b> <b>&times;' . abs((int) $premiumQuantity) . '</b>',
                        $premiumModiferExpire
                    )
                    : sprintf(
                        esc_html__('Unknown premium modifier %s has expired after %s', 'stephino-rpg'),
                        '<b>&times;'. abs((int) $premiumQuantity) . '</b>',
                        $premiumModiferExpire
                    )
            );
        ?>
        <div data-effect="sound" data-effect-args="attackDefeat"></div>
    </div>
<?php endif;?>