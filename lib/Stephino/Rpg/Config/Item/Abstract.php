<?php

/**
 * Stephino_Rpg_Config_Item_Abstract
 * 
 * @title      Configuration Item
 * @desc       Common configuration item
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
abstract class Stephino_Rpg_Config_Item_Abstract {

    // Definition arguments
    const DEF_KEY_TYPE              = 'type';
    const DEF_KEY_TYPE_COLLECTION   = 'collection';
    const DEF_KEY_TYPE_SINGLE       = 'single';
    const DEF_KEY_PARAMS            = 'params';
    const DEF_KEY_PARAM_TITLE       = 'title';
    const DEF_KEY_PARAM_NAME        = 'name';
    const DEF_KEY_PARAM_DESC        = 'desc';
    const DEF_KEY_PARAM_TYPE        = 'type';
    const DEF_KEY_PARAM_REF         = 'ref';
    const DEF_KEY_PARAM_SECTION     = 'section';
    const DEF_KEY_PARAM_OPT         = 'opt';
    const DEF_KEY_PARAM_DEPENDS     = 'depends';
    const DEF_KEY_PARAM_DUMMY       = 'dummy';
    const DEF_KEY_PARAM_DEFAULT     = 'default';
    const DEF_KEY_PARAM_LEVELS      = 'levels';
    const DEF_KEY_PARAM_SENSITIVE   = 'sensitive';
    const DEF_KEY_PARAM_PLACEHOLDER = 'placeholder';
    const DEF_KEY_PARAM_SLOT_EXC    = 'slot_exc';
    const DEF_KEY_CLASS             = 'class';
    const DEF_KEY_TITLE             = 'title';
    const DEF_KEY_VALUE             = 'value';
    
    /**
     * Serialization Key - Must be defined by each item individually
     */
    const KEY = '';

    /**
     * Configuration item
     */
    abstract public function __construct($data);

    /**
     * Serialization Key, defined by the class constant KEY
     * 
     * @return string
     */
    public static function key() {
        return static::KEY;
    }

    /**
     * Convert the current object into an array
     * 
     * @param boolean $hideSensitive (optional) Hide sensitive fields; default <b>false</b>
     * @return array Associative array
     */
    public function toArray($hideSensitive = false) {
        // Get the reflection class
        $reflectionClass = new ReflectionClass(get_called_class());

        // Prepare the result
        $result = array();

        // Go through the methods
        foreach ($reflectionClass->getMethods(ReflectionProperty::IS_PUBLIC) as /* @var $method ReflectionMethod */ $method) {
            if (preg_match('%^get[A-Z]%', $method->getName())) {
                // Hide sensitive values
                if ($hideSensitive && preg_match('%@' . self::DEF_KEY_PARAM_SENSITIVE . '\s*true\b%i', $method->getDocComment())) {
                    continue;
                }
                
                // Prepare the key
                $key = lcfirst(preg_replace('%^get%', '', $method->getName()));

                // Get the value
                $value = call_user_func(array($this, $method->getName()));

                // An array
                if (is_array($value)) {
                    // Go through the elements
                    foreach ($value as $valueKey => $valueVal) {
                        // Invalid entry
                        if (!$this->_toArrayValidate($valueVal)) {
                            unset($value[$valueKey]);
                            continue;
                        }

                        // Propagate the changes to the value
                        $value[$valueKey] = $valueVal;
                    }

                    // Store the modified array
                    $result[$key] = $value;
                } else {
                    if ($this->_toArrayValidate($value)) {
                        $result[$key] = $value;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Validate a value before serialization
     * 
     * @param mixed $value Value to check
     * @return boolean
     */
    protected function _toArrayValidate(&$value) {
        // Conver to integer for Single Items
        if ($value instanceof Stephino_Rpg_Config_Item_Single) {
            // Get the ID
            $value = $value->getId();
        }

        return is_numeric($value) || is_string($value) || is_bool($value);
    }
    
    /**
     * Get the current class definition
     * Includes the currently defined values for each item
     * 
     * @param boolean $hideSensitive (optional) Hide sensitive fields; default <b>false</b>
     */
    public function toDefinition($hideSensitive = false) {
        // Prepare the result
        $classParams = array();
        
        // Get the class type
        $classType = $this instanceof Stephino_Rpg_Config_Item_Collection ? self::DEF_KEY_TYPE_COLLECTION : self::DEF_KEY_TYPE_SINGLE;
        
        // Get the reflection class
        $reflectionClass = new ReflectionClass(self::DEF_KEY_TYPE_SINGLE === $classType ? get_called_class() : static::SINGLE_CLASS);

        // Get the class title
        $classTitle = trim(preg_replace(array('%^Stephino_Rpg_Config_%', '%([A-Z])%'), array('', ' ${1}'), get_called_class()));
        
        // Go through the methods
        foreach ($reflectionClass->getMethods(ReflectionProperty::IS_PUBLIC) as /* @var $method ReflectionMethod */ $method) {
            if (preg_match('%^get[A-Z]%', $method->getName())) {
                // Prepare the key name
                $paramName = lcfirst(preg_replace('%^get%', '', $method->getName()));
                
                // Get the parameter title
                $paramTitle = trim(
                    preg_replace(
                        array('%^get%', '%([A-Z\d])%'), 
                        array('', ' ${1}'), 
                        $method->getName()
                    )
                );
                
                // Get the comment
                $comment = trim(preg_replace('%^\s*(?:\/\*\*|\*\/|\*)*%m', '', $method->getDocComment()));
                
                // Prepare the parsed arguments
                $parsedParams = array(
                    self::DEF_KEY_PARAM_TYPE        => 'string',
                    self::DEF_KEY_PARAM_REF         => array(),
                    self::DEF_KEY_PARAM_PLACEHOLDER => array(),
                    self::DEF_KEY_PARAM_SLOT_EXC    => array(),
                    self::DEF_KEY_PARAM_OPT         => array(),
                    self::DEF_KEY_PARAM_SECTION     => null,
                    self::DEF_KEY_PARAM_DEPENDS     => null,
                    self::DEF_KEY_PARAM_DUMMY       => null,
                    self::DEF_KEY_PARAM_DEFAULT     => null,
                    self::DEF_KEY_PARAM_LEVELS      => false,
                    self::DEF_KEY_PARAM_SENSITIVE   => false,
                );
                
                // Parse the entries and description
                $paramDescription = trim(preg_replace_callback(
                    '%^\s*\@(\w+)(.*?)$%ms', 
                    function($item) use (&$parsedParams, &$paramTitle) {
                        switch ($item[1]) {
                            case 'return':
                                if (preg_match('%^\s*([\w]+\b(?:\s*\[\s*\])?)(.*)%is', $item[2], $matches)) {
                                    // Store the parameter type
                                    $parsedParams[self::DEF_KEY_PARAM_TYPE] = preg_replace('%(?:^Stephino_Rpg_|\s+)%', '', trim($matches[1]));

                                    // Store the new title
                                    if (strlen($returnDescription = trim(preg_replace('%\|\s*null%', '', $matches[2])))) {
                                        $paramTitle = $returnDescription;
                                    }
                                }
                                break;
                                
                            case self::DEF_KEY_PARAM_REF:
                            case self::DEF_KEY_PARAM_PLACEHOLDER:
                            case self::DEF_KEY_PARAM_SLOT_EXC:
                            case self::DEF_KEY_PARAM_OPT:
                                $parsedParams[$item[1]] = array_filter(array_map('trim', explode(',', $item[2])), function($item) {
                                    return strlen($item);
                                });
                                break;
                            
                            case self::DEF_KEY_PARAM_SECTION:
                            case self::DEF_KEY_PARAM_DEPENDS:
                            case self::DEF_KEY_PARAM_DEFAULT:
                                $parsedParams[$item[1]] = trim($item[2]);
                                if (!strlen($parsedParams[$item[1]])) {
                                    $parsedParams[$item[1]] = null;
                                }
                                break;
                            
                            case self::DEF_KEY_PARAM_LEVELS:
                            case self::DEF_KEY_PARAM_DUMMY:
                            case self::DEF_KEY_PARAM_SENSITIVE:
                                $parsedParams[$item[1]] = ('true' === trim($item[2]));
                                break;
                        }

                        // Remove the entry
                        return '';
                    }, 
                    $comment
                ));
                
                // Store te item
                $classParams[$paramName] = array(
                    self::DEF_KEY_PARAM_NAME     => $paramName,
                    self::DEF_KEY_PARAM_TITLE    => $paramTitle,
                    self::DEF_KEY_PARAM_DESC     => $paramDescription,
                    self::DEF_KEY_PARAM_TYPE     => $parsedParams[self::DEF_KEY_PARAM_TYPE],
                    self::DEF_KEY_PARAM_REF      => $parsedParams[self::DEF_KEY_PARAM_REF],
                    self::DEF_KEY_PARAM_LEVELS   => $parsedParams[self::DEF_KEY_PARAM_LEVELS],
                );
                
                // Placeholder defined
                if (count($parsedParams[self::DEF_KEY_PARAM_PLACEHOLDER]) >= 2) {
                    $classParams[$paramName][self::DEF_KEY_PARAM_PLACEHOLDER] = $parsedParams[self::DEF_KEY_PARAM_PLACEHOLDER];
                }
                
                // Slot extras
                if (in_array($parsedParams[self::DEF_KEY_PARAM_TYPE], array('slot', 'slots'))) {
                    $classParams[$paramName][self::DEF_KEY_PARAM_SLOT_EXC] = $parsedParams[self::DEF_KEY_PARAM_SLOT_EXC];
                }
                
                // String options
                if (count($parsedParams[self::DEF_KEY_PARAM_OPT])) {
                    $classParams[$paramName][self::DEF_KEY_PARAM_OPT] = $parsedParams[self::DEF_KEY_PARAM_OPT];
                }
                
                // Dependency
                if (null !== $parsedParams[self::DEF_KEY_PARAM_DEPENDS]) {
                    $classParams[$paramName][self::DEF_KEY_PARAM_DEPENDS] = $parsedParams[self::DEF_KEY_PARAM_DEPENDS];
                }
                
                // Section
                if (null !== $parsedParams[self::DEF_KEY_PARAM_SECTION]) {
                    $classParams[$paramName][self::DEF_KEY_PARAM_SECTION] = $parsedParams[self::DEF_KEY_PARAM_SECTION];
                }
                
                // Dummy item - don't trigger change event
                if (null !== $parsedParams[self::DEF_KEY_PARAM_DUMMY]) {
                    $classParams[$paramName][self::DEF_KEY_PARAM_DUMMY] = $parsedParams[self::DEF_KEY_PARAM_DUMMY];
                }
                
                // Default value
                if (null !== $parsedParams[self::DEF_KEY_PARAM_DEFAULT]) {
                    $classParams[$paramName][self::DEF_KEY_PARAM_DEFAULT] = $parsedParams[self::DEF_KEY_PARAM_DEFAULT];
                }
                
                // Hide this parameter
                if ($hideSensitive && $parsedParams[self::DEF_KEY_PARAM_SENSITIVE]) {
                    $classParams[$paramName][self::DEF_KEY_PARAM_SENSITIVE] = true;
                }
            }
        }
        
        return array(
            self::DEF_KEY_CLASS  => preg_replace('%^Stephino_Rpg_%', '', $reflectionClass->getName()),
            self::DEF_KEY_TYPE   => $classType,
            self::DEF_KEY_TITLE  => $classTitle,
            self::DEF_KEY_PARAMS => $classParams,
            self::DEF_KEY_VALUE  => $this->toArray($hideSensitive),
        );
    }

}

/*EOF*/