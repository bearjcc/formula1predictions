<?php

namespace App\Services\Import;

use App\Models\Drivers;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class DriverImportService
{
    /**
     * Import an array of normalized driver rows.
     *
     * Each row should be an associative array with at least:
     * - driver_id (string|int)
     * - name (string)
     *
     * Optional keys such as surname, nationality, date_of_birth, and is_active
     * will be used when present.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function import(array $rows, bool $dryRun = false): ImportResult
    {
        $result = new ImportResult;

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 1;

            $validationError = $this->validateRow($row);
            if ($validationError !== null) {
                $result->incrementSkipped();
                $result->addError($rowNumber, $validationError);

                continue;
            }

            $payload = $this->normalizePayload($row);

            // Use a small transaction so a failure in one row does not leave
            // partial updates while still keeping each row atomic.
            $outcome = DB::transaction(function () use ($payload, $dryRun) {
                $driverId = (string) $payload['driver_id'];

                /** @var Drivers|null $existing */
                $existing = Drivers::where('driver_id', $driverId)->first();

                if ($dryRun) {
                    return $existing ? 'updated' : 'created';
                }

                if ($existing) {
                    $existing->fill($this->allowedFields($payload));
                    $existing->save();

                    return 'updated';
                }

                Drivers::create($this->allowedFields($payload));

                return 'created';
            });

            if ($outcome === 'created') {
                $result->incrementCreated();
            } elseif ($outcome === 'updated') {
                $result->incrementUpdated();
            } else {
                $result->incrementSkipped();
                $result->addError($rowNumber, 'Unknown import outcome for driver.');
            }
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function validateRow(array $row): ?string
    {
        if (! isset($row['driver_id']) || $row['driver_id'] === null || $row['driver_id'] === '') {
            return 'Missing required field: driver_id';
        }

        if (! isset($row['name']) || trim((string) $row['name']) === '') {
            return 'Missing required field: name';
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function normalizePayload(array $row): array
    {
        $normalized = $row;

        $normalized['driver_id'] = (string) Arr::get($row, 'driver_id');

        if (isset($row['name'])) {
            $normalized['name'] = trim((string) $row['name']);
        }

        if (isset($row['surname'])) {
            $normalized['surname'] = trim((string) $row['surname']);
        }

        if (isset($row['nationality'])) {
            $normalized['nationality'] = trim((string) $row['nationality']);
        }

        if (isset($row['is_active'])) {
            $normalized['is_active'] = (bool) $row['is_active'];
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function allowedFields(array $payload): array
    {
        return Arr::only($payload, [
            'driver_id',
            'name',
            'surname',
            'nationality',
            'url',
            'driver_number',
            'description',
            'photo_url',
            'helmet_url',
            'date_of_birth',
            'website',
            'twitter',
            'instagram',
            'world_championships',
            'race_wins',
            'podiums',
            'pole_positions',
            'fastest_laps',
            'points',
            'is_active',
        ]);
    }
}
