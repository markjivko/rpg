<?php
/**
 * Stephino_Rpg_Db_Model_Entities
 * 
 * @title     Model:Entities
 * @desc      Entities Model
 * @copyright (c) 2021, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Db_Model_Entities extends Stephino_Rpg_Db_Model {

    /**
     * Entities Model Name
     */
    const NAME = 'entities';
    
    /**
     * Create (or update) an entity by city ID
     * 
     * @param int    $cityId         City ID
     * @param string $entityType     Entity type, one of<ul>
     * <li>Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT</li>
     * <li>Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_SHIP</li>
     * </ul>
     * @param int    $entityConfigId Entity Configuration ID
     * @param int    $entityCount    (optional) Entity count; default <b>0</b>
     * @return int Entity Database ID
     * @throws Exception
     */
    public function set($cityId, $entityType, $entityConfigId, $entityCount = 0) {
        // Get the city data
        if (!is_array($cityData = $this->getDb()->tableCities()->getById($cityId))) {
            throw new Exception(
                sprintf(
                    __('Invalid ID (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigCityName()
                )
            );
        }
        
        // Sanitize the entity count
        $entityCount = intval($entityCount);
        if ($entityCount < 0) {
            $entityCount = 0;
        }
        
        // Get the entity configuration
        $entityConfiguration = null;
        switch($entityType) {
            case Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT:
                $entityConfiguration = Stephino_Rpg_Config::get()->units()->getById($entityConfigId);
                break;
            
            case Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_SHIP:
                $entityConfiguration = Stephino_Rpg_Config::get()->ships()->getById($entityConfigId);
                break;
        }
        
        // Invalid configuration
        if (null === $entityConfiguration) {
            throw new Exception(__('Invalid entity type or configuration ID', 'stephino-rpg'));
        }
        
        // Get the building configuration
        if (null === $buildingConfiguration = $entityConfiguration->getBuilding()) {
            throw new Exception(
                sprintf(
                    __('Invalid configuration (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigBuildingName()
                )
            );
        }

        // Prepare the entity db ID
        $entityDbId = null;
        
        // Search
        if (is_array($entities = $this->getDb()->tableEntities()->getByCity($cityId))) {
            foreach ($entities as $entityRow) {
                if ($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE] == $entityType
                    && $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID] == $entityConfigId) {
                    $entityDbId = (int) $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ID];
                    break;
                }
            }
        }
        
        if (null === $entityDbId) {
            // Attempt to create the entity
            $entityDbId = $this->getDb()->tableEntities()->create(
                $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_USER_ID], 
                $cityData[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID], 
                $cityData[Stephino_Rpg_Db_Table_Cities::COL_ID], 
                $entityType, 
                $entityConfigId, 
                $entityCount
            );
            if (null === $entityDbId) {
                throw new Exception(__('Could not create entity', 'stephino-rpg'));
            }
        } else {
            // Attempt to update the entity
            if (false === $this->getDb()->tableEntities()->updateById(
                array(
                    Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT => $entityCount,
                ), 
                $entityDbId
            )) {
                throw new Exception(__('Could not update entity count', 'stephino-rpg'));
            }
        }
        return $entityDbId;
    }
    
    /**
     * Get the recruitment/build time
     * 
     * @param Stephino_Rpg_Config_Unit|Stephino_Rpg_Config_Ship $entityConfig        Entity Configuration Object
     * @param int                                                   $entityCount         Entity Count
     * @param int                                                   $parentBuildingLevel Parent Building Level
     * @param int                                                   $userId              (optional) User ID; <b>ignored in time-lapse mode</b>
     * @param boolean                                               $timelapseMode       (optional) Time-lapse mode; default <b>true</b>
     * @return int Time in seconds
     */
    public function getRecruitTime($entityConfig, $entityCount, $parentBuildingLevel, $userId = null, $timelapseMode = true) {
        $result = ($entityCount * Stephino_Rpg_Utils_Config::getPolyValue(
            Stephino_Rpg_Config::get()->core()->getSandbox() 
                ? null
                : $entityConfig->getCostTimePolynomial(),
            abs((int) $parentBuildingLevel), 
            $entityConfig->getCostTime()
        ));
        
        // Get the time contraction
        list($timeContraction) = $this->getDb()->modelPremiumModifiers()->getTimeContraction(
            Stephino_Rpg_Db_Model_Buildings::NAME, 
            $userId, 
            $timelapseMode
        );
        
        // Valid value
        if ($timeContraction > 1) {
            $result /= $timeContraction;
        }
        return (int) $result;
    }
    
    /**
     * Helper method to move troops in and out of a convoy payload
     * 
     * @param array $entityDeltas Associative array of <br/>
     * <i>(<b>int</b>) Entity ID</i> => <i>(<b>int</b>) Entity delta (positive or negative)</i>
     * @return int|false Number of rows affected or false on error
     */
    public function move($entityDeltas) {
        if (!is_array($entityDeltas) || !count($entityDeltas)) {
            throw new Exception(__('Invalid entities list', 'stephino-rpg'));
        }
        
        // Get the available entities
        $entityRows = $this->getDb()->tableEntities()->getByIds(array_keys($entityDeltas));
        
        // Prepare the new entities counts
        $entities = array();
        foreach ($entityRows as $entityRow) {
            if (isset($entityDeltas[$entityRow[Stephino_Rpg_Db_Table_Entities::COL_ID]])) {
                $entities[$entityRow[Stephino_Rpg_Db_Table_Entities::COL_ID]] = 
                    intval($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT]) 
                    + intval($entityDeltas[$entityRow[Stephino_Rpg_Db_Table_Entities::COL_ID]]);
            }
        }
        
        // Update the entities
        return $this->getDb()->tableEntities()->setCounts($entities);
    }
    
    /**
     * Delete all entities belonging to this user
     * 
     * @param int $userId User ID
     * @return int|false The number of rows updated or false on error
     */
    public function deleteByUser($userId) {
        return $this->getDb()->tableEntities()->deleteByUser($userId);
    }
}

/* EOF */