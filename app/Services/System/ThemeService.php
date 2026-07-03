<?php

namespace App\Services\System;

use App\Models\System\SystemConfiguration;
use Illuminate\Support\Facades\Cache;

class ThemeService
{
    public const PRIMARY = '#00A7DF';

    public const PRIMARY_HOVER = '#0090C0';

    public const SECONDARY = '#000000';

    public const ACCENT = '#FFFFFF';

    public const SIDEBAR_BG = '#000000';

    public const SIDEBAR_LINK_BG = 'rgba(255, 255, 255, 0.08)';

    public const PRIMARY_TINT = '#DAF2FA';

    public const BACKGROUND = '#F8FAFC';

    public const QUOTATION_PRIMARY = '#00A7DF';

    /** @var array<string, string> */
    public const CONFIG_DEFAULTS = [
        'sys_theme_primary_color' => self::PRIMARY,
        'sys_theme_secondary_color' => self::PRIMARY_HOVER,
        'sys_theme_accent_color' => self::ACCENT,
        'sys_sidebar_bg_color' => self::SIDEBAR_BG,
        'sys_sidebar_link_bg' => self::SIDEBAR_LINK_BG,
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

    /**
     * @return array<string, string>
     */
    public static function resolvedVariables(): array
    {
        return Cache::rememberForever('global_theme_variables', function () {
            $keys = array_keys(self::CONFIG_DEFAULTS);
            $configs = SystemConfiguration::whereIn('key', $keys)->get()->keyBy('key');

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
                'primary_tint' => self::PRIMARY_TINT,
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
