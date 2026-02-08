<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;

class StripeWebhookController extends CashierWebhookController
{
    /**
     * Handle successful payment.
     */
    public function handlePaymentIntentSucceeded($payload): void
    {
        $paymentIntent = $payload['data']['object'];

        // This is already handled by the checkout success flow,
        // but we can use this for additional processing
        Log::info('Payment succeeded', [
            'payment_intent_id' => $paymentIntent['id'],
            'amount' => $paymentIntent['amount'],
            'metadata' => $paymentIntent['metadata'] ?? [],
        ]);

        // Call parent to handle Cashier's default behavior
        parent::handlePaymentIntentSucceeded($payload);
    }

    /**
     * Handle checkout session completed.
     */
    public function handleCheckoutSessionCompleted($payload): void
    {
        $session = $payload['data']['object'];

        Log::info('Checkout session completed', [
            'session_id' => $session['id'],
            'payment_status' => $session['payment_status'],
            'metadata' => $session['metadata'] ?? [],
        ]);

        // The actual supporter granting is handled in the success flow,
        // but this webhook confirms the payment was processed
        parent::handleCheckoutSessionCompleted($payload);
    }

    /**
     * Handle failed payment.
     */
    public function handlePaymentIntentPaymentFailed($payload): void
    {
        $paymentIntent = $payload['data']['object'];

        Log::warning('Payment failed', [
            'payment_intent_id' => $paymentIntent['id'],
            'error' => $paymentIntent['last_payment_error'] ?? null,
        ]);

        parent::handlePaymentIntentPaymentFailed($payload);
    }

    /**
     * Handle customer subscription created (not used for one-time payments, but good to have).
     */
    public function handleCustomerSubscriptionCreated($payload): void
    {
        Log::info('Customer subscription created', [
            'subscription_id' => $payload['data']['object']['id'],
        ]);

        parent::handleCustomerSubscriptionCreated($payload);
    }
}
