<?php
/**
 * Stephino_Rpg_Config_Trait_Cost
 * 
 * @title     Item costs
 * @desc      Define item costs
 * @copyright (c) 2021, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */

trait Stephino_Rpg_Config_Trait_Cost {
    
    /**
     * Build Time
     * 
     * @var int
     */
    protected $_costTime = 1;

    /**
     * Build Time Polynomial
     * 
     * @var string|null
     */
    protected $_costTimePolynomial = null;
    
    /**
     * Cost in Gold
     * 
     * @var int|null
     */
    protected $_costGold = null;
    
    /**
     * Cost in Research points
     * 
     * @var int|null
     */
    protected $_costResearch = null;
    
    /**
     * Cost in Gem
     * 
     * @var int|null
     */
    protected $_costGem = null;

    /**
     * Cost in Resource Alpha
     * 
     * @var int|null
     */
    protected $_costAlpha = null;

    /**
     * Cost in Resource Beta
     * 
     * @var int|null
     */
    protected $_costBeta = null;

    /**
     * Cost in Resource Gamma
     * 
     * @var int|null
     */
    protected $_costGamma = null;

    /**
     * Cost in Resource Extra 1
     * 
     * @var int|null
     */
    protected $_costResourceExtra1 = null;

    /**
     * Cost in Resource Extra 2
     * 
     * @var int|null
     */
    protected $_costResourceExtra2 = null;

    /**
     * Cost Polynomial
     * 
     * @var string|null
     */
    protected $_costPolynomial = null;
    
    /**
     * Time required for acquiring this item in seconds
     * 
     * @default 1
     * @ref 1
     * @return int Time
     */
    public function getCostTime() {
        return null === $this->_costTime ? 1 : $this->_costTime;
    }

    /**
     * Set the "Cost Time" parameter
     * 
     * @param int $costTime Cost Time
     */
    public function setCostTime($costTime) {
        $this->_costTime = intval($costTime);

        // Minimum
        if ($this->_costTime < 1) {
            $this->_costTime = 1;
        }
        
        return $this;
    }

    /**
     * Acquisition time polynomial factor
     * 
     * @return poly|null Time polynomial
     */
    public function getCostTimePolynomial() {
        return $this->_costTimePolynomial;
    }

    /**
     * Set the "Build Time Polynomial" parameter
     * 
     * @param string|null $costTimePolynomial Build Time Polynomial
     */
    public function setCostTimePolynomial($costTimePolynomial) {
        $this->_costTimePolynomial = $this->_sanitizePoly($costTimePolynomial);

        return $this;
    }
    
    /**
     * Level 1 cost in {x}
     * 
     * @placeholder core.resourceGoldName,Gold
     * @ref 0
     * @return int|null Cost in {x}
     */
    public function getCostGold() {
        return $this->_costGold;
    }

    /**
     * Set the "Cost in Gold" parameter
     * 
     * @param int|null $cost Cost in Gold
     */
    public function setCostGold($cost) {
        $this->_costGold = (null === $cost ? null : intval($cost));

        // Minimum
        if (null !== $this->_costGold && $this->_costGold < 0) {
            $this->_costGold = 0;
        }
        
        return $this;
    }
    
    /**
     * Level 1 cost in {x}
     * 
     * @placeholder core.resourceResearchName,Research points
     * @ref 0
     * @return int|null Cost in {x}
     */
    public function getCostResearch() {
        return $this->_costResearch;
    }

    /**
     * Set the "Cost in Research points" parameter
     * 
     * @param int|null $cost Cost in Research points
     */
    public function setCostResearch($cost) {
        $this->_costResearch = (null === $cost ? null : intval($cost));

        // Minimum
        if (null !== $this->_costResearch && $this->_costResearch < 0) {
            $this->_costResearch = 0;
        }
        
        return $this;
    }
    
    /**
     * Level 1 cost in {x}
     * 
     * @placeholder core.resourceGemName,Gems
     * @ref 0
     * @return int|null Cost in {x}
     */
    public function getCostGem() {
        return $this->_costGem;
    }

