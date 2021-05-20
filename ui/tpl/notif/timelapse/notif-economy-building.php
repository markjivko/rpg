<?php
/**
 * Template:Timelapse:Economy:Building
 * 
 * @title      Timelapse template - Economy
 * @desc       Template for the Economy messages
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

if (Stephino_Rpg_Db_Model_Messages::isValidNotifData($notifData, 3)):
    /* @var $buildingConfigId int */
    /* @var $buildingLevel int */
    /* @var $buildingCityId int */
    list($buildingConfigId, $buildingLevel, $buildingCityId) = $notifData;

    $buildingConfig = Stephino_Rpg_Config::get()
        ->buildings()
        ->getById($buildingConfigId);
    
    $buildingConfigName = null !== $buildingConfig
        ? $buildingConfig->getName(true)
        : esc_html__('Unknown', 'stephino-rpg');
?>
    <div class="row framed p-0">
        <div data-effect="parallax" data-effect-args="<?php echo Stephino_Rpg_Config_Buildings::KEY;?>,<?php echo abs((int) $buildingConfigId);?>"></div>
        <div class="page-help">
            <span 
                data-effect="help"
                data-effect-args="<?php echo Stephino_Rpg_Config_Buildings::KEY;?>,<?php echo abs((int) $buildingConfigId);?>">
                <?php echo $buildingConfigName;?>
            </span>
        </div>
    </div>
    <div class="col-12">
        <?php 
            echo (
                $buildingLevel <= 1 
                    ? sprintf(
                        esc_html__('%s constructed in %s', 'stephino-rpg'),
                        '<b>' . $buildingConfigName . '</b>',
                        '<span data-click="dialog" data-click-args="dialogCityInfo,' . abs((int) $buildingCityId) . '">'
                            . Stephino_Rpg_Db::get()->modelCities()->getName($buildingCityId)
                        . '</span>'
                    )
                    : sprintf(
                        esc_html__('%s upgraded to level %s in %s', 'stephino-rpg'),
                        '<b>' . $buildingConfigName . '</b>',
                        abs((int) $buildingLevel),
                        '<span data-click="dialog" data-click-args="dialogCityInfo,' . abs((int) $buildingCityId) . '">'
                            . Stephino_Rpg_Db::get()->modelCities()->getName($buildingCityId)
                        . '</span>'
                    )
            );
        ?>
    </div>
<?php endif;?>