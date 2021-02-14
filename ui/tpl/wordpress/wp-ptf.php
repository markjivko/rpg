<?php
/**
 * Template:Ptf
 * 
 * @title      Platformer template
 * @desc       Template for the in-game platformer
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

// Get the game ID
$gameId = abs((int) Stephino_Rpg_Utils_Sanitizer::getViewData());
$gameVersion = 1;

// Check the game exists
$nextGameId = null;
if (null !== $gameTileMap = Stephino_Rpg_Db::get()->modelPtfs()->getTileMap($gameId)) {
    $gameVersion = $gameTileMap['version'];
    
    // Get the current user ID
    $userId = Stephino_Rpg_TimeLapse::get()->userId();

    // Get the next platformer ID
    $nextGameId = Stephino_Rpg_Db::get()->modelPtfs()->getNextId($userId, $gameId);
} else {
    $gameId = 0;
}
$userRatings = Stephino_Rpg_Cache_User::get()->read(Stephino_Rpg_Cache_User::KEY_PTF_RATES, array());
if (!is_array($userRatings)) {
    $userRatings = array();
}
?><!doctype html>
<html <?php language_attributes(); ?>>
    <head>
        <?php wp_head(); ?>
        <link rel="shortcut icon" type="image/png" href="<?php echo esc_attr(Stephino_Rpg_Utils_Media::getPluginsUrl() . '/ui/img/icon.png'); ?>" />
        <link rel="apple-touch-icon" href="<?php echo esc_attr(Stephino_Rpg_Utils_Media::getPluginsUrl() . '/themes/' . Stephino_Rpg_Config::get()->core()->getTheme() . '/img/ui/192.png'); ?>">
        <!--[if lt IE 10]><meta http-equiv="Refresh" content="0; url=<?php echo esc_attr(get_dashboard_url());?>"><![endif]-->
        <meta charset="utf-8" />
        <meta http-equiv="x-ua-compatible" content="IE=edge">
        <meta name="viewport" content="initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, shrink-to-fit=no" />
        <meta name="mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="theme-color" content="#222222" />
        <meta name="author" content="Mark Jivko" />
        <meta name="copyright" content="(c) 2021, Stephino" />
        <meta name="generator" content="<?php echo Stephino_Rpg::PLUGIN_SLUG;?>" />
        <meta name="version" content="<?php echo Stephino_Rpg::PLUGIN_VERSION;?>" />
        <meta name="description" content="<?php echo esc_html__('The first-ever RPG developed for WordPress', 'stephino-rpg');?>" />
    </head>
    <body oncontextmenu="return false;" data-id="<?php echo $gameId;?>" data-version="<?php echo $gameVersion;?>" data-plugin-version="<?php echo Stephino_Rpg::PLUGIN_VERSION;?>">
        <div class="panel">
            <div data-role="score"></div>
            <div data-role="mobile-toggle"></div>
            <div data-role="full-screen"></div>
        </div>
        <div data-btn="w"></div>
        <div data-btn="a"></div>
        <div data-btn="s"></div>
        <div data-btn="d"></div>
        <div id="stephino-rpg-ptf">
            <div class="not-found">
                <?php echo esc_html__('Game not found', 'stephino-rpg');?>
            </div>
        </div>
        <div class="framed active row" data-role="ptf-popup">
            <span class="col-12">
                <span class="success">
                    <h4><?php echo esc_html__('Congratulations!', 'stephino-rpg');?></h4>
                    <div class="ptf-reward text-center mb-2 d-none">
                        <span class="res res-<?php echo Stephino_Rpg_Renderer_Ajax::RESULT_RES_GEM;?>">
                        <div class="icon"></div>
                            <span>
                                <?php echo esc_html__('You have earned', 'stephino-rpg');?> <span class="value"></span> 
                                <b><?php echo Stephino_Rpg_Config::get()->core()->getResourceGemName(true);?></b>
                            </span>
                        </span>
                    </div>
                </span>
                <span class="failure">
                    <h4><?php echo esc_html__('Game over', 'stephino-rpg');?></h4>
                </span>
            </span>
            <div class="col-12">
                <div data-role="ptf-rating">
                    <?php 
                        for ($rating = 1; $rating <= 5; $rating++):
                            $ratingActive = isset($userRatings[$gameId]) && $userRatings[$gameId] >= $rating;
                    ?>
                        <i data-rating="<?php echo $rating;?>"<?php if ($ratingActive):?> class="active"<?php endif;?>></i>
                    <?php endfor;?>
                </div>
            </div>
            <span class="col">
                <button class="btn btn-info w-100" data-role="ptf-refresh">
                    <span><?php echo esc_html__('Play again', 'stephino-rpg');?></span>
                </button>
            </span>
            <?php if (null !== $nextGameId):?>
                <span class="col">
                    <button class="btn btn-warning w-100" data-role="ptf-next" data-id="<?php echo $nextGameId;?>">
                        <span><?php echo esc_html__('Next game', 'stephino-rpg');?></span>
                    </button>
                </span>
            <?php endif;?>
        </div>
    </body>
</html>