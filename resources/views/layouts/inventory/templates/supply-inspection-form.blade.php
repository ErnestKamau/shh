<?php
	$PO = \App\RequestEntity::find($entity->parent_request_id);
	$REQUESTER = \App\User::find($entity->request_initiator);
	$SUPPLIER = \App\Supplier::find($entity->supplier_id);
	$allItems = $entity->items($entity->ammendment) ?? array();

	$normalItems = empty($allItems) ? array() : ($allItems['normal'] ?? array());
?>
<style>
	table td{
		padding: 10px 5px;
	}
	html{
		font-family: tahoma;
	}
</style>
<table style="border-collapse: collapse; width: 100%;" border="0">
	<tbody>
	<tr style="height: 18px;">
	<td style="width: 50%; height: 18px;"><img src="https://aqualytic.imaralims.com/storage/companies/EixLy7nPubNy0uaGNFGlF4Ot9nzVHXpcDnMsEc0u.jpg" alt="Aqualytic Logo" width="270" height="71" /></td>
	<td style="width: 50%; height: 18px; text-align: right;">
	<p>AQUALYTIC LABORATORIES LIMITED<br />P.O. Box 4600 - 00506 Nairobi<br />Ramco Court<br />Off Mombasa Road<br />Email: <span style="color: #000000;">lab@aqualyticlab.com<br /></span>Website: www.aqualyticlab.com<br />Mobile: 0722547344<br /><br /><br /></p>
	</td>
	</tr>
	</tbody>
	</table>
	<hr style="border-color: #000">
	<br>
	<table style="border-collapse: collapse; width: 100%; border="1">
		<tbody>
			<tr style="height: 36px;">
				<td><h4>AQL-LMS-F-660-007 PRODUCT INSPECTION FORM</h4></td>
				<td><h4>{{ $entity->request_code }}</h4></td>
			</tr>
		</tbody>
	</table>
	<table style="border-collapse: collapse; width: 100%;" border="1">
	<tbody>
	<tr style="height: 36px;">
	<td colspan="6"><strong>LABORATORY PRODUCT INFORMATION</strong></td>
	</tr>
	<tr style="height: 18px;">
	<td style="width: 30.06%; height: 18px;"><strong>Description</strong></td>
	<td style="height: 18px;"><strong>Product No</strong></td>
	<td style="height: 18px;"><strong>Lot/Batch/Serial No</strong></td>
	<td style="height: 18px;"><strong>Exp Date</strong></td>
	<td style="height: 18px;"><strong>Test Purpose</strong></td>
	<td style="height: 18px;"><strong>Remarks</strong></td>
	</tr>
	@foreach ($normalItems as $item)
		<tr style="height: 18px;">
			<td style="width: 30.06%; height: 18px;">{{ $item->item_name }}</td>
			<td style="height: 18px;">{{ $item->code }}</td>
			<td style="height: 18px;">{{ $item->lot_no }}</td>
			<td style="height: 18px;">{{ number_format($item->quantity) }}{{ $item->unit_type }}</td>
			<td style="height: 18px;">{{ $item->gr_expiry }}</td>
			<td style="height: 18px;">&nbsp;</td>
		</tr>
	@endforeach
	</tbody>
	</table>
	{{-- <tr colspan="8" style="border: none"><td colspan="8" style="height:18px; border: none"></td></tr> --}}
	<table style="border-collapse: collapse; width: 100%; border-top:none" border="1">
	<tbody>
	<tr style="border-top: none"><td colspan="6">Approving Process</td></tr>
	<tr>
		<td style="width: 1%"></td>
		<td colspan="2">Name</td>
		<td>Position</td>
		<td>Date</td>
		<td>Sign</td>
	</tr>
	<tr>
		<td nowrap>Requested By:</td>
		<td colspan="2"></td>
		<td></td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td nowrap>Checked By:</td>
		<td colspan="2"></td>
		<td></td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td nowrap>Approved By:</td>
		<td colspan="2"></td>
		<td></td>
		<td></td>
		<td></td>
	</tr>
	</tbody>
	</table>
	<br><br>
	<span>REVISION [01] ISSUE DATE: 27.11.2018 | Authorized by: GM | Approved by: TM</span><br>
	<strong>AQL-LMS-F-660-007 PRODUCT INSPECTION FORM</strong>

	<script>
		window.onload = function(){
			window.print();
		}
	</script>