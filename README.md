<p align="center">
    <a href="https://wordpress.org/plugins/stephino-rpg">
        <img src="https://repository-images.githubusercontent.com/330349397/837e4b00-d982-11eb-9f6d-ad49da7a5665"/>
    </a>
</p>

# Stephino RPG (Game)

Host a stunning browser-based multiplayer RPG (Role-Playing Game) for the first time ever on [WordPress](https://wordpress.org/plugins/stephino-rpg).

## Description

This is a browser-based multi-player strategy role-playing game you and your friends can enjoy anytime!

The main goal is to expand your empire and complete research activities in order to uncover the history of your species.

You can form cities, attack other players, send resources between your cities, complete research activities and use premium modifiers to boost your gameplay.

Create your own platformer mini-games and play games created by others to earn rewards.

### A product of passion
If you want to learn more about why this project exists, I invite you to watch [this YouTube video](https://youtu.be/6d_Yx_WmHBo).

### Demo and Support
You can [access the Demo](https://stephino.com) by simply logging in with a Google or Twitter account.

We strongly believe in the right to be forgotten so when you're done testing the game just click on the "Delete Account" button from the game settings.

Real-time support and feedback are available on [Discord](https://discord.gg/32gFsSm).

### Play with AI
You can play this game by yourself or against robots or other players.

Starting with version 0.1.2, robots can perform the following tasks:

 * Create Buildings according to the Building Advisor
 * Randomly upgrade existing Buildings
 * Perform Research activities
 * Assign workers to Buildings

Starting with version 0.3.2, robots have military capabilities:

 * Queue military units and ships
 * Estimate the best time for attack
 * Systematically attack players

You can control the robot military activity with the following configuration items:

 * **Aggression**: low/medium/high
 * **Fervor**: between 5 and 100; the higher the number, the more active robots are

### Platformer
Design your own platformer mini-levels and play games created by others to earn gems!

### Progressive Web Application
The game can be played on any mobile device in landscape mode and on the desktop.
It functions as a progressive web application, handling offline mode and file requests in a way that mimics truly native applications.

### Optimized CPU usage
Since cron tasks cannot be used in WordPress, resource gathering and other actions are computed on-demand.
The algorithm was optimized to minimize DataBase interactions and provide a seamless real-time experience for all users.

### Optimized bandwidth
In order to deliver the best possible experience to your players, game assets are automatically stored in the browser **cache storage** using a service worker.
This way there are no redundant requests made to your server, resulting in a snappy experience for your players and lower bandwidth usage.
Image sprites are used to reduce the number of requests to your server further and all image files have been compressed.

### Game Mechanics (PRO)
Everything is customizable, from the game name and description to what each game object does.
For example, you can change how fast resources are gathered by altering **polynomials**. 
Available polynomials and their multiplicative inverses:

* **linear**: `a⋅x + c`
* **quadratic**: `a⋅x² + b⋅x + c`
* **exponential**: `a⋅bˣ + c`

Nothing was hard-coded, not even the tutorial, so you can change the game any way you like.
Future versions will include the ability to install game "themes", plugins that contain unique game mechanics and graphics that work with the Stephino RPG framework.

### Admin Console
As an admin, you have complete control over your game.
Just press **Alt+Ctrl+C** to toggle the console and type **help** to list all available commands.
You can add resources to users, change building levels, fast-forward the game and more.
New abilities will be added from time to time.

### Microtransactions (PRO)
You can enable microtransactions using PayPal and start monetizing your game.
