<?php
/**
 * Stephino_Rpg_Db_Model_Announcement
 * 
 * @title     Model:Announcement
 * @desc      Announcement Model
 * @copyright (c) 2020, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Db_Model_Announcement extends Stephino_Rpg_Db_Model {

    /**
     * Announcement Model Name
     */
    const NAME               = 'announcement';
    const MAX_LENGTH_TITLE   = 50;
    const MAX_LENGTH_CONTENT = 2000;
    const MAX_DAYS           = 30;
    
    /**
     * Get the transient key used to store the announcement
     * 
     * @return string
     */
    protected function _getTransientName() {
        return Stephino_Rpg::OPTION_CACHE . '_' . self::NAME;
    }
    
    /**
     * Get the active announcement
     * 
     * @param boolean $parseMarkDown (optional) Parse the announcement content with MarkDown; default <b>false</b>
     * @return array|null Array of <ul>
     * <li>(string) Announcement ID</li>
     * <li>(string) Announcement Title</li>
     * <li>(string) Announcement Content (MarkDown compatible)</li>
     * <li>(int) Announcement remaining time in seconds</li>
     * </ul> or <b>null</b> if there is no active announcement
     */
    public function get($parseMarkDown = false) {
        // Prepare the result
        $result = null;

        // Cache expired after 1 hour
        if (false !== $announceJson = get_site_transient($this->_getTransientName())) {
            $announceArray = @json_decode($announceJson, true);

            // Valid result stored
            if (is_array($announceArray) || 4 === count($announceArray)) {
                // Implicit conversion to string
                $result = array_map('trim', $announceArray);

                // Get the time remaining in seconds
                $result[3] = (int) $result[3] - time();
                
                // Parse the content
                if ($parseMarkDown) {
                    $result[2] = Stephino_Rpg_Parsedown::instance()->parse($result[2]);
                }
            }
        }
        return $result;
    }
    
    /**
     * Create/Update an announcement
     * 
     * @param string $annTitle   Announcement title
     * @param string $annContent Announcement content
     * @param int    $annDays    Announcement validity in days
     * @throws Exception
     * @return boolean
     */
    public function set($annTitle, $annContent, $annDays) {
        // Sanitize and validate title
        if (!strlen($annTitle = Stephino_Rpg_Utils_Lingo::cleanup($annTitle))) {
            throw new Exception(__('Announcement title is mandatory', 'stephino-rpg'));
        }
        if (strlen($annTitle) > self::MAX_LENGTH_TITLE) {
            throw new Exception(
                sprintf(
                    __('Maximum title length is %d characters', 'stephino-rpg'),
                    self::MAX_LENGTH_TITLE
                )
            );
        }
        
        // Sanitize and validate content
        if (!strlen($annContent = Stephino_Rpg_Utils_Lingo::cleanup($annContent))) {
            throw new Exception(__('Announcement content is mandatory', 'stephino-rpg'));
        }
        if (strlen($annContent) > self::MAX_LENGTH_CONTENT) {
            throw new Exception(
                sprintf(
                    __('Maximum content length is %d characters', 'stephino-rpg'),
                    self::MAX_LENGTH_TITLE
                )
            );
        }
        
        // Sanitize and validate duration
        $annDays = abs((int) $annDays);
        if ($annDays < 1 || $annDays > self::MAX_DAYS) {
            throw new Exception(
                sprintf(
                    __('Announcements last between 1 and %d days', 'stephino-rpg'),
                    self::MAX_LENGTH_TITLE
                )
            );
        }
        
        // Validity in seconds
        $annValidity = $annDays * 86400;
        
        // Store the value
        return set_site_transient(
            $this->_getTransientName(),
            json_encode(array(
                uniqid(),
                $annTitle,
                $annContent,
                time() + $annValidity
            )), 
            $annValidity
        );
    }
    
    /**
     * Delete the announcement
     * 
     * @return boolean
     */
    public function del() {
        return delete_site_transient($this->_getTransientName());
    }
}

/*EOF*/