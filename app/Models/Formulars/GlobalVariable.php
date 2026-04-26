<?php

namespace App\Models\Formulars;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GlobalVariable extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'value',
        'data_type',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the data type options.
     */
    public static function getDataTypes(): array
    {
        return [
            'string' => 'String',
            'number' => 'Number',
            'boolean' => 'Boolean',
        ];
    }

    /**
     * Get the typed value based on data_type.
     */
    public function getTypedValue()
    {
        switch ($this->data_type) {
            case 'number':
                return is_numeric($this->value) ? (float) $this->value : $this->value;
            case 'boolean':
                return filter_var($this->value, FILTER_VALIDATE_BOOLEAN);
            default:
                return $this->value;
        }
    }

    /**
     * Scope for active variables.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
