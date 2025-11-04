<?php

namespace App\Services;

class CertificateTemplateDataSourceService
{
    /**
     * Get all available data sources.
     */
    public static function getDataSources(): array
    {
        return [
            'Company' => 'Company',
            'CRMCustomer' => 'CRM Customer',
            'SampleHeader' => 'Sample Header',
            'SampleDetails' => 'Sample Details',
            'CapturedResult' => 'Captured Result',
        ];
    }

    /**
     * Get available fields for a given data source.
     */
    public static function getFieldsForDataSource(string $dataSource): array
    {
        return match ($dataSource) {
            'Company' => self::getCompanyFields(),
            'CRMCustomer' => self::getCRMCustomerFields(),
            'SampleHeader' => self::getSampleHeaderFields(),
            'SampleDetails' => self::getSampleDetailsFields(),
            'CapturedResult' => self::getCapturedResultFields(),
            default => [],
        };
    }

    /**
     * Get available fields for Company model.
     */
    private static function getCompanyFields(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Company Name',
            'logo' => 'Logo URL',
            'location' => 'Location',
            'address' => 'Address',
            'website' => 'Website',
            'email' => 'Email',
            'cell_phone' => 'Cell Phone',
            'telephone' => 'Telephone',
            'fax' => 'Fax',
            'client_number' => 'Client Number',
            'report_logo' => 'Report Logo URL',
        ];
    }

    /**
     * Get available fields for CRMCustomer model.
     */
    private static function getCRMCustomerFields(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Customer Name',
            'code' => 'Customer Code',
            'email' => 'Email',
            'telephone1' => 'Telephone 1',
            'telephone2' => 'Telephone 2',
            'postal_address' => 'Postal Address',
            'physical_address' => 'Physical Address',
            'fax' => 'Fax',
            'website' => 'Website',
            'vat_no' => 'VAT Number',
        ];
    }

    /**
     * Get available fields for SampleHeader model.
     */
    private static function getSampleHeaderFields(): array
    {
        return [
            'id' => 'Batch ID',
            'batch_code' => 'Batch Code',
            'receipt_date' => 'Receipt Date',
            'date_collected' => 'Date Collected',
            'reference_number' => 'Reference Number',
            'status' => 'Status',
            'document_number' => 'Document Number',
            'description' => 'Description',
            'priority' => 'Priority',
            'date_expected' => 'Date Expected',
            'processing_date' => 'Processing Date',
            'approval_date' => 'Approval Date',
            'email_date' => 'Email Date',
            'payment_date' => 'Payment Date',
            'invoice_number' => 'Invoice Number',
        ];
    }

    /**
     * Get available fields for SampleDetails model.
     */
    private static function getSampleDetailsFields(): array
    {
        return [
            'id' => 'Sample ID',
            'sample_code' => 'Sample Code',
            'barcode' => 'Barcode',
            'comments' => 'Comments',
            'gps' => 'GPS Coordinates',
            'photo_url' => 'Photo URL',
            'coa_number' => 'COA Number',
            'mfg_date' => 'Manufacturing Date',
            'expiry_date' => 'Expiry Date',
            'batch_lot_no' => 'Batch/Lot Number',
            'report_number' => 'Report Number',
            'sample_no' => 'Sample Number',
        ];
    }

    /**
     * Get available fields for CapturedResult model.
     */
    private static function getCapturedResultFields(): array
    {
        return [
            'id' => 'ID',
            'sample_detail_code' => 'Sample Detail Code',
            'analyte_code' => 'Analyte Code',
            'result' => 'Result',
            'result_reporting_symbol' => 'Reporting Symbol',
            'remark' => 'Remark',
            'machine_update_date' => 'Machine Update Date',
            'scienctific_result' => 'Scientific Result',
            'measure_uncertanity' => 'Measure Uncertainty',
        ];
    }

    /**
     * Get field label for a given data source and field name.
     */
    public static function getFieldLabel(string $dataSource, string $fieldName): string
    {
        $fields = self::getFieldsForDataSource($dataSource);
        return $fields[$fieldName] ?? $fieldName;
    }
}
