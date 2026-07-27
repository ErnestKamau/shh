{{-- Shared lab / RFT surface styling for submission-form admin pages --}}
@include('layouts.lab.partials.lab-panel-theme-styles')
@include('layouts.rft.partials.rft-theme-styles')
@include('layouts.partials.modal-styles')

<style>
	.lab-panel-theme.sf-admin-page {
		font-size: var(--rft-font-base, 0.8125rem);
		color: var(--workflow-text-main, #1e293b);
	}

	.lab-panel-theme.sf-admin-page .sf-page-title {
		margin: 0;
		font-size: 1.05rem;
		font-weight: var(--font-semibold, 600);
		color: var(--workflow-text-main, #1e293b);
		display: flex;
		align-items: center;
		gap: 8px;
	}

	.lab-panel-theme.sf-admin-page .sf-page-title .mdi {
		color: var(--workflow-accent);
		font-size: 1.1rem;
	}

	.lab-panel-theme.sf-admin-page .sf-page-subtitle {
		margin: 0.25rem 0 0;
		color: var(--workflow-muted, #64748b);
		font-size: 0.8125rem;
	}

	.lab-panel-theme.sf-admin-page .sf-meta-chip {
		display: inline-flex;
		align-items: center;
		padding: 2px 8px;
		border-radius: 20px;
		font-size: 0.6875rem;
		font-weight: 600;
		background: #f1f5f9;
		color: #475569;
		border: 1px solid #e2e8f0;
	}

	.lab-panel-theme.sf-admin-page .sf-meta-chip.is-success {
		background: #f0fdf4;
		color: #14532d;
		border-color: #bbf7d0;
	}

	.lab-panel-theme.sf-admin-page .sf-meta-chip.is-warning {
		background: #fffbeb;
		color: #92400e;
		border-color: #fde68a;
	}

	.lab-panel-theme.sf-admin-page .sf-meta-chip.is-danger {
		background: #fef2f2;
		color: #991b1b;
		border-color: #fecaca;
	}

	.lab-panel-theme.sf-admin-page .form-group label,
	.lab-panel-theme.sf-admin-page label.required {
		font-size: 0.75rem;
		font-weight: 600;
		color: var(--workflow-text-main, #1e293b);
		margin-bottom: 0.35rem;
	}

	.lab-panel-theme.sf-admin-page .form-control,
	.lab-panel-theme.sf-admin-page .custom-select {
		min-height: var(--rft-control-h, 34px);
		font-size: 0.8125rem;
		border-color: var(--workflow-border, #e2e8f0);
		border-radius: 6px;
	}

	.lab-panel-theme.sf-admin-page .form-text,
	.lab-panel-theme.sf-admin-page small.form-text {
		color: var(--workflow-muted, #64748b);
		font-size: 0.75rem;
	}

	.lab-panel-theme.sf-admin-page .checkbox-option-card {
		background: #f8fafc !important;
		border: 1px solid var(--workflow-border, #e2e8f0) !important;
		border-radius: 10px !important;
	}

	.lab-panel-theme.sf-admin-page .checkbox-option-card .form-check-input,
	.lab-panel-theme.sf-admin-page .checkbox-option-card input[type="checkbox"] {
		position: static !important;
		margin: 0 !important;
		float: none !important;
		flex-shrink: 0;
		width: 18px;
		height: 18px;
	}

	.lab-panel-theme.sf-admin-page .checkbox-option-card > div:first-child {
		display: flex;
		align-items: flex-start;
		padding-top: 2px;
	}

	.lab-panel-theme.sf-admin-page .sf-section-heading {
		font-size: 0.7rem;
		font-weight: 700;
		letter-spacing: 0.04em;
		text-transform: uppercase;
		color: var(--workflow-accent);
		margin: 0.75rem 0 0.35rem;
	}

	.lab-panel-theme.sf-admin-page .sf-alert-soft {
		border: 1px solid var(--workflow-border, #e2e8f0);
		border-radius: 10px;
		background: #f8fafc;
		color: var(--workflow-text-main, #1e293b);
		padding: 0.65rem 0.85rem;
		font-size: 0.8125rem;
	}

	.lab-panel-theme.sf-admin-page .sf-alert-soft.is-info {
		background: var(--workflow-accent-soft, #eff6ff);
		border-color: var(--workflow-accent-border, #bfdbfe);
	}

	.lab-panel-theme.sf-admin-page .sf-alert-soft.is-warning {
		background: #fffbeb;
		border-color: #fde68a;
	}

	.lab-panel-theme.sf-admin-page .sf-structure-section {
		border: 1px solid var(--workflow-border, #e2e8f0);
		border-radius: 10px;
		padding: 0.85rem 1rem;
		margin-bottom: 0.75rem;
		background: #fff;
		border-left: 3px solid var(--workflow-accent);
	}

	.lab-panel-theme.sf-admin-page .sf-holder-item {
		background: #f8fafc;
		border: 1px solid var(--workflow-border, #e2e8f0);
		border-radius: 8px;
		padding: 0.65rem 0.75rem;
		margin-bottom: 0.5rem;
	}

	.lab-panel-theme.sf-admin-page .sf-element-row {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 0.35rem 0.25rem;
		border-radius: 4px;
		gap: 8px;
	}

	.lab-panel-theme.sf-admin-page .sf-element-row:hover {
		background: #f1f5f9;
	}

	.lab-panel-theme.sf-admin-page .sf-stat-inline {
		display: flex;
		flex-wrap: wrap;
		gap: 0.75rem 1.25rem;
	}

	.lab-panel-theme.sf-admin-page .sf-stat-inline .sf-stat {
		min-width: 4.5rem;
	}

	.lab-panel-theme.sf-admin-page .sf-stat-inline .sf-stat-value {
		font-size: 1.15rem;
		font-weight: 700;
		color: var(--workflow-text-main, #1e293b);
		line-height: 1.2;
	}

	.lab-panel-theme.sf-admin-page .sf-stat-inline .sf-stat-label {
		font-size: 0.6875rem;
		text-transform: uppercase;
		letter-spacing: 0.04em;
		color: var(--workflow-muted, #64748b);
		font-weight: 600;
	}

	.lab-panel-theme.sf-admin-page .sf-action-stack {
		display: grid;
		gap: 0.5rem;
	}

	.lab-panel-theme.sf-admin-page .sf-action-stack .btn {
		width: 100%;
		justify-content: center;
	}

	.lab-panel-theme.sf-admin-page .element-types {
		display: grid;
		gap: 6px;
	}

	.lab-panel-theme.sf-admin-page .element-type {
		padding: 7px 10px;
		border: 1px solid var(--workflow-border, #e2e8f0);
		border-radius: 8px;
		cursor: pointer;
		transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
		font-size: 0.8125rem;
		background: #fff;
		color: var(--workflow-text-main, #1e293b);
	}

	.lab-panel-theme.sf-admin-page .element-type:hover {
		background: #f8fafc;
		border-color: var(--workflow-accent);
		box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
	}

	.lab-panel-theme.sf-admin-page .element-type i {
		margin-right: 8px;
		color: var(--workflow-muted, #64748b);
	}

	.lab-panel-theme.sf-admin-page .sf-builder-group-label {
		font-size: 0.7rem;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.04em;
		color: var(--workflow-muted, #64748b);
		padding: 8px 2px 4px;
		margin: 0;
	}

	.lab-panel-theme.sf-admin-page .section-item {
		border: 1px solid var(--workflow-border, #e2e8f0);
		border-radius: 10px;
		margin-bottom: 12px;
		background: #fff;
		overflow: hidden;
		box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
	}

	.lab-panel-theme.sf-admin-page .section-header {
		padding: 10px 14px;
		background: #f8fafc;
		border-bottom: 1px solid var(--workflow-border, #e2e8f0);
	}

	.lab-panel-theme.sf-admin-page .holder-item {
		border: 1px solid var(--workflow-border, #e2e8f0) !important;
		border-radius: 8px;
		margin: 8px 0;
		background: #fafbfc;
		cursor: pointer;
		transition: border-color 0.15s ease, background 0.15s ease;
	}

	.lab-panel-theme.sf-admin-page .holder-item:hover {
		background: #f8fafc;
	}

	.lab-panel-theme.sf-admin-page .holder-item.active {
		border-color: var(--workflow-accent) !important;
		background: var(--workflow-accent-soft, #eff6ff);
		box-shadow: 0 0 0 1px var(--workflow-accent-soft, #eff6ff);
	}

	.lab-panel-theme.sf-admin-page .holder-header {
		padding: 8px 12px;
		background: #f1f5f9;
		border-bottom: 1px solid var(--workflow-border, #e2e8f0);
	}

	.lab-panel-theme.sf-admin-page .holder-item.active .holder-header {
		background: var(--workflow-accent-soft, #eff6ff);
	}

	.lab-panel-theme.sf-admin-page .element-item {
		padding: 6px 12px;
		border-bottom: 1px solid #f1f5f9;
		min-width: 145px;
	}

	.lab-panel-theme.sf-admin-page .element-item:last-child {
		border-bottom: none;
	}

	.lab-panel-theme.sf-admin-page .element-item:hover {
		background-color: #f1f5f9;
	}

	.lab-panel-theme.sf-admin-page .form-section {
		border-left: 3px solid var(--workflow-accent);
		padding-left: 1rem;
		margin-bottom: 1rem;
	}

	.lab-panel-theme.sf-admin-page .element-holder {
		background: #f8fafc;
		border: 1px solid var(--workflow-border, #e2e8f0);
		border-radius: 10px;
		padding: 0.85rem 1rem;
		margin-bottom: 0.75rem;
	}

	.lab-panel-theme.sf-admin-page .sf-form-actions {
		display: flex;
		flex-wrap: wrap;
		justify-content: space-between;
		align-items: center;
		gap: 0.75rem;
		margin-top: 1rem;
		padding-top: 1rem;
		border-top: 1px solid var(--workflow-border, #e2e8f0);
	}

	.lab-panel-theme.sf-admin-page #form-data-preview {
		font-size: 0.75rem;
		max-height: 280px;
		overflow-y: auto;
		background: #f8fafc;
		border: 1px solid var(--workflow-border, #e2e8f0);
		border-radius: 8px;
		padding: 0.75rem;
	}

	/* Builder modals — match receive-sample modal chrome */
	#section-modal .modal-content,
	#holder-modal .modal-content,
	#element-modal .modal-content {
		border-radius: 14px;
		overflow: hidden;
		border: none;
	}

	#section-modal .modal-body,
	#holder-modal .modal-body,
	#element-modal .modal-body {
		padding: 0 1.25rem 1rem;
	}

	#section-modal .modal-footer,
	#holder-modal .modal-footer,
	#element-modal .modal-footer {
		padding: 0.75rem 1.25rem 1.15rem;
		gap: 0.5rem;
	}

	.required::after {
		content: " *";
		color: #dc2626;
	}
</style>
