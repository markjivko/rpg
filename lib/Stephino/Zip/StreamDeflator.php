<?php

/**
 * Stephino_Zip_StreamDeflator
 * 
 * @title      Zip
 * @desc       Zip utility: Stream deflator
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Zip_StreamDeflator extends Stephino_Zip_Stream {

    protected $_filter;

    /**
     * @var Stephino_Zip_Option_File
     */
    protected $_options;

    /**
     * Rewind stream
     *
     * @return void
     */
    public function rewind() {
        // deflate filter needs to be removed before rewind
        if ($this->_filter) {
            $this->removeDeflateFilter();
            $this->seek(0);
            $this->addDeflateFilter($this->_options);
        } else {
            rewind($this->_stream);
        }
    }

    /**
     * Remove the deflate filter
     *
     * @return void
     */
    public function removeDeflateFilter() {
        if (!$this->_filter) {
            return;
        }
        stream_filter_remove($this->_filter);
        $this->_filter = null;
    }

    /**
     * Add a deflate filter
     *
     * @param Stephino_Zip_Option_File $options
     * @return void
     */
    public function addDeflateFilter(Stephino_Zip_Option_File $options) {
        $this->_options = $options;
        // parameter 4 for stream_filter_append expects array
        // so we convert the option object in an array
        $optionsArr = [
            'comment' => $options->getComment(),
            'method' => $options->getMethod(),
            'deflateLevel' => $options->getDeflateLevel(),
            'time' => $options->getTime()
        ];
        $this->_filter = stream_filter_append(
            $this->_stream,
            'zlib.deflate',
            STREAM_FILTER_READ,
            $optionsArr
        );
    }
}

/* EOF */