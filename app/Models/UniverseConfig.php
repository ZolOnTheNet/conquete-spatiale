<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UniverseConfig extends Model
{
    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    /**
     * Obtenir une configuration par clé
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $config = static::where('key', $key)->first();

        if (!$config) {
            return $default;
        }

        // Essayer de décoder JSON si applicable
        $decoded = json_decode($config->value, true);
        return $decoded !== null ? $decoded : $config->value;
    }

    /**
     * Définir une configuration
     */
    public static function set(string $key, mixed $value, ?string $description = null): void
    {
        $encodedValue = is_array($value) ? json_encode($value) : $value;

        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $encodedValue,
                'description' => $description,
            ]
        );
    }

    /**
     * Obtenir toutes les configurations
     */
    public static function all(): array
    {
        return static::query()
            ->get()
            ->mapWithKeys(function ($config) {
                $decoded = json_decode($config->value, true);
                return [$config->key => $decoded !== null ? $decoded : $config->value];
            })
            ->toArray();
    }
}
