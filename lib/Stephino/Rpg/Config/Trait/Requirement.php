<?php
/**
 * Stephino_Rpg_Config_Trait_Requirement
 * 
 * @title     Item requirements
 * @desc      Define item requirements
 * @copyright (c) 2021, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */

trait Stephino_Rpg_Config_Trait_Requirement {

    /**
     * Requires building
     *  
     * @var int|null Building Config ID
     */
    protected $_requiredBuildingId = null;

    /**
     * Requires building minimum level
     *
     * @var int
     */
    protected $_requiredBuildingLevel = 1;

    /**
     * Requires research field
     *
     * @var int|null Stephino_Rpg_Config_ResearchField ID
     */
    protected $_requiredResearchFieldId = null;

    /**
     * Requires research field minimum level
     *
     * @var int
     */
    protected $_requiredResearchFieldLevel = 1;

    /**
     * Requires the existence of this building
     * 
     * @return Stephino_Rpg_Config_Building|null Required: Building
     */
    public function getRequiredBuilding() {
        return Stephino_Rpg_Config::get()->buildings()->getById($this->_requiredBuildingId);
    }

    /**
     * Set the "Required Building" parameter
     * 
     * @param int|null $requiredBuildingId Building Config ID
     */
    public function setRequiredBuilding($requiredBuildingId) {
        $this->_requiredBuildingId = (null === $requiredBuildingId ? null : intval($requiredBuildingId));

        return $this;
    }

    /**
     * Required building minimum level
     * 
     * @ref 1
     * @return int Required: Building Level
     */
    public function getRequiredBuildingLevel() {
        return null === $this->_requiredBuildingLevel ? 1 : $this->_requiredBuildingLevel;
    }

    /**
     * Set the "Required Building Level" parameter
     * 
     * @param int $requiredBuildingLevel Required Building Level
     */
    public function setRequiredBuildingLevel($requiredBuildingLevel) {
        $this->_requiredBuildingLevel = intval($requiredBuildingLevel);

        // Minimum
        if ($this->_requiredBuildingLevel < 1) {
            $this->_requiredBuildingLevel = 1;
        }
        
        return $this;
    }

    /**
     * Requires the completion of this research field
     * 
     * @return Stephino_Rpg_Config_ResearchField|null Required: Research Field
     */
    public function getRequiredResearchField() {
        return Stephino_Rpg_Config::get()->researchFields()->getById($this->_requiredResearchFieldId);
    }

    /**
     * Set the "Required Research Field" parameter
     * 
     * @param int|null $requiredResearchFieldId Stephino_Rpg_Config_ResearchField ID
     */
    public function setRequiredResearchField($requiredResearchFieldId) {
        $this->_requiredResearchFieldId = (null === $requiredResearchFieldId ? null : intval($requiredResearchFieldId));

        return $this;
    }

    /**
     * Research field minimum level
     * 
     * @ref 1
     * @return int Required: Research Field Level
     */
    public function getRequiredResearchFieldLevel() {
        return null === $this->_requiredResearchFieldLevel ? 1 : $this->_requiredResearchFieldLevel;
    }

    /**
     * Set the "Required Research Field Level" parameter
     * 
     * @param int $requiredResearchFieldLevel Required Research Field Level
     */
    public function setRequiredResearchFieldLevel($requiredResearchFieldLevel) {
        $this->_requiredResearchFieldLevel = intval($requiredResearchFieldLevel);

        // Minimum
        if ($this->_requiredResearchFieldLevel < 1) {
            $this->_requiredResearchFieldLevel = 1;
        }

        return $this;
    }

}

/* EOF */