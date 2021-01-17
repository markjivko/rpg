<?php
/**
 * Template:Dialog:Help
 * 
 * @title      Help Fragment - Requirements
 * @desc       Requirements
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $configUnlocks Stephino_Rpg_Config_Item_Single[] */
$configUnlocks = Stephino_Rpg_Utils_Config::getUnlocks($configObject);
?>
<?php if (count($configUnlocks)): ?>
    <div class="col-12">
        <h6 class="heading"><span><?php echo esc_html__('Unlocks', 'stephino-rpg');?></span></h6>
        <div class="col-12">
            <ul>
                <?php 
                    foreach ($configUnlocks as $configUnlocked):
                        // Prepare the AJAX key
                        $configUnlockedKey = null;
                        switch (true) {
                            case $configUnlocked instanceof Stephino_Rpg_Config_Building:
                                $configUnlockedKey = Stephino_Rpg_Config_Buildings::KEY;
                                break;
                            
                            case $configUnlocked instanceof Stephino_Rpg_Config_Government:
                                $configUnlockedKey = Stephino_Rpg_Config_Governments::KEY;
                                break;
                            
                            case $configUnlocked instanceof Stephino_Rpg_Config_ResearchArea:
                                $configUnlockedKey = Stephino_Rpg_Config_ResearchAreas::KEY;
                                break;
                            
                            case $configUnlocked instanceof Stephino_Rpg_Config_ResearchField:
                                $configUnlockedKey = Stephino_Rpg_Config_ResearchFields::KEY;
                                break;
                            
                            case $configUnlocked instanceof Stephino_Rpg_Config_Ship:
                                $configUnlockedKey = Stephino_Rpg_Config_Ships::KEY;
                                break;
                            
                            case $configUnlocked instanceof Stephino_Rpg_Config_Unit:
                                $configUnlockedKey = Stephino_Rpg_Config_Units::KEY;
                                break;
                        }
                        if (null === $configUnlockedKey) {
                            continue;
                        }
                        
                        // Prepare the level
                        $configUnlockedLevel = 1;
                        switch (true) {
                            case $configObject instanceof Stephino_Rpg_Config_Building:
                                $configUnlockedLevel = $configUnlocked->getRequiredBuildingLevel();
                                break;
                            
                            case $configObject instanceof Stephino_Rpg_Config_ResearchField:
                                $configUnlockedLevel = $configUnlocked->getRequiredResearchFieldLevel();
                                break;
                        }
                ?>
                    <li>
                        <span 
                            data-effect="helpMenuItem"
                            data-effect-args="<?php echo $configUnlockedKey;?>,<?php echo $configUnlocked->getId();?>">
                            <?php echo $configUnlocked->getName(true);?>
                        </span>
                        <?php 
                            echo sprintf(
                                esc_html__('at level %s', 'stephino-rpg'), 
                                '<b>' . $configUnlockedLevel . '</b>'
                            );
                        ?>
                    </li>
                <?php endforeach;?>
            </ul>
        </div>
    </div>
<?php endif; ?>