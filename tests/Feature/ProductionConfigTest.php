<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// region Session security (F1-050)

test('session secure cookie is configured and is boolean when resolved', function () {
    $secure = config('session.secure');
    expect($secure)->toBeIn([true, false]);
});

// endregion

// region Logging (F1-050)

test('logging config has daily channel with rotation days', function () {
    $channels = config('logging.channels');
    expect($channels)->toHaveKey('daily');
    expect($channels['daily'])->toHaveKey('days');
    expect($channels['daily']['driver'])->toBe('daily');
});

test('stack channel resolves to a defined channel', function () {
    $defaultStack = config('logging.channels.stack');
    expect($defaultStack)->toHaveKey('channels');
    $channelNames = $defaultStack['channels'];
    expect($channelNames)->not->toBeEmpty();
    foreach ($channelNames as $name) {
        expect(config("logging.channels.{$name}"))->toBeArray();
    }
});

// endregion

// region .env.example production documentation (F1-050)

test('env example documents production mail session and logging', function () {
    $path = base_path('.env.example');
    expect($path)->toBeReadableFile();
    $content = file_get_contents($path);
    expect($content)->toContain('SESSION_SECURE_COOKIE');
    expect($content)->toContain('LOG_LEVEL');
    expect($content)->toContain('MAIL_MAILER');
    expect($content)->toContain('LOG_STACK');
});

// endregion
