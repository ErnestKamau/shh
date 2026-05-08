<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use App\Models\Area;
use App\Models\SamplePoint as MasterSamplePoint;
use App\Models\SamplePointArea;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class SamplePoint extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    /**
     * Human-readable label for UIs when legacy `name` is absent on `sample_points`.
     *
     * @var list<string>
     */
    protected $appends = [
        'display_name',
    ];

    public function getDisplayNameAttribute(): string
    {
        $name = $this->attributes['name'] ?? null;
        if ($name !== null && $name !== '') {
            return (string) $name;
        }

        return 'Sample point #'.$this->id;
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(CRMCompanyUnit::class, 'crm_company_unit_id');
    }

    public function subUnit(): BelongsTo
    {
        return $this->belongsTo(CRMCompanySubUnit::class, 'crm_company_sub_unit_id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(SamplePointArea::class, 'sample_point_area_id');
    }

    public function crmArea(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'crm_area_id');
    }

    public function crmSamplePoint(): BelongsTo
    {
        return $this->belongsTo(MasterSamplePoint::class, 'crm_sample_point_id');
    }
}
