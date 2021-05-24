<?php

/**
 * Stephino_Zip_Enum_Version
 * 
 * @title      Zip
 * @desc       Zip utility: Versions
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 * 
 * @method static STORE():   Stephino_Zip_Enum_Version
 * @method static DEFLATE(): Stephino_Zip_Enum_Version
 * @method static ZIP64():   Stephino_Zip_Enum_Version
 */
class Stephino_Zip_Enum_Version extends Stephino_Zip_Enum {

    const STORE   = 0x000A; // 1.00
    const DEFLATE = 0x0014; // 2.00
    const ZIP64   = 0x002D; // 4.50

}

/*EOF*/