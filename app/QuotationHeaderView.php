<?php

namespace App;

use App\Casts\PlaintextWithLegacyDecrypt;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OwenIt\Auditing\Contracts\Auditable;

class QuotationHeaderView extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'quotation_header_view';

    protected $appends = ['contact'];

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by_id');
    }

    /**
     * @return BelongsToMany<SampleAnalysisStage, $this>
     */
    public function labSections(): BelongsToMany
    {
        return $this->belongsToMany(
            SampleAnalysisStage::class,
            'quotation_header_lab_sections',
            'quotation_header_id',
            'lab_section_id',
        )->withTimestamps();
    }

    public function getContactAttribute(): string
    {
        $parts = array_filter([
            trim((string) ($this->contact_first ?? '')),
            trim((string) ($this->contact_middle ?? '')),
            trim((string) ($this->contact_last ?? '')),
        ], static fn (string $part): bool => $part !== '');

        return $parts === [] ? '—' : implode(' ', $parts);
    }

    public function getPreparedByNameAttribute(?string $value): string
    {
        $decrypted = $value !== null && $value !== ''
            ? (string) (new PlaintextWithLegacyDecrypt)->get($this, 'prepared_by_name', $value, $this->attributes)
            : '';

        if ($decrypted !== '' && ! str_starts_with($decrypted, 'eyJ')) {
            return $decrypted;
        }

        $fromUser = $this->preparedBy?->name;
        if (is_string($fromUser) && $fromUser !== '') {
            return $fromUser;
        }

        return $decrypted !== '' ? $decrypted : '-';
    }
}
