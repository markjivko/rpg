<?php

/**
 * Uninstaller
 * This is also an entry point.
 * 
 * @title      Uninstall procedure
 * @desc       Perform the script uninstall
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
if (!defined('ABSPATH') || !defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

// Define the root
!defined('STEPHINO_RPG_ROOT') && define('STEPHINO_RPG_ROOT', dirname(__FILE__));

// Prepare the autoloader
require_once STEPHINO_RPG_ROOT . '/lib/Stephino/Rpg/Autoloader.php';

// Remove all options
Stephino_Rpg_Autoloader::getInstance();
Stephino_Rpg::get()->purge();

// Drop all tables
Stephino_Rpg_Db::get()->purge();

/*EOF*/