<?php

/**
 * Stephino_Zip
 * 
 * @title      Zip
 * @desc       Zip utility based on ZipStream, https://github.com/maennchen/ZipStream-PHP
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 * @copyright  (c) 2007-2009 Paul Duncan
 * @copyright  (c) 2014 Jonatan Männchen
 * @copyright  (c) 2014 Jesse G. Donat
 * @copyright  (c) 2018 Nicolas CARPi
 * @copyright  (c) 2021, Stephino
 */
class Stephino_Zip {

    /**
     * This number corresponds to the ZIP version/OS used (2 bytes)
     * From: https://www.iana.org/assignments/media-types/application/zip
     *
     * 0 - MS-DOS and OS/2 (F.A.T. file systems)
     * 1 - Amiga                     2 - VAX/VMS
     * 3 - *nix                      4 - VM/CMS
     * 5 - Atari ST                  6 - OS/2 H.P.F.S.
     * 7 - Macintosh                 8 - Z-System
     * 9 - CP/M                      10 thru 255 - unused
     */
    const ZIP_VERSION_MADE_BY = 0x603;

    /**
     * The following signatures end with 0x4b50, which in ASCII is PK,
     * the initials of the inventor Phil Katz.
     * See https://en.wikipedia.org/wiki/Zip_(file_format)#File_headers
     */
    const CDR_FILE_SIGNATURE          = 0x02014b50;
    const FILE_HEADER_SIGNATURE       = 0x04034b50;
    const CDR_EOF_SIGNATURE           = 0x06054b50;
    const ZIP64_CDR_EOF_SIGNATURE     = 0x06064b50;
    const ZIP64_CDR_LOCATOR_SIGNATURE = 0x07064b50;
    const DATA_DESCRIPTOR_SIGNATURE   = 0x08074b50;

    /**
     * Global Options
     *
     * @var Stephino_Zip_Option_Archive
     */
    public $opt;

    /**
     * @var array
     */
    public $files = [];

    /**
     * @var Stephino_Zip_BigInt
     */
    public $cdrOfs;

    /**
     * @var Stephino_Zip_BigInt
     */
    public $ofs;

    /**
     * @var bool
     */
    protected $_needHeaders;

    /**
     * @var null|String
     */
    protected $_outputName;

    /**
     * Create a new Stephino_Zip object.
     *
     * Parameters:
     *
     * @param String $archiveName - Name of output file (optional).
     * @param Stephino_Zip_Option_Archive $archiveOptions - Archive Options
     *
     * Large File Support:
     *
     * By default, the method addFileFromPath() will send send files
     * larger than 20 megabytes along raw rather than attempting to
     * compress them.  You can change both the maximum size and the
     * compression behavior using the largeFile* options above, with the
     * following caveats:
     *
     * * For "small" files (e.g. files smaller than largeFileSize), the
     *   memory use can be up to twice that of the actual file.  In other
     *   words, adding a 10 megabyte file to the archive could potentially
     *   occupy 20 megabytes of memory.
     *
     * * Enabling compression on large files (e.g. files larger than
     *   large_file_size) is extremely slow, because Stephino_Zip has to pass
     *   over the large file once to calculate header information, and then
     *   again to compress and send the actual data.
     */
    public function __construct($archiveName = null, Stephino_Zip_Option_Archive $archiveOptions = null) {
        $this->opt = $archiveOptions ?: new Stephino_Zip_Option_Archive();

        $this->_outputName = $archiveName;
        $this->_needHeaders = $archiveName && $this->opt->isSendHttpHeaders();

        $this->cdrOfs = new Stephino_Zip_BigInt();
        $this->ofs = new Stephino_Zip_BigInt();
    }
    
    /**
     * Create a format string and argument list for pack(), then call
     * pack() and return the result.
     * 
     * @param array $fields
     * @return string
     */
    public static function packFields(array $fields) {
        $fmt = '';
        $args = [];

        // populate format string and argument list
        foreach ($fields as $fieldData) {
            list($format, $value) = $fieldData;
            if ('P' === $format) {
                $fmt .= 'VV';
                if ($value instanceof Stephino_Zip_BigInt) {
                    $args[] = $value->getLow32();
                    $args[] = $value->getHigh32();
                } else {
                    $args[] = $value;
                    $args[] = 0;
                }
            } else {
                if ($value instanceof Stephino_Zip_BigInt) {
                    $value = $value->getLow32();
                }
                $fmt .= $format;
                $args[] = $value;
            }
        }

        // prepend format string to argument list
        array_unshift($args, $fmt);

        // build output string from header and compressed data
        return call_user_func_array('pack', $args);
    }

