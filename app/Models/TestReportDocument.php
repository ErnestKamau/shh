<?php

namespace App\Models;

use App\SampleDetails;
use App\SampleHeader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TestReportDocument extends Model
{
    /** @use HasFactory<\Database\Factories\TestReportDocumentFactory> */
    use HasFactory;

    protected $fillable = [
        'token',
        'batch_id',
        'sample_detail_id',
        'revision_no',
        'language',
        'report_number',
        'file_path',
        'is_official',
        'generated_by',
        'generated_at',
        'view_count',
        'last_viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'revision_no' => 'integer',
            'is_official' => 'boolean',
            'generated_at' => 'datetime',
            'view_count' => 'integer',
            'last_viewed_at' => 'datetime',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(SampleHeader::class, 'batch_id');
    }

    public function sampleDetail(): BelongsTo
    {
        return $this->belongsTo(SampleDetails::class, 'sample_detail_id');
    }

    /**
     * @param  Builder<TestReportDocument>  $query
     * @return Builder<TestReportDocument>
     */
    public function scopeOfficial(Builder $query): Builder
    {
        return $query->where('is_official', true);
    }

    public function publicUrl(): string
    {
        return route('public.test-report.show', ['token' => $this->token]);
    }

    public function hasStoredFile(): bool
    {
        return filled($this->file_path) && Storage::disk('public')->exists((string) $this->file_path);
    }

    public function absoluteFilePath(): ?string
    {
        return $this->hasStoredFile()
            ? Storage::disk('public')->path((string) $this->file_path)
            : null;
    }
}
