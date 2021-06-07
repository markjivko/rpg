<?php
/**
 * Stephino_Rpg_Utils_Config
 * 
 * @title     Utils:Config
 * @desc      Configuration utils
 * @copyright (c) 2021, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Utils_Config {
    
    /**
     * Cache for polynomial factors
     * 
     * @var float[]
     */
    protected static $_cachePolyFactor = array();
    
    /**
     * Calculate the final production value for the elapsed time
     * 
     * @param string $polyJSON               Polynomial JSON
     * @param int    $prodLevel              Current production level
     * @param float  $prodInitialHourlyValue Initial (Level 1) hourly production value
     * @param int    $prodTime               (optional) Total production time at this level (in seconds); default <b>3600</b>
     * @return float 6 digits precision float
     */
    public static function getPolyValue($polyJSON, $prodLevel, $prodInitialHourlyValue, $prodTime = 3600) {
        // Sanitize the hourly production rate
        $prodInitialHourlyValue = floatval($prodInitialHourlyValue);
        
        // Sanitize the production time
        $prodTime = intval($prodTime);
        
        // Prepare the x value
        $xValue = intval($prodLevel);
        
        // No production at all
        if ($prodInitialHourlyValue == 0 || $prodTime <= 0 || $xValue <= 0) {
            return 0;
        }
        
        // Prepare the cache key
        $cacheKey = $polyJSON . '-' . $xValue;
        
        // No polynomial provided
        if (!strlen($polyJSON) || $xValue <= 1) {
            self::$_cachePolyFactor[$cacheKey] = 1;
        }
        
        // Cache not set
        if (!isset(self::$_cachePolyFactor[$cacheKey])) {
            self::$_cachePolyFactor[$cacheKey] = self::getPolyRange($polyJSON, $xValue - 1);
        }
        
        // Precision 6
        return round($prodInitialHourlyValue * self::$_cachePolyFactor[$cacheKey] * $prodTime / 3600, 6);
    }
    
    /**
     * Get polynomial range
     * 
     * @param string $polyJSON  Polynomial JSON
     * @param int    $valueFrom X - Starting value
     * @param int    $valueTo   (optional) X - Ending value; default <b>null</b>
     * @return float[]|float Array of Y values or a single value if <b>$valueTo</b> is null
     */
    public static function getPolyRange($polyJSON, $valueFrom, $valueTo = null) {
        // Prepare the result
        $result = array();
        
        // Get the definition
        $polyDefinition = json_decode($polyJSON, true);
        
        // One element array
        if (null === $valueTo) {
            $valueTo = $valueFrom;
        }
        
        // Go through the values
        for ($xValue = $valueFrom; $xValue <= $valueTo; $xValue++) {
            // Prepare f(x)
            $yValue = 1;
            
            // Valid array
            if (is_array($polyDefinition) && isset($polyDefinition[Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC]) && isset($polyDefinition[Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS]) && is_array($polyDefinition[Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS])) {
                switch ($polyDefinition[Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC]) {
                    case Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC_LINEAR:
                    case Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC_LINEAR_INV:
                        if (isset($polyDefinition[Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS][Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS_A]) && isset($polyDefinition[Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS][Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS_C])) {
                            $argA = floatval($polyDefinition[Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS][Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS_A]);
                            $argC = floatval($polyDefinition[Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS][Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS_C]);

                            // Linear
                            $yValue = $argA * $xValue + $argC;

                            // Linear multiplicative inverse
                            if (Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC_LINEAR_INV === $polyDefinition[Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC]) {
                                // Division by 0 not allowed
                                if (0 == $yValue) {
                                    $yValue = 1;
                                }

                                // Inverse
                                $yValue = 1 / $yValue;
                            }
                        }
                        break;

                    case Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC_QUADRATIC:
                    case Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC_QUADRATIC_INV:
                    case Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC_EXPONENTIAL:
                    case Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC_EXPONENTIAL_INV:
                        if (isset($polyDefinition[Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS][Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS_A]) && isset($polyDefinition[Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS][Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS_B]) && isset($polyDefinition[Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS][Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS_C])) {
                            $argA = floatval($polyDefinition[Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS][Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS_A]);
                            $argB = floatval($polyDefinition[Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS][Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS_B]);
                            $argC = floatval($polyDefinition[Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS][Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS_C]);

                            if (Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC_QUADRATIC == $polyDefinition[Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC]) {
                                // Quadratic
                                $yValue = ($argA * pow($xValue, 2) + $argB * $xValue + $argC);

                                // Quadratic multiplicative inverse
                                if (Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC_QUADRATIC_INV === $polyDefinition[Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC]) {
                                    // Division by 0 not allowed
                                    if (0 == $yValue) {
                                        $yValue = 1;
                                    }

                                    // Inverse
                                    $yValue = 1 / $yValue;
                                }
                            } else {
                                // Exponential
                                $yValue = ($argA * pow($argB, $xValue) + $argC);

                                // Exponential multiplicative inverse
                                if (Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC_EXPONENTIAL_INV === $polyDefinition[Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC]) {
                                    // Division by 0 not allowed
                                    if (0 == $yValue) {
                                        $yValue = 1;
                                    }

                                    // Inverse
                                    $yValue = 1 / $yValue;
                                }
                            }
                        }
                        break;
                }
            }
            
            // Store the value
            $result[$xValue] = $yValue;
        }
        reset($result);
        
        return $valueFrom == $valueTo ? current($result) : $result;
    }
    
    /**
     * Get a polynomial JSON definition
     * 
     * @param string $funcName Function name, one of <ul>
     *     <li>Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC_CONSTANT</li>
     *     <li>Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC_LINEAR</li>
     *     <li>Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC_LINEAR_INV</li>
     *     <li>Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC_QUADRATIC</li>
     *     <li>Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC_QUADRATIC_INV</li>
     *     <li>Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC_EXPONENTIAL</li>
     *     <li>Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC_EXPONENTIAL_INV</li>
     * </ul>
     * @param float  $argA
     * @param float  $argB
     * @param float  $argC
     * @return string JSON poly definition
     */
    public static function getPolyJson($funcName, $argA, $argB, $argC) {
        // Validate the function name
        if (!in_array($funcName, array(
            Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC_CONSTANT,
            Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC_LINEAR,
            Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC_LINEAR_INV,
            Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC_QUADRATIC,
            Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC_QUADRATIC_INV,
            Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC_EXPONENTIAL,
            Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC_EXPONENTIAL_INV,
        ))) {
            $funcName = Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC_CONSTANT;
        }
        
        // Prepare the definition
        $definition = array(
            Stephino_Rpg_Config_Item_Single::TYPE_POLY_FUNC => $funcName,
            Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS => array(
                Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS_A => (float) $argA,
                Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS_B => (float) $argB,
                Stephino_Rpg_Config_Item_Single::TYPE_POLY_ARGS_C => (float) $argC,
            )
        );
        
        // Get the JSON value
        return json_encode($definition);
    }
    
    /**
     * Get all the models this Building or Research Field unlocks, in the order they are unlocked
     * 
     * @param Stephino_Rpg_Config_Building|Stephino_Rpg_Config_ResearchField $configObject Building or Research Field object
     * @return \Stephino_Rpg_Config_Item_Single[]
     */
    public static function getUnlocks($configObject) {
        $result = array();
        
        // Valid configuration object
        if ($configObject instanceof Stephino_Rpg_Config_Building
            || $configObject instanceof Stephino_Rpg_Config_ResearchField) {
            /* @var $configSet Stephino_Rpg_Config_Item_Collection */
            foreach(Stephino_Rpg_Config::get()->all() as $configSet) {
                if ($configSet instanceof Stephino_Rpg_Config_Item_Collection) {
                    foreach($configSet->getAll() as $configSetItem) {
                        /* @var $configSetItem Stephino_Rpg_Config_Trait_Requirement */
                        if ($configSetItem instanceof Stephino_Rpg_Config_Item_Single
                            && in_array(Stephino_Rpg_Config_Trait_Requirement::class, class_uses($configSetItem))) {
                            switch (true) {
                                case $configObject instanceof Stephino_Rpg_Config_Building:
                                    if (null !== $configSetItem->getRequiredBuilding() 
                                        && $configSetItem->getRequiredBuilding()->getId() == $configObject->getId()) {
                                        $result[] = $configSetItem;
                                    }
                                    break;
                                    
                                case $configObject instanceof Stephino_Rpg_Config_ResearchField:
                                    if (null !== $configSetItem->getRequiredResearchField() 
                                        && $configSetItem->getRequiredResearchField()->getId() == $configObject->getId()) {
                                        $result[] = $configSetItem;
                                    }
                                    break;
                            }
                        }
                    }
                }
            }
        }
        
        // Sort by level, ascending
        usort($result, function($configSetItemA, $configSetItemB) use ($configObject) {
            $levelA = intval(
                $configObject instanceof Stephino_Rpg_Config_Building
                    ? $configSetItemA->getRequiredBuildingLevel()
                    : $configSetItemA->getRequiredResearchFieldLevel()
            );
            $levelB = intval(
                $configObject instanceof Stephino_Rpg_Config_Building
                    ? $configSetItemB->getRequiredBuildingLevel()
                    : $configSetItemB->getRequiredResearchFieldLevel()
            );
            return $levelA == $levelB ? 0 : ($levelA < $levelB ? -1 : 1);
        });
        return $result;
    }
    
    /**
     * Get the maximum level at which this Building or Research Field unlocks anything
     * 
     * @param Stephino_Rpg_Config_Building|Stephino_Rpg_Config_ResearchField $configObject Building or Research Field object
     * @return int Maximum unlocking level or <b>0</b> if none defined/applicable
     */
    public static function getUnlocksMaxLevel($configObject) {
        // Prepare the result
        $unlockMaxLevel = 0;
        
        // Go through the data
        foreach (self::getUnlocks($configObject) as $configUnlocked) {
            // Prepare the level
            $configUnlockedLevel = 0;
            switch (true) {
                case $configObject instanceof Stephino_Rpg_Config_Building:
                    $configUnlockedLevel = $configUnlocked->getRequiredBuildingLevel();
                    break;

                case $configObject instanceof Stephino_Rpg_Config_ResearchField:
                    $configUnlockedLevel = $configUnlocked->getRequiredResearchFieldLevel();
                    break;
            }
            if ($configUnlockedLevel > $unlockMaxLevel) {
                $unlockMaxLevel = $configUnlockedLevel;
            }
        }
        
        return $unlockMaxLevel;
    }
    
    /**
     * Get the unlock stages and maximum required levels
     * 
     * @param boolean $returnObjects (optional) Return objects instead of <b>'building 1'</b> strings for configuration items
     * @return array [<b>(int)</b> Stage Key => [<ul>
     *     <li><b>(string|Stephino_Rpg_Config_Item_Single)</b> Configuration Item</li>
     *     <li>...</li>
     * </ul>], ...]
     * @throws Exception
     */
    public static function getUnlockStages($returnObjects = false) {
        /**
         * Get the node key
         * 
         * @param Stephino_Rpg_Config_Item_Single $configSetItem Configuration Item 
         * @return string
         */
        $getNodeKey = function($configSetItem) {
            return constant(get_class($configSetItem) . '::KEY') . ' ' . $configSetItem->getId();
        };
        
        /**
         * Get the node object based on the node key (inverse of getNodeKey)
         * 
         * @param string Node key
         * @return Stephino_Rpg_Config_Item_Single|null
         */
        $getNodeObject = function($nodeKey) {
            list($itemKey, $itemId) = explode(' ', $nodeKey);
            
            // Prepare the object
            $object = null;
            switch ($itemKey) {
                case Stephino_Rpg_Config_Building::KEY:
                    $object = Stephino_Rpg_Config::get()->buildings()->getById($itemId);
                    break;

                case Stephino_Rpg_Config_ResearchField::KEY:
                    $object = Stephino_Rpg_Config::get()->researchFields()->getById($itemId);
                    break;

                case Stephino_Rpg_Config_ResearchArea::KEY:
                    $object = Stephino_Rpg_Config::get()->researchAreas()->getById($itemId);
                    break;

                case Stephino_Rpg_Config_Government::KEY:
                    $object = Stephino_Rpg_Config::get()->governments()->getById($itemId);
                    break;

                case Stephino_Rpg_Config_Unit::KEY:
                    $object = Stephino_Rpg_Config::get()->units()->getById($itemId);
                    break;

                case Stephino_Rpg_Config_Ship::KEY:
                    $object = Stephino_Rpg_Config::get()->ships()->getById($itemId);
                    break;
            }
            return $object;
        };
        
        /**
         * Treat configuration items as binary trees and check for infinite recursion
         * 
         * @param Stephino_Rpg_Config_Item_Single|null $configSetItem  Configuration Item
         * @param int                                    $configSetLevel Configuration Item required level
         * @param array                                  $parentThread   Configuration Item dependency branch
         * @throws Exception
         */
        $validateBts = function($configSetItem, $configSetLevel = 1, $parentThread = array()) use(&$validateBts, &$validBts, &$getNodeKey, &$getNodeObject) {
            /*@var $configSetItem Stephino_Rpg_Config_Trait_Requirement */
            if (null === $configSetItem) {
                return;
            }
            
            // Get the node key
            $nodeKey = $getNodeKey($configSetItem);
            
            // Levels are not enabled for this research field
            if ($configSetItem instanceof Stephino_Rpg_Config_ResearchField) {
                if (!$configSetItem->getLevelsEnabled() && $configSetLevel > 1) {
                    throw new Exception(
                        sprintf(
                            __('Requirements: %s does not allow multiple upgrades', 'stephino-rpg'),
                            '<b>' . (null === $configSetItem->getName() ? $nodeKey : $configSetItem->getName()) . '</b>'
                        )
                    );
                }
            }
            
            // Node already set, detected an infinte loop
            if (isset($parentThread[$nodeKey])) {
                // Prepare the loop
                $infiniteLoop = array_keys($parentThread);
                
                // Get the inner loop only
                $infiniteLoop = array_slice($infiniteLoop, 0, array_search($nodeKey, $infiniteLoop) + 1);
                
                // Set the loop start
                array_unshift($infiniteLoop, $nodeKey);
                
                // Convert to a human-readable format
                $infiniteLoop = array_map(function($itemKey) use (&$getNodeObject) {
                    // Get the corresponding object
                    $itemObject = $getNodeObject($itemKey);
                    
                    // Show the user-defined name (if available) or the original item key
                    return null === $itemObject || null === $itemObject->getName() ? $itemKey : $itemObject->getName();
                }, $infiniteLoop);
                
                // Stop here
                throw new Exception(
                    sprintf(
                        __('Requirements: infinite recursion on %s', 'stephino-rpg'),
                        '[<b>' . implode(' › ', $infiniteLoop) . '</b>]'
                    )
                );
            }
            
            // Store this node (prepend)
            $parentThread = array($nodeKey => $configSetLevel) + $parentThread;
                
            // Append this node
            if (null !== $configSetItem->getRequiredBuilding() || null !== $configSetItem->getRequiredResearchField()) {
                // Required building branch
                $validateBts(
                    $configSetItem->getRequiredBuilding(), 
                    $configSetItem->getRequiredBuildingLevel(), 
                    $parentThread
                );
                
                // Required research field branch
                $validateBts(
                    $configSetItem->getRequiredResearchField(),
                    $configSetItem->getRequiredResearchFieldLevel(),
                    $parentThread
                );
            } else {
                if (count($parentThread) > 1) {
                    // Remove the stem (no longer needed for validation)
                    array_pop($parentThread);
                    
                    // Store the stem-to-leaf path
                    $validBts[] = $parentThread;
                }
            }
        };
        
        // Prepare the configuration items requirement branches
        $configBranches = array();
        
        /* @var $configSet Stephino_Rpg_Config_Item_Collection */
        foreach(Stephino_Rpg_Config::get()->all() as $configSet) {
            if ($configSet instanceof Stephino_Rpg_Config_Item_Collection) {
                foreach($configSet->getAll() as $configSetItem) {
                    /* @var $configSetItem Stephino_Rpg_Config_Trait_Requirement */
                    if ($configSetItem instanceof Stephino_Rpg_Config_Item_Single
                        && in_array(Stephino_Rpg_Config_Trait_Requirement::class, class_uses($configSetItem))) {
                        
                        // Re-initialize the empty binary tree structure array
                        $validBts = array();
                        
                        // Validate the binary tree structure (look for infinite dependency loops)
                        $validateBts($configSetItem);
                        
                        // Store the binary tree structure (as individual stem-to-leaf paths)
                        $configBranches[$getNodeKey($configSetItem)] = $validBts;
                    }
                }
            }
        }
        
        // Prepare the unlocked items
        $unlockLevels = array();
        foreach ($configBranches as $nodeKey => $nodeRequirements) {
            if (!count($nodeRequirements)) {
                $unlockLevels[$nodeKey] = 1;
            }
        }
        
        // No starting point
        if (!count($unlockLevels)) {
            throw new Exception(__('Requirements: all models are unreachable', 'stephino-rpg'));
        }
        
        // Prepare the unlock stages
        $unlockStages = array();
        
        /**
         * Prepare the unlock stages: grouped lists of items, in the order they become reachable
         * Also store unlocked item levels, as the stages progress
         * 
         * @param array $configBranches Configuration Items requirement branches
         */
        $prepareUnlockStages = function($configBranches) use(&$prepareUnlockStages, &$unlockLevels, &$unlockStages) {
            // Store the unlocks made in this step
            $unlockStage = array();
            
            // Next step levels
            $reqNextStep = array();
            
            // Go through the requirements for each branch
            $reqList = array();
            foreach ($configBranches as $branchKey => $branchReqs) {
                $reqList[$branchKey] = array();
                
                // Prepare the minimum requirements list for this item
                foreach ($branchReqs as $branchReq) {
                    foreach ($branchReq as $branchReqKey => $branchReqLevel) {
                        // Item not yet created or not at required level
                        if (!isset($unlockLevels[$branchReqKey]) || $unlockLevels[$branchReqKey] < $branchReqLevel) {
                            // Store the level
                            if (!isset($reqList[$branchKey][$branchReqKey]) || $reqList[$branchKey][$branchReqKey] < $branchReqLevel) {
                                $reqList[$branchKey][$branchReqKey] = $branchReqLevel;
                            }
                        }
                    }
                }

                // All requirements were met
                if (!count($reqList[$branchKey])) {
                    // Append to the unlocks for this stage
                    $unlockStage[] = $branchKey;
                    
                    // Remove from the check list
                    unset($configBranches[$branchKey]);
                }
            }
            
            // Create items
            if (count($unlockStage)) {
                // Create the items at level 1
                foreach ($unlockStage as $branchKey) {
                    $unlockLevels[$branchKey] = 1;
                }
                
                // Store the stage
                $unlockStages[] = $unlockStage;
            }
            
            // Find the next step requirements
            foreach ($configBranches as $branchKey => $branchReqs) {
                // Requirements close to completion (required items exist)
                $reqListClose = true;
                foreach ($branchReqs as $branchReq) {
                    foreach ($branchReq as $branchReqKey => $branchReqLevel) {
                        // Required item created
                        $reqListClose = $reqListClose && isset($unlockLevels[$branchReqKey]);
                        
                        // Item cannot be created in this stage
                        if (!$reqListClose) {
                            break 2;
                        }
                    }
                }

                // All requirements were met
                if ($reqListClose && count($reqList[$branchKey])) {
                    if (!count($reqNextStep) || count($reqNextStep) > count($reqList[$branchKey]) || array_sum($reqNextStep) > array_sum($reqList[$branchKey])) {
                        $reqNextStep = $reqList[$branchKey];
                    }
                }
            }
            
            // The next step is in sight
            if (count($reqNextStep)) {
                // Perform the necessary upgrades
                foreach ($reqNextStep as $reqNextStepKey => $reqNextStepLevel) {
                    $unlockLevels[$reqNextStepKey] = $reqNextStepLevel;
                }
                
                // Go to the next level
                $prepareUnlockStages($configBranches);
            }
        };
        
        // Get the unlock stages
        $prepareUnlockStages($configBranches);
        
        // Some items are unreachable
        $unreachableItems = array_diff(array_keys($configBranches), array_keys($unlockLevels));
        if (count($unreachableItems)) {
            // Human readable values
            $unreachableItems = array_map(function($itemKey) use(&$getNodeObject) {
                // Get the corresponding object
                $itemObject = $getNodeObject($itemKey);
                
                // Show the user-defined name (if available) or the original item key
                return null === $itemObject || null === $itemObject->getName() ? $itemKey : $itemObject->getName();
            }, $unreachableItems);
            
            // Inform the user
            throw new Exception(
                sprintf(
                    __('Requirements: %s are unreachable', 'stephino-rpg'),
                    '<b>' . implode(', ', $unreachableItems) . '</b>'
                )
            );
        }
        
        // Get objects instead
        if ($returnObjects) {
            $unlockStages = array_map(function($unlockStage) use(&$getNodeObject) {
                foreach ($unlockStage as &$itemName) {
                    $itemName = $getNodeObject($itemName);
                }

                return $unlockStage;
            }, $unlockStages);
        }
        
        // Prepare the result
        return $unlockStages;
    }
    
    /**
     * Get all the models this Building or Research Field needs and their respective levels
     * 
     * @param Stephino_Rpg_Config_Building|Stephino_Rpg_Config_ResearchField $configObject Building or Research Field object
     * @return array Array of [<ul>
     * <li><b>Stephino_Rpg_Config_Building|Stephino_Rpg_Config_ResearchField</b> Configuration Item</li>
     * <li><b>int</b> Minimum required level</li>
     * </ul>]
     */
    public static function getUnlockTree($configObject) {
        // Preprae the dependency list
        $result = array();
        
        // Valid configuration object
        if ($configObject instanceof Stephino_Rpg_Config_Building
            || $configObject instanceof Stephino_Rpg_Config_ResearchField
            || $configObject instanceof Stephino_Rpg_Config_ResearchArea) {
            
            /**
             * Get the unlock tree
             * 
             * @param Stephino_Rpg_Config_Building|Stephino_Rpg_Config_ResearchField $configObject Building or Research Field object
             */
            $getTree = function($configObject) use (&$getTree, &$result) {
                // Required building
                if (null !== $configObject->getRequiredBuilding()) {
                    $getTree($configObject->getRequiredBuilding());
                    
                    // Prepare the dependency key
                    $resultKey = constant(get_class($configObject->getRequiredBuilding()) . '::KEY') . ' ' . $configObject->getRequiredBuilding()->getId();
                    
                    // Initialize
                    if (!isset($result[$resultKey])) {
                        $result[$resultKey] = array(
                            $configObject->getRequiredBuilding(),
                            $configObject->getRequiredBuildingLevel()
                        );
                    } else {
                        // Store the max level
                        if ($result[$resultKey][1] < $configObject->getRequiredBuildingLevel()) {
                            $result[$resultKey][1] = $configObject->getRequiredBuildingLevel();
                        }
                    }
                }
                
                // Required research field
                if (null !== $configObject->getRequiredResearchField()) {
                    $getTree($configObject->getRequiredResearchField()->getResearchArea());
                    $getTree($configObject->getRequiredResearchField());
                    
                    // Prepare the dependency key
                    $resultKey = constant(get_class($configObject->getRequiredResearchField()) . '::KEY') . ' ' . $configObject->getRequiredResearchField()->getId();
                    
                    // Initialize
                    if (!isset($result[$resultKey])) {
                        $result[$resultKey] = array(
                            $configObject->getRequiredResearchField(),
                            $configObject->getRequiredResearchFieldLevel()
                        );
                    } else {
                        // Store the max level
                        if ($result[$resultKey][1] < $configObject->getRequiredResearchFieldLevel()) {
                            $result[$resultKey][1] = $configObject->getRequiredResearchFieldLevel();
                        }
                    }
                }
            };
            
            // Store the result
            $getTree($configObject);
        }
        return $result;
    }
    
    /**
     * Get the next item to create/upgrade
     * 
     * @param int[] $buildingLevels      Associative array of Building Configuration ID => Building Level
     * @param int[] $researchFieldLevels Associative array of Research Field Configuration ID => Research Field Level
     * @return array|null Array of [<ul>
     *     <li><b>Stephino_Rpg_Config_Building|Stephino_Rpg_Config_ResearchField</b> Item configuration object</li>
     *     <li><b>int</b> Item current level</li>
     *     <li><b>int</b> Item target level</li>
     * </ul>] or <b>null</b> if no step available
     */
    public static function getUnlockNext($buildingLevels, $researchFieldLevels) {
        // Prepare the result
        $result = null;
        
        // Valid input
        if (is_array($buildingLevels) && is_array($researchFieldLevels)) {
            // Sanitize the levels; 0 means the entity is under construction
            foreach ($buildingLevels as $buildingConfig => &$buildingLevel) {
                $buildingLevel = intval($buildingLevel);
                if ($buildingLevel <= 0) {
                    unset($buildingLevels[$buildingConfig]);
                }
            }
            foreach ($researchFieldLevels as $researchFieldConfig => &$researchFieldLevel) {
                $researchFieldLevel = intval($researchFieldLevel);
                if ($researchFieldLevel <= 0) {
                    unset($researchFieldLevels[$researchFieldConfig]);
                }
            }
            
            try {
                // Get the stages
                $stages = self::getUnlockStages(true);
                
                // Go through the items one by one
                foreach ($stages as $stageData) {
                    foreach ($stageData as $itemObject) {
                        // Item not yet created
                        if (($itemObject instanceof Stephino_Rpg_Config_Building && !isset($buildingLevels[$itemObject->getId()]))
                            || ($itemObject instanceof Stephino_Rpg_Config_ResearchField && !isset($researchFieldLevels[$itemObject->getId()]))) {
                            // Building requirements
                            $itemReqB = true;
                            if (null !== $itemObject->getRequiredBuilding()) {
                                if (!isset($buildingLevels[$itemObject->getRequiredBuilding()->getId()])
                                    || $buildingLevels[$itemObject->getRequiredBuilding()->getId()] < $itemObject->getRequiredBuildingLevel()) {
                                    $itemReqB = false;
                                }
                            }
                            
                            // Research field requirements
                            $itemReqRF = true;
                            if (null !== $itemObject->getRequiredResearchField()) {
                                if (!isset($researchFieldLevels[$itemObject->getRequiredResearchField()->getId()])
                                    || $researchFieldLevels[$itemObject->getRequiredResearchField()->getId()] < $itemObject->getRequiredResearchFieldLevel()) {
                                    $itemReqRF = false;
                                }
                            }
                            
                            // Requirements met but not yet built
                            if ($itemReqB && $itemReqRF) {
                                $result = array($itemObject, 0, 1);
                            } else {
                                if (!$itemReqB) {
                                    $result = array(
                                        $itemObject->getRequiredBuilding(), 
                                        // Current level
                                        isset($buildingLevels[$itemObject->getRequiredBuilding()->getId()])
                                            ? $buildingLevels[$itemObject->getRequiredBuilding()->getId()]
                                            : 0,
                                        
                                        // Target level
                                        isset($buildingLevels[$itemObject->getRequiredBuilding()->getId()])
                                            ? $itemObject->getRequiredBuildingLevel()
                                            : 1,
                                    );
                                } else {
                                    $result = array(
                                        $itemObject->getRequiredResearchField(), 
                                        
                                        // Current level
                                        isset($researchFieldLevels[$itemObject->getRequiredResearchField()->getId()])
                                            ? $researchFieldLevels[$itemObject->getRequiredResearchField()->getId()]
                                            : 0,
                                        
                                        // Target level
                                        isset($researchFieldLevels[$itemObject->getRequiredResearchField()->getId()])
                                            ? $itemObject->getRequiredResearchFieldLevel()
                                            : 1,
                                    );
                                }
                            }
                            
                            // Found our first task
                            break 2;
                        }
                    }
                }
            } catch (Exception $exc) {
                Stephino_Rpg_Log::check() && Stephino_Rpg_Log::warning("Utils_Config.getUnlockNext: {$exc->getMessage()}");
                Stephino_Rpg_Log::check() && Stephino_Rpg_Log::warning($buildingLevels);
                Stephino_Rpg_Log::check() && Stephino_Rpg_Log::warning($researchFieldLevels);
            }
        }
        
        return $result;
    }
    
    /**
     * Get the html <b>data-click</b> attributes for .item-card elements
     * 
     * @param Stephino_Rpg_Config_Item_Single $configObject    Configuration object
     * @param boolean                         $showHelp        (optional) Show the help dialog; default <b>false</b>
     * @param boolean                         $buildingUpgrade (optional) Show the upgrade dialog for buildings; only works if <b>showHelp</b> is false; default <b>false</b>
     * @return array Array of <ul>
     * <li><b>string</b> Item card data-click attribute</li>
     * <li><b>string</b> Item card data-click-args attribute</li>
     * </ul>
     */
    public static function getItemCardAttributes($configObject, $showHelp = false, $buildingUpgrade = false) {
        // Prepare the default attributes (help sections)
        $itemCardFn = 'helpDialog';
        $itemCardArgs = $configObject instanceof Stephino_Rpg_Config_Item_Single
            ? ($configObject->keyCollection() . ',' . $configObject->getId())
            : Stephino_Rpg_Config_Core::KEY . ',0';
        
        // Go deeper
        if (!$showHelp) {
            switch (true) {
                // Government: point to main building
                case $configObject instanceof Stephino_Rpg_Config_Government:
                    $itemCardFn = 'buildingViewDialog';
                    $itemCardArgs = Stephino_Rpg_Config::get()->core()->getMainBuilding()->getId();
                    break;
                
                // Building: upgrade or view dialog
                case $configObject instanceof Stephino_Rpg_Config_Building:
                    $itemCardFn = $buildingUpgrade 
                        ? 'buildingUpgradeDialog'
                        : 'buildingViewDialog';
                    $itemCardArgs = $configObject->getId();
                    break;

                // Research field: highlight in research area
                case $configObject instanceof Stephino_Rpg_Config_ResearchField:
                    if (null !== $configObject->getResearchArea()) {
                        $itemCardFn = 'researchAreaInfo';
                        $itemCardArgs = $configObject->getResearchArea()->getId() . ',' 
                            . $configObject->getId();
                    }
                    break;

                // Research area
                case $configObject instanceof Stephino_Rpg_Config_ResearchArea:
                    $itemCardFn = 'researchAreaInfo';
                    $itemCardArgs = $configObject->getId();
                    break;
                
                // Unit/Ship: point to parent building
                case $configObject instanceof Stephino_Rpg_Config_Unit:
                case $configObject instanceof Stephino_Rpg_Config_Ship:
                    if (null !== $configObject->getBuilding()) {
                        $itemCardFn = 'buildingViewDialog';
                        $itemCardArgs = $configObject->getBuilding()->getId() . ',' 
                            . $configObject->keyCollection() . ',' 
                            . $configObject->getId();
                    }
                    break;
            }
        }
        
        return array(
            $itemCardFn, 
            $itemCardArgs
        );
    }
    
    /**
     * Get entity configuration object by capability
     * 
     * @param string $capability Entity capability, one of <ul>
     *     <li>Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_ATTACK</li>
     *     <li>Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_COLONIZER</li>
     *     <li>Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_SPY</li>
     *     <li>Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_TRANSPORTER</li>
     * </ul>
     * @return (Stephino_Rpg_Config_Unit|Stephino_Rpg_Config_Unit)[] List of configuration objects. The list may be empty
     */
    public static function getEntitiesByCapability($capability) {
        $result = array();
        
        // A specific ability
        switch ($capability) {
            case Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_ATTACK:
                $result = array_merge(
                    array_filter(
                        Stephino_Rpg_Config::get()->units()->getAll(),
                        function($unitConfig) {
                            /* @var $unitConfig Stephino_Rpg_Config_Unit */
                            return !$unitConfig->getCivilian();
                        }
                    ), 
                    array_filter(
                        Stephino_Rpg_Config::get()->ships()->getAll(),
                        function($shipConfig) {
                            /* @var $shipConfig Stephino_Rpg_Config_Ship */
                            return !$shipConfig->getCivilian();
                        }
                    )
                );
                break;

            case Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_COLONIZER:
                $result = array_merge(
                    array_filter(
                        Stephino_Rpg_Config::get()->units()->getAll(),
                        function($unitConfig) {
                            /* @var $unitConfig Stephino_Rpg_Config_Unit */
                            return $unitConfig->getCivilian() && $unitConfig->getAbilityColonize();
                        }
                    ), 
                    array_filter(
                        Stephino_Rpg_Config::get()->ships()->getAll(),
                        function($shipConfig) {
                            /* @var $shipConfig Stephino_Rpg_Config_Ship */
                            return $shipConfig->getCivilian() && $shipConfig->getAbilityColonize();
                        }
                    )
                );
                break;

            case Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_SPY:
                $result = array_filter(
                    Stephino_Rpg_Config::get()->units()->getAll(),
                    function($unitConfig) {
                        /* @var $unitConfig Stephino_Rpg_Config_Unit */
                        return $unitConfig->getCivilian() && $unitConfig->getAbilitySpy();
                    }
                );
                break;

            case Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_TRANSPORTER:
                $result = array_filter(
                    Stephino_Rpg_Config::get()->ships()->getAll(),
                    function($shipConfig) {
                        /* @var $shipConfig Stephino_Rpg_Config_Ship */
                        return $shipConfig->getCivilian() && $shipConfig->getAbilityTransport();
                    }
                );
                break;
        }
        
        return $result;
    }
}

/* EOF */