<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Mollie Test mode
    |--------------------------------------------------------------------------
    |
    | Use Mollie test mode (sandbox). Defaults to false
    |
    */
    'test_mode' => env('MOLLIE_TEST_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Mollie Token Code
    |--------------------------------------------------------------------------
    |
    | The Mollie key for your website. You can find it in your
    | Mollie dashboard.
    |
    */
    'mollie_key' => env('MOLLIE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | These are the routes names that will be used to redirect the customer to after
    | the payment has been completed. The default redirect_route and webhook_route
    | are included in the packages routes file, so you don't have to create them
    | yourself. If you want to use your own routes, you can change them here.
    |
    | The redirect_route will be called when the user is redirected back to your
    | website from the Mollie payment screen. Depending on the outcome of the
    | payment attempt, the user will again be redirected to one of the four
    | payment status routes. These routes being part of your theme, they
    | aren't included in the package, be sure to create them yourself.
    */
    'redirect_route' => 'mollie.redirect', // package
    'webhook_route' => 'mollie.webhook', // package
    'override_webhook_url' => env('MOLLIE_WEBHOOK_URL', null),

    'payment_paid_route' => 'checkout-success.view', // route to the checkout success view
    'payment_paid_route_url' => env('MOLLIE_CHECKOUT_SUCCESS', null), // url to the checkout success page (could be external like to a cms)

    'payment_canceled_route' => 'checkout-canceled.view', // route to the checkout cancelled view
    'payment_canceled_route_url' => env('MOLLIE_CHECKOUT_CANCELED', null), // url to the checkout cancelled page (could be external like to a cms)

    'payment_open_route' => 'checkout-open.view', // route to the checkout open view
    'payment_open_route_url' => env('MOLLIE_CHECKOUT_OPEN', null), // url to the checkout open page (could be external like to a cms)

    'payment_failed_route' => 'checkout-failure.view', // route to the checkout failure view
    'payment_failed_route_url' => env('MOLLIE_CHECKOUT_FAILURE', null), // url to the checkout failure page (could be external like to a cms)


    /*
    |--------------------------------------------------------------------------
    | Payment status mappings
    |--------------------------------------------------------------------------
    |
    | The payment statuses you receive from Mollie will be mapped to the statuses
    | of your orders using the mapping below. Ideally, the values on the right
    | hand side should also be present in your lunar/orders.php config file.
    */

    'payment_status_mappings' => [
        'open' => 'payment-open',
        'canceled' => 'payment-canceled',
        'pending' => 'payment-pending',
        'expired' => 'payment-expired',
        'failed' => 'payment-failed',
        'paid' => 'payment-received',
    ],

    /*
     * if the order status is the same as the key, then instead of having the default status from above mapping we use this status
     *
     * example:
     * 'order_status' => [
     *   'preorder' => 'preorder-payment-received'
     * ],
     *
     * if he key is preorder-paid (based on order->status and payment->status) then instead of the default status after payment will be preorder-payment-received
     * but the value status should exist in lunar/order config file
     */
    'order_status' => [
        'preorder-paid' => 'preorder-payment-received'
    ],
];
