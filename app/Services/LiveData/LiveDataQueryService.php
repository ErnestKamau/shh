<?php

namespace App\Services\LiveData;

use Illuminate\Support\Facades\Log;
use App\Services\LiveData\Contracts\IntentDetectorInterface;
use App\Services\LiveData\Contracts\LiveDataHandlerInterface;
use App\Services\LiveData\DTOs\LiveDataResult;

/**
 * High-availability Coordinator for operational LIMS data queries.
 * This service operates independently of the AI stack to provide 
 * deterministic metrics and facts even during Python service outages.
 */
class LiveDataQueryService
{
    /** @var LiveDataHandlerInterface[] */
    protected array $handlers;

    public function __construct(
        protected IntentDetectorInterface $detector,
        iterable $handlers
    ) {
        $this->handlers = is_array($handlers) ? $handlers : iterator_to_array($handlers);
    }

    /**
     * Entry point for executing an operational query.
     */
    public function execute(string $intent, ?string $question = null): LiveDataResult
    {
        try {
            foreach ($this->handlers as $handler) {
                if ($handler->supports($intent)) {
                    return $handler->handle($intent, $question);
                }
            }

            return new LiveDataResult(
                intent: $intent,
                reply: "I recognized the intent '{$intent}', but no operational handler is registered to fulfill it.",
                value: null
            );
        } catch (\Throwable $e) {
            Log::error('LiveDataQueryService: Execution failed', [
                'intent' => $intent,
                'error'  => $e->getMessage(),
            ]);

            return new LiveDataResult(
                intent: $intent,
                reply: "I encountered an error while retrieving live operational data. Please check the system logs.",
                value: null
            );
        }
    }

    /**
     * Integrated flow: Detect intent and execute if it matches an operational pattern.
     */
    public function tryExecuteFromMessage(string $message): ?LiveDataResult
    {
        $detection = $this->detector->detect($message);

        if (!$detection) {
            return null;
        }

        return $this->execute($detection['intent'], $message);
    }
}
