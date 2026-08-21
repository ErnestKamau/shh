{{--
	ls-table-status — density table with soft status pills.
	Props: $rows optional array of [name, status, statusClass, rate, balance, currency]
--}}
@php
	$rows = $rows ?? [
		['name' => 'Acme Labs', 'status' => 'Open', 'statusClass' => 'ls-pill--open', 'rate' => '2.4%', 'balance' => '-$270.00', 'currency' => 'CAD'],
		['name' => 'North Pier', 'status' => 'Paid', 'statusClass' => 'ls-pill--paid', 'rate' => '1.1%', 'balance' => '$1,240.00', 'currency' => 'CAD'],
		['name' => 'Harbor Co', 'status' => 'Inactive', 'statusClass' => 'ls-pill--inactive', 'rate' => '0.0%', 'balance' => '$0.00', 'currency' => 'CAD'],
	];
@endphp
<div class="ls-table-wrap">
	<table class="ls-table">
		<thead>
			<tr>
				<th>Name</th>
				<th>Status</th>
				<th>Rate</th>
				<th>Balance</th>
			</tr>
		</thead>
		<tbody>
			@foreach($rows as $row)
				<tr>
					<td>{{ $row['name'] }}</td>
					<td><span class="ls-pill {{ $row['statusClass'] }}">{{ $row['status'] }}</span></td>
					<td>{{ $row['rate'] }}</td>
					<td>
						<div class="ls-table__stack-primary">{{ $row['balance'] }}</div>
						<div class="ls-table__stack-secondary">{{ $row['currency'] }}</div>
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>
</div>
