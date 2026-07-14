<?php

namespace App;

use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use App\Models\CRM\SamplePoint;
use App\Models\Currency;
use App\Models\SampleSubmissionRequest;
use App\SampleHeader;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class QuotationHeader extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;
    protected $table = 'quotation_headers';

    protected $guarded = ['id'];

    protected $appends = ['creator'];

    protected function casts(): array
    {
        return [
            'from_enquiry' => 'boolean',
            'sent_to_customer_at' => 'datetime',
            'prepared_by_id' => 'string',
            'approved_by' => 'string',
            'show_loq_column' => 'boolean',
            'show_mu_column' => 'boolean',
            'show_unit_price_column' => 'boolean',
            'structured_terms' => 'array',
        ];
    }

    public function getCreatorAttribute(): string
    {
        return $this->prepared_by_name;
    }

    public function getPreparedByNameAttribute(): string
    {
        return $this->preparedBy?->name ?? '-';
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by_id');
    }
    public function details(){
        return $this->hasMany(QuotationDetails::class,'quotation_header_id');
    }
    public function contact(){
        return $this->belongsTo(CustomerContact::class,'crm_customer_contact_id');
    }
    public function customer(){
        return $this->belongsTo(CRMCustomer::class,'crm_customer_id');
    }
    
    public function currency(){
        return $this->belongsTo(Currency::class,'currency_id');
    }

    public function sampleSubmissionRequest(): BelongsTo
    {
        return $this->belongsTo(SampleSubmissionRequest::class, 'sample_submission_request_id');
    }

    public function revisionOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'revision_of_quotation_header_id');
    }

    public function revisions()
    {
        return $this->hasMany(self::class, 'revision_of_quotation_header_id');
    }

    public function samplePoint()
    {
        return $this->belongsTo(SamplePoint::class, 'sample_point_id');
    }

    public function batches()
    {
        return $this->hasMany(SampleHeader::class, 'quote_id');
    }
}
