<h2>Equipment Monitoring</h2>
<p>
	Open <strong>Equipment → Equipment Monitoring</strong>.
	The page title is <strong>Monitoring</strong>.
	Subtitle on screen: track environmental conditions and equipment performance with dynamic formula-driven logs and audit-ready records.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/monitoring.png',
	'caption' => 'Monitoring home — Environmental Monitoring, Equipment Monitoring, and Template Engine cards with Assigned Laboratories.',
])

<h2>Three entry cards</h2>
<p>Click a card to switch the workspace below. The active card is highlighted.</p>
<ol>
	<li>
		<strong>Environmental Monitoring</strong> —
		<em>Execution, daily logs, thresholds, and deviation alerts for laboratory conditions.</em>
	</li>
	<li>
		<strong>Equipment Monitoring</strong> —
		<em>Intermediate checks, verification runs, dynamic calculations, and pass/fail controls.</em>
	</li>
	<li>
		<strong>Template Engine</strong> —
		<em>Template versions, variable logic, formula rules, and workflow-ready configurations.</em>
		(Full setup is in the next chapter: Monitoring Template Engine.)
	</li>
</ol>

<h2>Shared layout (Environmental &amp; Equipment)</h2>
<ul>
	<li><strong>Assigned Laboratories</strong> — count of labs linked to your templates. Hint: <em>Use the tabs below to switch between labs.</em></li>
	<li><strong>Lab tabs</strong> — click a lab name to work in that laboratory’s context.</li>
	<li><strong>Metric pills</strong> — depending on section, you may see counts such as:
		<strong>Due Today</strong>, <strong>Completed</strong>, <strong>Pending</strong>, <strong>Failed</strong>,
		<strong>Overdue Calibrations</strong>, <strong>Nearing Calibration Due</strong>, <strong>Daily Checks Due</strong>.
	</li>
</ul>

<h2>Environmental Monitoring — step by step</h2>
<ol>
	<li>Click the <strong>Environmental Monitoring</strong> card.</li>
	<li>Select a lab tab.</li>
	<li>Choose a <strong>section</strong> chip (lab section / area) when shown.</li>
	<li>Use history range options when available (for example 7 / 30 / 60 / 90 days).</li>
	<li>Sub-tabs:
		<ul>
			<li><strong>Logs</strong> — capture and review readings.</li>
			<li><strong>Charts</strong> — visual trends of captured values.</li>
		</ul>
	</li>
	<li>On Logs:
		<ul>
			<li>Banner may show <strong>LOG READINGS</strong> / <strong>Capture New Entry</strong>.</li>
			<li>Enter readings in the frequency matrix (slots match your template / daily schedule).</li>
			<li>Optional <strong>Comment</strong> / <strong>Remarks</strong>.</li>
			<li><strong>Save Reading</strong> — store the entry (some fields may auto-save on change).</li>
			<li><strong>Clear</strong> — clear the capture form.</li>
			<li>To change a saved row: <strong>Modify Saved Entry</strong> (edit mode). Finish with <strong>Done Editing</strong> or leave with <strong>Close Edit</strong>.</li>
		</ul>
	</li>
	<li><strong>Export LWS-011 PDF</strong> — download the environmental LWS-011 style report for the selected context.</li>
	<li>Open the <strong>Formula execution timeline</strong> when you need to see how calculated fields were derived, then <strong>Close</strong>.</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/monitoring-environmental.png',
	'caption' => 'Environmental Monitoring — lab sections, Logs/Charts area, and status pills.',
	'placeholder' => 'Screenshot coming soon — open Environmental Monitoring, pick a lab/section, and capture the Logs/Charts view.',
])

