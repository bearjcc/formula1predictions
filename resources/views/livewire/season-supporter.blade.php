<div class="card bg-gradient-to-r from-yellow-100 to-orange-100 dark:from-yellow-900 dark:to-orange-900 shadow-lg border-2 border-yellow-300 dark:border-yellow-700">
    <div class="card-body">
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-4">
                <div class="text-4xl">⭐</div>
                <div>
                    <h3 class="card-title text-xl">Season Supporter</h3>
                    <p class="text-sm opacity-75">
                        @if($isSupporter)
                            You've been a supporter since {{ $supporterSince }}!
                        @else
                            Support the game and get exclusive benefits
                        @endif
                    </p>
                </div>
            </div>
            
            @if($isSupporter)
                <div class="badge badge-success badge-lg">Active Supporter</div>
            @else
                @if($stripeEnabled)
                    <a href="{{ route('checkout.season-supporter') }}" class="btn btn-warning">
                        Become a Supporter - {{ $price }}
                    </a>
                @else
                    <button disabled class="btn btn-disabled" title="Stripe not configured">
                        Payment Unavailable
                    </button>
                @endif
            @endif
        </div>

        <!-- Benefits -->
        <div class="mt-4 pt-4 border-t border-yellow-300 dark:border-yellow-700">
            <h4 class="font-semibold mb-2">Supporter Benefits:</h4>
            <ul class="list-disc list-inside space-y-1 text-sm">
                <li>⭐ Exclusive "Season Supporter" badge on your profile</li>
                <li>🎯 Highlighted on the leaderboard with gold border</li>
                <li>📊 Full access to Pro Stats and analytics</li>
                <li>🏆 Priority access to new features and beta tests</li>
                <li>❤️ Support the ongoing development of the game</li>
            </ul>
        </div>

        <div class="card-actions justify-between mt-4">
            <div class="text-xs opacity-75">
                <span>Secure payment powered by</span>
                <svg class="inline-block w-12 h-5 ml-1" viewBox="0 0 48 16" fill="none">
                    <path fill="#635BFF" d="M14.5 6.5C14.5 8.5 13.5 10 12 10.5C11.5 10.5 11 10 11 9.5C11 9 11.5 8.5 12 8.5C13.5 8.5 14.5 8.5 14.5 6.5ZM13.5 4H8V16H11V12.5H13.5C16.5 12.5 17.5 10.5 17.5 8C17.5 5.5 16.5 4 13.5 4ZM26 4H21V16H24V12.5H25.5C27.5 12.5 28.5 11 28.5 9.5V8.5C28.5 6 27.5 4.5 26 4ZM25.5 6.5V8.5C25.5 9 25 9.5 24.5 9.5H24V6.5H25.5C25.5 6.5 25.5 6.5 25.5 6.5ZM33.5 6.5H30.5V9H33V10.5H30.5V16H27.5V6.5H33.5V6.5ZM36.5 6.5H35.5V16H38.5V6.5H36.5Z"/>
                </svg>
            </div>
            <div>
                @if($isSupporter)
                    <a href="{{ route('checkout.portal') }}" class="btn btn-sm btn-outline">
                        Manage Payment
                    </a>
                @else
                    <a href="https://github.com/sponsors" target="_blank" class="btn btn-sm btn-ghost">
                        Learn More
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Info Alert -->
@if(!$stripeEnabled && !$isSupporter)
    <div class="alert alert-warning mt-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <span class="text-sm">Stripe payments not yet configured for production. Contact the administrator.</span>
    </div>
@endif
