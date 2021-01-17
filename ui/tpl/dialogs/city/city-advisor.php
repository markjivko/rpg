<?php
/**
 * Template:Dialog:City
 * 
 * @title      City dialog
 * @desc       Template for the city advisor dialog
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var array|null $unlockNext */
?>
<div class="row mt-0 framed p-0">
    <div data-effect="parallax" data-effect-args="<?php echo Stephino_Rpg_Config_Cities::KEY;?>,<?php echo $cityConfig->getId();?>"></div>
    <div class="page-help">
        <span 
            data-effect="help"
            data-effect-args="<?php echo Stephino_Rpg_Config_Cities::KEY;?>,<?php echo $cityConfig->getId();?>">
            <?php echo $cityConfig->getName(true);?>
        </span>
    </div>
</div>
<div class="row item-level">
    <div class="item-level-badge col-12 col-lg-8">
        <?php if (is_array($cityInfo) && $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL] > 0):?>
            <div class="label item-level-number">
                <span>
                    <?php if ($cityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_IS_CAPITAL]):?>
                        <?php echo esc_html__('Metropolis', 'stephino-rpg');?>
                    <?php else:?>
                        <?php echo Stephino_Rpg_Config::get()->core()->getConfigCityName(true);?>
                    <?php endif;?>
                    <?php echo esc_html__('level', 'stephino-rpg');?> <b><?php echo ($cityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL]);?></b>
                </span>
            </div>
        <?php endif;?>
        <button class="item-level-upgrade btn btn-default" data-click="cityRenameButton">
            <span><?php echo esc_html__('Rename', 'stephino-rpg');?></span>
        </button>
        <?php if (!$cityInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_IS_CAPITAL] && is_array($costData) && count($costData)):?>
            <button 
                class="btn btn-default" 
                data-click="cityMoveCapitalPreviewButton">
                <span><?php echo esc_html__('Move metropolis here', 'stephino-rpg');?></span>
            </button>
        <?php endif;?>
    </div>
</div>
<?php 
    if (null != $unlockNext):
        list($unlockObject, $unlockCurrentLevel, $unlockTargetLevel, $unlockQueued) = $unlockNext;
    
        // Prepare the label
        $labelText = $unlockQueued ? '&#8230;' : ($unlockCurrentLevel . ' &#187; ' . $unlockTargetLevel);
        
        // The object can only be a Building or a Research Field
        if ($unlockObject instanceof Stephino_Rpg_Config_Building) {
            $unlockKey = Stephino_Rpg_Config_Buildings::KEY;
            $clickFunction = $unlockQueued ? 'buildingViewDialog' : 'buildingUpgradeDialog';
            $clickArgs = $unlockObject->getId();
            $title = $unlockQueued 
                ? esc_html__('Under construction', 'stephino-rpg') 
                : (
                    0 === $unlockCurrentLevel 
                        ? esc_html__('Build', 'stephino-rpg') 
                        : esc_html__('Upgrade', 'stephino-rpg')
                );
        } else {
            $unlockKey = Stephino_Rpg_Config_ResearchFields::KEY;
            if (null !== $unlockObject->getResearchArea()) {
                $clickFunction = 'researchAreaInfo';
                $clickArgs = $unlockObject->getResearchArea()->getId() . ',' . $unlockObject->getId();
                $title = $unlockQueued 
                    ? esc_html__('Researching', 'stephino-rpg') 
                    : esc_html__('Research', 'stephino-rpg');
            } else {
                $clickFunction = 'helpDialog';
                $clickArgs = $unlockKey . ',' . $unlockObject->getId();
                $title = esc_html__('Learn about', 'stephino-rpg');
            }
        }
?>
    <div class="framed">
        <h5><span><?php echo esc_html__('Upgrade Advisor', 'stephino-rpg');?></span></h5>
        <div class="col-12 w-100">
            <div class="row justify-content-center align-items-center">
                <div class="col-12 col-lg-3 text-center">
                    <div 
                        data-html="true"
                        title="<?php echo $title . ' <b>' . Stephino_Rpg_Utils_Lingo::escape($unlockObject->getName()) . '</b>'; ?>"
                        class="building-entity-icon framed mt-4" 
                        data-click="<?php echo $clickFunction;?>"
                        data-click-args="<?php echo $clickArgs;?>"
                        data-effect="background" 
                        data-effect-args="<?php echo $unlockKey;?>,<?php echo $unlockObject->getId();?>">
                        <span class="label">
                            <span>
                                <?php echo $labelText;?></b>
                            </span>
                        </span>
                    </div>
                </div>
                <div class="col-12 col-lg-9">
                    <h5>
                        <?php echo $title;?>
                        <span data-effect="help" data-effect-args="<?php echo $unlockKey;?>,<?php echo $unlockObject->getId();?>">
                            <?php echo Stephino_Rpg_Utils_Lingo::escape($unlockObject->getName());?>
                        </span>
                    </h5>
                    <button 
                        class="btn btn-default w-100" 
                        data-click="dialog"
                        data-click-args="dialogCityStages,<?php echo $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_ID];?>">
                        <span><?php echo esc_html__('View progress', 'stephino-rpg');?></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php else:?>
    <div class="framed">
        <div class="col-12">
            <button 
                class="btn btn-default w-100" 
                data-click="dialog"
                data-click-args="dialogCityStages,<?php echo $cityInfo[Stephino_Rpg_Db_Table_Cities::COL_ID];?>">
                <span><?php echo esc_html__('View progress', 'stephino-rpg');?></span>
            </button>
        </div>
    </div>
<?php endif; ?>