<?php

namespace App\Livewire\Qc;

use App\AnalysisElements;
use App\AnalysisType;
use App\Analyte;
use App\Models\QcModule\Configurations\QcSchemes;
use App\Models\QcModule\Configurations\QcTypes;
use App\SampleType;
use App\Standards;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class HistoryPage extends Component
{
    public string $startDate = '';
    public string $endDate = '';
    public string $qcTypeId = '';
    public string $qcSchemeId = '';
    public string $standardId = '';
    public string $sampleTypeId = '';
    public string $analysisTypeId = '';
    public string $analyteId = '';
    public string $remark = '';
    public string $groupBy = '1';

    public function updatedQcTypeId(): void
    {
        $this->standardId = '';
    }

    public function updatedSampleTypeId(): void
    {
        $this->analysisTypeId = '';
        $this->analyteId = '';
    }

    public function updatedAnalysisTypeId(): void
    {
        $this->analyteId = '';
    }

    public function clearFilters(): void
    {
        $this->startDate = '';
        $this->endDate = '';
        $this->qcTypeId = '';
        $this->qcSchemeId = '';
        $this->standardId = '';
        $this->sampleTypeId = '';
        $this->analysisTypeId = '';
        $this->analyteId = '';
        $this->remark = '';
        $this->groupBy = '1';
    }

    public function render()
    {
        $usesLegacyView = Schema::hasTable('qc_results_view');

        if ($usesLegacyView) {
            $query = DB::table('qc_results_view');
        } else {
            $query = DB::table('qc_results as qr')
                ->leftJoin('sample_headers as sh', 'sh.id', '=', 'qr.sample_header_id')
                ->leftJoin('sample_types as st', DB::raw('st.id::text'), '=', 'sh.sample_type_id')
                ->select([
                    'qr.id',
                    'sh.receipt_date',
                    'sh.batch_code',
                    'qr.sample_detail_code',
                    'qr.sample_detail_id',
                    'qr.sample_header_id',
                    'qr.analyte_id',
                    'qr.analyte_code',
                    'st.name as sample_type_name',
                    'sh.sample_type_id',
                    'qr.analysis_type_id',
                    'qr.qc_type_id',
                    'qr.qc_scheme_id',
                    'qr.result',
                    'qr.remarks',
                ])
                ->addSelect(DB::raw('NULL as standard_id'))
                ->addSelect(DB::raw('NULL as previous_result'))
                ->addSelect(DB::raw('NULL as config_percentage'))
                ->addSelect(DB::raw('qr.standard_value as main_value'))
                ->addSelect(DB::raw('NULL as analyst_name'));
        }

        if ($this->startDate !== '') {
            $query->where('receipt_date', '>=', $this->startDate);
        }

        if ($this->endDate !== '') {
            $query->where('receipt_date', '<=', $this->endDate);
        }

        if ($this->sampleTypeId !== '') {
            $query->where('sample_type_id', $this->sampleTypeId);
        }

        if ($this->analysisTypeId !== '') {
            $query->where('analysis_type_id', $this->analysisTypeId);
        }

        if ($this->qcTypeId !== '') {
            $query->where('qc_type_id', $this->qcTypeId);
        }

        if ($this->qcSchemeId !== '') {
            $query->where('qc_scheme_id', $this->qcSchemeId);
        }

        if ($this->analyteId !== '') {
            $query->where('analyte_id', $this->analyteId);
        }

        if ($this->standardId !== '' && $usesLegacyView) {
            $query->where('standard_id', $this->standardId);
        }

        if ($this->remark !== '' && strtolower($this->remark) !== 'all') {
            $query->where('remarks', $this->remark);
        }

        $results = $query->orderByDesc('receipt_date')->limit(1000)->get();

        if ($this->groupBy === '2') {
            $results = $results->unique('sample_detail_id')->values();
        }

        if ($this->groupBy === '3') {
            $results = $results->unique('sample_header_id')->values();
        }

        $sampleTypes = SampleType::query()->where('active', 1)->orderBy('name')->get(['id', 'name']);
        $qcTypes = QcTypes::query()->where('is_active', 1)->orderBy('name')->get(['id', 'name', 'use_existing_sample']);
        $qcSchemes = QcSchemes::query()->where('is_active', 1)->orderBy('name')->get(['id', 'name']);

        $standards = Standards::query()
            ->where('is_qc_standard', 1)
            ->where('status', 1)
            ->when($this->qcTypeId !== '', fn ($q) => $q->where('qc_type_id', $this->qcTypeId))
            ->orderBy('name')
            ->get(['id', 'name']);

        $analysisTypes = AnalysisType::query()
            ->when($this->sampleTypeId !== '', fn ($q) => $q->where('sample_type_id', $this->sampleTypeId))
            ->orderBy('name')
            ->get(['id', 'name']);

        $analyteIds = AnalysisElements::query()
            ->when($this->analysisTypeId !== '', fn ($q) => $q->where('analysis_type_id', $this->analysisTypeId))
            ->whereNotNull('analyte_id')
            ->pluck('analyte_id')
            ->filter()
            ->unique()
            ->values();

        $analytes = Analyte::query()
            ->when($analyteIds->isNotEmpty(), fn ($q) => $q->whereIn('id', $analyteIds->all()), fn ($q) => $q->whereRaw('1=0'))
            ->orderBy('code')
            ->get(['id', 'code']);

        $selectedQcType = $this->qcTypeId !== ''
            ? QcTypes::query()->find($this->qcTypeId)
            : null;

        return view('livewire.qc.history-page', [
            'results' => $results,
            'sampleTypes' => $sampleTypes,
            'qcTypes' => $qcTypes,
            'qcSchemes' => $qcSchemes,
            'standards' => $standards,
            'analysisTypes' => $analysisTypes,
            'analytes' => $analytes,
            'selectedQcType' => $selectedQcType,
        ]);
    }
}
