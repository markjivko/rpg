<?php

/**
 * Stephino_Rpg_Config_Island
 * 
 * @title      Island
 * @desc       Holds the configuration for a single "Island" item
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Config_Island extends Stephino_Rpg_Config_Item_Single {

    // Premium Modifier Costs
    use Stephino_Rpg_Config_Trait_Cost {
        // Player-level resources only
        getCostTime as _getCostTimeTrait;
        getCostTimePolynomial as _getCostTimePolynomialTrait;
        getCostGold as _getCostGoldTrait;
        getCostResearch as _getCostResearchTrait;
        getCostGem as _getCostGemTrait;
        getCostPolynomial as _getCostPolynomialTrait;
        getCostAlpha as _getCostAlphaTrait;
        getCostBeta as _getCostBetaTrait;
        getCostGamma as _getCostGammaTrait;
        getCostResourceExtra1 as _getCostResourceExtra1Trait;
        getCostResourceExtra2 as _getCostResourceExtra2Trait;
    }
    
    // Hide elements from control panel
    protected function getCostAlpha() {}
    protected function getCostBeta() {}
    protected function getCostGamma() {}
    protected function getCostResourceExtra1() {}
    protected function getCostResourceExtra2() {}
    
    /**
     * Serialization Key - Must be defined by each item individually
     */
    const KEY = 'island';

    /**
     * Island Name
     * 
     * @var string|null
     */
    protected $_name = null;

    /**
     * Island Description
     * 
     * @var string|null
     */
    protected $_description = null;
    
    /**
     * Island Width
     * 
     * @var int|null
     */
    protected $_islandWidth = null;
    
    /**
     * Island Height
     * 
     * @var int|null
     */
    protected $_islandHeight = null;

    /**
     * Statue slot: "x,y"
     * 
     * @var string|null
     */
    protected $_statueSlot = null;

    /**
     * City slots: "0,0;-1,0"
     * 
     * @var string|null
     */
    protected $_citySlots = null;

    /**
     * Animation slots: "0,0;-1,0"
     * 
     * @var slots|null
     */
    protected $_animationSlots = null;
    
    /**
     * Island Animations JSON
     * 
     * @var animations|null
     */
    protected $_islandAnimations = null;
    
    /**
     * Vacant Lot Animations JSON
     * 
     * @var animations|null
     */
    protected $_vacantLotAnimations = null;
    
    /**
     * World Animations JSON
     * 
     * @var animations|null
     */
    protected $_worldAnimations = null;

    /**
     * Resource Extra 1 Abundance
     * 
     * @var int
     */
    protected $_resourceExtra1Abundance = 10;

    /**
     * Resource Extra 2 Abundance
     * 
     * @var int
     */
    protected $_resourceExtra2Abundance = 10;

    /**
     * Island model name<br/>
     * <span class="info">
     *     Image files are stored in <b>{current theme}/img/story/islands/{id}/</b><br/> 
     *     Required files: <b>512.png</b>, <b>full.jpg</b>
     * </span>
     * 
     * @return string Name
     */
    public function getName($htmlOutput = false) {
        return parent::getName($htmlOutput);
    }

    /**
     * Set the Island Name
     * 
     * @param string|null $name Island Name
     * @return Stephino_Rpg_Config_Island
     */
    public function setName($name) {
        $this->_name = (null === $name ? null : Stephino_Rpg_Utils_Lingo::cleanup($name));

        // Method chaining
        return $this;
    }

    /**
     * Island model description<br/>
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
     * @return Stephino_Rpg_Config_Island
     */
    public function setDescription($description) {
        $this->_description = (null === $description ? null : Stephino_Rpg_Utils_Lingo::cleanup($description));

        // Method chaining
        return $this;
    }
    
    /**
     * The island width in number of cells
     * 
     * @default 9
     * @ref 1,25
     * @return odd_number Width
     */
    public function getIslandWidth() {
        return (null === $this->_islandWidth ? 9 : $this->_islandWidth);
    }

    /**
     * Set the "Island Width" parameter
     * 
     * @param int|null $islandWidth Island Width
     * @return Stephino_Rpg_Config_Island
     */
    public function setIslandWidth($islandWidth) {
        $this->_islandWidth = (null === $islandWidth ? 9 : intval($islandWidth));
        
        // Must be an odd number
        if (0 == $this->_islandWidth % 2) {
            $this->_islandWidth++;
        }
        
        // Minimum and maximum
        if ($this->_islandWidth < 1) {
            $this->_islandWidth = 1;
        }
        if ($this->_islandWidth > 25) {
            $this->_islandWidth = 25;
        }
        
        // Method chaining
        return $this;
    }
    
    /**
     * The island height in number of cells
     * 
     * @default 5
     * @ref 1,25
     * @return odd_number Height
     */
    public function getIslandHeight() {
        return (null === $this->_islandHeight ? 5 : $this->_islandHeight);
    }

    /**
     * Set the "Island Height" parameter
     * 
     * @param int|null $islandHeight Island Height
     * @return Stephino_Rpg_Config_Island
     */
    public function setIslandHeight($islandHeight) {
        $this->_islandHeight = (null === $islandHeight ? 5 : intval($islandHeight));
        
        // Must be an odd number
        if (0 == $this->_islandHeight % 2) {
            $this->_islandHeight++;
        }
        
        // Minimum and maximum
        if ($this->_islandHeight < 1) {
            $this->_islandHeight = 1;
        }
        if ($this->_islandHeight > 25) {
            $this->_islandHeight = 25;
        }
        
        // Method chaining
        return $this;
    }

    /**
     * The cell where the island statue is located
     * 
     * @slot_exc citySlots
     * @ref islandWidth,islandHeight
     * @return slot|null Statue slot
     */
    public function getStatueSlot() {
        return $this->_statueSlot;
    }

    /**
     * Set the "Slot" parameter
     * 
     * @param slot|null $statueSlot Statue slot
     * @return Stephino_Rpg_Config_Island
     */
    public function setStatueSlot($statueSlot) {
        // Store the value
        $this->_statueSlot = $this->_sanitizeSlot($statueSlot);

        // Method chaining
        return $this;
    }
    
    /**
     * Cells where cities can be built
     * 
     * @slot_exc statueSlot
     * @ref islandWidth,islandHeight
     * @return slots|null City slots
     */
    public function getCitySlots() {
        return $this->_citySlots;
    }

    /**
     * Set the "City Slots" parameter
     * 
     * @param slots|null $citySlots City slots
     * @return Stephino_Rpg_Config_Island
     */
    public function setCitySlots($citySlots) {
        // Store the value
        $this->_citySlots = $this->_sanitizeSlots($citySlots);

        // Method chaining
        return $this;
    }
    
    /**
     * Cells where island animations are shown
     * 
     * @ref islandWidth,islandHeight,islandAnimations!
     * @return slots|null Island animations slots
     */
    public function getAnimationSlots() {
        return $this->_animationSlots;
    }

    /**
     * Set the "Animation Slots" parameter
     * 
     * @param slots|null $animationSlots Animation slots
     * @return Stephino_Rpg_Config_Island
     */
    public function setAnimationSlots($animationSlots) {
        // Store the value
        $this->_animationSlots = $this->_sanitizeSlots($animationSlots, true);

        // Method chaining
        return $this;
    }
    
    /**
     * Define custom environment animations for this island<br/>
     * Visible from the <b>island</b> view
     * 
     * @return animations|null Island animations
     */
    public function getIslandAnimations() {
        return $this->_islandAnimations;
    }
    
    /**
     * Set the "Island Animations" parameter
     * 
     * @param animations|null $animations Animations JSON
     * @return Stephino_Rpg_Config_Island
     */
    public function setIslandAnimations($animations) {
        // Set the animations
        $this->_islandAnimations = $this->_sanitizeAnimations($animations);
        
        // Method chaining
        return $this;
    }
    
    /**
     * Vacant lot animations<br/>
     * Visible from the <b>island</b> view
     * 
     * @ref 512-vacant
     * @return animations|null Vacant lot animations
     */
    public function getVacantLotAnimations() {
        return $this->_vacantLotAnimations;
    }
    
    /**
     * Set the "Vacant Lot Animations" parameter
     * 
     * @param animations|null $animations Animations JSON
     * @return Stephino_Rpg_Config_Island
     */
    public function setVacantLotAnimations($animations) {
        // Set the animations
        $this->_vacantLotAnimations = $this->_sanitizeAnimations($animations);
        
        // Method chaining
        return $this;
    }
    
    /**
     * Define custom environment animations for this island<br/>
     * Visible from the <b>world</b> view
     * 
     * @ref 512
     * @return animations|null World animations
     */
    public function getWorldAnimations() {
        return $this->_worldAnimations;
    }
    
    /**
     * Set the "World Animations" parameter
     * 
     * @param animations|null $animations Animations JSON
     * @return Stephino_Rpg_Config_Island
     */
    public function setWorldAnimations($animations) {
        // Set the animations
        $this->_worldAnimations = $this->_sanitizeAnimations($animations);
        
        // Method chaining
        return $this;
    }
    
    /**
     * Abundance of <b>{x}</b> on this island<br/>
     * This affects all production of <b>{x}</b> on this island
     * 
     * @default 10
     * @ref 5,100
     * @placeholder core.resourceExtra1Name,Extra resource 1
     * @return int {x} abundance (%)
     */
    public function getResourceExtra1Abundance() {
        return (null === $this->_resourceExtra1Abundance ? 10 : $this->_resourceExtra1Abundance);
    }

    /**
     * Set the "Resource Extra 1 Abundance" parameter
     * 
     * @param int $resourceExtra1Abundance Resource Extra 1 Abundance
     * @return Stephino_Rpg_Config_Island
     */
    public function setResourceExtra1Abundance($resourceExtra1Abundance) {
        $this->_resourceExtra1Abundance = intval($resourceExtra1Abundance);

        // Limits
        if ($this->_resourceExtra1Abundance < 5) {
            $this->_resourceExtra1Abundance = 5;
        }
        if ($this->_resourceExtra1Abundance > 100) {
            $this->_resourceExtra1Abundance = 100;
        }
        
        // Method chaining
        return $this;
    }

    /**
     * Abundance of <b>{x}</b> on this island<br/>
     * This affects all production of <b>{x}</b> on this island
     * 
     * @default 10
     * @ref 5,100
     * @placeholder core.resourceExtra2Name,Extra resource 2
     * @return int {x} abundance (%)
     */
    public function getResourceExtra2Abundance() {
        return (null === $this->_resourceExtra2Abundance ? 10 : $this->_resourceExtra2Abundance);
    }

    /**
     * Set the "Resource Extra 2 Abundance" parameter
     * 
     * @param boolean $resourceExtra2Abundance Resource Extra 2 Abundance
     * @return Stephino_Rpg_Config_Island
     */
    public function setResourceExtra2Abundance($resourceExtra2Abundance) {
        $this->_resourceExtra2Abundance = intval($resourceExtra2Abundance);

        // Limits
        if ($this->_resourceExtra2Abundance < 5) {
            $this->_resourceExtra2Abundance = 5;
        }
        if ($this->_resourceExtra2Abundance > 100) {
            $this->_resourceExtra2Abundance = 100;
        }
        
        // Method chaining
        return $this;
    }
    
    /**
     * Colony founding time in seconds
     * 
     * @default 1
     * @ref 1
     * @return int Colony time
     */
    public function getCostTime() {
        return $this->_getCostTimeTrait();
    }
    
    /**
     * Colony time polynomial factor<br/><br/>
     * <span class="info">Varies with the total number of cities in your empire</span>
     * 
     * @return poly|null Colony time polynomial
     */
    public function getCostTimePolynomial() {
        return $this->_getCostTimePolynomialTrait();
    }
    
    /**
     * Colony founding cost in {x}
     * 
     * @placeholder core.resourceGoldName,Gold
     * @ref 0
     * @return int|null Colony cost in {x}
     */
    public function getCostGold() {
        return $this->_getCostGoldTrait();
    }
    
    /**
     * Colony founding cost in {x}
     * 
     * @placeholder core.resourceResearchName,Research points
     * @ref 0
     * @return int|null Colony cost in {x}
     */
    public function getCostResearch() {
        return $this->_getCostResearchTrait();
    }
    
    /**
     * Colony founding cost in {x}
     * 
     * @placeholder core.resourceGemName,Gems
     * @ref 0
     * @return int|null Colony cost in {x}
     */
    public function getCostGem() {
        return $this->_getCostGemTrait();
    }
    
    /**
     * Colony cost polynomial factor<br/><br/>
     * <span class="info">Varies with the total number of cities in your empire</span>
     * 
     * @return poly|null Colony cost polynomial
     */
    public function getCostPolynomial() {
        return $this->_getCostPolynomialTrait();
    }

}

/*EOF*/