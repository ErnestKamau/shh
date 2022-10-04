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
	<td style="width: 66.3334%; height: 21px;"><img src="https://aqualytic.imaralims.com/storage/companies/EixLy7nPubNy0uaGNFGlF4Ot9nzVHXpcDnMsEc0u.jpg" width="100" height="auto" alt="" /></td>
	<td style="width: 33.6666%; height: 21px; text-align: right;">
	<p><span>P.O. Box 2777 - 0056</span><br /><span>Nairobi - Kenya</span><br /><span>Tel: 254-060-02030270/81</span><br /><span>Fax: 254-060-02030279</span><span></span></p>
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
	<p>For the purpose of ___________________________________________________________________</p>
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
			<td style="width: 21.6667%; height: 21px; text-align:center">{{ number_format($item->quantity ?? 0) }}</td>
			<td style="width: 68.3333%; height: 21px; padding-left: 12px;">{{ $item->item_name ?? "" }}</td>
			</tr>
		@endforeach
	</tbody>
	</table>
	<table border="0" style="border-collapse: collapse; width: 100%;">
	<tbody>
	<tr>
	<td colspan="2" style="width: 100%;">
	<p><u><b>Checked by:</b></u></p>
	</td>
	</tr>
	<?php
		$approvers = \App\User::join('entity_approvals as ea', 'ea.user_id', 'users.id')
		->where('model', $entity->request_type)->where('model_id', $entity->id)->get()->toArray();
	?>
	<tr>
	<td style="width: 60%;">
	<p></p>
	@if(count($approvers) > 1)
	<p>Stores Manager: {!! isset($approvers[0]) ? '<span style="border-bottom: 1px solid #000; padding: 5px 10px; width: 100%">'.$approvers[0]['name'].'</span>' 
		: '_______________________' !!}</p>
	@endif
	
	</td>
	<td style="width: 40%;">
	<p></p>
	@if(count($approvers) > 1)
	<p>Signature: <span style="border-bottom: 1px solid #000; padding: 5px 10px; width: 100%">{!! trim($approvers[0]['electronic_sig']) != "" ?
		' <img src="'.$approvers[0]['electronic_sig'].'" style="height: 25px" />' : '_________________' !!}</span></p>
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
	@if(count($approvers) > 2)
	<p>Stores Manager: {!! isset($approvers[1]) ? '<span style="border-bottom: 1px solid #000; padding: 5px 10px; width: 100%">'.$approvers[0]['name'].'</span>' 
		: '_______________________' !!}</p>
	@endif
	
	</td>
	<td style="width: 40%;">
	<p></p>
	@if(count($approvers) > 2)
	<p>Signature: <span style="border-bottom: 1px solid #000; padding: 5px 10px; width: 100%">{!! trim($approvers[1]['electronic_sig']) != "" ?
		' <img src="'.$approvers[1]['electronic_sig'].'" style="height: 25px" />' : '_________________' !!}</span></p>
	@endif
	
	</td>
	</tr>
	</tbody>
	</table>
	<script>
		window.onload = function(){
			window.print();
		}
	</script>