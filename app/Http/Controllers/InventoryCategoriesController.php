<?php

namespace App\Http\Controllers;

use App\InventoryCategories;
use App\InventorySubCategories;

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
    $category->description = $request->description ?? $request->name;
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
    $category->description = $request->description ?? $request->name;
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
		$category = InventoryCategories::leftJoin('inventory_stores as ins', 'ins.id', 'inventory_categories.default_store_id')
		->leftJoin('inventory_store_slots as iss', 'iss.inventory_store_id', 'ins.id')
		->selectRaw('inventory_categories.*, ins.name as store, iss.name as slot')->where('inventory_categories.id', $id)->first();

		// return response()->json($category, 200);

		return view('layouts.inventory.categories.show', compact('category'));
	}

	public function destroy($id){
		$subs = InventorySubCategories::where('inventory_category_id', $id)->where('active', 1)->count();
		$inactiveSubs = InventorySubCategories::where('inventory_category_id', $id)->where('active', 0)->count();
		if($subs > 0){
			return \redirect()->back()->with('error', 'Category not empty. Please delete or move items to another category.');
		}

		$cat = InventoryCategories::find($id);

		if($inactiveSubs > 0){
			$cat->active = 0;
			$cat->save();
		}
		else{
			$cat->delete();
		}

		return \redirect()->back()->with('success', 'Category not removed successfully.');
	}

	public function set_default_store(Request $request, $id){
		$category = InventoryCategories::find($id);

		$category->default_store_id = $request->store_id;
		$category->default_slot_id = $request->slot_id;
		$category->save();

		// return response()->json($category, 200);

		return \redirect()->back()->with('success', 'Default stores set.');
	}
}
