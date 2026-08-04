<?php

namespace App\Domain\Platform\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['key', 'value', 'type', 'group', 'label', 'description', 'is_public'])]
class ApplicationSetting extends Model
{
    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("app_setting:{$key}", function () use ($key, $default) {
            $setting = static::query()->where('key', $key)->first();

            if (! $setting) {
                return $default;
            }

            return match ($setting->type) {
                'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'number' => is_numeric($setting->value) ? $setting->value + 0 : $default,
                'json' => json_decode((string) $setting->value, true),
                default => $setting->value,
            };
        });
    }

    public static function set(string $key, mixed $value, array $attributes = []): self
    {
        $setting = static::query()->updateOrCreate(
            ['key' => $key],
            array_merge($attributes, [
                'value' => is_array($value) ? json_encode($value) : $value,
            ]),
        );

        Cache::forget("app_setting:{$key}");

        return $setting;
    }
}
