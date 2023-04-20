<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class RequestEntity extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	public function items($ammendment_id)
	{
		$itemCount = RequestEntityItem::where('request_id', $this->id)->where('ammendment', $ammendment_id)->get()->count();

		// $ammendment_id = $this->ammendment;

		if($itemCount == 0 && $this->in_ammendment == 1){
			$ammendment_id = $this->ammendment-1;
		}

		$items = RequestEntityItem::join('inventory_sub_categories as isc', 'isc.id', 'request_entity_items.inventory_sub_category_id')
		->where('request_entity_items.request_id', $this->id)->where('request_entity_items.ammendment', $ammendment_id)
		->selectRaw('request_entity_items.*, isc.item_classification, isc.unit_type, isc.secondary_unit_type, isc.sap_code, isc.name as item_name, available_stock, isc.code')->get();
		$data = array();

		foreach($items as $i){
			if(!isset($data[$i->action])){
				$data[$i->action] = array();
			}

			$data[$i->action][] = $i;
		}
		return $data;
	}

	public function issued_received(){
		return RequestEntityItem::join('inventory_sub_categories as isc', 'isc.id', '=', 'request_entity_items.inventory_sub_category_id')
			->join('inventory_store_slots as iss', 'iss.id', '=', 'request_entity_items.slot_id')
			->join('inventory_stores as ins', 'ins.id', '=', 'request_entity_items.store_id')
			->join('users as u', 'u.id', '=', 'request_entity_items.issued_by')
			->selectRaw('u.name as issuer, ins.name as store, iss.name as slot, isc.name as item, isc.code, request_entity_items.quantity, request_entity_items.created_at')
			->where('request_id', $this->id)->where('action', 'issued_received')
			->orderBy('item')->get();
	}

	public function creator(){
		return User::find($this->created_by);
	}

	public function supplier_rfqs(){
		return SupplierRFQ::where('request_id', $this->id)->get();
	}

	public function supplier(){
		return Supplier::find($this->supplier_id);
	}

	public function quotes(){
		$quotes =  \App\SupplierQuote::join('request_entity_items as rei', 'rei.id', '=', 'supplier_quotes.request_item_id')
			->join('inventory_sub_categories as ics', 'ics.id', '=', 'rei.inventory_sub_category_id')
			->where('supplier_quotes.request_id', $this->id)->selectRaw('supplier_quotes.*, ics.name as item_name, ics.sap_code, rei.item_brand_id as brand_id')
			->orderBy('ics.inventory_category_id', 'asc')->orderBy('supplier_quotes.quote_amount', 'asc')->get();

		$return = [];

		foreach($quotes as $q){
			$brand = \App\ItemBrand::find($q->brand_id);

			$q->brand = $brand ? $brand->name : 'Non-Specific';

			$return[] = $q;
		}

		return $return;
	}

	public function approvals(){
		return array(
			"done" => $this->done_approvals(),
			"total" => getStageApprovals('Requisition', $this->request_type)
		);
	}

	public function defined_approvals(){
		return EntityApproval::where('model', $this->request_type)
			->where('model', $this->request_type)
			->where('model_id', $this->id)->get();
	}

	public function done_approvals(){
		$completed = ["Approved", "Rejected"];
		return EntityApproval::where('model', $this->request_type)
			->whereIn('status', $completed)
			->where('model_id', $this->id)->get();
	}

	public function pending_approvals(){
		$completed = ["Approved", "Rejected"];
		return EntityApproval::where('model', $this->request_type)
			->whereNotIn('status', $completed)
			->where('model_id', $this->id)->get();
	}

	public function notes(){
		return EntityNote::join('users as u', 'u.id', '=', 'entity_notes.created_by')
			->selectRaw('entity_notes.*, u.id as user_id, u.name as user_name, u.email as user_email')
			->where('model', $this->request_type)->where('model_id', $this->id)->get();
	}

	public function attachments(){
		return EntityAttachment::join('users as u', 'u.id', '=', 'entity_attachments.created_by')
			->selectRaw('entity_attachments.*, u.id as user_id, u.name as user_name, u.email as user_email')
			->where('model', $this->request_type)->where('model_id', $this->id)->get();
	}
}
