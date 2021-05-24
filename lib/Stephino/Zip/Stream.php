<?php

/**
 * Stephino_Zip_Stream
 * 
 * @title      Zip
 * @desc       Zip utility: Stream
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Zip_Stream implements Stephino_Zip_StreamInterface {

    protected $_stream;

    public function __construct($stream) {
        $this->_stream = $stream;
    }

    /**
     * Closes the stream and any underlying resources.
     *
     * @return void
     */
    public function close() {
        if (is_resource($this->_stream)) {
            fclose($this->_stream);
        }
        $this->detach();
    }

    /**
     * Separates any underlying resources from the stream.
     *
     * After the stream has been detached, the stream is in an unusable state.
     *
     * @return resource|null Underlying PHP stream, if any
     */
    public function detach() {
        $result = $this->_stream;
        $this->_stream = null;
        return $result;
    }

    /**
     * Reads all data from the stream into a string, from the beginning to end.
     *
     * This method MUST attempt to seek to the beginning of the stream before
     * reading data and read the stream until the end is reached.
     *
     * Warning: This could attempt to load a large amount of data into memory.
     *
     * This method MUST NOT raise an exception in order to conform with PHP's
     * string casting operations.
     *
     * @see http://php.net/manual/en/language.oop5.magic.php#object.tostring
     * @return string
     */
    public function __toString() {
        try {
            $this->seek(0);
        } catch (RuntimeException $e) {}
        
        return (string) stream_get_contents($this->_stream);
    }

    /**
     * Seek to a position in the stream.
     *
     * @link http://www.php.net/manual/en/function.fseek.php
     * @param int $offset Stream offset
     * @param int $whence Specifies how the cursor position will be calculated
     *     based on the seek offset. Valid values are identical to the built-in
     *     PHP $whence values for `fseek()`.  SEEK_SET: Set position equal to
     *     offset bytes SEEK_CUR: Set position to current location plus offset
     *     SEEK_END: Set position to end-of-stream plus offset.
     * @throws RuntimeException on failure.
     */
    public function seek($offset, $whence = SEEK_SET) {
        if (!$this->isSeekable()) {
            throw new RuntimeException();
        }
        if (0 !== fseek($this->_stream, $offset, $whence)) {
            throw new RuntimeException();
        }
    }

    /**
     * Returns whether or not the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable() {
        return (bool) $this->getMetadata('seekable');
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @link http://php.net/manual/en/function.stream-get-meta-data.php
     * @param string $key Specific metadata to retrieve.
     * @return array|mixed|null Returns an associative array if no key is
     *     provided. Returns a specific key value if a key is provided and the
     *     value is found, or null if the key is not found.
     */
    public function getMetadata($key = null) {
        $metadata = stream_get_meta_data($this->_stream);
        return null !== $key
            ? (isset($metadata[$key]) ? $metadata[$key] : null)
            : $metadata;
    }

    /**
     * Get the size of the stream if known.
     *
     * @return int|null Returns the size in bytes if known, or null if unknown.
     */
    public function getSize() {
        $stats = fstat($this->_stream);
        return $stats['size'];
    }

    /**
     * Returns the current position of the file read/write pointer
     *
     * @return int Position of the file pointer
     * @throws RuntimeException on error.
     */
    public function tell() {
        if (false === $position = ftell($this->_stream)) {
            throw new RuntimeException();
        }
        return $position;
    }

    /**
     * Returns true if the stream is at the end of the stream.
     *
     * @return bool
     */
    public function eof() {
        return feof($this->_stream);
    }

    /**
     * Seek to the beginning of the stream.
     *
     * If the stream is not seekable, this method will raise an exception;
     * otherwise, it will perform a seek(0).
     *
     * @see seek()
     * @link http://www.php.net/manual/en/function.fseek.php
     * @throws RuntimeException on failure.
     */
    public function rewind() {
        $this->seek(0);
    }

    /**
     * Write data to the stream.
     *
     * @param string $string The string that is to be written.
     * @return int Returns the number of bytes written to the stream.
     * @throws RuntimeException on failure.
     */
    public function write($string) {
        if (!$this->isWritable()) {
            throw new RuntimeException();
        }
        if (false === fwrite($this->_stream, $string)) {
            throw new RuntimeException();
        }
        return mb_strlen($string);
    }

    /**
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable() {
        return 1 === preg_match('/[waxc+]/', $this->getMetadata('mode'));
    }

    /**
     * Read data from the stream.
     *
     * @param int $length Read up to $length bytes from the object and return
     *     them. Fewer than $length bytes may be returned if underlying stream
     *     call returns fewer bytes.
     * @return string Returns the data read from the stream, or an empty string
     *     if no bytes are available.
     * @throws RuntimeException if an error occurs.
     */
    public function read($length) {
        if (!$this->isReadable()) {
            throw new RuntimeException();
        }
        if (false === $result = fread($this->_stream, $length)) {
            throw new RuntimeException();
        }
        return $result;
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable() {
        return 1 === preg_match('/[r+]/', $this->getMetadata('mode'));
    }

    /**
     * Returns the remaining contents in a string
     *
     * @return string
     * @throws RuntimeException if unable to read or an error occurs while
     *     reading.
     */
    public function getContents() {
        if (!$this->isReadable()) {
            throw new RuntimeException();
        }
        if (false === $result = stream_get_contents($this->_stream)) {
            throw new RuntimeException();
        }
        return $result;
    }

}

/*EOF*/