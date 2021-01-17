<?php
/**
 * Template:Dialog:Attack Review
 * 
 * @title      Attack Review dialog
 * @desc       Template for the attack review dialog
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();
?>
<div class="col-12 framed" data-role="entities">
    <div class="row">
        <h5>
            <?php 
                echo sprintf(
                    esc_html__('Attack from %s on %s', 'stephino-rpg'),
                    '<span data-click="dialog" data-click-args="dialogCityInfo,' . $attackerCityData[Stephino_Rpg_Db_Table_Cities::COL_ID] . '">'
                        . Stephino_Rpg_Utils_Lingo::getCityName($attackerCityData)
                    . '</span>',
                    '<span data-click="dialog" data-click-args="dialogCityInfo,' . $defenderCityData[Stephino_Rpg_Db_Table_Cities::COL_ID] . '">'
                        . Stephino_Rpg_Utils_Lingo::getCityName($defenderCityData)
                    . '</span>'
                );
            ?>
        </h5>
        <?php 
            list($convoyTravelFast, $convoyTravelTime) = Stephino_Rpg_Db::get()->modelConvoys()->getTravelInfo(
                $attackerCityData, 
                $defenderCityData,
                $attackerPayload
            );
            if ($convoyTravelTime > 0):
        ?>
            <div class="col-12 text-center">
                <div class="res res-time">
                    <div class="icon" data-html="true" title="<?php echo esc_attr__('Travel time', 'stephino-rpg');?>"></div>
                    <span
                        data-html="true" 
                        title="<?php echo Stephino_Rpg_Utils_Lingo::secondsHR($convoyTravelTime);?>">
                        <?php echo Stephino_Rpg_Utils_Lingo::secondsGM($convoyTravelTime);?> at
                        <b>
                            <?php if ($convoyTravelFast):?>
                                <?php echo esc_html__('full speed', 'stephino-rpg');?>
                            <?php else:?>
                                <?php echo esc_html__('reduced speed', 'stephino-rpg');?>
                            <?php endif;?>
                        </b>
                    </span>
                </div>
            </div>
        <?php endif;?>
    </div>
    <div class="row justify-content-center">
        <?php 
            foreach($attackerPayload as $entityRow):
                // Get the configuration object
                $entityConfig = null;
                switch ($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE]) {
                    case Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT:
                        $entityConfig = Stephino_Rpg_Config::get()->units()->getById(
                            $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]
                        );
                        break;

                    case Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_SHIP:
                        $entityConfig = Stephino_Rpg_Config::get()->ships()->getById(
                            $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]
                        );
                        break;
                }
                $entityKey = $entityConfig instanceof Stephino_Rpg_Config_Unit
                    ? Stephino_Rpg_Config_Units::KEY
                    : Stephino_Rpg_Config_Ships::KEY;
        ?>
            <div class="col-12 col-lg-3 text-center"
                data-role="entity" 
                data-entity-count="<?php echo $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT];?>"
                data-entity-type="<?php echo $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE];?>"
                data-entity-config="<?php echo $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID];?>" >
                <div 
                    class="building-entity-icon framed mt-4" 
                    data-click="helpDialog"
                    data-click-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>"
                    data-effect="background" 
                    data-effect-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>">
                    <span 
                        data-effect="help"
                        data-effect-args="<?php echo $entityKey;?>,<?php echo $entityConfig->getId();?>">
                        <?php echo $entityConfig->getName(true);?>
                    </span>
                    <span class="label" data-html="true" title="&times; <?php echo number_format($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT]);?>">
                        <span>
                            &times; <b><?php echo Stephino_Rpg_Utils_Lingo::isuFormat($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT]);?></b>
                        </span>
                    </span>
                </div>
            </div>
        <?php endforeach;?>
    </div>
</div>
<div class="row p2">
    <div class="col-6">
        <button class="btn w-100" data-click="goBack">
            <span><?php echo esc_html__('Go Back', 'stephino-rpg');?></span>
        </button>
    </div>
    <div class="col-6">
        <button 
            class="btn btn-warning w-100"
            data-click="cityAttackButton"
            data-click-args="<?php echo $attackerCityId;?>,<?php echo $defenderCityData[Stephino_Rpg_Db_Table_Cities::COL_ID];?>">
            <span><?php echo esc_html__('Attack', 'stephino-rpg');?></span>
        </button>
    </div>
</div>