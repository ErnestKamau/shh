<h2>Equipment Disposal</h2>
<p>
	Open <strong>Equipment → Equipment Disposal</strong>.
	Page title: <strong>Disposal Management</strong>.
	Use this when an asset must leave the register through a controlled request, approval, and execution process.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/disposal.png',
	'caption' => 'Disposal Management — request tabs and Create New Disposal Request.',
])

<h2>List page</h2>
<ul>
	<li><strong>Create New Disposal Request</strong> — start a new request.</li>
	<li><strong>Manage Workflows</strong> — configure approval workflows (administrators).</li>
	<li><strong>Filter Options</strong>: Search, <strong>Date From</strong>, <strong>Date To</strong>, <strong>Clear</strong>.</li>
	<li>Tabs: <strong>Draft</strong>, <strong>Pending Approval</strong>, <strong>Approved</strong>, <strong>Rejected</strong>, <strong>All Requests</strong>.</li>
	<li>Columns typically: <strong>Actions</strong>, <strong>Id</strong>, <strong>Equipment</strong>, <strong>Requested By</strong>, <strong>Status</strong>, <strong>Risk Level</strong>, <strong>Method</strong>, <strong>Created</strong>.</li>
	<li>Row action: <strong>View</strong>.</li>
</ul>
<p>
	You can also start disposal from the Equipment List row action <strong>Request Disposal</strong>.
</p>

<h2>Create or edit a request</h2>
<p>Modal titles: <strong>Initiate Disposal Request</strong> / <strong>Edit Disposal Request</strong>.</p>
<ol>
	<li><strong>Equipment Selection</strong> — choose <strong>Equipment *</strong>.</li>
	<li>Review <strong>Equipment Metadata</strong> (may show calibration / maintenance status summaries).</li>
	<li><strong>Disposal Justification</strong>:
		<ul>
			<li><strong>Justification *</strong></li>
			<li><strong>Proposed Disposal Method *</strong> — Scrap, Donation, Auction, Recycling, Destruction (as listed).</li>
			<li><strong>Risk Assessment *</strong> — Low, Medium, High, Critical.</li>
			<li><strong>Regulatory Category *</strong></li>
		</ul>
	</li>
	<li><strong>Evidence Uploads</strong> — <strong>Upload Evidence Files</strong>.</li>
	<li><strong>Requester Information</strong> — confirm requester details shown.</li>
	<li>Buttons:
		<ul>
			<li><strong>Save As Draft</strong> — keep working later under Draft.</li>
			<li><strong>Submit For Approval</strong> — send into the approval workflow (button may show Submitting...).</li>
			<li><strong>Cancel</strong></li>
		</ul>
	</li>
</ol>

<h2>Detail page</h2>
<ul>
	<li><strong>Back</strong> — return to the list.</li>
	<li>Tabs: <strong>Details</strong>, <strong>Approvals</strong>, <strong>Audit Trail</strong>, <strong>Execution</strong>, and <strong>Report</strong> when executed.</li>
	<li><strong>Review &amp; Approve</strong> — opens the approval modal (for approvers).</li>
	<li><strong>Execute Disposal</strong> — after approval, complete the physical/administrative disposal.</li>
</ul>

<h3>Review &amp; Approve Disposal Request</h3>
<ol>
	<li>Read the <strong>Disposal Request Summary</strong>.</li>
	<li><strong>Approval Decision</strong>: <strong>Decision *</strong> Approve or Reject; <strong>Remarks *</strong>; optional <strong>Digital Signature</strong>.</li>
	<li>Submit with <strong>Approve</strong> / <strong>Reject</strong> / <strong>Submit Decision</strong> (Processing while saving).</li>
	<li><strong>Cancel</strong> closes without deciding.</li>
</ol>

<h3>Execute Disposal modal</h3>
<ul>
	<li>Confirm final method.</li>
	<li><strong>Executed By *</strong>, <strong>Witness</strong>, <strong>Compliance Checklist *</strong>.</li>
	<li><strong>Execute Disposal</strong> to finish, or <strong>Cancel</strong>.</li>
</ul>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-alert-circle-outline"></i></span>
	<p>
		Once executed, the asset is treated as disposed (dashboard Disposed counts, list behaviour).
		Keep evidence files complete before Submit For Approval so approvers can decide safely.
	</p>
</div>
