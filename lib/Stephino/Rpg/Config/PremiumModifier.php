<?php

/**
 * Stephino_Rpg_Config_PremiumModifier
 * 
 * @title      Premium Modifier
 * @desc       Holds the configuration for a single "Premium Modifier" item
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Config_PremiumModifier extends Stephino_Rpg_Config_Item_Single {

    // Premium Modifiers
    use Stephino_Rpg_Config_Trait_Modifier {
        getModifier as _getModifierTrait;
        getModifierPolynomial as _getModifierPolynomialTrait;
    }
    
    // Premium Modifier Discounts
    use Stephino_Rpg_Config_Trait_Discount;
    
    // Premium Modifier Costs
    use Stephino_Rpg_Config_Trait_Cost {
        // Player-level resources only
        getCostAlpha as _getCostAlphaTrait;
        getCostBeta as _getCostBetaTrait;
        getCostGamma as _getCostGammaTrait;
        getCostResourceExtra1 as _getCostResourceExtra1Trait;
        getCostResourceExtra2 as _getCostResourceExtra2Trait;
        getCostTime as _getCostTime;
        getCostTimePolynomial as _getCostTimePolynomial;
    }
    
    // Hide elements from control panel
    protected function getCostAlpha() {}
    protected function getCostBeta() {}
    protected function getCostGamma() {}
    protected function getCostResourceExtra1() {}
    protected function getCostResourceExtra2() {}
    protected function getCostTime() {}
    protected function getCostTimePolynomial() {}
    
    // Premium Modifier - Extra Resources Abundance
    use Stephino_Rpg_Config_Trait_XResourceAbundance;
    
    /**
     * Serialization Key - Must be defined by each item individually
     */
    const KEY = 'premiumModifier';

    /**
     * Premium Modifier Name
     * 
     * @var string|null
     */
    protected $_name = null;

    /**
     * Premium Modifier Description<br/>
     * <span class="info">MarkDown enabled</span>
     * 
     * @var string|null
     */
    protected $_description = null;

    /**
     * Duration in hours
     * 
     * @var int
     */
    protected $_duration = 1;
    
    /**
     * Maximum Queue Buildings
     * 
     * @var int|null
     */
    protected $_maxQueueBuildings = null;
    
    /**
     * Maximum Queue Entities
     * 
     * @var int|null
     */
    protected $_maxQueueEntities = null;
    
    /**
     * Maximum Queue Research Fields
     * 
     * @var int|null
     */
    protected $_maxQueueResearchFields = null;
    
    /**
     * Time Contraction Buildings
     * 
     * @var int|null
     */
    protected $_timeContractionBuildings = null;
    
    /**
     * Time Contraction Entities
     * 
     * @var int|null
     */
    protected $_timeContractionEntities = null;
    
    /**
     * Time Contraction Research Fields
     * 
     * @var int|null
     */
    protected $_timeContractionResearchFields = null;

    /**
     * Premium modifier name
     * 
     * @return string Name
     */
    public function getName($htmlOutput = false) {
        return parent::getName($htmlOutput);
    }

    /**
     * Set the Premium Modifier Name
     * 
     * @param string|null $name Premium Modifier Name
     * @return Stephino_Rpg_Config_PremiumModifier
     */
    public function setName($name) {
        $this->_name = (null === $name ? null : Stephino_Rpg_Utils_Lingo::cleanup($name));

        // Method chaining
        return $this;
    }

    /**
     * Premium modifier description
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
     * @return Stephino_Rpg_Config_PremiumModifier
     */
    public function setDescription($description) {
        $this->_description = (null === $description ? null : Stephino_Rpg_Utils_Lingo::cleanup($description));

        // Method chaining
        return $this;
    }

    /**
     * The effects cast my this modifier expire after this many hours
     * 
     * @default 1
     * @ref 1
     * @return int|null Duration
     */
    public function getDuration() {
        return null === $this->_duration ? 1 : $this->_duration;
    }

    /**
     * Set the "Duration" parameter
     * 
     * @param int|null $duration Duration
     * @return Stephino_Rpg_Config_PremiumModifier
     */
    public function setDuration($duration) {
        $this->_duration = intval($duration);

        // Minimum
        if ($this->_duration < 1) {
            $this->_duration = 1;
        }
        
        // Method chaining
        return $this;
    }

    /**
     * Select a set of features this <b>premium modifier</b> model enables
     * 
     * @return Stephino_Rpg_Config_Modifier|null Modifier
     */
    public function getModifier() {
        return $this->_getModifierTrait();
    }

    /**
     * Effects cast by the modifier are amplified with the <b>premium modifier</b> level
     * 
     * @return poly|null Modifier polynomial
     */
    public function getModifierPolynomial() {
        return $this->_getModifierPolynomialTrait();
    }
    
    /**
     * Maximum number of simultaneous <b>building</b> upgrades in a city
     * 
     * @ref 0
     * @return int|null Max. Queue: Buildings
     */
    public function getMaxQueueBuildings() {
        return $this->_maxQueueBuildings;
    }

    /**
     * Set the "Max Queue Buildings" parameter
     * 
     * @param int|null $maxQueueBuildings Max Queue Buildings
     * @return Stephino_Rpg_Config_PremiumModifier
     */
    public function setMaxQueueBuildings($maxQueueBuildings) {
        $this->_maxQueueBuildings = intval($maxQueueBuildings);
        
        // Minimum
        if ($this->_maxQueueBuildings <= 0) {
            $this->_maxQueueBuildings = null;
        }
        
        // Method chaining
        return $this;
    }
    
    /**
     * Maximum number of simultaneous <b>entity</b> recruitment jobs in a city
     * 
     * @ref 0
     * @return int|null Max. Queue: Units and Ships
     */
    public function getMaxQueueEntities() {
        return $this->_maxQueueEntities;
    }

    /**
     * Set the "Max Queue Units" parameter
     * 
     * @param int|null $maxQueueEntities Max Queue Entities
     * @return Stephino_Rpg_Config_PremiumModifier
     */
    public function setMaxQueueEntities($maxQueueEntities) {
        $this->_maxQueueEntities = intval($maxQueueEntities);

        // Minimum
        if ($this->_maxQueueEntities <= 0) {
            $this->_maxQueueEntities = null;
        }
        
        // Method chaining
        return $this;
    }

    /**
     * Maximum number of simultaneous <b>research field</b> activities in all cities
     * 
     * @ref 0
     * @return int|null Max. Queue: Research fields
     */
    public function getMaxQueueResearchFields() {
        return $this->_maxQueueResearchFields;
    }

    /**
     * Set the "Max Queue Research Fields" parameter
     * 
     * @param int|null $maxQueueResearchFields Max Queue Research Fields
     * @return Stephino_Rpg_Config_PremiumModifier
     */
    public function setMaxQueueResearchFields($maxQueueResearchFields) {
        $this->_maxQueueResearchFields = intval($maxQueueResearchFields);

        // Minimum
        if ($this->_maxQueueResearchFields <= 0) {
            $this->_maxQueueResearchFields = null;
        }
        
        // Method chaining
        return $this;
    }

    /**
     * <b>Building</b> queues will be done <b>N times</b> faster<br/>
     * A value of <b>1</b> means the time contraction is disabled
     * 
     * @ref 1
     * @return int|null Time Contraction: Buildings
     */
    public function getTimeContractionBuildings() {
        return $this->_timeContractionBuildings;
    }

    /**
     * Set the "Time Contraction Buildings" parameter
     * 
     * @param int|null $timeContractionBuildings Time Contraction Buildings
     * @return Stephino_Rpg_Config_PremiumModifier
     */
    public function setTimeContractionBuildings($timeContractionBuildings) {
        $this->_timeContractionBuildings = intval($timeContractionBuildings);
        
        // Minimum
        if ($this->_timeContractionBuildings <= 1) {
            $this->_timeContractionBuildings = null;
        }
        
        // Method chaining
        return $this;
    }
    
    /**
     * <b>Entity</b> queues will be done <b>N times</b> faster<br/>
     * A value of <b>1</b> means the time contraction is disabled
     * 
     * @ref 1
     * @return int|null Time Contraction: Units and Ships
     */
    public function getTimeContractionEntities() {
        return $this->_timeContractionEntities;
    }

    /**
     * Set the "Time Contraction Units" parameter
     * 
     * @param int|null $timeContractionEntities Time Contraction Entities
     * @return Stephino_Rpg_Config_PremiumModifier
     */
    public function setTimeContractionEntities($timeContractionEntities) {
        $this->_timeContractionEntities = intval($timeContractionEntities);

        // Minimum
        if ($this->_timeContractionEntities <= 1) {
            $this->_timeContractionEntities = null;
        }
        
        // Method chaining
        return $this;
    }

    /**
     * <b>Research field</b> queues will be done <b>N times</b> faster<br/>
     * A value of <b>1</b> means the time contraction is disabled
     * 
     * @ref 1
     * @return int|null Time Contraction: Research fields
     */
    public function getTimeContractionResearchFields() {
        return $this->_timeContractionResearchFields;
    }

    /**
     * Set the "Time Contraction Research Fields" parameter
     * 
     * @param int|null $timeContractionResearchFields Time Contraction Research Fields
     * @return Stephino_Rpg_Config_PremiumModifier
     */
    public function setTimeContractionResearchFields($timeContractionResearchFields) {
        $this->_timeContractionResearchFields = intval($timeContractionResearchFields);

        // Minimum
        if ($this->_timeContractionResearchFields <= 1) {
            $this->_timeContractionResearchFields = null;
        }
        
        // Method chaining
        return $this;
    }
}

/*EOF*/