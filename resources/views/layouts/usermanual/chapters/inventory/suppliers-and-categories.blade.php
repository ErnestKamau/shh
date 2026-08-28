<h2>Suppliers and categories</h2>
<p>
	<strong>Suppliers</strong> are the companies you buy from.
	<strong>Categories</strong> group your catalogue (chemicals, consumables, PPE, and so on).
	Both should be in place before staff raise Purchase Requests or send RFQs.
</p>

<h2>Suppliers</h2>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/suppliers.png',
	'caption' => 'Suppliers — vendor directory with contact and address details.',
])

<h3>The list page</h3>
<p>Open <strong>Inventory → Suppliers</strong>.</p>
<ul>
	<li><strong>Add</strong> — register a new supplier.</li>
	<li>Click the supplier <strong>name</strong> or the green <strong>eye</strong> icon to open the supplier page.</li>
	<li><strong>Delete</strong> (red bin) removes a supplier from the list.</li>
</ul>
<p>Useful columns: <strong>Rating</strong>, <strong>Email</strong>, <strong>Phone</strong>, address fields, and <strong>PIN Number</strong> for tax or company registration.</p>

<h3>Supplier view page</h3>
<p>
	The left side is <strong>Edit Supplier</strong> — update name, logo, address, currency, and other master fields, then <strong>Save</strong>.
	The overall <strong>rating</strong> (percentage with a star) appears next to the supplier name in the page title.
</p>
<p>Seven tabs on the right cover orders, receipts, returns, catalogue links, performance, and contacts. Each is explained below.</p>

<h4>Orders</h4>
<p>
	Purchase orders placed with this supplier.
	Columns include order number, description, due date, source RFQ (clickable link), who created it, date, and <strong>Status</strong> (for example Completed).
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/supplier-orders.png',
	'caption' => 'Supplier view — Orders tab: purchase orders from this vendor.',
])

<h4>Goods Receipt</h4>
<p>
	Deliveries recorded against this supplier’s purchase orders.
	Each row shows the GRN number, items received, due date, linked PO, who created the receipt, date, and status (for example <em>Goods Accepted</em> or <em>Awaiting Approval</em>).
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/supplier-goods-receipt.png',
	'caption' => 'Supplier view — Goods Receipt tab: deliveries from this vendor.',
])

<h4>Goods Return</h4>
<p>
	Items sent back to this supplier.
	Same style of table as Orders — order number, description, due date, source, created by, date, and status.
	When nothing has been returned yet, the table is empty.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/supplier-goods-return.png',
	'caption' => 'Supplier view — Goods Return tab: returns to this vendor.',
])

<h4>Supplier Categories</h4>
<p>
	Which product <strong>categories</strong> this supplier is approved to supply.
	<strong>+ Category</strong> links a new category; the <strong>Items</strong> column shows how many catalogue lines sit in that category for this vendor.
	<strong>Delete</strong> removes a category link.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/supplier-categories.png',
	'caption' => 'Supplier view — Supplier Categories tab: what this vendor supplies.',
])

<h4>Supplier Items</h4>
<p>
	Specific catalogue <strong>items</strong> linked to this supplier — image, item name, code, and brand.
	Use this when building RFQs or checking what you usually buy from them.
	<strong>Delete</strong> unlinks an item from the supplier.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/supplier-items.png',
	'caption' => 'Supplier view — Supplier Items tab: catalogue lines for this vendor.',
])

<h4>Ratings</h4>
<p>
	Overall score out of 100, with a breakdown by criteria (for example Communication, Quality).
	Procurement can <strong>Update Rating</strong> or add new <strong>Criteria</strong>.
	Each criterion shows a score and progress bar.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/supplier-ratings.png',
	'caption' => 'Supplier view — Ratings tab: performance score and criteria.',
])

<h4>Contacts</h4>
<p>
	Named people at the supplier — the inboxes and phone numbers that receive RFQs and order correspondence.
	<strong>+ Add Contact</strong> records a new person; columns include name, ID, phone, email, and PIN where used.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/supplier-contacts.png',
	'caption' => 'Supplier view — Contacts tab: people at the supplier company.',
])

<h2>Categories</h2>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/categories.png',
	'caption' => 'Categories — groups of items with stock summary.',
])

<h3>The list page</h3>
<p>Open <strong>Inventory → Categories</strong>.</p>
<ul>
	<li><strong>Find Item</strong> — search across categories for a specific product.</li>
	<li><strong>Add</strong> — create a category (name, description, optional image).</li>
	<li>Click the category <strong>name</strong> or <strong>Show</strong> to open its items.</li>
	<li><strong>Edit</strong> / <strong>Delete</strong> on each row.</li>
</ul>
<p>
	<strong>Available</strong> shows how much stock exists in that category (and pending quantities where shown).
</p>

<h3>Category view page (items in this category)</h3>
<p>
	Opening a category shows <strong>Inventory Items</strong> for that group.
</p>
<ul>
	<li><strong>Add Item</strong> — new catalogue line under this category.</li>
	<li><strong>Edit</strong> — change category name, description, image, or <strong>Default Location</strong> (preferred store and slot for new stock).</li>
	<li>Each row: image, name, code, SAP (or other third-party code), <strong>stock</strong> badge, classification, max order quantity, units, prices, description.</li>
	<li><strong>ReOrder</strong> link appears when stock is low — jumps to Purchase Request.</li>
	<li>Green <strong>eye</strong> or the item name opens the full <strong>item view page</strong> (see the Items chapter).</li>
</ul>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-account-group"></i></span>
	<p>
		Link suppliers to the categories and items they actually sell.
		Keep contact emails current so Send Out RFQs reaches the right person.
	</p>
</div>
