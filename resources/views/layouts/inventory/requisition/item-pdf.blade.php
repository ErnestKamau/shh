<!DOCTYPE html>
<html>
	<body style=" color: #323232">
		@foreach ($entities as $entity)
			<h2>{{ $entity->request_type }} {{ $entity->request_code }}</h2>
			<h3>Cost Center: {{ $entity->cost_center }}</h3>
			<h3>Requester Date: {{ \Carbon\Carbon::parse($entity->created_at)->format('Y-m-d') }}</h3>
			<h3>Approval Date: {{ trim($entity->approved_at) == "" ? "Not Approved" : \Carbon\Carbon::parse($entity->approved_at)->format('Y-m-d') }}</h3>
			@if (isset($entity->issued_at))
				<h4>Issued Date: {{ $entity->issued_at }}</h4>					
			@endif
			<table style="border-collapse: collapse; width: 100%; border: 1px solid #232323;">
				<thead>
					<tr>
						<th style="border: 1px solid #232323; border-bottom: none; padding: 10px 5px">No.</th>
						<th style="border: 1px solid #232323; border-bottom: none; padding: 10px 5px" nowrap>SAP Code</th>
						<th style="border: 1px solid #232323; border-bottom: none; padding: 10px 5px">Item</th>
						<th style="border: 1px solid #232323; border-bottom: none; padding: 10px 5px">Code</th>
						<th style="border: 1px solid #232323; border-bottom: none; padding: 10px 5px" nowrap>Quantity</th>
						<th style="border: 1px solid #232323; border-bottom: none; padding: 10px 5px">U.o.M.</th>
						@if(in_array($entity->request_type, ['Goods Receipt', 'Material Issuance']))
							<th style="border: 1px solid #232323; border-bottom: none; padding: 10px 5px">Store</th>
							<th style="border: 1px solid #232323; border-bottom: none; padding: 10px 5px">Slot</th>
						@endif
					</tr>
				</thead>
				<tbody>
					@foreach ($entity->items as $item)
						<tr>
							<td style="border: 1px solid #232323; border-bottom: none; padding: 10px 5px">{{ $loop->iteration }}</td>
							<td style="border: 1px solid #232323; border-bottom: none; padding: 10px 5px">{{ $item['sap_code'] }}</td>
							<td style="border: 1px solid #232323; border-bottom: none; padding: 10px 5px">{{ $item['Item'] }}</td>
							<td style="border: 1px solid #232323; border-bottom: none; padding: 10px 5px">{{ $item['Code'] }}</td>
							<td style="border: 1px solid #232323; border-bottom: none; padding: 10px 5px">{{ number_format($item['Quantity'], 2) }}</td>
							<td style="border: 1px solid #232323; border-bottom: none; padding: 10px 5px">{{ $item['Unit Type'] }}</td>
							@if(in_array($entity->request_type, ['Goods Receipt', 'Material Issuance']))
								<td style="border: 1px solid #232323; border-bottom: none; padding: 10px 5px">{{ $item['Store'] }}</td>
								<td style="border: 1px solid #232323; border-bottom: none; padding: 10px 5px">{{ $item['Slot'] }}</td>
							@endif
						</tr>
					@endforeach
				</tbody>
			</table>
		@endforeach
	</body>
</html>