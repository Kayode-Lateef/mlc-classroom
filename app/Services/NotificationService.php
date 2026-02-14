<?php

namespace App\Services;

use App\Models\User;
use App\Models\NotificationSetting;
use App\Models\SystemSetting;
use App\Models\PendingEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected $smsService;
    
    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }
    
    /**
     * Send notification via multiple channels based on user preferences.
     * 
     * WIRING: System-level gate checks email_enabled/sms_enabled from SystemSetting
     * BEFORE checking user-level NotificationSetting preferences.
     * If SuperAdmin disables email/SMS globally, no emails/SMS are sent for ANY user.
     * In-app notifications are NOT affected by these system toggles.
     * 
     * @param User|array $users User or array of users
     * @param string $type Notification type (absence, homework_assigned, etc.)
     * @param string $title Notification title
     * @param string $message Notification message
     * @param array $data Additional data for notification
     * @param bool $immediate Send immediately or queue
     * @return array Results of notification sending
     */
    public function send($users, $type, $title, $message, $data = [], $immediate = false)
    {
        if (!is_array($users)) {
            $users = [$users];
        }
        
        $results = [
            'email_sent' => 0,
            'email_queued' => 0,
            'email_skipped_system' => 0,
            'sms_sent' => 0,
            'sms_queued' => 0,
            'sms_skipped_system' => 0,
            'in_app_created' => 0,
        ];

        // =============================================================
        // SYSTEM-LEVEL GATE (wired to SuperAdmin → System Settings)
        // If the SuperAdmin has disabled email or SMS globally,
        // the channel is completely off regardless of user preferences.
        // =============================================================
        $systemEmailEnabled = SystemSetting::isEmailEnabled();
        $systemSmsEnabled   = SystemSetting::isSmsEnabled();

        if (!$systemEmailEnabled) {
            Log::info("NotificationService: Email globally disabled — skipping email channel for type '{$type}'");
        }
        if (!$systemSmsEnabled) {
            Log::info("NotificationService: SMS globally disabled — skipping SMS channel for type '{$type}'");
        }
        
        foreach ($users as $user) {
            // Skip null users (e.g. student with no parent linked)
            if (!$user) {
                continue;
            }

            // Get user-level notification preferences
            $settings = NotificationSetting::where('user_id', $user->id)
                ->where('notification_type', $type)
                ->first();
            
            // If no settings exist, create default (all enabled)
            if (!$settings) {
                $settings = NotificationSetting::create([
                    'user_id' => $user->id,
                    'notification_type' => $type,
                    'email_enabled' => true,
                    'sms_enabled' => true,
                    'in_app_enabled' => true,
                ]);
            }
            
            // In-app notification (NOT affected by email/SMS system toggles)
            if ($settings->in_app_enabled) {
                $this->createInAppNotification($user, $type, $title, $message, $data);
                $results['in_app_created']++;
            }
            
            // ==========================================================
            // EMAIL: System gate → then user preference → then send/queue
            // ==========================================================
            if ($systemEmailEnabled && $settings->email_enabled && $user->email) {
                if ($immediate) {
                    $this->sendEmail($user->email, $title, $message, $data);
                    $results['email_sent']++;
                } else {
                    $this->queueEmail($user->id, $user->email, $title, $message, $data);
                    $results['email_queued']++;
                }
            } elseif (!$systemEmailEnabled && $settings->email_enabled) {
                // User wants email but system has it disabled
                $results['email_skipped_system']++;
            }
            
            // ==========================================================
            // SMS: System gate → then user preference → then send/queue
            // ==========================================================
            if ($systemSmsEnabled && $settings->sms_enabled && $user->phone) {
                if ($immediate) {
                    $result = $this->smsService->sendImmediate($user->phone, $message, $type);
                    if ($result['success']) {
                        $results['sms_sent']++;
                    }
                } else {
                    $this->smsService->queueSms($user->id, $user->phone, $message, $type);
                    $results['sms_queued']++;
                }
            } elseif (!$systemSmsEnabled && $settings->sms_enabled) {
                // User wants SMS but system has it disabled
                $results['sms_skipped_system']++;
            }
        }
        
        return $results;
    }
    
    /**
     * Send student absence notification
     */
    public function sendAbsenceNotification($attendance)
    {
        $student = $attendance->student;
        $parent = $student->parent;
        $class = $attendance->class;
        
        $title = 'Student Absence Alert';
        $message = "{$student->full_name} was marked absent from {$class->name} on " . 
                   $attendance->date->format('d/m/Y') . '. Please contact us if this is unexpected.';
        
        $data = [
            'student_id' => $student->id,
            'student_name' => $student->full_name,
            'class_name' => $class->name,
            'date' => $attendance->date->format('Y-m-d'),
            'url' => route('parent.attendance.index'),
        ];
        
        // Send immediately for absences (critical)
        return $this->send($parent, 'absence', $title, $message, $data, true);
    }
    
    /**
     * Send homework assigned notification
     */
    public function sendHomeworkAssignedNotification($homework, $students)
    {
        $parents = [];
        foreach ($students as $student) {
            if ($student->parent && !in_array($student->parent_id, array_column($parents, 'id'))) {
                $parents[] = $student->parent;
            }
        }
        
        $title = 'New Homework Assignment';
        $message = "New homework assigned for {$homework->class->name}: {$homework->title}. " .
                   "Due date: " . $homework->due_date->format('d/m/Y');
        
        $data = [
            'homework_id' => $homework->id,
            'homework_title' => $homework->title,
            'class_name' => $homework->class->name,
            'due_date' => $homework->due_date->format('Y-m-d'),
            'url' => route('parent.homework.show', $homework),
        ];
        
        // Queue for cron (non-urgent)
        return $this->send($parents, 'homework_assigned', $title, $message, $data, false);
    }
    
    /**
     * Send homework graded notification
     */
    public function sendHomeworkGradedNotification($submission)
    {
        $student = $submission->student;
        $parent = $student->parent;
        $homework = $submission->homeworkAssignment;
        
        $title = 'Homework Graded';
        $message = "Homework for {$homework->class->name} has been graded. " .
                   "Grade: {$submission->grade}";
        
        $data = [
            'homework_id' => $homework->id,
            'homework_title' => $homework->title,
            'grade' => $submission->grade,
            'url' => route('parent.homework.show', $homework),
        ];
        
        // Queue for cron (non-urgent)
        return $this->send($parent, 'homework_graded', $title, $message, $data, false);
    }
    
    /**
     * Send progress report notification
     */
    public function sendProgressReportNotification($progressSheet, $students)
    {
        $parents = [];
        foreach ($students as $student) {
            if ($student->parent && !in_array($student->parent_id, array_column($parents, 'id'))) {
                $parents[] = $student->parent;
            }
        }
        
        $title = 'New Progress Report';
        $message = "New progress report available for {$progressSheet->class->name} - " .
                   "{$progressSheet->topic}";
        
        $data = [
            'progress_sheet_id' => $progressSheet->id,
            'class_name' => $progressSheet->class->name,
            'topic' => $progressSheet->topic,
            'url' => route('parent.progress.show', $progressSheet),
        ];
        
        // Queue for cron (non-urgent)
        return $this->send($parents, 'progress_report', $title, $message, $data, false);
    }
    
    /**
     * Create in-app notification using Laravel's notification system
     */
    protected function createInAppNotification($user, $type, $title, $message, $data = [])
    {
        try {
            $user->notify(new \App\Notifications\GeneralNotification([
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
            ]));
        } catch (\Exception $e) {
            Log::error("Failed to create in-app notification: " . $e->getMessage());
        }
    }
    
    /**
     * Send email immediately
     */
    protected function sendEmail($email, $title, $message, $data = [])
    {
        try {
            Mail::send('emails.general-notification', [
            'title' => $title,
            'content' => $message,      // ✅ M-4: Use canonical variable name
            'messageContent' => $message, // ✅ Backward compat until all templates updated
            'data' => $data,
            ], function ($mail) use ($email, $title) {
                $mail->to($email)
                     ->subject($title);
            });
        } catch (\Exception $e) {
            Log::error("Failed to send email to {$email}: " . $e->getMessage());
        }
    }
    
    /**
     * Queue email for cron processing (shared hosting)
     */
    protected function queueEmail($userId, $email, $title, $message, $data = [])
    {
        try {
            PendingEmail::create([
                'user_id' => $userId,
                'to_email' => $email,
                'subject' => $title,
                'body' => $message,
                'data' => json_encode($data),
                'status' => 'pending',
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to queue email for {$email}: " . $e->getMessage());
        }
    }
}