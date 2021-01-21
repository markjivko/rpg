<?php
/**
 * Template:Dialog:Help
 * 
 * @title      Help dialog
 * @desc       Template for the help dialog
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();
?>
<div class="col-12">
    <div class="row">
        <div class="col-12 col-lg-4 help-menu framed order-last order-lg-first" id="menu">
            <?php foreach (Stephino_Rpg_Config::get()->all() as $collectionItem):?>
                <?php 
                    if ($collectionItem instanceof Stephino_Rpg_Config_Core
                        || ($collectionItem instanceOf Stephino_Rpg_Config_Item_Collection
                        && !$collectionItem instanceof Stephino_Rpg_Config_ResearchFields
                        && !$collectionItem instanceof Stephino_Rpg_Config_Tutorials
                        && !$collectionItem instanceof Stephino_Rpg_Config_Modifiers
                        && !$collectionItem instanceof Stephino_Rpg_Config_PremiumPackages)):
                        // No items defined in this collection
                        if ($collectionItem instanceof Stephino_Rpg_Config_Item_Collection && !count($collectionItem->getAll())) {
                            continue;
                        }
                            
                        // Get the key
                        $collectionItemKey = constant(get_class($collectionItem) . '::KEY');

                        // Get the name
                        switch(true) {
                            case $collectionItem instanceof Stephino_Rpg_Config_Core:
                                $collectionItemName = Stephino_Rpg_Utils_Lingo::getGameName();
                                break;
                            
                            case $collectionItem instanceof Stephino_Rpg_Config_Governments;
                                $collectionItemName = Stephino_Rpg_Config::get()->core()->getConfigGovernmentsName();
                                break;
                            
                            case $collectionItem instanceof Stephino_Rpg_Config_Islands;
                                $collectionItemName = Stephino_Rpg_Config::get()->core()->getConfigIslandsName();
                                break;
                            
                            case $collectionItem instanceof Stephino_Rpg_Config_IslandStatues;
                                $collectionItemName = Stephino_Rpg_Config::get()->core()->getConfigIslandStatuesName();
                                break;
                            
                            case $collectionItem instanceof Stephino_Rpg_Config_Cities;
                                $collectionItemName = Stephino_Rpg_Config::get()->core()->getConfigCitiesName();
                                break;
                            
                            case $collectionItem instanceof Stephino_Rpg_Config_Buildings;
                                $collectionItemName = Stephino_Rpg_Config::get()->core()->getConfigBuildingName();
                                break;
                            
                            case $collectionItem instanceof Stephino_Rpg_Config_Units;
                                $collectionItemName = Stephino_Rpg_Config::get()->core()->getConfigUnitsName();
                                break;
                            
                            case $collectionItem instanceof Stephino_Rpg_Config_Ships;
                                $collectionItemName = Stephino_Rpg_Config::get()->core()->getConfigShipsName();
                                break;
                            
                            case $collectionItem instanceof Stephino_Rpg_Config_ResearchAreas;
                                $collectionItemName = Stephino_Rpg_Config::get()->core()->getConfigResearchAreasName();
                                break;
                            
                            case $collectionItem instanceof Stephino_Rpg_Config_ResearchFields;
                                $collectionItemName = Stephino_Rpg_Config::get()->core()->getConfigResearchFieldsName();
                                break;
                            
                            case $collectionItem instanceof Stephino_Rpg_Config_PremiumModifiers;
                                $collectionItemName = '&#11088; ' . esc_html__('Modifiers', 'stephino-rpg');
                                break;
                            
                            default:
                                // Prepare the collection name
                                $collectionItemName = ucfirst(
                                    preg_replace(
                                        array('%([A-Z])%', '%^premium%i'), 
                                        array(' $1', '&#11088;'), 
                                        $collectionItemKey
                                    )
                                );
                                break;
                        }
                ?>
                    <div class="card">
                        <button 
                            class="btn btn-default card-header" 
                            id="card-button-<?php echo $collectionItemKey;?>"
                            data-toggle="collapse" 
                            data-target="#collapse-<?php echo $collectionItemKey;?>"
                            aria-expanded="<?php echo (
                                 $collectionItemKey == $itemType
                                 || (Stephino_Rpg_Config_ResearchAreas::KEY == $collectionItemKey && Stephino_Rpg_Config_ResearchFields::KEY == $itemType) 
                                    ? 'true' 
                                    : 'false'
                             );?>"
                            aria-controls="collapse-<?php echo $collectionItemKey;?>">
                            <span><?php echo esc_html($collectionItemName);?></span>
                        </button>
                        <div id="collapse-<?php echo $collectionItemKey;?>" 
                             class="<?php echo (
                                 $collectionItemKey == $itemType
                                 || (Stephino_Rpg_Config_ResearchAreas::KEY == $collectionItemKey && Stephino_Rpg_Config_ResearchFields::KEY == $itemType) 
                                    ? 'show' 
                                    : 'collapse'
                             );?>" 
                             aria-labelledby="card-button-<?php echo $collectionItemKey;?>" 
                             data-parent="#menu">
                            <div class="card-body">
                                <ul>
                                    <?php if ($collectionItem instanceof Stephino_Rpg_Config_Core):?>
                                    <li
                                        <?php if ($collectionItemKey == $itemType):?>
                                            class="active"
                                        <?php endif;?>
                                        data-effect="helpMenuItem"
                                        data-effect-args="<?php echo Stephino_Rpg_Config_Core::KEY;?>,0">
                                        <span><?php echo esc_html__('About the game', 'stephino-rpg');?></span>
                                    </li>
                                    <?php else:?>
                                        <?php 
                                            foreach($collectionItem->getAll() as $configItem):
                                                $displayConfigItem = true;
                                                if ($configItem instanceof Stephino_Rpg_Config_ResearchArea
                                                    || $configItem instanceof Stephino_Rpg_Config_Unit
                                                    || $configItem instanceof Stephino_Rpg_Config_Ship) {
                                                    if (null === $configItem->getBuilding()) {
                                                        $displayConfigItem = false;
                                                    }
                                                }
                                                if ($displayConfigItem):
                                        ?>
                                            <li
                                                <?php if ($collectionItemKey == $itemType && $configItem->getId() == $itemId):?>
                                                    class="active"
                                                <?php endif;?>
                                                data-effect="helpMenuItem"
                                                data-effect-args="<?php echo $collectionItemKey;?>,<?php echo $configItem->getId();?>">
                                                <span>
                                                    <?php if (strlen($configItem->getName())):?>
                                                        <?php echo $configItem->getName(true);?>
                                                    <?php else:?>
                                                        <i><?php echo esc_html__('empty', 'stephino-rpg');?></i>
                                                    <?php endif;?>
                                                </span>
                                                <?php if ($collectionItem instanceof Stephino_Rpg_Config_ResearchAreas):?>
                                                    <ul>
                                                        <?php foreach(Stephino_Rpg_Config::get()->researchFields()->getAll() as $researchFieldConfig):?>
                                                            <?php if (null !== $researchFieldConfig->getResearchArea() && $researchFieldConfig->getResearchArea()->getId() == $configItem->getId()):?>
                                                                <li
                                                                    <?php if (Stephino_Rpg_Config_ResearchFields::KEY == $itemType && $researchFieldConfig->getId() == $itemId):?>
                                                                        class="active"
                                                                    <?php endif;?>
                                                                    data-effect="helpMenuItem"
                                                                    data-effect-args="<?php echo Stephino_Rpg_Config_ResearchFields::KEY;?>,<?php echo $researchFieldConfig->getId();?>">
                                                                    <span>
                                                                        <?php if (strlen($researchFieldConfig->getName())):?>
                                                                            <?php echo $researchFieldConfig->getName(true);?>
                                                                        <?php else :?>
                                                                            <i><?php echo esc_html__('empty', 'stephino-rpg');?></i>
                                                                        <?php endif;?>
                                                                    </span>
                                                                </li>
                                                        <?php endif;?>
                                                        <?php endforeach;?>
                                                    </ul>
                                                <?php endif;?>
                                            </li>
                                        <?php endif; endforeach;?>
                                    <?php endif;?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endif;?>
            <?php endforeach;?>
        </div>
        <div class="col-12 col-lg-8 p-3 mt-2" data-role="content">
            <?php try {
                    Stephino_Rpg_Renderer_Ajax_Dialog_Help::ajaxItem(array(
                        Stephino_Rpg_Renderer_Ajax_Dialog_Help::REQUEST_ITEM_TYPE => $itemType,
                        Stephino_Rpg_Renderer_Ajax_Dialog_Help::REQUEST_ITEM_ID   => $itemId,
                    ));
                } catch (Exception $exc) {
                    echo '<div class="alert alert-danger">' . $exc->getMessage() . '</div>';
                }
            ?>
        </div>
    </div>
</div>