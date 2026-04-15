<?php

namespace App\Services\AI\Parts;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AiRegistryService extends AiBaseService
{
    /**
     * Unified Model Synchronization.
     * Delegates both LLM and Trained model discovery to the Python AI service.
     */
    public function syncModelRegistry(): array
    {
        try {
            $scriptPath = base_path('python/scripts/sync_model_registry.py');
            $pythonPath = base_path('.venv/bin/python');
            
            if (!file_exists($pythonPath)) {
                $pythonPath = 'python3';
            }

            $command = "{$pythonPath} {$scriptPath} 2>&1";
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception("Python registry sync failed: " . implode("\n", $output));
            }

            // Parse JSON output from the script
            $lastLine = end($output);
            $results = json_decode($lastLine, true);

            if (!$results) {
                return [
                    'success' => true,
                    'message' => 'Registry sync completed but output could not be parsed.',
                    'raw_output' => $output
                ];
            }

            $ollama = $results['ollama'] ?? [];
            $trained = $results['trained'] ?? [];

            $message = "Registry synchronized.";
            if ($ollama['success'] ?? false) {
                $message .= " Synced " . ($ollama['synced_count'] ?? 0) . " LLMs.";
                if (($ollama['deactivated_count'] ?? 0) > 0) {
                    $message .= " Deactivated " . $ollama['deactivated_count'] . " stale models.";
                }
            }
            
            if (($trained['registered'] ?? 0) > 0) {
                $message .= " Discovered " . $trained['registered'] . " new trained models.";
            }

            return [
                'success' => true,
                'message' => trim($message),
                'details' => $results
            ];
        } catch (\Exception $e) {
            $this->log('error', "Model Registry Sync Failed", ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => "Could not sync model registry: " . $e->getMessage()];
        }
    }

    /**
     * Compatibility wrapper for LLM sync.
     */
    public function syncLocalModels(): array
    {
        return $this->syncModelRegistry();
    }

    /**
     * Compatibility wrapper for trained model sync.
     */
    public function syncTrainedModels(): array
    {
        return $this->syncModelRegistry();
    }

    /**
     * Get active models from the registry.
     */
    public function getActiveModels(): array
    {
        return DB::connection($this->connection)
            ->table('ai.ai_model_registry')
            ->where('is_active', true)
            ->get()
            ->toArray();
    }
}
