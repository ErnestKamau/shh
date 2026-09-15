<?php

namespace App\Services\Sampleworkflow;

use App\SampleDetails;
use App\SampleHeader;
use Illuminate\Support\Collection;

class StatementOfConformityService
{
    public function __construct(
        private readonly CommentsInterpretationsDefaultsService $defaults,
    ) {}

    /**
     * Always refresh statement of conformity (header_body) from assigned specifications.
     * Notes are filled only when empty so modal edits are kept.
     */
    public function ensureForSample(SampleDetails $sample, ?SampleHeader $batch = null): SampleDetails
    {
        $batch ??= SampleHeader::query()->find($sample->sample_header_id);
        $generated = $this->defaults->generate($sample, $batch);

        $sample->header_body = $generated['header_body'];

        if ($this->defaults->isHtmlEmpty($sample->notes_body)) {
            $sample->notes_body = $generated['notes_body'];
        }

        $sample->save();

        return $sample;
    }

    /**
     * @param  Collection<int, SampleDetails>|iterable<SampleDetails>|null  $samples
     */
    public function ensureForBatch(SampleHeader $batch, ?iterable $samples = null): void
    {
        $samples ??= SampleDetails::query()
            ->where('sample_header_id', $batch->id)
            ->get();

        foreach ($samples as $sample) {
            if (! $sample instanceof SampleDetails) {
                continue;
            }

            $this->ensureForSample($sample, $batch);
        }
    }

    /**
     * @param  list<string|int>  $sampleDetailIds
     */
    public function ensureForSampleIds(array $sampleDetailIds, ?SampleHeader $batch = null): void
    {
        $ids = array_values(array_unique(array_filter(array_map(
            static fn ($id): string => trim((string) $id),
            $sampleDetailIds
        ))));

        if ($ids === []) {
            return;
        }

        $samples = SampleDetails::query()->whereIn('id', $ids)->get();
        foreach ($samples as $sample) {
            $this->ensureForSample($sample, $batch);
        }
    }
}
