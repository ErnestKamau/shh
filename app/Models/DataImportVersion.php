<?php

namespace App\Models;

use App\Company;
use App\Models\Sampleworkflow\LabSectionWorksheet;
use App\SampleHeader;
use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DataImportVersion extends Model
{
    use HasUuids;

    public const SCOPE_BATCH_RESULTS = 'batch_results';

    public const SCOPE_LAB_MASTER = 'lab_master';

    public const STATUS_APPLIED = 'applied';

    public const STATUS_SUPERSEDED = 'superseded';

    public const STATUS_ROLLED_BACK = 'rolled_back';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'user_id',
        'scope',
        'scope_key',
        'version',
        'status',
        'file_path',
        'pre_apply_snapshot_path',
        'change_summary',
        'bulk_import_batch_id',
        'sample_header_id',
        'lab_section_worksheet_id',
        'notes',
        'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'change_summary' => 'array',
            'version' => 'integer',
            'applied_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bulkImportBatch(): BelongsTo
    {
        return $this->belongsTo(BulkImportBatch::class, 'bulk_import_batch_id');
    }

    public function sampleHeader(): BelongsTo
    {
        return $this->belongsTo(SampleHeader::class, 'sample_header_id');
    }

    public function labSectionWorksheet(): BelongsTo
    {
        return $this->belongsTo(LabSectionWorksheet::class, 'lab_section_worksheet_id');
    }

    public function isCurrent(): bool
    {
        return $this->status === self::STATUS_APPLIED;
    }

    public function fileExists(): bool
    {
        return filled($this->file_path) && Storage::disk('public')->exists($this->file_path);
    }

    public function snapshotExists(): bool
    {
        return filled($this->pre_apply_snapshot_path)
            && Storage::disk('public')->exists($this->pre_apply_snapshot_path);
    }

    public static function labMasterScopeKey(string $module, string $formType): string
    {
        return strtolower(trim($module)).':'.strtolower(trim($formType));
    }
}
