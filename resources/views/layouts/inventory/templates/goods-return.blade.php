<?php
	$PO = \App\RequestEntity::find($entity->parent_request_id);
	$REQUESTER = \App\User::find($entity->request_initiator);
	$SUPPLIER = \App\Supplier::find($entity->supplier_id);
	$allItems = $entity->items($entity->ammendment) ?? array();

	$normalItems = empty($allItems) ? array() : ($allItems['normal'] ?? array());

?>
<style type="text/css">
	@media print{
		button{
				display:none;
		}
	}
</style>
@if(!$isHTML)
<div style="padding: 10px 15px">
	<button style="padding: 5px 10px; font-size: 13px" onclick="window.print()">Print</button>
</div>
@endif
<table border="0" style="border-collapse: collapse; width: 100%; height: 84px; margin-bottom: 10px;">
	<tbody>
	<tr style="height: 21px;">
	<td style="width: 66.3334%; height: 21px;"><img src="{{ url('/storage/companies/9RUZQlhlqNlYp1icQFROCRLgLTtvqRrTtcXyms2g.png') }}" width="100" height="auto" alt="" /></td>
	<td style="width: 33.6666%; height: 21px; text-align: right;">
	<p>{!! getConfigByName('site_po_box')->count() > 0 ? getConfigByName('site_po_box')[0]->value : 'P.O. BOX 27774 - 0056 Nairobi' !!}</p>
	</td>
	</tr>
	<tr style="height: 35px;">
	<td colspan="2" style="width: 100%; height: 35px; text-align: center;">GOODS RETURN NOTE</td>
	</tr>
	<tr style="height: 35px;">
	<td colspan="2" style="width: 100%; height: 35px;">
	<p>To: <span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ $SUPPLIER->name }}</span></p>
	</td>
	</tr>
	<tr style="height: 35px;">
	<td colspan="2" style="width: 100%; height: 35px;">
	<p><span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ $SUPPLIER->address ?? "" }}</span></p>
	</td>
	</tr>
	<tr style="height: 35px;">
	<td style="width: 100%; height: 35px;">Date <span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ $entity->created_at ?? "" }}</span></td>
	<td style="width: 100%; height: 35px;">Time Out <span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ $entity->time_out ?? "" }}</span></td>
	</tr>
	<tr style="height: 35px;">
	<td style="width: 100%; height: 35px;">Goods Return Note No. <b style="border-bottom: 1px solid #000; padding: 0px 15px">{{ $entity->request_code ?? "" }}</b></td>
	<td style="width: 100%; height: 35px;">Vehicle No. <span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ $entity->vehicle_no ?? "" }}</span></td>
	</tr>
	<tr style="height: 35px;">
	<td colspan="2" style="width: 100%; height: 35px;">
	<p>Please receive the following goods/tools _______________________________________________________________</p>
	</td>
	</tr>
	<tr style="height: 35px;">
	<td colspan="2" style="width: 100%; height: 35px;">
	<p>For the purpose of {{ $entity->description }}</p>
	</td>
	</tr>
	</tbody>
	</table>
	<table border="1" style="border-collapse: collapse; width: 100%; margin-bottom: 10px;">
	<thead>
	<tr style="height: 21px;">
	<th style="width: 21.6667%; height: 21px; text-align: center;">Quantity</th>
	<th style="width: 68.3333%; height: 21px; text-align: center;">Description</th>
	</tr>
	</thead>
	<tbody>
		@foreach ($normalItems as $item)
			<tr style="height: 21px;">
			<td style="width: 21.6667%; height: 21px; text-align:center">{{ number_format($item->quantity ?? 0, 3) }}{{ $item->unit_type }}</td>
			<td style="width: 68.3333%; height: 21px; padding-left: 12px;">{{ $item->item_name ?? "" }}</td>
			</tr>
		@endforeach
	</tbody>
	</table>
	@if(!$isHTML)
	<table border="0" style="border-collapse: collapse; width: 100%;">
	<tbody>
	<tr>
	<td colspan="2" style="width: 100%;">
	<p><u><b>Checked by:</b></u></p>
	</td>
	</tr>
	<?php
		$approvers = \App\User::join('entity_approvals as ea', 'ea.user_id', 'users.id')->where('ea.status', 'Approved')
		->where('model', $entity->request_type)->where('model_id', $entity->id)->get()->toArray();
	?>
	<tr>
	<td style="width: 60%;">
	<p></p>
	@if(count($approvers) >= 1)
	<p>Stores Manager: {!! isset($approvers[0]) ? '<span style="border-bottom: 1px solid #000; padding: 5px 10px; width: 100%">'.$approvers[0]['name'].'</span>'
		: '_______________________' !!}</p>
	@endif

	</td>
	<td style="width: 40%;">
	<p></p>
	@if(count($approvers) >= 1)
	<p>Signature: <span style="border-bottom: 1px solid #000; padding: 5px 10px; width: 100%">{!! trim($approvers[0]['electronic_sig']) != "" ?
		' <img src="'.$approvers[0]['electronic_sig'].'" style="height: 25px" />' : '' !!}</span></p>
	@endif

	</td>
	</tr>
	<tr>
	<td colspan="2" style="width: 100%;">
	<p><u><b>Authorised by Manager:</b></u></p>
	</td>
	</tr>
	<tr>
	<td style="width: 60%;">
	<p></p>
	@if(count($approvers) >= 2)
	<p>Stores Manager: {!! isset($approvers[1]) ? '<span style="border-bottom: 1px solid #000; padding: 5px 10px; width: 100%">'.$approvers[1]['name'].'</span>'
		: '_______________________' !!}</p>
	@endif

	</td>
	<td style="width: 40%;">
	<p></p>
	@if(count($approvers) >= 2)
	<p>Signature: <span style="border-bottom: 1px solid #000; padding: 5px 10px; width: 100%">{!! trim($approvers[1]['electronic_sig']) != "" ?
		' <img src="'.$approvers[1]['electronic_sig'].'" style="height: 25px" />' : '_________________' !!}</span></p>
	@endif

	</td>
	</tr>
	</tbody>
	</table>
	@endif
	<div style="text-align:center; padding: 15px; font-size: 12px">
		Nature of Purchase : {{ $entity->nature_of_purchase }}
		@if($entity->nature_of_purchase == "Capex")
			- <small>{{ $entity->capex_project_number }}</small>
		@endif
	</div>