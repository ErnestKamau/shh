<h2>Verification Log</h2>
<p>
	On an equipment profile, open the <strong>Verification Log</strong> tab.
	Use it for intermediate checks against reference standards (between full calibrations).
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/verification-log.png',
	'caption' => 'Verification Log tab — search, Add Verification Log, and columns (Type, Reference Standards, Service Performer, Verification Date).',
])

<h2>What you see on the tab</h2>
<ul>
	<li><strong>Add Verification Log</strong> — opens the create modal.</li>
	<li>Table columns typically include: <strong>Actions</strong>, <strong>Type</strong>, <strong>Reference Standards</strong>, <strong>Service Performer</strong>, <strong>Verification Date</strong>.</li>
</ul>

<h2>Row actions</h2>
<ul>
	<li><strong>Edit</strong> — update the entry.</li>
	<li><strong>View</strong> — open a read-only details modal.</li>
	<li><strong>Delete</strong> — remove the entry (soft-deleted in the system after confirm).</li>
</ul>

<h2>Add or edit a verification — step by step</h2>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/verification-log-create.png',
	'caption' => 'Create Verification Log — date, reference standard, In House / External, Operator, Procedure, Responses/Readings.',
])

<ol>
	<li>Click <strong>Add Verification Log</strong> (or Edit).</li>
	<li>Modal title: <strong>Create Verification Log</strong> (or Edit). Fill:
		<ul>
			<li><strong>Verification Date *</strong></li>
			<li><strong>Reference Standard *</strong> — which standard or reference was used.</li>
			<li><strong>Service Type</strong> — <strong>In House</strong> or <strong>External</strong>.
				<ul>
					<li>In House → choose <strong>Operator</strong>.</li>
					<li>External → choose <strong>Supplier</strong>.</li>
				</ul>
			</li>
			<li><strong>Procedure *</strong> — what procedure or method was followed.</li>
			<li><strong>Responses/Readings *</strong> — observed readings or pass/fail responses.</li>
			<li><strong>Remarks</strong> — comments and conclusions when shown.</li>
		</ul>
	</li>
	<li>Click <strong>Save</strong> or <strong>Cancel</strong>.</li>
</ol>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-lightbulb-on-outline"></i></span>
	<p>
		Verification does not replace calibration. Use Verification for interim checks;
		use Calibration Log when a full calibration (with certificate and uncertainty) is performed.
	</p>
</div>
