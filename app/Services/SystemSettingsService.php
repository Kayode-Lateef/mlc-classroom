<?php

namespace App\Services;

use App\Models\SystemSetting;
use App\Models\ActivityLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SystemSettingsService
{
    /**
     * Update all system settings within a transaction
     * 
     * @param array $data Validated form data (excluding _token, school_logo)
     * @param UploadedFile|null $logo Optional logo file
     * @param int $userId The authenticated user's ID
     * @param string $ipAddress Request IP
     * @param string $userAgent Request user agent
     * @return array ['success' => bool, 'message' => string, 'changes' => array]
     */
    public function updateAll(array $data, ?UploadedFile $logo, int $userId, string $ipAddress, string $userAgent): array
    {
        $updatedSettings = [];

        try {
            DB::beginTransaction();

            // Normalise checkbox fields
            $data = $this->normaliseCheckboxes($data);

            // Update each setting
            foreach ($data as $key => $value) {
                // Skip non-setting fields
                if (in_array($key, ['_token', 'school_logo'])) {
                    continue;
                }

                $change = $this->updateSetting($key, $value);
                if ($change) {
                    $updatedSettings[] = $change;
                }
            }

            // Handle logo upload
            if ($logo) {
                $logoChange = $this->handleLogoUpload($logo);
                if ($logoChange) {
                    $updatedSettings[] = $logoChange;
                }
            }

            DB::commit();

            // Clear settings cache AFTER successful commit
            SystemSetting::clearCache();

            // Log activity
            $this->logActivity($updatedSettings, $userId, $ipAddress, $userAgent);

            return [
                'success' => true,
                'message' => 'System settings updated successfully!',
                'changes' => $updatedSettings,
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Settings update failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return [
                'success' => false,
                'message' => 'Failed to update settings. Please try again or contact support.',
                'changes' => [],
            ];
        }
    }

    /**
     * Update a single setting
     * 
     * @param string $key
     * @param mixed $value
     * @return array|null Change record or null if no change
     */
    protected function updateSetting(string $key, $value): ?array
    {
        $setting = SystemSetting::where('key', $key)->first();

        if ($setting) {
            // Read the raw database value for comparison
            $oldValue = $setting->getAttributes()['value'] ?? null;

            // Convert value based on stored type
            $typedValue = $this->convertValueByType($value, $setting->getAttributes()['type'] ?? 'string');

            // Only update if value actually changed
            if ((string) $oldValue !== (string) $typedValue) {
                $setting->update(['value' => $typedValue]);

                return [
                    'key' => $key,
                    'old' => $oldValue,
                    'new' => $typedValue,
                ];
            }

            return null; // No change
        }

        // Create new setting if it doesn't exist
        SystemSetting::create([
            'key'   => $key,
            'value' => (string) $value,
            'type'  => $this->inferType($value),
        ]);

        return [
            'key' => $key,
            'old' => null,
            'new' => (string) $value,
        ];
    }

    /**
     * Handle school logo upload
     * 
     * @param UploadedFile $file
     * @return array|null Change record
     */
    protected function handleLogoUpload(UploadedFile $file): ?array
    {
        // Validate file size (belt and braces — Form Request also validates)
        if ($file->getSize() > 2097152) {
            throw new \RuntimeException('School logo must not exceed 2MB.');
        }

        // Delete old logo if exists
        $oldLogo = SystemSetting::where('key', 'school_logo')->first();
        $oldPath = $oldLogo ? ($oldLogo->getAttributes()['value'] ?? null) : null;

        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        // Store new logo
        $logoPath = $file->store('logos', 'public');

        SystemSetting::updateOrCreate(
            ['key' => 'school_logo'],
            ['value' => $logoPath, 'type' => 'string']
        );

        return [
            'key' => 'school_logo',
            'old' => $oldPath,
            'new' => $logoPath,
        ];
    }

    /**
     * Normalise checkbox fields — unchecked checkboxes don't appear in POST data
     * 
     * @param array $data
     * @return array
     */
    protected function normaliseCheckboxes(array $data): array
    {
        foreach (SystemSetting::CHECKBOX_FIELDS as $field) {
            $data[$field] = isset($data[$field]) && $data[$field] ? 1 : 0;
        }

        return $data;
    }

    /**
     * Convert value based on stored type
     * 
     * @param mixed $value
     * @param string $type
     * @return string
     */
    protected function convertValueByType($value, string $type): string
    {
        switch ($type) {
            case 'boolean':
                return $value ? 'true' : 'false';
            case 'integer':
                return (string) intval($value);
            case 'json':
                return is_array($value) ? json_encode($value) : (string) $value;
            default:
                return (string) $value;
        }
    }

    /**
     * Infer type from value (for new settings only)
     * 
     * @param mixed $value
     * @return string
     */
    protected function inferType($value): string
    {
        if (is_bool($value) || in_array($value, ['true', 'false'], true)) {
            return 'boolean';
        }
        if (is_numeric($value) && !str_contains((string) $value, '.')) {
            return 'integer';
        }
        if (is_array($value)) {
            return 'json';
        }
        return 'string';
    }

    /**
     * Log settings update activity
     * 
     * @param array $changes
     * @param int $userId
     * @param string $ipAddress
     * @param string $userAgent
     */
    protected function logActivity(array $changes, int $userId, string $ipAddress, string $userAgent): void
    {
        if (empty($changes)) {
            return;
        }

        $description = 'Updated ' . count($changes) . ' system setting(s)';

        // Special note for hourly rate changes
        $hourlyRateChange = collect($changes)->firstWhere('key', 'hourly_rate');
        if ($hourlyRateChange) {
            $description .= sprintf(
                ' (Hourly rate changed from £%s to £%s)',
                number_format((float) ($hourlyRateChange['old'] ?? 0), 2),
                number_format((float) $hourlyRateChange['new'], 2)
            );
        }

        // Special note for maintenance mode changes
        $maintenanceChange = collect($changes)->firstWhere('key', 'maintenance_mode');
        if ($maintenanceChange) {
            $status = filter_var($maintenanceChange['new'], FILTER_VALIDATE_BOOLEAN) ? 'ENABLED' : 'DISABLED';
            $description .= " (Maintenance mode {$status})";
        }

        ActivityLog::create([
            'user_id'    => $userId,
            'action'     => 'updated_system_settings',
            'model_type' => 'SystemSetting',
            'model_id'   => null,
            'description' => $description,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }
}