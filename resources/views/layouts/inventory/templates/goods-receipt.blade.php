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
<td colspan="2" style="width: 100%; height: 21px; text-align: center;">GOODS RECEIVED NOTE</td>
</tr>
<tr style="height: 40px; margin-bottom: 5px;">
<td style="width: 66.3334%; height: 40px;"><img src="{{ url('/storage/companies/9RUZQlhlqNlYp1icQFROCRLgLTtvqRrTtcXyms2g.png') }}" width="100" height="auto" alt="" /></td>
<td style="width: 33.6666%; height: 40px; text-align: right;">
<p><span>{!! getConfigByName('site_po_box')->count() > 0 ? getConfigByName('site_po_box')[0]->value : 'P.O. BOX 27774 - 0056 Nairobi' !!}</span></p>
</td>
</tr>
<tr style="height: 35px;">
<td style="width: 66.3334%; height: 35px;">
<p>Dept <span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ $REQUESTER->department()->name ?? "" }}</span></p>
</td>
<td style="text-align: right; width: 33.6666%; height: 35px;">
<p>GR NO <span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ $entity->request_code }}</span></p>
</td>
</tr>
<tr style="height: 35px;">
<td style="width: 66.3334%; height: 35px;">Received from <span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ $SUPPLIER->name ?? "" }}</span></td>
<td style="text-align: right; width: 33.6666%; height: 35px;">Date <span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ $entity->created_at ?? "" }}</span></td>
</tr>
</tbody>
</table>
<table border="1" style="border-collapse: collapse; width: 100%; margin-bottom: 10px;">
	<thead>
		<tr>
			<th>STOCK CLASS</th>
			<th>BIN NO.</th>
			<th>LPO NO.</th>
			<th>DESCRIPTION</th>
			<th>PACK UNIT</th>
			<th>NO. OF UNITS</th>
			<th>QUANTITY OR KGS</th>
			<th style="width: 20%; font-size:11px;">ALLOCATION STATE TO GENERAL STORE OR PURPOSE OR WHICH USED</th>
		</tr>
	</thead>
<tbody>
{{-- <pre>{{ json_encode($normalItems, JSON_PRETTY_PRINT) }}</pre> --}}
@foreach ($normalItems as $item)
<?php
	$isKitItem = !is_numeric($item->catalog_number);
?>
<tr style="height: 21px;">
	<td style="padding: 1px 3px">{{ getInventoryItemClassification($item->item_classification) }}</td>
	<td style="padding: 1px 3px"></td>
	<td style="padding: 1px 3px">{{ $PO->request_code ?? '' }}</td>
	<td style="padding: 1px 3px">{{ ($isKitItem ? $item->catalog_number.' - ' : '').$item->item_name }}</td>
	<td style="padding: 1px 3px">{{ $item->unit_type }}</td>
	<td style="text-align:right; padding: 1px 3px">{{ number_format($item->quantity, 3) }}</td>
	<td style="padding: 1px 3px"></td>
	<td style="padding: 1px 3px">{{ trim($entity->cost_center) != "" ? $entity->cost_center : $REQUESTER->department()->name }}</td>
</tr>
@endforeach
</tbody>
</table>
@if(!$isHTML)
<table border="1" style="border-collapse: collapse; width: 100%;">
<tbody>
<tr>
<td style="width: 14.2857%;">CARRIER</td>
<td style="width: 14.2857%;">VEHICLE NO.</td>
<td style="width: 14.2857%;">DELIVERY NOTE NUMBER</td>
<td style="width: 14.2857%;">SUPPLIERS INVOICE NUMBER</td>
<td style="width: 14.2857%;">MOISTURE CONTENTS</td>
<td style="width: 14.2857%;">REMARKS</td>
<td style="width: 14.2857%;">RECEIVED &amp; EXAMINED BY</td>
</tr>
<tr>
<td style="width: 14.2857%;">{{ $entity->note_bearer ?? '' }}</td>
<td style="width: 14.2857%;">{{ $entity->vehicle_no ?? '' }}</td>
<td style="width: 14.2857%;">{{ $entity->delivery_note_number ?? '' }}</td>
<td style="width: 14.2857%;">{{ $entity->supplier_invoice_number ?? '' }}</td>
<td style="width: 14.2857%;">{{ $entity->moisture_contents ?? '' }}</td>
<td style="width: 14.2857%;">{{ $entity->remarks ?? '' }}</td>
<td style="width: 14.2857%; text-align: center;">
<br><br>
<?php
	$approvers = \App\User::join('entity_approvals as ea', 'ea.user_id', 'users.id')->where('ea.status', 'Approved')
		->where('model', $entity->request_type)->where('model_id', $entity->id)->get()->toArray();

	$startKeySet = 0;
?>
<span>SIGNATURE</span>
<br>
@if(isset($approvers[$startKeySet]) && isset($approvers[$startKeySet]['name']))
	<span style="padding: 5px 10px; width: 100%">{!! trim($approvers[$startKeySet]['electronic_sig']) != "" ?
		'<small>'.$approvers[$startKeySet]['name'].'</small><br> <img src="'.$approvers[$startKeySet]['electronic_sig'].'" style="margin-left: 10px; height: 25px" />' : $approvers[$startKeySet]['name'] !!}</span>
	</p>
@endif
<br>
<span>LOCATON</span>
</td>
</tr>
</tbody>
</table>
<table border="0" style="border-collapse: collapse; width: 100%;">
	<tbody>
		<tr>
			<td style="width: 100%; text-align: right;">
				<br><br>
				<p>_________________________</p>
				<p>SIGNATURE OF CARRIER&nbsp; &nbsp;&nbsp;</p>
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