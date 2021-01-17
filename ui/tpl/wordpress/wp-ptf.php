<?php
/**
 * Template:Ptf
 * 
 * @title      Platformer template
 * @desc       Template for the in-game platformer
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

// Get the game ID
$gameId = abs((int) Stephino_Rpg_Utils_Sanitizer::getViewData());
$gameVersion = 1;

// Check the game exists
if (null !== $gameTileMap = Stephino_Rpg_Db::get()->modelPtfs()->getTileMap($gameId)) {
    $gameVersion = $gameTileMap['version'];
} else {
    $gameId = 0;
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
        <meta name="copyright" content="(c) 2020, Stephino" />
        <meta name="generator" content="<?php echo Stephino_Rpg::PLUGIN_SLUG;?>" />
        <meta name="version" content="<?php echo Stephino_Rpg::PLUGIN_VERSION;?>" />
        <meta name="description" content="<?php echo esc_html__('The first-ever RPG developed for WordPress', 'stephino-rpg');?>" />
    </head>
    <body data-id="<?php echo $gameId;?>" data-version="<?php echo $gameVersion;?>" data-plugin-version="<?php echo Stephino_Rpg::PLUGIN_VERSION;?>">
        <div data-role="score"></div>
        <div id="stephino-rpg-ptf">
            <div class="not-found">
                <?php echo esc_html__('Game not found', 'stephino-rpg');?>
            </div>
        </div>
    </body>
</html>