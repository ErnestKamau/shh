<h2>What is a quotation?</h2>
<p>
	A <strong>quotation</strong> is the price offer you prepare for a customer’s testing work.
	It lists the tests (or packages), how many samples, the unit prices, tax where it applies,
	and the total. Once it is approved and sent (and accepted when your process requires that),
	the lab can continue with that request.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/overview-quotations.png',
	'caption' => 'Quotations → Overview — counts for preparation, finalised, sent to customer, PDF, and value.',
])

<h2>What is a pricelist?</h2>
<p>
	A <strong>pricelist</strong> is the price book: agreed selling (and cost) prices for tests or packages,
	usually linked to customers and a currency. When you price a request or build a quote,
	prices can come from that book so you are not inventing numbers each time.
</p>

<h2>How they work together</h2>
<ol>
	<li>Keep pricelists up to date (billing mode: <strong>per package</strong> or <strong>per test</strong>) and assign the right customers.</li>
	<li>Build a quote from <strong>Billing → Quotations</strong> (see <strong>Billing quotation workflow</strong>), or price later from <strong>Process enquiry</strong> on a request.</li>
	<li>Line totals use unit price × number of samples (or package price × samples).</li>
	<li>Catalogue changes become live selling prices only after <strong>Apply Price Changes</strong> on the pricelist.</li>
</ol>

<h2>Two ways to price a quotation</h2>
<div class="um-compare">
	<div class="um-compare__card">
		<h4>Use a pricelist</h4>
		<ul>
			<li>Assign the customer to a pricelist under Billing → Pricelists.</li>
	<li>On the quote, open <strong>Commercial</strong> and select an eligible list — or use <strong>+ Add line</strong> for manual pricing.</li>
			<li>Default line mode is <strong>per package</strong>; tick <strong>Per parameter</strong> to bill each test separately.</li>
		</ul>
	</div>
	<div class="um-compare__card">
		<h4>Quote without a pricelist</h4>
		<ul>
			<li>If no list is assigned, the chooser shows “No eligible pricelist.”</li>
			<li>Add lines with <strong>+ Add line</strong>, or use <strong>Import</strong> from an AmSpec file.</li>
			<li>Set unit prices yourself; you can still send the quote for approval.</li>
		</ul>
	</div>
</div>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-information-outline"></i></span>
	<p>
		Think of the pricelist as the price book, and the quotation as the customer-facing offer for one job.
		Read in order: <strong>Quote preparation &amp; math</strong> → <strong>Billing quotation workflow</strong> →
		<strong>Approval &amp; notifications</strong> → <strong>Process request (pricing)</strong> for how both paths fit together.
	</p>
</div>
