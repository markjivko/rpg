<?php

/**
 * Stephino_Rpg_Config_Tutorial
 * 
 * @title      Tutorial
 * @desc       Holds the configuration for a single "Tutorial" item
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Config_Tutorial extends Stephino_Rpg_Config_Item_Single {

    /**
     * Serialization Key - Must be defined by each item individually
     */
    const KEY = 'tutorial';
    
    /**
     * Class name of corresponding Collection Item
     */
    const COLLECTION_CLASS = Stephino_Rpg_Config_Tutorials::class;

    // Views
    const VIEW_WORLD  = Stephino_Rpg_Renderer_Ajax::VIEW_WORLD;
    const VIEW_ISLAND = Stephino_Rpg_Renderer_Ajax::VIEW_ISLAND;
    const VIEW_CITY   = Stephino_Rpg_Renderer_Ajax::VIEW_CITY;
    
    /**
     * Tutorial Name
     * 
     * @var string|null
     */
    protected $_name = null;

    /**
     * Tutorial Description
     * 
     * @var string|null
     */
    protected $_description = null;

    /**
     * Tutorial View
     * 
     * @var string|null
     */
    protected $_tutorialView = null;

    /**
     * Tutorial Target
     * 
     * @var string|null
     */
    protected $_tutorialTarget = null;

    /**
     * Tutorial Target Clickable
     * 
     * @var boolean
     */
    protected $_tutorialTargetClick = false;

    /**
     * Tutorial Target Wait For Element
     * 
     * @var string|null
     */
    protected $_tutorialTargetWaitForElement = null;

    /**
     * Tutorial Step Skippable
     * 
     * @var boolean
     */
    protected $_tutorialCanSkip = false;

    /**
     * Tutorial Is CheckPoint
     * 
     * @var boolean
     */
    protected $_tutorialIsCheckPoint = false;

    /**
     * Tutorial Reward on Skip
     * 
     * @var boolean
     */
    protected $_tutorialRewardOnSkip = false;

    /**
     * Tutorial CheckPoint Reward Gold
     * 
     * @var int|null
     */
    protected $_tutorialCheckPointRewardGold = 0;

    /**
     * Tutorial CheckPoint Reward Research points
     * 
     * @var int|null
     */
    protected $_tutorialCheckPointRewardResearch = 0;

    /**
     * Tutorial CheckPoint Reward Gem
     * 
     * @var int|null
     */
    protected $_tutorialCheckPointRewardGem = 0;

    /**
     * Tutorial CheckPoint Reward Alpha
     * 
     * @var int|null
     */
    protected $_tutorialCheckPointRewardAlpha = 0;

    /**
     * Tutorial CheckPoint Reward Beta
     * 
     * @var int|null
     */
    protected $_tutorialCheckPointRewardBeta = 0;

    /**
     * Tutorial CheckPoint Reward Gamma
     * 
     * @var int|null
     */
    protected $_tutorialCheckPointRewardGamma = 0;

    /**
     * Tutorial CheckPoint Reward Extra 1
     * 
     * @var int|null
     */
    protected $_tutorialCheckPointRewardExtra1 = 0;

    /**
     * Tutorial CheckPoint Reward Extra 2
     * 
     * @var int|null
     */
    protected $_tutorialCheckPointRewardExtra2 = 0;

    /**
     * Tutorial step name
     * 
     * @return string Name
     */
    public function getName($htmlOutput = false) {
        return parent::getName($htmlOutput);
    }

    /**
     * Set the Tutorial Name
     * 
     * @param string|null $name Tutorial Name
     * @return Stephino_Rpg_Config_Tutorial
     */
    public function setName($name) {
        $this->_name = (null === $name ? null : Stephino_Rpg_Utils_Lingo::cleanup($name));

        return $this;
    }

    /**
     * Tutorial step description<br/>
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
     * @return Stephino_Rpg_Config_Tutorial
     */
    public function setDescription($description) {
        $this->_description = (null === $description ? null : Stephino_Rpg_Utils_Lingo::cleanup($description));

        return $this;
    }

    /**
     * This tutorial step is only visible in this view
     *
     * @opt world,island,city
     * @return string|null View
     */
    public function getTutorialView() {
        return $this->_tutorialView;
    }

    /**
     * Set the "View" parameter
     * 
     * @param string|null $tutorialView View
     * @return Stephino_Rpg_Config_Tutorial
     */
    public function setTutorialView($tutorialView) {
        // Validate the view
        if (!in_array($tutorialView, array(self::VIEW_WORLD, self::VIEW_ISLAND, self::VIEW_CITY))) {
            $tutorialView = null;
        }
        
        // Store it
        $this->_tutorialView = $tutorialView;

        return $this;
    }

    /**
     * Set a visual pointer on this element<br/>
     * 
     * <span class="info">CSS Selector</span>
     *
     * @return string|null Target
     */
    public function getTutorialTarget() {
        return $this->_tutorialTarget;
    }

    /**
     * Set the "Target" parameter
     * 
     * @param string|null $tutorialTarget Target
     * @return Stephino_Rpg_Config_Tutorial
     */
    public function setTutorialTarget($tutorialTarget) {
        $this->_tutorialTarget = (null === $tutorialTarget ? null : trim($tutorialTarget));

        return $this;
    }
    
    /**
     * Clicking on the target will advance the tutorial to the next step
     * 
     * @return boolean Target: Clickable
     */
    public function getTutorialTargetClick() {
        return null === $this->_tutorialTargetClick ? false : $this->_tutorialTargetClick;
    }
    
    /**
     * Set the "Target Click" parameter
     * 
     * @param boolean $clickable Target clickable
     * @return Stephino_Rpg_Config_Tutorial
     */
    public function setTutorialTargetClick($clickable) {
        $this->_tutorialTargetClick = !!$clickable;
        
        return $this;
    }

    /**
     * When this element becomes visible the tutorial will advance to the next step<br/>
     * This takes precedence over clicking on the target<br/>
     * If this is empty and the target is not clickable, a button will be shown to advance to the next step instead<br/>
     * 
     * <span class="info">CSS Selector</span>
     *
     * @return string|null Wait for visible
     */
    public function getTutorialTargetWaitForElement() {
        return $this->_tutorialTargetWaitForElement;
    }

    /**
     * Set the "Target Wait For Element" parameter
     * 
     * @param string|null $tutorialTargetWaitForElement Target Wait For Element
     * @return Stephino_Rpg_Config_Tutorial
     */
    public function setTutorialTargetWaitForElement($tutorialTargetWaitForElement) {
        $this->_tutorialTargetWaitForElement = (null === $tutorialTargetWaitForElement ? null : trim($tutorialTargetWaitForElement));

        return $this;
    }

    /**
     * Allow the user to skip the entire tutorial at this step?
     *
     * @return boolean Skip Point
     */
    public function getTutorialCanSkip() {
        return (null === $this->_tutorialCanSkip ? false : $this->_tutorialCanSkip);
    }

    /**
     * Set the "Can Skip" parameter
     * 
     * @param boolean $enabled Can Skip
     * @return Stephino_Rpg_Config_Tutorial
     */
    public function setTutorialCanSkip($enabled) {
        $this->_tutorialCanSkip = (boolean) $enabled;

        return $this;
    }
    
    /**
     * The tutorial automatically starts over one step after the last completed Checkpoint<br/>
     * <span class="info">
     *     Make sure the last step in your tutorial <b>is a Checkpoint</b>!
     * </span>
     *
     * @return boolean Checkpoint
     */
    public function getTutorialIsCheckPoint() {
        return (null === $this->_tutorialIsCheckPoint ? false : $this->_tutorialIsCheckPoint);
    }

    /**
     * Set the "Is CheckPoint" parameter
     * 
     * @param boolean $enabled Is CheckPoint
     * @return Stephino_Rpg_Config_Tutorial
     */
    public function setTutorialIsCheckPoint($enabled) {
        $this->_tutorialIsCheckPoint = (boolean) $enabled;

        return $this;
    }
    
    /**
     * Should we grant the player the reward for this checkpoint if he skips the tutorial?
     * 
     * @depends tutorialIsCheckPoint
     * @return boolean Reward when skipping
     */
    public function getTutorialRewardOnSkip() {
        return (null === $this->_tutorialRewardOnSkip ? false : $this->_tutorialRewardOnSkip);
    }
    
    /**
     * Set the "Reward on Skip" parmeter
     * 
     * @param boolean $enabled Reward on Skip
     * @return Stephino_Rpg_Config_Tutorial
     */
    public function setTutorialRewardOnSkip($enabled) {
        $this->_tutorialRewardOnSkip = (boolean) $enabled;

        return $this;
    }

    /**
     * CheckPoint reward in {x}
     *
     * @depends tutorialIsCheckPoint
     * @placeholder core.resourceGoldName,Gold
     * @ref 0
     * @return int {x} Reward
     */
    public function getTutorialCheckPointRewardGold() {
        return null === $this->_tutorialCheckPointRewardGold ? 0 : $this->_tutorialCheckPointRewardGold;
    }

    /**
     * Set the "CheckPoint Reward Gold" parameter
     * 
     * @param int $tutorialCheckPointRewardGold CheckPoint Reward Gold
     * @return Stephino_Rpg_Config_Tutorial
     */
    public function setTutorialCheckPointRewardGold($tutorialCheckPointRewardGold) {
        $this->_tutorialCheckPointRewardGold = intval($tutorialCheckPointRewardGold);

        // Minimum
        if ($this->_tutorialCheckPointRewardGold < 0) {
            $this->_tutorialCheckPointRewardGold = 0;
        }
        
        return $this;
    }
    
    /**
     * CheckPoint reward in {x}
     *
     * @depends tutorialIsCheckPoint
     * @placeholder core.resourceResearchName,Research points
     * @ref 0
     * @return int {x} Reward
     */
    public function getTutorialCheckPointRewardResearch() {
        return null === $this->_tutorialCheckPointRewardResearch ? 0 : $this->_tutorialCheckPointRewardResearch;
    }

    /**
     * Set the "CheckPoint Reward Research" parameter
     * 
     * @param int $tutorialCheckPointRewardResearch CheckPoint Reward Research points
     * @return Stephino_Rpg_Config_Tutorial
     */
    public function setTutorialCheckPointRewardResearch($tutorialCheckPointRewardResearch) {
        $this->_tutorialCheckPointRewardResearch = intval($tutorialCheckPointRewardResearch);

        // Minimum
        if ($this->_tutorialCheckPointRewardResearch < 0) {
            $this->_tutorialCheckPointRewardResearch = 0;
        }
        
        return $this;
    }

    /**
     * CheckPoint reward in {x}
     *
     * @depends tutorialIsCheckPoint
     * @placeholder core.resourceGemName,Gems
     * @ref 0
     * @return int {x} Reward
     */
    public function getTutorialCheckPointRewardGem() {
        return null === $this->_tutorialCheckPointRewardGem ? 0 : $this->_tutorialCheckPointRewardGem;
    }

    /**
     * Set the "CheckPoint Reward Gem" parameter
     * 
     * @param int $tutorialCheckPointRewardGem CheckPoint Reward Gem
     * @return Stephino_Rpg_Config_Tutorial
     */
    public function setTutorialCheckPointRewardGem($tutorialCheckPointRewardGem) {
        $this->_tutorialCheckPointRewardGem = intval($tutorialCheckPointRewardGem);

        // Minimum
        if ($this->_tutorialCheckPointRewardGem < 0) {
            $this->_tutorialCheckPointRewardGem = 0;
        }
        
        return $this;
    }

    /**
     * CheckPoint reward in {x}
     *
     * @depends tutorialIsCheckPoint
     * @placeholder core.resourceAlphaName,Resource Alpha
     * @ref 0
     * @return int {x} Reward
     */
    public function getTutorialCheckPointRewardAlpha() {
        return null === $this->_tutorialCheckPointRewardAlpha ? 0 : $this->_tutorialCheckPointRewardAlpha;
    }

    /**
     * Set the "CheckPoint Reward Alpha" parameter
     * 
     * @param int $tutorialCheckPointRewardAlpha CheckPoint Reward Alpha
     * @return Stephino_Rpg_Config_Tutorial
     */
    public function setTutorialCheckPointRewardAlpha($tutorialCheckPointRewardAlpha) {
        $this->_tutorialCheckPointRewardAlpha = intval($tutorialCheckPointRewardAlpha);

        // Minimum
        if ($this->_tutorialCheckPointRewardAlpha < 0) {
            $this->_tutorialCheckPointRewardAlpha = 0;
        }
        
        return $this;
    }

    /**
     * CheckPoint reward in {x}
     *
     * @depends tutorialIsCheckPoint
     * @placeholder core.resourceBetaName,Resource Beta
     * @ref 0
     * @return int {x} Reward
     */
    public function getTutorialCheckPointRewardBeta() {
        return null === $this->_tutorialCheckPointRewardBeta ? 0 : $this->_tutorialCheckPointRewardBeta;
    }

    /**
     * Set the "CheckPoint Reward Beta" parameter
     * 
     * @param int $tutorialCheckPointRewardBeta CheckPoint Reward Beta
     * @return Stephino_Rpg_Config_Tutorial
     */
    public function setTutorialCheckPointRewardBeta($tutorialCheckPointRewardBeta) {
        $this->_tutorialCheckPointRewardBeta = intval($tutorialCheckPointRewardBeta);

        // Minimum
        if ($this->_tutorialCheckPointRewardBeta < 0) {
            $this->_tutorialCheckPointRewardBeta = 0;
        }
        
        return $this;
    }

    /**
     * CheckPoint reward in {x}
     *
     * @depends tutorialIsCheckPoint
     * @placeholder core.resourceGammaName,Resource Gamma
     * @ref 0
     * @return int {x} Reward
     */
    public function getTutorialCheckPointRewardGamma() {
        return null === $this->_tutorialCheckPointRewardGamma ? 0 : $this->_tutorialCheckPointRewardGamma;
    }

    /**
     * Set the "CheckPoint Reward Gamma" parameter
     * 
     * @param int $tutorialCheckPointRewardGamma CheckPoint Reward Gamma
     * @return Stephino_Rpg_Config_Tutorial
     */
    public function setTutorialCheckPointRewardGamma($tutorialCheckPointRewardGamma) {
        $this->_tutorialCheckPointRewardGamma = intval($tutorialCheckPointRewardGamma);

        // Minimum
        if ($this->_tutorialCheckPointRewardGamma < 0) {
            $this->_tutorialCheckPointRewardGamma = 0;
        }
        
        return $this;
    }

    /**
     * CheckPoint reward in {x}
     *
     * @depends tutorialIsCheckPoint
     * @placeholder core.resourceExtra1Name,Extra resource 1
     * @ref 0
     * @return int {x} Reward
     */
    public function getTutorialCheckPointRewardExtra1() {
        return null === $this->_tutorialCheckPointRewardExtra1 ? 0 : $this->_tutorialCheckPointRewardExtra1;
    }

    /**
     * Set the "CheckPoint Reward Extra 1" parameter
     * 
     * @param int $tutorialCheckPointRewardExtra1 CheckPoint Reward Extra 1
     * @return Stephino_Rpg_Config_Tutorial
     */
    public function setTutorialCheckPointRewardExtra1($tutorialCheckPointRewardExtra1) {
        $this->_tutorialCheckPointRewardExtra1 = intval($tutorialCheckPointRewardExtra1);

        // Minimum
        if ($this->_tutorialCheckPointRewardExtra1 < 0) {
            $this->_tutorialCheckPointRewardExtra1 = 0;
        }
        
        return $this;
    }

    /**
     * CheckPoint reward in {x}
     *
     * @depends tutorialIsCheckPoint
     * @placeholder core.resourceExtra2Name,Extra resource 2
     * @ref 0
     * @return int {x} Reward
     */
    public function getTutorialCheckPointRewardExtra2() {
        return null === $this->_tutorialCheckPointRewardExtra2 ? 0 : $this->_tutorialCheckPointRewardExtra2;
    }

    /**
     * Set the "CheckPoint Reward Extra 2" parameter
     * 
     * @param int $tutorialCheckPointRewardExtra2 CheckPoint Reward Extra 2
     * @return Stephino_Rpg_Config_Tutorial
     */
    public function setTutorialCheckPointRewardExtra2($tutorialCheckPointRewardExtra2) {
        $this->_tutorialCheckPointRewardExtra2 = intval($tutorialCheckPointRewardExtra2);

        // Minimum
        if ($this->_tutorialCheckPointRewardExtra2 < 0) {
            $this->_tutorialCheckPointRewardExtra2 = 0;
        }
        
        return $this;
    }

}

/*EOF*/