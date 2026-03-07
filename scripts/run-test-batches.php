<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

require __DIR__.'/../vendor/autoload.php';

$root = dirname(__DIR__);
$batchCount = 2;
$selectedBatch = null;

foreach ($argv as $argument) {
    if (str_starts_with($argument, '--batches=')) {
        $batchCount = max(1, (int) substr($argument, strlen('--batches=')));
    }

    if (str_starts_with($argument, '--batch=')) {
        $selectedBatch = max(1, (int) substr($argument, strlen('--batch=')));
    }
}

$finder = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root.'/tests', FilesystemIterator::SKIP_DOTS)
);

$testFiles = [];

foreach ($finder as $file) {
    if (! $file instanceof SplFileInfo || ! $file->isFile()) {
        continue;
    }

    $path = str_replace('\\', '/', $file->getPathname());

    if (! str_ends_with($path, 'Test.php')) {
        continue;
    }

    if (! str_contains($path, '/tests/Unit/') && ! str_contains($path, '/tests/Feature/')) {
        continue;
    }

    $testFiles[] = substr($path, strlen(str_replace('\\', '/', $root)) + 1);
}

sort($testFiles);

if ($testFiles === []) {
    fwrite(STDERR, "No Unit/Feature tests were discovered.\n");

    exit(1);
}

$batchSize = (int) ceil(count($testFiles) / $batchCount);
$batches = array_chunk($testFiles, $batchSize);
$phpBinary = PHP_BINARY;
$batchesToRun = $batches;

if ($selectedBatch !== null) {
    if (! isset($batches[$selectedBatch - 1])) {
        fwrite(STDERR, sprintf("Batch %d does not exist.\n", $selectedBatch));

        exit(1);
    }

    $batchesToRun = [$selectedBatch - 1 => $batches[$selectedBatch - 1]];
}

foreach ($batchesToRun as $index => $batch) {
    $batchNumber = $index + 1;
    $label = sprintf(
        'Batch %d/%d (%d tests)',
        $batchNumber,
        count($batches),
        count($batch)
    );

    fwrite(STDOUT, $label.PHP_EOL);

    $clearViews = new Process([$phpBinary, 'artisan', 'view:clear'], $root);
    $clearViews->setTimeout(null);
    $clearViews->run(static function (string $type, string $output): void {
        if ($type === Process::ERR) {
            fwrite(STDERR, $output);

            return;
        }

        fwrite(STDOUT, $output);
    });

    if (! $clearViews->isSuccessful()) {
        exit($clearViews->getExitCode() ?? 1);
    }

    $process = new Process(
        array_merge([$phpBinary, 'artisan', 'test'], $batch),
        $root
    );
    $process->setTimeout(null);
    $process->run(static function (string $type, string $output): void {
        if ($type === Process::ERR) {
            fwrite(STDERR, $output);

            return;
        }

        fwrite(STDOUT, $output);
    });

    if (! $process->isSuccessful()) {
        exit($process->getExitCode() ?? 1);
    }
}

if ($selectedBatch !== null) {
    fwrite(STDOUT, sprintf("Batch %d passed.\n", $selectedBatch));

    exit(0);
}

fwrite(STDOUT, "All discovered Unit/Feature test batches passed.\n");
