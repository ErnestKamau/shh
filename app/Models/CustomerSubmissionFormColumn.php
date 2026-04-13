<?php

namespace App\Models;

use App\Models\CRM\CRMCustomer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerSubmissionFormColumn extends Model
{
    protected $fillable = [
        'crm_customer_id',
        'source',
        'source_key',
        'display_label',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CRMCustomer::class, 'crm_customer_id');
    }

    /**
     * Available sample_detail columns for submission form (all sample_details columns + specials).
     * Special keys resolve via relations: company_product_id -> Product, sample_point_id -> Section.
     *
     * @return array<string, string>
     */
    /**
     * Available sample_detail columns for NAS submission form.
     * Sorted A-Z by label. Excludes dropped columns: comments, coa_number, compartiment_lot,
     * gps, header_body, material_status, no_of_pots_plants, sample_report_code, standard_tests.
     *
     * @return array<string, string>
     */
    public static function sampleDetailColumns(): array
    {
        $columns = [
            'analysis_type_id' => 'Analysis Type',
            'batch_lot_no' => 'Batch/Lot No.',
            'brand_supplier' => 'Brand/Supplier',
            'barcode' => 'Sample Barcode',
            'collection_temperature' => 'Collection Temperature',
            'company_product_id' => 'Product',
            'condition_upon_receipt' => 'Condition Upon Receipt',
            'created_at' => 'Created At',
            'delivery_date' => 'Delivery Date',
            'disposal_date' => 'Disposal Date',
            'expiry_date' => 'Expiry Date',
            'lab_id' => 'Lab',
            'lab_section_id' => 'Lab Section',
            'lab_sub_no' => 'Lab Sub No.',
            'main_standard' => 'Main Standard',
            'mfg_date' => 'Production Date',
            'no_of_samples' => 'No. of Samples',
            'notes_body' => 'Notes',
            'photo_url' => 'Photo URL',
            'quantity' => 'Quantity',
            'receipt_temperature' => 'Receipt Temperature',
            'reporting_unit_id' => 'Reporting Unit',
            'sample_code' => 'Sample Code',
            'sample_condition_id' => 'Sample Condition',
            'sample_marks' => 'Sample Marks',
            'sample_no' => 'Sample No.',
            'sample_point_id' => 'Sample Source',
            'sampling_plan' => 'Sampling Plan',
            'section_details' => 'Section Details',
            'secondary_standard' => 'Secondary Standard',
            'store_expiry_date' => 'Store Expiry Date',
            'store_pack_date' => 'Store Pack Date',
            'sample_store_id' => 'Store',
            'sample_storage_unit_id' => 'Storage Unit',
            'third_standard_id' => 'Third Standard',
            'updated_at' => 'Updated At',
        ];

        uasort($columns, fn (string $a, string $b) => strcasecmp($a, $b));

        return $columns;
    }
}
