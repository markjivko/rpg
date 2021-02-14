<?php
/**
 * Template:Timelapse:Diplomacy
 * 
 * @title      Timelapse template - Diplomacy
 * @desc       Template for the Diplomacy messages
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/**
 * @param int    $userId   User ID
 * @param string $itemType Item Type
 * @param int    $itemId   Item ID
 * @param array  $itemData Item Data
 */
?>
<div class="col-12 p-2 framed">
    <?php 
        // Loaded in Stephino_Rpg_TimeLapse_Abstract::_sendMessages
        if (isset($itemData)):
            switch($itemType): case Stephino_Rpg_TimeLapse_Convoys::ACTION_COLONIZE: ?>
            <div class="col-12 p-2 text-center">
                <?php 
                    // Get the colonization details
                    list($itemDataCityInfo, $itemDataConvoyRow) = $itemData;
                    if (is_array($itemDataCityInfo)):
                        list($newCityId, $newCityConfigId) = $itemDataCityInfo;
                    
                        /* @var $newCityConfig Stephino_Rpg_Config_City */
                        $newCityConfig = Stephino_Rpg_Config::get()->cities()->getById($newCityConfigId);
                ?>
                    <h5>
                        <span
                            data-click="dialog"
                            data-click-args="dialogCityInfo,<?php echo $newCityId;?>">
                            <?php echo Stephino_Rpg_Db::get()->modelCities()->getName($newCityId);?>
                        </span>
                        <?php echo sprintf(
                            esc_html__('%s is now part of our empire!', 'stephino-rpg'),
                            '(<b>' . $newCityConfig->getName(true) . '</b>)'
                        );?>
                    </h5>
                <?php else:?>
                    <?php echo esc_html__('Colonization attempt failed', 'stephino-rpg');?>
                <?php endif;?>
            </div>
        <?php break; case Stephino_Rpg_TimeLapse_Queues::ACTION_PREMIUM_EXP: ?>
            <div class="col-12 p-2 text-center">
                <?php 
                    // Get the modifier config
                    $premiumModifierConfig = Stephino_Rpg_Config::get()->premiumModifiers()->getById(
                        $itemData[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID]
                    );
                    
                    // Prepare the expiration time in days
                    $expireDays = (int) $itemData[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_DURATION] / 86400;
                    $premiumModiferExpire = '<div class="res res-time">'
                        . '<div class="icon"></div>'
                            . '<span><span class="font-weight-bold">'
                                . $expireDays . ' ' . esc_html(_n('day', 'days', $expireDays, 'stephino-rpg'))
                            . '</span></span>'
                        . '</div>';
                    
                    // Show the message
                    echo (
                        null !== $premiumModifierConfig
                            ? sprintf(
                                esc_html__('Premium modifier %s has expired after %s', 'stephino-rpg'),
                                '<b><span data-effect="help" data-effect-args="' . Stephino_Rpg_Config_PremiumModifiers::KEY . ',' . $premiumModifierConfig->getId() . '">'
                                    . $premiumModifierConfig->getName(true)
                                    . '</span></b> <b>&times;' . $itemData[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_QUANTITY] . '</b>',
                                $premiumModiferExpire
                            )
                            : sprintf(
                                esc_html__('Unknown premium modifier %s has expired after %s', 'stephino-rpg'),
                                '<b>&times;'. $itemData[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_QUANTITY] . '</b>',
                                $premiumModiferExpire
                            )
                    );
                ?>
                <div data-effect="sound" data-effect-args="attackDefeat"></div>
            </div>
        <?php break; case Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_RESEARCH: ?>
            <div class="col-12 p-2">
                <div class="advisor"></div>
                <div class="card card-body bg-dark" data-effect="typewriter">
                    <?php echo Stephino_Rpg_Utils_Lingo::escape($itemData);?>
                </div>
            </div>
        <?php break; default: ?>
            <div class="col-12 p-2 text-center">
                <?php 
                    echo esc_html__('Unknown diplomatic action', 'stephino-rpg');
                    Stephino_Rpg_Log::check() && Stephino_Rpg_Log::warning(
                        "tpl/timelapse/timelapse-diplomacy, $itemType ($itemId), user #$userId: {$exc->getMessage()}"
                    );
                ?>
            </div>
        <?php break; endswitch;?>
    <?php 
        // Loaded in Stephino_Rpg_Db_Model_Messages::send()
        else:
    ?>
        <div class="col-12 p-2 text-center">
            <h5>
                <?php echo esc_html__('Message from', 'stephino-rpg');?>
                <b>
                    <?php if (isset($senderInfo) && is_array($senderInfo)):?>
                        <span
                            data-click="userViewProfile"
                            data-click-args="<?php echo $senderId;?>">
                            <?php echo Stephino_Rpg_Utils_Lingo::getUserName($senderInfo);?>
                        </span>
                    <?php else:?>
                        <?php echo Stephino_Rpg_Utils_Lingo::getGameName();?>
                    <?php endif;?>
                </b>
            </h5>
        </div>
        <div class="col-12 p-2 text-center">
            <div class="card card-body bg-dark">
                <?php echo 0 == $senderId ? $messageContent : Stephino_Rpg_Utils_Lingo::escape($messageContent);?>
            </div>
        </div>
    <?php endif;?>
</div>