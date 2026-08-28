<h2>What is Request to Store?</h2>
<p>
	<strong>Request to Store</strong> is how a department asks for items that are
	<strong>already held in the store</strong> — you are not buying from a supplier;
	you are asking the store to issue stock.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/request-to-store.png',
	'caption' => 'Request to Store — ask for stock already on hand.',
])

<h2>When to use it</h2>
<ul>
	<li>The item exists in a store (check <strong>Stores → Contents</strong> if unsure).</li>
	<li>You need it for day-to-day work and do not need a new purchase.</li>
</ul>
<p>
	If the store has nothing left (or never stocked the item), raise a <strong>Purchase Request</strong> instead
	and follow Request to order.
</p>

<h2>Typical path (summary)</h2>
<ol>
	<li>Go to <strong>Inventory → Request to Store</strong>.</li>
	<li>Create a request, add items and quantities, and save.</li>
	<li>Use <strong>Get Approval</strong> so department / configured approvers sign off.</li>
	<li>When approved, store staff continue with <strong>Material Issuance</strong>
		(issuing the physical stock from the chosen store and slot).</li>
</ol>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-information-outline"></i></span>
	<p>
		This chapter will be expanded with full screen-by-screen steps for Request to Store and Material Issuance.
		For buying from suppliers, use the <strong>Request to order</strong> chapters.
	</p>
</div>
