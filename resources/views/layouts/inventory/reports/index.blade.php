@extends('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
	<title> Reports | {{ config('app.name', 'Laravel') }}</title>
	<link type="text/css" rel="stylesheet" href="/assets/css/theme-default/libs/select2/select2.css?1424887856" />
  <link type="text/css" rel="stylesheet" href="/assets/css/theme-default/libs/morris/morris.core.css?1420463396" />
	<style type="text/css">
		.table-row{
			margin-bottom: 15px;
			border-bottom: 3px solid #efefef;
		}

		.table-row:last-child{
			border-bottom: none !important;
		}

		.table-row:nth-child(even){
			background-color: rgba(0,0,0,0.03) !important;
		}

		#unsaved-query{
			position: fixed;
			bottom: 0px;
			right: 0px;
			z-index: 99;
		}
	</style>
	@endsection
@section('content2')
<section>
	<?php
		$items = array(
			array(
				'link' => route('inventory-home'),
				'name' => 'Inventory Management',
				'icon' => null
			),
			array(
				'link' => route('inventory-reports'),
				'name' => 'Reports',
				'icon' => null
			),
			array(
				'link' => '#',
				'name' => "",
				'icon' => null
			)
		);
	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>
	<h4 class="p-4">
		<i class="mdi mdi-format-list-bulleted-type"></i>Reports <small class="text-muted"></small>
		<button class="btn bg-white text-info badge-pill btn-sm hidden" data-toggle="modal" data-target="#filter-configurations-modla" id="unsaved-query"><i class="fa fa-info-circle"></i> Unsaved Report Query</button>
		@if(Request::get('edit') == 'true')
			<button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#filter-configurations-modla">
				<i class="md md-add"></i> Create Report
			</button>
		@endif
	</h4>
	<br>
	<div class="section-body">
		<div class="panel-group" id="accordion1">
			<div class="card panel">
				<div class="card-head" style="border-bottom: 1px solid #cecece" data-toggle="collapse" data-parent="#accordion1" data-target="#accordion1-1">
					<h4 class="pl-3 pr-3 pt-4 pb-2">
						Available Reports
						<div class="tools float-right pull-right">
							<a class="btn btn-icon-toggle"><i class="fa fa-angle-down"></i></a>
						</div>
					</h4>
				</div>
				<div id="accordion1-1" class="collapse in">
					<div class="card-body">
						<div class="table-responsive" style="clear: both">
							<table class="dt-table table table-condensed table-sm table-striped table-banded table-bordered">
								<thead>
									<tr>
										<th>No</th>
										<th>Name</th>
										<th>Raw SQL</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									@foreach ($configurations as $item)
										<tr>
											<td>{{ $loop->iteration }}</td>
											<td nowrap>{{ $item->name }}</td>
											<td nowrap>{{ substr($item->raw_sql, 0, 100) }} {{ strlen($item->raw_sql) > 100 ? '...' : '' }}</td>
											<td nowrap>
												<button data-title="{{ $item->name }}" data-raw="{{ $item->raw_sql }}" data-graph="{{ $item->configuration ?? '' }}" class="btn btn-primary run-query btn-sm" data-toggle="tooltip" title="Run this report"><i class="fas fa-play"></i></button>
                        @if(Request::get('edit') == 'true')
                          <button class="btn btn-success save-query btn-sm"
                            data-target="#edit-configurations-modal-{{ $loop->iteration }}" data-toggle="modal"
                            data-id="{{ $item->id }}" data-toggle="tooltip" title="Edit this report"><i class="fas fa-edit"></i>
                          </button>
												  <button class="btn btn-danger delete-query btn-sm" data-id="{{ $item->id }}" data-toggle="tooltip" title="Delete this report"><i class="fas fa-trash"></i></button>
                          <div class="modal fade" tabindex="-1" role="dialog" id="edit-configurations-modal-{{ $loop->iteration }}">
                            <div class="modal-dialog" role="document">
                              <form class="card form floating-label modal-content" method="POST" action="{{ route('update_report', ['id'=>$item->id]) }}">
                                {{ csrf_field() }}
                                <div class="card-head style-info">
																	<div class="modal-header">
																		<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Query Creator</h4>
																	</div>
                                </div>
                                <div class="card-body">
                                  <div class="form-group">
                                    <label>Report Query</label>
                                    <textarea class="form-control" name="report_query" rows="5">{{ $item->raw_sql }}</textarea>
                                  </div>
                                  <div class="form-group">
                                    <label>Graph Query</label>
                                    <textarea class="form-control" name="graph_query" rows="5">{{ $item->configuration ?? '' }}</textarea>
                                  </div>
                                </div>
                                <div class="card-footer">
                                  <div class="form-group small-padding">
                                    <button type="button" class="btn btn-flat btn-default ink-reaction" data-dismiss="modal" aria-label="Close">CANCEL</button>
                                    <button class="btn btn-flat btn-primary ink-reaction"><i class="fas fa-save"></i> SAVE</button>
                                  </div>
                                </div>
                              </form>
                            </div>
                          </div>
                        @endif
                      </td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div><!--end .panel -->
			<div class="card panel mt-2">
				<div class="card-head collapsed" style="border-bottom: 1px solid #cecece" data-toggle="collapse" data-parent="#accordion1" data-target="#accordion1-2" >
					<h4 class="pl-3 pr-3 pt-4 pb-2">
						Generated Report
						<div class="tools pull-right float-right">
							<a class="btn btn-icon-toggle"><i class="fa fa-angle-down"></i></a>
						</div>
					</h4>
				</div>
				<div id="accordion1-2" class="collapse">
					<div class="card-body">
            <div class="tab-content">
							<div class="tab-pane active" id="first8" style="position: relative">
								<div class="pull-right hide hidden mb-1" id="filter-report-btn">
									<div class="btn btn-success btn-sm float-left mr-2 mb-2" data-target="#print-this-report-modal" data-toggle="modal">
										<i class="mdi mdi-printer"></i> Print
									</div>
									<div class="btn btn-info btn-sm float-left mr-2 mb-2" data-target="#add-report-filter" data-toggle="modal">
										<i class="mdi mdi-filter"></i> Filter
									</div>
									<div class="float-left p-1" id="selected-filters"></div>
								</div>
								<div id="fetching-data-msg"></div>
                <div class="table-responsive" id="dynamically-generated-report">
									<table id="generated-report-table" class="table table-collapse server-side table-condensed table-bordered table-striped table-sm"></table>
								</div>
                <br>
                <h3><i class="fas fa-chart-pie"></i> Graph</h3>
                <br>
                <div class="row" style="position: relative">
                  <div class="table-responsive" id="dynamically-generated-graph"></div>
                  <div id="legend-for-graph" class="card" style="position: absolute; right: 0px; top: 0px; padding: 10px; z-index: 99"></div>
                </div>
              </div>
            </div>
					</div>
				</div>
			</div><!--end .panel -->
		</div>
		<div class="row" id="filter-tables"></div>
	</div>
