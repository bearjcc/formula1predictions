<?php

namespace App\Console\Commands;

use App\Services\Import\DriverImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportLegacyDrivers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'legacy:import-drivers
                            {path : Path to the driver CSV file}
                            {--dry-run : Validate and report without writing to the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import legacy driver data from a CSV file';

    /**
     * Execute the console command.
     */
    public function handle(DriverImportService $service): int
    {
        $path = (string) $this->argument('path');

        $resolvedPath = $this->resolvePath($path);
        if ($resolvedPath === null) {
            $this->error("File not found or not readable: {$path}");

            return 1;
        }

        try {
            $rows = $this->readCsvRows($resolvedPath);
        } catch (\Throwable $e) {
            $this->error('Failed to read CSV: '.$e->getMessage());

            return 1;
        }

        if ($rows === []) {
            $this->info('No rows found in CSV file.');

            return 0;
        }

        $dryRun = (bool) $this->option('dry-run');

        $this->info(sprintf(
            'Importing %d driver row(s) from %s%s...',
            count($rows),
            $resolvedPath,
            $dryRun ? ' (dry run)' : ''
        ));

        $result = $service->import($rows, $dryRun);

        $this->line(sprintf(
            'Created: %d, Updated: %d, Skipped: %d, Errors: %d',
            $result->created,
            $result->updated,
            $result->skipped,
            count($result->errors),
        ));

        if ($result->errors !== []) {
            $this->warn('Some rows failed to import:');

            foreach (array_slice($result->errors, 0, 10) as $error) {
                $this->error(sprintf('Row %d: %s', $error['row'], $error['message']));
            }

            if (count($result->errors) > 10) {
                $this->warn(sprintf('...and %d more error(s).', count($result->errors) - 10));
            }

            return 1;
        }

        $this->info('Driver import completed successfully.');

        return 0;
    }

    private function resolvePath(string $path): ?string
    {
        if ($path === '') {
            return null;
        }

        if (File::isFile($path) && File::isReadable($path)) {
            return $path;
        }

        $candidate = base_path($path);
        if (File::isFile($candidate) && File::isReadable($candidate)) {
            return $candidate;
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function readCsvRows(string $path): array
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new \RuntimeException('Unable to open file for reading.');
        }

        try {
            $header = null;
            $rows = [];

            while (($data = fgetcsv($handle)) !== false) {
                // Skip completely empty lines
                if ($data === [null] || $data === [] || (count($data) === 1 && trim((string) $data[0]) === '')) {
                    continue;
                }

                if ($header === null) {
                    $header = array_map(static fn ($value) => trim((string) $value), $data);

                    continue;
                }

                $row = [];
                foreach ($header as $index => $column) {
                    if ($column === '') {
                        continue;
                    }

                    $row[$column] = $data[$index] ?? null;
                }

                if ($row !== []) {
                    $rows[] = $row;
                }
            }
        } finally {
            fclose($handle);
        }

        return $rows;
    }
}
