<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\UpdateSystemSettingsRequest;
use App\Models\SystemSetting;
use App\Services\SystemSettingsService;

class SystemSettingsController extends Controller
{
    protected SystemSettingsService $settingsService;

    public function __construct(SystemSettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Display system settings
     */
    public function index()
    {
        // Build a flat key-value map with PROPER type casting for the view.
        //
        // CRITICAL FIX: Boolean settings are stored as the strings 'true'/'false' in the DB.
        // PHP treats the string 'false' as TRUTHY (non-empty string).
        // We must cast booleans using filter_var() so Blade's ternary works correctly:
        //   filter_var('false', FILTER_VALIDATE_BOOLEAN) → false  ✓
        //   filter_var('true',  FILTER_VALIDATE_BOOLEAN) → true   ✓
        //   filter_var('0',     FILTER_VALIDATE_BOOLEAN) → false  ✓
        //   filter_var('1',     FILTER_VALIDATE_BOOLEAN) → true   ✓
        $settingsMap = [];
        $allSettings = SystemSetting::all();

        foreach ($allSettings as $setting) {
            $rawValue = $setting->getAttributes()['value'] ?? null;
            $type = $setting->getAttributes()['type'] ?? 'string';

            switch ($type) {
                case 'boolean':
                    $settingsMap[$setting->key] = filter_var($rawValue, FILTER_VALIDATE_BOOLEAN);
                    break;
                case 'integer':
                    $settingsMap[$setting->key] = (int) $rawValue;
                    break;
                default:
                    $settingsMap[$setting->key] = $rawValue;
                    break;
            }
        }

        // Statistics — uses the already-cast boolean values from $settingsMap
        $stats = [
            'total_settings' => count($settingsMap),
            'last_updated'   => $allSettings->max('updated_at'),
            'sms_enabled'    => $settingsMap['sms_enabled'] ?? false,
            'email_enabled'  => $settingsMap['email_enabled'] ?? false,
        ];

        return view('superadmin.settings.index', compact('settingsMap', 'stats'));
    }

    /**
     * Update system settings
     */
    public function update(UpdateSystemSettingsRequest $request)
    {
        $result = $this->settingsService->updateAll(
            $request->settingsData(),
            $request->file('school_logo'),
            auth()->id(),
            $request->ip(),
            $request->userAgent()
        );

        if ($result['success']) {
            return redirect()->route('superadmin.settings.index')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->withInput()
            ->with('error', $result['message']);
    }
}