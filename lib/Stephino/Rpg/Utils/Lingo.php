<?php
/**
 * Stephino_Rpg_Utils_Lingo
 * 
 * @title     Utils:Lingo
 * @desc      Language utils
 * @copyright (c) 2021, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Utils_Lingo {
    
    /**
     * Languages
     */
    const LANG_EN = 'en_US';
    const LANG_DE = 'de_DE';
    const LANG_ES = 'es_ES';
    const LANG_FR = 'fr_FR';
    const LANG_IT = 'it_IT';
    const LANG_PT = 'pt_BR';
    const LANG_RO = 'ro_RO';
    const LANG_RU = 'ru_RU';
    
    /**
     * List of allowed languages
     * 
     * @var array
     */
    const ALLOWED_LANGS = array(
        self::LANG_EN => 'English',
        self::LANG_DE => 'Deutsche',
        self::LANG_ES => 'Español',
        self::LANG_FR => 'Français',
        self::LANG_IT => 'Italiano',
        self::LANG_PT => 'Português',
        self::LANG_RO => 'Română',
        self::LANG_RU => 'Русский',
    );
    
    /**
     * Switch the locale and reload the text domain
     * 
     * @global string $locale
     * @global array $l10n
     * @param string $newLocale
     */
    public static function setLocale($newLocale) {
        global $locale, $l10n;
        $allowedLanguages = self::ALLOWED_LANGS;
        
        // Valid locale
        if (isset($allowedLanguages[$newLocale])) {
            // Set the locale
            $locale = $newLocale;

            // Reset the localization dictionary
            $l10n = null;

            // Short-circuit language determination
            add_filter(
                'pre_determine_locale', 
                function() use($newLocale) {
                    return $newLocale;
                }
            );

            // Re-load the text domain
            load_plugin_textdomain('stephino-rpg', false, 'stephino-rpg/languages');
        }
    }
    
    /**
     * Get the final game name, HTML escaped
     * 
     * @return string
     */
    public static function getGameName() {
        return Stephino_Rpg_Config::get()->core()->getName(true);
    }
    
    /**
     * Get the city name, decorated if it's a Metropolis, already escaped
     * 
     * @param array $cityDbRow City information array containing the following keys:<ul>
     * <li>Stephino_Rpg_Db_Table_Cities::COL_CITY_NAME</li>
     * <li>Stephino_Rpg_Db_Table_Cities::COL_CITY_IS_CAPITAL</li>
     * </ul>
     * @return string Decorated city name
     */
    public static function getCityName($cityDbRow) {
        // Prepare the result
        $result = '';
        
        // Name defined
        if (is_array($cityDbRow) && isset($cityDbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_NAME])) {
            // Prefix the Metropolis symbol
            $result = (isset($cityDbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_IS_CAPITAL])
                    && $cityDbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_IS_CAPITAL]
                        ? Stephino_Rpg_Renderer_Ajax_Html::SYMBOL_CAPITAL . ' ' 
                        : ''
                ) . self::escape($cityDbRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_NAME]);
        }
        
        return $result;
    }
    
    /**
     * Get a configuration name
     * 
     * @param string $configKey Configuration key (plural), ex.: Stephino_Rpg_Config_Governments::KEY
     * @param boolean $singular (optional) Singular form; default <b>true</b>
     * @return string|null
     */
    public static function getConfigName($configKey, $singular = true) {
        $configNames = array(
            Stephino_Rpg_Config_Governments::KEY      => $singular
                ? Stephino_Rpg_Config::get()->core()->getConfigGovernmentName()
                : Stephino_Rpg_Config::get()->core()->getConfigGovernmentsName(),
            
            Stephino_Rpg_Config_Islands::KEY          => $singular
                ? Stephino_Rpg_Config::get()->core()->getConfigIslandName()
                : Stephino_Rpg_Config::get()->core()->getConfigIslandsName(),
            
            Stephino_Rpg_Config_IslandStatues::KEY    => $singular
                ? Stephino_Rpg_Config::get()->core()->getConfigIslandStatueName()
                : Stephino_Rpg_Config::get()->core()->getConfigIslandStatuesName(),
            
            Stephino_Rpg_Config_Cities::KEY           => $singular
                ? Stephino_Rpg_Config::get()->core()->getConfigCityName()
                : Stephino_Rpg_Config::get()->core()->getConfigCitiesName(),
            
            Stephino_Rpg_Config_Buildings::KEY        => $singular
                ? Stephino_Rpg_Config::get()->core()->getConfigBuildingName()
                : Stephino_Rpg_Config::get()->core()->getConfigBuildingsName(),
            
            Stephino_Rpg_Config_Units::KEY            => $singular
                ? Stephino_Rpg_Config::get()->core()->getConfigUnitName()
                : Stephino_Rpg_Config::get()->core()->getConfigUnitsName(),
            
            Stephino_Rpg_Config_Ships::KEY            => $singular
                ? Stephino_Rpg_Config::get()->core()->getConfigShipName()
                : Stephino_Rpg_Config::get()->core()->getConfigShipsName(),
            
            Stephino_Rpg_Config_ResearchAreas::KEY   => $singular
                ? Stephino_Rpg_Config::get()->core()->getConfigResearchAreaName()
                : Stephino_Rpg_Config::get()->core()->getConfigResearchAreasName(),

            Stephino_Rpg_Config_ResearchFields::KEY   => $singular
                ? Stephino_Rpg_Config::get()->core()->getConfigResearchFieldName()
                : Stephino_Rpg_Config::get()->core()->getConfigResearchFieldsName(),
            
            Stephino_Rpg_Config_PremiumModifiers::KEY => $singular
                ? __('Premium Modifier', 'stephino-rpg')
                : __('Premium Modifiers', 'stephino-rpg'),
        );
        
        return isset($configNames[$configKey]) ? $configNames[$configKey] : null;
    }
    
    /**
     * Get this user's nick-name
     * 
     * @param array $userDbRow User information array optionally containing the following keys: <ul>
     * <li>Stephino_Rpg_Db_Table_Users::COL_ID</li>
     * <li>Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID</li>
     * </ul>
     * @return string
     */
    public static function getUserName($userDbRow = array()) {
        $result = __('Unknown', 'stephino-rpg');
        do {
            // Invalid input
            if (!is_array($userDbRow) && !isset($userDbRow[Stephino_Rpg_Db_Table_Users::COL_ID])) {
                break;
            }

            // A robot
            if (!isset($userDbRow[Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID]) 
                || !is_numeric($userDbRow[Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID])) {
                $result = __('Robot', 'stephino-rpg') . ' #' . $userDbRow[Stephino_Rpg_Db_Table_Users::COL_ID];
                break;
            }

            // Get the user metadata
            $result = get_user_meta(
                $userDbRow[Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID], 
                Stephino_Rpg_WordPress::USER_META_NICKNAME, 
                true
            );
        } while(false);
        
        return $result;
    }
    
    /**
     * Get this user's bio
     * 
     * @param array $userDbRow User information array containing the following keys: <ul>
     * <li>Stephino_Rpg_Db_Table_Users::COL_ID</li>
     * <li>Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID</li>
     * </ul>
     * @return string
     */
    public static function getUserDescription($userDbRow) {
        $result = '';
        
        do {
            // Invalid input
            if (!is_array($userDbRow) && !isset($userDbRow[Stephino_Rpg_Db_Table_Users::COL_ID])) {
                break;
            }
            
            // A robot
            if (!isset($userDbRow[Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID]) 
                || !is_numeric($userDbRow[Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID])) {
                break;
            }
            
            // Get the user bio
            $result = get_user_meta(
                $userDbRow[Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID], 
                Stephino_Rpg_WordPress::USER_META_DESCRIPTION, 
                true
            );
        } while(false);
        
        return $result;
    }
    
    /**
     * Get available languages, filtered by the core configuration<br/>
     * Always includes English
     * 
     * @return string[] Associative array of [locale ("en_US") => language name ("English"), ...]
     */
    public static function getLanguages() {
        $result = self::ALLOWED_LANGS;
        
        $configLanguages = Stephino_Rpg_Config::get()->core()->getLanguages();
        foreach (array_keys($result) as $locale) {
            if (self::LANG_EN == $locale || in_array($locale, $configLanguages)) {
                continue;
            }
            unset($result[$locale]);
        }
        
        return $result;
    }
    
    /**
     * Get the current language in plain text
     * 
     * @return string
     */
    public static function getLanguage() {
        $allowedLanguages = self::ALLOWED_LANGS;
        $currentLanguageKey = Stephino_Rpg_Config::lang(true);
        
        return isset($allowedLanguages[$currentLanguageKey])
            ? $allowedLanguages[$currentLanguageKey]
            : __('Unknown', 'stephino-rpg');
    }
    
    /**
     * Get the i18n and HTML escaped "Game Mechanics" or "Labels" text, depending on the current user language
     * 
     * @param boolean $titleMode (optional) Title mode - use the current language instead of "translations"; default <b>false</b>
     * @return string
     */
    public static function getGameMechanics($titleMode = false) {
        return null === Stephino_Rpg_Config::lang() 
            ? 'Game Mechanics' 
            : (
                $titleMode
                    ? self::ALLOWED_LANGS[Stephino_Rpg_Config::lang(true)]
                    : esc_html__('Translations', 'stephino-rpg')
            );
    }
    
    /**
     * Clean-up user texts, removing HTML tags and extra spaces, and converting tabs into spaces
     * 
     * @param string $text Text to clean-up
     * @return string|null Null for empty strings
     */
    public static function cleanup($text) {
        // Prepare the result
        $result = is_string($text)
            ? trim(preg_replace('%[\t ]+%', ' ', strip_tags($text)))
            : '';
        
        // Valid string
        return strlen($result) ? $result : null;
    }
    
    /**
     * Shorten a text to a specified length and add ellipsis if necessary
     * 
     * @param string $text      Text
     * @param int    $maxLength Maximum length
     * @return string
     */
    public static function ellipsize($text, $maxLength) {
        if (strlen($text) > $maxLength) {
            $text = substr($text, 0, $maxLength - 3) . '...';
        }
        
        return $text;
    }
    
    /**
     * Prepare text for HTML output: html special characters and new line to br
     * 
     * @param string $text Text to prepare for HTML output
     * @return string HTML-ready string
     */
    public static function escape($text) {
        return nl2br(htmlspecialchars($text));
    }
    
    /**
     * Parse MarkDown to HTML
     * 
     * @param string $text MarkDown-formatted string
     * @return string HTML-formatted string
     */
    public static function markdown($text) {
        return Stephino_Rpg_Parsedown::instance()->text($text);
    }
    
    /**
     * HTML-formatted currency
     * 
     * @param float $number Number
     */
    public static function currency($number) {
        // Prepare the parts
        list($base, $exponent) = explode('.', number_format($number, 2));
        
        // Format the result
        return "$base<sup>$exponent</sup>";
    }
    
    /**
     * Format a number in International System of Units
     * 
     * @param float   $number          Number
     * @param int     $digits          (optional) Number of digits; default <b>1</b>
     * @param boolean $digitsOverMille (optional) Only use digits for numbers larger than 1000; default <b>true</b>
     * @return string Formatted number
     */
    public static function isuFormat($number, $digits = 1, $digitsOverMille = true) {
        // Prepare the SI symbols
        $siSymbols = array("", "k", "M", "G", "T", "P", "E");
        
        // Prepare the sign
        $sign = $number < 0 ? '-' : '';
        
        // Store the absolute value
        $number = abs($number);
        
        // Compare with our number
        foreach (array_reverse(array_keys($siSymbols)) as $siKey) {
            if ($number >= pow(10, 3 * $siKey)) {
                break;
            }
        }
        
        // Prepare the number of digits
        $formatDigits = ($digitsOverMille && $number < 1000 ? 0 : $digits);
        
        // Format
        return $sign . preg_replace(
            '/\.0+$|(\.[0-9]*[1-9])0+$/',
            '$1',
            number_format($number / pow(10, 3 * $siKey), $formatDigits)
        ) . $siSymbols[$siKey];
    }
    
    /**
     * Convert an integer symbolizing a number of seconds into a human-readable time interval
     * 
     * @example "1 hour, 2 minutes and 30 seconds"
     * @param int $timeInSeconds Duration in seconds
     * @return string|null Duration in "hours, minutes, seconds" format or NULL on error
     */
    public static function secondsHR($timeInSeconds) {
        // Prepare the result
        $result = null;
        
        // Valid interval
        if ($timeInSeconds > 0) {
            // Split the interval in hours, minutes and seconds
            $hours = floor($timeInSeconds / 3600);
            $minutes = ($timeInSeconds / 60) % 60;
            $seconds = $timeInSeconds % 60;

            // Prepare the result
            $resultArray = array();
            if ($hours > 0) {
                $resultArray[] = $hours . ' hour' . (1 == $hours ? '' : 's');
            }
            if ($minutes > 0) {
                $resultArray[] = $minutes . ' minute' . (1 == $minutes ? '' : 's');
            }
            if ($seconds > 0) {
                $resultArray[] = $seconds . ' second' . (1 == $seconds ? '' : 's');
            }

            // Get the last element from the list
            $resultArrayLast = (count($resultArray) >= 2 ? array_pop($resultArray) : null);
            
            // Store the final result
            $result = implode(', ', $resultArray) . (null !== $resultArrayLast ? (' and ' . $resultArrayLast) : '');
        }
        
        return $result;
    }
    
    /**
     * Convert an integer symbolizing a number of seconds into a H:i:s format, supporting more than 24 hours
     * 
     * @example "01:02:30"
     * @example "123:04:00"
     * @param int $timeInSeconds Duration in seconds
     * @return string Duration in "H:i:s" format
     */
    public static function secondsGM($timeInSeconds) {
        // Prepare the result
        $result = '00:00:00';
        
        // Valid interval
        if ($timeInSeconds > 0) {
            // Split the interval in hours, minutes and seconds
            $hours = floor($timeInSeconds / 3600);
            $minutes = ($timeInSeconds / 60) % 60;
            $seconds = $timeInSeconds % 60;
            
            // HHH:MM:SS
            $result = implode(
                ':' , 
                array_map(
                    function($item) {
                        return sprintf('%02d', $item);
                    }, 
                    array($hours, $minutes, $seconds)
                )
            );
        }
        return $result;
    }
    
    /**
     * Convert an integer to a Roman numeral
     * 
     * @param int $integer Integer
     * @return string
     */
    public static function arabicToRoman($integer) {
        // Convert the integer into an integer (just to make sure)
        $integer = abs((int) $integer);
        $result = '';

        // Create a lookup array that contains all of the Roman numerals.
        $lookup = array(
            'M' => 1000,
            'CM' => 900,
            'D' => 500,
            'CD' => 400,
            'C' => 100,
            'XC' => 90,
            'L' => 50,
            'XL' => 40,
            'X' => 10,
            'IX' => 9,
            'V' => 5,
            'IV' => 4,
            'I' => 1
        );

        foreach ($lookup as $roman => $value) {
            // Determine the number of matches
            $matches = intval($integer / $value);

            // Add the same number of characters to the string
            $result .= str_repeat($roman, $matches);

            // Set the integer to be the remainder of the integer and the value
            $integer = $integer % $value;
        }

        // The Roman numeral should be built, return it
        return $result;
    }
    
}

/*EOF*/