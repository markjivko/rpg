/**
 * JS Options
 * 
 * @title      Game Integration
 * @desc       Prepare the PWA manifest and other common actions
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
/* global wp_rpg_tools, wp_rpg_data */
// Stephino RPG: Admin - Play
jQuery && jQuery(document).ready(function() { wp_rpg_tools && wp_rpg_tools.pwa.init();});