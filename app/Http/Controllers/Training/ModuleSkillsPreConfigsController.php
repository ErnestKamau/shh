<?php

namespace App\Http\Controllers\SkillsMatrix;
use App\JobDescription;
use App\Models\System\SystemConfiguration;
use App\Http\Controllers\Controller;
use App\Models\System\SystemConfigurationsType;
use App\ModulePreConfigs;
use App\CurrencyConversion;
use App\UoMConversion;
use Illuminate\Http\Request;

class ModuleSkillsPreConfigsController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
	}

	public function index(Request $request, $config, $module){
		
					
		if($config=='Roles') {
			$config = 'Job Description';			
			$config_items = ModulePreConfigs::where('type', $config)
			->where('inventory_location_id', getCurrentUserLocation()->id)->orderBy('name', 'asc')->get();
			return view('layouts.skillsmatrix.configs.index', compact('config_items', 'config', 'module'));
		}else if($config=='Education'){
			$config = 'Educational Levels';			
			$config_items = ModulePreConfigs::where('type', $config)
			->where('inventory_location_id', getCurrentUserLocation()->id)->orderBy('name', 'asc')->get();
			return view('layouts.skillsmatrix.configs.index', compact('config_items', 'config', 'module'));
		}else{
			$config_items = ModulePreConfigs::where('type', $config)->where('module', $module)
			->where('inventory_location_id', getCurrentUserLocation()->id)->orderBy('level', 'asc')->get();
			return view('layouts.skillsmatrix.configs.index', compact('config_items', 'config', 'module'));	
		}	
		
	}

	public function getLastLevel($module){
		$maxLevel = ModulePreConfigs::where('type', $module)->max('level');
		return $maxLevel ?? 0;
	}

	
	public function move_skills_types($direction,$module, $element){

		$theElement = ModulePreConfigs::find($element);				
		$currentLevel = $theElement->level;
		$currentMaxLevel = $this->getLastLevel($module);
		if($currentMaxLevel == 0 || $currentLevel == null){
			$theElement->level = $currentMaxLevel+1;
			$theElement->save();
			return json_encode(array("status"=>true));
		}

		if($direction == 'move-up'){
			$newLevel = intval($currentLevel)-1;
		}
		else{
			$newLevel = intval($currentLevel)+1;
		}

		$newLevel = $newLevel < 1 ? 1 : $newLevel;
		$sibling = ModulePreConfigs::where('type', $module)->where('level', $newLevel)->first();

		if($sibling){
			$sibling->level = $currentLevel;
			$sibling->save();
		}

		$theElement->level = $newLevel;
		$theElement->save();

		return json_encode(array("status"=>true));
  }
		
  	public function getLastLevelID(){
		$maxLevel = ModulePreConfigs::max('level');
		return $maxLevel ?? 0;
	}

	public function update(Request $request, $id, $config, $moduleT){
		
		$module = ModulePreConfigs::find($id) ?? new ModulePreConfigs;
		$module->name = $request->name;
		$module->type = $config;
		$module->description = $request->description;
		if(!ModulePreConfigs::find($id)){
			$currentMaxLevel = $this->getLastLevelID();
			$module->level =$currentMaxLevel+1;
		}
		if(isset($request->color) && !empty($request->color)){
			$module->color = $request->color;
		}	
		$module->module = $moduleT;
		$module->inventory_location_id = getCurrentUserLocation()->id;
		$module->active = $request->active ?? 0;
		$module->save();

		return redirect()->back()->with('success', 'Module configurations updated.');
	}

	
	
	

}
