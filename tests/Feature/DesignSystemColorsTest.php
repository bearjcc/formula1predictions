<?php

use Illuminate\Support\Facades\File;

test('blade views do not use Tailwind gray neutrals', function () {
    $viewsPath = resource_path('views');

    $files = File::allFiles($viewsPath);

    foreach ($files as $file) {
        $contents = file_get_contents($file->getRealPath());

        expect($contents)
            ->not->toContain('gray-');
    }
});

