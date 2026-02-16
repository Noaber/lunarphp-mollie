# lunar-mollie
Mollie payment driver for LunarPHP v1.x using Mollie SDK v4

# Installation

### Require the composer package

```sh
composer require noaber/lunar-mollie
```

### Publish the Mollie configuration

Use the below command to publish the configuration file to `config/lunar/mollie.php`.

```bash
php artisan vendor:publish --tag=lunar.mollie.config
```

### Enable the payment driver

Set the driver in `config/lunar/payments.php`

```php
<?php

return [
    // ...
    'types' => [
        'paynl' => [
            'driver' => 'mollie',
        ],
    ],
];
```

### Add your Mollie credentials and other config

Take a look at the configuration in `config/lunar/paynl.php`.   
Where appropriate, edit or set the environment variables in your `.env` file. At least the keys will need to be set.

```dotenv
MOLLIE_KEY="YOUR_KEY"
MOLLIE_CHECKOUT_SUCCESS="/your-url-to-thank-you-page"
```

You can use the Mollie Test key to switch between live and test mode.

### create named routes for success and cancellation pages
When the user returns form the payment provider webpage, a redirect will be generated, based on the result of the payment.
Therefore there have to be four named routed, as defined in the config.

```php
<?php
'payment_paid_route'     => 'checkout-success.view',
'payment_canceled_route' => 'checkout-canceled.view',
'payment_open_route'     => 'checkout-open.view',
'payment_failed_route'   => 'checkout-failure.view',
```

### or create urls using the .env variables (eg: when using a headless CMS and custom urls)

```env
MOLLIE_CHECKOUT_SUCCESS="order-confirmed" #will redirect to url.tld/order-confirmed
MOLLIE_CHECKOUT_CANCELED="your-url-canceled" #will redirect to url.tld/your-url-canceled
MOLLIE_CHECKOUT_OPEN="your-url-open" #will redirect to url.tld/your-url-open
MOLLIE_CHECKOUT_FAILURE="your-url-failure" #will redirect to url.tld/your-url-failure
```

### Example
To start a payment:
```php
<?php
$payment = \Lunar\Facades\Payments::driver('mollie')
    ->cart($this->cart)
    ->withData([
        'description'   => 'Description',
        'redirectRoute' => config('lunar.mollie.redirect_route'),
        'webhookUrl'    => config('lunar.mollie.override_webhook_url') ?: route(config('lunar.mollie.webhook_route')),
        'method'        => $paymentMethod,
        'bank'          => $bankID, // optional
        'extra1'        => '',      // optional
        'extra2'        => '',      // optional
        'extra3'        => '',      // optional
    ])
    ->initiatePayment();

return redirect($payment->getRedirectUrl());
```