<?php
/**
 * Template:Ptf:Author Reward
 * 
 * @title      Platformer author reward
 * @desc       Template for author royalties
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

if (Stephino_Rpg_Db_Model_Messages::isValidNotifData($notifData, 5)):
    /* @var $ptfId int*/
    /* @var $ptfName int*/
    /* @var $playerId int */
    /* @var $playerName string */
    /* @var $rewardGems int */
    list($ptfId, $ptfName, $playerId, $playerName, $rewardGems) = $notifData;
?>
    <div class="col-12">
        <div class="row justify-content-center">
            <div class="col-12">
                <?php 
                    echo sprintf(
                        esc_html__('%s has won your game, %s', 'stephino-rpg'),
                        '<span data-click="userViewProfile" data-click-args="' . abs((int) $playerId) . '"><b>' . Stephino_Rpg_Utils_Lingo::escape($playerName) . '</b></span>',
                        '<span data-click="dialog" data-click-args="dialogUserArenaPlay,' . abs((int) $ptfId) . '">'
                            . Stephino_Rpg_Utils_Lingo::escape($ptfName)
                        . '</span>'
                    );
                ?>
            </div>
            <div class="col-12">
                <span class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_GEM;?>">
                    <div class="icon"></div>
                    <span>
                        <?php echo esc_html__('You have earned', 'stephino-rpg');?> <b><?php echo abs((int) $rewardGems);?></b> <?php echo Stephino_Rpg_Config::get()->core()->getResourceGemName(true);?>
                    </span>
                </span>
            </div>
        </div>
    </div>
<?php endif;?>