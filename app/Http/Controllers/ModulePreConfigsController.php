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
  public function __construct()
  {
    $this->middleware('auth');
	}

	public function index(Request $request, $config, $module){
		$config_items = ModulePreConfigs::where('type', $config)->where('module', $module)
			->where('inventory_location_id', getCurrentUserLocation()->id)->orderBy('name', 'asc')->get();
		// return response()->json($items, 200);
		return view('layouts.personnel.configs.index', compact('config_items', 'config', 'module'));
	}

	public function view_currency_conversion(Request $request){
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
		$config = "Reporting-Units";
		$module = "Inventory-Management";
		$uoms = UoMConversion::where('uom_conversions.inventory_location_id', getCurrentUserLocation()->id)
		->selectRaw('id, ratio, uom1, uom2')->get();

		return view('layouts.personnel.configs.uom_conversion', compact('uoms', 'module'));
	}

	public function show_material_type($id){
		$materialType = ModulePreConfigs::find($id);
		$module = $materialType->module;
		$config = $materialType->type;

		return view('layouts.personnel.configs.material-type', compact('materialType', 'config', 'module'));
	}

	public function currency_conversion(Request $request){
		$currency = CurrencyConversion::find($request->conversion_id) ?? new CurrencyConversion;
		$currency->currency_1 = $request->currency_1;
		$currency->currency_2 = $request->currency_2;
		$currency->ratio = $request->ratio;
		$currency->inventory_location_id = getCurrentUserLocation()->id;

		$currency->save();

		return redirect()->back()->with('success', 'Currency conversion updated.');
	}

	public function uom_conversion(Request $request){
		$uom = UoMConversion::find($request->conversion_id) ?? new UoMConversion;
		$uom->uom1 = $request->uom1;
		$uom->uom2 = $request->uom2;
		$uom->ratio = $request->ratio;
		$uom->inventory_location_id = getCurrentUserLocation()->id;

		$uom->save();

		return redirect()->back()->with('success', 'UoM conversion updated.');
	}

	public function update(Request $request, $id, $config, $moduleT){
		$module = ModulePreConfigs::find($id) ?? new ModulePreConfigs;
		$module->name = $request->name;
		$module->type = $config;
		$module->description = $request->description;
		$module->module = $moduleT;
		$module->inventory_location_id = getCurrentUserLocation()->id;
		$module->save();

		return redirect()->back()->with('success', 'Module configurations updated.');
	}

	public function addResponsibilities(Request $request,$id){
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
		$designation = ModulePreConfigs::find($id);
		$responsibility = JobDescription::where('job_id',$designation->id)->get();
		foreach($responsibility as $res){
			if($res->edited_by > 0){

				$user = getUserById($res->edited_by);
				$res['edited'] = $user->name;
			}
		}
		$config_type = SystemConfigurationsType::where('configuration_type','Job Designation Responsibilities')->get();
		if(isset($config_type[0]->id)){
			$configs = SystemConfiguration::where('configuration_type_id',$config_type[0]->id)->get();
		}else{
			return redirect()->back()->with('error','Set the (Job Designation Responsibilities) configuration ');
		}



		// return response()->json($responsibility,200);

		return view('layouts.personnel.configs.job_responsibility',compact('designation','responsibility','configs'));
	}

}
