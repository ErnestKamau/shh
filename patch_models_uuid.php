<?php
$dir = new RecursiveDirectoryIterator('app/Models/');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

$patched = 0;
foreach ($files as $file) {
    $path = $file[0];
    $content = file_get_contents($path);

    // Look for class declaration
    if (preg_match('/class\s+[a-zA-Z0-9_]+\s+(extends|implements).*?\{/is', $content)) {
        
        $modified = false;

        // Check if HasUuids is imported
        if (strpos($content, 'Illuminate\Database\Eloquent\Concerns\HasUuids') === false) {
            // Find namespace or opening <?php
            if (preg_match('/namespace\s+[a-zA-Z0-9_\\\\]+;/', $content)) {
                $content = preg_replace('/(namespace\s+[a-zA-Z0-9_\\\\]+;)/', "$1\n\nuse Illuminate\Database\Eloquent\Concerns\HasUuids;", $content, 1);
            } else {
                $content = preg_replace('/(<\?php)/', "$1\n\nuse Illuminate\Database\Eloquent\Concerns\HasUuids;", $content, 1);
            }
        }

        // Check if HasUuids is used in the class
        if (strpos($content, 'use HasUuids;') === false && strpos($content, 'use \Illuminate\Database\Eloquent\Concerns\HasUuids;') === false) {
            $traitCode = "\n    use HasUuids;\n\n    protected \$keyType = 'string';\n    public \$incrementing = false;\n";
            $content = preg_replace('/(class\s+[a-zA-Z0-9_]+\s+(?:extends|implements)[^{]*\{)/is', "$1$traitCode", $content, 1);
            $modified = true;
        }

        if ($modified) {
            file_put_contents($path, $content);
            $patched++;
        }
    }
}
echo "Total models patched: $patched\n";
