
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\UpdateSettingRequest;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    /**
     * Show general settings form.
     */
    public function general()
    {
        $settings = Setting::where('group', 'general')->get()->keyBy('key');

        return view('admin.settings.general', compact('settings'));
    }

    /**
     * Show loan settings form.
     */
    public function loans()
    {
        $settings = Setting::where('group', 'loan')->get()->keyBy('key');

        return view('admin.settings.loans', compact('settings'));
    }

    /**
     * Show reservation settings form.
     */
    public function reservations()
    {
        $settings = Setting::where('group', 'reservation')->get()->keyBy('key');

        return view('admin.settings.reservations', compact('settings'));
    }

    /**
     * Show fine settings form.
     */
    public function fines()
    {
        $settings = Setting::where('group', 'fine')->get()->keyBy('key');

        return view('admin.settings.fines', compact('settings'));
    }

    /**
     * Show notification settings form.
     */
    public function notifications()
    {
        $settings = Setting::where('group', 'notification')->get()->keyBy('key');

        return view('admin.settings.notifications', compact('settings'));
    }

    /**
     * Update settings by group.
     */
    public function update(Request $request, $group)
    {
        // Validate group
        $validGroups = ['general', 'loan', 'reservation', 'fine', 'notification'];
        if (!in_array($group, $validGroups)) {
            return redirect()
                ->back()
                ->with('error', 'Invalid settings group.');
        }

        // Define validation rules based on group
        $rules = $this->getValidationRules($group);

        $validated = $request->validate($rules);

        try {
            // Update each setting
            foreach ($validated as $key => $value) {
                $setting = Setting::where('key', $key)->first();

                if ($setting) {
                    // Update existing setting
                    $setting->update(['value' => $value]);
                } else {
                    // Create new setting if it doesn't exist
                    Setting::create([
                        'key' => $key,
                        'value' => $value,
                        'group' => $group,
                        'type' => $this->determineType($value),
                    ]);
                }
            }

            // Clear settings cache
            Setting::clearCache();

            // Log activity
            \App\Models\ActivityLog::log(
                'settings_updated',
                "Settings updated for group: {$group}"
            );

            return redirect()
                ->back()
                ->with('success', 'Settings updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    /**
     * Get validation rules based on settings group.
     */
    protected function getValidationRules(string $group): array
    {
        return match ($group) {
            'general' => [
                'app_name' => ['nullable', 'string', 'max:255'],
                'app_description' => ['nullable', 'string', 'max:500'],
                'contact_email' => ['nullable', 'email', 'max:255'],
                'contact_phone' => ['nullable', 'string', 'max:20'],
                'address' => ['nullable', 'string', 'max:500'],
                'timezone' => ['nullable', 'string', 'max:50'],
            ],
            'loan' => [
                'loan_duration_days' => ['required', 'integer', 'min:1', 'max:365'],
                'max_loans_per_member' => ['required', 'integer', 'min:1', 'max:50'],
                'allow_loan_extension' => ['required', 'boolean'],
                'extension_duration_days' => ['nullable', 'integer', 'min:1', 'max:30'],
                'max_extensions_allowed' => ['nullable', 'integer', 'min:0', 'max:5'],
            ],
            'reservation' => [
                'reservation_duration_days' => ['required', 'integer', 'min:1', 'max:30'],
                'max_reservations_per_member' => ['required', 'integer', 'min:1', 'max:20'],
                'auto_cancel_expired_reservations' => ['required', 'boolean'],
                'reservation_pickup_days' => ['nullable', 'integer', 'min:1', 'max:7'],
            ],
            'fine' => [
                'fine_rate_per_day' => ['required', 'numeric', 'min:0', 'max:100000'],
                'fine_grace_period_days' => ['nullable', 'integer', 'min:0', 'max:30'],
                'max_fine_amount' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
                'allow_fine_waiver' => ['required', 'boolean'],
            ],
            'notification' => [
                'enable_email_notifications' => ['required', 'boolean'],
                'enable_sms_notifications' => ['required', 'boolean'],
                'notify_loan_due_days_before' => ['nullable', 'integer', 'min:0', 'max:30'],
                'notify_reservation_ready' => ['required', 'boolean'],
                'notify_overdue_loans' => ['required', 'boolean'],
            ],
            default => [],
        };
    }

    /**
     * Determine the type of a value.
     */
    protected function determineType($value): string
    {
        if (is_bool($value)) {
            return 'boolean';
        }

        if (is_int($value)) {
            return 'integer';
        }

        if (is_float($value)) {
            return 'float';
        }

        if (is_array($value)) {
            return 'json';
        }

        return 'string';
    }
}
