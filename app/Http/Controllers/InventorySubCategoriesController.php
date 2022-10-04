<?php

namespace App\Http\Controllers;

use Auth;
use App\InventoryCategories;
use App\InventorySubCategories;
use App\Supplier;
use App\InventoryDepartment;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InventorySubCategoriesController extends Controller
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
  public function index(Request $request, $category, $id)
  {
    $companyID = Auth::user()->company_id;

    $category = InventoryCategories::find($category);
		$subcategory = InventorySubCategories::find($id);

		$stores = \App\InventoryStore::where('inventory_location_id', getCurrentUserLocation()->id)
			->orderBy('name', 'asc')->get();

    $departments = InventoryDepartment::where('company_id', $companyID)->get();
		$suppliers = Supplier::where('company_id', $companyID)->where('inventory_location_id', getCurrentUserLocation()->id)->orderBy('name', 'asc')->get();

    // return response()->json($subcategories, 200, []);

    return view('layouts.inventory.categories.items', compact('category','subcategory', 'suppliers', 'departments', 'stores'));
  }

  public function add(Request $request, $internal = false)
  {

		$category = InventoryCategories::find($request->category_id);

		$pref = strtoupper(substr($category->name, 0, 1).substr($request->name, 0, 1));

    $subcategory = new InventorySubCategories;
    $subcategory->code = getNamingConventionCode("SubCategories", false, $pref);
    $subcategory->name = $request->name;
    $subcategory->description = $request->description;
    if ($request->hasFile('image')){
      $path = $request->image->path();
      $file = Storage::putFile('subcategory', new File($path));
      $file = explode('/', $file);

      $fName = '/storage/subcategory/'.urlencode(end($file));

      $subcategory->image = (String) $fName;
		}
		else{
			if($internal){
				$subcategory->image = '/images/lab-item.png';
			}
		}

		// if(floatval($request->unit_price) == 0 && $request->item_classification!=3){
		// 	return redirect()->back()->with('error', 'Item price can not be 0 for Non-Service items.');
		// }

		$subcategory->material_type_id = $request->material_type_id ?? 0;

		$subcategory->parent = $request->parent ?? null;
		$subcategory->parent_id = $request->parent_id ?? null;

	$subcategory->inventory_category_id = $request->category_id;
	$subcategory->sub_category_id = $request->sub_category_id;
    $subcategory->manufacturer = $request->manufacturer;
    $subcategory->maximum_order_quantity = $request->maximum_order_quantity;
    $subcategory->item_classification = $request->item_classification;
    $subcategory->unit_type = $request->unit_type;
    $subcategory->secondary_unit_type = $request->secondary_unit_type;
    $subcategory->unit_price = $request->unit_price;
    $subcategory->unit_price_credit = $request->unit_price_credit;
    $subcategory->company_id = getUserCompany();
    $subcategory->location_id = getCurrentUserLocation()->id;
		$subcategory->annual_consumption = $request->annual_consumption;
		$subcategory->working_days = $request->working_days;
		$subcategory->estimated_variation_in_demand_average_consumption = $request->estimated_variation_in_demand_average_consumption;
		$subcategory->internal_lead_time = $request->internal_lead_time;
		$subcategory->external_lead_time = $request->external_lead_time;
		$subcategory->sap_code = $request->sap_code;

    $subcategory->save();
		calculateAvailableStock($subcategory->id);
    setItemReorderLevel($subcategory->id, $subcategory->reorder_level());

		if($internal){
			return $subcategory;
		}

    return redirect()->back()->with('success', 'Inventory Sub-Category Added.');
  }

  public function edit(Request $request, $id)
  {
		// return response()->json($request->all(), 200);
	
    $subcategory = InventorySubCategories::find($id);
    $subcategory->name = $request->name;
    $subcategory->description = $request->description;
    if ($request->hasFile('image')){
      $path = $request->image->path();
      $file = Storage::putFile('subcategory', new File($path));
      $file = explode('/', $file);

      $fName = '/storage/subcategory/'.urlencode(end($file));

      $subcategory->image = (String) $fName;
    }

		$subcategory->material_type_id = $request->material_type_id ?? 0;

		// if(floatval($request->unit_price) == 0 && $request->item_classification!=3){
		// 	return redirect()->back()->with('error', 'Item price can not be 0 for Non-Service items.');
		// }

		$subcategory->inventory_category_id = $request->category_id;
    $subcategory->manufacturer = $request->manufacturer;
    $subcategory->maximum_order_quantity = $request->maximum_order_quantity;
    $subcategory->item_classification = $request->item_classification;
    $subcategory->unit_type = $request->unit_type;
	$subcategory->secondary_unit_type = $request->secondary_unit_type;
	$subcategory->sub_category_id = $request->sub_category_id;
    $subcategory->unit_price = $request->unit_price;
    $subcategory->unit_price_credit = $request->unit_price_credit;
		$subcategory->annual_consumption = $request->annual_consumption;
		$subcategory->working_days = $request->working_days;
		$subcategory->estimated_variation_in_demand_average_consumption = $request->estimated_variation_in_demand_average_consumption;
		$subcategory->internal_lead_time = $request->internal_lead_time;
		$subcategory->external_lead_time = $request->external_lead_time;
		$subcategory->sap_code = $request->sap_code;
		$subcategory->save();
		calculateAvailableStock($subcategory->id);
    setItemReorderLevel($subcategory->id, $subcategory->reorder_level());
    return redirect()->back()->with('success', 'Inventory Sub-Category Edited.');
	}

	public function get_items_via_ajax(Request $request){
		$term = $request->search;
		$limit = 100;
		$offset = $request->page;
		$items = InventorySubCategories::having("text", "LIKE", '%'.$term.'%')
		->selectRaw('id, CONCAT(code, "-", name) as `text`')->orderBy('name', 'asc')->offset($offset)->paginate($limit)->toArray();

		return response()->json([
			"results"=>$items['data'],
			"pagination"=>["more"=>$items['prev_page_url'] != null]
		], 200);
	}

	public function get_item_details($inv_sub_cat){
		$item = InventorySubCategories::find($inv_sub_cat);
		return [
			"text"=>$item->name,
			"unit_val"=>$item->unit_price,
			"uom"=>$item->unit_type,
			"uom2"=>$item->secondary_unit_type,
			"available_formated"=>number_format($item->available_stock, 2),
			"available"=>$item->available_stock,
			"brands"=> \App\ItemBrand::where('inventory_sub_category_id', $inv_sub_cat)->selectRaw('id, name')->orderBy('name')->get()
		];
	}
}
