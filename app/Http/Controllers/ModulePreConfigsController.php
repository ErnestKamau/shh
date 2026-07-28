<?php

namespace App\Http\Controllers;

use App\JobDescription;
use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;
use App\ModulePreConfigs;
use App\CurrencyConversion;
use App\UoMConversion;
use Illuminate\Http\Request;

class ModulePreConfigsController extends Controller
{
	/**
	 * Module slug to accepted access permission names.
	 */
	private array $moduleAccessPermissionMap = [
		'Inventory-Management' => ['access inventory', 'inventory.permission'],
		'Lab-Management' => ['access laboratory', 'laboratory.permission'],
		'Personnel-Management' => ['access personnel', 'personnel.permission', 'Personnel.permission', 'personnel.module.access', 'personnel.configurations.view'],
		'Skills-Matrix' => ['access skills matrix', 'skills-matrix.permission'],
		'Equipment-Management' => ['access equipment', 'equipment.permission'],
		'CRM' => ['access crm', 'crm.permission'],
	];

  public function __construct()
  {
    $this->middleware('auth');
	}

	private function authorizeModuleAccess(string $module): void
	{
		$user = auth()->user();

		if ($user && method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin()) {
			return;
		}

		$allowedPermissions = $this->moduleAccessPermissionMap[$module] ?? [];

		$hasAccess = false;
		foreach ($allowedPermissions as $permissionName) {
			if ($user->can($permissionName)) {
				$hasAccess = true;
				break;
			}
		}

		if (! $hasAccess) {
			abort(403, 'You do not have access to this module.');
		}
	}

	public function index(Request $request, $config, $module){
		$this->authorizeModuleAccess($module);

		if (in_array($config, ['Zones', 'Zone'], true)) {
			$layout = $module === 'Lab-Management' ? 'livewire.layout.lab-app' : 'livewire.layout.personnel-app';
			return view($layout, [
				'componentType' => 'zones',
				'pageTitle' => 'Zone Configuration',
				'config' => 'Zones',
				'module' => $module,
			]);
		}

		if ($module === 'Personnel-Management') {
			return view('livewire.layout.personnel-app', [
				'componentType' => 'personnel-configurations',
				'pageTitle' => $config . ' Configurations',
				'config' => $config,
				'module' => $module,
			]);
		}

		$config_items = ModulePreConfigs::where('type', $config)->where('module', $module)
			->where('inventory_location_id', getCurrentUserLocation()->id)->orderBy('name', 'asc')->get();
		// return response()->json($items, 200);
		return view('layouts.personnel.configs.index', compact('config_items', 'config', 'module'));
	}

	public function view_currency_conversion(Request $request){
		$this->authorizeModuleAccess('Inventory-Management');

		$currencies = CurrencyConversion::join('module_pre_configs as mpc', function($join){
			$config = "Currency";
			$module = "Inventory-Management";
			$join->on('mpc.id', '=', 'currency_conversions.currency_1');
			$join->where('mpc.type', $config)->where('mpc.module', $module);
		})
		->join('module_pre_configs as mpc2', function($join){
			$config = "Currency";
			$module = "Inventory-Management";
			$join->on('mpc2.id', '=', 'currency_conversions.currency_2');
			$join->where('mpc2.type', $config)->where('mpc2.module', $module);
		})
		->where('currency_conversions.inventory_location_id', getCurrentUserLocation()->id)
		->selectRaw('currency_conversions.id, currency_conversions.ratio, mpc.id as currency_1, mpc2.id as currency_2, mpc.name as currency_1_name, mpc2.name as currency_2_name')->get();
		$module = "Inventory-Management";
		return view('layouts.personnel.configs.currency_conversion', compact('currencies', 'module'));
	}

	public function view_uom_conversion(Request $request){
		$this->authorizeModuleAccess('Inventory-Management');

		$config = "Reporting-Units";
		$module = "Inventory-Management";
		$uoms = UoMConversion::where('uom_conversions.inventory_location_id', getCurrentUserLocation()->id)
		->selectRaw('id, ratio, uom1, uom2')->get();

		return view('layouts.personnel.configs.uom_conversion', compact('uoms', 'module'));
	}

