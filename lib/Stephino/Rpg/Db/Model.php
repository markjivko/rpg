<?php
/**
 * Stephino_Rpg_Db_Model
 * 
 * @title     Model
 * @desc      Models handle the business logic, never directly altering the database
 * @copyright (c) 2021, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Db_Model {

    /**
     * Model Name
     */
    const NAME = '';
    
    /**
     * Stephino_Rpg_Db reference
     * 
     * @var Stephino_Rpg_Db
     */
    private $_db = null;

    /**
     * Model definition
     * 
     * @param Stephino_Rpg_Db $dbObject
     */
    public function __construct(Stephino_Rpg_Db $dbObject) {
        $this->_db = $dbObject;
    }
    
    /**
     * Get the DataBase object
     * 
     * @return Stephino_Rpg_Db
     */
    protected function getDb() {
        return $this->_db;
    }

}

/* EOF */