    /**
     * Add a file to the archive
     *
     * @param String $name - path of file in archive (including directory).
     * @param String $data - contents of file
     * @param Stephino_Zip_Option_File $options
     * @return Stephino_Zip
     */
    public function addFile($name, $data, Stephino_Zip_Option_File $options = null) {
        $options = $options ?: new Stephino_Zip_Option_File();
        $options->defaultTo($this->opt);

        $file = new Stephino_Zip_File($this, $name, $options);
        $file->processData($data);
        return $this;
    }

    /**
     * Add a file at path to the archive.
     *
     * Note that large files may be compressed differently than smaller
     * files; see the "Large File Support" section above for more
     * information.
     *
     * @param String $name - name of file in archive (including directory path).
     * @param String $path - path to file on disk (note: paths should be encoded using
     *          UNIX-style forward slashes -- e.g '/path/to/some/file').
     * @param Stephino_Zip_Option_File $options
     * @return Stephino_Zip
     * @throws Exception
     */
    public function addFileFromPath($name, $path, Stephino_Zip_Option_File $options = null) {
        $options = $options ?: new Stephino_Zip_Option_File();
        $options->defaultTo($this->opt);

        $file = new Stephino_Zip_File($this, $name, $options);
        $file->processPath($path);
        return $this;
    }

    /**
     * Add an open stream to the archive
     *
     * @param String $name - path of file in archive (including directory).
     * @param resource $stream - contents of file as a stream resource
     * @param Stephino_Zip_Option_File $options
     * @return Stephino_Zip
     */
    public function addFileFromStream($name, $stream, Stephino_Zip_Option_File $options = null) {
        $options = $options ?: new Stephino_Zip_Option_File();
        $options->defaultTo($this->opt);

        $file = new Stephino_Zip_File($this, $name, $options);
        $file->processStream(new Stephino_Zip_StreamDeflator($stream));
        return $this;
    }

    /**
     * Add an open stream to the archive
     *
     * @param String $name - path of file in archive (including directory).
     * @param Stephino_Zip_StreamInterface $stream - contents of file as a stream resource
     * @param Stephino_Zip_Option_File $options
     * @return Stephino_Zip
     */
    public function addFileFromPsr7Stream($name, Stephino_Zip_StreamInterface $stream, Stephino_Zip_Option_File $options = null) {
        $options = $options ?: new Stephino_Zip_Option_File();
        $options->defaultTo($this->opt);

        $file = new Stephino_Zip_File($this, $name, $options);
        $file->processStream($stream);
        return $this;
    }

    /**
     * Write zip footer to stream
     * 
     * @throws Exception
     */
    public function finish() {
        // add trailing cdr file records
        foreach ($this->files as $cdrFile) {
            $this->send($cdrFile);
            $this->cdrOfs = $this->cdrOfs->add(Stephino_Zip_BigInt::init(strlen($cdrFile)));
        }

        // Add 64bit headers (if applicable)
        if (count($this->files) >= 0xFFFF ||
            $this->cdrOfs->isOver32() ||
            $this->ofs->isOver32()) {
            if (!$this->opt->isEnableZip64()) {
                throw new Exception('File size exceeds limit of 32 bit integer. Please enable "zip64" option.');
            }

            $this->_addCdr64Eof();
            $this->_addCdr64Locator();
        }

        // add trailing cdr eof record
        $this->_addCdrEof();

        // The End
        $this->_clear();
    }

    /**
     * Send string, sending HTTP headers if necessary.
     * Flush output after write if configure option is set.
     *
     * @param String $str
     */
    public function send($str) {
        if ($this->_needHeaders) {
            $this->_sendHttpHeaders();
        }
        $this->_needHeaders = false;

        fwrite($this->opt->getOutputStream(), (string) $str);

        if ($this->opt->isFlushOutput()) {
            // flush output buffer if it is on and flushable
            $status = ob_get_status();
            if (isset($status['flags']) && ($status['flags'] & PHP_OUTPUT_HANDLER_FLUSHABLE)) {
                ob_flush();
            }

            // Flush system buffers after flushing userspace output buffer
            flush();
        }
    }
    
