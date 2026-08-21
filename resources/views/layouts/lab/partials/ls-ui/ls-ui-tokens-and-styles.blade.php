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
		overflow-x: clip;
		max-width: 100%;
	}
	.ls-ui-kit [x-cloak] { display: none !important; }

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
		border: 1px solid var(--ls-border, #e2e8f0);
		border-radius: var(--ls-radius, 8px);
		background: var(--ls-surface, #ffffff);
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
		max-width: 100%;
		overflow: hidden;
	}
	.ls-select2-multi .select2-container,
	.ls-select2-single .select2-container {
		width: 100% !important;
		max-width: 100% !important;
		height: auto !important;
		/* Kill theme/bootstrap outer border — only .select2-selection should stroke */
		border: 0 !important;
		box-shadow: none !important;
		background: transparent !important;
		padding: 0 !important;
		outline: none !important;
	}
	/*
	 * Select2 dropdown host (dropdownParent / AttachBody) is a second
	 * .select2-container--open WITHOUT the .select2 class. Theme borders
	 * on that host stack with .select2-dropdown → double “layered” chrome.
	 */
	.select2-container.select2-container--open:not(.select2) {
		border: 0 !important;
		box-shadow: none !important;
		background: transparent !important;
		padding: 0 !important;
		outline: none !important;
	}
	.ls-select2-multi .select2-container .selection,
	.ls-select2-multi .select2-container .select2-selection {
		height: auto !important;
		max-width: 100% !important;
		overflow: hidden !important;
	}
	/* Multi: height hugs tags — no fixed min-height / no inline search row */
	.ls-select2-multi .select2-container--default .select2-selection--multiple {
		min-height: 0 !important;
		height: auto !important;
		border: 1px solid var(--ls-border) !important;
		border-radius: var(--ls-radius) !important;
		background: var(--ls-surface) !important;
		padding: 0 !important;
		overflow: hidden !important;
	}
	.ls-select2-single .select2-container--default .select2-selection--single {
		min-height: 34px !important;
		height: auto !important;
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
		align-items: center !important;
		gap: 0.3rem !important;
		padding: 0.25rem 0.4rem !important;
		margin: 0 !important;
		min-height: 0 !important;
		line-height: 1.2 !important;
	}
	/* Search lives in the dropdown only — kill Select2 inline search (stops page blowout) */
	.ls-select2-multi .select2-selection--multiple .select2-search--inline {
		position: absolute !important;
		width: 0 !important;
		min-width: 0 !important;
		max-width: 0 !important;
		height: 0 !important;
		margin: 0 !important;
		padding: 0 !important;
		overflow: hidden !important;
		opacity: 0 !important;
		pointer-events: none !important;
		left: 0 !important;
		top: 0 !important;
		float: none !important;
	}
	.ls-select2-multi .select2-selection--multiple .select2-search--inline .select2-search__field {
		width: 0 !important;
		min-width: 0 !important;
		max-width: 0 !important;
		height: 0 !important;
		padding: 0 !important;
		margin: 0 !important;
		border: 0 !important;
		font-size: 0 !important;
		line-height: 0 !important;
	}
	.ls-select2-multi .select2-container--default .select2-selection--multiple .select2-selection__choice {
		display: inline-flex !important;
		align-items: center;
		gap: 0.15rem;
		margin: 0 !important;
		padding: 0.12rem 0.4rem 0.12rem 0.3rem !important;
		border: 1px solid color-mix(in srgb, var(--ls-accent) 28%, #e2e8f0) !important;
		border-radius: 6px !important;
		background: color-mix(in srgb, var(--ls-accent) 12%, #ffffff) !important;
		color: var(--ls-accent) !important;
		font-size: 0.7rem !important;
		font-weight: 600;
		line-height: 1.2 !important;
	}
	.ls-select2-multi .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
		color: color-mix(in srgb, var(--ls-accent) 70%, #64748b) !important;
		border: none !important;
		margin-right: 0.1rem;
		font-weight: 700;
		line-height: 1 !important;
	}
	.ls-select2-multi .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
		color: var(--ls-accent) !important;
		background: transparent !important;
	}
	.ls-select2-multi.ls-select2-slate .select2-container--default .select2-selection--multiple .select2-selection__choice {
		background: #f1f5f9 !important;
		border-color: #cbd5e1 !important;
		color: #1e293b !important;
	}
	.ls-select2-multi.ls-select2-slate .select2-selection__choice__remove {
		color: #64748b !important;
	}
	/* Injected dropdown search (not in the column) — full width, matches list */
	.ls-select2-dropdown-search .ls-dd-search,
	.select2-dropdown.ls-select2-dropdown-search .ls-dd-search {
		display: flex;
		align-items: center;
		gap: 0.35rem;
		width: 100%;
		box-sizing: border-box;
		padding: 0.45rem 0.55rem;
		background: var(--ls-blue-soft, #eff6ff);
		border-bottom: 1px solid var(--ls-blue-soft-border, #dbeafe);
	}
	.ls-dd-search .mdi { color: #94a3b8; font-size: 0.95rem; flex-shrink: 0; }
	.ls-dd-search input {
		flex: 1 1 auto;
		width: 100%;
		min-width: 0;
		border: 1px solid var(--ls-border, #e2e8f0);
		border-radius: 8px;
		padding: 0.3rem 0.5rem;
		font-size: 0.78rem;
		min-height: 30px;
		outline: none;
		background: #fff;
		color: var(--ls-ink, #1e293b);
		box-sizing: border-box;
	}
	.ls-dd-search input:focus {
		border-color: var(--ls-blue-focus, #93c5fd);
	}
	/* Gallery pattern: never show native Select2 dropdown search alongside .ls-dd-search */
	.select2-dropdown.ls-select2-dropdown-search .select2-search--dropdown {
		display: none !important;
		height: 0 !important;
		padding: 0 !important;
		margin: 0 !important;
		border: 0 !important;
		overflow: hidden !important;
	}
	.ls-select2-single .select2-container--default .select2-selection--single .select2-selection__rendered {
		line-height: 32px !important;
		padding-left: 2rem !important;
		color: var(--ls-ink) !important;
		font-size: 0.8125rem;
	}
	.ls-select2-single .select2-selection__arrow { height: 32px !important; }
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

	/* —— Compact density (match content, no extra height) —— */
	.ls-compact .ls-field { margin-bottom: 0.55rem; gap: 0.25rem; }
	.ls-compact .ls-field__control { min-height: 32px; }
	.ls-compact .ls-field__input { padding: 0.3rem 0.55rem; font-size: 0.78rem; }
	.ls-compact .ls-field__label { font-size: 0.7rem; }
	.ls-compact .ls-field__hint,
	.ls-compact .ls-field__msg { font-size: 0.65rem; }
	.ls-gallery-demo.ls-compact { width: fit-content; max-width: 100%; min-width: 16rem; }
	.ls-gallery-demo.ls-compact-wide { width: 100%; max-width: 28rem; }

	/* —— Search bars (pill + filter) —— */
	.ls-search-bar {
		display: inline-flex;
		align-items: center;
		gap: 0.45rem;
		width: fit-content;
		max-width: 100%;
	}
	.ls-search-bar__field {
		display: inline-flex;
		align-items: center;
		gap: 0.4rem;
		min-height: 34px;
		padding: 0 0.7rem;
		border: 1px solid var(--ls-border);
		border-radius: 999px;
		background: #fff;
		box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
	}
	.ls-search-bar__field:focus-within {
		border-color: var(--ls-blue-focus);
		box-shadow: 0 0 0 3px color-mix(in srgb, var(--ls-blue-soft-ring) 50%, transparent);
	}
	.ls-search-bar__field .mdi-magnify { color: #94a3b8; font-size: 1rem; }
	.ls-search-bar__field input {
		border: none;
		outline: none;
		background: transparent;
		font-size: 0.78rem;
		min-width: 10rem;
		color: var(--ls-ink);
		padding: 0.35rem 0;
	}
	.ls-search-bar__filter {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 34px;
		height: 34px;
		border-radius: 10px;
		border: none;
		background: #2563eb;
		color: #fff;
		cursor: pointer;
		flex-shrink: 0;
	}
	.ls-search-bar__filter--ghost {
		background: #fff;
		border: 1px solid var(--ls-blue-focus);
		color: #2563eb;
	}
	.ls-search-bar__filter--inline {
		width: 28px;
		height: 28px;
		border-radius: 999px;
		background: transparent;
		border: 1px solid var(--ls-blue-soft-border);
		color: #2563eb;
		margin-left: 0.15rem;
	}
	.ls-search-bar.is-integrated .ls-search-bar__field { padding-right: 0.35rem; }

	/* —— Search basic / success (native Alpine) —— */
	.ls-search-basic.is-success .ls-field__control {
		border-color: #34d399;
		background: #f0fdf4;
	}
	.ls-search-basic .ls-combo__item.is-active {
		background: var(--ls-blue-soft);
		color: #1d4ed8;
		font-weight: 600;
	}
	.ls-search-basic .ls-combo__item .mdi-check { color: #2563eb; }
	.ls-search-basic.is-success .ls-combo__trigger .mdi-check-circle { color: #059669; margin-left: auto; }

	.ls-select2-single-columns .ls-field__control {
		display: flex;
		align-items: center;
		gap: 0.15rem;
		min-height: 38px;
		padding: 0.15rem 0.25rem 0.15rem 0.45rem;
		border: 1px solid var(--ls-border, #e2e8f0);
		border-radius: 8px;
		background: #fff;
	}
	.ls-select2-single-columns.is-open .ls-field__control,
	.ls-select2-single-columns .ls-field__control:focus-within {
		border-color: var(--ls-blue-focus, #93c5fd);
		box-shadow: 0 0 0 3px rgb(147 197 253 / 0.35);
	}
	.ls-select2-single-columns .ls-combo__search-wrap {
		flex: 1;
		display: flex;
		align-items: center;
		gap: 0.35rem;
		min-width: 0;
	}
	.ls-select2-single-columns .ls-combo__search-wrap input {
		border: 0;
		outline: 0;
		width: 100%;
		font-size: 0.8125rem;
		background: transparent;
	}
	.ls-select2-single-columns .ls-combo__menu {
		position: absolute;
		left: 0;
		right: 0;
		top: calc(100% + 4px);
		background: #fff;
		border: 1px solid var(--ls-border, #e2e8f0);
		border-radius: 10px;
		box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
		padding: 0.25rem 0;
		z-index: 60;
	}
	.ls-select2-single-columns:not(.is-open) .ls-combo__menu {
		display: none !important;
	}
	.ls-select2-single-columns.is-open .ls-combo__menu {
		display: block !important;
	}
	.ls-select2-single-columns__placeholder {
		color: #94a3b8;
		font-size: 0.8125rem;
		font-weight: 500;
	}
	.ls-select2-single-columns .ls-combo__item {
		width: 100%;
		display: flex;
		align-items: center;
		gap: 0.45rem;
		border: 0;
		background: transparent;
		padding: 0.45rem 0.7rem;
		text-align: left;
		font-size: 0.78rem;
		color: var(--ls-ink, #0f172a);
		cursor: pointer;
	}
	.ls-select2-single-columns .ls-combo__item:hover,
	.ls-select2-single-columns .ls-combo__item.is-active {
		background: var(--ls-blue-soft, #eff6ff);
	}
	.ls-select2-single-columns .ls-combo__item .mdi-check {
		color: #2563eb;
		margin-left: auto;
	}

	.select2-dropdown.ls-select2-dropdown-search,
	.ls-select2-dropdown-search .select2-dropdown {
		border: 1px solid var(--ls-border) !important;
		border-radius: 10px !important;
		overflow: hidden;
		box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
		background: #fff !important;
	}
	.select2-dropdown.ls-select2-dropdown-search .select2-results,
	.ls-select2-dropdown-search .select2-results {
		padding: 0.15rem 0;
	}
	.ls-select2-dropdown-search .select2-search--dropdown {
		padding: 0.45rem 0.55rem !important;
		background: var(--ls-blue-soft);
		border-bottom: 1px solid var(--ls-blue-soft-border);
	}
	.ls-select2-dropdown-search .select2-search--dropdown .select2-search__field {
		border: 1px solid var(--ls-border) !important;
		border-radius: 8px !important;
		padding: 0.35rem 0.55rem !important;
		font-size: 0.78rem !important;
		min-height: 30px !important;
	}
	.ls-select2-dropdown-search .select2-results__option {
		padding: 0.4rem 0.65rem !important;
		font-size: 0.78rem !important;
		display: flex !important;
		align-items: center;
		justify-content: flex-start !important;
		gap: 0.45rem;
		text-align: left !important;
		direction: ltr !important;
	}
	.ls-select2-dropdown-search .select2-results__option--highlighted,
	.ls-select2-dropdown-search .select2-results__option[aria-selected="true"] {
		background: var(--ls-blue-soft) !important;
		color: var(--ls-ink) !important;
	}
	.ls-select2-check {
		width: 14px;
		height: 14px;
		border: 1.5px solid #cbd5e1;
		border-radius: 3px;
		flex-shrink: 0;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: #fff;
		order: 0;
	}
	.ls-select2-results__option[aria-selected="true"] .ls-select2-check,
	.select2-results__option[aria-selected="true"] .ls-select2-check {
		background: var(--ls-accent);
		border-color: var(--ls-accent);
		color: #fff;
		font-size: 0.65rem;
	}
	.ls-select2-meta-row {
		display: flex;
		align-items: center;
		justify-content: flex-start;
		gap: 0.5rem;
		width: 100%;
		text-align: left;
	}
	.ls-select2-meta-row--spread {
		justify-content: space-between;
	}
	.ls-select2-meta-row__label {
		font-weight: 400;
		color: var(--ls-ink);
		text-align: left;
		flex: 0 1 auto;
	}
	.ls-select2-meta-row--spread .ls-select2-meta-row__label {
		font-weight: 500;
	}
	.ls-select2-meta-row__meta {
		font-size: 0.7rem;
		color: var(--ls-muted);
		display: inline-flex;
		align-items: center;
		gap: 0.25rem;
		white-space: nowrap;
		margin-left: auto;
		text-align: right;
		flex: 0 0 auto;
	}
	.ls-select2-choice-with-meta {
		display: inline-flex;
		align-items: center;
		gap: 0.35rem;
		max-width: 100%;
	}
	.ls-select2-choice-with-meta__label {
		overflow: hidden;
		text-overflow: ellipsis;
	}
	.ls-select2-choice-with-meta__meta {
		opacity: 0.85;
		font-size: 0.68rem;
		font-weight: 500;
		white-space: nowrap;
	}
	.ls-select2-multi.ls-select2-slate .select2-container--default .select2-selection--multiple .select2-selection__choice {
		background: #f1f5f9 !important;
		border-color: #cbd5e1 !important;
		color: var(--ls-ink) !important;
		border-radius: 6px !important;
	}
	.ls-select2-multi.ls-select2-slate .select2-selection__choice__remove {
		color: var(--ls-muted) !important;
	}
	.ls-select2-view {
		display: flex;
		flex-wrap: wrap;
		gap: 0.35rem;
		min-height: 32px;
		align-items: center;
	}
	.ls-select2-view__chip {
		display: inline-flex;
		align-items: center;
		padding: 0.15rem 0.5rem;
		border-radius: 6px;
		background: color-mix(in srgb, var(--ls-accent) 12%, #fff);
		border: 1px solid color-mix(in srgb, var(--ls-accent) 28%, #e2e8f0);
		color: var(--ls-accent);
		font-size: 0.7rem;
		font-weight: 600;
	}

	/* —— Filterable table —— */
	.ls-table-filter th .ls-th-filter {
		display: inline-flex;
		align-items: center;
		gap: 0.25rem;
	}
	.ls-table-filter .ls-filter-btn {
		border: none;
		background: transparent;
		color: #94a3b8;
		padding: 0;
		line-height: 1;
		cursor: pointer;
		font-size: 0.85rem;
	}
	.ls-table-filter .ls-filter-btn.is-active,
	.ls-table-filter .ls-filter-btn:hover { color: #2563eb; }
	.ls-table-filter .ls-filter-panel {
		position: absolute;
		z-index: 30;
		margin-top: 0.35rem;
		padding: 0.55rem;
		min-width: 11rem;
		background: #fff;
		border: 1px solid var(--ls-border);
		border-radius: 8px;
		box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
	}
	.ls-table-filter th { position: relative; }
	.ls-table-filter .ls-filter-panel input,
	.ls-table-filter .ls-filter-panel select {
		width: 100%;
		font-size: 0.75rem;
		border: 1px solid var(--ls-border);
		border-radius: 6px;
		padding: 0.3rem 0.45rem;
		margin-bottom: 0.35rem;
	}
	.ls-table.ls-table--dense thead th,
	.ls-table.ls-table--dense tbody td {
		padding: 0.45rem 0.65rem;
		font-size: 0.78rem;
		white-space: nowrap;
	}
	.ls-table-wrap.ls-table-wrap--fit {
		width: fit-content;
		max-width: 100%;
	}

	/* —— Collection settings card —— */
	.ls-card-collection {
		border: 1px solid var(--ls-border);
		border-radius: 14px;
		background: #fff;
		padding: 1rem 1.1rem;
		max-width: 26rem;
	}
	.ls-card-collection__head {
		display: flex;
		align-items: flex-start;
		justify-content: space-between;
		margin-bottom: 0.85rem;
	}
	.ls-card-collection__title { font-size: 0.95rem; font-weight: 700; margin: 0; color: var(--ls-ink); }
	.ls-card-collection__close {
		border: none; background: transparent; color: #94a3b8; cursor: pointer; line-height: 1;
	}
	.ls-card-collection__row {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 0.75rem;
		padding: 0.55rem 0;
	}
	.ls-card-collection__row-title { font-weight: 600; font-size: 0.8rem; margin: 0; }
	.ls-card-collection__row-sub { font-size: 0.7rem; color: var(--ls-muted); margin: 0.1rem 0 0; }
	.ls-toggle {
		width: 40px; height: 22px; border-radius: 999px; border: none;
		background: #cbd5e1; position: relative; cursor: pointer; flex-shrink: 0;
	}
	.ls-toggle.is-on { background: #2563eb; }
	.ls-toggle::after {
		content: ''; position: absolute; top: 2px; left: 2px;
		width: 18px; height: 18px; border-radius: 50%; background: #fff;
		transition: transform 0.15s ease;
	}
	.ls-toggle.is-on::after { transform: translateX(18px); }
	.ls-card-collection__section { font-size: 0.7rem; color: var(--ls-muted); margin: 0.65rem 0 0.45rem; font-weight: 600; }
	.ls-choice-card {
		display: block; width: 100%; text-align: left;
		border: 1px solid var(--ls-border); border-radius: 10px;
		padding: 0.65rem 0.75rem; margin-bottom: 0.45rem;
		background: #fff; cursor: pointer;
	}
	.ls-choice-card.is-selected {
		border-color: var(--ls-blue-focus);
		background: var(--ls-blue-soft);
		box-shadow: 0 0 0 1px var(--ls-blue-soft-ring);
	}
	.ls-choice-card__title { font-weight: 700; font-size: 0.8rem; margin: 0 0 0.15rem; }
	.ls-choice-card__body { font-size: 0.72rem; color: var(--ls-muted); margin: 0; }
	.ls-card-collection__foot {
		display: flex; justify-content: flex-end; gap: 0.45rem; margin-top: 0.85rem;
	}
	.ls-btn {
		border-radius: 8px; font-size: 0.75rem; font-weight: 600;
		padding: 0.4rem 0.75rem; border: 1px solid var(--ls-border); background: #fff; color: var(--ls-ink); cursor: pointer;
	}
	.ls-btn--primary { background: #2563eb; border-color: #2563eb; color: #fff; }
	.ls-btn--secondary-fill { background: var(--ls-ink); border-color: var(--ls-ink); color: var(--ls-ink-fg); }

	/* —— Notification card —— */
	.ls-card-notify {
		border: 1px solid var(--ls-border);
		border-radius: 16px;
		background: #fff;
		padding: 1rem;
		max-width: 26rem;
	}
	.ls-card-notify__head {
		display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;
	}
	.ls-card-notify__title { font-weight: 700; font-size: 0.9rem; margin: 0; }
	.ls-card-notify__see { font-size: 0.72rem; color: var(--ls-muted); text-decoration: none; }
	.ls-seg {
		display: flex; background: #f1f5f9; border-radius: 999px; padding: 0.2rem; margin-bottom: 0.85rem;
	}
	.ls-seg button {
		flex: 1; border: none; background: transparent; border-radius: 999px;
		font-size: 0.72rem; font-weight: 600; color: var(--ls-muted); padding: 0.35rem 0.5rem; cursor: pointer;
	}
	.ls-seg button.is-active { background: #fff; color: var(--ls-ink); box-shadow: 0 1px 2px rgba(15,23,42,0.06); }
	.ls-notify-item {
		display: flex; gap: 0.65rem; padding: 0.55rem 0; border-bottom: 1px solid #f1f5f9;
	}
	.ls-notify-item:last-child { border-bottom: none; }
	.ls-notify-item__icon {
		width: 32px; height: 32px; border-radius: 50%; background: #f1f5f9;
		display: inline-flex; align-items: center; justify-content: center; color: var(--ls-muted); flex-shrink: 0;
	}
	.ls-notify-item__title { font-size: 0.78rem; font-weight: 700; margin: 0; color: var(--ls-ink); }
	.ls-notify-item.is-unread .ls-notify-item__title::before {
		content: ''; display: inline-block; width: 6px; height: 6px; border-radius: 50%;
		background: var(--ls-accent); margin-right: 0.35rem; vertical-align: middle;
	}
	.ls-notify-item--action {
		border-left: 0; border-right: 0; border-top: 0; background: transparent;
		cursor: pointer; font: inherit; color: inherit; width: 100%; text-align: left;
	}
	.ls-notify-empty {
		padding: 1rem 0.25rem; text-align: center; color: var(--ls-muted); font-size: 0.78rem;
	}
	.ls-notify-item__meta { float: right; font-size: 0.65rem; color: var(--ls-muted); font-weight: 500; }
	.ls-notify-item__body { font-size: 0.72rem; color: var(--ls-muted); margin: 0.2rem 0 0; clear: both; }

	/* —— Upload —— */
	.ls-upload {
		border: 1px solid var(--ls-border);
		border-radius: 14px;
		background: #fff;
		padding: 1rem;
		max-width: 28rem;
	}
	.ls-upload__head {
		display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 0.75rem;
	}
	.ls-upload__title { font-weight: 700; font-size: 0.9rem; margin: 0; }
	.ls-upload__sub { font-size: 0.72rem; color: var(--ls-muted); margin: 0.15rem 0 0; }
	.ls-upload__drop {
		border: 1.5px dashed #cbd5e1;
		border-radius: 12px;
		padding: 1.25rem 1rem;
		text-align: center;
		background: #fafbfc;
		transition: border-color 0.15s, background 0.15s;
	}
	.ls-upload__drop.is-active {
		border-color: #2563eb;
		background: var(--ls-blue-soft);
	}
	.ls-upload__drop .mdi { font-size: 1.6rem; color: #94a3b8; }
	.ls-upload__drop.is-active .mdi { color: #2563eb; }
	.ls-upload__drop-text { font-size: 0.78rem; margin: 0.45rem 0 0.15rem; color: var(--ls-ink); }
	.ls-upload__drop-text a { color: #2563eb; font-weight: 600; text-decoration: none; }
	.ls-upload__drop-hint { font-size: 0.68rem; color: var(--ls-muted); margin: 0; }
	.ls-upload__file {
		display: flex; align-items: center; gap: 0.55rem;
		border: 1px solid var(--ls-border); border-radius: 10px;
		padding: 0.55rem 0.65rem; margin-top: 0.55rem;
	}
	.ls-upload__file-icon { color: #dc2626; font-size: 1.25rem; }
	.ls-upload__file-name { font-size: 0.75rem; font-weight: 600; margin: 0; }
	.ls-upload__file-meta { font-size: 0.65rem; color: var(--ls-muted); margin: 0; }
	.ls-upload__progress {
		height: 4px; border-radius: 999px; background: #e2e8f0; margin-top: 0.35rem; overflow: hidden;
	}
	.ls-upload__progress > span { display: block; height: 100%; background: #2563eb; }
	.ls-upload__ok { color: #059669; font-size: 0.65rem; font-weight: 600; }
	.ls-upload__or {
		display: flex; align-items: center; gap: 0.65rem; margin: 0.85rem 0 0.55rem;
		font-size: 0.7rem; color: var(--ls-muted); font-weight: 600;
	}
	.ls-upload__or::before, .ls-upload__or::after {
		content: ''; flex: 1; height: 1px; background: var(--ls-border);
	}

	/* —— Stepper card —— */
	.ls-card-stepper {
		display: grid;
		grid-template-columns: 11.5rem 1fr;
		gap: 1rem;
		border: 1px solid var(--ls-border);
		border-radius: 16px;
		background: #fff;
		padding: 1rem;
		max-width: 42rem;
	}
	.ls-stepper { list-style: none; margin: 0; padding: 0; }
	.ls-stepper__item {
		display: flex; gap: 0.55rem; position: relative; padding-bottom: 0.85rem;
	}
	.ls-stepper__item:not(:last-child)::before {
		content: ''; position: absolute; left: 11px; top: 24px; bottom: 0; width: 1px; background: #e2e8f0;
	}
	.ls-stepper__dot {
		width: 22px; height: 22px; border-radius: 50%; flex-shrink: 0;
		display: inline-flex; align-items: center; justify-content: center;
		font-size: 0.65rem; font-weight: 700; border: 1.5px solid #cbd5e1;
		background: #fff; color: var(--ls-muted); z-index: 1;
	}
	.ls-stepper__item.is-done .ls-stepper__dot,
	.ls-stepper__item.is-active .ls-stepper__dot {
		background: #2563eb; border-color: #2563eb; color: #fff;
	}
	.ls-stepper__label { font-size: 0.72rem; font-weight: 600; color: var(--ls-muted); margin: 0.15rem 0 0; }
	.ls-stepper__item.is-active .ls-stepper__label,
	.ls-stepper__item.is-done .ls-stepper__label { color: var(--ls-ink); }
	.ls-stepper-panel__progress {
		height: 4px; border-radius: 999px; background: #e2e8f0; margin-bottom: 0.65rem; overflow: hidden;
	}
	.ls-stepper-panel__progress > span { display: block; height: 100%; background: #2563eb; }
	.ls-stepper-panel__title { font-size: 0.85rem; font-weight: 700; margin: 0 0 0.65rem; }
	.ls-stepper-panel .ls-field { margin-bottom: 0.55rem; }
	.ls-stepper-panel__actions {
		display: flex; align-items: center; justify-content: space-between; margin-top: 0.85rem;
	}
	@media (max-width: 768px) {
		.ls-card-stepper { grid-template-columns: 1fr; }
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

	/* TRF sample panel — soft-card blue header + collection burgundy accents */
	.ls-trf-sample-panel {
		border: 1px solid color-mix(in srgb, var(--ls-accent) 18%, var(--ls-border));
		border-radius: var(--ls-radius-lg);
		overflow: hidden;
		background: var(--ls-surface);
		margin-bottom: 0.65rem;
	}
	.ls-trf-sample-panel.is-expanded {
		border-color: color-mix(in srgb, var(--ls-accent) 32%, var(--ls-blue-soft-border));
		box-shadow: 0 0 0 1px color-mix(in srgb, var(--ls-blue-soft-ring) 55%, transparent);
	}
	.ls-trf-sample-panel__header {
		width: 100%;
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 0.5rem;
		padding: 0.65rem 0.85rem;
		border: 0;
		background: color-mix(in srgb, var(--ls-accent) 6%, #fff);
		color: var(--ls-ink);
		font-size: 0.8125rem;
		font-weight: 700;
		text-align: left;
		cursor: pointer;
	}
	.ls-trf-sample-panel.is-expanded .ls-trf-sample-panel__header {
		background: var(--ls-blue-soft);
		border-bottom: 1px solid var(--ls-blue-soft-border);
	}
	.ls-trf-sample-panel__title {
		color: var(--ls-accent);
	}
	.ls-trf-sample-panel.is-expanded .ls-trf-sample-panel__title {
		color: var(--ls-ink);
	}
	.ls-trf-sample-panel__body {
		padding: 0.85rem;
		background: #fff;
	}
	.ls-trf-sample-panel--view .ls-trf-sample-panel__header {
		cursor: default;
	}

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

	/* —— Pack C: Icon system + motion —— */
	.ls-icon-grid {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(7.5rem, 1fr));
		gap: 0.65rem;
	}
	.ls-icon-tile {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 0.4rem;
		padding: 0.75rem 0.4rem 0.55rem;
		border: 1px solid var(--ls-border);
		border-radius: 12px;
		background: #fff;
		text-align: center;
		min-height: 5.5rem;
	}
	.ls-icon-tile__glyph {
		width: 2.75rem;
		height: 2.75rem;
		border-radius: 12px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		font-size: 1.35rem;
		line-height: 1;
	}
	.ls-icon-tile__glyph--lg {
		width: 3.35rem;
		height: 3.35rem;
		font-size: 1.65rem;
		border-radius: 14px;
	}
	.ls-icon-tile__label {
		font-size: 0.62rem;
		font-weight: 600;
		color: var(--ls-muted);
		line-height: 1.25;
		word-break: break-word;
	}
	.ls-icon-tile__name {
		font-size: 0.58rem;
		font-family: var(--ls-font-mono, "IBM Plex Mono", monospace);
		color: #94a3b8;
	}
	.ls-icon--blue { background: #eff6ff; color: #2563eb; }
	.ls-icon--slate { background: #f1f5f9; color: #1e293b; }
	.ls-icon--burgundy { background: color-mix(in srgb, var(--ls-accent, #8b1e2d) 12%, #fff); color: var(--ls-accent, #8b1e2d); }
	.ls-icon--green { background: #dcfce7; color: #15803d; }
	.ls-icon--amber { background: #fef3c7; color: #b45309; }
	.ls-icon--cyan { background: #e0f2fe; color: #0369a1; }
	.ls-icon--violet { background: #ede9fe; color: #6d28d9; }
	.ls-icon--rose { background: #ffe4e6; color: #be123c; }

	@keyframes ls-spin { to { transform: rotate(360deg); } }
	@keyframes ls-pulse-soft {
		0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 color-mix(in srgb, currentColor 25%, transparent); }
		50% { transform: scale(1.04); box-shadow: 0 0 0 8px transparent; }
	}
	@keyframes ls-ring {
		0%, 100% { transform: rotate(0deg); }
		10% { transform: rotate(12deg); }
		20% { transform: rotate(-10deg); }
		30% { transform: rotate(8deg); }
		40% { transform: rotate(-6deg); }
		50% { transform: rotate(0deg); }
	}
	@keyframes ls-fade-rise {
		from { opacity: 0; transform: translateY(6px); }
		to { opacity: 1; transform: translateY(0); }
	}
	@keyframes ls-check-pop {
		0% { transform: scale(0.6); opacity: 0.4; }
		60% { transform: scale(1.12); opacity: 1; }
		100% { transform: scale(1); }
	}
	@keyframes ls-shimmer {
		0% { background-position: 100% 0; }
		100% { background-position: -100% 0; }
	}
	.ls-motion-spin { animation: ls-spin 0.85s linear infinite; }
	.ls-motion-pulse { animation: ls-pulse-soft 1.8s ease-in-out infinite; }
	.ls-motion-ring { animation: ls-ring 2.4s ease-in-out infinite; transform-origin: top center; }
	.ls-motion-rise { animation: ls-fade-rise 0.35s ease-out both; }
	.ls-motion-check { animation: ls-check-pop 0.45s ease-out both; }
	.ls-motion-shimmer {
		background: linear-gradient(90deg, #eff6ff 0%, #dbeafe 40%, #eff6ff 80%);
		background-size: 200% 100%;
		animation: ls-shimmer 1.6s ease-in-out infinite;
		color: #2563eb;
	}
	.ls-icon-row-actions {
		display: flex;
		flex-wrap: wrap;
		gap: 0.45rem;
		align-items: center;
	}
	.ls-icon-btn {
		width: 2.1rem;
		height: 2.1rem;
		border-radius: 8px;
		border: 1px solid var(--ls-border);
		background: #fff;
		color: var(--ls-ink);
		display: inline-flex;
		align-items: center;
		justify-content: center;
		font-size: 1rem;
		cursor: pointer;
		transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease, transform 0.15s ease;
	}
	.ls-icon-btn:hover {
		background: var(--ls-blue-soft);
		border-color: var(--ls-blue-soft-border);
		color: #2563eb;
		transform: translateY(-1px);
	}
	.ls-icon-btn--danger:hover { background: #fee2e2; border-color: #fecaca; color: #dc2626; }
	.ls-icon-btn--success:hover { background: #dcfce7; border-color: #bbf7d0; color: #15803d; }

	/* —— Dropdown menus —— */
	.ls-dropdown {
		position: relative;
		display: inline-block;
	}
	.ls-dropdown__toggle {
		display: inline-flex;
		align-items: center;
		gap: 0.35rem;
	}
	.ls-dropdown__menu {
		position: absolute;
		z-index: 50;
		top: calc(100% + 4px);
		left: 0;
		min-width: 13.5rem;
		margin: 0;
		padding: 0.35rem;
		list-style: none;
		background: #fff;
		border: 1px solid var(--ls-border, #e2e8f0);
		border-radius: 10px;
		box-shadow: 0 12px 28px rgba(15, 23, 42, 0.14);
	}
	.ls-dropdown--right .ls-dropdown__menu {
		left: auto;
		right: 0;
	}
	.ls-dropdown__item {
		display: flex;
		align-items: center;
		gap: 0.5rem;
		width: 100%;
		border: none;
		background: transparent;
		border-radius: 7px;
		padding: 0.45rem 0.55rem;
		font-size: 0.78rem;
		font-weight: 500;
		color: var(--ls-ink, #1e293b);
		text-align: left;
		cursor: pointer;
	}
	.ls-dropdown__item .mdi {
		font-size: 1rem;
		color: #64748b;
		width: 1.1rem;
		text-align: center;
	}
	.ls-dropdown__item:hover {
		background: var(--ls-blue-soft, #eff6ff);
		color: #1e293b;
	}
	.ls-dropdown__item:hover .mdi { color: #2563eb; }
	.ls-dropdown__item--danger { color: #b91c1c; }
	.ls-dropdown__item--danger .mdi { color: #dc2626; }
	.ls-dropdown__item--danger:hover {
		background: #fee2e2;
		color: #991b1b;
	}
	.ls-dropdown__item--danger:hover .mdi { color: #b91c1c; }
	.ls-dropdown-demo-row {
		display: flex;
		flex-wrap: wrap;
		gap: 1rem;
		align-items: flex-start;
	}

	/* —— Typography —— */
	.ls-type-specimen {
		border: 1px solid var(--ls-border);
		border-radius: 12px;
		background: #fff;
		padding: 1rem 1.1rem;
	}
	.ls-type-row {
		display: grid;
		grid-template-columns: 6.5rem 1fr;
		gap: 0.75rem;
		align-items: baseline;
		padding: 0.55rem 0;
		border-bottom: 1px solid #f1f5f9;
	}
	.ls-type-row:last-child { border-bottom: none; }
	.ls-type-row__meta {
		font-size: 0.65rem;
		font-family: var(--ls-font-mono, "IBM Plex Mono", monospace);
		color: var(--ls-muted);
	}
	.ls-type-display { font-size: 1.75rem; font-weight: 700; letter-spacing: -0.02em; color: var(--ls-ink); margin: 0; }
	.ls-type-title { font-size: 1.15rem; font-weight: 700; color: var(--ls-ink); margin: 0; }
	.ls-type-subtitle { font-size: 0.95rem; font-weight: 600; color: var(--ls-ink); margin: 0; }
	.ls-type-body { font-size: 0.8125rem; font-weight: 400; line-height: 1.55; color: #334155; margin: 0; }
	.ls-type-label { font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--ls-muted); margin: 0; }
	.ls-type-caption { font-size: 0.7rem; font-weight: 400; color: var(--ls-muted); margin: 0; }
	.ls-type-mono { font-family: var(--ls-font-mono, "IBM Plex Mono", monospace); font-size: 0.78rem; color: var(--ls-ink); margin: 0; }
	.ls-type-accent { color: var(--ls-accent); }
	.ls-type-link { color: #2563eb; font-weight: 600; text-decoration: none; }

	/* —— Form grids (TRF-like) —— */
	.ls-form-grid {
		display: grid;
		gap: 0.75rem 1rem;
	}
	.ls-form-grid--2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
	.ls-form-grid--3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
	.ls-form-grid--4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
	.ls-form-grid--12 { grid-template-columns: repeat(12, minmax(0, 1fr)); }
	.ls-span-2 { grid-column: span 2; }
	.ls-span-3 { grid-column: span 3; }
	.ls-span-4 { grid-column: span 4; }
	.ls-span-6 { grid-column: span 6; }
	.ls-span-8 { grid-column: span 8; }
	.ls-span-12 { grid-column: span 12; }
	@media (max-width: 768px) {
		.ls-form-grid--2,
		.ls-form-grid--3,
		.ls-form-grid--4,
		.ls-form-grid--12 { grid-template-columns: 1fr; }
		.ls-span-2, .ls-span-3, .ls-span-4, .ls-span-6, .ls-span-8, .ls-span-12 { grid-column: span 1; }
	}
	.ls-form-panel {
		border: 1px solid var(--ls-border);
		border-radius: 12px;
		background: #fff;
		padding: 1rem;
	}
	.ls-form-panel__title {
		font-size: 0.85rem;
		font-weight: 700;
		margin: 0 0 0.85rem;
		color: var(--ls-ink);
		display: flex;
		align-items: center;
		gap: 0.4rem;
	}
	.ls-form-panel__title .mdi { color: #2563eb; }
	.ls-check-row, .ls-radio-row {
		display: flex;
		flex-wrap: wrap;
		gap: 0.65rem 1rem;
		padding: 0.35rem 0;
	}
	.ls-check, .ls-radio {
		display: inline-flex;
		align-items: center;
		gap: 0.4rem;
		font-size: 0.78rem;
		color: var(--ls-ink);
		cursor: pointer;
	}
	.ls-check input, .ls-radio input { accent-color: var(--ls-accent, #8b1e2d); }

	/* TRF option chips (checkbox / radio tiles) */
	.ls-option-grid {
		display: grid;
		gap: 0.35rem 0.4rem;
		width: 100%;
	}
	.ls-option-grid--transport {
		grid-template-columns: repeat(3, max-content);
		justify-content: start;
		width: max-content;
		max-width: 100%;
	}
	.ls-option-grid--method,
	.ls-option-grid--apparatus,
	.ls-option-grid--3 {
		grid-template-columns: repeat(3, minmax(0, 1fr));
	}
	.ls-option-grid--auto {
		grid-template-columns: repeat(auto-fill, minmax(8.5rem, 1fr));
	}
	.ls-option-chip {
		display: inline-flex;
		align-items: center;
		gap: 0.4rem;
		margin: 0;
		padding: 0.4rem 0.55rem;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		background: #f8fafc;
		cursor: pointer;
		font-size: 0.75rem;
		color: var(--ls-ink);
		line-height: 1.2;
		user-select: none;
		transition: border-color 0.12s ease, background 0.12s ease;
	}
	.ls-option-chip:hover {
		border-color: var(--ls-blue-soft-border, #dbeafe);
		background: var(--ls-blue-soft, #eff6ff);
	}
	.ls-option-chip:has(.ls-option-chip__input:checked) {
		border-color: color-mix(in srgb, var(--ls-accent, #8b1e2d) 35%, #e2e8f0);
		background: color-mix(in srgb, var(--ls-accent, #8b1e2d) 8%, #ffffff);
	}
	.ls-option-chip__input {
		flex-shrink: 0;
		margin: 0;
		accent-color: var(--ls-accent, #8b1e2d);
		width: 0.9rem;
		height: 0.9rem;
	}
	.ls-option-chip__label {
		font-weight: 500;
		text-align: left;
	}
	.ls-option-grid--readonly {
		pointer-events: none;
	}
	.ls-option-chip.is-muted {
		opacity: 0.45;
	}
	.ls-field__label .mdi {
		margin-right: 0.25rem;
		color: #64748b;
		font-size: 0.95em;
		vertical-align: -0.05em;
	}
	.ls-view-tag-row {
		display: flex;
		flex-wrap: wrap;
		gap: 0.35rem;
		padding: 0.35rem 0;
	}
	.ls-view-tag {
		display: inline-flex;
		align-items: center;
		padding: 0.25rem 0.55rem;
		border-radius: 999px;
		background: #eff6ff;
		border: 1px solid #bfdbfe;
		color: #1e3a5f;
		font-size: 0.75rem;
		font-weight: 500;
		line-height: 1.2;
	}
	@media (max-width: 767.98px) {
		.ls-option-grid--method,
		.ls-option-grid--apparatus,
		.ls-option-grid--3 {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
		.ls-option-grid--transport {
			grid-template-columns: repeat(2, max-content);
		}
	}

	.ls-textarea {
		width: 100%;
		min-height: 4.5rem;
		border: 1px solid var(--ls-border);
		border-radius: 8px;
		padding: 0.45rem 0.65rem;
		font-size: 0.78rem;
		resize: vertical;
		color: var(--ls-ink);
	}
	.ls-textarea:focus {
		outline: none;
		border-color: var(--ls-blue-focus);
		box-shadow: 0 0 0 3px color-mix(in srgb, var(--ls-blue-soft-ring) 55%, transparent);
	}
	.ls-field-with-icon .ls-field__control { padding-left: 0; }
	.ls-field-with-icon .ls-field__affix--prefix { color: #94a3b8; }

	/* Gallery chrome */
	.ls-gallery-section {
		margin-bottom: 0.75rem;
		border: 1px solid var(--ls-border, #e2e8f0);
		border-radius: 10px;
		background: #fff;
		overflow: clip;
	}
	.ls-gallery-section > summary {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 0.75rem;
		padding: 0.75rem 0.9rem;
		cursor: pointer;
		list-style: none;
		user-select: none;
	}
	.ls-gallery-section > summary::-webkit-details-marker { display: none; }
	.ls-gallery-section > summary::after {
		content: "▾";
		font-size: 0.85rem;
		color: #94a3b8;
		flex-shrink: 0;
		line-height: 1;
	}
	.ls-gallery-section[open] > summary::after {
		content: "▴";
	}
	.ls-gallery-section > summary:hover {
		background: #f8fafc;
	}
	.ls-gallery-section h3 {
		font-size: 0.95rem;
		font-weight: 700;
		color: var(--ls-ink);
		margin: 0;
	}
	.ls-gallery-section p.lead-muted {
		font-size: 0.75rem;
		color: var(--ls-muted);
		margin: 0.2rem 0 0;
	}
	.ls-gallery-section__body {
		padding: 0 0.9rem 0.9rem;
		border-top: 1px solid #f1f5f9;
	}
	.ls-gallery-toc {
		display: flex;
		flex-wrap: wrap;
		gap: 0.35rem;
		margin: 0 0 1rem;
	}
	.ls-gallery-toc a {
		font-size: 0.7rem;
		font-weight: 600;
		padding: 0.25rem 0.55rem;
		border-radius: 999px;
		border: 1px solid #e2e8f0;
		background: #fff;
		color: #334155;
		text-decoration: none;
	}
	.ls-gallery-toc a:hover {
		background: var(--ls-blue-soft, #eff6ff);
		border-color: var(--ls-blue-soft-border, #dbeafe);
		color: #1d4ed8;
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

	.ls-gallery-demo {
		margin-bottom: 1.15rem;
		padding: 0.75rem 0.85rem 0.35rem;
		border: 1px dashed #cbd5e1;
		border-radius: 10px;
		background: #fff;
	}
	.ls-gallery-include {
		display: block;
		margin: 0 0 0.65rem;
		padding: 0.35rem 0.5rem;
		border-radius: 6px;
		background: #f1f5f9;
		border: 1px solid #e2e8f0;
		color: #0f172a;
		font-size: 0.72rem;
		font-family: var(--ls-font-mono, "IBM Plex Mono", ui-monospace, monospace);
		line-height: 1.35;
		word-break: break-all;
		user-select: all;
	}
	.ls-gallery-include strong {
		font-weight: 700;
		color: var(--ls-accent, #8b1e2d);
	}
</style>
