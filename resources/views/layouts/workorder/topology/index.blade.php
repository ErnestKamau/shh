@extends('layouts.workorder.layout.app', ['select2'=>true])

@section('title2')
<title>Topology| Workorder Management</title>
@endsection
@section('content2')
<main>
	<?php
	$items = array(
		array(
			'link' => route('workorder-home'),
			'name' => 'Workorder Management',
			'icon' => null
		),
		array(
			'link' => '#',
			'name' => 'Topology',
			'icon' => null
		)
	);
	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>
	<h2 class="p-4">
		<i class="mdi mdi-lan"></i> Topology
	</h2>
	<br>
	<div class="table-responsive">
		<div class="pb-2">
			<small class="btn btn-flat btn-danger btn-sm" data-target="#modal-add-topology" data-toggle="modal">
				<i class="fas fa-plus"></i> Add Top Level Topology
			</small>
		</div>
		<br>
		<div id="topology-holder" style="mt-2"></div>
	</div>
@endsection
@section('script2')
	<div id="modal-add-topology" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<form class="modal-content" method="POST" enctype="multipart/form-data">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Topology</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label for="name">Name</label>
						<input type="text" class="form-control" id="name" name="name" placeholder="Name...">
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
	<div id="modal-edit-topology" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<form class="modal-content" method="POST" enctype="multipart/form-data">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-edit"></i> Edit Topology</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<div class="form-group">
							<label for="name">Name</label>
							<input type="text" class="form-control" id="name" name="name" placeholder="Name...">
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
	<div id="modal-remove-topology" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<form class="modal-content" method="POST" enctype="multipart/form-data">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-delete"></i> Remove Topology Item</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<div class="alert alert-danger alert-callout">
							<i class="fas fa-exclamation-triangle fa-1x"></i> Are you sure that you want to remove this topology?
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary"><i class="mdi mdi-delete"></i> Remove</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
	<script type="text/javascript">
		var getTopologyRow = function(data){
			var $row = $(`
				<div class="topology-row" data-name="${data.name}" data-id="${data.id}" data-parent="${data.parent}" data-level="${data.level}"></div>
			`);

			var $text = $(`
				<div class="topology-text"></div>
			`);

			$text.append(`<div class="left-icon text-muted dropdown-toggler"><i class="fas fa-plus"></i> </div> `);

			$text.find('.dropdown-toggler').on('click', function(){
				var parnt = $(this).parents('.topology-row').first();
				var bdy = parnt.find('.topology-body');

				bdy.toggleClass('visible');

				if(bdy.is(':visible')){
					$(this).find('i').removeClass('fa-plus').addClass('fa-minus');
					fetchTopology(data.id, bdy);
				}
				else{
					$(this).find('i').removeClass('fa-minus').addClass('fa-plus');
				}
			});

			$text.append(`<div class="right-icon delete-topology" data-target="#modal-remove-topology" data-toggle="modal"><i class="fas fa-trash text-danger"></i></div>`);
			$text.append(`<div class="right-icon edit-topology" data-target="#modal-edit-topology" data-toggle="modal"><i class="fas fa-edit  text-info"></i></div>`);
			// $text.append(`<div class="right-icon add-topology" data-target="#modal-add-topology" data-toggle="modal">
			// 	<i class="fas fa-plus  text-success"></i>
			// </div>`);
			$text.append(`<div class="text">${data.name}</div>`);

			$row.append($text);

			$row.append(`<div class="topology-body"><span class="md md-plus"></span> Add Topology Item</div>`);

			return $row;
		}

		var fetchTopology = function(parentID, parent){
			$.ajax({
				url: "/topology/"+parentID,
				dataType: "json",
				beforeSend: function(){
					parent.html('<span class="text-center"><i class="fas fa-spin fa-spinner"></i> Fetching Topology...</span>');
				},
				success: function(js){
					parent.empty();
					$.each(js, function(j,s){
						var $row = getTopologyRow(s);
						parent.append($row);
					});

					if(parentID > 0){
						parent.append(`<div style="padding: 7px 0px" class="topology-row text-info" data-target="#modal-add-topology" data-toggle="modal">
							<span class="md md-add" style="margin-left: 6px"></span> Add Topology Item
						</div>`);
					}
				}
			});
		}

    $(function(){
			$('.open-modal').on('click', function(){
				$('#modal-edit-topology').modal('show');
			})

			$(document).on('show.bs.modal', '#modal-add-topology', function(e){
				// alert("Yes");
				var parentRow = $(e.relatedTarget).parents('.topology-row').first();
				var parentId = parentRow.data('id');
				var level = parentRow.data('level');
				parentId = parentId || 0;
				level = level || 0;


				$(this).find('.card-head').find('.modal-title').html('<i class="mdi mdi-plus"></i> '+parentId == 0 ? 'Creating Top Level Topology' : 'Creating Level '+level+' Topology');

				$(this).find('form').attr('action', '/topology/'+parentId);
				$(this).find('form').prop('action', '/topology/'+parentId);
			});

			$('#modal-edit-topology').on('show.bs.modal', function(e){
				var parentRow = $(e.relatedTarget).parents('.topology-row').first();
				var mainId = parentRow.data('id');
				var level = parentRow.data('level');
				var name = parentRow.data('name');

				mainId = mainId || 0;
				level = level || 0;

				$(this).find('.card-head').find('.modal-title').html('<i class="mdi mdi-edit"></i> Edit Topology : '+name);

				$(this).find('[name="name"]').val(name).trigger('change');

				$(this).find('form').attr('action', '/topology/'+mainId+'/edit');
				$(this).find('form').prop('action', '/topology/'+mainId+'/edit');
			});

			$('#modal-remove-topology').on('show.bs.modal', function(e){
				// alert("Yes");
				var parentRow = $(e.relatedTarget).parents('.topology-row').first();
				var mainId = parentRow.data('id');
				var level = parentRow.data('level');
				var name = parentRow.data('name');
				mainId = mainId || 0;
				level = level || 0;

				$(this).find('.card-head').find('.modal-title').html('<i class="mdi mdi-delete"></i> Remove Topology : '+name);

				$(this).find('form').attr('action', '/topology/'+mainId+'/remove');
				$(this).find('form').prop('action', '/topology/'+mainId+'/remove');
			});

			fetchTopology(0, $('#topology-holder'));
    });
	</script>
@endsection