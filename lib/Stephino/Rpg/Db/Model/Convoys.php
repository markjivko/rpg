<?php
/**
 * Stephino_Rpg_Db_Model_Convoys
 * 
 * @title     Model:Convoys
 * @desc      Convoys Model
 * @copyright (c) 2021, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Db_Model_Convoys extends Stephino_Rpg_Db_Model {

    /**
     * Convoys Model Name
     */
    const NAME = 'convoys';

    /**
     * Create an attack convoy
     * 
     * @param int     $fromCityId     Origin City ID
     * @param int     $toCityId       Destination City ID
     * @param array   $convoyEntities Associative array of
     * <ul>
     *     <li>... (string) <b>"(s|u)_{Entity Config ID}"</b> => (int) <b>Entity Count</b></li>
     * </ul>
     * @return int New Convoy Attack ID
     * @throws Exception
     */
    public function createAttack($fromCityId, $toCityId, $convoyEntities) {
        // Origin information
        if (!is_array($fromInfo = $this->getDb()->tableCities()->getById($fromCityId))) {
            throw new Exception(__('Origin not found', 'stephino-rpg'));
        }
        
        // Destination information
        if (!is_array($toInfo = $this->getDb()->tableCities()->getById($toCityId))) {
            throw new Exception(__('Destination not found', 'stephino-rpg'));
        }
        
        // Prepare the entities
        $convoyPayload = $this->payloadFromEntities(
            $convoyEntities, 
            $this->getDb()->tableEntities()->getByCity($fromCityId), 
            $fromCityId
        );
        
        // Invalid entities count
        if (!count($convoyPayload)) {
            throw new Exception(__('Your army has no troops', 'stephino-rpg'));
        }
        
        // Get the final travel information
        list($travelFast, $travelDuration) = $this->getTravelInfo($fromInfo, $toInfo, $convoyPayload);
        
        // Add the convoy
        $convoyCreateResult = $this->getDb()->tableConvoys()->create(
            $fromInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_USER_ID], 
            $fromInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID], 
            $fromInfo[Stephino_Rpg_Db_Table_Cities::COL_ID], 
            $toInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_USER_ID], 
            $toInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID], 
            $toInfo[Stephino_Rpg_Db_Table_Cities::COL_ID],
            $travelDuration,
            $travelFast,
            $travelDuration + time(),
            0,
            array(
                Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES => $convoyPayload,
            ),
            Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_ATTACK
        );
        
        // DB Error
        if (null === $convoyCreateResult) {
            throw new Exception(__('Could not initiate the convoy', 'stephino-rpg'));
        }
        
        // Get the sanitized data
        list($newConvoyId, $convoyEntitiesSanitized) = $convoyCreateResult;
        
        // Move the troops out of the city
        $troopMovements = array();
        foreach ($convoyEntitiesSanitized as $entityId => $entityRow) {
            $troopMovements[$entityId] = -$entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT];
        }
        
        // Invalid troop movements
        if (false === $this->getDb()->modelEntities()->move($troopMovements)) {
            // Cancel the convoy
            $this->getDb()->tableConvoys()->deleteById($newConvoyId);
            throw new Exception(__('Could not update entities', 'stephino-rpg'));
        }
        
        return $newConvoyId;
    }
    
    /**
     * Create a colonizer convoy
     * 
     * @param int $fromCityId       Origin City ID
     * @param int $fromEntityId     Origin Entity ID
     * @param int $toIslandId       Destination Island ID
     * @param int $toIslandIndex    Destination Island Index
     * @param int $colonizationTime Colonization time in seconds
     * @return int New Colonizer Convoy ID
     * @throws Exception
     */
    public function createColonizer($fromCityId, $fromEntityId, $toIslandId, $toIslandIndex, $colonizationTime) {
        // Origin information
        if (!is_array($fromInfo = $this->getDb()->tableCities()->getById($fromCityId))) {
            throw new Exception(__('Origin not found', 'stephino-rpg'));
        }
        
        // Destination information
        $toInfo = array(
            Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID    => $toIslandId,
            Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_INDEX => $toIslandIndex,
        );
        
        // Get the entity information
        if (null === $entityRow = $this->getDb()->tableEntities()->getById($fromEntityId)) {
            throw new Exception(__('Colonizer entity not found', 'stephino-rpg'));
        }
        
        // Validate the city
        if ($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CITY_ID] != $fromCityId) {
            throw new Exception(__('Colonizer entity not garrisoned', 'stephino-rpg'));
        }
        
        /* @var $entityConfig Stephino_Rpg_Config_Unit|Stephino_Rpg_Config_Ship */
        $entityConfig = Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT == $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE]
            ? Stephino_Rpg_Config::get()->units()->getById($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID])
            : Stephino_Rpg_Config::get()->ships()->getById($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]);
        
        // Not a colonizer
        if (null === $entityConfig || !$entityConfig->getCivilian() || !$entityConfig->getAbilityColonize()) {
            throw new Exception(__('This entity cannot colonize', 'stephino-rpg'));
        }
        
        // Prepare the entities
        $convoyEntities = array($fromEntityId => array(
                Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT     => 1,
                Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE      => $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE],
                Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID => $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]
            )
        );
        
        // Get the final travel information
        list($travelFast, $travelTime) = $this->getTravelInfo($fromInfo, $toInfo);
        
        // Add the convoy
        $convoyCreateResult = $this->getDb()->tableConvoys()->create(
            $fromInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_USER_ID], 
            $fromInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID], 
            $fromInfo[Stephino_Rpg_Db_Table_Cities::COL_ID], 
            0, 
            $toIslandId, 
            0,
            $colonizationTime + $travelTime,
            $travelFast,
            $colonizationTime + $travelTime + time(),
            0,
            array(
                Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES => $convoyEntities,
                Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_DATA     => array($toIslandId, $toIslandIndex),
            ),
            Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_COLONIZER
        );
        
        // DB Error
        if (null === $convoyCreateResult) {
            throw new Exception(__('Could not initiate the convoy', 'stephino-rpg'));
        }
        
        // Get the sanitized data
        list($newConvoyId, $convoyEntitiesSanitized) = $convoyCreateResult;
        
        // Move the troops out of the city
        $troopMovements = array();
        foreach ($convoyEntitiesSanitized as $fromEntityId => $entityRow) {
            $troopMovements[$fromEntityId] = -$entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT];
        }
        
        // Invalid troop movements
        if (false === $this->getDb()->modelEntities()->move($troopMovements)) {
            // Cancel the convoy
            $this->getDb()->tableConvoys()->deleteById($newConvoyId);
            throw new Exception(__('Could not update entities', 'stephino-rpg'));
        }
        
        return $newConvoyId;
    }
    
    /**
     * Create a transport convoy
     * 
     * @param int   $fromCityId          Origin City ID
     * @param int   $toCityId            Destination City ID
     * @param array $payloadTransporters Transporters payload
     * @param array $payloadEntities     Entities payload
     * @param array $payloadResources    Resources payload
     * @return int New Transport Convoy ID
     * @throws Exception
     */
    public function createTransport($fromCityId, $toCityId, $payloadTransporters, $payloadEntities, $payloadResources) {
        // Origin information
        if (!is_array($fromInfo = $this->getDb()->tableCities()->getById($fromCityId))) {
            throw new Exception(__('Origin not found', 'stephino-rpg'));
        }
        
        // Get the user informaiton
        if (!is_array($userInfo = $this->getDb()->tableUsers()->getById($fromInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_USER_ID]))) {
            throw new Exception(__('Origin user not found', 'stephino-rpg'));
        }
        
        // Destination information
        if (!is_array($toInfo = $this->getDb()->tableCities()->getById($toCityId))) {
            throw new Exception(__('Destination not found', 'stephino-rpg'));
        }
        
        // Invalid IDs
        if ($fromCityId == $toCityId) {
            throw new Exception(__('The origin must not be the same as the destination', 'stephino-rpg'));
        }
        
        // Invalid entities count
        if (!count($payloadTransporters)) {
            throw new Exception(__('Your convoy has no transporters', 'stephino-rpg'));
        }
        
        // Prepare the entire entities payload (keeping the keys)
        $convoyEntities = $payloadTransporters + $payloadEntities;
        
        // Get the final travel information
        list($travelFast, $travelDuration) = $this->getTravelInfo($fromInfo, $toInfo);
        
        // Add the convoy
        $convoyCreateResult = $this->getDb()->tableConvoys()->create(
            $fromInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_USER_ID], 
            $fromInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID], 
            $fromInfo[Stephino_Rpg_Db_Table_Cities::COL_ID], 
            $toInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_USER_ID], 
            $toInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID], 
            $toInfo[Stephino_Rpg_Db_Table_Cities::COL_ID],
            $travelDuration,
            $travelFast,
            $travelDuration + time(),
            0,
            array(
                Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES  => $convoyEntities,
                Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_RESOURCES => $payloadResources,
            ),
            Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_TRANSPORTER
        );
        
        // DB Error
        if (null === $convoyCreateResult) {
            throw new Exception(__('Could not initiate the convoy', 'stephino-rpg'));
        }
        
        // Get the sanitized data
        list($newConvoyId, $convoyEntitiesSanitized) = $convoyCreateResult;
        
        // Move the troops out of the city
        $troopMovements = array();
        foreach ($convoyEntitiesSanitized as $entityId => $entityRow) {
            $troopMovements[$entityId] = -$entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT];
        }
        
        // Invalid troop movements
        if (false === $this->getDb()->modelEntities()->move($troopMovements)) {
            // Cancel the convoy
            $this->getDb()->tableConvoys()->deleteById($newConvoyId);
            
            // Stop here
            throw new Exception(__('Could not update entities', 'stephino-rpg'));
        }
        
        return $newConvoyId;
    }
    
    /**
     * Create a spy convoy
     * 
     * @param int $fromCityId   Origin city ID
     * @param int $fromEntityId Origin Entity ID
     * @param int $toCityId     Destination City ID
     * @return int New Spy Convoy ID
     * @throws Exception
     */
    public function createSpy($fromCityId, $fromEntityId, $toCityId) {
        // Origin information
        if (!is_array($fromInfo = $this->getDb()->tableCities()->getById($fromCityId))) {
            throw new Exception(__('Origin not found', 'stephino-rpg'));
        }
        
        // Destination information
        if (!is_array($toInfo = $this->getDb()->tableCities()->getById($toCityId))) {
            throw new Exception(__('Destination not found', 'stephino-rpg'));
        }
        
        // Get the entity information
        if (null === $entityRow = $this->getDb()->tableEntities()->getById($fromEntityId)) {
            throw new Exception(__('Spy entity not found', 'stephino-rpg'));
        }
        
        // Validate the city
        if ($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CITY_ID] != $fromCityId) {
            throw new Exception(__('Spy entity not garrisoned', 'stephino-rpg'));
        }
        
        /* @var $entityConfig Stephino_Rpg_Config_Unit */
        $entityConfig = Stephino_Rpg_Config::get()->units()->getById(
            $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]
        );
        
        // Not a spy
        if (null === $entityConfig || !$entityConfig->getCivilian() || !$entityConfig->getAbilitySpy()) {
            throw new Exception(__('This entity cannot spy', 'stephino-rpg'));
        }
        
        // Entity cannot exist without a parent building
        if (null === $entityConfig->getBuilding()) {
            throw new Exception(
                sprintf(
                    __('Invalid configuration (%s)', 'stephino-rpg'),
                    Stephino_Rpg_Config::get()->core()->getConfigUnitName()
                )
            );
        }
        
        // Get the main building info
        $buildingRow = $this->getDb()->tableBuildings()->getByCityAndConfig(
            $fromCityId, 
            $entityConfig->getBuilding()->getId()
        );
        
        // Prepare the entities
        $convoyEntities = array($fromEntityId => array(
                Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT     => 1,
                Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE      => $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE],
                Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID => $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]
            )
        );
        
        // Get the final travel information
        list($travelFast, $travelDuration) = $this->getTravelInfo($fromInfo, $toInfo);

        // Prepare the success rate
        $spySuccessRate = Stephino_Rpg_Utils_Config::getPolyValue(
            $entityConfig->getSpySuccessRatePolynomial(), 
            null === $buildingRow 
                ? 1
                : $buildingRow[Stephino_Rpg_Db_Table_Buildings::COL_BUILDING_LEVEL], 
            $entityConfig->getSpySuccessRate()
        );
        
        // Limits
        if ($spySuccessRate < 1) {
            $spySuccessRate = 1;
        }
        if ($spySuccessRate > 100) {
            $spySuccessRate = 100;
        }
        
        // Add the convoy
        $convoyCreateResult = $this->getDb()->tableConvoys()->create(
            $fromInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_USER_ID], 
            $fromInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID], 
            $fromInfo[Stephino_Rpg_Db_Table_Cities::COL_ID], 
            $toInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_USER_ID], 
            $toInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID], 
            $toInfo[Stephino_Rpg_Db_Table_Cities::COL_ID],
            $travelDuration,
            $travelFast,
            $travelDuration + time(),
            0,
            array(
                Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_ENTITIES => $convoyEntities,
                Stephino_Rpg_TimeLapse_Convoys::PAYLOAD_DATA     => $spySuccessRate,
            ),
            Stephino_Rpg_Db_Table_Convoys::CONVOY_TYPE_SPY
        );
        
        // DB Error
        if (null === $convoyCreateResult) {
            throw new Exception(__('Could not initiate the convoy', 'stephino-rpg'));
        }
        
        // Get the sanitized data
        list($newConvoyId, $convoyEntitiesSanitized) = $convoyCreateResult;
        
        // Move the troops out of the city
        $troopMovements = array();
        foreach ($convoyEntitiesSanitized as $fromEntityId => $entityRow) {
            $troopMovements[$fromEntityId] = -$entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT];
        }
        
        // Invalid troop movements
        if (false === $this->getDb()->modelEntities()->move($troopMovements)) {
            // Cancel the convoy
            $this->getDb()->tableConvoys()->deleteById($newConvoyId);
            throw new Exception(__('Could not update entities', 'stephino-rpg'));
        }
        
        return $newConvoyId;
    }
    
    /**
     * Get the number of convoys this user has
     * 
     * @param int $userId User ID
     * @return int
     */
    public function getCount($userId) {
        return $this->getDb()->tableConvoys()->getCountByUser($userId);
    }
    
    /**
     * Delete convoys this user initiated (along with entities in the convoy payload)
     * 
     * @param int $userId User ID
     * @return int|false The number of rows deleted or false on error
     */
    public function deleteByUser($userId) {
        return $this->getDb()->tableConvoys()->deleteByUser($userId);
    }
    
    /**
     * Get the travel information (whether fast travel is allowed and the total duration)
     * 
     * @param array $fromInfo Origin city DB row [<ul>
     *     <li>Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID    => (int) Island ID</li>
     *     <li>Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_INDEX => (int) Island Index</li>
     * </ul>]
     * @param array $toInfo   Destination city DB row [<ul>
     *     <li>Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID    => (int) Island ID</li>
     *     <li>Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_INDEX => (int) Island Index</li>
     * </ul>]
     * @param array $entities (optional) Entities list [...[<ul>
     *     <li>Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE      => (string) Entity Type</li>
     *     <li>Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID => (int) Entity Configuration ID</li>
     *     <li>Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT     => (int) Entity Count</li>
     * </ul>]]; default <b>[]</b>
     * @return array [<ul>
     *     <li>(boolean) <b>Travel Fast</b></li>
     *     <li>(int) <b>Travel Duration in seconds</b></li>
     * </ul>]
     * @throws Exception
     */
    public function getTravelInfo($fromInfo, $toInfo, $entities = array()) {
        // Distribute units in ships
        $unitsMass = 0;
        $shipsCapacity = 0;
        
        // Non-instantaneous travel
        if (Stephino_Rpg_Config::get()->core()->getTravelTime() > 0) {
            foreach ($entities as $entityData) {
                if (!is_array($entityData)) {
                    continue;
                }

                if (Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT == $entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE]) {
                    // Get the unit configuration
                    $configUnit = Stephino_Rpg_Config::get()->units()->getById($entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]);

                    // Add the mass
                    if (null !== $configUnit) {
                        $unitsMass += ($entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT] * $configUnit->getTroopMass());
                    }
                } else {
                    // Get the ship configuration
                    $configShip = Stephino_Rpg_Config::get()->ships()->getById($entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID]);

                    // Add ship capacity
                    if (null !== $configShip) {
                        $shipsCapacity += ($entityData[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT] * $configShip->getTroopCapacity());
                    }
                }
            }
        }

        // Whether or not the army is traveling fast
        $travelFast = ($unitsMass <= $shipsCapacity);
        
        // Prepare the travel distance
        if ($fromInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID] == $toInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID]) {
            // Get the island information
            $islandInfo = $this->getDb()->tableIslands()->getById($fromInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID]);
            
            // Invalid island configuration
            if (null === $islandConfig = Stephino_Rpg_Config::get()->islands()->getById($islandInfo[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_CONFIG_ID])) {
                throw new Exception(
                    sprintf(
                        __('Invalid configuration (%s)', 'stephino-rpg'),
                        Stephino_Rpg_Config::get()->core()->getConfigIslandName()
                    )
                );
            }
            
            // Get the city slots size
            $islandCitySlots = (null !== $islandConfig->getCitySlots() ? json_decode($islandConfig->getCitySlots(), true) : null);
            
            // City slots not defined
            if (null === $islandCitySlots) {
                throw new Exception(
                    sprintf(
                        __('Invalid slots configuration (%s)', 'stephino-rpg'),
                        Stephino_Rpg_Config::get()->core()->getConfigCityName()
                    )
                );
            }
            
            // Get the total number of city slots
            $islandCities = count($islandCitySlots);
            
            // Get the indexes
            $attCityIndex = $fromInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_INDEX];
            $defCityIndex = $toInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_INDEX];
            
            // Assume equal distance between cities, all placed on a circle and no way to travel outside the circle
            $travelSegments = max(array(
                abs($attCityIndex - $defCityIndex),
                $islandCities - abs($attCityIndex - $defCityIndex)
            ));
        } else {
            // Get the attacker island coordinates
            $attIslandCoords = Stephino_Rpg_Utils_Math::getSnakePoint(
                intval($fromInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID])
            );
            $defIslandCoords = Stephino_Rpg_Utils_Math::getSnakePoint(
                intval($toInfo[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID])
            );
            
            // Each "segment" is 10 times longer than that on the island
            $travelSegments = round(
                // 10 times the Euclidean distance
                10 * sqrt(pow($attIslandCoords[0] - $defIslandCoords[0], 2) + pow($attIslandCoords[1] - $defIslandCoords[1], 2)), 
                0
            );
        }
        
        return array(
            $travelFast, 
            round(Stephino_Rpg_Config::get()->core()->getTravelTime() * $travelSegments / ($travelFast ? 2 : 1), 0)
        );
    }
    
    /**
     * Convert an army array to a valid entities payload
     * 
     * @param array $convoyArmy   Associative array of
     * <ul>
     *     <li>... (string) <b>"(s|u)_{Entity Config ID}"</b> => (int) <b>Entity Count</b></li>
     * </ul>
     * @param array $fromEntities Array of Stephino_Rpg_Db_Table_Entities Rows
     * @param int   $fromCityId   Origin city ID
     * @return array Associative array of <br/>
     * <i><b>int</b> Entiy ID</i> => [<ul>
     * <li>Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT     => <b>int</b> Entity Count</li>
     * <li>Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE      => <b>string</b> Entity Type</li>
     * <li>Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID => <b>int</b> Entity Configuration ID</li>
     * </ul>]
     */
    public function payloadFromEntities($convoyArmy, $fromEntities, $fromCityId) {
        // Prepare the payload
        $convoyPayload = array();
        
        // Go through the entities
        if (is_array($convoyArmy)) {
            foreach ($convoyArmy as $entitySerial => $entityCount) {
                $entityCount = abs((int) $entityCount);
                if ($entityCount > 0 && 2 == count($entityParts = explode('_', $entitySerial))) {
                    list($entityType, $entityConfigId) = $entityParts;

                    // Go through our entities
                    if (is_array($fromEntities)) {
                        foreach ($fromEntities as $entityRow) {
                            // Find the entity ID
                            if ($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CITY_ID] == $fromCityId
                                && $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE] == $entityType
                                && $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID] == $entityConfigId
                            ) {
                                // Assign as many as we currently have
                                if ($entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT] < $entityCount) {
                                    $entityCount = (int) $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT];
                                }
                                if ($entityCount <= 0) {
                                    continue;
                                }
                                
                                // Get the configuration object
                                $configObject = null;
                                switch ($entityType) {
                                    case Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_UNIT:
                                        $configObject = Stephino_Rpg_Config::get()->units()->getById($entityConfigId);
                                        break;

                                    case Stephino_Rpg_Db_Table_Entities::ENTITY_TYPE_SHIP:
                                        $configObject = Stephino_Rpg_Config::get()->ships()->getById($entityConfigId);
                                        break;
                                }

                                // Valid configuration object
                                if (null !== $configObject) {
                                    $convoyPayload[$entityRow[Stephino_Rpg_Db_Table_Entities::COL_ID]] = array(
                                        Stephino_Rpg_Db_Table_Entities::COL_ENTITY_COUNT     => $entityCount,
                                        Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE      => $entityRow[Stephino_Rpg_Db_Table_Entities::COL_ENTITY_TYPE],
                                        Stephino_Rpg_Db_Table_Entities::COL_ENTITY_CONFIG_ID => $entityConfigId
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return $convoyPayload;
    }
    
    /**
     * Validate a convoy's resources array
     * 
     * @param array $convoyResources Convoy Resources
     * @param array $fromResources   Origin Resources
     * @param int   $fromCityId      Origin City ID
     * @return array Associative array of [(int) <b>...Resource DB Key</b> => (int) <b>Resource Amount</b>]
     */
    public function payloadFromResources($convoyResources, $fromResources, $fromCityId) {
        // Prepare the payload
        $convoyPayload = array();
        
        // Prepare the resource descriptions
        $resourceDescriptions = Stephino_Rpg_Renderer_Ajax_Action::getResourceData();
        
        // Go through the entities
        if (is_array($convoyResources)) {
            foreach ($convoyResources as $resourceDbKey => $resourceAmount) {
                if ($resourceAmount > 0 && isset($resourceDescriptions[$resourceDbKey])) {
                    // Go through our resources
                    if (is_array($fromResources)) {
                        foreach ($fromResources as $dbRow) {
                            // Found our city
                            if ($dbRow[Stephino_Rpg_Db_Table_Cities::COL_ID] == $fromCityId) {
                                // Validate the quantity
                                if ($dbRow[$resourceDbKey] >= $resourceAmount) {
                                    $convoyPayload[$resourceDbKey] = intval($resourceAmount);
                                }
                                
                                // Stop here
                                break;
                            }
                        }
                    }
                }
            }
        }
        
        return $convoyPayload;
    }
}

/* EOF */