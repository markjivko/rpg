<?php

/**
 * Stephino_Rpg_Config_Government
 * 
 * @title      Government
 * @desc       Holds the configuration for a single "Government" item
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Config_Government extends Stephino_Rpg_Config_Item_Single {

    // Government Modifiers
    use Stephino_Rpg_Config_Trait_Modifier {
        getModifier as _getModifierTrait;
        getModifierPolynomial as _getModifierPolynomialTrait;
    }
    
    // Government Costs
    use Stephino_Rpg_Config_Trait_Cost {
        // Upgrades are instantaneous
        getCostTime as _getCostTimeTrait;
        getCostTimePolynomial as _getCostTimePolynomialTrait;
    }
    
    // Hide elements from control panel
    protected function getCostTime() {}
    protected function getCostTimePolynomial() {}
    
    // Government Requirements
    use Stephino_Rpg_Config_Trait_Requirement;
    
    /**
     * Serialization Key - Must be defined by each item individually
     */
    const KEY = 'government';

    /**
     * Government Name
     * 
     * @var string|null
     */
    protected $_name = null;

    /**
     * Government Description
     * 
     * @var string|null
     */
    protected $_description = null;
    
    /**
     * Government name<br/>
     * <span class="info">
     *     Image files are stored in <b>{current theme}/img/story/governments/{id}/</b><br/> 
     *     Required files: <b>512.png</b>
     * </span>
     * 
     * @return string Name
     */
    public function getName($htmlOutput = false) {
        return parent::getName($htmlOutput);
    }

    /**
     * Set the Government Name
     * 
     * @param string|null $name Government Name
     * @return Stephino_Rpg_Config_Government
     */
    public function setName($name) {
        $this->_name = (null === $name ? null : Stephino_Rpg_Utils_Lingo::cleanup($name));

        // Method chaining
        return $this;
    }

    /**
     * Government description<br/>
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
     * @return Stephino_Rpg_Config_Government
     */
    public function setDescription($description) {
        $this->_description = (null === $description ? null : Stephino_Rpg_Utils_Lingo::cleanup($description));

        // Method chaining
        return $this;
    }
    
    /**
     * Select a set of features this <b>government</b> model enables
     * 
     * @return Stephino_Rpg_Config_Modifier|null Modifier
     */
    public function getModifier() {
        return $this->_getModifierTrait();
    }

    /**
     * Effects cast by the modifier are amplified with the <b>city</b> level
     * 
     * @return poly|null Modifier polynomial
     */
    public function getModifierPolynomial() {
        return $this->_getModifierPolynomialTrait();
    }

}

/*EOF*/