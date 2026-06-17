<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TestRequestFormInstance extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'test_request_form_id',
        'submission_form_instance_id',
        'sampling_schedule_id',
        'form_data',
        'status',
        'created_by',
    ];

    protected $casts = [
        'form_data' => 'array',
    ];

    public function testRequestForm()
    {
        return $this->belongsTo(TestRequestForm::class, 'test_request_form_id');
    }

    public function submissionFormInstance()
    {
        return $this->belongsTo(SubmissionFormInstance::class, 'submission_form_instance_id');
    }

    public function samplingSchedule()
    {
        return $this->belongsTo(SamplingSchedule::class, 'sampling_schedule_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    /**
     * Map Test Request Form data to Submission Form elements (GCLA/F/03 template fields).
     */
    public static function mapToSubmissionFormRequestData(array $formData, $trf = null): array
    {
        $requestData = [];

        // Customer details mapping
        $requestData['customer_name'] = $formData['customer_name'] ?? $formData['client_name'] ?? $formData['customer'] ?? $formData['client'] ?? '';
        $requestData['customer_address'] = $formData['customer_address'] ?? $formData['address'] ?? $formData['physical_address'] ?? $formData['postal_address'] ?? '';
        
        $phoneVal = $formData['customer_phone'] ?? $formData['customer_tel_fax'] ?? $formData['mobile_number'] ?? $formData['phone'] ?? $formData['telephone'] ?? $formData['phone_number'] ?? '';
        if (is_array($phoneVal)) {
            $isSequential = array_keys($phoneVal) === range(0, count($phoneVal) - 1);
            $phoneVal = $isSequential ? implode(', ', $phoneVal) : implode(', ', array_keys(array_filter($phoneVal)));
        }
        $requestData['customer_tel'] = $phoneVal;
        $requestData['tel'] = $phoneVal;
        
        $emailVal = $formData['customer_email'] ?? $formData['email'] ?? $formData['email_address'] ?? '';
        if (is_array($emailVal)) {
            $isSequential = array_keys($emailVal) === range(0, count($emailVal) - 1);
            $emailVal = $isSequential ? implode(', ', $emailVal) : implode(', ', array_keys(array_filter($emailVal)));
        }
        $requestData['customer_email'] = $emailVal;

        // Dates mapping
        $samplingDate = $formData['sampling_date'] ?? $formData['collection_date'] ?? $formData['date'] ?? '';
        if (is_array($samplingDate)) {
            $samplingDate = reset($samplingDate);
        }
        $requestData['customer_date'] = $samplingDate;
        $requestData['date_of_sampling'] = $samplingDate;
        $requestData['part_b_date'] = $samplingDate;
        $requestData['manager_date'] = $samplingDate;

        // Number of samples
        $sampleRows = $formData['sample_rows'] ?? [];
        $requestData['number_of_samples'] = count($sampleRows) > 0 ? count($sampleRows) : 1;

        // Mode of work
        $reason = $formData['reason_of_collection'] ?? '';
        if (is_array($reason)) {
            $isSequential = array_keys($reason) === range(0, count($reason) - 1);
            $reason = $isSequential ? implode(', ', $reason) : implode(', ', array_keys(array_filter($reason)));
        }
        $requestData['mode_of_work'] = stripos((string)$reason, 'Express') !== false ? 'Express' : 'Normal';

        // Type of samples
        $sampleType = '';
        if ($trf && $trf->sampleType) {
            $sampleType = $trf->sampleType->name;
        }
        if (!$sampleType && !empty($sampleRows) && is_array($sampleRows)) {
            $firstRow = reset($sampleRows);
            $sampleType = $firstRow['sample_type'] ?? '';
        }
        $requestData['type_of_samples'] = $sampleType ?: 'Water/Food';

        // Deviations, capabilites
        $requestData['deviation_conditions'] = 'No';
        $requestData['deviation_answer'] = 'No';
        $requestData['laboratory_capability'] = 'has capability';
        $requestData['manager_capability'] = 'has';
        $requestData['laboratory_name'] = 'Polucon';
        $requestData['lab_manager_name'] = 'Manager';
        $requestData['laboratory_manager_name'] = 'Manager';

        $requestData['part_b_customer_name'] = $formData['customer_rep_name'] ?? $formData['contact_person'] ?? $requestData['customer_name'];
        
        $statement = $formData['statement_of_conformity'] ?? '';
        if (is_array($statement)) {
            $isSequential = array_keys($statement) === range(0, count($statement) - 1);
            $statement = $isSequential ? implode(', ', $statement) : implode(', ', array_keys(array_filter($statement)));
        }
        $requestData['conformity_statement_request'] = stripos((string)$statement, 'YES') !== false ? 'Requested' : 'Not Requested';

        // Parameters extraction
        $parametersRequested = [];
        if (!empty($sampleRows) && is_array($sampleRows)) {
            foreach ($sampleRows as $row) {
                if (is_array($row)) {
                    if (!empty($row['parameters'])) {
                        $pVal = $row['parameters'];
                        if (is_array($pVal)) {
                            $parametersRequested = array_merge($parametersRequested, $pVal);
                        } else {
                            $parametersRequested[] = (string)$pVal;
                        }
                    }
                    if (!empty($row['microbiology']) && $row['microbiology']) {
                        $parametersRequested[] = 'Microbiology';
                    }
                    if (!empty($row['legionella']) && $row['legionella']) {
                        $parametersRequested[] = 'Legionella';
                    }
                    if (!empty($row['chemical_analysis']) && $row['chemical_analysis']) {
                        $parametersRequested[] = 'Chemical Analysis';
                    }
                }
            }
        }

        $reqs = $formData['field_data_requirements'] ?? [];
        if (is_array($reqs)) {
            $isSequential = array_keys($reqs) === range(0, count($reqs) - 1);
            $resolvedReqs = $isSequential ? $reqs : array_keys(array_filter($reqs));
            foreach ($resolvedReqs as $r) {
                $parametersRequested[] = $r;
            }
        } elseif ($reqs) {
            $parametersRequested[] = (string)$reqs;
        }

        $parametersRequested = array_values(array_unique(array_filter($parametersRequested)));
        if (empty($parametersRequested)) {
            $parametersRequested = ['General Analysis'];
        }

        $requestData['parameter_requested'] = [];
        $requestData['amount_usd'] = [];
        $requestData['accept'] = [];
        $requestData['reject'] = [];

        foreach ($parametersRequested as $index => $param) {
            $requestData['parameter_requested'][$index] = $param;
            $requestData['amount_usd'][$index] = 0;
            $requestData['accept'][$index] = 1;
            $requestData['reject'][$index] = 0;
        }

        // Map sample_rows to sample_details fields
        if (!empty($sampleRows) && is_array($sampleRows)) {
            foreach ($sampleRows as $rowIndex => $row) {
                if (!is_array($row)) {
                    continue;
                }

                // Map common fields
                if (!empty($row['sample_no'])) {
                    $requestData['sample_no'][$rowIndex] = $row['sample_no'];
                }
                if (!empty($row['sample_description'])) {
                    $requestData['comments'][$rowIndex] = $row['sample_description'];
                }
                if (!empty($row['qty'])) {
                    $requestData['quantity'][$rowIndex] = $row['qty'];
                }
                if (!empty($row['sample_temp'])) {
                    $requestData['quantity'][$rowIndex] = ($requestData['quantity'][$rowIndex] ?? '') . ' - Temp: ' . $row['sample_temp'];
                }

                // Food-specific fields
                if (!empty($row['production_date'])) {
                    $requestData['mfg_date'][$rowIndex] = $row['production_date'];
                }
                if (!empty($row['expiration_date'])) {
                    $requestData['expiry_date'][$rowIndex] = $row['expiration_date'];
                }
                if (!empty($row['batch_number'])) {
                    $requestData['batch_lot_no'][$rowIndex] = $row['batch_number'];
                }
                if (!empty($row['sample_condition'])) {
                    // Try to resolve sample condition ID from name
                    $conditionId = self::resolveSampleConditionId($row['sample_condition']);
                    if ($conditionId) {
                        $requestData['sample_condition_id'][$rowIndex] = $conditionId;
                    }
                }
                if (!empty($row['sampling_point'])) {
                    // Try to resolve sample point ID from name
                    $pointId = self::resolveSamplePointId($row['sampling_point']);
                    if ($pointId) {
                        $requestData['sample_point_id'][$rowIndex] = $pointId;
                    }
                }

                // Water-specific fields
                if (!empty($row['location'])) {
                    $requestData['comments'][$rowIndex] = ($requestData['comments'][$rowIndex] ?? $row['sample_description'] ?? '') . ' - Location: ' . $row['location'];
                }
                if (!empty($row['ph'])) {
                    $requestData['comments'][$rowIndex] = ($requestData['comments'][$rowIndex] ?? $row['sample_description'] ?? '') . ' - pH: ' . $row['ph'];
                }
                if (!empty($row['appearance'])) {
                    $requestData['comments'][$rowIndex] = ($requestData['comments'][$rowIndex] ?? $row['sample_description'] ?? '') . ' - Appearance: ' . $row['appearance'];
                }
                if (!empty($row['residual_chlorine'])) {
                    $requestData['comments'][$rowIndex] = ($requestData['comments'][$rowIndex] ?? $row['sample_description'] ?? '') . ' - Residual Chlorine: ' . $row['residual_chlorine'];
                }
                if (!empty($row['odor'])) {
                    $requestData['comments'][$rowIndex] = ($requestData['comments'][$rowIndex] ?? $row['sample_description'] ?? '') . ' - Odor: ' . $row['odor'];
                }
            }
        }

        return $requestData;
    }

    /**
     * Resolve sample condition ID from condition name
     */
    private static function resolveSampleConditionId($conditionName): ?string
    {
        if (empty($conditionName)) {
            return null;
        }

        $condition = \DB::table('sample_conditions')
            ->where('name', 'like', '%' . $conditionName . '%')
            ->first();

        return $condition ? $condition->id : null;
    }

    /**
     * Resolve sample point ID from point name
     */
    private static function resolveSamplePointId($pointName): ?string
    {
        if (empty($pointName)) {
            return null;
        }

        $point = \DB::table('sample_points')
            ->where('name', 'like', '%' . $pointName . '%')
            ->first();

        return $point ? $point->id : null;
    }
}
