<x-layouts.layout>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Edit Prediction</h1>
                <p class="text-zinc-600 dark:text-zinc-400">
                    Update your prediction for the upcoming race
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <x-mary-button variant="outline" size="sm" icon="o-arrow-left">
                    Back to Predictions
                </x-mary-button>
                <x-mary-button variant="error" size="sm" icon="o-trash">
                    Delete Prediction
                </x-mary-button>
            </div>
        </div>
    </div>

    <!-- Race Information -->
    <x-mary-card class="p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Race Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                                                <x-mary-icon name="o-flag" class="w-6 h-6 text-red-600 dark:text-red-400" />
                </div>
                <div>
                    <h3 class="font-semibold">British Grand Prix</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Silverstone Circuit</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                                <x-mary-icon name="o-calendar" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <h3 class="font-semibold">July 7, 2024</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Race Day</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                                <x-mary-icon name="o-clock" class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
                <div>
                    <h3 class="font-semibold">2 days left</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Deadline: July 4, 2024</p>
                </div>
            </div>
        </div>
    </x-mary-card>

    <!-- Current Prediction Summary -->
    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700 p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4 text-blue-800 dark:text-blue-200">Current Prediction</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="font-semibold mb-2">Podium Predictions</h3>
                <div class="space-y-2">
                    <p class="text-sm">🥇 1st: Max Verstappen (Red Bull)</p>
                    <p class="text-sm">🥈 2nd: Lewis Hamilton (Mercedes)</p>
                    <p class="text-sm">🥉 3rd: Lando Norris (McLaren)</p>
                </div>
            </div>
            <div>
                <h3 class="font-semibold mb-2">Additional Predictions</h3>
                <div class="space-y-2">
                    <p class="text-sm">🏁 Pole: Max Verstappen</p>
                    <p class="text-sm">⚡ Fastest Lap: Lewis Hamilton</p>
                    <p class="text-sm">💥 DNF: None predicted</p>
                </div>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-blue-200 dark:border-blue-700">
            <p class="text-sm text-blue-700 dark:text-blue-300">
                <strong>Confidence Level:</strong> Medium (4-6 points) • 
                <strong>Created:</strong> July 1, 2024 at 14:30
            </p>
        </div>
    </div>

    <!-- Edit Prediction Form -->
    <x-mary-card class="p-6 mb-8">
        <h2 class="text-xl font-semibold mb-6">Update Prediction</h2>
        
        <form class="space-y-6">
            <!-- Podium Predictions -->
            <div class="space-y-4">
                <h3 class="font-semibold mb-4">Podium Predictions</h3>
                
                <!-- 1st Place -->
                <div class="space-y-2">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">1st Place</label>
                    <x-mary-select>
                        <option value="max-verstappen" selected>Max Verstappen - Red Bull</option>
                        <option value="lewis-hamilton">Lewis Hamilton - Mercedes</option>
                        <option value="lando-norris">Lando Norris - McLaren</option>
                        <option value="charles-leclerc">Charles Leclerc - Ferrari</option>
                        <option value="carlos-sainz">Carlos Sainz - Ferrari</option>
                    </x-mary-select>
                </div>

                <!-- 2nd Place -->
                <div class="space-y-2">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">2nd Place</label>
                    <x-mary-select>
                        <option value="max-verstappen">Max Verstappen - Red Bull</option>
                        <option value="lewis-hamilton" selected>Lewis Hamilton - Mercedes</option>
                        <option value="lando-norris">Lando Norris - McLaren</option>
                        <option value="charles-leclerc">Charles Leclerc - Ferrari</option>
                        <option value="carlos-sainz">Carlos Sainz - Ferrari</option>
                    </x-mary-select>
                </div>

                <!-- 3rd Place -->
                <div class="space-y-2">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">3rd Place</label>
                    <x-mary-select>
                        <option value="max-verstappen">Max Verstappen - Red Bull</option>
                        <option value="lewis-hamilton">Lewis Hamilton - Mercedes</option>
                        <option value="lando-norris" selected>Lando Norris - McLaren</option>
                        <option value="charles-leclerc">Charles Leclerc - Ferrari</option>
                        <option value="carlos-sainz">Carlos Sainz - Ferrari</option>
                    </x-mary-select>
                </div>
            </div>

            <!-- Additional Predictions -->
            <div class="space-y-4">
                <h3 class="font-semibold mb-4">Additional Predictions</h3>
                
                <!-- Pole Position -->
                <div class="space-y-2">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Pole Position</label>
                    <x-mary-select>
                        <option value="max-verstappen" selected>Max Verstappen - Red Bull</option>
                        <option value="lewis-hamilton">Lewis Hamilton - Mercedes</option>
                        <option value="lando-norris">Lando Norris - McLaren</option>
                        <option value="charles-leclerc">Charles Leclerc - Ferrari</option>
                        <option value="carlos-sainz">Carlos Sainz - Ferrari</option>
                    </x-mary-select>
                </div>

                <!-- Fastest Lap -->
                <div class="space-y-2">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Fastest Lap</label>
                    <x-mary-select>
                        <option value="max-verstappen">Max Verstappen - Red Bull</option>
                        <option value="lewis-hamilton" selected>Lewis Hamilton - Mercedes</option>
                        <option value="lando-norris">Lando Norris - McLaren</option>
                        <option value="charles-leclerc">Charles Leclerc - Ferrari</option>
                        <option value="carlos-sainz">Carlos Sainz - Ferrari</option>
                    </x-mary-select>
                </div>

                <!-- DNF Predictions -->
                <div class="space-y-2">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">DNF Predictions (Optional)</label>
                    <x-mary-select multiple>
                        <option value="max-verstappen">Max Verstappen - Red Bull</option>
                        <option value="lewis-hamilton">Lewis Hamilton - Mercedes</option>
                        <option value="lando-norris">Lando Norris - McLaren</option>
                        <option value="charles-leclerc">Charles Leclerc - Ferrari</option>
                        <option value="carlos-sainz">Carlos Sainz - Ferrari</option>
                    </x-mary-select>
                </div>
            </div>

            <!-- Confidence Level -->
            <div class="space-y-4">
                <h3 class="font-semibold mb-4">Confidence Level</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                        <h4 class="font-semibold mb-2">Low Confidence</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">1-3 points if correct</p>
                    </div>
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700">
                        <h4 class="font-semibold mb-2">Medium Confidence</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">4-6 points if correct</p>
                    </div>
                    <div class="p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                        <h4 class="font-semibold mb-2">High Confidence</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">7-10 points if correct</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Your Confidence Level</label>
                    <x-mary-select>
                        <option value="low">Low (1-3 points)</option>
                        <option value="medium" selected>Medium (4-6 points)</option>
                        <option value="high">High (7-10 points)</option>
                    </x-mary-select>
                </div>
            </div>

            <!-- Notes -->
            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Notes (Optional)</label>
                <x-mary-textarea placeholder="Add any notes about your prediction..." rows="4">Based on recent form, Max should dominate qualifying and the race. Lewis and Lando should be close for the podium.</x-mary-textarea>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-6 border-t border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center space-x-4">
                    <x-mary-button variant="outline" size="lg" icon="o-arrow-left">
                        Cancel
                    </x-mary-button>
                    <x-mary-button variant="error" size="lg" icon="o-trash">
                        Delete Prediction
                    </x-mary-button>
                </div>
                <x-mary-button variant="primary" size="lg" icon="o-check">
                    Update Prediction
                </x-mary-button>
            </div>
        </form>
    </x-mary-card>

    <!-- Edit History -->
    <x-mary-card class="p-6">
        <h2 class="text-xl font-semibold mb-4">Edit History</h2>
        <div class="space-y-4">
            <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <div>
                    <h3 class="font-semibold">Prediction Created</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Initial prediction submitted</p>
                </div>
                <p class="text-sm text-zinc-500">July 1, 2024 at 14:30</p>
            </div>
            <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                <div>
                    <h3 class="font-semibold">Prediction Updated</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Changed 2nd place from Norris to Hamilton</p>
                </div>
                <p class="text-sm text-zinc-500">July 2, 2024 at 09:15</p>
            </div>
        </div>
    </x-mary-card>
</x-layouts.layout>
