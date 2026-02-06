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
                <button wire:click="openModal" class="btn btn-warning">
                    Become a Supporter
                </button>
            @endif
        </div>

        <!-- Benefits -->
        <div class="mt-4 pt-4 border-t border-yellow-300 dark:border-yellow-700">
            <h4 class="font-semibold mb-2">Supporter Benefits:</h4>
            <ul class="list-disc list-inside space-y-1 text-sm">
                <li>⭐ Exclusive "Season Supporter" badge on your profile</li>
                <li>🎯 Highlighted on the leaderboard</li>
                <li>📊 Advanced analytics and Pro Stats (coming soon)</li>
                <li>🏆 Priority access to new features</li>
                <li>❤️ Support the development of the game</li>
            </ul>
        </div>

        <div class="card-actions justify-end mt-4">
            <a href="https://github.com/sponsors" target="_blank" class="btn btn-sm btn-outline">
                Learn More
            </a>
        </div>
    </div>
</div>

<!-- Modal -->
@if($showModal)
    <dialog class="modal {{ $showModal ? 'modal-open' : '' }}">
        <div class="modal-box max-w-md">
            <h3 class="font-bold text-lg mb-4">Become a Season Supporter</h3>
            
            @if(!$confirming)
                <div class="space-y-4">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        By becoming a Season Supporter, you'll help support the ongoing development and maintenance 
                        of this Formula 1 prediction game.
                    </p>
                    
                    <div class="bg-base-200 rounded-lg p-4">
                        <h4 class="font-semibold mb-2">What you get:</h4>
                        <ul class="list-disc list-inside space-y-1 text-sm">
                            <li>Exclusive "Season Supporter" badge</li>
                            <li>Highlighted on the global leaderboard</li>
                            <li>Early access to new features</li>
                            <li>Our eternal gratitude 🙏</li>
                        </ul>
                    </div>

                    <div class="alert alert-info">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm">This is a one-time support badge for the current season.</span>
                    </div>
                </div>

                <div class="modal-action">
                    <form method="dialog">
                        <button type="button" wire:click="closeModal" class="btn">Cancel</button>
                    </form>
                    <button wire:click="confirmSupport" class="btn btn-warning">Continue</button>
                </div>
            @else
                <div class="space-y-4">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        Ready to show your support? Click the button below to get your exclusive badge!
                    </p>

                    <div class="text-center py-4">
                        <div class="text-5xl mb-2">🏆</div>
                        <div class="font-semibold">Season Supporter Badge</div>
                    </div>
                </div>

                <div class="modal-action">
                    <button wire:click="closeModal" class="btn">Cancel</button>
                    <button wire:click="becomeSupporter" class="btn btn-success">
                        Get My Badge
                    </button>
                </div>
            @endif
        </div>
        <form method="dialog" class="modal-backdrop">
            <button wire:click="closeModal">close</button>
        </form>
    </dialog>
@endif