<h2>Equipment Monitoring — step by step</h2>
<ol>
	<li>Click the <strong>Equipment Monitoring</strong> card and select a lab.</li>
	<li>Find the card titled like <strong>{Lab} | Equipment Due Today</strong> (lab name varies).</li>
	<li>Optional export row:
		<ul>
			<li>Select <strong>template</strong></li>
			<li>Select <strong>equipment</strong></li>
			<li>Select <strong>Month</strong></li>
			<li>Click <strong>Export LWS-011 PDF</strong></li>
		</ul>
	</li>
	<li>Due-today table columns: <strong>Template</strong>, <strong>Doc #</strong>, <strong>Version</strong>, <strong>Status</strong>, <strong>Action</strong>.</li>
	<li>Statuses you will see:
		<ul>
			<li><strong>PENDING</strong> — not yet run / waiting.</li>
			<li><strong>COMPLETED</strong> — run finished successfully.</li>
			<li><strong>FAILED</strong> — run finished with a fail / error outcome.</li>
		</ul>
	</li>
	<li>Click <strong>Execute</strong> on a row to open the execute modal.</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/monitoring-equipment.png',
	'caption' => 'Equipment Monitoring — Due Today filters and Export LWS-011 PDF.',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/monitoring-equipment-logs.png',
	'caption' => 'Equipment Due Today table and Equipment Monitoring Logs.',
])

<h3>Execute Monitoring modal</h3>
<p>Title: <strong>Execute Monitoring: {template name}</strong></p>
<ol>
	<li>Optional <strong>Equipment</strong> picker when the template allows selecting a unit.</li>
	<li><strong>Reading frequency *</strong> (and any other dynamic fields defined on the template — numbers, pass/fail, formula outputs).</li>
	<li><strong>Remark</strong> when shown.</li>
	<li><strong>Save Log</strong> — records the run and updates status.</li>
	<li><strong>Cancel</strong> — close without saving.</li>
</ol>

<h3>Equipment Monitoring Logs</h3>
<p>
	Below the due list, the card <strong>Equipment Monitoring Logs</strong> shows today’s (or recent) runs.
	Typical columns: <strong>Template</strong>, <strong>Result</strong>, <strong>Status</strong>, <strong>Executed By</strong>, <strong>Executed At</strong>.
	Related panels may compare logs vs optimum levels (for example <strong>Equipment Logs vs. Optimum Level</strong>).
</p>

<h2>Button &amp; label glossary (Monitoring)</h2>
<table class="table table-sm">
	<thead>
		<tr><th>On-screen text</th><th>What it does</th></tr>
	</thead>
	<tbody>
		<tr><td>Environmental Monitoring</td><td>Switch workspace to lab condition logs and charts.</td></tr>
		<tr><td>Equipment Monitoring</td><td>Switch workspace to equipment check runs.</td></tr>
		<tr><td>Template Engine</td><td>Manage templates (see next chapter).</td></tr>
		<tr><td>Execute</td><td>Start a monitoring run for that template row.</td></tr>
		<tr><td>Save Log</td><td>Commit the execute modal readings.</td></tr>
		<tr><td>Cancel</td><td>Close a modal without saving.</td></tr>
		<tr><td>Save Reading</td><td>Save environmental capture grid.</td></tr>
		<tr><td>Clear</td><td>Clear capture form values.</td></tr>
		<tr><td>Capture New Entry</td><td>Start a new environmental reading capture.</td></tr>
		<tr><td>Modify Saved Entry</td><td>Enter edit mode on an existing environmental entry.</td></tr>
		<tr><td>Done Editing / Close Edit</td><td>Leave environmental edit mode.</td></tr>
		<tr><td>Export LWS-011 PDF</td><td>Generate the LWS-011 PDF for the current selection.</td></tr>
		<tr><td>Logs / Charts</td><td>Toggle environmental data table vs charts.</td></tr>
		<tr><td>New Template</td><td>Open create-template wizard (Template Engine).</td></tr>
	</tbody>
</table>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-lightbulb-on-outline"></i></span>
	<p>
		Nothing appears under a lab until templates assign that lab and (for equipment checks)
		equipment has a <strong>Lab</strong> set on its profile. See Create &amp; edit and Monitoring Template Engine.
	</p>
</div>
