<h2>What is Request to Order?</h2>
<p>
	<strong>Request to Order (RTO)</strong> is the part of Inventory Management where you buy goods from suppliers.
	When something is not already in store — or stock has fallen to reorder level — you start here instead of Request to Store.
</p>
<p>
	Open <strong>Inventory Management</strong> in the sidebar and expand <strong>Request to Order</strong>.
	You will see the four main stages of buying, plus Goods Return for sending goods back to a supplier when needed.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rto-sidebar.png',
	'caption' => 'Request to Order in the sidebar — Purchase Request, Request for Quotation, Purchase Orders, Goods Receipt, and Goods Return.',
])

<h2>The four stages</h2>
<div class="um-compare">
	<div class="um-compare__card">
		<h4>1. Purchase Request</h4>
		<p>A department asks for items to be bought. The request is approved before procurement can act.</p>
	</div>
	<div class="um-compare__card">
		<h4>2. Request for Quotation</h4>
		<p>Procurement invites suppliers, collects quotes, and awards the best offer.</p>
	</div>
	<div class="um-compare__card">
		<h4>3. Purchase Orders</h4>
		<p>The formal order is raised from the awarded quote, approved, and sent to the supplier.</p>
	</div>
	<div class="um-compare__card">
		<h4>4. Goods Receipt</h4>
		<p>Store staff receive the delivery into the correct store and slot so stock levels update.</p>
	</div>
</div>

<h2>Menu badges</h2>
<p>
	Red numbers beside each RTO menu item show how many records need attention at that stage — for example pending approvals or open requests.
	Click the menu item to open the list and work through them.
</p>

<h2>Who does what</h2>
<ul>
	<li><strong>Requester / department staff</strong> — creates and submits Purchase Requests.</li>
	<li><strong>Approvers</strong> — review and approve (or reject / return) according to Approval Configuration.</li>
	<li><strong>Procurement</strong> — creates RFQs from approved PRs, manages quotes, and raises Purchase Orders.</li>
	<li><strong>Store</strong> — receives goods against an approved Purchase Order.</li>
	<li><strong>Finance</strong> — may be involved on Goods Receipt where your process requires finance sign-off.</li>
</ul>

<h2>How the stages connect</h2>
<p>
	Each document links to the next through <strong>Requisition Flow</strong> on the record.
	When a Purchase Request is fully approved, Procurement clicks <strong>Send to Procurement (create RFQ)</strong> to start the quotation stage.
	After quotes are awarded and the RFQ is approved, Procurement creates the Purchase Order.
	When the supplier delivers, Store creates a Goods Receipt from that order.
</p>
<p>
	The next chapters walk through each stage in detail — starting with <strong>Purchase Request</strong>.
</p>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-cart-outline"></i></span>
	<p>
		If the item is already in store and you only need it issued to your department, use
		<a href="{{ route('usermanual.show', ['manual' => 'inventory', 'chapter' => 'request-to-store']) }}">Request to Store</a> instead.
	</p>
</div>