</section>
@endsection
@section('script2')
<script src="/assets/js/libs/select2/select2.min.js"></script>
<script>
    // util function to convert the input to string type
    function convertToString(input) {

      if(input) {

        if(typeof input === "string") {

          return input;
        }

        return String(input);
      }
      return '';
    }


    // convert string to words
    function toWords(input) {

      input = convertToString(input);

      var regex = /[A-Z\xC0-\xD6\xD8-\xDE]?[a-z\xDF-\xF6\xF8-\xFF]+|[A-Z\xC0-\xD6\xD8-\xDE]+(?![a-z\xDF-\xF6\xF8-\xFF])|\d+/g;

      return input.match(regex);

    }


    // convert the input array to camel case
    function toCamelCase(inputArray) {

      let result = "";

      for(let i = 0 , len = inputArray.length; i < len; i++) {

        let currentStr = inputArray[i];

        let tempStr = currentStr.toLowerCase();

        if(i != 0) {

          // convert first letter to upper case (the word is in lowercase)
            tempStr = tempStr.substr(0, 1).toUpperCase() + tempStr.substr(1);

        }

        result +=tempStr;

      }

      return result;
    }


    // this function call all other functions

    function toCamelCaseString(input) {

      let words = toWords(input);

      return toCamelCase(words);

    }
  </script>

