<?php

$files = [
    'app/Zone.php',
    'app/Country.php',
    'app/TaxRegime.php',
    'app/QuotationHeader.php',
    'app/QuotationDetails.php',
    'app/QuotationNotes.php',
    'app/QuotationAttachment.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;

    $content = file_get_contents($file);

    // Apply HasUuids concern
    if (strpos($content, 'HasUuids') === false) {
        $content = preg_replace('/use Illuminate\\\\Database\\\\Eloquent\\\\Model;/', "use Illuminate\\Database\\Eloquent\\Model;\nuse Illuminate\\Database\\Eloquent\\Concerns\\HasUuids;", $content);
        $content = preg_replace('/class\s+([A-Za-z0-9_]+)\s+extends\s+Model\s*(?:implements\s+[A-Za-z0-9_,\s\n]+)?\s*\{/', "$0\n    use HasUuids;\n\n    protected \$keyType = 'string';\n    public \$incrementing = false;\n\n", $content);
    }
    
    file_put_contents($file, $content);
}

