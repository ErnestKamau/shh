{{-- Shared tag-select density — Process Enquiry / Receiving chip language --}}
<style>
	.lab-surface-theme .tag-select-container,
	.ls-admin-page .tag-select-container {
		position: relative;
		cursor: text;
	}

	.lab-surface-theme .tag-select-input,
	.ls-admin-page .tag-select-input {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: 0.35rem;
		min-height: var(--ls-control-h, var(--control-h, 34px));
		padding: 0.35rem 0.45rem;
		background: #fff;
		border: 1px solid var(--ls-color-border, var(--color-border, #e2e8f0));
		border-radius: var(--ls-radius-sm, 6px);
		transition: border-color 0.15s ease, box-shadow 0.15s ease;
	}

	.lab-surface-theme .tag-select-input:hover,
	.ls-admin-page .tag-select-input:hover {
		border-color: var(--ls-color-primary, var(--color-primary));
	}

	.lab-surface-theme .tag-select-input:focus-within,
	.ls-admin-page .tag-select-input:focus-within {
		border-color: var(--ls-color-primary, var(--color-primary));
		box-shadow: 0 0 0 3px var(--ls-color-primary-focus, var(--color-primary-focus));
		outline: none;
	}

	.lab-surface-theme .tag-badge,
	.ls-admin-page .tag-badge {
		display: inline-flex;
		align-items: center;
		gap: 0.25rem;
		padding: 0.15rem 0.35rem 0.15rem 0.45rem;
		background-color: var(--ls-color-primary-soft, var(--color-primary-soft));
		color: var(--ls-color-primary, var(--color-primary));
		border: 1px solid var(--ls-color-primary-border, var(--color-primary-border-soft));
		border-radius: 999px;
		font-size: var(--ls-text-sm, 0.75rem);
		font-weight: 600;
		line-height: 1.25;
		white-space: nowrap;
	}

	.lab-surface-theme .tag-badge i,
	.ls-admin-page .tag-badge i {
		cursor: pointer;
		font-size: 0.95rem;
		opacity: 0.75;
		color: inherit;
	}

	.lab-surface-theme .tag-badge i:hover,
	.ls-admin-page .tag-badge i:hover {
		opacity: 1;
	}

	.lab-surface-theme .tag-input,
	.ls-admin-page .tag-input {
		flex: 1;
		min-width: 100px;
		border: none;
		outline: none;
		padding: 0.15rem;
		font-size: var(--ls-text-base, 0.8125rem);
		background: transparent;
	}

	.lab-surface-theme .tag-dropdown,
	.ls-admin-page .tag-dropdown {
		position: absolute;
		top: calc(100% + 4px);
		left: 0;
		right: 0;
		background: #fff;
		border: 1px solid var(--ls-color-border, var(--color-border));
		border-radius: var(--ls-radius-sm, 6px);
		max-height: 240px;
		overflow-y: auto;
		z-index: 1060;
		box-shadow: 0 12px 28px rgba(15, 23, 42, 0.16);
		margin-top: 0;
	}

	.lab-surface-theme .tag-dropdown-item,
	.ls-admin-page .tag-dropdown-item {
		padding: 0.5rem 0.75rem;
		cursor: pointer;
		border-bottom: 1px solid #f1f5f9;
		font-size: var(--ls-text-base, 0.8125rem);
	}

	.lab-surface-theme .tag-dropdown-item:hover,
	.ls-admin-page .tag-dropdown-item:hover {
		background-color: #f8fafc;
	}

	.lab-surface-theme .tag-dropdown-item:last-child,
	.ls-admin-page .tag-dropdown-item:last-child {
		border-bottom: none;
	}
</style>
