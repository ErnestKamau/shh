@include('layouts.lab.partials.billing.amspec-import-modal', [
	'context' => 'quotation',
	'driver' => 'jquery',
	'importFormat' => 'excel',
	'importPricingMode' => 'per_package',
	'inputId' => 'ls-quote-import-file',
	'modalId' => 'ls-quote-import-modal',
])
