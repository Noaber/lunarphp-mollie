<?php

namespace Noaber\Lunar\Mollie;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Noaber\Lunar\Paynl\Facades\MollieFacade;
use Lunar\Facades\Payments;


class MolliePaymentsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // register paynl to Lunar payments
        Payments::extend('mollie', function($app) {
            return $app->make(\Noaber\Lunar\Mollie\MolliePaymentType::class);
        });

        // init pay.nl facade
        // PaynlFacade::initPayInstance();

        // load routes
        $this->loadRoutes();

        // make lunar paynl config publishable
        $this->registerPublishables();
    }

    /**
     * load routes
     * @return void
     */
    private function loadRoutes() {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }

    /**
     * register publishables
     * @return void
     */
    private function registerPublishables() {
        // make lunar paynl config publishable
        $this->publishes([
            __DIR__.'/../config/mollie.php' => config_path('lunar/mollie.php'),
        ], 'lunar.mollie.config');
    }
}
