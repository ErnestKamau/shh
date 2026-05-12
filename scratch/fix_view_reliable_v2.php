<?php
$path = '/home/andy/Desktop/Projects/nuvemite/polucon/resources/views/livewire/dms/active-documents-component.blade.php';
$lines = file($path);
$output = [];

// Line 69 in previous view_file was </div>
// Let's just find the Owner filter block and replace it correctly.
$fullContent = implode('', $lines);
$ownerFilterPattern = '/<div class="col-md-2">\s*<div class="form-group mb-3">\s*<label class="form-label fw-bold">Owner<\/label>.*?<\/select>\s*<\/div>\s*<\/div>/s';
$ownerReplacement = '<div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Owner</label>
                                <select wire:model.live="ownerFilter" class="form-select">
                                    <option value="">All Owners</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>';

$newContent = preg_replace($ownerFilterPattern, $ownerReplacement, $fullContent);

// Also fix the Status/AI/PerPage blocks if they got messed up
// Actually, I'll just rewrite the WHOLE row from <div class="row"> at the start of filters.

$startPattern = '/<!-- Filters -->.*?<div class="row">/s';
// I'll just use a simpler approach.

file_put_contents($path, $fullContent); // Just to be safe, no change yet.
?>
