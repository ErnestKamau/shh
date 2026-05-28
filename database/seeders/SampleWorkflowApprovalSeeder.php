<?php

namespace Database\Seeders;

use App\Models\Workflow\Approval;
use App\Services\WorkflowService;
use Illuminate\Database\Seeder;

class SampleWorkflowApprovalSeeder extends Seeder
{
    public function run(): void
    {
        $workflowService = app(WorkflowService::class);
        $stages = array_values(array_filter(getSampleWorflowStages(), function ($stage) {
            return $stage !== 'All Samples';
        }));

        $stageMap = [
            'receiving' => $this->resolveStage($stages, ['Samples Receiving', 'Samples Reception']),
            'verification' => $this->resolveStage($stages, ['Sample Verification']),
            'approval' => $this->resolveStage($stages, ['Sample Approval']),
        ];

        $approvals = array_values(array_filter([
            $stageMap['receiving'] ? [
                'stage_name' => $stageMap['receiving'],
                'code' => 'sro_reception_approval',
                'name' => 'SRO Reception Approval',
                'order' => 1,
                'is_active' => true,
                'items' => [
                    ['label' => 'Sample labels verified', 'type' => 'checkbox', 'is_required' => true, 'order' => 1],
                    ['label' => 'Chain of custody received', 'type' => 'checkbox', 'is_required' => true, 'order' => 2],
                    ['label' => 'Receiving notes', 'type' => 'text', 'is_required' => false, 'order' => 3],
                ],
            ] : null,
            $stageMap['receiving'] === 'Samples Receiving' ? [
                'stage_name' => 'Samples Receiving',
                'code' => 'sro_receiving_sample',
                'name' => 'SRO Receiving Sample',
                'order' => 2,
                'is_active' => true,
                'items' => [
                    ['label' => 'Sample labels verified', 'type' => 'checkbox', 'is_required' => true, 'order' => 1],
                    ['label' => 'Chain of custody received', 'type' => 'checkbox', 'is_required' => true, 'order' => 2],
                    ['label' => 'Receiving notes', 'type' => 'text', 'is_required' => false, 'order' => 3],
                ],
            ] : null,
            $stageMap['verification'] ? [
                'stage_name' => $stageMap['verification'],
                'code' => 'analyst_verification',
                'name' => 'Analyst Verification',
                'order' => 1,
                'is_active' => true,
                'items' => [
                    ['label' => 'Results reviewed against worksheet', 'type' => 'checkbox', 'is_required' => true, 'order' => 1],
                    ['label' => 'Verification outcome', 'type' => 'select', 'is_required' => true, 'options' => ['Pass', 'Conditional Pass', 'Fail'], 'order' => 2],
                    ['label' => 'Verification summary', 'type' => 'text', 'is_required' => true, 'order' => 3],
                ],
            ] : null,
            $stageMap['verification'] ? [
                'stage_name' => $stageMap['verification'],
                'code' => 'technical_reviewer_verification',
                'name' => 'Technical Reviewer Verification',
                'order' => 2,
                'is_active' => true,
                'items' => [
                    ['label' => 'All analytical results verified for accuracy and consistency', 'type' => 'checkbox', 'is_required' => true, 'order' => 1],
                    ['label' => 'Test methods applied are appropriate and compliant with standards', 'type' => 'checkbox', 'is_required' => true, 'order' => 2],
                    ['label' => 'QC/QA results (blanks, duplicates, standards) are within acceptance criteria', 'type' => 'checkbox', 'is_required' => true, 'order' => 3],
                    ['label' => 'Calculations and unit conversions verified', 'type' => 'checkbox', 'is_required' => true, 'order' => 4],
                    ['label' => 'Any method deviations documented and justified', 'type' => 'checkbox', 'is_required' => true, 'order' => 5],
                    ['label' => 'Technical review outcome', 'type' => 'select', 'is_required' => true, 'options' => ['Pass', 'Conditional Pass', 'Fail'], 'order' => 6],
                    ['label' => 'Technical reviewer remarks', 'type' => 'text', 'is_required' => false, 'order' => 7],
                ],
            ] : null,
            $stageMap['verification'] ? [
                'stage_name' => $stageMap['verification'],
                'code' => 'lab_manager_verification',
                'name' => 'Lab Manager Verification',
                'order' => 3,
                'is_active' => true,
                'items' => [
                    ['label' => 'Technical review has been completed and approved', 'type' => 'checkbox', 'is_required' => true, 'order' => 1],
                    ['label' => 'Overall QA/QC compliance confirmed', 'type' => 'checkbox', 'is_required' => true, 'order' => 2],
                    ['label' => 'Sample integrity and chain of custody maintained throughout analysis', 'type' => 'checkbox', 'is_required' => true, 'order' => 3],
                    ['label' => 'Report format and content reviewed for completeness', 'type' => 'checkbox', 'is_required' => true, 'order' => 4],
                    ['label' => 'Results are suitable for release to client', 'type' => 'checkbox', 'is_required' => true, 'order' => 5],
                    ['label' => 'Lab manager approval decision', 'type' => 'select', 'is_required' => true, 'options' => ['Approved', 'Approved with Conditions', 'Rejected'], 'order' => 6],
                    ['label' => 'Lab manager remarks', 'type' => 'text', 'is_required' => false, 'order' => 7],
                ],
            ] : null,
            $stageMap['approval'] ? [
                'stage_name' => $stageMap['approval'],
                'code' => 'manager_sample_approval',
                'name' => 'Manager Sample Approval',
                'order' => 1,
                'is_active' => true,
                'items' => [
                    ['label' => 'Final report checked', 'type' => 'checkbox', 'is_required' => true, 'order' => 1],
                    ['label' => 'Approval decision basis', 'type' => 'text', 'is_required' => true, 'order' => 2],
                ],
            ] : null,
        ]));

        foreach ($approvals as $definition) {
            $existingApproval = Approval::query()
                ->where('stage_name', $definition['stage_name'])
                ->where('code', $definition['code'])
                ->first();

            $approval = $workflowService->saveApproval($definition, $existingApproval?->id);

            foreach ($definition['items'] as $item) {
                $existingItem = $approval->checklistItems()
                    ->where('label', $item['label'])
                    ->first();

                $workflowService->saveChecklistItem($approval->id, $item, $existingItem?->id);
            }
        }
    }

    private function resolveStage(array $stages, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (in_array($candidate, $stages, true)) {
                return $candidate;
            }
        }

        return null;
    }
}