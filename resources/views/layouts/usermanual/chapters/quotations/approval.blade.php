<h2>Quotation stages</h2>
<p>
	On <strong>Billing → Quotations → Quotations</strong>, stage filters show where each quote sits:
</p>
<ul>
	<li><strong>In Preparation</strong> — configure header, lines, and terms.</li>
	<li><strong>In Approval</strong> — waiting for an approver.</li>
	<li><strong>Complete</strong> — finished (may already be sent to the customer).</li>
	<li><strong>Approval configuration</strong> — who may approve (role and people), not a quote list.</li>
</ul>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/tabs-in-preparation.png',
	'caption' => 'Quotations tab — All, In Preparation, In Approval, Complete, and Approval configuration.',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/overview-quotations.png',
	'caption' => 'Overview KPIs — In Preparation, Finalised, Sent to Customer, PDF Generated, and value at a glance.',
])

<h2>Who can approve?</h2>
<p>
	On <strong>Billing → Quotations → Approval configuration</strong>, choose the approver <strong>role</strong>
	(for example Lab Manager). Everyone active with that role can approve. The first approval wins.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/approval-settings.png',
	'caption' => 'Approval configuration — role and listed approvers.',
])

<h2>Send for approval</h2>
<h3>From Billing → Quotations (preparing quote)</h3>
<ol>
	<li>Finish the header and lines (from a pricelist, manually, and/or AmSpec Import).</li>
	<li>Press <strong>Save lines</strong> and review totals.</li>
	<li>Press <strong>Move To workflow</strong> (top right — not the Actions menu).</li>
	<li>Choose how to notify approvers (email and/or in-app bell — at least one).</li>
	<li>The quote moves to <strong>In Approval</strong>.</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/approval-channels.png',
	'caption' => 'Move To workflow — notify and move the quote into In Approval.',
])
@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/approval-channels-2.png',
	'caption' => 'Notify options — email and/or in-app bell for approvers.',
])
@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/approval-channels-3.png',
	'caption' => 'In Approval list — where approvers take action.',
])

<h3>From Process enquiry (request-side)</h3>
<ol>
	<li>Finish prices on the request (<strong>Build new</strong>).</li>
	<li>Press <strong>Send for Approval</strong>.</li>
	<li>Choose notify channels; the quote moves to <strong>In Approval</strong> the same way.</li>
</ol>

<h2>Approve or reject</h2>
<p>
	An approver opens the quote from <strong>Quotations → In Approval</strong> (or from a notification).
	The screen shows <strong>Awaiting Approval</strong>, the assigned approver, and a PDF preview of what the customer will receive.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/quotation-approval-awaiting.png',
	'caption' => 'Approval view — Awaiting Approval badge, approver name, PDF preview, Approve and Reject actions.',
])

<ul>
	<li><strong>Approve</strong> (or <strong>Approve and send</strong>) — pick customer contacts, optional PDF email, optional comments; quote becomes <strong>Complete</strong> and can go to the customer.</li>
	<li><strong>Reject</strong> — give a reason; the quote returns to <strong>In Preparation</strong> so someone can fix it.</li>
</ul>

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

<h2>Completion</h2>
<p>
	When approval succeeds, the quote appears under <strong>Complete</strong> with status <strong>Quote Complete</strong>.
	At this point:
</p>
<ul>
	<li>The customer may already have received the PDF (depending on your Approve and send choices).</li>
	<li>You can open the quote to view or re-send documents from Actions.</li>
	<li>You can start a <strong>Test Request / enquiry</strong> from the quote (see <strong>Billing quotation workflow</strong>).</li>
</ul>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-shield-check-outline"></i></span>
	<p><strong>Build new</strong> on Process enquiry needs approval. <strong>Use existing</strong> skips a new approval round when you attach an already-complete quote — you may still send that quote to the customer if it was not sent yet.</p>
</div>

<h2>Channels at a glance</h2>
<ul>
	<li><strong>In-app bell</strong> — “needs approval” style notices.</li>
	<li><strong>Email</strong> — often with PDF; approve in the lab system.</li>
	<li><strong>Quotations → In Approval</strong> — main place to approve and send or reject.</li>
</ul>
