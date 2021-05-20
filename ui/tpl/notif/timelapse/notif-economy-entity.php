<?php
/**
 * Template:Timelapse:Economy:Entity
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
    /* @var $entityConfigId int */
    /* @var $entityType string */
    /* @var $entityCityId int */
    list($entityConfigId, $entityType, $entityCityId) = $notifData;

    // Get the entity configuration
    $entityConfig = Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_UNIT == $entityType
        ? Stephino_Rpg_Config::get()->units()->getById($entityConfigId)
        : Stephino_Rpg_Config::get()->ships()->getById($entityConfigId);

    // Get the entity key
    $entityKey = Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_UNIT == $entityType
        ? Stephino_Rpg_Config_Units::KEY
        : Stephino_Rpg_Config_Ships::KEY;

    // Get the entity name
    $entityName = null !== $entityConfig
        ? $entityConfig->getName(true)
        : esc_html__('Unknown', 'stephino-rpg');
?>
    <div class="row framed p-0">
        <div data-effect="parallax" data-effect-args="<?php echo $entityKey;?>,<?php echo abs((int) $entityConfigId);?>"></div>
        <div class="page-help">
            <span 
                data-effect="help"
                data-effect-args="<?php echo $entityKey;?>,<?php echo abs((int) $entityConfigId);?>">
                <?php echo $entityName;?>
            </span>
        </div>
    </div>
    <div class="col-12">
        <?php 
            echo '<b>' . $entityName . '</b>: ' . sprintf(
                Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_UNIT == $entityType
                    ? esc_html__('recruitment finished in %s', 'stephino-rpg')
                    : esc_html__('construction finished in %s', 'stephino-rpg'),
                '<span data-click="dialog" data-click-args="dialogCityInfo,' . abs((int) $entityCityId) . '">'
                    . Stephino_Rpg_Db::get()->modelCities()->getName($entityCityId)
                . '</span>'
            );
        ?>
    </div>
<?php endif;?>