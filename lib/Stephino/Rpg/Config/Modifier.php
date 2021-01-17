<?php

/**
 * Stephino_Rpg_Config_Modifier
 * 
 * @title      Modifier
 * @desc       Holds the configuration for a single "Modifier" item
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Config_Modifier extends Stephino_Rpg_Config_Item_Single {

    /**
     * Serialization Key - Must be defined by each item individually
     */
    const KEY = 'modifier';

    /**
     * Modifier Name
     * 
     * @var string|null
     */
    protected $_name = null;

    /**
     * Effect: Satisfaction
     * 
     * @var int|null
     */
    protected $_effectMetricSatisfaction = null;

    /**
     * Effect: Storage
     * 
     * @var int|null
     */
    protected $_effectMetricStorage = null;

    /**
     * Effect: Population
     * 
     * @var int|null
     */
    protected $_effectMetricPopulation = null;

    /**
     * Effect: Gold
     * 
     * @var int|null
     */
    protected $_effectResourceGold = null;

    /**
     * Effect: Alpha
     * 
     * @var int|null
     */
    protected $_effectResourceAlpha = null;

    /**
     * Effect: Beta
     * 
     * @var int|null
     */
    protected $_effectResourceBeta = null;

    /**
     * Effect: Gamma
     * 
     * @var int|null
     */
    protected $_effectResourceGamma = null;

    /**
     * Effect: Resource Extra 1
     * 
     * @var int|null
     */
    protected $_effectResourceExtra1 = null;

    /**
     * Effect: Resource Extra 2
     * 
     * @var int|null
     */
    protected $_effectResourceExtra2 = null;

    /**
     * Effect: Research
     * 
     * @var int|null
     */
    protected $_effectResourceResearch = null;

    /**
     * Effect: Gem
     * 
     * @var int|null
     */
    protected $_effectResourceGem = null;

    /**
     * Modifier name
     * 
     * @return string Name
     */
    public function getName($htmlOutput = false) {
        return parent::getName($htmlOutput);
    }

    /**
     * Set the Modifier Name
     * 
     * @param string|null $name Modifier Name
     * @return Stephino_Rpg_Config_Modifier
     */
    public function setName($name) {
        $this->_name = (null === $name ? null : Stephino_Rpg_Utils_Lingo::cleanup($name));

        // Method chaining
        return $this;
    }
    
    /**
     * Level 1 <b>{x}</b> contribution
     * <br/> Affected by modifier polynomial
     * 
     * @placeholder core.metricSatisfactionName,Satisfaction
     * @return int|null Effect: {x}
     */
    public function getEffectMetricSatisfaction() {
        return $this->_effectMetricSatisfaction;
    }

    /**
     * Set the "Satisfaction" parameter
     * 
     * @param int|null $satisfaction Satisfaction
     * @return Stephino_Rpg_Config_Modifier
     */
    public function setEffectMetricSatisfaction($satisfaction) {
        $this->_effectMetricSatisfaction = (null === $satisfaction ? null : intval($satisfaction));

        // Method chaining
        return $this;
    }
    
    /**
     * Level 1 hourly <b>{x}</b> expansion
     * <br/> Affected by modifier polynomial
     * 
     * @placeholder core.metricStorageName,Storage
     * @return int|null Effect: {x} expansion
     */
    public function getEffectMetricStorage() {
        return $this->_effectMetricStorage;
    }

    /**
     * Set the "Effect Metric Storage" parameter
     * 
     * @param int|null $effectMetricStorage Effect Metric Storage
     * @return Stephino_Rpg_Config_Modifier
     */
    public function setEffectMetricStorage($effectMetricStorage) {
        $this->_effectMetricStorage = (null === $effectMetricStorage ? null : intval($effectMetricStorage));

        // Method chaining
        return $this;
    }

    /**
     * Level 1 hourly <b>{x}</b> migration
     * <br/> Affected by modifier polynomial
     * 
     * @placeholder core.metricPopulationName,Population
     * @return int|null Effect: {x} migration
     */
    public function getEffectMetricPopulation() {
        return $this->_effectMetricPopulation;
    }

    /**
     * Set the "Effect Metric Population" parameter
     * 
     * @param int|null $effectMetricPopulation Effect Metric Population
     * @return Stephino_Rpg_Config_Modifier
     */
    public function setEffectMetricPopulation($effectMetricPopulation) {
        $this->_effectMetricPopulation = (null === $effectMetricPopulation ? null : intval($effectMetricPopulation));

        // Method chaining
        return $this;
    }

    /**
     * Level 1 hourly <b>{x}</b> production
     * <br/> Affected by modifier polynomial
     * 
     * @placeholder core.resourceGoldName,Gold
     * @return int|null Effect: {x}
     */
    public function getEffectResourceGold() {
        return $this->_effectResourceGold;
    }

    /**
     * Set the "Effect Resource Gold" parameter
     * 
     * @param int|null $effectResourceGold Effect Resource Gold
     * @return Stephino_Rpg_Config_Modifier
     */
    public function setEffectResourceGold($effectResourceGold) {
        $this->_effectResourceGold = (null === $effectResourceGold ? null : intval($effectResourceGold));

        // Method chaining
        return $this;
    }
    
    /**
     * Level 1 hourly <b>{x}</b> acquisition
     * <br/> Affected by modifier polynomial
     * 
     * @placeholder core.resourceResearchName,Research points
     * @return int|null Effect: {x}
     */
    public function getEffectResourceResearch() {
        return $this->_effectResourceResearch;
    }

    /**
     * Set the "Effect Resource Research" parameter
     * 
     * @param int|null $effectResourceResearch Effect Resource Research
     * @return Stephino_Rpg_Config_Modifier
     */
    public function setEffectResourceResearch($effectResourceResearch) {
        $this->_effectResourceResearch = (null === $effectResourceResearch ? null : intval($effectResourceResearch));

        // Method chaining
        return $this;
    }

    /**
     * Level 1 hourly <b>{x}</b> production
     * <br/> Affected by modifier polynomial
     * 
     * @placeholder core.resourceGemName,Gems
     * @return int|null Effect: {x}
     */
    public function getEffectResourceGem() {
        return $this->_effectResourceGem;
    }

    /**
     * Set the "Effect Resource Gem" parameter
     * 
     * @param int|null $effectResourceGem Effect Resource Gem
     * @return Stephino_Rpg_Config_Modifier
     */
    public function setEffectResourceGem($effectResourceGem) {
        $this->_effectResourceGem = (null === $effectResourceGem ? null : intval($effectResourceGem));

        // Method chaining
        return $this;
    }

    /**
     * Level 1 hourly <b>{x}</b> production
     * <br/> Affected by modifier polynomial
     * 
     * @placeholder core.resourceAlphaName,Alpha
     * @return int|null Effect: {x}
     */
    public function getEffectResourceAlpha() {
        return $this->_effectResourceAlpha;
    }

    /**
     * Set the "Effect Resource Alpha" parameter
     * 
     * @param int|null $effectResourceAlpha Effect Resource Alpha
     * @return Stephino_Rpg_Config_Modifier
     */
    public function setEffectResourceAlpha($effectResourceAlpha) {
        $this->_effectResourceAlpha = (null === $effectResourceAlpha ? null : intval($effectResourceAlpha));

        // Method chaining
        return $this;
    }

    /**
     * Level 1 hourly <b>{x}</b> production
     * <br/> Affected by modifier polynomial
     * 
     * @placeholder core.resourceBetaName,Beta
     * @return int|null Effect: {x}
     */
    public function getEffectResourceBeta() {
        return $this->_effectResourceBeta;
    }

    /**
     * Set the "Effect Resource Beta" parameter
     * 
     * @param int|null $effectResourceBeta Effect Resource Beta
     * @return Stephino_Rpg_Config_Modifier
     */
    public function setEffectResourceBeta($effectResourceBeta) {
        $this->_effectResourceBeta = (null === $effectResourceBeta ? null : intval($effectResourceBeta));

        // Method chaining
        return $this;
    }

    /**
     * Level 1 hourly <b>{x}</b> production
     * <br/> Affected by modifier polynomial
     * 
     * @placeholder core.resourceGammaName,Gamma
     * @return int|null Effect: {x}
     */
    public function getEffectResourceGamma() {
        return $this->_effectResourceGamma;
    }

    /**
     * Set the "Effect Resource Gamma" parameter
     * 
     * @param int|null $effectResourceGamma Effect Resource Gamma
     * @return Stephino_Rpg_Config_Modifier
     */
    public function setEffectResourceGamma($effectResourceGamma) {
        $this->_effectResourceGamma = (null === $effectResourceGamma ? null : intval($effectResourceGamma));

        // Method chaining
        return $this;
    }

    /**
     * Level 1 hourly <b>{x}</b> production
     * <br/> Affected by modifier polynomial
     * 
     * @placeholder core.resourceExtra1Name,Extra resource 1
     * @return int|null Effect: {x}
     */
    public function getEffectResourceExtra1() {
        return $this->_effectResourceExtra1;
    }

    /**
     * Set the "Effect Resource Extra 1" parameter
     * 
     * @param int|null $effectResourceExtra1 Effect Resource Extra 1
     * @return Stephino_Rpg_Config_Modifier
     */
    public function setEffectResourceExtra1($effectResourceExtra1) {
        $this->_effectResourceExtra1 = (null === $effectResourceExtra1 ? null : intval($effectResourceExtra1));

        // Method chaining
        return $this;
    }

    /**
     * Level 1 hourly <b>{x}</b> production
     * <br/> Affected by modifier polynomial
     * 
     * @placeholder core.resourceExtra2Name,Extra resource 2
     * @return int|null Effect: {x}
     */
    public function getEffectResourceExtra2() {
        return $this->_effectResourceExtra2;
    }

    /**
     * Set the "Effect Resource Extra 2" parameter
     * 
     * @param int|null $effectResourceExtra2 Effect Resource Extra 2
     * @return Stephino_Rpg_Config_Modifier
     */
    public function setEffectResourceExtra2($effectResourceExtra2) {
        $this->_effectResourceExtra2 = (null === $effectResourceExtra2 ? null : intval($effectResourceExtra2));

        // Method chaining
        return $this;
    }
}

/*EOF*/