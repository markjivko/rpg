<?php

/**
 * Stephino_Rpg_Renderer_Ajax_Dialog
 * 
 * @title      Dialogs Renderer - delivered through AJAX
 * @desc       Creates Dialogs
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Renderer_Ajax_Dialog {
    
    // Common templates
    const TEMPLATE_COMMON_COSTS            = 'common/common-costs';
    const TEMPLATE_COMMON_ENTITY_MILITARY  = 'common/common-entity-military';
    const TEMPLATE_COMMON_ENTITY_PREPARE   = 'common/common-entity-prepare';
    const TEMPLATE_COMMON_PAGINATION       = 'common/common-pagination';
    const TEMPLATE_COMMON_PRODUCTION       = 'common/common-production';
    const TEMPLATE_COMMON_REQUIREMENTS     = 'common/common-requirements';
    
    // Common request keys
    const REQUEST_CITY_ID     = 'cityId';
    const REQUEST_COMMON_ARGS = 'commonArgs';
    
    // Result keys
    const RESULT_TITLE = 'title';
    const RESULT_DATA  = 'data';
    
    // Modal sizes
    const MODAL_SIZE_LARGE  = true;
    const MODAL_SIZE_NORMAL = null;
    const MODAL_SIZE_SMALL  = false;
    
    /**
     * Modal Size: false for "md", null for normal, true for "xl"
     *
     * @var boolean|null
     */
    protected static $_modalSize = null;
    
    /**
     * Get the dialog size<ul>
     *     <li><b>false</b> for "md"</li>
     *     <li><b>null</b> for normal</li>
     *     <li><b>true</b> for "xl"</li>
     * </ul>
     * 
     * @return boolean|null
     */
    public static function getModalSize() {
        return self::$_modalSize;
    }
    
    /**
     * Set the modal size<ul>
     *     <li><b>self::MODAL_SIZE_SMALL</b> for "md"</li>
     *     <li><b>self::MODAL_SIZE_NORMAL</b> for normal</li>
     *     <li><b>self::MODAL_SIZE_LARGE</b> for "xl"</li>
     * </ul>
     * 
     * @param boolean|null $modalSize Dialog size flag
     */
    public static function setModalSize($modalSize = null) {
        self::$_modalSize = (null === $modalSize ? null : (boolean) $modalSize);
    }
    
    /**
     * Get a dialog template path
     * 
     * @param string $templateName Dialog template name
     * @return string|null
     */
    public static function dialogTemplatePath($templateName) {
        if (!is_file($templatePath = STEPHINO_RPG_ROOT . '/' . Stephino_Rpg::FOLDER_UI_TPL . '/dialogs/' . $templateName . '.php')) {
            throw new Exception(
                sprintf(
                    'Dialog template "%s" not found',
                    $templateName
                )
            );
        }
        return $templatePath;
    }
}

/*EOF*/