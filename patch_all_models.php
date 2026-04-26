<?php

$dir = new RecursiveDirectoryIterator('app');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

$count = 0;
foreach ($files as $file) {
    $filePath = $file[0];
    
    $content = file_get_contents($filePath);
    
    // Skip if not an Eloquent Model
    if (strpos($content, 'extends Model') === false) continue;
    if (strpos($content, 'Illuminate\Database\Eloquent\Model') === false) continue;

    // Apply HasUuids concern
    if (strpos($content, 'HasUuids') === false) {
        $content = preg_replace('/use Illuminate\\\\Database\\\\Eloquent\\\\Model;/', "use Illuminate\\Database\\Eloquent\\Model;\nuse Illuminate\\Database\\Eloquent\\Concerns\\HasUuids;", $content);
        
        // Match class declaration, handling optional implements
        $content = preg_replace(
            '/class\s+([A-Za-z0-9_]+)\s+extends\s+Model\s*(implements\s+[A-Za-z0-9_,\s\n\\]+)?\s*\{/s', 
            "$0\n    use HasUuids;\n\n    protected \$keyType = 'string';\n    public \$incrementing = false;\n", 
            $content
        );

        file_put_contents($filePath, $content);
        echo "Patched: $filePath\n";
        $count++;
    }
}
echo "Total patched: $count\n";
