<h2>Who can approve</h2>
<p>
	On <strong>Billing → Quotations → Approval configuration</strong>, choose the approver <strong>role</strong>
	(for example Lab Manager). Everyone active with that role can approve. The first approval wins.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/approval-settings.png',
	'caption' => 'Approval configuration — role and listed approvers.',
])

<h2>Send for approval</h2>
<h3>From Process enquiry (Build new)</h3>
<ol>
	<li>Finish prices on the request.</li>
	<li>Press <strong>Send for Approval</strong>.</li>
	<li>Choose how to notify (email and/or in-app bell — at least one).</li>
	<li>The quote moves to <strong>In Approval</strong>.</li>
</ol>

<h3>From Billing quotation (In Preparation)</h3>
<ol>
	<li>Open the quote and finish lines.</li>
	<li>Use <strong>Request for Approval</strong> (under Actions).</li>
	<li>Same notify choices; same approver role from configuration.</li>
</ol>

<h2>Approve or reject</h2>
<p>
	Approvers work from the <strong>In Approval</strong> list (not only from the old detail screen).
</p>
<ul>
	<li><strong>Approve and send</strong> — pick customer contacts, optional PDF email, optional comments; quote becomes complete and can go to the customer.</li>
	<li><strong>Reject</strong> — give a reason; the quote returns to preparation so someone can fix it.</li>
</ul>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/approval-channels.png',
	'caption' => 'Approval path — notify and move the quote into In Approval.',
])
@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/approval-channels-2.png',
	'caption' => 'Notify options — email and/or in-app bell for approvers.',
])
@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/approval-channels-3.png',
	'caption' => 'In Approval list — where approvers take action.',
])
@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/approval-channels-4.png',
	'caption' => 'Approve and send — choose recipients and confirm.',
])
@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/approval-channels-5.png',
	'caption' => 'Reject — return to preparation with a reason.',
])
@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/approval-channels-6.png',
	'caption' => 'Bell / email cues so approvers know work is waiting.',
])

<h2>Channels at a glance</h2>
<ul>
	<li><strong>In-app bell</strong> — “needs approval” style notices.</li>
	<li><strong>Email</strong> — often with PDF; approve in the lab system.</li>
	<li><strong>Quotations → In Approval</strong> — main place to approve and send or reject.</li>
</ul>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-shield-check-outline"></i></span>
	<p>Build new needs approval. Use existing skips a new approval round when you attach a complete quote — you may still send that quote to the customer if it was not sent yet.</p>
</div>
