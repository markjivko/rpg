<?php

/**
 * The first-ever Multi-Player Online Role-Playing Game for WordPress!
 *
 * Plugin Name: Stephino RPG
 * Description: Host a stunning browser-based multiplayer RPG (Role-Playing Game) for the first time ever on WordPress
 * Author:      Mark Jivko
 * Author URI:  https://stephino.com
 * Version:     0.3.2
 * Text Domain: stephino-rpg
 * Domain Path: /languages
 * License:     GPL v3+
 * License URI: gnu.org/licenses/gpl-3.0.txt
 * 
 * @author      Mark Jivko <stephino.team@gmail.com>
 * @license     GNU General Public License, version 3
 * @copyright   2018 Stephino
 */
!defined('ABSPATH') && exit();

// Define the root
define('STEPHINO_RPG_ROOT', dirname(__FILE__));

// Prepare the autoloader
require_once STEPHINO_RPG_ROOT . '/lib/Stephino/Rpg/Autoloader.php';

// Run the game!
Stephino_Rpg_Autoloader::getInstance();
Stephino_Rpg::get();

/*EOF*/