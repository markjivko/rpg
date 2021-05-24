<?php

/**
 * Stephino_Zip_Option_File
 * 
 * @title      Zip
 * @desc       Zip utility: File options
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
final class Stephino_Zip_Option_File {

    /**
     * File comment
     * 
     * @var string
     */
    protected $_comment = '';

    /**
     * @var Stephino_Zip_Enum_Method
     */
    protected $_method;

    /**
     * @var int
     */
    protected $_deflateLevel;

    /**
     * @var DateTime
     */
    protected $_time;

    /**
     * @var int
     */
    protected $_size = 0;

    /**
     * Set the deflate level and time from the archive options
     * 
     * @param Stephino_Zip_Option_Archive $archiveOptions
     * @return Stephino_Zip_Option_File
     */
    public function defaultTo(Stephino_Zip_Option_Archive $archiveOptions) {
        $this->_deflateLevel = $this->_deflateLevel ?: $archiveOptions->getDeflateLevel();
        $this->_time = $this->_time ?: new DateTime();
        return $this;
    }

    /**
     * Get file comment
     * 
     * @return string
     */
    public function getComment() {
        return $this->_comment;
    }

    /**
     * Set file comment comment
     * 
     * @param string $comment
     * @return Stephino_Zip_Option_File
     */
    public function setComment($comment) {
        $this->_comment = (string) $comment;
        return $this;
    }

    /**
     * Get the compression method
     * 
     * @return Stephino_Zip_Enum_Method
     */
    public function getMethod() {
        return $this->_method ?: Stephino_Zip_Enum_Method::DEFLATE();
    }

    /**
     * Set the compression method
     * 
     * @param Stephino_Zip_Enum_Method $method
     */
    public function setMethod(Stephino_Zip_Enum_Method $method) {
        $this->_method = $method;
        return $this;
    }

    /**
     * Get the deflate level
     * 
     * @return int
     */
    public function getDeflateLevel() {
        return $this->_deflateLevel ?: Stephino_Zip_Option_Archive::DEFAULT_DEFLATE_LEVEL;
    }

    /**
     * Set the deflate level
     * 
     * @param int $deflateLevel
     * @return Stephino_Zip_Option_File
     */
    public function setDeflateLevel($deflateLevel) {
        $this->_deflateLevel = (int) $deflateLevel;
        return $this;
    }

    /**
     * Get file time
     * 
     * @return DateTime
     */
    public function getTime() {
        return $this->_time;
    }

    /**
     * Set file time
     * 
     * @param DateTime $time
     * @return Stephino_Zip_Option_File
     */
    public function setTime(DateTime $time) {
        $this->_time = $time;
        return $this;
    }

    /**
     * Get file size
     * 
     * @return int
     */
    public function getSize() {
        return $this->_size;
    }

    /**
     * Set file size
     * 
     * @param int $size
     * @return Stephino_Zip_Option_File
     */
    public function setSize($size) {
        $this->_size = (int) $size;
        return $this;
    }

}

/* EOF */