<div class="container-fluid workflow-board-page lab-panel-theme workflow-theme lab-surface-theme"
	data-ls-type="plex"
	x-data="{
		selectedCount: 0,
		selectedLabBatchCount: 0,
		selectedInstanceIds() {
			return Array.from(document.querySelectorAll('input[data-instance-select]:checked')).map((el) => el.value);
		},
		selectedEnquiryIds() {
			return Array.from(document.querySelectorAll('input[data-enquiry-select]:checked')).map((el) => el.value);
		},
		refreshLabBatchSelectionCount() {
			this.selectedLabBatchCount = document.querySelectorAll('input[data-lab-batch-select]:checked').length;
		},
		refreshSelectionCount() {
			this.selectedCount = document.querySelectorAll('input[data-instance-select]:checked, input[data-enquiry-select]:checked').length;
			this.refreshLabBatchSelectionCount();
		},
		onSelectionChange(event) {
			if (!event.target || !event.target.matches) {
				return;
			}

			if (event.target.matches('input[data-instance-select]') || event.target.matches('input[data-enquiry-select]')) {
				this.refreshSelectionCount();
				if (typeof window.rebuildWorkflowSelectionLists === 'function') {
					window.rebuildWorkflowSelectionLists();
				}
			}

			if (event.target.matches('input[data-lab-batch-select]')) {
				this.refreshLabBatchSelectionCount();
				if (typeof window.rebuildLabBatchPrintLabels === 'function') {
					window.rebuildLabBatchPrintLabels();
				}
				if (typeof window.rebuildEmailReportsSelection === 'function') {
					window.rebuildEmailReportsSelection();
				}
			}
		},
		init() {
			this.refreshSelectionCount();
			if (typeof window.rebuildLabBatchPrintLabels === 'function') {
				window.rebuildLabBatchPrintLabels();
			}
			if (typeof window.rebuildEmailReportsSelection === 'function') {
				window.rebuildEmailReportsSelection();
			}
			if (typeof Livewire !== 'undefined') {
				Livewire.hook('morph.updated', () => {
					this.refreshSelectionCount();
					if (typeof window.rebuildLabBatchPrintLabels === 'function') {
						window.rebuildLabBatchPrintLabels();
					}
					if (typeof window.rebuildEmailReportsSelection === 'function') {
						window.rebuildEmailReportsSelection();
					}
				});
			}
		},
	}"
	@change.window="onSelectionChange($event)">
	<button
		type="button"
		class="d-none"
		data-sf-trigger="workflow-walk-in-request"
		onclick="window.location.href=@json(route('sample-workflow.request-for-testing'))"
	></button>
