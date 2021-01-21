<?php

/**
 * Stephino_Rpg_Config_Building
 * 
 * @title      Building
 * @desc       Holds the configuration for a single "Building" item
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Config_Building extends Stephino_Rpg_Config_Item_Single {

    // Virtual resource keys
    const RES_MILITARY_ATTACK  = 'res_military_attack';
    const RES_MILITARY_DEFENSE = 'res_military_defense';
    
    // Building Modifiers
    use Stephino_Rpg_Config_Trait_Modifier {
        getModifier as _getModifierTrait;
        getModifierPolynomial as _getModifierPolynomialTrait;
    }
    
    // Building Costs
    use Stephino_Rpg_Config_Trait_Cost;
    
    // Building Requirements
    use Stephino_Rpg_Config_Trait_Requirement;
    
    /**
     * Serialization Key - Must be defined by each item individually
     */
    const KEY = 'building';

    /**
     * Building Name
     * 
     * @var string|null
     */
    protected $_name = null;

    /**
     * Building Description
     * 
     * @var string|null
     */
    protected $_description = null;

    /**
     * Attack points
     *
     * @var int|null
     */
    protected $_attackPoints = null;
    
    /**
     * Attack points polynomial
     *
     * @var int|null
     */
    protected $_attackPointsPolynomial = null;

    /**
     * Defense points
     *
     * @var int|null
     */
    protected $_defensePoints = null;
    
    /**
     * Defense points polynomial
     *
     * @var int|null
     */
    protected $_defensePointsPolynomial = null;
    
    /**
     * Use workers
     *
     * @var boolean
     */
    protected $_useWorkers = false;
    
    /**
     * Workers capacity
     *
     * @var int
     */
    protected $_workersCapacity = 1;
    
    /**
     * Workers capacity polynomial
     *
     * @var string|null
     */
    protected $_workersCapacityPolynomial = null;
    
    /**
     * Refund percentage
     * 
     * @var int|null
     */
    protected $_refundPercent = null;
    
    /**
     * City Animations JSON
     * 
     * @var animations|null
     */
    protected $_cityAnimations = null;

    /**
     * Action Area JSON
     * 
     * @var action_area|null
     */
    protected $_actionArea = null;

    /**
     * Check whether this building is the main building
     * 
     * @return boolean
     */
    public function isMainBuilding() {
        $result = false;
        
        // Main building defined
        if (null !== Stephino_Rpg_Config::get()->core()->getMainBuilding()) {
            $result = ($this->getId() == Stephino_Rpg_Config::get()->core()->getMainBuilding()->getId());
        }
        
        return $result;
    }
    
    /**
     * Check whether this building is the market building
     * 
     * @return boolean
     */
    public function isMarketBuilding() {
        $result = false;
        
        // Main building defined
        if (Stephino_Rpg_Config::get()->core()->getMarketEnabled()
            && null !== Stephino_Rpg_Config::get()->core()->getMarketBuilding()) {
            $result = ($this->getId() == Stephino_Rpg_Config::get()->core()->getMarketBuilding()->getId());
        }
        
        return $result;
    }
    
    /**
     * Building name<br/>
     * <span class="info">
     *     Image files are stored in <b>{current theme}/img/story/buildings/{id}/</b><br/> 
     *     Required files: <b>512.png</b>, <b>768.png</b>
     * </span>
     * 
     * @return string Name
     */
    public function getName($htmlOutput = false) {
        return parent::getName($htmlOutput);
    }

    /**
     * Set the "Building Name" parameter
     * 
     * @param string|null $name Building Name
     * @return Stephino_Rpg_Config_Building
     */
    public function setName($name) {
        $this->_name = (null === $name ? null : Stephino_Rpg_Utils_Lingo::cleanup($name));

        return $this;
    }

    /**
     * Building description<br/>
     * <span class="info">MarkDown enabled</span>
     * 
     * @ref large
     * @return string|null Description
     */
    public function getDescription() {
        return $this->_description;
    }

    /**
     * Set the "Building Description" parameter
     * 
     * @param string|null $description Building Description
     * @return Stephino_Rpg_Config_Building
     */
    public function setDescription($description) {
        $this->_description = (null === $description ? null : Stephino_Rpg_Utils_Lingo::cleanup($description));

        return $this;
    }
    
    /**
     * This building will attack enemy units and ships
     * 
     * @ref 0
     * @return int|null Attack points
     */
    public function getAttackPoints() {
        return $this->_attackPoints;
    }

    /**
     * Set the "Attack Points" parameter
     * 
     * @param int|null $attackPoints Attack Points
     * @return Stephino_Rpg_Config_Building
     */
    public function setAttackPoints($attackPoints) {
        $this->_attackPoints = (null === $attackPoints ? null : intval($attackPoints));

        // Minimum
        if (null !== $this->_attackPoints && $this->_attackPoints < 0) {
            $this->_attackPoints = 0;
        }
        
        return $this;
    }
    
    /**
     * Attack points polynomial
     * 
     * @return poly|null Attack points polynomial
     */
    public function getAttackPointsPolynomial() {
        return $this->_attackPointsPolynomial;
    }

    /**
     * Set the "Attack points Polynomial" parameter
     * 
     * @param string|null $attackPointsPolynomial Attack points Polynomial
     * @return Stephino_Rpg_Config_Building
     */
    public function setAttackPointsPolynomial($attackPointsPolynomial) {
        $this->_attackPointsPolynomial = $this->_sanitizePoly($attackPointsPolynomial);

        return $this;
    }
    
    /**
     * This building will defend against enemy units and ships
     * 
     * @ref 0
     * @return int|null Defense points
     */
    public function getDefensePoints() {
        return $this->_defensePoints;
    }

    /**
     * Set the "Defense points" parameter
     * 
     * @param int|null $defensePoints Defense points
     * @return Stephino_Rpg_Config_Building
     */
    public function setDefensePoints($defensePoints) {
        $this->_defensePoints = (null === $defensePoints ? null : intval($defensePoints));

        // Minimum
        if (null !== $this->_defensePoints && $this->_defensePoints < 0) {
            $this->_defensePoints = 0;
        }
        
        return $this;
    }
    
    /**
     * Defense points polynomial
     * 
     * @return poly|null Defense points polynomial
     */
    public function getDefensePointsPolynomial() {
        return $this->_defensePointsPolynomial;
    }

    /**
     * Set the "Defense points Polynomial" parameter
     * 
     * @param string|null $defensePointsPolynomial Defense points Polynomial
     * @return Stephino_Rpg_Config_Building
     */
    public function setDefensePointsPolynomial($defensePointsPolynomial) {
        $this->_defensePointsPolynomial = $this->_sanitizePoly($defensePointsPolynomial);

        return $this;
    }

    /**
     * This building needs workers in order to function<br/>
     * Maximum production is achieved when reaching workers capacity
     * 
     * @return boolean Use workers
     */
    public function getUseWorkers() {
        return (null === $this->_useWorkers ? false : $this->_useWorkers);
    }

    /**
     * Set the "Use Workers" parameter
     * 
     * @param boolean $enabled Use Workers
     * @return Stephino_Rpg_Config_Building
     */
    public function setUseWorkers($enabled) {
        $this->_useWorkers = (boolean) $enabled;

        return $this;
    }

    /**
     * The number of workers needed to produce at full capacity
     * 
     * @depends useWorkers
     * @ref 1
     * @return int Workers capacity
     */
    public function getWorkersCapacity() {
        return (null === $this->_workersCapacity ? 1 : $this->_workersCapacity);
    }

    /**
     * Set the "Worker Capacity" parameter
     * 
     * @param int $numOfWorkers Number of workers
     * @return Stephino_Rpg_Config_Building
     */
    public function setWorkersCapacity($numOfWorkers) {
        $this->_workersCapacity = intval($numOfWorkers);

        // Minimum
        if ($this->_workersCapacity < 1) {
            $this->_workersCapacity = 1;
        }
        
        return $this;
    }
    
    /**
     * Change the number of workers needed to produce at full capacity
     * 
     * @depends useWorkers
     * @return poly|null Workers capacity polynomial
     */
    public function getWorkersCapacityPolynomial() {
        return $this->_workersCapacityPolynomial;
    }

    /**
     * Set the "Workers Capacity Polynomial" parameter
     * 
     * @param string|null $workersCapacityPolynomial Workers Capacity Polynomial
     * @return Stephino_Rpg_Config_Building
     */
    public function setWorkersCapacityPolynomial($workersCapacityPolynomial) {
        $this->_workersCapacityPolynomial = $this->_sanitizePoly($workersCapacityPolynomial);

        return $this;
    }
    
    /**
     * Refund some of the building costs in case the player cancels the upgrade
     * 
     * @default 50
     * @ref 0,100
     * @return int Refund Policy (%)
     */
    public function getRefundPercent() {
        return (null === $this->_refundPercent ? 50 : $this->_refundPercent);
    }

    /**
     * Set the "Refund Percent" parameter
     * 
     * @param int|null $percent Refund Percent
     * @return Stephino_Rpg_Config_Building
     */
    public function setRefundPercent($percent) {
        $this->_refundPercent = (null === $percent ? 50 : intval($percent));
        
        // Minimum and maximum
        if ($this->_refundPercent < 0) {
            $this->_refundPercent = 0;
        }
        if ($this->_refundPercent > 100) {
            $this->_refundPercent = 100;
        }
        
        return $this;
    }
    
    /**
     * Define custom character and environment animations for this building<br/>
     * Visible from the City view
     * 
     * @levels true
     * @ref 512
     * @return animations|null City animations
     */
    public function getCityAnimations() {
        return $this->_cityAnimations;
    }

    /**
     * Set the "City Animations" parameter
     * 
     * @param animations|null $animations Animations JSON
     * @return Stephino_Rpg_Config_Building
     */
    public function setCityAnimations($animations) {
        // Set the animations
        $this->_cityAnimations = $this->_sanitizeLevels($animations, '_sanitizeAnimations');

        return $this;
    }

    /**
     * Mark the clickable area on this building
     * 
     * @levels true
     * @return action_area|null Action area
     */
    public function getActionArea() {
        return $this->_actionArea;
    }

    /**
     * Set the "Action Area" parameter
     * 
     * @param action_area|null $actionArea Action Area JSON
     * @return Stephino_Rpg_Config_Building
     */
    public function setActionArea($actionArea) {
        // Set the action area
        $this->_actionArea = $this->_sanitizeLevels($actionArea, '_sanitizeActionArea');

        return $this;
    }
    
    /**
     * Select a set of features this <b>building</b> model enables
     * 
     * @return Stephino_Rpg_Config_Modifier|null Modifier
     */
    public function getModifier() {
        return $this->_getModifierTrait();
    }

    /**
     * Effects cast by the modifier are amplified with the <b>building</b> level
     * 
     * @return poly|null Modifier polynomial
     */
    public function getModifierPolynomial() {
        return $this->_getModifierPolynomialTrait();
    }

}

/*EOF*/