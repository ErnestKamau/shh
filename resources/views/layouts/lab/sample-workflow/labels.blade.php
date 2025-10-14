<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
	<meta charset="utf-8">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
		integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
	<style>
		@media print {
			@page {
				size: landscape;
			}

			.card {
				clear: both;
				page-break-after: always !important;
			}

			#print {
				display: none;
			}

			body,
			html {
				margin: 0px;
				padding: 0px;
				font-weight:800;
			}
			
			
		}

		.card {
			background-color: white;
			width: 480px !important;
			max-height: 200px !important;
			font-size: 10px !important;
			border: none !important;
			clear: both;
			page-break-after: always !important;
		}

		body {
			background-color: white;
		}

		table {
			border-collapse: collapse;
			width: 100% !important;
			height: 100% !important;
		}

		table,
		tr,
		td {
			border: 0.5px solid #131313 !important
		}

		td {
			padding: 1px !important;
		}
		.barcode-container {
			display: flex;
			align-items: center;
			/* gap: 5px; */
		}

		.barcode {
			display: inline-block;
			width: auto;
			height: 30px;
		}

		.barcode svg {
			width: 100%;
			height: 100%;
		}

		.print-barcode {
			cursor: pointer;
		}
	</style>
</head>

<body>
	<span id="print" onclick="window.print()" class="btn btn-success float-right m-2"><i class="mdi mdi-printer"></i>
		Print</span>
	<div class="pl-3">
		<?php $check_company = getActiveCompany() ?>
		@foreach ($labels as $item)
				<div class="card p-2 mt-1 mb-4">
					<div class="card-body p-0">
						<b style="font-size:14px">{{$check_company->name}}</b>
						<table>
							<tbody>
								<tr>
									<td>Client Name</td>
									<td>{{ $item['client_name'] ?? 'N/A' }}</td>
								</tr>
								<tr>
									<td>Sample Point</td>
									<td>{{ isset($item['sample_point']) && $item['sample_point'] != 'N/A' ? 'Sample Point: ' . $item['sample_point'] : 'N/A' }}</td>
								</tr>
								<tr>
									<td>Lab Ref. No</td>
									<td>{{ $item['ref_no'] ?? 'N/A' }}</td>
								</tr>
								<tr>
									<td>Analysis Types</td>
									<td>{{ $item['analysis_types'] ?? 'N/A' }}</td>
								</tr>
								<tr>
									<td>Target Date</td>
									<td>{{ $item['target_date'] ?? 'N/A' }}</td>
								</tr>
								<tr>
									<td>Sample Code</td>
									<td>
										<span class="barcode">{!! DNS1D::getBarcodeSVG($item['sample_code'], 'C128B') !!}</span>
									</td>
								</tr>
								
							</tbody>
						</table>
				</div>

			</div>
		@endforeach
	</div>
</body>

</html>