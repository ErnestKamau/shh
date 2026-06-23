<?php

namespace App\Console\Commands;

use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\Models\TestRequestForm;
use App\Models\TestRequestFormInstance;
use App\Services\Commercial\CommercialEnquiryFromFormService;
use App\Services\TestRequestForm\TestRequestFormDataMapper;
use Illuminate\Console\Command;

class TrfBackfillCanonicalLinksCommand extends Command
{
    protected $signature = 'trf:backfill-canonical-links {--dry-run : Report changes without writing}';

    protected $description = 'Backfill TRFI canonical metadata and SSR.test_request_form_instance_id links';

    public function handle(TestRequestFormDataMapper $mapper): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $stats = [
            'trfi_metadata' => 0,
            'trfi_created' => 0,
            'ssr_linked' => 0,
            'trfi_ssr_linked' => 0,
        ];

        TestRequestFormInstance::query()
            ->with('submissionFormInstance')
            ->orderBy('created_at')
            ->chunkById(100, function ($trfis) use ($dryRun, &$stats): void {
                foreach ($trfis as $trfi) {
                    $sfi = $trfi->submissionFormInstance;
                    if ($sfi === null) {
                        continue;
                    }

                    $updates = $this->metadataUpdatesFromShadow($trfi, $sfi);
                    if ($updates === []) {
                        continue;
                    }

                    $stats['trfi_metadata']++;
                    if (! $dryRun) {
                        $trfi->update($updates);
                    }
                }
            });

        SubmissionFormInstance::query()
            ->whereHas('submissionForm', function ($query): void {
                $query->where('document_code', 'like', 'TRF-%')
                    ->orWhere('document_code', 'LSR-001')
                    ->orWhereRaw('lower(name) like ?', ['%test request form%']);
            })
            ->whereDoesntHave('testRequestFormInstance')
            ->with(['submissionForm.sampleTypes', 'values.element'])
            ->orderBy('created_at')
            ->chunkById(50, function ($instances) use ($dryRun, &$stats, $mapper): void {
                foreach ($instances as $instance) {
                    if (! app(CommercialEnquiryFromFormService::class)->isCommercialTestRequestForm($instance)) {
                        continue;
                    }

                    $sampleTypeId = $instance->submissionForm?->sampleTypes->first()?->id;
                    if (! $sampleTypeId) {
                        continue;
                    }

                    $template = TestRequestForm::query()
                        ->where('sample_type_id', $sampleTypeId)
                        ->where('is_active', true)
                        ->first();

                    if ($template === null) {
                        continue;
                    }

                    $raw = $mapper->fromSubmissionFormInstance($instance, $instance->submissionForm, $template);
                    $formData = $mapper->normalizeFormData($raw, $template);

                    $stats['trfi_created']++;
                    if ($dryRun) {
                        continue;
                    }

                    TestRequestFormInstance::query()->create([
                        'test_request_form_id' => $template->id,
                        'submission_form_instance_id' => $instance->id,
                        'form_data' => $formData,
                        'form_number' => $instance->form_number,
                        'sequence_number' => $instance->sequence_number,
                        'crm_customer_id' => $instance->crm_customer_id,
                        'portal_account_id' => $instance->portal_account_id,
                        'source_channel' => $this->resolveSourceChannel($instance),
                        'submitted_by' => $instance->submitted_by,
                        'submitted_at' => $instance->submitted_at,
                        'status' => $instance->status ?? TestRequestFormInstance::STATUS_SUBMITTED,
                        'created_by' => $instance->submitted_by,
                    ]);
                }
            });

        SampleSubmissionRequest::query()
            ->whereNotNull('submission_form_instance_id')
            ->whereNull('test_request_form_instance_id')
            ->orderBy('created_at')
            ->chunkById(100, function ($requests) use ($dryRun, &$stats): void {
                foreach ($requests as $request) {
                    $trfiId = TestRequestFormInstance::query()
                        ->where('submission_form_instance_id', $request->submission_form_instance_id)
                        ->value('id');

                    if (! $trfiId) {
                        continue;
                    }

                    $stats['ssr_linked']++;
                    if (! $dryRun) {
                        $request->update(['test_request_form_instance_id' => $trfiId]);
                    }
                }
            });

        TestRequestFormInstance::query()
            ->whereNull('sample_submission_request_id')
            ->orderBy('created_at')
            ->chunkById(100, function ($trfis) use ($dryRun, &$stats): void {
                foreach ($trfis as $trfi) {
                    if (! $trfi->submission_form_instance_id) {
                        continue;
                    }

                    $ssrId = SampleSubmissionRequest::query()
                        ->where('submission_form_instance_id', $trfi->submission_form_instance_id)
                        ->value('id');

                    if (! $ssrId) {
                        continue;
                    }

                    $stats['trfi_ssr_linked']++;
                    if (! $dryRun) {
                        $trfi->update(['sample_submission_request_id' => $ssrId]);
                    }
                }
            });

        $this->info($dryRun ? 'Dry run complete.' : 'Backfill complete.');
        $this->table(
            ['Metric', 'Count'],
            collect($stats)->map(fn ($count, $key) => [$key, $count])->values()->all()
        );

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function metadataUpdatesFromShadow(TestRequestFormInstance $trfi, SubmissionFormInstance $sfi): array
    {
        $updates = [];

        foreach ([
            'form_number' => $sfi->form_number,
            'sequence_number' => $sfi->sequence_number,
            'crm_customer_id' => $sfi->crm_customer_id,
            'portal_account_id' => $sfi->portal_account_id,
            'submitted_by' => $sfi->submitted_by,
            'submitted_at' => $sfi->submitted_at,
            'zone_id' => $sfi->zone_id,
            'receiving_lab_id' => $sfi->receiving_lab_id,
        ] as $column => $value) {
            if (($trfi->{$column} === null || $trfi->{$column} === '') && $value !== null && $value !== '') {
                $updates[$column] = $value;
            }
        }

        if (! $trfi->source_channel) {
            $updates['source_channel'] = $this->resolveSourceChannel($sfi);
        }

        if (! $trfi->status || $trfi->status === TestRequestFormInstance::STATUS_DRAFT) {
            $updates['status'] = $sfi->status ?? TestRequestFormInstance::STATUS_SUBMITTED;
        }

        return $updates;
    }

    private function resolveSourceChannel(SubmissionFormInstance $instance): string
    {
        if ($instance->portal_account_id) {
            return TestRequestFormInstance::CHANNEL_PORTAL;
        }

        return TestRequestFormInstance::CHANNEL_WALK_IN;
    }
}
