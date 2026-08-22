<?php

namespace App\Services\Billing;

use App\Services\Ops\Concerns\PurgesDatabaseTables;
use Illuminate\Support\Facades\Schema;

final class QuotationCleanupService
{
    use PurgesDatabaseTables;

    /**
     * Delete all billing quotation headers and related rows. Preserves customers,
     * sample taxonomy, TRF templates, pricelists, and sample workflow data.
     *
     * @return array<string, int>
     */
    public function deleteAll(): array
    {
        $counts = [];

        $counts['sample_headers.quote_id'] = $this->nullColumn('sample_headers', 'quote_id');
        $counts['sample_submission_requests.current_quotation_header_id'] = $this->nullColumn(
            'sample_submission_requests',
            'current_quotation_header_id'
        );
        $counts['sample_submission_requests.accepted_quotation_header_id'] = $this->nullColumn(
            'sample_submission_requests',
            'accepted_quotation_header_id'
        );
        $counts['sample_submission_requests.created_from_quotation_header_id'] = $this->nullColumn(
            'sample_submission_requests',
            'created_from_quotation_header_id'
        );
        $counts['customer_invoice.quotation_header_id'] = $this->nullColumn('customer_invoice', 'quotation_header_id');

        if (Schema::hasTable('quotation_headers')) {
            foreach ([
                'revision_of_quotation_header_id',
                'superseded_by_quotation_header_id',
                'source_quotation_header_id',
            ] as $column) {
                if (Schema::hasColumn('quotation_headers', $column)) {
                    $counts['quotation_headers.'.$column] = $this->nullColumn('quotation_headers', $column);
                }
            }
        }

        foreach ([
            'quotation_details_analysis_type',
            'quotation_detail_analysis_splits',
            'quotation_attachments',
            'quotation_notes',
            'quotation_details',
            'quotation_approval_logs',
            'quotation_header_lab_sections',
            'enquiry_quotations',
            'quotation_headers',
        ] as $table) {
            $counts[$table] = $this->deleteAllRows($table);
        }

        return $counts;
    }
}
