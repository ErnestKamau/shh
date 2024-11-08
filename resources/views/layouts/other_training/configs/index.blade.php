@extends($module == "Skills-Matrix" &&  $config=='Roles' ? 'layouts.personnel.layout.app':'layouts.skillsmatrix.layout.app' , ['dataTable'=>true, 'select2'=>true])
<?php $module_text = implode(" ", explode("-", $module)); ?>
@section('title2')
  <title>{{ $config }} | {{ $module_text }}</title>
@endsection
@section('content2')
<style>
.colorDisplay {
    margin-left: 20px;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    border: 1px solid #000;
}
</style>
  <main>
    <?php
      $items = array(
        array(
          'link' => route('matrix'),
          'name' => $module_text,
          'icon' => null
        ),
        array(
          'link' => null,
          'name' => 'Configurations',
          'icon' => null
        ),
        array(
          'link' => null,
          'name' => $config." Area",
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
      <i class="mdi mdi-format-list-bulleted-type"></i> @if($config  == 'Training' ) Training Proficiency  @else {{ $config }}  @endif          
      <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-config"><i class="mdi mdi-plus"></i> Add</button>
    </h2>
    <br>
    <div class="table-responsive bg-light p-5">
      <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
        <thead class="bg-light p-2">
          <tr>
            @if($config  == 'Proficiency' || $config  == 'Roles' || $config  == 'Training')
            <th>Level</th>  
            @endif          
            <th>Name</th>
            <th>Description</th>
            
            @if($config  == 'Proficiency' || $config  == 'Training')
            <th>Color</th>
            @endif
            <th>Active?</th>
            <th></th>
          </tr>
        </thead>
        <tbody id="analytes-holder">
            @foreach($config_items as $item)
              <tr data-element="{{ $item->id }}">
               @if($config  == 'Proficiency' || $config  == 'Job Description' || $config  == 'Training')
                <td valign="center" nowrap style="font-size: 17px">
                  <span style="cursor: pointer">
                    <i class="mdi mdi-arrow-up-drop-circle move-analyte-up move-analyte" data-action="move-up"></i>
                  </span>
                  <span style="cursor: pointer">
                    <i class="mdi mdi-arrow-down-drop-circle move-analyte-down move-analyte" data-action="move-down"></i> </span>
                </td>  
                @endif                     
                <td>{{ $item->name ?? '' }}</td>
                <td>{{ $item->description ?? '' }}</td>
               
                @if($config  == 'Proficiency' || $config  == 'Training')
                <td><div class="colorDisplay" style="background-color: {{ $item->color ?? '' }};"></div></td>
                @endif
                <td class="text-small">{!! $item->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                <td>
									<span class="btn btn-sm btn-default text-info" data-target="#edit-config" data-toggle="modal" data-config = "{{ json_encode($item) }}">
										<i class="mdi mdi-pencil"></i>
									</span>	
                 			
								</td>
              </tr>
            @endforeach
        </tbody>
      </table>
    </div>
  </main>
@endsection

@section('script2')
  <div id="edit-config" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
      <!-- Modal content-->
      <form class="modal-content" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add {{ $config }}</h4>
        </div>
        <div class="modal-body" id="edit-config-fields"></div>
        <div class="modal-footer">
					<input type="hidden" name="module" value="" />
          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
	</div>
  <div id="add-config" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('add-module-skills-pre-configs', ['id'=>time(), 'config'=>$config, 'module'=>$module]) }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add {{ $config }}</h4>
        </div>
        <div class="modal-body">
					<div class="form-group">
						<label class="control-label">Name</label>
						<input type="text" class="form-control" name="name" value="" placeholder="Name..." required />
					</div>
					<div class="form-group">
						<label class="control-label">Description</label>
						<textarea type="text" class="form-control" name="description" placeholder="Description..."></textarea>
					</div>
          @if($config  == 'Proficiency' || $config  == 'Training')
          <div class="form-group">
						<label class="control-label">Select Color</label>
            <input type="color" class="form-control" name="color"  id="color" style="width:100px;" />
					</div> 
          @endif
          <div class="form-group">                            
            <label class="control-label"><input type="checkbox" name="active" value="1" checked> Active</label>
          </div>
        </div>
        <div class="modal-footer">
					<input type="hidden" name="module" value="{{ $module }}" />
          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
	</div>
	<script>
		var returnFields = function($data){

      if($data.type=='Proficiency' || $data.type  == 'Training'){

        return $(`
				<div class="form-group">
					<label class="control-label">Name</label>
					<input type="text" class="form-control" name="name" value="${$data.name || ''}" placeholder="Name..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Description</label>
					<textarea type="text" class="form-control" name="description" placeholder="Description...">${$data.description || ''}</textarea>
				</div>
        <div class="form-group">
					<label class="control-label">Color</label>
			    <input type="color" class="form-control" name="color"  id="color" value="${$data.color || ''}" style="width:100px;" required />
				</div>        
			`).clone();

      }else{

        return $(`
				<div class="form-group">
					<label class="control-label">Name</label>
					<input type="text" class="form-control" name="name" value="${$data.name || ''}" placeholder="Name..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Description</label>
					<textarea type="text" class="form-control" name="description" placeholder="Description...">${$data.description || ''}</textarea>
				</div>  
        <div class="form-group">                            
          <label class="control-label"><input type="checkbox" name="active" value="1" ${ $data.active == 1 ? 'checked' : '' } /> Active</label>
        </div>             
			`).clone();

      }

		
		}

		$(function(){
      

      var configureThemArrow = function() {
      $('#analytes-holder').find('tr').find('.move-analyte-up').addClass('text-success').removeClass('text-muted');
      $('#analytes-holder').find('tr:first-child').find('.move-analyte-up').addClass('text-muted').removeClass('text-success');

      $('#analytes-holder').find('tr').find('.move-analyte-down').addClass('text-success').removeClass('text-muted');
      $('#analytes-holder').find('tr:last-child').find('.move-analyte-down').addClass('text-muted').removeClass('text-success');
    }

    configureThemArrow();

    $('#analytes-holder').on('click', '.move-analyte:not(.text-muted)', function() {
        var action = $(this).data('action');
        var pTR = $(this).parents('tr');
        var i = pTR.index();
        console.log(pTR.data('element'))
        $.ajax({
         // url: "/move-skills-type/" + action + "/1/" + pTR.data('element'),
          url: "/move-skills-type/" + action + "/{{ $config }}/" + pTR.data('element'),
          dataType: 'json',
          beforeSend: function() {
            $('#analytes-holder').find('tr').find('.move-analyte-up').addClass('text-muted');
            $('#analytes-holder').find('tr').find('.move-analyte-down').addClass('text-muted');
          },
          success: function(js) {
            var siblingIndex = action == 'move-up' ? (i - 1) : (i + 1);
            siblingIndex = siblingIndex < 0 ? 0 : siblingIndex;

            var sibling = $('#analytes-holder').find('tr').get(siblingIndex);

            if (action == 'move-up') {
              $(sibling).before(pTR);
            } else {
              $(pTR).before(sibling);
            }

            configureThemArrow();
          }
        })
      });


			$('#edit-config').on('show.bs.modal', function(e){
				var config = $(e.relatedTarget).data('config');

				var field = returnFields(config);

				$('#edit-config-fields').html(field);

				$(this).find('form').prop('action', '/add-module-skills-pre-configs/'+config.id+'/{{ $config }}/{{ $module }}');
			})


			$('[name="has_credentials"]').on('change', function(){
				console.log(123);
				if($(this).is(':checked')){
					$("#passwords-holder").removeClass("hidden");
					$(".pass").attr('required', true);
				}
				else{
					$("#passwords-holder").addClass("hidden");
					$(".pass").removeAttr('required');
				}
			});

			$('[name="confirm_password"]').on('keyup', function(){
				var pass1 = $('[name="password"]').val();
				var pass2 = $(this).val();

				if(pass1 != pass2){
					$(this).siblings('.has-success').html('').addClass('text-success');
					$(this).siblings('.has-error').html(`<i class="mdi mdi-cancel"></i> Passwords did not match.`).addClass('text-danger');
				}
				else{
					$(this).siblings('.has-error').html('')
					$(this).siblings('.has-success').html('<i class="mdi mdi-check-circle"></i> Passwords Match!.')
				}
			});
		});
	</script>
@endsection
