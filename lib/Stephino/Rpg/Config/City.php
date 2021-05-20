<?php

/**
 * Stephino_Rpg_Config_City
 * 
 * @title      City
 * @desc       Holds the configuration for a single "City" item
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Config_City extends Stephino_Rpg_Config_Item_Single {

    // Metropolis Movement Costs
    use Stephino_Rpg_Config_Trait_Cost {
        getCostGold as _getCostGoldTrait;
        getCostResearch as _getCostResearchTrait;
        getCostGem as _getCostGemTrait;
        
        // Player-level resources only
        getCostAlpha as _getCostAlphaTrait;
        getCostBeta as _getCostBetaTrait;
        getCostGamma as _getCostGammaTrait;
        getCostResourceExtra1 as _getCostResourceExtra1Trait;
        getCostResourceExtra2 as _getCostResourceExtra2Trait;
        
        // Movements are instantaneous
        getCostTime as _getCostTimeTrait;
        getCostTimePolynomial as _getCostTimePolynomialTrait;
    }
    
    // Hide elements from control panel
    protected function getCostAlpha() {}
    protected function getCostBeta() {}
    protected function getCostGamma() {}
    protected function getCostResourceExtra1() {}
    protected function getCostResourceExtra2() {}
    protected function getCostTime() {}
    protected function getCostTimePolynomial() {}
    
    /**
     * Serialization Key - Must be defined by each item individually
     */
    const KEY = 'city';
    
    /**
     * Class name of corresponding Collection Item
     */
    const COLLECTION_CLASS = Stephino_Rpg_Config_Cities::class;

    /**
     * City Name
     * 
     * @var string|null
     */
    protected $_name = null;

    /**
     * City Description
     * 
     * @var string|null
     */
    protected $_description = null;

    /**
     * City Width
     * 
     * @var int|null
     */
    protected $_cityWidth = null;
    
    /**
     * City Height
     * 
     * @var int|null
     */
    protected $_cityHeight = null;
    
    /**
     * Building slots
     * 
     * @var string|null
     */
    protected $_cityBuildingSlots = null;
    
    /**
     * Animation slots
     * "0,0;-1,0"
     * 
     * @var slots|null
     */
    protected $_cityAnimationSlots = null;
    
    /**
     * City Animations JSON
     * 
     * @var animations|null
     */
    protected $_cityAnimations = null;
    
    /**
     * Island Animations JSON
     * 
     * @var animations|null
     */
    protected $_islandAnimations = null;
    
    /**
     * Under Construction Animations JSON
     * 
     * @var animations|null
     */
    protected $_underConstructionAnimations = null;
    
    /**
     * Satisfaction
     * 
     * @var int
     */
    protected $_satisfaction = 100;
    
    /**
     * Satisfaction polynomial
     *
     * @var string|null
     */
    protected $_satisfactionPolynomial = null;
    
    /**
     * Maximum Storage
     * 
     * @var int|null
     */
    protected $_maxStorage = 100;

    /**
     * Maximum Storage Polynomial
     * 
     * @var string|null
     */
    protected $_maxStoragePolynomial = null;

    /**
     * Maximum Population
     * 
     * @var int|null
     */
    protected $_maxPopulation = 100;

    /**
     * Maximum Population Polynomial
     * 
     * @var string|null
     */
    protected $_maxPopulationPolynomial = null;

    /**
     * City model name<br/>
     * <span class="info">
     *     Image files are stored in <b>{current theme}/img/story/cities/{id}/</b><br/> 
     *     Required files: <b>512.png</b>, <b>512-under-construction.png</b>, <b>512-vacant.png</b>, <b>768.png</b>, <b>full.jpg</b>
     * </span>
     * 
     * @return string Name
     */
    public function getName($htmlOutput = false) {
        return parent::getName($htmlOutput);
    }

    /**
     * Set the City Name
     * 
     * @param string|null $name City Name
     * @return Stephino_Rpg_Config_City
     */
    public function setName($name) {
        $this->_name = (null === $name ? null : Stephino_Rpg_Utils_Lingo::cleanup($name));

        return $this;
    }

    /**
     * City model description<br/>
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
     * @return Stephino_Rpg_Config_City
     */
    public function setDescription($description) {
        $this->_description = (null === $description ? null : Stephino_Rpg_Utils_Lingo::cleanup($description));

        return $this;
    }
    
    /**
     * The city width in number of cells<br/>
     * Each cell is 256 pixels wide
     * 
     * @default 19
     * @ref 5,49
     * @return odd_number Width
     */
    public function getCityWidth() {
        return (null === $this->_cityWidth ? 19 : $this->_cityWidth);
    }

    /**
     * Set the "City Width" parameter
     * 
     * @param int|null $cityWidth City Width
     * @return Stephino_Rpg_Config_City
     */
    public function setCityWidth($cityWidth) {
        $this->_cityWidth = (null === $cityWidth ? 19 : intval($cityWidth));
        
        // Must be an odd number
        if (0 == $this->_cityWidth % 2) {
            $this->_cityWidth++;
        }
        
        // Minimum  and maximum
        if ($this->_cityWidth < 5) {
            $this->_cityWidth = 5;
        }
        if ($this->_cityWidth > 49) {
            $this->_cityWidth = 49;
        }
        
        return $this;
    }
    
    /**
     * The city height in number of cells<br/>
     * Each cell is 256 pixels tall
     * 
     * @default 11
     * @ref 5,49
     * @return odd_number Height
     */
    public function getCityHeight() {
        return (null === $this->_cityHeight ? 11 : $this->_cityHeight);
    }

    /**
     * Set the "City Height" parameter
     * 
     * @param int|null $cityHeight City Height
     * @return Stephino_Rpg_Config_City
     */
    public function setCityHeight($cityHeight) {
        $this->_cityHeight = (null === $cityHeight ? 11 : intval($cityHeight));
        
        // Must be an odd number
        if (0 == $this->_cityHeight % 2) {
            $this->_cityHeight++;
        }
        
        // Minimum  and maximum
        if ($this->_cityHeight < 5) {
            $this->_cityHeight = 5;
        }
        if ($this->_cityHeight > 49) {
            $this->_cityHeight = 49;
        }
        
        return $this;
    }
    
    /**
     * Assign building positions<br/>
     * Each building occupies <b>4</b> cells, anchored by the top-left cell
     * 
     * @ref cityWidth,cityHeight,buildings
     * @return slots|null Building slots
     */
    public function getCityBuildingSlots() {
        return $this->_cityBuildingSlots;
    }
    
    /**
     * Set the "Building Slots" parameter
     * 
     * @param slots|null $slots Building slots
     * @return Stephino_Rpg_Config_City
     */
    public function setCityBuildingSlots($slots) {
        // Store the value
        $this->_cityBuildingSlots = $this->_sanitizeSlots($slots, true);

        return $this;
    }
    
    /**
     * Cells where city animations are shown
     * <br/>Each animation occupies <b>4</b> cells, anchored by the top-left cell
     * 
     * @ref cityWidth,cityHeight,cityAnimations!
     * @return slots|null City animations slots
     */
    public function getCityAnimationSlots() {
        return $this->_cityAnimationSlots;
    }

    /**
     * Set the "City Animation Slots" parameter
     * 
     * @param slots|null $slots Animation slots
     * @return Stephino_Rpg_Config_City
     */
    public function setCityAnimationSlots($slots) {
        // Store the value
        $this->_cityAnimationSlots = $this->_sanitizeSlots($slots, true);

        return $this;
    }
    
    /**
     * Define custom character and environment animations for this city<br/>
     * Visible from the <b>city</b> view
     * 
     * @return animations|null City animations
     */
    public function getCityAnimations() {
        return $this->_cityAnimations;
    }
    
    /**
     * Set the "City Animations" parameter
     * 
     * @param animations|null $animations Animations JSON
     * @return Stephino_Rpg_Config_City
     */
    public function setCityAnimations($animations) {
        // Set the animations
        $this->_cityAnimations = $this->_sanitizeAnimations($animations);
        
        return $this;
    }
    
    /**
     * Define custom environment animations for this city<br/>
     * Visible from the <b>island</b> view
     * 
     * @levels true
     * @ref 512
     * @return animations|null Island animations
     */
    public function getIslandAnimations() {
        return $this->_islandAnimations;
    }
    
    /**
     * Set the "Island Animations" parameter
     * 
     * @param animations|null $animations Animations JSON
     * @return Stephino_Rpg_Config_City
     */
    public function setIslandAnimations($animations) {
        // Set the animations
        $this->_islandAnimations = $this->_sanitizeLevels($animations, '_sanitizeAnimations');
        
        return $this;
    }
    
    /**
     * Define custom under construction animation for buildings<br/>
     * Visible from the <b>city</b> view
     * 
     * @ref 512-under-construction
     * @return animations|null Under construction animations
     */
    public function getUnderConstructionAnimations() {
        return $this->_underConstructionAnimations;
    }
    
    /**
     * Set the "Under Construction Animations" parameter
     * 
     * @param animations|null $animations Animations JSON
     * @return Stephino_Rpg_Config_City
     */
    public function setUnderConstructionAnimations($animations) {
        // Set the animations
        $this->_underConstructionAnimations = $this->_sanitizeAnimations($animations);
        
        return $this;
    }
    
    /**
     * Baseline city <b>{x}</b>
     * 
     * @default 100
     * @ref 1
     * @placeholder core.metricSatisfactionName,Satisfaction
     * @return int {x}
     */
    public function getSatisfaction() {
        return null == $this->_satisfaction ? 100 : $this->_satisfaction;
    }
    
    /**
     * Set the "Satisfaction" parameter
     * 
     * @param int $satisfaction Satisfaction
     * @return Stephino_Rpg_Config_City
     */
    public function setSatisfaction($satisfaction) {
        $this->_satisfaction = intval($satisfaction);
        
        // Minimum
        if ($this->_satisfaction < 1) {
            $this->_satisfaction = 1;
        }
        
        return $this;
    }
    
    /**
     * Baseline city <b>{x}</b> polynomial factor
     * 
     * @placeholder core.metricSatisfactionName,Satisfaction
     * @return poly|null {x} polynomial
     */
    public function getSatisfactionPolynomial() {
        return $this->_satisfactionPolynomial;
    }
    
    /**
     * Set the "Satisfaction Polynomial" parameter
     * 
     * @param string|null Satisfaction Polynomial
     * @return Stephino_Rpg_Config_City
     */
    public function setSatisfactionPolynomial($satisfactionPolynomial) {
        $this->_satisfactionPolynomial = $this->_sanitizePoly($satisfactionPolynomial);
        
        return $this;
    }

    /**
     * Maximum <b>{x}</b> capacity
     * 
     * @placeholder core.metricStorageName,Storage
     * @default 100
     * @ref 1
     * @return int|null Max. {x}
     */
    public function getMaxStorage() {
        return null === $this->_maxStorage ? 100 : $this->_maxStorage;
    }

    /**
     * Set the "Max Storage" parameter
     * 
     * @param int $maxStorage Storage
     * @return Stephino_Rpg_Config_City
     */
    public function setMaxStorage($maxStorage) {
        $this->_maxStorage = intval($maxStorage);

        // Minimum
        if ($this->_maxStorage < 1) {
            $this->_maxStorage = 1;
        }
        
        return $this;
    }

    /**
     * Maximum <b>{x}</b> polynomial factor
     * 
     * @placeholder core.metricStorageName,Storage
     * @return poly|null Max. {x} polynomial
     */
    public function getMaxStoragePolynomial() {
        return $this->_maxStoragePolynomial;
    }

    /**
     * Set the "Max Storage Polynomial" parameter
     * 
     * @param string|null $maxStoragePolynomial Max. Storage Polynomial
     * @return Stephino_Rpg_Config_City
     */
    public function setMaxStoragePolynomial($maxStoragePolynomial) {
        $this->_maxStoragePolynomial = $this->_sanitizePoly($maxStoragePolynomial);

        return $this;
    }

    /**
     * Maximum <b>{x}</b>
     * 
     * @placeholder core.metricPopulationName,Population
     * @default 100
     * @ref 1
     * @return int|null Max. {x}
     */
    public function getMaxPopulation() {
        return null === $this->_maxPopulation ? 100 : $this->_maxPopulation;
    }

    /**
     * Set the "Max Population" parameter
     * 
     * @param int|null $maxPopulation Max. population
     * @return Stephino_Rpg_Config_City
     */
    public function setMaxPopulation($maxPopulation) {
        $this->_maxPopulation = intval($maxPopulation);

        // Minimum
        if ($this->_maxPopulation < 1) {
            $this->_maxPopulation = 1;
        }
        
        return $this;
    }

    /**
     * Maximum <b>{x}</b> polynomial factor
     * 
     * @placeholder core.metricPopulationName,Population
     * @return poly|null Max. {x} polynomial
     */
    public function getMaxPopulationPolynomial() {
        return $this->_maxPopulationPolynomial;
    }

    /**
     * Set the "Max Population Polynomial" parameter
     * 
     * @param string|null $maxPopulationPolynomial Max. Population Polynomial
     * @return Stephino_Rpg_Config_City
     */
    public function setMaxPopulationPolynomial($maxPopulationPolynomial) {
        $this->_maxPopulationPolynomial = $this->_sanitizePoly($maxPopulationPolynomial);

        return $this;
    }
    
    /**
     * The cost of moving the metropolis to a level 1 city in {x}
     * 
     * @placeholder core.resourceGoldName,Gold
     * @ref 0
     * @return int|null Metropolis change cost in {x}
     */
    public function getCostGold() {
        return $this->_getCostGoldTrait();
    }
    
    /**
     * The cost of moving the metropolis to a level 1 city in {x}
     * 
     * @placeholder core.resourceResearchName,Research points
     * @ref 0
     * @return int|null Metropolis change cost in {x}
     */
    public function getCostResearch() {
        return $this->_getCostResearchTrait();
    }
    
    /**
     * The cost of moving the metropolis to a level 1 city in {x}
     * 
     * @placeholder core.resourceGemName,Gems
     * @ref 0
     * @return int|null Metropolis change cost in {x}
     */
    public function getCostGem() {
        return $this->_getCostGemTrait();
    }

}

/*EOF*/