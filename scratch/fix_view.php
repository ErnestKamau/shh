<?php
$path = '/home/andy/Desktop/Projects/nuvemite/polucon/resources/views/livewire/dms/active-documents-component.blade.php';
$content = file_get_contents($path);

$search = '/<select wire:model.live="ownerFilter" class="form-select">.*?<\/select>\s*<\/div>\s*<\/div>\s*<div class="col-md-1">/s';
$replacement = '<select wire:model.live="ownerFilter" class="form-select">
                                    <option value="">All Owners</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <select wire:model.live="statusFilter" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="draft">Draft</option>
                                    <option value="pending_approval">Pending Approval</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">AI Status</label>
                                <select wire:model.live="kbFilter" class="form-select">
                                    <option value="">All</option>
                                    <option value="indexed">Indexed</option>
                                    <option value="not_indexed">Not Indexed</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Per Page</label>
                                <select wire:model.live="perPage" class="form-select">
                                    @foreach($perPageOptions as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-1">';

$newContent = preg_replace($search, $replacement, $content);

if ($newContent !== null && $newContent !== $content) {
    file_put_contents($path, $newContent);
    echo "Successfully updated the file.\n";
} else {
    echo "Failed to update the file or no changes needed.\n";
    if ($newContent === null) echo "Regex error.\n";
}