	public function show_material_type($id){
		$materialType = ModulePreConfigs::find($id);
		if (! $materialType) {
			abort(404);
		}

		$module = $materialType->module;
		$this->authorizeModuleAccess($module);

		$config = $materialType->type;

		return view('layouts.personnel.configs.material-type', compact('materialType', 'config', 'module'));
	}

	public function currency_conversion(Request $request){
		$this->authorizeModuleAccess('Inventory-Management');

		$conversionId = $request->conversion_id;
		$currency = (\Illuminate\Support\Str::isUuid((string) $conversionId) ? CurrencyConversion::find($conversionId) : null) ?? new CurrencyConversion;
		$currency->currency_1 = $request->currency_1;
		$currency->currency_2 = $request->currency_2;
		$currency->ratio = $request->ratio;
		$currency->inventory_location_id = getCurrentUserLocation()->id;

		$currency->save();

		return redirect()->back()->with('success', 'Currency conversion updated.');
	}

	public function uom_conversion(Request $request){
		$this->authorizeModuleAccess('Inventory-Management');

		$conversionId = $request->conversion_id;
		$uom = (\Illuminate\Support\Str::isUuid((string) $conversionId) ? UoMConversion::find($conversionId) : null) ?? new UoMConversion;
		$uom->uom1 = $request->uom1;
		$uom->uom2 = $request->uom2;
		$uom->ratio = $request->ratio;
		$uom->inventory_location_id = getCurrentUserLocation()->id;

		$uom->save();

		return redirect()->back()->with('success', 'UoM conversion updated.');
	}

	public function update(Request $request, $id, $config, $moduleT){
		$this->authorizeModuleAccess($moduleT);

		if (in_array($config, ['Zones', 'Zone'], true)) {
			$zone = (\Illuminate\Support\Str::isUuid((string) $id) ? \App\Zone::find($id) : null) ?? new \App\Zone;
			$zone->key = $request->name;
			$zone->value = $request->value;
			$zone->description = $request->description;
			if (!$zone->exists) {
				$zone->module = 'Global';
			}
			$zone->inventory_location_id = getCurrentUserLocation()->id;
			$zone->save();

			return redirect()->back()->with('success', 'Zone updated successfully.');
		}

		$module = (\Illuminate\Support\Str::isUuid((string) $id) ? ModulePreConfigs::find($id) : null) ?? new ModulePreConfigs;
		$module->name = $request->name;
		$module->type = $config;
		$module->description = $request->description;
		$module->module = $moduleT;
		
		$module->inventory_location_id = getCurrentUserLocation()->id;
		$module->save();

		return redirect()->back()->with('success', 'Module configurations updated.');
	}

	public function addResponsibilities(Request $request,$id){
		$this->authorizeModuleAccess('Personnel-Management');

		$responsibility = new JobDescription();
		$responsibility->job_id = $id;
		$config = SystemConfiguration::find((int)$request->name);

		$responsibility->config_id = $config->id;
		$responsibility->name = $config->key;
		$responsibility->description = $config->value;
		if(isset($request->status)){
			$responsibility->active = 1;
		}
		$responsibility->save();
		return redirect()->back()->with('success','Job responsibility added successfully!');

	}
	public function editResposibility(Request $request,$id){
		$this->authorizeModuleAccess('Personnel-Management');

		$responsibility = JobDescription::find($id);
		$config = SystemConfiguration::find((int)$request->name);
		$responsibility->config_id = $config->id;
		$responsibility->name = $config->key;
		$responsibility->description = $config->value;
		$responsibility->edited_by = auth()->user()->id;
		if(isset($request->status)){
			$responsibility->active = 1;
		}else{
			$responsibility->active = 0;
		}
		$responsibility->save();
		return redirect()->back()->with('success','Job responsibility edited successfully!');
	}
	public function showResponsibility($id){
		$this->authorizeModuleAccess('Personnel-Management');

		return view('livewire.layout.personnel-app', [
			'componentType' => 'job-responsibility',
			'pageTitle' => 'Job Responsibilities',
			'designationId' => (int) $id,
		]);
	}

}
