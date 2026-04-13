<?php

namespace App\Models\CRM;

use App\Models\Area;
use App\Models\SamplePoint as MasterSamplePoint;
use App\Models\SamplePointArea;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class SamplePoint extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;

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
