<?php

/**
 * Stephino_Rpg_Config_PremiumPackage
 * 
 * @title      Premium Package
 * @desc       Holds the configuration for a single "Premium Package" item
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Config_PremiumPackage extends Stephino_Rpg_Config_Item_Single {

    /**
     * Serialization Key - Must be defined by each item individually
     */
    const KEY = 'premiumPackage';
    
    /**
     * Class name of corresponding Collection Item
     */
    const COLLECTION_CLASS = Stephino_Rpg_Config_PremiumPackages::class;

    /**
     * Premium Package Name
     * 
     * @var string|null
     */
    protected $_name = null;

    /**
     * Premium Package Description
     * 
     * @var string|null
     */
    protected $_description = null;

    /**
     * Gem
     * 
     * @var int
     */
    protected $_gem = 1;

    /**
     * Cost in fiat currency
     * 
     * @var float
     */
    protected $_costFiat = 0.0;
    
    /**
     * Cost in Gold
     * 
     * @var int
     */
    protected $_costGold = 0;
    
    /**
     * Cost in research points
     *
     * @var int
     */
    protected $_costResearch = 0;

    /**
     * Premium package name
     * 
     * @return string Name
     */
    public function getName($htmlOutput = false) {
        return parent::getName($htmlOutput);
    }

    /**
     * Set the Premium Package Name
     * 
     * @param string|null $name Premium Package Name
     * @return Stephino_Rpg_Config_PremiumPackage
     */
    public function setName($name) {
        $this->_name = (null === $name ? null : Stephino_Rpg_Utils_Lingo::cleanup($name));

        return $this;
    }

    /**
     * Premium package description<br/>
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
     * @return Stephino_Rpg_Config_PremiumPackage
     */
    public function setDescription($description) {
        $this->_description = (null === $description ? null : Stephino_Rpg_Utils_Lingo::cleanup($description));

        return $this;
    }

    /**
     * Number of <b>{x}</b> received with this package
     * 
     * @placeholder core.resourceGemName,Gems
     * @ref 1
     * @return int {x} reward
     */
    public function getGem() {
        return null === $this->_gem ? 1 : $this->_gem;
    }

    /**
     * Set the "Gem" parameter
     * 
     * @param int $gem Gem
     * @return Stephino_Rpg_Config_PremiumPackage
     */
    public function setGem($gem) {
        $this->_gem = intval($gem);

        // Minimum
        if ($this->_gem < 1) {
            $this->_gem = 1;
        }
        
        return $this;
    }

    /**
     * Package cost in <b>{x}</b> - set by <b>Core &gt; PayPal Currency</b>
     * 
     * @placeholder core.payPalCurrency,USD
     * @default 0
     * @ref 0
     * @return float Cost in {x}
     */
    public function getCostFiat() {
        return null === $this->_costFiat ? 0.0 : $this->_costFiat;
    }

    /**
     * Set the "Cost Fiat" parameter
     * 
     * @param float $costFiat Cost in fiat currency
     * @return Stephino_Rpg_Config_PremiumPackage
     */
    public function setCostFiat($costFiat) {
        $this->_costFiat = round($costFiat, 2);

        // Minimum
        if ($this->_costFiat < 0.0) {
            $this->_costFiat = 0.0;
        }
        
        return $this;
    }
    
    /**
     * Package cost in <b>{x}</b>
     * 
     * @placeholder core.resourceGoldName,Gold
     * @default 0
     * @ref 0
     * @return int Cost in {x}
     */
    public function getCostGold() {
        return null === $this->_costGold ? 0 : $this->_costGold;
    }

    /**
     * Set the "Cost Gold" parameter
     * 
     * @param int $costGold Cost Gold
     * @return Stephino_Rpg_Config_PremiumPackage
     */
    public function setCostGold($costGold) {
        $this->_costGold = intval($costGold);

        // Minimum
        if ($this->_costGold < 0) {
            $this->_costGold = 0;
        }
        
        return $this;
    }
    
    /**
     * Package cost in <b>{x}</b>
     * 
     * @placeholder core.resourceResearchName,Research pooints
     * @default 0
     * @ref 0
     * @return int Cost in {x}
     */
    public function getCostResearch() {
        return null === $this->_costResearch ? 0 : $this->_costResearch;
    }

    /**
     * Set the "Cost Research" parameter
     * 
     * @param int $costResearch Cost Research
     * @return Stephino_Rpg_Config_PremiumPackage
     */
    public function setCostResearch($costResearch) {
        $this->_costResearch = intval($costResearch);

        // Minimum
        if ($this->_costResearch < 0) {
            $this->_costResearch = 0;
        }
        
        return $this;
    }
}

/*EOF*/