<?php

namespace App\Services;

use App\Models\User;
use App\Models\NotificationSetting;
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
     * Send notification via multiple channels based on user preferences
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
        // Ensure $users is an array
        if (!is_array($users)) {
            $users = [$users];
        }
        
        $results = [
            'email_sent' => 0,
            'email_queued' => 0,
            'sms_sent' => 0,
            'sms_queued' => 0,
            'in_app_created' => 0,
        ];
        
        foreach ($users as $user) {
            // Get user notification preferences
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
            
            // In-app notification (always create if enabled)
            if ($settings->in_app_enabled) {
                $this->createInAppNotification($user, $type, $title, $message, $data);
                $results['in_app_created']++;
            }
            
            // Email notification
            if ($settings->email_enabled && $user->email) {
                if ($immediate) {
                    $this->sendEmail($user->email, $title, $message, $data);
                    $results['email_sent']++;
                } else {
                    $this->queueEmail($user->id, $user->email, $title, $message, $data);
                    $results['email_queued']++;
                }
            }
            
            // SMS notification
            if ($settings->sms_enabled && $user->phone) {
                if ($immediate) {
                    $result = $this->smsService->sendImmediate($user->phone, $message, $type);
                    if ($result['success']) {
                        $results['sms_sent']++;
                    }
                } else {
                    $this->smsService->queueSms($user->id, $user->phone, $message, $type);
                    $results['sms_queued']++;
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Send student absence notification
     * 
     * @param object $attendance Attendance record
     * @return array
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
     * 
     * @param object $homework Homework assignment
     * @param array $students Array of students
     * @return array
     */
    public function sendHomeworkAssignedNotification($homework, $students)
    {
        $parents = [];
        foreach ($students as $student) {
            if (!in_array($student->parent_id, array_column($parents, 'id'))) {
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
     * 
     * @param object $submission Homework submission
     * @return array
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
     * 
     * @param object $progressSheet Progress sheet
     * @param array $students Array of students
     * @return array
     */
    public function sendProgressReportNotification($progressSheet, $students)
    {
        $parents = [];
        foreach ($students as $student) {
            if (!in_array($student->parent_id, array_column($parents, 'id'))) {
                $parents[] = $student->parent;
            }
        }
        
        $title = 'New Progress Report';
        $message = "New progress report available for {$progressSheet->class->name} - " .
                   $progressSheet->topic;
        
        $data = [
            'progress_sheet_id' => $progressSheet->id,
            'class_name' => $progressSheet->class->name,
            'topic' => $progressSheet->topic,
            'date' => $progressSheet->date->format('Y-m-d'),
            'url' => route('parent.progress.index'),
        ];
        
        // Queue for cron (non-urgent)
        return $this->send($parents, 'progress_report', $title, $message, $data, false);
    }
    
    /**
     * Send schedule change notification
     * 
     * @param object $schedule Schedule
     * @param string $changeType Type of change (created, updated, deleted)
     * @return array
     */
    public function sendScheduleChangeNotification($schedule, $changeType)
    {
        $class = $schedule->class;
        $students = $class->students;
        $parents = [];
        
        foreach ($students as $student) {
            if (!in_array($student->parent_id, array_column($parents, 'id'))) {
                $parents[] = $student->parent;
            }
        }
        
        $title = 'Schedule Change';
        $message = "Schedule change for {$class->name}: {$changeType}. " .
                   "{$schedule->day_of_week} at " . 
                   \Carbon\Carbon::parse($schedule->start_time)->format('H:i');
        
        $data = [
            'class_name' => $class->name,
            'day' => $schedule->day_of_week,
            'time' => $schedule->start_time,
            'change_type' => $changeType,
        ];
        
        // Send immediately for schedule changes
        return $this->send($parents, 'schedule_change', $title, $message, $data, true);
    }
    
    /**
     * Send emergency notification
     * 
     * @param string $message Emergency message
     * @param array $recipients Array of users or 'all'
     * @return array
     */
    public function sendEmergencyNotification($message, $recipients = 'all')
    {
        if ($recipients === 'all') {
            $recipients = User::where('role', 'parent')->get()->toArray();
        }
        
        $title = 'Emergency Alert';
        $data = [
            'priority' => 'high',
            'type' => 'emergency',
        ];
        
        // Send immediately for emergencies
        return $this->send($recipients, 'emergency', $title, $message, $data, true);
    }
    
    /**
     * Create in-app notification
     * 
     * @param User $user
     * @param string $type
     * @param string $title
     * @param string $message
     * @param array $data
     * @return void
     */
    protected function createInAppNotification($user, $type, $title, $message, $data)
    {
        $user->notify(new \App\Notifications\GeneralNotification([
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]));
    }
    
    /**
     * Send email immediately
     * 
     * @param string $to
     * @param string $subject
     * @param string $message
     * @param array $data
     * @return void
     */
    protected function sendEmail($to, $subject, $message, $data = [])
    {
        try {
            Mail::send('emails.notification', [
                'subject' => $subject,
                'message' => $message,
                'data' => $data,
            ], function ($mail) use ($to, $subject) {
                $mail->to($to)
                     ->subject($subject);
            });
            
            Log::info('Email sent to ' . $to);
            
        } catch (\Exception $e) {
            Log::error('Email send failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Queue email for cron processing
     * 
     * @param int $userId
     * @param string $email
     * @param string $subject
     * @param string $body
     * @param array $data
     * @return PendingEmail
     */
    protected function queueEmail($userId, $email, $subject, $body, $data = [])
    {
        return PendingEmail::create([
            'user_id' => $userId,
            'email' => $email,
            'subject' => $subject,
            'body' => $body,
            'status' => 'pending',
            'scheduled_at' => now()->addMinutes(5),
            'attempts' => 0,
        ]);
    }
    
    /**
     * Process pending emails (called by cron command)
     * 
     * @param int $limit
     * @return array
     */
    public function processPendingEmails($limit = 5)
    {
        $pending = PendingEmail::where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->where('attempts', '<', 3)
            ->limit($limit)
            ->get();
        
        $processed = 0;
        $sent = 0;
        $failed = 0;
        
        foreach ($pending as $email) {
            try {
                $this->sendEmail($email->email, $email->subject, $email->body);
                
                $email->update(['status' => 'sent']);
                $sent++;
                
            } catch (\Exception $e) {
                $email->increment('attempts');
                
                if ($email->attempts >= 3) {
                    $email->update(['status' => 'failed']);
                    $failed++;
                }
                
                Log::error('Email processing failed: ' . $e->getMessage());
            }
            
            $processed++;
        }
        
        return [
            'processed' => $processed,
            'sent' => $sent,
            'failed' => $failed,
        ];
    }


    // Example of a Voodoo-specific SMS sending method if you want to use Voodoo features directly
    protected function sendVoodooSMS($phone, $message, $type, $options = [])
    {
        // If you want to use Voodoo-specific features like flash SMS for emergencies:
        $smsOptions = [];
        
        if ($type === 'emergency') {
            $smsOptions['flash'] = 1; // Flash SMS for emergencies
        }
        
        return $this->smsService->sendImmediate($phone, $message, $type);
    }
}