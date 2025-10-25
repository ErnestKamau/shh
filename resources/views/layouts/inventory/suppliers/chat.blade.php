@extends('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true])


@section('title2')
	<title> Chat-System </title>
	<style type="text/css">
		.tab-card {
			border:1px solid #eee;
		}

		.tab-card-header {
			background:none;
		}
		/* Default mode */
		.tab-card-header > .nav-tabs {
			border: none;
			margin: 0px;
		}
		.tab-card-header > .nav-tabs > li {
			margin-right: 2px;
		}
		.tab-card-header > .nav-tabs > li > a {
			border: 0;
			border-bottom:2px solid transparent;
			margin-right: 0;
			color: #737373;
			padding: 2px 15px;
		}

		.tab-card-header > .nav-tabs > li > a.show {
			border-bottom:2px solid #007bff;
			color: #007bff;
		}
		.tab-card-header > .nav-tabs > li > a:hover {
			color: #007bff;
		}

		.tab-card .nav-link.active{
			background-color: #dadccd !important;
			border: 1px solid #cccebf !important;
		}

		.tab-card-header > .tab-content {
			padding-bottom: 0;
		}

		.my-small-text{
			font-size: 13px !important;
		}

		.removeThis {
			z-index: 12;
			position: absolute;
			cursor: pointer;
			top: 0px;
			right: 2px;
			padding: 1px 4px;
			font-size: 12px;
			background-color: red;
			border-radius: 50%;
			color: #fff;
			box-shadow: 0px 0px 5px rgba(0,0,0,0.08);
		}
		#chat{
			box-shadow: 5px 5px  5px grey;
			width:60%;
			
			/* background-color: grey; */

		
		}
		#chat:hover{
			box-shadow: 10px 10p 5px black;
			transform: scale(1.05)
		}

	</style>
@endsection
@section('content2')
	<main>
		<?php
      $items = array(
        array(
          'link' => route('inventory-home'),
          'name' => 'Inventory Management',
          'icon' => null
        ),
        array(
          'link' => route('inventory-suppliers'),
          'name' => 'Suppliers',
          'icon' => null
        ),
				array(
          'link' => '#',
          'name' => $supplier->name,
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
		<h2 class="p-4">
			Imara-Lims Chat System
		</h2>
		<div class="row no-gutter">
		<div class="col-sm-4 p-2">
			<div class="card p-3">
				<h5 class="card-title">System Users</h5>
				<div class="table-responsive">
					<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
						<thead class="bg-light p-2">
							<tr>
								<th>Contacts</th>
								<th></th>
							</tr>

						</thead>
						<tbody>
							@foreach($login_users as $user)
							<?php
								$convo_id = getConvoID($user->id);
								if($convo_id > 0){
									$messages = checkNewUserMessage($convo_id);
									$count = $messages->count();
								}else{
									$count = 0;
								}
							?>
							<tr>
								<td>
									{!!$user->is_online == 1 ? '<i class="mdi mdi-circle-medium text-success" style="font-size:20px;" ></i>':'' !!}
									{{$user->name}}
									<small class="float-right badge badge-pill {{ $count <= 0 ? '' : 'badge-success ' }}">{{$count<=0 ? '':$count}}</small>
								</td>
								
								<td class="text-small">
									
									<a href="{{route('user-chat-supplier',['id'=>$user->id])}}" class="btn btn-outline-dark btn-sm"><i style="color:green" class="mdi mdi-chat"></i></a>
									
								</td>
								
							</tr>
							@endforeach
						</tbody>

					</table>
				</div>
			</div>
		</div>
		<div class="col-sm-8 p-4">
			<div class="card " style="height: 100%;">
				<h5 class="card-title bg-light p-2" style="height:50px">
				{{$usernow->name ?? ''}}
				  
				@if(isset($usernow))

				@if($usernow && $usernow->is_online==1)
				<small class="float-right" style="color: blue;">online</small>
				@endif
				@if($usernow->is_online==0)
				<small class="float-right" style="color: red;">offline</small>
				@endif
				</h5>
				<div class="card-body" style=" height:300px;overflow:auto">
					@foreach($chats as $chat)
					<?php
						if($chat->from_user_id == auth()->user()->id){
								$name = 'You:~';
								
							}else{
								$user_name = getUserById($chat->from_user_id);
								$name = $user_name->name.':~';
							}
							$user_id = auth()->user()->id
						?>
						@if($chat->from_user_id == $user_id)
						<div class="card p-2 mb-3 float-right "  id="chat">
							<h5>
								
							  {{$name}}
							</h5>
							
							<p class="mb-2">{{$chat->message}}</p>
							<span style="position:absolute;left:70%;"><i>{{date("m/d/Y h:i:s",strtotime($chat->created_at))}}</i></span>
							
						  </div>
						@else
						<div class="card p-2 mb-3 bg-light float-left "  id="chat">
							<h5>
								
							  {{$name}}
							</h5>
							
							<p class="mb-2">{{$chat->message}}</p>
							<span style="position:absolute;left:70%;"><i>{{date("m/d/Y h:i:s",strtotime($chat->created_at))}}</i></span>
							
						  </div>
						@endif
					@endforeach
				</div>
				<div class="card-footer">
					<form action="{{route('add-chat')}}" method="POST" enctype="multipart/form-data">
						@csrf
						<div class="row">
							<div class="col-md-11">

								<div class="form-group">
									<textarea name="chat" rows="5" class="form-control" placeholder="Type here..."></textarea>
								</div>
								<div class="form-group hidden">
									<label class="control-label">User_id</label>
									<input type="number" name="to_user_id" value={{$usernow->id}} class="form-control">
								</div>
							</div>
							<div class="col-md-1" >
								<button type="submit" class="btn btn-outline-success mt-5 "><i class="mdi mdi-send-circle-outline"></i></button>
							</div>
						</div>

					</form>
				</div>
				@endif
				@if(!isset($usernow))
	  				<div class="card-body p-4 text-center" >
						  <h5>No Chats</h5>
						  <small>Select contact to view chat!</small>
					</div>
				@endif
				
			</div>			
		</div>
			
			
		</div>
		</div>
			
			
		
	</main>
	<script>
	$(document).ready(function(){
		function fetch_user(){
			$.ajax({
				url:'/chat/userdetails',
				method:'POST',
				success:function(data){
					$('#user_details').html(data);
				}
			})
		}
	})
	</script>
@endsection
@section('script2')



@endsection
