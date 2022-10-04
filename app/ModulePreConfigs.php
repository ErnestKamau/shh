<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ModulePreConfigs extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;

	public function states(){
		return \App\ItemState::where('material_type_id', $this->id)
			->join('reporting_units as ru', 'ru.id', '=', 'item_states.uom')
			->selectRaw('item_states.id, item_states.is_default, ru.name as uom, ru.id as uom_id, item_states.name')
			->orderBy('item_states.is_default', 'desc')->orderBy('ru.name', 'asc')->get();
	}

	public function conversions(){
		return \App\UnitOfMeasureConversion::where('material_type_id', $this->id)
			->join('reporting_units as ru1', 'ru1.id', '=', 'unit_of_measure_conversions.uom1')
			->join('reporting_units as ru2', 'ru2.id', '=', 'unit_of_measure_conversions.uom2')
			->selectRaw('unit_of_measure_conversions.id, ru1.id as uom1_id, ru2.id as uom2_id,  ru1.name as uom1, ru2.name as uom2, unit_of_measure_conversions.conversion')
			->orderBy('ru1.name')->get();
	}
}
