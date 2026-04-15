<?php

namespace App\Services\LiveData\Contracts;

use App\Services\LiveData\DTOs\LiveDataResult;

interface LiveDataHandlerInterface
{
    /**
     * Determine if this handler supports the given intent.
     */
    public function supports(string $intent): bool;

    /**
     * Execute the query logic and return a typed result.
     */
    public function handle(string $intent, ?string $question = null): LiveDataResult;
}
