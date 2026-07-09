<?php

namespace App\DTOs\Dashboard;

use App\DTOs\Dashboard\Concerns\ArrayableDto;

final class ReportLanguageDownloadDTO
{
    use ArrayableDto;

    public function __construct(
        public readonly string $code,
        public readonly string $label,
        public readonly ?string $downloadUrl,
    ) {}
}
