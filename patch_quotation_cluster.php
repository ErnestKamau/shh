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
    if (!file_exists($file)) {
        echo "File $file not found!\n";
        continue;
    }
    
    $content = file_get_contents($file);
    
    if (strpos($content, 'HasUuids') !== false) {
        echo "$file already has HasUuids, skipping.\n";
        continue;
    }
    
    // Add HasUuids import
    $content = preg_replace(
        '/(use Illuminate\\\\Database\\\\Eloquent\\\\Model;)/',
        "$1\nuse Illuminate\\Database\\Eloquent\\Concerns\\HasUuids;",
        $content
    );
    
    // Add HasUuids trait and properties
    $uuidCode = "    use HasUuids;\n\n    protected \$keyType = 'string';\n    public \$incrementing = false;\n";
    $content = preg_replace(
        '/(class\s+[a-zA-Z0-9_]+\s+extends\s+Model\s*\{)/',
        "$1\n$uuidCode",
        $content
    );
    
    // Customise casts based on file
    $casts = [];
    if (preg_match('/protected\s+\$casts\s*=\s*\[(.*?)\];/s', $content, $matches)) {
        // Has casts
        $existingCasts = trim($matches[1]);
        if (!empty($existingCasts) && !str_ends_with($existingCasts, ',')) {
            $existingCasts .= ',';
        }
        $newCasts = "'id' => 'string',";
        
        if ($file === 'app/Zone.php') {
            $newCasts .= "\n        'inventory_location_id' => 'string',";
        } elseif ($file === 'app/TaxRegime.php') {
            $newCasts .= "\n        'registered_by' => 'string',\n        'active' => 'boolean',";
        } elseif ($file === 'app/QuotationHeader.php') {
            $newCasts .= "\n        'crm_customer_id' => 'string',\n        'crm_customer_contact_id' => 'string',\n        'prepared_by_id' => 'string',\n        'pricelist_id' => 'string',\n        'approved_by' => 'string',\n        'currency_id' => 'string',\n        'is_draft' => 'boolean',\n        'is_complete' => 'boolean',\n        'is_print' => 'boolean',\n        'is_approved' => 'boolean',";
        } elseif ($file === 'app/QuotationDetails.php') {
            $newCasts .= "\n        'quotation_header_id' => 'string',\n        'invoicable_item_id' => 'string',\n        'analyte_id' => 'string',\n        'sample_type_id' => 'string',";
        } elseif ($file === 'app/QuotationAttachment.php') {
            $newCasts .= "\n        'quote_id' => 'string',";
        } elseif ($file === 'app/QuotationNotes.php') {
            $newCasts .= "\n        'quote_id' => 'string',\n        'user_id' => 'string',";
        }
        
        if (!empty($existingCasts)) {
             $content = preg_replace(
                '/protected\s+\$casts\s*=\s*\[(.*?)\];/s',
                "protected \$casts = [\n        $newCasts\n$1];",
                $content
            );
        } else {
             $content = preg_replace(
                '/protected\s+\$casts\s*=\s*\[\s*\];/s',
                "protected \$casts = [\n        $newCasts\n    ];",
                $content
            );
        }
        
    } else {
        // No casts yet
        $castsCode = "    protected \$casts = [\n        'id' => 'string',";
        
        if ($file === 'app/Zone.php') {
            $castsCode .= "\n        'inventory_location_id' => 'string',";
        } elseif ($file === 'app/TaxRegime.php') {
            $castsCode .= "\n        'registered_by' => 'string',\n        'active' => 'boolean',";
        } elseif ($file === 'app/QuotationHeader.php') {
            $castsCode .= "\n        'crm_customer_id' => 'string',\n        'crm_customer_contact_id' => 'string',\n        'prepared_by_id' => 'string',\n        'pricelist_id' => 'string',\n        'approved_by' => 'string',\n        'currency_id' => 'string',\n        'is_draft' => 'boolean',\n        'is_complete' => 'boolean',\n        'is_print' => 'boolean',\n        'is_approved' => 'boolean',";
        } elseif ($file === 'app/QuotationDetails.php') {
            $castsCode .= "\n        'quotation_header_id' => 'string',\n        'invoicable_item_id' => 'string',\n        'analyte_id' => 'string',\n        'sample_type_id' => 'string',";
        } elseif ($file === 'app/QuotationAttachment.php') {
            $castsCode .= "\n        'quote_id' => 'string',";
        } elseif ($file === 'app/QuotationNotes.php') {
            $castsCode .= "\n        'quote_id' => 'string',\n        'user_id' => 'string',";
        }
        
        $castsCode .= "\n    ];\n";
        
        $content = preg_replace(
            '/(public\s+\$incrementing\s*=\s*false;)/',
            "$1\n$castsCode",
            $content
        );
    }
    
    // Fix common relation keys for these specific models based on db schema or standard conventions
    if ($file === 'app/QuotationHeader.php') {
        $content = preg_replace('/return \$this->hasMany\(QuotationDetails::class\);/', 'return $this->hasMany(QuotationDetails::class, \'quotation_header_id\', \'id\');', $content);
        $content = preg_replace('/return \$this->belongsTo\(CrmCustomerContact::class,.*?\);/', 'return $this->belongsTo(CrmCustomerContact::class, \'crm_customer_contact_id\', \'id\');', $content);
        $content = preg_replace('/return \$this->belongsTo\(CrmCustomerContact::class\);/', 'return $this->belongsTo(CrmCustomerContact::class, \'crm_customer_contact_id\', \'id\');', $content);
        $content = preg_replace('/return \$this->belongsTo\(Customer::class,.*?\);/', 'return $this->belongsTo(Customer::class, \'crm_customer_id\', \'id\');', $content);
        $content = preg_replace('/return \$this->belongsTo\(Customer::class\);/', 'return $this->belongsTo(Customer::class, \'crm_customer_id\', \'id\');', $content);
        $content = preg_replace('/return \$this->belongsTo\(CurrencyConversion::class,.*?\);/', 'return $this->belongsTo(CurrencyConversion::class, \'currency_id\', \'id\');', $content);
        $content = preg_replace('/return \$this->belongsTo\(CurrencyConversion::class\);/', 'return $this->belongsTo(CurrencyConversion::class, \'currency_id\', \'id\');', $content);
    }
    
    if ($file === 'app/QuotationDetails.php') {
         $content = preg_replace('/return \$this->belongsTo\(InvoicableItem::class\);/', 'return $this->belongsTo(InvoicableItem::class, \'invoicable_item_id\', \'id\');', $content);
    }

    file_put_contents($file, $content);
    echo "Patched $file\n";
}
