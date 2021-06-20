<?php
/**
 * Stephino_Rpg_Db_Model_Islands
 * 
 * @title     Model:Islands
 * @desc      Islands Model
 * @copyright (c) 2021, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Db_Model_Islands extends Stephino_Rpg_Db_Model {

    /**
     * Islands Model Name
     */
    const NAME = 'islands';
    
    /**
     * Create a new island
     * 
     * @param string $islandName           (optional) Island name; default <b>null</b>, auto-generated
     * @param int    $configIslandId       (optional) Configuration - Island ID; default <b>null</b>, randomly chosen
     * @param int    $configIslandStatueId (optional) Configuration - Island statue ID; default <b>null</b>, randomly chosen
     * @return [int,Stephino_Rpg_Config_Island,Stephino_Rpg_Config_IslandStatue] Array of new island ID, island configuration object
     * @throws Exception
     */
    public function create($islandName = null, $configIslandId = null, $configIslandStatueId = null) {
        // Validate config ID
        if (null !== $configIslandId) {
            $configIslandObject = Stephino_Rpg_Config::get()->islands()->getById($configIslandId);
        } else {
            $configIslandObject = Stephino_Rpg_Config::get()->islands()->getRandom();
        }

        // Validate the island slots
        $islandCitySlotsValid = false;
        
        // Get all the configuration objects
        $allConfigIslandObjects = Stephino_Rpg_Config::get()->islands()->getAll();
        do {
            // Prepare the city slots
            $islandCitySlots = (null !== $configIslandObject && null !== $configIslandObject->getCitySlots() ? json_decode($configIslandObject->getCitySlots(), true) : null);

            // Invalid island slots configuration
            if (is_array($islandCitySlots)) {
                $islandCitySlotsValid = true;
                break;
            }
            
            // Start from the beginning
            $configIslandObject = current($allConfigIslandObjects);
            
            // Nothing to do
            if (false === $configIslandObject) {
                break;
            }
            
            // Go to the next slot
            next($allConfigIslandObjects);
        } while (true);
        
        // Invalid city slots
        if (!$islandCitySlotsValid) {
            throw new Exception(
                sprintf(
                    __('Invalid slots configuration (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigCityName()
                )
            );
        }
        
        // Validate the statue config ID
        if (null !== $configIslandStatueId) {
            $configIslandStatueObject = Stephino_Rpg_Config::get()->islandStatues()->getById($configIslandStatueId);
        } else {
            $configIslandStatueObject = Stephino_Rpg_Config::get()->islandStatues()->getRandom();
        }
        
        // Invalid object
        if (null === $configIslandStatueObject) {
            throw new Exception(
                sprintf(
                    __('Invalid configuration (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigIslandStatueName()
                )
            );
        }

        // Successful insert
        if (null === $islandId = $this->getDb()->tableIslands()->create(
            $configIslandObject->getId(), 
            $configIslandStatueObject->getId()
        )) {
            throw new Exception(
                sprintf(
                    __('Could not create (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigIslandName()
                )
            );
        }
        
        // Calculate the [X,Y] coordinates
        list($coordX, $coordY) = Stephino_Rpg_Utils_Math::getSnakePoint($islandId);
        
        // Get a new island name
        if (null === $islandName) {
            $islandName = Stephino_Rpg_Utils_Lingo::generateIslandName(true, $coordX, $coordY);
        }
        
        // Update the coordinates and name
        $updateResult = $this->getDb()->tableIslands()->updateById(
            array(
                Stephino_Rpg_Db_Table_Islands::COL_ISLAND_NAME => $islandName,
            ), 
            $islandId
        );
        
        // Could not update
        if (!$updateResult) {
            // Try to remove the row
            $this->getDb()->tableIslands()->deleteById($islandId);
            
            // Inform the user
            throw new Exception(
                sprintf(
                    __('Could not update name (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigIslandName()
                )
            );
        }
        
        return array($islandId, $configIslandObject, $configIslandStatueObject);
    }
    
    /**
     * Get the colonization time
     * 
     * @param Stephino_Rpg_Config_Island $islandConfig   Island Configuration Object
     * @param int                        $numberOfCities Total number of cities the user has
     * @return int Time in seconds
     */
    public function getColonizationTime($islandConfig, $numberOfCities) {
        return (int) Stephino_Rpg_Utils_Config::getPolyValue(
            $islandConfig->getCostTimePolynomial(),
            abs((int) $numberOfCities), 
            $islandConfig->getCostTime()
        );
    }
    
    /**
     * Set the Statue Level
     * 
     * @param int $islandId    Island ID
     * @param int $statueLevel Island Statue Level
     * @return int|false Number of rows updated or false on error
     */
    public function setStatueLevel($islandId, $statueLevel) {
        // Sanitize the statue level
        $statueLevel = abs((int) $statueLevel);
        if ($statueLevel < 1) {
            $statueLevel = 1;
        }
        
        return $this->getDb()->tableIslands()->updateById(
            array(
                Stephino_Rpg_Db_Table_Islands::COL_ISLAND_STATUE_LEVEL => $statueLevel
            ), 
            $islandId
        );
    }
    
    /**
     * Get the city index
     * 
     * @param Stephino_Rpg_Config_Island $islandConfig Island configuration object
     * @param int                          $islandSlot   Island slot, produced with <i>Stephino_Rpg_Utils_Math::getSnakeLength()</i>
     * @return int|null City index or null on error
     */
    public function getCityIndex($islandConfig, $islandSlot) {
        // Prepare the result
        $result = null;
        
        // Invalid city slots
        if (null === $citySlots = $islandConfig->getCitySlots()) {
            return null;
        }
        
        // Prepare the slot index
        if (is_array($citySlotsArray = json_decode($citySlots, true))) {
            foreach ($citySlotsArray as $citySlotIndex => list($coordX, $coordY)) {
                if ($islandSlot == Stephino_Rpg_Utils_Math::getSnakeLength($coordX, $coordY)) {
                    $result = $citySlotIndex;
                    break;
                }
            }
        }
        return $result;
    }
}

/* EOF */