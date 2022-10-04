<?php

namespace App\Http\Controllers;

use App\SupplierContract;
use App\SupplierContractItem;

use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SupplierContractController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }
	public function modify(Request $request, $supplier, $id=false){

		// return response()->json($request->all(), 200);

		$contract = $id ? SupplierContract::find($id) : new SupplierContract;

		$contract->description = $request->description;
		$contract->supplier_id = $supplier;

		SupplierContractItem::where('contract_id', $contract->id)->delete();

		$contract->start = $request->start;
		$contract->end = $request->end;

		if ($request->hasFile('file')){
			$path = $request->file->path();
			$file = Storage::putFile('suppliers-contracts', new File($path));
			$file = explode('/', $file);

			$fName = '/storage/suppliers-contracts/'.urlencode(end($file));

			$contract->file = (String) $fName;
		}
		else{
			if(!$id){
				return redirect()->back()->with('error', 'Contract file missing');
			}
		}
		$contract->status = $request->status ?? 0;
		$contract->save();

		foreach($request->item as $item){
			$contractItem = new SupplierContractItem;
			$contractItem->item_id = $item;
			$contractItem->contract_id = $contract->id;
			$contractItem->save();
		}

		return redirect()->back()->with('success', 'Supplier '.($id ? 'Updated' : 'Added'));
	}

	public function destroy($id)
	{
		$supCat = SupplierCategory::find($id);

		$supCat->status = 0;
		$supCat->save();

		return redirect()->back()->with('success', 'Supplier Category Deleted!');
	}
}
