<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addSampleSubmissionRequestColumns();
        $this->addRequestedAnalysesColumns();
        $this->addQuotationHeaderColumns();
        $this->addSampleHeaderColumn();
    }

    public function down(): void
    {
        if (Schema::hasColumn('sample_headers', 'sample_submission_request_id')) {
            Schema::table('sample_headers', function (Blueprint $table): void {
                $table->dropColumn('sample_submission_request_id');
            });
        }

        if (Schema::hasTable('quotation_headers')) {
            Schema::table('quotation_headers', function (Blueprint $table): void {
                $columns = array_filter([
                    Schema::hasColumn('quotation_headers', 'sample_submission_request_id') ? 'sample_submission_request_id' : null,
                    Schema::hasColumn('quotation_headers', 'sent_to_customer_at') ? 'sent_to_customer_at' : null,
                    Schema::hasColumn('quotation_headers', 'revision_of_quotation_header_id') ? 'revision_of_quotation_header_id' : null,
                    Schema::hasColumn('quotation_headers', 'from_enquiry') ? 'from_enquiry' : null,
                ]);
                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasTable('sample_submission_request_requested_analyses')) {
            Schema::table('sample_submission_request_requested_analyses', function (Blueprint $table): void {
                $columns = array_filter([
                    Schema::hasColumn('sample_submission_request_requested_analyses', 'sample_type_id') ? 'sample_type_id' : null,
                    Schema::hasColumn('sample_submission_request_requested_analyses', 'analysis_type_id') ? 'analysis_type_id' : null,
                    Schema::hasColumn('sample_submission_request_requested_analyses', 'analysis_element_id') ? 'analysis_element_id' : null,
                    Schema::hasColumn('sample_submission_request_requested_analyses', 'number_of_samples') ? 'number_of_samples' : null,
                ]);
                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }

        Schema::table('sample_submission_requests', function (Blueprint $table): void {
            $columns = array_filter([
                'source_channel',
                'submission_form_instance_id',
                'current_quotation_header_id',
                'accepted_quotation_header_id',
                'enquiry_notes',
                'quotation_accepted_at',
                'client_po_number',
                'po_skipped',
                'advance_payment_reference',
                'pricing_source',
                'reporting_language',
                'statement_of_conformity',
                'request_for_sampling',
                'sample_description',
                'reference_number',
                'date_expected',
                'priority',
                'batch_sample_type_id',
                'collection_data',
                'sample_lines',
                'request_date_of_service',
                'zone_id',
                'sample_id',
                'sample_type_id',
                'matrix_id',
                'parameter_ids',
                'purpose',
                'nature_of_sample',
                'safety_precautions',
                'safety_precautions_mention',
                'unique_identification',
                'mode_of_payment',
                'further_request',
                'mode_of_service_priority',
            ], fn (string $column): bool => Schema::hasColumn('sample_submission_requests', $column));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }

    private function addSampleSubmissionRequestColumns(): void
    {
        Schema::table('sample_submission_requests', function (Blueprint $table): void {
            if (! Schema::hasColumn('sample_submission_requests', 'request_date_of_service')) {
                $table->date('request_date_of_service')->nullable();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'zone_id')) {
                $table->uuid('zone_id')->nullable()->index();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'sample_id')) {
                $table->string('sample_id')->nullable();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'sample_type_id')) {
                $table->uuid('sample_type_id')->nullable()->index();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'matrix_id')) {
                $table->uuid('matrix_id')->nullable()->index();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'parameter_ids')) {
                $table->json('parameter_ids')->nullable();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'purpose')) {
                $table->text('purpose')->nullable();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'nature_of_sample')) {
                $table->string('nature_of_sample')->nullable();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'safety_precautions')) {
                $table->string('safety_precautions', 1)->nullable();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'safety_precautions_mention')) {
                $table->text('safety_precautions_mention')->nullable();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'unique_identification')) {
                $table->string('unique_identification')->nullable();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'mode_of_payment')) {
                $table->string('mode_of_payment')->nullable();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'further_request')) {
                $table->text('further_request')->nullable();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'mode_of_service_priority')) {
                $table->string('mode_of_service_priority')->nullable();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'source_channel')) {
                $table->string('source_channel', 32)->nullable()->index();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'submission_form_instance_id')) {
                $table->uuid('submission_form_instance_id')->nullable()->index();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'current_quotation_header_id')) {
                $table->uuid('current_quotation_header_id')->nullable()->index();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'accepted_quotation_header_id')) {
                $table->uuid('accepted_quotation_header_id')->nullable()->index();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'enquiry_notes')) {
                $table->text('enquiry_notes')->nullable();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'quotation_accepted_at')) {
                $table->timestamp('quotation_accepted_at')->nullable();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'client_po_number')) {
                $table->string('client_po_number')->nullable();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'po_skipped')) {
                $table->boolean('po_skipped')->default(false);
            }
            if (! Schema::hasColumn('sample_submission_requests', 'advance_payment_reference')) {
                $table->string('advance_payment_reference')->nullable();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'pricing_source')) {
                $table->string('pricing_source', 32)->nullable();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'reporting_language')) {
                $table->string('reporting_language')->nullable();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'statement_of_conformity')) {
                $table->string('statement_of_conformity')->nullable();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'request_for_sampling')) {
                $table->boolean('request_for_sampling')->default(false);
            }
            if (! Schema::hasColumn('sample_submission_requests', 'sample_description')) {
                $table->text('sample_description')->nullable();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'reference_number')) {
                $table->string('reference_number')->nullable();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'date_expected')) {
                $table->date('date_expected')->nullable();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'priority')) {
                $table->string('priority')->nullable();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'batch_sample_type_id')) {
                $table->uuid('batch_sample_type_id')->nullable();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'collection_data')) {
                $table->json('collection_data')->nullable();
            }
            if (! Schema::hasColumn('sample_submission_requests', 'sample_lines')) {
                $table->json('sample_lines')->nullable();
            }
        });
    }

    private function addRequestedAnalysesColumns(): void
    {
        Schema::table('sample_submission_request_requested_analyses', function (Blueprint $table): void {
            if (! Schema::hasColumn('sample_submission_request_requested_analyses', 'sample_type_id')) {
                $table->uuid('sample_type_id')->nullable()->index();
            }
            if (! Schema::hasColumn('sample_submission_request_requested_analyses', 'analysis_type_id')) {
                $table->uuid('analysis_type_id')->nullable()->index();
            }
            if (! Schema::hasColumn('sample_submission_request_requested_analyses', 'analysis_element_id')) {
                $table->uuid('analysis_element_id')->nullable()->index();
            }
            if (! Schema::hasColumn('sample_submission_request_requested_analyses', 'number_of_samples')) {
                $table->unsignedInteger('number_of_samples')->nullable();
            }
        });
    }

    private function addQuotationHeaderColumns(): void
    {
        Schema::table('quotation_headers', function (Blueprint $table): void {
            if (! Schema::hasColumn('quotation_headers', 'sample_submission_request_id')) {
                $table->uuid('sample_submission_request_id')->nullable()->index();
            }
            if (! Schema::hasColumn('quotation_headers', 'sent_to_customer_at')) {
                $table->timestamp('sent_to_customer_at')->nullable();
            }
            if (! Schema::hasColumn('quotation_headers', 'revision_of_quotation_header_id')) {
                $table->uuid('revision_of_quotation_header_id')->nullable()->index();
            }
            if (! Schema::hasColumn('quotation_headers', 'from_enquiry')) {
                $table->boolean('from_enquiry')->default(false);
            }
        });
    }

    private function addSampleHeaderColumn(): void
    {
        Schema::table('sample_headers', function (Blueprint $table): void {
            if (! Schema::hasColumn('sample_headers', 'sample_submission_request_id')) {
                $table->uuid('sample_submission_request_id')->nullable()->index();
            }
        });
    }
};
