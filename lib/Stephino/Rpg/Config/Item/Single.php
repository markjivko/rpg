<?php

/**
 * Stephino_Rpg_Config_Item_Single
 * 
 * @title      Single Item
 * @desc       An abstraction of a "single" item
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
abstract class Stephino_Rpg_Config_Item_Single extends Stephino_Rpg_Config_Item_Abstract {
    
    /**
     * Class name of corresponding Collection Item
     */
    const COLLECTION_CLASS = '';
    
    // Polynomial JSON specifics
    const TYPE_POLY_FUNC                 = 'func';
    const TYPE_POLY_FUNC_CONSTANT        = 'c';
    const TYPE_POLY_FUNC_LINEAR          = 'l';
    const TYPE_POLY_FUNC_LINEAR_INV      = 'li';
    const TYPE_POLY_FUNC_QUADRATIC       = 'q';
    const TYPE_POLY_FUNC_QUADRATIC_INV   = 'qi';
    const TYPE_POLY_FUNC_EXPONENTIAL     = 'e';
    const TYPE_POLY_FUNC_EXPONENTIAL_INV = 'ei';
    const TYPE_POLY_ARGS                 = 'args';
    const TYPE_POLY_ARGS_A               = 'a';
    const TYPE_POLY_ARGS_B               = 'b';
    const TYPE_POLY_ARGS_C               = 'c';
    
    // Animations JSON specifics
    const TYPE_ANIM_FRAME_WIDTH          = 'frameWidth';
    const TYPE_ANIM_FRAME_HEIGHT         = 'frameHeight';
    const TYPE_ANIM_FRAME_DURATION       = 'frameDuration';
    const TYPE_ANIM_FRAME_STEPS          = 'frameSteps';
    const TYPE_ANIM_FRAME_SPRITE         = 'frameSprite';
    const TYPE_ANIM_KEYFRAMES            = 'keyFrames';
    const TYPE_ANIM_KEYFRAMES_X          = 'x';
    const TYPE_ANIM_KEYFRAMES_Y          = 'y';
    const TYPE_ANIM_KEYFRAMES_ROW        = 'r';
    const TYPE_ANIM_KEYFRAMES_FLIP_X     = 'fx';
    const TYPE_ANIM_KEYFRAMES_FLIP_Y     = 'fy';
    const TYPE_ANIM_KEYFRAMES_TRANSITION = 't';
    const TYPE_ANIM_KEYFRAMES_ZINDEX     = 'z';
    const TYPE_ANIM_KEYFRAMES_OPACITY    = 'o';
    
    /**
     * Single item ID
     * 
     * @var int|null
     */
    private $_id = null;

    /**
     * Get the key of corresponding Collection Item
     * 
     * @return string|null
     */
    public function keyCollection() {
        return constant(static::COLLECTION_CLASS . '::KEY');
    }
    
    /**
     * Get this single item's ID
     * 
     * @return int|null
     */
    public function getId() {
        return $this->_id;
    }
    
    /**
     * Common name handler
     * 
     * @param string $htmlOutput (optional) Format the name for HTML output; default <b>false</b>
     * @return string
     */
    public function getName($htmlOutput = false) {
        $result = (null === $this->_name ? (ucfirst(static::KEY) . ' ' . $this->getId()) : $this->_name);
        return $htmlOutput ? Stephino_Rpg_Utils_Lingo::escape($result) : $result;
    }
    
    /**
     * Parse a single item
     * 
     * @param array $data Associative array
     */
    public function __construct($data, $id = null) {
        // Valid ID
        if (is_numeric($id)) {
            $this->_setId($id);
        }

        // Valid configuration array
        if (is_array($data)) {
            // Go through the items
            foreach ($data as $key => $value) {
                // Ignore nulls
                if (null === $value) {
                    continue;
                }

                // Prepare the method name
                $methodName = 'set' . ucfirst(preg_replace('%\W+%', '', $key));

                // Valid method; do not allow ID resetting
                if ('setId' !== $methodName && method_exists($this, $methodName)) {
                    call_user_func(array($this, $methodName), $value);
                }
            }
        }
    }

    /**
     * Set the single item ID
     * 
     * @param int $id
     */
    private function _setId($id) {
        $this->_id = intval($id);
    }
    
    /**
     * Sanitize data for levels
     * 
     * @param string $jsonData     JSON-encoded data
     * @param string $methodToCall Sanitizer
     * @return string JSON-encoded level-ready data
     */
    protected function _sanitizeLevels($jsonData, $methodToCall) {
        // Decode the data
        $result = json_decode($jsonData, true);
        
        // Invalid data type, expecting array
        if (!is_array($result)) {
            return null;
        }
        
        // Parse the data
        foreach ($result as $key => $value) {
            // Invalid key
            if (intval($key) <= 0) {
                unset($result[$key]);
                continue;
            }
            
            // Prepare the sanitized value
            $sanitizedValue = call_user_func(array($this, $methodToCall), json_encode($value));
            
            // Invalid data
            if (null === $sanitizedValue) {
                unset($result[$key]);
                continue;
            } 
            
            // Store the sanitized value
            $result[$key] = json_decode($sanitizedValue, true);
        }

        // Not a valid array
        if (!count($result)) {
            return null;
        }
        
        return json_encode($result);
    }
    
    /**
     * Sanitize a custom "Slot" JSON object
     * 
     * @param string|null $slot JSON object
     * @return string|null
     */
    protected function _sanitizeSlot($slot) {
        // Sanitize the value
        if (null !== $slot) {
            do {
                // Get the slot details
                $slotArray = json_decode($slot, true);
                if (is_array($slotArray)) {
                    // Convert to int
                    $slotArray = array_filter($slotArray, function($item) {
                        return preg_match('%^[\+\-]?\d+$%', $item);
                    });
                    
                    // The right size
                    if (2 === count($slotArray)) {
                        // Recreate the array, making it unassociative
                        $slot = json_encode(array_values(array_map('intval', $slotArray)));
                        break;
                    }
                }
                
                // Invalid slot
                $slot = null;
            } while (false);
        }
        
        return $slot;
    }
    
    /**
     * Sanitize a custom "Slots" JSON object
     * 
     * @param string|null $slots       JSON object
     * @param boolean     $associative Whether to store the object as an associative array
     * @return string|null
     */
    protected function _sanitizeSlots($slots, $associative = false) {
        // Sanitize the value
        if (null !== $slots) {
            do {
                // Get the slot details
                $slotsArray = json_decode($slots, true);
                
                // An unassociative array
                if (is_array($slotsArray)) {
                    // Go through all the pairs
                    foreach ($slotsArray as $slotKey => $slotCoords) {
                        // Get the coordinates
                        $slotCoords = array_filter($slotCoords, function($item) {
                            return preg_match('%^[\+\-]?\d+$%', $item);
                        });
                        
                        // Invalid pair found
                        if (2 !== count($slotCoords)) {
                            unset($slotsArray[$slotKey]);
                            continue;
                        }
                        
                        // Make it unassociative
                        $slotsArray[$slotKey] = array_values(array_map('intval', $slotCoords));
                    }
                    
                    // Recreate the array
                    if (count($slotsArray)) {
                        $slots = json_encode($associative ? $slotsArray : array_values($slotsArray));
                        break;
                    }
                }
                
                // Invalid slots
                $slots = null;
            } while (false);
        }
        
        return $slots;
    }
    
    /**
     * Sanitize a custom "Action Area" JSON object
     * 
     * @param string|null $actionArea JSON object
     * @return string|null
     */
    protected function _sanitizeActionArea($actionArea) {
        if (null !== $actionArea) {
            do {
                // Decode the action area
                $actionAreaDecoded = json_decode($actionArea, true);
                
                // Seemingly valid input
                if (is_string($actionAreaDecoded) && strlen($actionAreaDecoded) && preg_match('%^M%i', $actionAreaDecoded) && substr_count($actionAreaDecoded, ',') > 2) {
                    break;
                }
                
                // Something went wrong
                $actionArea = null;
            } while (false);
        }
        
        return $actionArea;
    }
    
    /**
     * Sanitize a custom "Polynomial" JSON object
     * 
     * @param string|null $polynomial JSON object
     * @return string|null
     */
    protected function _sanitizePoly($polynomial) {
        if (null !== $polynomial) {
            // Get the definition
            $polyDefinition = json_decode($polynomial, true);
            
            // Prepare the new definition
            $newDefinition = null;
                
            // Valid array
            if (is_array($polyDefinition) && isset($polyDefinition[self::TYPE_POLY_FUNC]) && isset($polyDefinition[self::TYPE_POLY_ARGS]) && is_array($polyDefinition[self::TYPE_POLY_ARGS])) {
                switch ($polyDefinition[self::TYPE_POLY_FUNC]) {
                    case self::TYPE_POLY_FUNC_LINEAR:
                    case self::TYPE_POLY_FUNC_LINEAR_INV;
                        if (isset($polyDefinition[self::TYPE_POLY_ARGS][self::TYPE_POLY_ARGS_A]) && isset($polyDefinition[self::TYPE_POLY_ARGS][self::TYPE_POLY_ARGS_C])) {
                            $newDefinition = array(
                                self::TYPE_POLY_FUNC => $polyDefinition[self::TYPE_POLY_FUNC],
                                self::TYPE_POLY_ARGS => array(
                                    self::TYPE_POLY_ARGS_A => floatval($polyDefinition[self::TYPE_POLY_ARGS][self::TYPE_POLY_ARGS_A]),
                                    self::TYPE_POLY_ARGS_C => floatval($polyDefinition[self::TYPE_POLY_ARGS][self::TYPE_POLY_ARGS_C]),
                                )
                            );
                        }
                        break;

                    case self::TYPE_POLY_FUNC_QUADRATIC:
                    case self::TYPE_POLY_FUNC_QUADRATIC_INV:
                    case self::TYPE_POLY_FUNC_EXPONENTIAL:
                    case self::TYPE_POLY_FUNC_EXPONENTIAL_INV:
                        if (isset($polyDefinition[self::TYPE_POLY_ARGS][self::TYPE_POLY_ARGS_A]) && isset($polyDefinition[self::TYPE_POLY_ARGS][self::TYPE_POLY_ARGS_B]) && isset($polyDefinition[self::TYPE_POLY_ARGS][self::TYPE_POLY_ARGS_C])) {
                            $newDefinition = array(
                                self::TYPE_POLY_FUNC => $polyDefinition[self::TYPE_POLY_FUNC],
                                self::TYPE_POLY_ARGS => array(
                                    self::TYPE_POLY_ARGS_A => floatval($polyDefinition[self::TYPE_POLY_ARGS][self::TYPE_POLY_ARGS_A]),
                                    self::TYPE_POLY_ARGS_B => floatval($polyDefinition[self::TYPE_POLY_ARGS][self::TYPE_POLY_ARGS_B]),
                                    self::TYPE_POLY_ARGS_C => floatval($polyDefinition[self::TYPE_POLY_ARGS][self::TYPE_POLY_ARGS_C]),
                                )
                            );
                        }
                        break;
                }
            }

            // Store the data
            $polynomial = (null === $newDefinition ? null : json_encode($newDefinition));
        }
        
        return $polynomial;
    }
    
    /**
     * Sanitize a custom "Animations" JSON object
     * 
     * @param string|null $animations JSON object
     * @return string|null
     */
    protected function _sanitizeAnimations($animations) {
        if (null !== $animations) {
            $coreAnimationDefinition = json_decode($animations, true);
            
            // Prepare the animations list
            $animationsArray = array();
            
            // Valid array
            if (is_array($coreAnimationDefinition)) {
                // Go through the items
                foreach ($coreAnimationDefinition as $animationsDefinition) {
                    // Prepare the new definition
                    $newDefinition = null;

                    // Valid definition
                    if (is_array($animationsDefinition) 
                        && isset($animationsDefinition[self::TYPE_ANIM_FRAME_WIDTH]) 
                        && isset($animationsDefinition[self::TYPE_ANIM_FRAME_HEIGHT]) 
                        && isset($animationsDefinition[self::TYPE_ANIM_FRAME_DURATION]) 
                        && isset($animationsDefinition[self::TYPE_ANIM_FRAME_STEPS]) 
                        && isset($animationsDefinition[self::TYPE_ANIM_FRAME_SPRITE]) 
                        && isset($animationsDefinition[self::TYPE_ANIM_KEYFRAMES])) {

                        // Keyframes provided
                        if (is_array($animationsDefinition[self::TYPE_ANIM_KEYFRAMES])) {
                            // Prepre the new keyframes
                            $newKeyframes = array();

                            // Sanitize the keyframes
                            foreach ($animationsDefinition[self::TYPE_ANIM_KEYFRAMES] as $keyFrameIndex => $keyFrame) {
                                if (isset($keyFrame[self::TYPE_ANIM_KEYFRAMES_X])
                                    && isset($keyFrame[self::TYPE_ANIM_KEYFRAMES_Y])
                                    && isset($keyFrame[self::TYPE_ANIM_KEYFRAMES_ROW])
                                    && isset($keyFrame[self::TYPE_ANIM_KEYFRAMES_FLIP_X])
                                    && isset($keyFrame[self::TYPE_ANIM_KEYFRAMES_FLIP_Y])
                                    && isset($keyFrame[self::TYPE_ANIM_KEYFRAMES_TRANSITION])
                                    && isset($keyFrame[self::TYPE_ANIM_KEYFRAMES_ZINDEX])
                                    && isset($keyFrame[self::TYPE_ANIM_KEYFRAMES_OPACITY])) {

                                    // Make everything an integer
                                    $keyFrame = array_map('intval', $keyFrame);

                                    // Append the keyframe, storing the index as well
                                    $newKeyframes[strval($keyFrameIndex)] = array(
                                        self::TYPE_ANIM_KEYFRAMES_X          => $keyFrame[self::TYPE_ANIM_KEYFRAMES_X] < 0 ? 0 : $keyFrame[self::TYPE_ANIM_KEYFRAMES_X], 
                                        self::TYPE_ANIM_KEYFRAMES_Y          => $keyFrame[self::TYPE_ANIM_KEYFRAMES_Y] < 0 ? 0 : $keyFrame[self::TYPE_ANIM_KEYFRAMES_Y], 
                                        self::TYPE_ANIM_KEYFRAMES_ROW        => $keyFrame[self::TYPE_ANIM_KEYFRAMES_ROW] < 0 ? 0 : $keyFrame[self::TYPE_ANIM_KEYFRAMES_ROW], 
                                        self::TYPE_ANIM_KEYFRAMES_FLIP_X     => $keyFrame[self::TYPE_ANIM_KEYFRAMES_FLIP_X], 
                                        self::TYPE_ANIM_KEYFRAMES_FLIP_Y     => $keyFrame[self::TYPE_ANIM_KEYFRAMES_FLIP_Y], 
                                        self::TYPE_ANIM_KEYFRAMES_TRANSITION => $keyFrame[self::TYPE_ANIM_KEYFRAMES_TRANSITION] < 1 ? 1 : $keyFrame[self::TYPE_ANIM_KEYFRAMES_TRANSITION], 
                                        self::TYPE_ANIM_KEYFRAMES_ZINDEX     => $keyFrame[self::TYPE_ANIM_KEYFRAMES_ZINDEX] < 1 ? 1 : $keyFrame[self::TYPE_ANIM_KEYFRAMES_ZINDEX], 
                                        self::TYPE_ANIM_KEYFRAMES_OPACITY    => $keyFrame[self::TYPE_ANIM_KEYFRAMES_OPACITY] < 0 ? 0 : ($keyFrame[self::TYPE_ANIM_KEYFRAMES_OPACITY] > 100 ? 100 : $keyFrame[self::TYPE_ANIM_KEYFRAMES_OPACITY]), 
                                    );
                                }
                            }
                            
                            // Always in order
                            ksort($newKeyframes);

                            // Prepare the new definition
                            $newDefinition = array(
                                self::TYPE_ANIM_FRAME_WIDTH    => intval($animationsDefinition[self::TYPE_ANIM_FRAME_WIDTH]),
                                self::TYPE_ANIM_FRAME_HEIGHT   => intval($animationsDefinition[self::TYPE_ANIM_FRAME_HEIGHT]),
                                self::TYPE_ANIM_FRAME_DURATION => intval($animationsDefinition[self::TYPE_ANIM_FRAME_DURATION]),
                                self::TYPE_ANIM_FRAME_STEPS    => intval($animationsDefinition[self::TYPE_ANIM_FRAME_STEPS]),
                                self::TYPE_ANIM_FRAME_SPRITE   => intval($animationsDefinition[self::TYPE_ANIM_FRAME_SPRITE]),
                                self::TYPE_ANIM_KEYFRAMES      => $newKeyframes
                            );

                            // Minimum 2 pixels width and height
                            if ($newDefinition[self::TYPE_ANIM_FRAME_WIDTH] < 2) {
                                $newDefinition[self::TYPE_ANIM_FRAME_WIDTH] = 2;
                            }
                            if ($newDefinition[self::TYPE_ANIM_FRAME_HEIGHT] < 2) {
                                $newDefinition[self::TYPE_ANIM_FRAME_HEIGHT] = 2;
                            }
                            
                            // Minimum 1ms
                            if ($newDefinition[self::TYPE_ANIM_FRAME_DURATION] < 1) {
                                $newDefinition[self::TYPE_ANIM_FRAME_DURATION] = 1;
                            }
                            
                            // Minimum 1 frame
                            if ($newDefinition[self::TYPE_ANIM_FRAME_STEPS] < 1) {
                                $newDefinition[self::TYPE_ANIM_FRAME_STEPS] = 1;
                            }
                            
                            // Minimum frame sprite ID is 0
                            if ($newDefinition[self::TYPE_ANIM_FRAME_STEPS] < 0) {
                                $newDefinition[self::TYPE_ANIM_FRAME_STEPS] = 0;
                            }
                        }
                    }

                    // Valid animation definition found
                    if (null !== $newDefinition) {
                        $animationsArray[] = $newDefinition;
                    }
                }
            }
            
            // Store the data
            $animations = (!count($animationsArray) ? null : json_encode($animationsArray));
        }
        
        return $animations;
    }
}

/*EOF*/