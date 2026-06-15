@extends('layouts.crm.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>Customer | CRM</title>
<style type="text/css">
	.tab-card { border: 1px solid #eee; }
	.tab-card-header { background: none; }
	.tab-card-header>.nav-tabs { border: none; margin: 0px; }
	.tab-card-header>.nav-tabs>li { margin-right: 2px; }
	/* Tab styles from imara-lims.css */
	.tab-card-header>.tab-content { padding-bottom: 0; }
	.my-small-text { font-size: 12px !important; }
	.livewire-modal-overlay {
		position: fixed;
		top: 0;
		right: 0;
		bottom: 0;
		left: 0;
		z-index: 1055;
		overflow-x: auto;
		overflow-y: auto;
	}
</style>
@endsection

@section('content2')
<main>
	<livewire:crm.customer.customer-show :customerId="$customerId" />
</main>
@endsection

@section('script2')
@endsection
