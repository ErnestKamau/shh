<div class="card mb-3"><div class="card-body">
<form wire:submit="upload" class="mb-3"><input type="file" wire:model="file" class="form-control-file">@error('file')<span class="text-danger d-block">{{ $message }}</span>@enderror<button type="submit" class="btn btn-sm btn-primary mt-2" wire:loading.attr="disabled">Upload</button></form>
<ul class="list-group">@foreach($documents as $doc)<li class="list-group-item d-flex justify-content-between"><span>v{{ $doc->version }} — {{ $doc->original_name }}</span><a href="{{ route('registry.documents.download', $doc->id) }}" class="btn btn-sm btn-outline-secondary">Download</a></li>@endforeach</ul>
</div></div>
