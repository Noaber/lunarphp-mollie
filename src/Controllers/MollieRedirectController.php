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
            return redirect()->route(config('lunar.mollie.payment_failed_route'));
        }

        // Transaction succeeded, authorize payment
        $payment_authorize = Payments::driver('mollie')
            ->withData(['paymentId' => $transaction->reference,])
            ->authorize();

        if (!$payment_authorize->success) {
            $data = json_decode($payment_authorize->message, true);

            return match ($data['status']) {
                'open' => $this->redirectTo('payment_open_route'),
                'CANCEL' => $this->redirectTo('payment_canceled_route'),
                'VERIFY' => $this->redirectTo('payment_canceled_route'),
                'PENDING' => $this->redirectTo('payment_canceled_route'),
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
    private function redirectTo(string $config_key): mixed
    {
        $url = url(config("lunar.mollie.{$config_key}_url"));

        if ($url !== null && filter_var($url, FILTER_VALIDATE_URL) !== false) {
            return $url;
        }

        return route(config("lunar.mollie.{$config_key}"));
    }
}