@include('layouts.lab.partials.lab-panel-theme-styles')
@include('layouts.lab.partials.lab-surface-theme-styles')
<style>
	.workflow-board-header .batch-header-bar {
		margin-bottom: 0;
		overflow: visible;
	}
	.workflow-board-header .batch-header-top {
		display: flex;
		align-items: center;
		justify-content: space-between;
		flex-wrap: wrap;
		gap: 8px;
		padding-bottom: 12px;
		overflow: visible;
	}
	.workflow-board-header .batch-title-group {
		display: flex;
		align-items: center;
		gap: 10px;
		flex-wrap: wrap;
	}
	.workflow-board-header .btn-action-sm {
		height: 30px;
		padding: 0 12px;
		font-size: var(--ls-text-sm, 0.75rem);
		border-radius: 6px;
		display: inline-flex;
		align-items: center;
		gap: 5px;
		font-weight: 500;
	}
	.workflow-board-header .workflow-header-actions .btn-group .btn-action-sm {
		height: 32px;
	}

	.workflow-header-actions .workflow-actions-dropdown {
		position: relative;
		flex-shrink: 0;
	}

	.workflow-header-actions .workflow-actions-dropdown > .dropdown-menu {
		position: absolute !important;
		top: 100% !important;
		right: 0 !important;
		left: auto !important;
		transform: none !important;
		float: none;
		margin-top: 0.35rem;
		min-width: 15.5rem;
		max-width: 20rem;
		max-height: min(70vh, 520px);
		overflow-y: auto;
		padding: 0.35rem 0;
		border: 1px solid #dbe5f0;
		border-radius: 10px;
		box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
		z-index: 1050;
	}

	.workflow-header-actions .workflow-actions-dropdown .dropdown-menu > li {
		list-style: none;
		margin: 0;
		padding: 0;
	}

	.workflow-header-actions .workflow-actions-dropdown .dropdown-item {
		display: flex;
		align-items: center;
		width: 100%;
		padding: 0.5rem 0.95rem;
		font-size: 0.8125rem;
		font-weight: 500;
		line-height: 1.35;
		color: #334155;
		border: none;
		border-radius: 0;
		background: transparent;
		box-shadow: none;
		text-align: left;
		white-space: nowrap;
	}

	.workflow-header-actions .workflow-actions-dropdown .dropdown-item:hover,
	.workflow-header-actions .workflow-actions-dropdown .dropdown-item:focus {
		background: #f1f5f9;
		color: #1e293b;
	}

	.workflow-header-actions .workflow-actions-dropdown .dropdown-item.disabled,
	.workflow-header-actions .workflow-actions-dropdown .dropdown-item:disabled {
		opacity: 0.55;
		pointer-events: none;
	}

	.workflow-header-actions .workflow-actions-dropdown .dropdown-item .mdi {
		flex-shrink: 0;
		width: 1.125rem;
		margin-right: 0.5rem;
		text-align: center;
	}

	.workflow-board-header .workflow-header-actions {
		overflow: visible;
	}
	.form-summary-cell {
		display: flex;
		flex-direction: column;
		gap: 6px;
		min-width: 220px;
	}
	.form-count-badge {
		display: inline-flex;
		align-items: center;
		padding: 3px 10px;
		border-radius: 999px;
		background: var(--color-primary-soft);
		color: var(--color-primary);
		font-size: 11px;
		font-weight: 600;
		width: fit-content;
	}
	.form-chip-wrap {
		display: flex;
		flex-wrap: wrap;
		gap: 6px;
	}
	.form-chip {
		display: inline-flex;
		align-items: center;
		gap: 5px;
		padding: 2px 8px;
		border-radius: 999px;
		background: #f8fafc;
		border: 1px solid #dbe4ed;
		font-size: 11px;
		color: #475569;
	}
	.form-chip small {
		font-size: 10px;
		color: #64748b;
	}
	.form-chip-link {
		text-decoration: none;
	}
	.form-chip-link:hover {
		background: var(--color-primary-soft);
		border-color: var(--color-primary-focus);
		color: var(--color-primary);
	}
	.form-actions {
		display: flex;
		flex-wrap: wrap;
		gap: 6px;
	}
	.form-actions .btn {
		padding: 0.2rem 0.45rem;
	}
	.workflow-table .workflow-row-actions {
		gap: 4px;
	}
	.workflow-table .rm-act-btn {
		border-radius: 7px;
		padding: 4px 8px;
		margin-right: 0;
		font-size: 12px;
	}
	.workflow-table .rm-act-btn--view {
		border: 1px solid #bbf7d0;
		color: #15803d;
		background: #f0fdf4;
	}
	.workflow-table .rm-act-btn--view:hover {
		background: #dcfce7;
		border-color: #86efac;
		color: #15803d;
	}
	.workflow-table .rm-act-btn--edit {
		border: 1px solid var(--color-primary-shadow);
		color: var(--color-primary);
		background: var(--color-primary-soft-medium);
	}
	.workflow-table .rm-act-btn--edit:hover {
		background: var(--color-primary-soft-10);
		border-color: var(--color-primary-border-soft);
		color: var(--color-primary);
	}
	.workflow-table .rm-act-btn--delete {
		border: 1px solid #fecdd3;
		color: #e11d48;
		background: #fff5f7;
	}
	.workflow-table .rm-act-btn--delete:hover {
		background: #ffe4e6;
		border-color: #fda4af;
		color: #e11d48;
	}
	.workflow-table .rm-act-btn--muted {
		border: 1px solid #e2e8f0;
		color: #475569;
		background: #f8fafc;
	}
	.workflow-table .rm-act-btn--muted:hover {
		background: #f1f5f9;
		border-color: #cbd5e1;
		color: #475569;
	}
	.form-template-block {
		display: flex;
		flex-direction: column;
		gap: 8px;
	}
	.form-attachment-group {
		padding: 8px 10px;
		border: 1px solid var(--color-primary-highlight);
		border-radius: 8px;
		background: #FAF9F9;
	}
	.form-attachment-group-title {
		display: inline-flex;
		align-items: center;
		gap: 5px;
		font-size: 11px;
		font-weight: 600;
		color: var(--color-primary);
		margin-bottom: 6px;
	}
	.form-attachment-list {
		display: flex;
		flex-wrap: wrap;
		gap: 6px;
	}
	.form-row.form-attachment-row td {
		background: #fcfdff;
	}
	.workflow-stat-card {
		background: #fff;
		border: 1px solid #e9ecef;
		border-radius: 10px;
		padding: 14px;
		height: 100%;
	}
	.workflow-stat-label {
		font-size: var(--text-caption);
		color: var(--color-muted);
		font-weight: var(--font-semibold);
		text-transform: uppercase;
		letter-spacing: 0.04em;
	}
	.workflow-stat-value {
		font-size: var(--text-metric);
		font-weight: var(--font-bold);
		line-height: var(--leading-tight);
		color: var(--color-text);
	}
	.workflow-stat-meta {
		display: flex;
		align-items: center;
		justify-content: space-between;
		margin-top: 8px;
	}
	.workflow-stat-icon {
		font-size: 1.1rem;
		width: 30px;
		height: 30px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border-radius: 8px;
		background: #f1f5f9;
		color: #1e293b;
	}

	.workflow-board-page .workflow-filters-primary-row {
		align-items: flex-end;
	}
	.workflow-board-page .workflow-filters-advanced {
		border-top: 1px solid #dee2e6;
		padding-top: 0.75rem;
		margin-top: 0.5rem;
	}
	.workflow-board-page .workflow-receiving-tabs {
		display: flex;
		flex-wrap: wrap;
		gap: 6px;
		padding: calc(4px - 0.5px);
		background: #f8fafc;
		border: 1px solid #e2e8f0;
		border-radius: var(--ls-radius-lg, 8px);
	}
	.workflow-board-page .workflow-receiving-tab {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		padding: var(--ls-tab-pad-y, calc(0.5rem - 0.5px)) var(--ls-tab-pad-x, 0.85rem);
		border: 1px solid transparent;
		border-radius: var(--ls-radius-md, 6px);
		background: transparent;
		color: #475569;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif) !important;
		font-size: var(--ls-tab-font, 0.8125rem);
		font-weight: 600;
		line-height: 1.25;
		cursor: pointer;
		transition: background 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease, color 0.15s ease;
	}
	.workflow-board-page .workflow-receiving-tab:hover {
		background: #fff;
		border-color: var(--color-primary-border-soft);
		color: var(--color-primary);
	}
	.workflow-board-page .workflow-receiving-tab.is-active {
		background: #fff;
		border-color: var(--color-primary);
		color: var(--color-primary);
		box-shadow: 0 1px 3px var(--color-primary-highlight);
	}
	.workflow-board-page .workflow-receiving-tab-badge {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-width: 1.25rem;
		padding: calc(2px - 0.5px) 6px;
		border-radius: 999px;
		font-size: var(--ls-text-xs, 0.6875rem);
		font-weight: 700;
		background: #e2e8f0;
		color: #475569;
	}
	.workflow-board-page .workflow-receiving-tab.is-active .workflow-receiving-tab-badge {
		background: var(--color-primary-soft-10);
		color: var(--color-primary);
	}

	.workflow-board-page .workflow-filters-toggle {
		display: flex;
		align-items: center;
		justify-content: space-between;
		width: 100%;
		gap: 0.75rem;
		padding: 0.15rem 0;
		border: 0 !important;
		outline: none !important;
		box-shadow: none !important;
		background: transparent;
		color: inherit;
		text-align: left;
		cursor: pointer;
		-webkit-tap-highlight-color: transparent;
	}
	.workflow-board-page .workflow-filters-toggle:focus,
	.workflow-board-page .workflow-filters-toggle:focus-visible,
	.workflow-board-page .workflow-filters-toggle:active {
		outline: none !important;
		box-shadow: none !important;
		border: 0 !important;
	}
	.workflow-board-page .workflow-filters-toggle__label {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		font-size: var(--ls-text-md, 0.875rem);
		font-weight: 600;
		color: var(--color-text, #0f172a);
	}
	.workflow-board-page .workflow-filters-toggle__badge {
		display: inline-flex;
		align-items: center;
		padding: 1px 7px;
		border-radius: 999px;
		font-size: 0.6875rem;
		font-weight: 700;
		background: var(--color-primary-soft-10, #e8eefc);
		color: var(--color-primary, #3b5fc0);
	}
	.workflow-board-page .workflow-filters-panel {
		margin-top: 0.75rem;
	}
	.workflow-board-page .workflow-brand-filters-card {
		padding: 0.7rem 0.9rem;
		margin-bottom: 0.9rem;
		border-radius: 10px;
	}
	.workflow-board-page .workflow-panel-selection-actions .btn {
		padding: 0.3rem 0.7rem;
		font-size: 0.8125rem;
		line-height: 1.2;
	}
	.workflow-board-page .workflow-board-panel-header h5 {
		font-size: var(--ls-text-lg, 0.95rem);
	}
	/* Table/card density comes from lab-surface-theme tokens */

	.workflow-review-status-legend {
		display: flex;
		align-items: center;
		flex-wrap: wrap;
		gap: 14px;
		padding: 10px 12px;
		border: 1px solid #e2e8f0;
		border-radius: 10px;
		background: #f8fafc;
		font-size: 13px;
		color: #334155;
	}
	.workflow-review-status-legend .legend-label {
		font-weight: 700;
		color: #475569;
	}
	.workflow-review-status-legend .legend-item {
		display: inline-flex;
		align-items: center;
		gap: 8px;
		font-weight: 500;
	}
	.workflow-review-status-legend .legend-swatch {
		display: inline-block;
		width: 20px;
		height: 14px;
		background: #fff;
		border: 1px solid #e2e8f0;
		border-left-width: 4px;
		border-radius: 2px;
	}
	.workflow-review-status-legend .legend-swatch--awaiting-customer {
		border-left-color: #f59e0b;
	}
	.workflow-review-status-legend .legend-swatch--awaiting-manager {
		border-left-color: #7dd3fc;
	}
	.workflow-review-status-legend .legend-swatch--pending {
		border-left-color: #94a3b8;
	}
	.workflow-table tr.workflow-review-row--awaiting-customer td:first-child {
		border-left: 4px solid #f59e0b;
	}
	.workflow-table tr.workflow-review-row--awaiting-manager td:first-child {
		border-left: 4px solid #7dd3fc;
	}
	.workflow-table tr.workflow-review-row--pending td:first-child {
		border-left: 4px solid #94a3b8;
	}

	[x-cloak] {
		display: none !important;
	}

	.workflow-panel-selection-actions .btn {
		font-weight: 600;
		border-radius: 8px;
	}

	.workflow-board-panel-header .workflow-panel-header-title {
		white-space: nowrap;
		flex-shrink: 0;
	}

	.workflow-board-panel-header .workflow-panel-header-row {
		gap: 12px;
		min-width: 0;
	}

	.workflow-board-panel-header .workflow-panel-selection-actions {
		flex-shrink: 0;
		justify-content: flex-end;
	}

	.trf-signature-pad.acc-signature-pad {
		background: #fff;
		border: 1px dashed #cbd5e1;
		border-radius: 10px;
		padding: 0.5rem;
	}

	.trf-signature-pad.acc-signature-pad canvas {
		width: 100%;
		height: 120px;
		display: block;
		border-radius: 6px;
	}

	.receive-checkin-card {
		border: 1px solid #e2e8f0;
		border-radius: 14px;
		background: #fff;
		box-shadow: 0 4px 18px rgba(15, 23, 42, 0.06);
		overflow: hidden;
	}

	.receive-checkin-card + .receive-checkin-card {
		margin-top: 1rem;
	}

	.receive-checkin-card__header {
		display: flex;
		align-items: flex-start;
		justify-content: space-between;
		gap: 1rem;
		padding: 1rem 1.25rem;
		background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
		border-bottom: 1px solid #e2e8f0;
	}

	.receive-checkin-card__identity {
		display: flex;
		flex-direction: column;
		gap: 0.2rem;
		min-width: 0;
	}

	.receive-checkin-card__ref {
		font-size: 1rem;
		font-weight: 700;
		color: #0f172a;
		line-height: 1.2;
	}

	.receive-checkin-card__customer {
		font-size: 0.875rem;
		color: #64748b;
	}

	.receive-checkin-card__channel {
		flex-shrink: 0;
		font-size: 0.6875rem;
		letter-spacing: 0.04em;
	}

	.receive-checkin-card__header-aside {
		display: flex;
		flex-direction: column;
		align-items: flex-end;
		gap: 0.35rem;
		flex-shrink: 0;
		text-align: right;
	}

	.receive-checkin-card__status {
		font-size: 0.8125rem;
		font-weight: 700;
		color: #0f766e;
		line-height: 1.2;
	}

	.receive-checkin-card__grid {
		display: grid;
		grid-template-columns: repeat(2, minmax(0, 1fr));
		gap: 0.75rem 1.25rem;
		padding: 1rem 1.25rem 0.25rem;
	}

	.receive-checkin-stat {
		display: flex;
		flex-direction: column;
		gap: 0.15rem;
		min-width: 0;
	}

	.receive-checkin-stat--wide {
		grid-column: span 2;
	}

	.receive-checkin-stat--full {
		grid-column: 1 / -1;
	}

	.receive-checkin-stat__label {
		font-size: 0.6875rem;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.05em;
		color: #94a3b8;
	}

	.receive-checkin-stat__value {
		font-size: 0.9375rem;
		font-weight: 600;
		color: #1e293b;
		word-break: break-word;
	}

	.receive-checkin-stat__value--status {
		color: #0f766e;
	}

	.receive-checkin-rich-text {
		font-weight: 500;
	}

	.receive-checkin-rich-text p:last-child {
		margin-bottom: 0;
	}

	.check-in-trf-metadata {
		margin-top: 1rem;
		padding: 1rem 1.25rem;
		border-top: 1px solid #e2e8f0;
	}

	.receive-checkin-card__footer {
		padding: 0.75rem 1.25rem 1rem;
		border-top: 1px solid #f1f5f9;
		margin-top: 0.75rem;
	}

	@media (max-width: 575.98px) {
		.receive-checkin-card__grid {
			grid-template-columns: 1fr;
		}

		.receive-checkin-stat--wide,
		.receive-checkin-stat--full {
			grid-column: auto;
		}
	}

	#receive-sample-modal .modal-content {
		max-height: calc(100vh - 2rem);
	}

	#receive-sample-modal .modal-body {
		padding: 0 1.5rem 1.25rem;
		overflow-y: auto;
		overscroll-behavior: contain;
	}

	.receive-sample-modal-body {
		padding: 0;
	}

	.receive-sample-section-label {
		font-size: 0.7rem;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.06em;
		color: #64748b;
		margin: 0 0 0.5rem;
	}

	.receive-sample-selected {
		margin-bottom: 1.25rem;
	}

	.receive-sample-chips {
		display: flex;
		flex-wrap: wrap;
		gap: 0.5rem;
		margin-bottom: 0.5rem;
	}

	.receive-sample-chip {
		display: inline-flex;
		align-items: center;
		gap: 0.35rem;
		padding: 0.4rem 0.75rem;
		border-radius: 999px;
		background: linear-gradient(135deg, var(--color-primary-soft-medium) 0%, #f8fafc 100%);
		border: 1px solid var(--color-primary-shadow);
		font-size: 0.8125rem;
		line-height: 1.2;
	}

	.receive-sample-chip-code {
		font-weight: 600;
		color: var(--color-primary);
	}

	.receive-sample-chip-meta {
		color: #64748b;
		font-size: 0.75rem;
	}

	.receive-sample-chip--muted {
		color: #64748b;
	}

	.receive-sample-selected-hint {
		margin: 0;
		font-size: 0.8125rem;
		color: #64748b;
	}

	.receive-sample-alert {
		display: flex;
		align-items: flex-start;
		gap: 0.5rem;
		padding: 0.75rem 1rem;
		border-radius: 10px;
		font-size: 0.875rem;
	}

	.receive-sample-alert--warning {
		background: #fffbeb;
		border: 1px solid #fde68a;
		color: #92400e;
	}

	.receive-sample-alert--empty {
		background: #f8fafc;
		border: 1px solid #e2e8f0;
		color: #64748b;
	}

	.receive-sample-checklist {
		border: 1px solid #e2e8f0;
		border-radius: 14px;
		background: #fff;
		box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
		overflow: hidden;
	}

	.receive-sample-checklist-head {
		display: flex;
		align-items: flex-start;
		justify-content: space-between;
		gap: 1rem;
		padding: 1rem 1.15rem;
		background: linear-gradient(180deg, #f8fafc 0%, #fff 100%);
		border-bottom: 1px solid #e2e8f0;
	}

	.receive-sample-checklist-title {
		margin: 0 0 0.15rem;
		font-size: 0.9375rem;
		font-weight: 700;
		color: #0f172a;
	}

	.receive-sample-legend {
		display: inline-flex;
		align-items: center;
		gap: 0.35rem;
		font-size: 0.75rem;
		font-weight: 600;
		color: #64748b;
		white-space: nowrap;
	}

	.receive-required-dot {
		width: 8px;
		height: 8px;
		border-radius: 50%;
		background: #dc2626;
		flex-shrink: 0;
	}

	.receive-checklist-list {
		list-style: none;
		margin: 0;
		padding: 0;
	}

	.receive-checklist-item {
		border-bottom: 1px solid #f1f5f9;
	}

	.receive-checklist-item:last-child {
		border-bottom: none;
	}

	.receive-checklist-item.is-required {
		background: linear-gradient(90deg, rgba(254, 242, 242, 0.35) 0%, transparent 100%);
	}

	.receive-checklist-row {
		display: flex;
		align-items: flex-start;
		gap: 0.75rem;
		margin: 0;
		padding: 0.9rem 1.15rem;
		cursor: pointer;
		transition: background-color 0.15s ease;
	}

	.receive-checklist-row:hover {
		background: #f8fafc;
	}

	.receive-checklist-input {
		width: 1.125rem;
		height: 1.125rem;
		margin: 0.15rem 0 0;
		flex-shrink: 0;
		accent-color: var(--color-primary);
		cursor: pointer;
	}

	.receive-checklist-copy {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: 0.5rem;
		min-width: 0;
		flex: 1;
	}

	.receive-checklist-label {
		font-size: 0.9rem;
		font-weight: 500;
		color: #1e293b;
		line-height: 1.45;
	}

	.receive-required-badge {
		display: inline-flex;
		align-items: center;
		padding: 0.1rem 0.45rem;
		border-radius: 999px;
		font-size: 0.65rem;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.04em;
		background: #fef2f2;
		color: #b91c1c;
		border: 1px solid #fecaca;
	}

	.receive-optional-badge {
		display: inline-flex;
		align-items: center;
		padding: 0.1rem 0.45rem;
		border-radius: 999px;
		font-size: 0.65rem;
		font-weight: 600;
		text-transform: uppercase;
		letter-spacing: 0.04em;
		background: #f1f5f9;
		color: #64748b;
	}

	.receive-checklist-field-block {
		padding: 0.9rem 1.15rem 0.9rem 2.65rem;
	}

	.receive-checklist-field-label {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: 0.5rem;
		margin-bottom: 0.45rem;
		font-size: 0.875rem;
		font-weight: 600;
		color: #334155;
	}

	.receive-checklist-control {
		border-radius: 8px;
		border-color: #cbd5e1;
	}

	.receive-checklist-control:focus {
		border-color: var(--color-primary);
		box-shadow: 0 0 0 3px var(--color-primary-highlight);
	}

	.receive-checklist-error {
		margin: 0;
		padding: 0 1.15rem 0.75rem 2.65rem;
		font-size: 0.8rem;
		color: #dc2626;
	}

	.receive-remarks {
		padding: 1rem 1.15rem 1.15rem;
		border-top: 1px solid #e2e8f0;
		background: #fafbfc;
	}

	.receive-sample-modal-footer {
		display: flex;
		justify-content: flex-end;
		align-items: center;
		gap: 0.5rem;
		margin-top: 1.25rem;
		padding-top: 1rem;
		border-top: 1px solid #e2e8f0;
	}

	.receive-sample-submit-btn {
		min-width: 9.5rem;
		border-radius: 8px;
		font-weight: 600;
	}

	.workflow-intray-panel {
		border: 1px solid #e2e8f0;
		border-radius: 12px;
		padding: 0.85rem 1rem;
		background: #f8fafc;
	}

	.workflow-intray-panel-header .workflow-intray-toggle {
		color: #1e293b;
		text-decoration: none;
	}

	.move-to-intray-info-card {
		border: 1px solid var(--color-primary-shadow);
		border-radius: 10px;
		padding: 1rem;
		background: var(--color-primary-soft-medium);
	}

	.move-to-intray-info-icon {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 2.25rem;
		height: 2.25rem;
		border-radius: 8px;
		background: var(--color-primary);
		color: #fff;
		font-size: 1.25rem;
		flex-shrink: 0;
	}

</style>
	<div class="row workflow-board-header mb-3">
		<div class="col-12">
			<div class="batch-header-bar">
				<div class="batch-header-top">
					<div class="batch-title-group">
						<i class="mdi mdi-file-document-edit" style="font-size:1.2rem; color:#64748b;"></i>
						<span class="batch-code-label">Sample Workflow</span>
						<span class="batch-stage-pill">
							<i class="mdi mdi-sitemap" style="font-size:0.75rem;"></i>
							{{ getSampleWorkflowStageLabel($status) }}
						</span>
					</div>
					<div class="d-flex align-items-center flex-wrap workflow-header-actions" style="gap: 6px;">
						@if(in_array($status, ['Sample Approval', 'Sample Verification']))
							<span class="btn btn-sm btn-outline-danger btn-action-sm"
								data-status="{{ $status }}" data-toggle="modal" data-target="#awaiting-approval-modal"><i
									class="mdi mdi-account-check-outline"></i> Batch(es) Awaiting Approval <span
									class="badge badge-danger badge-pill pt-1" id="approval-counter"></span></span>
						@endif
						@if($tatTodayCount > 0)
							<span class="btn btn-sm btn-danger btn-action-sm" data-toggle="modal"
								data-target="#get-batch-tat"><i class="mdi mdi-clock-outline"></i> TAT Today Batches <span
									class="badge badge-light badge-pill pt-1">{{ $tatTodayCount }}</span></span>
						@endif
						{{-- Check-in samples hidden in header: use selection bar Receive or Actions → Receive Sample on ready_for_reception.
						@if($status === 'Samples Receiving' && $workflowSubTab === 'ready_for_reception')
							<button type="button" class="btn btn-sm btn-outline-primary btn-action-sm"
								@click.prevent="$wire.openReceiveModal(selectedInstanceIds())">
								<i class="mdi mdi-package-variant-closed mr-1"></i> Check-in samples
							</button>
						@endif
						--}}
						{{-- Sample Submissions dropdown hidden: walk-ins use Receive Sample only (P1.1)
						<div class="btn-group" role="group">
							<button type="button" class="btn btn-sm btn-primary btn-action-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="mdi mdi-form-select"></i> Sample Submissions
							</button>
							<div class="dropdown-menu dropdown-menu-right">
								@if ($status === 'Samples Receiving' || $this->isReceivingStage() || (in_array($status, ['Samples En-Route', 'Samples Reception']) && $workflowSubTab === 'received'))
									<button class="dropdown-item" type="button" data-toggle="modal" data-target="#add-submission-form-modal">
										<i class="mdi mdi-plus mr-2"></i> Capture Samples
									</button>
									<div class="dropdown-divider"></div>
								@endif
								<a class="dropdown-item" href="{{ route('sample-workflow.saved-forms') }}">
									<i class="mdi mdi-file-document-multiple mr-2"></i> View Submissions
								</a>
							</div>
						</div>
						--}}
						<div class="btn-group workflow-actions-dropdown"
							x-data="{ actionsOpen: false }"
							@click.outside="actionsOpen = false">
							<button type="button" class="btn btn-sm btn-outline-secondary btn-action-sm dropdown-toggle"
								id="workflowActionsDropdownToggle"
								@click.stop="actionsOpen = !actionsOpen"
								:aria-expanded="actionsOpen"
								aria-haspopup="true">
								<i class="mdi mdi-dots-horizontal"></i> Actions
							</button>
							<ul class="dropdown-menu dropdown-menu-right"
								:class="{ 'show': actionsOpen }"
								aria-labelledby="workflowActionsDropdownToggle"
								@click="if ($event.target.closest('.dropdown-item, [data-toggle=\'modal\'], form')) { actionsOpen = false; }">
								@if(isset($status) && in_array($status, array("Samples En-Route", "Samples Receiving", "Samples Request Review", "Samples Reception", "Samples In Lab")))
									<li>
										<button type="button" class="dropdown-item initiate-interlab" data-toggle="modal"
											data-sf-trigger="workflow-action-interlab-transfer"
											data-target="#inter-lab-add" data-action="bulk"><i
												class="mdi mdi-swap-horizontal-bold mr-2 text-warning" data-toggle="tooltip"
												title="Initiate inter Lab"></i> Intiate Inter Lab Transfer(s)</button>

									</li>
								@endif
								@if($status === 'Samples Receiving' && $workflowSubTab === 'submitted')
									<li>
										<button type="button" class="dropdown-item"
											data-sf-trigger="workflow-review-quotation"
											:class="{ 'disabled': selectedInstanceIds().length === 0 }"
											:style="selectedInstanceIds().length === 0 ? 'pointer-events: none; opacity: 0.55;' : ''"
											@click.prevent="selectedInstanceIds().length > 0 && $wire.openReviewQuotationFromInstances(selectedInstanceIds())"><i class="mdi mdi-file-document-edit-outline mr-2"></i> Review quotation</button>
									</li>
									<li>
										<button type="button" class="dropdown-item"
											data-sf-trigger="workflow-record-po"
											:class="{ 'disabled': selectedInstanceIds().length === 0 }"
											:style="selectedInstanceIds().length === 0 ? 'pointer-events: none; opacity: 0.55;' : ''"
											@click.prevent="selectedInstanceIds().length > 0 && $wire.openPoCaptureFromInstances(selectedInstanceIds())"><i class="mdi mdi-file-document-edit-outline mr-2"></i> Record PO</button>
									</li>
								@endif
								@if($status === 'Samples Receiving' && $workflowSubTab === 'ready_for_reception')
									<li>
										<button type="button" class="dropdown-item"
											data-sf-trigger="workflow-receive-sample"
											@click.prevent="$wire.openReceiveModal(selectedInstanceIds())"><i class="mdi mdi-package-variant-closed mr-2"></i> Receive Sample</button>
									</li>
								@endif
								{{-- Move to tray removed as unnecessary
								@if($status === 'Samples Receiving' && $workflowSubTab === 'in_review')
									<li>
										<button type="button" class="dropdown-item"
											data-sf-trigger="workflow-move-to-intray"
											:class="{ 'disabled': selectedCount === 0 }"
											:style="selectedCount === 0 ? 'pointer-events: none; opacity: 0.55;' : ''"
											@click.prevent="selectedCount > 0 && $wire.openMoveToIntrayModal(selectedInstanceIds())"><i class="mdi mdi-inbox-arrow-down mr-2"></i> Move to tray</button>
									</li>
								@endif
								--}}
								{{-- Interzone transfer removed with zones
								@if(in_array($status, ['Samples Receiving', 'Samples Request Review'], true))
									<li>
										<button type="button" class="dropdown-item"
											data-sf-trigger="workflow-interzone-transfer"
											:class="{ 'disabled': selectedCount === 0 }"
											:style="selectedCount === 0 ? 'pointer-events: none; opacity: 0.55;' : ''"
											@click.prevent="openInterzoneTransfer('request')">
											<i class="mdi mdi-map-marker-path mr-2 text-info"></i> Interzone transfer
										</button>
									</li>
								@endif
								--}}
								@if($status === 'Samples Receiving' && $workflowSubTab === 'in_review')
									<li>
										<button type="button" class="dropdown-item"
											data-sf-trigger="workflow-action-request-additional-info"
											:class="{ 'disabled': selectedCount === 0 }"
											:style="selectedCount === 0 ? 'pointer-events: none; opacity: 0.55;' : ''"
											@click.prevent="selectedCount > 0 && $wire.openRequestAdditionalInfoModal(selectedInstanceIds())">
											<i class="mdi mdi-file-document-edit-outline mr-2"></i> Request more info
										</button>
									</li>
								@endif
								@if($status === 'Samples Receiving' && $workflowSubTab === 'sub_contracting')
									<li>
										<button type="button" class="dropdown-item"
											data-sf-trigger="workflow-action-request-review"
											:class="{ 'disabled': selectedCount === 0 }"
											:style="selectedCount === 0 ? 'pointer-events: none; opacity: 0.55;' : ''"
											@click.prevent="selectedCount > 0 && $wire.openRequestReviewModal(selectedInstanceIds())">
											<i class="mdi mdi-clipboard-arrow-right mr-2"></i> Send for review
										</button>
									</li>
									<li>
										<button type="button" class="dropdown-item"
											data-sf-trigger="workflow-action-request-additional-info"
											:class="{ 'disabled': selectedCount === 0 }"
											:style="selectedCount === 0 ? 'pointer-events: none; opacity: 0.55;' : ''"
											@click.prevent="selectedCount > 0 && $wire.openRequestAdditionalInfoModal(selectedInstanceIds())">
											<i class="mdi mdi-file-document-edit-outline mr-2"></i> Request more info
										</button>
									</li>
								@endif
								@if(in_array($status, ['Samples En-Route'], true) && $workflowSubTab === 'requests')
									<li>
										<button type="button" class="dropdown-item" disabled data-target="#dispatch-to-labs-modal"
											data-sf-trigger="workflow-action-request-review"
											data-toggle="modal"><i class="mdi mdi-clipboard-arrow-right mr-2"></i> Send for request review</button>
									</li>
								@endif
								@if ($status == "Samples Reception" || $status == "Samples Receiving")


									<li>
										<button type="button" class="dropdown-item" data-toggle="modal" disabled data-target="#delete-batch"
											data-sf-trigger="workflow-action-cancel-batch">
											<i class="mdi mdi-delete-empty mr-2"></i> Cancel Batch
										</button>
									</li>
									@if($status !== 'Samples Receiving')
									<li>
										<button type="button" class="dropdown-item" data-toggle="modal" disabled data-target="#move-to-lab"
											data-sf-trigger="workflow-action-move-to-lab">
											<i class="mdi mdi-swap-vertical mr-2"></i> Move to Lab
										</button>
									</li>
									@endif

									<li>
										<button type="button" class="dropdown-item" data-target="#print-labels-modal" data-toggle="modal"
											data-sf-trigger="workflow-action-print-labels"><i
												class="mdi mdi-printer mr-2"></i> Labels</button>
									</li>
									@if($status !== 'Samples Receiving')
									<li>
										<button type="button" class="dropdown-item" disabled data-target="#dispatch-to-labs-modal"
											data-sf-trigger="workflow-action-request-review"
											data-toggle="modal"><i class="mdi mdi-clipboard-arrow-right mr-2"></i> Send for request review</button>
									</li>
									<li>
										<button type="button" class="dropdown-item" disabled data-target="#dispatch-to-labs-modal-approve"
											data-toggle="modal"><i class="mdi mdi-check-decagram mr-2"></i> Generate Draft Invoice</button>
									</li>
									<li>
										<button type="button" class="dropdown-item" disabled data-target="#approve-begin-process"
											data-sf-trigger="workflow-action-approve-for-analysis"
											data-toggle="modal"><i class="mdi mdi-checkbox-marked-circle-outline mr-2"></i> Approve For
											Analysis</button>
									</li>
									<li>
										<button type="button" class="dropdown-item" disabled
											data-target="#dispatch-to-labs-modal-payment-reminder" data-toggle="modal"
											data-sf-trigger="workflow-action-payment-reminder"
											title="Dispatch Labeled"><i class="mdi mdi-bell-ring mr-2"></i> Payment Reminder</button>
									</li>
									<li>
										<button type="button" class="dropdown-item" disabled data-target="#generarate_customer_focus"
											data-sf-trigger="workflow-action-generate-customer-focus"
											data-toggle="modal" title="Generate Customer Focus"><i
												class="mdi mdi-file-document-outline mr-2"></i> Generate Customer Focus</button>
									</li>
									@endif
									<li>
										<button type="button" class="dropdown-item" data-toggle="modal" data-target="#send-schedule-analysis"
											data-sf-trigger="workflow-action-send-schedule-analysis"
											disabled>
											<i class="mr-2 mdi mdi-email-send-outline"></i> Send Schedule of Analysis
										</button>
									</li>
									<li>
										<button type="button" class="dropdown-item" data-target="#clone-batches" data-toggle="modal"
											data-sf-trigger="workflow-action-clone-batches"><i
												class="mdi mdi-content-duplicate mr-2"></i> Clone Batch(es)</button>
									</li>
								@endif


								@if ($status == "Reports for Collection")
									<li>
										<button type="button" class="dropdown-item" data-target="#send-email-reports-modal"
											data-toggle="modal" data-sf-trigger="workflow-action-email-reports" title="Email Report(s)"
											:class="{ 'disabled': selectedLabBatchCount === 0 }"
											:style="selectedLabBatchCount === 0 ? 'pointer-events: none; opacity: 0.55;' : ''"><i class="mdi mdi-email mr-2"></i> Email
											Report(s)</button>

									</li>
								@endif
								@if($status === 'Samples Receiving' && $workflowSubTab === 'in_review')
									<li>
										<button type="button" class="dropdown-item" disabled data-target="#dispatch-to-labs-modal-approve"
											data-sf-trigger="workflow-action-generate-draft-invoice"
											data-toggle="modal"><i class="mdi mdi-check-decagram mr-2"></i> Generate Draft Invoice</button>
									</li>
									<li>
										<button type="button" class="dropdown-item"
											data-sf-trigger="workflow-action-approve-request"
											:class="{ 'disabled': selectedCount === 0 }"
											:style="selectedCount === 0 ? 'pointer-events: none; opacity: 0.55;' : ''"
											wire:click.prevent="openAcceptSampleWizardFromSelection">
											<i class="mdi mdi-check-circle-outline mr-2"></i> Accept sample
										</button>
									</li>
									<li>
										<button type="button" class="dropdown-item"
											data-sf-trigger="workflow-action-reject-request"
											:class="{ 'disabled': selectedCount === 0 }"
											:style="selectedCount === 0 ? 'pointer-events: none; opacity: 0.55;' : ''"
											wire:click.prevent="openRejectSampleWizardFromSelection">
											<i class="mdi mdi-close-circle-outline mr-2"></i> Reject sample
										</button>
									</li>
									<li>

										<button type="button" class="dropdown-item" data-target="#print-labels-modal" data-toggle="modal" data-sf-trigger="workflow-action-print-labels"><i class="mdi mdi-printer mr-2"></i>Print Labels</button>
									</li>
								@endif
								@if(in_array($status, ['Samples Reception', 'Samples Receiving']) || (in_array($status, ['Samples En-Route']) && $workflowSubTab === 'requests'))
									<li>
										<button type="button" class="dropdown-item" disabled data-target="#portal-request-reject-form-modal" data-toggle="modal" data-sf-trigger="workflow-action-reject-request" title="Reject Request">
											<i class="mdi mdi-close-circle-outline mr-2"></i> Reject Request
										</button>
									</li>
								@endif
								@if($status == "Samples In Lab")
									@if(auth()->user()->can('laboratory.components.sample-workflow.assign-user'))
									<li>
										<button type="button" class="dropdown-item" data-target="#assign-user-modal" data-toggle="modal">
											<i class="mdi mdi-account-arrow-right-outline mr-2"></i> Assign user
										</button>
									</li>
									@endif
									<li>

										<button type="button" class="dropdown-item"
											:class="{ 'disabled': selectedLabBatchCount === 0 }"
											:style="selectedLabBatchCount === 0 ? 'pointer-events: none; opacity: 0.55;' : ''"
											data-target="#print-labels-modal" data-toggle="modal" data-sf-trigger="workflow-action-print-labels"><i
												class="mdi mdi-printer mr-2"></i>Print Labels</button>
									</li>
									<li>
										<button type="button" class="dropdown-item"
											wire:click.prevent="openDecontaminationModal">
											<i class="mdi mdi-biohazard mr-2"></i> Decontamination samples
										</button>
									</li>
									<li>
										<button type="button" class="dropdown-item" disabled data-target="#dispatch-to-labs-modal-approve"
											data-toggle="modal"><i class="mdi mdi-check-decagram mr-2"></i> Generate Draft Invoice</button>
									</li>
								@endif
								@if($status == 'Sample Approval')
									<li>
										<button type="button" class="dropdown-item" disabled data-target="#move-batch-complete"
											data-sf-trigger="workflow-action-mark-complete"
											data-toggle="modal"><i class="mdi mdi-subdirectory-arrow-right mr-2"></i>Mark Complete</button>
									</li>
								@endif
								@if($status == 'Finished Sample')
									<li>
										<button type="button" class="dropdown-item" disabled data-target="#move-sample-approval"
											data-sf-trigger="workflow-action-return-to-approval"
											data-toggle="modal">
											<i class="mdi mdi-subdirectory-arrow-right mr-2"></i> Return to Approval
										</button>
									</li>
								@endif
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	@if($status === 'Samples Receiving')
		@include('livewire.partials.workflow-receiving-kpi-row')
	@elseif(in_array($status, ['Samples Reception', 'Samples En-Route'], true))
		<div class="row mb-3">
			<div class="col-lg-3 col-md-6 mb-3">
				<div class="workflow-stat-card">
					<div class="workflow-stat-label">Customers Requested Submission</div>
					<div class="workflow-stat-meta">
						<div class="workflow-stat-value">{{ $samplesReceptionStats['customers_requested'] ?? 0 }}</div>
						<span class="workflow-stat-icon"><i class="mdi mdi-account-multiple-outline"></i></span>
					</div>
				</div>
			</div>
			<div class="col-lg-3 col-md-6 mb-3">
				<div class="workflow-stat-card">
					<div class="workflow-stat-label">Portal Samples Submitted</div>
					<div class="workflow-stat-meta">
						<div class="workflow-stat-value">{{ $samplesReceptionStats['portal_submitted'] ?? 0 }}</div>
						<span class="workflow-stat-icon"><i class="mdi mdi-file-document-outline"></i></span>
					</div>
				</div>
			</div>
			<div class="col-lg-3 col-md-6 mb-3">
				<div class="workflow-stat-card">
					<div class="workflow-stat-label">Sent To Request Review</div>
					<div class="workflow-stat-meta">
						<div class="workflow-stat-value">{{ $samplesReceptionStats['sent_to_request_review'] ?? 0 }}</div>
						<span class="workflow-stat-icon"><i class="mdi mdi-clipboard-check-outline"></i></span>
					</div>
				</div>
			</div>
			<div class="col-lg-3 col-md-6 mb-3">
				<div class="workflow-stat-card">
					<div class="workflow-stat-label">Waiting For Delivery To Lab</div>
					<div class="workflow-stat-meta">
						<div class="workflow-stat-value">{{ $samplesReceptionStats['waiting_for_delivery'] ?? 0 }}</div>
						<span class="workflow-stat-icon"><i class="mdi mdi-truck-delivery-outline"></i></span>
					</div>
				</div>
			</div>
		</div>
	@endif
	@if($status == 'All Samples')
		<div class="row">
			<div class="col-12">
				<div class="workflow-board-panel">
					<div class="workflow-board-panel-header">
						<h6>
							<i class="mdi mdi-filter-variant"></i>
							Apply filters
						</h6>
					</div>
					<div class="workflow-board-panel-body">
		<form action="{{route('sample-workflow', ['status' => $status])}}"
			method="get">
			<div class="row">
				<div class="col-md-3">
					<div class="form-group">
						<label for="" class="control-label">Customer</label>
						<select name="customer_id" id="" class="form-control">
							<option value="All" {{ ($allFilter['customer_id'] ?? 'All') == 'All' ? 'selected' : '' }}>All</option>
							@foreach ($clients as $client)
								<option value="{{$client->id}}" {{ ($allFilter['customer_id'] ?? 'All') == $client->id ? 'selected' : '' }}>{{$client->name}}</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label for="" class="control-label">Sample Types</label>
						<select name="sample_type_id" id="" class="form-control">
							<option value="All" {{ ($allFilter['sample_type_id'] ?? 'All') == 'All' ? 'selected' : '' }}>All</option>
							@foreach ($sampletypes as $s_type)
								<option value="{{$s_type->id}}" {{ ($allFilter['sample_type_id'] ?? 'All') == $s_type->id ? 'selected' : '' }}>{{$s_type->name}}</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label for="" class="control-label">Receipt Date From</label>
						<input type="date" name="receipt_date_from" id="" value="{{$allFilter['receipt_date_from'] ?? ''}}" class="form-control">
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label for="" class="control-label">Receipt Date To</label>
						<input type="date" name="receipt_date_to" id="" value="{{$allFilter['receipt_date_to'] ?? ''}}" class="form-control">
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label for="" class="control-label">TAT Date From</label>
						<input type="date" name="tat_date_from" id="" value="{{$allFilter['tat_date_from'] ?? ''}}" class="form-control">
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label for="" class="control-label">TAT Date To</label>
						<input type="date" name="tat_date_to" id="" value="{{$allFilter['tat_date_to'] ?? ''}}" class="form-control">
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label for="" class="control-label">Schedule of Analysis Status</label>
						<select name="schedule_sent" id="" class="form-control">
							<option value="All" {{ ($allFilter['schedule_sent'] ?? 'All') == 'All' ? 'selected' : '' }}>All</option>
							<option value="sent" {{ ($allFilter['schedule_sent'] ?? 'All') == 'sent' ? 'selected' : '' }}>Sent</option>
							<option value="not_sent" {{ ($allFilter['schedule_sent'] ?? 'All') == 'not_sent' ? 'selected' : '' }}>Not Sent</option>
						</select>
					</div>
				</div>
				<div class="col-md-12">
					<button type="submit" class="btn btn-sm btn-outline-primary float-right btn-action-sm"><i class="mdi mdi-filter-outline"></i>
						Apply</button>
				</div>
			</div>
		</form>
					</div>
				</div>
			</div>
		</div>
	@endif
	
	@if($status != 'All Samples' && $status != 'Finished Sample')
		<!-- Livewire Filters -->
		@if($status != 'Samples Reception')
		@endif
	@endif


	
	<!-- Submission Forms for status pages using form-instance workflow -->
	@if($status != 'All Samples')
		<div class="row mb-4 mt-2">
			<div class="col-12">
				<div class="workflow-board-panel">
					<div class="workflow-board-panel-header">
						<div class="d-flex align-items-center justify-content-between w-100 flex-nowrap workflow-panel-header-row">
							<h5 class="mb-0 workflow-panel-header-title">
								<i class="mdi mdi-file-document-multiple"></i>
								@if($status === 'Samples Receiving')
									{{ $receivingRequestTabs[$workflowSubTab] ?? 'Submitted Requests' }}
								@elseif($status === 'Samples Request Review')
									{{ $requestReviewTabs[$workflowSubTab] ?? 'In review' }}
								@elseif(in_array($status, ['Samples Reception', 'Samples En-Route']))
									{{ $workflowSubTab === 'requests' ? 'Submission requests' : 'Samples received' }}
								@else
									{{ getSampleWorkflowStageLabel($status) }}
								@endif
							</h5>
							@if(in_array($status, ['Samples Receiving', 'Samples Request Review'], true))
								<div class="d-flex align-items-center flex-wrap workflow-panel-selection-actions ml-auto"
									style="gap: 8px;"
									x-show="selectedCount > 0"
									x-cloak>
									@if($status === 'Samples Receiving' && $workflowSubTab === 'sub_contracting')
										<span class="badge badge-light border text-dark">
											<i class="mdi mdi-truck-delivery-outline mr-1"></i> Sub-contracting queue
										</span>
										<button type="button"
											class="btn btn-sm btn-outline-primary"
											@click.prevent="selectedCount > 0 && $wire.openSubcontractDispatchModal(selectedInstanceIds())">
											<i class="mdi mdi-barcode-scan mr-1"></i> Dispatch request
										</button>
									@endif
									@if($status === 'Samples Receiving' && $workflowSubTab === 'submitted')
										<button type="button"
											class="btn btn-sm btn-outline-primary"
											data-sf-trigger="workflow-process-enquiry"
											@click.prevent="selectedCount > 0 && $wire.openProcessEnquiryFromInstances(selectedInstanceIds())">
											<i class="mdi mdi-file-chart-outline mr-1"></i> Process enquiry
										</button>
										<button type="button"
											class="btn btn-sm btn-outline-primary"
											data-sf-trigger="workflow-walk-in-acceptance"
											@click.prevent="selectedCount > 0 && $wire.recordWalkInAcceptanceFromInstances(selectedInstanceIds())">
											<i class="mdi mdi-check-decagram mr-1"></i> Accept quotation
										</button>
									@endif
									@if($status === 'Samples Receiving' && $workflowSubTab === 'ready_for_reception')
										<button type="button"
											class="btn btn-sm btn-outline-primary"
											data-sf-trigger="workflow-receive-sample"
											@click.prevent="selectedInstanceIds().length > 0 && $wire.openReceiveModal(selectedInstanceIds())">
											<i class="mdi mdi-package-variant-closed mr-1"></i> Receive
										</button>
									@endif
									@if($status === 'Samples Receiving' && $workflowSubTab === 'sub_contracting')
										<button type="button"
											class="btn btn-sm btn-outline-primary"
											data-sf-trigger="workflow-action-request-review"
											@click.prevent="selectedCount > 0 && $wire.openRequestReviewModal(selectedInstanceIds())">
											<i class="mdi mdi-clipboard-arrow-right mr-1"></i> Send for review
										</button>
										<button type="button"
											class="btn btn-sm btn-outline-primary"
											data-sf-trigger="workflow-action-request-additional-info"
											@click.prevent="selectedCount > 0 && $wire.openRequestAdditionalInfoModal(selectedInstanceIds())">
											<i class="mdi mdi-file-document-edit-outline mr-1"></i> Request more info
										</button>
									@endif
									@if($status === 'Samples Receiving' && $workflowSubTab === 'in_review')
										<button type="button"
											class="btn btn-sm btn-outline-primary"
											data-sf-trigger="workflow-action-request-additional-info"
											@click.prevent="selectedCount > 0 && $wire.openRequestAdditionalInfoModal(selectedInstanceIds())">
											<i class="mdi mdi-file-document-edit-outline mr-1"></i> Request more info
										</button>
										<button type="button"
											class="btn btn-sm btn-primary"
											data-sf-trigger="workflow-action-approve-request"
											@click.prevent="selectedCount > 0 && $wire.openAcceptSampleWizardFromSelection()">
											<i class="mdi mdi-check-circle-outline mr-1"></i> Accept sample
										</button>
										<button type="button"
											class="btn btn-sm btn-outline-danger"
											data-sf-trigger="workflow-action-reject-request"
											@click.prevent="selectedCount > 0 && $wire.openRejectSampleWizardFromSelection()">
											<i class="mdi mdi-close-circle-outline mr-1"></i> Reject sample
										</button>
										{{-- Move to tray removed as unnecessary
										<button type="button"
											class="btn btn-sm btn-outline-primary"
											data-sf-trigger="workflow-move-to-intray"
											@click.prevent="selectedCount > 0 && $wire.openMoveToIntrayModal(selectedInstanceIds())">
											<i class="mdi mdi-inbox-arrow-down mr-1"></i> Move to tray
										</button>
										--}}
									@endif
									{{-- Former Samples Request Review selection actions are on Receiving → In Review above. --}}
								</div>
							@endif
							@if($status !== 'Samples Receiving' && $status !== 'Samples Request Review')
							<div class="d-flex align-items-center" style="gap: 15px;">
								<div class="d-flex align-items-center">
									<span class="mr-2 text-muted" style="font-size: 0.82rem;">Per page</span>
									<select wire:model.live="batchesPerPage" class="form-control form-control-sm" style="width: auto; min-width: 4.5rem; height: 30px; border-radius: 6px;">
										@foreach($batchesPerPageOptions as $size)
											<option value="{{ $size }}">{{ $size }}</option>
										@endforeach
									</select>
								</div>
							</div>
							@endif
						</div>
					</div>
					<div class="workflow-board-panel-body flush-top">
						@if($status === 'Samples Receiving')
							<div class="workflow-receiving-tabs mb-3" role="tablist" wire:loading.class="is-busy" wire:target="setWorkflowSubTab,setSubcontractingDispatchStatus">
								@foreach($receivingRequestTabs as $tabKey => $tabLabel)
									<button
										type="button"
										class="workflow-receiving-tab {{ $workflowSubTab === $tabKey ? 'is-active' : '' }}"
										wire:click="setWorkflowSubTab('{{ $tabKey }}')"
										wire:loading.class="is-loading"
										wire:target="setWorkflowSubTab('{{ $tabKey }}')"
										role="tab"
										aria-selected="{{ $workflowSubTab === $tabKey ? 'true' : 'false' }}"
									>
										{{ $tabLabel }}
										<span class="workflow-receiving-tab-badge">{{ $receivingRequestTabCounts[$tabKey] ?? 0 }}</span>
									</button>
								@endforeach
							</div>
							@if($workflowSubTab === 'sub_contracting')
								<div class="d-flex flex-wrap align-items-center mb-3" style="gap: 8px;">
									@foreach($subcontractingDispatchStatuses as $dispatchKey => $dispatchLabel)
										<button
											type="button"
											class="workflow-receiving-tab {{ $subcontractingDispatchStatus === $dispatchKey ? 'is-active' : '' }}"
											wire:click="setSubcontractingDispatchStatus('{{ $dispatchKey }}')"
										>
											{{ $dispatchLabel }}
										</button>
									@endforeach
								</div>
								<p class="text-muted small mb-3">
									After dispatch, requests move to <strong>Dispatched</strong> here and into <strong>Samples In Lab</strong> (Requests view).
								</p>
							@endif
							@if($workflowSubTab !== 'interzone_transfers')
							<div class="workflow-brand-filters-card"
								x-data="{ open: false, advanced: false }">
								<button type="button"
									class="workflow-filters-toggle"
									@click="open = !open"
									:aria-expanded="open.toString()">
									<span class="workflow-filters-toggle__label">
										<i class="mdi mdi-filter-variant"></i>
										Filters
										@if($this->activeRequestFilterCount > 0)
											<span class="workflow-filters-toggle__badge">{{ $this->activeRequestFilterCount }} active</span>
										@endif
									</span>
									<i class="mdi" :class="open ? 'mdi-chevron-up' : 'mdi-chevron-down'"></i>
								</button>
								<div class="workflow-filters-panel" x-show="open" x-cloak x-transition.opacity.duration.150ms>
								<div class="row workflow-filters-primary-row">
									<div class="col-md-4">
										<div class="form-group mb-3 mb-md-0">
											<label class="form-label small fw-bold">Search</label>
											<div class="position-relative">
												<input type="text" wire:model.live.debounce.300ms="submissionFormsSearch" class="form-control form-control-sm" placeholder="Form #, Title, or Name...">
												<div wire:loading wire:target="submissionFormsSearch" class="position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%);">
													<span class="spinner-border spinner-border-sm text-primary"></span>
												</div>
											</div>
										</div>
									</div>
									<div class="col-md-2">
										<div class="form-group mb-3 mb-md-0">
											<label class="form-label small fw-bold">Receipt From</label>
											<input type="date" wire:model.live="receiptDateFrom" class="form-control form-control-sm">
										</div>
									</div>
									<div class="col-md-2">
										<div class="form-group mb-3 mb-md-0">
											<label class="form-label small fw-bold">Receipt To</label>
											<input type="date" wire:model.live="receiptDateTo" class="form-control form-control-sm">
										</div>
									</div>
									<div class="col-md-2">
										<div class="form-group mb-3 mb-md-0">
											<label class="form-label small fw-bold">Per page</label>
											<select wire:model.live="submissionFormsPerPage" class="form-control form-control-sm">
												@foreach($submissionFormsPerPageOptions as $size)
													<option value="{{ $size }}">{{ $size }}</option>
												@endforeach
											</select>
										</div>
									</div>
									<div class="col-md-2 d-flex align-items-end">
										<button type="button" @click="advanced = !advanced" class="btn btn-primary btn-sm btn-more-filters w-100" style="height: 31px;">
											<i class="mdi mdi-filter-variant"></i>
											<span x-text="advanced ? 'Fewer filters' : 'More filters'"></span>
										</button>
									</div>
								</div>
								<div class="workflow-filters-advanced" x-show="advanced" x-cloak x-transition.opacity.duration.150ms>
									<div class="row">
										<div class="col-md-2">
											<div class="form-group mb-3 mb-md-0">
												<label class="form-label small fw-bold">Priority</label>
												<select wire:model.live="submissionFormsPriority" class="form-control form-control-sm">
													<option value="">All Priorities</option>
													<option value="low">Low</option>
													<option value="normal">Normal</option>
													<option value="high">High</option>
													<option value="urgent">Urgent</option>
												</select>
											</div>
										</div>
										<div class="col-md-2">
											<div class="form-group mb-3 mb-md-0">
												<label class="form-label small fw-bold">Nature of Sample</label>
												<select wire:model.live="natureOfSampleFilter" class="form-control form-control-sm">
													<option value="">All</option>
													@foreach($natureOfSampleOptions as $opt)
														<option value="{{ $opt->value }}">{{ $opt->key }}</option>
													@endforeach
												</select>
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group mb-3 mb-md-0">
												<label class="form-label small fw-bold">Customer</label>
												<div class="position-relative">
													<div class="tag-select-container form-control-sm py-0"
														 wire:click="$set('showCustomerDropdown', true)"
														 wire:click.outside="$set('showCustomerDropdown', false)"
														 wire:key="receiving-customer-dropdown-{{ $customerFilter }}">
														<div class="tag-select-input" style="min-height: 29px;">
															@if($this->selectedCustomer)
																<span class="tag-badge py-0 px-2" style="font-size: 11px;">
																	{{ $this->selectedCustomer->name }}
																	<i class="mdi mdi-close-circle" wire:click.stop="$set('customerFilter', null); $set('customerSearch', ''); $set('customerPage', 1)"></i>
																</span>
															@endif
															@if(!$this->selectedCustomer)
																<input type="text"
																	   wire:model.live.debounce.300ms="customerSearch"
																	   wire:click.stop="$set('showCustomerDropdown', true)"
																	   class="tag-input customer-search-input py-0"
																	   style="font-size: 12px; height: 28px;"
																	   placeholder="Search customers..."
																	   autocomplete="off">
															@endif
														</div>
														@if($showCustomerDropdown)
															<div class="tag-dropdown customer-dropdown-scroll" style="max-height: 250px; overflow-y: auto; z-index: 1000;">
																<div wire:loading wire:target="customerSearch,selectCustomer" class="tag-dropdown-item text-center py-2">
																	<span class="spinner-border spinner-border-sm text-primary"></span>
																</div>
																<div wire:loading.remove wire:target="customerSearch,selectCustomer">
																	@if(count($this->filteredCustomers) > 0)
																		@foreach($this->filteredCustomers as $customer)
																			<div class="tag-dropdown-item py-1 px-3" style="font-size: 12px;" wire:click.stop="selectCustomer({{ $customer->id }})">
																				{{ $customer->name }}
																			</div>
																		@endforeach
																		@if($this->hasMoreCustomers)
																			<div class="tag-dropdown-item text-center text-primary py-1" wire:click.stop="loadMoreCustomers" style="cursor: pointer; font-weight: 600; font-size: 11px;">
																				<i class="mdi mdi-chevron-down"></i> Load More
																			</div>
																		@endif
																	@else
																		<div class="tag-dropdown-item text-muted py-1 px-3" style="font-size: 12px;">No customers found</div>
																	@endif
																</div>
															</div>
														@endif
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-2">
											<div class="form-group mb-3 mb-md-0">
												<label class="form-label small fw-bold">Sample Type</label>
												<select wire:model.live="sampleTypeFilter" class="form-control form-control-sm">
													<option value="">All Sample Types</option>
													@foreach($sampletypes as $sampleType)
														<option value="{{ $sampleType->id }}">{{ $sampleType->name }}</option>
													@endforeach
												</select>
											</div>
										</div>
										<div class="col-md-2">
											<div class="form-group mb-3 mb-md-0">
												<label class="form-label small fw-bold">Request Origin</label>
												<select wire:model.live="originFilter" class="form-control form-control-sm">
													<option value="">All Origins</option>
													<option value="scheduled">Scheduled Sampling</option>
													<option value="walk-in">Walk-in</option>
												</select>
											</div>
										</div>
										<div class="col-md-1 d-flex align-items-end">
											<button type="button" wire:click="clearFilters" class="btn btn-outline-secondary btn-sm w-100" style="height: 31px;" title="Clear Filters">
												<i class="mdi mdi-refresh"></i>
											</button>
										</div>
									</div>
								</div>
								</div>
							</div>
							@endif
							{{-- Interzone Transfers tab panel (tab hidden)
							@if($workflowSubTab === 'interzone_transfers')
								@livewire('sampleworkflow.interzone-transfer-manager', [
									'workflowStatus' => $status,
									'workflowSubTab' => $workflowSubTab,
								], key('interzone-receiving-panel'))
							@endif
							--}}
							@if($workflowSubTab === 'scheduled_sampling')
								<div class="mt-3">
									@php
										$samplingSchedules = $this->samplingSchedules;
									@endphp
									@if($samplingSchedules->count() > 0)
										<div class="table-responsive">
											<table class="table table-sm table-hover workflow-table mb-0">
												<thead>
													<tr>
														<th>Customer Name</th>
														<th>Date for Sampling</th>
														<th>Sample Details</th>
														<th>Frequency</th>
														<th>Personnel</th>
													</tr>
												</thead>
												<tbody>
													@foreach($samplingSchedules as $schedule)
														@php
															$sampleDetailsList = [];
															if (!empty($schedule->sample_details) && is_array($schedule->sample_details)) {
																foreach ($schedule->sample_details as $entry) {
																	$st = \App\SampleType::find($entry['sample_type_id'] ?? null);
																	$at = \App\AnalysisType::find($entry['analysis_type_id'] ?? null);
																	$paramCount = count($entry['parameters'] ?? []);
																	if ($st) {
																		$detailText = $st->name;
																		if ($at) $detailText .= ' - ' . $at->name;
																		if ($paramCount > 0) $detailText .= ' (' . $paramCount . ' params)';
																		$sampleDetailsList[] = $detailText;
																	}
																}
															} elseif ($schedule->sample_type) {
																$detailText = $schedule->sample_type->name;
																if ($schedule->analysis_type) $detailText .= ' - ' . $schedule->analysis_type->name;
																$paramCount = count($schedule->parameters ?? []);
																if ($paramCount > 0) $detailText .= ' (' . $paramCount . ' params)';
																$sampleDetailsList[] = $detailText;
															}
															$sampleDetailsText = implode('; ', $sampleDetailsList);
														@endphp
														<tr>
															<td>{{ $schedule->client->name ?? 'N/A' }}</td>
															<td>{{ $schedule->sampling_datetime ? $schedule->sampling_datetime->format('M d, Y H:i') : 'N/A' }}</td>
															<td>{{ $sampleDetailsText ?: 'N/A' }}</td>
															<td><span class="badge badge-info">{{ $schedule->frequency ?? 'One-time' }}</span></td>
															<td>{{ $schedule->personnel->name ?? 'N/A' }}</td>
														</tr>
													@endforeach
												</tbody>
											</table>
										</div>
										<div class="mt-2">
											{{ $samplingSchedules->links() }}
										</div>
									@else
										<div class="text-center py-5">
											<i class="mdi mdi-calendar-clock text-muted" style="font-size: 3rem;"></i>
											<p class="text-muted mt-3">No scheduled sampling found.</p>
										</div>
									@endif
								</div>
							@endif
						@elseif($status === 'Samples Request Review')
							<div class="workflow-receiving-tabs mb-3" role="tablist">
								@foreach($requestReviewTabs as $tabKey => $tabLabel)
									<button
										type="button"
										class="workflow-receiving-tab {{ $workflowSubTab === $tabKey ? 'is-active' : '' }}"
										wire:click="setWorkflowSubTab('{{ $tabKey }}')"
										role="tab"
										aria-selected="{{ $workflowSubTab === $tabKey ? 'true' : 'false' }}"
									>
										{{ $tabLabel }}
										<span class="workflow-receiving-tab-badge">{{ $requestReviewTabCounts[$tabKey] ?? 0 }}</span>
									</button>
								@endforeach
							</div>
							<div class="workflow-brand-filters-card"
								x-data="{ open: false, advanced: false }">
								<button type="button"
									class="workflow-filters-toggle"
									@click="open = !open"
									:aria-expanded="open.toString()">
									<span class="workflow-filters-toggle__label">
										<i class="mdi mdi-filter-variant"></i>
										Filters
										@if($this->activeRequestFilterCount > 0)
											<span class="workflow-filters-toggle__badge">{{ $this->activeRequestFilterCount }} active</span>
										@endif
									</span>
									<i class="mdi" :class="open ? 'mdi-chevron-up' : 'mdi-chevron-down'"></i>
								</button>
								<div class="workflow-filters-panel" x-show="open" x-cloak x-transition.opacity.duration.150ms>
								<div class="row workflow-filters-primary-row">
									<div class="col-md-4">
										<div class="form-group mb-3 mb-md-0">
											<label class="form-label small fw-bold">Search</label>
											<div class="position-relative">
												<input type="text" wire:model.live.debounce.300ms="submissionFormsSearch" class="form-control form-control-sm" placeholder="Form #, Title, or Name...">
												<div wire:loading wire:target="submissionFormsSearch" class="position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%);">
													<span class="spinner-border spinner-border-sm text-primary"></span>
												</div>
											</div>
										</div>
									</div>
									<div class="col-md-2">
										<div class="form-group mb-3 mb-md-0">
											<label class="form-label small fw-bold">Receipt From</label>
											<input type="date" wire:model.live="receiptDateFrom" class="form-control form-control-sm">
										</div>
									</div>
									<div class="col-md-2">
										<div class="form-group mb-3 mb-md-0">
											<label class="form-label small fw-bold">Receipt To</label>
											<input type="date" wire:model.live="receiptDateTo" class="form-control form-control-sm">
										</div>
									</div>
									<div class="col-md-2">
										<div class="form-group mb-3 mb-md-0">
											<label class="form-label small fw-bold">Per page</label>
											<select wire:model.live="submissionFormsPerPage" class="form-control form-control-sm">
												@foreach($submissionFormsPerPageOptions as $size)
													<option value="{{ $size }}">{{ $size }}</option>
												@endforeach
											</select>
										</div>
									</div>
									<div class="col-md-2 d-flex align-items-end">
										<button type="button" @click="advanced = !advanced" class="btn btn-primary btn-sm btn-more-filters w-100" style="height: 31px;">
											<i class="mdi mdi-filter-variant"></i>
											<span x-text="advanced ? 'Fewer filters' : 'More filters'"></span>
										</button>
									</div>
								</div>
								<div class="workflow-filters-advanced" x-show="advanced" x-cloak x-transition.opacity.duration.150ms>
										<div class="row">
											<div class="col-md-2">
												<div class="form-group mb-3 mb-md-0">
													<label class="form-label small fw-bold">Status</label>
													<select wire:model.live="submissionFormsStatus" class="form-control form-control-sm">
														<option value="">All Statuses</option>
														<option value="submitted">Submitted</option>
														<option value="in_review">In Review</option>
														<option value="approved">Approved</option>
														<option value="rejected">Rejected</option>
													</select>
												</div>
											</div>
											<div class="col-md-2">
												<div class="form-group mb-3 mb-md-0">
													<label class="form-label small fw-bold">Priority</label>
													<select wire:model.live="submissionFormsPriority" class="form-control form-control-sm">
														<option value="">All Priorities</option>
														<option value="low">Low</option>
														<option value="normal">Normal</option>
														<option value="high">High</option>
														<option value="urgent">Urgent</option>
													</select>
												</div>
											</div>
											<div class="col-md-2">
												<div class="form-group mb-3 mb-md-0">
													<label class="form-label small fw-bold">Nature of Sample</label>
													<select wire:model.live="natureOfSampleFilter" class="form-control form-control-sm">
														<option value="">All</option>
														@foreach($natureOfSampleOptions as $opt)
															<option value="{{ $opt->value }}">{{ $opt->key }}</option>
														@endforeach
													</select>
												</div>
											</div>
											<div class="col-md-3">
												<div class="form-group mb-3 mb-md-0">
													<label class="form-label small fw-bold">Customer</label>
													<div class="position-relative">
															<div class="tag-select-container form-control-sm py-0"
															 wire:click="$set('showCustomerDropdown', true)"
															 wire:click.outside="$set('showCustomerDropdown', false)"
															 wire:key="request-review-customer-dropdown-{{ $customerFilter }}">
															<div class="tag-select-input" style="min-height: 29px;">
																@if($this->selectedCustomer)
																	<span class="tag-badge py-0 px-2" style="font-size: 11px;">
																		{{ $this->selectedCustomer->name }}
																		<i class="mdi mdi-close-circle" wire:click.stop="$set('customerFilter', null); $set('customerSearch', ''); $set('customerPage', 1)"></i>
																	</span>
																@endif
																@if(!$this->selectedCustomer)
																	<input type="text"
																		   wire:model.live.debounce.300ms="customerSearch"
																		   wire:click.stop="$set('showCustomerDropdown', true)"
																		   class="tag-input customer-search-input py-0"
																		   style="font-size: 12px; height: 28px;"
																		   placeholder="Search customers..."
																		   autocomplete="off">
																@endif
															</div>
															@if($showCustomerDropdown)
																<div class="tag-dropdown customer-dropdown-scroll" style="max-height: 250px; overflow-y: auto; z-index: 1000;">
																	<div wire:loading wire:target="customerSearch,selectCustomer" class="tag-dropdown-item text-center py-2">
																		<span class="spinner-border spinner-border-sm text-primary"></span>
																	</div>
																	<div wire:loading.remove wire:target="customerSearch,selectCustomer">
																		@if(count($this->filteredCustomers) > 0)
																			@foreach($this->filteredCustomers as $customer)
																				<div class="tag-dropdown-item py-1 px-3" style="font-size: 12px;" wire:click.stop="selectCustomer({{ $customer->id }})">
																					{{ $customer->name }}
																				</div>
																			@endforeach
																			@if($this->hasMoreCustomers)
																				<div class="tag-dropdown-item text-center text-primary py-1" wire:click.stop="loadMoreCustomers" style="cursor: pointer; font-weight: 600; font-size: 11px;">
																					<i class="mdi mdi-chevron-down"></i> Load More
																				</div>
																			@endif
																		@else
																			<div class="tag-dropdown-item text-muted py-1 px-3" style="font-size: 12px;">No customers found</div>
																		@endif
																	</div>
																</div>
															@endif
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-2">
												<div class="form-group mb-3 mb-md-0">
													<label class="form-label small fw-bold">Sample Type</label>
													<select wire:model.live="sampleTypeFilter" class="form-control form-control-sm">
														<option value="">All Sample Types</option>
														@foreach($sampletypes as $sampleType)
															<option value="{{ $sampleType->id }}">{{ $sampleType->name }}</option>
														@endforeach
													</select>
												</div>
											</div>
											<div class="col-md-1 d-flex align-items-end">
												<button type="button" wire:click="clearFilters" class="btn btn-outline-secondary btn-sm w-100" style="height: 31px;" title="Clear Filters">
													<i class="mdi mdi-refresh"></i>
												</button>
											</div>
										</div>
									</div>
								</div>
							</div>
						@endif
						@if($status !== 'Samples Receiving' && $status !== 'Samples Request Review')
						@php
							$boardUsesSubmissionSearch = $status === 'Samples In Lab'
								|| ($this->isReceivingStage() && $workflowSubTab === 'requests');
						@endphp
						<!-- Board filters — collapsible (match Samples Receiving density) -->
						<div class="workflow-brand-filters-card"
							x-data="{ open: {{ $this->activeRequestFilterCount > 0 ? 'true' : 'false' }} }">
							<button type="button"
								class="workflow-filters-toggle"
								@click="open = !open"
								:aria-expanded="open.toString()">
								<span class="workflow-filters-toggle__label">
									<i class="mdi mdi-filter-variant"></i>
									Filters
									@if($this->activeRequestFilterCount > 0)
										<span class="workflow-filters-toggle__badge">{{ $this->activeRequestFilterCount }} active</span>
									@endif
								</span>
								<i class="mdi" :class="open ? 'mdi-chevron-up' : 'mdi-chevron-down'"></i>
							</button>
							<div class="workflow-filters-panel" x-show="open" x-cloak x-transition.opacity.duration.150ms>
								<div class="row">
									<div class="col-md-3">
										<div class="form-group mb-3 mb-md-0">
											<label class="form-label small fw-bold">Search</label>
											<div class="position-relative">
												@if($boardUsesSubmissionSearch)
													<input type="text" wire:model.live.debounce.300ms="submissionFormsSearch" class="form-control form-control-sm" placeholder="Form #, Title, or Name...">
													<div wire:loading wire:target="submissionFormsSearch" class="position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%);">
														<span class="spinner-border spinner-border-sm text-primary"></span>
													</div>
												@else
													<input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" placeholder="Batch code or sample code...">
													<div wire:loading wire:target="search" class="position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%);">
														<span class="spinner-border spinner-border-sm text-primary"></span>
													</div>
												@endif
											</div>
										</div>
									</div>

									@if($boardUsesSubmissionSearch)
										<div class="col-md-2">
											<div class="form-group mb-3 mb-md-0">
												<label class="form-label small fw-bold">Status</label>
												<select wire:model.live="submissionFormsStatus" class="form-control form-control-sm">
													<option value="">All Statuses</option>
													<option value="submitted">Submitted</option>
													<option value="in_review">In Review</option>
													<option value="approved">Approved</option>
													<option value="rejected">Rejected</option>
												</select>
											</div>
										</div>
										<div class="col-md-2">
											<div class="form-group mb-3 mb-md-0">
												<label class="form-label small fw-bold">Priority</label>
												<select wire:model.live="submissionFormsPriority" class="form-control form-control-sm">
													<option value="">All Priorities</option>
													<option value="low">Low</option>
													<option value="normal">Normal</option>
													<option value="high">High</option>
													<option value="urgent">Urgent</option>
												</select>
											</div>
										</div>
									@endif

									<div class="col-md-2">
										<div class="form-group mb-3 mb-md-0">
											<label class="form-label small fw-bold">Receipt From</label>
											<input type="date" wire:model.live="receiptDateFrom" class="form-control form-control-sm">
										</div>
									</div>
									<div class="col-md-2">
										<div class="form-group mb-3 mb-md-0">
											<label class="form-label small fw-bold">Receipt To</label>
											<input type="date" wire:model.live="receiptDateTo" class="form-control form-control-sm">
										</div>
									</div>

									<div class="col-md-1 d-flex align-items-end">
										<button type="button" wire:click="clearFilters" class="btn btn-outline-secondary btn-sm w-100" style="height: 31px;" title="Clear Filters">
											<i class="mdi mdi-refresh"></i>
										</button>
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-md-6">
										<div class="form-group mb-0">
											<label class="form-label small fw-bold">Customer</label>
											<div class="position-relative">
												<div class="tag-select-container form-control-sm py-0"
													wire:click="$set('showCustomerDropdown', true)"
													wire:click.outside="$set('showCustomerDropdown', false)"
													wire:key="customer-dropdown-{{ $customerFilter }}">
													<div class="tag-select-input" style="min-height: 29px;">
														@if($this->selectedCustomer)
															<span class="tag-badge py-0 px-2" style="font-size: 11px;">
																{{ $this->selectedCustomer->name }}
																<i class="mdi mdi-close-circle" wire:click.stop="$set('customerFilter', null); $set('customerSearch', ''); $set('customerPage', 1)"></i>
															</span>
														@endif
														@if(!$this->selectedCustomer)
															<input type="text"
																wire:model.live.debounce.300ms="customerSearch"
																wire:click.stop="$set('showCustomerDropdown', true)"
																class="tag-input customer-search-input py-0"
																style="font-size: 12px; height: 28px;"
																placeholder="Search customers..."
																autocomplete="off">
														@endif
													</div>
													@if($showCustomerDropdown)
														<div class="tag-dropdown customer-dropdown-scroll" style="max-height: 250px; overflow-y: auto; z-index: 1000;">
															<div wire:loading wire:target="customerSearch,selectCustomer" class="tag-dropdown-item text-center py-2">
																<span class="spinner-border spinner-border-sm text-primary"></span>
															</div>
															<div wire:loading.remove wire:target="customerSearch,selectCustomer">
																@if(count($this->filteredCustomers) > 0)
																	@foreach($this->filteredCustomers as $customer)
																		<div class="tag-dropdown-item py-1 px-3" style="font-size: 12px;" wire:click.stop="selectCustomer({{ $customer->id }})">
																			{{ $customer->name }}
																		</div>
																	@endforeach
																	@if($this->hasMoreCustomers)
																		<div class="tag-dropdown-item text-center text-primary py-1" wire:click.stop="loadMoreCustomers" style="cursor: pointer; font-weight: 600; font-size: 11px;">
																			<i class="mdi mdi-chevron-down"></i> Load More
																		</div>
																	@endif
																@else
																	<div class="tag-dropdown-item text-muted py-1 px-3" style="font-size: 12px;">No customers found</div>
																@endif
															</div>
														</div>
													@endif
												</div>
											</div>
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group mb-0">
											<label class="form-label small fw-bold">Sample Type</label>
											<select wire:model.live="sampleTypeFilter" class="form-control form-control-sm">
												<option value="">All Sample Types</option>
												@foreach($sampletypes as $sampleType)
													<option value="{{ $sampleType->id }}">{{ $sampleType->name }}</option>
												@endforeach
											</select>
										</div>
									</div>
								</div>
							</div>
						</div>
						@endif
						@if($this->isReceivingStage() && $status !== 'Samples Receiving')
							<div class="mb-4">
								<ul class="nav nav-tabs" role="tablist">
									<li class="nav-item">
										<button type="button" class="nav-link {{ $workflowSubTab === 'requests' ? 'active' : '' }}" wire:click="setWorkflowSubTab('requests')">Requests</button>
									</li>
									<li class="nav-item">
										<button type="button" class="nav-link {{ $workflowSubTab === 'received' ? 'active' : '' }}" wire:click="setWorkflowSubTab('received')">Received</button>
									</li>
								</ul>
							</div>
						@endif

						@if(
							($status === 'Samples Receiving' && $workflowSubTab !== 'interzone_transfers')
							|| $status === 'Samples Request Review'
							|| ($this->isReceivingStage() && $status !== 'Samples Receiving' && $workflowSubTab === 'requests')
						)
							@php
								$submissionForms = $this->submissionForms;
								$portalSubmissions = in_array($status, ['Samples Receiving', 'Samples Request Review'], true) ? null : $this->portalSubmissions;
								$hasSubmissions = ($submissionForms && $submissionForms->count() > 0)
									|| ($portalSubmissions && $portalSubmissions->count() > 0);
							@endphp

							@if($hasSubmissions)
								@if($submissionForms && $submissionForms->count() > 0)
									@if($this->isReceivingAnalystReviewTab())
										<div class="workflow-review-status-legend mb-3">
											<span class="legend-label">Row border:</span>
											<span class="legend-item">
												<span class="legend-swatch legend-swatch--awaiting-customer"></span>
												Awaiting customer approval
											</span>
											<span class="legend-item">
												<span class="legend-swatch legend-swatch--awaiting-manager"></span>
												Awaiting lab manager approval
											</span>
											<span class="legend-item">
												<span class="legend-swatch legend-swatch--pending"></span>
												Nothing actioned — awaiting acceptance or rejection
											</span>
										</div>
									@endif
									@if(in_array($status, ['Samples Receiving', 'Samples Request Review'], true))
									<div class="workflow-table-wrapper">
									@endif
									<div class="table-responsive">
										<table class="table table-hover workflow-table">
											<thead>
												<tr>
													<th style="width: 40px;"></th>
													<th>Actions</th>
													<th>Test request number</th>
													<th>Customer</th>
													@if(in_array($status, ['Samples Receiving'], true))
														<th>Enquiry Status</th>
													@endif
													@if($status === 'Samples Receiving')
														<th>Origin</th>
													@endif
													@if(! in_array($status, ['Samples Receiving', 'Samples Request Review'], true))
														<th>Batch Status</th>
														<th>Batches</th>
													@endif
													<th>Sample Type</th>
													<th>Tests Required</th>
													@if($status === 'Samples Request Review')
														<th>Acceptance</th>
													@endif
													<th>Submitted</th>
												</tr>
											</thead>
											<tbody>
												@foreach($submissionForms as $instance)
													@php
														$isSamplesReceiving = $status === 'Samples Receiving';
														$hasBatch = $instance->batches->count() > 0;
														$formSampleTypeNames = $instance->getReceivingSampleTypeNames();
														$testsRequiredCount = $hasBatch
															? (int) $instance->tests_count
															: (int) $instance->requested_tests_count;
														$sampleCount = 0;
														$instanceCustomerName = trim((string) (
															$instance->crmCustomer->name
															?? ($instance->submittedBy->name ?? '')
														));
														$instanceCustomerEmail = trim((string) (
															$instance->crmCustomer->email
															?? ($instance->submittedBy->email ?? '')
														));
														$instanceCustomerPhone = trim((string) (
															$instance->crmCustomer->telephone1 ?? ''
														));
														$instanceCustomerAddress = trim((string) (
															$instance->crmCustomer->postal_address
															?? $instance->crmCustomer->physical_address
															?? ''
														));
														$instanceRequestDate = trim((string) (
															optional($instance->submitted_at)->format('Y-m-d') ?? ''
														));

														// Receiving rows use CRM/enquiry fields only — skip getAllSampleDetails()
														// (loads full form graph per row) and values pickers.
														if (! $isSamplesReceiving) {
															$sampleCount = count($instance->getAllSampleDetails());
															$instanceValues = $instance->relationLoaded('values')
																? collect($instance->values)
																: collect($instance->values ?? []);
															$pickValue = function (array $fieldHints) use ($instanceValues) {
																$hintBag = collect($fieldHints)
																	->map(fn ($value) => strtolower(trim((string) $value)))
																	->filter()
																	->values();

																if ($hintBag->isEmpty()) {
																	return '';
																}

																$match = $instanceValues->first(function ($row) use ($hintBag) {
																	$element = $row->element ?? null;
																	if (! $element) {
																		return false;
																	}

																	$mappingField = strtolower(trim((string) ($element->mapping_field ?? '')));
																	$elementName = strtolower(trim((string) ($element->name ?? '')));

																	foreach ($hintBag as $hint) {
																		if (($mappingField !== '' && str_contains($mappingField, $hint))
																			|| ($elementName !== '' && str_contains($elementName, $hint))) {
																			return true;
																		}
																	}

																	return false;
																});

																return trim((string) ($match->value ?? ''));
															};

															$instanceCustomerName = trim((string) (
																$instance->crmCustomer->name
																?? $pickValue(['customer_name', 'submitting_agency', 'name_of_client'])
																?? ($instance->submittedBy->name ?? '')
															));
															$instanceCustomerEmail = trim((string) (
																$instance->crmCustomer->email
																?? $pickValue(['customer_email', 'email'])
																?? ($instance->submittedBy->email ?? '')
															));
															$instanceCustomerPhone = trim((string) (
																$instance->crmCustomer->telephone1
																?? $pickValue(['mobile_telephone_no', 'office_telephone_no', 'telephone', 'phone', 'tel'])
															));
															$instanceCustomerAddress = trim((string) (
																$instance->crmCustomer->postal_address
																?? $instance->crmCustomer->physical_address
																?? $pickValue(['physical_address', 'postal_address', 'address'])
															));
															$instanceRequestDate = trim((string) (
																optional($instance->submitted_at)->format('Y-m-d')
																?? $pickValue(['submitted_by_date', 'submission_date', 'date_of_seizure', 'date_of_sampling'])
															));
														}
														$reviewRowBorderClass = '';
													@endphp
													<tr @if($reviewRowBorderClass !== '') class="{{ $reviewRowBorderClass }}" @endif>
														<td class="align-middle">
															@php
																$showRequestReviewCheckbox = (
																	$this->isReceivingAnalystReviewTab()
																	&& ! $hasBatch
																	&& in_array($instance->status, ['in_review', 'In Review', 'received'], true)
																) || (
																	$status === 'Samples Request Review'
																	&& $workflowSubTab === 'in_review'
																	&& ! $hasBatch
																	&& in_array($instance->status, ['in_review', 'In Review'], true)
																) || (
																	$status === 'Samples Receiving'
																	&& ! $this->isReceivingReviewOutcomeTab()
																	&& ! $hasBatch
																	&& in_array($instance->status, ['submitted', 'in_review', 'received'], true)
																);
																$showInstanceRowCheckbox = $status === 'Samples Receiving'
																	|| $showRequestReviewCheckbox;
															@endphp
															@if($showInstanceRowCheckbox)
																<input type="checkbox"
																	wire:model="selectedFormInstanceIds"
																	data-instance-select
																	value="{{ $instance->id }}"
																	class="mr-1"
																	title="Select"
																	@if($showRequestReviewCheckbox && $status !== 'Samples Receiving')
																		name="submission_form_instance_id[]"
																		data-source-selection="1"
																		data-selection-purpose="request-review"
																		form="dispatch-to-labs-modal-form"
																		data-form-number="{{ $instance->getDocumentControlNumber() ?? $instance->form_number ?? 'Pending' }}"
																		data-form-name="{{ $instance->receivingFormDisplayName() }}"
																		data-customer-name="{{ $instanceCustomerName }}"
																		data-customer-email="{{ $instanceCustomerEmail }}"
																		data-customer-phone="{{ $instanceCustomerPhone }}"
																		data-customer-address="{{ $instanceCustomerAddress }}"
																		data-sample-type="{{ implode(', ', $formSampleTypeNames) }}"
																		data-number-samples="{{ $sampleCount }}"
																		data-request-date="{{ $instanceRequestDate }}"
																		data-mode-of-work="{{ in_array($instance->priority, ['high', 'urgent'], true) ? 'Express' : 'Normal' }}"
																	@endif>
															@else
																<span class="text-muted small">-</span>
															@endif
														</td>
														<td nowrap class="workflow-requests-actions-cell">
															<div class="d-flex align-items-center flex-nowrap workflow-row-actions">
																@if(in_array($status, ['Samples Receiving', 'Samples Request Review'], true) && $instance->activePendingIntray && (string) $instance->activePendingIntray->to_user_id === (string) auth()->id())
																	<button type="button"
																		class="btn btn-sm rm-act-btn rm-act-btn--view"
																		wire:click="completeIntray('{{ $instance->id }}')"
																		wire:confirm="Mark this intray task as complete?"
																		title="Complete intray">
																		<i class="mdi mdi-check-circle-outline"></i>
																	</button>
																@endif
																@if($instance->isDraft())
																	<a href="{{ route('submission-forms.instances.fill', [$instance->submissionForm, $instance]) }}"
																		class="btn btn-sm rm-act-btn rm-act-btn--edit"
																		title="Edit">
																		<i class="mdi mdi-pencil-outline"></i>
																	</a>
																	<button type="button"
																		wire:click="deleteSubmissionForm('{{ $instance->id }}')"
																		wire:confirm="Are you sure you want to delete this draft?"
																		class="btn btn-sm rm-act-btn rm-act-btn--delete"
																		title="Delete draft">
																		<i class="mdi mdi-delete"></i>
																	</button>
																@else
																	<a href="{{ route('submission-forms.instances.show', [$instance->submissionForm, $instance]) }}"
																		class="btn btn-sm rm-act-btn rm-act-btn--view"
																		title="View details">
																		<i class="mdi mdi-eye"></i>
																	</a>
																	@php
																		$enquiryStatus = trim((string) ($instance->sampleSubmissionRequest?->status ?? ''));
																		$normalizedEnquiryStatus = strtolower($enquiryStatus);
																		$reviewQuotationId = $instance->sampleSubmissionRequest?->current_quotation_header_id
																			?? $instance->sampleSubmissionRequest?->currentQuotation?->id
																			?? $instance->sampleSubmissionRequest?->accepted_quotation_header_id;
																	@endphp
																	@if(($normalizedEnquiryStatus === strtolower(\App\Models\SampleSubmissionRequest::STATUS_QUOTATION_UNDER_REVIEW)
																			|| str_contains($normalizedEnquiryStatus, 'quotation under review'))
																		&& !empty($reviewQuotationId))
																		<button type="button"
																			wire:click="openReviewQuotationByEnquiryId('{{ $instance->sampleSubmissionRequest->id }}')"
																			class="btn btn-sm rm-act-btn rm-act-btn--view"
																			title="Review quotation">
																			<i class="mdi mdi-file-document-edit-outline"></i>
																		</button>
																	@endif
																	@if($status === 'Samples Receiving' && $workflowSubTab === 'submitted' && $instance->sampleSubmissionRequest && $instance->sampleSubmissionRequest->status === \App\Models\SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED)
																		<button type="button"
																			class="btn btn-sm rm-act-btn rm-act-btn--view"
																			title="Record PO"
																			wire:click="openPoCaptureModal('{{ $instance->sampleSubmissionRequest->id }}')">
																			<i class="mdi mdi-file-document-edit-outline"></i>
																		</button>
																	@endif
																@endif
																{{-- Interzone transfer removed with zones
																@if($status === 'Samples Request Review' && $hasBatch)
																	@foreach($instance->batches as $batch)
																		<button type="button"
																			class="btn btn-sm rm-act-btn rm-act-btn--muted"
																			title="Interzone transfer"
																			@click.prevent="openInterzoneTransfer('batch', { batchId: '{{ $batch->id }}' })">
																			<i class="mdi mdi-map-marker-path"></i>
																		</button>
																	@endforeach
																@endif
																--}}
															</div>
														</td>
														<td nowrap>
															@php
																$testRequestNumber = $instance->getDocumentControlNumber()
																	?? $instance->form_number
																	?? null;
															@endphp
															@if($testRequestNumber)
																<span class="ls-table-id ls-table-emphasis">{{ $testRequestNumber }}</span>
															@else
																<span class="text-muted small">—</span>
															@endif
														</td>


														<td>
															@if($instance->crmCustomer)
																<span class="ls-table-emphasis" title="Portal customer">{{ $instance->crmCustomer->name }}</span>
															@elseif($instance->submittedBy)
																<span class="text-muted small" title="LIMS user submission">{{ $instance->submittedBy->name }}</span>
															@else
																<span class="text-muted small">—</span>
															@endif
														</td>
														@php
															$linkedEnquiry = $instance->sampleSubmissionRequest;
														@endphp
														@if($status === 'Samples Receiving')
															<td>
																@if($linkedEnquiry)
																	<span class="small">{{ $linkedEnquiry->commercialStatus() }}</span>
																@else
																	<span class="text-muted small">—</span>
																@endif
															</td>
														@endif
														@if($status === 'Samples Receiving')
															<td>
																@php
																	$originChannel = $instance->receivingOriginChannel();
																@endphp
																@if($originChannel === 'portal')
																	<span class="badge badge-primary"><i class="mdi mdi-web mr-1"></i> Portal</span>
																@elseif($originChannel === 'walk_in')
																	<span class="badge badge-info"><i class="mdi mdi-walk mr-1"></i> Walk-in</span>
																@elseif($originChannel === 'scheduled')
																	<span class="badge badge-success"><i class="mdi mdi-calendar-clock mr-1"></i> Scheduled</span>
																	<span class="badge badge-light border text-dark ml-1">Sampling contract</span>
																@else
																	<span class="badge badge-secondary">{{ ucwords(str_replace(['_', '-'], ' ', $originChannel)) }}</span>
																@endif
															</td>
														@endif
														@if(! in_array($status, ['Samples Receiving', 'Samples Request Review'], true))
															<td>
																@if($hasBatch)
																	<span class="workflow-status-chip" style="--chip-accent: #28a745;">
																		{{ $instance->batches->count() }} Batch{{ $instance->batches->count() > 1 ? 'es' : '' }} created
																	</span>
																@else
																	<span class="workflow-status-chip" style="--chip-accent: #dc3545;">
																		Not created
																	</span>
																@endif
															</td>
															<td nowrap>
																@if($hasBatch)
																	@foreach($instance->batches as $batch)
																		<a href="{{ route('view-batch-details', ['batch' => $batch->id, 'client' => 0, 'portal' => 0, 'status' => $status]) }}">
																			{{ $batch->batch_code }}
																		</a>@if(!$loop->last) @endif
																	@endforeach
																@else
																	<span class="text-muted small">—</span>
																@endif
															</td>
														@endif
														<td nowrap class="ls-table-meta">{{ implode(', ', $formSampleTypeNames) ?: 'N/A' }}</td>
														<td class="text-center ls-table-meta">
															{{ $testsRequiredCount }}
														</td>
														@if($status === 'Samples Request Review')
															@php
																$acceptanceForm = $instance->analysisAcceptanceForms->first();
																$acceptanceStatus = $acceptanceForm?->status;
																$hasLabAcceptance = $acceptanceStatus === \App\Models\Sampleworkflow\AnalysisAcceptanceForm::STATUS_COMPLETED;
															@endphp
															<td>
																@if($hasLabAcceptance)
																	<span class="workflow-status-pill workflow-status-pill--complete{{ $status === 'Samples Receiving' ? '' : ' badge badge-success' }}">Complete</span>
																@else
																	<span class="workflow-status-pill workflow-status-pill--pending{{ $status === 'Samples Receiving' ? '' : ' badge badge-secondary' }}">Pending</span>
																@endif
															</td>
														@endif
														<td nowrap class="ls-table-meta">
															@if($instance->submitted_at)
																{{ $instance->submitted_at->format('Y-m-d H:i') }}
															@else
																<span class="text-muted small">Not submitted</span>
															@endif
														</td>
													</tr>
												@endforeach
											</tbody>
										</table>
									</div>
									@if(in_array($status, ['Samples Receiving', 'Samples Request Review'], true))
									</div>
									@endif
									<div class="mt-2">
										{{ $submissionForms->links() }}
									</div>
								@endif

								@if($portalSubmissions && $portalSubmissions->count() > 0)
									<!-- Portal Submissions (Legal Requests) Table -->
									<div class="workflow-board-section-label mt-4">
										<i class="mdi mdi-scale-balance"></i> Legal Sample Submission Requests
									</div>
									<div class="table-responsive">
										<table class="table table-hover workflow-table">
											<thead>
												<tr>
													<th style="width: 40px;"></th>
													<th>Actions</th>
													<th>Request Number</th>
													<th>Customer</th>
													<th>Offence</th>
													<th>Case Number</th>
													<th>Sample Count</th>
													<th>Status</th>
													<th>Submitted Date</th>
													<th>Batch</th>
												</tr>
											</thead>
											<tbody>
												@foreach($portalSubmissions as $request)
													@php
														$hasBatch = (bool) $request->sample_header_id;
														$statusText = ucfirst(str_replace(['_', '-'], ' ', $request->status));
														$color = match(strtolower($request->status)) {
															'submitted' => '#28a745',
															'pending_reception', 'received_at_lab' => '#17a2b8',
															'booking_date_approved', 'booking_date_rescheduled' => '#ffc107',
															'in_review' => '#6c757d',
															default => '#6c757d'
														};
													@endphp
													<tr>
														<td>
															<input type="checkbox" name="portal_submission_id[]" value="{{ $request->id }}" data-source-selection="1">
														</td>
														<td nowrap>
															<a href="{{ route('sample-submission-requests.show', $request) }}" class="btn btn-sm rm-act-btn rm-act-btn--view" title="View details">
																<i class="mdi mdi-eye"></i>
															</a>
															@if(($request->status ?? '') === \App\Models\SampleSubmissionRequest::STATUS_QUOTATION_UNDER_REVIEW && !empty($request->current_quotation_header_id))
																<button type="button"
																	wire:click="openProcessEnquiryByEnquiryId('{{ $request->id }}')"
																	class="btn btn-sm rm-act-btn rm-act-btn--view"
																	title="Review quotation">
																	<i class="mdi mdi-file-document-edit-outline"></i>
																</button>
															@endif
														</td>
														<td>
															<strong>{{ $request->unique_identification ?? $request->getFormattedNumberAttribute() }}</strong>
														</td>
														<td>{{ $request->customer->name ?? 'N/A' }}</td>
														<td>{{ $request->offence ?? 'N/A' }}</td>
														<td>{{ $request->case_no ?? 'N/A' }}</td>
														<td class="text-center">{{ $request->exhibits->count() }}</td>
														<td>
															<span class="workflow-status-chip" style="--chip-accent: {{ $color }};">
																{{ $statusText }}
															</span>
														</td>
														<td nowrap>{{ optional($request->submitted_by_date)->format('Y-m-d') ?? 'N/A' }}</td>
														<td nowrap>
															@if($hasBatch)
																<a href="{{ route('view-batch-details', ['batch' => $request->sample_header_id, 'client' => 0, 'portal' => 0, 'status' => $status]) }}">
																	{{ $request->batch->batch_code ?? 'View Batch' }}
																</a>
															@else
																<span class="text-muted small">—</span>
															@endif
														</td>
													</tr>
												@endforeach
											</tbody>
										</table>
									</div>
									<div class="mt-2">
										{{ $portalSubmissions->links() }}
									</div>
								@endif
							@else
								<div class="text-center py-5 workflow-empty-state">
									<i class="mdi mdi-file-document-outline" style="font-size: 3rem;"></i>
									<h5 class="mt-3">No submissions found</h5>
									<p class="mb-0">
										@if($isCommercialSubmittedTab ?? false)
											No commercial enquiries match your current filters.
										@elseif($status === 'Samples Receiving' && $workflowSubTab === 'ready_for_reception')
											No test requests are ready for physical receive. Quotation must be accepted first.
										@else
											No portal-submitted requests match your current filters.
										@endif
									</p>
								</div>
							@endif
						@elseif($status !== 'Samples Receiving' && $status !== 'Samples Request Review')
							<!-- Batches Table -->
							@if($status == 'Finished Sample')
								<div class="mb-4">
									<div class="workflow-board-section-label">
										<i class="mdi mdi-filter-outline"></i>
										Apply filters
									</div>
									<form action="{{ route('sample-workflow', ['status' => 'Finished Sample']) }}" method="get">
										<div class="workflow-board-filter-nested">
										<div class="row">
											<div class="col-md-4">
												<div class="form-group">
													<label for="" class="control-label">Receipt Date From</label>
													<input type="date" name="receipt_from" id="" value="{{$finishedFilter['receipt_from'] ?? ''}}"
														class="form-control">
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label for="" class="control-label">Receipt Date To</label>
													<input type="date" name="receipt_to" id="" value="{{$finishedFilter['receipt_to'] ?? ''}}"
														class="form-control">
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label for="" class="control-label">Customer</label>
													<select name="customer_id" id="" class="form-control">
														<option value="">Select Customer</option>
														@foreach($customers as $customer)
															<option value="{{$customer->id}}" {{isset($finishedFilter['customer_id']) && $customer->id == $finishedFilter['customer_id'] ? 'selected' : ''}}>{{$customer->name}}</option>
														@endforeach
													</select>
												</div>
											</div>
											<div class="col-md-12">
												<div class="form-group">
													<label for="" class="control-label">Sample Codes <small>(can provide multiple sample codes comma
															separated)</small></label>
													<input type="text" name="sample_codes" value="{{$finishedFilter['sample_codes'] ?? ''}}"
														class="form-control">
												</div>
											</div>
											<input type="hidden" name="has_filter" value="1">
											<div class="col-md-12">
												<button type="submit" class="btn btn-sm btn-outline-primary float-right btn-action-sm"><i
														class="mdi mdi-filter"></i> Apply</button>
											</div>
										</div>
										</div>
									</form>
								</div>
							@endif

							@if($batches && $batches->count() > 0)
								<div class="table-responsive">
									<table class="table table-hover workflow-table">
										<thead>
											<tr>
												<th style="width: 40px;"></th>
												<th>Batch Code</th>
												@if($status !== 'Samples En-Route' && auth()->user()->CheckViewQcSample())
													<th>Is Qc</th>
												@endif
												@if($status !== 'Samples En-Route')
													<th>Sample Codes</th>
													<th>Draft Invoice</th>
													<th>Stage</th>
												@else
													<th>Client</th>
												@endif
												@if($status !== 'Samples En-Route')
													<th>Client / LPO Ref</th>
												@endif
												<th nowrap>Receipt Date</th>
												@if($status !== 'Samples En-Route')
													<th nowrap>Date Collected</th>
													<th nowrap>Target Date</th>
													<th nowrap>Status Days</th>
													<th>Samples</th>
													@if($status != 'Samples In Lab')
														<th>Client Unit</th>
													@endif
													<th>Lab</th>
													<th nowrap>Sample Type</th>
													<th>Routine</th>
												@endif
												@if($status == 'Samples In Lab')
													<th>Assigned User</th>
												@endif
												<th>Actions</th>
											</tr>
										</thead>
										<tbody>
											@foreach ($batches as $item)
												@php
												$targetDateRaw = optional($item->get_target_date)->date;
												$statusDays = \App\Livewire\Sampleworkflow\WorkflowBoard::statusDaysUntilTarget($targetDateRaw);
												$sample_codes = $item->samples->pluck('sample_code')->toArray();
												$sample_count = count($sample_codes);
												@endphp
												<tr class="batch-row crm-customer-{{ $item->crm_customer_id }}" data-class="{{ $item->crm_customer_id }}">
													<td>
														<input type="checkbox" name="batch_id[]" value="{{$item->id}}" data-lab-batch-select data-batch-code="{{$item->batch_code}}" data-customer-id="{{ $item->crm_customer_id }}">
													</td>
													<td nowrap>
														<a href="{{ route('view-batch-details', ['batch' => $item->id, 'client' => 0, 'portal' => 0, 'status' => $status]) }}">
															<strong>{{ $item->batch_code }}</strong>
														</a>
													</td>
													@if($status !== 'Samples En-Route' && auth()->user()->CheckViewQcSample())
														<td>{{ $item->is_qc ? 'Yes' : 'No' }}</td>
													@endif
													@if($status !== 'Samples En-Route')
														<td>
															<small>{{ $sample_codes[0] ?? '' }} ... {{ end($sample_codes) ?? '' }}</small>
														</td>
														<td>{{ $item->invoice->invoice_number ?? 'N/A' }}</td>
														<td>{{ $item->prelim_batch_status }}</td>
													@else
														<td>{{ $item->client->name ?? 'N/A' }}</td>
													@endif
													@if($status !== 'Samples En-Route')
														<td>{{ $item->client->name ?? 'N/A' }} / {{ $item->lpo_ref ?? 'N/A' }}</td>
													@endif
													<td nowrap>{{ $item->receipt_date }}</td>
													@if($status !== 'Samples En-Route')
														<td nowrap>{{ $item->date_collected }}</td>
														<td nowrap>{{ $targetDateRaw ? \Illuminate\Support\Carbon::parse($targetDateRaw)->format('Y-m-d') : 'N/A' }}</td>
														<td nowrap @class(['text-danger font-weight-bold' => $statusDays !== null && $statusDays < 0])>{{ \App\Livewire\Sampleworkflow\WorkflowBoard::formatStatusDaysLabel($statusDays) }}</td>
														<td>{{ $sample_count }}</td>
														@if($status != 'Samples In Lab')
															<td>{{ $item->client_unit ?? 'N/A' }}</td>
														@endif
														<td>{{ $item->lab->name ?? 'N/A' }}</td>
														<td nowrap>{{ $item->sample_type->name ?? 'N/A' }}</td>
														<td>{{ $item->is_routine ? 'Yes' : 'No' }}</td>
													@endif
													@if($status == 'Samples In Lab')
														@php
															$batchAssignee = $batchAssignmentMap[(string) $item->id] ?? null;
														@endphp
														<td>
															@if($batchAssignee && ($batchAssignee['name'] ?? '') !== '')
																<span class="badge badge-light border">{{ $batchAssignee['name'] }}</span>
															@else
																<span class="text-muted">Unassigned</span>
															@endif
														</td>
													@endif
													<td nowrap>
														<a href="{{ route('view-batch-details', ['batch' => $item->id, 'client' => 0, 'portal' => 0, 'status' => $status]) }}" class="btn btn-sm btn-outline-info">
															<i class="mdi mdi-eye"></i>
														</a>
													</td>
												</tr>
											@endforeach
										</tbody>
									</table>
								</div>
								<div class="mt-3 d-flex justify-content-between align-items-center">
									<div class="text-muted" style="font-size: 0.85rem;">
										Showing {{ $batches->firstItem() }}–{{ $batches->lastItem() }} of {{ $batches->total() }} batches
									</div>
									<div>
										{{ $batches->links() }}
									</div>
								</div>
							@else
								<div class="text-center py-5 workflow-empty-state">
									<i class="mdi mdi-file-document-outline" style="font-size: 3rem;"></i>
									<h5 class="mt-3">No batches found</h5>
									<p class="mb-0">No batches match your current filters.</p>
								</div>
							@endif
						@endif
					</div>
				</div>
			</div>
		</div>
	@endif
	@push('script2')
		@if($showDecontaminationModal)
			<div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(15, 23, 42, 0.45);">
				<div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
					<div class="modal-content">
						<div class="modal-header bg-light">
							<h5 class="modal-title">
								<i class="mdi mdi-biohazard mr-2 text-danger"></i>
								Decontamination Samples
							</h5>
							<button type="button" class="close" wire:click="closeDecontaminationModal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<div class="alert alert-info">
								<strong>What are decontamination samples?</strong>
								<div class="small mt-1">
									These are swab checks collected from decontamination areas/rooms to verify cleaning effectiveness before routine analysis.
								</div>
							</div>

							<div class="row">
								<div class="col-md-4">
									<label class="font-weight-bold">Date <span class="text-danger">*</span></label>
									<input type="date" class="form-control" wire:model.defer="decontaminationDate">
									@error('decontaminationDate') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
								</div>
								<div class="col-md-4">
									<label class="font-weight-bold">Field (Officer) <span class="text-danger">*</span></label>
									<input type="text" class="form-control" wire:model.defer="decontaminationOfficer" placeholder="Officer name">
									@error('decontaminationOfficer') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
								</div>
								<div class="col-md-4">
									<label class="font-weight-bold">Lab <span class="text-danger">*</span></label>
									<select class="form-control" wire:model.live="decontaminationLabId">
										<option value="">Select lab...</option>
										@foreach($this->decontaminationLabs as $lab)
											<option value="{{ $lab->id }}">{{ $lab->code }} - {{ $lab->name }}</option>
										@endforeach
									</select>
									@error('decontaminationLabId') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
								</div>
							</div>

							<div class="alert alert-light border mt-3 mb-3">
								<i class="mdi mdi-information-outline text-primary mr-1"></i>
								<strong>Analysis note:</strong> Test kits, type of instrument, and run IDs will be populated during analysis.
							</div>

							<div class="table-responsive">
								<table class="table table-bordered table-sm align-middle">
									<thead class="thead-light">
										<tr>
											<th style="width: 70%;">Item (Name of area)</th>
											<th style="width: 30%;">Swabbing</th>
										</tr>
									</thead>
									<tbody>
										@if($decontaminationLabId === '')
											<tr>
												<td colspan="2" class="text-muted text-center py-3">Select a lab to load decontamination areas.</td>
											</tr>
										@else
											@forelse($this->selectedLabDecontaminationAreasGrouped as $sectionLabel => $areas)
												<tr class="table-light">
													<td colspan="2" class="font-weight-bold text-primary">
														<i class="mdi mdi-layers-triple mr-1"></i>{{ $sectionLabel }}
													</td>
												</tr>
												@foreach($areas as $area)
													<tr wire:key="decon-swab-{{ $area->id }}">
														<td>{{ $area->name }}</td>
														<td>
															<div class="d-flex align-items-center gap-3">
																<label class="mb-0 mr-3">
																	<input type="radio" wire:model.live="decontaminationSwabbing.{{ $area->id }}" value="yes"> Yes
																</label>
																<label class="mb-0">
																	<input type="radio" wire:model.live="decontaminationSwabbing.{{ $area->id }}" value="no"> No
																</label>
															</div>
															@error('decontaminationSwabbing.' . $area->id)
																<div class="text-danger small mt-1">{{ $message }}</div>
															@enderror
														</td>
													</tr>
												@endforeach
											@empty
												<tr>
													<td colspan="2" class="text-muted text-center py-3">No decontamination areas configured for the selected lab.</td>
												</tr>
											@endforelse
										@endif
									</tbody>
								</table>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" wire:click="closeDecontaminationModal">Cancel</button>
							<button type="button" class="btn btn-primary" wire:click="saveDecontaminationLog">
								<i class="mdi mdi-content-save mr-1"></i> Save Decontamination Log
							</button>
						</div>
					</div>
				</div>
			</div>
		@endif
		<div class="modal fade" id="get-batch-tat" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header text-center">
						<h6> <i class="mdi mdi-alert"></i> Batches with Today as Expected Date Out</h6>
					</div>
					<div class="modal-body">
						<table class="table table-bordered table-sm table-stripped">
							<thead class="bg-light">
								<th>Batch Code</th>
								<th>Tat Date</th>
							</thead>
							<tbody>
								@foreach($tatTodayBatches as $tatBatch)
									<tr wire:key="tat-today-{{ $tatBatch->id }}">
										<td>
											<a href="{{ route('view-batch-details', ['batch' => $tatBatch->id, 'client' => 0, 'portal' => 0, 'status' => $tatBatch->status]) }}">
												{{ $tatBatch->batch_code }}
											</a>
										</td>
										<td @class([
											'text-danger' => $tatBatch->is_late,
											'text-warning' => $tatBatch->is_today,
										])>{{ $tatBatch->tat_date }}</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>
					<div class="modal-footer">
						<span class="btn btn-sm btn-default text-danger" data-dismiss="modal">Close</span>
					</div>
				</div>
			</div>
		</div>
		@if(in_array($status, ['Sample Approval', 'Sample Verification']))
			<div class="modal fade" id="awaiting-approval-modal" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h6><i class="mdi mdi-alert"></i> Batches Awaiting Approval</h6>
						</div>
						<div class="modal-body">
							<div class="data">
								<table class="table table-sm table-bordered table-stripped">
									<thead class="bg-light">
										<th>Batch</th>
										<th>Approver</th>
									</thead>
									<tbody></tbody>
								</table>
							</div>
						</div>
						<div class="modal-footer">
							<span class="btn btn-sm btn-default text-danger" data-dismiss="modal">Close</span>
						</div>
					</div>
				</div>
			</div>
		@endif
		@if(isset($status) && in_array($status, array("Samples En-Route", "Samples Request Review", "Samples Reception", "Samples In Lab")))
			<div class="modal fade" id="inter-lab-add" data-backdrop="static" data-keyboard="false" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<form action="{{route('create_sample_inter_lab_log')}}" method="post">
							@csrf
							<div class="modal-body">
								<div class="alert alert-primary p-2 d-flex">
									<i class="mdi mdi-alert-decagram-outline" style="font-size: 30px"></i>
									<span class="p-2">
										Initiate Interlab for all samples in the following batches below by providing the
										information below
									</span>
								</div>
								<div class="form-group">
									<label for="" class="control-label">To Lab</label>
									<select name="to_lab_section_id" id="" class="form-control">
										@foreach($labsections as $lab)
											<option value="{{$lab->id}}">{{$lab->code}} - {{$lab->name}}</option>
										@endforeach
									</select>
								</div>
								<div class="form-group">
									<label for="" class="control-label">Quantity</label>
									<input type="text" name="quantity" value="" class="form-control">
								</div>
								<div class="form-group">
									<label for="" class="control-label">Expected Date</label>
									<input type="date" name="expected_date" value="" id="" class="form-control">
								</div>
								<div class="form-group">
									<label for="" class="control-label">Prelim Date</label>
									<input type="date" name="prelim_date" id="" class="form-control">
								</div>
								<div class="form-group">
									<label for="" class="control-label">Remark</label>
									<textarea name="remarks" id="" cols="30" rows="5" class="form-control"></textarea>
								</div>
								<div class="form-group">
									<label for="" class="control-label">Notify</label>
									<select name="notify_user" id="" class="form-control notify_user">
										@foreach($users as $user)
											<option value="{{$user->id}}">{{$user->name}}</option>
										@endforeach
									</select>
								</div>
								<div class="form-group">
									<label for="" class="control-label">Notify</label>
									<select name="also_notify[]" multiple id="" class="form-control also_notify">
										@foreach($users as $user)
											<option value="{{$user->id}}">{{$user->name}}</option>
										@endforeach
									</select>
								</div>
								<input type="hidden" name="batch_level" value="1">
								<div class="form-group">
									<label class="control-label">Batches</label>
									<div class="selected-batches-interlab"></div>
								</div>
							</div>
							<div class="modal-footer">
								<Button type="submit" class="btn btn-outline-primary btn-sm submit-button"><i
										class="mdi mdi-swap-horizontal-bold"></i> Initiate</Button>
								<span class="btn btn-sm btn-default text-danger" data-dismiss="modal">Cancel</span>
							</div>
						</form>
					</div>
				</div>
			</div>
			<div id="dispatch-to-labs-modal-approve" data-backdrop="static" data-keyboard="false" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<!-- Modal content-->
					<div class="modal-content">
						<div class="modal-header" style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(248, 249, 250, 0.8) 100%); backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px); box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05), inset 0 1px 2px rgba(255, 255, 255, 0.8); border-bottom: 1px solid rgba(0, 0, 0, 0.08);">
							<h4 class="modal-title"><i class="mdi mdi-clipboard-arrow-right"></i> Generate Draft Invoice</h4>
							<button type="button" class="close" data-dismiss="modal">&times;</button>
						</div>
						<div class="modal-body">
							<div class="alert alert-info">
								<i class="mdi mdi-information"></i> You will be redirected to the Draft Invoice Wizard to complete the process.
							</div>

							<div class="form-group">
								<label class="control-label"><strong>Selected Batches:</strong></label>
								<div class="selected-batches-request-approve p-3 bg-light rounded"></div>
							</div>

							<p class="text-muted mt-3">
								The wizard will guide you through:
								<ul>
									<li>Customer Dynamics mapping</li>
									<li>Analysis type to invoicable item mapping</li>
									<li>Adding additional fees and charges</li>
									<li>Reviewing and generating the draft invoice</li>
								</ul>
							</p>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-success btn-sm proceed-to-wizard-btn">
								<i class="mdi mdi-arrow-right"></i> Proceed
							</button>
							<button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
						</div>
					</div>
				</div>
			</div>

		@endif
		@if ($status == "Reports for Collection")
			<div id="send-email-reports-modal" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<!-- Modal content-->
					<form class="modal-content" id="print-labels-form" method="POST" action="{{ route('send-out-email-reports') }}"
						enctype="multipart/form-data">
						@csrf
						<div class="modal-header">
							<h4 class="modal-title"><i class="mdi mdi-printer"></i> Email Report(s) To Client </h4>
						</div>
						<div class="modal-body">
							<div class="form-group">
								<label class="control-label">Client Contacts <span
										class="btn btn-sm btn-default text-primary add-contact"><i
											class="mdi mdi-plus"></i></span></label>
								<select class="form-control" name="contacts[]" required multiple
									placeholder="Select Contact..."></select>
							</div>
							<div class="form-group">
								<label for="" class="control-label">Other Emails to CC</label>
								<input type="text" name="cc_emails" class="form-control" placeholder="a@gmail.com,b@gmail.com...">
							</div>
							<div class="add-contact-fields hidden card bg-light mb-3"
								style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;">
								<div class="card-body">
									<div class="row border-bottom">
										<div class="col-sm-4">
											<div class="form-group">
												<label class="control-label">Title *</label>
												<select name="title" class="form-control title" placeholder="Title...">
													<option></option>
													@foreach (getModulePreconfig("Designation", "Personnel-Management") as $item)
														<option value="{{ $item->id }}">{{ $item->name }}</option>
													@endforeach
												</select>
											</div>
										</div>
										<div class="col-sm-4">
											<div class="form-group">
												<label class="control-label">First Name <span class="text-danger">*</span></label>
												<input type="text" class="form-control first_name" name="first_name" value=""
													placeholder="First Name..." />
											</div>
										</div>
										<div class="col-sm-4">
											<div class="form-group">
												<label class="control-label">Middle Name</label>
												<input type="text" class="form-control middle_name" name="second_name" value=""
													placeholder="Middle Name..." />
											</div>
										</div>
										<div class="col-sm-4">
											<div class="form-group">
												<label class="control-label">Surname</label>
												<input type="text" class="form-control surname" name="third_name" value=""
													placeholder="Surame..." />
											</div>
										</div>
									</div>

									<div class="row mt-2">
										<div class="col-sm-4">
											<div class="form-group">
												<label class="control-label">Job Occupation</label>
												<input type="text" class="form-control job_occupation" name="job_occupation"
													value="" placeholder="Job Title..." />
											</div>
										</div>
										<div class="col-sm-4">
											<div class="form-group">
												<label class="control-label">Company Units <span class="text-danger">*</span>
												</label>
												<select class="form-control unit_name" name="unit_name[]" multiple>
													<option value="">Select Company Unit...</option>
													{{-- @foreach ($customer->units as $unit)
													<option value="{{ $unit->name }}">{{ $unit->name }}</option>
													@endforeach --}}
												</select>
											</div>
										</div>
										<div class="col-sm-4">
											<div class="form-group">
												<label class="control-label">Email <span class="text-danger">*</span></label>
												<input type="email" class="form-control email" name="email" value=""
													placeholder="Email..." />
											</div>
										</div>
									</div>

									<div class="row border-bottom">
										<div class="col-sm-4">
											<div class="form-group">
												<label class="control-label">Telephone <span class="text-danger">*</span></label>
												<input type="text" class="form-control telephone" name="telephone" value=""
													placeholder="Telephone..." />
											</div>
										</div>
										<div class="col-sm-4">
											<div class="form-group">
												<label class="control-label">Mobile</label>
												<input type="text" class="form-control mobile" name="mobile" value=""
													placeholder="Mobile..." />
											</div>
										</div>

									</div>

									<div class="row mt-2">
										<div class="col-sm-4">
											<div class="form-group">
												<label class="control-label"><input type="checkbox" value="1"
														name="receive_price_list" class="receive_price_list" /> Receives
													Pricelist?</label>
											</div>
										</div>
										<div class="col-sm-4">
											<div class="form-group">
												<label class="control-label"><input type="checkbox" value="1" class="receive_report"
														name="receive_report" /> Receives Report?</label>
											</div>
										</div>
										<div class="col-sm-4">
											<div class="form-group">
												<label class="control-label"><input type="checkbox" value="1"
														class="receive_invoice" name="receive_invoice" /> Receives Invoice?</label>
											</div>
										</div>

									</div>
									<div class="row">
										<div class="col-md-12">
											<span class="float-right btn btn-default btn-sm text-danger close-add-contact">Close
												Setion</span>
											<span class="float-right btn btn-sm btn-primary save-add-contact"><i
													class="mdi mdi-content-save"></i> Save Contact</span>
										</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label">Batches</label>
								<div class="selected-batches"></div>
							</div>
							<div class="form-group">
								<label class="control-label">Email Body</label>
								<textarea name="email_body" class="form-control" placeholder="Email Body"></textarea>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-info btn-sm send-report-to-client-btn" data-dismiss="modal"><i
									class="mdi mdi-send"></i> Email Reports</button>
							<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
						</div>

					</form>
				</div>
			</div>
		@endif
		@if ($status === 'Samples Receiving')
			<div id="receive-sample-modal" class="modal fade" tabindex="-1" role="dialog">
				<div id="receive-sample-modal-dialog" class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
					<div class="modal-content receive-sample-modal-content border-0 shadow">
						<div class="modal-header receive-sample-modal-header border-0">
							<div>
								<h5 class="modal-title mb-0" id="receive-sample-modal-title-wrap">
									<i id="receive-sample-modal-icon" class="mdi mdi-clipboard-text text-primary mr-2"></i>
									<span id="receive-sample-modal-title-text">Test Request Form</span>
								</h5>
								<p id="receive-sample-modal-subtitle" class="text-muted small mb-0 mt-1 d-none"></p>
							</div>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							@livewire('sampleworkflow.receive-sample-request', key('receive-sample-request'))
						</div>
					</div>
				</div>
			</div>
			<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
			<div id="request-additional-info-modal" class="modal fade" tabindex="-1" role="dialog">
				<div class="modal-dialog modal-lg modal-dialog-centered">
					<div class="modal-content receive-sample-modal-content border-0 shadow">
						<div class="modal-header receive-sample-modal-header border-0">
							<div>
								<h5 class="modal-title mb-1">
									<i class="mdi mdi-file-document-edit-outline text-primary mr-2"></i>
									Request more info
								</h5>
								<p class="text-muted small mb-0">Ask the customer for additional information on requests in review.</p>
							</div>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							@livewire('sampleworkflow.request-additional-info', key('request-additional-info'))
						</div>
					</div>
				</div>
			</div>
			<div id="send-for-analyst-review-modal" class="modal fade" tabindex="-1" role="dialog">
				<div class="modal-dialog modal-lg modal-dialog-centered">
					<div class="modal-content receive-sample-modal-content border-0 shadow">
						<div class="modal-header receive-sample-modal-header border-0">
							<div>
								<h5 class="modal-title mb-1">
									<i class="mdi mdi-clipboard-arrow-right text-primary mr-2"></i>
									Send for review
								</h5>
								<p class="text-muted small mb-0">Send selected requests to the analyst review queue.</p>
							</div>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							@livewire('sampleworkflow.send-for-analyst-review', key('send-for-analyst-review'))
						</div>
					</div>
				</div>
			</div>
			<div id="subcontract-dispatch-modal" class="modal fade" tabindex="-1" role="dialog">
				<div class="modal-dialog modal-lg modal-dialog-centered">
					<div class="modal-content receive-sample-modal-content border-0 shadow">
						<div class="modal-header receive-sample-modal-header border-0">
							<div>
								<h5 class="modal-title mb-1">
									<i class="mdi mdi-truck-delivery-outline text-primary mr-2"></i>
									Dispatch subcontracting request
								</h5>
								<p class="text-muted small mb-0">Scan the generated system label barcode before dispatching this subcontracting request.</p>
							</div>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							@livewire('sampleworkflow.dispatch-subcontract-request', key('dispatch-subcontract-request'))
						</div>
					</div>
				</div>
			</div>
		@endif
		@if (in_array($status, ['Samples Receiving', 'Samples Request Review'], true))
			<div id="move-to-intray-modal" class="modal fade" tabindex="-1" role="dialog">
				<div class="modal-dialog modal-lg modal-dialog-centered">
					<div class="modal-content receive-sample-modal-content border-0 shadow">
						<div class="modal-header receive-sample-modal-header border-0">
							<div>
								<h5 class="modal-title mb-1">
									<i class="mdi mdi-inbox-arrow-down text-primary mr-2"></i>
									Move to tray
								</h5>
								<p class="text-muted small mb-0">Assign selected requests to another user's intray.</p>
							</div>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body pt-0">
							@livewire('sampleworkflow.move-to-intray', key('move-to-intray'))
						</div>
					</div>
				</div>
			</div>
		@endif
		@if ($status == "Samples Reception" || $status == "Samples Request Review" || $status === 'Samples Receiving' || (in_array($status, ['Samples En-Route'], true) && $workflowSubTab === 'requests'))
			<div id="dispatch-to-labs-modal" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<!-- Modal content-->
					<form id="dispatch-to-labs-modal-form" class="modal-content" method="POST" action="{{ route('change-batch-workflow') }}"
						enctype="multipart/form-data">
						@csrf
						<div class="modal-header">
							<h4 class="modal-title"><i class="mdi mdi-clipboard-arrow-right"></i> Send Labeled Samples for Sample
								Request Review </h4>
						</div>
						<div class="modal-body">
							<input type="hidden" name="status" value="Samples Request Review" />
							<input type="hidden" name="return_status" value="{{ $status }}" />
							<input type="hidden" name="return_tab" value="{{ $workflowSubTab }}" />
							<div id="not-paid-parent"></div>
							<div class="form-group">
								<div class="alert alert-callout alert-primary">
									<i class="fas fa-info-circle"></i> Are you sure you want to send labeled samples for <b>Sample
										Request Review</b>?
								</div>
							</div>
							<div class="form-group mb-0">
								<label class="control-label">Selected Forms</label>
								<div class="selected-submissions-request text-muted small"></div>
							</div>

							<div class="form-check">
								<input class="form-check-input" type="checkbox" class="form-control" name="notification" />
								<label class="form-check-label">
									Send Email Notification
								</label>
							</div>
							<br>
							<div class="form-check">
								<input class="form-check-input" type="checkbox" class="form-control" name="send_message" />
								<label class="form-check-label">
									Send Message
								</label>
							</div>
						</div>
						<div class="modal-footer">
							<button type="submit" class="btn btn-info btn-sm"><i class="mdi mdi-thumb-up"></i> Yes</button>
							<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
						</div>
					</form>
				</div>
			</div>
			<div class="modal fade" id="move-to-lab" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<form action="{{ route('moveToLab') }}" method="post">
							@csrf 
							<div class="modal-body">
								<div class="alert alert-primary p-2 d-flex">
									<i class="mdi mdi-alert-decagram-outline" style="font-size:25px"></i>
									<span class="pl-2 pt-2">Move the following batches to samples in laboratory </span>
								</div>
								<div class="form-group mt-4">
									<label class="control-label">Batches</label>
									<div class="selected-batches-movetolab"></div>
								</div>

							</div>
							<div class="modal-footer">
								<button class="btn btn-sm btn-outline-primary submit-btn" type="submit"><i class="mdi mdi-thumb-up"></i> Yes, Send</button>
								<span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
									
							</div>
						</form>
					</div>
				</div>
			</div>
			<div class="modal fade" id="move-to-labss" role="dialog" data-backdrop="static" data-keyboard="false">
				<div class="modal-dialog modal-xl">
					<div class="modal-content">
						<form action="{{route('moveToLab')}}" id="send-to-lab-form" method="post">
							@csrf
							<div class="modal-body">
								<div class="card border-0">
									<div class="card-body">
										<div class="alert alert-primary p-2 d-flex">
											<i class="mdi mdi-alert-decagram-outline" style="font-size:25px"></i>
											<h6 class="p-2">Confirm you want to send the following batch(es) to Samples In Lab
												stage.<br>Processes to be done:</h6>
										</div>
										<div class="proccesses client-validation border-bottom p-2 d-flex">
											<i class="mdi mdi-minus" style="font-size:25px"></i>
											<span class="p-2">Client validation</span>
										</div>
										<div class="proccesses send-schedule border-bottom p-2 d-flex">
											<i class="mdi mdi-minus" style="font-size:25px"></i>
											<span class="p-2">Sending schedule of analysis</span>
										</div>
										<div class="proccesses create-order border-bottom p-2 d-flex">
											<i class="mdi mdi-minus" style="font-size:25px"></i>
											<span class="p-2">Creating draft invoice</span>
										</div>
										<div class="proccesses sending-order border-bottom p-2 d-flex">
											<i class="mdi mdi-minus" style="font-size:25px"></i>
											<span class="p-2">Sending to Zoho</span>
										</div>
										
										<div class="proccesses send-lab border-bottom p-2 d-flex">
											<i class="mdi mdi-minus" style="font-size:25px"></i>
											<span class="p-2">Sending to Lab</span>
										</div>
										<div class="p-2 alert hidden error-area-header">
											<div class="alert-danger p-2 error-area-body "></div>
										</div>
										<div class="alert alert-success p-2 success-text mt-3 hidden d-flex">
											<i class="mdi mdi-check-decagram text-succes" style="font-size:30px"></i>
											<h6 class="mt-2 pl-3">All processes have been completed successfully!</h6>
										</div>
										<div class="success-loader mt-3 hidden">
											<center>
												<img src="/images/suc.gif" height="250px" width="auto" alt="">
											</center>
										</div>
										<div class="loading-area mt-3 hidden">
											<center>
												<img src="/images/load.gif" height="250px" width="auto" alt="">
											</center>
										</div>
										<div class="invoice-part mt-3"></div>
										<div class="form-group mt-4">
											<label class="control-label">Batches</label>
											<div class="selected-batches-movtolab"></div>
										</div>
									</div>
								</div>
							</div>
							<div class="modal-footer">
								<button class="btn btn-sm btn-outline-primary submit-btn" type="submit"><i class="mdi mdi-thumb-up"></i> Yes,
									Send</button>
									<a href="{{route('sample-workflow', ['status' => $status])}}" class="btn btn-sm dismiss-btn btn-default">Close</a>
							</div>
						</form>
					</div>
				</div>
			</div>

			<div id="dispatch-to-labs-modal-payment-reminder" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<!-- Modal content-->
					<form class="modal-content" method="POST" action="{{ route('send_payment_notification') }}"
						enctype="multipart/form-data">
						@csrf
						<div class="modal-header">
							<h4 class="modal-title"><i class="mdi mdi-clipboard-arrow-right"></i> Send Payment Reminder</h4>
						</div>
						<div class="modal-body">
							<input type="hidden" name="status" value="Samples Request Review" />
							<div class="form-group">
								<div class="alert alert-callout alert-primary">
									<i class="fas fa-info-circle"></i> By confirming this you will send a payment reminder email to
									all the clients of the following batches: </b>?
								</div>
							</div>
							<div class="form-group">
								<label class="control-label">Batches</label>
								<div class="selected-batches-request"></div>
							</div>

						</div>
						<div class="modal-footer">
							<button type="submit" class="btn btn-info btn-sm"><i class="mdi mdi-thumb-up"></i> Yes</button>
							<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
						</div>
					</form>
				</div>
			</div>

			

			<div id="approve-begin-process" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<!-- Modal content-->
					<form class="modal-content" method="POST" action="{{ route('approve_batch_begin_process') }}"
						enctype="multipart/form-data">
						@csrf

						<div class="modal-body">
							<input type="hidden" name="status" value="Samples Request Review" />
							<div class="form-group">
								<div class="alert alert-callout alert-primary">
									<i class="fas fa-info-circle"></i> By clicking Approve, the following batches will proceed to
									Laboratory without payment!
								</div>
							</div>

							<div class="form-group">
								<label class="control-label">Batches</label>
								<div class="selected-batches-request-approve"></div>
							</div>

						</div>
						<div class="modal-footer">
							<button type="submit" class="btn btn-info btn-sm"><i class="mdi mdi-thumb-up"></i> Approve</button>
							<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
						</div>
					</form>
				</div>
			</div>

			<div id="print-labels-modal" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<!-- Modal content-->
					<form class="modal-content" target="_blank" id="print-labels-form" method="POST"
						action="{{ route('print-labels') }}" enctype="multipart/form-data">
						@csrf
						<div class="modal-header">
							<h4 class="modal-title"><i class="mdi mdi-printer"></i> Print Labels </h4>
						</div>
						<div class="modal-body">
							<div class="form-group">
								<label class="control-label">Label Size</label>
								<select class="form-control" name="label_size" required>
									<option value="small-label">Small</option>
									<option value="normal-label">Normal</option>
								</select>
							</div>
							<div class="form-group">
								<label class="control-label">Batches</label>
								<div class="selected-samples">
									<div class="alert alert-callout alert-danger">
										<i class="fas fa-exclamation-triangle"></i> No batch selected.
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-info btn-sm print-label-btn" data-dismiss="modal"><i
									class="mdi mdi-printer"></i> Print</button>
							<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
						</div>
					</form>
				</div>
			</div>
		@endif
		<div class="modal fade" id="clone-batches" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<form action="{{route('cloneBatchInformation')}}" method="post">
						@csrf
						<div class="modal-body">
							<div class="d-flex alert alert-primary">
								<i class="mdi mdi-alert-decagram-outline" style="font-size: 30px"></i>
								<span class="p-2">Confirm you want to duplicate the following batches below:</span>
							</div>

							<div class="form-group mt-3">
								<label class="control-label">Batches</label>
								<div class="selected-batches-clone"></div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="submit" class="btn btn-sm btn-outline-primary save-clone"><i
									class="mdi mdi-thumb-up"></i> Yes, Clone</button>
							<span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
						</div>
					</form>
				</div>
			</div>
		</div>
		@if($status == 'Samples Reception' || $status == 'Samples Receiving')
			<div class="modal fade" id="send-schedule-analysis" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<form action="{{route('send-batches-soa')}}" method="POST">
							@csrf
							<div class="modal-body">
								<div class="alert alert-primary p-2 d-flex">
									<i class="mdi mdi-alert-decagram" style="font-size: 30px"></i>
									<span class="p-2">Confirm you want to send schedule of analysis for the following batches:
										<br> Ensure all the batches are from the same client</span>
								</div>
								<div class="form-group">
									<label class="control-label">Batch(es)</label>
									<div class="selected-batches-review"></div>
								</div>
							</div>
							<div class="modal-footer">
								<button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-thumb-up"></i> Yes,
									Send</button>
								<span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
							</div>
						</form>
					</div>
				</div>
			</div>
			<div class="modal fade" id="generarate_customer_focus" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<form target="_blank" action="{{route('generateCustomerFocusIndex', ['batch_id' => 0])}}" method="get">

							<div class="modal-body">
								<div class="alert alert-primary p-2 d-flex">
									<i class="mdi mdi-alert-decagram" style="font-size: 30px"></i>
									<span class="p-2">Confirm you want to genarate a batched customer focus of the following batches
										below <br><br>
										<b>Kindly ensure all the batches are from the same client and the doesnot have an already
											signed customer focus</b></span>
								</div>
								<div class="form-group">
									<label for="" class="control-label">Total Amount</label>
									<input type="text" name="invoice_amount" class="form-control" placeholder="Invoice Amount ...">
								</div>
								<div class="form-group">
									<label for="" class="control-label">VAT</label>
									<input type="text" name="vat" class="form-control" placeholder="Vat ...">
								</div>
								<div class="form-group">
									<label for="" class="control-label">Amount Paid</label>
									<input type="text" name="amount_paid" class="form-control" placeholder="Amount Paid...">
								</div>
								<div class="form-group">
									<label for="" class="control-label">Balance</label>
									<input type="text" name="balance" class="form-control" placeholder="Balance ...">
								</div>
								<div class="form-group">
									<label class="control-label">Batch(es)</label>
									<div class="selected-batches-review"></div>
								</div>
								<input type="hidden" name="is_clustered" value="1">
							</div>
							<div class="modal-footer">
								<button type="submit" class="btn btn-outline-primary btn-sm"><i class="mdi mdi-thumb-up"></i> Yes,
									Generate</button>
								<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
							</div>
						</form>
					</div>
				</div>
			</div>


			<div id="delete-batch" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<!-- Modal content-->
					<form class="modal-content" method="POST" action="{{route('delete-batch')}}" enctype="multipart/form-data">
						@csrf

						<div class="modal-header">
							<h4 class="modal-title"><i class="mdi mdi-delete text-danger"></i> Cancel Batch(es) </h4>
						</div>
						<div class="modal-body">
							<input type="hidden" name="status" value="Samples In Lab" />
							<p class="text-center">Are you sure you want to Cancel the following Batch(es) ? </p><br>
							<hr>
							<div class="form-group">
								<label class="control-label">Batch(es)</label>
								<div class="selected-batches-review"></div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="submit" class="btn btn-info btn-sm"><i class="mdi mdi-thumb-up"></i> Yes</button>
							<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
						</div>
					</form>
				</div>
			</div>
			<!-- Add Submission Form Modal -->
			<div class="modal fade" id="add-submission-form-modal" role="dialog">
				<div class="modal-dialog modal-md">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title">
								<i class="mdi mdi-file-document-plus"></i> Select Submission Form
							</h4>
							<button type="button" class="close" data-dismiss="modal">&times;</button>
						</div>
						<div class="modal-body">
							<div id="submission-form-loading" class="text-center py-4">
								<i class="mdi mdi-loading mdi-spin" style="font-size: 2rem;"></i>
								<p class="mt-2">Loading available forms...</p>
							</div>
							
							<div id="submission-form-content" style="display: none;">
								<div class="form-group">
									<label for="submission-form-select" class="control-label">Choose a Form:</label>
									<select class="form-control no-select2" id="submission-form-select" required>
										<option value="">Select a submission form...</option>
									</select>
								</div>
								
								<div id="form-preview" class="mt-3" style="display: none;">
									<div class="card">
										<div class="card-body">
											<h6 class="card-title" id="form-name-preview"></h6>
											<p class="card-text text-muted" id="form-description-preview"></p>
											<small class="text-info">
												<i class="mdi mdi-file-document"></i> <span id="form-sections-count"></span> sections | 
												<i class="mdi mdi-account"></i> Created by <span id="form-creator"></span> | 
												<i class="mdi mdi-calendar"></i> <span id="form-created-date"></span>
											</small>
										</div>
									</div>
								</div>
								
								<div id="no-forms-message" class="alert alert-info text-center" style="display: none;">
									<i class="mdi mdi-information"></i> No published submission forms available.
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
							<button type="button" class="btn btn-primary" id="create-form-instance-btn" disabled>
								<i class="mdi mdi-arrow-right"></i> Continue to Form
							</button>
						</div>
					</div>
				</div>
			</div>
		@endif
		@if($status == 'Samples In Lab')
			<div id="print-labels-modal" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<!-- Modal content-->
					<form class="modal-content" target="_blank" id="print-labels-form" method="POST"
						action="{{ route('print-labels') }}" enctype="multipart/form-data">
						@csrf
						<div class="modal-header">
							<h4 class="modal-title"><i class="mdi mdi-printer"></i> Print Labels </h4>
						</div>
						<div class="modal-body">
							<div class="form-group">
								<label class="control-label">Label Size</label>
								<select class="form-control" name="label_size" required>
									<option value="small-label">Small</option>
									<option value="normal-label">Normal</option>
								</select>
							</div>
							<div class="form-group">
								<label class="control-label">Batches</label>
								<div class="selected-samples">
									<div class="alert alert-callout alert-danger">
										<i class="fas fa-exclamation-triangle"></i> No batch selected.
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-info btn-sm print-label-btn" data-dismiss="modal"><i
									class="mdi mdi-printer"></i> Print</button>
							<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
						</div>
					</form>
				</div>
			</div>

			@if(auth()->user()->can('laboratory.components.sample-workflow.assign-user'))
			<div id="assign-user-modal" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<form class="modal-content" method="POST" action="{{ route('sample-workflow.assign-user') }}" id="assign-user-form" data-users-url="{{ route('sample-workflow.assign-user.users') }}">
						@csrf
						<div class="modal-header">
							<h5 class="modal-title"><i class="mdi mdi-account-arrow-right-outline"></i> Assign User</h5>
							<button type="button" class="close" data-dismiss="modal">&times;</button>
						</div>
						<div class="modal-body">
							<div class="form-group">
								<label class="control-label">Assign to</label>
								<select name="assignee_user_id" id="assign-user-select" class="form-control form-control-sm" required>
									<option value="">Select user</option>
									@foreach(($users ?? collect()) as $assignableUser)
										@php
											$assignableName = trim((string) ($assignableUser->name ?? ''));
											if ($assignableName === '') {
												$nameParts = array_filter([
													trim((string) ($assignableUser->first_name ?? '')),
													trim((string) ($assignableUser->middle_name ?? '')),
													trim((string) ($assignableUser->last_name ?? '')),
												]);
												$assignableName = trim(implode(' ', $nameParts));
											}
											if ($assignableName === '') {
												$assignableName = 'Personnel ' . \Illuminate\Support\Str::limit((string) $assignableUser->id, 8, '');
											}
										@endphp
										<option value="{{ (string) $assignableUser->id }}">{{ $assignableName }}</option>
									@endforeach
								</select>
								<small id="assign-user-empty-state" class="text-danger d-none mt-1">No active users available for assignment.</small>
								<small id="assign-user-debug-state" class="text-muted d-none mt-1"></small>
							</div>
							<div class="form-group">
								<label class="control-label">Comment</label>
								<textarea name="comment" class="form-control form-control-sm" rows="3" placeholder="Optional note for this assignment"></textarea>
							</div>
							<div class="form-group mb-0">
								<label class="control-label">Selected Batches</label>
								<div id="assign-user-selected-batches" class="small text-muted">No batch selected.</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
							<button type="submit" class="btn btn-primary btn-sm">Assign</button>
						</div>
					</form>
				</div>
			</div>
			@endif
		@endif
		@if($status == "Samples Request Review" || $status == "Samples Reception" || $status === 'Samples Receiving' || (in_array($status, ['Samples En-Route'], true) && $workflowSubTab === 'requests'))
			<div id="portal-request-reject-form-modal" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<!-- Modal content-->
					<form class="modal-content" method="POST" action="{{ route('return_batch_reception') }}"
						enctype="multipart/form-data">
						@csrf

						<div class="modal-header">
							<h4 class="modal-title"><i class="mdi mdi-close-circle-outline"></i> Sample Rejection Form (QARM/F/01)</h4>
						</div>
						<div class="modal-body">
							<input type="hidden" name="status" value="Samples Request Review" />
							<div class="alert alert-callout alert-danger">
								<i class="fas fa-info-circle"></i> Fill this rejection form before confirming request rejection.
							</div>
							<div class="row">
								<div class="col-md-6 form-group">
									<label class="control-label">Sample ID</label>
									<input type="text" class="form-control" name="sample_rejection[sample_id]" placeholder="Sample ID">
								</div>
								<div class="col-md-6 form-group">
									<label class="control-label">Name client</label>
									<input type="text" class="form-control" name="sample_rejection[name_of_client]" placeholder="Client name">
								</div>
								<div class="col-md-6 form-group">
									<label class="control-label">Date sample(s) received/collected</label>
									<input type="date" class="form-control" name="sample_rejection[date_sample_received]">
								</div>
								<div class="col-md-6 form-group">
									<label class="control-label">Date of sample(s)</label>
									<input type="date" class="form-control" name="sample_rejection[date_of_sample_collection]">
								</div>
								<div class="col-md-6 form-group">
									<label class="control-label">Number of samples received</label>
									<input type="number" min="0" class="form-control" name="sample_rejection[number_of_samples_received]" placeholder="0">
								</div>
							</div>
							<div class="form-group mb-1">
								<label class="control-label">Reasons</label>
								<div class="border rounded p-2" style="max-height: 190px; overflow-y: auto;">
									<label class="d-block mb-1"><input type="checkbox" name="sample_rejection[reasons][]" value="Sample was collected in improper container"> Sample was collected in improper container</label>
									<label class="d-block mb-1"><input type="checkbox" name="sample_rejection[reasons][]" value="Sample not properly sealed was leaking"> Sample not properly sealed was leaking</label>
									<label class="d-block mb-1"><input type="checkbox" name="sample_rejection[reasons][]" value="Sample was stored in appropriate storage conditions"> Sample was stored in appropriate storage conditions</label>
									<label class="d-block mb-1"><input type="checkbox" name="sample_rejection[reasons][]" value="Sample was inappropriate treated after sampling prior analysis"> Sample was inappropriate treated after sampling prior analysis</label>
									<label class="d-block mb-1"><input type="checkbox" name="sample_rejection[reasons][]" value="Sample was improperly labeled and date of collection was not clear"> Sample was improperly labeled and date of collection was not clear</label>
									<label class="d-block mb-1"><input type="checkbox" name="sample_rejection[reasons][]" value="Sample material was inappropriate for the test(s) requested"> Sample material was inappropriate for the test(s) requested</label>
									<label class="d-block mb-1"><input type="checkbox" name="sample_rejection[reasons][]" value="Sample volume /weight was inappropriate for the test(s) requested"> Sample volume /weight was inappropriate for the test(s) requested</label>
									<label class="d-block mb-1"><input type="checkbox" name="sample_rejection[reasons][]" value="Sample was not accompanied by a request form/sample could not be related to a request form"> Sample was not accompanied by a request form/sample could not be related to a request form</label>
									<label class="d-block mb-1"><input type="checkbox" name="sample_rejection[reasons][]" value="Sample name/date of collection on request form did not match the same details on the sample label"> Sample name/date of collection on request form did not match the same details on the sample label</label>
									<label class="d-block mb-0"><input type="checkbox" name="sample_rejection[reasons][]" value="Other"> Other</label>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label">Explanation</label>
								<textarea class="form-control" name="sample_rejection[explanation]" rows="2" placeholder="Reason details..."></textarea>
							</div>
							<div class="row">
								<div class="col-md-4 form-group">
									<label class="control-label">Laboratory Staff</label>
									<input type="text" class="form-control" name="sample_rejection[laboratory_staff]" value="{{ auth()->user()->name ?? '' }}">
								</div>
								<div class="col-md-4 form-group">
									<label class="control-label">Signature Name</label>
									<input type="text" class="form-control" name="sample_rejection[signature_name]" placeholder="Signature">
								</div>
								<div class="col-md-4 form-group">
									<label class="control-label">Date</label>
									<input type="date" class="form-control" name="sample_rejection[date]" value="{{ now()->format('Y-m-d') }}">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label">Comments</label>
								<textarea class="form-control" name="comment" placeholder="Comments..."></textarea>
							</div>
							<div class="form-group mb-1">
								<label class="control-label">Selected Requests</label>
								<div class="selected-submissions-request text-muted small"></div>
							</div>
							<div class="form-group">
								<label class="control-label">Selected Batches</label>
								<div class="selected-batches-request-approve"></div>
							</div>
							<div class="form-check">
								<input class="form-check-input" type="checkbox" class="form-control" name="notification" />
								<label class="form-check-label">
									Send Email Notification
								</label>
							</div>
							<br>
							<div class="form-check">
								<input class="form-check-input" type="checkbox" class="form-control" name="send_message" />
								<label class="form-check-label">
									Send SMS
								</label>
							</div>
						</div>
						<div class="modal-footer">
							<button type="submit" class="btn btn-danger btn-sm"><i class="mdi mdi-thumb-up"></i> Submit & Reject</button>
							<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
						</div>
					</form>
				</div>
			</div>
			@if($status == "Samples Request Review")
			<div id="dispatch-to-labs-modal-review" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<!-- Modal content-->
					<form class="modal-content" method="POST" action="{{ route('change-batch-workflow') }}"
						enctype="multipart/form-data">
						@csrf

						<div class="modal-header">
							<h4 class="modal-title"><i class="mdi mdi-clipboard-arrow-right"></i> Approve Request</h4>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body p-0 bg-light">
							<input type="hidden" name="status" value="Samples In Lab" />
							<input type="hidden" name="tracking_stage" value="20008" />
							<input type="hidden" name="customer_id" value=0>

							<div class="px-3 pt-3 bg-white border-bottom">
								<strong class="text-dark"><i class="mdi mdi-file-document-edit-outline mr-1"></i> Laboratory acceptance (Laboratory Analysis Acceptance Form &amp; GCLA 01)</strong>
							</div>

							<div class="px-4 py-3">
								<div id="review-lab-acceptance">
									<div class="workflow-board-panel mb-0 border-0 shadow-sm">
										<div class="workflow-board-panel-header d-flex align-items-center justify-content-between flex-wrap" style="gap: 8px;">
											<h5><i class="mdi mdi-file-document-edit-outline"></i> Laboratory Analysis Acceptance Form</h5>
										</div>
										<div class="workflow-board-panel-body">
											<div class="d-flex flex-wrap mb-3" style="gap: 6px;">
												<button type="button" class="btn btn-sm btn-primary lab-acc-part-btn" data-part="1">Part A</button>
												<button type="button" class="btn btn-sm btn-outline-primary lab-acc-part-btn" data-part="2">Part B</button>
												<button type="button" class="btn btn-sm btn-outline-primary lab-acc-part-btn" data-part="3">Part C</button>
												<button type="button" class="btn btn-sm btn-outline-primary lab-acc-part-btn" data-part="4">Part D</button>
												<button type="button" class="btn btn-sm btn-outline-primary lab-acc-part-btn" data-part="5">Part E</button>
											</div>

											<!-- Part A -->
											<div class="card border-0 lab-acc-part" id="lab-acc-part-1" style="background: #f8fafc;">
												<div class="card-body">
													<h6 class="mb-3">Part A: Sample Details</h6>
													<div class="row">
														<div class="col-md-6 form-group">
															<label class="control-label">Date</label>
															<input type="date" class="form-control" name="lab_acceptance[date]" value="{{ now()->format('Y-m-d') }}">
														</div>
														<div class="col-md-6 form-group">
															<label class="control-label">Lab No.</label>
															<input type="text" class="form-control" name="lab_acceptance[lab_no]" placeholder="Auto-generated on approval" readonly>
														</div>
														<div class="col-md-6 form-group">
															<label class="control-label">Customer Name</label>
															<input type="text" class="form-control" name="lab_acceptance[customer_name]" placeholder="Customer">
														</div>
														<div class="col-md-6 form-group">
															<label class="control-label">Address</label>
															<input type="text" class="form-control" name="lab_acceptance[address]" placeholder="Address">
														</div>
														<div class="col-md-4 form-group">
															<label class="control-label">Email</label>
															<input type="email" class="form-control" name="lab_acceptance[email]" placeholder="Email">
														</div>
														<div class="col-md-4 form-group">
															<label class="control-label">Tel</label>
															<input type="text" class="form-control" name="lab_acceptance[tel]" placeholder="Telephone">
														</div>
														<div class="col-md-4 form-group">
															<label class="control-label">Mode of Work</label>
															<select class="form-control" name="lab_acceptance[mode_of_work]">
																<option value="Normal">Normal</option>
																<option value="Express">Express</option>
															</select>
														</div>
														<div class="col-md-4 form-group">
															<label class="control-label">Number of Samples</label>
															<input type="number" min="0" class="form-control" name="lab_acceptance[number_of_samples]" placeholder="0">
														</div>
														<div class="col-md-4 form-group">
															<label class="control-label">Type of Sample</label>
															<input type="text" class="form-control" name="lab_acceptance[type_of_sample]" placeholder="Type">
														</div>
														<div class="col-md-4 form-group">
															<label class="control-label">Date of Sampling</label>
															<input type="date" class="form-control" name="lab_acceptance[date_of_sampling]">
														</div>
														<div class="col-md-12 form-group">
															<label class="control-label fw-bold">Parameters & Pricing</label>
															<div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
																<table class="table table-sm table-bordered mb-0" id="lab_acceptance_parameters_table">
																	<thead class="bg-light">
																		<tr>
																			<th style="width: 50%;">Parameter Name</th>
																			<th style="width: 30%; text-align: right;">Amount (USD)</th>
																			<th style="width: 20%; text-align: center;">Action</th>
																		</tr>
																	</thead>
																	<tbody id="lab_acceptance_parameters_tbody">
																	</tbody>
																	<tfoot class="bg-light fw-bold">
																		<tr>
																			<td style="text-align: right;"><strong>TOTAL</strong></td>
																			<td style="text-align: right;"><span id="lab_acceptance_total_amount">0.00</span></td>
																			<td></td>
																		</tr>
																	</tfoot>
																</table>
															</div>
															<input type="hidden" name="lab_acceptance[amount_usd]" id="lab_acceptance_amount_usd_hidden" value="0">
															<input type="hidden" name="lab_acceptance[parameters_json]" id="lab_acceptance_parameters_json" value="[]">
															<small class="text-muted d-block mt-2">Click the remove button (×) next to any parameter to exclude it from the analysis. The total will update automatically.</small>
														</div>
														<div class="col-md-12 form-group">
															<label class="control-label">Any deviation from specified conditions?</label>
															<div class="d-flex" style="gap: 18px;">
																<label class="mb-0"><input type="radio" name="lab_acceptance[deviation_answer]" value="Yes"> Yes</label>
																<label class="mb-0"><input type="radio" name="lab_acceptance[deviation_answer]" value="No" checked> No</label>
															</div>
														</div>
													</div>
												</div>
											</div>

											<!-- Part B -->
											<div class="card border-0 lab-acc-part d-none" id="lab-acc-part-2" style="background: #f8fafc;">
												<div class="card-body">
													<h6 class="mb-3">Part B: Customer Certified</h6>
													<div class="row">
														<div class="col-md-6 form-group">
															<label class="control-label">Customer Name</label>
															<input type="text" class="form-control" name="lab_acceptance[customer_name_certified]" placeholder="Name">
														</div>
														<div class="col-md-6 form-group">
															<label class="control-label">Date</label>
															<input type="date" class="form-control" name="lab_acceptance[customer_date]" value="{{ now()->format('Y-m-d') }}">
														</div>
													</div>
													<label class="form-label d-block">Customer Signature</label>
													<div class="bg-white border rounded p-2" style="max-width: 560px;">
														<canvas id="review-customer-signature-canvas" style="width: 100%; height: 160px; border: 1px dashed #cbd5e1;"></canvas>
														<div class="d-flex mt-2" style="gap: 8px;">
															<button type="button" class="btn btn-sm btn-outline-secondary" id="review-customer-sign-clear">Clear</button>
														</div>
													</div>
													<input type="hidden" id="review-customer-signature-input" name="lab_acceptance[customer_signature]">
												</div>
											</div>

											<!-- Part C -->
											<div class="card border-0 lab-acc-part d-none" id="lab-acc-part-3" style="background: #f8fafc;">
												<div class="card-body">
													<h6 class="mb-3">Part C: Conformity Assessment</h6>
													<label class="form-label d-block">Customer requests a statement of conformity to specification/standard</label>
													<div class="d-flex" style="gap: 18px;">
														<label class="mb-0"><input type="radio" name="lab_acceptance[conformity_request]" value="requested"> Requested</label>
														<label class="mb-0"><input type="radio" name="lab_acceptance[conformity_request]" value="not_requested" checked> Not requested</label>
													</div>
												</div>
											</div>

											<!-- Part D -->
											<div class="card border-0 lab-acc-part d-none" id="lab-acc-part-4" style="background: #f8fafc;">
												<div class="card-body">
													<h6 class="mb-3">Part D: Laboratory Manager</h6>
													<label class="form-label d-block">I certify that the laboratory has/has not capability and resources to meet customer requirements</label>
													<div class="d-flex mb-3" style="gap: 18px;">
														<label class="mb-0"><input type="radio" name="lab_acceptance[manager_capability]" value="has" checked> Has</label>
														<label class="mb-0"><input type="radio" name="lab_acceptance[manager_capability]" value="has_not"> Has not</label>
													</div>

													<div class="row">
														<div class="col-md-4 form-group">
															<label class="control-label">Laboratory</label>
															<input type="text" class="form-control" name="lab_acceptance[laboratory_name]" value="{{ config('app.name') }}">
														</div>
														<div class="col-md-4 form-group">
															<label class="control-label">Laboratory Manager Name</label>
															<input type="text" class="form-control" name="lab_acceptance[laboratory_manager_name]" value="{{ auth()->user()->name ?? '' }}">
														</div>
														<div class="col-md-4 form-group">
															<label class="control-label">Manager Date</label>
															<input type="date" class="form-control" name="lab_acceptance[manager_date]" value="{{ now()->format('Y-m-d') }}">
														</div>
													</div>

													<label class="form-label d-block">Manager Signature</label>
													<div class="bg-white border rounded p-2" style="max-width: 560px;">
														<canvas id="review-manager-signature-canvas" style="width: 100%; height: 160px; border: 1px dashed #cbd5e1;"></canvas>
														<div class="d-flex mt-2" style="gap: 8px;">
															<button type="button" class="btn btn-sm btn-outline-secondary" id="review-manager-sign-clear">Clear</button>
														</div>
													</div>
													<input type="hidden" id="review-manager-signature-input" name="lab_acceptance[manager_signature]">
												</div>
											</div>
											<!-- Part E: Sample Receipt Notification -->
											<div class="lab-acc-part d-none" id="lab-acc-part-5">
												@include('livewire.partials.receipt-notification-wire-fields', [
													'mode' => 'static',
													'canvasPrefix' => 'review-receipt',
													'partLabel' => 'Part E: Sample Receipt Notification (GCLA 01)',
													'showSubmitterSection' => false,
													'showSubmitterSigningNotice' => true,
												])
											</div>
										</div>
									</div>
								</div>
							</div>
								<div class="card border-0 bg-white">
									<div class="card-body">
										<h6 class="mb-3">Other Settings & Confirmations</h6>
										<div class="form-group">
											<div class="custom-control custom-checkbox">
												<input type="checkbox" class="custom-control-input" id="reviewIsPriority" name="is_priority" value="High">
												<label class="custom-control-label" for="reviewIsPriority">Is High Priority</label>
											</div>
										</div>
										<div class="form-group">
											<div class="custom-control custom-checkbox">
												<input type="checkbox" class="custom-control-input" id="reviewSendEmail" name="notification">
												<label class="custom-control-label" for="reviewSendEmail">Send Email Notification</label>
											</div>
										</div>
										<div class="form-group">
											<div class="custom-control custom-checkbox">
												<input type="checkbox" class="custom-control-input" id="reviewSendSMS" name="send_message">
												<label class="custom-control-label" for="reviewSendSMS">Send SMS</label>
											</div>
										</div>
										<div class="form-group mt-3">
											<label class="control-label fw-bold">Batches</label>
											<div class="selected-batches-review"></div>
										</div>
										<div class="form-group mb-0">
											<label class="control-label fw-bold">Selected Forms</label>
											<div class="selected-submissions-request text-muted small"></div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="modal-footer bg-light">
							<button type="submit" class="btn btn-info btn-sm"><i class="mdi mdi-thumb-up"></i> Approve Request</button>
							<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
						</div>
					</form>
				</div>
			</div>
			
			<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
			<script>
				document.addEventListener('DOMContentLoaded', function () {
					// Parts navigation
					const partBtns = document.querySelectorAll('.lab-acc-part-btn');
					const parts = document.querySelectorAll('.lab-acc-part');
					
					partBtns.forEach(btn => {
						btn.addEventListener('click', function() {
							const targetPart = this.getAttribute('data-part');

							// Update buttons
							partBtns.forEach(b => {
								b.classList.remove('btn-primary');
								b.classList.add('btn-outline-primary');
							});
							this.classList.remove('btn-outline-primary');
							this.classList.add('btn-primary');

							// Update parts
							parts.forEach(p => p.classList.add('d-none'));
							document.getElementById('lab-acc-part-' + targetPart).classList.remove('d-none');

							if (targetPart === '5' && !reviewReceiptPadsReady) {
								const r = setupPad('review-receipt-receiver-canvas', 'review-receipt-receiver-input', 'review-receipt-receiver-clear');
								if (r) {
									pads.push(r);
								}
								reviewReceiptPadsReady = true;
							}

							window.dispatchEvent(new Event('resize'));
						});
					});

					// Signature Pads
					function setupPad(canvasId, inputId, clearBtnId) {
						const canvas = document.getElementById(canvasId);
						const input = document.getElementById(inputId);
						const clearBtn = document.getElementById(clearBtnId);
						if (!canvas || !input || !window.SignaturePad) return null;

						const ratio = Math.max(window.devicePixelRatio || 1, 1);
						const rect = canvas.getBoundingClientRect();
						const width = rect.width > 10 ? rect.width : 520;
						canvas.width = width * ratio;
						canvas.height = rect.height * ratio;
						canvas.getContext('2d').scale(ratio, ratio);

						const pad = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)' });

						pad.addEventListener('endStroke', function () {
							input.value = pad.isEmpty() ? '' : pad.toDataURL('image/png');
						});

						if (clearBtn) {
							clearBtn.addEventListener('click', function () {
								pad.clear();
								input.value = '';
							});
						}

						return pad;
					}

					let padsInitialized = false;
					let pads = [];
					let reviewReceiptPadsReady = false;

					$('#dispatch-to-labs-modal-review').on('shown.bs.modal', function () {
						if (!padsInitialized) {
							pads.push(setupPad('review-customer-signature-canvas', 'review-customer-signature-input', 'review-customer-sign-clear'));
							pads.push(setupPad('review-manager-signature-canvas', 'review-manager-signature-input', 'review-manager-sign-clear'));
							padsInitialized = true;
							reviewReceiptPadsReady = false;
						} else {
							window.dispatchEvent(new Event('resize'));
						}
					});

					$('#dispatch-to-labs-modal-review').on('hidden.bs.modal', function () {
						padsInitialized = false;
						reviewReceiptPadsReady = false;
						pads = [];
					});
				});
			</script>
			@endif
		@endif
		@if($status == 'Sample Approval')
			<div class="modal fade" id="move-batch-complete" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<form action="{{route('mark-finished')}}" method="post">
							@csrf
							<div class="modal-body">
								<div class="alert alert-primary p-2 d-flex">
									<i class="mdi mdi-alert-decagram-outline" style="font-size:25px"></i>
									<span class="p-2">Confirm you want to move the follwing batche(s) to Finished Sample(s). </span>
								</div>
								<div class="form-group">
									<label class="control-label">Batches</label>
									<div class="selected-batches-review"></div>
								</div>

							</div>
							<div class="modal-footer">
								<button type="submit" class="btn btn-sm btn-outline-primary"><i
										class="mdi mdi-thumb-up-outline"></i> Yes, Move</button>
								<span class="btn btn-sm btn-default" data-dismiss="modal">Cancel</span>
							</div>
						</form>
					</div>
				</div>
			</div>
		@endif
		@if($status == 'Finished Sample')
			<div class="modal fade" id="move-sample-approval" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<form action="{{route('return-finished')}}" method="post">
							@csrf
							<div class="modal-body">
								<div class="alert alert-primary p-2 d-flex">
									<i class="mdi mdi-alert-decagram-outline" style="font-size:25px"></i>
									<span class="p-2">Confirm you want to move the follwing batche(s) to Sample(s) Approval Section.
									</span>
								</div>
								<div class="form-group">
									<label class="control-label">Batches</label>
									<div class="selected-batches-review"></div>
								</div>
							</div>
							<div class="modal-footer">
								<button type="submit" class="btn btn-sm btn-outline-primary"><i
										class="mdi mdi-thumb-up-outline"></i> Yes, Move</button>
								<span class="btn btn-sm btn-default" data-dismiss="modal">Cancel</span>
							</div>
						</form>
					</div>
				</div>
			</div>
		@endif

		<style>
			/* Hide loading overlay initially */
			.table-filter-loading {
				display: none !important;
			}
			
			/* Show when Livewire adds wire:loading attribute */
			.table-filter-loading[wire\:loading] {
				display: flex !important;
			}
			
			/* Prevent text wrapping in table cells and increase table width */
			.table-responsive {
				overflow-x: auto;
				-webkit-overflow-scrolling: touch;
			}
			
			.table-responsive table {
				min-width: 100%;
				width: max-content;
				table-layout: auto;
			}
			
			.table-responsive table td,
			.table-responsive table th {
				white-space: nowrap !important;
				padding: 8px 12px;
				min-width: fit-content;
			}
			
			/* Override any max-width constraints on cells */
			.table-responsive table td[style*="max-width"] {
				max-width: none !important;
				word-wrap: normal !important;
				white-space: nowrap !important;
			}
			
			.form-part-toggler {
				margin: 0px 0px 5px 0px !important;
				padding: 6px 6px 6px 6px;
				border-bottom: 1px solid rgba(0, 0, 0, 0.09);
				cursor: pointer;
			}
		
			.form-part-toggler:hover {
				background-color: rgba(0, 0, 0, 0.08);
			}
		
			#sample-detail-rows .form-group {
				display: none;
			}
		
			#sample-detail-rows tr.selected-row {
				background-color: rgb(253, 220, 220);
			}
		
			#sample-detail-rows .text {
				display: unset;
			}
		
			#sample-detail-rows tr.editable .form-group {
				display: unset;
			}
		
			#sample-detail-rows tr.editable .text {
				display: none;
			}
		
			#sample-detail-rows tr {
				cursor: pointer;
			}
		
			.hidden {
				display: none;
			}
		
			.overdue-bg-color {
				background-color: rgba(240, 185, 83, 0.972) !important;
			}
		
			.upfront-bg-color {
				background-color: skyblue !important;
			}
		
			.ammend-bg-color {
				background-color: #fef764 !important;
			}
		
			.btn-white {
				background-color: white !important;
			}
		
		.badge-active {
			background-color: white !important;
			color: black;
		}
	
		/* Tag-based Dropdown Styling (sample workflow theme) */
		.workflow-board-page .tag-select-container {
			position: relative;
			cursor: text;
		}
	
		.workflow-board-page .tag-select-input {
			display: flex;
			flex-wrap: wrap;
			align-items: center;
			gap: 6px;
			min-height: 42px;
			padding: 6px 12px;
			background: #fff;
			border: 1px solid #e9ecef;
			border-radius: 6px;
			transition: border-color 0.2s ease, box-shadow 0.2s ease;
		}
	
		.workflow-board-page .tag-select-input:hover {
			border-color: var(--color-primary-border-soft);
		}
	
		.workflow-board-page .tag-select-input:focus-within {
			border-color: var(--color-primary);
			box-shadow: 0 0 0 0.2rem var(--color-primary-highlight);
			outline: none;
		}
	
		.workflow-board-page .tag-badge {
			display: inline-flex;
			align-items: center;
			gap: 4px;
			padding: 4px 10px;
			background-color: var(--color-primary);
			color: white;
			border-radius: 16px;
			font-size: 0.875rem;
			font-weight: 500;
			white-space: nowrap;
		}
	
		.workflow-board-page .tag-badge i {
			cursor: pointer;
			font-size: 1rem;
			opacity: 0.8;
			transition: opacity 0.2s;
		}
	
		.workflow-board-page .tag-badge i:hover {
			opacity: 1;
		}
	
		.workflow-board-page .tag-input {
			flex: 1;
			min-width: 120px;
			border: none;
			outline: none;
			padding: 4px;
			font-size: 0.9rem;
		}
	
		.workflow-board-page .tag-dropdown {
			position: absolute;
			top: 100%;
			left: 0;
			right: 0;
			background: white;
			border: 1px solid var(--color-primary-border-soft);
			border-top: none;
			border-radius: 0 0 6px 6px;
			max-height: 250px;
			overflow-y: auto;
			z-index: 1050;
			box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
			margin-top: -1px;
		}
	
		.workflow-board-page .tag-dropdown-item {
			padding: 10px 16px;
			cursor: pointer;
			transition: background-color 0.2s;
			border-bottom: 1px solid #f1f5f9;
		}
	
		.workflow-board-page .tag-dropdown-item:hover {
			background-color: #f8fafc;
		}
	
		.workflow-board-page .tag-dropdown-item:last-child {
			border-bottom: none;
		}
	
		.workflow-board-page .tag-dropdown-item.text-muted {
			cursor: default;
		}
	</style>
		
		
		<script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>
		<script type="text/javascript">
			console.log('here');
			var selectedSampleIDs = [];
			var selectedBatchesIDs = [];
			var selectedSubmissionIDs = [];
			var selectedFormInstanceIDs = [];
			var sampleAnalysisByType = [];
			var sampleCondtions = [];
			var defaultClass = '';
			var notPaid = [];
			var invoiceItemCounter = 0;
			var lastParametersRequestKey = null;
			var lastPreviewRequestKey = null;
		
			$('[data-target="#awaiting-approval-modal"]').hide();
		
			var checkClientValidity = (data, callback) => {
				$.ajax({
					url: `/validate/client-batches`,
					data: data,
					type: 'POST',
					success: (res) => {
						callback(res);
					},
					error: (res) => {
						callback({ 'error': 'Error validating client' });
					}
		
				})
			}
			var sendScheduleAnaltysis = (data, callback) => {
				$.ajax({
					url: `/ajax/send-schedule`,
					data: data,
					type: 'POST',
					success: (res) => {
						callback(res);
					},
					error: (res) => {
						callback({ 'error': 'Error sending schedule of analysis' })
					}
				})
			}
			var generateSalesorder = (data, callback) => {
				// $.ajaxSetup({
				// 	headers: {
				// 		'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
				// 	}
				// });
				$.ajax({
					url: `/generate/batch-invoice/ajax`,
					type: 'POST',
					data: data,
					success: (data) => {
						callback(data);
					},
					error: (data) => {
						callback({'error':'Error creating the draft invoice'});
					}
				})
			}
			var moveToLab = (data,callback)=>{
				$.ajax({
					url:`/send/sales/order-ajax`,
					data:data,
					type:'POST',
					success:(data)=>callback(data),
					error: (data)=>callback({'error':'Error sending batches to lab'}),
				})
			}
		
			$('#send-to-lab-form').on('submit', (e) => {
				e.preventDefault();
				$('#send-to-lab-form').find('.submit-btn').addClass('hidden');
				$('#send-to-lab-form').find('.dismiss-btn').addClass('hidden');
				$('#send-to-lab-form').find('.loading-area').removeClass('hidden')
				$('#send-to-lab-form').find('.client-validation i').addClass('mdi-spin');
				let formData = $('#send-to-lab-form').serializeArray(); 
				var responseChecker = (response,currentClass,nextClass)=>{
					if(response['error']){
						$('#send-to-lab-form').find('.error-area-body').empty();
						$('#send-to-lab-form').find('.error-area-body').append(response['error']);
						$('#send-to-lab-form').find('.error-area-header').removeClass('hidden');
						$('#send-to-lab-form').find('.dismiss-btn').removeClass('hidden');
						$('#send-to-lab-form').find(`${currentClass} i`).removeClass('mdi-spin');
						$('#send-to-lab-form').find(`${currentClass} i`).removeClass('mdi-minus');
						$('#send-to-lab-form').find(`${currentClass} i`).addClass('mdi-close-circle text-danger');
						$('#send-to-lab-form').find('.loading-area').addClass('hidden');
						return false;
					}else{
						$('#send-to-lab-form').find(`${currentClass} i`).removeClass('mdi-spin');
						$('#send-to-lab-form').find(`${currentClass} i`).removeClass('mdi-minus');
						$('#send-to-lab-form').find(`${currentClass} i`).addClass('mdi-check-decagram text-success');
						if(nextClass == '.success-text'){
							$('#send-to-lab-form').find(`${nextClass}`).removeClass('hidden');
							$('#send-to-lab-form').find('.success-loader').removeClass('hidden');
							$('#send-to-lab-form').find('.loading-area').addClass('hidden');
						}else{
							$('#send-to-lab-form').find(`${nextClass} i`).addClass('mdi-spin');
						}
						return true;
					}
				}
				checkClientValidity(formData,(data)=>{
					var resValid =responseChecker(data,'.client-validation','.send-schedule');
					if(resValid){
						sendScheduleAnaltysis(formData,(res)=>{
							var scheduleRes =responseChecker(res,'.send-schedule','.create-order');
							if(scheduleRes){
								console.log('here');
								generateSalesorder(formData,(response)=>{
									var generateRes = responseChecker(response,'.create-order','.sending-order');
									if(generateRes){
										$('#send-to-lab-form').find('.loading-area').addClass('hidden')
										var invoicePreview = getInvoiceBody(response['customer'], response['invoice'], response['details']);
										$('#send-to-lab-form').find('.invoice-part').append(invoicePreview)
									}
								})
							}
						})
					}
				});
				$('#send-to-lab-form').on('allProccessesDone',(e)=>{
					moveToLab(formData,(data)=>{
						var tolabRes =responseChecker(data,'.send-lab','.success-text');
						if(tolabRes){
							$('#send-to-lab-form').find('.dismiss-btn').removeClass('hidden');
						}
					})
				})
				
			});
		
		
			var getTatApprovalCounter = () => {
				var status = $('[data-target="#awaiting-approval-modal"]').data('status');
				$.ajax({
					url: `/get/Tat/Batch/ApprovalCounter/Ajax/${status}`,
					type: 'GET',
					success: (data) => {
						if (data['approval_count'] > 0) {
							$('#approval-counter').empty();
							$('#approval-counter').append(data['approval_count']);
							$('[data-target="#awaiting-approval-modal"]').show();
						}
					}
				})
			};
			getTatApprovalCounter();
		
			var deleteSalesOrder = (invoice_id, callback) => {
				$.ajax({
					url: `/delete/sales-order/${invoice_id}`,
					type: 'GET',
					success: (data) => {
						callback(data);
					},
					error: (err) => {
						console.log(err);
					}
				})
			}
			var getUpdateFields = (invoice_id) => {
				var invoice_details = [];
				$('#send-to-lab-form').find('tbody tr.carry_data').each(function () {
					var mode = $(this).data('mode');
					var detail = {
						invoice_detail_id: mode == 'new' ? 0 : $(this).find('[name="invoice_detail_id[]"]').val(),
						unit_price: $(this).find('.invoice_price').val(),
						quantity: $(this).find('.invoice_quantity').val(),
						discount: $(this).find('.invoice_discount').val(),
						discount_type: $(this).find('.discount_type').val(),
						final_price: $(this).find('.invoice_final_price').val(),
						title: $(this).find('.invoice_title').val(),
						item_id: $(this).find('.item_id').val(),
						invoice_id: invoice_id
		
					}
					invoice_details.push(detail);
				});
				console.log('Invoice details');
				console.log(invoice_details)
				return invoice_details;
			}
			var updateInvoiceAjax = (invoice_details, callback) => {
				$.ajaxSetup({
					headers: {
						'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
					}
				});
				$.ajax({
					url: '/update-invoice',
					type: 'POST',
					data: { details: invoice_details },
					success: (data) => {
						callback(data);
					},
					error: (data) => {
						callback(data);
					}
				})
			}
			var sendSalesOrder = (invoice_id, callback) => {
		
				$.ajax({
					url: `/send/Draft-Invoice/${invoice_id}`,
					type: 'GET',
					success: (data) => {
						console.log('here2')
						callback(data);
					},
					error: (data) => {
						console.log('here3')
						console.log(data);
					}
				})
			}
			var getAwaitingTr = (data) => {
				var body = $(`
				<tr>
					<td><a href="/sample-workflow/batch/${data.id}/details/0/0/${data.status}">${data.batch_code}</a></td>
					<td>${data.batch_approver}</td>
				</tr>
				`).clone();
				return body;
			}
			var getBatchesAwaitingApproval = (status, callback) => {
				$.ajax({
					url: `/awaiting/Approval/Samples/${status}`,
					type: 'GET',
					success: (data) => {
						callback(data);
					},
					error: (data) => {
						console.log(data);
					}
				})
			}
			$('#awaiting-approval-modal').on('show.bs.modal', (e) => {
				var status = $(e.relatedTarget).data('status');
				$('#awaiting-approval-modal').find('tbody').empty();
				getBatchesAwaitingApproval(status, (data) => {
					$.each(data, (i, obj) => {
						var tr = getAwaitingTr(obj);
						$('#awaiting-approval-modal').find('tbody').append(tr);
		
					})
				})
			})
			var generateInvoiceBody = () => {
				var body = $(`
				<div class="before-save">
					<input type="hidden" name="status" value="Samples Request Review" />
					<div class="form-group">
						<div class="alert alert-callout alert-primary d-flex">
							<i class="fas fa-info-circle" style="font-size:25px"></i>
							<span class="pl-2">
								By approving this you will generate a Draft Invoice with the following Batches</b>?
							</span>
						</div>
					</div>
				</div>
				<div class="after-save hidden">
					<center class="loader">
						<img src="/images/load.gif" height="250px" width="auto" alt="">
					</center>
					<div class="a-detail">
						<p><i class="mdi mdi-minus saving-invoice"></i> Saving draft invoice details.</p>
						<p><i class="mdi mdi-minus send-sales"></i> Sending draft invoice details to Zoho.</p> <br>
					</div>
				</div>
				
				<div class="alert alert-danger d-flex error-area hidden">
					<i class="mdi mdi-alert-decagram-outline" style="font-size:25px"></i>
					<span class="p-2 error-body"></span>
				</div>
				`).clone();
				return body;
			}
			$('#dispatch-to-labs-modal-approve').on('show.bs.modal', (e) => {
				invoiceItemCounter = 0;
				$('#generate-invoice-form').find('.submit-btn').removeClass('hidden');
				var body = generateInvoiceBody();
		
				$('#dispatch-to-labs-modal-approve').find('.to-be-updated').empty();
				$('#dispatch-to-labs-modal-approve').find('.to-be-updated').append(body);
			});
			var getInvoiceBody = (customer, invoice, details) => {
				var body = $(`
				<div class="invoice_body bordered p-2" style="box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px;">
					<h4 class="text-center bg-light p-2">
						<b>Draft Invoice ${invoice.invoice_number} Preview</b>
					</h4>
					<div class="header mt-5">
						<b>CUSTOMER : </b> ${customer.name}
						<span class="btn btn-sm btn-outline-primary add-item-initiator float-right"><i class="mdi mdi-plus"></i> Add Item</span>
					</div>
					<div class="table-responsive mt-4">
						<table class="table table-sm table-bordered">
							<thead class="bg-light">
								<th>#</th>
								<th>Item</th>
								<th>Title</th>
								<th>Quantity</th>
								<th>Initial Unit Price</th>
								<th>Discount Type</th>
								<th>Discount</th>
								<th>Final Unit Price</th>
								<th>Total</th>
							</thead>
							<tbody>
								
							</tbody>
						</table>
					</div>
					
					<div class="alert alert-default bg-light p-3 mt-3 text-center">
						<i class="mdi mdi-alert-decagram-outline"></i>
						<span class="ml-2">Confirm you want to create above DRAFT draft invoice to zoho</span>
					</div>
					<div class="row">
						<div class="col-md-6 p-2">
							<span class="btn btn-outline-primary btn-block btn-sm" data-invoice="${invoice.id}" id="send_sales"><i class="mdi mdi-thumb-up-outline"> Yes, Send Draft Invoice</i></span> <br>
						</div>
						<div class="col-md-6 p-2">
							<span class="btn btn-outline-danger btn-block btn-sm" data-invoice="${invoice.id}" id="cancel_sales"><i class="mdi mdi-thumb-down-outline"> Cancel Draft Invoice</i></span>
						</div>
		
						
						
					</div>
				</div>
				`).clone();
				$(body).find('#send_sales').on('click', (e) => {
					var invoice_id = $(body).find('#send_sales').data('invoice');
					$('#send-to-lab-form').find('.loading-area').removeClass('hidden');
					$('#send-to-lab-form').find('.invoice_body').addClass('hidden');
					// $('#send-to-lab-form').find('.send-sales').addClass('mdi-spin');
					var invoice_details = getUpdateFields(invoice_id);
					updateInvoiceAjax(invoice_details, (res1) => {
						sendSalesOrder(invoice_id, (res) => {
							var currentClass = '.sending-order';
							var nextClass = '.send-lab'
							if(res['error']){
								$('#send-to-lab-form').find('.error-area-body').empty();
								$('#send-to-lab-form').find('.error-area-body').append(res['error']);
								$('#send-to-lab-form').find('.error-area-header').removeClass('hidden');
								$('#send-to-lab-form').find('.dismiss-btn').removeClass('hidden');
								$('#send-to-lab-form').find(`${currentClass} i`).removeClass('mdi-spin');
								$('#send-to-lab-form').find(`${currentClass} i`).removeClass('mdi-minus');
								$('#send-to-lab-form').find(`${currentClass} i`).addClass('mdi-close-circle text-danger');
								$('#send-to-lab-form').find('.loading-area').addClass('hidden');
							}else{
								$('#send-to-lab-form').find(`${currentClass} i`).removeClass('mdi-spin');
								$('#send-to-lab-form').find(`${currentClass} i`).removeClass('mdi-minus');
								$('#send-to-lab-form').find(`${currentClass} i`).addClass('mdi-check-decagram text-success');
								$('#send-to-lab-form').find(`${nextClass} i`).addClass('mdi-spin');
								
								$('#send-to-lab-form').trigger('allProccessesDone');
							}
							
						})
					})
				});
		
				$(body).find('#cancel_sales').on('click', (e) => {
					var errorBody = `Deleting created draft invoice in process!`;
					var invoice_id = $(body).find('#send_sales').data('invoice');
					$('#generate-invoice-form').find('.loader').removeClass('hidden');
					$('#generate-invoice-form').find('.invoice_body').addClass('hidden');
					$('#generate-invoice-form').find('.send-sales').addClass('mdi-spin');
					$('#generate-invoice-form').find('.error-body').empty();
					$('#generate-invoice-form').find('.error-body').append(errorBody);
					$('#generate-invoice-form').find('.error-area').removeClass('hidden');
					deleteSalesOrder(invoice_id, (data) => {
						$('#generate-invoice-form').find('.loader').empty();
						$('#generate-invoice-form').find('.a-detail').addClass('hidden');
						var imgElem = $(`<img src="/images/suc.gif" height="250px" width="auto" alt="">`);
						$('#generate-invoice-form').find('.loader').append(imgElem);
						$('#generate-invoice-form').find('.error-area').addClass('hidden');
					})
		
		
				})
				$.each(details, (i, obj) => {
					var tr = `
					<tr class="carry_data" data-mode="existing">
						<input type="hidden" name="invoice_detail_id[]" value="${obj.id}">
						<input type="hidden" name="item_id[${obj.id}]" class="item_id" value="${obj.analysis_type}">
						<td>#</td>
						<td>${obj.zoho_item_name} <br> ${obj.analysis_type_name}</td>
						<td style="width:30%"><textarea name="title[${obj.id}]" data-id="${obj.id}" class="form-control invoice_title">${obj.analysis_type_name} ${obj.samplecodes}</textarea></td>
						<td style="width:5%"><input type="text" name="quantity[${obj.id}]" data-id="${obj.id}" value="${obj.quantity}" class="form-control invoice_quantity"></td>
						<td><input type="text" name="unit_price[${obj.id}]"  data-id="${obj.id}" value="${obj.selling_price}" class="form-control invoice_price"></td>
						<td>
						<select name="discount_type[${obj.id}]" data-id="${obj.id}" id="" class="form-control discount_type">
							<option value="" ${obj.discount_type == "" ? 'selected' : ''}>Select Discount Type</option>
							<option value="percentage" ${obj.discount_type == "percentage" ? 'selected' : ''}>Percentage</option>
							<option value="amount" ${obj.discount_type == "amount" ? 'selected' : ''}>Fixed</option>
						</select>
							
						</td>
						<td><input type="text" data-id="${obj.id}" name="discount[${obj.id}]" value="${obj.discount || 0}" class="form-control invoice_discount"></td>
						<td><input type="text" readonly name="final_unit_price[${obj.id}]" value="${obj.final_unit_price || 0}" class="form-control invoice_final_price"></td>
		
						<td><input type="text" readonly name="total[${obj.id}]" value="${obj.total}" class="form-control invoice_total"></td>
		
					</tr>`;
					$(body).find('tbody').append(tr);
				});
				var addItemBody = (counter) => {
					var tr = $(`
					<tr class="carry_data" data-mode="new">
						<input type="hidden" name="invoice_detail_id[]" value="nw${counter}">
						
						<td><span class="remove-item btn btn-sm btn-default"><i class="mdi mdi-delete-empty"><i/></span></td>
						<td>
						<select name="item_id[nw${counter}]" data-id="nw${counter}" id="" class="form-control item_id">
							<option value="">Select Item</option>
							@foreach ($zoho_items as $z_item)
								<option value="{{$z_item->id}}">{{$z_item->name}}</option>
							@endforeach
						</select>
						</td>
						<td><textarea name="title[nw${counter}]" data-id="nw${counter}" class="form-control invoice_title"></textarea></td>
						<td><input type="text" name="quantity[nw${counter}]" data-id="nw${counter}" value="0" class="form-control invoice_quantity"></td>
						<td><input type="text" name="unit_price[nw${counter}]"  data-id="nw${counter}" value="0" class="form-control invoice_price"></td>
						<td>
						<select name="discount_type[nw${counter}]" data-id="nw${counter}" id="" class="form-control discount_type">
							<option value="">Select Discount Type</option>
							<option value="percentage">Percentage</option>
							<option value="amount">Fixed</option>
						</select>
							
						</td>
						<td><input type="text" name="discount[nw${counter}]" data-id="nw${counter}" value="0" class="form-control invoice_discount"></td>
						<td><input type="text" readonly name="final_unit_price[nw${counter}]" data-id="nw${counter}" value="0" class="form-control invoice_final_price"></td>
		
						<td><input type="text" readonly name="total[nw${counter}]" value="0" class="form-control invoice_total"></td>
					</tr>`).clone();
		
					$(tr).find('.item_id').on('change', function () {
						var value = $(this).val();
						var valueText = $(this).find("option:selected").text();
						$(tr).find('.invoice_title').val(valueText);
					})
		
					return tr;
				}
				$(body).find('.add-item-initiator').on('click', (ev) => {
					invoiceItemCounter += 1;
					var trbody = addItemBody(invoiceItemCounter)
					$(trbody).find('.item_id').select2();
					$(body).find('tbody tr.final-row').before(trbody)
					$(body).find('.invoice_discount').on('change', (e) => {
						var detail_id = $(e.currentTarget).data('id');
						console.log(detail_id);
						changeofinvoicedetails(detail_id);
					});
					$(body).find('.discount_type').on('change', (e) => {
						var detail_id = $(e.currentTarget).data('id');
						changeofinvoicedetails(detail_id);
					});
					$(body).find('.invoice_quantity').on('change', (e) => {
						var detail_id = $(e.currentTarget).data('id');
						changeofinvoicedetails(detail_id);
					});
		
					$(body).find('.invoice_price').on('change', (e) => {
						var detail_id = $(e.currentTarget).data('id');
						changeofinvoicedetails(detail_id);
					});
				});
				$(body).on('click', '.remove-item', function () {
					$(this).closest('tr').remove();
				});
				var getItemData = (item_id, callback) => {
					var invoiceID = $(body).find('#send_sales').data('invoice');
					$.ajax({
						url: `/get-invoice/itemData/${invoiceID}/${item_id}`,
						type: 'GET',
						success: (data) => {
							callback(data);
						},
						error: (err) => {
							console.log();
						}
					});
				}
				$(body).on('change', '.item_id', function () {
					var trElem = $(this).closest('tr');
					var item_id = $(this).val();
					getItemData(item_id, (item) => {
						var price = item.unit_price_rate > 0 ? item.unit_price_rate : item.unit_price;
						$(trElem).find('.invoice_price').val(price);
						$(trElem).find('.invoice_final_price').val(price);
					});
		
				});
				var finaltr = `
				<tr class="bg-light final-row">
					<td colspan="8"><b>TOTAL:</b></td>
					<td class="total_amount" >${invoice.total}</td>
				</tr>`;
		
				var changeofinvoicedetails = (detail_id) => {
					var quantity = $('#send-to-lab-form').find(`[name="quantity[${detail_id}]"]`).val();
					var unit_price = $('#send-to-lab-form').find(`[name="unit_price[${detail_id}]"]`).val();
					var discount_type = $('#send-to-lab-form').find(`[name="discount_type[${detail_id}]"]`).val();
					var discount = $('#send-to-lab-form').find(`[name="discount[${detail_id}]"]`).val();
					var final_unit_price = unit_price;
					if (discount_type != '' && parseInt(discount) > 0) {
						final_unit_price = discount_type == 'percentage' ? (100 - parseInt(discount)) / 100 * unit_price : parseInt(unit_price) - parseInt(discount);
					}
					var total = parseInt(quantity) * parseInt(final_unit_price);
		
					$('#send-to-lab-form').find(`[name="total[${detail_id}]"]`).val(total);
					$('#send-to-lab-form').find(`[name="final_unit_price[${detail_id}]"]`).val(final_unit_price);
		
					var current_total = 0;
					$('#send-to-lab-form').find('.invoice_total').each(function () {
						current_total += parseInt($(this).val());
					});
					$('#send-to-lab-form').find('.total_amount').empty();
					$('#send-to-lab-form').find('.total_amount').append(current_total);
				}
		
				$(body).find('.invoice_quantity').on('change', (e) => {
					var detail_id = $(e.currentTarget).data('id');
					changeofinvoicedetails(detail_id);
				});
		
				$(body).find('.invoice_price').on('change', (e) => {
					var detail_id = $(e.currentTarget).data('id');
					changeofinvoicedetails(detail_id);
				});
				$(body).find('.invoice_discount').on('change', (e) => {
					var detail_id = $(e.currentTarget).data('id');
					console.log(detail_id);
					changeofinvoicedetails(detail_id);
				});
				$(body).find('.discount_type').on('change', (e) => {
					var detail_id = $(e.currentTarget).data('id');
					changeofinvoicedetails(detail_id);
				});
		
		
				$(body).find('tbody').append(finaltr);
		
				return body;
		
			}
			$('#generate-invoice-form').on('submit', function (event) {
				event.preventDefault();
				$('#generate-invoice-form').find('.submit-btn').addClass('hidden');
				$('#generate-invoice-form').find('.before-save').addClass('hidden');
				$('#generate-invoice-form').find('.after-save').removeClass('hidden');
				$('#generate-invoice-form').find('.saving-invoice').addClass('mdi-spin');
		
				var formdata = $(this).serializeArray();
				$.ajaxSetup({
					headers: {
						'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
					}
				});
				console.log('am here ')
				$.ajax({
					url: `/generate/batch-invoice/ajax`,
					type: 'POST',
					data: formdata,
					success: (data) => {
		
						if (data['error']) {
							$('#generate-invoice-form').find('.loader').addClass('hidden');
							$('#generate-invoice-form').find('.saving-invoice').removeClass('mdi-spin');
							$('#generate-invoice-form').find('.send-sales').removeClass('mdi-spin');
							$('#generate-invoice-form').find('.after-save').addClass('hidden');
		
							$('#generate-invoice-form').find('.error-body').empty();
							$('#generate-invoice-form').find('.error-body').append(data['error']);
							$('#generate-invoice-form').find('.error-area').removeClass('hidden');
						} else {
							$('#generate-invoice-form').find('.loader').addClass('hidden');
							$('#generate-invoice-form').find('.saving-invoice').removeClass('mdi-spin');
							$('#generate-invoice-form').find('.saving-invoice').removeClass('mdi-minus');
							$('#generate-invoice-form').find('.saving-invoice').addClass('mdi-check-circle-outline text-success');
							var invoicePreview = getInvoiceBody(data['customer'], data['invoice'], data['details']);
							$('#generate-invoice-form').find('.invoice-part').append(invoicePreview)
						}
					},
					error: (data) => {
		
					}
				})
			});
		
			$('#dispatch-to-labs-modal').on('show.bs.modal', function () {
				$('#not-paid-parent').empty();
				if (notPaid.length > 0) {
					var bodyNot = `<div class="alert alert-danger p-2">
									<span class="text-center"><i class="mdi mdi-alert-decagram"></i> The following Batch(es) have not been paid fully </span>
									
									
									<div class="row mt-3" id="NotPaidBatches">
									
									</div>
								</div>`;
					$('#not-paid-parent').append(bodyNot);
					$.each(notPaid, function (j, k) {
						var batch_body = `
							<div class="col-md-6 col-sm-6 col-lg-6"><i class="mdi mdi-chevron-right"></i> ${k}</div>
						`
						$('#NotPaidBatches').append(batch_body);
					})
				}
			});
		
		
			const getSourceSelections = function () {
				const isSourceCheckbox = function () {
					const $checkbox = $(this);
					return $checkbox.closest('.modal').length === 0
						&& $checkbox.closest('.selected-submissions-request').length === 0
						&& $checkbox.closest('.selected-batches-request').length === 0;
				};

				return {
					selectedBatchCheckboxes: $("input[name='table_sample_id[]'][data-batch]:checked").filter(isSourceCheckbox),
					selectedSubmissionCheckboxes: $("input[name='portal_submission_id[]']:checked").filter(isSourceCheckbox),
					selectedFormInstanceCheckboxes: $("input[name='submission_form_instance_id[]']:checked").filter(isSourceCheckbox)
				};
			};

			const renderRequestReviewSelectionSummary = function () {
				const $containers = $('#dispatch-to-labs-modal .selected-submissions-request');
				if ($containers.length === 0) {
					return;
				}

				$containers.empty();

				const $formInputs = $("input[name='submission_form_instance_id[]'][form='dispatch-to-labs-modal-form']:checked").filter(function () {
					return $(this).closest('.selected-submissions-request').length === 0;
				});

				const $requestInputs = $("input[name='portal_submission_id[]'][form='dispatch-to-labs-modal-form']:checked").filter(function () {
					return $(this).closest('.selected-submissions-request').length === 0;
				});

				$formInputs.each(function () {
					const $source = $(this);
					const id = $source.val();
					const formNumber = $source.data('form-number') || ('Form #' + id);
					const formName = $source.data('form-name') || 'Template Form';
					$containers.append(`<span class="p-2 mr-2 d-inline-block"><input type="checkbox" name="submission_form_instance_id[]" value="${id}" checked> ${formNumber} <small class="text-muted">${formName}</small></span>`);
				});

				$requestInputs.each(function () {
					const $source = $(this);
					const id = $source.val();
					const submissionNumber = $source.data('submission-number') || ('Request #' + id);
					$containers.append(`<span class="p-2 mr-2 d-inline-block"><input type="checkbox" name="submission_request_id[]" value="${id}" checked> ${submissionNumber}</span>`);
				});
			};

			const rebuildLabBatchPrintLabels = function () {
				const $selected = $('input[data-lab-batch-select]:checked').filter(function () {
					return $(this).closest('.modal').length === 0;
				});
				const $container = $('#print-labels-modal .selected-samples');

				$container.empty();

				if ($selected.length === 0) {
					if ($container.length > 0) {
						$container.html('<div class="alert alert-callout alert-danger"><i class="fas fa-exclamation-triangle"></i> No batch selected.</div>');
					}

					return;
				}

				$selected.each(function () {
					const batchCode = $(this).attr('data-batch-code') || '';

					if (batchCode === '') {
						return;
					}

					$container.append(`<span class="p-2 mr-2 d-inline-block"><input type="hidden" name="sample_code[]" value="${batchCode}"> ${batchCode}</span>`);
				});
			};

			window.rebuildLabBatchPrintLabels = rebuildLabBatchPrintLabels;

			const rebuildEmailReportsSelection = function () {
				if ($('#send-email-reports-modal').length === 0) {
					return;
				}

				const $selected = $('input[data-lab-batch-select]:checked').filter(function () {
					return $(this).closest('.modal').length === 0;
				});
				const $container = $('.selected-batches');

				$container.empty();

				if ($selected.length === 0) {
					defaultClass = '';
					$('tr.batch-row').find('input[data-lab-batch-select]').removeAttr('disabled');

					return;
				}

				const $first = $selected.first();
				const custID = $first.closest('tr').data('class') || $first.data('customer-id');
				const customerClass = custID ? '.crm-customer-' + custID : '';

				if (customerClass !== '') {
					$('tr.batch-row').not(customerClass).find('input[data-lab-batch-select]').attr('disabled', true);
					defaultClass = customerClass;
				} else {
					$('tr.batch-row').find('input[data-lab-batch-select]').removeAttr('disabled');
					defaultClass = '';
				}

				$selected.each(function () {
					const batchId = $(this).val();
					const batchCode = $(this).data('batch-code') || batchId;
					$container.append(`<span class="p-2 mr-2"><input type="checkbox" name="sample_code[]" value="${batchId}" checked> ${batchCode}</span>`);
				});

				selectedSampleIDs = $selected.map(function () {
					return $(this).data('batch-code') || $(this).val();
				}).get();
			};

			window.rebuildEmailReportsSelection = rebuildEmailReportsSelection;

			const rebuildSelectionLists = function () {
				const selections = getSourceSelections();
				const selectedBatchCheckboxes = selections.selectedBatchCheckboxes;
				const selectedSubmissionCheckboxes = selections.selectedSubmissionCheckboxes;
				const selectedFormInstanceCheckboxes = selections.selectedFormInstanceCheckboxes;

				notPaid = [];

				if (selectedBatchCheckboxes.length > 0 || selectedSubmissionCheckboxes.length > 0 || selectedFormInstanceCheckboxes.length > 0) {
					$('[data-target="#delete-batch"]').removeAttr('disabled').addClass('btn-danger').removeClass('btn-outline-danger');
					$('[data-target="#inter-lab-add"]').removeAttr('disabled');
					$('[data-target="#move-to-lab"]').removeAttr('disabled');
					$('[data-target="#generarate_customer_focus"]').removeAttr('disabled');
					$('[data-target="#clone-batches"]').removeAttr('disabled');
					$('[data-target="#move-batch-complete"]').removeAttr('disabled');
					$('[data-target="#move-sample-approval"]').removeAttr('disabled');
					$('[data-target="#send-schedule-analysis"]').removeAttr('disabled');

					$('[data-target="#dispatch-to-labs-modal"]').removeAttr('disabled').addClass('btn-warning').removeClass('btn-outline-warning');
					$('[data-target="#dispatch-to-labs-modal-approve"]').removeAttr('disabled').addClass('btn-success').removeClass('btn-outline-success');
					$('[data-target="#portal-request-reject-form-modal"]').removeAttr('disabled').addClass('btn-danger').removeClass('btn-outline-danger');
					$('[data-target="#dispatch-to-labs-modal-review"]').removeAttr('disabled');
					$('[data-target="#dispatch-to-labs-modal-payment-reminder"]').removeAttr('disabled').removeClass('btn-outline-info').addClass('btn-info');
					$('[data-target = "#approve-begin-process"]').removeAttr('disabled').addClass('btn-outline-success').removeClass('btn-default');
				} else {
					$('[data-target="#delete-batch"]').attr('disabled', true).removeClass('btn-danger').addClass('btn-outline-danger');
					$('[data-target="#inter-lab-add"]').attr('disabled', true);
					$('[data-target="#move-to-lab"]').attr('disabled', true);
					$('[data-target="#generarate_customer_focus"]').attr('disabled', true);
					$('[data-target="#clone-batches"]').attr('disabled', true);
					$('[data-target="#move-batch-complete"]').attr('disabled', true);
					$('[data-target="#move-sample-approval"]').attr('disabled', true);
					$('[data-target="#send-schedule-analysis"]').attr('disabled', true);

					$('[data-target="#dispatch-to-labs-modal"]').attr('disabled', true).removeClass('btn-warning').addClass('btn-outline-warning');
					$('[data-target = "#approve-begin-process"]').removeAttr('disabled').addClass('btn-default').removeClass('btn-outline-success');
					$('[data-target="#dispatch-to-labs-modal-approve"]').attr('disabled', true).removeClass('btn-success').addClass('btn-outline-success');
					$('[data-target="#portal-request-reject-form-modal"]').attr('disabled', true).removeClass('btn-danger').addClass('btn-outline-danger');
					$('[data-target="#dispatch-to-labs-modal-review"]').attr('disabled', true);
					$('[data-target="#dispatch-to-labs-modal-payment-reminder"]').attr('disabled', true).removeClass('btn-info').addClass('btn-outline-info');
				}

				$('.selected-batches-review').empty();
				$('.selected-batches-interlab').empty();
				$('.selected-batches-clone').empty();
				$('.selected-batches-movetolab').empty();
				$('.selected-batches-request').empty();
				$('.selected-batches-request-approve').empty();
				$('.selected-submissions-request').empty();

				selectedBatchesIDs = selectedBatchCheckboxes.map(function () {
					var $value = $(this).val();
					var batch = $(this).data('batch') || {};

					if (batch.customer_paid == 0 && batch.batch_code) {
						notPaid.push(batch.batch_code);
					}

					$('.selected-batches-review').append(`<span class="p-2 mr-2"><input type="checkbox" name="batch_code[]" value="${$value}" checked> ${$value}</span>`);
					$('.selected-batches-interlab').append(`<span class="p-2 mr-2"><input type="checkbox" name="batch_code[]" value="${$value}" checked> ${$value}</span>`);
					$('.selected-batches-clone').append(`<span class="p-2 mr-2"><input type="checkbox" class="batch_clone" name="batch_code[]" value="${$value}" checked> ${$value}</span>`);
					$('.selected-batches-movetolab').append(`<span class="p-2 mr-2"><input type="checkbox" name="batch_code[]" value="${$value}" checked> ${$value}</span>`);
					$('.selected-batches-request').append(`<span class="p-2 mr-2"><input type="checkbox" name="batch_code[]" value="${$value}" checked> ${$value}</span>`);
					$('.selected-batches-request-approve').append(`<span class="p-2 mr-2"><input type="checkbox" name="batch_code[]" value="${$value}" checked> ${$value}</span>`);

					return $value;
				}).get();

				selectedSubmissionIDs = selectedSubmissionCheckboxes.map(function () {
					var $source = $(this);
					var submissionId = $(this).val();
					var submissionNumber = $(this).data('submission-number') || ('Request #' + submissionId);

					$('.selected-submissions-request').append(`<span class="p-2 mr-2 d-inline-block"><input type="checkbox" name="submission_request_id[]" value="${submissionId}" checked data-submission-number="${submissionNumber}" data-customer-name="${$source.data('customer-name') || ''}" data-customer-email="${$source.data('customer-email') || ''}" data-customer-phone="${$source.data('customer-phone') || ''}" data-customer-address="${$source.data('customer-address') || ''}" data-sample-type="${$source.data('sample-type') || ''}" data-number-samples="${$source.data('number-samples') || ''}" data-request-date="${$source.data('request-date') || ''}" data-mode-of-work="${$source.data('mode-of-work') || ''}"> ${submissionNumber}</span>`);

					return submissionId;
				}).get();

				selectedFormInstanceIDs = selectedFormInstanceCheckboxes.map(function () {
					var $source = $(this);
					var formInstanceId = $(this).val();
					var formNumber = $(this).data('form-number') || ('Form #' + formInstanceId);
					var formName = $(this).data('form-name') || 'Template Form';

					$('.selected-submissions-request').append(`<span class="p-2 mr-2 d-inline-block"><input type="checkbox" name="submission_form_instance_id[]" value="${formInstanceId}" checked data-form-number="${formNumber}" data-form-name="${formName}" data-customer-name="${$source.data('customer-name') || ''}" data-customer-email="${$source.data('customer-email') || ''}" data-customer-phone="${$source.data('customer-phone') || ''}" data-customer-address="${$source.data('customer-address') || ''}" data-sample-type="${$source.data('sample-type') || ''}" data-number-samples="${$source.data('number-samples') || ''}" data-request-date="${$source.data('request-date') || ''}" data-mode-of-work="${$source.data('mode-of-work') || ''}"> ${formNumber} <small class="text-muted">${formName}</small></span>`);

					return formInstanceId;
				}).get();

				let prefillFrom = selectedFormInstanceCheckboxes.first().length
					? selectedFormInstanceCheckboxes.first()
					: (selectedSubmissionCheckboxes.first().length ? selectedSubmissionCheckboxes.first() : null);

				if (!prefillFrom || !prefillFrom.length) {
					const mirroredForm = $(".selected-submissions-request input[name='submission_form_instance_id[]']:checked").first();
					const mirroredRequest = $(".selected-submissions-request input[name='submission_request_id[]']:checked").first();
					prefillFrom = mirroredForm.length ? mirroredForm : (mirroredRequest.length ? mirroredRequest : null);
				}

				const batchPrefill = selectedBatchCheckboxes.first().data('batch') || {};
				const requestPrefill = batchPrefill.sample_submission_request || {};
				const batchClientPrefill = batchPrefill.client || {};

				const customerName = prefillFrom
					? (prefillFrom.data('customer-name') || '')
					: (requestPrefill.submitting_agency || batchClientPrefill.name || '');
				const customerEmail = prefillFrom
					? (prefillFrom.data('customer-email') || '')
					: (requestPrefill.email || batchClientPrefill.email || '');
				const customerPhone = prefillFrom
					? (prefillFrom.data('customer-phone') || '')
					: (requestPrefill.mobile_telephone_no || requestPrefill.office_telephone_no || batchClientPrefill.telephone1 || '');
				const customerAddress = prefillFrom
					? (prefillFrom.data('customer-address') || '')
					: (requestPrefill.physical_address || batchClientPrefill.postal_address || '');
				const sampleType = prefillFrom
					? (prefillFrom.data('sample-type') || '')
					: ((batchPrefill.sample_type && batchPrefill.sample_type.name) ? batchPrefill.sample_type.name : '');
				const numberSamples = prefillFrom
					? (prefillFrom.data('number-samples') || '')
					: ((batchPrefill.samples && batchPrefill.samples.length) ? batchPrefill.samples.length : '');
				const requestDate = prefillFrom
					? (prefillFrom.data('request-date') || '')
					: (requestPrefill.submitted_by_date || '');
				const modeOfWork = prefillFrom
					? (prefillFrom.data('mode-of-work') || 'Normal')
					: ((batchPrefill.priority && String(batchPrefill.priority).toLowerCase() === 'express') ? 'Express' : 'Normal');

				$('[name="lab_acceptance[customer_name]"]').val(customerName);
				$('[name="lab_acceptance[address]"]').val(customerAddress);
				$('[name="lab_acceptance[email]"]').val(customerEmail);
				$('[name="lab_acceptance[tel]"]').val(customerPhone);
				$('[name="lab_acceptance[type_of_sample]"]').val(sampleType);
				$('[name="lab_acceptance[number_of_samples]"]').val(numberSamples);
				$('[name="lab_acceptance[date_of_sampling]"]').val(requestDate);
				$('[name="lab_acceptance[mode_of_work]"]').val(modeOfWork || 'Normal');
				$('[name="lab_acceptance[customer_name_certified]"]').val(customerName);
				$('[name="lab_acceptance[lab_no]"]').val(batchPrefill.batch_code || '');

				const sampleIdentifier = prefillFrom
					? (prefillFrom.data('submission-number') || prefillFrom.data('form-number') || '')
					: (batchPrefill.batch_code || '');
				$('[name="sample_rejection[sample_id]"]').val(sampleIdentifier);
				$('[name="sample_rejection[name_of_client]"]').val(customerName);
				$('[name="sample_rejection[date_sample_received]"]').val(requestDate);
				$('[name="sample_rejection[date_of_sample_collection]"]').val(requestDate);
				$('[name="sample_rejection[number_of_samples_received]"]').val(numberSamples);

			const shouldFetchPrefillData = $('#dispatch-to-labs-modal').hasClass('show') || $('#dispatch-to-labs-modal-review').hasClass('show');

			if (shouldFetchPrefillData) {
				// Populate parameters table from selected request/form.
				window.labAcceptanceParameters = [];
				window.labAcceptancePricelist = {};
				populateLabAcceptanceParametersTable();

				if (selectedSubmissionIDs.length > 0 || selectedFormInstanceIDs.length > 0) {
					var requestData = {};
					if (selectedSubmissionIDs.length > 0) {
						requestData.submission_request_id = selectedSubmissionIDs[0];
					} else {
						requestData.submission_form_instance_id = selectedFormInstanceIDs[0];
					}

					var parametersRequestKey = JSON.stringify(requestData);
					if (parametersRequestKey !== lastParametersRequestKey) {
						lastParametersRequestKey = parametersRequestKey;
						$.ajax({
							url: '{{ route("api.submission-request-parameters") }}',
							type: 'GET',
							data: requestData,
							success: function(response) {
								window.labAcceptanceParameters = response.parameters || [];
								window.labAcceptancePricelist = response.pricelist || {};
								populateLabAcceptanceParametersTable();
							},
							error: function(err) {
								console.warn('Failed to load parameters:', err);
							}
						});
					}
				}

				var previewData = {};
				if (selectedFormInstanceIDs.length > 0) {
					previewData.submission_form_instance_id = selectedFormInstanceIDs[0];
				} else if (selectedBatchesIDs.length > 0) {
					previewData.batch_code = selectedBatchesIDs[0];
				}

				if (Object.keys(previewData).length > 0) {
					var previewRequestKey = JSON.stringify(previewData);
					if (previewRequestKey !== lastPreviewRequestKey) {
						lastPreviewRequestKey = previewRequestKey;
						$.ajax({
							url: '{{ route("api.workflow.preview-batch-code") }}',
							type: 'GET',
							data: previewData,
							success: function(response) {
								if (response && response.is_new) {
									$('[name="lab_acceptance[lab_no]"]').val('[Auto-generated on approval]').prop('readonly', true);
								} else if (response && response.lab_no) {
									$('[name="lab_acceptance[lab_no]"]').val(response.lab_no).prop('readonly', true);
								}
								if (response && response.customer_name) {
									$('[name="lab_acceptance[customer_name]"]').val(response.customer_name);
									$('[name="lab_acceptance[customer_name_certified]"]').val(response.customer_name);
								}
								if (response && response.address) {
									$('[name="lab_acceptance[address]"]').val(response.address);
								}
								if (response && response.email) {
									$('[name="lab_acceptance[email]"]').val(response.email);
								}
								if (response && response.tel) {
									$('[name="lab_acceptance[tel]"]').val(response.tel);
								}
							},
							error: function(err) {
								console.warn('Failed to load preview batch code:', err);
							}
						});
					}
				}
			}
			};

			$(document)
				.off('change.workflowSelection', "input[name='table_sample_id[]'], input[name='portal_submission_id[]'], input[name='submission_form_instance_id[]']")
				.on('change.workflowSelection', "input[name='table_sample_id[]'][data-source-selection='1'], input[name='portal_submission_id[]'][data-source-selection='1'], input[name='submission_form_instance_id[]'][data-source-selection='1']", function () {
					rebuildSelectionLists();
				});

			$(document)
				.off('change.workflowLabBatch', 'input[data-lab-batch-select]')
				.on('change.workflowLabBatch', 'input[data-lab-batch-select]', function () {
					rebuildLabBatchPrintLabels();
					rebuildEmailReportsSelection();
				});

			rebuildLabBatchPrintLabels();
			rebuildEmailReportsSelection();

			// Bind directly on the modal elements — delegated $(document).on() with a custom namespace
			// suffix (e.g. show.bs.modal.workflowSelection) never fires because Bootstrap triggers
			// $.Event('show.bs.modal') with namespace ['bs','modal'] and jQuery's namespace matching
			// requires the listener's namespaces to be a subset of the event's namespaces.
			$('#portal-request-reject-form-modal').off('show.bs.modal.workflowSelection').on('show.bs.modal', function () {
				rebuildSelectionLists();
			});
			$('#dispatch-to-labs-modal').off('show.bs.modal.workflowSelection').on('show.bs.modal', function () {
				rebuildSelectionLists();
				renderRequestReviewSelectionSummary();
			});
			$('#dispatch-to-labs-modal-review').off('show.bs.modal.workflowSelection').on('show.bs.modal', function () {
				rebuildSelectionLists();
			});

			// Primary prefill trigger: fire rebuildSelectionLists the moment the user clicks a
			// modal-trigger button, BEFORE Bootstrap opens the modal. This is the most reliable
			// mechanism and handles Livewire re-render edge cases (delegated — survives DOM morphing).
			$('#print-labels-modal').off('show.bs.modal.workflowLabBatch').on('show.bs.modal', function () {
				rebuildLabBatchPrintLabels();
			});

			$('#print-labels-modal').off('click.workflowLabBatch', '.print-label-btn').on('click', '.print-label-btn', function () {
				const $form = $(this).closest('form');

				if ($form.find('input[name="sample_code[]"]').length === 0) {
					alert('Please select at least one batch before printing labels.');

					return;
				}

				$form.trigger('submit');
			});

			$(document)
				.off('click.prefillWorkflow', '[data-target="#portal-request-reject-form-modal"], [data-target="#dispatch-to-labs-modal"], [data-target="#dispatch-to-labs-modal-review"], [data-target="#print-labels-modal"], [data-target="#send-email-reports-modal"]')
				.on('click.prefillWorkflow', '[data-target="#portal-request-reject-form-modal"], [data-target="#dispatch-to-labs-modal"], [data-target="#dispatch-to-labs-modal-review"], [data-target="#print-labels-modal"], [data-target="#send-email-reports-modal"]', function (event) {
					const target = $(this).data('target');
					rebuildSelectionLists();
					if (target === '#print-labels-modal') {
						rebuildLabBatchPrintLabels();
					}
					if (target === '#send-email-reports-modal') {
						const selectedCount = $('input[data-lab-batch-select]:checked').filter(function () {
							return $(this).closest('.modal').length === 0;
						}).length;

						if (selectedCount === 0) {
							event.preventDefault();
							event.stopImmediatePropagation();

							return;
						}

						rebuildEmailReportsSelection();
					}
					if (target === '#dispatch-to-labs-modal') {
						renderRequestReviewSelectionSummary();
					}

					if (target === '#dispatch-to-labs-modal') {
						const selections = getSourceSelections();
						if (selections.selectedBatchCheckboxes.length === 0
							&& selections.selectedSubmissionCheckboxes.length === 0
							&& selections.selectedFormInstanceCheckboxes.length === 0) {
							event.preventDefault();
							event.stopImmediatePropagation();
							$('#dispatch-to-labs-modal .js-selection-error').remove();
							$('<div class="alert alert-danger js-selection-error mb-2">Please tick at least one request/form row before sending to Request Review.</div>')
								.prependTo($('#dispatch-to-labs-modal .modal-body'));
							return false;
						}
					}
				});

			window.rebuildWorkflowSelectionLists = rebuildSelectionLists;

			window.openWorkflowAcceptSampleModal = function () {
				const checked = document.querySelectorAll('input[data-instance-select]:checked');
				const ids = Array.from(checked).map((el) => el.value).filter(Boolean);

				if (ids.length !== 1) {
					alert('Please select exactly one submission request or form row before accepting.');
					return;
				}

				if (typeof Livewire !== 'undefined') {
					Livewire.dispatch('open-acceptance-wizard', {
						submissionFormInstanceId: ids[0],
						submissionRequestId: null,
					});
				}
			};

			window.openWorkflowRejectSampleModal = function () {
				const checked = document.querySelectorAll('input[data-instance-select]:checked');
				const ids = Array.from(checked).map((el) => el.value).filter(Boolean);

				if (ids.length !== 1) {
					alert('Please select exactly one submission request or form row before rejecting.');
					return;
				}

				if (typeof Livewire !== 'undefined') {
					Livewire.dispatch('open-rejection-wizard', {
						submissionFormInstanceId: ids[0],
						submissionRequestId: null,
					});
				}
			};

			$('#dispatch-to-labs-modal form').off('submit.workflowSelection').on('submit.workflowSelection', function (event) {
				const $form = $(this);
				rebuildSelectionLists();
				renderRequestReviewSelectionSummary();
				const selections = getSourceSelections();

				$form.find('.js-selection-error').remove();
				if (selections.selectedBatchCheckboxes.length === 0
					&& selections.selectedSubmissionCheckboxes.length === 0
					&& selections.selectedFormInstanceCheckboxes.length === 0) {
					event.preventDefault();
					$('<div class="alert alert-danger js-selection-error mb-2">No requests were selected. Please tick at least one request/form row and try again.</div>')
						.prependTo($form.find('.modal-body'));
					return false;
				}

				$form.find('input.js-injected-selection').remove();

				selections.selectedBatchCheckboxes.each(function () {
					$('<input>')
						.attr('type', 'hidden')
						.attr('name', 'batch_code[]')
						.attr('value', $(this).val())
						.addClass('js-injected-selection')
						.appendTo($form);
				});

				selections.selectedSubmissionCheckboxes.each(function () {
					$('<input>')
						.attr('type', 'hidden')
						.attr('name', 'submission_request_id[]')
						.attr('value', $(this).val())
						.addClass('js-injected-selection')
						.appendTo($form);
				});

				selections.selectedFormInstanceCheckboxes.each(function () {
					$('<input>')
						.attr('type', 'hidden')
						.attr('name', 'submission_form_instance_id[]')
						.attr('value', $(this).val())
						.addClass('js-injected-selection')
						.appendTo($form);
				});
			});

			rebuildSelectionLists();

			$('#send-email-reports-modal').on('show.bs.modal', function () {
				rebuildEmailReportsSelection();

				const $selected = $('input[data-lab-batch-select]:checked').filter(function () {
					return $(this).closest('.modal').length === 0;
				});
				const custID = $selected.first().closest('tr').data('class') || $selected.first().data('customer-id');

				if (custID) {
					$.ajax({
						url: '/get-customer-contacts/receive_report/' + custID,
						dataType: 'json',
						beforeSend: function () {
							$('select[name="contacts[]"]').empty();
						},
						success: function (js) {
							$.each(js, function (j, s) {
								$('select[name="contacts[]"]').append(`<option value="${s.id}">${s.first_name + ' ' + s.middle_name + ' ' + s.last_name} [${s.email}]</option>`);
							});
						}
					});
				}

				$('#send-email-reports-modal').find('.add-contact-fields').removeClass('hidden');
				$('#send-email-reports-modal').find('.save-add-contact').off('click').on('click', function () {
						var body = {
							first_name: $('.add-contact-fields').find('.first_name').val(),
							middle_name: $('.add-contact-fields').find('.middle_name').val(),
							surname: $('.add-contact-fields').find('.surname').val(),
							job_occupation: $('.add-contact-fields').find('.job_occupation').val(),
							unit_name: $('.add-contact-fields').find('.unit_name').val(),
							email: $('.add-contact-fields').find('.email').val(),
							telephone: $('.add-contact-fields').find('.telephone').val(),
							mobile: $('.add-contact-fields').find('.mobile').val(),
							receive_price_list: $('.add-contact-fields').find('.receive_price_list').is(':checked') ? 1 : 0,
							receive_invoice: $('.add-contact-fields').find('.receive_invoice').is(':checked') ? 1 : 0,
						};

						$.ajax({
							url: ``,
							type: 'POST',
							data: body,
							success: function () {
								$('#send-email-reports-modal').find('.add-contact-fields').addClass('hidden');
							},
							error: function (data) {
								console.log(data);
							}
						});
				});
			});

		
			var detectChange = function (ts) {
				var op = $(ts).children('option:selected');
				$('#client-unit-select').html('<option value="" selected>Select Organizational Unit...</option>');
				$('#client-unit-select').trigger('change');
				$.each(op.data('units'), function (i, e) {
					$('#client-unit-select').append('<option value="' + e.name + '">' + e.name + '</option>');
				});
			};
		
			$('[name="is_routine"]').on('change', function () {
				if ($(this).is(':checked')) {
					$('#routine_frequency').removeClass('hidden');
					$('[name="routine_frequency"]').prop('required');
					$('[name="routine_frequency"]').attr('required');
				} else {
					$('#routine_frequency').addClass('hidden');
					$('[name="routine_frequency"]').find("option:selected").removeAttr("selected");
					$('[name="routine_frequency"]').find("option:selected").removeProp("selected");
					$('[name="routine_frequency"]').removeAttr('required');
					$('[name="routine_frequency"]').removeProp('required');
				}
			});
		
			$('.form-part-toggler').on('click', function () {
				var parentSibling = $(this).parents('.form-part').siblings();
		
				var sibformPartSibling = parentSibling.find('.form-part-toggler');
				var siblingformrowData = parentSibling.find('.form-data-row');
		
				$(this).find('i').removeClass('fa-arrow-down').addClass('fa-arrow-up');
				sibformPartSibling.find('i').removeClass('fa-arrow-up').addClass('fa-arrow-down');
		
				$(this).parents('.form-part').find('.form-data-row').addClass('hidden');
				siblingformrowData.removeClass('hidden');
			});
		
			// Submission Form Modal Functionality
			let availableForms = [];
			const submissionFormContextRoute = @json((function () {
				$route = request()->route();
				if (!$route) {
					return '';
				}

				$routeName = (string) $route->getName();
				if ($routeName === '') {
					return '';
				}

				$status = $route->parameter('status');
				if (is_string($status) && trim($status) !== '') {
					return $routeName . '@status=' . trim($status);
				}

				return $routeName;
			})());
		
			// Test button click
			$(document).on('click', '[data-target="#add-submission-form-modal"]', function() {
				console.log('Add Submission Form button clicked!');
			});
		
			// Load forms when modal is shown
			$('#add-submission-form-modal').on('show.bs.modal', function() {
				console.log('Modal is opening, loading forms...');
				loadAvailableForms();
				
				// Initialize Select2 with dropdownParent to ensure it's above the modal
				setTimeout(function() {
					$('#submission-form-select').select2({
						dropdownParent: $('#add-submission-form-modal'),
						placeholder: 'Select a submission form...',
						allowClear: true,
						width: '100%'
					});
				}, 100);
			});
		
			// Reset modal when hidden
			$('#add-submission-form-modal').on('hidden.bs.modal', function() {
				resetModal();
			});
		
			// Handle form selection change
			$(document).on('change', '#submission-form-select', function() {
				const selectedFormId = $(this).val();
				if (selectedFormId) {
					const selectedForm = availableForms.find(form => form.id == selectedFormId);
					if (selectedForm) {
						showFormPreview(selectedForm);
						$('#create-form-instance-btn').prop('disabled', false);
					}
				} else {
					hideFormPreview();
					$('#create-form-instance-btn').prop('disabled', true);
				}
			});
		
			// Handle create form instance button click
			$(document).on('click', '#create-form-instance-btn', function() {
				const selectedFormId = $('#submission-form-select').val();
				if (selectedFormId) {
					createFormInstance(selectedFormId);
				}
			});
		
			function loadAvailableForms() {
				console.log('loadAvailableForms called');
				$('#submission-form-loading').show();
				$('#submission-form-content').hide();
				$('#no-forms-message').hide();
		
				const url = '{{ route("sample-workflow.submission-forms") }}';
				const contextRoute = submissionFormContextRoute;
				console.log('Making AJAX request to:', url);
		
				$.ajax({
					url: url,
					method: 'GET',
					data: {
						context_route: contextRoute
					},
					headers: {
						'X-Requested-With': 'XMLHttpRequest',
						'Accept': 'application/json'
					},
					success: function(response) {
						console.log('AJAX success:', response);
						$('#submission-form-loading').hide();
		
						if (response.success && response.forms.length > 0) {
							availableForms = response.forms;
							populateFormSelect(response.forms);
							$('#submission-form-content').show();
						} else {
							$('#no-forms-message').show();
						}
					},
					error: function(xhr, status, error) {
						console.error('AJAX error:', xhr, status, error);
						$('#submission-form-loading').hide();
						alert('Failed to load submission forms. Please try again.');
					}
				});
			}
		
			function populateFormSelect(forms) {
				console.log('populateFormSelect called with forms:', forms);
				const select = $('#submission-form-select');
				select.empty();
				select.append('<option value="">Select a submission form...</option>');
				
				forms.forEach(function(form) {
					console.log('Adding form option:', form.name, form.id);
					select.append(`<option value="${form.id}">${form.name}</option>`);
				});
				
				// Update Select2 if it's already initialized
				if (select.hasClass('select2-hidden-accessible')) {
					select.trigger('change');
				}
				console.log('Form select populated with', forms.length, 'options');
			}
		
			function showFormPreview(form) {
				$('#form-name-preview').text(form.name);
				$('#form-description-preview').text(form.description || 'No description available');
				$('#form-sections-count').text(form.sections_count);
				$('#form-creator').text(form.creator);
				$('#form-created-date').text(form.created_at);
				$('#form-preview').show();
			}
		
			function hideFormPreview() {
				$('#form-preview').hide();
			}
		
			function resetModal() {
				$('#submission-form-select').val('');
				$('#create-form-instance-btn').prop('disabled', true);
				hideFormPreview();
				availableForms = [];
			}
		
			function createFormInstance(formId) {
				const btn = $('#create-form-instance-btn');
				const originalText = btn.html();
				const contextRoute = submissionFormContextRoute;
				
				// Show loading state
				btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Creating...');
		
				$.ajax({
					url: '{{ route("sample-workflow.create-form-instance") }}',
					method: 'POST',
					data: {
						submission_form_id: formId,
						context_route: contextRoute,
						_token: '{{ csrf_token() }}'
					},
					success: function(response) {
						if (response.success) {
							// Close modal
							$('#add-submission-form-modal').modal('hide');
							
							// Redirect to form fill page
							window.location.href = response.redirect_url;
						} else {
							alert('Error: ' + (response.message || 'Failed to create form instance'));
							btn.prop('disabled', false).html(originalText);
						}
					},
					error: function(xhr) {
						console.error('Error creating form instance:', xhr);
						let errorMessage = 'Failed to create form instance. Please try again.';
						
						if (xhr.responseJSON && xhr.responseJSON.message) {
							errorMessage = xhr.responseJSON.message;
						}
						
						alert('Error: ' + errorMessage);
						btn.prop('disabled', false).html(originalText);
					}
				});
			}
		
			// New Draft Invoice Wizard Integration
			$('.proceed-to-wizard-btn').on('click', function() {
				// Collect selected batch codes
				var selectedBatches = [];
				$('.selected-batches-request-approve input[name="batch_code[]"]').each(function() {
					selectedBatches.push($(this).val());
				});
		
				if (selectedBatches.length === 0) {
					alert('No batches selected');
					return;
				}
		
				// Build URL with batch codes as query parameters
				var params = new URLSearchParams();
				selectedBatches.forEach(function(code) {
					params.append('batches[]', code);
				});
		
				var wizardUrl = '{{ route("billing.sales-order.create") }}?' + params.toString();
				
			// Redirect to wizard
			window.location.href = wizardUrl;
		});
	
		// Handle blur on customer search input with delay to allow dropdown clicks
		document.addEventListener('livewire:init', function() {
			document.addEventListener('blur', function(e) {
				if (e.target.classList.contains('customer-search-input')) {
					setTimeout(function() {
						// Only close if not clicking on dropdown item
						if (!document.activeElement || !document.activeElement.closest('.tag-dropdown')) {
							@this.set('showCustomerDropdown', false);
						}
					}, 200);
				}
			}, true);
		});
	
		// Handle infinite scroll for customer dropdown
		let scrollTimeout;
		function handleCustomerScroll(e) {
			const dropdown = e.target;
			const scrollTop = dropdown.scrollTop;
			const scrollHeight = dropdown.scrollHeight;
			const clientHeight = dropdown.clientHeight;
			
			// Clear existing timeout
			clearTimeout(scrollTimeout);
			
			// Check if scrolled to bottom (within 50px)
			if (scrollHeight - scrollTop - clientHeight < 50) {
				// Debounce the load more call
				scrollTimeout = setTimeout(function() {
					if (window.Livewire) {
						@this.call('loadMoreCustomers');
					}
				}, 100);
			}
		}

		function onLivewireMorphUpdated(callback) {
			if (window.Livewire && typeof window.Livewire.hook === 'function') {
				window.Livewire.hook('morph.updated', callback);
				return;
			}

			document.addEventListener('livewire:init', function registerMorphHook() {
				if (window.Livewire && typeof window.Livewire.hook === 'function') {
					window.Livewire.hook('morph.updated', callback);
				}
			}, { once: true });
		}
	
		// Initialize scroll handler when dropdown is shown
		onLivewireMorphUpdated(({ el, component }) => {
			setTimeout(function() {
				const dropdown = document.getElementById('customer-dropdown-list');
				if (dropdown) {
					// Remove existing listener to avoid duplicates
					dropdown.removeEventListener('scroll', handleCustomerScroll);
					// Add scroll listener
					dropdown.addEventListener('scroll', handleCustomerScroll, { passive: true });
				}
			}, 50);
		});
	
		// Also initialize on page load
		document.addEventListener('DOMContentLoaded', function() {
			setTimeout(function() {
				const dropdown = document.getElementById('customer-dropdown-list');
				if (dropdown) {
					dropdown.addEventListener('scroll', handleCustomerScroll, { passive: true });
				}
			}, 100);
		});
	
		// Initialize after Livewire loads
		document.addEventListener('livewire:init', function() {
			setTimeout(function() {
				const dropdown = document.getElementById('customer-dropdown-list');
				if (dropdown) {
					dropdown.addEventListener('scroll', handleCustomerScroll, { passive: true });
				}
			}, 100);
		});
	
		// Hide loading overlay on initial page load
		document.addEventListener('DOMContentLoaded', function() {
			const loadingOverlay = document.querySelector('.table-filter-loading');
			if (loadingOverlay) {
				loadingOverlay.style.display = 'none';
			}
		});
	
		// Hide loading overlay after Livewire initializes
		document.addEventListener('livewire:init', function() {
			setTimeout(function() {
				const loadingOverlay = document.querySelector('.table-filter-loading');
				if (loadingOverlay) {
					loadingOverlay.style.display = 'none';
				}
			}, 500);

			Livewire.on('show-receive-sample-modal', function (payload) {
				const physical = payload?.physicalCheckIn ?? false;
				const modalEl = document.getElementById('receive-sample-modal');
				const dialogEl = document.getElementById('receive-sample-modal-dialog');
				const titleEl = document.getElementById('receive-sample-modal-title-text');
				const iconEl = document.getElementById('receive-sample-modal-icon');
				const subtitleEl = document.getElementById('receive-sample-modal-subtitle');
				if (modalEl) {
					modalEl.classList.toggle('receive-sample-modal--compact', physical);
				}
				if (dialogEl) {
					dialogEl.classList.toggle('modal-xl', !physical);
					dialogEl.classList.toggle('modal-dialog-scrollable', !physical);
				}
				if (titleEl && iconEl) {
					if (physical) {
						titleEl.textContent = 'Move to In Review';
						iconEl.className = 'mdi mdi-clipboard-arrow-right text-primary mr-2';
						if (subtitleEl) {
							subtitleEl.textContent = '';
							subtitleEl.classList.add('d-none');
						}
					} else {
						titleEl.textContent = 'Test Request Form';
						iconEl.className = 'mdi mdi-clipboard-text text-primary mr-2';
						if (subtitleEl) {
							subtitleEl.textContent = '';
							subtitleEl.classList.add('d-none');
						}
					}
				}
				$('#receive-sample-modal').modal('show');
				setTimeout(function () {
					if (typeof window.initTrfSignaturePads === 'function') {
						window.initTrfSignaturePads();
					}
					if (typeof window.initWalkInTrfParameterSelects === 'function') {
						window.initWalkInTrfParameterSelects();
					}
				}, 200);
			});

			Livewire.on('trf-reinit-parameter-selects', function (payload) {
				setTimeout(function () {
					if (typeof window.initWalkInTrfParameterSelects === 'function') {
						window.initWalkInTrfParameterSelects({
							rowIndex: payload?.rowIndex,
						});
					}
				}, 120);
			});

			Livewire.on('trf-reset-all-parameter-selects', function () {
				setTimeout(function () {
					const modal = document.getElementById('receive-sample-modal');
					if (modal) {
						modal.querySelectorAll('.walk-in-trf-parameters-select').forEach(function (select) {
							if (typeof window.destroyWalkInParamSelect === 'function') {
								window.destroyWalkInParamSelect($(select));
							}
						});
					}

					if (typeof window.initWalkInTrfParameterSelects === 'function') {
						window.initWalkInTrfParameterSelects();
					}
				}, 150);
			});

			Livewire.on('walk-in-params-row-reset', function (payload) {
				setTimeout(function () {
					if (typeof window.resetWalkInParamRow !== 'function') {
						return;
					}

					const modal = document.getElementById('receive-sample-modal');
					if (!modal || payload?.rowIndex === undefined) {
						return;
					}

					const wrap = modal.querySelector('.walk-in-trf-parameters-wrap[data-row-index="' + payload.rowIndex + '"]');
					if (!wrap) {
						return;
					}

					window.resetWalkInParamRow(wrap, payload.options || [], payload.selected || []);
				}, 80);
			});

			Livewire.on('trf-reinit-signatures', function () {
				setTimeout(function () {
					if (typeof window.initTrfSignaturePads === 'function') {
						window.initTrfSignaturePads();
					}
				}, 150);
			});

			Livewire.on('walk-in-trf-step-changed', function () {
				const modalBody = document.querySelector('#receive-sample-modal .modal-body');
				if (modalBody) {
					modalBody.scrollTop = 0;
				}
			});

			$('#receive-sample-modal').on('hidden.bs.modal', function () {
				const modalEl = document.getElementById('receive-sample-modal');
				const dialogEl = document.getElementById('receive-sample-modal-dialog');
				if (modalEl) {
					modalEl.classList.remove('receive-sample-modal--compact');
				}
				if (dialogEl) {
					dialogEl.classList.add('modal-xl', 'modal-dialog-scrollable');
				}
				window.dispatchEvent(new CustomEvent('trf-destroy-editors'));
				document.querySelectorAll('.walk-in-trf-parameters-select').forEach(function (select) {
					if (typeof window.destroyWalkInParamSelect === 'function') {
						window.destroyWalkInParamSelect($(select));
					}
				});
				document.querySelectorAll('.trf-signature-canvas').forEach(function (canvas) {
					delete canvas.dataset.signatureInitialized;
					canvas._trfSignaturePad = null;
				});
				if (typeof tinymce !== 'undefined') {
					document.querySelectorAll('.trf-sample-desc-editor textarea').forEach(function (el) {
						if (tinymce.get(el.id)) {
							tinymce.remove('#' + el.id);
						}
					});
				}
			});

			window.syncWalkInParametersBeforeSubmit = function () {
				const modal = document.getElementById('receive-sample-modal');
				if (!modal || typeof $ === 'undefined') {
					return;
				}

				modal.querySelectorAll('.walk-in-trf-parameters-wrap').forEach(function (wrap) {
					const $select = $(wrap).find('.walk-in-trf-parameters-select');
					if ($select.length === 0) {
						return;
					}

					const livewireModel = $(wrap).data('livewire-model') || $select.data('livewire-model');
					const componentEl = wrap.closest('[wire\\:id]');
					const selected = $select.val() || [];

					if (livewireModel && componentEl && window.Livewire) {
						const component = Livewire.find(componentEl.getAttribute('wire:id'));
						if (component) {
							component.set(livewireModel, selected);
						}
					}
				});
			};

			window.syncTrfSignaturesBeforeSubmit = function () {
				if (typeof SignaturePad === 'undefined') {
					return;
				}

				document.querySelectorAll('.trf-signature-canvas').forEach(function (canvas) {
					const pad = canvas._trfSignaturePad;
					const fieldName = canvas.getAttribute('data-field');
					if (!pad || !fieldName) {
						return;
					}

					const input = document.getElementById('field_' + fieldName);
					if (!input) {
						return;
					}

					const value = pad.isEmpty() ? '' : pad.toDataURL('image/png');
					input.value = value;

					const livewireModel = canvas.getAttribute('data-livewire-model');
					const componentEl = canvas.closest('[wire\\:id]');
					if (livewireModel && componentEl && window.Livewire) {
						const component = Livewire.find(componentEl.getAttribute('wire:id'));
						if (component) {
							component.set(livewireModel, value);
						}
					}

					input.dispatchEvent(new Event('input', { bubbles: true }));
				});
			};

			window.initTrfSignaturePads = function (forceReinit) {
				if (typeof SignaturePad === 'undefined') {
					return;
				}

				function syncSignatureValue(canvas, input, pad) {
					const value = pad.isEmpty() ? '' : pad.toDataURL('image/png');
					input.value = value;

					const livewireModel = canvas.getAttribute('data-livewire-model');
					const componentEl = canvas.closest('[wire\\:id]');
					if (livewireModel && componentEl && window.Livewire) {
						const component = Livewire.find(componentEl.getAttribute('wire:id'));
						if (component) {
							component.set(livewireModel, value, false);
						}
					}

					input.dispatchEvent(new Event('input', { bubbles: true }));
				}

				function setupTrfSignatureCanvas(canvas) {
					if (forceReinit) {
						delete canvas.dataset.signatureInitialized;
						canvas._trfSignaturePad = null;
					}

					const fieldName = canvas.getAttribute('data-field');
					const input = document.getElementById('field_' + fieldName);
					if (!input || !canvas.parentElement) {
						return;
					}

					const rect = canvas.getBoundingClientRect();
					const width = rect.width > 10 ? rect.width : canvas.offsetWidth;
					const height = rect.height > 10 ? rect.height : canvas.offsetHeight;
					if (width < 10 || height < 10) {
						delete canvas.dataset.signatureInitialized;
						return;
					}

					if (canvas.dataset.signatureInitialized === '1' && canvas._trfSignaturePad) {
						return;
					}

					canvas.dataset.signatureInitialized = '1';
					const ratio = Math.max(window.devicePixelRatio || 1, 1);
					canvas.width = width * ratio;
					canvas.height = height * ratio;
					const context = canvas.getContext('2d');
					context.setTransform(1, 0, 0, 1, 0, 0);
					context.scale(ratio, ratio);

					const pad = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)' });
					canvas._trfSignaturePad = pad;

					if (input.value) {
						try { pad.fromDataURL(input.value); } catch (e) {}
					}

					pad.addEventListener('endStroke', function () {
						syncSignatureValue(canvas, input, pad);
					});

					const clearBtn = canvas.parentElement.querySelector('.trf-signature-clear[data-canvas="' + canvas.id + '"]');
					if (clearBtn) {
						clearBtn.onclick = function () {
							pad.clear();
							syncSignatureValue(canvas, input, pad);
						};
					}
				}

				document.querySelectorAll('.trf-signature-canvas').forEach(setupTrfSignatureCanvas);
			};

			window.saveWalkInTrfDescription = function (modalId, editorId, wireKey) {
				const editor = typeof tinymce !== 'undefined' ? tinymce.get(editorId) : null;
				const content = editor ? editor.getContent() : '';
				const componentEl = document.querySelector('#receive-sample-modal [wire\\:id]');

				if (componentEl && window.Livewire && wireKey) {
					const component = Livewire.find(componentEl.getAttribute('wire:id'));
					if (component) {
						component.set(wireKey, content, false);
					}
				}

				const $modal = $('#' + modalId);
				if ($modal.length && $.fn.modal) {
					$modal.modal('hide');
				}
			};

			window.destroyWalkInParamSelect = function ($select) {
				if (!$select || !$select.length) {
					return;
				}

				if ($select.hasClass('select2-hidden-accessible')) {
					$select.off('change.walkin-params');
					$select.select2('destroy');
				}

				$select.removeData('walkin-params-bound');
			};

			window.updateWalkInParamSelectionCount = function (wrapEl, selected) {
				if (!wrapEl) {
					return;
				}

				const countEl = wrapEl.querySelector('.walk-in-trf-parameters-count');
				if (!countEl) {
					return;
				}

				let options = [];
				try {
					options = JSON.parse(wrapEl.getAttribute('data-options') || '[]');
				} catch (error) {
					options = [];
				}

				if (!Array.isArray(options) || options.length === 0) {
					const $select = typeof $ !== 'undefined'
						? $(wrapEl).find('.walk-in-trf-parameters-select')
						: null;
					if ($select && $select.length) {
						options = $select.find('option').map(function () {
							return this.value;
						}).get();
					}
				}

				const selectedCount = Array.isArray(selected) ? selected.length : 0;
				countEl.textContent = options.length > 0
					? (selectedCount + '/' + options.length + ' selected')
					: '';
			};

			window.applyWalkInParamBulkSelection = function (wrapEl, selectAll) {
				if (!wrapEl || typeof $ === 'undefined' || !$.fn.select2) {
					return;
				}

				const $wrap = $(wrapEl);
				const $select = $wrap.find('.walk-in-trf-parameters-select');
				if ($select.length === 0) {
					return;
				}

				let options = [];
				try {
					options = JSON.parse(wrapEl.getAttribute('data-options') || '[]');
				} catch (error) {
					options = [];
				}

				if (!Array.isArray(options) || options.length === 0) {
					options = $select.find('option').map(function () {
						return this.value;
					}).get();
				}

				const selected = selectAll ? options : [];
				$select.val(selected).trigger('change');
				$wrap.attr('data-selected', JSON.stringify(selected));
				window.updateWalkInParamSelectionCount(wrapEl, selected);
			};

			window.bindWalkInParamSelect = function ($select) {
				if (!$select || !$select.length || $select.data('walkin-params-bound')) {
					return;
				}

				const modal = document.getElementById('receive-sample-modal');
				const livewireModel = $select.data('livewire-model');
				const componentEl = $select.closest('[wire\\:id]');
				const wrapEl = $select.closest('.walk-in-trf-parameters-wrap').get(0);

				$select.select2({
					width: '100%',
					placeholder: 'Select parameters...',
					allowClear: true,
					closeOnSelect: false,
					dropdownParent: modal ? $(modal) : $(document.body),
				});

				$select.on('change.walkin-params', function () {
					const val = $(this).val() || [];

					if (wrapEl) {
						$(wrapEl).attr('data-selected', JSON.stringify(val));
						window.updateWalkInParamSelectionCount(wrapEl, val);
					}

					if (livewireModel && componentEl && window.Livewire) {
						const component = Livewire.find(componentEl.getAttribute('wire:id'));
						if (component) {
							component.set(livewireModel, val, false);
						}
					}
				});

				$select.data('walkin-params-bound', true);
			};

			window.resetWalkInParamRow = function (wrapEl, options, selected) {
				if (!wrapEl || typeof $ === 'undefined' || !$.fn.select2) {
					return;
				}

				const $wrap = $(wrapEl);
				const $select = $wrap.find('.walk-in-trf-parameters-select');
				const livewireModel = $wrap.data('livewire-model');
				const componentEl = $wrap.closest('[wire\\:id]');
				const safeSelected = Array.isArray(selected) ? selected : [];
				const safeOptions = Array.isArray(options) ? options : [];

				window.destroyWalkInParamSelect($select);
				$select.empty();

				safeOptions.forEach(function (name) {
					const isSelected = safeSelected.indexOf(name) !== -1;
					$select.append(new Option(name, name, isSelected, isSelected));
				});

				window.bindWalkInParamSelect($select);
				$select.val(safeSelected).trigger('change.select2');
				$wrap.attr('data-options', JSON.stringify(safeOptions));
				$wrap.attr('data-selected', JSON.stringify(safeSelected));
				window.updateWalkInParamSelectionCount(wrapEl, safeSelected);
				$wrap.find('[data-walk-in-params-action]').prop('disabled', safeOptions.length === 0);

				if (livewireModel && componentEl && window.Livewire) {
					const component = Livewire.find(componentEl.getAttribute('wire:id'));
					if (component) {
						component.set(livewireModel, safeSelected, false);
					}
				}
			};

			window.initWalkInTrfParameterSelects = function (options) {
				options = options || {};
				const targetRowIndex = options.rowIndex;

				if (typeof $ === 'undefined' || !$.fn.select2) {
					return;
				}

				const modal = document.getElementById('receive-sample-modal');
				if (!modal) {
					return;
				}

				modal.querySelectorAll('.walk-in-trf-parameters-wrap').forEach(function (wrap) {
					const rowIndex = wrap.getAttribute('data-row-index');
					if (targetRowIndex !== undefined && targetRowIndex !== null && String(rowIndex) !== String(targetRowIndex)) {
						return;
					}

					const $select = $(wrap).find('.walk-in-trf-parameters-select');
					if ($select.length === 0 || $select.data('walkin-params-bound')) {
						return;
					}

					let selected = [];
					try {
						selected = JSON.parse(wrap.getAttribute('data-selected') || '[]');
					} catch (e) {
						selected = [];
					}

					window.bindWalkInParamSelect($select);
					$select.val(selected).trigger('change.select2');
					window.updateWalkInParamSelectionCount(wrap, selected);
				});
			};

			$(document).off('click.walkin-params-bulk', '#receive-sample-modal [data-walk-in-params-action]')
				.on('click.walkin-params-bulk', '#receive-sample-modal [data-walk-in-params-action]', function (event) {
					event.preventDefault();
					const wrap = this.closest('.walk-in-trf-parameters-wrap');
					if (!wrap || this.disabled) {
						return;
					}

					window.applyWalkInParamBulkSelection(
						wrap,
						this.getAttribute('data-walk-in-params-action') === 'select-all',
					);
				});

			Livewire.on('hide-receive-sample-modal', function () {
				$('#receive-sample-modal').modal('hide');
			});

			Livewire.on('open-test-request-pdf', ({ url }) => {
				if (url) {
					window.open(url, '_blank');
				}
			});

			Livewire.on('show-request-additional-info-modal', function () {
				$('#request-additional-info-modal').modal('show');
			});

			Livewire.on('hide-request-additional-info-modal', function () {
				$('#request-additional-info-modal').modal('hide');
			});

			Livewire.on('show-analyst-review-modal', function () {
				$('#send-for-analyst-review-modal').modal('show');
			});

			Livewire.on('hide-analyst-review-modal', function () {
				$('#send-for-analyst-review-modal').modal('hide');
			});

			Livewire.on('show-subcontract-dispatch-modal', function () {
				$('#subcontract-dispatch-modal').modal('show');
			});

			Livewire.on('hide-subcontract-dispatch-modal', function () {
				$('#subcontract-dispatch-modal').modal('hide');
			});

			Livewire.on('open-subcontract-label-tab', function ({ url }) {
				if (url) {
					window.open(url, '_blank');
				}
			});

			Livewire.on('show-move-to-intray-modal', function () {
				$('#move-to-intray-modal').modal('show');
			});

			Livewire.on('hide-move-to-intray-modal', function () {
				$('#move-to-intray-modal').modal('hide');
			});
		});
	
		// Ensure overlay hides after Livewire updates complete
		onLivewireMorphUpdated(({ el, component }) => {
			// Overlay will be shown/hidden by wire:loading automatically
			// This ensures it doesn't stay visible
			setTimeout(function() {
				const loadingOverlay = document.querySelector('.table-filter-loading');
				if (loadingOverlay && !loadingOverlay.hasAttribute('wire:loading')) {
					loadingOverlay.style.display = 'none';
				}
				if (typeof window.rebuildWorkflowSelectionLists === 'function') {
					window.rebuildWorkflowSelectionLists();
				}
			}, 100);
		});

		// Function to populate laboratory acceptance parameters table
		function populateLabAcceptanceParametersTable() {
			var parameters = window.labAcceptanceParameters || [];
			var tbody = $('#lab_acceptance_parameters_tbody');
			tbody.empty();

			if (parameters.length === 0) {
				tbody.html('<tr><td colspan="3" class="text-center text-muted">No parameters found for this request</td></tr>');
				updateLabAcceptanceTotal();
				return;
			}

			var totalAmount = 0;
			var parametersData = [];

			$.each(parameters, function(index, param) {
				var paramAmount = parseFloat(param.price || 0);
				totalAmount += paramAmount;

				var row = $('<tr>')
					.attr('data-param-id', param.analysis_id)
					.attr('data-param-price', paramAmount);

				row.append($('<td>').text(param.label || param.name || 'Unknown'));
				row.append($('<td style="text-align: right;">').text(paramAmount.toFixed(2)));
				
				var removeBtn = $('<button type="button" class="btn btn-sm btn-outline-danger" title="Remove parameter">')
					.html('&times;')
					.on('click', function(e) {
						e.preventDefault();
						removeLabAcceptanceParameter(param.analysis_id);
					});

				row.append($('<td style="text-align: center;">').append(removeBtn));
				tbody.append(row);

				parametersData.push({
					analysis_id: param.analysis_id,
					label: param.label || param.name,
					price: paramAmount
				});
			});

			// Store parameters data for form submission
			$('#lab_acceptance_parameters_json').val(JSON.stringify(parametersData));
			updateLabAcceptanceTotal();
		}

		// Function to remove a parameter from the table
		function removeLabAcceptanceParameter(paramId) {
			$('#lab_acceptance_parameters_table tbody tr[data-param-id="' + paramId + '"]').fadeOut(300, function() {
				$(this).remove();
				updateLabAcceptanceTotal();

				// Update the stored parameters JSON
				var remainingRows = $('#lab_acceptance_parameters_table tbody tr');
				var updatedParams = [];
				remainingRows.each(function() {
					updatedParams.push({
						analysis_id: $(this).data('param-id'),
						label: $(this).find('td').eq(0).text(),
						price: parseFloat($(this).data('param-price'))
					});
				});
				$('#lab_acceptance_parameters_json').val(JSON.stringify(updatedParams));
			});
		}

		// Function to update the total amount in the table footer
		function updateLabAcceptanceTotal() {
			var total = 0;
			$('#lab_acceptance_parameters_table tbody tr').each(function() {
				var price = parseFloat($(this).data('param-price')) || 0;
				total += price;
			});

			$('#lab_acceptance_total_amount').text(total.toFixed(2));
			$('#lab_acceptance_amount_usd_hidden').val(total.toFixed(2));
		}

		function syncAssignUserModalSelection() {
			const selected = $("input[name='batch_id[]']:checked");
			const container = $('#assign-user-selected-batches');
			const form = $('#assign-user-form');

			if (!container.length || !form.length) {
				return;
			}

			container.empty();
			form.find('input.js-assignment-batch').remove();

			if (selected.length === 0) {
				container.text('No batch selected.');
				return;
			}

			selected.each(function () {
				const batchId = $(this).val();
				const batchCode = $(this).data('batch-code') || batchId;

				$('<span class="badge badge-light border mr-1 mb-1 d-inline-block"></span>')
					.text(batchCode)
					.appendTo(container);

				$('<input>')
					.attr('type', 'hidden')
					.attr('name', 'batch_ids[]')
					.attr('value', batchId)
					.addClass('js-assignment-batch')
					.appendTo(form);
			});
		}

		function setAssignUserOptions(optionsPayload) {
			const form = $('#assign-user-form');
			if (!form.length) {
				return;
			}

			const select = form.find('select[name="assignee_user_id"]');
			if (!select.length) {
				return;
			}

			const normalizedOptions = Array.isArray(optionsPayload) ? optionsPayload : [];
			let insertedCount = 0;

			select.find('option').not(':first').remove();
			normalizedOptions.forEach(function (entry) {
				if (!entry || !entry.id) {
					return;
				}

				const entryLabel = String(entry.name || '').trim() || ('Personnel ' + String(entry.id).slice(0, 8));

				$('<option>')
					.attr('value', entry.id)
					.text(entryLabel)
					.appendTo(select);
				insertedCount++;
			});

			ensureAssignUserSelectWidget();

			const hasUsers = Math.max(select.find('option').length - 1, 0) > 0;
			$('#assign-user-empty-state').toggleClass('d-none', hasUsers);
			setAssignUserDebugState(insertedCount > 0
				? ('Loaded ' + insertedCount + ' personnel options.')
				: 'No visible personnel options were inserted into the dropdown.');

			if (select.hasClass('select2-hidden-accessible')) {
				select.trigger('change.select2');
			}
		}

		function ensureAssignUserSelectWidget() {
			const form = $('#assign-user-form');
			if (!form.length) {
				return;
			}

			const select = form.find('select[name="assignee_user_id"]');
			if (!select.length) {
				return;
			}

			if (typeof $.fn.select2 !== 'function') {
				return;
			}

			if (select.hasClass('select2-hidden-accessible')) {
				select.select2('destroy');
			}

			select.select2({
				width: '100%',
				placeholder: 'Select user',
				dropdownParent: $('#assign-user-modal'),
				minimumResultsForSearch: 0,
			});
		}

		function setAssignUserDebugState(message, isError = false) {
			const debugState = $('#assign-user-debug-state');
			if (!debugState.length) {
				return;
			}

			const normalizedMessage = String(message || '').trim();
			debugState.toggleClass('d-none', normalizedMessage === '');
			debugState.toggleClass('text-danger', isError === true);
			debugState.toggleClass('text-muted', isError !== true);
			debugState.text(normalizedMessage);
		}

		function refreshAssignUserEmptyState() {
			const select = $('#assign-user-form').find('select[name="assignee_user_id"]');
			if (!select.length) {
				return;
			}

			const hasUsers = Math.max(select.find('option').length - 1, 0) > 0;
			$('#assign-user-empty-state').toggleClass('d-none', hasUsers);
		}

		function ensureAssignUserOptionsLoaded() {
			const form = $('#assign-user-form');
			if (!form.length) {
				return;
			}

			const usersUrl = form.data('users-url');
			if (!usersUrl) {
				setAssignUserOptions([]);
				setAssignUserDebugState('Users endpoint URL is missing on the form.', true);
				return;
			}

			$.ajax({
				url: usersUrl,
				type: 'GET',
				dataType: 'json',
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				},
				success: function (response) {
					const incomingUsers = response && Array.isArray(response.users) ? response.users : [];
					if (incomingUsers.length > 0) {
						setAssignUserOptions(incomingUsers);
						setAssignUserDebugState('Loaded ' + incomingUsers.length + ' personnel records.');
						console.info('Assign-user personnel loaded', {
							count: incomingUsers.length,
						});
						return;
					}

					console.warn('Assign-user personnel endpoint returned no users', response);
					setAssignUserDebugState(
						(response && response.debug_message) ? response.debug_message : 'No personnel returned by the users endpoint.',
						false
					);
					refreshAssignUserEmptyState();
				},
				error: function (xhr, textStatus) {
					console.error('Assign-user personnel endpoint request failed', {
						status: xhr ? xhr.status : null,
						textStatus: textStatus,
						responseText: xhr ? xhr.responseText : null,
					});
					setAssignUserDebugState(
						'Failed to load personnel list (HTTP ' + (xhr && xhr.status ? xhr.status : 'unknown') + '). Check browser console and Laravel logs.',
						true
					);
					refreshAssignUserEmptyState();
				}
			});
		}

		$(document).on('show.bs.modal', '#assign-user-modal', function () {
			ensureAssignUserSelectWidget();
			refreshAssignUserEmptyState();
			ensureAssignUserOptionsLoaded();
			syncAssignUserModalSelection();
		});

		$(document).on('submit', '#assign-user-form', function (event) {
			syncAssignUserModalSelection();
			if ($('#assign-user-form').find('input.js-assignment-batch').length === 0) {
				event.preventDefault();
				alert('Please select at least one batch before assigning a user.');
			}
		});
	
	</script>
	@endpush

	@livewire('sampleworkflow.acceptance-form-wizard')
	@livewire('sampleworkflow.process-enquiry-wizard')

	@if($showPoCaptureModal)
		<div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.45);">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Record PO</h5>
						<button type="button" class="close" wire:click="closePoCaptureModal"><span>&times;</span></button>
					</div>
					<div class="modal-body">
							<div class="alert alert-info py-2 mb-3 small">
							{{ $poRuleMessage }}
							</div>
						<div class="form-group">
							<label>Client PO number</label>
							<input type="text" class="form-control" wire:model.defer="clientPoNumber">
								@error('client_po_number')
									<small class="text-danger d-block mt-1">{{ $message }}</small>
								@enderror
						</div>
						<div class="form-group">
							<label>Advance payment reference</label>
							<input type="text" class="form-control" wire:model.defer="advancePaymentReference">
								@error('advance_payment_reference')
									<small class="text-danger d-block mt-1">{{ $message }}</small>
								@enderror
						</div>
						<div class="form-check">
								<input type="checkbox" class="form-check-input" id="poSkippedCheck" wire:model="poSkipped" @disabled(! $poAllowsSkip)>
							<label class="form-check-label" for="poSkippedCheck">PO not required / skipped</label>
								@error('po_skipped')
									<small class="text-danger d-block mt-1">{{ $message }}</small>
								@enderror
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-outline-secondary" wire:click="closePoCaptureModal">Cancel</button>
						<button type="button" class="btn btn-primary" wire:click="submitPoAndReadyForReception">Mark ready for reception</button>
					</div>
				</div>
			</div>
		</div>
	@endif
	@livewire('sampleworkflow.sample-rejection-wizard')

	<script>
		document.addEventListener('livewire:init', function () {
			Livewire.on('notify', function (payload) {
				const data = payload?.detail ?? payload ?? {};
				const type = data.type ?? 'info';
				const message = data.message ?? data[0]?.message ?? '';
				if (!message) {
					return;
				}
				if (typeof toastr !== 'undefined') {
					toastr[type === 'error' ? 'error' : (type === 'warning' ? 'warning' : 'success')](message);
					return;
				}
				alert(message);
			});

			Livewire.on('acceptance-form-created', function () {
				window.location.reload();
			});
			Livewire.on('sample-rejection-completed', function () {
				window.location.reload();
			});
			Livewire.on('acceptance-form-completed', function (event) {
				const redirectUrl = event?.redirectUrl ?? event?.detail?.redirectUrl;

				if (redirectUrl) {
					window.location.href = redirectUrl;
					return;
				}
				window.location.reload();
			});
		});
	</script>
</div>