    /**
     * Set the "Cost in Gem" parameter
     * 
     * @param int|null $cost Cost in Gem
     */
    public function setCostGem($cost) {
        $this->_costGem = (null === $cost ? null : intval($cost));

        // Minimum
        if (null !== $this->_costGem && $this->_costGem < 0) {
            $this->_costGem = 0;
        }
        
        return $this;
    }

    /**
     * Level 1 in {x}
     * 
     * @placeholder core.resourceAlphaName,Resource Alpha
     * @ref 0
     * @return int|null Cost in {x}
     */
    public function getCostAlpha() {
        return $this->_costAlpha;
    }

    /**
     * Set the "Cost in Resource Alpha" parameter
     * 
     * @param int|null $cost Cost in Resource Alpha
     */
    public function setCostAlpha($cost) {
        $this->_costAlpha = (null === $cost ? null : intval($cost));

        // Minimum
        if (null !== $this->_costAlpha && $this->_costAlpha < 0) {
            $this->_costAlpha = 0;
        }
        
        return $this;
    }

    /**
     * Level 1 cost in {x}
     * 
     * @placeholder core.resourceBetaName,Resource Beta
     * @ref 0
     * @return int|null Cost in {x}
     */
    public function getCostBeta() {
        return $this->_costBeta;
    }

    /**
     * Set the "Cost in Resource Beta" parameter
     * 
     * @param int|null $cost Cost in Resource Beta
     */
    public function setCostBeta($cost) {
        $this->_costBeta = (null === $cost ? null : intval($cost));

        // Minimum
        if (null !== $this->_costBeta && $this->_costBeta < 0) {
            $this->_costBeta = 0;
        }
        
        return $this;
    }

    /**
     * Level 1 cost in {x}
     * 
     * @placeholder core.resourceGammaName,Resource Gamma
     * @ref 0
     * @return int|null Cost in {x}
     */
    public function getCostGamma() {
        return $this->_costGamma;
    }

    /**
     * Set the "Cost in Resource Gamma" parameter
     * 
     * @param int|null $cost Cost in Resource Gamma
     */
    public function setCostGamma($cost) {
        $this->_costGamma = (null === $cost ? null : intval($cost));

        // Minimum
        if (null !== $this->_costGamma && $this->_costGamma < 0) {
            $this->_costGamma = 0;
        }
        
        return $this;
    }

    /**
     * Level 1 cost in {x}
     * 
     * @placeholder core.resourceExtra1Name,Extra resource 1
     * @ref 0
     * @return int|null Cost in {x}
     */
    public function getCostResourceExtra1() {
        return $this->_costResourceExtra1;
    }

    /**
     * Set the "Cost in Resource Extra 1" parameter
     * 
     * @param int|null $cost Cost in Resource Extra 1
     */
    public function setCostResourceExtra1($cost) {
        $this->_costResourceExtra1 = (null === $cost ? null : intval($cost));

        // Minimum
        if (null !== $this->_costResourceExtra1 && $this->_costResourceExtra1 < 0) {
            $this->_costResourceExtra1 = 0;
        }
        
        return $this;
    }

    /**
     * Level 1 cost in {x}
     * 
     * @placeholder core.resourceExtra2Name,Extra resource 2
     * @ref 0
     * @return int|null Cost in {x}
     */
    public function getCostResourceExtra2() {
        return $this->_costResourceExtra2;
    }

    /**
     * Set the "Cost in Resource Extra 2" parameter
     * 
     * @param int|null $cost Cost in Resource Extra 2
     */
    public function setCostResourceExtra2($cost) {
        $this->_costResourceExtra2 = (null === $cost ? null : intval($cost));

        // Minimum
        if (null !== $this->_costResourceExtra2 && $this->_costResourceExtra2 < 0) {
            $this->_costResourceExtra2 = 0;
        }
        
        return $this;
    }

    /**
     * Cost polynomial factor
     * 
     * @return poly|null Cost polynomial
     */
    public function getCostPolynomial() {
        return $this->_costPolynomial;
    }

    /**
     * Set the "Cost Polynomial" parameter
     * 
     * @param string|null $costPolynomial Cost Polynomial
     */
    public function setCostPolynomial($costPolynomial) {
        $this->_costPolynomial = $this->_sanitizePoly($costPolynomial);

        return $this;
    }

}

/* EOF */