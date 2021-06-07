<?php
/**
 * Stephino_Rpg_Db_Model_Invoices
 * 
 * @title     Model:Invoices
 * @desc      Invoices Model
 * @copyright (c) 2021, Stephino
 * @author    Mark Jivko <stephino.team@gmail.com>
 * @package   stephino-rpg
 * @license   GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */

class Stephino_Rpg_Db_Model_Invoices extends Stephino_Rpg_Db_Model {

    // Prepare the allowed currencies
    const CURRENCY_AUD = 'AUD';
    const CURRENCY_BRL = 'BRL';
    const CURRENCY_CAD = 'CAD';
    const CURRENCY_CZK = 'CZK';
    const CURRENCY_DKK = 'DKK';
    const CURRENCY_EUR = 'EUR';
    const CURRENCY_HKD = 'HKD';
    const CURRENCY_HUF = 'HUF';
    const CURRENCY_INR = 'INR';
    const CURRENCY_ILS = 'ILS';
    const CURRENCY_JPY = 'JPY';
    const CURRENCY_MYR = 'MYR';
    const CURRENCY_MXN = 'MXN';
    const CURRENCY_TWD = 'TWD';
    const CURRENCY_NZD = 'NZD';
    const CURRENCY_NOK = 'NOK';
    const CURRENCY_PHP = 'PHP';
    const CURRENCY_PLN = 'PLN';
    const CURRENCY_GBP = 'GBP';
    const CURRENCY_RUB = 'RUB';
    const CURRENCY_SGD = 'SGD';
    const CURRENCY_SEK = 'SEK';
    const CURRENCY_CHF = 'CHF';
    const CURRENCY_THB = 'THB';
    const CURRENCY_USD = 'USD';
    
    /**
     * Allowed currencies and their names
     * 
     * @var array
     */
    const CURRENCIES = array(
        self::CURRENCY_AUD => 'Australian dollar',
        self::CURRENCY_BRL => 'Brazilian real',
        self::CURRENCY_CAD => 'Canadian dollar',
        self::CURRENCY_CZK => 'Czech koruna',
        self::CURRENCY_DKK => 'Danish krone',
        self::CURRENCY_EUR => 'Euro',
        self::CURRENCY_HKD => 'Hong Kong dollar',
        self::CURRENCY_HUF => 'Hungarian forint',
        self::CURRENCY_INR => 'Indian rupee',
        self::CURRENCY_ILS => 'Israeli new shekel',
        self::CURRENCY_JPY => 'Japanese yen',
        self::CURRENCY_MYR => 'Malaysian ringgit',
        self::CURRENCY_MXN => 'Mexican peso',
        self::CURRENCY_TWD => 'New Taiwan dollar',
        self::CURRENCY_NZD => 'New Zealand dollar',
        self::CURRENCY_NOK => 'Norwegian krone',
        self::CURRENCY_PHP => 'Philippine peso',
        self::CURRENCY_PLN => 'Polish złoty',
        self::CURRENCY_GBP => 'Pound sterling',
        self::CURRENCY_RUB => 'Russian ruble',
        self::CURRENCY_SGD => 'Singapore dollar',
        self::CURRENCY_SEK => 'Swedish krona',
        self::CURRENCY_CHF => 'Swiss franc',
        self::CURRENCY_THB => 'Thai baht',
        self::CURRENCY_USD => 'United States dollar',
    );
    
    /**
     * Invoice details
     */
    const INVOICE_ID           = 'invoice_id';
    const INVOICE_USER_ID      = 'invoice_user_id';
    const INVOICE_USER_WP_ID   = 'invoice_user_wp_id';
    const INVOICE_USER_WP_NAME = 'invoice_user_wp_name';
    const INVOICE_USER_WP_ICON = 'invoice_user_wp_icon';
    const INVOICE_PAYMENT_ID   = 'invoice_payment_id';
    const INVOICE_PACKAGE_ID   = 'invoice_package_id';
    const INVOICE_PACKAGE_NAME = 'invoice_package_name';
    const INVOICE_VALUE        = 'invoice_value';
    const INVOICE_CURRENCY     = 'invoice_currency';
    const INVOICE_PAID         = 'invoice_paid';
    const INVOICE_DATE         = 'invoice_date';
    
    /**
     * Invoices Model Name
     */
    const NAME = 'invoices';

