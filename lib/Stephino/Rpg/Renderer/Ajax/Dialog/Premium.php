<?php

/**
 * ThemeWarlock - Stephino_Rpg_Renderer_Ajax_Dialog_Premium
 * 
 * @title      Dialog::Premium
 * @desc       Premium dialogs
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    ThemeWarlock
 * @since      TW 1.0
 */
class Stephino_Rpg_Renderer_Ajax_Dialog_Premium extends Stephino_Rpg_Renderer_Ajax_Dialog {

    // Dialog templates
    const TEMPLATE_PACKAGES          = 'premium/premium-packages';
    const TEMPLATE_MODIFIERS         = 'premium/premium-modifiers';
    const TEMPLATE_MODIFIERS_DETAILS = 'premium/premium-modifiers-details';
    
    /**
     * View premium packages
     * 
     * @throws Exception
     */
    public static function ajaxPackageList() {
        self::setModalSize(self::MODAL_SIZE_LARGE);
        
        if (!count(Stephino_Rpg_Config::get()->premiumPackages()->getAll())) {
            throw new Exception(__('No premium packages available', 'stephino-rpg'));
        }
        
        // Get the user data
        $userData = Stephino_Rpg_TimeLapse::get()->userData();
        
        require self::dialogTemplatePath(self::TEMPLATE_PACKAGES);
        return Stephino_Rpg_Renderer_Ajax::wrap(array(
            self::RESULT_TITLE => '&#11088; ' . __('Packages', 'stephino-rpg'),
        ));
    }
    
    /**
     * View premium modifiers
     * 
     * @throws Exception
     */
    public static function ajaxModifiersList() {
        if (!count(Stephino_Rpg_Config::get()->premiumModifiers()->getAll())) {
            throw new Exception(__('No premium modifiers available', 'stephino-rpg'));
        }
        
        // Store the premium packages
        $premiumEnabled = array();
        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Queues::KEY)->getData())) {
            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Queues::KEY)->getData() as $queueRow) {
                if ($queueRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_TYPE] == Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_PREMIUM) {
                    $premiumEnabled[$queueRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID]] = array(
                        // Count
                        intval($queueRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_QUANTITY]),
                        // Total time
                        intval($queueRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_DURATION]),
                        // Remaining time
                        intval($queueRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_TIME]) - time(),
                    );
                }
            }
        }
        
        // Show the dialog
        require self::dialogTemplatePath(self::TEMPLATE_MODIFIERS);
        
        return Stephino_Rpg_Renderer_Ajax::wrap(array(
            self::RESULT_TITLE => '&#11088; ' . __('Modifiers', 'stephino-rpg'),
        ));
    }
}

/* EOF */