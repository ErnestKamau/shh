<?php

$file = 'app/Livewire/Equipment/Workflow/WorkflowForm.php';
$content = file_get_contents($file);

$functions = <<<PHP
    public function toggleAssetTypeDropdown() { \$this->showAssetTypeDropdown = !\$this->showAssetTypeDropdown; }
    public function hideAssetTypeDropdown() { \$this->showAssetTypeDropdown = false; }
    
    public function toggleLocationDropdown() { \$this->showLocationDropdown = !\$this->showLocationDropdown; }
    public function hideLocationDropdown() { \$this->showLocationDropdown = false; }

    public function toggleStepRoleDropdown(\$index) {
        if(isset(\$this->steps[\$index])) {
            \$this->steps[\$index]['showRoleDropdown'] = !\$this->steps[\$index]['showRoleDropdown'];
        }
    }
    public function hideStepRoleDropdown(\$index) {
        if(isset(\$this->steps[\$index])) {
            \$this->steps[\$index]['showRoleDropdown'] = false;
        }
    }

    public function toggleStepAssigneeDropdown(\$index) {
        if(isset(\$this->steps[\$index])) {
            \$this->steps[\$index]['showAssigneeDropdown'] = !\$this->steps[\$index]['showAssigneeDropdown'];
        }
    }
    public function hideStepAssigneeDropdown(\$index) {
        if(isset(\$this->steps[\$index])) {
            \$this->steps[\$index]['showAssigneeDropdown'] = false;
        }
    }

    public function selectStepRole(
PHP;

$content = str_replace('    public function selectStepRole(', $functions, $content);
file_put_contents($file, $content);

// Now patch blade
$bladeFile = 'resources/views/livewire/equipment/workflow/workflow-form.blade.php';
$blade = file_get_contents($bladeFile);

$blade = str_replace('wire:click="$set(\'showAssetTypeDropdown\', true)" wire:click.outside="$set(\'showAssetTypeDropdown\', false)"', 'wire:click="toggleAssetTypeDropdown" wire:click.outside="hideAssetTypeDropdown"', $blade);

$blade = str_replace('wire:click="$set(\'showLocationDropdown\', true)" wire:click.outside="$set(\'showLocationDropdown\', false)"', 'wire:click="toggleLocationDropdown" wire:click.outside="hideLocationDropdown"', $blade);

$blade = preg_replace('/wire:click="\$set\(\'steps\.(\{\{ \$index \}\})\.showRoleDropdown\', true\)" wire:click\.outside="\$set\(\'steps\.\1\.showRoleDropdown\', false\)"/', 'wire:click="toggleStepRoleDropdown($1)" wire:click.outside="hideStepRoleDropdown($1)"', $blade);

$blade = preg_replace('/wire:click="\$set\(\'steps\.(\{\{ \$index \}\})\.showAssigneeDropdown\', true\)" wire:click\.outside="\$set\(\'steps\.\1\.showAssigneeDropdown\', false\)"/', 'wire:click="toggleStepAssigneeDropdown($1)" wire:click.outside="hideStepAssigneeDropdown($1)"', $blade);

file_put_contents($bladeFile, $blade);

echo "Patched\n";
