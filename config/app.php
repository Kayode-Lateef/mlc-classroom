<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),
    'logo' => env('APP_LOGO', 'logos/logo-mlc.png'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | the application so that it's available within Artisan commands.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. The timezone
    | is set to "UTC" by default as it is suitable for most use cases.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by Laravel's translation / localization methods. This option can be
    | set to any locale for which you plan to have translation strings.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],


      /*
    |--------------------------------------------------------------------------
    | Application Pagination Settings
    |--------------------------------------------------------------------------
    |
    | Configure default pagination limits for different modules
    |
    */
    'pagination' => [
        'users' => env('PAGINATION_USERS', 10),
        'students' => env('PAGINATION_STUDENTS', 20),
        'teachers' => env('PAGINATION_TEACHERS', 20),
        'parents' => env('PAGINATION_PARENTS', 20),
        'classes' => env('PAGINATION_CLASSES', 20),
        'attendance' => env('PAGINATION_ATTENDANCE', 50),
        'homework' => env('PAGINATION_HOMEWORK', 20),
        'resources' => env('PAGINATION_RESOURCES', 24),
        'activity_logs' => env('PAGINATION_LOGS', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Attendance History Settings
    |--------------------------------------------------------------------------
    |
    | Configure how many days of attendance history to display
    |
    */
    
    'attendance_history_limit' => env('ATTENDANCE_HISTORY_LIMIT', 30),

    /*
    |--------------------------------------------------------------------------
    | Student Settings
    |--------------------------------------------------------------------------
    |
    | Configure student-specific settings
    |
    */
    'student' => [
        // Age restrictions (in years)
        'min_age' => env('STUDENT_MIN_AGE', 4),
        'max_age' => env('STUDENT_MAX_AGE', 18),
        
        // Profile photo settings
        'photo_max_size' => env('STUDENT_PHOTO_MAX_SIZE', 2048), // KB
        'photo_allowed_types' => ['jpeg', 'png', 'jpg', 'gif'],
        
        // History limits
        'attendance_history_limit' => env('STUDENT_ATTENDANCE_HISTORY', 30),
        'homework_history_limit' => env('STUDENT_HOMEWORK_HISTORY', 20),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Settings
    |--------------------------------------------------------------------------
    |
    | Configure file upload limits and allowed types
    |
    */
    'uploads' => [
        'profile_photos' => [
            'max_size' => 2048, // KB
            'allowed_types' => ['jpeg', 'png', 'jpg', 'gif'],
        ],
        'homework_submissions' => [
            'max_size' => 10240, // 10MB
            'allowed_types' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
        ],
        'learning_resources' => [
            'max_size' => 20480, // 20MB
            'allowed_types' => ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'mp4', 'zip'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure notification behavior
    |
    */
    'notifications' => [
        // Who gets notified for student enrollments
        'student_enrollment' => ['superadmin', 'parent'],
        
        // Who gets notified for status changes
        'student_status_change' => ['superadmin', 'parent'],
        
        // Who gets notified for deletions
        'student_deletion' => ['parent'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Activity Log Settings
    |--------------------------------------------------------------------------
    |
    | Configure activity logging
    |
    */
    'activity_log' => [
        'enabled' => env('ACTIVITY_LOG_ENABLED', true),
        'retention_days' => env('ACTIVITY_LOG_RETENTION_DAYS', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configure cache durations (in seconds)
    |
    */
    'cache' => [
        'statistics' => env('CACHE_STATISTICS_DURATION', 3600), // 1 hour
        'user_permissions' => env('CACHE_PERMISSIONS_DURATION', 86400), // 24 hours
        'class_schedules' => env('CACHE_SCHEDULES_DURATION', 3600), // 1 hour
    ],


];
