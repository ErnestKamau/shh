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
        $errors[] = [
            'row' => $rowNumber,
            'message' => $this->cleanDataForJson($message),
            'data' => $this->cleanDataForJson($rowData),
        ];
        
        $this->errors_json = $errors;
        $this->error_rows = count($errors);
    }

    /**
     * Recursive helper to ensure data is UTF-8 encoded for JSON storage.
     */
    private function cleanDataForJson($data)
    {
        if (is_string($data)) {
            // Use iconv to strip invalid UTF-8 characters
            // This is more reliable than mb_convert_encoding for fixing malformed JSON
            $cleaned = @iconv('UTF-8', 'UTF-8//IGNORE', $data);
            if ($cleaned === false) {
                return mb_convert_encoding($data, 'UTF-8', 'auto');
            }
            return $cleaned;
        }
        if (is_array($data)) {
            $cleaned = [];
            foreach ($data as $key => $value) {
                $cleanedKey = $this->cleanDataForJson($key);
                $cleaned[$cleanedKey] = $this->cleanDataForJson($value);
            }
            return $cleaned;
        }
        return $data;
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
