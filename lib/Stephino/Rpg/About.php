<?php

/**
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
 * and Lukáš Tvrdoň. 
 * Due to the **10MB** limit imposed by WordPress.org, the audio files could not be included in the free version.
 * 
 * ##### Libraries
 * 
 * Thanks to all the people behind these awesome frameworks/libraries:
 * [James Simpson](https://twitter.com/GoldFireStudios) @ [Howler.js](https://github.com/goldfire/howler.js),
 * [Federico Zivolo](https://twitter.com/fezvrasta) @ [Popper.js](https://github.com/FezVrasta/popper.js),
 * [Jonathan Puckey](https://twitter.com/jonathanpuckey) & [Jürg Lehni](https://twitter.com/juerglehni) @ 
 * [Paper.js](https://github.com/paperjs/paper.js),
 * [Jorik Tangelder](https://twitter.com/jorikdelaporik) @ [Hammer.js](https://github.com/hammerjs/hammer.js),
 * [Richard Davey](https://twitter.com/photonstorm) @ [Phaser JS](https://github.com/photonstorm/phaser),
 * [André Ruffert](https://twitter.com/andreruffert) @ [RangeSlider.js](https://github.com/andreruffert/rangeslider.js),
 * [Bootstrap](https://github.com/twbs/bootstrap), [jQuery](https://github.com/jquery/jquery) and 
 * [jQuery UI](https://github.com/jquery/jquery-ui).
 * 
 * ##### Thank you!
 * 
 * Special thanks to all those who contributed to Stephino RPG on [KickStarter](https://www.kickstarter.com/projects/markjivko/stephino-rpg): 
 * Stefy, Iuly, Patrick Lawitzky, Floris Robbert Bronzwaer, Nicholas Wilson, 
 * [Digissues.co](https://twitter.com/digissues), [JayKubKolTun](https://twitter.com/JayKubKolTun), [Jakob Schindler-Scholz](https://twitter.com/jakobscholz), 
 * [Smitty2447](https://twitter.com/Smitty2447), Crimson D, JinjaGamer, Kevin Streat, [Ward Pederson](https://twitter.com/WarPed1), Eddy Dauber 
 * and Carl Wiseman.
 * 
 * Thank you all! This game could not have grown without your support!
 * 
 * @title      About
 * @desc       About area handler
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_About {
    
    /**
     * Markdown strings
     *
     * @var null|string[]
     */
    protected static $_markdowns = null;

    /**
     * Get the HTML(s)
     * 
     * @param boolean $asArray (optional) Get the result as an array of strings; default <b>false</b>
     * @return string|array HTML or array of strings: Credits, Changelog HTMLs
     */
    public static function html($asArray = false) {
        if ($asArray) {
            return array_map(
                function($item) {
                    return Stephino_Rpg_Parsedown::instance()->parse($item);
                }, 
                self::markDown()
            );
        }
        
        return Stephino_Rpg_Parsedown::instance()->parse(
            implode(
                PHP_EOL . PHP_EOL, 
                self::markDown()
            )
        );
    }

    /**
     * Get the info MarkDown syntax
     * 
     * @return string[] Array of strings: Credits, Changelog MarkDowns
     */
    public static function markDown() {
        if (null === self::$_markdowns) {
            // Get the Class comment
            self::$_markdowns = array(
                // Credits
                trim(
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
                ),
                
                // Changelog
                trim(
                    preg_replace(
                        '%^.*?\=\=\s*(Change[\- ]*?log)\s*\=\=.*?^\=\s*\[((?:\d+\.)+\d)\s*\]\s*([\d\-]+)\s*\=(.*?)(?=\=).*%ims', 
                        "##### $1 [v. $2](" . Stephino_Rpg::PLUGIN_URL_WORDPRESS . ") - $3\n$4", 
                        file_get_contents(STEPHINO_RPG_ROOT . '/readme.txt')
                    )
                ),
            );
        }
        return self::$_markdowns;
    }
}

/*EOF*/