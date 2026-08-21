<?php

namespace App\Services\Commercial;

use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;

final class QuotationApprovalConfigService
{
    public const CONFIG_TYPE = 'Quotation Approval';

    public const CONFIG_KEY = 'approver_role';

    public const CONFIG_KEY_TITLE = 'approver_title';

    public const DEFAULT_ROLE = 'Lab Manager';

    public const DEFAULT_TITLE = 'Quotation Approver';

    public function approverRoleName(): string
    {
        $cached = Cache::remember('quotation_approval.approver_role', 300, function (): string {
            $role = $this->configValue(self::CONFIG_KEY);

            return $role !== '' ? $role : self::DEFAULT_ROLE;
        });

        return $cached !== '' ? $cached : self::DEFAULT_ROLE;
    }

    public function configurationTitle(): string
    {
        $cached = Cache::remember('quotation_approval.approver_title', 300, function (): string {
            $title = $this->configValue(self::CONFIG_KEY_TITLE);

            return $title !== '' ? $title : self::DEFAULT_TITLE;
        });

        return $cached !== '' ? $cached : self::DEFAULT_TITLE;
    }

    /**
     * @return Collection<int, User>
     */
    public function usersForApproverRole(?string $roleName = null): Collection
    {
        $roleName = trim((string) ($roleName ?? $this->approverRoleName()));
        if ($roleName === '') {
            return collect();
        }

        return User::query()
            ->role($roleName)
            ->where('active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    public function saveConfiguration(string $title, string $roleName): void
    {
        $title = trim($title);
        $roleName = trim($roleName);

        if ($title === '') {
            throw new \InvalidArgumentException('Enter a name for this approval configuration.');
        }

        if ($roleName === '') {
            throw new \InvalidArgumentException('Select a role for quotation approval.');
        }

        if (! Role::query()->where('name', $roleName)->exists()) {
            throw new \InvalidArgumentException('The selected role does not exist.');
        }

        $type = SystemConfigurationsType::query()->firstOrCreate(
            ['configuration_type' => self::CONFIG_TYPE],
            [
                'description' => 'Role of personnel who can approve quotations.',
                'status' => 1,
            ],
        );

        $this->upsertConfig($type->id, self::CONFIG_KEY, $roleName);
        $this->upsertConfig($type->id, self::CONFIG_KEY_TITLE, $title);

        Cache::forget('quotation_approval.approver_role');
        Cache::forget('quotation_approval.approver_title');
    }

    public function setApproverRoleName(string $roleName): void
    {
        $this->saveConfiguration($this->configurationTitle(), $roleName);
    }

    private function configValue(string $key): string
    {
        $type = SystemConfigurationsType::query()
            ->where('configuration_type', self::CONFIG_TYPE)
            ->first();

        if ($type === null) {
            return '';
        }

        $config = SystemConfiguration::query()
            ->where('configuration_type_id', $type->id)
            ->where('key', $key)
            ->where('status', true)
            ->first();

        return trim((string) ($config?->value ?? ''));
    }

    private function upsertConfig(string $typeId, string $key, string $value): void
    {
        $config = SystemConfiguration::query()->firstOrNew([
            'configuration_type_id' => $typeId,
            'key' => $key,
        ]);

        $config->value = $value;
        $config->status = true;
        $config->save();
    }

    /**
     * @return list<array{id: string|int, name: string}>
     */
    public function availableRoles(): array
    {
        return Role::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(static fn (Role $role): array => [
                'id' => $role->id,
                'name' => (string) $role->name,
            ])
            ->filter(static fn (array $role): bool => $role['name'] !== '')
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public function availableRoleNames(): array
    {
        return array_values(array_map(
            static fn (array $role): string => $role['name'],
            $this->availableRoles(),
        ));
    }
}
