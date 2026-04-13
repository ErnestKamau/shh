<?php

namespace App\Models;

use App\Models\CRM\CRMCustomer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CRMCustomerReportInfoColumn extends Model
{
    protected $table = 'crm_report_info_columns';

    protected $fillable = [
        'crm_customer_id',
        'source',
        'source_key',
        'display_label',
        'display_order',
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CRMCustomer::class, 'crm_customer_id');
    }

    /**
     * Get the display label (override or from source definition).
     */
    public function getDisplayLabelAttribute(): string
    {
        if (isset($this->attributes['display_label']) && $this->attributes['display_label'] !== '') {
            return $this->attributes['display_label'];
        }
        if ($this->source === 'sample_header') {
            return self::sampleHeaderColumns()[$this->source_key] ?? $this->source_key;
        }
        if ($this->source === 'sample_detail') {
            return self::sampleDetailColumns()[$this->source_key] ?? $this->source_key;
        }
        if ($this->source === 'custom_field') {
            $cf = \App\Models\CustomField::find($this->source_key);
            return $cf ? ($cf->label ?? $cf->name) : $this->source_key;
        }
        return $this->source_key;
    }

    /**
     * All available report info block columns for template designer (sample_header + sample_detail).
     * Each item: ['source' => ..., 'source_key' => ..., 'label' => ..., 'key' => 'source:source_key'].
     *
     * @return array<int, array{source: string, source_key: string, label: string, key: string}>
     */
    public static function allAvailableReportInfoColumns(): array
    {
        $list = [];
        foreach (self::sampleHeaderColumns() as $sourceKey => $label) {
            $list[] = [
                'source' => 'sample_header',
                'source_key' => $sourceKey,
                'label' => $label,
                'key' => 'sample_header:' . $sourceKey,
            ];
        }
        foreach (self::sampleDetailColumns() as $sourceKey => $label) {
            $list[] = [
                'source' => 'sample_detail',
                'source_key' => $sourceKey,
                'label' => $label,
                'key' => 'sample_detail:' . $sourceKey,
            ];
        }
        return $list;
    }

    /**
     * Available sample_header columns for info block.
     *
     * @return array<string, string>
     */
    public static function sampleHeaderColumns(): array
    {
        return [
            'batch_code' => 'Batch Code',
            'reference_number' => 'Reference Number',
            'receipt_date' => 'Receipt Date',
            'date_collected' => 'Date Collected',
            'date_expected' => 'Date Expected',
            'description' => 'Description',
            'document_number' => 'Document Number',
            'priority' => 'Priority',
            'reason_for_submission' => 'Reason for Submission',
            'report_type' => 'Report Type',
            'status' => 'Status',
            'crm_unit_name' => 'Unit Name',
            'how_sample_was_obtained' => 'How Sample Was Obtained',
            'declared_commodity_code' => 'Declared Commodity Code',
            'net_quantity_and_unit_of_quantity' => 'Net Quantity & Unit',
            'sample_appearance_description' => 'Sample Appearance Description',
            'use_of_goods' => 'Use of Goods',
            'kra_office_ref' => 'KRA Office Ref',
            'kra_office_station' => 'KRA Office Station',
            'where_sample_was_obtained' => 'Where Sample Was Obtained',
            'declared_amount' => 'Declared Amount',
            'final_declared_amount' => 'Final Declared Amount',
            'radio_active_levels' => 'Radio Active Levels',
            'quantity' => 'Quantity',
            'processing_date' => 'Processing Date',
        ];
    }

    /**
     * Available sample_detail columns for info block.
     *
     * @return array<string, string>
     */
    public static function sampleDetailColumns(): array
    {
        return [
            'sample_code' => 'Sample Code',
            'report_number' => 'Report Number',
            'barcode' => 'Barcode',
            'comments' => 'Comments',
            'sample_marks' => 'Sample Marks',
            'sample_source' => 'Sample Source',
            'condition_upon_receipt' => 'Condition Upon Receipt',
            'receipt_temperature' => 'Receipt Temperature',
            'section_details' => 'Section Details',
            'sampling_plan' => 'Sampling Plan',
            'batch_lot_no' => 'Batch/Lot No.',
            'brand_supplier' => 'Brand/Supplier',
            'collection_temperature' => 'Collection Temperature',
            'delivery_date' => 'Delivery Date',
            'disposal_date' => 'Disposal Date',
            'expiry_date' => 'Expiry Date',
            'mfg_date' => 'Production Date',
            'notes_body' => 'Notes',
            'no_of_samples' => 'No. of Samples',
            'quantity' => 'Quantity',
            'store_expiry_date' => 'Store Expiry Date',
            'store_pack_date' => 'Store Pack Date',
            'date_analysed' => 'Date Analysed',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            // From samples_by_category view (resolved names)
            'product_name' => 'Product',
            'sample_point_name' => 'Section',
            'sample_condition_name' => 'Sample Condition',
            'customer_crm_unit' => 'Customer Unit',
        ];
    }
}
