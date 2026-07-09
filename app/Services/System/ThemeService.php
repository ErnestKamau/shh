<?php

namespace App\Services\System;

use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;
use Illuminate\Support\Facades\Cache;

class ThemeService
{
    public const PRIMARY = '#00A7DF';

    public const PRIMARY_HOVER = '#0090C0';

    public const SECONDARY = '#000000';

    public const ACCENT = '#FFFFFF';

    public const SIDEBAR_BG = '#000000';

    public const SIDEBAR_LINK_BG = 'rgba(255, 255, 255, 0.08)';

    public const BACKGROUND = '#F8FAFC';

    public const QUOTATION_PRIMARY = '#00A7DF';

    public const AMSPEC_PRIMARY = '#6D0A0E';

    public const AMSPEC_PRIMARY_HOVER = '#8B1E22';

    public const AMSPEC_QUOTATION_PRIMARY = '#6D0A0E';

    /** @var array<string, string> Kenya Dairy Board (cyan) palette */
    public const KDB_THEME = [
        'sys_theme_primary_color' => self::PRIMARY,
        'sys_theme_secondary_color' => self::PRIMARY_HOVER,
        'sys_theme_accent_color' => self::ACCENT,
        'sys_sidebar_bg_color' => self::SIDEBAR_BG,
        'sys_sidebar_link_bg' => self::SIDEBAR_LINK_BG,
    ];

    /** @var array<string, string> AmSpec (maroon / burgundy) palette */
    public const AMSPEC_THEME = [
        'sys_theme_primary_color' => self::AMSPEC_PRIMARY,
        'sys_theme_secondary_color' => self::AMSPEC_PRIMARY_HOVER,
        'sys_theme_accent_color' => '#ffffff',
        'sys_sidebar_bg_color' => self::AMSPEC_PRIMARY,
        'sys_sidebar_link_bg' => 'rgba(255, 255, 255, 0.08)',
    ];

    /** @var array<string, string> */
    public const CONFIG_DEFAULTS = self::KDB_THEME;

    /** @var array<string, array<string, string>> */
    public const PRESETS = [
        'amspec' => self::AMSPEC_THEME,
        'kdb' => self::KDB_THEME,
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
     * @return array<string, string>
     */
    public static function applyPreset(string $preset): array
    {
        $palette = self::PRESETS[$preset] ?? null;

        if ($palette === null) {
            throw new \InvalidArgumentException("Unknown theme preset [{$preset}]. Use: ".implode(', ', array_keys(self::PRESETS)));
        }

        $type = SystemConfigurationsType::query()->firstOrCreate(
            ['configuration_type' => 'Global System Theme Settings'],
            [
                'description' => 'Manage colors and aesthetics globally across all modules, including primary highlight colors and sidebar styles.',
                'status' => true,
            ]
        );

        foreach ($palette as $key => $value) {
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
            $quotationPrimary = $preset === 'amspec'
                ? self::AMSPEC_QUOTATION_PRIMARY
                : self::QUOTATION_PRIMARY;

            SystemConfiguration::query()->updateOrCreate(
                ['key' => 'sys_quotation_primary_color'],
                [
                    'configuration_type_id' => $quotationType->id,
                    'value' => $quotationPrimary,
                    'status' => true,
                ]
            );
        }

        self::forgetCache();

        return $palette;
    }

    /**
     * @return array<string, string>
     */
    public static function resolvedVariables(): array
    {
        return Cache::rememberForever('global_theme_variables', function () {
            $keys = array_keys(self::KDB_THEME);
            $configs = SystemConfiguration::whereIn('key', $keys)->get()->keyBy('key');

            $primary = optional($configs->get('sys_theme_primary_color'))->value ?? self::AMSPEC_PRIMARY;
            $secondary = optional($configs->get('sys_theme_secondary_color'))->value ?? self::AMSPEC_PRIMARY_HOVER;
            $accent = optional($configs->get('sys_theme_accent_color'))->value ?? '#ffffff';
            $sidebarBg = optional($configs->get('sys_sidebar_bg_color'))->value ?? self::AMSPEC_PRIMARY;
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
