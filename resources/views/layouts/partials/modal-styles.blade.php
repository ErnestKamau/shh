<style>
	/*
	 * Brand mono modal header — primary → primary-hover
	 * linear-gradient(135deg, var(--color-primary), var(--color-primary-hover))
	 */
	:root {
		--ls-modal-header-gradient: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-hover) 100%);
	}

	.modal-header,
	.receive-sample-modal-header,
	.acc-wizard-header,
	.modal-header-modern,
	.vw-modal-header,
	.cf-modal-header {
		background: var(--ls-modal-header-gradient) !important;
		color: #ffffff !important;
		border-bottom-color: rgba(255, 255, 255, 0.15) !important;
	}

	.modal-header .modal-title,
	.modal-header h4,
	.modal-header h5,
	.receive-sample-modal-header .modal-title,
	.receive-sample-modal-header h5,
	#receive-sample-modal-title-text,
	.acc-wizard-header .acc-wizard-title,
	.acc-wizard-header .acc-wizard-eyebrow,
	.modal-title-modern,
	.vw-modal-header .vw-title,
	.cf-modal-header h5 {
		color: #ffffff !important;
	}

	.modal-header .text-muted,
	.modal-header small,
	.modal-header .small,
	.receive-sample-modal-header .text-muted,
	.receive-sample-modal-header p.small,
	.acc-wizard-header .text-muted,
	.vw-modal-header .vw-subtitle,
	.cf-modal-header .text-muted {
		color: rgba(255, 255, 255, 0.82) !important;
	}

	.modal-header .close,
	.modal-header button.close,
	.receive-sample-modal-header .close,
	.acc-wizard-close,
	.modal-header-modern .close,
	.vw-modal-header .close,
	.cf-modal-header .close,
	.ls-modal-close {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 2rem;
		height: 2rem;
		padding: 0;
		margin: 0;
		border: none;
		border-radius: var(--radius-sm, 6px);
		background: rgba(255, 255, 255, 0.15) !important;
		color: #ffffff !important;
		text-shadow: none !important;
		opacity: 0.95;
		cursor: pointer;
		line-height: 1;
		font-size: 1.15rem;
		transition: background 0.15s ease, opacity 0.15s ease;
	}

	.modal-header .close:hover,
	.receive-sample-modal-header .close:hover,
	.acc-wizard-close:hover,
	.modal-header-modern .close:hover,
	.ls-modal-close:hover {
		background: rgba(255, 255, 255, 0.25) !important;
		opacity: 1;
		color: #ffffff !important;
	}

	/* Bootstrap btn-close on gradient headers — replace broken SVG dash with white X */
	.modal-header .btn-close,
	.receive-sample-modal-header .btn-close,
	.acc-wizard-header .btn-close,
	.modal-header-modern .btn-close,
	.vw-modal-header .btn-close,
	.cf-modal-header .btn-close {
		display: inline-flex !important;
		align-items: center;
		justify-content: center;
		width: 2rem;
		height: 2rem;
		padding: 0;
		margin: 0;
		border: none;
		border-radius: var(--radius-sm, 6px);
		background-color: rgba(255, 255, 255, 0.15) !important;
		background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23ffffff'%3E%3Cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707A1 1 0 01.293.293z'/%3E%3C/svg%3E") !important;
		background-size: 0.7em !important;
		background-position: center !important;
		background-repeat: no-repeat !important;
		opacity: 0.95;
		box-shadow: none;
		filter: none !important;
	}

	.modal-header .btn-close:hover,
	.receive-sample-modal-header .btn-close:hover,
	.acc-wizard-header .btn-close:hover {
		background-color: rgba(255, 255, 255, 0.25) !important;
		opacity: 1;
	}

	.modal-header .mdi.text-primary,
	.modal-header .mdi.text-warning,
	.receive-sample-modal-header .mdi.text-primary,
	.receive-sample-modal-header .mdi.text-warning,
	.acc-wizard-header .mdi {
		color: rgba(255, 255, 255, 0.95) !important;
	}

	.acc-wizard-close {
		background: rgba(255, 255, 255, 0.15) !important;
		color: #fff !important;
	}

	.acc-wizard-close:hover {
		background: rgba(255, 255, 255, 0.25) !important;
	}

	.acc-wizard-header {
		padding: 0.85rem 1rem;
	}

	.acc-wizard-step.is-done {
		color: var(--color-muted);
	}

	.acc-wizard-step.is-done .acc-wizard-step-index {
		background: var(--color-primary) !important;
		border-color: var(--color-primary) !important;
		color: #fff !important;
	}

    .acc-wizard-step.is-active {
        color: #111827 !important;
        border-bottom-color: var(--color-primary) !important;
    }

    .acc-wizard-step.is-active .acc-wizard-step-label {
        color: #111827 !important;
    }

	.acc-wizard-step.is-active .acc-wizard-step-index {
		background: var(--color-primary) !important;
		color: #fff !important;
	}

	.acc-wizard-section-title {
		border-left-color: var(--color-primary) !important;
	}

	.receive-sample-modal-content,
	#request-additional-info-modal .modal-content,
	#send-for-analyst-review-modal .modal-content,
	#move-to-intray-modal .modal-content {
		border-radius: 14px;
		overflow: hidden;
		border: none;
	}

	#receive-sample-modal .modal-dialog {
		max-width: min(1140px, calc(100vw - 2rem));
	}

	#receive-sample-modal.receive-sample-modal--compact .modal-dialog {
		max-width: min(420px, calc(100vw - 2rem));
	}

	#receive-sample-modal.receive-sample-modal--compact .modal-body {
		padding: 0 1.25rem 0.5rem;
	}

	#receive-sample-modal .modal-body {
		padding: 0 1.5rem 1.25rem;
	}

	.receive-sample-modal-footer {
		border-top: 1px solid var(--color-border);
		padding: 1rem 1.5rem;
	}

	.receive-checkin-card {
		border-left: 4px solid var(--color-primary);
	}

	.receive-checkin-card__header {
		background: linear-gradient(135deg, var(--color-primary-soft-light) 0%, #ffffff 100%);
	}

	.vw-modal-header .vw-icon-wrap {
		background: rgba(255, 255, 255, 0.15) !important;
	}

	.vw-modal-header .vw-icon-wrap i {
		color: #fff !important;
	}

	.btn-primary-modern {
		background-color: var(--color-primary) !important;
		border-color: var(--color-primary) !important;
		color: #fff !important;
	}

	.btn-primary-modern:hover {
		background-color: var(--color-primary-hover) !important;
		border-color: var(--color-primary-hover) !important;
	}

	.modal-body,
	.modal-body p,
	.modal-body label,
	.modal-body .form-group label,
	.modal-body .col-form-label,
	.acc-wizard-body,
	.acc-wizard-root .acc-wizard-body,
	.acc-wizard-root .acc-wizard-panel,
	.acc-wizard-root .acc-wizard-section-body,
	.receive-sample-modal-body {
		color: var(--ls-color-ink, #1e293b);
	}

	.modal-body .text-muted,
	.acc-wizard-root .text-muted,
	.acc-wizard-root .acc-wizard-muted,
	.acc-wizard-root .acc-wizard-help {
		color: var(--ls-color-muted, #64748b) !important;
	}

	.modal-body h1,
	.modal-body h2,
	.modal-body h3,
	.modal-body h4,
	.modal-body h5,
	.modal-body h6,
	.acc-wizard-root h1,
	.acc-wizard-root h2,
	.acc-wizard-root h3,
	.acc-wizard-root h4,
	.acc-wizard-root h5,
	.acc-wizard-root h6,
	.acc-wizard-root .acc-wizard-section-title,
	.acc-wizard-root .acc-wizard-field-label {
		color: var(--ls-color-ink, #1e293b);
	}
</style>