    /**
     * Create a new invoice using the PayPal API
     * 
     * @return string Approval link
     * @throws Exception
     */
    public function create($userId, Stephino_Rpg_Config_PremiumPackage $packageConfig) {
        if (!function_exists('curl_version')) {
            throw new Exception(sprintf(__('%s is not enabled on your server', 'stephino-rpg'), '<b>cURL</b>'));
        }
        if (!class_exists('\Stephino\PayPal\Api\Payment')) {
            throw new Exception(__('Plugin not activated', 'stephino-rpg'));
        }
        
        // Sanitize the user ID
        $userId = abs((int) $userId);
        if ($userId <= 0) {
            throw new Exception(__('Invalid user ID', 'stephino-rpg'));
        }
        
        // Remove other pending payments
        $this->getDb()->tableMessages()->deleteInvoicePending($userId);
        
        // Validate the cost
        if (null === $packageConfig || 0 == $packageConfig->getCostFiat()) {
            throw new Exception(__('Cost not set for this package', 'stephino-rpg'));
        }
        
        // PayPal not enabled
        if (null === Stephino_Rpg_Config::get()->core()->getPayPalClientId() 
            || null === Stephino_Rpg_Config::get()->core()->getPayPalClientSecret()) {
            throw new Exception(__('PayPal not enabled', 'stephino-rpg'));
        }
        
        // Prepare the invoice ID
        $invoiceContent = $userId . '-' . $packageConfig->getId() . '-' . md5(uniqid('', true)) . '-' . round($packageConfig->getCostFiat(), 2) . '-' . Stephino_Rpg_Config::get()->core()->getPayPalCurrency();
        
        // Prepare the return URL
        $returnUrl = Stephino_Rpg_Utils_Media::getAdminUrl(true, false);
        
        // Prepare the payment
        $payPalPayment = (new \Stephino\PayPal\Api\Payment())
            ->setIntent('sale')
            ->setPayer((new \Stephino\PayPal\Api\Payer())->setPaymentMethod('paypal'))
            ->setTransactions(array(
                (new \Stephino\PayPal\Api\Transaction())
                    ->setAmount(
                        (new \Stephino\PayPal\Api\Amount())
                            ->setCurrency(Stephino_Rpg_Config::get()->core()->getPayPalCurrency())
                            ->setTotal($packageConfig->getCostFiat())
                    )
                    ->setDescription(
                        sprintf(
                            '<b>%s: %s %s</b><hr/><p>%s</p>',
                            $packageConfig->getName(),
                            number_format($packageConfig->getGem()),
                            Stephino_Rpg_Config::get()->core()->getResourceGemName(),
                            $packageConfig->getDescription()
                        )
                    )
                    ->setInvoiceNumber($invoiceContent)
            ))
            ->setRedirectUrls(
                (new \Stephino\PayPal\Api\RedirectUrls())
                    ->setReturnUrl($returnUrl)
                    ->setCancelUrl($returnUrl)
            );
        
        // Create the payment
        $payPalPayment->create(
            (new \Stephino\PayPal\Rest\ApiContext(
                new \Stephino\PayPal\Auth\OAuthTokenCredential(
                    Stephino_Rpg_Config::get()->core()->getPayPalClientId(),
                    Stephino_Rpg_Config::get()->core()->getPayPalClientSecret()
                )
            ))->setConfig(array(
                'mode' => Stephino_Rpg_Config::get()->core()->getPayPalSandbox() ? 'sandbox' : 'live',
            ))
        );
        
        // Create a pending payment
        $this->getDb()->tableMessages()->create(
            $userId, 
            0, 
            Stephino_Rpg_Db_Table_Messages::MESSAGE_TYPE_INVOICE, 
            $payPalPayment->getId(),
            $invoiceContent
        );
        
        // Get the approval link
        return $payPalPayment->getApprovalLink();
    }
    
