<?php

/**
 * Stephino_Rpg_Config_Unit
 * 
 * @title      Unit
 * @desc       Holds the configuration for a single "Unit" item
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Config_Unit extends Stephino_Rpg_Config_Item_Single {

    // Unit traits
    use Stephino_Rpg_Config_Trait_Entity {
        getBuilding as _getBuildingTrait;
        getDisbandable as _getDisbandableTrait;
        getDisbandablePopulation as _getDisbandablePopulationTrait;
        getCivilian as _getCivilianTrait;
        getAbilityColonize as _getAbilityColonizeTrait;
    }
    
    // Unit Modifiers
    use Stephino_Rpg_Config_Trait_Modifier {
        getModifier as _getModifierTrait;
        getModifierPolynomial as _getModifierPolynomialTrait;
    }
    
    // Unit Costs
    use Stephino_Rpg_Config_Trait_Cost;
    
    // Unit Requirements
    use Stephino_Rpg_Config_Trait_Requirement;
    
    /**
     * Serialization Key - Must be defined by each item individually
     */
    const KEY = 'unit';
    
    /**
     * Class name of corresponding Collection Item
     */
    const COLLECTION_CLASS = Stephino_Rpg_Config_Units::class;

    /**
     * Unit Name
     * 
     * @var string|null
     */
    protected $_name = null;

    /**
     * Unit Description
     * 
     * @var string|null
     */
    protected $_description = null;
    
    /**
     * Ability:Spy
     * 
     * @var boolean
     */
    protected $_abilitySpy = false;
    
    /**
     * Ability:Spy:Success rate
     * 
     * @var int
     */
    protected $_spySuccessRate = 50;
    
    /**
     * Ability:Spy:Success rate polynomial
     * 
     * @var string|null
     */
    protected $_spySuccessRatePoly = 50;

    /**
     * Unit Mass
     * 
     * @var int
     */
    protected $_troopMass = 0;

    /**
     * Loot box size
     *
     * @var int
     */
    protected $_lootBox = 0;

    /**
     * Unit Building
     * 
     * @var int|null Stephino_Rpg_Config_Building ID
     */
    protected $_buildingId = null;
    
    /**
     * Unit name<br/>
     * <span class="info">
     *     Image files are stored in <b>{current theme}/img/story/units/{id}/</b><br/> 
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
     * @return Stephino_Rpg_Config_Unit
     */
    public function setName($name) {
        $this->_name = (null === $name ? null : Stephino_Rpg_Utils_Lingo::cleanup($name));

        return $this;
    }

    /**
     * Unit description<br/>
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
     * @return Stephino_Rpg_Config_Unit
     */
    public function setDescription($description) {
        $this->_description = (null === $description ? null : Stephino_Rpg_Utils_Lingo::cleanup($description));

        return $this;
    }
    
    /**
     * Set the <b>main building</b> for this unit
     * 
     * @return Stephino_Rpg_Config_Building|null Main Building
     */
    public function getBuilding() {
        return $this->_getBuildingTrait();
    }
    
    /**
     * Is this unit disbandable?<br/>
     * Disbanding units converts them into <b>{x}</b>
     * 
     * @placeholder core.metricPopulationName,Population
     * @return boolean Disbandable
     */
    public function getDisbandable() {
        return $this->_getDisbandableTrait();
    }
    
    /**
     * The <b>{x}</b> gained when disbanding this unit
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
     * This is <b>not</b> a military unit
     * 
     * @return boolean Civilian
     */
    public function getCivilian() {
        return $this->_getCivilianTrait();
    }
    
    /**
     * Can this unit colonize new cities?
     * 
     * @depends civilian
     * @return boolean Colonizer
     */
    public function getAbilityColonize() {
        return $this->_getAbilityColonizeTrait();
    }
    
    /**
     * Can this unit spy on other cities?
     * 
     * @depends civilian
     * @return boolean Spy unit
     */
    public function getAbilitySpy() {
        return (null === $this->_abilitySpy ? false : $this->_abilitySpy);
    }
    
    /**
     * Set the "Ability:Spy" parameter
     * 
     * @param boolean $enabled Ability:Spy
     * @return Stephino_Rpg_Config_Unit
     */
    public function setAbilitySpy($enabled) {
        $this->_abilitySpy = (boolean) $enabled;
        
        return $this;
    }
    
    /**
     * Set the spy success probability
     * 
     * @ref 1,100
     * @default 50
     * @depends abilitySpy
     * @return int Spy: Success rate (%)
     */
    public function getSpySuccessRate() {
        return null === $this->_spySuccessRate ? 50 : $this->_spySuccessRate;
    }
    
    /**
     * Set the "Success Rate" parameter
     * 
     * @param int $successRate Success rate
     * @return Stephino_Rpg_Config_Unit
     */
    public function setSpySuccessRate($successRate) {
        $this->_spySuccessRate = intval($successRate);
        
        // Min and max
        if ($this->_spySuccessRate < 1) {
            $this->_spySuccessRate = 1;
        }
        if ($this->_spySuccessRate > 100) {
            $this->_spySuccessRate = 100;
        }
        
        return $this;
    }
    
    /**
     * Spies abilities change with the recruiting building level
     * 
     * @depends abilitySpy
     * @return poly|null Spy: Success rate polynomial
     */
    public function getSpySuccessRatePolynomial() {
        return $this->_spySuccessRatePoly;
    }
    
    /**
     * Set the "Spy success rate polynomial" parameter
     * 
     * @param string|null $successRatePolynomial Success rate polynomial
     * @return Stephino_Rpg_Config_Unit
     */
    public function setSpySuccessRatePolynomial($successRatePolynomial) {
        $this->_spySuccessRatePoly = $this->_sanitizePoly($successRatePolynomial);

        return $this;
    }
    
    /**
     * Select a set of features this <b>unit</b> enables
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
    
    /**
     * Unit mass when part of a military convoy
     * 
     * @depends !civilian
     * @ref 0
     * @return int Troops mass
     */
    public function getTroopMass() {
        return null == $this->_troopMass ? 0 : $this->_troopMass;
    }

    /**
     * Set the "Troop Mass" parameter
     * 
     * @param int $troopMass Troop Mass
     * @return Stephino_Rpg_Config_Unit
     */
    public function setTroopMass($troopMass) {
        $this->_troopMass = (null === $troopMass ? 0 : intval($troopMass));

        // Minimum
        if ($this->_troopMass < 0) {
            $this->_troopMass = 0;
        }
        
        return $this;
    }
    
}

/*EOF*/