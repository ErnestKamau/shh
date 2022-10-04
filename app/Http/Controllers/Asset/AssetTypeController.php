<?php

namespace App\Http\Controllers\Asset;

use App\Models\Assets\AssetType;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AssetTypeController extends Controller
{
    public function __construct()
    {
       $this->middleware('auth') ;
    }
    public function index()
    {
        $types = AssetType::all();
        return view('layouts.equipment.asset.index',compact('types'));
    }
    public function add(Request $request){
        $asset = new AssetType;

        $asset->asset_code = $request->code;
        $asset->descripton = $request->description;
        if ($request->active == 'True'){
            $asset->is_active = 1;
        }else{
            $asset->is_active = 0;
        }

        $asset->save();

        return redirect()->back()->with('sucess','Asset Type added successfully!');
    }
    public function edit(Request $request,$id){
        $asset_type = AssetType::find($id);
        $asset_type->asset_code = $request->code;
        $asset_type->descripton = $request->description;
        if ($request->active == 'True'){
            $asset_type->is_active = 1;
        }else{
            $asset_type->is_active = 0;
        }

        $asset_type->save();

        return redirect()->back()->with('sucess','Asset Type edited successfully!');
    }

}
