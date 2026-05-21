<?php

namespace App\Models\GroupedWorksheets;

use App\AnalysisType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class GroupedWorksheetHolder extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'document_control_no',
        'revision',
        'issue_date',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'issue_date' => 'date',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(GroupedWorksheetItem::class)->orderBy('sort_order');
    }

    public function orderedItems(): HasMany
    {
        return $this->items();
    }

    public function analysisTypes(): HasMany
    {
        return $this->hasMany(AnalysisType::class, 'grouped_worksheet_holder_id');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(GroupedWorksheetRun::class);
    }
}
