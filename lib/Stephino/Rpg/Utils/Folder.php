<?php
/**
 * Stephino_Rpg_Utils_Folder
 * 
 * @title     Utils:Folder
 * @desc      Folder utils
 * @copyright (c) 2021, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Utils_Folder {
    
    /**
     * WordPress FileSystem
     * 
     * @var \WP_Filesystem_Direct
     */
    protected $_fileSystem = null;
    
    /**
     * Singleton instance
     * 
     * @var Stephino_Rpg_Utils_Folder
     */
    protected static $_instance = null;
    
    /**
     * Folder utilities
     * 
     * @return Stephino_Rpg_Utils_Folder
     * @throws Exception
     */
    public static function get() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    /**
     * Initialize the WordPress FileSystem
     * 
     * @global \WP_Filesystem_Direct $wp_filesystem
     * @throws Exception
     */
    protected function __construct() {
        /** @var \WP_Filesystem_Direct $wp_filesystem */
        global $wp_filesystem;
        
        if (!$wp_filesystem || !is_object($wp_filesystem)) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
            
            // Load the filesystem
            if (!WP_Filesystem(false, false, true)) {
                throw new Exception('Could not initialize WordPress File System');
            }
            
            $this->_fileSystem = $wp_filesystem;
		}
    }
    
    /**
     * Get the WordPress FileSystem
     * 
     * @return \WP_Filesystem_Direct
     */
    public function fileSystem() {
        return $this->_fileSystem;
    }
    
    /**
     * Get the path to the upload directory
     * 
     * @return string
     */
    public function baseDir() {
        $uploadDir = wp_upload_dir();
        
        return rtrim(
            $this->_fileSystem->find_folder($uploadDir['basedir']), 
            '/\\'
        );
    }
    
    /**
     * Recursively copy the contents of a directory to another, creating the destination directory if necessary
     * 
     * @param string $sourceDir      Source directory
     * @param string $destinationDir Destination directory
     * @throws Exception
     */
    public function copy($sourceDir, $destinationDir) {
        // Verify the source folder
        if (!$this->_fileSystem->is_dir($sourceDir)) {
            throw new Excaption('Could not find "' . $sourceDir . '"');
        }
        
        // Create the destination folder
        if (!$this->_fileSystem->is_dir($destinationDir)) {
			if (!$this->_fileSystem->mkdir($destinationDir)) {
				throw new Excaption('Could not create "' . $destinationDir . '"');
			}
		}
        
        // Get the file list
        if (is_array($dirList = $this->_fileSystem->dirlist($sourceDir, false))) {
            foreach ($dirList as $itemData) {
                if ('d' === $itemData['type']) {
                    $this->copy(
                        $sourceDir . '/' . $itemData['name'], 
                        $destinationDir . '/' . $itemData['name']
                    );
                } else {
                    $this->_fileSystem->copy(
                        $sourceDir . '/' . $itemData['name'], 
                        $destinationDir . '/' . $itemData['name']
                    );
                }
            }
        }
    }
}

/*EOF*/