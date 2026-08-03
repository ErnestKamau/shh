<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;

class Analyte extends Model implements Auditable
{
    use HasUuids;
	use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = [
        'code',
        'name',
        'common_name',
        'decimal_places',
        'equivalent_weight',
        'reporting_symbol',
        'reporting_unit',
        'method',
        'equipment_id',
        'is_italic',
        'non_detectable',
        'non_accredited',
        'show_on_report',
        'active',
        'company_id',
        'deleted_at'
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('notDeleted', function ($query) {
            $query->whereNull('deleted_at');
        });
    }

    public function analysis_elements()
    {
        return $this->hasMany('App\AnalysisElements');
    }

	public function equipment(){
        return $this->belongsTo('App\Models\Equipments\Equipment');
    }

	public function method(){
        return $this->belongsTo('App\AnalysisMethod', 'method');
	}

	/**
	 * Many-to-many relationship with AnalysisMethod via analyte_methods pivot table.
	 */
	public function analysisMethods()
	{
		return $this->belongsToMany(
			AnalysisMethod::class,
			'analyte_methods',
			'analyte_id',
			'analysis_method_id'
		)->withTimestamps();
	}

	/**
	 * Many-to-many relationship with Equipment via analyte_equipment pivot table.
	 */
	public function equipmentItems()
	{
		return $this->belongsToMany(
			Models\Equipments\Equipment::class,
			'analyte_equipment',
			'analyte_id',
			'equipment_id'
		)->withTimestamps();
	}

	public function methods(){
		$methods = $this->analysisMethods()->get();
		$response = [];
		foreach ($methods as $method) {
			$response[$method->name] = $method->id;
		}
		return $response;
	}

	public function equipments(){
		$equipments = $this->equipmentItems()->get();
		$response = [];
		foreach ($equipments as $equipment) {
			$response[$equipment->name] = $equipment->id;
		}
		return $response;
	}

	/**
	 * Report display label: plain bold-friendly text (no scream-case / underscores).
	 */
	public function plainReportDisplay(): string
	{
		$code = trim(str_replace('_', ' ', preg_replace('/\s+/', ' ', (string) $this->code) ?? (string) $this->code));

		if ($code === '') {
			return '';
		}

		if (preg_match('/^[A-Z0-9]+(?: [A-Z0-9]+)*$/', $code) === 1) {
			return \Illuminate\Support\Str::title(strtolower($code));
		}

		return $code;
	}
}
