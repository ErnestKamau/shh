<?php

/**
 * Purge all TRF submission form templates except Food / Swab / Water.
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
    echo "- {$form->document_code} | {$form->name} | instances=".count($instanceIds)."\n";

    if ($dryRun) {
        continue;
    }

    DB::transaction(function () use ($form, $instanceIds): void {
        purgeSubmissionFormInstances($instanceIds);
        $form->delete();
    });

    echo "  deleted\n";
}

echo "Done.\n";

/**
 * @param  list<string>  $instanceIds
 */
function purgeSubmissionFormInstances(array $instanceIds): void
{
    if ($instanceIds === []) {
        return;
    }

    nullInstanceColumn('sample_submission_requests', 'submission_form_instance_id', $instanceIds);
    nullInstanceColumn('sample_submission_requests', 'test_request_form_instance_id', $instanceIds);
    nullInstanceColumn('sample_headers', 'submission_form_instance_id', $instanceIds);
    nullInstanceColumn('analysis_acceptance_forms', 'submission_form_instance_id', $instanceIds);
    nullInstanceColumn('analysis_acceptance_forms', 'test_request_form_instance_id', $instanceIds);
    nullInstanceColumn('test_request_form_instances', 'submission_form_instance_id', $instanceIds);
    nullInstanceColumn('interzone_transfers', 'submission_form_instance_id', $instanceIds);
    nullInstanceColumn('request_workflow_forms', 'submission_form_instance_id', $instanceIds);

    deleteInstanceRows('sample_rejection_logs', $instanceIds);
    deleteInstanceRows('test_request_form_instances', $instanceIds);
    deleteInstanceRows('request_workflow_forms', $instanceIds);
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
