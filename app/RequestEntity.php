<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class RequestEntity extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;

	protected $guarded = ['id'];

	public function items($ammendment_id, $grp=false, $grpItems=false)
	{
		$itemCount = RequestEntityItem::where('request_id', $this->id)->where('ammendment', $ammendment_id)->get()->count();

		// $ammendment_id = $this->ammendment;

		if($itemCount == 0 && $this->in_ammendment == 1){
			$ammendment_id = $this->ammendment-1;
		}

		$items = RequestEntityItem::join('inventory_sub_categories as isc', 'isc.id', 'request_entity_items.inventory_sub_category_id')
		->leftJoin('module_pre_configs as mpc', function($join){
			$join->on('mpc.id', '=', 'request_entity_items.currency');
			$join->where('mpc.type', "Currency");
		})
		->where('request_entity_items.request_id', $this->id)->where('request_entity_items.ammendment', $ammendment_id);

		;
		if($grp){
			$items = $items->selectRaw('request_entity_items.*, request_entity_items.catalog_number, isc.zoho_account_id, isc.item_classification, "" as unit_type, isc.unit_price as price, isc.secondary_unit_type, isc.zoho_account_id,
			GROUP_CONCAT(request_entity_items.id) as kit_item_ids, GROUP_CONCAT(CONCAT(IFNULL(isc.name,""), " - ", IFNULL(request_entity_items.quantity, ""), "", IFNULL(request_entity_items.uom, ""), " ",
			IFNULL(request_entity_items.comments, "")))
			as kit_item_name, isc.unit_type, isc.name as item_name, available_stock, isc.code')
				->orderBy('request_entity_items.catalog_number', 'desc')->groupBy('catalog_number');
		}
		else{
			if($grpItems){
				$items = $items->selectRaw('request_entity_items.*, request_entity_items.catalog_number, isc.zoho_account_id, isc.item_classification, isc.unit_type, isc.unit_price as price, isc.secondary_unit_type, isc.name as item_name, available_stock, isc.code, sum(request_entity_items.quantity) as quantity')->orderBy('request_entity_items.catalog_number', 'asc')
				->groupBy('request_entity_items.inventory_sub_category_id')->groupBy('request_entity_items.comments');
			}
			else{
				$items = $items->selectRaw('request_entity_items.*, request_entity_items.catalog_number, isc.zoho_account_id, isc.item_classification, isc.unit_type, isc.unit_price as price, isc.secondary_unit_type, isc.name as item_name, available_stock, isc.code')->orderBy('request_entity_items.catalog_number', 'asc');
			}
		}

		$items = $items->get();

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

	public function quotes($item_id=false, $grp=false){
		if($grp){
			$quotes =  \App\SupplierQuote::join('request_entity_items as rei', 'rei.id', '=', 'supplier_quotes.request_item_id')
			->join('inventory_sub_categories as ics', 'ics.id', '=', 'rei.inventory_sub_category_id')
			->where('supplier_quotes.request_id', $this->id)->selectRaw('supplier_quotes.*, COALESCE(mpc.name, "-1") as currency, rei.catalog_number, GROUP_CONCAT(supplier_quotes.id) as quotes_id, GROUP_CONCAT(CONCAT(IFNULL(ics.name,""), " - ", IFNULL(rei.quantity, ""), "", IFNULL(rei.uom, ""), " ",
			IFNULL(rei.comments, "")))
			as kit_item_name, ics.name as item_name, rei.item_brand_id as brand_id');
		}
		else{
			$quotes =  \App\SupplierQuote::join('request_entity_items as rei', 'rei.id', '=', 'supplier_quotes.request_item_id')
			->join('inventory_sub_categories as ics', 'ics.id', '=', 'rei.inventory_sub_category_id')
			->where('supplier_quotes.request_id', $this->id)->selectRaw('supplier_quotes.*, COALESCE(mpc.name, "-1") as currency, ics.name as item_name, rei.item_brand_id as brand_id');
		}

		$quotes = $quotes->leftJoin('module_pre_configs as mpc', 'mpc.id', 'supplier_quotes.currency_id');

		if($item_id){
			$quotes = $quotes->where('ics.id', $item_id)
				->orderBy('supplier_quotes.quote_amount', 'asc');
		}
		else{
			if($grp){
				$quotes = $quotes->orderBy('kit_item_name', 'asc')->orderBy('supplier_quotes.quote_amount', 'asc');
			}
			else{
				$quotes = $quotes->orderBy('ics.name', 'asc')->orderBy('supplier_quotes.quote_amount', 'asc');
			}
		}
		if($grp){
			$quotes = $quotes->groupBy('rei.catalog_number', 'supplier_quotes.supplier_id');
		}

		$quotes = $quotes->get();

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
			"total" => $this->defined_approvals()
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

	public function currency(){
		return $this->belongsTo(ModulePreConfigs::class, 'currency');
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

	public function request_entity_items(){
		return $this->hasMany(RequestEntityItem::class, 'request_id');
	}

	public function source_request(){
		return $this->belongsTo(RequestEntity::class, 'parent_material_requisition');
	}

	public function getNetValueAttribute(){
		return $this->request_entity_items->sum('net_value');
	}

	public function children(){
		return $this->hasMany(RequestEntity::class, 'parent_request_id');
	}
}
