<?php

namespace Noaber\Lunar\Mollie\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Lunar\Facades\Payments;

class MollieWebhookController
{
    public function webhook(Request $request): Application|ResponseFactory|Response
    {
        $payment_id = $request->input('order_id');

        Payments::driver('mollie')
            ->withData(['id' => $payment_id,])
            ->authorize();

        // return paynl status
        return response('TRUE|', 200);
    }
}
