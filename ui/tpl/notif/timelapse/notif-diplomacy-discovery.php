<?php
/**
 * Template:Timelapse:Diplomacy
 * 
 * @title      Timelapse template - Diplomacy
 * @desc       Template for the Diplomacy messages
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

if (Stephino_Rpg_Db_Model_Messages::isValidNotifData($notifData, 1)):
    /* @var $researchFieldConfigId int */
    list($researchFieldConfigId) = $notifData;
    $researchFieldConfig = Stephino_Rpg_Config::get()
        ->researchFields()
        ->getById(
        $researchFieldConfigId
    );
?>
    <div class="col-12">
        <div class="advisor"></div>
        <div class="card card-body bg-dark" data-effect="typewriter">
            <?php 
                echo null !== $researchFieldConfig && strlen($researchFieldConfig->getStory())
                    ? esc_html($researchFieldConfig->getStory())
                    : esc_html__('Our records were mysteriously destroyed', 'stephino-rpg');
            ?>
        </div>
    </div>
<?php endif;?>