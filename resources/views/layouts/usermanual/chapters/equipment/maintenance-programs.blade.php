<h2>Equipment Maintenance (programmes)</h2>
<p>
	Open <strong>Equipment → Equipment Maintenance</strong>.
	Page title: <strong>Equipment Maintenance Program</strong>.
	This is the lab-wide calendar of annual and preventive work — not the same as the per-asset <strong>Maintenance Log</strong> tab.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/maintenance-programs.png',
	'caption' => 'Equipment Maintenance Program — Annual, Preventive, Register, and Replacement Plan tabs.',
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
	<li><strong>Advanced Filters</strong> / <strong>Clear Filters</strong></li>
	<li><strong>Start Date</strong> / <strong>End Date</strong></li>
	<li><strong>Maintenance Status</strong> — All Statuses, Scheduled, Serviced, Overdue (labels may also read Scheduled (Pending) / Serviced (Done)).</li>
	<li><strong>Service Type</strong> / <strong>Service Provider</strong> when offered.</li>
</ul>

<h2>Annual Program</h2>
<ol>
	<li>Click <strong>Create Annual Program</strong>.</li>
	<li>Modal fields: <strong>Name *</strong>, <strong>Date *</strong>, <strong>Description</strong>, <strong>Status</strong> (Active / Draft).</li>
	<li><strong>Save Program</strong> or <strong>Update Program</strong>; <strong>Cancel</strong> to close.</li>
	<li>Use <strong>Add Equipment to Program</strong> to attach assets.</li>
	<li>Table columns typically: <strong>Equipment Name</strong>, <strong>Serial Number</strong>, <strong>Serviced Date</strong>, <strong>Status</strong>, <strong>Next Service</strong>, <strong>Remark</strong>, <strong>Actions</strong>.</li>
	<li>Row actions include <strong>Details</strong>, edit, and delete programme tools (<strong>Edit Program</strong> / <strong>Delete Program</strong>).</li>
	<li><strong>Export Annual Program</strong> — download the programme spreadsheet/report.</li>
</ol>

<h2>Preventive Program</h2>
<ol>
	<li><strong>Create Preventive Program</strong> — same style of Name / Date / Description / Status.</li>
	<li><strong>Add Equipment to Program</strong> — schedule units on quarter/month grids as shown.</li>
	<li><strong>Mark Done</strong> (or similar mark-serviced control) — record that preventive work for that slot is complete.</li>
	<li><strong>Apply to All</strong> — push a scheduled month / setting across selected rows when available.</li>
	<li><strong>Export Preventive Program</strong>.</li>
</ol>

<h2>Maintenance Register</h2>
<ol>
	<li><strong>Add Equipment to Register</strong> — capture cost and provider details (USD/TZS or other currencies as configured).</li>
	<li>Edit / delete register rows via <strong>Edit Register</strong> / <strong>Delete Register</strong>.</li>
	<li><strong>Export Register</strong>.</li>
</ol>
<p>
	Opening an asset from here may show profile tabs <strong>Annual (TSU/F/06)</strong>, <strong>Preventive (TSU/F/05)</strong>, and <strong>Maintenance Register</strong>
	with <strong>Add Annual Record</strong>, <strong>Add Preventive Record</strong>, <strong>Add Register Entry</strong>.
</p>

<h2>Replacement Plan</h2>
<ol>
	<li>Create or edit a plan: modal <strong>Create/Edit Replacement Plan</strong> with <strong>Plan Name *</strong>, <strong>Start Year *</strong>, and related fields.</li>
	<li><strong>Add Equipment to Plan</strong> / <strong>Edit Plan Item</strong>.</li>
	<li><strong>Edit Plan</strong> / <strong>Delete Plan</strong>.</li>
	<li><strong>Export Replacement Plan</strong>.</li>
</ol>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-lightbulb-on-outline"></i></span>
	<p>
		Use programmes for planned calendars and compliance forms;
		still record the actual service visit on the asset’s <strong>Maintenance Log</strong> or <strong>Calibration Log</strong> when work is completed.
	</p>
</div>
