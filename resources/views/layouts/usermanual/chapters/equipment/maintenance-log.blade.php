<h2>Maintenance Log (per asset)</h2>
<p>
	On an equipment profile, open the <strong>Maintenance Log</strong> tab.
	This is the service history for <em>this one asset</em> — repairs, servicing visits, and notes.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/maintenance-log.png',
	'caption' => 'Create Maintenance Log — Date, Description, Reference Number, Service Type, Employee/Supplier, Certificate.',
])

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-alert-circle-outline"></i></span>
	<p>
		This is <strong>not</strong> the same as <strong>Equipment → Equipment Maintenance</strong>
		(annual / preventive programmes). Programme calendars are documented in the Equipment Maintenance chapter.
		Use this tab for day-to-day service records on the asset profile.
	</p>
</div>

<h2>What you see on the tab</h2>
<ul>
	<li><strong>Add Maintenance Log</strong></li>
	<li>Table columns typically include: <strong>Actions</strong>, <strong>Type</strong>, <strong>Service Provider</strong>, <strong>Date</strong>, <strong>Certificate</strong>, <strong>Overseen By</strong>, <strong>Notes</strong>.</li>
</ul>

<h2>Row actions</h2>
<ul>
	<li><strong>Edit</strong> / <strong>Download</strong> (certificate) / <strong>View</strong> (notes) / <strong>Delete</strong></li>
</ul>

<h2>Add or edit a maintenance log — step by step</h2>
<ol>
	<li>Click <strong>Add Maintenance Log</strong> (or Edit).</li>
	<li>Modal title: <strong>Create/Edit Maintenance Log</strong>. Fill:
		<ul>
			<li><strong>Date *</strong></li>
			<li><strong>Description *</strong></li>
			<li><strong>Reference Number</strong> — optional job / work-order reference.</li>
			<li><strong>Service Type</strong> — <strong>In House</strong> or <strong>External</strong>, then choose <strong>Employee</strong> or <strong>Supplier</strong>.</li>
			<li><strong>Certificate</strong> — optional attachment (service report PDF, etc.).</li>
			<li><strong>Notes *</strong></li>
		</ul>
	</li>
	<li><strong>Save</strong> or <strong>Cancel</strong>.</li>
</ol>

<h2>How this ties to schedules</h2>
<p>
	On create/edit, you set <strong>Maintenance After (Days)</strong> and notification days.
	The list and dashboard use those intervals (with the latest logs) to show when maintenance is due.
	Always record the visit here after work is completed so the next due date stays meaningful.
</p>
