@if($loop->last)
	@if (isset($is_stamp->id))
		<div class="stamp-section" style="position: absolute; top:-10px; left: 5px; z-index: 10">
				<img src="{{ $stamp }}" style="height:90px; z-index:1000;position: relative;" alt="">
		</div>
	@endif
@endif
	