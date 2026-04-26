<?php

namespace App\Models\Workorder;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

  protected $table = 'work_orders';

	public function getSite($typ){
		$obj = explode(" >> ", $this->site);

		if(count($obj) == 0){
			return null;
		}
		else{
			if($typ == "name"){
				return $obj[1] ?? null;
			}
			else{
				return $obj[0] ?? null;
			}
		}
	}

	public function resources($typ){
		$response = WorkOrderResource::where('workorder_id', $this->id)->where('type', $typ)->orderBy('name')->get();
		return $response ?? [];
	}

	public function contacts(){
		$typ = "contacts";
		$response = WorkOrderResource::where('workorder_id', $this->id)->where('type', $typ)->orderBy('name')->get();
		return $response ?? [];
	}

	public function attachments(){
		// $response = \App\EntityAttachment::where('model', 'WorkOrder')->where('model_id', $this->id)->orderBy('id')->get();
		// return $response ?? [];

		return \App\EntityAttachment::join('users as u', 'u.id', '=', 'entity_attachments.created_by')
			->selectRaw('entity_attachments.*, u.id as user_id, u.name as user_name, u.email as user_email')
			->where('model', 'WorkOrder')->where('model_id', $this->id)->get();
	}

	public function edits(){
		$response = WorkorderEdit::where('workorder_id', $this->id)->orderBy('created_at', 'desc')->get();
		return $response ?? [];
	}

	public function status(){
		$data = [];
		$response = WorkOrderStatusHistory::where('workorder_id', $this->id)->orderBy('created_at', 'desc')->get();

		foreach($response as $r){
			$data[$r->status] = $r;
		}

		return $data ?? [];
	}
}
