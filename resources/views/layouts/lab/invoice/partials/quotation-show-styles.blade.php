<style>
	.quotation-show-page {
		--quote-accent: #8b1538;
		--quote-accent-hover: #6f102d;
	}

	.quotation-show-page .ls-quotation-header-card {
		border-radius: 14px;
		overflow: hidden;
	}

	.quotation-show-page .ls-quotation-header-card .card-body {
		background: #fff;
	}

	.quotation-show-page .ls-form-panel {
		background: #fff;
		border: 1px solid #e2e8f0;
		border-radius: 12px;
		padding: 1rem 1.1rem;
		box-shadow: 0 1px 2px rgb(15 23 42 / 0.04);
	}

	.quotation-show-page .ls-form-panel__title {
		margin: 0 0 0.85rem;
		font-size: 0.8125rem;
		font-weight: 700;
		color: #1e293b;
	}

	.quotation-show-page .ls-form-panel__title .mdi {
		color: var(--quote-accent);
	}

	.quotation-show-page .ls-field {
		margin-bottom: 0;
		min-width: 0;
	}

	.quotation-show-page .ls-search-basic[data-ls-disable-success="1"].is-success .ls-field__control {
		border-color: var(--ls-border, #e2e8f0) !important;
		box-shadow: none !important;
		background: #fff !important;
	}

	.quotation-show-page .ls-select2-multi .select2-container,
	.quotation-show-page .select2-container.select2-container--open:not(.select2) {
		border: 0 !important;
		box-shadow: none !important;
		background: transparent !important;
		padding: 0 !important;
	}

	.quotation-show-page .ls-select2-multi .select2-container--default .select2-selection--multiple,
	.quotation-show-page .ls-select2-multi .select2-container--default .select2-selection--single {
		min-height: 38px !important;
		height: auto !important;
		border: 1px solid var(--ls-border, #e2e8f0) !important;
		border-radius: var(--ls-radius, 8px) !important;
		background: #fff !important;
		padding: 0 !important;
		box-shadow: none !important;
	}

	.quotation-show-page .ls-select2-multi .select2-selection__rendered {
		min-height: 0 !important;
		padding: 0.25rem 0.4rem !important;
	}

	.quotation-show-page .ls-select2-multi .select2-container--default .select2-selection--single .select2-selection__rendered {
		line-height: 36px !important;
		padding-left: 0.55rem !important;
		padding-right: 1.75rem !important;
		font-size: 0.8125rem !important;
	}

	.quotation-show-page .btn-quotation-primary {
		background: var(--quote-accent);
		border-color: var(--quote-accent);
		color: #fff;
		border-radius: 10px;
		font-weight: 600;
		padding: 0.45rem 1.1rem;
	}

	.quotation-show-page .btn-quotation-primary:hover {
		background: var(--quote-accent-hover);
		border-color: var(--quote-accent-hover);
		color: #fff;
	}

	.quotation-show-page .ls-quotation-meta-chip {
		display: inline-flex;
		align-items: center;
		gap: 0.3rem;
		padding: 0.2rem 0.65rem;
		border-radius: 999px;
		font-size: 0.72rem;
		font-weight: 600;
		border: 1px solid #e2e8f0;
		background: #f8fafc;
		color: #334155;
	}

	.quotation-show-page .ls-quotation-meta-chip--ok {
		background: #ecfdf5;
		border-color: #a7f3d0;
		color: #047857;
	}

	.quotation-show-page .ls-quotation-meta-chip--warn {
		background: #eff6ff;
		border-color: #bfdbfe;
		color: #1d4ed8;
	}

	.quotation-show-page .ls-quotation-meta-chip--danger {
		background: #fef2f2;
		border-color: #fecaca;
		color: #b91c1c;
	}

	.quotation-show-page .ls-quotation-lines-panel {
		background: #fff;
		border: 1px solid #e2e8f0;
		border-radius: 12px;
		padding: 1rem 1.1rem 1.25rem;
		box-shadow: 0 1px 2px rgb(15 23 42 / 0.04);
	}

	.quotation-show-page .ls-quotation-lines-panel__title {
		margin: 0 0 0.85rem;
		font-size: 0.9rem;
		font-weight: 700;
		color: #1e293b;
	}

	.quotation-show-page .ls-quotation-lines-panel__title .mdi {
		color: var(--quote-accent);
	}

	.quotation-show-page .my-small-text {
		font-size: 13px !important;
	}

	.quotation-show-page .quotation-lines-toolbar {
		min-height: 38px;
		position: relative;
		z-index: 2;
	}

	.quotation-show-page .removeThis {
		z-index: 12;
		position: absolute;
		cursor: pointer;
		top: 0px;
		right: 2px;
		padding: 1px 4px;
		font-size: 12px;
		background-color: #dc2626;
		border-radius: 50%;
		color: #fff;
		box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.08);
	}

	/* Left header rail */
	.quotation-show-page .ls-quotation-workspace {
		align-items: flex-start;
	}

	.quotation-show-page .ls-quotation-rail {
		position: sticky;
		top: 0.75rem;
	}

	.quotation-show-page .ls-quotation-rail__card {
		background: #fff;
		border: 1px solid #e2e8f0;
		border-radius: 14px;
		box-shadow: 0 1px 2px rgb(15 23 42 / 0.04);
		display: flex;
		flex-direction: column;
	}

	.quotation-show-page .ls-quotation-rail__head {
		padding: 1rem 1.1rem 0.85rem;
		background:
			linear-gradient(180deg, rgb(139 21 56 / 0.06) 0%, transparent 100%),
			#fff;
		border-bottom: 1px solid #eef2f7;
	}

	.quotation-show-page .ls-quotation-rail__eyebrow {
		font-size: 0.65rem;
		font-weight: 700;
		letter-spacing: 0.08em;
		text-transform: uppercase;
		color: var(--quote-accent);
		margin-bottom: 0.2rem;
	}

	.quotation-show-page .ls-quotation-rail__title {
		margin: 0;
		font-size: 1.05rem;
		font-weight: 700;
		color: #0f172a;
		line-height: 1.25;
	}

	.quotation-show-page .ls-quotation-rail__subtitle {
		margin: 0.2rem 0 0;
		font-size: 0.75rem;
		color: #64748b;
	}

	.quotation-show-page .ls-quotation-rail__section {
		padding: 0.9rem 1.1rem;
		border-bottom: 1px solid #f1f5f9;
	}

	.quotation-show-page .ls-quotation-rail__section:last-of-type {
		border-bottom: 0;
	}

	.quotation-show-page .ls-quotation-rail__section-title {
		margin: 0 0 0.7rem;
		font-size: 0.72rem;
		font-weight: 700;
		letter-spacing: 0.03em;
		text-transform: uppercase;
		color: #475569;
		display: flex;
		align-items: center;
		gap: 0.35rem;
	}

	.quotation-show-page .ls-quotation-rail__section-title .mdi {
		color: var(--quote-accent);
		font-size: 0.95rem;
	}

	.quotation-show-page .ls-quotation-rail__stack {
		display: flex;
		flex-direction: column;
		gap: 0.7rem;
	}

	.quotation-show-page .ls-quotation-rail__stack .ls-field,
	.quotation-show-page .ls-quotation-rail__stack .ls-select2-multi {
		margin-bottom: 0;
		width: 100%;
		min-width: 0;
	}

	.quotation-show-page .ls-quotation-rail__footer {
		padding: 0.85rem 1.1rem 1.1rem;
		background: #f8fafc;
		border-top: 1px solid #eef2f7;
	}

	.quotation-show-page .ls-quotation-rail__footer .btn-block {
		width: 100%;
		display: block;
	}

	.quotation-show-page .ls-quotation-rail .ls-field__hint,
	.quotation-show-page .ls-quotation-rail .ls-select2-multi__hint {
		font-size: 0.68rem;
		line-height: 1.35;
	}

	@media (max-width: 991.98px) {
		.quotation-show-page .ls-quotation-rail {
			position: static;
		}
	}

	@media (max-width: 767.98px) {
		.quotation-show-page .ls-form-grid--3 {
			grid-template-columns: 1fr;
		}
	}

	/* Phase C — workflow modals */
	.ls-quotation-workflow-modal .modal-content {
		border: 0;
		border-radius: 14px;
		overflow: hidden;
		box-shadow: 0 24px 48px rgba(15, 23, 42, 0.18);
	}

	.ls-quotation-workflow-modal .modal-header {
		background: linear-gradient(135deg, #8b1538 0%, #a61d45 100%);
		color: #fff;
		border: 0;
		padding: 1rem 1.25rem;
	}

	.ls-quotation-workflow-modal .modal-header .modal-title {
		font-size: 1rem;
		font-weight: 700;
		margin: 0;
	}

	.ls-quotation-workflow-modal .modal-header .close {
		color: #fff;
		opacity: 0.9;
		text-shadow: none;
	}

	.ls-quotation-workflow-modal .modal-body {
		padding: 1.1rem 1.25rem;
		background: #f8fafc;
	}

	.ls-quote-params-modal .modal-footer {
		border-top: 1px solid #e2e8f0;
		background: #fff;
		padding: 0.75rem 1.05rem;
		gap: 0.5rem;
		box-shadow: 0 -8px 20px rgb(15 23 42 / 0.04);
	}

	.ls-quotation-workflow-modal .btn-quotation-primary {
		background: #8b1538;
		border-color: #8b1538;
		color: #fff;
		border-radius: 10px;
		font-weight: 600;
	}

	.ls-quotation-workflow-modal .btn-quotation-primary:hover {
		background: #6f102d;
		border-color: #6f102d;
		color: #fff;
	}

	.ls-quotation-workflow-modal .btn-outline-secondary {
		border-radius: 10px;
		font-weight: 600;
	}

	.ls-quotation-table-scroll {
		overflow-x: auto;
		border: 1px solid #e2e8f0;
		border-radius: 10px;
	}

	.quotation-show-page .ls-quotation-lines-panel .ls-table thead th {
		background: #f8fafc;
		border-bottom: 1px solid #e2e8f0;
		font-size: 0.72rem;
		text-transform: uppercase;
		letter-spacing: 0.02em;
		color: #64748b;
		white-space: nowrap;
	}

	/* Analysis line cells */
	.quotation-show-page .quotation-analysis-lines .ls-field__control {
		min-height: 34px;
	}

	.quotation-show-page .quotation-analysis-lines .ls-select2-single-wrap > .mdi-magnify {
		font-size: 0.85rem;
	}

	.quotation-show-page .quotation-analysis-lines .ls-select2-single .select2-container,
	.quotation-show-page .quotation-analysis-lines .ls-select2-multi .select2-container {
		border: 0 !important;
		box-shadow: none !important;
		background: transparent !important;
		padding: 0 !important;
	}

	.quotation-show-page .quotation-analysis-lines .ls-select2-single .select2-container--default .select2-selection--single,
	.quotation-show-page .quotation-analysis-lines .ls-select2-multi .select2-container--default .select2-selection--multiple {
		min-height: 34px !important;
		border: 1px solid var(--ls-border, #e2e8f0) !important;
		border-radius: var(--ls-radius, 8px) !important;
		background: #fff !important;
	}

	.quotation-show-page .ls-quote-line-actions {
		display: flex;
		align-items: center;
		gap: 0.25rem;
		white-space: nowrap;
	}

	.quotation-show-page .ls-quote-line-no {
		font-size: 0.75rem;
		font-weight: 700;
		color: #64748b;
		min-width: 1rem;
	}

	.quotation-show-page .ls-quote-icon-btn {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 1.65rem;
		height: 1.65rem;
		padding: 0;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		background: #fff;
		color: #475569;
		cursor: pointer;
		line-height: 1;
	}

	.quotation-show-page .ls-quote-icon-btn:hover {
		border-color: #cbd5e1;
		color: var(--quote-accent);
		background: #fff7f9;
	}

	.quotation-show-page .ls-quote-icon-btn--danger {
		color: #b91c1c;
	}

	.quotation-show-page .ls-quote-icon-btn--danger:hover {
		background: #fef2f2;
		border-color: #fecaca;
		color: #991b1b;
	}

	.quotation-show-page .ls-quote-params {
		background: #f8fafc;
		border: 1px solid #e2e8f0;
		border-radius: 10px;
		padding: 0.45rem 0.55rem;
		min-height: 2.5rem;
	}

	.quotation-show-page .ls-quote-params--empty {
		display: flex;
		align-items: center;
		justify-content: center;
		background: #fff;
	}

	.quotation-show-page .ls-quote-params__toolbar {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 0.5rem;
		margin-bottom: 0.35rem;
	}

	.quotation-show-page .ls-quote-params__title {
		appearance: none;
		border: 0;
		background: transparent;
		padding: 0;
		margin: 0;
		font-size: 0.72rem;
		font-weight: 700;
		color: #334155;
		line-height: 1.3;
		display: inline-flex;
		align-items: center;
		gap: 0.4rem;
		text-align: left;
		cursor: pointer;
	}

	.quotation-show-page .ls-quote-params__title:hover {
		color: #0f172a;
	}

	.quotation-show-page .ls-quote-params__count {
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

	.quotation-show-page .ls-quote-params.is-collapsed .ls-quote-params__chips {
		display: none;
	}

	.quotation-show-page .ls-quote-params__edit {
		padding: 0.15rem 0.45rem !important;
		font-size: 0.7rem !important;
		flex-shrink: 0;
	}

	.quotation-show-page .ls-quote-params__chips {
		gap: 0.3rem;
	}

	.quotation-show-page .ls-quote-param-chip {
		max-width: 100%;
	}

	.quotation-show-page .ls-quote-param-chip .mdi {
		font-size: 0.7rem;
		margin-left: 0.15rem;
	}

	.quotation-show-page .ls-quote-param-chip--acc {
		background: color-mix(in srgb, #059669 12%, #fff);
		border-color: color-mix(in srgb, #059669 30%, #e2e8f0);
		color: #047857;
	}

	.quotation-show-page .ls-quote-param-chip--sub {
		border-style: dashed;
	}

	.quotation-show-page .ls-quote-tax-display {
		display: inline-flex;
		align-items: center;
		min-height: 34px;
		padding: 0 0.55rem;
		font-size: 0.8125rem;
		font-weight: 600;
		color: #334155;
		background: #f8fafc;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
	}

	.ls-quotation-workflow-modal .ls-quote-analyte-table {
		flex: 1 1 auto;
		min-height: 0;
		max-height: none;
		overflow: auto;
		border-radius: 12px;
		border: 1px solid #dbe3ef;
		background:
			linear-gradient(180deg, rgb(255 255 255 / 0.92), rgb(248 250 252 / 0.98)),
			radial-gradient(120% 80% at 0% 0%, rgb(14 165 233 / 0.06), transparent 55%);
	}

	.ls-quotation-workflow-modal .ls-quote-analyte-group td {
		background: linear-gradient(90deg, #eff6ff 0%, #dbeafe 55%, #e0f2fe 100%);
		font-size: 0.68rem;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.08em;
		color: #1e3a8a;
		padding: 0.55rem 0.85rem !important;
		border: 0 !important;
		border-top: 1px solid #bfdbfe !important;
		border-bottom: 1px solid #bfdbfe !important;
	}

	.ls-quotation-workflow-modal .description-analytes.ls-quote-params,
	.ls-quotation-workflow-modal .ls-quote-params__chips-host {
		min-height: 3rem;
	}

	/* Fixed layout is required — auto table layout ignored th/td widths when Select2 content was wider. */
	.quotation-show-page .ls-quote-analysis-table {
		table-layout: fixed;
		width: 100%;
		min-width: 1280px;
	}

	.quotation-show-page .ls-quote-analysis-table .ls-quote-col--sample,
	.quotation-show-page .ls-quote-analysis-table th:nth-child(2),
	.quotation-show-page .ls-quote-analysis-table td:nth-child(2) {
		width: 22% !important;
	}

	.quotation-show-page .ls-quote-analysis-table .ls-quote-col--params,
	.quotation-show-page .ls-quote-analysis-table th:nth-child(3),
	.quotation-show-page .ls-quote-analysis-table td:nth-child(3) {
		width: 42% !important;
	}

	.quotation-show-page .ls-quote-analysis-table .ls-quote-col--price,
	.quotation-show-page .ls-quote-analysis-table th:nth-child(6),
	.quotation-show-page .ls-quote-analysis-table td:nth-child(6) {
		width: 12% !important;
	}

	.quotation-show-page .ls-quote-analysis-table .ls-quote-col--tax,
	.quotation-show-page .ls-quote-analysis-table th:nth-child(8),
	.quotation-show-page .ls-quote-analysis-table td:nth-child(8) {
		width: 9% !important;
	}

	.quotation-show-page .ls-quote-analysis-table td:nth-child(2),
	.quotation-show-page .ls-quote-analysis-table td:nth-child(3),
	.quotation-show-page .ls-quote-analysis-table td:nth-child(6) {
		overflow: hidden;
	}

	.quotation-show-page .ls-quote-analysis-table .select2-container {
		width: 100% !important;
		max-width: 100% !important;
	}

	.quotation-show-page .ls-quote-analysis-table td:nth-child(6) .ls-field__control {
		position: relative;
		padding-right: 1.45rem;
	}

	.quotation-show-page .ls-quote-analysis-table td:nth-child(6) .ls-field__input,
	.quotation-show-page .ls-quote-analysis-table td:nth-child(6) .quotation-unit-price {
		padding-left: 0.25rem;
		padding-right: 0.25rem;
		font-size: 0.68rem;
		line-height: 1.2;
	}

	.quotation-show-page .ls-quote-analysis-table td:nth-child(6) .ls-field__hint,
	.quotation-show-page .ls-quote-analysis-table td:nth-child(6) .quotation-price-hint {
		font-size: 0.58rem;
		line-height: 1.2;
		margin-top: 0.15rem;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}

	.quotation-show-page .ls-quote-analysis-table td:nth-child(6) .ls-field__action-btn {
		position: absolute;
		right: 2px;
		top: 50%;
		transform: translateY(-50%);
		padding: 0 0.25rem;
		font-size: 0.6rem;
		min-width: 0;
		height: 1.25rem;
		line-height: 1;
	}

	.quotation-show-page .ls-quote-analysis-table td:nth-child(8) .ls-quote-tax-display {
		font-size: 0.72rem;
		min-height: 32px;
		padding: 0 0.35rem;
	}

	/* Parameters modal — instrument-console grid */
	.ls-quote-params-modal.modal {
		z-index: 2200 !important;
	}

	.ls-quote-params-modal .modal-dialog {
		max-width: 980px;
		width: calc(100% - 1.5rem);
		margin: 1rem auto;
		height: min(92vh, 860px);
		display: flex;
		align-items: center;
	}

	.ls-quote-params-modal .modal-content {
		display: flex;
		flex-direction: column;
		width: 100%;
		max-height: min(92vh, 860px);
		overflow: hidden;
	}

	.ls-quote-params-modal .modal-header,
	.ls-quote-params-modal .modal-footer {
		flex: 0 0 auto;
	}

	.ls-quotation-workflow-modal .ls-quote-params-modal-body {
		flex: 1 1 auto;
		min-height: 0;
		display: flex;
		flex-direction: column;
		overflow: hidden;
		background:
			radial-gradient(90% 70% at 100% 0%, rgb(139 21 56 / 0.08), transparent 50%),
			radial-gradient(70% 60% at 0% 100%, rgb(14 165 233 / 0.07), transparent 45%),
			#eef2f7;
		padding: 0.9rem 1.05rem 0;
	}

	.ls-quote-params-shell {
		display: flex;
		flex-direction: column;
		gap: 0.65rem;
		flex: 1 1 auto;
		min-height: 0;
		height: 100%;
	}

	.ls-quote-params-hero,
	.ls-quote-params-toolbar,
	.ls-quote-params-dock {
		flex: 0 0 auto;
	}

	.ls-quote-params-hero {
		display: flex;
		align-items: flex-start;
		justify-content: space-between;
		gap: 1rem;
		padding: 0.85rem 1rem;
		border-radius: 14px;
		border: 1px solid #bfdbfe;
		background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 55%, #e0f2fe 100%);
		color: #0f172a;
		box-shadow: 0 8px 20px rgb(59 130 246 / 0.08);
	}

	.ls-quote-params-hero__eyebrow {
		margin: 0 0 0.2rem;
		font-size: 0.65rem;
		font-weight: 700;
		letter-spacing: 0.12em;
		text-transform: uppercase;
		color: #3b82f6;
	}

	.ls-quote-params-hero__title {
		margin: 0 0 0.25rem;
		font-size: 1.05rem;
		font-weight: 700;
		letter-spacing: -0.01em;
		color: #1e3a8a;
	}

	.ls-quote-params-hero__caption {
		margin: 0;
		max-width: 36rem;
		font-size: 0.8rem;
		line-height: 1.45;
		color: #475569;
	}

	.ls-quote-params-legend {
		display: flex;
		flex-wrap: wrap;
		gap: 0.4rem;
		justify-content: flex-end;
		padding-top: 0.15rem;
	}

	.ls-quote-params-legend__chip {
		display: inline-flex;
		align-items: center;
		padding: 0.28rem 0.55rem;
		border-radius: 999px;
		font-size: 0.65rem;
		font-weight: 700;
		letter-spacing: 0.04em;
		text-transform: uppercase;
		border: 1px solid transparent;
		background: #fff;
	}

	.ls-quote-params-legend__chip--acc {
		background: #ecfdf5;
		border-color: #a7f3d0;
		color: #047857;
	}

	.ls-quote-params-legend__chip--sub {
		background: #f0f9ff;
		border-color: #bae6fd;
		color: #0369a1;
	}

	.ls-quote-params-toolbar {
		display: flex;
		flex-wrap: wrap;
		gap: 0.45rem;
		padding: 0.45rem;
		border-radius: 12px;
		background: rgb(255 255 255 / 0.78);
		border: 1px solid #e2e8f0;
		backdrop-filter: blur(8px);
	}

	.ls-quote-params-switch {
		display: inline-flex;
		align-items: center;
		gap: 0.45rem;
		margin: 0;
		padding: 0.35rem 0.65rem;
		border-radius: 999px;
		border: 1px solid #e2e8f0;
		background: #fff;
		cursor: pointer;
		user-select: none;
		transition: border-color 0.15s ease, box-shadow 0.15s ease;
	}

	.ls-quote-params-switch:hover {
		border-color: #cbd5e1;
		box-shadow: 0 0 0 3px rgb(148 163 184 / 0.12);
	}

	.ls-quote-params-switch__input {
		position: absolute;
		opacity: 0;
		pointer-events: none;
	}

	.ls-quote-params-switch__ui {
		width: 1.7rem;
		height: 0.95rem;
		border-radius: 999px;
		background: #cbd5e1;
		position: relative;
		flex-shrink: 0;
		transition: background 0.15s ease;
	}

	.ls-quote-params-switch__ui::after {
		content: '';
		position: absolute;
		top: 2px;
		left: 2px;
		width: 0.7rem;
		height: 0.7rem;
		border-radius: 50%;
		background: #fff;
		box-shadow: 0 1px 2px rgb(15 23 42 / 0.2);
		transition: transform 0.15s ease;
	}

	.ls-quote-params-switch__input:checked + .ls-quote-params-switch__ui {
		background: #8b1538;
	}

	.ls-quote-params-switch__input:checked + .ls-quote-params-switch__ui::after {
		transform: translateX(0.72rem);
	}

	.ls-quote-params-switch__label {
		font-size: 0.72rem;
		font-weight: 600;
		color: #334155;
		white-space: nowrap;
	}

	.ls-quote-params-stage {
		position: relative;
		flex: 1 1 auto;
		min-height: 220px;
		display: flex;
		flex-direction: column;
		overflow: hidden;
	}

	.ls-quote-params-stage > .ls-quote-analyte-table,
	.ls-quote-params-stage > .ls-quote-params-skeleton,
	.ls-quote-params-stage > .ls-quote-params-empty {
		flex: 1 1 auto;
		min-height: 180px;
		width: 100%;
	}

	.ls-quote-params-stage > .ls-quote-analyte-table:not([hidden]) {
		display: block;
		overflow: auto;
	}

	.ls-quote-params-stage::after {
		content: '';
		pointer-events: none;
		position: absolute;
		left: 0;
		right: 0;
		bottom: 0;
		height: 1.25rem;
		background: linear-gradient(180deg, transparent, rgb(238 242 247 / 0.95));
		border-radius: 0 0 12px 12px;
		z-index: 3;
	}

	.ls-quote-params-dock {
		margin: 0 -1.05rem;
		padding: 0.65rem 1.05rem;
		border-top: 1px solid #e2e8f0;
		background: #fff;
	}

	.ls-quote-params-foot {
		margin: 0;
		font-size: 0.72rem;
		color: #64748b;
	}

	.ls-quote-params-empty {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		text-align: center;
		padding: 2rem 1.5rem 1.75rem;
		min-height: 220px;
		flex: 1 1 auto;
		border-radius: 14px;
		border: 1px dashed #fda4af;
		background:
			radial-gradient(circle at 50% 20%, rgb(244 63 94 / 0.08), transparent 55%),
			#fff;
	}

	.ls-quote-params-empty[hidden],
	.ls-quote-params-skeleton[hidden],
	.ls-quote-analyte-table[hidden] {
		display: none !important;
	}

	.ls-quote-params-empty__art {
		margin-bottom: 0.85rem;
		animation: ls-quote-empty-bob 2.8s ease-in-out infinite;
	}

	.ls-quote-params-empty__title {
		margin: 0 0 0.45rem;
		font-size: 1.05rem;
		font-weight: 700;
		color: #9f1239;
	}

	.ls-quote-params-empty__text {
		margin: 0;
		max-width: 28rem;
		font-size: 0.875rem;
		line-height: 1.45;
		color: #64748b;
	}

	@keyframes ls-quote-empty-bob {
		0%, 100% { transform: translateY(0); }
		50% { transform: translateY(-6px); }
	}

	.ls-quote-params-skeleton {
		display: flex;
		flex-direction: column;
		gap: 0.55rem;
		padding: 0.85rem;
		min-height: 220px;
		flex: 1 1 auto;
		overflow: auto;
		border-radius: 14px;
		border: 1px solid #e2e8f0;
		background: #fff;
	}

	.ls-quote-params-skeleton__row {
		display: grid;
		grid-template-columns: 1.6rem minmax(0, 1fr) 2.4rem 2.4rem 4rem 3.2rem 4.2rem;
		gap: 0.55rem;
		align-items: center;
		padding: 0.45rem 0.35rem;
	}

	.ls-quote-params-skeleton .ls-skeleton {
		display: block;
		height: 0.7rem;
		border-radius: 999px;
		background: linear-gradient(90deg, #e2e8f0 0%, #f1f5f9 45%, #e2e8f0 100%);
		background-size: 200% 100%;
		animation: ls-quote-skel-shine 1.25s ease-in-out infinite;
	}

	.ls-quote-params-skeleton__chk { width: 0.95rem; height: 0.95rem !important; border-radius: 4px !important; }
	.ls-quote-params-skeleton__name { width: 72%; }
	.ls-quote-params-skeleton__flag { width: 1rem; height: 1rem !important; border-radius: 4px !important; justify-self: center; }
	.ls-quote-params-skeleton__loq { width: 100%; }
	.ls-quote-params-skeleton__mu { width: 70%; justify-self: center; }
	.ls-quote-params-skeleton__tat { width: 85%; justify-self: center; }

	@keyframes ls-quote-skel-shine {
		0% { background-position: 100% 0; }
		100% { background-position: -100% 0; }
	}

	.ls-quote-analyte-grid {
		table-layout: fixed;
		width: 100%;
		margin: 0;
		border-collapse: separate;
		border-spacing: 0;
	}

	.ls-quote-analyte-col--select { width: 3.1rem; }
	.ls-quote-analyte-col--name { width: auto; }
	.ls-quote-analyte-col--acc { width: 4.1rem; }
	.ls-quote-analyte-col--sub { width: 4.1rem; }
	.ls-quote-analyte-col--loq { width: 4.4rem; }
	.ls-quote-analyte-col--mu { width: 4.2rem; }
	.ls-quote-analyte-col--tat { width: 6.2rem; }

	.ls-quote-analyte-grid thead th {
		position: sticky;
		top: 0;
		z-index: 2;
		padding: 0.65rem 0.55rem;
		font-size: 0.62rem;
		font-weight: 700;
		letter-spacing: 0.08em;
		text-transform: uppercase;
		color: #64748b;
		background: rgb(248 250 252 / 0.96);
		border-bottom: 1px solid #e2e8f0;
		backdrop-filter: blur(8px);
		white-space: nowrap;
	}

	.ls-quote-analyte-grid tbody td {
		padding: 0.55rem 0.5rem;
		border-bottom: 1px solid #eef2f7;
		vertical-align: middle;
		background: transparent;
	}

	.ls-quote-analyte-row:hover td {
		background: rgb(241 245 249 / 0.65);
	}

	.ls-quote-analyte-row__select,
	.ls-quote-analyte-row__flag,
	.ls-quote-analyte-row__mu,
	.ls-quote-analyte-row__tat,
	.ls-quote-analyte-row__loq {
		text-align: center;
	}

	.ls-quote-analyte-row__name {
		text-align: left;
		overflow: hidden;
	}

	.ls-quote-analyte-row__name-inner {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 0.5rem;
		min-width: 0;
	}

	.ls-quote-analyte-row__label {
		display: block;
		font-size: 0.8125rem;
		font-weight: 600;
		color: #0f172a;
		line-height: 1.3;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		min-width: 0;
	}

	.ls-quote-analyte-row__meta {
		display: inline-flex;
		flex-wrap: wrap;
		justify-content: flex-end;
		gap: 0.25rem;
		flex-shrink: 0;
		max-width: 55%;
	}

	.ls-quote-meta-pill {
		display: inline-flex;
		align-items: center;
		max-width: 9rem;
		padding: 0.1rem 0.4rem;
		border-radius: 999px;
		border: 1px solid #e2e8f0;
		background: #f8fafc;
		color: #475569;
		font-size: 0.62rem;
		font-weight: 600;
		line-height: 1.2;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}

	.ls-quote-meta-pill--lab {
		background: #eff6ff;
		border-color: #bfdbfe;
		color: #1d4ed8;
	}

	.ls-quote-meta-pill--method {
		background: #f0fdf4;
		border-color: #bbf7d0;
		color: #15803d;
	}

	.ls-quote-meta-pill--method.is-non-accredited {
		background: #fef2f2;
		border-color: #fecaca;
		color: #b91c1c;
	}

	.ls-quote-check {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		margin: 0;
		cursor: pointer;
	}

	.ls-quote-check input {
		position: absolute;
		opacity: 0;
		pointer-events: none;
	}

	.ls-quote-check__box {
		width: 1.05rem;
		height: 1.05rem;
		border-radius: 5px;
		border: 1.5px solid #94a3b8;
		background: #fff;
		box-shadow: inset 0 1px 0 rgb(255 255 255 / 0.8);
		transition: border-color 0.12s ease, background 0.12s ease, box-shadow 0.12s ease;
		position: relative;
	}

	.ls-quote-check__box::after {
		content: '';
		position: absolute;
		left: 3px;
		top: 1px;
		width: 5px;
		height: 8px;
		border: solid #fff;
		border-width: 0 2px 2px 0;
		transform: rotate(45deg) scale(0);
		transition: transform 0.12s ease;
	}

	.ls-quote-check input:checked + .ls-quote-check__box {
		background: #8b1538;
		border-color: #8b1538;
		box-shadow: 0 0 0 3px rgb(139 21 56 / 0.16);
	}

	.ls-quote-check input:checked + .ls-quote-check__box::after {
		transform: rotate(45deg) scale(1);
	}

	.ls-quote-check--acc input:checked + .ls-quote-check__box {
		background: #059669;
		border-color: #059669;
		box-shadow: 0 0 0 3px rgb(5 150 105 / 0.16);
	}

	.ls-quote-check--sub input:checked + .ls-quote-check__box {
		background: #0284c7;
		border-color: #0284c7;
		box-shadow: 0 0 0 3px rgb(2 132 199 / 0.16);
	}

	.ls-quote-analyte-loq {
		width: 100%;
		max-width: 3.9rem;
		height: 1.85rem;
		margin: 0 auto;
		padding: 0 0.3rem;
		border: 1px solid #dbe3ef;
		border-radius: 8px;
		background: #fff;
		font-size: 0.68rem;
		font-weight: 600;
		color: #0f172a;
		text-align: center;
	}

	.ls-quote-analyte-loq:focus {
		outline: none;
		border-color: #38bdf8;
		box-shadow: 0 0 0 3px rgb(56 189 248 / 0.2);
	}

	.ls-quote-metric {
		display: inline-flex;
		min-width: 2.2rem;
		justify-content: center;
		font-size: 0.72rem;
		font-weight: 600;
		color: #64748b;
		font-variant-numeric: tabular-nums;
	}

	.ls-quote-tat {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-width: 3.1rem;
		padding: 0.22rem 0.45rem;
		border-radius: 999px;
		background: linear-gradient(135deg, #ecfeff, #e0f2fe);
		border: 1px solid #bae6fd;
		color: #0369a1;
		font-size: 0.72rem;
		font-weight: 700;
		font-variant-numeric: tabular-nums;
		letter-spacing: 0.02em;
	}

	@media (prefers-reduced-motion: reduce) {
		.ls-quote-params-empty__art,
		.ls-quote-params-skeleton .ls-skeleton {
			animation: none;
		}
	}

	@media (max-width: 767.98px) {
		.ls-quote-params-hero {
			flex-direction: column;
		}

		.ls-quote-params-legend {
			justify-content: flex-start;
		}

		.ls-quote-analyte-col--loq { width: 4.6rem; }
		.ls-quote-analyte-col--tat { width: 4.8rem; }
	}
</style>
