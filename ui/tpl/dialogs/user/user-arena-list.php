<?php
/**
 * Template:Dialog:User Arena
 * 
 * @title      User Arena dialog - List
 * @desc       Template for listing available platformers
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $authorId int */
/* @var $authorName string */
/* @var $userId int */
/* @var $userGamesCreated int */
/* @var $userCanCreate boolean */
/* @var $userSuspended boolean */
?>
<div class="col-12 framed p-4">
    <div class="row align-items-center">
        <div class="col-12 col-lg-4">
            <select 
                class="form-control"
                data-change="<?php echo Stephino_Rpg_Renderer_Ajax_Dialog_User::JS_ACTION_PTF_ARENA_LIST;?>">
                <?php foreach (Stephino_Rpg_Db::get()->modelPtfs()->getCategories() as $arenaCatColumn => $arenaCatName):?>
                    <option value="<?php echo esc_attr($arenaCatColumn);?>"><?php echo esc_html($arenaCatName);?></option>
                <?php endforeach;?>
            </select>
        </div>
        <div class="col-12 col-lg-8">
            <button class="dot dot-two" data-click="<?php echo Stephino_Rpg_Renderer_Ajax_Dialog_User::JS_ACTION_PTF_ARENA_LIST;?>">
                <span>&#x25BC;</span>
                <span>&#x25B2;</span>
            </button>
            <span class="float-right">
                <?php if ($authorId > 0):?>
                    <?php echo esc_html__('Games by', 'stephino-rpg');?>
                    <span data-click="userViewProfile" data-click-args="<?php echo $authorId;?>">
                        <?php echo esc_html($authorName);?>
                    </span>
                    <button class="btn btn-info" data-click="dialog" data-click-args="dialogUserArenaList">
                        <span><?php echo esc_html__('All games', 'stephino-rpg');?></span>
                    </button>
                <?php else:?>
                    <span 
                        data-effect="help"
                        data-effect-args="<?php echo Stephino_Rpg_Config_Core::KEY;?>,<?php echo Stephino_Rpg_Renderer_Ajax_Dialog_Help::CORE_SECTION_GAME_ARENA;?>">
                        <?php echo esc_html__('Rules', 'stephino-rpg');?>
                    </span>
                    <?php if (Stephino_Rpg_Cache_User::get()->isGameMaster() || Stephino_Rpg_Config::get()->core()->getPtfAuthorLimit() > 0):?>
                        <button 
                            class="btn btn-info" data-click="dialog" data-click-args="dialogUserArenaList,<?php echo $userId;?>">
                            <span><?php echo esc_html__('My games', 'stephino-rpg');?></span>
                        </button>
                    <?php endif;?>
                <?php endif;?>
            </span>
        </div>
    </div>
</div>
<div class="col-12 framed p-4">
    <div class="row mt-4 align-items-center" 
         data-role="arena-list-page" 
         data-author="<?php echo $authorId;?>" 
         data-effect="<?php echo Stephino_Rpg_Renderer_Ajax_Dialog_User::JS_ACTION_PTF_ARENA_LIST;?>"></div>
</div>
<?php if ($authorId == $userId && $userCanCreate && !$userSuspended): ?>
    <div class="col-12 framed p-4">
        <div class="row">
            <div class="col-12">
                <button class="btn w-100" data-click="dialog" data-click-args="dialogUserArenaEdit">
                    <span>
                        <?php echo esc_html__('Create game', 'stephino-rpg');?>
                        (<?php echo $userGamesCreated;?> / <b><?php echo Stephino_Rpg_Config::get()->core()->getPtfAuthorLimit();?></b>)
                    </span>
                </button>
            </div>
        </div>
    </div>
<?php endif;?>