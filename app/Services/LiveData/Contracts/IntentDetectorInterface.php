<?php

namespace App\Services\LiveData\Contracts;

interface IntentDetectorInterface
{
    /**
     * Detect intent from a raw message.
     */
    public function detect(string $message): ?array;
}
