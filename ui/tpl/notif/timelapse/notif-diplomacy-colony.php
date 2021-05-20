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

if (Stephino_Rpg_Db_Model_Messages::isValidNotifData($notifData, 1)):
    /* @var $itemDataCityInfo int[]|null */
    list($itemDataCityInfo) = $notifData;
?>
    <div class="col-12">
        <?php 
            // Get the colonization details
            if (is_array($itemDataCityInfo)):
                list($newCityId, $newCityConfigId) = $itemDataCityInfo;

                /* @var $newCityConfig Stephino_Rpg_Config_City */
                $newCityConfig = Stephino_Rpg_Config::get()->cities()->getById($newCityConfigId);
                $newCityConfigName = null !== $newCityConfig
                    ? $newCityConfig->getName(true)
                    : esc_html__('Unknown', 'stephino-rpg');
        ?>
            <span
                data-click="dialog"
                data-click-args="dialogCityInfo,<?php echo abs((int) $newCityId);?>">
                <?php echo Stephino_Rpg_Db::get()->modelCities()->getName($newCityId);?>
            </span>
            <?php echo sprintf(
                esc_html__('%s is now part of our empire!', 'stephino-rpg'),
                '(<b>' . $newCityConfigName . '</b>)'
            );?>
        <?php else:?>
            <?php echo esc_html__('Colonization attempt failed', 'stephino-rpg');?>
        <?php endif;?>
    </div>
<?php endif;?>