<?php

return [
    'provider' => env('SMS_PROVIDER', 'twilio'),
    'username' => env('SMS_USERNAME'),
    'api_key' => env('SMS_API_KEY'),
    'sender_id' => env('SMS_SENDER_ID', 'MLC_CLASS'),
    'sandbox' => env('SMS_SANDBOX', false),
    
    'rate_limits' => [
        'daily' => env('SMS_DAILY_LIMIT', 1000),
        'monthly' => env('SMS_MONTHLY_LIMIT', 10000),
    ],
    
    'alerts' => [
        'low_balance_threshold' => env('SMS_LOW_BALANCE_THRESHOLD', 1000),
    ],
];