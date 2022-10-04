<?php
	$PO = \App\RequestEntity::find($entity->parent_request_id);
	$REQUESTER = \App\User::find($entity->request_initiator);
	$SUPPLIER = \App\Supplier::find($entity->supplier_id);
	$allItems = $entity->items($entity->ammendment) ?? array();

	$normalItems = empty($allItems) ? array() : ($allItems['normal'] ?? array());

?>
<table style="border-collapse: collapse; width: 100%; height: 231px; margin-bottom: 10px;" border="0">
	<tbody>
	<tr style="height: 21px;">
	<td style="width: 66.3334%; height: 21px;"><img src="https://www.logolynx.com/images/logolynx/03/037a165cfa584a7e0d01df3c2f850ddf.png" alt="" width="100" height="auto" /></td>
	<td style="width: 33.6666%; height: 21px; text-align: right;">
	<p>P.O. Box 2777 - 0056<br />Nairobi - Kenya<br />Tel: 254-060-02030270/81<br />Fax: 254-060-02030279</p>
	</td>
	</tr>
	<tr style="height: 35px;">
	<td style="width: 100%; height: 35px; text-align: center;" colspan="2"><strong><span style="text-decoration: underline;">GATE PASS</span></strong></td>
	</tr>
	<tr style="height: 35px;">
	<td style="width: 100%; height: 35px;" colspan="2">&nbsp;</td>
	</tr>
	<tr style="height: 35px;">
	<td style="width: 100%; height: 35px;">Date <span style="border-bottom: 1px solid #000; padding: 5px 10px;">{{ $entity->created_at ?? "" }}</span></td>
	<td style="width: 100%; height: 35px; white-space: nowrap">Time Out <span style="border-bottom: 1px solid #000; padding: 5px 10px;">{{ $entity->time_out ?? "" }}</span></td>
	</tr>
	<tr style="height: 35px;">
	<td style="width: 100%; height: 35px;">Gate Pass No. <span  style="border: 2px solid #000; min-width: 100px; padding: 5px 10px; font-size: 22px; color: red">{{ $entity->gate_pass ?? "" }}</span></td>
	<td>
			<table  style="border-collapse: collapse; width: 100%;" border="0">
				<tr>
					<td style="width: 100%; height: 35px; white-space: nowrap">Vehicle No. <span style="border-bottom: 1px solid #000; padding: 5px 10px;">{{ $entity->vehicle_no ?? "" }}</span>
					</td>
				</tr>
				<tr>
					<td style="width: 100%; height: 35px; white-space: nowrap">Destination <span style="border-bottom: 1px solid #000; padding: 5px 10px;">{{ $entity->destination ?? "" }}</span>
					</td>
				</tr>
			</table>
	</td>
	</tr>
	<tr style="height: 35px;">
	<td style="width: 100%; height: 35px;" colspan="2">
	<p>Please allow the bearer of this note Mr./Mrs. <span style="border-bottom: 1px solid #000; padding: 5px 10px;">{{ $entity->note_bearer }}</span></p>
	</td>
	</tr>
	<tr style="height: 35px;">
	<td style="width: 100%; height: 35px;" colspan="2">
	<p>For the purpose of <span style="border-bottom: 1px solid #000; padding: 5px 10px;">{{ $entity->description }}</span></p>
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
		<p style="padding: 5px"><b>Checked by:</b></p>
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
		<p style="padding: 5px">Stores Manager: {!! isset($approvers[0]) ? '<span style="border-bottom: 1px solid #000; padding: 5px 10px; width: 100%">'.$approvers[0]['name'].'</span>'
			: '_______________________' !!}</p>
		@endif

		</td>
		<td style="width: 40%;">
		<p></p>
		@if(count($approvers) > 1)
		<p style="padding: 5px">Signature: <span style="border-bottom: 1px solid #000; padding: 5px 10px; width: 100%">{!! trim($approvers[0]['electronic_sig']) != "" ?
			' <img src="'.$approvers[0]['electronic_sig'].'" style="height: 25px" />' : '_________________' !!}</span></p>
		@endif

		</td>
		</tr>
		<tr>
		<td colspan="2" style="width: 100%;">
		<p style="padding: 5px"><b>Authorised by Manager:</b></p>
		</td>
		</tr>
		<tr>
		<td style="width: 60%;">
		<p></p>
		@if(count($approvers) > 2)
		<p style="padding: 5px">Stores Manager: {!! isset($approvers[1]) ? '<span style="border-bottom: 1px solid #000; padding: 5px 10px; width: 100%">'.$approvers[0]['name'].'</span>'
			: '_______________________' !!}</p>
		@endif

		</td>
		<td style="width: 40%;">
		<p></p>
		@if(count($approvers) > 2)
		<p style="padding: 5px">Signature: <span style="border-bottom: 1px solid #000; padding: 5px 10px; width: 100%">{!! trim($approvers[1]['electronic_sig']) != "" ?
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