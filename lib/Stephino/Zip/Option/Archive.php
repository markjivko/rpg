<?php

/**
 * Stephino_Zip_Option_Archive
 * 
 * @title      Zip
 * @desc       Zip utility: Archive options
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
final class Stephino_Zip_Option_Archive {

    const DEFAULT_DEFLATE_LEVEL = 6;

    /**
     * Archive comment
     * 
     * @var string
     */
    protected $_comment = '';

    /**
     * Size, in bytes, of the largest file to try
     * and load into memory (used by
     * addFileFromPath()).  Large files may also
     * be compressed differently; see the
     * '_largeFileMethod' option. Default is ~20 Mb.
     *
     * @var int
     */
    protected $_largeFileSize = 20 * 1024 * 1024;

    /**
     * How to handle large files.  Legal values are
     * Stephino_Zip_Enum_Method::STORE() (the default), or
     * Stephino_Zip_Enum_Method::DEFLATE(). STORE sends the file
     * raw and is significantly
     * faster, while DEFLATE compresses the file
     * and is much, much slower. Note that DEFLATE
     * must compress the file twice and is extremely slow.
     *
     * @var Stephino_Zip_Enum_Method
     */
    protected $_largeFileMethod;

    /**
     * Boolean indicating whether or not to send
     * the HTTP headers for this file.
     *
     * @var bool
     */
    protected $_sendHttpHeaders = false;

    /**
     * The method called to send headers
     *
     * @var Callable
     */
    protected $_httpHeaderCallback = 'header';

    /**
     * Enable Zip64 extension, supporting very large
     * archives (any size > 4 GB or file count > 64k)
     *
     * @var bool
     */
    protected $_enableZip64 = true;

    /**
     * Enable streaming files with single read where
     * general purpose bit 3 indicates local file header
     * contain zero values in crc and size fields,
     * these appear only after file contents
     * in data descriptor block.
     *
     * @var bool
     */
    protected $_zeroHeader = false;

    /**
     * Enable reading file stat for determining file size.
     * When a 32-bit system reads file size that is
     * over 2 GB, invalid value appears in file size
     * due to integer overflow. Should be disabled on
     * 32-bit systems with method addFileFromPath
     * if any file may exceed 2 GB. In this case file
     * will be read in blocks and correct size will be
     * determined from content.
     *
     * @var bool
     */
    protected $_statFiles = true;

    /**
     * Enable flush after every write to output stream.
     * @var bool
     */
    protected $_flushOutput = false;

    /**
     * HTTP Content-Disposition.  Defaults to
     * 'attachment', where
     * FILENAME is the specified filename.
     *
     * Note that this does nothing if you are
     * not sending HTTP headers.
     *
     * @var string
     */
    protected $_contentDisposition = 'attachment';

    /**
     * Note that this does nothing if you are
     * not sending HTTP headers.
     *
     * @var string
     */
    protected $_contentType = 'application/x-zip';

    /**
     * @var int
     */
    protected $_deflateLevel = self::DEFAULT_DEFLATE_LEVEL;

    /**
     * @var resource
     */
    protected $_outputStream;

    /**
     * Options constructor.
     */
    public function __construct() {
        $this->_largeFileMethod = Stephino_Zip_Enum_Method::STORE();
        $this->_outputStream = fopen('php://output', 'wb');
    }

    /**
     * Get archive comment
     * 
     * @return string
     */
    public function getComment() {
        return $this->_comment;
    }

    /**
     * Set archive comment
     * 
     * @param string $comment
     * @return Stephino_Zip_Option_Archive
     */
    public function setComment($comment) {
        $this->_comment = (string) $comment;
        return $this;
    }

    /**
     * Get largest file size
     * 
     * @return int
     */
    public function getLargeFileSize() {
        return $this->_largeFileSize;
    }

    /**
     * Set large file size
     * 
     * @param int $fileSize
     * @return Stephino_Zip_Option_Archive
     */
    public function setLargeFileSize($fileSize) {
        $this->_largeFileSize = (int) $fileSize;
        return $this;
    }

    /**
     * Get the large file method
     * 
     * @return Stephino_Zip_Enum_Method
     */
    public function getLargeFileMethod() {
        return $this->_largeFileMethod;
    }

    /**
     * Set the large file method
     * 
     * @param Stephino_Zip_Enum_Method $_largeFileMethod
     * @return Stephino_Zip_Option_Archive
     */
    public function setLargeFileMethod(Stephino_Zip_Enum_Method $_largeFileMethod) {
        $this->_largeFileMethod = $_largeFileMethod;
        return $this;
    }

    /**
     * Send HTTP headers
     * 
     * @return boolean
     */
    public function isSendHttpHeaders() {
        return !!$this->_sendHttpHeaders;
    }

    /**
     * Set whether to send HTTP headers for this archive
     * 
     * @param boolean $sendHttpHeaders
     * @return Stephino_Zip_Option_Archive
     */
    public function setSendHttpHeaders($sendHttpHeaders) {
        $this->_sendHttpHeaders = !!$sendHttpHeaders;
        return $this;
    }

    /**
     * Get the HTTP header callback
     * 
     * @return Callable
     */
    public function getHttpHeaderCallback() {
        return $this->_httpHeaderCallback;
    }

    /**
     * Set the HTTP header callback
     * 
     * @param Callable $httpHeaderCallbak
     * @return Stephino_Zip_Option_Archive
     */
    public function setHttpHeaderCallback($httpHeaderCallbak) {
        $this->_httpHeaderCallback = $httpHeaderCallbak;
        return $this;
    }

    /**
     * ZIP 64 enabled
     * 
     * @return boolean
     */
    public function isEnableZip64() {
        return !!$this->_enableZip64;
    }

    /**
     * Set ZIP 64 support
     * 
     * @param boolean $enableZip64
     * @return Stephino_Zip_Option_Archive
     */
    public function setEnableZip64($enableZip64) {
        $this->_enableZip64 = !!$enableZip64;
        return $this;
    }

    /**
     * Enable zero header
     * 
     * @return boolean
     */
    public function isZeroHeader() {
        return !!$this->_zeroHeader;
    }
    
    /**
     * Set zero header
     * 
     * @param boolean $zeroHeader
     * @return Stephino_Zip_Option_Archive
     */
    public function setZeroHeader($zeroHeader) {
        $this->_zeroHeader = !!$zeroHeader;
        return $this;
    }

    /**
     * Flush output enabled
     * 
     * @return boolean
     */
    public function isFlushOutput() {
        return !!$this->_flushOutput;
    }

    /**
     * Enable flush after every write to output stream
     * 
     * @param boolean $flushOutput
     * @return Stephino_Zip_Option_Archive
     */
    public function setFlushOutput($flushOutput) {
        $this->_flushOutput = !!$flushOutput;
        return $this;
    }

    /**
     * Reading file stat
     * 
     * @return boolean
     */
    public function isStatFiles() {
        return !!$this->_statFiles;
    }

    /**
     * Enable reading file stat
     * 
     * @param boolean $statFiles
     * @return Stephino_Zip_Option_Archive
     */
    public function setStatFiles($statFiles) {
        $this->_statFiles = !!$statFiles;
        return $this;
    }

    /**
     * Get HTTP content disposition
     * 
     * @return string
     */
    public function getContentDisposition() {
        return "$this->_contentDisposition";
    }

    /**
     * Set HTTP content disposition
     * 
     * @param string $contentDisposition
     * @return Stephino_Zip_Option_Archive
     */
    public function setContentDisposition($contentDisposition) {
        $this->_contentDisposition = "$contentDisposition";
        return $this;
    }

    /**
     * Get HTTP content type
     * 
     * @return boolean
     */
    public function getContentType() {
        return "$this->_contentType";
    }

    /**
     * Set HTTP content type
     * 
     * @param string $contentType
     * @return Stephino_Zip_Option_Archive
     */
    public function setContentType($contentType) {
        $this->_contentType = "$contentType";
        return $this;
    }

    /**
     * Get output stream resource
     * 
     * @return resource
     */
    public function getOutputStream() {
        return $this->_outputStream;
    }

    /**
     * Set output stream resource
     * 
     * @param resource $outputStream
     * @return Stephino_Zip_Option_Archive
     */
    public function setOutputStream($outputStream) {
        $this->_outputStream = $outputStream;
        return $this;
    }

    /**
     * Get the deflate level
     * 
     * @return int
     */
    public function getDeflateLevel() {
        return (int) $this->_deflateLevel;
    }

    /**
     * Set the deflate level
     * 
     * @param int $deflateLevel
     * @return Stephino_Zip_Option_Archive
     */
    public function setDeflateLevel($deflateLevel) {
        $this->_deflateLevel = (int) $deflateLevel;
        return $this;
    }
}

/*EOF*/