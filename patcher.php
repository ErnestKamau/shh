<?php

$definitions = [
    'SupplierContract' => [
        'table' => null,
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
        'relations' => "
public function criteria()
{
 \$this->belongsTo(SuppliersRatingCriteria::class, 'criteria_id', 'id');
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
    ],
    'SuppliersRatingCriteria' => [
        'table' => null,
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
        'relations' => "",
        'casts' => "
protected \$casts = [
'string',
];
",
    ]
];

foreach ($definitions as $file => $def) {
    if (!file_exists("app/\$file.php")) continue;
    $content = file_get_contents("app/\$file.php");
    if (strpos($content, 'HasUuids') !== false) {
        continue;
    }
    
    // Header
    $content = str_replace(
        "use Illuminate\Database\Eloquent\Model;",
        "use Illuminate\Database\Eloquent\Concerns\HasUuids;\nuse Illuminate\Database\Eloquent\Model;",
        $content
    );

    // Get table line if any
    $tableLine = $def['table'] ? "\tprotected \$table = '{$def['table']}';\n" : "";

    $inner = "\n\n\tuse HasUuids;\n\n" . $tableLine . "\tprotected \$keyType = 'string';\n\n\tpublic \$incrementing = false;\n\n" . $def['casts'] . "\n" . ltrim($def['relations']);

    // we will find "use \OwenIt\Auditing\Auditable;\n" and replace everything after it
    if (preg_match('/use \\\\OwenIt\\\\Auditing\\\\Auditable;/', $content, $matches, PREG_OFFSET_CAPTURE)) {
        $pos = $matches[0][1] + strlen($matches[0][0]);
        $top = substr($content, 0, $pos);
        $bottom = "\n" . ltrim($inner) . "\n}\n";
        file_put_contents("app/\$file.php", $top . $bottom);
    }
}
