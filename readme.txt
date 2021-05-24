=== Stephino RPG (Game) ===
Contributors: stephino
Plugin link: https://stephino.com
Donate link: https://gum.co/stephino-rpg
Tags: rpg, fun, gutenberg, game, games, platformer, pwa, strategy, mmorpg
Requires at least: 5.0
Tested up to: 5.7
Stable tag: trunk
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.txt

Host a stunning browser-based multiplayer RPG (Role-Playing Game) for the first time ever on WordPress.

== Description ==

This is a browser-based multi-player strategy role-playing game you and your friends can enjoy anytime!

The main goal is to expand your empire and complete research activities in order to uncover the history of your species.

You can form cities, attack other players, send resources between your cities, complete research activities and use premium modifiers to boost your gameplay.

Create your own platformer mini-games and play games created by others to earn rewards.

= Demo and Support =
You can [access the Demo](https://stephino.com) by simply logging in with a Google or Twitter account.

We strongly believe in the right to be forgotten so when you're done testing the game just click on the "Delete Account" button from the game settings.

Real-time support and feedback are available on [Discord](https://discord.gg/32gFsSm).

= Play with AI =
You can play this game by yourself or against robots or other players.

Starting with version `0.1.2`, robots can perform the following tasks:

 * Create Buildings according to the Building Advisor
 * Randomly upgrade existing Buildings
 * Perform Research activities
 * Assign workers to Buildings

Starting with version `0.3.2`, robots have military capabilities:

 * Queue military units and ships
 * Estimate the best time for attack
 * Systematically attack players

You can control the robot military activity with the following configuration items:

 * **Aggression**: low/medium/high
 * **Fervor**: between 5 and 100; the higher the number, the more active robots are

= Platformer =
Design your own platformer mini-levels and play games created by others to earn gems!

= Progressive Web Application =
The game can be played on any mobile device in landscape mode and on the desktop.
It functions as a progressive web application, handling offline mode and file requests in a way that mimics truly native applications.

= Optimized CPU usage =
Since cron tasks cannot be used in WordPress, resource gathering and other actions are computed on-demand.
The algorithm was optimized to minimize database interactions and provide a seamless real-time experience for all players.

= Optimized bandwidth =
In order to deliver the best possible experience to your players, game assets are automatically stored in the browser **cache storage** using a service worker.
This way there are no redundant requests made to your server, resulting in a snappy experience for your players and lower bandwidth usage.
Image sprites are used to reduce the number of requests to your server further and all image files have been compressed.

= Game Mechanics  =
Everything is customizable, from the game name and description to what each game object does.
For example, you can change how fast resources are gathered by altering **polynomials**. 
Available polynomials and their multiplicative inverses:

* **linear**: `a⋅x + c`
* **quadratic**: `a⋅x² + b⋅x + c`
* **exponential**: `a⋅bˣ + c`

Nothing was hard-coded, not even the tutorial, so you can change the game any way you like.

= Admin Console =
As an admin, you have complete control over your game.
Just press **Alt+Ctrl+C** to toggle the console and type **help** to list all available commands.
You can add resources to players, change building levels, fast-forward the game and more.
New abilities will be added from time to time.

= Themes (PRO) =
Modify the design of your game to your heart's content directly from your browser.
Themes are meant to be portable, i.e. you can export your game design and game mechanics changes as your very own themes
in the form of Zip archives.

= Microtransactions (PRO) =
You can enable microtransactions using PayPal and start monetizing your game.

== Installation ==

1. Visit 'Plugins > Add New'
2. Search for 'Stephino RPG'
3. Install 'Stephino RPG' once it appears
4. Activate 'Stephino RPG' from your Plugins page
5. Go to 'after activation' below

= Manually =

1. Upload the `stephino-rpg` folder to the `/wp-content/plugins/` directory
2. Activate the 'Stephino RPG' plugin through the 'Plugins' menu in WordPress
3. Go to 'after activation' below

= After activation =

1. You should see the 'Play Mors' menu item
2. The game is now installed and playable. Enjoy!

You can optionally enable new players registration from 'Settings > General > Membership'.

== Frequently Asked Questions ==

= How to get started? =
Just follow the short tutorial and the advice of the **Upgrade Advisor**.
The Codex is also available to learn more about how the game works.
Just click on **Settings** > **Help** inside the game.

For more information and real-time support from other players, please head on to [Discord](https://discord.gg/32gFsSm).

= How do I embed this? =
You can use either the **[stephino-rpg]** shortcode or the **Stephino RPG** Gutenberg block anywhere you want.
Unauthenticated players will be greeted with a login page.

= Where is the music? =
Since there is a 10MB upload limit on WordPress.org, the music and other assets such as video files and other effects have been moved to the PRO plugin.

== Screenshots ==

1. Create and upgrade buildings, gather and trade resources, recruit units, ships and more
2. Colonize empty slots, attack other players, send spies, prepare transport convoys and more
3. Perform research activities and uncover the hidden secrets of your species
4. Use **Alt+Ctrl+C** to enable the in-game console and perform administrative tasks
5. Customize all game objects and even monetize your game with PayPal micro-transactions (PRO)!
6. Earn gems by designing and playing platformer mini-games in the game arena

== Changelog ==

= [0.3.6] 2021-05-24 =
* Enhancements
  * PWA Just-in-time cache control in `Game Mechanics > Core > Performance`
  * Theme import and export
* Bug fixes
  * Fixed audio loading bug
  * Fixed PWA caching issues
  * Fixed 768 assets 404 errors for the free version
  * Fixed chat room pruning bug
* Additional changes
  * Theme editor improvements (cache clean and UI)

= [0.3.5] 2021-05-20 =
* Notices
  * Please upgrade Stephino RPG **PRO** to version **0.2.1**
  * **Chat Room**: Added Firebase anonymous login; please go to `Game Mechanics > User Content > Chat Room > Getting Started` and follow steps **6** and **9** to enable Anonymous Sign-in and update the Realtime Database rules
* Enhancements
  * **Themes**: you can now safely edit your own themes directly from the browser with auto-update enabled
  * Chat Room: Messages can be deleted by authors; removed "message sent" sound and other improvements
  * **Messages i18n**: messages are now stored as JSON configuration objects instead of plain HTML, resulting in a much smaller database footprint and dynamic templating of messages
  * **Game Masters**: Promote/demote players to game masters, allowing them more control over the game i.e. console, Game Arena and others; game masters can promote/demote other players including themselves but not super-admins; super-admins are the only ones allowed to access administrative parts of the game, such as the `Dashboard`, `Themes`, `Game Mechanics` etc.
  * **Item unlocked** notifications
* Bug fixes
  * Multisite (MU): Network Admin menu
  * Fixed message pruning and delete bugs
  * Fixed avatar URL mismatch
  * Fixed interaction with other themes and plugins UI
  * Fixed Discovery mix-up bug
  * Fixed minor i18n issues in resource dialogs
  * Fixed entity suggestions for attack and transport actions
  * Fixed max. queue bug
* Additional changes
  * Updated Firebase to version 8.4.3
  * Improved messages layout: user messages vs. system messages and other
  * Unfulfilled requirements point to the corresponding building/research field instead of the help menu
  * Unified item card behavior
  * Added platformer game ID to interface

= [0.3.4] 2021-02-19 =
* Enhancements
  * Robots earn tutorial rewards automatically
* Bug fixes
  * Fixed English labels bug
  * Fixed several translation issues
* Additional changes
  * Improved "Extra info" display for game masters

= [0.3.3] 2021-02-14 =
* Enhancements
  * Player-level **language selection**
  * Game master-level control over translations
  * Configuration option for allowed languages (besides English)
  * Game arena: added game ratings
  * Game arena: implemented review process and a suspension policy
  * Extra player information for game masters
  * Added "Prepare app" button for the progressive app installation
  * Prepared for WordPress v.5.7
* Bug fixes
  * Fixed discretization method integer overflow bug and improved performance
  * Fixed maximum storage bug in Stock Market
  * Fixed i18n bug
* Additional changes
  * All `data-click` buttons except toggles accept only 1 click
  * Improved default configuration values (performance, premium packages, island statues and others)
  * Added support for case-insensitive commands in the CLI
  * Database reloads when reactivating plugin, performing `dbUpdate` and game arena bundled games updates

= [0.3.2] 2021-01-31 =
* Enhancements
  * Robots can now create military entities, **attack** on their own and **fight back**
  * Significant improvements in performance
  * Added 3 new platformer levels
* Bug fixes
  * Fixed Firebase Chat Integration; new configuration item
  * Fixed performance issues related to `dbUpdate`
* Additional changes
  * Added Firebase "Getting Started" help page to configuration
  * Added messages pagination; new configuration item
  * Added full-screen button to log-in form
  * Changed full-screen button behavior: no more redirects
  * Added leader board size configuration item
  * Improved default configuration values for some entities and buildings
  * `Platformer Author Limit` set to **0** now disables game authorship by players

= [0.3.1] 2021-01-26 =
* Enhancements
  * Added **6** new levels!
  * Platformer games can now be enjoyed in uninterrupted full-screen mode
  * Improved platformer games UI on mobile devices
  * Added game arena statistics to leader board
  * Improved pre-defined platformers reload method
* Bug fixes
  * Fixed important bug related to DB migration
  * Fixed UI issues on mobile devices
* Additional changes
  * Removed `.po` files
  * Total score increases with game arena victories (new configuration item)

= [0.3.0] 2021-01-21 =
* Enhancements
  * Games arena: sorting, pagination, author page, rewards and more
  * Action buttons to Queues/Garrison dialogs
  * Building level indicator and upgrade arrows in city view
  * City level and statue level indicators to island view
  * Occupied slots indicators to world view
  * Garrison effects to entity recruit/disband dialogs
* Bug fixes
  * Fixed Bug: Research field dequeue refund
* Additional changes
  * Improved multi-update SQL helper
  * Improved Changelog announcement
  * Announcements are marked as read on player action
  * Moved garrison effect to garrison dialog
  * Removed the `stephino_rpg_ptf_plays` DB table

= [0.2.9] 2021-01-11 =
* Enhancements
  * Improved platformer performance on some browsers
  * Added sections to Game Mechanics > Core
  * Added total score details to help menu
  * Added dequeue action for entities
* Bug fixes
  * Zoom fix on mobile devices
  * Fixed Bug: Population ignored for main building military attributes
* Additional changes
  * Added PWA install button to "Settings" > "More Actions"
  * More robust import tools
  * Added `list-city-military`
  * Moved total score class constants to Game Mechanics > Core > User Score
  
= [0.2.8] 2020-12-07 =
* Enhancements
  * Added `list-ptf` console command
  * Improved PTF reload algorithm
  * Improved Game Creator
  * Improved console suggestions for misspelled commands
  * Improved `set-city-building` console method (recursive dependency check)
  * Added `set-city-research-field` console method w/ recursive dependency check
  * Added configuration import utility
* Bug fixes
  * Fixed entity recruitment bug
  * Fixed military time-lapse bug
  * Fixed missing convoys icon bug
* Additional changes
  * UI fixes
  * Better description of units/ships attack/defense points in the codex

= [0.2.7] 2020-11-24 =
* Bug fixes
  * Fixed crucial "dbDelta" bug

= [0.2.6] 2020-11-24 =
* Enhancements
  * Introducing the game arena
  * Custom Log In text
  * More i18n work
  * Improved performance related to Robot Crons
* Bug fixes
  * Fixed "dbDelta" bug
  * Fixed Stephino_Rpg_Cache_User bug
  * Fixed double escaping bug in Game Mechanics
  * Fixed game volume bug
  * Fixed first-time login bug
* Additional changes
  * Restored "Privacy Policy" link to Log In form

= [0.2.5] 2020-11-11 =
* Bug fixes
  * Fixed a null pointer error
* Enhancements
  * Improved performance

= [0.2.4] 2020-11-10 =
* Bug fixes
  * Fixed several UI issues on mobile devices
  * Fixed Statistics model bug
  * Fixed auto-play warnings
* Enhancements
  * Added in-game Leader Board
  * Added Config option for showing the "Reload" button on mobile devices
  * More i18n work
  * Added cities list in player profile
* Additional changes
  * Added 2 new icons
  * Code clean-up

= [0.2.3] 2020-11-02 =
* Bug fixes
  * Fixed null pointer bug
* Enhancements
  * Added option to remove the link to WordPress.org
  * Added latest Changelog in Credits
  * Added Leader Board in Dashboard
* Additional changes
  * Compatibility with WordPress v.5.6 beta

= [0.2.2] 2020-10-25 =
* Enhancements
  * Improved Log In interface layout
  * Integrated with "Nextend Social Login"
  * Improved Log Out utility
  * Automatically log in players after registration (for our game only)

= [0.2.1] 2020-10-13 =
* Enhancements
  * Added Custom Log-In screen for the Gutenberg block
* Bug fixes
  * Several UI issues fixed

= [0.2.0] 2020-10-11 =
* Bug fixes
  * Chat interface minor bugs fixed
* Enhancements
  * Improved performance
  * Added configuration values to fine-tune performance-accuracy trade-off
  * Added Gutenberg Game Block
  * Added [stephino-rpg] shortcode
* Additional changes
  * Requires at least WordPress 5.0+
  * Removed link to WordPress profile
  * Added password changer to in-game profile

= [0.1.9] 2020-10-08 =
* Bug Fixes
  * Dashboard bug fixes
* Enhancements
  * Added Chat Room using Firebase Realtime Database

= [0.1.8] 2020-09-27 =
* Bug Fixes
  * Fixed UI issues
* Enhancements
  * i18n for the game templates (700+ strings)
  * Improved look and feel
  * Improved reliability of walk function in Config::getDefault
* Additional changes
  * Ability to use custom strings for core configuration items (i.e. units, ships etc.)

= [0.1.7] 2020-09-25 =
* Bug Fixes
  * Fixed i18n strings not loading correctly
* Enhancements
  * i18n for the game configuration strings
  * Improved readability on the default game configuration
  * Added minimum version requirement for the PRO plugin
* Additional changes
  * Custom game cursors

= [0.1.6] 2020-09-22 =
* Bug Fixes
  * Fixed configuration error

= [0.1.5] 2020-09-22 =
* Enhancements
  * Added SandBox Mode
* Bug Fixes
  * Fixed typos
* Additional changes
  * Removed workers from the Museum building
  * Tweaked satisfaction and other metrics

= [0.1.4] 2020-09-10 =
* Enhancements
  * Improved internationalization
  * Improved transactions table
* Bug Fixes
  * Tutorial bug that allowed for infinite money
  * Display issues in the Dashboard
* Additional changes
  * Made the locked sections clearer

= [0.1.3] 2020-09-08 =
* Enhancements
  * Improved the *Dashboard* active players tracking accuracy whilst maintaining GDPR compliance
  * Added the *Announcement* tool, allowing you to communicate important news to your players
* Bug Fixes
  * Tutorial arrow orientation
* Additional changes
  * The default tutorial is now shorter

= [0.1.2] 2020-09-01 =
* Enhancements
  * The robots are alive! They can now create and upgrade buildings, assign workers and perform research activities
  * Added the *Dashboard* where you can check your transactions, active players, total players, online players and revenue
* Bug Fixes
  * Fixed timelapse update procedure warning
* Additional changes
  * More robust script/style filtering for game addons
  * The "Game Mechanics > Core > Robots Fervor" control was enabled

= [0.1.1] 2020-08-17 =
* Enhancements
  * Improved discretization method to allow for graceful degradation over large periods of time
  * Improved layout on mobile devices
  * Added *Refresh* button on mobile
* Bug Fixes
  * Fixed tutorial UX issues on mobile devices
  * Multisite integration
  * Fixed city garrison bug with newly formed city
  * Fixed *jquery-draggable*, *jquery-droppable* library issues in the **Game Mechanics** interface
  * Fixed *transport/spy/attack* dialog support outside of the island view
  * Fixed island view *colonization* dialog
  * Fixed last tutorial step error on mobile devices (*touchstart* event not triggered)
* Additional changes
  * The game page now integrates with the *Admin Color Scheme*
  * Removed the *kg* unit for mass

= [0.1.0] 2020-08-11 =
* First release