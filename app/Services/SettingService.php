
<?php

namespace App\Services;

use App\Models\Setting;

/**
 * SettingService - Wrapper service for Setting model
 *
 * This service provides a clean interface for managing application settings
 * with caching support and group management.
 */
class SettingService
{
    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return Setting::get($key, $default);
    }

    /**
     * Set a setting value.
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param string|null $group
     * @param string|null $description
     * @return bool
     */
    public function set(string $key, $value, string $type = 'string', ?string $group = null, ?string $description = null): bool
    {
        $group = $group ?? 'general';

        $setting = Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
                'description' => $description,
            ]
        );

        // Clear cache for this key
        \Cache::forget("setting_{$key}");
        \Cache::forget("settings_group_{$group}");

        \Log::info("Setting updated: {$key}", [
            'value' => $value,
            'type' => $type,
            'group' => $group,
        ]);

        return $setting !== null;
    }

    /**
     * Get all settings by group.
     *
     * @param string $group
     * @return array
     */
    public function getByGroup(string $group): array
    {
        return Setting::getByGroup($group);
    }

    /**
     * Update multiple settings in a group.
     *
     * @param string $group
     * @param array $settings Array of ['key' => 'value'] pairs
     * @return bool
     */
    public function updateGroup(string $group, array $settings): bool
    {
        try {
            foreach ($settings as $key => $value) {
                // Get existing setting to determine type
                $existing = Setting::where('key', $key)->first();
                $type = $existing ? $existing->type : 'string';

                Setting::updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => $value,
                        'type' => $type,
                        'group' => $group,
                    ]
                );
            }

            // Clear cache for this group
            \Cache::forget("settings_group_{$group}");

            // Clear individual key caches
            foreach ($settings as $key => $value) {
                \Cache::forget("setting_{$key}");
            }

            \Log::info("Settings group updated: {$group}", [
                'count' => count($settings),
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to update settings group: {$group}", [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Clear settings cache.
     *
     * @return void
     */
    public function clearCache(): void
    {
        Setting::clearCache();
        \Log::info("Settings cache cleared");
    }

    /**
     * Get all settings grouped by their group name.
     *
     * @return array
     */
    public function getAllGrouped(): array
    {
        $settings = Setting::all();

        return $settings->groupBy('group')->map(function ($groupSettings) {
            return $groupSettings->mapWithKeys(function ($setting) {
                return [
                    $setting->key => [
                        'value' => $this->castValue($setting->value, $setting->type),
                        'type' => $setting->type,
                        'description' => $setting->description,
                    ]
                ];
            })->toArray();
        })->toArray();
    }

    /**
     * Get all settings as a flat array.
     *
     * @return array
     */
    public function getAll(): array
    {
        $settings = Setting::all();

        return $settings->mapWithKeys(function ($setting) {
            return [$setting->key => $this->castValue($setting->value, $setting->type)];
        })->toArray();
    }

    /**
     * Check if a setting exists.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return Setting::where('key', $key)->exists();
    }

    /**
     * Delete a setting.
     *
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        try {
            $setting = Setting::where('key', $key)->first();

            if ($setting) {
                $group = $setting->group;
                $setting->delete();

                // Clear cache
                \Cache::forget("setting_{$key}");
                \Cache::forget("settings_group_{$group}");

                \Log::info("Setting deleted: {$key}");

                return true;
            }

            return false;
        } catch (\Exception $e) {
            \Log::error("Failed to delete setting: {$key}", [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Set multiple settings at once.
     *
     * @param array $settings Array of ['key' => ['value' => ..., 'type' => ..., 'group' => ...]]
     * @return bool
     */
    public function setMultiple(array $settings): bool
    {
        try {
            foreach ($settings as $key => $data) {
                $value = $data['value'] ?? null;
                $type = $data['type'] ?? 'string';
                $group = $data['group'] ?? 'general';
                $description = $data['description'] ?? null;

                $this->set($key, $value, $type, $group, $description);
            }

            \Log::info("Multiple settings updated", ['count' => count($settings)]);

            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to update multiple settings", [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Cast value based on type (helper method).
     *
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    protected function castValue($value, string $type)
    {
        return match ($type) {
            'integer' => (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            'float', 'decimal' => (float) $value,
            default => $value,
        };
    }
}
