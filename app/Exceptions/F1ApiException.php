<?php

namespace App\Exceptions;

use Exception;

class F1ApiException extends Exception
{
    public function __construct(
        string $message,
        public ?int $statusCode = null,
        public ?string $endpoint = null,
        public ?int $year = null,
        ?Exception $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * Get structured context for logging (no sensitive data).
     *
     * @return array{year?: int, endpoint?: string, status?: int}
     */
    public function getLogContext(): array
    {
        $context = [];
        if ($this->year !== null) {
            $context['year'] = $this->year;
        }
        if ($this->endpoint !== null) {
            $context['endpoint'] = $this->endpoint;
        }
        if ($this->statusCode !== null) {
            $context['status'] = $this->statusCode;
        }

        return $context;
    }
}
