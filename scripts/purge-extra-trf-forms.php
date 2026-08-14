<?php

/**
 * Purge all TRF submission form templates except Food / Swab / Water.
 * Deletes linked submission instances AND their enquiries/requests (sample_submission_requests).
 *
 * Usage (Dubai server):
 *   cd /var/www/html/amspec-dubai-imara-lims
 *   php scripts/purge-extra-trf-forms.php --dry-run
 *   php scripts/purge-extra-trf-forms.php --force
 */

use App\Models\SubmissionForm;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$dryRun = in_array('--dry-run', $argv, true);
$force = in_array('--force', $argv, true);

if (! $dryRun && ! $force) {
    echo "Refusing to run without --dry-run or --force\n";
    exit(1);
}

$keepIds = array_values(array_filter([
    SubmissionForm::query()
        ->where('document_code', 'TRF-FOOD-019')
        ->where('name', 'Test Request Form - Food')
        ->value('id'),
    SubmissionForm::query()->where('document_code', 'TRF-SWAB-022')->value('id'),
    SubmissionForm::query()->where('document_code', 'TRF-WATER-020')->value('id'),
]));

if (count($keepIds) !== 3) {
    echo "Expected 3 keep forms, found ".count($keepIds).". Aborting.\n";
    exit(1);
}

$forms = SubmissionForm::query()
    ->where('form_type', 'template')
    ->where(function ($q): void {
        $q->where('document_code', 'like', 'TRF%')
            ->orWhereRaw('lower(name) like ?', ['%test request form%']);
    })
    ->whereNotIn('id', $keepIds)
    ->orderBy('document_code')
    ->get();

if ($forms->isEmpty()) {
    echo "Nothing to purge.\n";
    exit(0);
}

echo ($dryRun ? '[DRY RUN] ' : '').'Forms to purge: '.$forms->count()."\n";

foreach ($forms as $form) {
    $instanceIds = $form->instances()->pluck('id')->all();
    $requestIds = collectRequestIdsForInstances($instanceIds);

    echo "- {$form->document_code} | {$form->name} | instances=".count($instanceIds)." | enquiries=".count($requestIds)."\n";

    if ($dryRun) {
        continue;
    }

    DB::transaction(function () use ($form, $instanceIds, $requestIds): void {
        purgeSubmissionFormInstances($instanceIds, $requestIds);
        $form->delete();
    });

    echo "  deleted\n";
}

echo "Done.\n";

/**
 * @param  list<string>  $instanceIds
 * @return list<string>
 */
function collectRequestIdsForInstances(array $instanceIds): array
{
    if ($instanceIds === [] || ! Schema::hasTable('sample_submission_requests')) {
        return [];
    }

    $requestIds = DB::table('sample_submission_requests')
        ->where(function ($query) use ($instanceIds): void {
            $query->whereIn('submission_form_instance_id', $instanceIds);

            if (Schema::hasColumn('sample_submission_requests', 'test_request_form_instance_id')) {
                $query->orWhereIn('test_request_form_instance_id', $instanceIds);
            }
        })
        ->pluck('id')
        ->all();

    if (Schema::hasTable('submission_form_instances') && Schema::hasColumn('submission_form_instances', 'portal_request_id')) {
        $portalLinked = DB::table('submission_form_instances')
            ->whereIn('id', $instanceIds)
            ->whereNotNull('portal_request_id')
            ->pluck('portal_request_id')
            ->all();

        $requestIds = array_merge($requestIds, $portalLinked);
    }

    return array_values(array_unique(array_filter($requestIds)));
}

/**
 * @param  list<string>  $instanceIds
 * @param  list<string>  $requestIds
 */
