<?php

/**
 * Stephino_Rpg_Config_Governments
 * 
 * @title      Governments
 * @desc       Holds the configuration for Governments
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Config_Governments extends Stephino_Rpg_Config_Item_Collection {

    /**
     * Serialization Key - Must be defined by each item individually
     */
    const KEY = 'governments';

    /**
     * Class name of corresponding Single Item
     */
    const SINGLE_CLASS = Stephino_Rpg_Config_Government::class;

    /**
     * Get a single configuration item
     * 
     * @param int $id Government ID
     * @return Stephino_Rpg_Config_Government|null
     */
    public function getById($id) {
        return parent::getById($id);
    }
    
    /**
     * Get a single (random) configuration item
     * 
     * @return Stephino_Rpg_Config_Government|null
     */
    public function getRandom() {
        return parent::getRandom();
    }

    /**
     * Get all available single configuration items
     * 
     * @return Stephino_Rpg_Config_Government[]
     */
    public function getAll() {
        return parent::getAll();
    }
    
    /**
     * Add a new item to the collection
     * 
     * @param array $data      (optional) Item data; default <b>empty array</b>
     * @param int   $newItemId (optional) New Item ID; default <b>null</b>, auto-assigned
     * @return Stephino_Rpg_Config_Government
     */
    public function add(Array $data = array(), $newItemId = null) {
        return parent::add($data, $newItemId);
    }

}

/*EOF*/