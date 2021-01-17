<?php
/**
 * Stephino_Rpg_Config_Trait_Discount
 * 
 * @title     Item discounts
 * @desc      Define item discounts
 * @copyright (c) 2020, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */

trait Stephino_Rpg_Config_Trait_Discount {
    
    /**
     * Enables Units Discount
     * 
     * @var boolean
     */
    protected $_enablesDiscountUnits = false;
    
    /**
     * Units - Discount
     * 
     * @var int[]|null Stephino_Rpg_Config_Unit IDs
     */
    protected $_discountUnits = null;
    
    /**
     * Units - Discount percentage
     * 
     * @var int|null
     */
    protected $_discountUnitsPercent = null;
    
    /**
     * Enables Ships Discount
     * 
     * @var boolean
     */
    protected $_enablesDiscountShips = false;
    
    /**
     * Ships - Discount
     * 
     * @var int[]|null Stephino_Rpg_Config_Ship IDs
     */
    protected $_discountShips = null;
    
    /**
     * Ships - Discount percentage
     * 
     * @var int|null
     */
    protected $_discountShipsPercent = null;
    
    /**
     * Enables Buildings Discount
     * 
     * @var boolean
     */
    protected $_enablesDiscountBuildings = false;
    
    /**
     * Buildings - Discount
     * 
     * @var int[]|null Stephino_Rpg_Config_Building IDs
     */
    protected $_discountBuildings = null;
    
    /**
     * Buildings - Discount percentage
     * 
     * @var int
     */
    protected $_discountBuildingsPercent = 10;
    
    /**
     * Enable the cost discount feature for units
     * 
     * @return boolean Enable: Units Discount
     */
    public function getEnablesDiscountUnits() {
        return (null === $this->_enablesDiscountUnits ? false : $this->_enablesDiscountUnits);
    }
    
    /**
     * Set the "Enabled Discount Units" parameter
     * 
     * @param boolean $enabled Enabled Units Discounts
     */
    public function setEnablesDiscountUnits($enabled) {
        $this->_enablesDiscountUnits = (boolean) $enabled;
        
        // Method chaining
        return $this;
    }

    /**
     * A cost discount applies to these units
     * 
     * @depends enablesDiscountUnits
     * @return Stephino_Rpg_Config_Unit[]|null Units Discount
     */
    public function getDiscountUnits() {
        // Not a valid array
        if (!is_array($this->_discountUnits)) {
            return null;
        }

        // Prepare the result
        $result = array();

        // Go through the IDs
        foreach ($this->_discountUnits as $unitId) {
            /* @var $unit Stephino_Rpg_Config_Unit */
            $unit = Stephino_Rpg_Config::get()->units()->getById($unitId);

            // Valid result
            if (null !== $unit) {
                $result[$unitId] = $unit;
            }
        }

        // Final validation
        return count($result) ? $result : null;
    }

    /**
     * Set the Discount Units
     * 
     * @param int[]|null $discountUnitsIds Stephino_Rpg_Config_Unit IDs
     */
    public function setDiscountUnits($discountUnitsIds) {
        if (!is_array($discountUnitsIds)) {
            $this->_discountUnits = null;
        } else {
            // Convert to integers
            $discountUnitsIds = array_filter(array_map('intval', $discountUnitsIds));
            
            // Store the data
            $this->_discountUnits = (!count($discountUnitsIds) ? null : $discountUnitsIds);
        }

        // Method chaining
        return $this;
    }
    
    /**
     * Applies a cost reduction to selected units
     * 
     * @depends enablesDiscountUnits
     * @ref 0,100
     * @return int|null Units Discount (%)
     */
    public function getDiscountUnitsPercent() {
        return $this->_discountUnitsPercent;
    }

    /**
     * Set the "Discount Units Percent" parameter
     * 
     * @param int|null $discountUnitsPercent Discount Units Percent
     */
    public function setDiscountUnitsPercent($discountUnitsPercent) {
        $this->_discountUnitsPercent = (null === $discountUnitsPercent ? null : intval($discountUnitsPercent));
        
        // Minimum and maximum
        if (null !== $this->_discountUnitsPercent) {
            if ($this->_discountUnitsPercent < 0) {
                $this->_discountUnitsPercent = 0;
            }
            if ($this->_discountUnitsPercent > 100) {
                $this->_discountUnitsPercent = 100;
            }
        }
        
        // Method chaining
        return $this;
    }
    
    /**
     * Enable the cost discount feature for ships
     * 
     * @return boolean Enable: Ships Discount
     */
    public function getEnablesDiscountShips() {
        return (null === $this->_enablesDiscountShips ? false : $this->_enablesDiscountShips);
    }
    
    /**
     * Set the "Enabled Discount Ships" parameter
     * 
     * @param boolean $enabled Enabled Ships Discounts
     */
    public function setEnablesDiscountShips($enabled) {
        $this->_enablesDiscountShips = (boolean) $enabled;
        
        // Method chaining
        return $this;
    }
    
