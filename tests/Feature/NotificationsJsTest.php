<?php

test('notifications js has no console log statements', function () {
    $path = base_path('resources/js/notifications.js');
    expect($path)->toBeReadableFile();

    $content = file_get_contents($path);

    expect($content)->not->toContain('console.log');
});