    /**
     * Is this file larger than large_file_size?
     *
     * @param string $path
     * @return bool
     */
    public function isLargeFile($path) {
        if (!$this->opt->isStatFiles()) {
            return false;
        }
        $stat = stat($path);
        return $stat['size'] > $this->opt->getLargeFileSize();
    }

    /**
     * Save file attributes for trailing CDR record.
     *
     * @param Stephino_Zip_File $file
     * @return Stephino_Zip
     */
    public function addToCdr(Stephino_Zip_File $file) {
        $file->ofs = $this->ofs;
        $this->ofs = $this->ofs->add($file->getTotalLength());
        $this->files[] = $file->getCdrFile();
        return $this;
    }

    /**
     * Send ZIP64 CDR EOF (Central Directory Record End-of-File) record.
     */
    protected function _addCdr64Eof() {
        $num_files = count($this->files);
        $cdr_length = $this->cdrOfs;
        $cdr_offset = $this->ofs;

        $fields = [
            ['V', static::ZIP64_CDR_EOF_SIGNATURE], // ZIP64 end of central file header signature
            ['P', 44], // Length of data below this header (length of block - 12) = 44
            ['v', static::ZIP_VERSION_MADE_BY], // Made by version
            ['v', Stephino_Zip_Enum_Version::ZIP64], // Extract by version
            ['V', 0x00], // disk number
            ['V', 0x00], // no of disks
            ['P', $num_files], // no of entries on disk
            ['P', $num_files], // no of entries in cdr
            ['P', $cdr_length], // CDR size
            ['P', $cdr_offset], // CDR offset
        ];

        $ret = static::packFields($fields);
        $this->send($ret);
    }

    /**
     * Send HTTP headers for this stream.
     */
    protected function _sendHttpHeaders() {
        // grab content disposition
        $disposition = $this->opt->getContentDisposition();

        if ($this->_outputName) {
            // Various different browsers dislike various characters here. Strip them all for safety.
            $safe_output = trim(str_replace(['"', "'", '\\', ';', "\n", "\r"], '', $this->_outputName));

            // Check if we need to UTF-8 encode the filename
            $urlencoded = rawurlencode($safe_output);
            $disposition .= "; filename*=UTF-8''{$urlencoded}";
        }

        $headers = array(
            'Content-Type' => $this->opt->getContentType(),
            'Content-Disposition' => $disposition,
            'Pragma' => 'public',
            'Cache-Control' => 'public, must-revalidate',
            'Content-Transfer-Encoding' => 'binary'
        );

        $call = $this->opt->getHttpHeaderCallback();
        foreach ($headers as $key => $val) {
            $call("$key: $val");
        }
    }

    /**
     * Send ZIP64 CDR Locator (Central Directory Record Locator) record.
     */
    protected function _addCdr64Locator() {
        $cdr_offset = $this->ofs->add($this->cdrOfs);

        $fields = [
            ['V', static::ZIP64_CDR_LOCATOR_SIGNATURE], // ZIP64 end of central file header signature
            ['V', 0x00], // Disc number containing CDR64EOF
            ['P', $cdr_offset], // CDR offset
            ['V', 1], // Total number of disks
        ];

        $ret = static::packFields($fields);
        $this->send($ret);
    }

    /**
     * Send CDR EOF (Central Directory Record End-of-File) record.
     */
    protected function _addCdrEof() {
        $num_files = count($this->files);
        $cdr_length = $this->cdrOfs;
        $cdr_offset = $this->ofs;

        // grab comment (if specified)
        $comment = $this->opt->getComment();

        $fields = [
            ['V', static::CDR_EOF_SIGNATURE], // end of central file header signature
            ['v', 0x00], // disk number
            ['v', 0x00], // no of disks
            ['v', min($num_files, 0xFFFF)], // no of entries on disk
            ['v', min($num_files, 0xFFFF)], // no of entries in cdr
            ['V', $cdr_length->getLowFF()], // CDR size
            ['V', $cdr_offset->getLowFF()], // CDR offset
            ['v', strlen($comment)], // Zip Comment size
        ];

        $ret = static::packFields($fields) . $comment;
        $this->send($ret);
    }

    /**
     * Clear all internal variables. Note that the stream object is not usable after this.
     */
    protected function _clear() {
        $this->files = [];
        $this->ofs = new Stephino_Zip_BigInt();
        $this->cdrOfs = new Stephino_Zip_BigInt();
        $this->opt = new Stephino_Zip_Option_Archive();
    }

}

/*EOF*/