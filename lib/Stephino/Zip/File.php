<?php

/**
 * Stephino_Zip_File
 * 
 * @title      Zip
 * @desc       Zip utility: Enumerators
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Zip_File {

    const HASH_ALGORITHM = 'crc32b';
    const BIT_ZERO_HEADER = 0x0008;
    const BIT_EFS_UTF8 = 0x0800;
    const COMPUTE = 1;
    const SEND = 2;
    const CHUNKED_READ_BLOCK_SIZE = 1048576;

    /**
     * @var string
     */
    public $name;

    /**
     * @var Stephino_Zip_Option_File
     */
    public $opt;

    /**
     * @var Stephino_Zip_BigInt
     */
    public $len;

    /**
     * @var Stephino_Zip_BigInt
     */
    public $zlen;

    /**
     * @var int 
     */
    public $crc;

    /**
     * @var Stephino_Zip_BigInt
     */
    public $hlen;

    /**
     * @var Stephino_Zip_BigInt
     */
    public $ofs;

    /**
     * @var int
     */
    public $bits;

    /**
     * @var Stephino_Zip_Enum_Version
     */
    public $version;

    /**
     * @var Stephino_Zip
     */
    public $zip;

    /**
     * @var resource
     */
    protected $_deflate;

    /**
     * @var resource
     */
    protected $_hash;

    /**
     * @var Stephino_Zip_Enum_Method
     */
    protected $_method;

    /**
     * @var Stephino_Zip_BigInt
     */
    protected $_totalLength;

    public function __construct(Stephino_Zip $zip, $name, Stephino_Zip_Option_File $opt = null) {
        $this->zip = $zip;
        $this->name = "$name";
        $this->opt = $opt ?: new Stephino_Zip_Option_File();
        $this->version = Stephino_Zip_Enum_Version::STORE();
        $this->ofs = new Stephino_Zip_BigInt();
        
        $this->_method = $this->opt->getMethod();
    }
    
    /**
     * Strip characters that are not legal in Windows filenames
     * to prevent compatibility issues
     *
     * @param string $filename Unprocessed filename
     * @return string
     */
    public static function filterFilename($filename) {
        // strip leading slashes from file name
        // (fixes bug in windows archive viewer)
        $filename = preg_replace('/^\\/+/', '', $filename);

        return str_replace(['\\', ':', '*', '?', '"', '<', '>', '|'], '_', $filename);
    }

    /**
     * Convert a UNIX timestamp to a DOS timestamp.
     *
     * @param int $when
     * @return int DOS Timestamp
     */
    final protected static function _dosTime($when) {
        // get date array for timestamp
        $d = getdate((int) $when);

        // set lower-bound on dates
        if ($d['year'] < 1980) {
            $d = array(
                'year' => 1980,
                'mon' => 1,
                'mday' => 1,
                'hours' => 0,
                'minutes' => 0,
                'seconds' => 0
            );
        }

        // remove extra years from 1980
        $d['year'] -= 1980;

        // return date string
        return
            ($d['year'] << 25) |
            ($d['mon'] << 21) |
            ($d['mday'] << 16) |
            ($d['hours'] << 11) |
            ($d['minutes'] << 5) |
            ($d['seconds'] >> 1);
    }

    public function processPath($path) {
        $path = (string) $path;
        if (!is_readable($path)) {
            if (!file_exists($path)) {
                throw new Exception("The file with the path $path wasn't found.");
            }
            throw new Exception("The file with the path $path isn't readable.");
        }
        if (false === $this->zip->isLargeFile($path)) {
            $data = file_get_contents($path);
            $this->processData($data);
        } else {
            $this->_method = $this->zip->opt->getLargeFileMethod();

            $stream = new Stephino_Zip_StreamDeflator(fopen($path, 'rb'));
            $this->processStream($stream);
            $stream->close();
        }
    }

    public function processData($data) {
        $data = (string) $data;
        $this->len = new Stephino_Zip_BigInt(strlen($data));
        $this->crc = crc32($data);

        // compress data if needed
        if ($this->_method->equals(Stephino_Zip_Enum_Method::DEFLATE())) {
            $data = gzdeflate($data);
        }

        $this->zlen = new Stephino_Zip_BigInt(strlen($data));
        $this->addFileHeader();
        $this->zip->send($data);
        $this->addFileFooter();
    }

    /**
     * Create and send zip header for this file.
     *
     * @return void
     * @throws Exception
     */
    public function addFileHeader() {
        $name = static::filterFilename($this->name);

        // calculate name length
        $nameLength = strlen($name);

        // create dos timestamp
        $time = static::_dosTime($this->opt->getTime()->getTimestamp());

        $comment = $this->opt->getComment();

        if (!mb_check_encoding($name, 'ASCII') ||
            !mb_check_encoding($comment, 'ASCII')) {
            // Sets Bit 11: Language encoding flag (EFS).  If this bit is set,
            // the filename and comment fields for this file
            // MUST be encoded using UTF-8. (see APPENDIX D)
            if (!mb_check_encoding($name, 'UTF-8') ||
                !mb_check_encoding($comment, 'UTF-8')) {
                throw new Exception(
                    'File name and comment should use UTF-8 if one of them does not fit into ASCII range.'
                );
            }
            $this->bits |= self::BIT_EFS_UTF8;
        }

        if ($this->_method->equals(Stephino_Zip_Enum_Method::DEFLATE())) {
            $this->version = Stephino_Zip_Enum_Version::DEFLATE();
        }

        $force = (boolean) ($this->bits & self::BIT_ZERO_HEADER) &&
            $this->zip->opt->isEnableZip64();

        $footer = $this->_buildZip64ExtraBlock($force);

        // If this file will start over 4GB limit in ZIP file,
        // CDR record will have to use Zip64 extension to describe offset
        // to keep consistency we use the same value here
        if ($this->zip->ofs->isOver32()) {
            $this->version = Stephino_Zip_Enum_Version::ZIP64();
        }

        $fields = [
            ['V', Stephino_Zip::FILE_HEADER_SIGNATURE],
            ['v', $this->version->getValue()], // Stephino_Zip_Enum_Version needed to Extract
            ['v', $this->bits], // General purpose bit flags - data descriptor flag set
            ['v', $this->_method->getValue()], // Compression method
            ['V', $time], // Timestamp (DOS Format)
            ['V', $this->crc], // CRC32 of data (0 -> moved to data descriptor footer)
            ['V', $this->zlen->getLowFF($force)], // Length of compressed data (forced to 0xFFFFFFFF for zero header)
            ['V', $this->len->getLowFF($force)], // Length of original data (forced to 0xFFFFFFFF for zero header)
            ['v', $nameLength], // Length of filename
            ['v', strlen($footer)], // Extra data (see above)
        ];

        // pack fields and calculate "total" length
        $header = Stephino_Zip::packFields($fields);

        // print header and filename
        $data = $header . $name . $footer;
        $this->zip->send($data);

        // save header length
        $this->hlen = Stephino_Zip_BigInt::init(strlen($data));
    }

    /**
     * Send CDR record for specified file.
     *
     * @return string
     */
    public function getCdrFile() {
        $name = static::filterFilename($this->name);

        // get attributes
        $comment = $this->opt->getComment();

        // get dos timestamp
        $time = static::_dosTime($this->opt->getTime()->getTimestamp());

        $footer = $this->_buildZip64ExtraBlock();

        $fields = [
            ['V', Stephino_Zip::CDR_FILE_SIGNATURE], // Central file header signature
            ['v', Stephino_Zip::ZIP_VERSION_MADE_BY], // Made by version
            ['v', $this->version->getValue()], // Extract by version
            ['v', $this->bits], // General purpose bit flags - data descriptor flag set
            ['v', $this->_method->getValue()], // Compression method
            ['V', $time], // Timestamp (DOS Format)
            ['V', $this->crc], // CRC32
            ['V', $this->zlen->getLowFF()], // Compressed Data Length
            ['V', $this->len->getLowFF()], // Original Data Length
            ['v', strlen($name)], // Length of filename
            ['v', strlen($footer)], // Extra data len (see above)
            ['v', strlen($comment)], // Length of comment
            ['v', 0], // Disk number
            ['v', 0], // Internal File Attributes
            ['V', 32], // External File Attributes
            ['V', $this->ofs->getLowFF()]           // Relative offset of local header
        ];

        // pack fields, then append name and comment
        $header = Stephino_Zip::packFields($fields);

        return $header . $name . $footer . $comment;
    }

    /**
     * @return Stephino_Zip_BigInt
     */
    public function getTotalLength() {
        return $this->_totalLength;
    }

    /**
     * Create and send data descriptor footer for this file.
     *
     * @return void
     */
    public function addFileFooter() {
        if ($this->bits & self::BIT_ZERO_HEADER) {
            // compressed and uncompressed size
            $sizeFormat = 'V';
            if ($this->zip->opt->isEnableZip64()) {
                $sizeFormat = 'P';
            }
            $fields = [
                ['V', Stephino_Zip::DATA_DESCRIPTOR_SIGNATURE],
                ['V', $this->crc], // CRC32
                [$sizeFormat, $this->zlen], // Length of compressed data
                [$sizeFormat, $this->len], // Length of original data
            ];

            $footer = Stephino_Zip::packFields($fields);
            $this->zip->send($footer);
        } else {
            $footer = '';
        }
        $this->_totalLength = $this->hlen->add($this->zlen)->add(Stephino_Zip_BigInt::init(strlen($footer)));
        $this->zip->addToCdr($this);
    }

    public function processStream(Stephino_Zip_StreamInterface $stream) {
        $this->zlen = new Stephino_Zip_BigInt();
        $this->len = new Stephino_Zip_BigInt();

        if ($this->zip->opt->isZeroHeader()) {
            $this->_processStreamWithZeroHeader($stream);
        } else {
            $this->_processStreamWithComputedHeader($stream);
        }
    }

    protected function _buildZip64ExtraBlock($force = false) {

        $fields = [];
        if ($this->len->isOver32($force)) {
            $fields[] = ['P', $this->len];          // Length of original data
        }

        if ($this->len->isOver32($force)) {
            $fields[] = ['P', $this->zlen];         // Length of compressed data
        }

        if ($this->ofs->isOver32()) {
            $fields[] = ['P', $this->ofs];          // Offset of local header record
        }

        if (!empty($fields)) {
            if (!$this->zip->opt->isEnableZip64()) {
                throw new Exception('File size exceeds limit of 32 bit integer. Please enable "zip64" option.');
            }

            array_unshift(
                $fields,
                ['v', 0x0001], // 64 bit extension
                ['v', count($fields) * 8]             // Length of data block
            );
            $this->version = Stephino_Zip_Enum_Version::ZIP64();
        }

        return Stephino_Zip::packFields($fields);
    }

    protected function _processStreamWithZeroHeader(Stephino_Zip_StreamInterface $stream) {
        $this->bits |= self::BIT_ZERO_HEADER;
        $this->addFileHeader();
        $this->_readStream($stream, self::COMPUTE | self::SEND);
        $this->addFileFooter();
    }

    protected function _readStream(Stephino_Zip_StreamInterface $stream, $options = null) {
        $this->_deflateInit();
        $total = 0;
        $size = $this->opt->getSize();
        while (!$stream->eof() && (0 === $size || $total < $size)) {
            $data = $stream->read(self::CHUNKED_READ_BLOCK_SIZE);
            $total += strlen($data);
            if ($size > 0 && $total > $size) {
                $data = substr($data, 0, strlen($data) - ($total - $size));
            }
            $this->_deflateData($stream, $data, $options);
            if ($options & self::SEND) {
                $this->zip->send($data);
            }
        }
        $this->_deflateFinish($options);
    }

    protected function _deflateInit() {
        $this->_hash = hash_init(self::HASH_ALGORITHM);
        if ($this->_method->equals(Stephino_Zip_Enum_Method::DEFLATE())) {
            $this->_deflate = deflate_init(
                ZLIB_ENCODING_RAW,
                ['level' => $this->opt->getDeflateLevel()]
            );
        }
    }

    protected function _deflateData(Stephino_Zip_StreamInterface $stream, &$data, $options = null) {
        if ($options & self::COMPUTE) {
            $this->len = $this->len->add(Stephino_Zip_BigInt::init(strlen($data)));
            hash_update($this->_hash, $data);
        }
        if ($this->_deflate) {
            $data = deflate_add(
                $this->_deflate,
                $data,
                $stream->eof() ? ZLIB_FINISH : ZLIB_NO_FLUSH
            );
        }
        if ($options & self::COMPUTE) {
            $this->zlen = $this->zlen->add(Stephino_Zip_BigInt::init(strlen($data)));
        }
    }

    protected function _deflateFinish($options = null) {
        if ($options & self::COMPUTE) {
            $this->crc = hexdec(hash_final($this->_hash));
        }
    }

    protected function _processStreamWithComputedHeader(Stephino_Zip_StreamInterface $stream) {
        $this->_readStream($stream, self::COMPUTE);
        $stream->rewind();

        // incremental compression with deflate_add
        // makes this second read unnecessary
        // but it is only available from PHP 7.0
        if (!$this->_deflate && $stream instanceof Stephino_Zip_StreamDeflator && $this->_method->equals(Stephino_Zip_Enum_Method::DEFLATE())) {
            $stream->addDeflateFilter($this->opt);
            $this->zlen = new Stephino_Zip_BigInt();
            while (!$stream->eof()) {
                $data = $stream->read(self::CHUNKED_READ_BLOCK_SIZE);
                $this->zlen = $this->zlen->add(Stephino_Zip_BigInt::init(strlen($data)));
            }
            $stream->rewind();
        }

        $this->addFileHeader();
        $this->_readStream($stream, self::SEND);
        $this->addFileFooter();
    }
}

/*EOF*/