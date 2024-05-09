<?php

namespace App\Http\Controllers;

use App\SupplierCategory;
use App\SupplierByCategory;
use App\InventorySubCategories as SubCat;


use Illuminate\Http\Request;

class SupplierByCategoryController extends Controller
{
	public function __construct()
  {
    $this->middleware('auth');
  }

	public function add(Request $request, $supplier_id){
		$categories = $request->category_id;

		foreach($categories as $c){
			$cat = new SupplierByCategory;
			$cat->supplier_id = $supplier_id;
			$cat->category_id = $c;
			$cat->save();
			$this->fetchCategoryItems($c, $supplier_id, false);
		}

		return redirect()->back()->with('success', 'Category added.');
	}

	public function remove(Request $request, $id){
		$sC = SupplierByCategory::find($id);
		$supplierID = $sC->supplier_id;
		$categoryID = $sC->category_id;

		$sC->delete();

		$this->fetchCategoryItems($categoryID, $supplierID, true);

		return redirect()->back()->with('success', 'Category removed.');
	}

	public function fetchCategoryItems($c, $s, $isDelete = false){
		$subIDs = SubCat::where('inventory_category_id', $c)->get()->pluck('id')->toArray();
		SupplierCategory::where('supplier_id', $s)->whereIn('inventory_sub_category_id', $subIDs)->delete();

		if($isDelete){
			return true;
		}
		else{
			foreach($subIDs as $sub){
				$sc = new SupplierCategory;
				$sc->supplier_id = $s;
				$sc->inventory_sub_category_id = $sub;
				$sc->save();
			}

			return true;
		}
	}
}
