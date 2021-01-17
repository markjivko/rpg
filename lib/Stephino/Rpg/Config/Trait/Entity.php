<?php
/**
 * Stephino_Rpg_Config_Trait_Entity
 * 
 * @title     Entity
 * @desc      Define entity items
 * @copyright (c) 2020, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */

trait Stephino_Rpg_Config_Trait_Entity {
    
    /**
     * Entity Building
     * 
     * @var int|null Stephino_Rpg_Config_Building ID
     */
    protected $_buildingId = null;
    
    /**
     * Entity Disbandable
     * 
     * @var boolean
     */
    protected $_disbandable = false;
    
    /**
     * Entity Disbandable Population reward
     * 
     * @var int
     */
    protected $_disbandablePopulation = 1;
    
    /**
     * Civilian entity
     * 
     * @var boolean
     */
    protected $_civilian = false;
    
    /**
     * Ability:Colonize
     * 
     * @var boolean
     */
    protected $_abilityColonize = false;
    
    /**
     * Entity Armour
     * 
     * @var int
     */
    protected $_armour = 1;

    /**
     * Entity Agility
     * 
     * @var int
     */
    protected $_agility = 1;

    /**
     * Entity Damage
     * 
     * @var int
     */
    protected $_damage = 1;

    /**
     * Entity Ammo
     * 
     * @var int
     */
    protected $_ammo = 1;
    
    /**
     * Transport weight
     * 
     * @var int
     */
    protected $_transportMass = 1;
    
    /**
     * Entity Maintenance
     * 
     * @var int|null
     */
    protected $_maintenance = null;

    /**
     * Entity Maintenance Polynomial
     * 
     * @var string|null
     */
    protected $_maintenancePolynomial = null;
    
    /**
     * Loot box size
     *
     * @var int
     */
    protected $_lootBox = 0;
    
    /**
     * Set the recruitment building
     * 
     * @return Stephino_Rpg_Config_Building|null Main Building
     */
    public function getBuilding() {
        return Stephino_Rpg_Config::get()->buildings()->getById($this->_buildingId);
    }

    /**
     * Set the "Building" parameter
     * 
     * @param int|null $id Stephino_Rpg_Config_Building ID
     */
    public function setBuilding($id) {
        $this->_buildingId = (null === $id ? null : intval($id));

        // Method chaining
        return $this;
    }
    
    /**
     * Is this entity disbandable?<br/>
     * Disbanding entities converts them into <b>{x}</b>
     * 
     * @placeholder core.metricPopulationName,Population
     * @return boolean Disbandable
     */
    public function getDisbandable() {
        return (null === $this->_disbandable ? false : $this->_disbandable);
    }

    /**
     * Set the "Disbandable" parameter
     * 
     * @param boolean $enabled Disbandable
     */
    public function setDisbandable($enabled) {
        $this->_disbandable = (boolean) $enabled;

        // Method chaining
        return $this;
    }
    
    /**
     * The <b>{x}</b> gained when disbanding this entity
     * 
     * @depends disbandable
     * @placeholder core.metricPopulationName,Population
     * @default 1
     * @ref 0
     * @return int Disbandable: {x} reward
     */
    public function getDisbandablePopulation() {
        return (null === $this->_disbandablePopulation ? 1 : $this->_disbandablePopulation);
    }
    
    /**
     * Set the "Disbandable Population" parameter
     * 
     * @param int $reward Population reward
     */
    public function setDisbandablePopulation($reward) {
        $this->_disbandablePopulation = intval($reward);
        
        if ($this->_disbandablePopulation < 0) {
            $this->__disbandablePopulation = 0;
        }
        
        // Method chaining
        return $this;
    }
    
    /**
     * This is <b>not</b> a military entity
     * 
     * @return boolean Civilian unit
     */
    public function getCivilian() {
        return (null === $this->_civilian ? false : $this->_civilian);
    }
    
    /**
     * Set the "Non Military" parameter
     * 
     * @param boolean $civilian Non Military Unit
     */
    public function setCivilian($civilian) {
        $this->_civilian = (boolean) $civilian;
        
        // Method chaining
        return $this;
    }
    
    /**
     * Can this entity colonize new cities?
     * 
     * @depends civilian
     * @return boolean Colonizer
     */
    public function getAbilityColonize() {
        return (null === $this->_abilityColonize ? false : $this->_abilityColonize);
    }
    
