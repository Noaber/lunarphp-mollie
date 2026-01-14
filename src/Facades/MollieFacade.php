<?php

namespace Noaber\Lunar\Mollie\Facades;

use Illuminate\Support\Facades\Facade;
use Paynl\Config;
use Paynl\Paymentmethods;

class MollieFacade extends Facade
{
    public static function initPayInstance(): void
    {
        if (!config('lunar.paynl.test_mode')) {
            Config::setApiToken(config('lunar.paynl.paynl_api_token'));
            Config::setServiceId(config('lunar.paynl.paynl_service_id'));
            if (!empty(config('lunar.paynl.paynl_tokencode'))) {
                Config::setTokenCode(config('lunar.paynl.paynl_tokencode'));
            }
        } else {
            Config::setApiToken(config('lunar.paynl.paynl_api_token_test'));
            Config::setServiceId(config('lunar.paynl.paynl_service_id_test'));
            if (!empty(config('lunar.paynl.paynl_tokencode_test'))) {
                Config::setTokenCode(config('lunar.paynl.paynl_tokencode_test'));
            }
        }
    }

    /**
     * get paynl payment methods
     * @param array $options
     * @param string|null $language_code
     * @return array
     */
    public static function getPaymentMethods(array $options = [], string $language_code = null): array
    {
        self::initPayInstance();

        return Paymentmethods::getList($options, $language_code);
    }

    /**
     * get paynl banks
     * @param $payment_method_id
     * @return array
     */
    public static function getBanks($payment_method_id = 10): array
    {
        self::initPayInstance();

        return Paymentmethods::getBanks($payment_method_id);
    }
}
