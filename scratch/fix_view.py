import re

file_path = '/home/andy/Desktop/Projects/nuvemite/polucon/resources/views/livewire/dms/active-documents-component.blade.php'
with open(file_path, 'r') as f:
    content = f.read()

target = r'''<div class="col-md-6">
                                                        <div class="form-group mb-3">
                                                            
                                                        </div>'''

replacement = '''<div class="col-md-6">
                                                        <div class="form-group mb-3">
                                                            <label class="form-label small fw-bold">Required AI Permission</label>
                                                            <select wire:model="documentForm.kb_required_permission" class="form-select form-select-sm">
                                                                <option value="">No special permission (General Access)</option>
                                                                @foreach($spatiePermissions as $perm)
                                                                    <option value="{{ $perm }}">{{ $perm }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>'''

new_content = content.replace(target, replacement)

with open(file_path, 'w') as f:
    f.write(new_content)

print("Done")
