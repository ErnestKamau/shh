<?php

namespace App\Http\Controllers\Asset;

use App\Models\Assets\AssetLocation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AssetLocationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
        $locations = AssetLocation::all();
        return view('layouts.equipment.asset.asset-location', compact('locations'));
    }
    public function add(Request $request){
        $location = new AssetLocation;

        $location->location_code = $request->code;
        $location->name = $request->description;
        if ($request->active == 'True'){
            $location->is_active = 1;
        }else{
            $location->is_active = 0;
        }

        $location->save();

        return redirect()->back()->with('success','Asset location added successfully');
    }
    public function edit(Request $request,$id){
        $location = AssetLocation::find($id);

        $location->location_code = $request->code;
        $location->name = $request->description;
        if ($request->active == 'True'){
            $location->is_active = 1;
        }else{
            $location->is_active = 0;
        }

        $location->save();

        return redirect()->back()->with('success','Asset location edited successfully');
    }
}
