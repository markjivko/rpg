<?php
/**
 * Stephino_Rpg_Utils_Math
 * 
 * @title     Utils:Math
 * @desc      Math utils
 * @copyright (c) 2021, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Utils_Math {
    
    /**
     * Break-down a time interval into equal steps in order to perform discretization.<br/>
     * This is used when calculating resources that vary according to continuous functions.<br/>
     * An accuracy degradation is performed by increasing the step size after the allotted time has passed.<br/>
     * This is to ensure no performance degradation for very large <b>$length</b> values.<br/><br/>
     * The resulting interval lengths are as follows:<ul>
     * <li><b>$stepSize, ...</b> - Until <b>$degradeAfter</b> is reached in total length</li>
     * <li><b>x * $stepSize * $degradeBase ^ k, ...</b> - Where <b>x</b> increases with each step and <b>k = total length / $degradeAfter</b></li>
     * </ul><br/>
     * The end result is accurate discretization for the first <b>$degradeAfter</b> seconds and a small result array size afterwards.<br/>
     * 
     * @param int   $timestamp    Final timestamp in seconds
     * @param int   $length       Total interval length in seconds
     * @param int   $stepSize     Step size in seconds
     * @param int   $degradeAfter (optional) Increase step size exponentially after this total in seconds; default <b>21600</b>
     * @param float $degradeBase  (optional) Exponential base; default <b>1.2</b>
     * @param int   $maxSteps     (optional) Maximum number of steps; default <b>350</b>
     * @return array Array of [<ul>
     * <li>(int) Timestamp</li>
     * <li>(int) Step Size</li>
     * </ul>]
     */
    public static function getDiscretization($timestamp, $length, $stepSize, $degradeAfter = 21600, $degradeBase = 1.5, $maxSteps = 300) {
        $timestamp = abs((int) $timestamp);
        $length = abs((int) $length);
        $stepSize = abs((int) $stepSize);
        $degradeAfter = abs((int) $degradeAfter);
        $degradeBase = abs((float) $degradeBase);
        $maxSteps = abs((int) $maxSteps);
        
        // Prepare the result
        $result = array();

        // Initialize the counters
        $divStep = $divTotal = $stepSize;
        
        // Total number of steps
        $totalSteps = 1;
        
        // Go through the divisions
        do {
            // Append to the result
            $result[] = array(
                $timestamp - $length + (int) $divTotal, 
                (int) $divStep
            );
            
            // Exponential accuracy degradation
            if ($divTotal >= $degradeAfter) {
                $divStep += round($stepSize * pow($degradeBase, floor($divTotal / $degradeAfter)), 0);
            }
            
            // Next division
            $divTotal += $divStep;
            
            // Check for the final step
            if ($divTotal + $divStep > $length) {
                break;
            }
            
            // Avoid infinite loops
            if (++$totalSteps >= $maxSteps) {
                break;
            }
        } while (true);

        // The final step includes the left-over time
        $result[] = array(
            $timestamp, 
            $timestamp - current(end($result))
        );
        
        return $result;
    }
    
    /**
     * Get a snake grid.<br/><br/>
     * The snake starts from <b>$centerX, $centerY</b> and coils around itself counter-clockwise from the left.<br/>
     * <pre>
     * LU--##--RU
     * _       |
     * ##--XY  ##
     * |       |
     * LD--##--RD
     * </pre>
     * 
     * @param int $width   Odd positive integer, grid width
     * @param int $height  Odd positive integer, grid height
     * @param int $centerX (optional) Snake center X coordinate; default <b>0</b>
     * @param int $centerY (optional) Snake center Y coordinate; default <b>0</b>
     * @return type
     */
    public static function getSnakeGrid($width, $height, $centerX = 0, $centerY = 0) {
        // Sanitize the width
        $width = intval($width);
        if ($width < 1) {
            $width = 1;
        }
        if (0 === $width % 2) {
            $width++;
        }
        
        // Sanitize the height
        $height = intval($height);
        if ($height < 1) {
            $height = 1;
        }
        if (0 === $height % 2) {
            $height++;
        }
        
        // Prepare the number of coils
        $snakeCoils = (($width > $height ? $width : $height) + 1 ) / 2;
        
        // Prepare the horizontal padding
        $verticalPadding = ($width > $height) ? ($width - $height) / 2 : 0;
        
        // Prepare the vertical padding
        $horizontalPadding = ($height > $width) ? ($height - $width) / 2 : 0;
        
        // Get the snake
        return self::getSnake($centerX, $centerY, $snakeCoils, $verticalPadding, $horizontalPadding);
    }
    
    /**
     * Get a coiled snake, starting from the specified point.<br/>
     * <br/>
     * The snake starts from <b>$centerX, $centerY</b> and coils around itself counter-clockwise from the left.<br/>
     * <pre>
     * LU--##--RU
     * _       |
     * ##--XY  ##
     * |       |
     * LD--##--RD
     * </pre>
     * 
     * @param int $centerX           (optional) Snake center X coordinate; default <b>0</b>
     * @param int $centerY           (optional) Snake center Y coordinate; default <b>0</b>
     * @param int $snakeCoils        (optional) Number of snake coils; default <b>5</b>
     * @param int $verticalPadding   (optional) Used to create a "wide" snake coil, removing X number of rows from the top and bottom; default <b>0</b>
     * @param int $horizontalPadding (optional) Used to create a "tall" snake coil, removing X number of rows from the left and right; default <b>0</b>
     * @return array Array of array(x,y), where "x" and "y" are coordinates (integers)
     */
    public static function getSnake($centerX = 0, $centerY = 0, $snakeCoils = 5, $verticalPadding = 0, $horizontalPadding = 0) {
        // Sanitize the coordinates
        $centerX = intval($centerX);
        $centerY = intval($centerY);
        
        // Sanitize the coils count
        $snakeCoils = intval($snakeCoils);
        if ($snakeCoils < 1) {
            $snakeCoils = 1;
        }
        
        // Sanitize the paddings
        if ($verticalPadding >= $snakeCoils) {
            $verticalPadding = $snakeCoils - 1;
        }
        if ($horizontalPadding >= $snakeCoils) {
            $horizontalPadding = $snakeCoils - 1;
        }
        
        // Normalize the values
        if ($verticalPadding > 0 && $horizontalPadding > 0) {
            // Get the minimum padding
            $minPadding = $verticalPadding >= $horizontalPadding ? $horizontalPadding : $verticalPadding;
            
            // Skip extra work
            $snakeCoils -= $minPadding;
            $verticalPadding -= $minPadding;
            $horizontalPadding -= $minPadding;
        }
        
        // Prepare the snake length
        $snakeLength = pow(2 * ($snakeCoils - 1) + 1, 2) - 1;
        
        // Create the snake, vertebra by vertebra
        $result = array_map(
            function($vertebra){
                return self::getSnakePoint($vertebra);
            }, 
            range(0, $snakeLength)
        );
        
        // Vertical padding
        if ($verticalPadding > 0) {
            $result = array_filter($result, function($item) use ($snakeCoils, $verticalPadding) {
                return $item[1] < $snakeCoils - $verticalPadding && $item[1] > $verticalPadding - $snakeCoils;
            });
        }
        
        // Horizontal padding
        if ($horizontalPadding > 0) {
            $result = array_filter($result, function($item) use ($snakeCoils, $horizontalPadding) {
                return $item[0] < $snakeCoils - $horizontalPadding && $item[0] > $horizontalPadding - $snakeCoils;
            });
        }
        
        // Translate the snake on X,Y axes
        if (0 !== $centerX || 0 !== $centerY) {
            $result = array_map(
                function($item) use($centerX, $centerY) {
                    return array($item[0] + $centerX, $item[1] + $centerY);
                }, 
                $result
            );
        }
        
        // Snake complete!
        return $result;
    }
    
    /**
     * Get the snake head coordinates at a certain length.<br/>
     * <br/>
     * The snake starts from <b>0, 0</b> and coils around itself counter-clockwise from the left.<br/>
     * <pre>
     * LU--##--RU
     * _       |
     * ##--0   ##
     * |       |
     * LD--##--RD
     * </pre><br/>
     * If <b>s</b> = snake length, then the coil number <b>c</b> (natural number) is defined as follows:<ul>
     * <li><b>c</b> = (<b>s</b> / 4) ^ 0.5</li>
     * </ul>
     * The corners have the following values:<ul>
     * <li><b>lu</b> = 4 * <b>c</b> ^ 2 + 4 * <b>c</b></li>
     * <li><b>ru</b> = 4 * <b>c</b> ^ 2 + 2 * <b>c</b></li>
     * <li><b>rd</b> = 4 * <b>c</b> ^ 2</li>
     * <li><b>ld</b> = 4 * <b>c</b> ^ 2 - 2 * <b>c</b></li>
     * </ul>
     * The diagonal coordinates are defined as such:<ul>
     * <li><b>Left-Up</b>:    (-<b>c</b>, <b>c</b>)</li>
     * <li><b>Right-Up</b>:   (<b>c</b>, <b>c</b>)</li>
     * <li><b>Right-Down</b>: (<b>c</b>, -<b>c</b>)</li>
     * <li><b>Left-Down</b>:  (-<b>c</b>, -<b>c</b>)</li>
     * </ul>
     * The horizontal and vertical line coordinates:<ul>
     * <li><b>Top</b>:   (<b>c</b> - <b>s</b> % <b>ru</b>, <b>c</b>)</li>
     * <li><b>Right</b>: (<b>c</b>, <b>s</b> % <b>rd</b> - <b>c</b>)</li>
     * <li><b>Down</b>:  (<b>s</b> % <b>ld</b> - <b>c</b>, -<b>c</b>)</li>
     * <li><b>Left</b>:  (-<b>c</b>, <b>ld</b> % <b>s</b> - <b>c</b>)</li>
     * </ul>
     * 
     * @param int $snakeLength Snake length
     * @return int[] X,Y point
     */
    public static function getSnakePoint($snakeLength) {
        // Invalid snake
        if ($snakeLength <= 0) {
            return array(0, 0);
        }
        
        // First element
        if (1 == $snakeLength) {
            return array(-1, 0);
        }
        
        // Prepare the result
        $result = array();
        
        // Run along the coil
        do {
            // Get the coil number
            $coilNumber = (int) round(pow($snakeLength / 4, 0.5), 0);
        
            // RD
            $cornerRightDown = 4 * (int) pow($coilNumber, 2);
        
            // Left-Up corner
            if ($snakeLength == ($cornerRightDown + 4 * $coilNumber)) {
                $result = array(-$coilNumber, $coilNumber);
                break;
            }
            
            // RU
            $cornerRightUp = $cornerRightDown + 2 * $coilNumber;
            
            // Upper side
            if ($snakeLength > $cornerRightUp) {
                $result = array($coilNumber - $snakeLength % $cornerRightUp, $coilNumber);
                break;
            }
            
            // Right-Up corner
            if ($snakeLength == $cornerRightUp) {
                $result = array($coilNumber, $coilNumber);
                break;
            }
            
            // Right side
            if ($snakeLength > $cornerRightDown) {
                $result = array($coilNumber, $snakeLength % $cornerRightDown - $coilNumber);
                break;
            }
            
            // Right-Down corner
            if ($snakeLength == $cornerRightDown) {
                $result = array($coilNumber, -$coilNumber);
                break;
            }
            
            // LD
            $cornerLeftDown = $cornerRightDown - 2 * $coilNumber;
            
            // Down side
            if ($snakeLength > $cornerLeftDown) {
                $result = array($snakeLength % $cornerLeftDown - $coilNumber, -$coilNumber);
                break;
            }
            
            // Left-Down corner
            if ($snakeLength == $cornerLeftDown) {
                $result = array(-$coilNumber, -$coilNumber);
                break;
            }

            // Left side
            $result = array(-$coilNumber, $cornerLeftDown % $snakeLength - $coilNumber);
        } while(false);
        
        return $result;
    }
    
    /**
     * Get the snake length at specified coordinates.<br/>
     * Inverse of <b>getSnakePoint</b>.<br/>
     * <br/>
     * The snake starts from <b>0, 0</b> and coils around itself counter-clockwise from the left.<br/>
     * <pre>
     * LU--##--RU
     * _       |
     * ##--0   ##
     * |       |
     * LD--##--RD
     * </pre>
     * 
     * @param int $x X coordinate
     * @param int $y Y coordinate
     * @return int Snake length
     */
    public static function getSnakeLength($x, $y) {
        // Get the coil number
        $coilNumber = max(array(abs((int) $x), abs((int) $y)));

        // Full snake length
        $result = 4 * (int) pow($coilNumber, 2);
        
        // Shifting
        switch ($coilNumber) {
            // Top
            case $y:
                $result += 3 * $coilNumber - $x;
                break;
            
            // Right
            case $x:
                $result += $coilNumber + $y;
                break;
            
            // Bottom
            case -$y:
                $result += $x - $coilNumber;
                break;
            
            // Left
            default:
                $result += -3 * $coilNumber - $y;
        }
        return $result;
    }
    
    /**
     * Compress/expand a list of integers, replacing sequences of repetitive numbers with "{number}:{count}" if the
     * sequences are larger than 2 in length
     * 
     * @example [1, 2,2, 3,3,3, 4,4,4] becomes [1, 2,2, "3:3", "4:3"]
     * @param int[]|string[] $intList  List of integers to compress or compressed list to expand
     * @param boolean        $compress (optional) Compress/Expand; default <b>true</b>
     * @return string[]|int[]
     */
    public static function getIntListZip($intList, $compress = true) {
        $result = array();
        
        if (is_array($intList)) {
            do {
                // Unzip
                if (!$compress) {
                    foreach ($intList as $intListItem) {
                        if (preg_match('%^(\d+):(\d+)$%', $intListItem, $intListMatches)) {
                            list(, $intListValue, $intListCount) = array_map('intval', $intListMatches);
                            for ($i = 1; $i <= $intListCount; $i++) {
                                $result[] = $intListValue;
                            }
                        } else {
                            $result[] = intval($intListItem);
                        }
                    }
                    break;
                }
                
                // Store the previous value and count
                $prevValue = null;
                $prevCount = 0;

                // Append the previous set of values to the final list
                $append = function($intListItem = null) use(&$prevValue, &$prevCount, &$result) {
                    if (null !== $prevValue) {
                        if ($prevCount < 3) {
                            for($i = 1; $i <= $prevCount; $i++) {
                                $result[] = $prevValue;
                            }
                        } else {
                            $result[] = "$prevValue:$prevCount";
                        }
                    }

                    // Reset the counter
                    $prevValue = $intListItem;
                    $prevCount = 0;
                };

                // Go through the list of items
                foreach ($intList as $intListItem) {
                    if ($intListItem !== $prevValue) {
                        $append($intListItem);
                    }
                    $prevCount++;
                }
                $append();
            } while(false);
        }
        
        return $result;
    }
}

/*EOF*/