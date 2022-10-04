<?php

namespace App\Http\Controllers\System;

use App\Models\System\SystemConfigurationsType;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SystemConfigurationTypeController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');

	}

	public function add(Request $request){

		$exist = SystemConfigurationsType::where('configuration_type',$request->name)->get();
		if(isset($exist->configuration_type)){
			return redirect()->back()->with('error','There is a configuration type with the specified name!');
		}else{

			$new_configuration_type = new SystemConfigurationsType();
			$new_configuration_type->configuration_type = $request->name;
			$new_configuration_type->description = $request->description;
			$new_configuration_type->save();

			return redirect()->back()->with("success","Configuration type added successfully");
		}
	}

	public function edit(Request $request,$id){
		$configuration_type = SystemConfigurationsType::find($id);
		if(isset($configuration_type->configuration_type) && $configuration_type->id == $request->config_id){
			$configuration_type->configuration_type = $request->name;
			$configuration_type->description = $request->description;
			if(isset($request->status)){

				$configuration_type->status = $request->status;
			}else{
				$configuration_type->status = 1;
			}

			$configuration_type->save();
			return redirect()->back()->with('success','Configuration type edited successffully!');
		}else{
			return redirect()->back()->with('error','Theres is no configuration with the specified ID!');
		}
	}

	public function index(){
		$configuration_types = SystemConfigurationsType::all();
		return view('layouts.configuration.system.configurationType',compact('configuration_types'));
	}



}