    /**
     * Set the "Ability:Colonize" parameter
     * 
     * @param boolean $enabled Ability:Colonize
     */
    public function setAbilityColonize($enabled) {
        $this->_abilityColonize = (boolean) $enabled;
        
        // Method chaining
        return $this;
    }
    
    /**
     * Entity Armour (Defense)
     * 
     * @depends !civilian
     * @default 1
     * @ref 1,100
     * @return int Armour
     */
    public function getArmour() {
        return null === $this->_armour ? 1 : $this->_armour;
    }

    /**
     * Set the "Armour" parameter
     * 
     * @param int $armour Armour
     */
    public function setArmour($armour) {
        $this->_armour = (null === $armour ? 1 : intval($armour));

        // Minimum and maximum
        if ($this->_armour < 1) {
            $this->_armour = 1;
        }
        if ($this->_armour > 100) {
            $this->_armour = 100;
        }
        
        // Method chaining
        return $this;
    }

    /**
     * Entity Agility (Defense)
     * 
     * @depends !civilian
     * @default 1
     * @ref 1,100
     * @return int Agility
     */
    public function getAgility() {
        return null === $this->_agility ? 1 : $this->_agility;
    }

    /**
     * Set the "Agility" parameter
     * 
     * @param int $agility Agility
     */
    public function setAgility($agility) {
        $this->_agility = (null === $agility ? 1 : intval($agility));

        // Minimum and maximum
        if ($this->_agility < 1) {
            $this->_agility = 1;
        }
        if ($this->_agility > 100) {
            $this->_agility = 100;
        }
        
        // Method chaining
        return $this;
    }

    /**
     * Entity Damage (Offense)
     * 
     * @depends !civilian
     * @default 1
     * @ref 1,100
     * @return int Damage
     */
    public function getDamage() {
        return null === $this->_damage ? 1 : $this->_damage;
    }

    /**
     * Set the "Damage" parameter
     * 
     * @param int $damage Damage
     */
    public function setDamage($damage) {
        $this->_damage = (null === $damage ? 1 : intval($damage));

        // Minimum and maximum
        if ($this->_damage < 1) {
            $this->_damage = 1;
        }
        if ($this->_damage > 100) {
            $this->_damage = 100;
        }
        
        // Method chaining
        return $this;
    }

    /**
     * Entity Ammunition (Offense)
     * 
     * @depends !civilian
     * @default 1
     * @ref 1,100
     * @return int Ammo
     */
    public function getAmmo() {
        return null === $this->_ammo ? 1 : $this->_ammo;
    }

    /**
     * Set the "Ammo" parameter
     * 
     * @param int $ammo Ammunition
     */
    public function setAmmo($ammo) {
        $this->_ammo = (null === $ammo ? 1 : intval($ammo));

        // Minimum and maximum
        if ($this->_ammo < 1) {
            $this->_ammo = 1;
        }
        if ($this->_ammo > 100) {
            $this->_ammo = 100;
        }
        
        // Method chaining
        return $this;
    }
    
    /**
     * The loot capacity for each individual resource
     * 
     * @depends !civilian
     * @ref 0
     * @return int Loot Box
     */
    public function getLootBox() {
        return null === $this->_lootBox ? 0 : $this->_lootBox;
    }
    
    /**
     * Set the "Loot Box" parameter
     * 
     * @param int $lootBox Loot Box
     */
    public function setLootBox($lootBox) {
        $this->_lootBox = (null === $lootBox ? 0 : intval($lootBox));

        // Minimum
        if ($this->_lootBox < 0) {
            $this->_lootBox = 0;
        }
        
        // Method chaining
        return $this;
    }
    
    /**
     * Cargo space this entity occupies when transported from city to city
     * 
     * @ref 1
     * @return int Transported mass
     */
    public function getTransportMass() {
        return null === $this->_transportMass ? 1 : $this->_transportMass;
    }

    /**
     * Set the "Transport Mass" parameter
     * 
     * @param int $transportMass Transport Mass
     */
    public function setTransportMass($transportMass) {
        $this->_transportMass = (null === $transportMass ? 1 : intval($transportMass));

        // Minimum
        if ($this->_transportMass < 1) {
            $this->_transportMass = 1;
        }
        
        // Method chaining
        return $this;
    }
    
}

/* EOF */