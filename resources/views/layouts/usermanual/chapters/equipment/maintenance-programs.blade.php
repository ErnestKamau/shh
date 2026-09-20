<h2>Equipment Maintenance (programmes)</h2>
<p>
	Open <strong>Equipment → Equipment Maintenance</strong>.
	Page title: <strong>Equipment Maintenance Program</strong>.
	This is the lab-wide calendar of annual and preventive work — not the same as the per-asset <strong>Maintenance Log</strong> tab.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/maintenance-programs.png',
	'caption' => 'Annual Program — period, Export Annual Program, filters, and + Create Annual Program.',
])

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-book-open-page-variant"></i></span>
	<p>
		Per-asset service history lives on the equipment profile under <strong>Maintenance Log</strong>.
		This page manages programmes, registers, and replacement plans across many assets.
	</p>
</div>

<h2>Main tabs</h2>
<ol>
	<li><strong>Annual Program</strong> (form reference TSU/F/06)</li>
	<li><strong>Preventive Program</strong> (form reference TSU/F/05)</li>
	<li><strong>Maintenance Register</strong></li>
	<li><strong>Replacement Plan</strong></li>
</ol>

<h2>Shared filters and search</h2>
<ul>
	<li>Search placeholder: <strong>Search equipment by name or serial...</strong></li>
	<li><strong>Show</strong> — rows per page.</li>
	<li><strong>Advanced Filters</strong> — <strong>Start Date</strong> / <strong>End Date</strong>, status or service filters as shown on each tab.</li>
	<li>Period banner (for example <em>Jan 2026 – Dec 2026</em>) and an <strong>Export …</strong> button for the active tab.</li>
</ul>

<h2>Annual Program</h2>
<ol>
	<li>Click <strong>+ Create Annual Program</strong>.</li>
	<li>Modal fields: <strong>Name *</strong>, <strong>Date *</strong>, <strong>Description</strong>, <strong>Status</strong> (Active / Draft).</li>
	<li><strong>Save Program</strong> or <strong>Update Program</strong>; <strong>Cancel</strong> to close.</li>
	<li>Use the Active Annual Program dropdown (for example <em>View All Equipment</em>) and <strong>Add Equipment to Program</strong> where offered.</li>
	<li><strong>Export Annual Program</strong> — download the programme spreadsheet/report.</li>
</ol>

<h2>Preventive Program</h2>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/maintenance-preventive.png',
	'caption' => 'Preventive Program — Export Preventive Program, filters, Create Preventive Program, and month-cell legend.',
])

<ol>
	<li><strong>+ Create Preventive Program</strong> — Name / Date / Description / Status.</li>
	<li>Schedule units on the month grid: click a month cell to schedule; click ✓ Done to mark serviced.</li>
	<li>Legend: <strong>Not scheduled</strong> · <strong>Scheduled (pending)</strong> · <strong>Serviced / Done</strong>.</li>
	<li><strong>Export Preventive Program</strong>.</li>
</ol>

<h2>Maintenance Register</h2>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/maintenance-register.png',
	'caption' => 'Maintenance Register — filters (service type / provider), Create Maintenance Register, and cost columns.',
])

<ol>
	<li><strong>+ Create Maintenance Register</strong> (or add equipment to an active register).</li>
	<li>Columns typically: <strong>Equipment Name</strong>, <strong>Service Provider</strong>, <strong>Type of Service</strong>, <strong>Cost (USD)</strong>, <strong>Cost (TZS)</strong>, <strong>Actions</strong>.</li>
	<li><strong>Export Register</strong>.</li>
</ol>
<p>
	From an equipment profile you can also open <strong>Maintenance Register</strong> and click <strong>+ Add Register Entry</strong>.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/maintenance-register-add.png',
	'caption' => 'Add Maintenance Register Entry — Year / Financial Year, Service Provider, Type of Service, Cost (USD / TZS).',
])

<ul>
	<li><strong>Year / Financial Year</strong> (for example <em>2026/2027</em>)</li>
	<li><strong>Service Provider</strong></li>
	<li><strong>Type of Service</strong></li>
	<li><strong>Cost (USD)</strong> / <strong>Cost (TZS)</strong> (or other currencies as configured)</li>
	<li><strong>Save Entry</strong> or <strong>Cancel</strong></li>
</ul>

<h2>Replacement Plan</h2>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/maintenance-replacement.png',
	'caption' => 'Replacement Plan — date filters, Create Plan, and equipment with Planned Replacement Year.',
])

<ol>
	<li>Click <strong>+ Create Plan</strong> — Plan Name, Start Year, and related fields.</li>
	<li>Table columns: <strong>Equipment Name</strong>, <strong>Serial Number</strong>, <strong>Planned Replacement Year</strong>, <strong>Actions</strong>.</li>
	<li>Use row view/edit actions to set the planned replacement year.</li>
	<li><strong>Export Replacement Plan</strong> when offered.</li>
</ol>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-lightbulb-on-outline"></i></span>
	<p>
		Use programmes for planned calendars and compliance forms;
		still record the actual service visit on the asset’s <strong>Maintenance Log</strong> or <strong>Calibration Log</strong> when work is completed.
	</p>
</div>
