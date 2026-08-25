<h2>Quotations page layout</h2>
<p>
	Open <strong>Billing → Quotations</strong>.
	You will see two main tabs: <strong>Overview</strong> (KPIs) and <strong>Quotations</strong> (the working list).
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/overview-quotations.png',
	'caption' => 'Overview — Total, In Preparation, Finalised, Drafts, From Enquiry, Sent to Customer, PDF Generated, Finalised Value.',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/tabs-in-preparation.png',
	'caption' => 'Quotations tab — stage filters and quote rows (Quote In Preparation).',
])

<h2>Stage filters on the Quotations tab</h2>
<p>Use these to focus on work that needs you now:</p>
<ul>
	<li><strong>All</strong> — every quote you can see.</li>
	<li><strong>In Preparation</strong> — still being built or edited; use <strong>Move To workflow</strong> when ready for approval.</li>
	<li><strong>In Approval</strong> — waiting for an approver. Approvers approve or reject from this list.</li>
	<li><strong>Complete</strong> — finished quotes (ready to reuse or already sent).</li>
	<li><strong>Approval configuration</strong> — who may approve (role and people), not a quote list.</li>
</ul>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-tab"></i></span>
	<p>If a quote “disappears,” check you are not filtered to another stage.</p>
</div>
