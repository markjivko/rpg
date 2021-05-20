<?php

/**
 * Stephino_Rpg_Renderer_Ajax_Media
 * 
 * @title      Media driver
 * @desc       Retrieves files location based on the current theme
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Renderer_Ajax_Media {

    /**
     * Find file information for the requested media
     * 
     * @return string File path
     * @throws Exception
     */
    public static function ajaxMedia() {
        if (!is_file($filePath = Stephino_Rpg_Utils_Themes::getActive()->getFilePath(
            Stephino_Rpg_Utils_Sanitizer::getMediaPath()
        ))) {
            throw new Exception('File not found: ' . $filePath);
        }
        
        return $filePath;
    }
}

/*EOF*/