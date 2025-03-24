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
									<td>Lab Ref. No</td>
									<td>
										<span class="barcode">{!! DNS1D::getBarcodeSVG($item['ref_no'], 'C128B') !!}</span><span class="btn btn-sm btn-transparent print-barcode" ><i class="mdi mdi-printer text-info"></i></span>
									</td>
								</tr>
								<tr>
									<td>Date Received & Time</td>
									<td>{{ $item['date_received'].' '.$item['time'].' '.(is_numeric(str_replace(':','',$item['time'])) ? 'hrs' : '').' - '.$item['received_by'] }}</td>
								</tr>
								<tr>
									<td>Tests Required</td>
									<td>{{ $item['test'] }}</td>
								</tr>
								
							</tbody>
						</table>
				</div>

			</div>
		@endforeach
	</div>
</body>

</html>