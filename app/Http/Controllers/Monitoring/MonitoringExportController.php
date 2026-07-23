<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use App\LabSection;
use App\Models\Equipments\Equipment;
use App\Models\Monitoring\MonitoringLog;
use App\Models\Monitoring\MonitoringTemplate;
use App\Services\Monitoring\MonitoringLogValueResolver;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MonitoringExportController extends Controller
{
    protected MonitoringLogValueResolver $valueResolver;

    public function __construct()
    {
        $this->valueResolver = new MonitoringLogValueResolver;
    }

    public function exportLws011(Request $request)
    {
        $request->validate([
            'template_id' => 'required|string',
            'month' => 'required|date_format:Y-m',
            'section_id' => 'nullable|required_without:equipment_id|string',
            'equipment_id' => 'nullable|required_without:section_id|string',
        ]);

        $templateId = $request->input('template_id');
        $monthStr = $request->input('month');
        $carbonMonth = Carbon::createFromFormat('Y-m', $monthStr);
        $year = $carbonMonth->year;
        $monthNumber = $carbonMonth->month;

        $template = MonitoringTemplate::findOrFail($templateId);

        $section = null;
        $equipment = null;
        $logsQuery = MonitoringLog::query()
            ->with(['entries', 'executedBy', 'approvedBy'])
            ->where('template_id', $templateId)
            ->whereYear('log_date', $year)
            ->whereMonth('log_date', $monthNumber);

        if ($request->filled('equipment_id')) {
            $equipment = Equipment::query()
                ->with(['latestCalibration', 'assetLocation', 'lab'])
                ->findOrFail($request->input('equipment_id'));

            $logsQuery->where('equipment_id', $equipment->id);
        } else {
            $section = LabSection::query()
                ->with(['equipment.latestCalibration', 'equipment.assetLocation', 'reportingUnit'])
                ->findOrFail($request->input('section_id'));

            $equipment = $section->equipment;
            if ($equipment) {
                $equipment->loadMissing(['latestCalibration', 'assetLocation', 'lab']);
            }

            $logsQuery->where('lab_section_id', $section->id);
        }

        $logs = $logsQuery
            ->orderBy('log_date', 'asc')
            ->orderBy('frequency_slot', 'asc')
            ->get();

        $logsByDay = $logs->groupBy(fn ($log) => $log->log_date->day);
        $daysInMonth = $carbonMonth->daysInMonth;
        $gridData = [];

        for ($day = 1; $day <= 31; $day++) {
            if ($day > $daysInMonth) {
                $gridData[$day] = $this->emptyDayRow();

                continue;
            }

            $dayLogs = $logsByDay->get($day, collect());

            if ($dayLogs->isEmpty()) {
                $gridData[$day] = $this->emptyDayRow();
            } else {
                $gridData[$day] = $this->buildDayRow($dayLogs, $equipment);
            }
        }

        $toleranceStr = $this->resolveToleranceString($equipment);

        $viewData = [
            'gridData' => $gridData,
            'monthName' => $carbonMonth->format('F Y'),
            'monthNum' => $monthNumber,
            'yearNum' => $year,
            'section' => $section,
            'equipment' => $equipment,
            'toleranceStr' => $toleranceStr,
            'template' => $template,
        ];

        $pdf = Pdf::loadView('pdfs.lws_011_temperature_monitoring', $viewData);
        $pdf->setPaper('a4', 'portrait');

        $fileName = "LWS_011_Temperature_Monitoring_Record_{$monthStr}.pdf";

        return $pdf->stream($fileName);
    }

    /**
     * @return array<string, mixed>
     */
    protected function emptyDayRow(): array
    {
        return [
            'exists' => false,
            'chiller_min' => '',
            'chiller_max' => '',
            'chiller_obs' => '',
            'freezer_min' => '',
            'freezer_max' => '',
            'freezer_obs' => '',
            'calibration_valid' => '',
            'wire_plug_condition' => '',
            'operator' => '',
            'verifier' => '',
            'remarks' => '',
        ];
    }

    /**
     * @param  Collection<int, MonitoringLog>  $dayLogs
     * @return array<string, mixed>
     */
    protected function buildDayRow(Collection $dayLogs, ?Equipment $equipment): array
    {
        $chillerMinValues = [];
        $chillerMaxValues = [];
        $chillerObsValues = [];

        $freezerMinValues = [];
        $freezerMaxValues = [];
        $freezerObsValues = [];

        $calibrationsValid = [];
        $wirePlugConditions = [];
        $remarks = [];
        $operators = [];
        $verifiers = [];

        foreach ($dayLogs as $log) {
            $cMin = $this->findValue($log, ['chiller_temp_min', 'chiller_min', 'refrigerator_min', 'ref_min', 'chiller_refrigerator_temp_min', 'chiller_refrigerator_min', 'chiller_temp', 'refrigerator_temp', 'observed_temp', 'temperature', 'temp'], ['chiller min', 'refrigerator min', 'ref min', 'chiller temp min']);
            $cMax = $this->findValue($log, ['chiller_temp_max', 'chiller_max', 'refrigerator_max', 'ref_max', 'chiller_refrigerator_temp_max', 'chiller_refrigerator_max', 'chiller_temp', 'refrigerator_temp', 'observed_temp', 'temperature', 'temp'], ['chiller max', 'refrigerator max', 'ref max', 'chiller temp max']);
            $cObs = $this->findValue($log, ['chiller_temp', 'refrigerator_temp', 'observed_temp', 'temperature', 'temp', 'chiller_temp_observed', 'chiller_observed', 'refrigerator_observed'], ['chiller temp', 'refrigerator temp', 'observed temp', 'chiller actual', 'refrigerator actual']);

            if ($cMin !== null && is_numeric($cMin)) {
                $chillerMinValues[] = (float) $cMin;
            }
            if ($cMax !== null && is_numeric($cMax)) {
                $chillerMaxValues[] = (float) $cMax;
            }
            if ($cObs !== null && is_numeric($cObs)) {
                $chillerObsValues[] = (float) $cObs;
            }

            $fMin = $this->findValue($log, ['freezer_temp_min', 'freezer_min', 'freezer_temp', 'observed_temp', 'temperature', 'temp'], ['freezer min', 'freezer temp min']);
            $fMax = $this->findValue($log, ['freezer_temp_max', 'freezer_max', 'freezer_temp', 'observed_temp', 'temperature', 'temp'], ['freezer max', 'freezer temp max']);
            $fObs = $this->findValue($log, ['freezer_temp', 'observed_temp', 'temperature', 'temp', 'freezer_temp_observed', 'freezer_observed'], ['freezer temp', 'observed temp', 'freezer actual']);

            if ($fMin !== null && is_numeric($fMin)) {
                $freezerMinValues[] = (float) $fMin;
            }
            if ($fMax !== null && is_numeric($fMax)) {
                $freezerMaxValues[] = (float) $fMax;
            }
            if ($fObs !== null && is_numeric($fObs)) {
                $freezerObsValues[] = (float) $fObs;
            }

            $calVal = $this->findValue($log, ['calibration_valid', 'thermometer_calibration_valid', 'cal_valid'], ['calibration', 'cal valid']);
            if ($calVal !== null) {
                $calibrationsValid[] = $calVal;
            }

            $plugCond = $this->findValue($log, ['wire_plug_condition', 'plug_condition', 'wire_plug', 'plug'], ['wire', 'plug', 'cable']);
            if ($plugCond !== null) {
                $wirePlugConditions[] = $plugCond;
            }

            if (filled($log->resolvedRemark())) {
                $remarks[] = $log->resolvedRemark();
            }

            if ($log->executedBy) {
                $operators[] = $this->getInitials($log->executedBy->name);
            }
            if ($log->approvedBy) {
                $verifiers[] = $this->getInitials($log->approvedBy->name);
            }
        }

        $chillerMin = count($chillerMinValues) ? min($chillerMinValues) : '';
        $chillerMax = count($chillerMaxValues) ? max($chillerMaxValues) : '';
        $chillerObs = count($chillerObsValues) ? end($chillerObsValues) : '';

        $freezerMin = count($freezerMinValues) ? min($freezerMinValues) : '';
        $freezerMax = count($freezerMaxValues) ? max($freezerMaxValues) : '';
        $freezerObs = count($freezerObsValues) ? end($freezerObsValues) : '';

        $cal = 'Yes';
        if (count($calibrationsValid)) {
            $hasNo = collect($calibrationsValid)->contains(fn ($v) => in_array($v, [false, 0, 'No', 'no', '0', 'fail', 'Fail'], true));
            $cal = $hasNo ? 'No' : 'Yes';
        } elseif ($equipment) {
            $calibrationValid = false;
            $calibration = $equipment->latestCalibration;
            if ($calibration && $calibration->date) {
                $calibrationExpiry = $calibration->date->copy()->addYear();
                $calibrationValid = now()->lte($calibrationExpiry);
            }
            $cal = $calibrationValid ? 'Yes' : 'No';
        }

        $plug = 'Yes';
        if (count($wirePlugConditions)) {
            $hasNo = collect($wirePlugConditions)->contains(fn ($v) => in_array($v, [false, 0, 'No', 'no', 'Fail', 'fail', '0'], true));
            $plug = $hasNo ? 'No' : 'Yes';
        }

        return [
            'exists' => true,
            'chiller_min' => $chillerMin,
            'chiller_max' => $chillerMax,
            'chiller_obs' => $chillerObs,
            'freezer_min' => $freezerMin,
            'freezer_max' => $freezerMax,
            'freezer_obs' => $freezerObs,
            'calibration_valid' => $cal,
            'wire_plug_condition' => $plug,
            'operator' => implode(', ', array_unique($operators)),
            'verifier' => implode(', ', array_unique($verifiers)),
            'remarks' => implode('; ', array_unique($remarks)),
        ];
    }

    protected function resolveToleranceString(?Equipment $equipment): string
    {
        $expectedMin = null;
        $expectedMax = null;
        $unit = '°C';

        if (! $equipment) {
            return '—';
        }

        if ($equipment->daily_log_value_types && is_array($equipment->daily_log_value_types)) {
            foreach ($equipment->daily_log_value_types as $vt) {
                if (($vt['value_type'] ?? '') === 'range' && isset($vt['expected_min']) && isset($vt['expected_max'])) {
                    $expectedMin = $vt['expected_min'];
                    $expectedMax = $vt['expected_max'];
                    if (isset($vt['reporting_unit'])) {
                        $unit = $vt['reporting_unit'];
                    }
                    break;
                }
            }
        }

        if ($expectedMin === null && $equipment->daily_log_expected_min !== null) {
            $expectedMin = $equipment->daily_log_expected_min;
        }
        if ($expectedMax === null && $equipment->daily_log_expected_max !== null) {
            $expectedMax = $equipment->daily_log_expected_max;
        }
        if ($equipment->daily_log_reporting_unit) {
            $unit = $equipment->daily_log_reporting_unit;
        }

        return ($expectedMin !== null && $expectedMax !== null)
            ? "{$expectedMin}{$unit} to {$expectedMax}{$unit}"
            : '—';
    }

    protected function findValue(MonitoringLog $log, array $keys, array $labelSubstrings = [])
    {
        foreach ($keys as $key) {
            $val = $this->valueResolver->valueFromLogByKey($log, $key);
            if ($val !== null && $val !== '') {
                return $val;
            }
        }

        foreach ($log->entries as $entry) {
            foreach ($keys as $key) {
                if (strcasecmp($entry->field_key, $key) === 0) {
                    return $entry->computed_value ?? $entry->raw_value;
                }
            }
            foreach ($labelSubstrings as $sub) {
                if (str_contains(strtolower($entry->field_label ?? ''), strtolower($sub))) {
                    return $entry->computed_value ?? $entry->raw_value;
                }
            }
        }

        return null;
    }

    protected function getInitials(string $name): string
    {
        $words = explode(' ', preg_replace('/\s+/', ' ', trim($name)));
        $initials = '';
        foreach ($words as $w) {
            if (strlen($w) > 0) {
                $initials .= strtoupper($w[0]);
            }
        }

        return $initials;
    }
}
