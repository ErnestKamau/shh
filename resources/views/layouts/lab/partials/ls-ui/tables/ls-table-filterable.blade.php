{{--
	ls-table-filterable — dense table with per-column filter icons (demo Alpine).
--}}
@php
	$rows = $rows ?? [
		['name' => 'Acme Labs', 'status' => 'Open', 'rate' => '2.4%', 'balance' => '-$270.00'],
		['name' => 'North Pier', 'status' => 'Paid', 'rate' => '1.1%', 'balance' => '$1,240.00'],
		['name' => 'Harbor Co', 'status' => 'Inactive', 'rate' => '0.0%', 'balance' => '$0.00'],
		['name' => 'Blue Bay', 'status' => 'Open', 'rate' => '3.2%', 'balance' => '$880.00'],
	];
@endphp
<div
	class="ls-table-wrap ls-table-wrap--fit ls-table-filter"
	x-data="{
		rows: @js($rows),
		open: null,
		filters: { name: '', status: '', rate: '', balance: '' },
		get filtered() {
			return this.rows.filter(r => {
				const f = this.filters;
				const match = (val, q) => !q || String(val).toLowerCase().includes(String(q).toLowerCase());
				return match(r.name, f.name) && match(r.status, f.status) && match(r.rate, f.rate) && match(r.balance, f.balance);
			});
		},
		toggle(col) { this.open = this.open === col ? null : col; },
		clear(col) { this.filters[col] = ''; this.open = null; }
	}"
	@click.outside="open = null"
>
	<table class="ls-table ls-table--dense">
		<thead>
			<tr>
				@foreach(['name' => 'Name', 'status' => 'Status', 'rate' => 'Rate', 'balance' => 'Balance'] as $col => $heading)
					<th>
						<span class="ls-th-filter">
							{{ $heading }}
							<button
								type="button"
								class="ls-filter-btn"
								:class="{ 'is-active': filters.{{ $col }} || open === '{{ $col }}' }"
								@click.stop="toggle('{{ $col }}')"
								aria-label="Filter {{ $heading }}"
							>
								<i class="mdi mdi-filter-outline"></i>
							</button>
						</span>
						<div class="ls-filter-panel" x-show="open === '{{ $col }}'" x-cloak @click.stop>
							<input type="text" x-model="filters.{{ $col }}" placeholder="Filter {{ strtolower($heading) }}…" @keydown.enter="open = null">
							<button type="button" class="ls-btn" style="width:100%;" @click="clear('{{ $col }}')">Clear</button>
						</div>
					</th>
				@endforeach
			</tr>
		</thead>
		<tbody>
			<template x-for="row in filtered" :key="row.name">
				<tr>
					<td x-text="row.name"></td>
					<td x-text="row.status"></td>
					<td x-text="row.rate"></td>
					<td x-text="row.balance"></td>
				</tr>
			</template>
			<tr x-show="filtered.length === 0">
				<td colspan="4" class="text-muted">No matching rows</td>
			</tr>
		</tbody>
	</table>
</div>
