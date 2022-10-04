<?php

namespace App\Http\Controllers;

use App\InventoryCategories;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class InventoryCategoriesController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    $categories = InventoryCategories::orderBy('name')->where('inventory_categories.inventory_location_id', getCurrentUserLocation()->id)->get();

    return view('layouts.inventory.categories.index', compact('categories'));
  }

  public function add(Request $request)
  {
    $category = new InventoryCategories;
    $category->name = $request->name;
    $category->description = $request->description;
    if ($request->hasFile('image')){
      $path = $request->image->path();
      $file = Storage::putFile('categories', new File($path));
      $file = explode('/', $file);

      $fName = '/storage/categories/'.urlencode(end($file));

      $category->image = (String) $fName;
    }

    $category->company_id = getUserCompany();
		$category->inventory_location_id = getCurrentUserLocation()->id;
    $category->save();


    return redirect()->back()->with('success', 'Inventory Category Added.');
  }

  public function edit(Request $request, $id)
  {
    $category = InventoryCategories::find($id);
    $category->name = $request->name;
    $category->description = $request->description;
    if ($request->hasFile('image')){
      $path = $request->image->path();
      $file = Storage::putFile('categories', new File($path));
      $file = explode('/', $file);

      $fName = '/storage/categories/'.urlencode(end($file));

      $category->image = (String) $fName;
    }

    $category->company_id = getUserCompany();
    $category->save();


    return redirect()->back()->with('success', 'Inventory Category Edited.');
	}

	public function show($id){
		$category = InventoryCategories::find($id);
		return view('layouts.inventory.categories.show', compact('category'));
	}
}
