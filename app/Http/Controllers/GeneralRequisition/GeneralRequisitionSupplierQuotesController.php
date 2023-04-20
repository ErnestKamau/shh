<?php


namespace App\Http\Controllers\GeneralRequisition;

use App\Supplier;
use App\Http\Controllers\Controller;
use App\EntityAttachment;
use App\Models\GeneralRequisition\GeneralRequistionRequest as GRequest;
use App\Models\GeneralRequisition\GeneralRequisitionSupplierQuotes as Quote;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GeneralRequisitionSupplierQuotesController extends Controller
{
	public function add(Request $request, $id){
		$is_approved = 1;
		if($request->has('new_supplier') && trim($request->new_supplier) != ""){
			$supplier = new Supplier;
			$supplier->name = $request->new_supplier;
			$supplier->is_approved = 0;
			$supplier->company_id = 3;
			$supplier->inventory_location_id = 1;
			$supplier->save();
			$is_approved = 0;
		}
		else{
			$supplier = Supplier::find($request->supplier_id);
		}

		// return \json_encode($request->all());

		foreach($request->quote['request_item_id'] as $i=>$q){
			$quote = new Quote;
			$quote->supplier_id = $supplier->id;
			$quote->request_item_id = $q;
			$quote->request_id = $id;
			$quote->is_approved = $is_approved;
			$quote->amount = $request->quote['amount'][$i];

			$quote->save();
		}

		if($request->hasFile('file')){
			$path = $request->file->path();
			$attachment =  new EntityAttachment;
			$attachment->type = "Supplier Quote";
			$attachment->title = "Supplier Quote for ".$supplier->name;
			$attachment->description = "Supplier Quote for ".$supplier->name;
			$attachment->model = 'General Requisition';
			$attachment->model_id = $id;
			$attachment->created_by = \Auth::user()->id;

			$file = Storage::putFile('general-requisition', new File($path));
			$file = explode('/', $file);

			$fName = '/storage/general-requisition/'.urlencode(end($file));

			$attachment->file = (String) $fName;

			$attachment->save();
		}

		return redirect()->back()->with('success', 'Quote added successfully.');
	}

	public function update(Request $request, $id){
		$quote = Quote::find($id);
		$quote->amount = $request->amount;
		$quote->save();
		

		return redirect()->back()->with('success', 'Quote edited successfully.');
	}

	public function remove(Request $request, $id){
		$quote = Quote::find($id);

		$quote->delete();
		return redirect()->back()->with('success', 'Quote removed successfully.');
	}
}