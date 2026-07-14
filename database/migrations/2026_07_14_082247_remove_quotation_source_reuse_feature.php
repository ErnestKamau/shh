<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $this->cloneSharedQuotationsForPristineReuseEnquiries();

        if (Schema::hasTable('sample_submission_requests')) {
            Schema::table('sample_submission_requests', function (Blueprint $table): void {
                if (Schema::hasColumn('sample_submission_requests', 'selected_source_quotation_header_id')) {
                    $table->dropColumn('selected_source_quotation_header_id');
                }
                if (Schema::hasColumn('sample_submission_requests', 'quotation_source_mode')) {
                    $table->dropColumn('quotation_source_mode');
                }
            });
        }

        if (Schema::hasTable('quotation_headers')
            && Schema::hasColumn('quotation_headers', 'source_quotation_header_id')) {
            Schema::table('quotation_headers', function (Blueprint $table): void {
                $table->dropColumn('source_quotation_header_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sample_submission_requests')) {
            Schema::table('sample_submission_requests', function (Blueprint $table): void {
                if (! Schema::hasColumn('sample_submission_requests', 'selected_source_quotation_header_id')) {
                    $table->uuid('selected_source_quotation_header_id')->nullable()->index();
                }
                if (! Schema::hasColumn('sample_submission_requests', 'quotation_source_mode')) {
                    $table->string('quotation_source_mode', 50)->nullable()->index();
                }
            });
        }

        if (Schema::hasTable('quotation_headers')
            && ! Schema::hasColumn('quotation_headers', 'source_quotation_header_id')) {
            Schema::table('quotation_headers', function (Blueprint $table): void {
                $table->uuid('source_quotation_header_id')->nullable()->index();
            });
        }
    }

    /**
     * Enquiries in "pristine reuse" state point at a quotation shared with another
     * enquiry or created independently. Clone the shared quotation (header, details,
     * analysis splits) so every enquiry owns its quotation before the guard logic
     * that prevented shared mutation is removed.
     */
    private function cloneSharedQuotationsForPristineReuseEnquiries(): void
    {
        if (! Schema::hasTable('sample_submission_requests')
            || ! Schema::hasColumn('sample_submission_requests', 'quotation_source_mode')
            || ! Schema::hasColumn('sample_submission_requests', 'selected_source_quotation_header_id')
            || ! Schema::hasTable('quotation_headers')) {
            return;
        }

        $enquiries = DB::table('sample_submission_requests')
            ->where('quotation_source_mode', 'from_existing')
            ->whereNotNull('selected_source_quotation_header_id')
            ->whereColumn('current_quotation_header_id', 'selected_source_quotation_header_id')
            ->get(['id', 'current_quotation_header_id', 'accepted_quotation_header_id']);

        foreach ($enquiries as $enquiry) {
            $source = DB::table('quotation_headers')
                ->where('id', $enquiry->current_quotation_header_id)
                ->first();

            if ($source === null) {
                continue;
            }

            DB::transaction(function () use ($enquiry, $source): void {
                $newHeaderId = (string) Str::uuid();
                $now = now();

                $headerRow = (array) $source;
                unset($headerRow['id']);
                $headerRow['id'] = $newHeaderId;
                $headerRow['quote_number'] = $this->uniqueCloneQuoteNumber((string) ($source->quote_number ?? ''));
                $headerRow['sample_submission_request_id'] = $enquiry->id;
                $headerRow['from_enquiry'] = true;
                $headerRow['created_at'] = $now;
                $headerRow['updated_at'] = $now;
                if (array_key_exists('source_quotation_header_id', $headerRow)) {
                    $headerRow['source_quotation_header_id'] = null;
                }

                DB::table('quotation_headers')->insert($headerRow);

                $details = DB::table('quotation_details')
                    ->where('quotation_header_id', $source->id)
                    ->get();

                foreach ($details as $detail) {
                    $newDetailId = (string) Str::uuid();

                    $detailRow = (array) $detail;
                    unset($detailRow['id']);
                    $detailRow['id'] = $newDetailId;
                    $detailRow['quotation_header_id'] = $newHeaderId;
                    $detailRow['created_at'] = $now;
                    $detailRow['updated_at'] = $now;

                    DB::table('quotation_details')->insert($detailRow);

                    if (Schema::hasTable('quotation_details_analysis_type')) {
                        $splits = DB::table('quotation_details_analysis_type')
                            ->where('quotation_detail_id', $detail->id)
                            ->get();

                        foreach ($splits as $split) {
                            $splitRow = (array) $split;
                            unset($splitRow['id']);
                            $splitRow['id'] = (string) Str::uuid();
                            $splitRow['quotation_detail_id'] = $newDetailId;

                            DB::table('quotation_details_analysis_type')->insert($splitRow);
                        }
                    }
                }

                $update = ['current_quotation_header_id' => $newHeaderId, 'updated_at' => $now];
                if ((string) $enquiry->accepted_quotation_header_id === (string) $source->id) {
                    $update['accepted_quotation_header_id'] = $newHeaderId;
                }

                DB::table('sample_submission_requests')
                    ->where('id', $enquiry->id)
                    ->update($update);
            });
        }
    }

    private function uniqueCloneQuoteNumber(string $sourceNumber): string
    {
        $base = $sourceNumber !== '' ? $sourceNumber : 'QUOTE';
        $suffix = 1;

        do {
            $candidate = $base.'-C'.$suffix;
            $suffix++;
        } while (DB::table('quotation_headers')->where('quote_number', $candidate)->exists());

        return $candidate;
    }
};
