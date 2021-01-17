<?php

/**
 * Stephino_Rpg_Config_PremiumModifiers
 * 
 * @title      Premium Modifiers
 * @desc       Holds the configuration for Premium Modifiers
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Config_PremiumModifiers extends Stephino_Rpg_Config_Item_Collection {

    /**
     * Serialization Key - Must be defined by each item individually
     */
    const KEY = 'premiumModifiers';

    /**
     * Class name of corresponding Single Item
     */
    const SINGLE_CLASS = Stephino_Rpg_Config_PremiumModifier::class;

    /**
     * Get a single configuration item
     * 
     * @param int $id Premium Modifier ID
     * @return Stephino_Rpg_Config_PremiumModifier|null
     */
    public function getById($id) {
        return parent::getById($id);
    }

    /**
     * Get a single (random) configuration item
     * 
     * @return Stephino_Rpg_Config_PremiumModifier|null
     */
    public function getRandom() {
        return parent::getRandom();
    }
    
    /**
     * Get all available single configuration items
     * 
     * @return Stephino_Rpg_Config_PremiumModifier[]
     */
    public function getAll() {
        return parent::getAll();
    }
    
    /**
     * Add a new item to the collection
     * 
     * @param array $data      (optional) Item data; default <b>empty array</b>
     * @param int   $newItemId (optional) New Item ID; default <b>null</b>, auto-assigned
     * @return Stephino_Rpg_Config_PremiumModifier
     */
    public function add(Array $data = array(), $newItemId = null) {
        return parent::add($data, $newItemId);
    }

}

/*EOF*/