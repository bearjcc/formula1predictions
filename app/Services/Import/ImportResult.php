<?php

namespace App\Services\Import;

/**
 * Simple aggregate of import results for reporting and testing.
 */
class ImportResult
{
    public int $created = 0;

    public int $updated = 0;

    public int $skipped = 0;

    /**
     * @var list<array{row:int,message:string}>
     */
    public array $errors = [];

    public function incrementCreated(int $by = 1): void
    {
        $this->created += $by;
    }

    public function incrementUpdated(int $by = 1): void
    {
        $this->updated += $by;
    }

    public function incrementSkipped(int $by = 1): void
    {
        $this->skipped += $by;
    }

    public function addError(int $row, string $message): void
    {
        $this->errors[] = [
            'row' => $row,
            'message' => $message,
        ];
    }
}
