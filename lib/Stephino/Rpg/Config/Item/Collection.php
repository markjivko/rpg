<?php

/**
 * Stephino_Rpg_Config_Item_Collection
 * 
 * @title      Configuration Item abstraction
 * @desc       An abstraction of all individual configuration items
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
abstract class Stephino_Rpg_Config_Item_Collection extends Stephino_Rpg_Config_Item_Abstract {

    /**
     * Class name of corresponding Single Item
     * Must be defined by each item individually
     */
    const SINGLE_CLASS = '';

    /**
     * Collection of Single Config Items
     *
     * @var Stephino_Rpg_Config_Item_Single[]
     */
    protected $_data = array();

    /**
     * Homologous Single Item class name
     * 
     * @var string
     */
    protected $_singleClassName = '';

    /**
     * Get the key of corresponding Single Item
     * 
     * @return string|null
     */
    public static function keySingle() {
        return constant(static::SINGLE_CLASS . '::KEY');
    }

    /**
     * Parse an item collection
     * 
     * @param array $data Associative array
     * @throws Exception
     */
    public function __construct($data) {
        // Prepare the single class name
        $this->_singleClassName = Stephino_Rpg_Config::class . '_' . ucfirst(self::keySingle());

        // Valid class
        if (!class_exists($this->_singleClassName)) {
            throw new Exception('Could not find corresponding Single config item class "' . $this->_singleClassName . '"');
        }

        // Valid configuration array
        if (is_array($data)) {
            // Prepare the single item class name
            foreach ($data as $id => $config) {
                // Valid ID and config data
                if (is_numeric($id) && is_array($config)) {
                    // Convert to an integer
                    $id = intval($id);

                    // Valid class
                    if ($id > 0 && class_exists($this->_singleClassName)) {
                        // Get the class instance
                        $this->_data[$id] = new $this->_singleClassName($config, $id);

                        // Not a valid item
                        if (!$this->_data[$id] instanceof Stephino_Rpg_Config_Item_Single) {
                            unset($this->_data[$id]);
                        }
                    }
                }
            }
        }
    }

    /**
     * Get a single configuration item
     * 
     * @param int $id Configuration item ID
     * @return Stephino_Rpg_Config_Item_Single|null
     */
    public function getById($id) {
        return (null === $id ? null : (isset($this->_data[$id]) ? $this->_data[$id] : null));
    }
    
    /**
     * Get a single (random) configuration item
     * 
     * @return Stephino_Rpg_Config_Item_Single|null
     */
    public function getRandom() {
        if (!count($this->_data)) {
            return null;
        }
        
        // Get the keys
        $keys = array_keys($this->_data);
        
        // Shuffle them
        shuffle($keys);
        
        // Get the first (random) item
        return $this->_data[current($keys)];
    }

    /**
     * Get all available single configuration items
     * 
     * @return Stephino_Rpg_Config_Item_Single[]
     */
    public function getAll() {
        return $this->_data;
    }
    
    /**
     * Add a new item to the collection
     * 
     * @param array $data      (optional) Item data; default <b>empty array</b>
     * @param int   $newItemId (optional) New Item ID; default <b>null</b>, auto-assigned
     * @param int   $newItemId (optional) New Item ID; default <b>null</b>, auto-assigned
     * @return Stephino_Rpg_Config_Item_Single
     * @throws Exception
     */
    public function add(Array $data = array(), $newItemId = null) {
        if (null === $newItemId) {
            // Prepare the new ID
            $newItemId = 1;

            // Get the current keys
            $itemIds = array_map('intval', array_keys($this->_data));

            // A valid list of IDs was found
            if (count($itemIds)) {
                do {
                    // Fill in the gaps
                    if (!in_array($newItemId, $itemIds)) {
                        break;
                    }

                    // Move to the next slot
                    $newItemId++;
                } while (true);
            }
        } else {
            if (isset($this->_data[$newItemId])) {
                throw new Exception('Trying to create duplicate of #' . $newItemId);
            }
        }

        // Add the item to the collection
        $this->_data[$newItemId] = new $this->_singleClassName($data, $newItemId);

        // Hold on to the new ID
        return $this->_data[$newItemId];
    }

    /**
     * Delete an item from the collection
     * 
     * @param int $itemId Item ID
     * @return boolean
     */
    public function delete($itemId) {
        // Item found
        if (isset($this->_data[$itemId])) {
            unset($this->_data[$itemId]);
            return true;
        }

        return false;
    }

    /**
     * Convert the current object into an array
     * 
     * @param boolean $hideSensitive (optional) Hide sensitive fields; default <b>false</b>
     * @return array Associative array
     */
    public function toArray($hideSensitive = false) {
        return array_map(
            function(/* @var $item Stephino_Rpg_Config_Item_Single */ $item) use($hideSensitive) {
                return $item->toArray($hideSensitive);
            }, $this->_data
        );
    }

}

/*EOF*/