<?php

use Illuminate\Support\Facades\File;

test('blade views do not use indigo or blue focus ring colors', function () {
    $viewsPath = resource_path('views');

    $files = File::allFiles($viewsPath);

    foreach ($files as $file) {
        $contents = file_get_contents($file->getRealPath());

        expect($contents)
            ->not->toContain('focus:ring-indigo-500')
            ->and($contents)
            ->not->toContain('focus:ring-blue-500');
    }
});

