{{--
  ls-quotation-overview-styles — shared LS chrome for quotation index / manager / doc.
--}}
<style>
	.ls-quotation-shell .ls-quotation-header-card,
	.ls-quotation-shell .ls-quotation-panel,
	.ls-quotation-shell .quotation-stage-tabs-card {
		border: 1px solid var(--ls-border, #e2e8f0);
		border-radius: 12px;
		background: #fff;
		box-shadow: 0 1px 2px rgb(15 23 42 / 0.04);
	}

	.ls-quotation-shell .quotation-stage-tabs {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
	}

	.ls-quotation-shell .quotation-stage-tab {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		padding: 0.5rem 0.9rem;
		border-radius: 8px;
		border: 1px solid var(--ls-border, #e2e8f0);
		background: #fff;
		color: var(--ls-muted, #64748b);
		font-size: 0.8125rem;
		font-weight: 600;
		text-decoration: none;
		cursor: pointer;
		transition: background 0.18s ease, border-color 0.18s ease, color 0.18s ease, transform 0.18s ease;
	}

	.ls-quotation-shell .quotation-stage-tab:hover {
		border-color: var(--color-primary-border-soft, #e2b4b4);
		color: var(--color-primary, #6D0A0E);
		text-decoration: none;
	}

	.ls-quotation-shell .quotation-stage-tab.is-active {
		border-color: var(--color-primary, #6D0A0E);
		color: var(--color-primary, #6D0A0E);
		background: var(--color-primary-soft, #f8ecec);
		box-shadow: 0 1px 2px rgb(0 0 0 / 0.05);
	}

	.ls-quotation-shell .quotation-stage-tab--complete.is-active {
		border-color: #15803d;
		color: #15803d;
		background: #ecfdf5;
	}

	.ls-quotation-shell .quotation-stage-tab__count {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-width: 1.35rem;
		padding: 2px 6px;
		border-radius: 999px;
		font-size: 0.6875rem;
		font-weight: 700;
		background: #e2e8f0;
		color: #475569;
	}

	.ls-quotation-shell .quotation-stage-tab.is-active .quotation-stage-tab__count {
		background: rgba(109, 10, 14, 0.12);
		color: var(--color-primary, #6D0A0E);
	}

	.ls-quotation-shell .quotation-stage-tab--complete.is-active .quotation-stage-tab__count {
		background: #dcfce7;
		color: #166534;
	}

	.ls-quotation-shell .quotation-type-chip,
	.ls-quotation-shell .quotation-status-chip {
		display: inline-flex;
		align-items: center;
		padding: 2px 8px;
		border-radius: 999px;
		font-size: 0.75rem;
		font-weight: 600;
		border: 1px solid transparent;
		white-space: nowrap;
	}

	.ls-quotation-shell .quotation-type-chip--analysis {
		background: #eff6ff;
		color: #1d4ed8;
		border-color: #bfdbfe;
	}

	.ls-quotation-shell .quotation-type-chip--general {
		background: #ecfeff;
		color: #0e7490;
		border-color: #a5f3fc;
	}

	.ls-quotation-shell .quotation-status-chip--prep {
		background: #fffbeb;
		color: #b45309;
		border-color: #fde68a;
	}

	.ls-quotation-shell .quotation-status-chip--approval {
		background: #eff6ff;
		color: #1d4ed8;
		border-color: #bfdbfe;
	}

	.ls-quotation-shell .quotation-status-chip--complete {
		background: #ecfdf5;
		color: #15803d;
		border-color: #bbf7d0;
	}

	.ls-quotation-shell .quotation-actions-cell {
		gap: 0.45rem;
		flex-wrap: nowrap;
		white-space: nowrap;
	}

	.ls-quotation-shell .rm-act-btn {
		border-radius: 7px;
		padding: 4px 8px;
		margin-right: 0;
		font-size: 12px;
		border: 1px solid transparent;
		background: #fff;
		transition: all 0.2s ease;
		text-decoration: none;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		line-height: 1;
		box-shadow: none;
		cursor: pointer;
	}

	.ls-quotation-shell .rm-act-btn--open,
	.ls-quotation-shell .rm-act-btn--view {
		border-color: #bbf7d0;
		color: #15803d;
		background: #f0fdf4;
	}

	.ls-quotation-shell .rm-act-btn--edit {
		border-color: #bfdbfe;
		color: #1d4ed8;
		background: #eff6ff;
	}

	.ls-quotation-shell .rm-act-btn--clone {
		border-color: #cbd5e1;
		color: #475569;
		background: #f8fafc;
	}

	.ls-quotation-shell .rm-act-btn--enquiry {
		border-color: #fed7aa;
		color: #c2410c;
		background: #fff7ed;
	}

	.ls-quotation-shell .quotation-list-panel {
		transition: opacity 0.18s ease, transform 0.18s ease;
	}

	.ls-quotation-shell .quotation-list-panel.is-loading {
		opacity: 0.45;
		transform: translateY(4px);
		pointer-events: none;
	}

	.ls-quotation-shell .ls-quotation-toolbar {
		display: flex;
		flex-wrap: wrap;
		gap: 0.75rem;
		align-items: flex-end;
	}

	.ls-quotation-shell .ls-quotation-toolbar .ls-field {
		margin-bottom: 0;
		min-width: 140px;
	}

	.ls-quotation-shell .ls-quotation-panel {
		overflow: visible;
	}

	.ls-quotation-shell .ls-quotation-panel > .card-header {
		overflow: visible;
	}

	.ls-quotation-shell .ls-quotation-list-header {
		display: flex;
		flex-direction: column;
		gap: 0.75rem;
		padding-bottom: 0.85rem;
		overflow: visible;
	}

	.ls-quotation-shell .ls-quotation-list-header__top {
		display: flex;
		flex-direction: column;
		align-items: stretch;
		gap: 0.75rem;
	}

	.ls-quotation-shell .ls-quotation-list-header__top .card-title {
		flex: 0 0 auto;
	}

	.ls-quotation-shell .ls-quotation-list-header__search {
		position: relative;
		width: 100%;
		max-width: 42rem;
		min-width: 0;
	}

	.ls-quotation-shell .ls-quotation-list-footer {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
		padding: 0.85rem 0.25rem 0.15rem;
		margin-top: 0.75rem;
		border-top: 1px solid #f1f5f9;
	}

	.ls-quotation-shell .ls-quotation-list-footer__meta {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: 12px;
	}

	.ls-quotation-shell .ls-quotation-list-footer__per-page {
		display: inline-flex;
		align-items: center;
		gap: 6px;
	}

	.ls-quotation-shell .ls-quotation-list-footer__per-page select {
		width: auto;
		min-width: 4.25rem;
	}

	.ls-quotation-shell .ls-quotation-list-footer__pages {
		margin-left: auto;
	}

	.ls-quotation-shell .ls-quotation-list-footer__pages .pagination {
		margin-bottom: 0;
	}

	.ls-quotation-shell .ls-quotation-search-toolbar__row {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: 0.5rem;
	}

	.ls-quotation-shell .ls-quotation-search-toolbar__search {
		display: inline-flex;
		align-items: center;
		gap: 0.45rem;
		width: 100%;
		max-width: 100%;
	}

	.ls-quotation-shell .ls-quotation-search-toolbar__search .ls-search-bar__field {
		flex: 1 1 auto;
		min-width: 0;
		width: 100%;
	}

	.ls-quotation-shell .ls-quotation-search-toolbar__search .ls-search-bar__field input {
		width: 100%;
		min-width: 0;
	}

	.ls-quotation-shell .ls-search-bar__filter {
		position: relative;
	}

	.ls-quotation-shell .ls-search-bar__filter.is-open {
		box-shadow: 0 0 0 3px color-mix(in srgb, var(--ls-blue-soft-ring, #93c5fd) 45%, transparent);
	}

	.ls-quotation-shell .ls-quotation-search-toolbar__badge {
		position: absolute;
		top: -5px;
		right: -5px;
		min-width: 16px;
		height: 16px;
		padding: 0 4px;
		border-radius: 999px;
		background: var(--color-primary, #6D0A0E);
		color: #fff;
		font-size: 0.625rem;
		font-weight: 700;
		line-height: 16px;
		text-align: center;
	}

	.ls-quotation-shell .ls-quotation-search-toolbar__clear {
		display: inline-flex;
		align-items: center;
		gap: 4px;
		border: 0;
		background: transparent;
		color: var(--ls-muted, #64748b);
		font-size: 0.75rem;
		font-weight: 600;
		padding: 0.25rem 0.4rem;
		cursor: pointer;
	}

	.ls-quotation-shell .ls-quotation-search-toolbar__clear:hover {
		color: var(--color-primary, #6D0A0E);
	}

	.ls-quotation-shell .ls-quotation-filter-panel {
		position: absolute;
		z-index: 40;
		top: calc(100% + 8px);
		left: 0;
		right: auto;
		width: min(100vw - 2rem, 40rem);
		min-width: min(100%, 28rem);
		padding: 0.95rem 1rem;
		border: 1px solid var(--ls-border, #e2e8f0);
		border-radius: 12px;
		background: #fff;
		box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
	}

	.ls-quotation-shell .ls-quotation-filter-panel__head {
		display: flex;
		align-items: center;
		justify-content: space-between;
		margin-bottom: 0.75rem;
		font-size: 0.8125rem;
		font-weight: 700;
		color: #1e293b;
	}

	.ls-quotation-shell .ls-quotation-filter-panel__close {
		border: 0;
		background: transparent;
		color: #94a3b8;
		padding: 0;
		line-height: 1;
		cursor: pointer;
	}

	.ls-quotation-shell .ls-quotation-filter-panel__grid {
		display: grid;
		grid-template-columns: 1fr 1fr 1fr;
		gap: 0.65rem 0.85rem;
	}

	@media (max-width: 640px) {
		.ls-quotation-shell .ls-quotation-filter-panel {
			width: min(100vw - 1.5rem, 100%);
			min-width: 0;
		}

		.ls-quotation-shell .ls-quotation-filter-panel__grid {
			grid-template-columns: 1fr 1fr;
		}
	}

	.ls-quotation-shell .ls-quotation-filter-panel__grid .ls-field:first-child {
		grid-column: 1 / -1;
	}

	.ls-quotation-shell .ls-quotation-filter-panel__grid .ls-field__control {
		border: 1px solid var(--ls-border, #e2e8f0);
		border-radius: 8px;
		background: #fff;
		overflow: hidden;
	}

	.ls-quotation-shell .ls-quotation-filter-panel__grid .ls-field__input {
		width: 100%;
		border: 0;
		outline: none;
		background: transparent;
		padding: 0.35rem 0.55rem;
		font-size: 0.78rem;
		color: #0f172a;
		min-height: 32px;
	}

	.ls-quotation-shell .ls-quotation-filter-panel__foot {
		display: flex;
		justify-content: flex-end;
		gap: 0.5rem;
		margin-top: 0.85rem;
		padding-top: 0.75rem;
		border-top: 1px solid #f1f5f9;
	}

	.ls-quotation-shell .ls-quotation-filter-panel--enter {
		transition: opacity 0.15s ease, transform 0.15s ease;
	}

	.ls-quotation-shell .ls-quotation-filter-panel--enter-start,
	.ls-quotation-shell .ls-quotation-filter-panel--leave-end {
		opacity: 0;
		transform: translateY(-4px);
	}

	.ls-quotation-shell .ls-quotation-filter-panel--enter-end,
	.ls-quotation-shell .ls-quotation-filter-panel--leave-start {
		opacity: 1;
		transform: translateY(0);
	}

	.ls-quotation-shell .ls-quotation-filter-panel--leave {
		transition: opacity 0.12s ease, transform 0.12s ease;
	}

	.ls-quotation-shell .ls-quotation-table-scroll {
		width: 100%;
		max-width: 100%;
		overflow-x: auto;
		overflow-y: visible;
		-webkit-overflow-scrolling: touch;
		border: 1px solid var(--ls-border, #e2e8f0);
		border-radius: 10px;
		background: #fff;
	}

	.ls-quotation-shell .ls-quotation-table-scroll > .ls-table {
		min-width: 1100px;
		width: 100%;
	}

	.ls-quotation-shell .ls-quotation-table-scroll .ls-table thead th,
	.ls-quotation-shell .ls-quotation-table-scroll .ls-table tbody td {
		white-space: nowrap;
	}

	.ls-quotation-shell .ls-quotation-sort-btn {
		display: inline-flex;
		align-items: center;
		gap: 4px;
		border: 0;
		background: transparent;
		padding: 0;
		font: inherit;
		font-weight: 700;
		color: inherit;
		cursor: pointer;
	}

	.ls-quotation-shell .ls-quotation-sort-btn .mdi {
		font-size: 0.85rem;
		opacity: 0.55;
	}

	.ls-quotation-shell .ls-quotation-sort-btn.is-active .mdi {
		opacity: 1;
		color: var(--color-primary, #6D0A0E);
	}

	.ls-quotation-shell .quotation-quote-number {
		font-weight: 700;
		color: var(--color-primary, #6D0A0E);
	}

	.ls-quotation-doc-chrome {
		border: 1px solid var(--ls-border, #e2e8f0);
		border-radius: 12px;
		background: #fff;
		padding: 1rem 1.25rem;
		margin-bottom: 1rem;
		box-shadow: 0 1px 2px rgb(15 23 42 / 0.04);
	}

	.ls-quotation-doc-chrome__title {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: 0.5rem 0.75rem;
		margin: 0;
		font-size: 1.15rem;
		font-weight: 700;
		color: #1e293b;
	}

	.ls-quotation-doc-chrome__meta {
		display: flex;
		flex-wrap: wrap;
		gap: 0.4rem;
		align-items: center;
		margin-top: 0.65rem;
	}

	.ls-quotation-doc-chrome__actions {
		display: flex;
		flex-wrap: wrap;
		gap: 0.5rem;
		align-items: center;
		margin-left: auto;
	}

	.ls-quotation-doc-chrome .ls-btn-ghost {
		border: 1px solid #e2e8f0;
		background: #fff;
		color: #334155;
		border-radius: 8px;
		padding: 0.35rem 0.75rem;
		font-size: 0.8125rem;
		font-weight: 600;
	}

	.quotation-kpi-card button.quotation-kpi-hit {
		display: block;
		width: 100%;
		border: 0;
		background: transparent;
		padding: 0;
		text-align: left;
		cursor: pointer;
		color: inherit;
	}
</style>
