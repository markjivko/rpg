<?php

/**
 * Stephino_Rpg_Config_Ship
 * 
 * @title      Ship
 * @desc       Holds the configuration for a single "Ship" item
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Config_Ship extends Stephino_Rpg_Config_Item_Single {
    
    // Ship traits
    use Stephino_Rpg_Config_Trait_Entity {
        getBuilding as _getBuildingTrait;
        getDisbandable as _getDisbandableTrait;
        getDisbandablePopulation as _getDisbandablePopulationTrait;
        getCivilian as _getCivilianTrait;
        getAbilityColonize as _getAbilityColonizeTrait;
        getTransportMass as _getTransportMassTrait;
    }

    // Ship Modifiers
    use Stephino_Rpg_Config_Trait_Modifier {
        getModifier as _getModifierTrait;
        getModifierPolynomial as _getModifierPolynomialTrait;
    }
    
    // Ship Costs
    use Stephino_Rpg_Config_Trait_Cost;
    
    // Ship Requirements
    use Stephino_Rpg_Config_Trait_Requirement;
    
    /**
     * Serialization Key - Must be defined by each item individually
     */
    const KEY = 'ship';

    /**
     * Ship Name
     * 
     * @var string|null
     */
    protected $_name = null;

    /**
     * Ship Description
     * 
     * @var string|null
     */
    protected $_description = null;
    
    /**
     * Ability:Transport
     * 
     * @var boolean
     */
    protected $_abilityTransport = false;
    
    /**
     * Transport capacity
     * 
     * @var int
     */
    protected $_abilityTransportCapacity = 1;

    /**
     * Ship Capacity
     * 
     * @var int
     */
    protected $_capacity = 0;

    /**
     * Ship name<br/>
     * <span class="info">
     *     Image files are stored in <b>{current theme}/img/story/ships/{id}/</b><br/> 
     *     Required files: <b>512.png</b>, <b>768.png</b>
     * </span>
     * 
     * @return string Name
     */
    public function getName($htmlOutput = false) {
        return parent::getName($htmlOutput);
    }

    /**
     * Set the "Name" parameter
     * 
     * @param string|null $name Name
     * @return Stephino_Rpg_Config_Ship
     */
    public function setName($name) {
        $this->_name = (null === $name ? null : Stephino_Rpg_Utils_Lingo::cleanup($name));

        return $this;
    }

    /**
     * Ship description<br/>
     * <span class="info">MarkDown enabled</span>
     * 
     * @ref large
     * @return string|null Description
     */
    public function getDescription() {
        return $this->_description;
    }

    /**
     * Set the "Description" parameter
     * 
     * @param string|null $description Description
     * @return Stephino_Rpg_Config_Ship
     */
    public function setDescription($description) {
        $this->_description = (null === $description ? null : Stephino_Rpg_Utils_Lingo::cleanup($description));

        return $this;
    }
    
    /**
     * Set the <b>main building</b> for this ship
     * 
     * @return Stephino_Rpg_Config_Building|null Main Building
     */
    public function getBuilding() {
        return $this->_getBuildingTrait();
    }
    
    /**
     * Is this ship disbandable?<br/>
     * Disbanding ships converts them into <b>{x}</b>
     * 
     * @placeholder core.metricPopulationName,Population
     * @return boolean Disbandable
     */
    public function getDisbandable() {
        return $this->_getDisbandableTrait();
    }
    
    /**
     * The <b>{x}</b> gained when disbanding this ship
     * 
     * @depends disbandable
     * @placeholder core.metricPopulationName,Population
     * @default 1
     * @ref 0
     * @return int Disbandable: {x} reward
     */
    public function getDisbandablePopulation() {
        return $this->_getDisbandablePopulationTrait();
    }
    
    /**
     * This is <b>not</b> a military ship
     * 
     * @return boolean Commercial ship
     */
    public function getCivilian() {
        return $this->_getCivilianTrait();
    }
    
    /**
     * Can this ship colonize new cities?
     * 
     * @depends civilian
     * @return boolean Colonizer
     */
    public function getAbilityColonize() {
        return $this->_getAbilityColonizeTrait();
    }
    
    /**
     * Can this ship transport goods?
     * 
     * @depends civilian
     * @return boolean Transporter ship
     */
    public function getAbilityTransport() {
        return (null === $this->_abilityTransport ? false : $this->_abilityTransport);
    }
    
    /**
     * Set the "Ability:Transport" parameter
     * 
     * @param boolean $enabled Ability:Transport
     * @return Stephino_Rpg_Config_Ship
     */
    public function setAbilityTransport($enabled) {
        $this->_abilityTransport = (boolean) $enabled;
        
        return $this;
    }
    
    /**
     * Sets the capacity to transport goods
     * 
     * @depends abilityTransport
     * @ref 1
     * @return int Transporter capacity
     */
    public function getAbilityTransportCapacity() {
        return null === $this->_abilityTransportCapacity ? 1 : $this->_abilityTransportCapacity;
    }

    /**
     * Set the "Ability:Transport Capacity" parameter
     * 
     * @param int $transportCapacity Transport Capacity
     * @return Stephino_Rpg_Config_Ship
     */
    public function setAbilityTransportCapacity($transportCapacity) {
        $this->_abilityTransportCapacity = (null === $transportCapacity ? 1 : intval($transportCapacity));

        // Minimum
        if ($this->_abilityTransportCapacity < 1) {
            $this->_abilityTransportCapacity = 1;
        }
        
        return $this;
    }
    
    /**
     * Cargo space this <b>ship</b> occupies when transported from city to city<br/>
     * Does not apply for <b>transporter</b> ships
     * 
     * @depends !abilityTransport
     * @ref 1
     * @return int Transported mass
     */
    public function getTransportMass() {
        return $this->_getTransportMassTrait();
    }
    
    /**
     * Total <b>troops mass</b> that can be transported by this ship in a military convoy<br/>
     * If the total ship capacity of a convoy is larger than that of the infantry,
     * the travel speed is 100%, otherwise the convoy moves at 50% speed
     * 
     * @depends !civilian
     * @ref 0
     * @return int Troops capacity
     */
    public function getTroopCapacity() {
        return null === $this->_capacity ? 0 : $this->_capacity;
    }

    /**
     * Set the "Troop Capacity" parameter
     * 
     * @param int $troopCapacity Troop Capacity
     * @return Stephino_Rpg_Config_Ship
     */
    public function setTroopCapacity($troopCapacity) {
        $this->_capacity = (null === $troopCapacity ? 0 : intval($troopCapacity));

        // Minimum
        if ($this->_capacity < 0) {
            $this->_capacity = 0;
        }
        
        return $this;
    }
    
    /**
     * Select a set of features this <b>ship</b> enables
     * 
     * @return Stephino_Rpg_Config_Modifier|null Modifier
     */
    public function getModifier() {
        return $this->_getModifierTrait();
    }

    /**
     * Effects polynomial factor
     * 
     * @return poly|null Modifier polynomial
     */
    public function getModifierPolynomial() {
        return $this->_getModifierPolynomialTrait();
    }

}

/*EOF*/