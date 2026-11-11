<?php

use App\Models\Setting;

if (!function_exists('setting')) {
    /**
     * Get a setting value by key with optional default.
     * Usage: setting('company_name', 'My Company')
     */
    function setting(string $key, mixed $default = null): mixed
    {
        try {
            return Setting::get($key, $default);
        } catch (\Throwable) {
            return $default;
        }
    }
}

if (!function_exists('setting_image')) {
    /**
     * Return the public URL for an image setting.
     * Falls back to $default if not set or file missing.
     */
    function setting_image(string $key, string $default = ''): string
    {
        try {
            $val = Setting::get($key);
            if ($val && (file_exists(public_path('storage/' . $val)) || file_exists(storage_path('app/public/' . $val)))) {
                return asset('storage/' . $val);
            }
        } catch (\Throwable) {}
        return $default;
    }
}

if (!function_exists('setting_image_path')) {
    /**
     * Absolute filesystem path for DomPDF / local image embedding.
     */
    function setting_image_path(string $key): string
    {
        try {
            $val = Setting::get($key);
            if ($val) {
                $path = public_path('storage/' . $val);
                if (file_exists($path)) {
                    return str_replace('\\', '/', $path);
                }
            }
        } catch (\Throwable) {}
        return '';
    }
}

if (!function_exists('format_date')) {
    /**
     * Format a date/Carbon instance using the admin-configured date format.
     * Usage: format_date($user->created_at)
     */
    function format_date(mixed $date, string $fallback = 'd/m/Y'): string
    {
        if (!$date) return '—';
        try {
            $fmt = Setting::get('date_format', $fallback);
            if ($date instanceof \Carbon\Carbon || $date instanceof \DateTime) {
                return $date->format($fmt);
            }
            return \Carbon\Carbon::parse($date)->format($fmt);
        } catch (\Throwable) {
            return '—';
        }
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format a number as currency using admin settings.
     * Usage: format_currency(1500.50)  →  ₹1,500.50
     */
    function format_currency(float $amount): string
    {
        try {
            $symbol    = Setting::get('currency_symbol', '₹');
            $position  = Setting::get('currency_position', 'before');
            $precision = (int) Setting::get('decimal_precision', 2);
            $formatted = number_format($amount, $precision);
            return $position === 'before' ? $symbol . $formatted : $formatted . $symbol;
        } catch (\Throwable) {
            return '₹' . number_format($amount, 2);
        }
    }
}

if (!function_exists('amount_to_words_inr')) {
    /**
     * Convert an amount to Indian Rupees in words without external dependencies.
     */
    function amount_to_words_inr(float $amount): string
    {
        $amount = round($amount, 2);
        $decimal = (int) round(($amount - floor($amount)) * 100);
        $number = (int) floor($amount);

        $words = [
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen',
            20 => 'Twenty', 30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty',
            60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
        ];

        $convertTwoDigits = function (int $n) use ($words): string {
            if ($n < 20) {
                return $words[$n] ?? '';
            }
            $tens = (int) ($n / 10) * 10;
            $units = $n % 10;
            return trim(($words[$tens] ?? '') . ' ' . ($words[$units] ?? ''));
        };

        if ($number === 0) {
            $result = 'Zero';
        } else {
            $units = [
                10000000 => 'Crore',
                100000   => 'Lakh',
                1000     => 'Thousand',
                100      => 'Hundred'
            ];

            $result = '';
            foreach ($units as $unitVal => $unitWord) {
                if ($number >= $unitVal) {
                    $quotient = (int) ($number / $unitVal);
                    $number %= $unitVal;
                    if ($quotient >= 100) {
                        $h = (int) ($quotient / 100);
                        $rem = $quotient % 100;
                        $result .= ' ' . $words[$h] . ' Hundred ' . $convertTwoDigits($rem) . ' ' . $unitWord;
                    } else {
                        $result .= ' ' . $convertTwoDigits($quotient) . ' ' . $unitWord;
                    }
                }
            }

            if ($number > 0) {
                $result .= ' ' . $convertTwoDigits($number);
            }
        }

        $result = trim($result) . ' Rupees';
        if ($decimal > 0) {
            $result .= ' and ' . $convertTwoDigits($decimal) . ' Paise';
        }

        return $result . ' Only';
    }
}

