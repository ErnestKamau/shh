<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;
use App\Services\Planner\SamplingScheduleVisibility;
use App\User;

class SamplingSchedule extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'sampling_schedules';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'title',
        'crm_customer_id',
        'contact_id',
        'contact_ids',
        'sampling_datetime',
        'location',
        'sample_point_id',
        'sample_type_id',
        'analysis_type_id',
        'parameters',
        'sample_details',
        'number_of_samples',
        'frequency',
        'notify_client',
        'personnel_id',
        'personnel_ids',
        'description',
        'company_id',
        'is_collected',
    ];

    protected $casts = [
        'sampling_datetime' => 'datetime',
        'notify_client' => 'boolean',
        'number_of_samples' => 'integer',
        'parameters' => 'array',
        'sample_details' => 'array',
        'contact_ids' => 'array',
        'personnel_ids' => 'array',
        'is_collected' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(\App\Models\CRM\CRMCustomer::class, 'crm_customer_id');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeVisibleTo(Builder $query, ?User $user = null): Builder
    {
        return SamplingScheduleVisibility::constrain($query, $user);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeAssignedTo(Builder $query, string $userId): Builder
    {
        return SamplingScheduleVisibility::constrainAssigned($query, $userId);
    }

    public function contact()
    {
        return $this->belongsTo(\App\Models\CRM\CustomerContact::class, 'contact_id');
    }

    public function samplePoint()
    {
        return $this->belongsTo(\App\Models\CRM\SamplePoint::class, 'sample_point_id');
    }

    /**
     * Human-readable location for UI / exports / email.
     */
    public function locationDisplayName(): string
    {
        if ($this->relationLoaded('samplePoint') && $this->samplePoint) {
            return (string) $this->samplePoint->display_name;
        }

        if (! empty($this->sample_point_id)) {
            $point = $this->samplePoint()->first();
            if ($point) {
                return (string) $point->display_name;
            }
        }

        $location = trim((string) ($this->location ?? ''));

        return $location !== '' ? $location : 'N/A';
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\CRM\CustomerContact>
     */
    public function contacts()
    {
        $ids = $this->resolvedContactIds();

        if ($ids === []) {
            return collect();
        }

        return \App\Models\CRM\CustomerContact::query()
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn ($contact) => array_search((string) $contact->id, $ids, true))
            ->values();
    }

    /**
     * @return list<string>
     */
    public function resolvedContactIds(): array
    {
        $ids = is_array($this->contact_ids) ? $this->contact_ids : [];
        $ids = array_values(array_filter(array_map('strval', $ids)));

        if ($ids === [] && ! empty($this->contact_id)) {
            $ids = [(string) $this->contact_id];
        }

        return $ids;
    }

    public function sample_type()
    {
        return $this->belongsTo(\App\SampleType::class, 'sample_type_id');
    }

    /**
     * Sample type IDs explicitly set on the schedule (header and/or sample_details).
     *
     * @return list<string>
     */
    public function assignedSampleTypeIds(): array
    {
        $ids = [];

        if (! empty($this->sample_type_id)) {
            $ids[] = (string) $this->sample_type_id;
        }

        foreach ((array) ($this->sample_details ?? []) as $entry) {
            $id = trim((string) ($entry['sample_type_id'] ?? ''));
            if ($id !== '') {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * Whether this schedule is tied to the given sample type.
     */
    public function matchesSampleType(string $sampleTypeId): bool
    {
        $sampleTypeId = trim($sampleTypeId);
        if ($sampleTypeId === '') {
            return false;
        }

        return in_array($sampleTypeId, $this->assignedSampleTypeIds(), true);
    }

    /**
     * True when the schedule matches the sample type, or has no sample type set
     * (usable with any sampling form until typed).
     */
    public function isCompatibleWithSampleType(string $sampleTypeId): bool
    {
        $assigned = $this->assignedSampleTypeIds();
        if ($assigned === []) {
            return trim($sampleTypeId) !== '';
        }

        return $this->matchesSampleType($sampleTypeId);
    }

    public function analysis_type()
    {
        return $this->belongsTo(\App\AnalysisType::class, 'analysis_type_id');
    }

    public function personnel()
    {
        return $this->belongsTo(\App\User::class, 'personnel_id');
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\User>
     */
    public function personnelMembers()
    {
        $ids = $this->resolvedPersonnelIds();

        if ($ids === []) {
            return collect();
        }

        return \App\User::query()
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn ($user) => array_search((string) $user->id, $ids, true))
            ->values();
    }

    /**
     * @return list<string>
     */
    public function resolvedPersonnelIds(): array
    {
        $ids = is_array($this->personnel_ids) ? $this->personnel_ids : [];
        $ids = array_values(array_filter(array_map('strval', $ids)));

        if ($ids === [] && ! empty($this->personnel_id)) {
            $ids = [(string) $this->personnel_id];
        }

        return $ids;
    }

    public function personnelNames(): string
    {
        $names = $this->personnelMembers()
            ->pluck('name')
            ->filter()
            ->values()
            ->all();

        return $names !== [] ? implode(', ', $names) : 'N/A';
    }

    /**
     * @return array{
     *     scheduled: int,
     *     collected: int,
     *     remaining: int,
     *     status: string,
     *     label: string,
     *     is_complete: bool
     * }
     */
    public function collectionProgress(): array
    {
        return app(\App\Services\Planner\SamplingScheduleCollectionProgress::class)->progress($this);
    }

    /**
     * @return 'pending'|'partial'|'collected'
     */
    public function collectionStatus(): string
    {
        return app(\App\Services\Planner\SamplingScheduleCollectionProgress::class)->status($this);
    }

    public function submissionFormInstances()
    {
        return $this->hasMany(SubmissionFormInstance::class, 'sampling_schedule_id');
    }

    /**
     * @deprecated-remove TRF_LAYER_MANIFEST.md Phase 6
     */
    public function testRequestFormInstances()
    {
        return $this->submissionFormInstances();
    }
}

