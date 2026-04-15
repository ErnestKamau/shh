<?php

namespace App\Jobs\AI;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use App\Services\AI\AiInferenceService;

class AiPredictionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Model $entity;
    protected string $modelType;
    protected bool $forceFresh;

    /**
     * Create a new job instance.
     */
    public function __construct(Model $entity, string $modelType, bool $forceFresh = true)
    {
        $this->entity = $entity;
        $this->modelType = $modelType;
        $this->forceFresh = $forceFresh;
    }

    /**
     * Execute the job.
     */
    public function handle(AiInferenceService $inferenceService): void
    {
        // 1. Invalidate old predictions for this entity/model
        if ($this->forceFresh) {
            \Illuminate\Support\Facades\DB::connection('pgsql_ai')
                ->table('ai.ai_predictions')
                ->where('entity_type', get_class($this->entity))
                ->where('entity_id', $this->entity->id)
                ->where('model_type', $this->modelType)
                ->update(['is_valid' => false]);
        }

        // 2. Perform Inference & Persist
        $inferenceService->getPrediction($this->entity, $this->modelType, true);
    }
}
