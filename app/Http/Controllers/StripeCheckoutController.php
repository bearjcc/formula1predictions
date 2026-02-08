<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeCheckoutController extends Controller
{
    /**
     * Create a Stripe Checkout session for Season Supporter purchase.
     */
    public function createCheckoutSession(Request $request)
    {
        $user = Auth::user();

        // Prevent duplicate purchases
        if ($user->is_season_supporter) {
            return redirect()->route('settings.profile')
                ->with('info', 'You are already a Season Supporter!');
        }

        // Stripe API key from config
        Stripe::setApiKey(config('cashier.secret'));

        $priceId = config('services.stripe.season_supporter_price_id');
        $successUrl = route('checkout.success', ['session_id' => '{CHECKOUT_SESSION_ID}']);
        $cancelUrl = route('checkout.cancel');

        try {
            $session = Session::create([
                'customer_email' => $user->email,
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'F1 Predictions - Season Supporter',
                            'description' => 'Support the game and get exclusive benefits',
                            'images' => [asset('images/supporter-badge.png') ?: null],
                        ],
                        'unit_amount' => config('services.stripe.season_supporter_amount', 1000), // $10.00 in cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment', // One-time payment
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'metadata' => [
                    'user_id' => $user->id,
                    'type' => 'season_supporter',
                ],
                'allow_promotion_codes' => true,
                'billing_address_collection' => 'required',
                'customer_creation' => 'always',
            ]);

            return redirect()->away($session->url);
        } catch (\Exception $e) {
            return redirect()->route('settings.profile')
                ->with('error', 'Payment failed: '.$e->getMessage());
        }
    }

    /**
     * Handle successful payment.
     */
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        if (! $sessionId) {
            return redirect()->route('settings.profile')
                ->with('error', 'Invalid session');
        }

        Stripe::setApiKey(config('cashier.secret'));

        try {
            $session = Session::retrieve($sessionId);

            // Verify the payment was successful
            if ($session->payment_status !== 'paid') {
                return redirect()->route('settings.profile')
                    ->with('error', 'Payment was not completed');
            }

            $user = User::find($session->metadata->user_id ?? Auth::id());

            if (! $user) {
                return redirect()->route('settings.profile')
                    ->with('error', 'User not found');
            }

            // Create or update Stripe customer
            $user->createOrGetStripeCustomer([
                'email' => $user->email,
                'name' => $user->name,
            ]);

            // Grant supporter status
            if (! $user->is_season_supporter) {
                $user->makeSeasonSupporter();
            }

            return redirect()->route('settings.profile')
                ->with('success', '🎉 Thank you for supporting the game! You are now a Season Supporter.');
        } catch (\Exception $e) {
            return redirect()->route('settings.profile')
                ->with('error', 'Error processing payment: '.$e->getMessage());
        }
    }

    /**
     * Handle cancelled payment.
     */
    public function cancel()
    {
        return redirect()->route('settings.profile')
            ->with('info', 'Payment cancelled. You can become a supporter anytime!');
    }

    /**
     * Create a Stripe customer portal session for managing payment methods.
     */
    public function portal(Request $request)
    {
        $user = Auth::user();

        if (! $user->stripe_id) {
            return redirect()->route('settings.profile')
                ->with('info', 'No payment history found');
        }

        Stripe::setApiKey(config('cashier.secret'));

        try {
            $session = \Stripe\BillingPortal\Session::create([
                'customer' => $user->stripe_id,
                'return_url' => route('settings.profile'),
            ]);

            return redirect()->away($session->url);
        } catch (\Exception $e) {
            return redirect()->route('settings.profile')
                ->with('error', 'Error opening billing portal: '.$e->getMessage());
        }
    }
}
