<?php

namespace App\Exceptions;

use Exception;

class ImportValidationException extends Exception
{
    /**
     * @var array<int, string> Individual error lines (one per row/issue)
     */
    protected array $errorLines;

    public function __construct(string $message, array $errorLines = [])
    {
        parent::__construct($message);
        $this->errorLines = $errorLines;
    }

    public function errorLines(): array
    {
        return $this->errorLines;
    }
}
