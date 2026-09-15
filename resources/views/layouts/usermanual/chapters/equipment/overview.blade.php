<h2>Welcome to Equipment</h2>
<p>
	<strong>Equipment</strong> is where your organisation keeps the laboratory asset register:
	what instruments and devices you own, who they belong to, when they need calibration or maintenance,
	how they are monitored day to day, and what happens when an asset is disposed of or depreciated.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/overview.png',
	'caption' => 'Equipment List — main register with sidebar navigation for the whole Equipment module.',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/dashboard.png',
	'caption' => 'Equipment Dashboard — starting point after you open the Equipment module.',
])

<p>
	Open Equipment from the home app launcher card <strong>Equipment</strong>, or from the module switcher
	at the top of the screen. You land on the <strong>Equipment Dashboard</strong>.
	The left sidebar is your map of everything in this module.
</p>

<h2>Sidebar tour</h2>
<ol>
	<li><strong>Equipment Dashboard</strong> — counts, overdue alerts, and quick actions.</li>
	<li><strong>Equipment List</strong> — search, add, edit, import, and open any asset.</li>
	<li><strong>Equipment Monitoring</strong> — environmental and equipment checks, templates, and LWS-011 export.</li>
	<li><strong>Equipment Maintenance</strong> — annual and preventive programmes, maintenance register, and replacement plans.</li>
	<li><strong>Equipment Disposal</strong> — requests, approvals, and execution of disposal.</li>
	<li><strong>Asset Types</strong> / <strong>Asset Locations</strong> — master data used when assigning equipment.</li>
	<li><strong>Asset Depreciation</strong> — depreciation list, methods, and reports (if you have permission).</li>
	<li><strong>Equipment Daily Log</strong> — day-to-day on/off and usage for units that require a daily log.</li>
	<li><strong>Reports</strong> — equipment report generation.</li>
</ol>

<h2>How this guide is organised</h2>

<h3>1. Register and profile</h3>
<p>
	Start with the <strong>Dashboard</strong> and <strong>Equipment List</strong>, then learn
	<strong>Create &amp; edit</strong> (the five-step wizard) and the <strong>Equipment detail page</strong>
	with all of its tabs.
</p>

<h3>2. Logs on a single asset</h3>
<p>
	On an equipment profile you record <strong>Calibration</strong>, <strong>Verification</strong>, and
	<strong>Maintenance</strong> logs, plus operators, attachments, and reminders.
</p>

<h3>3. Monitoring and programmes</h3>
<p>
	<strong>Equipment Monitoring</strong> and the <strong>Template Engine</strong> cover scheduled checks.
	<strong>Equipment Maintenance</strong> covers lab-wide annual/preventive programmes
	(different from the per-asset Maintenance Log).
</p>

<h3>4. Daily use, master data, disposal, finance</h3>
<p>
	<strong>Daily Log</strong>, <strong>Asset Types &amp; Locations</strong>, <strong>Disposal</strong>,
	and <strong>Depreciation &amp; Reports</strong> complete the module.
</p>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-book-open-page-variant"></i></span>
	<p>
		Two different “maintenance” ideas appear in this module:
		the <strong>Maintenance Log</strong> tab on one asset (service history),
		and <strong>Equipment Maintenance</strong> programmes (annual / preventive calendars).
		Both are documented in separate chapters so you do not mix them up.
	</p>
</div>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-lightbulb-on-outline"></i></span>
	<p>
		What you can see and click depends on your permissions
		(for example depreciation and disposal approvals).
		If a menu item is missing, ask your administrator to grant access.
	</p>
</div>
