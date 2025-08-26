<?php

return [
    /**
     * Default component prefix.
     *
     * Make sure to clear view cache after renaming with `php artisan view:clear`
     *
     *    prefix => ''
     *              <x-button />
     *              <x-card />
     *
     *    prefix => 'mary-'
     *               <x-mary-button />
     *               <x-mary-card />
     */
    'prefix' => 'mary-',

    /**
     * Default route prefix.
     *
     * Some maryUI components make network request to its internal routes.
     *
     *      route_prefix => ''
     *          - Spotlight: '/mary/spotlight'
     *          - Editor: '/mary/upload'
     *          - ...
     *
     *      route_prefix => 'my-components'
     *          - Spotlight: '/my-components/mary/spotlight'
     *          - Editor: '/my-components/mary/upload'
     *          - ...
     */
    'route_prefix' => '',

    /**
     * Components settings
     */
    'components' => [
        'spotlight' => [
            'class' => 'App\Support\Spotlight',
        ],

        // Dark-mode friendly defaults
        'card' => [
            'default' => [
                'base' => 'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 rounded-lg shadow',
            ],
        ],
        'button' => [
            'variants' => [
                'primary' => 'bg-red-600 hover:bg-red-700 text-white',
                'secondary' => 'bg-zinc-200 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200 hover:bg-zinc-300 dark:hover:bg-zinc-600',
                'outline' => 'border border-zinc-300 dark:border-zinc-600 text-zinc-800 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-700',
            ],
        ],
    ],
];
