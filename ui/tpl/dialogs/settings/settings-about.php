<?php
/**
 * Template:Dialog:About
 * 
 * @title      About dialog
 * @desc       Template for the About dialog
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $changeLogFlag boolean True when the dialog is triggered on game update */
/* @var $about string[] Array of Credits, Changelog */
?>
<div class="framed<?php if ($changeLogFlag):?> active<?php endif;?> p-3">
    <?php if (is_file(STEPHINO_RPG_ROOT . '/ui/img/changelog.png')):?>
        <div data-effect="parallax" data-effect-args="changelog,0"></div>
    <?php endif;?>
    <div data-effect="typewriter" data-effect-args="ul ul li"><?php echo $about[1];?></div>
    <?php if (strlen(Stephino_Rpg::PLUGIN_URL_DISCORD)):?>
        <a class="btn btn-info w-100" target="_blank" href="<?php echo esc_url(Stephino_Rpg::PLUGIN_URL_DISCORD);?>">
            <span><?php echo esc_html__('Feedback', 'stephino-rpg');?></span>
        </a>
    <?php endif;?>
</div>
<div class="framed p-3">
    <h4><?php echo esc_html__('Credits', 'stephino-rpg');?></h4>
    <?php echo $about[0];?>
    <a target="_blank" href="https://twitter.com/markjivko"><div data-effect="sign"></div></a>
</div>