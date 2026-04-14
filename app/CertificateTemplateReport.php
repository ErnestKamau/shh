<?php

namespace App;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateTemplateReport extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'certificate_template_id',
        'submission_form_instance_id',
        'generated_by',
        'file_path',
        'generated_at',
        'status',
        'error_message'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'generated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Status constants.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_GENERATING = 'generating';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * Get all available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_GENERATING => 'Generating',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed'
        ];
    }

    /**
     * Get the template that was used to generate this report.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class, 'certificate_template_id');
    }

    /**
     * Get the submission form instance that was used to generate this report.
     */
    public function submissionFormInstance(): BelongsTo
    {
        return $this->belongsTo(SubmissionFormInstance::class, 'submission_form_instance_id');
    }

    /**
     * Get the user who generated this report.
     */
    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Scope a query to only include reports with a specific status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include pending reports.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include generating reports.
     */
    public function scopeGenerating($query)
    {
        return $query->where('status', self::STATUS_GENERATING);
    }

    /**
     * Scope a query to only include completed reports.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope a query to only include failed reports.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Check if this report is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if this report is generating.
     */
    public function isGenerating(): bool
    {
        return $this->status === self::STATUS_GENERATING;
    }

    /**
     * Check if this report is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if this report failed.
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if this report has an error.
     */
    public function hasError(): bool
    {
        return !empty($this->error_message);
    }

    /**
     * Check if this report file exists.
     */
    public function fileExists(): bool
    {
        return file_exists(storage_path('app/' . $this->file_path));
    }

    /**
     * Get the file size in bytes.
     */
    public function getFileSizeAttribute(): int
    {
        if (!$this->fileExists()) {
            return 0;
        }

        return filesize(storage_path('app/' . $this->file_path));
    }

    /**
     * Get the file size in human readable format.
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        $statuses = self::getStatuses();
        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Get the download URL for this report.
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('certificate-templates.reports.download', $this->id);
    }

    /**
     * Mark this report as generating.
     */
    public function markAsGenerating(): bool
    {
        return $this->update(['status' => self::STATUS_GENERATING]);
    }

    /**
     * Mark this report as completed.
     */
    public function markAsCompleted(string $filePath): bool
    {
        return $this->update([
            'status' => self::STATUS_COMPLETED,
            'file_path' => $filePath,
            'generated_at' => now()
        ]);
    }

    /**
     * Mark this report as failed.
     */
    public function markAsFailed(string $errorMessage): bool
    {
        return $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage
        ]);
    }
}
