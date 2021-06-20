<?php

/**
 * Stephino_Rpg_Renderer_Ajax_Action_Premium
 * 
 * @title      Action::Premium
 * @desc       Premium actions
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
class Stephino_Rpg_Renderer_Ajax_Action_Premium extends Stephino_Rpg_Renderer_Ajax_Action {

    // Request keys
    const REQUEST_PACKAGE_ID         = 'packageId';
    const REQUEST_PACKAGE_CURRENY    = 'packageCurrency';
    const REQUEST_PACKAGE_PAYMENT_ID = 'packagePaymentId';
    const REQUEST_PACKAGE_PAYER_ID   = 'packagePayerId';
    const REQUEST_MODIFIER_ID        = 'modifierId';
    const REQUEST_MODIFIER_COUNT     = 'modifierCount';
    
    /**
     * Buy a premium package
     * 
     * @param array $data Data containing <ul>
     *     <li>packageId</li>
     *     <li>packageCurrency</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxPackageBuy($data) {
        $packageId = isset($data[self::REQUEST_PACKAGE_ID]) ? intval($data[self::REQUEST_PACKAGE_ID]) : null;
        $packageCurrency = isset($data[self::REQUEST_PACKAGE_CURRENY]) ? $data[self::REQUEST_PACKAGE_CURRENY] : null;
        
        // Validate the package ID
        if (null === $packageConfig = Stephino_Rpg_Config::get()->premiumPackages()->getById($packageId)) {
            throw new Exception(__('Invalid premium package', 'stephino-rpg'));
        }
        
        // Buy
        if (!in_array($packageCurrency, array(
            Stephino_Rpg_Renderer_Ajax::RESULT_RES_GOLD,
            Stephino_Rpg_Renderer_Ajax::RESULT_RES_RESEARCH,
            Stephino_Rpg_Renderer_Ajax::RESULT_RES_FIAT,
        ))) {
            throw new Exception(__('Invalid currency', 'stephino-rpg'));
        }
        
        // Prepare the result
        $redirectUrl = null;
        
        // Game currency
        switch ($packageCurrency) {
            case Stephino_Rpg_Renderer_Ajax::RESULT_RES_GOLD:
                if ($packageConfig->getCostGold() <= 0) {
                    throw new Exception(
                        sprintf(
                            __('You cannot buy this package with %s', 'stephino-rpg'),
                            Stephino_Rpg_Config::get()->core()->getResourceGoldName()
                        )
                    );
                }

                // Buy premium package
                self::spend(array(
                    Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GOLD => array(
                        Stephino_Rpg_Config::get()->core()->getResourceGoldName(), 
                        $packageConfig->getCostGold(),
                        Stephino_Rpg_Renderer_Ajax::RESULT_RES_GOLD,
                    )
                ));

                // Reward the gems
                self::_rewardGems($packageConfig, false);
                break;
            
            case Stephino_Rpg_Renderer_Ajax::RESULT_RES_RESEARCH:
                if ($packageConfig->getCostResearch() <= 0) {
                    throw new Exception(
                        sprintf(
                            __('You cannot buy this package with %s', 'stephino-rpg'),
                            Stephino_Rpg_Config::get()->core()->getResourceResearchName()
                        )
                    );
                }

                // Buy premium package
                self::spend(array(
                    Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_RESEARCH => array(
                        Stephino_Rpg_Config::get()->core()->getResourceResearchName(), 
                        $packageConfig->getCostResearch(),
                        Stephino_Rpg_Renderer_Ajax::RESULT_RES_RESEARCH,
                    )
                ));

                // Reward the gems
                self::_rewardGems($packageConfig, false);
                break;
            
            default:
                if ($packageConfig->getCostFiat() <= 0) {
                    throw new Exception(
                        sprintf(
                            __('You cannot buy this package with %s', 'stephino-rpg'),
                            Stephino_Rpg_Config::get()->core()->getPayPalCurrency()
                        )
                    );
                }

                // Prepare the invoice ID
                echo esc_html__('Proceeding to checkout...', 'stephino-rpg');
                $redirectUrl = Stephino_Rpg_Db::get()->modelInvoices()->create(
                    Stephino_Rpg_TimeLapse::get()->userId(), 
                    $packageConfig
                );
        }
        
        return Stephino_Rpg_Renderer_Ajax::wrap($redirectUrl);
    }
    
    /**
     * Request created after a PayPal payment redirect; confirm the payment and reward gems
     * 
     * @param array $data Data containing <ul>
     *     <li>packagePaymentId</li>
     *     <li>packagePayerId</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxPackageBuyConfirm($data) {
        $packagePaymentId = isset($data[self::REQUEST_PACKAGE_PAYMENT_ID]) ? trim($data[self::REQUEST_PACKAGE_PAYMENT_ID]) : null;
        $packagePayerId = isset($data[self::REQUEST_PACKAGE_PAYER_ID]) ? trim($data[self::REQUEST_PACKAGE_PAYER_ID]) : null;
        
        // Invalid values
        if (null === $packagePaymentId || null == $packagePayerId) {
            throw new Exception(__('Invalid request', 'stephino-rpg'));
        }
        
        // Execute the transaction (charge the player)
        $packageConfig = Stephino_Rpg_Db::get()->modelInvoices()->execute(
            Stephino_Rpg_TimeLapse::get()->userId(),
            $packagePaymentId, 
            $packagePayerId
        );
        
        // A reward is in order
        self::_rewardGems($packageConfig);
        return Stephino_Rpg_Renderer_Ajax::wrap(null);
    }
    
    /**
     * Buy a premium modifier
     * 
     * @param array $data Data containing <ul>
     *     <li>modifierId</li>
     *     <li>modifierCount</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxModifierBuy($data) {
        // Get the modifier count
        $premiumModifierCount = isset($data[self::REQUEST_MODIFIER_COUNT]) ? intval($data[self::REQUEST_MODIFIER_COUNT]) : 0;
        
        // Invalid count
        if ($premiumModifierCount < 1 || $premiumModifierCount > 10) {
            throw new Exception(__('Invalid number of premium modifiers', 'stephino-rpg'));
        }
        
        // Get the modifier ID
        $premiumModifierConfigId = isset($data[self::REQUEST_MODIFIER_ID]) ? intval($data[self::REQUEST_MODIFIER_ID]) : null;
        
        // Get the modifier configuration object
        $premiumModifierConfig = Stephino_Rpg_Config::get()->premiumModifiers()->getById($premiumModifierConfigId);
        
        // Invalid modifier
        if (null === $premiumModifierConfig) {
            throw new Exception(__('Invalid premium modifier', 'stephino-rpg'));
        }
        
        // You can only buy this once
        if (is_array(Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Queues::KEY)->getData())) {
            foreach (Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Queues::KEY)->getData() as $dbRow) {
                if (Stephino_Rpg_Db_Table_Queues::ITEM_TYPE_PREMIUM == $dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_TYPE]
                    && $dbRow[Stephino_Rpg_Db_Table_Queues::COL_QUEUE_ITEM_ID] == $premiumModifierConfigId) {
                    throw new Exception(__('Premium modifier already enabled', 'stephino-rpg'));
                }
            }
        }
        
        // Buy premium modifier
        self::spend(self::getCostData($premiumModifierConfig, $premiumModifierCount - 1));
        
        // Add the queue
        $queueId = Stephino_Rpg_Db::get()->modelQueues()->queuePremiumModifier(
            Stephino_Rpg_TimeLapse::get()->userId(),
            $premiumModifierConfigId, 
            $premiumModifierCount
        );
        
        // Inform the user
        echo sprintf(
            esc_html__('Premium modifier "%s" enabled', 'stephino-rpg'),
            '<b>' . $premiumModifierConfig->getName(true) . '</b>'
        );
        
        // Send the notification
        Stephino_Rpg_Db::get()->modelMessages()->notify(
            Stephino_Rpg_TimeLapse::get()->userId(), 
            Stephino_Rpg_Db_Model_Messages::TEMPLATE_NOTIF_PREMIUM_MODIFIER,
            array(
                // premiumModifierConfigId
                $premiumModifierConfig->getId()
            ),
            Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_ECONOMY
        );
        
        // Wrap the final result
        return Stephino_Rpg_Renderer_Ajax::wrap($queueId);
    } 
    
    /**
     * Preview premium modifier production, cost and other details
     * 
     * @param array $data Data containing <ul>
     *     <li>modifierId</li>
     *     <li>modifierCount</li>
     * </ul>
     * @throws Exception
     */
    public static function ajaxModifierPreview($data) {
        // Get the modifier count
        $premiumModifierCount = isset($data[self::REQUEST_MODIFIER_COUNT]) ? intval($data[self::REQUEST_MODIFIER_COUNT]) : 0;
        
        // Invalid count
        if ($premiumModifierCount < 1 || $premiumModifierCount > 10) {
            throw new Exception(__('Invalid number of premium modifiers', 'stephino-rpg'));
        }
        
        // Get the modifier ID
        $premiumModifierId = isset($data[self::REQUEST_MODIFIER_ID]) ? intval($data[self::REQUEST_MODIFIER_ID]) : null;
        
        // Get the modifier configuration object
        $premiumModifierConfig = Stephino_Rpg_Config::get()->premiumModifiers()->getById($premiumModifierId);
        
        // Invalid modifier
        if (null === $premiumModifierConfig) {
            throw new Exception(__('Invalid premium modifier', 'stephino-rpg'));
        }
        
        // Show the dialog
        require Stephino_Rpg_Renderer_Ajax_Dialog::dialogTemplatePath(
            Stephino_Rpg_Renderer_Ajax_Dialog_Premium::TEMPLATE_MODIFIERS_DETAILS
        );
        
        return Stephino_Rpg_Renderer_Ajax::wrap(true);
    }
    
    /**
     * Premium package bought successfully - award gems and optionally send a notification
     * 
     * @param Stephino_Rpg_Config_PremiumPackage $premiumPackageConfig Premium package configuration object
     * @param boolean                            $sendNotif            (optional) Send a notification; default <b>true</b>
     */
    protected static function _rewardGems($premiumPackageConfig, $sendNotif = true) {
        // Get the user data
        $userData = Stephino_Rpg_TimeLapse::get()->userData();

        // Get the reward
        $gemReward = $premiumPackageConfig->getGem();

        // Prepare the new balance
        $gemBalance = $userData[Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM] + $gemReward;

        // Add the reward
        Stephino_Rpg_Db::get()->tableUsers()->updateById(
            array(
                Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM => $gemBalance,
            ), 
            $userData[Stephino_Rpg_Db_Table_Users::COL_ID]
        );
        
        // Send the notification
        $sendNotif && Stephino_Rpg_Db::get()->modelMessages()->notify(
            $userData[Stephino_Rpg_Db_Table_Users::COL_ID], 
            Stephino_Rpg_Db_Model_Messages::TEMPLATE_NOTIF_PREMIUM_PACKAGE,
            array(
                // premiumPackageConfigId
                $premiumPackageConfig->getId()
            ),
            Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_ECONOMY
        );

        // Update the time-lapse references
        Stephino_Rpg_TimeLapse::get()->worker(Stephino_Rpg_TimeLapse_Resources::KEY)->updateRef(
            Stephino_Rpg_Db_Table_Users::COL_ID, 
            $userData[Stephino_Rpg_Db_Table_Users::COL_ID], 
            Stephino_Rpg_Db_Table_Users::COL_USER_RESOURCE_GEM, 
            $gemBalance
        );

        // Inform the user
        echo sprintf(
            esc_html__('Thank you for buying %s!', 'stephino-rpg'),
            '<b>' . number_format($gemReward) . '</b> ' . Stephino_Rpg_Config::get()->core()->getResourceGemName(true)
        );
    }
}

/*EOF*/