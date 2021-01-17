<?php

/**
 * ##### Game design, game art, programming & testing
 * 
 * [Mark Jivko](https://twitter.com/markjivko)
 * 
 * ##### Sound design
 * 
 * Game soundtrack by [Philippe Gagné](https://www.youtube.com/channel/UC4V7ku98D4ARhOFvVtdFa0Q), 
 * audio experience possible thanks to
 * [Phil Michalski](https://twitter.com/pmsfx),
 * [Morten Søegaard](https://twitter.com/LittleRobotSfx),
 * [Heiko Lohmann](https://twitter.com/SoundPackTree),
 * [Stuart Keenan](https://twitter.com/Glitchedtones),
 * [Felix Blume](https://twitter.com/felixblume),
 * [Articulated Sounds](https://twitter.com/artisounds),
 * [Audio Hero](https://twitter.com/AudioHero_com),
 * [ZapSplat](https://twitter.com/zap_splat),
 * and Lukáš Tvrdoň. Due to the 10MB limit imposed by WordPress.org, the audio files could not be included in the free version.
 * 
 * ##### Libraries
 * 
 * Special thanks to all the people behind these awesome frameworks/libraries:
 * [Bootstrap](https://github.com/twbs/bootstrap),
 * [jQuery](https://github.com/jquery/jquery) and [jQuery UI](https://github.com/jquery/jquery-ui),
 * [James Simpson](https://twitter.com/GoldFireStudios) @ [Howler.js](https://github.com/goldfire/howler.js),
 * [Federico Zivolo](https://twitter.com/fezvrasta) @ [Popper.js](https://github.com/FezVrasta/popper.js),
 * [Jonathan Puckey](https://twitter.com/jonathanpuckey) & [Jürg Lehni](https://twitter.com/juerglehni) @ [Paper.js](https://github.com/paperjs/paper.js),
 * [Jorik Tangelder](https://twitter.com/jorikdelaporik) @ [Hammer.js](https://github.com/hammerjs/hammer.js),
 * [Richard Davey](https://twitter.com/photonstorm) @ [Phaser JS](https://github.com/photonstorm/phaser),
 * and [André Ruffert](https://twitter.com/andreruffert) @ [RangeSlider.js](https://github.com/andreruffert/rangeslider.js)
 * 
 * ##### Thank you!
 * 
 * Special thanks to all those who contributed to [Stephino RPG on KickStarter](https://www.kickstarter.com/projects/markjivko/stephino-rpg): 
 * Stefy, Iuly, Patrick Lawitzky, Bellerogrim, Nicholas Wilson, Digissues Co., Jakub, Jakob Schindler-Scholz, 
 * Ryan Smith, Crimson D, Joshua Elmore, Kevin Streat, Ward Pederson, Anigame and Carl Wiseman.
 * 
 * Thank you all! This game could not have grown without your support!
 * 
 * @title      Credits
 * @desc       Thank you and credits handler
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Credits {
    
    /**
     * Credits.md
     *
     * @var string
     */
    protected static $_creditsMarkDown = null;

    /**
     * Get the credits HTML
     * 
     * @return string HTML
     */
    public static function html() {
        // Parse the credits file
        return Stephino_Rpg_Parsedown::instance()->text(self::markDown());
    }

    /**
     * Get the credits in MarkDown syntax
     * 
     * @return string Credits
     */
    public static function markDown() {
        if (null === self::$_creditsMarkDown) {
            // Get the Class comment
            self::$_creditsMarkDown = trim(
                preg_replace(
                    array(
                        // Remove PHPDoc comments
                        '%^ \* \@.*?\n%im',
                        // Remove the comment border
                        '%^\s*(?:\/\*\*|\*\/|\* )%im',
                        // Lists are one-liners
                        '%,\s*\n%i'
                    ), 
                    array('', '', ', '), 
                    (new ReflectionClass(__CLASS__))->getDocComment()
                )
                . PHP_EOL . PHP_EOL 
                . preg_replace(
                    '%^.*?\=\=\s*(Change[\- ]*?log)\s*\=\=.*?^\=\s*\[((?:\d+\.)+\d)\s*\]\s*([\d\-]+)\s*\=(.*?)(?=\=).*%ims', 
                    "##### $1 [v. $2](" . Stephino_Rpg::PLUGIN_URL_WORDPRESS . ") - $3\n$4", 
                    file_get_contents(STEPHINO_RPG_ROOT . '/readme.txt')
                )
            );
        }
        return self::$_creditsMarkDown;
    }
}

/*EOF*/