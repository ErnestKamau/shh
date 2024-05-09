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
<table style="border-collapse: collapse; width: 100%; height: 18px;" border="0">
	<tbody>
	<tr style="height: 18px;">
	<td style="width: 50%; height: 18px;"><img src="{{ url('/storage/companies/9RUZQlhlqNlYp1icQFROCRLgLTtvqRrTtcXyms2g.png') }}" alt="Aqualytic Logo" width="270" height="71" /></td>
	<td style="width: 50%; height: 18px; text-align: right;">
	<p>AQUALYTIC LABORATORIES LIMITED<br />P.O. Box 4600 - 00506 Nairobi<br />Ramco Court<br />Off Mombasa Road<br />Email: <span style="color: #000000;">lab@aqualyticlab.com<br /></span>Website: www.aqualyticlab.com<br />Mobile: 0722547344<br /><br /><br /></p>
	</td>
	</tr>
	</tbody>
	</table>
	<hr style="border-color: #000">
	<h4 style="text-align: center;"><span style="text-decoration: underline;">{{ $entity->request_code }} LABORATORY SUPPLY INSPECTION FORM</span></h4>
	<table style="border-collapse: collapse; width: 100%; height: 144px;" border="1">
	<tbody>
	<tr style="height: 36px;">
	<td style="text-align: center; height: 36px; width: 99.9999%;" colspan="8"><strong><span style="text-decoration: underline;">LABORATORY CHEMICALS AND REAGENTS FORM<br /></span></strong></td>
	</tr>
	<tr style="height: 18px;">
	<td style="width: 3.5%; height: 18px;"><strong>No</strong></td>
	<td style="width: 12.06%; height: 18px;"><strong>Description</strong></td>
	<td style="width: 12.06%; height: 18px;"><strong>Product No</strong></td>
	<td style="width: 12.06%; height: 18px;"><strong>Lot/Batch No</strong></td>
	<td style="width: 12.06%; height: 18px;"><strong>Qty</strong></td>
	<td style="width: 12.06%; height: 18px;"><strong>Exp Date</strong></td>
	<td style="width: 12.06%; height: 18px;"><strong>Test Purpose</strong></td>
	<td style="width: 12.06%; height: 18px;"><strong>Remarks</strong></td>
	</tr>
	@foreach ($normalItems as $item)
		<tr style="height: 18px;">
			<td style="width: 3.5%; height: 18px;">{{ $loop->iteration }}</td>
			<td style="width: 12.06%; height: 18px;">{{ $item->item_name }}</td>
			<td style="width: 12.06%; height: 18px;">{{ $item->code }}</td>
			<td style="width: 12.06%; height: 18px;">{{ $item->lot_no }}</td>
			<td style="width: 12.06%; height: 18px;">{{ number_format($item->quantity) }}{{ $item->unit_type }}</td>
			<td style="width: 12.06%; height: 18px;">{{ $item->gr_expiry }}</td>
			<td style="width: 12.06%; height: 18px;">&nbsp;</td>
			<td style="width: 12.06%; height: 18px;">&nbsp;</td>
		</tr>
	@endforeach
	{{-- <tr colspan="8" style="border: none"><td colspan="8" style="height:18px; border: none"></td></tr> --}}
	<tr colspan="8" style="border: none"><td colspan="8" style="height:18px; border: none"></td></tr>
	<tr style="height: 18px;">
	<td style="width: 42.8571%; height: 18px;" colspan="4"><strong>REQUESTED BY</strong></td>
	<td style="width: 57.1428%; height: 18px;" colspan="4"><strong>APPROVED BY</strong></td>
	</tr>
	<tr style="height: 18px;">
	<td style="width: 42.8571%; height: 18px;" colspan="4">Name</td>
	<td style="width: 57.1428%; height: 18px;" colspan="4">Name</td>
	</tr>
	<tr style="height: 18px;">
	<td style="width: 42.8571%; height: 18px;" colspan="4">Position</td>
	<td style="width: 57.1428%; height: 18px;" colspan="4">Position</td>
	</tr>
	<tr style="height: 18px;">
	<td style="width: 42.8571%; height: 18px;" colspan="4">Date</td>
	<td style="width: 57.1428%; height: 18px;" colspan="4">Date</td>
	</tr>
	</tbody>
	</table>
	<b>{{ $entity->request_code }}</b>