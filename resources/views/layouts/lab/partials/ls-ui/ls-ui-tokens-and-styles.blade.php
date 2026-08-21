{{--
	LS complementary UI kit — opt-in styles only.
	Include from gallery (or any page that @includes ls-* partials).
	Does not alter global Select2 / workflow markup unless wrapped in .ls-* shells.
--}}
<style>
	.ls-ui-kit {
		--ls-blue-soft: var(--ls-blue-soft, #eff6ff);
		--ls-blue-soft-border: var(--ls-blue-soft-border, #dbeafe);
		--ls-blue-soft-ring: var(--ls-blue-soft-ring, #bfdbfe);
		--ls-blue-focus: var(--ls-blue-focus, #93c5fd);
		--ls-ink: var(--workflow-secondary, #1e293b);
		--ls-ink-fg: var(--workflow-secondary-fg, #f8fafc);
		--ls-muted: var(--workflow-muted, #64748b);
		--ls-border: var(--workflow-border, #e2e8f0);
		--ls-surface: var(--workflow-surface, #ffffff);
		--ls-bg: var(--workflow-bg, #f8fafc);
		--ls-accent: var(--workflow-accent, #8b1e2d);
		--ls-radius: 8px;
		--ls-radius-lg: 10px;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		color: var(--ls-ink);
	}

	/* —— Fields —— */
	.ls-field {
		display: flex;
		flex-direction: column;
		gap: 0.35rem;
		margin-bottom: 0.85rem;
	}
	.ls-field__label {
		font-size: 0.75rem;
		font-weight: 600;
		color: var(--ls-ink);
		margin: 0;
	}
	.ls-field__label .ls-req {
		color: var(--ls-accent);
		margin-left: 2px;
	}
	.ls-field__hint {
		font-size: 0.7rem;
		color: var(--ls-muted);
		margin: 0;
	}
	.ls-field__msg {
		font-size: 0.7rem;
		margin: 0;
		display: flex;
		align-items: center;
		gap: 0.35rem;
	}
	.ls-field__msg--error { color: #dc2626; }
	.ls-field__msg--success { color: #059669; }
	.ls-field__control {
		position: relative;
		display: flex;
		align-items: center;
		min-height: 38px;
		border: 1px solid var(--ls-border);
		border-radius: var(--ls-radius);
		background: var(--ls-surface);
		transition: border-color 0.15s ease, box-shadow 0.15s ease;
	}
	.ls-field__control:focus-within {
		border-color: var(--ls-blue-focus);
		box-shadow: 0 0 0 3px color-mix(in srgb, var(--ls-blue-soft-ring) 55%, transparent);
	}
	.ls-field.is-error .ls-field__control {
		border-color: #f87171;
		background: #fff5f5;
	}
	.ls-field.is-success .ls-field__control {
		border-color: #34d399;
	}
	.ls-field.is-disabled .ls-field__control {
		background: #f1f5f9;
		opacity: 0.75;
		pointer-events: none;
	}
	.ls-field__input {
		flex: 1 1 auto;
		border: none !important;
		background: transparent !important;
		box-shadow: none !important;
		outline: none !important;
		padding: 0.45rem 0.7rem;
		font-size: 0.8125rem;
		color: var(--ls-ink);
		min-width: 0;
		width: 100%;
	}
	.ls-field__input::placeholder { color: #94a3b8; }
	.ls-field__affix {
		flex: 0 0 auto;
		display: inline-flex;
		align-items: center;
		gap: 0.35rem;
		padding: 0 0.65rem;
		color: var(--ls-muted);
		font-size: 0.75rem;
		font-weight: 600;
		white-space: nowrap;
	}
	.ls-field__affix--prefix { border-right: 1px solid var(--ls-border); }
	.ls-field__affix--suffix { border-left: 1px solid var(--ls-border); }
	.ls-field__affix select {
		border: none;
		background: transparent;
		font-size: 0.75rem;
		font-weight: 600;
		color: var(--ls-ink);
		padding: 0;
		outline: none;
	}
	.ls-field__icon-btn {
		border: none;
		background: transparent;
		color: #94a3b8;
		padding: 0 0.55rem;
		cursor: pointer;
		line-height: 1;
	}
	.ls-field__icon-btn:hover { color: var(--ls-ink); }
	.ls-field__action-btn {
		border: none;
		background: var(--ls-ink);
		color: var(--ls-ink-fg);
		font-size: 0.72rem;
		font-weight: 600;
		padding: 0.4rem 0.75rem;
		border-radius: 6px;
		margin-right: 0.3rem;
		cursor: pointer;
	}

	/* Status select / search combo (pure CSS demo lists) */
	.ls-combo {
		position: relative;
	}
	.ls-combo__trigger {
		width: 100%;
		display: flex;
		align-items: center;
		gap: 0.5rem;
		padding: 0.45rem 0.7rem;
		border: none;
		background: transparent;
		text-align: left;
		font-size: 0.8125rem;
		color: var(--ls-ink);
		cursor: pointer;
	}
	.ls-combo__dot {
		width: 8px;
		height: 8px;
		border-radius: 50%;
		flex-shrink: 0;
	}
	.ls-combo__chevron { margin-left: auto; color: #94a3b8; }
	.ls-combo__menu {
		display: none;
		position: absolute;
		z-index: 40;
		left: 0;
		right: 0;
		top: calc(100% + 4px);
		background: #fff;
		border: 1px solid var(--ls-border);
		border-radius: var(--ls-radius-lg);
		box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
		max-height: 220px;
		overflow-y: auto;
		padding: 0.35rem;
	}
	.ls-combo.is-open .ls-combo__menu { display: block; }
	.ls-combo.is-open .ls-field__control {
		border-color: var(--ls-blue-focus);
	}
	.ls-combo__item {
		display: flex;
		align-items: center;
		gap: 0.5rem;
		width: 100%;
		border: none;
		background: transparent;
		padding: 0.45rem 0.55rem;
		border-radius: 6px;
		font-size: 0.8125rem;
		color: var(--ls-ink);
		cursor: pointer;
		text-align: left;
	}
	.ls-combo__item:hover,
	.ls-combo__item.is-active {
		background: var(--ls-blue-soft);
	}
	.ls-combo__item .mdi-check { margin-left: auto; color: #2563eb; }
	.ls-combo__search-wrap {
		display: flex;
		align-items: center;
		gap: 0.35rem;
		flex: 1;
		padding: 0 0.55rem;
	}
	.ls-combo__search-wrap .mdi { color: #94a3b8; }
	.ls-combo__search-wrap input {
		border: none;
		outline: none;
		background: transparent;
		width: 100%;
		font-size: 0.8125rem;
		padding: 0.45rem 0;
		color: var(--ls-ink);
	}

	/* —— Select2 shells —— */
	.ls-select2-multi,
	.ls-select2-single {
		width: 100%;
	}
	.ls-select2-multi .select2-container,
	.ls-select2-single .select2-container {
		width: 100% !important;
	}
	.ls-select2-multi .select2-container--default .select2-selection--multiple,
	.ls-select2-single .select2-container--default .select2-selection--single {
		min-height: 38px !important;
		border: 1px solid var(--ls-border) !important;
		border-radius: var(--ls-radius) !important;
		background: var(--ls-surface) !important;
	}
	.ls-select2-multi .select2-container--default.select2-container--focus .select2-selection--multiple,
	.ls-select2-multi .select2-container--default.select2-container--open .select2-selection--multiple,
	.ls-select2-single .select2-container--default.select2-container--focus .select2-selection--single,
	.ls-select2-single .select2-container--default.select2-container--open .select2-selection--single {
		border-color: var(--ls-blue-focus) !important;
		box-shadow: 0 0 0 3px color-mix(in srgb, var(--ls-blue-soft-ring) 55%, transparent) !important;
	}
	.ls-select2-multi .select2-container--default .select2-selection--multiple .select2-selection__rendered {
		display: flex !important;
		flex-wrap: wrap !important;
		gap: 0.35rem !important;
		padding: 0.35rem 0.45rem !important;
	}
	.ls-select2-multi .select2-container--default .select2-selection--multiple .select2-selection__choice {
		display: inline-flex !important;
		align-items: center;
		gap: 0.15rem;
		margin: 0 !important;
		padding: 0.15rem 0.45rem 0.15rem 0.35rem !important;
		border: 1px solid color-mix(in srgb, var(--ls-accent) 28%, #e2e8f0) !important;
		border-radius: 999px !important;
		background: color-mix(in srgb, var(--ls-accent) 12%, #ffffff) !important;
		color: var(--ls-accent) !important;
		font-size: 0.7rem !important;
		font-weight: 600;
		line-height: 1.25;
	}
	.ls-select2-multi .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
		color: color-mix(in srgb, var(--ls-accent) 70%, #64748b) !important;
		border: none !important;
		margin-right: 0.1rem;
		font-weight: 700;
	}
	.ls-select2-multi .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
		color: var(--ls-accent) !important;
		background: transparent !important;
	}
	.ls-select2-single .select2-container--default .select2-selection--single .select2-selection__rendered {
		line-height: 36px !important;
		padding-left: 2rem !important;
		color: var(--ls-ink) !important;
		font-size: 0.8125rem;
	}
	.ls-select2-single .select2-selection__arrow { height: 36px !important; }
	.ls-select2-single-wrap { position: relative; }
	.ls-select2-single-wrap > .mdi-magnify {
		position: absolute;
		left: 0.65rem;
		top: 50%;
		transform: translateY(-50%);
		z-index: 2;
		color: #94a3b8;
		pointer-events: none;
	}

	/* —— Cards —— */
	.ls-soft-card {
		border: 1px solid var(--ls-border);
		border-radius: var(--ls-radius-lg);
		background: var(--ls-surface);
		overflow: hidden;
	}
	.ls-soft-card.is-expanded {
		border-color: var(--ls-blue-soft-ring);
		box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
	}
	.ls-soft-card__header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		width: 100%;
		padding: 0.7rem 0.85rem;
		border: none;
		background: var(--ls-bg);
		color: var(--ls-ink);
		font-size: 0.8125rem;
		font-weight: 600;
		text-align: left;
		cursor: pointer;
	}
	.ls-soft-card.is-expanded .ls-soft-card__header {
		background: var(--ls-blue-soft);
		border-bottom: 1px solid var(--ls-blue-soft-border);
	}
	.ls-soft-card__chevron { color: var(--ls-muted); }
	.ls-soft-card__body { padding: 0.85rem; }

	.ls-hierarchy-card {
		border: 1px solid var(--ls-border);
		border-radius: 16px;
		background: var(--ls-bg);
		padding: 1rem 1.1rem;
	}
	.ls-hierarchy-card__top {
		display: flex;
		align-items: center;
		gap: 0.65rem;
		margin-bottom: 0.65rem;
	}
	.ls-hierarchy-card__avatar {
		width: 36px;
		height: 36px;
		border-radius: 50%;
		background: #3b82f6;
		color: #fff;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		font-weight: 700;
		font-size: 0.9rem;
		flex-shrink: 0;
	}
	.ls-hierarchy-card__title {
		font-weight: 700;
		font-size: 0.9rem;
		color: var(--ls-ink);
		margin: 0;
	}
	.ls-hierarchy-card__meta {
		font-size: 0.75rem;
		color: var(--ls-muted);
		margin-left: 0.35rem;
	}
	.ls-hierarchy-card__body {
		font-size: 0.8125rem;
		color: #334155;
		line-height: 1.5;
		margin: 0 0 0.75rem;
	}
	.ls-hierarchy-card__footer {
		display: flex;
		align-items: center;
		gap: 0.85rem;
		font-size: 0.75rem;
		color: var(--ls-muted);
	}

	/* —— Tables —— */
	.ls-table-wrap {
		border: 1px solid var(--ls-border);
		border-radius: var(--ls-radius-lg);
		background: #fff;
		overflow: hidden;
		box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
	}
	.ls-table {
		width: 100%;
		margin: 0;
		border-collapse: collapse;
		font-size: 0.8125rem;
	}
	.ls-table thead th {
		font-size: 0.68rem;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.03em;
		color: var(--ls-muted);
		padding: 0.65rem 0.85rem;
		background: var(--ls-bg);
		border-bottom: 1px solid var(--ls-border);
		white-space: nowrap;
	}
	.ls-table tbody td {
		padding: 0.75rem 0.85rem;
		border-bottom: 1px solid #f1f5f9;
		color: var(--ls-ink);
		vertical-align: middle;
	}
	.ls-table tbody tr:last-child td { border-bottom: none; }
	.ls-pill {
		display: inline-flex;
		align-items: center;
		padding: 0.15rem 0.65rem;
		border-radius: 999px;
		font-size: 0.7rem;
		font-weight: 600;
	}
	.ls-pill--open { background: #ede9fe; color: #5b21b6; }
	.ls-pill--paid { background: #d1fae5; color: #065f46; }
	.ls-pill--inactive { background: #f1f5f9; color: #64748b; }
	.ls-table__stack-primary { font-weight: 700; color: var(--ls-accent); }
	.ls-table__stack-secondary { font-size: 0.7rem; color: var(--ls-muted); }
	.ls-skeleton {
		display: inline-block;
		height: 10px;
		border-radius: 6px;
		background: #e2e8f0;
		min-width: 4rem;
	}

	/* —— Drag and drop —— */
	.ls-dnd-list {
		display: flex;
		flex-direction: column;
		gap: 0.5rem;
	}
	.ls-dnd-item {
		display: flex;
		align-items: center;
		gap: 0.55rem;
		padding: 0.55rem 0.75rem;
		border: 1px solid var(--ls-border);
		border-radius: 999px;
		background: #fff;
		cursor: grab;
		user-select: none;
	}
	.ls-dnd-item.is-dragging {
		border-color: var(--ls-blue-soft-ring);
		box-shadow: 0 8px 20px rgba(59, 130, 246, 0.18);
		opacity: 0.95;
	}
	.ls-dnd-item.is-ghost {
		opacity: 0.4;
		border-style: dashed;
	}
	.ls-dnd-handle {
		color: #94a3b8;
		font-size: 1rem;
		line-height: 1;
	}
	.ls-dnd-item__label { flex: 1; font-size: 0.8125rem; font-weight: 600; color: var(--ls-ink); }
	.ls-dnd-status {
		width: 10px;
		height: 10px;
		border-radius: 50%;
		flex-shrink: 0;
	}

	.ls-dnd-tree { list-style: none; margin: 0; padding: 0; }
	.ls-dnd-tree ul { list-style: none; margin: 0.35rem 0 0.35rem 1.25rem; padding: 0; border-left: 1px solid #e2e8f0; padding-left: 0.75rem; }
	.ls-dnd-tree-node {
		display: flex;
		align-items: stretch;
		border: 1px solid var(--ls-border);
		border-radius: 8px;
		background: #fff;
		margin-bottom: 0.4rem;
		overflow: hidden;
	}
	.ls-dnd-tree-node.is-dragging {
		border-color: var(--ls-blue-focus);
		box-shadow: 0 0 0 3px color-mix(in srgb, var(--ls-blue-soft) 80%, transparent);
	}
	.ls-dnd-tree-node.is-drop-target {
		outline: 2px dashed var(--ls-blue-focus);
		outline-offset: 2px;
	}
	.ls-dnd-tree-handle {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 2.25rem;
		background: #f1f5f9;
		color: #64748b;
		cursor: grab;
		border: none;
	}
	.ls-dnd-tree-body { flex: 1; padding: 0.55rem 0.75rem; }
	.ls-dnd-tree-title { font-weight: 700; font-size: 0.8125rem; margin: 0; color: var(--ls-ink); }
	.ls-dnd-tree-sub { font-size: 0.7rem; color: var(--ls-muted); margin: 0; }
	.ls-dnd-tree-toggle {
		width: 1.35rem;
		height: 1.35rem;
		border-radius: 50%;
		border: 1px solid #cbd5e1;
		background: #fff;
		color: #64748b;
		font-size: 0.75rem;
		line-height: 1;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		margin-right: 0.35rem;
		cursor: pointer;
	}

	/* Gallery chrome */
	.ls-gallery-section {
		margin-bottom: 2rem;
	}
	.ls-gallery-section h3 {
		font-size: 1rem;
		font-weight: 700;
		color: var(--ls-ink);
		margin: 0 0 0.35rem;
	}
	.ls-gallery-section p.lead-muted {
		font-size: 0.8rem;
		color: var(--ls-muted);
		margin: 0 0 1rem;
	}
	.ls-swatch-row {
		display: flex;
		flex-wrap: wrap;
		gap: 0.75rem;
		margin-bottom: 1rem;
	}
	.ls-swatch {
		width: 7.5rem;
		border: 1px solid var(--ls-border);
		border-radius: 8px;
		overflow: hidden;
		background: #fff;
		font-size: 0.68rem;
	}
	.ls-swatch__chip { height: 2.25rem; }
	.ls-swatch__meta { padding: 0.4rem 0.5rem; color: var(--ls-muted); }
</style>
