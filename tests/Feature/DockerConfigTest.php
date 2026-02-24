<?php

use Illuminate\Support\Facades\File;

it('has Docker configuration for production deployments', function () {
    $dockerfile = base_path('Dockerfile');
    $composeFile = base_path('docker-compose.yml');

    expect(File::exists($dockerfile))->toBeTrue();
    expect(File::exists($composeFile))->toBeTrue();

    $dockerContents = File::get($dockerfile);
    $composeContents = File::get($composeFile);

    expect($dockerContents)
        ->toContain('FROM php:8.4-cli-alpine')
        ->toContain('composer install')
        ->toContain('npm run build')
        ->toContain('CMD ["php", "artisan", "serve"');

    expect($composeContents)
        ->toContain('services:')
        ->toContain('app:')
        ->toContain('db:')
        ->toContain('image: mysql:8.0')
        ->toContain('ports:')
        ->toContain('8000:8000');
});

