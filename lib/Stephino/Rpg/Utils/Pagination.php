<?php
/**
 * Stephino_Rpg_Utils_Pagination
 * 
 * @title     Utils:Pagination
 * @desc      Pagination utility
 * @copyright (c) 2021, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Utils_Pagination {

    /**
     * Pagination item action
     * 
     * @var string|null
     */
    protected $_action = null;
    
    /**
     * List radius
     * 
     * @var int
     */
    protected $_listRadius = 2;
    
    /**
     * Total number of items
     * 
     * @var int
     */
    protected $_itemsTotal = 0;
    
    /**
     * Items per page
     * 
     * @var int
     */
    protected $_itemsPerPage = 1;
    
    /**
     * Current page
     * 
     * @var int
     */
    protected $_pageCurrent = 1;
    
    /**
     * Total number of pages
     * 
     * @var int
     */
    protected $_pagesTotal = 0;
    
    /**
     * Pagination
     * 
     * @param int $itemsTotal   Total number of items
     * @param int $itemsPerPage (optional) Items per page, strictly larger than 0; default <b>1</b>
     * @param int $currentPage  (optional) Current page, strictly larger than 0; default <b>1</b>
     */
    public function __construct($itemsTotal, $itemsPerPage = 1, $currentPage = 1) {
        $this->setItemsTotal($itemsTotal)
            ->setItemsPerPage($itemsPerPage)
            ->setPageCurrent($currentPage);
    }
    
    /**
     * Stringify
     * 
     * @return string
     */
    public function __toString() {
        $data = array();
        
        // Go through the properties
        $reflection = new ReflectionClass($this);
        foreach ($reflection->getProperties() as /* @var $property ReflectionProperty */$property) {
            $propertyName = preg_replace('%^_%', '', $property->getName());
            $data[$propertyName] = $property->getValue();
        }
        
        return json_encode($data);
    }

    /**
     * Recalculate the total number of pages and the current page
     * 
     * @return Stephino_Rpg_Utils_Pagination
     */
    protected function _setPages() {
        $this->_pagesTotal = (int) ceil($this->_itemsTotal / $this->_itemsPerPage);
        if ($this->_pageCurrent > $this->_pagesTotal) {
            $this->_pageCurrent = $this->_pagesTotal;
        }
        
        return $this;
    }
    
    /**
     * Set the list radius around the current page
     * 
     * @param int $listRadius Current page radius, strictly larger than 0
     * @return Stephino_Rpg_Utils_Pagination
     */
    public function setListRadius($listRadius) {
        $this->_listRadius = abs((int) $listRadius);
        if ($this->_listRadius < 1) {
            $this->_listRadius = 1;
        }
        
        return $this;
    }
    
    /**
     * Get the list radius around the current page
     * 
     * @return int
     */
    public function getListRadius() {
        return $this->_listRadius;
    }
    
    /**
     * Get the pagination list
     * 
     * @return array Array of integers and NULL signifying ellipsis
     */
    public function getList() {
        $result = array();
        
        if ($this->getPageCurrent() > 0) {
            // Store the current page
            $result[] = $this->getPageCurrent();
            
            // Add the neighbors
            for ($i = 1; $i <= $this->getListRadius(); $i++) {
                if ($this->getPageCurrent() + $i <= $this->getPagesTotal()) {
                    $result[] = $this->getPageCurrent() + $i;
                }
                if ($this->getPageCurrent() - $i >= 1) {
                    array_unshift($result, $this->getPageCurrent() - $i);
                }
            }

            // List start
            if ($this->getPageCurrent() - $this->getListRadius() - 1 > 1) {
                array_unshift($result, 1 == $this->getPageCurrent() - $this->getListRadius() - 2 ? 2 : null);
                array_unshift($result, 1);
            } else {
                if (1 < current($result)) {
                    array_unshift($result, 1);
                }
            }
            
            // List end
            if ($this->getPagesTotal() - $this->getPageCurrent() - $this->getListRadius() > 1) {
                $result[] = (1 == $this->getPagesTotal() - $this->getPageCurrent() - $this->getListRadius() - 1 ? $this->getPagesTotal() - 1 : null);
                $result[] = $this->getPagesTotal();
            } else {
                if ($this->getPagesTotal() > end($result)) {
                    $result[] = $this->getPagesTotal();
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Get the SQL Limit Count argument
     * 
     * @return int
     */
    public function getSqlLimitCount() {
        return $this->getItemsPerPage();
    }
    
    /**
     * Get the SQL Limit Offset argument
     * 
     * @return int
     */
    public function getSqlLimitOffset() {
        return $this->getPageCurrent() > 0
            ? ($this->getPageCurrent() - 1) * $this->getItemsPerPage()
            : 0;
    }
    
    /**
     * Set the pagination item action
     * 
     * @param string $action Action string; only word characters permitted
     * @return Stephino_Rpg_Utils_Pagination
     */
    public function setAction($action) {
        $this->_action = trim(preg_replace('%\W+%', '', $action));
        if (!strlen($this->_action)) {
            $this->_action = null;
        }
        
        return $this;
    }
    
    /**
     * Get the pagination item action
     * 
     * @return string|null
     */
    public function getAction() {
        return $this->_action;
    }
    
    /**
     * Set the total number of items
     * 
     * @param int $itemsTotal Total number of items
     * @return Stephino_Rpg_Utils_Pagination
     */
    public function setItemsTotal($itemsTotal) {
        $this->_itemsTotal = abs((int) $itemsTotal);
        
        return $this->_setPages();
    }
    
    /**
     * Get the total number of items
     * 
     * @return int
     */
    public function getItemsTotal() {
        return $this->_itemsTotal;
    }
    
    /**
     * Set the number of items per page
     * 
     * @param int $itemsPerPage Items per page, strictly larger than 0
     * @return Stephino_Rpg_Utils_Pagination
     */
    public function setItemsPerPage($itemsPerPage) {
        $this->_itemsPerPage = abs((int) $itemsPerPage);
        if ($this->_itemsPerPage < 1) {
            $this->_itemsPerPage = 1;
        }
        
        return $this->_setPages();
    }
    
    /**
     * Get the number of items per page
     */
    public function getItemsPerPage() {
        return $this->_itemsPerPage;
    }
    
    /**
     * Set the current page
     * 
     * @param int $currentPage Current page, strictly larger than 0
     * @return Stephino_Rpg_Utils_Pagination
     */
    public function setPageCurrent($currentPage) {
        $this->_pageCurrent = abs((int) $currentPage);
        if ($this->_pageCurrent < 1) {
            $this->_pageCurrent = 1;
        }
        
        return $this->_setPages();
    }
    
    /**
     * Get the current page
     * 
     * @return int
     */
    public function getPageCurrent() {
        return $this->_pageCurrent;
    }
    
    /**
     * Get the total number of pages
     * 
     * @return int
     */
    public function getPagesTotal() {
        return $this->_pagesTotal;
    }
    
}

/* EOF */