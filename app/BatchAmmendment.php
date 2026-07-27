<?php

namespace App;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\User;

class BatchAmmendment extends Model implements Auditable
{
	use HasUuids;
	use \OwenIt\Auditing\Auditable;

    protected $table = 'batch_ammendments';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $appends = ['creator'];
    
    public function getCreatorAttribute(){
        return User::find($this->created_by_id)->name ?? '-';
    }
    
    public function creator()
    {
        return $this->belongsTo('App\User', 'created_by_id');
    }
    
    public function sampleHeader()
    {
        return $this->belongsTo('App\SampleHeader', 'batch_id');
    }

    /**
     * Prefer the amendment matching the batch's current version; fall back to latest.
     */
    public static function resolveForBatch(SampleHeader $batch): ?self
    {
        $version = (int) ($batch->is_amendment ?? 0);

        if ($version > 0) {
            $matched = static::query()
                ->where('batch_id', $batch->id)
                ->where('version_number', $version)
                ->first();

            if ($matched) {
                return $matched;
            }
        }

        return static::query()
            ->where('batch_id', $batch->id)
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Snapshot a prior report path for amendment history (column is NOT NULL).
     */
    public static function snapshotReportUrl(SampleHeader $batch): string
    {
        $direct = trim((string) ($batch->batch_report_url ?? ''));
        if ($direct !== '') {
            return $direct;
        }

        $languageFile = \App\Models\TestRequestReportLanguageFile::query()
            ->where('batch_id', $batch->id)
            ->whereNotNull('report_url')
            ->where('report_url', '!=', '')
            ->orderByDesc('revision_no')
            ->orderByDesc('id')
            ->first();

        if ($languageFile && trim((string) $languageFile->report_url) !== '') {
            return (string) $languageFile->report_url;
        }

        return '';
    }
}
