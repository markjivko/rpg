<?php
/**
 * Template:Ptf:Ptf Review
 * 
 * @title      Platformer game review
 * @desc       Template for game review
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

if (Stephino_Rpg_Db_Model_Messages::isValidNotifData($notifData, 4)):
    /* @var $ptfReview string */
    /* @var $ptfId int */
    /* @var $ptfName string */
    /* @var $ptfSuspended int|null */
    list($ptfReview, $ptfId, $ptfName, $ptfSuspended) = $notifData;

    $ptfLabel = Stephino_Rpg_Db::get()->modelPtfs()->getReviewLabels($ptfReview);
?>
    <div class="col-12">
        <div class="row justify-content-center">
            <div class="col-12">
                <?php 
                    if (Stephino_Rpg_Db_Table_Ptfs::PTF_REVIEW_PENDING != $ptfReview) {
                        echo sprintf(
                            esc_html__('%s has received the following review: %s', 'stephino-rpg'),
                            '<span data-click="dialog" data-click-args="dialogUserArenaPlay,' . abs((int) $ptfId) . '">'
                                . esc_html($ptfName)
                            . '</span>',
                            '<b>' . esc_html($ptfLabel) . '</b>'
                        );
                    } else {
                        echo sprintf(
                            esc_html__('%s was marked as under review by a game master', 'stephino-rpg'),
                            '<span data-click="dialog" data-click-args="dialogUserArenaPlay,' . abs((int) $ptfId) . '">'
                                . esc_html($ptfName)
                            . '</span>'
                        );
                    }
                ?>
            </div>
            <div class="col-12">
                <?php if (null !== $ptfSuspended):?>
                    <div data-effect="sound" data-effect-args="attackDefeat"></div>
                        <?php 
                            echo sprintf(
                                esc_html__('You have %s suspended %s', 'stephino-rpg'),
                                '<b>' . $ptfSuspended . '</b>',
                                _n('game', 'games', $ptfSuspended, 'stephino-rpg')
                            ); 
                        ?><br/>
                        <?php 
                            if ($ptfSuspended >= Stephino_Rpg_Config::get()->core()->getPtfStrikes()) {
                                echo __('Your game arena publisher account was suspended', 'stephino-rpg');
                            } else {
                                echo sprintf( 
                                    esc_html__('If you reach %s strikes you will not be able to edit or create new games', 'stephino-rpg'),
                                    '<b>' . Stephino_Rpg_Config::get()->core()->getPtfStrikes() . '</b>'
                                );
                            }
                        ?>
                <?php else:?>
                    <?php 
                        if (Stephino_Rpg_Db_Table_Ptfs::PTF_REVIEW_APPROVED == $ptfReview) {
                            echo __('Congratulations!', 'stephino-rpg');
                        } elseif (Stephino_Rpg_Db_Table_Ptfs::PTF_REVIEW_PENDING != $ptfReview) {
                            echo __('Please correct the issue and mark your game as public when you are ready for another review', 'stephino-rpg');
                        }
                    ?>
                <?php endif;?>
            </div>
        </div>
    </div>
<?php endif;?>