    /**
     * A cost discount applies to these ships
     * 
     * @depends enablesDiscountShips
     * @return Stephino_Rpg_Config_Ship[]|null Ships Discount
     */
    public function getDiscountShips() {
        // Not a valid array
        if (!is_array($this->_discountShips)) {
            return null;
        }

        // Prepare the result
        $result = array();

        // Go through the IDs
        foreach ($this->_discountShips as $shipId) {
            /* @var $ship Stephino_Rpg_Config_Ship */
            $ship = Stephino_Rpg_Config::get()->ships()->getById($shipId);

            // Valid result
            if (null !== $ship) {
                $result[$shipId] = $ship;
            }
        }

        // Final validation
        return count($result) ? $result : null;
    }

    /**
     * Set the Discount Ships
     * 
     * @param int[]|null $discountShipsIds Stephino_Rpg_Config_Ship IDs
     */
    public function setDiscountShips($discountShipsIds) {
        if (!is_array($discountShipsIds)) {
            $this->_discountShips = null;
        } else {
            // Convert to integers
            $discountShipsIds = array_filter(array_map('intval', $discountShipsIds));
            
            // Store the data
            $this->_discountShips = (!count($discountShipsIds) ? null : $discountShipsIds);
        }

        // Method chaining
        return $this;
    }
    
    /**
     * Applies a cost reduction to selected ships
     * 
     * @depends enablesDiscountShips
     * @ref 0,100
     * @return int|null Ships Discount (%)
     */
    public function getDiscountShipsPercent() {
        return $this->_discountShipsPercent;
    }

    /**
     * Set the "Discount Ships Percent" parameter
     * 
     * @param int|null $discountShipsPercent Discount Ships Percent
     */
    public function setDiscountShipsPercent($discountShipsPercent) {
        $this->_discountShipsPercent = (null === $discountShipsPercent ? null : intval($discountShipsPercent));

        // Minimum and maximum
        if (null !== $this->_discountShipsPercent) {
            if ($this->_discountShipsPercent < 0) {
                $this->_discountShipsPercent = 0;
            }
            if ($this->_discountShipsPercent > 100) {
                $this->_discountShipsPercent = 100;
            }
        }
        
        // Method chaining
        return $this;
    }
    
    /**
     * Enable the cost discount feature for buildings
     * 
     * @return boolean Enable: Buildings Discount
     */
    public function getEnablesDiscountBuildings() {
        return (null === $this->_enablesDiscountBuildings ? false : $this->_enablesDiscountBuildings);
    }
    
    /**
     * Set the "Enabled Discount Buildings" parameter
     * 
     * @param boolean $enabled Enabled Buildings Discounts
     */
    public function setEnablesDiscountBuildings($enabled) {
        $this->_enablesDiscountBuildings = (boolean) $enabled;
        
        // Method chaining
        return $this;
    }
    
    /**
     * A cost discount applies to these buildings
     * 
     * @depends enablesDiscountBuildings
     * @return Stephino_Rpg_Config_Building[]|null Buildings Discount
     */
    public function getDiscountBuildings() {
        // Not a valid array
        if (!is_array($this->_discountBuildings)) {
            return null;
        }

        // Prepare the result
        $result = array();

        // Go through the IDs
        foreach ($this->_discountBuildings as $buildingId) {
            /* @var $building Stephino_Rpg_Config_Building */
            $building = Stephino_Rpg_Config::get()->buildings()->getById($buildingId);

            // Valid result
            if (null !== $building) {
                $result[$buildingId] = $building;
            }
        }

        // Final validation
        return count($result) ? $result : null;
    }

    /**
     * Set the Discount Buildings
     * 
     * @param int[]|null $discountBuildingsIds Stephino_Rpg_Config_Building IDs
     */
    public function setDiscountBuildings($discountBuildingsIds) {
        if (!is_array($discountBuildingsIds)) {
            $this->_discountBuildings = null;
        } else {
            // Convert to integers
            $discountBuildingsIds = array_filter(array_map('intval', $discountBuildingsIds));
            
            // Store the data
            $this->_discountBuildings = (!count($discountBuildingsIds) ? null : $discountBuildingsIds);
        }

        // Method chaining
        return $this;
    }
    
    /**
     * Applies a cost reduction to selected buildings
     * 
     * @depends enablesDiscountBuildings
     * @default 10
     * @ref 1,100
     * @return int|null Buildings Discount (%)
     */
    public function getDiscountBuildingsPercent() {
        return null === $this->_discountBuildingsPercent ? 10 : $this->_discountBuildingsPercent;
    }

    /**
     * Set the "Discount Buildings Percent" parameter
     * 
     * @param int $discountBuildingsPercent Discount Buildings Percent
     */
    public function setDiscountBuildingsPercent($discountBuildingsPercent) {
        $this->_discountBuildingsPercent = null === $discountBuildingsPercent ? 10 : intval($discountBuildingsPercent);

        // Minimum and maximum
        if (null !== $this->_discountBuildingsPercent) {
            if ($this->_discountBuildingsPercent < 1) {
                $this->_discountBuildingsPercent = 1;
            }
            if ($this->_discountBuildingsPercent > 100) {
                $this->_discountBuildingsPercent = 100;
            }
        }
        
        // Method chaining
        return $this;
    }
    
}

/* EOF */