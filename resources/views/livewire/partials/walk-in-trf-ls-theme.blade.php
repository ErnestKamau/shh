{{--
	TRF fill theme — mirrors Edit request details (LS fields, chips, sample panels).
	Scope: .trf-ls-theme only. Does not style Direct Registration TRF chooser (.dr-select-trf).
--}}
<style>
	.trf-ls-theme {
		--workflow-accent: var(--color-primary, #8b1e2d);
		--trf-wizard-accent: var(--workflow-accent, #8b1e2d);
		--ls-accent: var(--workflow-accent, #8b1e2d);
		--ls-blue-soft: #eff6ff;
		--ls-blue-soft-border: #dbeafe;
		--ls-blue-soft-ring: #bfdbfe;
		--ls-blue-focus: #93c5fd;
		--ls-ink: #1e293b;
		--ls-muted: #64748b;
		--ls-border: #e2e8f0;
		--ls-surface: #ffffff;
		--ls-bg: #f8fafc;
		--ls-radius: 8px;
		--ls-radius-lg: 10px;
	}

	/* —— Section titles (edit modal) —— */
	.trf-ls-theme .walk-in-trf-wizard__panel-title {
		font-size: calc(0.8rem + 2px);
		font-weight: 700;
		letter-spacing: 0.03em;
		text-transform: uppercase;
		color: #1e3a8a;
		margin: 0 0 0.35rem;
	}

	.trf-ls-theme .walk-in-trf-wizard__panel-desc {
		font-size: 0.75rem;
		color: var(--ls-muted);
		margin: 0 0 1rem;
	}

	.trf-ls-theme .walk-in-trf-wizard__panel {
		border-color: #e8ecf2;
		border-radius: var(--ls-radius-lg);
		box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
		padding: 1.25rem 1.45rem 1.35rem;
		min-height: 0;
	}

	.trf-ls-theme .walk-in-trf-wizard-shell {
		background: transparent !important;
		border: 0 !important;
		padding: 0 !important;
		min-height: 0 !important;
	}

	.trf-ls-theme .walk-in-trf-wizard__panel--samples {
		padding-bottom: 0.85rem;
	}

	.trf-ls-theme .walk-in-trf-wizard__panel--samples .walk-in-trf-wizard__panel-title {
		margin-bottom: 0.15rem;
	}

	.trf-ls-theme .walk-in-trf-wizard__panel--samples .walk-in-trf-wizard__panel-desc {
		margin-bottom: 0.5rem;
	}

	/* —— Labels + soft inputs —— */
	.trf-ls-theme label,
	.trf-ls-theme .font-weight-bold.text-secondary.small,
	.trf-ls-theme .ls-field__label {
		font-size: 0.75rem !important;
		font-weight: 600 !important;
		color: var(--ls-ink) !important;
		margin-bottom: 0.35rem;
	}

	.trf-ls-theme .form-control,
	.trf-ls-theme select.form-control,
	.trf-ls-theme textarea.form-control {
		min-height: 38px;
		border: 1px solid var(--ls-border) !important;
		border-radius: var(--ls-radius) !important;
		background: var(--ls-blue-soft) !important;
		font-size: 0.8125rem;
		color: var(--ls-ink);
		box-shadow: none !important;
	}

	.trf-ls-theme .form-control:focus,
	.trf-ls-theme select.form-control:focus,
	.trf-ls-theme textarea.form-control:focus {
		border-color: var(--ls-blue-focus) !important;
		background: #fff !important;
		box-shadow: 0 0 0 3px color-mix(in srgb, var(--ls-blue-soft-ring) 55%, transparent) !important;
	}

	.trf-ls-theme .form-control[readonly],
	.trf-ls-theme .form-control:disabled {
		background: #f1f5f9 !important;
		opacity: 0.85;
	}

	.trf-ls-theme .form-control.is-invalid {
		border-color: #f87171 !important;
		background: #fff5f5 !important;
	}

	/* Select2 soft shell */
	.trf-ls-theme .select2-container--default .select2-selection--single,
	.trf-ls-theme .select2-container--default .select2-selection--multiple {
		min-height: 38px;
		border: 1px solid var(--ls-border) !important;
		border-radius: var(--ls-radius) !important;
		background: var(--ls-blue-soft) !important;
	}

	.trf-ls-theme .select2-container--default.select2-container--focus .select2-selection--single,
	.trf-ls-theme .select2-container--default.select2-container--focus .select2-selection--multiple,
	.trf-ls-theme .select2-container--default.select2-container--open .select2-selection--single,
	.trf-ls-theme .select2-container--default.select2-container--open .select2-selection--multiple {
		border-color: var(--ls-blue-focus) !important;
		box-shadow: 0 0 0 3px color-mix(in srgb, var(--ls-blue-soft-ring) 55%, transparent);
		background: #fff !important;
	}

	.trf-ls-theme .select2-container--default .select2-selection--single .select2-selection__rendered {
		line-height: 36px;
		padding-left: 0.7rem;
		color: var(--ls-ink);
		font-size: 0.8125rem;
	}

	.trf-ls-theme .select2-container--default .select2-selection--multiple .select2-selection__choice {
		background: #e2e8f0;
		border: 1px solid #cbd5e1;
		border-radius: 999px;
		color: #334155;
		font-size: 0.72rem;
		padding: 0.1rem 0.45rem;
	}

	/* —— Option chips (edit modal) —— */
	.trf-ls-theme .trf-option-grid,
	.trf-ls-theme .ls-option-grid {
		display: grid;
		gap: 0.4rem 0.45rem;
		width: 100%;
	}

	.trf-ls-theme .trf-option-grid--transport,
	.trf-ls-theme .ls-option-grid--transport {
		grid-template-columns: repeat(2, max-content);
		justify-content: start;
		width: max-content;
		max-width: 100%;
		column-gap: 0.3rem;
	}

	.trf-ls-theme .trf-option-grid--apparatus,
	.trf-ls-theme .ls-option-grid--apparatus {
		grid-template-columns: repeat(3, minmax(0, 1fr));
	}

	.trf-ls-theme .trf-option-grid--method,
	.trf-ls-theme .ls-option-grid--method {
		grid-template-columns: repeat(4, minmax(0, 1fr));
	}

	.trf-ls-theme .trf-option-grid--compact,
	.trf-ls-theme .ls-option-grid--compact {
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}

	.trf-ls-theme .trf-option-grid--auto,
	.trf-ls-theme .ls-option-grid--auto {
		grid-template-columns: repeat(auto-fill, minmax(8.5rem, 1fr));
	}

	.trf-ls-theme .trf-option-chip,
	.trf-ls-theme .ls-option-chip {
		display: flex !important;
		align-items: center;
		gap: 0.45rem;
		box-sizing: border-box;
		width: 100%;
		margin: 0;
		padding: 0.45rem 0.6rem;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		background: #f8fafc;
		font-size: 0.78rem;
		font-weight: 500;
		color: #334155;
		cursor: pointer;
		line-height: 1.25;
		user-select: none;
		transition: border-color 0.12s ease, background 0.12s ease;
	}

	.trf-ls-theme .trf-option-chip:has(input:checked),
	.trf-ls-theme .ls-option-chip:has(input:checked),
	.trf-ls-theme .ls-option-chip.is-checked {
		border-color: color-mix(in srgb, #34d399 55%, #e2e8f0);
		background:
			linear-gradient(135deg, color-mix(in srgb, #bbf7d0 42%, rgba(255, 255, 255, 0.94)) 0%, color-mix(in srgb, #86efac 22%, rgba(255, 255, 255, 0.96)) 100%);
		box-shadow:
			inset 0 1px 0 rgba(255, 255, 255, 0.78),
			0 0 0 1px color-mix(in srgb, #34d399 18%, transparent);
		color: #065f46;
	}

	.trf-ls-theme .trf-option-chip:hover,
	.trf-ls-theme .ls-option-chip:hover {
		border-color: #86efac;
		background: #f0fdf4;
	}

	.trf-ls-theme .trf-option-chip input,
	.trf-ls-theme .ls-option-chip input,
	.trf-ls-theme .trf-option-chip .custom-control-input,
	.trf-ls-theme .ls-option-chip .custom-control-input {
		position: static !important;
		float: none !important;
		margin: 0 !important;
		flex: 0 0 auto;
		width: 0.95rem !important;
		height: 0.95rem !important;
		accent-color: #059669;
	}

	.trf-ls-theme .trf-option-chip__label,
	.trf-ls-theme .ls-option-chip__label {
		flex: 1 1 auto;
		min-width: 0;
		font-weight: 500;
		text-align: left;
	}

	/* Tests column — quotation-style parameter chips + modal */
	.trf-ls-theme .rft-tests-picker__shell {
		display: block;
		width: 100%;
	}

	.trf-ls-theme .rft-trf-tests-params.ls-quote-params {
		background: #f8fafc;
		border: 1px solid #e2e8f0;
		border-radius: 10px;
		padding: 0.45rem 0.55rem;
		min-height: 2.75rem;
		cursor: pointer;
		transition: border-color 0.12s ease, box-shadow 0.12s ease;
	}

	.trf-ls-theme .rft-trf-tests-params.ls-quote-params:hover {
		border-color: #cbd5e1;
		box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
	}

	.trf-ls-theme .rft-trf-tests-params.ls-quote-params--empty {
		display: flex;
		align-items: center;
		justify-content: center;
		background: #fff;
		min-height: 2.5rem;
	}

	.trf-ls-theme .rft-trf-tests-params .ls-quote-params__toolbar {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 0.5rem;
		margin-bottom: 0.35rem;
	}

	.trf-ls-theme .rft-trf-tests-params .ls-quote-params__title {
		font-size: 0.72rem;
		font-weight: 700;
		color: #334155;
		line-height: 1.3;
		display: inline-flex;
		align-items: center;
		gap: 0.4rem;
	}

	.trf-ls-theme .rft-trf-tests-params .ls-quote-params__count {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-width: 1.35rem;
		height: 1.2rem;
		padding: 0 0.35rem;
		border-radius: 999px;
		background: #dbeafe;
		border: 1px solid #bfdbfe;
		color: #1e40af;
		font-size: 0.68rem;
		font-weight: 700;
		font-variant-numeric: tabular-nums;
	}

	.trf-ls-theme .rft-trf-tests-params.is-collapsed .ls-quote-params__chips {
		display: none;
	}

	.trf-ls-theme .rft-trf-tests-params .ls-quote-params__edit {
		padding: 0.15rem 0.45rem !important;
		font-size: 0.7rem !important;
		flex-shrink: 0;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		background: #fff;
		color: #64748b;
	}

	.trf-ls-theme .rft-trf-tests-params .ls-quote-params__edit:hover {
		color: #9f1239;
		border-color: #cbd5e1;
	}

	.trf-ls-theme .rft-trf-tests-params .ls-quote-params__chips {
		gap: 0.3rem;
		max-height: 8rem;
		overflow-y: auto;
	}

	.trf-ls-theme .rft-trf-tests-params .ls-quote-param-chip .mdi {
		font-size: 0.85em;
		margin-left: 0.15rem;
		opacity: 0.85;
	}

	.trf-ls-theme .rft-trf-tests-params .ls-quote-param-chip {
		max-width: 100%;
		font-size: 0.68rem;
	}

	.trf-ls-theme .rft-trf-tests-params .ls-quote-param-chip--acc {
		background: color-mix(in srgb, #059669 12%, #fff);
		border-color: color-mix(in srgb, #059669 30%, #e2e8f0);
		color: #047857;
	}

	.trf-ls-theme .rft-tests-picker__select {
		flex: 1 1 auto;
		min-width: 0;
		cursor: pointer;
	}

	.trf-ls-theme .rft-tests-picker__select .select2-container .select2-selection--multiple {
		cursor: pointer;
	}

	.trf-ls-theme .rft-tests-select2 + .select2-container .select2-selection__choice__remove {
		display: none !important;
	}

	.trf-ls-theme .rft-tests-select2 + .select2-container .select2-search--inline {
		display: none !important;
		width: 0 !important;
		min-width: 0 !important;
	}

	.trf-ls-theme .rft-tests-picker__select .ls-field {
		margin-bottom: 0;
	}

	.trf-ls-theme .rft-tests-picker__select .ls-select2-multi .select2-container {
		width: 100% !important;
	}

	.trf-ls-theme .rft-tests-picker__modal-btn {
		flex: 0 0 auto;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 34px;
		height: 34px;
		margin-top: 0;
		border: 1px solid #e2e8f0;
		border-radius: 10px;
		background: #f8fafc;
		color: #64748b;
		cursor: pointer;
		transition: border-color 0.12s ease, background 0.12s ease, color 0.12s ease;
	}

	.trf-ls-theme .rft-tests-picker__modal-btn:hover:not(:disabled) {
		border-color: #cbd5e1;
		background: #fff;
		color: #9f1239;
	}

	.trf-ls-theme .rft-tests-picker__modal-btn:disabled {
		opacity: 0.45;
		cursor: not-allowed;
	}

	.trf-ls-theme .rft-tests-select2 + .select2-container .select2-selection--multiple {
		min-height: 34px;
		max-height: 2.75rem;
		overflow: hidden;
	}

	.trf-ls-theme .rft-tests-select2 + .select2-container .select2-selection__rendered.rft-tests-select2__summary .select2-selection__choice {
		display: none !important;
	}

	.trf-ls-theme .rft-tests-select2 + .select2-container .select2-selection__rendered.rft-tests-select2__summary::before {
		content: attr(data-summary);
		display: block;
		padding: 0.35rem 0.5rem;
		font-size: 0.8125rem;
		font-weight: 600;
		color: #334155;
		line-height: 1.35;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}

	.trf-ls-theme .rft-tests-select2 + .select2-container .select2-selection__rendered.rft-tests-select2__summary .select2-selection__placeholder {
		display: none !important;
	}

	/* —— Collection 3-col tracks —— */
	.trf-ls-theme .trf-collection-trio {
		display: grid;
		grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1fr);
		column-gap: clamp(1rem, 4vw, 2rem);
		align-items: start;
		margin-bottom: 1.25rem;
	}

	.trf-ls-theme .trf-collection-trio > * {
		min-width: 0;
	}

	@media (max-width: 767.98px) {
		.trf-ls-theme .trf-collection-trio {
			grid-template-columns: 1fr;
			row-gap: 0.85rem;
		}

		.trf-ls-theme .trf-option-grid--method,
		.trf-ls-theme .trf-option-grid--apparatus,
		.trf-ls-theme .ls-option-grid--method,
		.trf-ls-theme .ls-option-grid--apparatus {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
	}

	/* —— Sample / step accordion (baby-blue, not burgundy) —— */
	.trf-ls-theme .rft-sample-row-card,
	.trf-ls-theme .rft-trf-step-card {
		border: 1px solid var(--ls-blue-soft-border);
		border-radius: var(--ls-radius-lg);
		background: color-mix(in srgb, var(--ls-blue-soft) 28%, #fff);
		overflow: hidden;
		box-shadow: none;
		margin-bottom: 0.65rem;
	}

	.trf-ls-theme .rft-sample-row-card.is-open,
	.trf-ls-theme .rft-trf-step-card.is-open {
		border-color: var(--ls-blue-soft-border);
		background: color-mix(in srgb, var(--ls-blue-soft) 38%, #fff);
		box-shadow: 0 0 0 1px color-mix(in srgb, var(--ls-blue-soft-ring) 55%, transparent);
	}

	.trf-ls-theme .rft-sample-row-card__header,
	.trf-ls-theme .rft-trf-step-card .rft-sample-row-card__header {
		background: color-mix(in srgb, var(--ls-blue-soft) 52%, #fff);
		padding: 0.7rem 0.85rem;
	}

	.trf-ls-theme .rft-sample-row-card.is-open .rft-sample-row-card__header,
	.trf-ls-theme .rft-trf-step-card.is-open .rft-sample-row-card__header {
		background: var(--ls-blue-soft);
		border-bottom: 1px solid var(--ls-blue-soft-border);
		padding-bottom: 0.7rem;
	}

	.trf-ls-theme .rft-sample-row-card__title,
	.trf-ls-theme .rft-trf-step-card .rft-sample-row-card__title {
		font-size: 0.8125rem;
		font-weight: 700;
		color: #0369a1;
		margin: 0;
	}

	.trf-ls-theme .rft-sample-row-card.is-open .rft-sample-row-card__title,
	.trf-ls-theme .rft-trf-step-card.is-open .rft-sample-row-card__title {
		color: #0c4a6e;
	}

	.trf-ls-theme .rft-sample-row-card__toggle-main:hover .rft-sample-row-card__title,
	.trf-ls-theme .rft-trf-step-card .rft-sample-row-card__toggle-main:hover .rft-sample-row-card__title {
		color: #0284c7;
	}

	.trf-ls-theme .rft-sample-row-card__body,
	.trf-ls-theme .rft-trf-step-card .rft-sample-row-card__body {
		padding: 0.95rem 1rem 0.65rem;
		background: color-mix(in srgb, var(--ls-blue-soft) 12%, #fff);
	}

	.trf-ls-theme .rft-sample-row-card.is-open .rft-sample-row-card__body,
	.trf-ls-theme .rft-trf-step-card.is-open .rft-sample-row-card__body {
		background: color-mix(in srgb, var(--ls-blue-soft) 18%, #fff);
	}

	.trf-ls-theme .rft-trf-step-cards {
		margin-bottom: 0.35rem;
	}

	/* Catalog row: sample type + analysis side-by-side (edit modal parity) */
	.trf-ls-theme .rv-trf-catalog-row,
	.trf-ls-theme .rft-sample-catalog-row {
		display: grid;
		grid-template-columns: repeat(2, minmax(0, 1fr));
		column-gap: clamp(1.15rem, 3vw, 2rem);
		row-gap: 0.65rem;
		margin-bottom: 1rem;
		align-items: start;
	}

	.trf-ls-theme .walk-in-trf-wizard__panel--samples .rft-sample-cards {
		margin-bottom: 0;
	}

	.trf-ls-theme .rft-sample-catalog-row {
		margin-top: 0.15rem;
	}

	.trf-ls-theme .rft-sample-row-card__body > .rft-sample-catalog-row:first-child,
	.trf-ls-theme .rft-trf-step-card .rft-sample-row-card__body > .rft-sample-catalog-row:first-child {
		margin-top: 0;
		margin-bottom: 0.85rem;
	}

	.trf-ls-theme .rft-sample-catalog-row--type-tests,
	.trf-ls-theme .rv-trf-catalog-row--type-tests {
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}

	.trf-ls-theme .rv-trf-catalog-row__cell,
	.trf-ls-theme .rft-sample-catalog-row .rv-trf-catalog-row__cell {
		min-width: 0;
	}

	.trf-ls-theme .rv-trf-select-shell .select2-container {
		width: 100% !important;
	}

	.trf-ls-theme .rft-sample-grid-row.row,
	.trf-ls-theme .rft-sample-grid-row {
		column-gap: clamp(1.35rem, 3.5vw, 2.35rem);
	}

	/* Customer step: preserve Bootstrap 3-col rows; flex + buttons stay inside each col */
	.trf-ls-theme .rft-customer-grid-row.row {
		display: flex;
		flex-wrap: wrap;
		margin-left: -15px;
		margin-right: -15px;
	}

	.trf-ls-theme .rft-customer-grid-row > [class*='col-'] {
		min-width: 0;
		padding-left: 15px;
		padding-right: 15px;
	}

	.trf-ls-theme .trf-field-col--with-action {
		display: flex;
		align-items: flex-start;
		gap: 0.25rem;
		width: 100%;
		max-width: 100%;
	}

	.trf-ls-theme .trf-field-col__main {
		flex: 1 1 auto;
		min-width: 0;
	}

	.trf-ls-theme .trf-field-col__action {
		flex: 0 0 auto;
		align-self: flex-start;
		margin-top: 1.35rem;
		padding: 0.08rem 0.28rem;
		line-height: 1;
		font-size: 0.68rem;
		border-radius: 4px;
	}

	@media (max-width: 767.98px) {
		.trf-ls-theme .rv-trf-catalog-row,
		.trf-ls-theme .rft-sample-catalog-row {
			grid-template-columns: 1fr;
		}
	}

	.trf-ls-theme .rft-sample-section-label {
		font-size: 0.72rem;
		font-weight: 700;
		letter-spacing: 0.04em;
		text-transform: uppercase;
		color: #1e3a8a;
		margin: 0.35rem 0 0.65rem;
	}

	/* —— Stepper: maroon active (page + modal) —— */
	.trf-ls-theme .rft-wizard-stepper__item.is-active .rft-wizard-stepper__index,
	.trf-ls-theme .walk-in-trf-wizard__step.is-active .walk-in-trf-wizard__step-index {
		background: var(--ls-accent, #8b1e2d) !important;
		border-color: var(--ls-accent, #8b1e2d) !important;
		color: #fff !important;
		box-shadow: 0 4px 12px color-mix(in srgb, var(--ls-accent, #8b1e2d) 35%, transparent);
	}

	.trf-ls-theme .rft-wizard-stepper__item.is-active .rft-wizard-stepper__label,
	.trf-ls-theme .walk-in-trf-wizard__step.is-active .walk-in-trf-wizard__step-label {
		color: var(--ls-ink);
		font-weight: 700;
	}

	.trf-ls-theme .rft-wizard-progress__bar,
	.trf-ls-theme .walk-in-trf-wizard__progress-bar,
	.trf-ls-theme .walk-in-trf-wizard__track-fill {
		background: linear-gradient(90deg, var(--ls-accent, #8b1e2d) 0%, #059669 100%) !important;
	}

	.trf-ls-theme .rft-wizard-stepper__list--spread::before {
		background: #e2e8f0;
	}

	.trf-ls-theme .rft-wizard-stepper__item.is-complete .rft-wizard-stepper__index,
	.trf-ls-theme .walk-in-trf-wizard__step.is-complete .walk-in-trf-wizard__step-index {
		background: var(--ls-accent, #8b1e2d) !important;
		border-color: var(--ls-accent, #8b1e2d) !important;
		color: #fff !important;
		box-shadow: 0 2px 8px color-mix(in srgb, var(--ls-accent, #8b1e2d) 28%, transparent);
	}

	.trf-ls-theme .rft-wizard-stepper__item.is-complete .rft-wizard-stepper__index .mdi,
	.trf-ls-theme .walk-in-trf-wizard__step.is-complete .walk-in-trf-wizard__step-index .mdi {
		color: #fff !important;
	}

	.trf-ls-theme .rft-wizard-stepper__item.is-complete .rft-wizard-stepper__label,
	.trf-ls-theme .walk-in-trf-wizard__step.is-complete .walk-in-trf-wizard__step-label {
		color: var(--ls-accent, #8b1e2d) !important;
		font-weight: 600;
	}

	.trf-ls-theme .walk-in-trf-wizard__step.is-done .walk-in-trf-wizard__step-index {
		background: var(--ls-accent, #8b1e2d) !important;
		border-color: var(--ls-accent, #8b1e2d) !important;
		color: #fff !important;
		box-shadow: 0 2px 8px color-mix(in srgb, var(--ls-accent, #8b1e2d) 28%, transparent);
	}

	.trf-ls-theme .walk-in-trf-wizard__step.is-done .walk-in-trf-wizard__step-index .mdi {
		color: #fff !important;
	}

	.trf-ls-theme .walk-in-trf-wizard__step.is-done .walk-in-trf-wizard__step-label {
		color: var(--ls-accent, #8b1e2d) !important;
		font-weight: 600;
	}

	/* —— Plus buttons next to CRM labels —— */
	.trf-ls-theme .btn-outline-primary.btn-xs {
		color: var(--ls-accent, #8b1e2d);
		border-color: color-mix(in srgb, var(--ls-accent, #8b1e2d) 45%, #e2e8f0);
	}

	.trf-ls-theme .btn-outline-primary.btn-xs:hover {
		background: color-mix(in srgb, var(--ls-accent, #8b1e2d) 8%, #fff);
		color: var(--ls-accent, #8b1e2d);
		border-color: var(--ls-accent, #8b1e2d);
	}

	/* Primary continue stays accent */
	.trf-ls-theme .btn-primary,
	.trf-ls-theme .rft-wizard-nav .btn-primary,
	.trf-ls-theme .submission-instance-actions .btn-primary,
	.trf-ls-theme .workflow-board-panel-header .btn-primary {
		background: var(--ls-accent, #8b1e2d) !important;
		border-color: var(--ls-accent, #8b1e2d) !important;
		color: #fff !important;
	}

	.trf-ls-theme .btn-primary:hover,
	.trf-ls-theme .rft-wizard-nav .btn-primary:hover,
	.trf-ls-theme .submission-instance-actions .btn-primary:hover {
		filter: brightness(0.92);
	}

	/* Compact table density unchanged when not card mode */
	.trf-ls-theme .walk-in-trf-rows-table .form-control {
		min-height: 28px;
		background: #fff !important;
		font-size: 11px;
	}

	.trf-ls-theme .rv-qty-unit-control {
		display: flex;
		align-items: stretch;
		gap: 0;
		padding: 0;
		overflow: visible;
	}
	.trf-ls-theme .rv-qty-unit-control > .ls-field__input {
		flex: 1 1 auto;
		min-width: 0;
		width: calc(100% + 4px);
		max-width: calc(100% + 4px);
		border: 0 !important;
		border-radius: 0;
		box-shadow: none !important;
	}
	.trf-ls-theme .rv-qty-unit-select2 {
		flex: 0 0 calc(7.5rem - 4px);
		max-width: calc(42% - 4px);
		display: flex;
		align-items: stretch;
		padding: 0 !important;
		margin: 0 !important;
		gap: 0 !important;
		border-left: 1px solid var(--ls-border, #e2e8f0);
		background: #f8fafc;
	}
	.trf-ls-theme .rv-qty-unit-select2 .select2-container {
		width: 100% !important;
		align-self: stretch;
		margin: 0 !important;
	}
	.trf-ls-theme .rv-qty-unit-select2 .select2-container--default .select2-selection--single {
		height: 100% !important;
		min-height: 32px !important;
		border: 0 !important;
		border-radius: 0 !important;
		background: transparent !important;
		display: flex;
		align-items: center;
		box-shadow: none !important;
	}
	.trf-ls-theme .rv-qty-unit-select2 .select2-container--default .select2-selection--single .select2-selection__rendered {
		line-height: 1.2 !important;
		padding-left: 0.45rem !important;
		padding-right: 1.4rem !important;
		font-size: 0.78rem !important;
		color: var(--ls-ink, #1e293b) !important;
	}
	.trf-ls-theme .rv-qty-unit-select2 .select2-container--default .select2-selection--single .select2-selection__arrow {
		height: 100% !important;
		top: 0 !important;
		right: 0.15rem !important;
	}

	.trf-ls-theme .rv-qty-unit-control .select2-dropdown.ls-select2-dropdown-search {
		min-width: 12rem;
	}

	.trf-ls-theme .rft-sample-field > label {
		font-size: 0.75rem;
		font-weight: 600;
		color: var(--ls-ink, #1e293b);
		margin-bottom: 0.35rem;
		display: block;
	}

	.trf-ls-theme .ls-select2-multi .select2-selection--multiple {
		min-height: 38px;
		border-radius: 8px;
		background: #eff6ff !important;
		border-color: #e2e8f0 !important;
		cursor: pointer;
	}
	/* Empty multi-select collapses to a thin line in ls-ui-kit — keep fill TRF controls visible */
	.trf-ls-theme .ls-select2-multi .select2-container--default .select2-selection--multiple {
		min-height: 38px !important;
	}
	.trf-ls-theme .ls-select2-multi .select2-container--default .select2-selection--multiple .select2-selection__rendered {
		min-height: 34px !important;
		padding: 0.35rem 0.5rem !important;
		align-items: center !important;
	}
	.trf-ls-theme .ls-select2-multi .select2-container--default .select2-selection--multiple .select2-selection__placeholder {
		color: #94a3b8;
		font-size: 0.8125rem;
		line-height: 1.4;
		padding: 0.15rem 0;
	}
	.trf-ls-theme .ls-select2-multi .select2-container {
		width: 100% !important;
	}
	.trf-ls-theme select.walk-in-ls-select2--sample-type + .select2-container .select2-selection--single {
		min-height: 38px !important;
		border-radius: 8px !important;
		background: #eff6ff !important;
		border-color: #e2e8f0 !important;
	}
	.trf-ls-theme select.walk-in-ls-select2--sample-type + .select2-container .select2-selection--single .select2-selection__rendered {
		line-height: 36px !important;
		padding-left: 0.65rem !important;
		color: #0f172a !important;
		font-size: 0.8125rem !important;
	}
	.trf-ls-theme select.walk-in-ls-select2--sample-type + .select2-container .select2-selection--single .select2-selection__placeholder {
		color: #94a3b8 !important;
	}
	.trf-ls-theme .rft-sample-field--full .ls-select2-multi,
	.trf-ls-theme .rft-sample-field--full .ls-field {
		width: 100%;
	}
</style>
