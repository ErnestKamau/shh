<style>
	.modal-header,
	.receive-sample-modal-header,
	.acc-wizard-header,
	.modal-header-modern,
	.vw-modal-header,
	.cf-modal-header {
		background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-hover) 100%) !important;
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
	.cf-modal-header .close {
		color: #ffffff !important;
		text-shadow: none !important;
		opacity: 0.9;
	}

	.modal-header .close:hover,
	.receive-sample-modal-header .close:hover,
	.acc-wizard-close:hover,
	.modal-header-modern .close:hover {
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
