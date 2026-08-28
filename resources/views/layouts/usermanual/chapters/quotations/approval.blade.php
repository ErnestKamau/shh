<h2>Two doors, same quote</h2>
<p>
	A quotation can be sent for approval from two places. Both paths create or update the <strong>same quotation record</strong>
	and both land in <strong>Billing → Quotations → In Approval</strong> for the same approvers and PDF.
</p>

<div class="um-compare">
	<div class="um-compare__card">
		<h4>Billing → Quotations</h4>
		<p>Commercial builds the quote directly on the Quotations page. Send with <strong>Move To workflow</strong> (top right).</p>
	</div>
	<div class="um-compare__card">
		<h4>Process enquiry (request-side)</h4>
		<p>Price an existing request from the request view. Send with <strong>Send for Approval</strong> after <strong>Build new</strong>.</p>
	</div>
</div>

<table class="table table-sm table-bordered um-table">
	<thead>
		<tr>
			<th></th>
			<th>Billing quotations</th>
			<th>Process enquiry</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><strong>Where you start</strong></td>
			<td>Billing → Quotations</td>
			<td>Request view → Process enquiry</td>
		</tr>
		<tr>
			<td><strong>Send button</strong></td>
			<td><strong>Move To workflow</strong> (top right — not Actions)</td>
			<td><strong>Send for Approval</strong></td>
		</tr>
		<tr>
			<td><strong>Before you send</strong></td>
			<td>Header + lines saved (<strong>Save lines</strong>)</td>
			<td>Prices set on <strong>Build new</strong></td>
		</tr>
		<tr>
			<td><strong>Approval list</strong></td>
			<td colspan="2">Same — <strong>Quotations → In Approval</strong></td>
		</tr>
		<tr>
			<td><strong>Approve / reject</strong></td>
			<td colspan="2">Same screen, same PDF preview, same actions</td>
		</tr>
	</tbody>
</table>

<p>
	For quotation stages and tab filters, see the <strong>Quotation tabs</strong> chapter.
	For who may approve, open <strong>Billing → Quotations → Approval configuration</strong> and choose the approver
	<strong>role</strong> (for example Lab Manager). Everyone active with that role can approve; the first approval wins.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/approval-settings.png',
	'caption' => 'Approval configuration — role and listed approvers (shared by both send paths).',
])

<h2>Send for approval</h2>

<h3>Path A — Billing → Quotations (preparing quote)</h3>
<ol>
	<li>Finish the header and lines (from a pricelist, manually, and/or AmSpec Import). See <strong>Quote preparation &amp; math</strong>.</li>
	<li>Press <strong>Save lines</strong> and review totals.</li>
	<li>Press <strong>Move To workflow</strong> (top right — not the Actions menu).</li>
	<li>Choose how to notify approvers (email and/or in-app bell — at least one).</li>
	<li>The quote moves to <strong>In Approval</strong>.</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/approval-channels.png',
	'caption' => 'Billing path — Move To workflow opens the notify dialog and moves the quote into In Approval.',
])
@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/approval-channels-2.png',
	'caption' => 'Billing path — pick email and/or in-app bell for approvers.',
])
@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/approval-channels-3.png',
	'caption' => 'Shared — In Approval list where approvers take action (quotes from Billing or Process enquiry).',
])

<h3>Path B — Process enquiry (request-side)</h3>
<ol>
	<li>Open the request and use <strong>Process enquiry</strong> (Process Request).</li>
	<li>Choose <strong>Build new from this enquiry</strong> and finish prices on the lines.</li>
	<li>Press <strong>Send for Approval</strong> (not Move To workflow — that button is on the Billing page).</li>
	<li>Choose notify channels (email and/or in-app bell — at least one).</li>
	<li>The quote moves to <strong>In Approval</strong> the same way as Path A.</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/process-enquiry-2.png',
	'caption' => 'Process enquiry path — review lines, then Send for Approval (not Move To workflow).',
])

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-information-outline"></i></span>
	<p>
		<strong>Use existing</strong> on Process enquiry links an already-complete quote and <strong>skips a new approval round</strong>.
		<strong>Build new</strong> always needs approval. See <strong>Process request (pricing)</strong> for when to use each.
	</p>
</div>

<h2>Approve or reject (both paths)</h2>
<p>
	Whether the quote was sent from <strong>Billing</strong> or <strong>Process enquiry</strong>, approval works the same way.
	An approver opens the quote from <strong>Quotations → In Approval</strong> (or from a notification).
	The screen shows <strong>Awaiting Approval</strong>, the assigned approver, and a PDF preview of what the customer will receive.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/quotation-approval-awaiting.png',
	'caption' => 'Shared approval view — Awaiting Approval badge, approver name, PDF preview, Approve and Reject actions.',
])

<ul>
	<li><strong>Approve</strong> (or <strong>Approve and send</strong>) — pick customer contacts, optional PDF email, optional comments; quote becomes <strong>Complete</strong> and can go to the customer.</li>
	<li><strong>Reject</strong> — give a reason; the quote returns to <strong>In Preparation</strong> so someone can fix it (re-send from Billing or Process enquiry when ready).</li>
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
	What you do next depends on where the quote started:
</p>

<div class="um-compare">
	<div class="um-compare__card">
		<h4>Started from Billing</h4>
		<ul>
			<li>The customer may already have received the PDF (depending on Approve and send choices).</li>
			<li>Open the quote to view or re-send documents from Actions.</li>
			<li>Use <strong>Create enquiry from quotation</strong> to start a Test Request — see <strong>Billing quotation workflow</strong>.</li>
		</ul>
	</div>
	<div class="um-compare__card">
		<h4>Started from Process enquiry</h4>
		<ul>
			<li>The customer may already have received the PDF from this flow.</li>
			<li>Return to the request view to continue sample workflow.</li>
			<li><strong>Build new</strong> required approval; <strong>Use existing</strong> skipped re-approval when linking a complete quote.</li>
		</ul>
	</div>
</div>

<h2>Channels at a glance</h2>
<ul>
	<li><strong>In-app bell</strong> — “needs approval” style notices.</li>
	<li><strong>Email</strong> — often with PDF; approve in the lab system.</li>
	<li><strong>Quotations → In Approval</strong> — main place to approve and send or reject.</li>
</ul>
