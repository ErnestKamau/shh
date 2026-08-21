{{--
	ls-dnd-reorder-list — flat reorder list (HTML5 DnD demo).
	Props: $dndItems (list of [label, color]) — avoid $items (often breadcrumb).
--}}
@php
	$dndItems = $dndItems ?? [
		['label' => 'Option A', 'color' => '#eab308'],
		['label' => 'Option B', 'color' => '#22c55e'],
		['label' => 'Option C', 'color' => '#ef4444'],
	];
	$listId = $listId ?? 'ls-dnd-'.uniqid();
@endphp
<div class="ls-dnd-list" id="{{ $listId }}" data-ls-dnd-reorder>
	@foreach($dndItems as $index => $item)
		<div
			class="ls-dnd-item"
			draggable="true"
			data-index="{{ $index }}"
		>
			<span class="ls-dnd-handle" aria-hidden="true"><i class="mdi mdi-drag"></i></span>
			<span class="ls-dnd-item__label">{{ $item['label'] }}</span>
			<span class="ls-dnd-status" style="background: {{ $item['color'] }};"></span>
		</div>
	@endforeach
</div>
<input type="hidden" class="ls-dnd-order-input" name="{{ $name ?? 'ls_dnd_order' }}" value="{{ collect($dndItems)->pluck('label')->implode(', ') }}">
