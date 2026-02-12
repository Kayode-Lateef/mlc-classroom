<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSystemSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isSuperAdmin();
    }

    /**
     * Normalise checkbox fields before validation.
     * HTML forms don't submit unchecked checkboxes, so we
     * inject 0 for any missing checkbox field.
     */
    protected function prepareForValidation(): void
    {
        $checkboxes = [
            'email_enabled',
            'sms_enabled',
            'attendance_required',
            'late_homework_penalty',
            'maintenance_mode',
        ];

        foreach ($checkboxes as $field) {
            if (!$this->has($field)) {
                $this->merge([$field => 0]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // School Information
            'school_name'              => 'required|string|max:255',
            'school_email'             => 'required|email|max:255',
            'school_phone'             => [
                'required', 'string', 'min:10', 'max:20',
                'regex:/^(\+44\s?|0)[0-9\s\-\(\)]{9,}$/',
            ],
            'school_address'           => 'required|string|max:500',
            'school_logo'              => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

            // System Settings
            'max_class_capacity'       => 'required|integer|min:1|max:100',
            'term_start_date'          => 'required|date',
            'term_end_date'            => 'required|date|after:term_start_date',
            'timezone'                 => 'nullable|string|max:100',
            'date_format'              => 'nullable|string|max:50',
            'time_format'              => 'nullable|string|max:50',

            // Notification Settings
            'email_enabled'            => 'nullable|boolean',
            'sms_enabled'              => 'nullable|boolean',
            'admin_notification_email'  => 'required|email|max:255',

            // Academic Settings
            'hourly_rate'              => 'required|numeric|min:0|max:1000',
            'attendance_required'      => 'nullable|boolean',
            'late_homework_penalty'    => 'nullable|boolean',
            'homework_due_days'        => 'nullable|integer|min:1|max:30',
            'progress_report_frequency' => 'nullable|string|max:50',

            // Maintenance
            'maintenance_mode'         => 'nullable|boolean',
            'maintenance_message'      => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'school_name.required'              => 'School name is required.',
            'school_email.required'             => 'School email is required.',
            'school_email.email'                => 'Please provide a valid school email address.',
            'school_phone.required'             => 'School phone is required.',
            'school_phone.min'                  => 'Phone number must be at least 10 characters.',
            'school_phone.max'                  => 'Phone number must not exceed 20 characters.',
            'school_phone.regex'                => 'Please enter a valid UK phone number (e.g., +44 20 1234 5678 or 020 1234 5678).',
            'school_address.required'           => 'School address is required.',
            'school_logo.image'                 => 'School logo must be an image.',
            'school_logo.mimes'                 => 'School logo must be a file of type: jpeg, png, jpg, gif.',
            'school_logo.max'                   => 'School logo must not exceed 2MB.',
            'max_class_capacity.required'       => 'Maximum class capacity is required.',
            'max_class_capacity.integer'        => 'Class capacity must be a whole number.',
            'max_class_capacity.min'            => 'Class capacity must be at least 1.',
            'max_class_capacity.max'            => 'Class capacity cannot exceed 100.',
            'term_start_date.required'          => 'Term start date is required.',
            'term_end_date.required'            => 'Term end date is required.',
            'term_end_date.after'               => 'Term end date must be after the start date.',
            'admin_notification_email.required' => 'Admin notification email is required.',
            'admin_notification_email.email'    => 'Please provide a valid admin email address.',
            'hourly_rate.required'              => 'Hourly rate is required.',
            'hourly_rate.numeric'               => 'Hourly rate must be a number.',
            'hourly_rate.min'                   => 'Hourly rate must be at least £0.',
            'hourly_rate.max'                   => 'Hourly rate cannot exceed £1,000.',
            'homework_due_days.integer'         => 'Homework due days must be a whole number.',
            'homework_due_days.min'             => 'Homework due days must be at least 1.',
            'homework_due_days.max'             => 'Homework due days cannot exceed 30.',
            'maintenance_message.max'           => 'Maintenance message cannot exceed 500 characters.',
        ];
    }

    /**
     * Get the validated data excluding file uploads
     * (files are handled separately via $request->file())
     */
    public function settingsData(): array
    {
        return collect($this->validated())
            ->except(['school_logo'])
            ->toArray();
    }
}