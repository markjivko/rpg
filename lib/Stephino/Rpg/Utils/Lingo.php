<?php
/**
 * Stephino_Rpg_Utils_Lingo
 * 
 * @title     Utils:Lingo
 * @desc      Language utils
 * @copyright (c) 2020, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Utils_Lingo {
    
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
        
        // All done
        return $result;
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
        $result = 'Unknown';
        do {
            // Invalid input
            if (!is_array($userDbRow) && !isset($userDbRow[Stephino_Rpg_Db_Table_Users::COL_ID])) {
                break;
            }

            // A robot
            if (!isset($userDbRow[Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID]) 
                || !is_numeric($userDbRow[Stephino_Rpg_Db_Table_Users::COL_USER_WP_ID])) {
                $result = 'Robot #' . $userDbRow[Stephino_Rpg_Db_Table_Users::COL_ID];
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