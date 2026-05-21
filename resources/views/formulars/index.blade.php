@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>Worksheet Engine | Lab Management</title>
@include('layouts.lab.partials.lab-panel-theme-styles')
<style type="text/css">
	/* Original palette as left accent only (no fill) */
	.lab-panel-theme .formulars-stat-panel {
		margin-bottom: 0;
		border: 1px solid var(--workflow-border) !important;
		border-left: 4px solid var(--formulars-accent, #64748b) !important;
	}
	.lab-panel-theme .formulars-stat-panel .workflow-board-panel-body {
		padding: 1rem 1.25rem;
	}
	.lab-panel-theme .formulars-stat-panel.formulars-accent-success { --formulars-accent: #28a745; }
	.lab-panel-theme .formulars-stat-panel.formulars-accent-purple { --formulars-accent: #6f42c1; }
	.lab-panel-theme .formulars-stat-panel.formulars-accent-info { --formulars-accent: #17a2b8; }
	.lab-panel-theme .formulars-stat-panel.formulars-accent-warning { --formulars-accent: #ffc107; }
	.lab-panel-theme .formulars-stat-number {
		font-size: 1.35rem;
		font-weight: 700;
		color: #334155;
		line-height: 1.2;
	}
	.lab-panel-theme .formulars-stat-label {
		font-size: 0.72rem;
		font-weight: 600;
		text-transform: uppercase;
		letter-spacing: 0.04em;
		color: #64748b;
		margin-top: 0.35rem;
	}
	.lab-panel-theme .formulars-stat-icon {
		font-size: 1.75rem;
		margin-top: 0.5rem;
		color: var(--formulars-accent, #94a3b8);
		opacity: 0.9;
	}
	/* Link tiles: neutral card frame; color only on icons */
	.lab-panel-theme .formulars-module-tile {
		display: block;
		background: #fff;
		border: 1px solid var(--workflow-border);
		border-radius: 10px;
		padding: 1.35rem 1rem;
		text-align: center;
		text-decoration: none !important;
		color: inherit;
		height: 100%;
		transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
	}
	.lab-panel-theme .formulars-module-tile.tile-accent-primary { --tile-accent: #3b5fc0; }
	.lab-panel-theme .formulars-module-tile.tile-accent-success { --tile-accent: #28a745; }
	.lab-panel-theme .formulars-module-tile.tile-accent-info { --tile-accent: #17a2b8; }
	.lab-panel-theme .formulars-module-tile.tile-accent-purple { --tile-accent: #6f42c1; }
	.lab-panel-theme .formulars-module-tile.tile-accent-warning { --tile-accent: #ffc107; }
	.lab-panel-theme .formulars-module-tile:hover {
		border-color: #dfe3e8;
		transform: translateY(-2px);
		box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
	}
	.lab-panel-theme .formulars-module-tile .tile-icon {
		font-size: 2.5rem;
		color: var(--tile-accent, var(--workflow-accent));
		margin-bottom: 0.75rem;
		display: block;
		opacity: 0.92;
	}
	.lab-panel-theme .formulars-module-tile .tile-title {
		font-weight: 600;
		color: #334155;
		font-size: 0.95rem;
		margin-bottom: 0.35rem;
	}
	.lab-panel-theme .formulars-module-tile .tile-desc {
		font-size: 0.82rem;
		color: #64748b;
		margin-bottom: 0;
		line-height: 1.35;
	}
</style>
@endsection

@section('content2')
<main>
	<?php
	$items = [
		[
			'link' => route('lab-home'),
			'name' => 'Lab',
			'icon' => null,
		],
		[
			'link' => null,
			'name' => 'Worksheet Engine',
			'icon' => null,
		],
	];
	?>
	<div class="px-4 lab-panel-theme">
	<style>.breadcrumb-container { margin-left: 0 !important; margin-right: 0 !important; margin-top: 12px; margin-bottom: 12px; }</style>
	<x-bread-crumb :items="$items"></x-bread-crumb>

	<div class="container-fluid px-0">
		<div class="workflow-board-panel mb-3">
			<div class="workflow-board-panel-header">
				<h5>
					<i class="mdi mdi-calculator"></i>
					Worksheet engine
				</h5>
			</div>
			<div class="workflow-board-panel-body flush-top">
				<p class="text-muted mb-0" style="font-size: 0.9rem;">
					Create and manage formula workflows, lookups, and procedure worksheets for laboratory calculations.
				</p>
			</div>
		</div>

		<div class="row mb-3">
			<div class="col-6 col-lg-3 mb-3">
				<div class="workflow-board-panel formulars-stat-panel formulars-accent-success">
					<div class="workflow-board-panel-body text-center">
						<div class="formulars-stat-number">{{ $stats['activeFormulas'] }}</div>
						<div class="formulars-stat-label">Active formulas</div>
						<i class="mdi mdi-check-circle-outline formulars-stat-icon"></i>
					</div>
				</div>
			</div>
			<div class="col-6 col-lg-3 mb-3">
				<div class="workflow-board-panel formulars-stat-panel formulars-accent-purple">
					<div class="workflow-board-panel-body text-center">
						<div class="formulars-stat-number">{{ $stats['methodSequences'] }}</div>
						<div class="formulars-stat-label">Method sequences</div>
						<i class="mdi mdi-chart-timeline formulars-stat-icon"></i>
					</div>
				</div>
			</div>
			<div class="col-6 col-lg-3 mb-3">
				<div class="workflow-board-panel formulars-stat-panel formulars-accent-info">
					<div class="workflow-board-panel-body text-center">
						<div class="formulars-stat-number">{{ $stats['executions'] }}</div>
						<div class="formulars-stat-label">Saved executions</div>
						<i class="mdi mdi-play-circle-outline formulars-stat-icon"></i>
					</div>
				</div>
			</div>
			<div class="col-6 col-lg-3 mb-3">
				<div class="workflow-board-panel formulars-stat-panel formulars-accent-warning">
					<div class="workflow-board-panel-body text-center">
						<div class="formulars-stat-number">{{ $stats['lookupTables'] }}</div>
						<div class="formulars-stat-label">Lookup tables</div>
						<i class="mdi mdi-table formulars-stat-icon"></i>
					</div>
				</div>
			</div>
		</div>

		<div class="workflow-board-panel">
			<div class="workflow-board-panel-header">
				<h5>
					<i class="mdi mdi-view-grid-outline"></i>
					Workspace modules
				</h5>
			</div>
			<div class="workflow-board-panel-body flush-top">
				<div class="row">
					<div class="col-md-4 col-lg-3 mb-3">
						<a href="{{ route('formulars.manage') }}" class="formulars-module-tile tile-accent-primary">
							<i class="mdi mdi-calculator tile-icon"></i>
							<div class="tile-title">Manage formulas</div>
							<p class="tile-desc">Create, edit, and version control formulas</p>
						</a>
					</div>
					<div class="col-md-4 col-lg-3 mb-3">
						<a href="{{ route('formulars.global-variables') }}" class="formulars-module-tile tile-accent-success">
							<i class="mdi mdi-variable tile-icon"></i>
							<div class="tile-title">Global variables</div>
							<p class="tile-desc">Constants and shared variables</p>
						</a>
					</div>
					<div class="col-md-4 col-lg-3 mb-3">
						<a href="{{ route('formulars.lookup-tables') }}" class="formulars-module-tile tile-accent-info">
							<i class="mdi mdi-table tile-icon"></i>
							<div class="tile-title">Lookup tables</div>
							<p class="tile-desc">Reference data and lookup tables</p>
						</a>
					</div>
					<div class="col-md-4 col-lg-3 mb-3">
						<a href="{{ route('stage-headers.index') }}" class="formulars-module-tile tile-accent-purple">
							<i class="mdi mdi-chart-timeline tile-icon"></i>
							<div class="tile-title">Method sequences</div>
							<p class="tile-desc">Workflow stages and sequences</p>
						</a>
					</div>
					<div class="col-md-4 col-lg-3 mb-3">
						<a href="{{ route('formulars.procedures.manage') }}" class="formulars-module-tile tile-accent-primary">
							<i class="mdi mdi-clipboard-text-outline tile-icon"></i>
							<div class="tile-title">Procedure worksheets</div>
							<p class="tile-desc">Procedure capture worksheets</p>
						</a>
					</div>
					<div class="col-md-4 col-lg-3 mb-3">
						<a href="{{ route('formulars.grouped-worksheets.manage') }}" class="formulars-module-tile tile-accent-success">
							<i class="mdi mdi-folder-multiple-outline tile-icon"></i>
							<div class="tile-title">Grouped worksheets</div>
							<p class="tile-desc">Multi-stage ordered pipelines</p>
						</a>
					</div>
					<div class="col-md-4 col-lg-3 mb-3">
						<a href="{{ route('formulars.hybrid-worksheets.manage') }}" class="formulars-module-tile tile-accent-info">
							<i class="mdi mdi-file-tree tile-icon"></i>
							<div class="tile-title">Hybrid worksheets</div>
							<p class="tile-desc">Mixed formula, procedure &amp; sequence sheets</p>
						</a>
					</div>
					<div class="col-md-4 col-lg-3 mb-3">
						<a href="{{ route('formulars.history') }}" class="formulars-module-tile tile-accent-warning">
							<i class="mdi mdi-history tile-icon"></i>
							<div class="tile-title">Execution history</div>
							<p class="tile-desc">View saved worksheet runs</p>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	</div>
</main>
@endsection
