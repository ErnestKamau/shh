<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkImportBatch extends Model
{
    use HasUuids;

    protected $fillable = [
        'company_id',
        'user_id',
        'module',
        'form_type',
        'status',
        'total_rows',
        'imported_rows',
        'error_rows',
        'errors_json',
        'upserted_summary',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'errors_json' => 'json',
        'upserted_summary' => 'json',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public $keyType = 'string';
    public $incrementing = false;

    /**
     * Get the company associated with the batch.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who initiated the import.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Add error to the batch.
     */
    public function addError(int $rowNumber, string $message, array $rowData = []): void
    {
        $errors = $this->errors_json ?? [];
        $this->error_rows++;
        
        // Limit the number of detailed errors stored to prevent memory/storage issues
        if (count($errors) >= 500) {
            if (count($errors) === 500) {
                $errors[] = [
                    'row' => 0,
                    'message' => 'Further error details suppressed to save memory. Total error count continues to increment.',
                    'data' => [],
                ];
                $this->errors_json = $errors;
            }
            return;
        }

        $errors[] = [
            'row' => $rowNumber,
            'message' => $this->cleanDataForJson($message),
            'data' => $this->cleanDataForJson($rowData),
        ];
        
        $this->errors_json = $errors;
    }

    /**
     * Recursive helper to ensure data is UTF-8 encoded for JSON storage.
     */
    private function cleanDataForJson($data)
    {
        // Use PHP's built-in JSON tools to fix UTF-8 issues recursively
        // JSON_INVALID_UTF8_SUBSTITUTE ensures malformed characters are replaced with 
        // JSON_PARTIAL_OUTPUT_ON_ERROR allows us to save as much as possible
        $flags = defined('JSON_INVALID_UTF8_SUBSTITUTE') ? JSON_INVALID_UTF8_SUBSTITUTE : 1048576;
        $json = json_encode($data, $flags | JSON_PARTIAL_OUTPUT_ON_ERROR);
        
        if ($json === false) {
            // Last resort: if even the partial encoding fails, return a safe fallback
            return is_array($data) ? [] : (string)$data;
        }

        return json_decode($json, true);
    }

    /**
     * Add upserted record info.
     */
    public function addUpsertedRecord(string $identifier, string $action = 'inserted'): void
    {
        $upserted = $this->upserted_summary ?? [];
        if (!isset($upserted[$action])) {
            $upserted[$action] = [];
        }
        $upserted[$action][] = $this->cleanDataForJson($identifier);
        
        $this->upserted_summary = $upserted;
    }

    /**
     * Mark batch as completed.
     */
    public function markAsCompleted(): void
    {
        $this->status = $this->error_rows > 0 ? 'completed_with_errors' : 'completed';
        $this->completed_at = now();
        $this->save();
    }

    /**
     * Mark batch as failed.
     */
    public function markAsFailed(string $reason): void
    {
        $this->status = 'failed';
        $this->addError(0, "Batch failed: {$reason}");
        $this->completed_at = now();
        $this->save();
    }

    /**
     * Get formatted error summary for user display.
     */
    public function getErrorSummary(): array
    {
        $errors = $this->errors_json ?? [];
        $grouped = [];
        
        foreach ($errors as $error) {
            if (!isset($grouped[$error['message']])) {
                $grouped[$error['message']] = [];
            }
            $grouped[$error['message']][] = $error['row'];
        }
        
        return array_map(function ($rows, $message) {
            return [
                'message' => $message,
                'rows' => $rows,
                'count' => count($rows),
            ];
        }, $grouped, array_keys($grouped));
    }

    /**
     * Get formatted upsert summary for user display.
     */
    public function getUpsertSummary(): array
    {
        return [
            'inserted' => count($this->upserted_summary['inserted'] ?? []),
            'updated' => count($this->upserted_summary['updated'] ?? []),
            'records' => $this->upserted_summary ?? [],
        ];
    }
}