    /**
     * Validate and execute a PayPal transaction, marking the associated invoice message as "read"
     * 
     * @param in     $userId    User ID
     * @param string $paymentId Payment ID
     * @param string $payerId   Payer ID
     * @return Stephino_Rpg_Config_PremiumPackage Premium package configuration
     * @throws Exception
     */
    public function execute($userId, $paymentId, $payerId) {
        if (!class_exists('\Stephino\PayPal\Api\Payment')) {
            throw new Exception(__('Plugin not activated', 'stephino-rpg'));
        }
        
        // PayPal not enabled
        if (null === Stephino_Rpg_Config::get()->core()->getPayPalClientId() 
            || null === Stephino_Rpg_Config::get()->core()->getPayPalClientSecret()) {
            throw new Exception(__('PayPal not enabled', 'stephino-rpg'));
        }
        
        // Invoice not found
        if (null === $invoiceData = $this->getDb()->tableMessages()->getInvoice($userId, $paymentId)) {
            throw new Exception(__('Payment not found', 'stephino-rpg'));
        }
        
        // Already processed
        if (0 != $invoiceData[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_READ]) {
            throw new Exception(__('Payment already processed', 'stephino-rpg'));
        }
        
        // Get the package config
        $packageId = preg_replace('%^\d+\-(\d+)\-.*?$%i', '$1', $invoiceData[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_CONTENT]);
        $packageConfig = Stephino_Rpg_Config::get()->premiumPackages()->getById($packageId);

        // Invalid Premium Package
        if (null === $packageConfig) {
            $this->getDb()->tableMessages()->deleteById($invoiceData[Stephino_Rpg_Db_Table_Messages::COL_ID]);
            throw new Exception(__('Invalid premium package', 'stephino-rpg'));
        }
        
        // Prepare the context
        $payPalContext = (new \Stephino\PayPal\Rest\ApiContext(
            new \Stephino\PayPal\Auth\OAuthTokenCredential(
                Stephino_Rpg_Config::get()->core()->getPayPalClientId(),
                Stephino_Rpg_Config::get()->core()->getPayPalClientSecret()
            )
        ))->setConfig(array(
            'mode' => Stephino_Rpg_Config::get()->core()->getPayPalSandbox() ? 'sandbox' : 'live',
        ));
        
        // Execute the payment
        \Stephino\PayPal\Api\Payment::get($paymentId, $payPalContext)->execute(
            (new \Stephino\PayPal\Api\PaymentExecution())->setPayerId($payerId), 
            $payPalContext
        );
        
        // Successful payment
        if (!\Stephino\PayPal\Api\Payment::get($paymentId, $payPalContext)->getState()) {
            $this->getDb()->tableMessages()->deleteById($invoiceData[Stephino_Rpg_Db_Table_Messages::COL_ID]);
            throw new Exception(__('PayPal payment failed', 'stephino-rpg'));
        }

        // Mark this payment as successful
        $this->getDb()->tableMessages()->updateById(
            array(
                Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_READ => 1,
                Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_TIME => time(),
            ), 
            $invoiceData[Stephino_Rpg_Db_Table_Messages::COL_ID]
        );
        
        // Remove other pending payments
        $this->getDb()->tableMessages()->deleteInvoicePending($userId);
        
        // Get the bought premium package configuration
        return $packageConfig;
    }
    
    /**
     * Get the invoices for the specified month
     * 
     * @param int $year  Reporting year
     * @param int $month Reporting month
     * @return array List of invoices; each item is an array with the following keys:<ul>
     * <li>self::INVOICE_ID         => (string) PayPal Invoice ID</li>
     * <li>self::INVOICE_USER_ID    => (int) User ID</li>
     * <li>self::INVOICE_PAYMENT_ID => (string) PayPal Payment ID</li>
     * <li>self::INVOICE_PACKAGE_ID => (int) Premium Package Configuration ID</li>
     * <li>self::INVOICE_VALUE      => (float) Fiat currency value</li>
     * <li>self::INVOICE_CURRENCY   => (string) Fiat currency </li>
     * <li>self::INVOICE_PAID       => (boolean) Invoice was paid</li>
     * <li>self::INVOICE_DATE       => (string) Date in "YYYY-MM-DD HH:mm:ss" format</li>
     * </ul>
     */
    public function getList($year, $month) {
        // Prepare the result
        $result = array();
        
        // Prepare the interval
        $startTime = strtotime($year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01');
        $endTime = strtotime('+1 month', $startTime) - 1;
        
        // Get the invoices
        if (is_array($invoices = $this->getDb()->tableMessages()->getAllInvoices($startTime, $endTime))) {
            foreach ($invoices as $dbRow) {
                // Get the package config
                if (preg_match('%^(\d+)\-(\d+)\-[^\-]*?(?:\-(.*?)\-(\w+))?$%i', $dbRow[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_CONTENT], $matches)) {
                    list(, $invoiceUserId, $invoicePackageId) = $matches;
                    
                    // Prepare the invoice value
                    $invoiceValue = 0;
                    if (isset($matches[3])) {
                        $invoiceValue = $matches[3];
                    } else {
                        // Older entry, fallback to the configuration value (may not be accurate)
                        if (null !== $packageConfig = Stephino_Rpg_Config::get()->premiumPackages()->getById($invoicePackageId)) {
                            $invoiceValue = $packageConfig->getCostFiat();
                        }
                    }
                    
                    // Prepare the invoice currency
                    $invoiceCurrency = Stephino_Rpg_Config::get()->core()->getPayPalCurrency();
                    if (isset($matches[4])) {
                        $invoiceCurrency = $matches[4];
                    }
                    
                    // Append the invoice
                    $result[] = array(
                        self::INVOICE_ID         => trim($dbRow[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_CONTENT]),
                        self::INVOICE_USER_ID    => (int) $invoiceUserId,
                        self::INVOICE_PAYMENT_ID => trim($dbRow[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_SUBJECT]),
                        self::INVOICE_PACKAGE_ID => (int) $invoicePackageId,
                        self::INVOICE_VALUE      => (float) $invoiceValue,
                        self::INVOICE_CURRENCY   => trim($invoiceCurrency),
                        self::INVOICE_PAID       => 1 === (int) $dbRow[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_READ],
                        self::INVOICE_DATE       => date('Y-m-d H:i:s', $dbRow[Stephino_Rpg_Db_Table_Messages::COL_MESSAGE_TIME]),
                    );
                }
            }
        }
        return $result;
    }
}

/* EOF */