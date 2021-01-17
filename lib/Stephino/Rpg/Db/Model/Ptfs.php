<?php
/**
 * Stephino_Rpg_Db_Model_Platformers
 * 
 * @title     Model:Platformers
 * @desc      Platformers Model
 * @copyright (c) 2020, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Db_Model_Ptfs extends Stephino_Rpg_Db_Model {

    /**
     * Platformers Model Name
     */
    const NAME = 'ptfs';

    /**
     * Tile: side length in pixels
     */
    const TILE_SIDE           = 64;
    
    /**
     * Tile set: width in tiles
     */
    const TILE_SET_HORIZONTAL = 11;
    
    /**
     * Tile set: height in tiles
     */
    const TILE_SET_VERTICAL   = 4;
    
    // Platformer definition
    const PTF_DEF_NAME    = 'name';
    const PTF_DEF_VERSION = 'version';
    const PTF_DEF_WIDTH   = 'width';
    const PTF_DEF_HEIGHT  = 'height';
    
    // Extra columns
    const PTF_EXTRA_REWARD  = 'ptf_x_reward';
    const PTF_EXTRA_PREVIEW = 'ptf_x_preview';
    
    /**
     * Create a platformer with this author
     * 
     * @param int $userId User ID
     * @return int|null New Ptf ID or Null on error
     * @throws Exception
     */
    public function create($userId) {
        $userId = abs((int) $userId);
        if (0 === $userId) {
            throw new Exception(__('Invalid author ID', 'stephino-rpg'));
        }
        
        // Total platformers limit
        if (Stephino_Rpg_Config::get()->core()->getPtfAuthorLimit() > 0) {
            $authorPlatformers = Stephino_Rpg_Db::get()->tablePtfs()->getByUserId($userId);
            if (count($authorPlatformers) >= Stephino_Rpg_Config::get()->core()->getPtfAuthorLimit()) {
                throw new Exception(__('You cannot create more games', 'stephino-rpg'));
            }
        }
        
        // Prepare the platformer name
        $ptfName = $this->getDb()->modelIslands()->generateName(mt_rand(-100, 100), mt_rand(-100, 100));
        if (strlen($ptfName) < 3 || $ptfName > 64) {
            $ptfName = md5(mt_rand(1, 999));
        }
        
        // Compressed tile set
        $tileSetC = Stephino_Rpg_Utils_Math::getIntListZip(
            array_fill(
                0, 
                Stephino_Rpg_Db_Table_Ptfs::PTF_MIN_WIDTH * Stephino_Rpg_Db_Table_Ptfs::PTF_MIN_HEIGHT, 
                1
            )
        );
            
        // Sanitize the platformer definition and tile set
        $ptfDef = $this->sanitize(
            array(
                self::PTF_DEF_VERSION => 1,
                self::PTF_DEF_NAME    => $ptfName,
                self::PTF_DEF_WIDTH   => Stephino_Rpg_Db_Table_Ptfs::PTF_MIN_WIDTH,
                self::PTF_DEF_HEIGHT  => Stephino_Rpg_Db_Table_Ptfs::PTF_MIN_HEIGHT,
            ), 
            $tileSetC
        );
        
        // Get the platformer ID
        return $this->getDb()->tablePtfs()->create(
            $userId, 
            $ptfDef[self::PTF_DEF_NAME], 
            $ptfDef[self::PTF_DEF_WIDTH], 
            $ptfDef[self::PTF_DEF_HEIGHT], 
            $tileSetC
        );
    }
    
    /**
     * Mark the game as started of finished, updating the corresponding fields in both <b>tablePtf</b> and <b>tablePtfPlays</b>
     * 
     * @param int     $ptfId    Platformer Id
     * @param int     $userId   Player Id
     * @param boolean $ptfStart (optional) Start/Finish; default <b>true</b>
     * @param boolean $ptfWon   (optional) When finishing a game, mark it as a win; default <b>false</b>
     * @throws Exception
     */
    public function play($ptfId, $userId, $ptfStart = true, $ptfWon = false) {
        if (is_array($ptfRow = $this->getDb()->tablePtfs()->getById($ptfId))) {
            $currentTime = time();
            
            // Get the plays row
            $ptfPlaysRow = $this->getDb()->tablePtfPlays()->getByUserAndPtf($userId, $ptfId);
            
            // User stats
            if (!is_array($ptfPlaysRow) && !$ptfStart) {
                throw new Exception(__('Cannot finish this game', 'stephino-rpg'));
            }
            
            if (is_array($ptfPlaysRow)) {
                // Update the existing play
                $ptfPlaysUpdate = $ptfStart
                    ? array(
                        Stephino_Rpg_Db_Table_PtfPlays::COL_PTF_PLAY_STARTED      => $ptfPlaysRow[Stephino_Rpg_Db_Table_PtfPlays::COL_PTF_PLAY_STARTED] + 1,
                        Stephino_Rpg_Db_Table_PtfPlays::COL_PTF_PLAY_STARTED_TIME => $currentTime,
                    )
                    : array(
                        Stephino_Rpg_Db_Table_PtfPlays::COL_PTF_PLAY_FINISHED => $ptfPlaysRow[Stephino_Rpg_Db_Table_PtfPlays::COL_PTF_PLAY_FINISHED] + 1,
                    );
                
                // Mark the victory
                if (!$ptfStart && $ptfWon) {
                    $ptfPlaysUpdate[Stephino_Rpg_Db_Table_PtfPlays::COL_PTF_PLAY_WON] = $ptfPlaysRow[Stephino_Rpg_Db_Table_PtfPlays::COL_PTF_PLAY_WON] + 1;
                    $ptfPlaysUpdate[Stephino_Rpg_Db_Table_PtfPlays::COL_PTF_PLAY_WON_TIME] = $currentTime;
                }
                
                // Update the play
                $this->getDb()->tablePtfPlays()->updateById(
                    $ptfPlaysUpdate, 
                    $ptfPlaysRow[Stephino_Rpg_Db_Table_PtfPlays::COL_ID]
                );
            } else {
                // Start a new play
                $this->getDb()->tablePtfPlays()->create($ptfId, $userId, $currentTime);
            }
            
            // Update the platformer
            $ptfUpdate = $ptfStart
                ? array(
                    Stephino_Rpg_Db_Table_Ptfs::COL_PTF_STARTED => $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_STARTED] + 1,
                )
                : array(
                    Stephino_Rpg_Db_Table_Ptfs::COL_PTF_FINISHED => $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_FINISHED] + 1,
                );
            
            // Mark the victory
            if (!$ptfStart && $ptfWon) {
                $ptfUpdate[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_FINISHED_WON] = $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_FINISHED_WON] + 1;
            }
            
            // Update the play
            $this->getDb()->tablePtfs()->updateById($ptfUpdate, $ptfId);
        }
    }
    
    /**
     * Reward a user; if <b>ptfId</b> and <b>playerId</b> are both set, the user is considered an author and a diplomacy message is sent
     * 
     * @param int $userId     User ID
     * @param int $rewardGems Gems reward
     * @param int $ptfId      (optional) Platformer ID; when specified, send a message to the user (author royalties); default <b>null</b>
     * @param int $playerId   (optional) Player ID; used for the royalties message; default <b>null</b>
     */
    public function reward($userId, $rewardGems, $ptfId = null, $playerId = null) {
        $userId = abs((int) $userId);
        $rewardGems = abs((int) $rewardGems);
        
        // Valid user ID
        if ($userId > 0 && $rewardGems > 0) {
            $userRow = $this->getDb()->tableUsers()->getById($userId);
            if (is_array($userRow)) {
                // Get the current gems count
                $userGems = (int) $userRow[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM];
                
                do {
                    // Author royalties
                    if (null !== $ptfId && null !== $playerId) {
                        $playerId = abs((int) $playerId);
                        
                        // Don't send royalties to yourself
                        if ($userId === $playerId) {
                            break;
                        }
                        
                        // Valid game
                        if (is_array($ptfRow = $this->getDb()->tablePtfs()->getById($ptfId))) {
                            // Get the player name
                            $playerName = Stephino_Rpg_Utils_Lingo::getUserName(
                                $this->getDb()->tableUsers()->getById($playerId)
                            );

                            // Send the notification
                            $this->getDb()->modelMessages()->sendNotification(
                                $userId, 
                                esc_html__('Royalties', 'stephino-rpg'), 
                                Stephino_Rpg_TimeLapse::TEMPLATE_NOTIF_PTF_AUTHOR_REWARD,
                                array($ptfRow, $playerId, $playerName, $rewardGems)
                            );
                        }
                    }
                    
                    // Add the reward
                    $this->getDb()->tableUsers()->updateById(
                        array(
                            Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM => $userGems + $rewardGems
                        ), 
                        $userId
                    );
                } while(false);
            }
        }
    }
    
    /**
     * Sanitize the platformer definition for the provide tile set
     * 
     * @param array   $ptfDef    Associative array containing the following keys:<ul>
     * <li><b>Stephino_Rpg_Db_Model_Platformers::PTF_DEF_NAME</b>    => (string) Platformer name [3,64] characters long</li>
     * <li><b>Stephino_Rpg_Db_Model_Platformers::PTF_DEF_VERSION</b> => (int) Platformer version [1,]</li>
     * <li><b>Stephino_Rpg_Db_Model_Platformers::PTF_DEF_WIDTH</b>   => (int) Platformer width [Stephino_Rpg_Db_Table_Ptfs::PTF_MIN_WIDTH,Stephino_Rpg_Db_Table_Ptfs::PTF_MAX_WIDTH]</li>
     * <li><b>Stephino_Rpg_Db_Model_Platformers::PTF_DEF_HEIGHT</b>  => (int) Platformer height [Stephino_Rpg_Db_Table_Ptfs::PTF_MIN_HEIGHT,Stephino_Rpg_Db_Table_Ptfs::PTF_MAX_HEIGHT]</li>
     * </ul>
     * @param array   $tileSetC  <b>Compressed</b> unassociative array of integers
     * @param boolean $checkGate (optional) Fail if the gate tile was not placed; default <b>false</b>
     * @return array Sanitized platformer definition
     * @throws Exception
     */
    public function sanitize($ptfDef, $tileSetC, $checkGate = false) {
        // Pre-validate the array
        if (!is_array($ptfDef) 
            || !isset($ptfDef[self::PTF_DEF_NAME])
            || !isset($ptfDef[self::PTF_DEF_VERSION])
            || !isset($ptfDef[self::PTF_DEF_WIDTH])
            || !isset($ptfDef[self::PTF_DEF_HEIGHT])) {
            throw new Exception(__('Incomplete definition', 'stephino-rpg'));
        }

        // Validate platformer name
        $preDefPtfName = trim(preg_replace('%[^\w\- ]+%i', '', $ptfDef[self::PTF_DEF_NAME]));
        if (strlen($preDefPtfName) < 3 || strlen($preDefPtfName) > 64) {
            throw new Exception(__('Name must be between 3 and 64 characters long', 'stephino-rpg'));
        }

        // Validate version
        $preDefPtfVersion = abs((int) $ptfDef[self::PTF_DEF_VERSION]);
        if ($preDefPtfVersion < 1) {
            throw new Exception(__('Version must be greater than 1', 'stephino-rpg'));
        }

        // Validate width
        $preDefPtfWidth  = abs((int) $ptfDef[self::PTF_DEF_WIDTH]);
        if ($preDefPtfWidth < Stephino_Rpg_Db_Table_Ptfs::PTF_MIN_WIDTH) {
            throw new Exception(
                sprintf(
                    __('Width must be greater than %d', 'stephino-rpg'),
                    Stephino_Rpg_Db_Table_Ptfs::PTF_MIN_WIDTH
                )
            );
        }
        
        // Validate height
        $preDefPtfHeight = abs((int) $ptfDef[self::PTF_DEF_HEIGHT]);
        if ($preDefPtfHeight < Stephino_Rpg_Db_Table_Ptfs::PTF_MIN_HEIGHT) {
            throw new Exception(
                sprintf(
                    __('Height must be greater than %d', 'stephino-rpg'),
                    Stephino_Rpg_Db_Table_Ptfs::PTF_MIN_HEIGHT
                )
            );
        }

        // Validate the tile data
        if (!is_array($tileSetC)) {
            throw new Exception(__('Invalid tile set data', 'stephino-rpg'));
        }
        
        // Decompress the tile set
        $tileSetC = Stephino_Rpg_Utils_Math::getIntListZip($tileSetC, false);
        
        // Sanitize the tile data; tile IDs must be between 1 and W*H
        $tileSetC = array_values(array_filter(array_map('intval', $tileSetC), function($pieceId) {
            return $pieceId >= 1 && $pieceId <= self::TILE_SET_HORIZONTAL * self::TILE_SET_VERTICAL;
        }));
        
        // No gate tile placed
        if ($checkGate && !in_array(20, $tileSetC)) {
            throw new Exception(__('You must place at least one gate', 'stephino-rpg'));
        }

        // Invalid number of pieces
        if (count($tileSetC) !== $preDefPtfWidth * $preDefPtfHeight) {
            throw new Exception(
                sprintf(
                    __('Invalid tile set size (%d instead of %d)', 'stephino-rpg'),
                    count($tileSetC),
                    $preDefPtfWidth * $preDefPtfHeight
                )
            );
        }
        
        return array(
            self::PTF_DEF_NAME    => $preDefPtfName,
            self::PTF_DEF_VERSION => $preDefPtfVersion,
            self::PTF_DEF_WIDTH   => $preDefPtfWidth,
            self::PTF_DEF_HEIGHT  => $preDefPtfHeight,
        );
    }
    
    /**
     * Store/update the references to pre-defined platformers in the database<br/>
     * Performs multi-updates, multi-inserts and multi-deletes as necessary
     */
    public function reload() {
        do {
            // Get the platformers definition
            $preDefPlatformersData = null;
            if (is_file($ptfsDataPath = STEPHINO_RPG_ROOT . '/ui/js/ptf/' . Stephino_Rpg_Renderer_Ajax::FILE_PTF_LIST . '.json')) {
                $preDefPlatformersData = @json_decode(file_get_contents($ptfsDataPath), true);
            }
            if (!is_array($preDefPlatformersData)) {
                Stephino_Rpg_Log::warning(Stephino_Rpg_Renderer_Ajax::FILE_PTF_LIST . '.json file is not a valid associative array');
                break;
            }
            
            // Clean-up the platformers data
            foreach ($preDefPlatformersData as $preDefPtfId => $preDefPtfDef) {
                try {
                    // No definition found
                    if (!is_file($preDefPtfPath = STEPHINO_RPG_ROOT . '/ui/js/ptf/' . Stephino_Rpg_Renderer_Ajax::FILE_PTF_LIST . '/' . intval($preDefPtfId) . '.json')) {
                        throw new Exception('JSON data file missing');
                    }
                    
                    // Sanitize the data
                    $preDefPlatformersData[$preDefPtfId] = $this->sanitize(
                        $preDefPtfDef, 
                        @json_decode(file_get_contents($preDefPtfPath), true)
                    );
                } catch (Exception $exc) {
                    Stephino_Rpg_Log::warning($exc->getMessage() . ' (#' . intval($preDefPtfId) . ')');
                    unset($preDefPlatformersData[$preDefPtfId]);
                }
            }
            
            // Prepare the DB changes
            $dbInserts = array();
            $dbUpdates = array();
            $dbDeletes = array();
            $timestamp = time();
            
            // Store {pre-defined platformer ID} => {database ID} association from the database
            $dbPreDefPtfIds = array();
            
            // Store {pre-defined platformer ID} => {database version} association from the database
            $dbPreDefPtfVer = array();
            
            // Get the pre-defined platformers from the database
            $dbPreDefPlatformers = $this->getDb()->tablePtfs()->getAll(true);
            foreach ($dbPreDefPlatformers as $dbRow) {
                // Sanitize the pre-defined platformer ID
                $dbPreDefPtfId = abs((int) $dbRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_CONTENT]);
                
                // Store the database ID
                $dbPreDefPtfIds[$dbPreDefPtfId] = (int) $dbRow[Stephino_Rpg_Db_Table_Ptfs::COL_ID];
                $dbPreDefPtfVer[$dbPreDefPtfId] = (int) $dbRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_VERSION];
                
                // DB platformer removed from the pre-defined list or invalid content
                if (!isset($preDefPlatformersData[$dbPreDefPtfId])) {
                    $dbDeletes[] = (int) $dbPreDefPtfIds[$dbPreDefPtfId];
                }
            }
            
            // Parse the changes
            foreach ($preDefPlatformersData as $preDefPtfId => $preDefPtfDef) {
                // Sanitize the platformer ID
                $preDefPtfId = (int) $preDefPtfId;
                
                // New platformer
                if (!isset($dbPreDefPtfIds[$preDefPtfId])) {
                    $dbInserts[] = array(
                        Stephino_Rpg_Db_Table_Ptfs::COL_PTF_USER_ID       => 0,
                        Stephino_Rpg_Db_Table_Ptfs::COL_PTF_CONTENT       => $preDefPtfId,
                        Stephino_Rpg_Db_Table_Ptfs::COL_PTF_WIDTH         => $preDefPtfDef[self::PTF_DEF_WIDTH],
                        Stephino_Rpg_Db_Table_Ptfs::COL_PTF_HEIGHT        => $preDefPtfDef[self::PTF_DEF_HEIGHT],
                        Stephino_Rpg_Db_Table_Ptfs::COL_PTF_NAME          => $preDefPtfDef[self::PTF_DEF_NAME],
                        Stephino_Rpg_Db_Table_Ptfs::COL_PTF_VERSION       => $preDefPtfDef[self::PTF_DEF_VERSION],
                        Stephino_Rpg_Db_Table_Ptfs::COL_PTF_CREATED_TIME  => $timestamp,
                        Stephino_Rpg_Db_Table_Ptfs::COL_PTF_MODIFIED_TIME => $timestamp,
                        Stephino_Rpg_Db_Table_Ptfs::COL_PTF_VISIBILITY    => Stephino_Rpg_Db_Table_Ptfs::PTF_VISIBILITY_PUBLIC,
                    );
                } else {
                    // Version change
                    if ($dbPreDefPtfVer[$preDefPtfId] != $preDefPtfDef[self::PTF_DEF_VERSION]) {
                        $dbUpdates[$dbPreDefPtfIds[$preDefPtfId]] = array(
                            Stephino_Rpg_Db_Table_Ptfs::COL_PTF_WIDTH         => $preDefPtfDef[self::PTF_DEF_WIDTH],
                            Stephino_Rpg_Db_Table_Ptfs::COL_PTF_HEIGHT        => $preDefPtfDef[self::PTF_DEF_HEIGHT],
                            Stephino_Rpg_Db_Table_Ptfs::COL_PTF_NAME          => $preDefPtfDef[self::PTF_DEF_NAME],
                            Stephino_Rpg_Db_Table_Ptfs::COL_PTF_VERSION       => $preDefPtfDef[self::PTF_DEF_VERSION],
                            Stephino_Rpg_Db_Table_Ptfs::COL_PTF_MODIFIED_TIME => $timestamp,
                            Stephino_Rpg_Db_Table_Ptfs::COL_PTF_VISIBILITY    => Stephino_Rpg_Db_Table_Ptfs::PTF_VISIBILITY_PUBLIC,
                        );
                    }
                }
            }
            
            // Execute the queries
            if (count($dbInserts)) {
                if (null !== $multiInsertQuery = Stephino_Rpg_Utils_Db::getMultiInsert(
                    $dbInserts, 
                    $this->getDb()->tablePtfs()->getTableName()
                )) {
                    $this->getDb()->getWpDb()->query($multiInsertQuery);
                }
            }
            if (count($dbUpdates)) {
                if (null !== $multiUpdateQuery = Stephino_Rpg_Utils_Db::getMultiUpdate(
                    $dbUpdates, 
                    $this->getDb()->tablePtfs()->getTableName(), 
                    Stephino_Rpg_Db_Table_Ptfs::COL_ID
                )) {
                    $this->getDb()->getWpDb()->query($multiUpdateQuery);
                }
            }
            if (count($dbDeletes)) {
                if (null !== $multiDeleteQuery = Stephino_Rpg_Utils_Db::getMultiDelete(
                    $dbDeletes, 
                    $this->getDb()->tablePtfs()->getTableName(), 
                    Stephino_Rpg_Db_Table_Ptfs::COL_ID
                )) {
                    $this->getDb()->getWpDb()->query($multiDeleteQuery);
                }
            }
        } while(false);
    }
    
    /**
     * Get the list of available tiles, i18n ready and HTML escaped
     * 
     * @return array List of tiles as {tile ID} => [{tile name}, {tile description}]
     */
    public function getTileList() {
        return array(
            1 => array(
                esc_html__('Void', 'stephino-rpg'),
                esc_html__('An empty tile', 'stephino-rpg'),
            ),
            8 => array(
                esc_html__('Pipe', 'stephino-rpg'),
                esc_html__('A solid structure to stand on', 'stephino-rpg')
            ),
            18 => array(
                esc_html__('Crystal', 'stephino-rpg'),
                esc_html__('A rare item that must be collected to open the gate', 'stephino-rpg')
            ),
            19 => array(
                esc_html__('Bomb', 'stephino-rpg'),
                esc_html__('Instant death', 'stephino-rpg')
            ),
            20 => array(
                esc_html__('Gate', 'stephino-rpg'),
                esc_html__('Escape the current level with your loot', 'stephino-rpg')
            ),
        );
    }
    
    /**
     * Get the sanitized tile set from the specified platformer row
     * 
     * @param array   $ptfRow  Platformer DB Row
     * @param boolean $preview (optional) Preview Mode: get the top-left square; default <b>false</b>
     * @param boolean $compressed (optional) Compress the tile set; default <b>false</b>
     * @return array|null
     */
    public function getTileSet($ptfRow, $preview = false, $compressed = false) {
        // Prepare the tile data
        $tileSet = null;

        // This is a pre-defined platformer
        if (0 === intval($ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_USER_ID])) {
            // Prepare the file name
            $preDefPtfId = abs((int) $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_CONTENT]) ;

            // Store the enclosed data
            if (is_file($preDefPtfPath = STEPHINO_RPG_ROOT . '/ui/js/ptf/' . Stephino_Rpg_Renderer_Ajax::FILE_PTF_LIST . '/' . $preDefPtfId . '.json')) {
                $tileSet = @json_decode(file_get_contents($preDefPtfPath), true);
            }
        } else {
            $tileSet = @json_decode($ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_CONTENT], true);
        }
        
        // Sanitize the content; pieces must be between 1 and W*H
        if (is_array($tileSet)) {
            // Decomporess
            $tileSet = Stephino_Rpg_Utils_Math::getIntListZip($tileSet, false);
            
            // Cast to int and validate range
            $tileSet = array_values(array_filter(array_map('intval', $tileSet), function($pieceId) {
                return $pieceId >= 1 && $pieceId <= self::TILE_SET_HORIZONTAL * self::TILE_SET_VERTICAL;
            }));
            
            // Preview mode
            if ($preview) {
                // Prepare the square preview
                $previewTileSet = array();
                
                // Get the current platformer width
                $ptfWidth  = (int) $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_WIDTH];
                
                // Only keep the top-left square
                for ($i = 0; $i < Stephino_Rpg_Db_Table_Ptfs::PTF_MIN_HEIGHT; $i++) {
                    for ($j = 0; $j < Stephino_Rpg_Db_Table_Ptfs::PTF_MIN_HEIGHT; $j++) {
                        $previewTileSet[] = $tileSet[$i * $ptfWidth + $j];
                    }
                }
                $tileSet = $previewTileSet;
            }
            
            // Compress
            if ($compressed) {
                $tileSet = Stephino_Rpg_Utils_Math::getIntListZip($tileSet);
            }
        }
        
        return $tileSet;
    }
    
    /**
     * Get the platformer tile map as expected by the Phaser script<br/>
     * Tile data is sanitized and validated
     * 
     * @param int $ptfId Platformer ID
     * @return array|null
     */
    public function getTileMap($ptfId) {
        $ptfTileMap = null;
        
        do {
            // Get the platformer data
            if (!is_array($ptfRow = Stephino_Rpg_Db::get()->tablePtfs()->getById($ptfId))) {
                break;
            }
            
            // Preliminary check
            if (!is_array($tileSet = $this->getTileSet($ptfRow))) {
                break;
            }
            
            // Sanitize the platformer width and height
            $ptfWidth  = (int) $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_WIDTH];
            $ptfHeight = (int) $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_HEIGHT];

            // Invalid number of pieces
            if (count($tileSet) !== $ptfWidth * $ptfHeight) {
                break;
            }
            
            // Prepare the final platformer configuration array
            $ptfTileMap = array(
                'version'     => (int) $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_VERSION],
                'width'       => $ptfWidth,
                'height'      => $ptfHeight,
                'orientation' => 'orthogonal',
                'tilewidth'   => self::TILE_SIDE,
                'tileheight'  => self::TILE_SIDE,
                'layers'      => array(
                    array(
                        'name'    => 'stephino-rpg-layer',
                        'data'    => $tileSet,
                        'width'   => $ptfWidth,
                        'height'  => $ptfHeight,
                        'opacity' => 1,
                        'type'    => 'tilelayer',
                        'visible' => true,
                        'x'       => 0,
                        'y'       => 0,
                    )
                ),
                'tilesets'    => array(
                    array(
                        'name'        => 'stephino-rpg-tiles',
                        'image'       => Stephino_Rpg_Utils_Media::getPluginsUrl() . '/themes/' 
                            . Stephino_Rpg_Config::get()->core()->getTheme() . '/img/ui/ptf-tiles.png?ver=' . Stephino_Rpg::PLUGIN_VERSION,
                        'imagewidth'  => self::TILE_SIDE * self::TILE_SET_HORIZONTAL,
                        'imageheight' => self::TILE_SIDE * self::TILE_SET_VERTICAL,
                        'firstgid'    => 1,
                        'margin'      => 0,
                        'spacing'     => 0,
                        'tilewidth'   => self::TILE_SIDE,
                        'tileheight'  => self::TILE_SIDE,
                    )
                )
            );
        } while(false);
        
        return $ptfTileMap;
    }
    
    /**
     * Get the view box size in pixels
     * 
     * @param boolean $getString (optional) Get the result as a string "{width} {height}"; default <b>false</b>
     * @return int[]|string array({width}, {height}) OR "{width} {height}"
     */
    public function getViewBox($getString = false) {
        $viewBox = array(
            self::TILE_SIDE * Stephino_Rpg_Db_Table_Ptfs::PTF_MIN_WIDTH,
            self::TILE_SIDE * Stephino_Rpg_Db_Table_Ptfs::PTF_MIN_HEIGHT,
        );
        return $getString ? implode(' ', $viewBox) : $viewBox;
    }

    /**
     * Get the next platformer ID
     * 
     * @param int $userId User ID
     * @param int $ptfId  Platformer ID
     * @return int|null Null if no results found
     */
    public function getNextId($userId, $ptfId) {
        $result = null;
        
        // Get the platformers for this user
        $ptfs = $this->getDb()->tablePtfs()->getForUserId($userId);
        
        do {
            // Nothing found
            if (!count($ptfs)) {
                break;
            }

            // Get the IDs only
            $ptfIds = array_values(
                array_map(
                    function($item) {
                        return (int) $item[Stephino_Rpg_Db_Table_Ptfs::COL_ID];
                    }, 
                    $ptfs
                )
            );

            // Get the current game key
            $ptfKey = array_search($ptfId, $ptfIds);

            // Next
            $newPtfKey = (int) $ptfKey + 1;

            // Key not found or reached the end
            if (false === $ptfKey || $newPtfKey >= count($ptfIds)) {
                $newPtfKey = 0;
            }
            
            $result = $ptfIds[$newPtfKey];
        } while(false);
        
        return $result;
    }
    
    /**
     * Get all the platformers this user has access to (own or public platformers) supplemented with the following columns:<ul>
     * <li>self::PTF_EXTRA_PREVIEW => <b>(int[])</b> Top-left square tile set used for preview</li>
     * <li>self::PTF_EXTRA_REWARD  => <b>(int)</b> Reward in gems for finishing this platformer</li>
     * </ul>
     * 
     * @param int $userId User ID
     * @return array List of platformers; array may be empty
     */
    public function getForUserId($userId) {
        // Get the platformers win times
        $ptfsWon = array();
        foreach($this->getDb()->tablePtfPlays()->getByUserId($userId) as $ptfPlayRow) {
            $ptfWonTime = (int) $ptfPlayRow[Stephino_Rpg_Db_Table_PtfPlays::COL_PTF_PLAY_WON_TIME];
            if ($ptfWonTime > 0) {
                $ptfId = (int) $ptfPlayRow[Stephino_Rpg_Db_Table_PtfPlays::COL_PTF_PLAY_PTF_ID];
                $ptfsWon[$ptfId] = $ptfWonTime;
            }
        }
        
        // Prepare the current timestamp
        $currentTime = time();
        
        // Get the platformers list
        $ptfsList = $this->getDb()->tablePtfs()->getForUserId($userId);
        
        // Prepare the available rewards
        foreach ($ptfsList as $dbKey => &$ptfRow) {
            // Prepare the tile set
            if (!is_array($tileSet = $this->getTileSet($ptfRow, true, true))) {
                unset($ptfsList[$dbKey]);
                continue;
            }
            
            // Store the tile set
            $ptfRow[self::PTF_EXTRA_PREVIEW] = $tileSet;
            
            // Prepare the reward
            $ptfRow[self::PTF_EXTRA_REWARD] = 0;
            
            // Get the platformer ID
            $ptfId = (int) $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_ID];
            
            // Reward reset
            $resetSeconds = 3600 * Stephino_Rpg_Config::get()->core()->getPtfRewardResetHours();
            
            // First time or enough time has passed
            if (!isset($ptfsWon[$ptfId]) || ($resetSeconds > 0 && $currentTime - $ptfsWon[$ptfId] >= $resetSeconds)) {
                $ptfRow[self::PTF_EXTRA_REWARD] = Stephino_Rpg_Config::get()->core()->getPtfRewardPlayer();
            }
        }
        return $ptfsList;
    }
    
    /**
     * Calculate the rewards for this player and the author when winning a platformer
     * 
     * @param int $userId User ID
     * @param int $ptfId  Platformer ID
     * @return int[] Array of 2 values, reward for the player and reward for the author
     */
    public function getRewards($userId, $ptfId) {
        $result = array(0,0);
        
        // Prepare the current timestamp
        $currentTime = time();
        
        // Get the play entrt
        if (is_array($ptfPlay = $this->getDb()->tablePtfPlays()->getByUserAndPtf($userId, $ptfId))) {
            // Get the win time
            $ptfWon = (int) $ptfPlay[Stephino_Rpg_Db_Table_PtfPlays::COL_PTF_PLAY_WON_TIME];

            // Reward reset
            $resetSeconds = 3600 * Stephino_Rpg_Config::get()->core()->getPtfRewardResetHours();

            // First time or enough time has passed
            if (0 === $ptfWon || ($resetSeconds > 0 && $currentTime - $ptfWon >= $resetSeconds)) {
                $result = array(
                    Stephino_Rpg_Config::get()->core()->getPtfRewardPlayer(),
                    Stephino_Rpg_Config::get()->core()->getPtfRewardAuthor(),
                );
            }
        }
        return $result;
    }
}

/* EOF */