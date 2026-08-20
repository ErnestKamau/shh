<?php

namespace App\Http\Controllers;

use App\SupplierCategory;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SupplierCategoryController extends Controller
{
	public function __construct()
  {
    $this->middleware('auth');
  }
	public function add(Request $request, $supplier){

		// return response()->json($request->all(), 200);

		foreach($request->brands as $data){
			$data = explode("-", $data);
			$brand_id = $data[1];
			$brand = \App\ItemBrand::find($brand_id);

			$supCat = SupplierCategory::where('inventory_item_brand_id', $brand->id ?? null)
			->where('inventory_sub_category_id', $data[0])->where('supplier_id', $supplier)->first() ?? new SupplierCategory;
			$supCat->supplier_id = $supplier;
			$supCat->inventory_sub_category_id = $data[0];
			$supCat->inventory_item_brand_id = $brand->id ?? null;
			$supCat->supplier_image = $brand->image ?? '/images/no-logo.png';
			$supCat->status = 1;
			$supCat->save();
		}

		$plural = count($request->brands) > 1 ? 'Items' : 'Item';

		return redirect()->back()->with('success', 'Supplier '.$plural.' Added!');
	}

	// public function change_image(Request $request, $id){
	// 	$supCat = SupplierCategory::find($id);

	// 	if ($request->hasFile('image')){
  //     $path = $request->image->path();
  //     $file = Storage::putFile('suppliers-items', new File($path));
  //     $file = explode('/', $file);

  //     $fName = '/storage/suppliers-items/'.urlencode(end($file));

  //     $supCat->supplier_image = (String) $fName;
	// 	}

	// 	$supCat->save();
	// 	return redirect()->back()->with('success', 'Supplier Item Image Updated!');
	// }

	public function destroy($id)
	{
		$supCat = SupplierCategory::find($id);

		$supCat->status = 0;
		$supCat->save();

		return redirect()->back()->with('success', 'Supplier Category Deleted!');
	}

	
}
