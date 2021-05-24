<?php

/**
 * Stephino_Rpg_Renderer_Ajax_Admin
 * 
 * @title      Admin actions
 * @desc       Perform actions that are only available to the site admin
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Renderer_Ajax_Admin {

    /**
     * Request keys
     */
    const REQUEST_STATS_TYPE     = 'statsType';
    const REQUEST_STATS_YEAR     = 'statsYear';
    const REQUEST_STATS_MONTH    = 'statsMonth';
    const REQUEST_ANN_TITLE      = 'annTitle';
    const REQUEST_ANN_CONTENT    = 'annContent';
    const REQUEST_ANN_DAYS       = 'annDays';
    const REQUEST_THEME_NAME     = 'themeName';
    const REQUEST_THEME_TEMPLATE = 'themeTemplate';
    const REQUEST_THEME_SLUG     = 'themeSlug';
    const REQUEST_EXPORT_TEST    = 'exportTest';
    const REQUEST_FILE_PATH      = 'filePath';
    const REQUEST_FILE_TEXT      = 'fileText';
    
    /**
     * Get the configuration definition
     * 
     * @return array
     */
    public static function ajaxAdminGetConfig() {
        return Stephino_Rpg_Config::definition();
    }
    
    /**
     * Get the statistics
     * 
     * @param array $data
     * @return array
     * @throws Exception
     */
    public static function ajaxAdminGetStats($data) {
        if (!Stephino_Rpg_Cache_User::get()->isGameAdmin()) {
            throw new Exception(__('You do not have access to the Dashboard', 'stephino-rpg'));
        }
        
        return Stephino_Rpg_Db::get()->modelStatistics()->export(
            isset($data[self::REQUEST_STATS_TYPE]) ? trim($data[self::REQUEST_STATS_TYPE]) : null,
            isset($data[self::REQUEST_STATS_YEAR]) ? (int) $data[self::REQUEST_STATS_YEAR] : null,
            isset($data[self::REQUEST_STATS_MONTH]) ? (int) $data[self::REQUEST_STATS_MONTH] : null
        );
    }
    
    /**
     * Set an announcement
     * 
     * @param array $data
     * @return array
     * @throws Exception
     */
    public static function ajaxAdminSetAnnouncement($data) {
        if (!Stephino_Rpg_Cache_User::get()->isGameAdmin()) {
            throw new Exception(__('You do not have access to the Announcements', 'stephino-rpg'));
        }
        
        // Set the announcement
        Stephino_Rpg_Db::get()->modelAnnouncement()->set(
            isset($data[self::REQUEST_ANN_TITLE]) ? trim($data[self::REQUEST_ANN_TITLE]) : '',
            isset($data[self::REQUEST_ANN_CONTENT]) ? trim($data[self::REQUEST_ANN_CONTENT]) : '',
            isset($data[self::REQUEST_ANN_DAYS]) ? abs((int) $data[self::REQUEST_ANN_DAYS]) : 1
        );
        
        // Export the stats
        return Stephino_Rpg_Db::get()->modelStatistics()->export(
            Stephino_Rpg_Db_Model_Statistics::EXPORT_ANNOUNCEMENT
        );
    }
    
    /**
     * Delete the announcement
     * 
     * @return array
     * @throws Exception
     */
    public static function ajaxAdminDelAnnouncement() {
        if (!Stephino_Rpg_Cache_User::get()->isGameAdmin()) {
            throw new Exception(__('You do not have access to the Announcements', 'stephino-rpg'));
        }
        
        // Set the announcement
        Stephino_Rpg_Db::get()->modelAnnouncement()->del();
        
        // Export the stats
        return Stephino_Rpg_Db::get()->modelStatistics()->export(
            Stephino_Rpg_Db_Model_Statistics::EXPORT_ANNOUNCEMENT
        );
    }

    /**
     * Export the configuration data in JSON format
     * 
     * @return string JSON
     * @throws Exception
     */
    public static function ajaxAdminExportConfig() {
        if (!Stephino_Rpg_Cache_User::get()->isGameAdmin()) {
            throw new Exception(__('You do not have permission to export the game configuration', 'stephino-rpg'));
        }
        return Stephino_Rpg_Config::export(false, true);
    }
    
    /**
     * Reset the game configuration to default values
     */
    public static function ajaxAdminResetConfig() {
        if (!Stephino_Rpg_Cache_User::get()->isGameAdmin()) {
            throw new Exception(__('You do not have permission to reset the game configuration', 'stephino-rpg'));
        }
        
        return Stephino_Rpg_Config::reset();
    }
    
    /**
     * Restart the game, erasing all game progress for all players
     * 
     * @throws Exception
     */
    public static function ajaxAdminRestartGame() {
        if (!Stephino_Rpg_Cache_User::get()->isGameAdmin()) {
            throw new Exception(__('You do not have permission to restart the game', 'stephino-rpg'));
        }
        
        // Remove all options (except for the PRO-level config)
        Stephino_Rpg_Cache_Game::get()->purge(false);

        // Drop all tables
        Stephino_Rpg_Db::get()->purge();
    }

    /**
     * Save the configuration data
     * 
     * @param array $data Configuration array
     * @throws Exception
     */
    public static function ajaxAdminSetConfig($data) {
        if (!Stephino_Rpg_Cache_User::get()->isGameAdmin()) {
            throw new Exception(__('(DEMO) You are free to experiment but your changes will not be saved', 'stephino-rpg'));
        }

        if (!Stephino_Rpg::get()->isPro()) {
            throw new Exception(__('You need to unlock the game to save your changes', 'stephino-rpg'));
        }
        
        // Get the data
        if (!is_array($data) || !count($data)) {
            throw new Exception(__('Invalid configuration object', 'stephino-rpg'));
        }

        // Set the data
        Stephino_Rpg_Config::set($data);

        // Validate and save the data
        Stephino_Rpg_Config::save(true);

        // Regenerate the CSS animation rules
        Stephino_Rpg_Renderer_Ajax_Css::generate();
    }
    
    /**
     * Create a new theme
     * 
     * @param array $data
     * @return boolean
     * @throws Exception
     */
    public static function ajaxAdminThemeCreate($data) {
        if (!Stephino_Rpg_Cache_User::get()->isGameAdmin()) {
            throw new Exception(__('Permission denied', 'stephino-rpg'));
        }
        
        // Get the arguments
        $themeSlug = isset($data[self::REQUEST_THEME_TEMPLATE]) ? trim($data[self::REQUEST_THEME_TEMPLATE]) : '';
        $themeName = isset($data[self::REQUEST_THEME_NAME]) ? trim($data[self::REQUEST_THEME_NAME]) : '';
        
        // Sanitize the name
        $themeName = trim(
            preg_replace(
                array('%[^\w \-]+%i', '% {2,}%'), 
                array('', ' '), 
                $themeName
            )
        );
        
        // Validate the template
        if (null === Stephino_Rpg_Utils_Themes::getTheme($themeSlug)) {
            throw new Exception(__('Invalid theme template', 'stephino-rpg'));
        }
        
        // Duplicate this theme
        return Stephino_Rpg_Utils_Themes::getTheme($themeSlug)->duplicate($themeName);
    }
    
    /**
     * Delete an existing theme
     * 
     * @param array $data
     * @return boolean
     * @throws Exception
     */
    public static function ajaxAdminThemeDelete($data) {
        if (!Stephino_Rpg_Cache_User::get()->isGameAdmin()) {
            throw new Exception(__('Permission denied', 'stephino-rpg'));
        }
        
        $themeSlug = isset($data[self::REQUEST_THEME_SLUG]) ? trim($data[self::REQUEST_THEME_SLUG]) : '';
        
        // Validate the theme
        if (null === $themeObject = Stephino_Rpg_Utils_Themes::getTheme($themeSlug)) {
            throw new Exception(__('Theme does not exist', 'stephino-rpg'));
        }
        
        // Remove this theme
        return $themeObject->delete();
    }
    
    /**
     * Activate a theme and restart the game
     * 
     * @param array $data
     * @throws Exception
     */
    public static function ajaxAdminThemeActivate($data) {
        if (!Stephino_Rpg_Cache_User::get()->isGameAdmin()) {
            throw new Exception(__('Permission denied', 'stephino-rpg'));
        }
        
        $themeSlug = isset($data[self::REQUEST_THEME_SLUG]) ? trim($data[self::REQUEST_THEME_SLUG]) : '';
        
        // Validate the theme
        if (null === $themeObject = Stephino_Rpg_Utils_Themes::getTheme($themeSlug)) {
            throw new Exception(__('Theme does not exist', 'stephino-rpg'));
        }
        
        // Theme already active
        if ($themeObject->isActive()) {
            throw new Exception(__('Theme already activated', 'stephino-rpg'));
        }
        
        // Activate this theme
        return $themeObject->activate();
    }

    /**
     * Get all the files within a theme
     * 
     * @param array $data
     * @return array
     * @throws Exception
     */
    public static function ajaxAdminThemeEditList($data) {
        if (!Stephino_Rpg_Cache_User::get()->isGameAdmin()) {
            throw new Exception(__('Permission denied', 'stephino-rpg'));
        }
        
        $themeSlug = isset($data[self::REQUEST_THEME_SLUG]) ? trim($data[self::REQUEST_THEME_SLUG]) : '';
        
        // Validate the theme
        if (null === $themeObject = Stephino_Rpg_Utils_Themes::getTheme($themeSlug)) {
            throw new Exception(__('Theme does not exist', 'stephino-rpg'));
        }
        
        // Cannot edit this theme
        if ($themeObject->isDefault()) {
            throw new Exception(__('Cannot edit the default theme', 'stephino-rpg'));
        }
        
        // List the files in this theme
        return Stephino_Rpg_Utils_Folder::get()
            ->fileSystem()
            ->dirlist($themeObject->getFilePath(), false, true);
    }
    
    /**
     * Upload a file
     * 
     * @param array $data
     * @throws Exception
     */
    public static function ajaxAdminThemeEditUpload($data) {
        if (!Stephino_Rpg_Cache_User::get()->isGameAdmin()) {
            throw new Exception(__('Permission denied', 'stephino-rpg'));
        }
        
        // Prepare the file path and slug
        $filePath = isset($data[self::REQUEST_FILE_PATH]) ? trim($data[self::REQUEST_FILE_PATH]) : '';
        $themeSlug = isset($data[self::REQUEST_THEME_SLUG]) ? trim($data[self::REQUEST_THEME_SLUG]) : '';
        
        // Validate the theme
        if (null === $themeObject = Stephino_Rpg_Utils_Themes::getTheme($themeSlug)) {
            throw new Exception(__('Theme does not exist', 'stephino-rpg'));
        }
        
        // Cannot edit this theme
        if ($themeObject->isDefault()) {
            throw new Exception(__('Cannot edit the default theme', 'stephino-rpg'));
        }
        
        // No files uploaded
        if (!isset($_FILES) || !isset($_FILES['file']) || !isset($_FILES['file']['tmp_name'])) {
            throw new Exception(__('No file was uploaded', 'stephino-rpg'));
        }
        
        // Get the file extension
        if (!preg_match('%^.*?\.(\w+)$%i', $filePath, $matches)) {
            throw new Exception(__('Invalid file name', 'stephino-rpg'));
        }
        
        // Validate extension
        if (!in_array($matches[1], array('png', 'jpg', 'cur', 'mp3', 'webm', 'mp4'))) {
            throw new Exception(__('Invalid file extension', 'stephino-rpg'));
        }
        
        // Validate the uploaded file extension
        if (!preg_match('%\.' . $matches[1] . '$%i', $_FILES['file']['name'])) {
            throw new Exception(
                sprintf(
                    __('Invalid file extension, expecting "%s"', 'stephino-rpg'),
                    '.' . strtolower($matches[1])
                )
            );
        }
        
        // Check file on disk
        $themeFilePath = $themeObject->getFilePath($filePath);
        if (!Stephino_Rpg_Utils_Folder::get()->fileSystem()->is_file($themeFilePath)) {
            throw new Exception(__('Invalid file', 'stephino-rpg'));
        }
        
        // Move uploaded file
        Stephino_Rpg_Utils_Folder::get()
            ->fileSystem()
            ->move(
                $_FILES['file']['tmp_name'],
                $themeFilePath,
                true
            );
        
        // Force cache reset and PWA app version change, forcing local storage reset
        if ($themeObject->isActive()) {
            Stephino_Rpg_Cache_Game::get()->write(Stephino_Rpg_Cache_Game::KEY_MEDIA_CHANGED, time());
        }
        return __('File uploaded successfully', 'stephino-rpg');
    }
    
    /**
     * Save a text file
     * 
     * @param array $data
     * @throws Exception
     */
    public static function ajaxAdminThemeEditText($data) {
        if (!Stephino_Rpg_Cache_User::get()->isGameAdmin()) {
            throw new Exception(__('Permission denied', 'stephino-rpg'));
        }
        
        // Prepare the file path and slug
        $filePath = isset($data[self::REQUEST_FILE_PATH]) ? trim($data[self::REQUEST_FILE_PATH]) : '';
        $fileText = isset($data[self::REQUEST_FILE_TEXT]) ? trim($data[self::REQUEST_FILE_TEXT]) : '';
        $themeSlug = isset($data[self::REQUEST_THEME_SLUG]) ? trim($data[self::REQUEST_THEME_SLUG]) : '';
        
        // Validate the theme
        if (null === $themeObject = Stephino_Rpg_Utils_Themes::getTheme($themeSlug)) {
            throw new Exception(__('Theme does not exist', 'stephino-rpg'));
        }
        
        // Cannot edit this theme
        if ($themeObject->isDefault()) {
            throw new Exception(__('Cannot edit the default theme', 'stephino-rpg'));
        }
        
        // Get the file extension
        if (!preg_match('%^.*?\.(\w+)$%i', $filePath, $matches)) {
            throw new Exception(__('Invalid file name', 'stephino-rpg'));
        }
        
        // Validate text
        switch ($matches[1]) {
            case 'txt':
            case 'css':
                // Nothing to check
                break;
            
            case 'json':
                if ('null' !== $fileText && null === json_decode($fileText, true)) {
                    throw new Exception(__('Invalid JSON', 'stephino-rpg'));
                }
                break;
            
            default:
                throw new Exception(__('Invalid file extension', 'stephino-rpg'));
        }
        
        // Check file on disk
        $themeFilePath = $themeObject->getFilePath($filePath);
        if (!Stephino_Rpg_Utils_Folder::get()->fileSystem()->is_file($themeFilePath)) {
            throw new Exception(__('Invalid file', 'stephino-rpg'));
        }
        
        // Write to file
        Stephino_Rpg_Utils_Folder::get()
            ->fileSystem()
            ->put_contents(
                $themeFilePath,
                $fileText
            );
        
        // Force cache reset and PWA app version change, forcing local storage reset
        if ($themeObject->isActive()) {
            Stephino_Rpg_Cache_Game::get()->write(Stephino_Rpg_Cache_Game::KEY_MEDIA_CHANGED, time());
        }
        return __('File saved successfully', 'stephino-rpg');
    }
    
    /**
     * Output a theme's ZIP archive directly to the browser
     * 
     * @param array $data
     * @throws Exception
     */
    public static function ajaxAdminThemeExport($data) {
        if (!Stephino_Rpg_Cache_User::get()->isGameAdmin()) {
            throw new Exception(__('Permission denied', 'stephino-rpg'));
        }
        $themeSlug = isset($data[self::REQUEST_THEME_SLUG]) ? trim($data[self::REQUEST_THEME_SLUG]) : '';
        $exportTest = isset($data[self::REQUEST_EXPORT_TEST]) ? !!$data[self::REQUEST_EXPORT_TEST] : false;
        
        // Validate the theme
        if (null === $themeObject = Stephino_Rpg_Utils_Themes::getTheme($themeSlug)) {
            throw new Exception(__('Theme does not exist', 'stephino-rpg'));
        }
        
        // Cannot export this theme
        if ($themeObject->isDefault()) {
            throw new Exception(__('Cannot export the default theme', 'stephino-rpg'));
        }
        
        // Get the file list
        $themeFiles = Stephino_Rpg_Utils_Folder::get()
            ->fileSystem()
            ->dirlist($themeObject->getFilePath(), false, true);
        if (!is_array($themeFiles)) {
            throw new Exception(__('Could not export theme', 'stephino-rpg'));
        }
        
        // Send the archive to the user
        if (!$exportTest) {
            // Clean the output buffer
            if (ob_get_length()) {
                ob_end_clean();
            }
        
            // Direct output
            $zipOptions = (new Stephino_Zip_Option_Archive())
                ->setSendHttpHeaders(true)
                ->setFlushOutput(true)
                ->setComment(
                    Stephino_Rpg::PLUGIN_NAME . ' theme "' . $themeObject->getName() . '" is licensed under ' . Stephino_Rpg_Theme::LICENSE_NAME . '.' . PHP_EOL
                    . 'Copyright (c) 2021 Stephino (https://stephino.com).' . PHP_EOL
                    . 'Derivative work by ' . $themeObject->getAuthor() . ' (' . $themeObject->getAuthorUrl() . ').' . PHP_EOL . PHP_EOL
                    
                    . 'To view a copy of this license, view the attached "license.md" file' . PHP_EOL 
                    . 'or visit ' . Stephino_Rpg_Theme::LICENSE_URL . '.'
                );

            // Files share the archive compression method and creation time 
            $zipFileOptions = (new Stephino_Zip_Option_File())
                ->defaultTo($zipOptions);

            // Set the file name
            $zipObject = new Stephino_Zip(
                Stephino_Rpg::PLUGIN_SLUG .  '-theme-' . $themeSlug . '.zip', 
                $zipOptions
            );

            // Flatten the files list
            $fileWalker = function($fileList, $root = '') use(&$fileWalker) {
                $result = array();
                $prefix = strlen($root) ? ($root . '/') : '';

                foreach ($fileList as $fileData) {
                    if ('f' === $fileData['type']) {
                        $result[] =  $prefix . $fileData['name'];
                    } else {
                        $result = array_merge(
                            $fileWalker($fileData['files'], $prefix . $fileData['name']),
                            $result
                        );
                    }
                }

                return $result;
            };
            
            // Flatten the dirlist and add files one by one
            foreach ($fileWalker($themeFiles) as $relativePath) {
                $zipObject->addFileFromPath(
                    $themeSlug . '/' . $relativePath, 
                    $themeObject->getFilePath($relativePath),
                    $zipFileOptions
                );
            }

            // Close the archive
            $zipObject->finish();

            // Stop here
            exit();
        }
    }
    
    public static function ajaxAdminThemeImport() {
        if (!Stephino_Rpg_Cache_User::get()->isGameAdmin()) {
            throw new Exception(__('Permission denied', 'stephino-rpg'));
        }
        
        // No files uploaded
        if (!isset($_FILES) || !isset($_FILES['file']) || !isset($_FILES['file']['tmp_name'])) {
            throw new Exception(__('No file was uploaded', 'stephino-rpg'));
        }
        
        // Validate file name
        if (!preg_match('%^' . Stephino_Rpg::PLUGIN_SLUG . '-theme-([a-z\-\d]+)\.zip$%', $_FILES['file']['name'], $matches)) {
            throw new Exception(__('Invalid theme archive', 'stephino-rpg'));
        }
        
        // Get the theme slug
        $themeSlug = $matches[1];
        if (null !== Stephino_Rpg_Utils_Themes::getTheme($themeSlug)) {
            throw new Exception(__('Theme already exists', 'stephino-rpg'));
        }
        
        // Prepare the temporary path
        $tempPath = Stephino_Rpg_Utils_Themes::getPath(false);
        
        // Prepare the parent structure in case it does not exist
        for ($level = 2; $level >= 1; $level--) {
            if (!Stephino_Rpg_Utils_Folder::get()->fileSystem()->is_dir(dirname($tempPath, $level))) {
                Stephino_Rpg_Utils_Folder::get()->fileSystem()->mkdir(dirname($tempPath, $level));
            }
        }
        
        // Folder needs clean-up
        if (Stephino_Rpg_Utils_Folder::get()->fileSystem()->is_dir($tempPath)) {
            Stephino_Rpg_Utils_Folder::get()->fileSystem()->delete($tempPath, true);
        }
        Stephino_Rpg_Utils_Folder::get()->fileSystem()->mkdir($tempPath);
        
        // Extract archive
        unzip_file($_FILES['file']['tmp_name'], $tempPath);
        
        // Did not find the theme
        if (!Stephino_Rpg_Utils_Folder::get()->fileSystem()->is_dir($tempPath . '/' . $themeSlug)
            || !Stephino_Rpg_Utils_Folder::get()->fileSystem()->is_file($tempPath . '/' . $themeSlug . '/' . Stephino_Rpg_Theme::FILE_CONFIG)
            || !Stephino_Rpg_Utils_Folder::get()->fileSystem()->is_file($tempPath . '/' . $themeSlug . '/' . Stephino_Rpg_Theme::FILE_ABOUT)
            || !Stephino_Rpg_Utils_Folder::get()->fileSystem()->is_file($tempPath . '/' . $themeSlug . '/' . Stephino_Rpg_Theme::FILE_LICENSE)) {
            Stephino_Rpg_Utils_Folder::get()->fileSystem()->delete($tempPath, true);
            throw new Exception(__('Invalid archive structure', 'stephino-rpg'));
        }
        
        // Move the theme
        Stephino_Rpg_Utils_Folder::get()->fileSystem()->move(
            $tempPath . '/' . $themeSlug,
            Stephino_Rpg_Utils_Themes::getPath($themeSlug),
            true
        );
        
        // Final clean-up
        Stephino_Rpg_Utils_Folder::get()->fileSystem()->delete($tempPath, true);
    }
}

/*EOF*/