<script src="/assets/js/libs/raphael/raphael-min.js"></script>
<script src="{{ asset('assets/js/libs/morris.js/morris.min.js') }}"></script>
<script>
  var colors = ['grey','red','yellow','orange','green','lime','brown','cyan'];
	var table_row = $(`
	<h5>New Criteria</h5>
	<div class="row table-row">
		<div class="col-md-5">
			<div class="form-group">
				<label for="name">Table</label>
				<select class="form-control" name="loaded_table" placeholder="Select Table...">
					<option value=""></option>
					@foreach($tables_to_load as $table)
						<option value="{{ $table }}">{{ clear_underscore($table) }}</option>
					@endforeach
				</select>
			</div>
		</div>
		<div class="col-md-7">
			<div class="form-group">
				<label for="name">Selected Columns...</label>
				<select class="form-control" name="selected_columns" multiple placeholder="Select Table First...">
					<option value=""></option>
				</select>
			</div>
		</div>
		<div class="col-md-12">
			<h5>Criteria</h5>
			<div class="criteria-row">
				<div style="padding: 5px  0px">
					<label><input type="checkbox" class="check-select" /> Equal another table column</label>
				</div>
				<div class="row no-padding filter-fields-holder">
					<div class="col-md-3">
						<div class="form-group">
							<label for="name">Filter Column...</label>
							<select class="form-control" name="filter_column" placeholder="Select Column...">
								<option value="">Select Column...</option>
							</select>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label for="name">Operator</label>
							<select class="form-control" name="conditional_operator">
								<option value="=">=</option>
								<option value=">">&gt;</option>
								<option value=">=">&gt;=</option>
								<option value="<">&lt;</option>
								<option value="<=">&lt;=</option>
								<option value="!=">!=</option>
								<option value="LIKE">LIKE</option>
								<option value="LIKE %...%">LIKE %...%</option>
								<option value="NOT LIKE">NOT LIKE</option>
								<option value="IN (...)">IN (...)</option>
								<option value="NOT IN (...)">NOT IN (...)</option>
								<option value="BETWEEN">BETWEEN</option>
								<option value="NOT BETWEEN">NOT BETWEEN</option>
								<option value="IS NULL">IS NULL</option>
								<option value="IS NOT NULL">IS NOT NULL</option>
								<option value="REGEXP">REGEXP</option>
								<option value="REGEXP ^...$">REGEXP ^...$</option>
								<option value="NOT REGEXP">NOT REGEXP</option>
							</select>
						</div>
					</div>
					<div class="col-md-7 value-filter-fields filter-fields">
						<div class="form-group">
							<label for="name">Value</label>
							<input type="text" placeholder="Enter the filter value" class="form-control" name="filter_value" />
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	`);

	var columnFilterFields = $(`
		<div class="col-md-3 column-filter-fields filter-fields">
			<div class="form-group">
				<label for="name">Criteria Table</label>
				<select class="form-control" name="criteria_table">
					<option value="">Select Table...</option>
					@foreach($tables_to_load as $table)
						<option value="{{ $table }}">{{ clear_underscore($table) }}</option>
					@endforeach
				</select>
			</div>
		</div>
		<div class="col-md-4 column-filter-fields filter-fields">
			<div class="form-group">
				<label for="name">Filter Column...</label>
				<select class="form-control" name="criteria_filter_column">
					<option value="">Select Column...</option>
				</select>
			</div>
		</div>
	`);

	var valueFilterFields = $(`
		<div class="col-md-7 value-filter-fields filter-fields">
			<div class="form-group">
				<label for="name">Value</label>
				<input type="text" placeholder="Enter the filter value" class="form-control" name="criteria_filter_value" />
			</div>
		</div>
	`);
	$(function(){
    var currentGraph;
		var reportFields = [];
		var FilterFields = [];
		var selectedSQL;
		var preProcessedSQL;
		var selectedQueryTitle;

		$('#add-table-row').on('click', function(){
			var cloned = table_row.clone();
			$('#table-rows').before(cloned)
			$('#table-rows').parent().find('select').select2();
		});

		var add_report_filter = function(data){
			var $row = $(`
				<div class="mv-1 filter-row">
					<div><b>Filter</b></div>
					<div class="row">
						<div class="col-5">
							<div class="form-group">
								<label>Field</label>
								<select name="field" class="form-control filter-field" placeholder="Select Field..."><option></option></select>
							</div>
						</div>
						<div class="col-2">
							<div class="form-group">
								<label>Operator</label>
								<select name="operator" class="form-control filter-operator" placeholder="Operator...">
									<option></option>
									<option value="=">=</option>
									<option value=">">&gt;</option>
									<option value=">=">&gt;=</option>
									<option value="<">&lt;</option>
									<option value="<=">&lt;=</option>
									<option value="!=">!=</option>
									<option value="LIKE">LIKE</option>
								</select>
							</div>
						</div>
						<div class="col-5">
							<div class="form-group">
								<label>Value</label>
								<input name="value" class="form-control filter-value" placeholder="Select Value...">
							</div>
						</div>
					</div>
				</div>
			`);

			$.each(reportFields, function(f,fl){
				var $op = $(`<option value="${fl}">${fl}</option>`);
				$row.find('.filter-field').append($op);
			});

			return $row.clone();
		}

		var createFilterTags = function(){
			$('#selected-filters').empty();
			$.each(FilterFields, function(f,fl){
				var $filter = $(`
					<div class="float-left pull-left ml-1" style="background-color: #fff; border:1px solid #eaeaea; box-shadow: 0px 0px 5px #dfdfdf">
						<div class="p-1 float-left pull-left">${fl.field} ${fl.operator} ${fl.value}</div>
						<div class="p-1 close float-left pull-left" style="cursor: pointer; font-size: 18px; font-weight: 600"><i class="mdi mdi-close text-danger"></i></div>
					</div>
				`);

				$('#selected-filters').append($filter);

				$filter.find('.close').on('click', function(){
					var ind = FilterFields.indexOf(fl);
					FilterFields.splice(ind, 1);
					createFilterTags();
				});
			});

			fetchReportData(selectedSQL);
		}

		$('#add-filter-btn').on('click', function(){
			FilterFields = [];
			$('#add-report-filter').find('.filter-row').each(function(i, e){
				var key = $(e).find('.filter-field').children('option:selected').val();
				var operator = $(e).find('.filter-operator').children('option:selected').val();
				var value = $(e).find('.filter-value').val();

				FilterFields.push({
					'field': key,
					'operator': operator,
					'value': value,
				});
			});

			createFilterTags();
		});

		$('.add-new-filter-row').on('click', function(){
			var filterRow = add_report_filter();
			$('#add-report-filter').find('.modal-body').append(filterRow);
			filterRow.find('select').select2();
		});

		$('#add-report-filter').on('show.bs.modal', function(){
			$(this).find('.modal-body').empty();

			if(FilterFields.length > 0){
				$.each(FilterFields, function(f, fl){
					var $filterRow = add_report_filter(fl);
					$('#add-report-filter').find('.modal-body').append($filterRow);

					$filterRow.find('.filter-field').val(fl.field).trigger('change');
					$filterRow.find('.filter-operator').val(fl.operator).trigger('change');
					$filterRow.find('.filter-value').val(fl.value).trigger('change');
				});

				$(this).find('.modal-body').find('select').select2();
			}
			else{
				var filterRow = add_report_filter();
				$(this).find('.modal-body').append(filterRow);
				filterRow.find('select').select2();
			}
		});

		$('#print-this-report-modal').on('show.bs.modal', function(){
			$(this).find('.add-new-filter-row').trigger('click');
		});

		$('#print-this-report-modal').on('show.bs.modal', function(){
			$('#report-sql').val(preProcessedSQL);
			$('#report-title').val(selectedQueryTitle);
			$('.report-title').text(selectedQueryTitle);
		});

		$('#filter-configurations-modla').on('click', '.check-select', function(){
			var criteria_row = $(this).parents('.criteria-row');
			criteria_row.find('.filter-fields').remove();

			if($(this).is(':checked')){
				criteria_row.find('.filter-fields-holder').append(columnFilterFields.clone());
			}
			else{
				criteria_row.find('.filter-fields-holder').append(valueFilterFields.clone());
			}

			criteria_row.find('select.select2').select2();

		});

		$('.delete-query').on('click', function(){
			if(confirm("Are you sure you want to deletet this report?")){
				var $id = $(this).data('id');
				$.ajax({
					url: "/reports/delete",
					dataType: 'json',
					type: "POST",
					data: {"id": $id, "_token": $('[name="_token"]').val()},
					success: function(){
						location.reload();
					}
				});
			};
		});

		$('.run-query').on('click', function(){
			var sql = $(this).data('raw');
			selectedQueryTitle = $(this).data('title');
			var graph = $.trim( $(this).data('graph') );
			selectedSQL = sql;
			FilterFields = [];
      fetchReportData(sql);
			$('#selected-filters').empty();

      if(graph.indexOf("SELECT") > -1){
        $('[data-target="#accordion1-2"]').trigger('click');
        fetchGraphData(graph);
      }
      else{
        $('#dynamically-generated-graph').html(`<div class="alert alert-info text-muted fa-2x">
          <i class="fas fa-info-circle"></i> Report has no graphical report.
        </div>`);
      }
		});

		$('#filter-configurations-modla').on('change', '[name="criteria_table"]', function(){
			var selectedVal = $(this).val();
			var filterHolder = $(this).parents('.filter-fields-holder');
			$.ajax({
				url: "/reports/fields/"+selectedVal,
				dataType: "json",
				beforeSend: function(){
					filterHolder.find('[name="criteria_filter_column"]').html('<option value="">Loading Fields...</option>');
				},
				success: function(js){
					filterHolder.find('[name="criteria_filter_column"]').html(`<option value="">Select Column...</option>`);
					$.each(js, function(j,s){
						filterHolder.find('[name="criteria_filter_column"]').append(`<option value="${s}">${s}</option>`);
					});
				}
			});
		});


		$('#filter-configurations-modla').on('change', '[name="loaded_table"]',function(){
			var selectedVal = $(this).val();
			var parentDiv = $(this).parents('.table-row');
			$.ajax({
				url: "/reports/fields/"+selectedVal,
				dataType: "json",
				beforeSend: function(){
					parentDiv.find('[name="selected_columns"], [name="filter_column"]').html('<option value="">Loading Fields...</option>');
				},
				success: function(js){
					parentDiv.find('[name="selected_columns"], [name="filter_column"]').html(`<option value="">Select Columns...</option>`);
					parentDiv.find('[name="selected_columns"]').append(`<option value="*">Select All Columns (*)...</option>`);
					$.each(js, function(j,s){
						parentDiv.find('[name="selected_columns"], [name="filter_column"]').append(`<option value="${s}">${s}</option>`);
					});
				}
			});
		});

		var criteriaObject;

		$('#create-criteria-object').on('click', function(){
      criteriaObject = {};
      $('#unsaved-query').removeClass('hidden');
			$('.table-row').each(function(i, e){
				var loaded_table = $(e).find('[name="loaded_table"]').val();
				var selected_columns = $(e).find('[name="selected_columns"]').val();
				var filter_column = $(e).find('[name="filter_column"]').val();
				var conditional_operator = $(e).find('[name="conditional_operator"]').val();
				if(criteriaObject[loaded_table]==undefined){
					criteriaObject[loaded_table] = {
						"tables": [loaded_table],
						"columns": selected_columns.map((val, index) => val=='*' ? '`'+loaded_table+'`.'+val : '`'+loaded_table+'`.`'+val+'`'),
						"criteria":[]
					};
				}

				if($.trim(filter_column)!==""){
					var criteriaOps = {
						"filter_column": '`'+loaded_table+'`.`'+filter_column+'`',
						"conditional_operator": conditional_operator
					}

					if($(e).find('[name="filter_value"]').length > 0){
						criteriaOps['filter_value'] = "'"+$.trim($(e).find('[name="filter_value"]').val())+"'" || "''";
					}
					else{
						criteriaObject[loaded_table]['tables'].push(loaded_table);
						criteriaOps['criteria_table'] = $(e).find('[name="criteria_table"]').val();
						criteriaOps['filter_value'] = '`'+criteriaOps['criteria_table']+'`.`'+$(e).find('[name="criteria_filter_column"]').val()+'`';
					}

					criteriaObject[loaded_table]['criteria'].push(criteriaOps);
				}
			});

			generateRawSQL(criteriaObject);
    });

    var fetchGraphData = function(sql){
      $.ajax({
        url: "/reports/fetch",
        dataType: 'json',
        type: "POST",
        data: {"raw": sql, "_token": $('[name="_token"]').val()},
        beforeSend: function(){
          $('#dynamically-generated-graph').empty();
          $('#fetching-data-msg').html('<center style="padding: 100px"><i class="fas fa-spin fa-4x fa-spinner text-muted"></i><br>FETCHING DATA</center>');
        },
        success: function(js){
					$('#fetching-data-msg').empty();
          var firstRecord  = js.data[0]; //first focus on the graph
          if(firstRecord != undefined){
            if(firstRecord.datetime){
              drawLineChart(js.data);
            }
            else{
              drawPieChart(js.data);
            }
          }
          else{
            $('#dynamically-generated-graph').html(`<div class="alert alert-war text-muted fa-2x">
              <i class="fas fa-info-circle"></i> Report has no graphical report.
            </div>`);
          }
        }
      });
    }

    var drawLineChart = function($data){
      var ykeys = removeElement('datetime', Object.keys($data[0]));
      var camelKeys = camelCaseArray(ykeys);

      currentGraph = Morris.Line({
        element: 'dynamically-generated-graph',
        data: $data,
        xkey: 'datetime',
        ykeys: ykeys,
        labels: camelKeys,
        lineColors: colors
      });

      $.each(camelKeys, function(i, e){
        var li = $(`
          <li><i class="fas fa-square" style="color: ${colors[i]}"></i> ${e} </li>
        `);

        $('#legend-for-graph').find('ul').append(li);
      });
    }

    var drawPieChart = function($data){
      currentGraph = Morris.Donut({
        element: 'dynamically-generated-graph',
        data: $data,
        colors: colors
      });

      $('#legend-for-graph').html('<ul style="list-style: none"></ul>');

      $.each($data, function(i, e){
        var li = $(`
          <li><i class="fas fa-square" style="color: ${colors[i]}"></i> ${e.label}</li>
        `);

        $('#legend-for-graph').find('ul').append(li);
      });

    }
		var setTable;
		var fetchReportData = function(sql){
			$.ajax({
				url: "/reports/fetch",
				dataType: 'json',
				type: "POST",
				data: {"raw": sql, "_token": $('[name="_token"]').val(), "filters": FilterFields},
				beforeSend: function(){
          $('[data-target="#accordion1-2"]').trigger('click');
					$('#fetching-data-msg').html('<center style="padding: 100px"><i class="fas fa-spin fa-4x fa-spinner text-muted"></i><br>FETCHING DATA</center>');
				},
				success: function(js){
					$('#fetching-data-msg').html('');

					$('#generated-report-table').empty();

					var newTable = $('#generated-report-table');

					var columns = Object.keys(js.data[0]);

					preProcessedSQL = js.sql;

					reportFields = columns;

					columns.unshift("#");

					$('#filter-report-btn').removeClass('hide').removeClass('hidden');

					var $data = [];

					$.each(js.data, function(i, j){
						var row = Object.values(j);
						row.unshift(i+1);

						$data.push(row);
					});

					var $columns = [];

					$.each(columns, function(a, b){
						$columns.push({title: b.toUpperCase()});
					});

					// console.log($data, $columns);

					if(setTable){
						setTable.destroy();
					}

					setTable = $('#generated-report-table').DataTable({
						dom: 'Blfrtip',
						buttons: [
							'copy', 'csv', 'excel', 'pdf', 'print'
						],
						"order": [],
						"language": {
							// "lengthMenu": lengthMenu,
							"search": '<i class="fa fa-search"></i>',
							"paginate": {
								"previous": '<i class="fa fa-angle-left"></i>',
								"next": '<i class="fa fa-angle-right"></i>'
							}
						},
						data: $data,
						columns: $columns
					});
				}
			})
		}

		var generateRawSQL = function(obj){
			$('[name="raw_sql"]').parent().removeClass('hidden');

			var sql = "SELECT ";
			var tables = [];
			var columns = [];
			var criteriaArr = [];
			$.each(obj, function(tbl, crit){
				tables = tables.concat(crit.tables);
				columns = columns.concat(crit.columns);

				$.each(crit.criteria, function(a, b){
					criteriaArr.push(b.filter_column+' '+b.conditional_operator+' "'+b.filter_value+'"');
				});
			});

			sql = sql+removeDups(columns).join(', ')+' FROM '+removeDups(tables).join(', ')+(criteriaArr.length > 0 ? ' WHERE '+removeDups(criteriaArr).join(' AND '): '');
			$('[name="raw_sql"]').val(sql);

			fetchReportData(sql);
		}

		var saveConfiguration = function(){
			var qName = $('[name="Query Name"]').val();
			if($.trim(qName) == ""){
				qName = prompt("Query Name...");
			}

			var raw_sql = $.trim($('[name="raw_sql"]').val());

			if(raw_sql == ""){
				generateRawSQL(criteriaObject);

			}

			if(raw_sql == ""){
				alert("Raw SQL cannot be null ensure that you have configurations in place!");
				return 0;
			}

			var postOBJ = {
				'_token': $('[name="_token"]').val(),
				'name': qName || 'Un-named Query',
				'configuration': $.trim($('[name="graph_query"]').val()),
				'raw_sql': raw_sql
			}

			$.ajax({
				url: "/reports/save",
				dataType: 'json',
				type: "POST",
				data: postOBJ,
				success: function(){
					window.location.reload();
				}
			})
		}

		$('#run-query-fetch-data').on('click', function(){
			saveConfiguration();
		})
	});

	function removeDups(names) {
		let unique = {};
		names.forEach(function(i) {
			if(!unique[i]) {
				unique[i] = true;
			}
		});
		return Object.keys(unique);
  }

  function removeElement(elem, arr){
    arr.splice( arr.indexOf(elem), 1 );
    return arr;
  }

  function camelCaseArray($arr){
    return $arr.map(function(val, ind){
			val = (val.split('_')).join(' ');

      return toCamelCaseString(val);
    })
  }