function purgeSubmissionFormInstances(array $instanceIds, array $requestIds): void
{
    if ($requestIds !== []) {
        purgeSampleSubmissionRequests($requestIds);
    }

    if ($instanceIds === []) {
        return;
    }

    nullInstanceColumn('sample_headers', 'submission_form_instance_id', $instanceIds);
    nullInstanceColumn('analysis_acceptance_forms', 'submission_form_instance_id', $instanceIds);
    nullInstanceColumn('analysis_acceptance_forms', 'test_request_form_instance_id', $instanceIds);
    nullInstanceColumn('test_request_form_instances', 'submission_form_instance_id', $instanceIds);
    nullInstanceColumn('interzone_transfers', 'submission_form_instance_id', $instanceIds);

    deleteInstanceRows('sample_rejection_logs', $instanceIds);
    deleteInstanceRows('test_request_form_instances', $instanceIds);
    deleteInstanceRows('request_workflow_forms', $instanceIds);
    deleteInstanceRows('workflow_checklist_responses', $instanceIds);
    deleteInstanceRows('workflow_approval_logs', $instanceIds);
    deleteInstanceRows('submission_form_instance_values', $instanceIds);
    deleteInstanceRows('submission_form_instance_attachments', $instanceIds);
    deleteInstanceRows('submission_form_instance_notes', $instanceIds);
    deleteInstanceRows('submission_form_instance_intrays', $instanceIds);
    deleteInstanceRows('submission_form_audit_log', $instanceIds);
    deleteInstanceRows('submission_form_audit_logs', $instanceIds);
    deleteInstanceRows('batch_sequences', $instanceIds);
    deleteInstanceRows('certificate_template_reports', $instanceIds);

    if (Schema::hasTable('submission_form_instances')) {
        DB::table('submission_form_instances')->whereIn('id', $instanceIds)->delete();
    }
}

/**
 * @param  list<string>  $requestIds
 */
function purgeSampleSubmissionRequests(array $requestIds): void
{
    nullRequestColumn('sample_headers', 'sample_submission_request_id', $requestIds);
    nullRequestColumn('quotation_headers', 'sample_submission_request_id', $requestIds);
    nullRequestColumn('quotation_approval_logs', 'sample_submission_request_id', $requestIds);
    nullRequestColumn('analysis_acceptance_forms', 'sample_submission_request_id', $requestIds);
    nullRequestColumn('test_request_form_instances', 'sample_submission_request_id', $requestIds);

    foreach ([
        'sample_submission_request_exhibits',
        'sample_submission_request_suspects',
        'sample_submission_request_requested_analyses',
        'sample_submission_request_supporting_document_templates',
        'enquiry_quotations',
        'subcontracting_dispatch_assignments',
        'supporting_document_instances',
        'shelf_life_studies',
        'sample_rejection_logs',
        'request_workflow_forms',
    ] as $table) {
        deleteRequestRows($table, $requestIds);
    }

    if (Schema::hasTable('sample_submission_requests')) {
        DB::table('sample_submission_requests')->whereIn('id', $requestIds)->delete();
    }
}

/**
 * @param  list<string>  $instanceIds
 */
function nullInstanceColumn(string $table, string $column, array $instanceIds): void
{
    if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
        return;
    }

    DB::table($table)->whereIn($column, $instanceIds)->update([$column => null]);
}

/**
 * @param  list<string>  $requestIds
 */
function nullRequestColumn(string $table, string $column, array $requestIds): void
{
    if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
        return;
    }

    DB::table($table)->whereIn($column, $requestIds)->update([$column => null]);
}

/**
 * @param  list<string>  $instanceIds
 */
function deleteInstanceRows(string $table, array $instanceIds): void
{
    if (! Schema::hasTable($table)) {
        return;
    }

    $column = Schema::hasColumn($table, 'submission_form_instance_id')
        ? 'submission_form_instance_id'
        : (Schema::hasColumn($table, 'instance_id') ? 'instance_id' : null);

    if ($column === null) {
        return;
    }

    DB::table($table)->whereIn($column, $instanceIds)->delete();
}

/**
 * @param  list<string>  $requestIds
 */
function deleteRequestRows(string $table, array $requestIds): void
{
    if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'sample_submission_request_id')) {
        return;
    }

    DB::table($table)->whereIn('sample_submission_request_id', $requestIds)->delete();
}
