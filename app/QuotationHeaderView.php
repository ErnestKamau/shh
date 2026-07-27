<?php

namespace App;

use App\Casts\PlaintextWithLegacyDecrypt;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class QuotationHeaderView extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'quotation_header_view';

    protected $appends = ['contact'];

    protected function casts(): array
    {
        return [
            // users.name may still be legacy ciphertext; the SQL view returns it raw.
            'prepared_by_name' => PlaintextWithLegacyDecrypt::class,
        ];
    }

    public function getContactAttribute(): string
    {
        return $this->contact_first.$this->contact_middle.$this->contact_last;
    }
}
