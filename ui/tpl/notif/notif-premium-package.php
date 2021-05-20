<?php
/**
 * Template:Timelapse:Notification - Premium Package
 * 
 * @title      Notification template
 * @desc       Notification template for premium packages
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

if (Stephino_Rpg_Db_Model_Messages::isValidNotifData($notifData, 1)):
    /* @var $premiumPackageConfigId int */
    list($premiumPackageConfigId) = $notifData;

    $premiumPackageConfig = Stephino_Rpg_Config::get()
        ->premiumPackages()
        ->getById($premiumPackageConfigId);

    $premiumPackageName = null !== $premiumPackageConfig 
        ? $premiumPackageConfig->getName()
        : __('Unknown premium package', 'stephino-rpg');

    $premiumPackageGems = null !== $premiumPackageConfig
        ? $premiumPackageConfig->getGem()
        : 0;
?>
    <div class="col-12">
        <?php 
            echo sprintf(
                esc_html__('Thank you for purchasing %s', 'stephino-rpg'),
                '<span data-click="dialog" data-click-args="dialogPremiumPackageList">'
                    . Stephino_Rpg_Utils_Lingo::escape($premiumPackageName)
                . '</span>'
            );
        ?>
        <br/>
        <?php 
            echo sprintf(
                esc_html__('You have acquired %s', 'stephino-rpg'),
                '<div class="res res-' . Stephino_Rpg_Renderer_Ajax::RESULT_RES_GEM . '">'
                    . '<div class="icon"></div>'
                    . '<span><b>' . number_format($premiumPackageGems) . '</b> ' . Stephino_Rpg_Config::get()->core()->getResourceGemName(true) . '</span>'
                . '</div>'
            );
        ?>
    </div>
<?php endif;?>