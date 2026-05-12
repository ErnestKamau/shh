<?php
$path = '/home/andy/Desktop/Projects/nuvemite/polucon/resources/views/livewire/dms/active-documents-component.blade.php';
$lines = file($path);
$newLines = [];

foreach ($lines as $line) {
    $newLines[] = $line;
    if (strpos($line, 'ownerFilter" class="form-select">') !== false) {
        // Skip until the closing div of the owner column
        // In the current file, after ownerFilter there is:
        // option, foreach, option, endforeach, /select, /div, /div
    }
}

// Let's just rewrite the whole filter block using line numbers from previous view_file
// Line 69 was </div>
// Line 72 was <div class="col-md-1">

$output = [];
for ($i = 0; $i < 69; $i++) {
    $output[] = $lines[$i];
}

$output[] = "                            </div>\n";
$output[] = "                        </div>\n";
$output[] = "                        <div class=\"col-md-2\">\n";
$output[] = "                            <div class=\"form-group mb-3\">\n";
$output[] = "                                <label class=\"form-label fw-bold\">Status</label>\n";
$output[] = "                                <select wire:model.live=\"statusFilter\" class=\"form-select\">\n";
$output[] = "                                    <option value=\"\">All Status</option>\n";
$output[] = "                                    <option value=\"draft\">Draft</option>\n";
$output[] = "                                    <option value=\"pending_approval\">Pending Approval</option>\n";
$output[] = "                                    <option value=\"approved\">Approved</option>\n";
$output[] = "                                    <option value=\"rejected\">Rejected</option>\n";
$output[] = "                                </select>\n";
$output[] = "                            </div>\n";
$output[] = "                        </div>\n";
$output[] = "                        <div class=\"col-md-2\">\n";
$output[] = "                            <div class=\"form-group mb-3\">\n";
$output[] = "                                <label class=\"form-label fw-bold\">AI Status</label>\n";
$output[] = "                                <select wire:model.live=\"kbFilter\" class=\"form-select\">\n";
$output[] = "                                    <option value=\"\">All</option>\n";
$output[] = "                                    <option value=\"indexed\">Indexed</option>\n";
$output[] = "                                    <option value=\"not_indexed\">Not Indexed</option>\n";
$output[] = "                                </select>\n";
$output[] = "                            </div>\n";
$output[] = "                        </div>\n";
$output[] = "                        <div class=\"col-md-2\">\n";
$output[] = "                            <div class=\"form-group mb-3\">\n";
$output[] = "                                <label class=\"form-label fw-bold\">Per Page</label>\n";
$output[] = "                                <select wire:model.live=\"perPage\" class=\"form-select\">\n";
$output[] = "                                    @foreach(\$perPageOptions as \$option)\n";
$output[] = "                                        <option value=\"{{ \$option }}\">{{ \$option }}</option>\n";
$output[] = "                                    @endforeach\n";
$output[] = "                                </select>\n";
$output[] = "                            </div>\n";
$output[] = "                        </div>\n";

for ($i = 71; $i < count($lines); $i++) {
    $output[] = $lines[$i];
}

file_put_contents($path, implode('', $output));
echo "Fixed file.\n";
