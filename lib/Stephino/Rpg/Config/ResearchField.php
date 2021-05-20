<?php

/**
 * Stephino_Rpg_Config_ResearchField
 * 
 * @title      Research Field
 * @desc       Holds the configuration for a single "Research Field" item
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Config_ResearchField extends Stephino_Rpg_Config_Item_Single {

    // Research Field Modifiers
    use Stephino_Rpg_Config_Trait_Modifier {
        getModifier as _getModifierTrait;
        getModifierPolynomial as _getModifierPolynomialTrait;
    }
    
    // Research Field Discounts
    use Stephino_Rpg_Config_Trait_Discount;
    
    // Research Field Costs
    use Stephino_Rpg_Config_Trait_Cost {
        // Player-level resources only
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
    
    // Research Field Requirements
    use Stephino_Rpg_Config_Trait_Requirement;
    
    // Research Field - Extra Resources Abundance
    use Stephino_Rpg_Config_Trait_XResourceAbundance;
    
    /**
     * Serialization Key - Must be defined by each item individually
     */
    const KEY = 'researchField';
    
    /**
     * Class name of corresponding Collection Item
     */
    const COLLECTION_CLASS = Stephino_Rpg_Config_ResearchFields::class;

    /**
     * Research Field Name
     * 
     * @var string|null
     */
    protected $_name = null;

    /**
     * Research Field Description
     * 
     * @var string|null
     */
    protected $_description = null;
    
    /**
     * Research Field Story Fragment
     * 
     * @var string|null
     */
    protected $_story = null;
    
    /**
     * Research Area ID
     * 
     * @var int|null Stephino_Rpg_Config_ResearchArea ID
     */
    protected $_researchAreaId = null;
    
    /**
     * Levels Enabled<br/><br/>
     * <b>Stephino_Rpg_Config_Modifier</b> features prefixed by "<b>enables</b>" are incompatible<br/><br/>
     * When enabled, each feature must have <b>costPolynomial</b> and <b>modifierPolynomial</b> associated with the rewards in <b>Stephino_Rpg_Config_Modifier</b>
     * 
     * @var boolean
     */
    protected $_levelsEnabled = false;

    /**
     * Research field name<br/>
     * <span class="info">
     *     Image files are stored in <b>{current theme}/img/story/researchFields/{id}/</b><br/> 
     *     Required files: <b>512.png</b>
     * </span>
     * 
     * @return string Name
     */
    public function getName($htmlOutput = false) {
        return parent::getName($htmlOutput);
    }

    /**
     * Set the Research Field Name
     * 
     * @param string|null $name Research Field Name
     * @return Stephino_Rpg_Config_ResearchField
     */
    public function setName($name) {
        $this->_name = (null === $name ? null : Stephino_Rpg_Utils_Lingo::cleanup($name));

        return $this;
    }

    /**
     * Research field description<br/>
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
     * @return Stephino_Rpg_Config_ResearchField
     */
    public function setDescription($description) {
        $this->_description = (null === $description ? null : Stephino_Rpg_Utils_Lingo::cleanup($description));

        return $this;
    }
    
    /**
     * Research field story fragment
     * 
     * @ref large
     * @return string|null Story
     */
    public function getStory() {
        return $this->_story;
    }

    /**
     * Set the "Story" parameter
     * 
     * @param string|null $story Story
     * @return Stephino_Rpg_Config_ResearchField
     */
    public function setStory($story) {
        $this->_story = (null === $story ? null : Stephino_Rpg_Utils_Lingo::cleanup($story));

        return $this;
    }
    
    /**
     * Each field corresponds to a research area
     * 
     * @return Stephino_Rpg_Config_ResearchArea|null Research Area
     */
    public function getResearchArea() {
        return Stephino_Rpg_Config::get()->researchAreas()->getById($this->_researchAreaId);
    }
    
    /**
     * Set the "Research Area" parameter
     * 
     * @param int|null $researchAreaId Stephino_Rpg_Config_ResearchArea ID
     * @return Stephino_Rpg_Config_ResearchField
     */
    public function setResearchArea($researchAreaId) {
        $this->_researchAreaId = (null === $researchAreaId ? null : intval($researchAreaId));

        return $this;
    }
    
    /**
     * This research field can be upgraded
     * 
     * @return boolean Research levels
     */
    public function getLevelsEnabled() {
        return (null === $this->_levelsEnabled ? false : $this->_levelsEnabled);
    }

    /**
     * Set the "Levels Enabled" parameter
     * 
     * @param boolean $enabled Levels Enabled
     * @return Stephino_Rpg_Config_ResearchField
     */
    public function setLevelsEnabled($enabled) {
        $this->_levelsEnabled = (boolean) $enabled;

        return $this;
    }
    
    /**
     * Select a set of features this <b>research field</b> model enables
     * 
     * @return Stephino_Rpg_Config_Modifier|null Modifier
     */
    public function getModifier() {
        return $this->_getModifierTrait();
    }
    
    /**
     * If levels are enabled, effects cast by the modifier are amplified with the <b>research field</b> level
     * 
     * @depends levelsEnabled
     * @return poly|null Modifier polynomial
     */
    public function getModifierPolynomial() {
        return $this->_getModifierPolynomialTrait();
    }

}

/*EOF*/