</script>
<div class="modal fade" tabindex="-1" role="dialog" id="filter-configurations-modla">
	<div class="modal-dialog" role="document">
		<div class="card form floating-label modal-content">
			{{ csrf_field() }}
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Query Creator</h4>
			</div>
			<div class="card-body modal-body">
				<div id="table-rows"></div>
				<div class="form-group">
					<button class="btn btn-primary btn-flat btn-block" id="add-table-row"><i class="fas fa-plus"></i> Add New Criteria</button>
				</div>
				<div class="form-group">
					<label>Query Name</label>
					<input type="text" name="Query Name" class="form-control" placeholder="Query Name..." />
				</div>
				<div class="form-group hidden">
					<label>Raw SQL</label>
					<textarea class="form-control" name="raw_sql"></textarea>
				</div>
				<div class="form-group">
					<label>Graph Query</label>
					<textarea class="form-control" name="graph_query"></textarea>
				</div>
			</div>
			<div class="card-footer">
				<div class="form-group small-padding">
					<button type="button" class="btn btn-flat btn-default ink-reaction" data-dismiss="modal" aria-label="Close">CANCEL</button>
					<button class="btn btn-flat btn-accent ink-reaction" id="create-criteria-object" aria-label="Close">GENERATE QUERY</button>
					<button class="btn btn-flat btn-primary ink-reaction" id="run-query-fetch-data">SAVE QUERY</button>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" tabindex="-1" role="dialog" id="add-report-filter">
	<div class="modal-dialog" role="document">
		<div class="card form floating-label modal-content">
			<div class="modal-header">
				<h4 class="modal-title" style="width: 100%">
					<i class="mdi mdi-pencil"></i> Report Filters
					<span class="float-right pull-right btn btn-success btn-sm add-new-filter-row">
						<i class="mdi mdi-plus"></i> Filter
					</span>
				</h4>
			</div>
			<div class="card-body modal-body"></div>
			<div class="card-footer">
				<div class="form-group small-padding">
					<button type="button" class="btn btn-flat btn-default ink-reaction" data-dismiss="modal" aria-label="Close">CANCEL</button>
					<button class="btn btn-flat btn-primary ink-reaction" id="add-filter-btn" data-dismiss="modal">ADD FILTER</button>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" tabindex="-1" role="dialog" id="print-this-report-modal">
	<div class="modal-dialog" role="document">
		<form class="card form floating-label modal-content" method="POST" action="{{ route('report_print') }}" target="_blank">
			<div class="modal-header">
				<h4 class="modal-title" style="width: 100%">
					<i class="mdi mdi-printer"></i> Print this Report
				</h4>
			</div>
			<div class="card-body modal-body">
				@csrf
				<div class="form-group">
					<label>Report Title</label>
					<input type="text" class="form-control" name="title" id="report-title" />
				</div>
				<div class="alert alert-primary">
					<i class="mdi mdi-information"></i> Continue to print this report?
				</div>
				<input type="hidden" name="sql" id="report-sql" />
			</div>
			<div class="card-footer">
				<div class="form-group small-padding">
					<button type="button" class="btn btn-flat btn-default ink-reaction" data-dismiss="modal" aria-label="Close">CANCEL</button>
					<button class="btn btn-flat btn-primary ink-reaction">YES, PRINT</button>
				</div>
			</div>
		</form>
	</div>
</div>
@endsection