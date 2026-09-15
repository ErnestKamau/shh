<h2>Operators, attachments &amp; reminders</h2>
<p>
	Still on the equipment detail page, three tabs keep people, files, and chase reminders organised.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/operators-attachments-notifications.png',
	'caption' => 'Equipment detail — Operators, Attachments, and Notifications tabs.',
])

<h2>Operators</h2>
<p>Open the <strong>Operators</strong> tab.</p>
<ol>
	<li>Click <strong>Add Operator</strong>.</li>
	<li>Select the user(s) who are allowed to operate or are responsible for this equipment.</li>
	<li>Save the assignment. Remove operators from the list when they no longer work with the asset.</li>
</ol>
<p>
	Keeping this list current helps audits and makes it clear who should appear on verification / in-house service records.
</p>

<h2>Attachments</h2>
<p>Open the <strong>Attachments</strong> tab for manuals, SOPs, photos, or other files that are not a calibration certificate (certificates usually live on the Calibration / Maintenance log itself).</p>
<ol>
	<li>Click <strong>Add Attachment</strong>.</li>
	<li>Enter a title and choose a file.</li>
	<li>Save. Use delete (with confirmation) when a file is obsolete.</li>
</ol>

<h2>Notifications (reminders)</h2>
<p>Open the <strong>Notifications</strong> tab.</p>
<ol>
	<li>Click <strong>Add Notification</strong>.</li>
	<li>Choose the notification type — typically <strong>Calibration</strong>, <strong>Maintenance</strong>, or <strong>Verification</strong>.</li>
	<li>Set the <strong>Value</strong> (for example number of days before due) and the <strong>Frequency</strong> unit (Days / Weeks / Months as offered on screen).</li>
	<li>Save. Edit or delete rows when chase rules change.</li>
</ol>
<p>
	The system can send chase reminders on a schedule (for example overnight equipment chase jobs).
	Notifications on the asset work together with the interval days from the create/edit wizard.
</p>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-lightbulb-on-outline"></i></span>
	<p>
		Also see <strong>Accessories</strong> and <strong>Spare Parts</strong> on the same profile
		(<strong>Add Accessory</strong> / <strong>Save Accessory</strong>, <strong>Add Spare Part</strong> / <strong>Save Spare Part</strong>)
		if you track kits and consumable spares against the instrument.
	</p>
</div>
