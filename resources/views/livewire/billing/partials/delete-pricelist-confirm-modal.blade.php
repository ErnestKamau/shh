@php
	$isOpen = (bool) ($isOpen ?? false);
@endphp

@include('layouts.lab.partials.billing.pricelist-delete-modal-styles')

@teleport('body')
{{-- Teleported so backdrop is not clipped by #main-container-body overflow. --}}
<div
	class="modal fade ls-pricelist-delete-modal ls-ui-kit {{ $isOpen ? 'show d-block' : '' }}"
	tabindex="-1"
	role="dialog"
	wire:key="ls-pricelist-delete-modal"
	@if($isOpen)
		style="background-color: rgba(15, 23, 42, 0.55);"
		aria-modal="true"
		aria-hidden="false"
		wire:click.self="closeDeletePricelistConfirmModal"
	@else
		style="display: none;"
		aria-hidden="true"
	@endif
>
	<div class="modal-dialog modal-dialog-centered ls-pricelist-delete-dialog" wire:click.stop>
		<div class="modal-content item-modal-content">
			<div class="modal-header item-modal-header border-0 ls-pricelist-delete-header">
				<div>
					<h5 class="modal-title mb-1">Delete pricelist</h5>
					<p class="mb-0 ls-pricelist-delete-subtitle">
						This permanently removes the pricelist and cannot be undone.
					</p>
				</div>
				<button
					type="button"
					class="btn-close btn-close-white ls-pricelist-delete-close"
					wire:click="closeDeletePricelistConfirmModal"
					aria-label="Close"
				></button>
			</div>

			<div class="modal-body item-modal-body">
				<div class="ls-pricelist-delete-warning" role="status">
					<i class="mdi mdi-information-outline ls-pricelist-delete-warning__icon" aria-hidden="true"></i>
					<p class="ls-pricelist-delete-warning__text">
						Items, customer assignments, and email logs will be deleted. Linked quotations keep their lines but lose the pricelist reference.
					</p>
				</div>
			</div>

			<div class="modal-footer item-modal-footer border-0">
				<button type="button" class="btn item-modal-cancel-btn" wire:click="closeDeletePricelistConfirmModal">
					Cancel
				</button>
				<button
					type="button"
					class="btn ls-pricelist-delete-confirm-btn"
					wire:click="confirmDeletePricelist"
					wire:loading.attr="disabled"
					wire:target="confirmDeletePricelist"
				>
					<span wire:loading.remove wire:target="confirmDeletePricelist">
						<i class="mdi mdi-delete-outline" aria-hidden="true"></i>
						Delete pricelist
					</span>
					<span wire:loading wire:target="confirmDeletePricelist">
						Deleting…
					</span>
				</button>
			</div>
		</div>
	</div>
</div>
@endteleport
