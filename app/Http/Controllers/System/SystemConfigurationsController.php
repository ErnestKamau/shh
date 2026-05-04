<?php

namespace App\Http\Controllers\System;

use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SystemConfigurationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function add(Request $request,$id){
        $configuration_type = SystemConfigurationsType::find($id);
        
        if(isset($configuration_type->configuration_type) && $configuration_type->id == $request->config_id){
            if($configuration_type->configuration_type == 'Personnel to Recieve Feedback and Complaint Notification'){
                $user = getUserById($request->name);
                if(!isset($user->id)){
                    return redirect()->back()->with('error','The specified user doesnot exist!');
                }else{
                    $new_configuration = new SystemConfiguration();
                    $new_configuration->configuration_type_id = $request->config_id;
                    $new_configuration->value = $user->email;
                    $new_configuration->key = $user->name;
        
                    $new_configuration->save();
        
                }

            }else{

                $new_configuration = new SystemConfiguration();
                $new_configuration->configuration_type_id = $request->config_id;
                $new_configuration->value = $request->value;
                $new_configuration->key = $request->key;
    
                $new_configuration->save();
            }

            return redirect()->back()->with('success','System configuration added successfully!');
        }else{
            return redirect()->back()->with('error','There is no configuration type with specified ID!');
        }
    }

    public function edit(Request $request,$id){
        $configuration = SystemConfiguration::find($id);

        if (!isset($configuration->configuration_type_id)) {
            return redirect()->back()->with('error', 'No configuration with specified ID!');
        }

        // Optional defensive check when config_id is posted from the form.
        if ($request->filled('config_id') && (string) $configuration->id !== (string) $request->config_id) {
            return redirect()->back()->with('error', 'Configuration ID mismatch.');
        }

        $configuration->value = $request->value;
        $configuration->key = $request->key;
        $configuration->save();

        return redirect()->back()->with('success','System configuration edited successfully!');
    }

    public function index(){
        $configuration_types = SystemConfigurationsType::all();
        return view('layouts.configuration.system.configurations',compact('configuration_types'));
    }
    public function delete(Request $request,$id){
        $configuration = SystemConfiguration::find($id);
        if(isset($configuration->configuration_type_id) && $configuration->id == $request->config_id){
            
            $configuration->delete();
            return redirect()->back()->with('success','Configuration deleted successfully!');
        }else{
            return redirect()->back()->with('error','No configuration with specified ID!');
        }
    }

}
