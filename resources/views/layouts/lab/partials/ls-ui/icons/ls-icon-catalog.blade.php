{{--
	ls-icon-catalog — lab domain icons + intentional motion demos.
	Curated from quotations, pricelist, sample receiving, worksheets, request view.
--}}
@php
	$groups = $groups ?? [
		'Quotations & billing' => [
			['icon' => 'mdi-file-document-edit-outline', 'label' => 'Quotation', 'tone' => 'burgundy'],
			['icon' => 'mdi-file-eye', 'label' => 'Preview', 'tone' => 'blue'],
			['icon' => 'mdi-printer', 'label' => 'PDF', 'tone' => 'slate'],
			['icon' => 'mdi-share-circle', 'label' => 'Approve/share', 'tone' => 'blue'],
			['icon' => 'mdi-thumb-up', 'label' => 'Approved', 'tone' => 'green'],
			['icon' => 'mdi-alert-decagram', 'label' => 'Awaiting', 'tone' => 'amber'],
			['icon' => 'mdi-file-clock-outline', 'label' => 'Pending review', 'tone' => 'amber'],
			['icon' => 'mdi-file-document-alert-outline', 'label' => 'Needs approval', 'tone' => 'violet'],
			['icon' => 'mdi-account-clock-outline', 'label' => 'Awaiting approver', 'tone' => 'cyan'],
			['icon' => 'mdi-source-branch', 'label' => 'Revision', 'tone' => 'violet'],
			['icon' => 'mdi-cash-multiple', 'label' => 'Billing', 'tone' => 'green'],
			['icon' => 'mdi-email-check', 'label' => 'Sent', 'tone' => 'cyan'],
			['icon' => 'mdi-content-duplicate', 'label' => 'Duplicate', 'tone' => 'slate'],
		],
		'Pricelist' => [
			['icon' => 'mdi-format-list-bulleted-type', 'label' => 'Pricelist', 'tone' => 'slate'],
			['icon' => 'mdi-account-multiple-outline', 'label' => 'Customers', 'tone' => 'blue'],
			['icon' => 'mdi-send', 'label' => 'Email list', 'tone' => 'burgundy'],
			['icon' => 'mdi-history', 'label' => 'Email log', 'tone' => 'amber'],
			['icon' => 'mdi-content-copy', 'label' => 'Clone', 'tone' => 'slate'],
			['icon' => 'mdi-filter-variant', 'label' => 'Filter', 'tone' => 'blue'],
		],
		'Sample receiving' => [
			['icon' => 'mdi-sitemap', 'label' => 'Workflow', 'tone' => 'burgundy'],
			['icon' => 'mdi-package-down', 'label' => 'Receive', 'tone' => 'blue'],
			['icon' => 'mdi-clipboard-check-outline', 'label' => 'Integrity', 'tone' => 'green'],
			['icon' => 'mdi-file-chart-outline', 'label' => 'Enquiry', 'tone' => 'cyan'],
			['icon' => 'mdi-check-decagram', 'label' => 'Accept quote', 'tone' => 'green'],
			['icon' => 'mdi-barcode', 'label' => 'Labels', 'tone' => 'slate'],
			['icon' => 'mdi-barcode-scan', 'label' => 'Dispatch', 'tone' => 'violet'],
			['icon' => 'mdi-walk', 'label' => 'Walk-in', 'tone' => 'cyan'],
			['icon' => 'mdi-web', 'label' => 'Portal', 'tone' => 'blue'],
			['icon' => 'mdi-calendar-clock', 'label' => 'Scheduled', 'tone' => 'amber'],
			['icon' => 'mdi-close-circle-outline', 'label' => 'Reject', 'tone' => 'rose'],
			['icon' => 'mdi-bell-ring', 'label' => 'Reminder', 'tone' => 'amber'],
		],
		'Lab / worksheets / TRF' => [
			['icon' => 'mdi-flask-outline', 'label' => 'Tests', 'tone' => 'blue'],
			['icon' => 'mdi-test-tube', 'label' => 'Sample', 'tone' => 'cyan'],
			['icon' => 'mdi-map-marker-radius-outline', 'label' => 'Collection', 'tone' => 'rose'],
			['icon' => 'mdi-thermometer', 'label' => 'Temperature', 'tone' => 'amber'],
			['icon' => 'mdi-clipboard-text', 'label' => 'Worksheet', 'tone' => 'slate'],
			['icon' => 'mdi-function', 'label' => 'Formula', 'tone' => 'violet'],
			['icon' => 'mdi-chart-timeline-variant', 'label' => 'Sequence', 'tone' => 'blue'],
			['icon' => 'mdi-play', 'label' => 'Run', 'tone' => 'green'],
			['icon' => 'mdi-draw', 'label' => 'Signature', 'tone' => 'burgundy'],
			['icon' => 'mdi-paperclip', 'label' => 'Attachments', 'tone' => 'slate'],
			['icon' => 'mdi-comment-text-outline', 'label' => 'Notes', 'tone' => 'amber'],
			['icon' => 'mdi-truck-outline', 'label' => 'Transport', 'tone' => 'cyan'],
		],
	];
