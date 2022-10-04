<?php

namespace App\Http\Controllers;

use App\Supplier;
use App\User;
use Illuminate\Http\File;
use App\InventoryCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SupplierController extends Controller
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
		// return json_encode(getInventoryItems(), JSON_PRETTY_PRINT);

		$suppliers = Supplier::orderBy('name', 'asc')->where('inventory_location_id', getCurrentUserLocation()->id)->get();
		return view('layouts.inventory.suppliers.index', compact('suppliers'));
	}

	public function add(Request $request)
  {
    $supplier = new Supplier;
    $supplier->name = $request->name;
    if ($request->hasFile('logo')){
      $path = $request->logo->path();
      $file = Storage::putFile('suppliers', new File($path));
      $file = explode('/', $file);

      $fName = '/storage/suppliers/'.urlencode(end($file));

      $supplier->logo = (String) $fName;
		}

		$supplier->inventory_location_id = getCurrentUserLocation()->id;
    $supplier->email = $request->email;
    $supplier->phone = $request->phone;
    $supplier->building = $request->building;
    $supplier->street = $request->street;
    $supplier->town = $request->town;
    $supplier->address = $request->address;
    $supplier->pin_number = $request->pin_number;
    $supplier->vat_number = $request->vat_number;
    $supplier->payment_terms = $request->payment_terms;
    $supplier->payment_method = $request->payment_method;

    $supplier->company_id = getUserCompany();
    $supplier->save();

    // $user = new User();
    // $user->name = $supplier->name;
    // $user->email = $supplier->email;
    // $user->company_id = $supplier->company_id;
    // $user->supplier_id = $supplier->id;
    // $user->password = bcrypt('test1234');
    // $user->save();


    return redirect()->back()->with('success', 'Supplier Added.');
  }

  public function make_suppliers_users(){
    $suppliers = Supplier::all();
    foreach($suppliers as $supplier){
      // $user = new User();
      // $user->name = $supplier->name;
      // $user->email = $supplier->email;
      // $user->company_id = $supplier->company_id;
      // $user->supplier_id = $supplier->id;
      // $user->password = bcrypt('test1234');
      // $user->save();
    }
    return redirect()->back()->with('success','supplier made users successfullfy');
  }

	public function edit(Request $request, $id)
  {
    $supplier = Supplier::find($id);
    $supplier->name = $request->name;
    if ($request->hasFile('logo')){
      $path = $request->logo->path();
      $file = Storage::putFile('suppliers', new File($path));
      $file = explode('/', $file);

      $fName = '/storage/suppliers/'.urlencode(end($file));

      $supplier->logo = (String) $fName;
		}

    $supplier->email = $request->email;
    $supplier->phone = $request->phone;
    $supplier->building = $request->building;
    $supplier->street = $request->street;
    $supplier->town = $request->town;
    $supplier->address = $request->address;
    $supplier->pin_number = $request->pin_number;
    $supplier->vat_number = $request->vat_number;
    $supplier->payment_terms = $request->payment_terms;
    $supplier->payment_method = $request->payment_method;

    $supplier->company_id = getUserCompany();
		$supplier->save();

    return redirect()->back()->with('success', 'Supplier Edited.');
	}

	public function show($id){
		$supplier = Supplier::find($id);
		$stores = \App\InventoryStore::where('inventory_location_id', getCurrentUserLocation()->id)
			->orderBy('name', 'asc')->get();

		$exclude_lab_storage = "is_lab_samples";

		$cats = InventoryCategories::orderBy('name', 'asc')->where('inventory_location_id', getCurrentUserLocation()->id)
			->where('category_type', '!=', $exclude_lab_storage)->get();

		return view('layouts.inventory.suppliers.show', compact('supplier', 'cats', 'stores'));
	}

	public function remove_supplier_from_inventory($id, $itemID){
		\App\SupplierCategory::where('supplier_id', $id)->where('inventory_sub_category_id', $itemID)->delete();

		return redirect()->back()->with('success', 'Supplier removed.');
	}

	public function add_supplier_to_inventory(Request $request, $itemID){
		$sCat = new \App\SupplierCategory;
		$sCat->supplier_id = $request->supplier;
		$sCat->inventory_sub_category_id = $itemID;
		$sCat->save();

		return redirect()->back()->with('success', 'Supplier added.');
	}

	public function get_suppliers_via_ajax(Request $request){
		$term = $request->search;
		$limit = 100;
		$offset = $request->page;
		$items = Supplier::having("text", "LIKE", '%'.$term.'%')
		->selectRaw('id, name as text')->orderBy('name', 'asc')->offset($offset)->paginate($limit)->toArray();

		return response()->json([
			"results"=>$items['data'],
			"pagination"=>["more"=>$items['prev_page_url'] != null]
		], 200);
	}
}
