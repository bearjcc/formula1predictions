<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-zinc-100 dark:bg-zinc-900">
        <header class="bg-white dark:bg-zinc-800 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-zinc-800 dark:text-zinc-200 leading-tight">
                    {{ __('Draggable Driver Predictions Demo') }}
                </h2>
            </div>
        </header>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-zinc-900 dark:text-zinc-100">
                    @livewire('predictions.draggable-driver-list', [
                        'drivers' => [
                            [
                                'id' => 1,
                                'name' => 'Max',
                                'surname' => 'Verstappen',
                                'nationality' => 'Dutch',
                                'team' => ['team_name' => 'Red Bull Racing']
                            ],
                            [
                                'id' => 2,
                                'name' => 'Charles',
                                'surname' => 'Leclerc',
                                'nationality' => 'Monégasque',
                                'team' => ['team_name' => 'Ferrari']
                            ],
                            [
                                'id' => 3,
                                'name' => 'Lewis',
                                'surname' => 'Hamilton',
                                'nationality' => 'British',
                                'team' => ['team_name' => 'Mercedes']
                            ],
                            [
                                'id' => 4,
                                'name' => 'Carlos',
                                'surname' => 'Sainz',
                                'nationality' => 'Spanish',
                                'team' => ['team_name' => 'Ferrari']
                            ],
                            [
                                'id' => 5,
                                'name' => 'Sergio',
                                'surname' => 'Pérez',
                                'nationality' => 'Mexican',
                                'team' => ['team_name' => 'Red Bull Racing']
                            ],
                            [
                                'id' => 6,
                                'name' => 'George',
                                'surname' => 'Russell',
                                'nationality' => 'British',
                                'team' => ['team_name' => 'Mercedes']
                            ],
                            [
                                'id' => 7,
                                'name' => 'Lando',
                                'surname' => 'Norris',
                                'nationality' => 'British',
                                'team' => ['team_name' => 'McLaren']
                            ],
                            [
                                'id' => 8,
                                'name' => 'Fernando',
                                'surname' => 'Alonso',
                                'nationality' => 'Spanish',
                                'team' => ['team_name' => 'Aston Martin']
                            ],
                            [
                                'id' => 9,
                                'name' => 'Oscar',
                                'surname' => 'Piastri',
                                'nationality' => 'Australian',
                                'team' => ['team_name' => 'McLaren']
                            ],
                            [
                                'id' => 10,
                                'name' => 'Lance',
                                'surname' => 'Stroll',
                                'nationality' => 'Canadian',
                                'team' => ['team_name' => 'Aston Martin']
                            ]
                        ],
                        'raceName' => 'Monaco Grand Prix',
                        'season' => 2024,
                        'raceRound' => 8
                    ])
                </div>
            </div>
        </div>
    </div>
    @livewireScripts
</body>
</html>
