<?php
	$PO = \App\RequestEntity::find($entity->parent_request_id);
	$REQUESTER = \App\User::find($entity->request_initiator);
	$SUPPLIER = \App\Supplier::find($entity->supplier_id);
	$allItems = $entity->items($entity->ammendment) ?? array();

	$normalItems = empty($allItems) ? array() : ($allItems['normal'] ?? array());
?>
<table border="0" style="border-collapse: collapse; width: 100%; height: 84px; margin-bottom: 10px;">
<tbody>
<tr style="height: 21px;">
<td colspan="2" style="width: 100%; height: 21px; text-align: center;">GOODS RECEIVED NOTE</td>
</tr>
<tr style="height: 40px; margin-bottom: 5px;">
<td style="width: 66.3334%; height: 40px;"><img src="https://aqualytic.imaralims.com/storage/companies/EixLy7nPubNy0uaGNFGlF4Ot9nzVHXpcDnMsEc0u.jpg" width="100" height="auto" alt="" /></td>
<td style="width: 33.6666%; height: 40px; text-align: right;">
<p><span>P.O. Box 2777 - 0056</span><br /><span>Nairobi - Kenya</span><br /><span>Tel: 254-060-02030270/81</span><br /><span>Fax: 254-060-02030279</span><span></span></p>
</td>
</tr>
<tr style="height: 35px;">
<td style="width: 66.3334%; height: 35px;">
<p>Dept <span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ $REQUESTER->department()->name ?? "" }}</span></p>
</td>
<td style="text-align: right; width: 33.6666%; height: 35px;">
<p>GRN NO <span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ $entity->request_code }}</span></p>
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
<tr style="height: 21px;">
	<td style="padding: 1px 3px">{{ getInventoryItemClassification($item->item_classification) }}</td>
	<td style="padding: 1px 3px"></td>
	<td style="padding: 1px 3px">{{ $PO->request_code }}</td>
	<td style="padding: 1px 3px">{{ $item->item_name }}</td>
	<td style="padding: 1px 3px">{{ $item->unit_type }}</td>
	<td style="text-align:right; padding: 1px 3px">{{ number_format($item->quantity) }}</td>
	<td style="padding: 1px 3px"></td>
	<td style="padding: 1px 3px"></td>
</tr>
@endforeach
</tbody>
</table>
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
<td style="width: 14.2857%;"></td>
<td style="width: 14.2857%;"></td>
<td style="width: 14.2857%;"></td>
<td style="width: 14.2857%;"></td>
<td style="width: 14.2857%;"></td>
<td style="width: 14.2857%;"></td>
<td style="width: 14.2857%; text-align: center;">
<br><br>
<span>SIGNATURE</span>
<br><br>
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
<script>
	window.onload = function(){
		window.print();
	}
</script>