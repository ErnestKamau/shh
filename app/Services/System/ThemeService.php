<?php

namespace App\Services\System;

use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ThemeService
{
    public const PRIMARY = '#6D0A0E';

    public const PRIMARY_HOVER = '#8B1E22';

    public const ACCENT = '#ffffff';

    public const SIDEBAR_BG = '#6D0A0E';

    public const SIDEBAR_LINK_BG = 'rgba(255, 255, 255, 0.08)';

    public const BACKGROUND = '#F8FAFC';

    public const QUOTATION_PRIMARY = '#6D0A0E';

    public const QUOTATION_ACCENT = '#4CAF50';

    /** @var array<string, string> */
    public const AMSPEC_THEME = [
        'sys_theme_primary_color' => self::PRIMARY,
        'sys_theme_secondary_color' => self::PRIMARY_HOVER,
        'sys_theme_accent_color' => self::ACCENT,
        'sys_sidebar_bg_color' => self::SIDEBAR_BG,
        'sys_sidebar_link_bg' => self::SIDEBAR_LINK_BG,
    ];

    /** @var array<string, string> */
    public const CONFIG_DEFAULTS = self::AMSPEC_THEME;

    /** @var list<string> */
    public const BRANDING_CONFIG_KEYS = [
        'sys_theme_primary_color',
        'sys_theme_secondary_color',
        'sys_theme_accent_color',
        'sys_sidebar_bg_color',
        'sys_sidebar_link_bg',
        'sys_quotation_primary_color',
        'sys_quotation_accent_color',
    ];

    /**
     * @return array{r: int, g: int, b: int}
     */
    public static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return [
            'r' => hexdec(substr($hex, 0, 2)) ?: 0,
            'g' => hexdec(substr($hex, 2, 2)) ?: 0,
            'b' => hexdec(substr($hex, 4, 2)) ?: 0,
        ];
    }

    public static function rgbaFromHex(string $hex, float $alpha): string
    {
        $rgb = self::hexToRgb($hex);

        return sprintf('rgba(%d, %d, %d, %s)', $rgb['r'], $rgb['g'], $rgb['b'], rtrim(rtrim((string) $alpha, '0'), '.'));
    }

    public static function sidebarTextColor(string $sidebarBg): string
    {
        $rgb = self::hexToRgb($sidebarBg);
        $yiq = (($rgb['r'] * 299) + ($rgb['g'] * 587) + ($rgb['b'] * 114)) / 1000;

        return ($yiq >= 128) ? 'rgba(0, 0, 0, 0.85)' : 'rgba(255, 255, 255, 0.95)';
    }

    public static function sidebarTextMutedColor(string $sidebarBg): string
    {
        $rgb = self::hexToRgb($sidebarBg);
        $yiq = (($rgb['r'] * 299) + ($rgb['g'] * 587) + ($rgb['b'] * 114)) / 1000;

        return ($yiq >= 128) ? 'rgba(0, 0, 0, 0.55)' : 'rgba(255, 255, 255, 0.6)';
    }

    public static function sidebarHoverColor(string $sidebarBg, string $secondary): string
    {
        $rgb = self::hexToRgb($sidebarBg);
        $yiq = (($rgb['r'] * 299) + ($rgb['g'] * 587) + ($rgb['b'] * 114)) / 1000;

        return ($yiq >= 128) ? '#e5e7eb' : $secondary;
    }

    public static function tintFromHex(string $hex, float $whiteMix = 0.92): string
    {
        $rgb = self::hexToRgb($hex);

        $r = (int) round($rgb['r'] + (255 - $rgb['r']) * $whiteMix);
        $g = (int) round($rgb['g'] + (255 - $rgb['g']) * $whiteMix);
        $b = (int) round($rgb['b'] + (255 - $rgb['b']) * $whiteMix);

        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }

    /**
     * @return Collection<string, SystemConfiguration>
     */
    public static function themeConfigurationRecords(): Collection
    {
        return SystemConfiguration::query()
            ->whereIn('key', array_keys(self::AMSPEC_THEME))
            ->orderByDesc('updated_at')
            ->get()
            ->unique('key')
            ->keyBy('key');
    }

    /**
     * @return array<string, string>
     */
    public static function applyTheme(): array
    {
        $type = SystemConfigurationsType::query()->firstOrCreate(
            ['configuration_type' => 'Global System Theme Settings'],
            [
                'description' => 'Manage colors and aesthetics globally across all modules, including primary highlight colors and sidebar styles.',
                'status' => true,
            ]
        );

        foreach (self::AMSPEC_THEME as $key => $value) {
            SystemConfiguration::query()->updateOrCreate(
                ['key' => $key],
                [
                    'configuration_type_id' => $type->id,
                    'value' => $value,
                    'status' => true,
                ]
            );
        }

        $quotationType = SystemConfigurationsType::query()->where('configuration_type', 'Quotation Report')->first();

        if ($quotationType) {
            SystemConfiguration::query()->updateOrCreate(
                ['key' => 'sys_quotation_primary_color'],
                [
                    'configuration_type_id' => $quotationType->id,
                    'value' => self::QUOTATION_PRIMARY,
                    'status' => true,
                ]
            );
        }

        self::forgetCache();

        return self::AMSPEC_THEME;
    }

    /**
     * @return array<string, string>
     */
    public static function resolvedVariables(): array
    {
        return Cache::rememberForever('global_theme_variables', function () {
            $configs = self::themeConfigurationRecords();

            $primary = optional($configs->get('sys_theme_primary_color'))->value ?? self::PRIMARY;
            $secondary = optional($configs->get('sys_theme_secondary_color'))->value ?? self::PRIMARY_HOVER;
            $accent = optional($configs->get('sys_theme_accent_color'))->value ?? self::ACCENT;
            $sidebarBg = optional($configs->get('sys_sidebar_bg_color'))->value ?? self::SIDEBAR_BG;
            $sidebarLinkBg = optional($configs->get('sys_sidebar_link_bg'))->value ?? self::SIDEBAR_LINK_BG;

            return [
                'primary' => $primary,
                'secondary' => $secondary,
                'accent' => $accent,
                'sidebar_bg' => $sidebarBg,
                'sidebar_link_bg' => $sidebarLinkBg,
                'sidebar_hover' => self::sidebarHoverColor($sidebarBg, $secondary),
                'sidebar_text' => self::sidebarTextColor($sidebarBg),
                'sidebar_text_muted' => self::sidebarTextMutedColor($sidebarBg),
                'primary_soft' => self::rgbaFromHex($primary, 0.08),
                'primary_soft_10' => self::rgbaFromHex($primary, 0.1),
                'primary_focus' => self::rgbaFromHex($primary, 0.25),
                'primary_border_soft' => self::rgbaFromHex($primary, 0.3),
                'primary_shadow' => self::rgbaFromHex($primary, 0.2),
                'primary_soft_light' => self::rgbaFromHex($primary, 0.03),
                'primary_soft_medium' => self::rgbaFromHex($primary, 0.05),
                'primary_highlight' => self::rgbaFromHex($primary, 0.15),
                'primary_glow' => self::rgbaFromHex($primary, 0.4),
                'primary_tint' => self::tintFromHex($primary),
            ];
        });
    }

    public static function primaryColor(): string
    {
        return self::resolvedVariables()['primary'];
    }

    public static function forgetCache(): void
    {
        Cache::forget('global_theme_variables');
    }
}
