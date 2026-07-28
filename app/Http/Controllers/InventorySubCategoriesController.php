<?php

namespace App\Http\Controllers;

use Auth;
use App\InventoryCategories;
use App\InventorySubCategories;
use App\Supplier;
use App\SupplierByCategory;
use App\SupplierCategory;
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
	$subcategory = InventorySubCategories::find($id);

    $companyID = Auth::user()->company_id;

	if($category == "fetch-category"){
		return redirect()->route('show-inventory-items', ['category'=>$subcategory->inventory_category_id, 'id'=>$id]);
	}

    $category = InventoryCategories::find($category);

	$stores = \App\InventoryStore::where('inventory_location_id', getCurrentUserLocation()->id)->orderBy('name', 'asc')->get();

    $departments = InventoryDepartment::where('company_id', $companyID)->get();
		$suppliers = Supplier::where('company_id', $companyID)->where('inventory_location_id', getCurrentUserLocation()->id)->orderBy('name', 'asc')->get();

    // return response()->json($subcategories, 200, []);

		$categories = InventoryCategories::orderBy('name', 'asc')->select('name', 'id')->get();

    return view('layouts.inventory.categories.items', compact('categories', 'category','subcategory', 'suppliers', 'departments', 'stores'));
  }

  public function add(Request $request, $internal = false)
  {

		$category = InventoryCategories::find($request->category_id);

		$pref = "IM";

    $subcategory = new InventorySubCategories;
    $subcategory->code = getNamingConventionCode("SubCategories", false, $pref);
    $subcategory->name = $request->name;
    $subcategory->active = 1;
    $subcategory->description = $request->description ?? 'n/a';
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

		if(floatval($request->unit_price) == 0 && $request->item_classification!=3 && !$internal){
			// return 7;
			return redirect()->back()->with('error', 'Item price can not be 0 for Non-Service items.');
		}

		$materialTypeId = $request->material_type_id;
		$subcategory->material_type_id = (! empty($materialTypeId) && $materialTypeId !== '0' && \Illuminate\Support\Str::isUuid((string) $materialTypeId))
			? $materialTypeId
			: null;

		$subcategory->parent = $request->parent ?? null;
		$subcategory->parent_id = $request->parent_id ?? null;

    $subcategory->inventory_category_id = $request->category_id;
    $subcategory->manufacturer = $request->manufacturer ?? 'Any';
    $subcategory->maximum_order_quantity = $request->maximum_order_quantity ?? 0;
    $subcategory->item_classification = $request->item_classification;
    $subcategory->unit_type = $request->unit_type;
    $subcategory->secondary_unit_type = $request->secondary_unit_type;
    $subcategory->unit_price = $request->unit_price;
    $subcategory->unit_price_credit = $request->unit_price_credit;
    $subcategory->company_id = getUserCompany();
    $subcategory->location_id = getCurrentUserLocation()->id;
		$subcategory->annual_consumption = $request->annual_consumption;
		$subcategory->working_days = $request->working_days ?? 90;
		$subcategory->estimated_variation_in_demand_average_consumption = $request->estimated_variation_in_demand_average_consumption;
		$subcategory->internal_lead_time = $request->internal_lead_time ?? 0;
		$subcategory->external_lead_time = $request->external_lead_time ?? 0;
		$subcategory->sap_code = $request->sap_code;

    $subcategory->save();

		$this->update_item_to_supplier_category($subcategory->id, $category->id);

		calculateAvailableStock($subcategory->id);
    setItemReorderLevel($subcategory->id, $subcategory->reorder_level());

		if($internal){
			return $subcategory;
		}

    return redirect()->back()->with('success', 'Inventory Sub-Category Added.');
  }

	public function update_item_to_supplier_category($subCatID, $catID){
		if (! \Illuminate\Support\Str::isUuid((string) $catID) || ! \Illuminate\Support\Str::isUuid((string) $subCatID)) {
			return true;
		}

		$suppliers = SupplierByCategory::where('category_id', $catID)->pluck('supplier_id')->toArray();
		$suppliers = array_unique($suppliers);

		// return json_encode($suppliers);

		foreach($suppliers as $s){
			$supCat = new SupplierCategory;
			$supCat->supplier_id = $s;
			$supCat->inventory_sub_category_id = $subCatID;
			$supCat->inventory_item_brand_id = 0;
			$supCat->supplier_image = '/images/no-logo.png';
			$supCat->status = 1;
			$supCat->save();
		}

		return true;
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

		$materialTypeId = $request->material_type_id;
		$subcategory->material_type_id = (! empty($materialTypeId) && $materialTypeId !== '0' && \Illuminate\Support\Str::isUuid((string) $materialTypeId))
			? $materialTypeId
			: null;

		if(floatval($request->unit_price) == 0 && $request->item_classification!=3){
			return redirect()->back()->with('error', 'Item price can not be 0 for Non-Service items.');
		}

		$subcategory->inventory_category_id = $request->category_id;
    $subcategory->manufacturer = $request->manufacturer ?? 'Any';
    $subcategory->maximum_order_quantity = $request->maximum_order_quantity;
    $subcategory->item_classification = $request->item_classification;
    $subcategory->unit_type = $request->unit_type;
    $subcategory->secondary_unit_type = $request->secondary_unit_type;
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
    return redirect()->route('show-inventory-items', ['category'=>$request->category_id, 'id'=>$id])->with('success', 'Inventory Sub-Category Edited.');
	}

	public function get_items_via_ajax(Request $request, $cat_id=false, $name=false){
		$term = $request->search;
		$limit = 100;
		$offset = $request->page;
		$gate_pass_category = getConfigByName('gate_pass_category_id');
		$gate_pass_category_id = count($gate_pass_category) > 0 ? $gate_pass_category[0]->value : 0;
		$items = InventorySubCategories::where('inventory_category_id', '!=', $gate_pass_category_id)->where('active', 1)->having("text", "LIKE", '%'.$term.'%');
		if($cat_id){
			if($name){
				$cat_id = InventoryCategories::where('name', $cat_id)->first()->id;
			}
			$items = $items->where('inventory_category_id', $cat_id);
		}
		$items = $items->selectRaw('id, CONCAT(code, "-", name) as `text`')->orderBy('name', 'asc')->offset($offset)->paginate($limit)->toArray();

		return response()->json([
			"results"=>$items['data'],
			"pagination"=>["more"=>$items['prev_page_url'] != null]
		], 200);
	}

	public function get_item_details($inv_sub_cat, $req_id=false){
		$item = InventorySubCategories::find($inv_sub_cat);
		$availableStock = $req_id ? $item->availableByStoreSlot($req_id) : $item->available_stock;
		return [
			"text"=>$item->name,
			"unit_val"=>$item->unit_price,
			"uom"=>$item->unit_type,
			"maximum_order_quantity"=>$item->max_standard_inventory()-$availableStock,
			"reorder_level"=>$item->reorder_level(),
			"uom2"=>$item->secondary_unit_type,
			"available_formated"=>number_format($availableStock, 2),
			"available"=>$availableStock,
			"brands"=> \App\ItemBrand::where('inventory_sub_category_id', $inv_sub_cat)->selectRaw('id, name')->orderBy('name')->get(),
			"account_id" => $item->zoho_account_id
		];
	}

	public function destroy($id){
		$item = InventorySubCategories::find($id);
		$item->active = 0;
		$item->save();

		return redirect()->back()->with('success', 'Inventory Item was removed.');
	}
}
