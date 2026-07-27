@extends('layouts.planner.layout.app', ['select2' => true])

@php
    $fillSamplingFormsLabel = __('planner.fill_sampling_forms');
    if ($fillSamplingFormsLabel === 'planner.fill_sampling_forms') {
        $fillSamplingFormsLabel = 'Fill Sampling Forms';
    }
@endphp

@section('title2')
<title>{{ $fillSamplingFormsLabel }} | System Planner</title>
@include('layouts.rft.partials.rft-theme-styles')
<style>
	.planner-fsf-page {
		--fsf-ink: #1f2937;
		--fsf-muted: #6b7280;
		--fsf-line: #e8ecf1;
		--fsf-surface: #ffffff;
		--fsf-soft: #f7f8fa;
		--fsf-accent: var(--color-primary, #8a1a1f);
		--fsf-accent-soft: var(--color-primary-soft, #f3e8e9);
		--fsf-accent-border: var(--color-primary-highlight, #e2b8bb);
		--workflow-accent: var(--fsf-accent);
		--workflow-accent-soft: var(--fsf-accent-soft);
		--workflow-accent-border: var(--fsf-accent-border);
		--rft-support: var(--fsf-accent);
		--rft-support-hover: #6f1418;
		--rft-support-deep: var(--fsf-accent);
		--rft-support-soft: var(--fsf-accent-soft);
		--rft-support-border: var(--fsf-accent-border);
		--rft-support-rgb: 138, 26, 31;
		--rft-support-text: #fff;
		max-width: 1180px;
		margin-left: auto;
		margin-right: auto;
	}

	.planner-fsf-page .rft-overview-section {
		background: var(--fsf-surface);
		border: 1px solid var(--fsf-line);
		border-radius: 14px;
		padding: 16px 18px 8px;
		margin-bottom: 1rem !important;
		box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
	}

	.planner-fsf-page .workflow-board-section-label {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
		font-size: 0.72rem;
		letter-spacing: 0.06em;
		text-transform: uppercase;
		font-weight: 700;
		color: var(--fsf-muted);
		margin-bottom: 0.85rem !important;
	}

	.planner-fsf-page .workflow-board-section-label .mdi {
		color: var(--fsf-accent);
	}

	.planner-fsf-page .fsf-form-grid {
		display: grid;
		grid-template-columns: repeat(4, minmax(0, 1fr));
		gap: 12px;
		margin-bottom: 8px;
	}

	.planner-fsf-page .rft-form-type-card {
		display: flex;
		flex-direction: column;
		height: 100%;
		background: var(--fsf-soft);
		border: 1px solid var(--fsf-line);
		border-radius: 12px;
		padding: 14px;
		box-shadow: none;
		transition: border-color 0.15s ease, box-shadow 0.15s ease, transform 0.15s ease, background 0.15s ease;
	}

	.planner-fsf-page .rft-form-type-card:hover {
		background: #fff;
		border-color: var(--fsf-accent-border);
		box-shadow: 0 8px 20px rgba(138, 26, 31, 0.08);
		transform: translateY(-1px);
	}

	.planner-fsf-page .rft-form-type-card-header {
		display: flex;
		align-items: flex-start;
		gap: 10px;
		margin-bottom: 8px;
	}

	.planner-fsf-page .rft-form-type-card-icon {
		flex-shrink: 0;
		width: 38px;
		height: 38px;
		border-radius: 10px;
		display: flex;
		align-items: center;
		justify-content: center;
		background: var(--fsf-accent-soft);
		color: var(--fsf-accent);
		font-size: 1.15rem;
		position: relative;
	}

	.planner-fsf-page .rft-form-type-card-icon::after {
		display: none !important;
	}

	.planner-fsf-page .rft-form-type-card h6 {
		font-size: 0.84rem;
		font-weight: 700;
		color: var(--fsf-ink);
		line-height: 1.3;
		white-space: normal;
	}

	.planner-fsf-page .rft-form-type-card .text-muted.small {
		display: inline-block;
		margin-top: 2px;
		font-size: 0.7rem;
		font-weight: 600;
		letter-spacing: 0.02em;
		color: var(--fsf-accent) !important;
	}

	.planner-fsf-page .rft-form-type-card-desc {
		flex: 1 1 auto;
		font-size: 0.75rem !important;
		color: var(--fsf-muted) !important;
		line-height: 1.4;
		margin-bottom: 10px !important;
		min-height: 0 !important;
		display: -webkit-box;
		-webkit-line-clamp: 2;
		-webkit-box-orient: vertical;
		overflow: hidden;
	}

	.planner-fsf-page .rft-form-type-card-meta {
		display: flex;
		flex-wrap: wrap;
		gap: 6px 10px;
		font-size: 0.7rem !important;
		color: #64748b;
		margin-bottom: 10px !important;
	}

	.planner-fsf-page .rft-form-type-card-actions {
		margin-top: auto;
		padding-top: 10px !important;
		border-top: 1px solid #eef1f5;
		justify-content: stretch !important;
	}

	.planner-fsf-page .rft-form-type-card-actions .btn-primary {
		width: 100%;
		background: var(--fsf-accent);
		border-color: var(--fsf-accent);
		font-weight: 600;
	}

	.planner-fsf-page .rft-form-type-card-actions .btn-primary:hover {
		filter: brightness(0.95);
	}

	.planner-fsf-page .workflow-board-panel {
		border: 1px solid var(--fsf-line);
		border-radius: 14px;
		overflow: hidden;
		box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
		background: var(--fsf-surface);
	}

	.planner-fsf-page .workflow-board-panel-header {
		padding: 14px 18px;
		background: linear-gradient(180deg, #fcfcfd 0%, #fff 100%);
		border-bottom: 1px solid var(--fsf-line);
	}

	.planner-fsf-page .workflow-board-panel-header h5 {
		font-size: 1rem;
		font-weight: 700;
		color: var(--fsf-ink);
		margin: 0;
	}

	.planner-fsf-page .workflow-board-panel-header h5 .mdi {
		color: var(--fsf-accent);
	}

	.planner-fsf-page .workflow-board-panel-header .text-muted {
		color: var(--fsf-muted) !important;
	}

	.planner-fsf-page .workflow-board-panel-body {
		padding: 14px 18px 18px;
	}

	.planner-fsf-page .rft-segmented {
		background: var(--fsf-soft);
		border: 1px solid var(--fsf-line);
		border-radius: 10px;
		padding: 3px;
		display: inline-flex;
		gap: 2px;
		margin-bottom: 12px;
	}

	.planner-fsf-page .rft-segmented__btn {
		border: none;
		background: transparent;
		border-radius: 8px;
		padding: 7px 14px;
		font-size: 0.78rem;
		font-weight: 600;
		color: var(--fsf-muted);
	}

	.planner-fsf-page .rft-segmented__btn.is-active {
		background: #fff;
		color: var(--fsf-accent);
		box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
	}

	.planner-fsf-page .rft-toolbar {
		display: flex;
		gap: 8px;
		margin-bottom: 12px;
	}

	.planner-fsf-page .rft-toolbar .form-control {
		flex: 1 1 auto;
		background: var(--fsf-soft);
		border-color: var(--fsf-line);
	}

	.planner-fsf-page .rft-instance-list {
		gap: 8px;
	}

	.planner-fsf-page .rft-instance-card {
		grid-template-columns: 1fr auto auto;
		align-items: center;
		padding: 12px 14px;
		border-radius: 11px;
		background: var(--fsf-soft);
		border-color: var(--fsf-line);
	}

	.planner-fsf-page .rft-instance-card:hover {
		background: #fff;
		border-color: var(--fsf-accent-border);
	}

	.planner-fsf-page .rft-instance-card__number {
		min-width: 52px;
		height: 44px;
		padding: 0 8px;
		border-radius: 10px;
		background: #fff;
		border: 1px solid var(--fsf-line);
		color: var(--fsf-accent);
		font-weight: 700;
		font-size: 0.72rem;
		letter-spacing: 0.02em;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		text-align: center;
		line-height: 1.15;
	}

	.planner-fsf-page .rft-instance-card__title {
		font-size: 0.88rem;
		font-weight: 700;
		color: var(--fsf-ink);
	}

	.planner-fsf-page .rft-instance-card__subtitle {
		font-size: 0.75rem;
		color: var(--fsf-muted);
	}

	.planner-fsf-page .rft-instance-card__meta {
		justify-content: flex-end;
		gap: 8px;
	}

	.planner-fsf-page .rft-status-chip--draft {
		background: #fff8e8;
		color: #856404;
		border: 1px solid #f0e0b2;
	}

	.planner-fsf-page .rft-status-chip--submitted {
		background: #edf7ee;
		color: #1b5e20;
		border: 1px solid #c9e6cb;
	}

	.planner-fsf-page .rft-status-chip--partial {
		background: #eff6ff;
		color: #1d4ed8;
		border: 1px solid #bfdbfe;
	}

	.planner-fsf-page .rft-instance-card__actions .btn-primary {
		background: var(--fsf-accent);
		border-color: var(--fsf-accent);
		font-weight: 600;
		min-width: 96px;
	}

	.planner-fsf-page .rft-touch-bar {
		display: none;
	}

	.planner-fsf-page .workflow-empty-state {
		padding: 2.25rem 1rem !important;
		background: var(--fsf-soft);
		border: 1px dashed var(--fsf-line);
		border-radius: 12px;
	}

	.planner-fsf-page .fsf-panel-link {
		font-size: 0.78rem;
		font-weight: 600;
		color: var(--fsf-accent);
		text-decoration: none;
	}

	.planner-fsf-page .fsf-panel-link:hover {
		text-decoration: underline;
		color: var(--fsf-accent);
	}

	@media (max-width: 1199.98px) {
		.planner-fsf-page .fsf-form-grid {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
	}

	@media (max-width: 575.98px) {
		.planner-fsf-page .fsf-form-grid {
			grid-template-columns: 1fr;
		}

		.planner-fsf-page .rft-instance-card {
			grid-template-columns: 1fr;
			gap: 10px;
		}

		.planner-fsf-page .rft-instance-card__meta,
		.planner-fsf-page .rft-instance-card__actions {
			justify-content: flex-start;
		}

		.planner-fsf-page .rft-toolbar {
			flex-direction: column;
		}
	}
</style>
@endsection

@section('content2')
<main class="lab-surface-theme ls-admin-page" data-ls-type="plex">
    @php
        $items = [
            [
                'link' => route('system-planner.dashboard'),
                'name' => __('planner.module_name') === 'planner.module_name' ? 'System Planner' : __('planner.module_name'),
                'icon' => null,
            ],
            [
                'link' => route('system-planner.fill-sampling-forms'),
                'name' => $fillSamplingFormsLabel,
                'icon' => null,
            ],
        ];
    @endphp
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="container-fluid workflow-board-page rft-page-shell rft-theme planner-fsf-page px-3 px-md-4 pt-2 pb-4">
        @if ($errors->any())
            <div class="alert alert-danger mt-2">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success') || session('error'))
            <div class="alert alert-{{ session('success') ? 'success' : 'danger' }} mt-2">
                {{ session('success') ?? session('error') }}
            </div>
        @endif

        @livewire('sampleworkflow.receive-sample-request', [
            'pageMode' => true,
            'plannerMode' => true,
        ], key('planner-fill-sampling-forms'))
    </div>
</main>
@endsection

@section('script2')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
@stack('script2')
@endsection
