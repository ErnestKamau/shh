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

	public function add(Request $request, $supplier_id)
	{
		$categories = $request->category_id ?? [];

		foreach ($categories as $c) {
			SupplierByCategory::firstOrCreate([
				'supplier_id' => $supplier_id,
				'category_id' => $c,
			]);

			$this->fetchCategoryItems($c, $supplier_id, false);
		}

		return redirect()->back()->with('success', 'Category added.');
	}

	public function remove(Request $request, $id)
	{
		$sC = SupplierByCategory::find($id);

		if (! $sC) {
			return redirect()->back()->with('error', 'Category link was not found.');
		}

		$supplierID = $sC->supplier_id;
		$categoryID = $sC->category_id;

		$sC->delete();

		// Only remove item links if no other row still links this category to the supplier.
		$stillLinked = SupplierByCategory::where('supplier_id', $supplierID)
			->where('category_id', $categoryID)
			->exists();

		if (! $stillLinked) {
			$this->fetchCategoryItems($categoryID, $supplierID, true);
		}

		return redirect()->back()->with('success', 'Category removed.');
	}

	public function fetchCategoryItems($c, $s, $isDelete = false): bool
	{
		$subIDs = SubCat::where('inventory_category_id', $c)->pluck('id')->toArray();

		SupplierCategory::where('supplier_id', $s)
			->whereIn('inventory_sub_category_id', $subIDs)
			->delete();

		if ($isDelete) {
			return true;
		}

		foreach ($subIDs as $sub) {
			$sc = new SupplierCategory;
			$sc->supplier_id = $s;
			$sc->inventory_sub_category_id = $sub;
			$sc->inventory_item_brand_id = null;
			$sc->supplier_image = '/images/no-logo.png';
			$sc->status = true;
			$sc->save();
		}

		return true;
	}

	/**
	 * Ensure supplier_categories rows exist for every linked main category.
	 */
	public function syncSupplierItems(string $supplierId): void
	{
		$categoryIds = SupplierByCategory::where('supplier_id', $supplierId)
			->pluck('category_id')
			->unique()
			->filter()
			->values();

		foreach ($categoryIds as $categoryId) {
			$this->fetchCategoryItems($categoryId, $supplierId, false);
		}
	}
}
