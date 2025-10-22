<?php

if (!function_exists('convertCurrency')) {
    /**
     * Convert amount from one currency to another using the currency_conversions table.
     *
     * @param float $amount
     * @param int $fromCurrencyId
     * @param int $toCurrencyId
     * @return float
     */
    function convertCurrency($amount, $fromCurrencyId, $toCurrencyId)
    {
        // If currencies are the same, no conversion needed
        if ($fromCurrencyId == $toCurrencyId) {
            return $amount;
        }

        // Try direct conversion (currency_1 -> currency_2)
        $conversion = \DB::table('currency_conversions')
            ->where('currency_1', $fromCurrencyId)
            ->where('currency_2', $toCurrencyId)
            ->first();

        if ($conversion) {
            return $amount * $conversion->ratio;
        }

        // Try reverse conversion (currency_2 -> currency_1)
        $conversion = \DB::table('currency_conversions')
            ->where('currency_1', $toCurrencyId)
            ->where('currency_2', $fromCurrencyId)
            ->first();

        if ($conversion && $conversion->ratio > 0) {
            return $amount / $conversion->ratio;
        }

        // No conversion rate found, return original amount
        return $amount;
    }
}

if (!function_exists('getCurrencySymbol')) {
    /**
     * Get currency symbol/name from module_pre_configs.
     *
     * @param int $currencyId
     * @return string
     */
    function getCurrencySymbol($currencyId)
    {
        $currency = \App\ModulePreConfigs::where('id', $currencyId)
            ->where('type', 'Currency')
            ->first();

        return $currency ? $currency->name : '';
    }
}

