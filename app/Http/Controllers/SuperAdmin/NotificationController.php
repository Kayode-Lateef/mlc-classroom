<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\SmsLog;
use App\Models\SmsConfiguration;
use App\Models\ActivityLog;
use App\Models\NotificationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Display notifications interface
     */
    public function index()
    {
        try {
            // Get classes for class selection
            $classes = ClassModel::with('teacher')->orderBy('name')->get();

            // Get recent notifications (last 50)
            $recentNotifications = DatabaseNotification::orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            // Statistics
            $stats = [
                'today_notifications' => DatabaseNotification::whereDate('created_at', today())->count(),
                'week_notifications' => DatabaseNotification::whereDate('created_at', '>=', now()->subWeek())->count(),
                'month_notifications' => DatabaseNotification::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                'unread_notifications' => DatabaseNotification::whereNull('read_at')->count(),
            ];

            return view('superadmin.notifications.index', compact('classes', 'recentNotifications', 'stats'));
            
        } catch (\Exception $e) {
            Log::error('Error loading notifications page: ' . $e->getMessage());
            
            return redirect()->route('superadmin.dashboard')
                ->with('error', 'Failed to load notifications page. Please try again.');
        }
    }

    /**
     * Send notification to selected recipients
     */
    public function send(Request $request)
    {
        // Validate input
        $rules = [
            'recipient_type' => 'required|in:all_parents,all_teachers,class,individual',
            'notification_type' => 'required|in:general,emergency,homework,progress_report,schedule_change,absence',
            'channels' => 'required|array|min:1',
            'channels.*' => 'in:email,sms,in_app',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
        ];

        // Only require class_id if recipient_type is 'class'
        if ($request->recipient_type === 'class') {
            $rules['class_id'] = 'required|exists:classes,id';
        }

        // Only require user_id if recipient_type is 'individual'
        if ($request->recipient_type === 'individual') {
            $rules['user_id'] = 'required|exists:users,id';
        }

        $validator = Validator::make($request->all(), $rules, [
            // Custom error messages
            'channels.required' => 'Please select at least one notification channel',
            'channels.min' => 'Please select at least one notification channel',
            'recipient_type.required' => 'Please select who to send the notification to',
            'class_id.required' => 'Please select a class when sending to specific class parents',
            'title.required' => 'Please enter a notification title',
            'message.required' => 'Please enter a notification message',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Get recipients based on type
            $recipients = $this->getRecipients($request->recipient_type, $request->class_id, $request->user_id);

            if ($recipients->isEmpty()) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'No recipients found for the selected criteria. Please check your selection and try again.');
            }

            $channels = $request->channels;
            $sentCount = 0;
            $failedCount = 0;

            // Prepare notification data
            $notificationData = [
                'type' => $request->notification_type,
                'title' => $request->title,
                'message' => $request->message,
                'sent_by' => auth()->user()->name,
                'sent_at' => now()->format('d M Y, H:i'),
            ];

            foreach ($recipients as $recipient) {
                try {
                    // Check user's notification preferences
                    $userPreferences = $this->getUserPreferences($recipient, $request->notification_type);

                    // Send Email
                    if (in_array('email', $channels) && $userPreferences['email_enabled']) {
                        $this->sendEmail($recipient, $notificationData);
                    }

                    // Send SMS
                    if (in_array('sms', $channels) && $userPreferences['sms_enabled']) {
                        $this->sendSms($recipient, $notificationData);
                    }

                    // Send In-App Notification
                    if (in_array('in_app', $channels) && $userPreferences['in_app_enabled']) {
                        $this->sendInAppNotification($recipient, $notificationData);
                    }

                    $sentCount++;

                } catch (\Exception $e) {
                    Log::error("Failed to send notification to user {$recipient->id}: " . $e->getMessage());
                    $failedCount++;
                }
            }

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'sent_notification',
                'model_type' => 'Notification',
                'model_id' => null,
                'description' => "Sent {$request->notification_type} notification to {$sentCount} recipients via " . implode(', ', $channels),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Build success message
            $message = "Notification sent successfully to {$sentCount} recipient(s)";
            if ($failedCount > 0) {
                $message .= ". Warning: {$failedCount} failed to send.";
            }

            return redirect()->route('superadmin.notifications.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Notification sending failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to send notifications. Error: ' . $e->getMessage());
        }
    }

    /**
     * Get recipients based on type
     */
    protected function getRecipients($type, $classId = null, $userId = null)
    {
        switch ($type) {
            case 'all_parents':
                return User::where('role', 'parent')
                    ->where('status', 'active')
                    ->get();

            case 'all_teachers':
                return User::where('role', 'teacher')
                    ->where('status', 'active')
                    ->get();

            case 'class':
                // Get all parents of students in the specified class
                return User::where('role', 'parent')
                    ->where('status', 'active')
                    ->whereHas('children', function($query) use ($classId) {
                        $query->whereHas('enrollments', function($q) use ($classId) {
                            $q->where('class_id', $classId)
                              ->where('status', 'active');
                        });
                    })
                    ->get();

            case 'individual':
                return User::where('id', $userId)
                    ->where('status', 'active')
                    ->get();

            default:
                return collect();
        }
    }

    /**
     * Get user's notification preferences
     */
    protected function getUserPreferences($user, $notificationType)
    {
        // Get user's specific preferences for this notification type
        $setting = NotificationSetting::where('user_id', $user->id)
            ->where('notification_type', $notificationType)
            ->first();

        // If no specific setting, return defaults (all enabled)
        if (!$setting) {
            return [
                'email_enabled' => true,
                'sms_enabled' => true,
                'in_app_enabled' => true,
            ];
        }

        return [
            'email_enabled' => $setting->email_enabled,
            'sms_enabled' => $setting->sms_enabled,
            'in_app_enabled' => $setting->in_app_enabled,
        ];
    }

    /**
     * Send email notification
     */
    protected function sendEmail($recipient, $data)
    {
        try {
            Mail::send('emails.general-notification', $data, function($message) use ($recipient, $data) {
                $message->to($recipient->email, $recipient->name)
                    ->subject($data['title']);
            });

            return true;

        } catch (\Exception $e) {
            Log::error("Email sending failed for user {$recipient->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send SMS notification
     */
    protected function sendSms($recipient, $data)
    {
        try {
            // Check if SMS is configured and active
            $config = SmsConfiguration::first();

            if (!$config || !$config->is_active) {
                Log::warning('SMS system not configured or inactive');
                return false;
            }

            // Check if user has phone number
            if (!$recipient->phone) {
                Log::warning("User {$recipient->id} has no phone number");
                return false;
            }

            // Prepare SMS message (max 160 characters for standard SMS)
            $smsMessage = $this->prepareSmsMessage($data);

            // Decrypt credentials
            $apiKey = Crypt::decryptString($config->api_key);
            $apiSecret = Crypt::decryptString($config->api_secret);

            // Send SMS via configured provider
            $result = $this->sendSmsViaProvider(
                $config->provider,
                $apiKey,
                $apiSecret,
                $config->sender_id,
                $recipient->phone,
                $smsMessage
            );

            // Log SMS
            SmsLog::create([
                'user_id' => $recipient->id,
                'phone_number' => $recipient->phone,
                'message_type' => $data['type'],
                'message_content' => $smsMessage,
                'provider' => $config->provider,
                'provider_message_id' => $result['message_id'] ?? null,
                'status' => $result['success'] ? 'sent' : 'failed',
                'failure_reason' => $result['error'] ?? null,
                'cost' => 0.04, // Estimated cost in GBP
                'sent_at' => now(),
            ]);

            // Deduct from balance if successful
            if ($result['success']) {
                $config->decrement('credit_balance', 0.04);
            }

            return $result['success'];

        } catch (\Exception $e) {
            Log::error("SMS sending failed for user {$recipient->id}: " . $e->getMessage());
            // Don't throw - allow other channels to continue
            return false;
        }
    }

    /**
     * Prepare SMS message (shorten for SMS limits)
     */
    protected function prepareSmsMessage($data)
    {
        // Format: [Title] Message (max 160 chars)
        $message = "[{$data['title']}] {$data['message']}";
        
        if (strlen($message) > 160) {
            $message = substr($message, 0, 157) . '...';
        }

        return $message;
    }

    /**
     * Send SMS via configured provider
     */
    protected function sendSmsViaProvider($provider, $apiKey, $apiSecret, $senderId, $to, $message)
    {
        try {
            switch ($provider) {
                case 'twilio':
                    return $this->sendViaTwilio($apiKey, $apiSecret, $senderId, $to, $message);
                    
                case 'vonage':
                    return $this->sendViaVonage($apiKey, $apiSecret, $senderId, $to, $message);
                    
                case 'messagebird':
                    return $this->sendViaMessageBird($apiKey, $senderId, $to, $message);
                    
                default:
                    return [
                        'success' => false,
                        'error' => 'Provider not implemented.',
                    ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send via Twilio
     */
    protected function sendViaTwilio($accountSid, $authToken, $from, $to, $message)
    {
        // Note: Requires Twilio SDK - composer require twilio/sdk
        // This is a placeholder implementation
        
        try {
            // Uncomment when Twilio SDK is installed:
            /*
            $client = new \Twilio\Rest\Client($accountSid, $authToken);
            
            $result = $client->messages->create($to, [
                'from' => $from,
                'body' => $message
            ]);
            
            return [
                'success' => true,
                'message_id' => $result->sid,
            ];
            */

            // Placeholder response for testing
            return [
                'success' => true,
                'message_id' => 'TEST_' . uniqid(),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send via Vonage (Nexmo)
     */
    protected function sendViaVonage($apiKey, $apiSecret, $from, $to, $message)
    {
        // Placeholder for Vonage implementation
        return [
            'success' => true,
            'message_id' => 'TEST_' . uniqid(),
        ];
    }

    /**
     * Send via MessageBird
     */
    protected function sendViaMessageBird($apiKey, $from, $to, $message)
    {
        // Placeholder for MessageBird implementation
        return [
            'success' => true,
            'message_id' => 'TEST_' . uniqid(),
        ];
    }

    /**
     * Send in-app notification
     */
    protected function sendInAppNotification($recipient, $data)
    {
        try {
            // Create database notification using Laravel's notification system
            $recipient->notify(new \App\Notifications\GeneralNotification($data));

            return true;

        } catch (\Exception $e) {
            Log::error("In-app notification failed for user {$recipient->id}: " . $e->getMessage());
            throw $e;
        }
    }
}