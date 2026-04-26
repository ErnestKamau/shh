<?php

$files = [
    'SupplierContract' => [
        'table' => null,
        'has_comments' => false,
        'relations' => "
public function supplier()
{
 \$this->belongsTo(Supplier::class, 'supplier_id', 'id');
}

public function items()
{
 \$this->hasMany(SupplierContractItem::class, 'contract_id', 'id');
}
",
        'casts' => "
protected \$casts = [
'string',
'string',
'date',
d' => 'date',
'boolean',
];
",
    ],
    'SupplierContractItem' => [
        'table' => null,
        'has_comments' => true,
        'relations' => "
public function contract()
{
 \$this->belongsTo(SupplierContract::class, 'contract_id', 'id');
}

public function item()
{
 \$this->belongsTo(InventoryItem::class, 'item_id', 'id');
}
",
        'casts' => "
protected \$casts = [
'string',
'string',
tract_id' => 'string',
];
",
    ],
    'SupplierRFQ' => [
        'table' => 'supplier_r_f_q_s',
        'has_comments' => true,
        'relations' => "
public function supplier()
{
 \$this->belongsTo(Supplier::class, 'supplier_id', 'id');
}

public function request()
{
 \$this->belongsTo(RequestEntity::class, 'request_id', 'id');
}
",
        'casts' => "
protected \$casts = [
'string',
'string',
uest_id' => 'string',
_sent' => 'boolean',
uote_received' => 'boolean',
];
",
    ],
    'SupplierRatingCriteriaGuide' => [
        'table' => null,
        'has_comments' => false,
        'relations' => "
    public function criteria()
    {
        return \$this->belongsTo(SuppliersRatingCriteria::class, 'criteria_id', 'id');
    }
",
        'casts' => "
protected \$casts = [
'string',
'string',
'float',
'float',
];
",
        'replace_criteria' => true,
    ],
    'SuppliersRatingCriteria' => [
        'table' => null,
        'has_comments' => true,
        'relations' => "
public function supplier()
{
 \$this->belongsTo(Supplier::class, 'supplier_id', 'id');
}

public function ratingBy()
{
 \$this->belongsTo(User::class, 'rating_by', 'id');
}
",
        'casts' => "
protected \$casts = [
'string',
'string',
'string',
g_by' => 'string',
'float',
t' => 'boolean',
];
",
    ],
    'SupplierRatingCriteriaGuideSupplierScore' => [
        'table' => null,
        'has_comments' => false,
        'relations' => "",
        'casts' => "
protected \$casts = [
'string',
];
",
    ]
];

foreach ($files as $className => $config) {
    $path = \"app/\$className.php\";
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        // Ensure not already using HasUuids
        if (strpos($content, 'use HasUuids;') !== false) {
            continue;
        }

        // Add import
        $content = str_replace(
            \"use Illuminate\Database\Eloquent\Model;\",
            \"use Illuminate\Database\Eloquent\Concerns\HasUuids;\nuse Illuminate\Database\Eloquent\Model;\",
            $content
        );

        $toInsert = \"\tuse HasUuids;\n\";
        if ($config['table']) {
            $toInsert .= \"\n\tprotected \$table = '{$config['table']}';\n\";
        }
        $toInsert .= \"\n\tprotected \$keyType = 'string';\n\n\tpublic \$incrementing = false;\n\";
        $toInsert .= $config['casts'];

        // Replace inner content
        if (isset($config['replace_criteria']) && $config['replace_criteria']) {
            // Remove the old criteria method
            $content = preg_replace('/public function criteria\(\)\s*\{[^\}]+\}/si', '', $content);
            $content = str_replace(\"\n\n\n\", \"\n\", $content);
            $content = str_replace(\"    \n}\", \"}\", $content);
            $content = str_replace(\"use \OwenIt\Auditing\Auditable;\", \"use \OwenIt\Auditing\Auditable;\n\" . $toInsert . $config['relations'], $content);
        } else {
            if ($config['has_comments']) {
                $content = str_replace(\"    //\n\", $toInsert . $config['relations'], $content);
                $content = str_replace(\"    //\", $toInsert . $config['relations'], $content);
            } else {
                $content = str_replace(\"use \OwenIt\Auditing\Auditable;\", \"use \OwenIt\Auditing\Auditable;\n\n\" . $toInsert . $config['relations'], $content);
            }
        }

        file_put_contents($path, $content);
    }
}
