<?php

namespace App\Http\Controllers;

use App\SampleType;
use App\SampleTypeCategory;

use Illuminate\Http\Request;

class SampleTypeCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(){
        $categories = SampleTypeCategory::orderBy('active','DESC')->orderBy('sample_type_category','ASC')->get();
        return view('layouts.lab.sample-type-category.index',compact('categories'));
    }
    public function addCategory(Request $request){
        $category = SampleTypeCategory::find($request->category_id) ?? new SampleTypeCategory();
        $category->active = $request->active ?? 0;
        $category->sample_type_category = $request->name;
        $category->zoho_id = $request->zoho_id;
        $category->save();
        return redirect()->back()->with('success','Categories updated successfully');
    }
}
