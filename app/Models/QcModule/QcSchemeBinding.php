<?php

namespace App\Models\QcModule;

use App\AnalysisMethod;
use App\Models\QcModule\Configurations\QcSchemes;
use App\Standards;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class QcSchemeBinding extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    public const MODE_OVERRIDE = 'override';

    public const MODE_MERGE = 'merge';

    public const MODE_ADDITIVE = 'additive';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'qc_scheme_bindings';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'conditions' => 'array',
        ];
    }

    public function standard(): BelongsTo
    {
        return $this->belongsTo(Standards::class, 'standard_id');
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(AnalysisMethod::class, 'method_id');
    }

    public function scheme(): BelongsTo
    {
        return $this->belongsTo(QcSchemes::class, 'qc_scheme_id');
    }
}
