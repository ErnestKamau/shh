<?php

namespace App\Models\GeneralRequisition;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use App\EntityAttachment as Attachment;

class GeneralRequistionRequest extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

	public function items(){
		return $this->hasMany(GeneralRequisitionRequestItem::class, 'request_id');
	}

	public function quotes(){
		return GeneralRequisitionSupplierQuotes::join('general_requisition_request_items as grri', 'grri.id', 'general_requisition_supplier_quotes.request_item_id')
			->join('suppliers as s', 's.id', 'general_requisition_supplier_quotes.supplier_id')->selectRaw('general_requisition_supplier_quotes.id, general_requisition_supplier_quotes.created_at, 
			general_requisition_supplier_quotes.amount, grri.item_description,
			s.name as supplier, s.is_approved, s.id as supplier_id, grri.item_id, grri.qty, IF(s.id = grri.supplier_id, "YES", "NO") as awarded ')
			->where('general_requisition_supplier_quotes.request_id', $this->id)->orderBy('grri.item_description', 'asc')
			->orderBy('general_requisition_supplier_quotes.amount', 'asc')->get();
	}

	public function documents(){
		return $this->hasMany(Attachment::class, 'model_id')->where('model', 'General Requisition');
	}

	public function requester(){
		return $this->belongsTo(\App\User::class, 'created_by');
	}
}