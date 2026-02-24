<?php

/**
 * Ensures database config (config/database.php) does not trigger PHP 8.5+ deprecations.
 * With convertDeprecationsToExceptions=true in phpunit.xml, loading config would fail
 * if we used deprecated PDO::MYSQL_ATTR_SSL_CA instead of Pdo\Mysql::ATTR_SSL_CA.
 */

// region MySQL/MariaDB SSL option key (PHP 8.5 PDO deprecation)

test('database config loads without deprecation and mysql options use current SSL attribute', function () {
    $config = config('database.connections.mysql');
    expect($config)->toBeArray()->toHaveKey('options');

    $options = $config['options'] ?? [];
    if (! extension_loaded('pdo_mysql')) {
        expect($options)->toBeArray();

        return;
    }

    $expectedKey = (PHP_VERSION_ID >= 80500 && class_exists(\Pdo\Mysql::class, false))
        ? \Pdo\Mysql::ATTR_SSL_CA
        : PDO::MYSQL_ATTR_SSL_CA;
    expect($options)->toBeArray();
    if ($options !== []) {
        expect(array_key_first($options))->toBe($expectedKey);
    }
});

test('database config mariadb options use current SSL attribute', function () {
    $config = config('database.connections.mariadb');
    expect($config)->toBeArray()->toHaveKey('options');

    $options = $config['options'] ?? [];
    if (! extension_loaded('pdo_mysql')) {
        expect($options)->toBeArray();

        return;
    }

    $expectedKey = (PHP_VERSION_ID >= 80500 && class_exists(\Pdo\Mysql::class, false))
        ? \Pdo\Mysql::ATTR_SSL_CA
        : PDO::MYSQL_ATTR_SSL_CA;
    expect($options)->toBeArray();
    if ($options !== []) {
        expect(array_key_first($options))->toBe($expectedKey);
    }
});

// endregion
