<?php
/**
 * Stephino_Rpg_Config_Trait_XResourceAbundance
 * 
 * @title     Extra resource abundance
 * @desc      Define override for extra resources abundance
 * @copyright (c) 2020, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */

trait Stephino_Rpg_Config_Trait_XResourceAbundance {
    
    /**
     * Abundance of Resource Extra 1
     * 
     * @var boolean
     */
    protected $_resourceExtra1Abundant = false;

    /**
     * Abundance of Resource Extra 2
     * 
     * @var boolean
     */
    protected $_resourceExtra2Abundant = false;
    
    /**
     * Forces abundance of <b>{x}</b> to 100% in all of our cities
     * 
     * @placeholder core.resourceExtra1Name,Extra resource 1
     * @return boolean Abundant {x}
     */
    public function getResourceExtra1Abundant() {
        return (null === $this->_resourceExtra1Abundant ? false : $this->_resourceExtra1Abundant);
    }

    /**
     * Set the "Resource Extra 1 Abundant" parameter
     * 
     * @param boolean $abundant Abundance of Resource Extra 1
     */
    public function setResourceExtra1Abundant($abundant) {
        $this->_resourceExtra1Abundant = (boolean) $abundant;

        // Method chaining
        return $this;
    }

    /**
     * Forces abundance of <b>{x}</b> to 100% in all of our cities
     * 
     * @placeholder core.resourceExtra2Name,Extra resource 2
     * @return boolean Abundant {x}
     */
    public function getResourceExtra2Abundant() {
        return (null === $this->_resourceExtra2Abundant ? false : $this->_resourceExtra2Abundant);
    }

    /**
     * Set the "Resource Extra 2 Abundant" parameter
     * 
     * @param boolean $abundance Abundance of Resource Extra 2
     */
    public function setResourceExtra2Abundant($abundance) {
        $this->_resourceExtra2Abundant = (boolean) $abundance;

        // Method chaining
        return $this;
    }
}

/*EOF*/