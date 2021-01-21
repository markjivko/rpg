<?php
/**
 * Template:Dialog:User Leader Board
 * 
 * @title      User Leader Board dialog
 * @desc       Template for the leader board
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $userData array|null */
/* @var $userPlace int */
/* @var $userIsMvp boolean */
/* @var $mvpList array */
/* @var $currentTime int */
?>
<div class="col-12 framed p-4">
    <?php if (null === $mvpList):?>
        <?php echo esc_html__('The leader board did not initialize correctly, please try again', 'stephino-rpg');?>
    <?php else:?>
    <table class="table table-hover table-striped table-responsive-sm">
        <thead>
            <tr>
                <th>#</th>
                <th><?php echo esc_html__('Nickname', 'stephino-rpg');?></th>
                <th>
                    <span 
                        data-effect="help"
                        data-effect-args="<?php echo Stephino_Rpg_Config_Core::KEY;?>,<?php echo Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_SCORE;?>">
                        <?php echo esc_html__('Total score', 'stephino-rpg');?>
                    </span>
                </th>
                <th><?php echo esc_html__('Battles', 'stephino-rpg');?></th>
                <th><?php echo esc_html__('Online', 'stephino-rpg');?></th>
            </tr>
        </thead>
        <tbody>
            <?php $mvpPlace = 1; foreach ($mvpList as $mvpUserData): ?>
            <tr <?php if ($userIsMvp && $mvpPlace == $userPlace):?>class="framed active"<?php endif;?>
                data-click="userViewProfile"
                data-click-args="<?php echo Stephino_Rpg_Utils_Lingo::escape($mvpUserData[Stephino_Rpg_Db_Table_Users::COL_ID]);?>">
                <th><?php echo $mvpPlace;?></td>
                <td><?php echo Stephino_Rpg_Utils_Lingo::getUserName($mvpUserData);?></td>
                <td>
                    <span data-role="score" title="<?php echo number_format($mvpUserData[Stephino_Rpg_Db_Table_Users::COL_USER_SCORE]);?>">
                        <?php echo Stephino_Rpg_Utils_Lingo::isuFormat($mvpUserData[Stephino_Rpg_Db_Table_Users::COL_USER_SCORE]);?>
                    </span>
                </td>
                <td>
                    <span class="tb-v" title="<?php echo esc_attr__('Victories', 'stephino-rpg');?>">
                        <?php echo $mvpUserData[Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_VICTORIES];?>
                    </span> /
                    <span class="tb-w" title="<?php echo esc_attr__('Draws', 'stephino-rpg');?>">
                        <?php echo $mvpUserData[Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_DRAWS];?>
                    </span> /
                    <span class="tb-d" title="<?php echo esc_attr__('Defeats', 'stephino-rpg');?>">
                        <?php echo $mvpUserData[Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_DEFEATS];?>
                    </span>
                </td>
                <td>
                    <?php if ($currentTime - $mvpUserData[Stephino_Rpg_Db_Table_Users::COL_USER_LAST_TICK_AJAX] <= 900):?>
                        <span class="badge badge-success">&#x2713;</span>
                    <?php else:?>
                        <span class="badge">&#x2716;</span>
                    <?php endif;?>
                </td>
            </tr>
            <?php $mvpPlace++; endforeach;?>
            <?php if (!$userIsMvp):?>
            <tr><td colspan="5" class="text-center">...</td></tr>
            <tr class="font-weight-bold"
                data-click="userViewProfile"
                data-click-args="<?php echo Stephino_Rpg_Utils_Lingo::escape($userData[Stephino_Rpg_Db_Table_Users::COL_ID]);?>">
                <th><?php echo number_format($userPlace);?></td>
                <td><?php echo Stephino_Rpg_Utils_Lingo::getUserName($userData);?></td>
                <td>
                    <span data-role="score" title="<?php echo number_format($userData[Stephino_Rpg_Db_Table_Users::COL_USER_SCORE]);?>">
                        <?php echo Stephino_Rpg_Utils_Lingo::isuFormat($userData[Stephino_Rpg_Db_Table_Users::COL_USER_SCORE]);?>
                    </span>
                </td>
                <td>
                    <span class="tb-v" title="<?php echo esc_attr__('Victories', 'stephino-rpg');?>">
                        <?php echo $userData[Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_VICTORIES];?>
                    </span> /
                    <span class="tb-w" title="<?php echo esc_attr__('Draws', 'stephino-rpg');?>">
                        <?php echo $userData[Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_DRAWS];?>
                    </span> /
                    <span class="tb-d" title="<?php echo esc_attr__('Defeats', 'stephino-rpg');?>">
                        <?php echo $userData[Stephino_Rpg_Db_Table_Users::COL_USER_BATTLE_DEFEATS];?>
                    </span>
                </td>
                <td><span class="badge badge-success">&#x2713;</span></td>
            </tr>
            <?php endif;?>
        </tbody>
    </table>
    <?php endif;?>
</div>