{{--
	ls-table-plain — clean density table; optional $skeleton = true.
--}}
@php
	$skeleton = $skeleton ?? false;
	$rows = $rows ?? [
		['name' => 'TRFF038/26', 'customer' => 'Amspec Santos', 'submitted' => '2026-08-21'],
		['name' => 'TRFF039/26', 'customer' => 'Harbor Foods', 'submitted' => '2026-08-20'],
	];
@endphp
<div class="ls-table-wrap">
	<table class="ls-table">
		<thead>
			<tr>
				<th>Request</th>
				<th>Customer</th>
				<th>Submitted</th>
			</tr>
		</thead>
		<tbody>
			@if($skeleton)
				@for($i = 0; $i < 3; $i++)
					<tr>
						<td><span class="ls-skeleton" style="width: 5.5rem;"></span></td>
						<td><span class="ls-skeleton" style="width: 7rem;"></span></td>
						<td><span class="ls-skeleton" style="width: 4.5rem;"></span></td>
					</tr>
				@endfor
			@else
				@foreach($rows as $row)
					<tr>
						<td>{{ $row['name'] }}</td>
						<td>{{ $row['customer'] }}</td>
						<td>{{ $row['submitted'] }}</td>
					</tr>
				@endforeach
			@endif
		</tbody>
	</table>
</div>
