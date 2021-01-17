<?php

/**
 * Stephino_Rpg_Config_IslandStatue
 * 
 * @title      Island Statue
 * @desc       Holds the configuration for a single "Island Statue" item
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Config_IslandStatue extends Stephino_Rpg_Config_Item_Single {

    // Island Statue Modifiers
    use Stephino_Rpg_Config_Trait_Modifier {
        getModifier as _getModifierTrait;
        getModifierPolynomial as _getModifierPolynomialTrait;
    }
    
    // Island Statue Costs
    use Stephino_Rpg_Config_Trait_Cost {
        // Player-level resources only
        getCostAlpha as _getCostAlphaTrait;
        getCostBeta as _getCostBetaTrait;
        getCostGamma as _getCostGammaTrait;
        getCostResourceExtra1 as _getCostResourceExtra1Trait;
        getCostResourceExtra2 as _getCostResourceExtra2Trait;
        
        // Upgrades are instantaneous
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
    const KEY = 'islandStatue';

    /**
     * Island Statue Name
     * 
     * @var string|null
     */
    protected $_name = null;

    /**
     * Island Statue Description
     * 
     * @var string|null
     */
    protected $_description = null;
    
    /**
     * Island Animations JSON
     * 
     * @var animations|null
     */
    protected $_islandAnimations = null;

    /**
     * Island statue name<br/>
     * <span class="info">
     *     Image files are stored in <b>{current theme}/img/story/islandStatues/{id}/</b><br/> 
     *     Required files: <b>512.png</b>, <b>512-above.png</b>, <b>768.png</b>
     * </span>
     * 
     * @return string Name
     */
    public function getName($htmlOutput = false) {
        return parent::getName($htmlOutput);
    }

    /**
     * Set the Island Statue Name
     * 
     * @param string|null $name Island Statue Name
     * @return Stephino_Rpg_Config_IslandStatue
     */
    public function setName($name) {
        $this->_name = (null === $name ? null : Stephino_Rpg_Utils_Lingo::cleanup($name));

        // Method chaining
        return $this;
    }

    /**
     * Island statue description<br/>
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
     * @return Stephino_Rpg_Config_IslandStatue
     */
    public function setDescription($description) {
        $this->_description = (null === $description ? null : Stephino_Rpg_Utils_Lingo::cleanup($description));

        // Method chaining
        return $this;
    }
    
    /**
     * Define custom environment animations for this statue<br/>
     * Visible from the <b>island</b> view
     * 
     * @ref 512
     * @return animations|null Island animations
     */
    public function getIslandAnimations() {
        return $this->_islandAnimations;
    }
    
    /**
     * Set the "Island Animations" parameter
     * 
     * @param animations|null $animations Island Animations JSON
     * @return Stephino_Rpg_Config_IslandStatue
     */
    public function setIslandAnimations($animations) {
        // Set the animations
        $this->_islandAnimations = $this->_sanitizeAnimations($animations);
        
        // Method chaining
        return $this;
    }

    /**
     * Select a set of features this <b>island statue</b> model enables
     * 
     * @return Stephino_Rpg_Config_Modifier|null Modifier
     */
    public function getModifier() {
        return $this->_getModifierTrait();
    }

    /**
     * Effects cast by the modifier are amplified with the <b>island statue</b> level
     * 
     * @return poly|null Modifier polynomial
     */
    public function getModifierPolynomial() {
        return $this->_getModifierPolynomialTrait();
    }

}

/*EOF*/