<?php

namespace Tests\Unit\Services;

use App\Models\JobNumberSequence;
use App\Models\SampleSequence;
use App\SampleDetails;
use App\SampleHeader;
use App\Services\Sampleworkflow\JobSampleNumberingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class JobSampleNumberingServiceTest extends TestCase
{
    private JobSampleNumberingService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('job_number_sequences');
        Schema::dropIfExists('sample_sequences');

        Schema::create('job_number_sequences', function (Blueprint $table) {
            $table->id();
            $table->char('date_ymd', 6)->unique();
            $table->unsignedInteger('last_sequence')->default(0);
            $table->timestamps();
        });

        Schema::create('sample_sequences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('batch_code');
            $table->char('prefix', 1)->default('');
            $table->integer('sample_sequence')->default(0);
            $table->timestamps();
            $table->unique(['batch_code', 'prefix']);
        });

        $this->service = app(JobSampleNumberingService::class);
    }

    /** @test */
    public function it_generates_daily_job_numbers(): void
    {
        $date = Carbon::create(2026, 4, 28, 10, 0, 0);

        $first = $this->service->generateJobNumber($date);
        $second = $this->service->generateJobNumber($date);

        $this->assertSame('260428001', $first);
        $this->assertSame('260428002', $second);
    }

    /** @test */
    public function it_resets_job_sequence_on_new_day(): void
    {
        $dayOne = Carbon::create(2026, 4, 28, 10, 0, 0);
        $dayTwo = Carbon::create(2026, 4, 29, 10, 0, 0);

        $this->service->generateJobNumber($dayOne);
        $this->service->generateJobNumber($dayOne);

        $nextDay = $this->service->generateJobNumber($dayTwo);

        $this->assertSame('260429001', $nextDay);
    }

    /** @test */
    public function it_generates_prefixed_sample_codes_per_job_and_category(): void
    {
        $job = '260428001';

        $microOne = $this->service->nextSampleCode($job, JobSampleNumberingService::PREFIX_MICROBIOLOGY);
        $microTwo = $this->service->nextSampleCode($job, JobSampleNumberingService::PREFIX_MICROBIOLOGY);
        $chemOne = $this->service->nextSampleCode($job, JobSampleNumberingService::PREFIX_CHEMISTRY);

        $this->assertSame('260428001-M001', $microOne);
        $this->assertSame('260428001-M002', $microTwo);
        $this->assertSame('260428001-C001', $chemOne);
    }

    /** @test */
    public function it_builds_report_numbers_with_revision(): void
    {
        $this->assertSame('260428001-R01', $this->service->reportNumber('260428001', 1));
        $this->assertSame('260428001-R02', $this->service->reportNumber('260428001', 2));
    }

    /** @test */
    public function it_resolves_category_prefix_from_row_flags(): void
    {
        $prefix = $this->service->resolveCategoryPrefixFromRow([
            'test_category' => 'chemistry',
        ]);

        $this->assertSame(JobSampleNumberingService::PREFIX_CHEMISTRY, $prefix);
    }

    /** @test */
    public function it_rejects_multiple_category_flags(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->resolveCategoryPrefix(true, false, true);
    }

    /** @test */
    public function it_syncs_report_numbers_for_batch_amendment(): void
    {
        Schema::dropIfExists('sample_details');
        Schema::dropIfExists('sample_headers');

        Schema::create('sample_headers', function (Blueprint $table) {
            $table->id();
            $table->string('batch_code');
            $table->timestamps();
        });

        Schema::create('sample_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sample_header_id');
            $table->string('sample_code')->nullable();
            $table->string('report_number')->nullable();
            $table->timestamps();
        });

        $header = SampleHeader::query()->create(['batch_code' => '260428001']);
        SampleDetails::query()->create([
            'sample_header_id' => $header->id,
            'sample_code' => '260428001-M001',
            'report_number' => '260428001-R01',
        ]);
        SampleDetails::query()->create([
            'sample_header_id' => $header->id,
            'sample_code' => '260428001-C001',
            'report_number' => '260428001-R01',
        ]);

        $this->service->syncReportNumbersForBatch($header, 2);

        $this->assertSame(
            ['260428001-R02', '260428001-R02'],
            SampleDetails::query()->orderBy('id')->pluck('report_number')->all(),
        );
    }
}
