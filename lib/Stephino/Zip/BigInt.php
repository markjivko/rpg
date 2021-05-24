<?php

/**
 * Stephino_Zip_BigInt
 * 
 * @title      Zip
 * @desc       Zip utility: Big Int implementation
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Zip_BigInt {

    /**
     * @var int[]
     */
    protected $_bytes = [0, 0, 0, 0, 0, 0, 0, 0];

    /**
     * Initialize the bytes array
     *
     * @param int $value
     */
    public function __construct($value = 0) {
        $this->_fillBytes((int) $value, 0, 8);
    }

    /**
     * Fill the bytes field with int
     *
     * @param int $value
     * @param int $start
     * @param int $count
     * @return void
     */
    protected function _fillBytes($value, $start, $count) {
        for ($i = 0; $i < (int) $count; $i++) {
            $this->_bytes[$start + $i] = $i >= PHP_INT_SIZE ? 0 : (int) $value & 0xFF;
            (int) $value >>= 8;
        }
    }

    /**
     * Get an instance
     *
     * @param int $value
     * @return Stephino_Zip_BigInt
     */
    public static function init($value = 0) {
        return new self($value);
    }

    /**
     * Fill bytes from low to high
     *
     * @param int $low
     * @param int $high
     * @return Stephino_Zip_BigInt
     */
    public static function fromLowHigh($low, $high) {
        $bigint = new self();
        $bigint->_fillBytes((int) $low, 0, 4);
        $bigint->_fillBytes((int) $high, 4, 4);
        return $bigint;
    }

    /**
     * Get high 32
     *
     * @return int
     */
    public function getHigh32() {
        return $this->getValue(4, 4);
    }

    /**
     * Get value from bytes array
     *
     * @param int $end
     * @param int $length
     * @return int
     */
    public function getValue($end = 0, $length = 8) {
        $result = 0;
        for ($i = $end + $length - 1; $i >= $end; $i--) {
            $result <<= 8;
            $result |= $this->_bytes[$i];
        }
        return $result;
    }

    /**
     * Get low FF
     *
     * @param bool $force
     * @return float
     */
    public function getLowFF($force = false) {
        $result = $force || $this->isOver32()
            ? (float) 0xFFFFFFFF
            : (float) $this->getLow32();
        
        return $result;
    }

    /**
     * Check if is over 32
     *
     * @param bool $force
     * @return bool
     */
    public function isOver32($force = false) {
        // value 0xFFFFFFFF already needs a Zip64 header
        return $force ||
            max(array_slice($this->_bytes, 4, 4)) > 0 ||
            0xFF === min(array_slice($this->_bytes, 0, 4));
    }

    /**
     * Get low 32
     *
     * @return int
     */
    public function getLow32() {
        return $this->getValue(0, 4);
    }

    /**
     * Get hexadecimal
     *
     * @return string
     */
    public function getHex64() {
        $result = '0x';
        for ($i = 7; $i >= 0; $i--) {
            $result .= sprintf('%02X', $this->_bytes[$i]);
        }
        return $result;
    }

    /**
     * Add
     *
     * @param Stephino_Zip_BigInt $number
     * @return Stephino_Zip_BigInt
     */
    public function add(Stephino_Zip_BigInt $number) {
        $result = clone $this;
        $overflow = false;
        for ($i = 0; $i < 8; $i++) {
            $result->_bytes[$i] += $number->_bytes[$i];
            if ($overflow) {
                $result->_bytes[$i]++;
                $overflow = false;
            }
            if ($result->_bytes[$i] & 0x100) {
                $overflow = true;
                $result->_bytes[$i] &= 0xFF;
            }
        }
        if ($overflow) {
            throw new Exception('File size exceeds limit of 32 bit integer. Please enable "zip64" option.');
        }
        return $result;
    }
}

/* EOF */