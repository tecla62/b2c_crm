<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group', 'label'];

    // ── Cache key ──────────────────────────────────────────────────────────
    const CACHE_KEY = 'crm_settings_all';

    // ── Get a single setting value ─────────────────────────────────────────
    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = static::allCached();
        return $settings[$key] ?? $default;
    }

    // ── Set / upsert a single setting ──────────────────────────────────────
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        static::clearCache();
    }

    // ── Get all settings as key=>value array (cached) ─────────────────────
    public static function allCached(): array
    {
        return Cache::rememberForever(static::CACHE_KEY, function () {
            return static::pluck('value', 'key')->toArray();
        });
    }

    // ── Get all settings for a group ──────────────────────────────────────
    public static function group(string $group): array
    {
        return static::where('group', $group)->pluck('value', 'key')->toArray();
    }

    // ── Clear settings cache ───────────────────────────────────────────────
    public static function clearCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }

    // ── Bulk upsert from array ─────────────────────────────────────────────
    public static function bulkSet(array $data): void
    {
        foreach ($data as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        static::clearCache();
    }
}
