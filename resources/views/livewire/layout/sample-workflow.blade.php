@extends('layouts.lab.layout.app', [ 'datePicker' => true, 'select2' => true])

@section('title2')
<title>{{ getSampleWorkflowStageLabel($status) }} | Sample WorkFlow</title>
@endsection

@section('content2')
<main>
	<?php
$items = [
	[
		'link' => route('dashboard-lab'),
		'name' => 'Dashboard',
		'icon' => null,
	],
	[
		'link' => route('sample-workflow', ['status' => 'All Samples']),
		'name' => 'Sample Workflow',
		'icon' => null,
	],
	[
		'link' => route('sample-workflow', ['status' => $status]),
		'name' => getSampleWorkflowStageLabel($status),
		'icon' => null,
	],
];
	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>
	@livewire('sampleworkflow.workflow-board', [
		'status' => $status,
		'initialFilters' => $initialFilters ?? [],
	])
</main>
@endsection
@section('script2')
<script>
	(function () {
		function unlockStuckScroll() {
			var hasOpenModal = document.querySelector('.modal.show');

			if (!hasOpenModal) {
				document.body.classList.remove('modal-open');
				document.body.style.removeProperty('overflow');
				document.body.style.removeProperty('padding-right');
			}
		}

		document.addEventListener('DOMContentLoaded', unlockStuckScroll);
		document.addEventListener('hidden.bs.modal', unlockStuckScroll);

		document.addEventListener('livewire:initialized', function () {
			unlockStuckScroll();
			Livewire.hook('morph.updated', unlockStuckScroll);
		});
	})();
</script>
@stack('script2')
@endsection



