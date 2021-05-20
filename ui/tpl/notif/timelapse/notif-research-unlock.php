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

if (Stephino_Rpg_Db_Model_Messages::isValidNotifData($notifData, 1)):
    /* @var $unlockedItems array[] */
    list($unlockedItems) = $notifData;
?>
    <div class="row justify-content-center">
        <?php 
            foreach ($unlockedItems as $unlockItem):
                if (!is_array($unlockItem) || count($unlockItem) < 3) {
                    continue;
                }
                
                // Validate the input
                list($unlockKey, $unlockItemId, $unlockCityIds) = $unlockItem;
                
                // Prepare the configuration object
                $unlockObject = null;
                switch ($unlockKey) {
                    case Stephino_Rpg_Config_Buildings::KEY:
                        $unlockObject = Stephino_Rpg_Config::get()
                            ->buildings()
                            ->getById($unlockItemId);
                        break;
                    
                    case Stephino_Rpg_Config_Governments::KEY:
                        $unlockObject = Stephino_Rpg_Config::get()
                            ->governments()
                            ->getById($unlockItemId);
                        break;
                    
                    case Stephino_Rpg_Config_ResearchAreas::KEY:
                        $unlockObject = Stephino_Rpg_Config::get()
                            ->researchAreas()
                            ->getById($unlockItemId);
                        break;
                    
                    case Stephino_Rpg_Config_ResearchFields::KEY:
                        $unlockObject = Stephino_Rpg_Config::get()
                            ->researchFields()
                            ->getById($unlockItemId);
                        break;
                    
                    case Stephino_Rpg_Config_Ships::KEY:
                        $unlockObject = Stephino_Rpg_Config::get()
                            ->ships()
                            ->getById($unlockItemId);
                        break;
                    
                    case Stephino_Rpg_Config_Units::KEY:
                        $unlockObject = Stephino_Rpg_Config::get()
                            ->units()
                            ->getById($unlockItemId);
                        break;
                }
                if (null === $unlockObject) {
                    continue;
                }
                
                // Get the item card details
                list($itemCardFn, $itemCardArgs) = Stephino_Rpg_Utils_Config::getItemCardAttributes($unlockObject);
                if (!is_array($unlockCityIds) || !count($unlockCityIds)) {
                    $unlockCityIds = array(0);
                }
                foreach ($unlockCityIds as $unlockCityId):
                    $unlockCityName = 0 !== $unlockCityId
                        ? Stephino_Rpg_Db::get()->modelCities()->getName($unlockCityId)
                        : null;
        ?>
            <div class="col-12 col-md-6 col-lg-4 col-xl-3 text-center">
                <div 
                    class="item-card framed mt-4" 
                    <?php if (null !== $unlockCityName):?>
                        data-html="true"
                        title="<?php 
                            echo esc_attr(
                                sprintf(
                                    __('%s unlocked in %s', 'stephino-rpg'),
                                    '<b>' . $unlockObject->getName(true) . '</b>',
                                    '<b>' . $unlockCityName . '</b>'
                                )
                            );
                        ?>"
                    <?php endif;?>
                    data-click="<?php echo $itemCardFn;?>"
                    data-click-args="<?php echo $itemCardArgs;?>"
                    <?php if (0 !== $unlockCityId):?>
                        data-click-city-id="<?php echo $unlockCityId;?>"
                    <?php endif;?>
                    data-effect="background" 
                    data-effect-args="<?php echo $unlockKey;?>,<?php echo $unlockObject->getId();?>">
                    <span>
                        <?php echo $unlockObject->getName(true);?>
                    </span>
                    <?php if (null !== $unlockCityName):?>
                        <span class="label">
                            <span>
                                <?php echo $unlockCityName;?>
                            </span>
                        </span>
                    <?php endif;?>
                </div>
            </div>
        <?php endforeach;endforeach;?>
    </div>
<?php endif;?>