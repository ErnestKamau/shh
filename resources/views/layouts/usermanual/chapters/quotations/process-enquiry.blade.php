<h2>When do you open this window?</h2>
<p>
	After a request arrives — from the <strong>customer portal</strong> or from a <strong>walk-in / direct registration</strong>
	where the testing form has been filled — open the request and use <strong>Process enquiry</strong>
	(sometimes shown as Process Request).
</p>
<p>
	The purpose of this window is simple: <strong>set prices for the tests on this request</strong>,
	either by creating a <strong>new quotation</strong> or by <strong>picking an existing one</strong>,
	then move it toward approval and the customer.
</p>
<ul>
	<li>If the quote is <strong>not yet approved</strong>, use this flow to <strong>send it for approval</strong>.</li>
	<li>If it is approved but <strong>not yet sent to the customer</strong>, you can use the same area to <strong>send it to the customer</strong>.</li>
</ul>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/process-enquiry.png',
	'caption' => 'Process Request — Build new from this enquiry, with prices, samples, and VAT.',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/process-enquiry-2.png',
	'caption' => 'Same window — choose how to price, then review lines before Send for Approval.',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/process-enquiry-3.png',
	'caption' => 'Build new vs Use existing — pick the path that matches the job.',
])

<h2>Two ways to attach a price</h2>
<div class="um-compare">
	<div class="um-compare__card">
		<h4>Build new from this enquiry</h4>
		<ul>
			<li>Create a <strong>fresh quotation</strong> from the samples and tests on this request.</li>
			<li>Adjust unit prices, sample counts, and VAT per line.</li>
			<li>Tick <strong>LOQ</strong> and <strong>MU%</strong> at the top if those columns should appear on the <strong>customer PDF</strong> (they do not change the money — only what is printed).</li>
			<li>When ready, press <strong>Send for Approval</strong> (see <strong>Approval &amp; notifications</strong> — Process enquiry path).</li>
			<li>After approval, send the quote to the customer from this flow when your process says so.</li>
			<li><strong>Use when:</strong> this job needs its own prices or mix of tests.</li>
		</ul>
	</div>
	<div class="um-compare__card">
		<h4>Use existing quotation</h4>
		<ul>
			<li>Link a quotation that is already <strong>complete</strong> and still valid for this customer.</li>
			<li>You skip building lines again and you <strong>do not start a new approval</strong> for that link step.</li>
			<li>You can still <strong>send to the customer</strong> if it has not been sent yet.</li>
			<li><strong>Use when:</strong> the customer already agreed a standing or recent quote that covers this work.</li>
		</ul>
	</div>
</div>

<h2>Similarities and differences with Billing quotations</h2>
<p>
	Quotes created here are the <strong>same records</strong> you see under <strong>Billing → Quotations</strong>
	(In Preparation → In Approval → Complete). Process enquiry is the guided door from the request;
	the Quotations page is where finance and commercial teams browse every quote.
</p>

<div class="um-compare">
	<div class="um-compare__card">
		<h4>Same</h4>
		<ul>
			<li>One quotation record, same tabs, same approvers, same customer PDF.</li>
			<li>Both paths end in <strong>Quotations → In Approval</strong> for approve/reject.</li>
			<li>LOQ and MU% columns work the same way on the PDF.</li>
		</ul>
	</div>
	<div class="um-compare__card">
		<h4>Different</h4>
		<ul>
			<li><strong>Entry point</strong> — request view vs Billing → Quotations.</li>
			<li><strong>Send button</strong> — <strong>Send for Approval</strong> here vs <strong>Move To workflow</strong> on Billing.</li>
			<li><strong>Use existing</strong> skips re-approval; Billing always approves new quotes.</li>
			<li>Billing path can <strong>Create enquiry from quotation</strong> after Complete; Process enquiry starts from the request.</li>
		</ul>
	</div>
</div>

<p>
	For step-by-step approval from both paths, see <strong>Approval &amp; notifications</strong>.
	For the full Billing journey (add quote → prepare → approve → create enquiry), see <strong>Billing quotation workflow</strong>.
</p>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-file-document-outline"></i></span>
	<p><strong>LOQ</strong> and <strong>MU%</strong> on the table: turn them on only when the customer PDF should show those columns. Pricing still comes from unit price × samples.</p>
</div>
