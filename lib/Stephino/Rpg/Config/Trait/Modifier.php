<?php
/**
 * Stephino_Rpg_Config_Trait_Modifier
 * 
 * @title     Item modifiers
 * @desc      Define item modifiers
 * @copyright (c) 2020, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */

trait Stephino_Rpg_Config_Trait_Modifier {

    /**
     * Modifier
     * 
     * @var int|null
     */
    protected $_modifierId = null;

    /**
     * Modifier Polynomial
     * 
     * @var string|null
     */
    protected $_modifierPolynomial = null;
    
    /**
     * Select a set of features this item enables
     * 
     * @return Stephino_Rpg_Config_Modifier|null Modifier
     */
    public function getModifier() {
        return Stephino_Rpg_Config::get()->modifiers()->getById($this->_modifierId);
    }

    /**
     * Set the "Modifier" parameter
     * 
     * @param int|null $modifierId Stephino_Rpg_Config_Modifier ID
     */
    public function setModifier($modifierId) {
        $this->_modifierId = (null === $modifierId ? null : intval($modifierId));

        // Method chaining
        return $this;
    }

    /**
     * Effects cast by the modifier are amplified with the item level
     * 
     * @return poly|null Modifier polynomial
     */
    public function getModifierPolynomial() {
        return $this->_modifierPolynomial;
    }

    /**
     * Set the "Modifier Polynomial" parameter
     * 
     * @param string|null $modifierPolynomial Modifier Polynomial
     */
    public function setModifierPolynomial($modifierPolynomial) {
        $this->_modifierPolynomial = $this->_sanitizePoly($modifierPolynomial);

        // Method chaining
        return $this;
    }
}

/*EOF*/