<?php

namespace Noaber\Lunar\Mollie;

use Carbon\Carbon;
use Exception;
use Lunar\Events\PaymentAttemptEvent;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\PaymentTypes\AbstractPayment;
use Lunar\Models\Order;
use Lunar\Models\Transaction;
use Mollie\Api\Http\Data\Money;
use Mollie\Api\Http\Requests\CreatePaymentRequest;
use Mollie\Api\Http\Requests\GetPaymentRequest;
use Mollie\Api\Resources\Payment;
use Mollie\Laravel\Facades\Mollie;


class MolliePaymentType extends AbstractPayment
{
    /**
     * initiate payment and set settings
     * @throws Exception
     */
    public function initiatePayment(): Payment
    {
        if (!$this->order) {
            if (!$this->order = $this->cart->order) {
                $this->order = $this->cart->createOrder();
            }
        }

        if ($this->order->placed_at) {
            throw new Exception('order has already been placed');
        }

        // create transaction
        $transaction = Transaction::create([
            'success' => false,
            'driver' => 'mollie',
            'order_id' => $this->order->id,
            'type' => 'capture',
            'amount' => $this->order->total,
            'reference' => '',
            'status' => '',
            'card_type' => '',
        ]);

        $customerName = $this->order->customer?->name
            ?? trim($this->order->shipping_address?->first_name . ' ' . $this->order->shipping_address?->last_name)
            ?? 'Guest';

        $customerEmail = $this->order->customer?->email
            ?? $this->order->billing_address?->email
            ?? null;

        $payment = Mollie::send(new CreatePaymentRequest(
            description: 'Order #' . $this->order->reference,
            amount: new Money(
                $this->order->currency->code,
                $this->order->total->decimal
            ),
            redirectUrl: route(config('lunar.mollie.redirect_route', 'mollie.redirect'), ['order' => $this->order->id, 'transaction' => $transaction->id]),
            webhookUrl: route(config('lunar.mollie.webhook_route', 'mollie.webhook')),
            metadata: [
                "order_id" => $this->order->id,
                "customer_info" => [
                    "name" => $customerName,
                    "email" => $customerEmail,
                ]
            ]
        ));

        $transaction->update([
            'reference' => $payment->id,
            'status' => $payment->status,
            'notes' => $payment->description,
        ]);

        return $payment;
    }

    /**
     * authorize payment
     * @return PaymentAuthorize
     */
    public function authorize(): PaymentAuthorize
    {
        if (!array_key_exists('id', $this->data)) {
            $response = new PaymentAuthorize(
                success: false,
                message: json_encode(['status' => 'not_found', 'message' => 'No payment ID provided'], JSON_THROW_ON_ERROR),
            );
            PaymentAttemptEvent::dispatch($response);
            return $response;
        }

        $paymentId = $this->data['id'];
        $payment = Mollie::send(new GetPaymentRequest($paymentId));

        $orderId = $payment->metadata->order_id ?? null;
        if (! $orderId) {
            $response = new PaymentAuthorize(
                success: false,
                message: json_encode(['status' => 'not_found', 'message' => 'Invalid order ID'], JSON_THROW_ON_ERROR),
            );
            PaymentAttemptEvent::dispatch($response);
            return $response;
        }

        // find transaction
        $transaction = Transaction::where('reference', $this->data['id'])
            ->where('order_id', $orderId)
            ->where('driver', 'mollie')
            ->first();

        // find order
        $this->order = Order::find($orderId);

        if (!$transaction || !$payment || !$this->order) {
            $response = new PaymentAuthorize(
                success: false,
                message: json_encode([
                    'status' => 'not_found',
                    'message' => 'No transaction found for payment ID ' . $paymentId,
                ], JSON_THROW_ON_ERROR),
            );
            PaymentAttemptEvent::dispatch($response);
            return $response;
        }

        // handle payment authorization
        if ($this->order->placed_at) {
            $response = new PaymentAuthorize(
                success: true,
                message: json_encode(['status' => 'duplicate', 'message' => 'This order has already been placed'], JSON_THROW_ON_ERROR),
            );

            PaymentAttemptEvent::dispatch($response);
            return $response;
        }

        $transaction->update([
            'success' => ($payment->isPaid() || $payment->isAuthorized()),
            'status' => $payment->status,
            'meta' => [
                'mollie_status' => $payment->status,
                'method' => $payment->method,
                'paid_at' => $payment->paidAt,
                'amount' => $payment->amount->value,
            ],
        ]);

        if (($payment->isPaid() || $payment->isAuthorized())) {
            $this->order->placed_at = now();
        }
        $this->order->status = config('lunar.mollie.payment_status_mappings.' . $payment->status) ?? $this->order->status;
        $this->order->saveQuietly();

        $response = new PaymentAuthorize(
            success: ($payment->isPaid() || $payment->isAuthorized()),
            message: json_encode(['status' => $payment->status], JSON_THROW_ON_ERROR),
            orderId: $this->order->id,
            paymentType: 'mollie',
        );
        PaymentAttemptEvent::dispatch($response);
        return $response;
    }

    /**
     * get redirect url
     * @param Payment $payment
     * @return string
     */
    public function getRedirectUrl(Payment $payment): string
    {
        return $payment->getCheckoutUrl();
    }

    public function capture(Transaction|\Lunar\Models\Contracts\Transaction $transaction, $amount = 0): PaymentCapture
    {
        return new PaymentCapture(success: true);
    }

    public function refund(Transaction|\Lunar\Models\Contracts\Transaction $transaction, int $amount, $notes = null): PaymentRefund
    {
        return new PaymentRefund(success: false, message: 'Not implemented');
    }
}
