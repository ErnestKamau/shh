<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;
use App\User;
use Modules\QualityControl\Entities\Configurations\QcSchemes;
use Modules\QualityControl\Entities\Configurations\QcTypes as ConfigurationsQcTypes;

class Standards extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;
    protected $table = 'standards';
    protected $fillable = [
        'name', 'code', 'main_standard', 'is_qc_standard', 
        'qc_type_id', 'qc_scheme_ids', 'status', 'edited_by'
    ];
    protected $appends = ['qcschemeidsarr','qcschemenames'];

    public function getQcType(){
        return ConfigurationsQcTypes::find($this->qc_type_id);
    }
    public function creator(){
        return User::find($this->edited_by);
    }
    
    public function getQcSchemeIdsArrAttribute(){
        return explode(',',$this->qc_scheme_ids);
    }
    public function getQcSchemeNamesAttribute(): string
    {
        if (empty($this->qc_scheme_ids)) {
            return '';
        }

        $values = array_values(array_filter(array_map('trim', explode(',', (string) $this->qc_scheme_ids))));
        if ($values === []) {
            return '';
        }

        $uuidIds = [];
        $codes = [];

        foreach ($values as $value) {
            if ($this->isUuid($value)) {
                $uuidIds[] = $value;
            } else {
                $codes[] = $value;
            }
        }

        $resolved = [];

        if ($uuidIds !== []) {
            $resolved = array_merge($resolved, QcSchemes::whereIn('id', $uuidIds)->pluck('code')->all());
        }

        if ($codes !== []) {
            $foundByCode = QcSchemes::whereIn('code', $codes)->pluck('code')->all();
            $resolved = array_merge($resolved, $foundByCode);

            foreach ($codes as $code) {
                if (! in_array($code, $foundByCode, true)) {
                    $resolved[] = $code;
                }
            }
        }

        return implode(', ', array_values(array_unique($resolved)));
    }

    private function isUuid(string $value): bool
    {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value);
    }

    public function standardAnalytes(){
        return $this->hasMany('App\StandardAnalytes', 'standard_id');
    }
}
