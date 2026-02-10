<?php

use Illuminate\Support\Facades\File;

it('has a deployment script with key steps', function () {
    $path = base_path('deploy.sh');

    expect(File::exists($path))->toBeTrue();

    $contents = File::get($path);

    expect($contents)
        ->toContain('composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev')
        ->toContain('php artisan migrate --force --no-interaction')
        ->toContain('npm run build')
        ->toContain('php artisan config:cache')
        ->toContain('php artisan route:cache')
        ->toContain('php artisan view:cache');
});
