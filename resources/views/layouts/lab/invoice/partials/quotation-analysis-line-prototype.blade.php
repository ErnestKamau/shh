{{--
	Hidden prototype row for Analysis quotation lines (Amspec UAE prep model).

	Commercial math (see QuotationPricingResolver):
	  Package (default): Total = No. of samples × Unit price ONCE for the sample package.
	  Per parameter: each selected test becomes its own billed line (qty × unit price each).

	quantity_required = free text (e.g. "Per Sample Swab") — not a multiplier.
	Flow: Sample Type → Parameters modal → save. Analysis type is NOT a line column.
	Per-parameter mode explodes the prep row into one commercial strip per test in the UI.
--}}
@php
	$sampleTypeSelectOptions = collect($sample_types ?? [])->mapWithKeys(
		fn ($type) => [(string) $type->id => (string) $type->name]
	)->all();
@endphp
<table class="d-none" aria-hidden="true">
	<tbody>
		<tr id="ls-quote-analysis-line-prototype">
			<td class="text-center align-middle">
				<input type="checkbox" class="ls-quote-line-select" value="" aria-label="Select line" disabled>
			</td>
			<td class="text-center align-middle ls-quote-line-actions">
				<span class="ls-quote-line-no">0</span>
				<button type="button" class="ls-quote-icon-btn ls-quote-icon-btn--danger js-delete-line" title="Remove line">
					<i class="mdi mdi-minus-circle-outline"></i>
				</button>
			</td>
			<td class="align-middle ls-quote-col-sample">
				@include('layouts.lab.partials.ls-ui.select2.ls-select2-multi-dropdown-search', [
					'label' => null,
					'id' => 'select-sample-type-proto',
					'name' => 'sample_type[]',
					'options' => $sampleTypeSelectOptions,
					'selected' => [],
					'multiple' => false,
					'placeholder' => 'Choose sample type…',
					'variant' => 'slate',
					'extraSelectClass' => 'select-sample-type',
				])
				<div class="ls-quote-line-mode-wrap mt-1" data-manual-only="1">
					<label class="ls-quote-line-mode-toggle mb-0" title="Package = samples × one price. Per parameter = each test billed separately.">
						<input type="checkbox" class="ls-quote-line-mode-input" disabled>
						<span class="ls-quote-line-mode-label">Per parameter</span>
					</label>
				</div>
				{{-- Derived from selected parameters' analysis_type_id on modal save. --}}
				<input type="hidden" class="form-control select-part-final" value="" name="part_number_final[]" id="select-part-final-proto" disabled>
			</td>
			<td class="align-middle quote-description-cell" id="quote-description-proto">
				<div class="ls-quote-params ls-quote-params--empty">
					<span class="ls-type-caption">Select sample type, then Parameters</span>
				</div>
			</td>
			<td class="align-middle ls-quote-col-qty-req">
				<input type="hidden" name="pricing_mode[]" class="quotation-pricing-mode" value="per_package" disabled>
				<div class="ls-field ls-compact mb-0">
					<div class="ls-field__control">
						<input type="text" name="quantity_required[]" class="ls-field__input quotation-qty-required" value="" placeholder="e.g. Per Sample" disabled>
					</div>
				</div>
			</td>
			<td class="align-middle ls-quote-col-qty">
				<div class="ls-field ls-compact mb-0">
					<div class="ls-field__control">
						<input type="number" name="quantity[]" class="ls-field__input quotation-qty" value="" placeholder="Qty" disabled data-ls-quote-required="1">
					</div>
				</div>
			</td>
			<td class="align-middle ls-quote-col-price">
				<div class="ls-field ls-compact mb-0">
					<div class="ls-field__control">
						<input type="number" name="unit_price[]" class="ls-field__input quotation-unit-price" min="0" step="1" value="0" placeholder="0" disabled>
					</div>
					<p class="ls-field__hint quotation-price-hint mb-0"></p>
				</div>
			</td>
			<td class="align-middle ls-quote-col-total">
				<div class="ls-field ls-compact is-disabled mb-0">
					<div class="ls-field__control">
						<input type="text" class="ls-field__input text-right quotation-line-total" value="0.00" readonly tabindex="-1" disabled>
					</div>
				</div>
			</td>
			<td class="align-middle ls-quote-col-tax">
				<div class="ls-field ls-compact mb-0">
					<input type="hidden" name="tax[]" class="quotation-tax" value="0" disabled>
					<span class="ls-quote-tax-display quotation-tax-display">0%</span>
				</div>
			</td>
		</tr>
	</tbody>
</table>
