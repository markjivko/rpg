<?php

/**
 * Stephino_Rpg_Config_Modifiers
 * 
 * @title      Modifiers
 * @desc       Holds the configuration for modifiers
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Config_Modifiers extends Stephino_Rpg_Config_Item_Collection {

    /**
     * Serialization Key - Must be defined by each item individually
     */
    const KEY = 'modifiers';

    /**
     * Class name of corresponding Single Item
     */
    const SINGLE_CLASS = Stephino_Rpg_Config_Modifier::class;

    /**
     * Get a single configuration item
     * 
     * @param int $id Modifier ID
     * @return Stephino_Rpg_Config_Modifier|null
     */
    public function getById($id) {
        return parent::getById($id);
    }

    /**
     * Get a single (random) configuration item
     * 
     * @return Stephino_Rpg_Config_Modifier|null
     */
    public function getRandom() {
        return parent::getRandom();
    }
    
    /**
     * Get all available single configuration items
     * 
     * @return Stephino_Rpg_Config_Modifier[]
     */
    public function getAll() {
        return parent::getAll();
    }

    /**
     * Add a new item to the collection
     * 
     * @param array $data      (optional) Item data; default <b>empty array</b>
     * @param int   $newItemId (optional) New Item ID; default <b>null</b>, auto-assigned
     * @return Stephino_Rpg_Config_Modifier
     */
    public function add(Array $data = array(), $newItemId = null) {
        return parent::add($data, $newItemId);
    }
    
}

/*EOF*/