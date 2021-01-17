<?php

/**
 * Stephino_Rpg_Config_ResearchArea
 * 
 * @title      Research Area
 * @desc       Holds the configuration for a single "Research Area" item
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Config_ResearchArea extends Stephino_Rpg_Config_Item_Single {

    // Research Area Requirements
    use Stephino_Rpg_Config_Trait_Requirement;
    
    /**
     * Serialization Key - Must be defined by each item individually
     */
    const KEY = 'researchArea';

    /**
     * Research Name
     * 
     * @var string|null
     */
    protected $_name = null;

    /**
     * Research Description
     * 
     * @var string|null
     */
    protected $_description = null;
    
    /**
     * Research Building
     * 
     * @var int|null Stephino_Rpg_Config_Building ID
     */
    protected $_buildingId = null;

    /**
     * Research area name<br/>
     * <span class="info">
     *     Image files are stored in <b>{current theme}/img/story/researchAreas/{id}/</b><br/> 
     *     Required files: <b>512.png</b>, <b>768.png</b>
     * </span>
     * 
     * @return string Name
     */
    public function getName($htmlOutput = false) {
        return parent::getName($htmlOutput);
    }

    /**
     * Set the Research Name
     * 
     * @param string|null $name Research Name
     * @return Stephino_Rpg_Config_ResearchArea
     */
    public function setName($name) {
        $this->_name = (null === $name ? null : Stephino_Rpg_Utils_Lingo::cleanup($name));

        // Method chaining
        return $this;
    }

    /**
     * Research area description<br/>
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
     * @return Stephino_Rpg_Config_ResearchArea
     */
    public function setDescription($description) {
        $this->_description = (null === $description ? null : Stephino_Rpg_Utils_Lingo::cleanup($description));

        // Method chaining
        return $this;
    }
    
    /**
     * Set the <b>main building</b> for this research area
     * 
     * @return Stephino_Rpg_Config_Building|null Building
     */
    public function getBuilding() {
        return Stephino_Rpg_Config::get()->buildings()->getById($this->_buildingId);
    }

    /**
     * Set the "Building" parameter
     * 
     * @param int|null $id Stephino_Rpg_Config_Building ID
     * @return Stephino_Rpg_Config_ResearchArea
     */
    public function setBuilding($id) {
        $this->_buildingId = (null === $id ? null : intval($id));

        // Method chaining
        return $this;
    }
}

/*EOF*/