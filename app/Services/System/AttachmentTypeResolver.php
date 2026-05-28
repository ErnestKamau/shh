<?php

namespace App\Services\System;

use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;
use Illuminate\Support\Str;

class AttachmentTypeResolver
{
    /**
     * Resolve the system_configurations.id for an attachment_type label, creating it when missing.
     */
    public function resolveOrCreateAttachmentTypeId(string $label): ?string
    {
        $label = trim($label);
        if ($label === '') {
            return null;
        }

        $existing = SystemConfiguration::query()
            ->where('key', 'attachment_type')
            ->get()
            ->first(fn (SystemConfiguration $config) => (string) $config->value === $label);

        if ($existing !== null) {
            return (string) $existing->id;
        }

        $configurationTypeId = $this->resolveAttachmentConfigurationTypeId();
        if ($configurationTypeId === null) {
            return null;
        }

        $newConfig = new SystemConfiguration();
        $newConfig->key = 'attachment_type';
        $newConfig->value = $label;
        $newConfig->configuration_type_id = $configurationTypeId;
        $newConfig->save();

        return (string) $newConfig->id;
    }

    /**
     * The attachment_type_config_id row stores the Attachment Types configuration type id in value.
     */
    public function resolveAttachmentConfigurationTypeId(): ?string
    {
        $pointer = SystemConfiguration::query()
            ->where('key', 'attachment_type_config_id')
            ->first();

        if ($pointer !== null) {
            $typeId = trim((string) $pointer->value);
            if ($typeId !== '' && $this->isUuid($typeId) && SystemConfigurationsType::query()->where('id', $typeId)->exists()) {
                return $typeId;
            }
        }

        $type = SystemConfigurationsType::query()
            ->where(function ($query) {
                $query->where('configuration_type', 'Attachment Types')
                    ->orWhere('configuration_type', 'like', '%Attachment Type%');
            })
            ->orderBy('configuration_type')
            ->first();

        return $type !== null ? (string) $type->id : null;
    }

    private function isUuid(string $value): bool
    {
        return Str::isUuid($value);
    }
}
