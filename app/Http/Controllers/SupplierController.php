<?php

namespace App\Http\Controllers;

use App\Supplier;
use App\User;
use App\SupplierRFQ;
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
    $supplier->default_currency = $request->default_currency;

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
    $supplier->default_currency = $request->default_currency;

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

		$all_categories = InventoryCategories::orderBy('name', 'asc')->selectRaw('id,name')->get();


		$supplier_categories = \App\SupplierByCategory::join('inventory_categories as ic', 'ic.id', 'supplier_by_categories.category_id')
			->leftJoin('inventory_sub_categories as isc', 'isc.inventory_category_id', 'ic.id')
			->selectRaw('supplier_by_categories.id as row_id, ic.name, count(isc.id) as items')
			->where('supplier_id', $id)
			->groupBy('supplier_by_categories.id', 'ic.name')
			->orderBy('ic.name', 'asc')
			->get();

		// return json_encode($all_categories, JSON_PRETTY_PRINT);

		$criteria = \App\SuppliersRatingCriteria::where('supplier_id', $id)
			->selectRaw('criteria_id, AVG(score) as score')
			->where('is_current', 1)
			->groupBy('criteria_id')
			->get();

		$ratingScores = [];

		foreach($criteria as $c){
			$ratingScores[$c->criteria_id] = $c->score;
		}

		// return json_encode($ratingScores);

		$paymentTerms = getModulePreconfig('Payment-Terms', 'Inventory-Management', 'level');

		return view('layouts.inventory.suppliers.show', compact('paymentTerms','ratingScores', 'supplier', 'cats', 'stores', 'all_categories', 'supplier_categories'));
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

  public function fetch_supplier_items(Request $request, $sID){
    return Supplier::find($sID)->itemIDs();
  }

	public function delete_supplier($id){
		$supplier = Supplier::find($id);

		if(!isset($supplier->id)){
			return redirect()->back()->with('error', 'No supplier was found.');
		}

		$rfqs = SupplierRFQ::where('supplier_id', $id)->get();

		if($rfqs->count() > 0){
			return redirect()->back()->with('error', 'This supplier is already assigned to some RFQs.');
		}

		// return json_encode($supplier);
		$supplier->delete();
		return redirect()->back()->with('success', 'The Supplier has been deleted.');
	}
}
