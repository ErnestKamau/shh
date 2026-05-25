@props([
	'fieldLabel' => '',
	'required' => false,
	'help' => null,
	'selectedLabel' => '',
	'showDropdown' => false,
	'filteredOptions' => [],
	'emptySearch' => '',
	'placeholder' => 'Search...',
	'openDropdownAction' => '',
	'closeDropdownAction' => '',
	'selectMethod' => '',
	'clearMethod' => '',
	'searchModel' => '',
	'errorKey' => null,
])

<div @class(['form-group', $attributes->get('class')])>
	<label class="lec-form-label">{{ $fieldLabel }} @if($required)<span class="text-danger">*</span>@endif</label>
	<div class="tag-select-container"
		wire:click="{{ $openDropdownAction }}"
		wire:click.outside="{{ $closeDropdownAction }}">
		<div class="tag-select-input">
			@if($selectedLabel)
				<span class="tag-badge">
					{{ $selectedLabel }}
					<i class="mdi mdi-close-circle" wire:click.stop="{{ $clearMethod }}"></i>
				</span>
			@else
				<input type="text"
					class="tag-input"
					wire:model.live.debounce.200ms="{{ $searchModel }}"
					placeholder="{{ $placeholder }}"
					autocomplete="off">
			@endif
		</div>
		@if($showDropdown && count($filteredOptions) > 0)
			<div class="tag-dropdown">
				@foreach($filteredOptions as $opt)
					<div class="tag-dropdown-item" wire:click.stop="{{ $selectMethod }}('{{ $opt['value'] }}')">
						{{ $opt['label'] }} ({{ $opt['value'] }})
					</div>
				@endforeach
			</div>
		@elseif($showDropdown && $emptySearch !== '' && count($filteredOptions) === 0)
			<div class="tag-dropdown">
				<div class="tag-dropdown-item text-muted">No matches found</div>
			</div>
		@endif
	</div>
	@if($errorKey)
		@error($errorKey) <small class="text-danger d-block">{{ $message }}</small> @enderror
	@endif
	@if($help)
		<span class="lec-field-hint">{{ $help }}</span>
	@endif
</div>
