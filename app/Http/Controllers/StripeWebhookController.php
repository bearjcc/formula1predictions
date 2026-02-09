<?php

namespace App\Http\Controllers;

use App\Models\User;
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

        Log::info('Payment succeeded', [
            'payment_intent_id' => $paymentIntent['id'],
            'amount' => $paymentIntent['amount'],
            'metadata' => $paymentIntent['metadata'] ?? [],
        ]);

        parent::handlePaymentIntentSucceeded($payload);
    }

    /**
     * Handle checkout session completed - this is the primary fulfillment path.
     * Grants supporter status when Stripe confirms payment was successful.
     */
    public function handleCheckoutSessionCompleted($payload): void
    {
        $session = $payload['data']['object'];
        $metadata = $session['metadata'] ?? [];

        Log::info('Checkout session completed', [
            'session_id' => $session['id'],
            'payment_status' => $session['payment_status'] ?? null,
            'metadata' => $metadata,
        ]);

        // Grant supporter status if this was a season supporter purchase
        if (($metadata['type'] ?? null) === 'season_supporter' && ($session['payment_status'] ?? null) === 'paid') {
            $userId = $metadata['user_id'] ?? null;

            if ($userId) {
                $user = User::find($userId);

                if ($user) {
                    $user->makeSeasonSupporter();

                    Log::info('Season supporter status granted via webhook', [
                        'user_id' => $user->id,
                        'session_id' => $session['id'],
                    ]);
                } else {
                    Log::error('Webhook fulfillment failed: user not found', [
                        'user_id' => $userId,
                        'session_id' => $session['id'],
                    ]);
                }
            } else {
                Log::error('Webhook fulfillment failed: no user_id in metadata', [
                    'session_id' => $session['id'],
                    'metadata' => $metadata,
                ]);
            }
        }

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
     * Handle customer subscription created.
     */
    public function handleCustomerSubscriptionCreated($payload): void
    {
        Log::info('Customer subscription created', [
            'subscription_id' => $payload['data']['object']['id'],
        ]);

        parent::handleCustomerSubscriptionCreated($payload);
    }
}
