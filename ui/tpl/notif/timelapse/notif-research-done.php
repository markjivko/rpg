<?php
/**
 * Template:Timelapse:Research
 * 
 * @title      Timelapse template - Research
 * @desc       Template for the research messages
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

if (Stephino_Rpg_Db_Model_Messages::isValidNotifData($notifData, 2)):
    /* @var $researchFieldConfigId int */
    /* @var $researchFieldLevel int */
    list($researchFieldConfigId, $researchFieldLevel) = $notifData;

    $researchFieldConfig = Stephino_Rpg_Config::get()
        ->researchFields()
        ->getById($researchFieldConfigId);
?>
    <?php 
        if (null !== $researchFieldConfig):
            // Get the item card details
            list($itemCardFn, $itemCardArgs) = Stephino_Rpg_Utils_Config::getItemCardAttributes($researchFieldConfig, true);
    ?>
        <?php if (null !== $researchAreaConfig = $researchFieldConfig->getResearchArea()):?>
            <div class="row framed p-0">
                <div data-effect="parallax" data-effect-args="<?php echo $researchAreaConfig->keyCollection();?>,<?php echo $researchAreaConfig->getId();?>"></div>
                <div class="page-help">
                    <span 
                        data-effect="help"
                        data-effect-args="<?php echo $researchAreaConfig->keyCollection();?>,<?php echo $researchAreaConfig->getId();?>">
                        <?php echo $researchAreaConfig->getName(true);?>
                    </span>
                </div>
            </div>
        <?php endif;?>
        <div class="col-12">
            <?php 
                $i18nResearchName = '<span data-effect="help" data-click="' . $itemCardFn . '" data-click-args="' . $itemCardArgs . '">'
                    . $researchFieldConfig->getName(true)
                . '</span>';
                $i18nResearchLevel = '<b>' . abs((int) $researchFieldLevel) . '</b>';
                echo $researchFieldConfig->getLevelsEnabled()
                    ? sprintf(esc_html__('Research %s level %s complete', 'stephino-rpg'), $i18nResearchName, $i18nResearchLevel)
                    : sprintf(esc_html__('Research %s complete', 'stephino-rpg'), $i18nResearchName);
            ?>
        </div>
    <?php else:?>
        <div class="col-12">
            <?php echo esc_html__('Unknown research complete', 'stephino-rpg');?>
        </div>
    <?php endif; ?>
<?php endif;?>