@endphp

@foreach($groups as $groupTitle => $icons)
	<p class="ls-field__label mb-2 mt-3">{{ $groupTitle }}</p>
	<div class="ls-icon-grid mb-2">
		@foreach($icons as $item)
			<div class="ls-icon-tile">
				<span class="ls-icon-tile__glyph ls-icon--{{ $item['tone'] }}">
					<i class="mdi {{ $item['icon'] }}" aria-hidden="true"></i>
				</span>
				<span class="ls-icon-tile__label">{{ $item['label'] }}</span>
				<span class="ls-icon-tile__name">{{ $item['icon'] }}</span>
			</div>
		@endforeach
	</div>
@endforeach

<p class="ls-field__label mb-2 mt-3">Motion (professional, not decorative noise)</p>
<div class="ls-icon-grid mb-3">
	<div class="ls-icon-tile">
		<span class="ls-icon-tile__glyph ls-icon-tile__glyph--lg ls-icon--blue"><i class="mdi mdi-loading ls-motion-spin"></i></span>
		<span class="ls-icon-tile__label">Busy / save</span>
		<span class="ls-icon-tile__name">ls-motion-spin</span>
	</div>
	<div class="ls-icon-tile">
		<span class="ls-icon-tile__glyph ls-icon-tile__glyph--lg ls-icon--amber ls-motion-pulse"><i class="mdi mdi-clock-alert-outline"></i></span>
		<span class="ls-icon-tile__label">TAT alert</span>
		<span class="ls-icon-tile__name">ls-motion-pulse</span>
	</div>
	<div class="ls-icon-tile">
		<span class="ls-icon-tile__glyph ls-icon-tile__glyph--lg ls-icon--amber"><i class="mdi mdi-bell-ring ls-motion-ring"></i></span>
		<span class="ls-icon-tile__label">Quote reminder</span>
		<span class="ls-icon-tile__name">ls-motion-ring</span>
	</div>
	<div class="ls-icon-tile">
		<span class="ls-icon-tile__glyph ls-icon-tile__glyph--lg ls-icon--green ls-motion-check"><i class="mdi mdi-check-decagram"></i></span>
		<span class="ls-icon-tile__label">Success pop</span>
		<span class="ls-icon-tile__name">ls-motion-check</span>
	</div>
	<div class="ls-icon-tile">
		<span class="ls-icon-tile__glyph ls-icon-tile__glyph--lg ls-motion-shimmer"><i class="mdi mdi-cloud-upload-outline"></i></span>
		<span class="ls-icon-tile__label">Upload wait</span>
		<span class="ls-icon-tile__name">ls-motion-shimmer</span>
	</div>
	<div class="ls-icon-tile ls-motion-rise">
		<span class="ls-icon-tile__glyph ls-icon-tile__glyph--lg ls-icon--violet"><i class="mdi mdi-flask-outline"></i></span>
		<span class="ls-icon-tile__label">Enter stage</span>
		<span class="ls-icon-tile__name">ls-motion-rise</span>
	</div>
</div>

<p class="ls-field__label mb-2">Row action cluster (receiving board style)</p>
<div class="ls-icon-row-actions">
	<button type="button" class="ls-icon-btn" title="View"><i class="mdi mdi-eye-outline"></i></button>
	<button type="button" class="ls-icon-btn" title="Process enquiry"><i class="mdi mdi-file-chart-outline"></i></button>
	<button type="button" class="ls-icon-btn ls-icon-btn--success" title="Accept quotation"><i class="mdi mdi-check-decagram"></i></button>
	<button type="button" class="ls-icon-btn" title="Receive samples"><i class="mdi mdi-package-down"></i></button>
	<button type="button" class="ls-icon-btn" title="Integrity"><i class="mdi mdi-clipboard-check-outline"></i></button>
	<button type="button" class="ls-icon-btn" title="Print labels"><i class="mdi mdi-barcode"></i></button>
	<button type="button" class="ls-icon-btn ls-icon-btn--danger" title="Reject"><i class="mdi mdi-close-circle-outline"></i></button>
</div>
