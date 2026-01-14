<?php

namespace Noaber\Lunar\Mollie\Controllers;

use Lunar\Facades\Payments;
use Lunar\Models\Order;
use Lunar\Models\Transaction;

class MollieRedirectController
{
    public function redirect(Order $order, Transaction $transaction)
    {
        if (!$transaction->reference) {
            return redirect()->route(config('lunar.paynl.payment_failed_route'));
        }

        // Transaction succeeded, authorize payment
        $payment_authorize = Payments::driver('paynl')
            ->withData(['paymentId' => $transaction->reference,])
            ->authorize();

        if (!$payment_authorize->success) {
            $data = json_decode($payment_authorize->message, true);

            return match ($data['status']) {
                // 'open' => redirect()->route(config('lunar.paynl.payment_open_route')),
                'open' => $this->redirectTo('payment_open_route'),
                //'CANCEL' => redirect()->route(config('lunar.paynl.payment_canceled_route')),
                'CANCEL' => $this->redirectTo('payment_canceled_route'),
                // 'VERIFY' => redirect()->route(config('lunar.paynl.payment_canceled_route')),
                'VERIFY' => $this->redirectTo('payment_canceled_route'),
                // 'PENDING' => redirect()->route(config('lunar.paynl.payment_canceled_route')),
                'PENDING' => $this->redirectTo('payment_canceled_route'),
                // default => redirect()->route(config('lunar.paynl.payment_failed_route')),
                default => $this->redirectTo('payment_failed_route'),
            };
        }

        return redirect()->to($this->redirectTo('payment_paid_route'));
    }

    /**
     * get the url based on a given url or route
     * @param string $config_key
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|mixed|string
     */
    private function redirectTo(string $config_key) {
        $url = url(config("lunar.paynl.{$config_key}_url"));

        if ($url !== null && filter_var($url, FILTER_VALIDATE_URL) !== false) {
            return $url;
        }

        return route(config("lunar.paynl.{$config_key}"));
    }
}
