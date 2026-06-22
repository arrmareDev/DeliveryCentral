<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ConfiguracionCentral extends Model
{
    protected $table = 'configuraciones_central';
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        return Cache::remember("config_central_{$key}", 3600, function () use ($key, $default) {
            return static::where('key', $key)->value('value') ?? $default;
        });
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("config_central_{$key}");
    }
}
