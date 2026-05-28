<style>
	.log-entry-editor .lec-column-modal-backdrop {
		position: fixed;
		inset: 0;
		z-index: 1055;
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 1.25rem;
		background: rgba(15, 23, 42, 0.52);
		backdrop-filter: blur(4px);
		overflow-y: auto;
	}

	.log-entry-editor .lec-column-modal-dialog {
		width: 100%;
		max-width: 960px;
		margin: auto;
	}

	.log-entry-editor .lec-column-modal-content {
		border: none;
		border-radius: 1rem;
		box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35);
		overflow: visible;
		display: flex;
		flex-direction: column;
		max-height: calc(100vh - 2.5rem);
	}

	.log-entry-editor .lec-column-modal-header {
		display: flex;
		align-items: flex-start;
		justify-content: space-between;
		gap: 1rem;
		padding: 1.35rem 1.5rem;
		background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
		border-bottom: 1px solid #e2e8f0;
		border-radius: 1rem 1rem 0 0;
		flex-shrink: 0;
	}

	.log-entry-editor .lec-column-modal-header__icon {
		width: 2.75rem;
		height: 2.75rem;
		border-radius: 0.75rem;
		display: flex;
		align-items: center;
		justify-content: center;
		background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
		color: #fff;
		font-size: 1.35rem;
		flex-shrink: 0;
		box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
	}

	.log-entry-editor .lec-column-modal-title {
		font-size: 1.15rem;
		font-weight: 700;
		color: #0f172a;
		margin: 0;
		line-height: 1.3;
	}

	.log-entry-editor .lec-column-modal-subtitle {
		font-size: 0.8125rem;
		color: #64748b;
		margin: 0.2rem 0 0;
	}

	.log-entry-editor .lec-column-modal-close {
		width: 2rem;
		height: 2rem;
		border: none;
		border-radius: 0.5rem;
		background: #f1f5f9;
		color: #64748b;
		font-size: 1.25rem;
		line-height: 1;
		cursor: pointer;
		transition: background 0.15s ease, color 0.15s ease;
		flex-shrink: 0;
	}

	.log-entry-editor .lec-column-modal-close:hover {
		background: #e2e8f0;
		color: #0f172a;
	}

	.log-entry-editor .lec-column-modal-body {
		padding: 1.35rem 1.5rem;
		background: #fff;
		overflow: visible;
		flex: 1;
		min-height: 0;
	}

	.log-entry-editor .lec-column-modal-body::-webkit-scrollbar {
		width: 6px;
	}

	.log-entry-editor .lec-column-modal-body::-webkit-scrollbar-thumb {
		background: #cbd5e1;
		border-radius: 3px;
	}

	.log-entry-editor .lec-column-modal-footer {
		display: flex;
		align-items: center;
		justify-content: flex-end;
		gap: 0.65rem;
		padding: 1rem 1.5rem;
		background: #f8fafc;
		border-top: 1px solid #e2e8f0;
		border-radius: 0 0 1rem 1rem;
		flex-shrink: 0;
	}

	.log-entry-editor .lec-form-section {
		margin-bottom: 1.35rem;
	}

	.log-entry-editor .lec-form-section:last-child {
		margin-bottom: 0;
	}

	.log-entry-editor .lec-section-title {
		font-size: 0.6875rem;
		font-weight: 700;
		letter-spacing: 0.08em;
		text-transform: uppercase;
		color: #64748b;
		margin: 0 0 1rem;
		padding-bottom: 0.5rem;
		border-bottom: 1px solid #f1f5f9;
	}

	.log-entry-editor .lec-form-label {
		display: block;
		font-size: 0.8125rem;
		font-weight: 600;
		color: #334155;
		margin-bottom: 0.4rem;
	}

	.log-entry-editor .lec-form-label .text-danger {
		color: #ef4444 !important;
	}

	.log-entry-editor .lec-column-modal .form-control {
		border-radius: 0.5rem;
		border: 1px solid #e2e8f0;
		background: #fff;
		font-size: 0.9rem;
		padding: 0.5rem 0.75rem;
		min-height: 2.5rem;
		transition: border-color 0.15s ease, box-shadow 0.15s ease;
	}

	.log-entry-editor .lec-column-modal .form-control:focus {
		border-color: #6366f1;
		box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
	}

	.log-entry-editor .lec-column-modal .form-control[readonly] {
		background: #f8fafc;
		color: #64748b;
	}

	.log-entry-editor .lec-column-modal textarea.form-control {
		min-height: auto;
	}

	.log-entry-editor .lec-expression-input {
		font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
		font-size: 0.8125rem;
		background: #f8fafc;
	}

	.log-entry-editor .lec-field-hint {
		display: block;
		font-size: 0.75rem;
		color: #94a3b8;
		margin-top: 0.35rem;
		line-height: 1.4;
	}

	.log-entry-editor .lec-dataset-panel {
		background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
		border: 1px solid #e2e8f0;
		border-radius: 0.75rem;
		overflow: visible;
	}

	.log-entry-editor .lec-dataset-panel__header {
		display: flex;
		align-items: center;
		gap: 0.85rem;
		padding: 1rem 1.15rem;
		background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
		border-bottom: 1px solid #c7d2fe;
	}

	.log-entry-editor .lec-dataset-panel__icon {
		width: 2.25rem;
		height: 2.25rem;
		border-radius: 0.5rem;
		background: #fff;
		color: #4f46e5;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 1.15rem;
		box-shadow: 0 1px 3px rgba(79, 70, 229, 0.12);
	}

	.log-entry-editor .lec-dataset-panel__title {
		font-size: 0.9375rem;
		font-weight: 700;
		color: #312e81;
		margin: 0;
	}

	.log-entry-editor .lec-dataset-panel__desc {
		font-size: 0.75rem;
		color: #6366f1;
		margin: 0.1rem 0 0;
		opacity: 0.85;
	}

	.log-entry-editor .lec-dataset-panel__body {
		padding: 1.15rem;
		overflow: visible;
	}

	.log-entry-editor .lec-dataset-panel__body .form-group {
		margin-bottom: 1rem;
	}

	.log-entry-editor .lec-dataset-panel__body .form-group:last-child {
		margin-bottom: 0;
	}

	.log-entry-editor .lec-type-placeholder {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		text-align: center;
		padding: 2.5rem 1.5rem;
		border: 1px dashed #e2e8f0;
		border-radius: 0.75rem;
		background: #fafbfc;
		color: #94a3b8;
		min-height: 200px;
	}

	.log-entry-editor .lec-type-placeholder i {
		font-size: 2.5rem;
		margin-bottom: 0.75rem;
		opacity: 0.5;
	}

	.log-entry-editor .lec-checkbox-card {
		display: flex;
		align-items: center;
		gap: 0.65rem;
		padding: 0.75rem 1rem;
		border: 1px solid #e2e8f0;
		border-radius: 0.5rem;
		background: #fafbfc;
		cursor: pointer;
		transition: border-color 0.15s ease, background 0.15s ease;
		margin: 0;
	}

	.log-entry-editor .lec-checkbox-card:hover {
		border-color: #c7d2fe;
		background: #f8fafc;
	}

	.log-entry-editor .lec-checkbox-card input {
		width: 1.1rem;
		height: 1.1rem;
		margin: 0;
		accent-color: #4f46e5;
	}

	.log-entry-editor .lec-checkbox-card span {
		font-size: 0.875rem;
		font-weight: 500;
		color: #334155;
	}

	.log-entry-editor .lec-btn-ghost {
		border-radius: 0.5rem;
		padding: 0.5rem 1.15rem;
		font-size: 0.875rem;
		font-weight: 600;
		border: 1px solid #e2e8f0;
		background: #fff;
		color: #475569;
		transition: all 0.15s ease;
	}

	.log-entry-editor .lec-btn-ghost:hover {
		background: #f8fafc;
		border-color: #cbd5e1;
		color: #0f172a;
	}

	.log-entry-editor .lec-btn-primary {
		border-radius: 0.5rem;
		padding: 0.5rem 1.35rem;
		font-size: 0.875rem;
		font-weight: 600;
		border: none;
		background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
		color: #fff;
		box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
		transition: transform 0.15s ease, box-shadow 0.15s ease;
	}

	.log-entry-editor .lec-btn-primary:hover {
		transform: translateY(-1px);
		box-shadow: 0 6px 18px rgba(79, 70, 229, 0.4);
		color: #fff;
	}

	.log-entry-editor .lec-btn-validate {
		border-radius: 0.5rem;
		font-size: 0.8125rem;
		font-weight: 600;
		border: 1px solid #e2e8f0;
		color: #475569;
		background: #fff;
	}

	.log-entry-editor .lec-btn-validate:hover {
		background: #f8fafc;
		border-color: #cbd5e1;
	}

	.log-entry-editor .lec-validation-msg {
		font-size: 0.8125rem;
		margin-top: 0.5rem;
	}

	.log-entry-editor .lec-column-modal .tag-select-input {
		border: 1px solid #e2e8f0;
		border-radius: 0.5rem;
		min-height: 2.5rem;
		padding: 0.35rem 0.65rem;
		background: #fff;
		transition: border-color 0.15s ease, box-shadow 0.15s ease;
	}

	.log-entry-editor .lec-column-modal .tag-select-input:focus-within {
		border-color: #6366f1;
		box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
	}

	.log-entry-editor .lec-column-modal .tag-badge {
		background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
		border-radius: 0.375rem;
		font-size: 0.8125rem;
		padding: 0.3rem 0.65rem;
	}

	.log-entry-editor .lec-column-modal .tag-select-container {
		position: relative;
	}

	.log-entry-editor .lec-column-modal .form-group:has(.tag-dropdown),
	.log-entry-editor .lec-column-modal .lec-schema-tag-field:has(.tag-dropdown) {
		position: relative;
		z-index: 40;
	}

	.log-entry-editor .lec-column-modal .tag-dropdown {
		position: absolute;
		top: 100%;
		left: 0;
		right: 0;
		border: 1px solid #e2e8f0;
		border-radius: 0.5rem;
		box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.15);
		margin-top: 0.25rem;
		max-height: 220px;
		overflow-y: auto;
		background: #fff;
		z-index: 1070;
	}

	.log-entry-editor .lec-column-modal .tag-dropdown-item {
		font-size: 0.875rem;
		padding: 0.55rem 0.85rem;
	}

	.log-entry-editor .lec-column-modal .tag-dropdown-item:hover {
		background: #eef2ff;
		color: #4338ca;
	}

	.log-entry-editor .lec-column-modal .form-group label:not(.lec-form-label) {
		font-size: 0.8125rem;
		font-weight: 600;
		color: #334155;
	}

	.log-entry-editor .lec-mandatory-modal__choices {
		margin-top: 0;
		padding-top: 1.25rem;
	}

	.log-entry-editor .lec-choices-panel {
		border: 1px solid #e2e8f0;
		border-radius: 0.75rem;
		background: linear-gradient(180deg, #fafbff 0%, #fff 100%);
		overflow: hidden;
		box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
	}

	.log-entry-editor .lec-choices-panel__header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 1rem;
		padding: 1rem 1.25rem;
		border-bottom: 1px solid #e2e8f0;
		background: #fff;
	}

	.log-entry-editor .lec-choices-panel__title-wrap {
		display: flex;
		align-items: flex-start;
		gap: 0.85rem;
		min-width: 0;
	}

	.log-entry-editor .lec-choices-panel__icon {
		width: 2.5rem;
		height: 2.5rem;
		border-radius: 0.625rem;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-shrink: 0;
		background: linear-gradient(135deg, #4f46e5 0%, #818cf8 100%);
		color: #fff;
		font-size: 1.25rem;
	}

	.log-entry-editor .lec-choices-panel__title {
		font-size: 0.9375rem;
		font-weight: 700;
		color: #0f172a;
		letter-spacing: 0.01em;
	}

	.log-entry-editor .lec-choices-panel__desc {
		font-size: 0.8125rem;
		color: #64748b;
		margin-top: 0.15rem;
	}

	.log-entry-editor .lec-choices-panel__count {
		font-size: 0.75rem;
		font-weight: 600;
		color: #4338ca;
		background: #eef2ff;
		border: 1px solid #c7d2fe;
		border-radius: 999px;
		padding: 0.35rem 0.75rem;
		white-space: nowrap;
	}

	.log-entry-editor .lec-choices-add {
		display: flex;
		align-items: stretch;
		gap: 0.65rem;
		padding: 1rem 1.25rem 0.75rem;
	}

	.log-entry-editor .lec-choices-add__input-wrap {
		display: flex;
		align-items: center;
		flex: 1;
		min-width: 0;
		border: 1px solid #cbd5e1;
		border-radius: 0.5rem;
		background: #fff;
		overflow: hidden;
		transition: border-color 0.15s ease, box-shadow 0.15s ease;
	}

	.log-entry-editor .lec-choices-add__input-wrap:focus-within {
		border-color: #6366f1;
		box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
	}

	.log-entry-editor .lec-choices-add__prefix {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		flex-shrink: 0;
		padding-left: 0.75rem;
		padding-right: 0.35rem;
		color: #94a3b8;
		font-size: 1.125rem;
		line-height: 1;
	}

	.log-entry-editor .lec-column-modal .lec-choices-add__input.form-control {
		flex: 1;
		min-width: 0;
		border: none;
		border-radius: 0;
		box-shadow: none;
		background: transparent;
		padding: 0.5rem 0.75rem 0.5rem 0.25rem;
		min-height: 2.5rem;
	}

	.log-entry-editor .lec-column-modal .lec-choices-add__input.form-control:focus {
		border: none;
		box-shadow: none;
		background: transparent;
	}

	.log-entry-editor .lec-choices-add__btn {
		display: inline-flex;
		align-items: center;
		gap: 0.35rem;
		padding: 0 1.15rem;
		border: none;
		border-radius: 0.5rem;
		font-size: 0.875rem;
		font-weight: 600;
		color: #fff;
		background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
		box-shadow: 0 2px 8px rgba(79, 70, 229, 0.3);
		transition: transform 0.15s ease, box-shadow 0.15s ease;
		white-space: nowrap;
	}

	.log-entry-editor .lec-choices-add__btn:hover {
		transform: translateY(-1px);
		box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);
		color: #fff;
	}

	.log-entry-editor .lec-choices-error {
		padding: 0 1.25rem 0.5rem;
		margin-top: -0.25rem;
	}

	.log-entry-editor .lec-choices-list {
		list-style: none;
		margin: 0;
		padding: 0.5rem 1.25rem 1.25rem;
		display: flex;
		flex-direction: column;
		gap: 0.5rem;
	}

	.log-entry-editor .lec-choices-list__item {
		display: flex;
		align-items: center;
		gap: 0.65rem;
		padding: 0.5rem 0.65rem 0.5rem 0.5rem;
		background: #fff;
		border: 1px solid #e2e8f0;
		border-radius: 0.5rem;
		transition: border-color 0.15s ease, box-shadow 0.15s ease;
	}

	.log-entry-editor .lec-choices-list__item:focus-within {
		border-color: #a5b4fc;
		box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
	}

	.log-entry-editor .lec-choices-list__order {
		width: 1.75rem;
		height: 1.75rem;
		border-radius: 0.375rem;
		background: #f1f5f9;
		color: #475569;
		font-size: 0.75rem;
		font-weight: 700;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		flex-shrink: 0;
	}

	.log-entry-editor .lec-choices-list__input {
		flex: 1;
		min-width: 0;
		border: none;
		background: transparent;
		padding: 0.35rem 0.25rem;
		box-shadow: none;
		font-size: 0.875rem;
	}

	.log-entry-editor .lec-choices-list__input:focus {
		box-shadow: none;
		background: transparent;
	}

	.log-entry-editor .lec-choices-list__remove {
		width: 2rem;
		height: 2rem;
		border: none;
		border-radius: 0.375rem;
		background: transparent;
		color: #94a3b8;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		flex-shrink: 0;
		transition: background 0.15s ease, color 0.15s ease;
	}

	.log-entry-editor .lec-choices-list__remove:hover {
		background: #fef2f2;
		color: #dc2626;
	}

	.log-entry-editor .lec-choices-empty {
		margin: 0 1.25rem 1.25rem;
		padding: 2rem 1rem;
		text-align: center;
		border: 1px dashed #cbd5e1;
		border-radius: 0.5rem;
		background: #f8fafc;
		color: #64748b;
	}

	.log-entry-editor .lec-choices-empty i {
		font-size: 2rem;
		color: #cbd5e1;
		display: block;
		margin-bottom: 0.5rem;
	}

	.log-entry-editor .lec-choices-empty p {
		font-size: 0.875rem;
	}

	.log-entry-editor .lec-mandatory-modal__top .lec-form-section {
		margin-bottom: 0;
	}

	.log-entry-editor .lec-mandatory-modal__full {
		margin-top: 0.25rem;
		padding-top: 1.25rem;
		border-top: 1px solid #e2e8f0;
	}

	.log-entry-editor .lec-mandatory-modal__dataset {
		margin-top: 0;
	}

	.log-entry-editor .lec-mandatory-modal__dataset .lec-dataset-panel__body .row {
		margin-left: -0.5rem;
		margin-right: -0.5rem;
	}

	.log-entry-editor .lec-preset-lookup-panel .lec-dataset-panel__icon {
		background: linear-gradient(135deg, #0ea5e9 0%, #38bdf8 100%);
	}

	.log-entry-editor .lec-preset-lookup-preview {
		display: flex;
		align-items: center;
		gap: 0.65rem;
		padding: 0.75rem 1rem;
		border-radius: 0.5rem;
		background: #f0f9ff;
		border: 1px dashed #7dd3fc;
		color: #0369a1;
		font-size: 0.8125rem;
		line-height: 1.45;
	}

	.log-entry-editor .lec-preset-lookup-preview i {
		font-size: 1.25rem;
		flex-shrink: 0;
	}

	.log-entry-editor .lec-column-modal-header__icon--danger {
		background: linear-gradient(135deg, #dc2626 0%, #f87171 100%);
	}

	.log-entry-editor .lec-btn-primary--danger {
		background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
		box-shadow: 0 4px 14px rgba(220, 38, 38, 0.3);
	}

	.log-entry-editor .lec-btn-primary--danger:hover {
		box-shadow: 0 6px 18px rgba(220, 38, 38, 0.38);
	}

	.log-entry-editor .lec-btn-primary i {
		margin-right: 0.25rem;
		font-size: 1rem;
		vertical-align: -2px;
	}

	@media (max-width: 991.98px) {
		.log-entry-editor .lec-column-modal-backdrop {
			padding: 0.75rem;
			align-items: flex-start;
		}

		.log-entry-editor .lec-column-modal-content {
			max-height: none;
		}
	}
</style>
