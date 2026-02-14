<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\Student;
use App\Models\ClassModel;
use App\Models\PendingEmail;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\SystemSetting;

class NotificationHelper
{

    /**
     * Public entry point for sending a single notification email.
     * Used by NotificationControllers and any code that needs to send
     * an email through the unified pipeline with system gate checks
     * and template selection.
     *
     * @param User $user Recipient
     * @param string $title Email subject
     * @param string $messageText Email body text
     * @param array $data Additional data (must include 'type' for template selection)
     * @return bool Whether email was sent
     */
    public static function sendNotificationEmail($user, $title, $messageText, $data = [])
    {
        return self::sendEmailImmediate($user, $title, $messageText, $data);
    }

    /**
     * ✅ SEND EMAIL IMMEDIATELY (for critical notifications, 1-3 recipients)
     */
    private static function sendEmailImmediate($user, $title, $messageText, $data = [])
    {
        try {
            // System-level gate: respect global email toggle from SuperAdmin Settings
            if (!SystemSetting::isEmailEnabled()) {
                Log::info("NotificationHelper: Email globally disabled — skipping immediate email to {$user->email}");
                return false;
            }

            if (!$user->email) {
                Log::warning("User {$user->id} has no email address");
                return false;
            }

            // ✅ Select template based on type
            $template = self::selectEmailTemplate($data['type'] ?? 'general', $data);
            
            Mail::send($template, array_merge([
                'title' => $title,
                'content' => $messageText,
                'url' => $data['url'] ?? null,
                'type' => $data['type'] ?? 'general',
            ], $data), function ($mail) use ($user, $title) {
                $mail->to($user->email, $user->name)
                    ->subject($title);
            });
            
            Log::info("Email sent immediately to: {$user->email}");

            // ✅ M-3: Log email delivery
            \App\Models\EmailLog::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'subject' => $title,
                'template' => $template,
                'type' => $data['type'] ?? 'general',
                'method' => 'immediate',
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send email to {$user->email}: " . $e->getMessage());
            // ✅ M-3: Log email failure
            \App\Models\EmailLog::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'subject' => $title,
                'template' => $template ?? 'emails.notification',
                'type' => $data['type'] ?? 'general',
                'method' => 'immediate',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    // ✅ NEW: Template selector
    private static function selectEmailTemplate($type, $data)
    {
        // Use specialized templates when available
        $specializedTemplates = [
            'enrollment' => 'emails.student.enrolled',
            'student_enrolled' => 'emails.student.enrolled',
            'account_created' => 'emails.user.account-created',
            'homework_assigned' => 'emails.homework.assigned',
            'homework_graded' => 'emails.homework.graded',
            'attendance_marked' => 'emails.attendance.marked',
        ];
            
        // Check if specialized template exists
        if (isset($specializedTemplates[$type])) {
            $templatePath = str_replace('.', '/', $specializedTemplates[$type]) . '.blade.php';
            if (view()->exists($specializedTemplates[$type])) {
                return $specializedTemplates[$type];
            }
        }
        
        // Fall back to general notification template
        return 'emails.notification';
    }
    
    /**
     * ✅ QUEUE EMAIL FOR CRON PROCESSING (for bulk operations)
     */
    private static function queueEmail($user, $title, $messageText, $data = [])
    {
        try {
            // System-level gate: respect global email toggle from SuperAdmin Settings
            if (!SystemSetting::isEmailEnabled()) {
                Log::info("NotificationHelper: Email globally disabled — skipping queued email to {$user->email}");
                return false;
            }

            if (!$user->email) {
                Log::warning("User {$user->id} has no email address");
                return false;
            }

            $template = self::selectEmailTemplate($data['type'] ?? 'general', $data);

            PendingEmail::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'subject' => $title,
                'body' => $messageText,
                'data' => $data, 
                'template' => $template,
                'status' => 'pending',
                'scheduled_at' => now()->addMinutes(5),
                'attempts' => 0,
            ]);

            // ✅ M-3: Log queued email
            \App\Models\EmailLog::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'subject' => $title,
                'template' => $template,
                'type' => $data['type'] ?? 'general',
                'method' => 'queued',
                'status' => 'queued',
            ]);

            Log::info("Email queued for: {$user->email}");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to queue email for {$user->email}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ NOTIFY SINGLE USER (with optional email)
     */
    public static function notifyUser($user, $title, $message, $type, $data = [], $sendEmail = true)
    {
        try {
            // Create in-app notification
            $user->notify(new GeneralNotification([
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
            ]));

            // Send email if enabled
            if ($sendEmail && $user->email) {
                $data['type'] = $type;
                self::sendEmailImmediate($user, $title, $message, $data);
            }

            return 1;

        } catch (\Exception $e) {
            Log::error("Failed to notify user {$user->id}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * ✅ NOTIFY TEACHER (with email)
     */
    public static function notifyTeacher($teacher, $title, $message, $type, $data = [], $sendEmail = true)
    {
        return self::notifyUser($teacher, $title, $message, $type, $data, $sendEmail);
    }

    /**
     * ✅ NOTIFY STUDENT'S PARENT (with email)
     */
    public static function notifyStudentParent($student, $title, $message, $type, $data = [], $sendEmail = true)
    {
        try {
            if (!$student->parent) {
                Log::warning("Student {$student->id} has no parent assigned");
                return 0;
            }

            // Add student info to data
            $data['student_id'] = $student->id;
            $data['student_name'] = $student->full_name;

            return self::notifyUser($student->parent, $title, $message, $type, $data, $sendEmail);

        } catch (\Exception $e) {
            Log::error("Failed to notify student parent: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * ✅ NOTIFY ALL PARENTS IN A CLASS (queue emails for bulk)
     */
    public static function notifyClassParents($class, $title, $message, $type, $data = [])
    {
        try {
            $students = $class->students()
                ->wherePivot('status', 'active')
                ->with('parent')
                ->get();

            $notified = 0;

            foreach ($students as $student) {
                if (!$student->parent) {
                    Log::warning("Student {$student->id} has no parent assigned");
                    continue;
                }

                // Add student-specific data
                $studentData = array_merge($data, [
                    'student_id' => $student->id,
                    'student_name' => $student->full_name,
                    'type' => $type,
                ]);

                // Create in-app notification
                $student->parent->notify(new GeneralNotification([
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'data' => $studentData,
                ]));

                // Queue email (don't send immediately for bulk)
                if ($student->parent->email) {
                    self::queueEmail($student->parent, $title, $message, $studentData);
                }

                $notified++;
            }

            Log::info("Notified {$notified} class parents (in-app + queued emails)");
            return $notified;

        } catch (\Exception $e) {
            Log::error("Failed to notify class parents: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * ✅ NOTIFY ALL SUPERADMINS (queue emails)
     */
    public static function notifySuperAdmins($title, $message, $type, $data = [])
    {
        try {
            $superAdmins = User::where('role', 'superadmin')
                ->where('status', 'active')
                ->get();

            if ($superAdmins->isEmpty()) {
                Log::warning("No active superadmins found");
                return 0;
            }

            $notified = 0;
            $data['type'] = $type;

            foreach ($superAdmins as $admin) {
                // Create in-app notification
                $admin->notify(new GeneralNotification([
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'data' => $data,
                ]));

                // Queue email
                if ($admin->email) {
                    self::queueEmail($admin, $title, $message, $data);
                }

                $notified++;
            }

            Log::info("Notified {$notified} superadmins (in-app + queued emails)");
            return $notified;

        } catch (\Exception $e) {
            Log::error("Failed to notify superadmins: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * ✅ NOTIFY ALL ADMINS (queue emails)
     */
    public static function notifyAdmins($title, $message, $type, $data = [])
    {
        try {
            $admins = User::where('role', 'admin')
                ->where('status', 'active')
                ->get();

            if ($admins->isEmpty()) {
                Log::warning("No active admins found");
                return 0;
            }

            $notified = 0;
            $data['type'] = $type;

            foreach ($admins as $admin) {
                // Create in-app notification
                $admin->notify(new GeneralNotification([
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'data' => $data,
                ]));

                // Queue email (bulk operation)
                if ($admin->email) {
                    self::queueEmail($admin, $title, $message, $data);
                }

                $notified++;
            }

            Log::info("Notified {$notified} admins (in-app + queued emails)");
            return $notified;

        } catch (\Exception $e) {
            Log::error("Failed to notify admins: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * ✅ NOTIFY ALL PARENTS (queue all emails)
     */
    public static function notifyAllParents($title, $message, $type, $data = [])
    {
        try {
            $parents = User::where('role', 'parent')
                ->where('status', 'active')
                ->get();

            if ($parents->isEmpty()) {
                Log::warning("No active parents found");
                return 0;
            }

            $notified = 0;
            $data['type'] = $type;

            foreach ($parents as $parent) {
                // Create in-app notification
                $parent->notify(new GeneralNotification([
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'data' => $data,
                ]));

                // Queue email (bulk operation)
                if ($parent->email) {
                    self::queueEmail($parent, $title, $message, $data);
                }

                $notified++;
            }

            Log::info("Notified {$notified} parents (in-app + queued emails)");
            return $notified;

        } catch (\Exception $e) {
            Log::error("Failed to notify all parents: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * ✅ NOTIFY ALL TEACHERS (queue all emails)
     */
    public static function notifyAllTeachers($title, $message, $type, $data = [])
    {
        try {
            $teachers = User::where('role', 'teacher')
                ->where('status', 'active')
                ->get();

            if ($teachers->isEmpty()) {
                Log::warning("No active teachers found");
                return 0;
            }

            $notified = 0;
            $data['type'] = $type;

            foreach ($teachers as $teacher) {
                // Create in-app notification
                $teacher->notify(new GeneralNotification([
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'data' => $data,
                ]));

                // Queue email (bulk operation)
                if ($teacher->email) {
                    self::queueEmail($teacher, $title, $message, $data);
                }

                $notified++;
            }

            Log::info("Notified {$notified} teachers (in-app + queued emails)");
            return $notified;

        } catch (\Exception $e) {
            Log::error("Failed to notify all teachers: " . $e->getMessage());
            return 0;
        }
    }



    // NEW EMAILS AND NOTIFICATIONS BELOW
    /**
     * ✅ SECURITY FIX (C-1): Notify user when account is created  app/Helpers/NotificationHelper.php
     * No longer accepts or transmits passwords. Uses setup link instead.
     */
    public static function notifyAccountCreated($user, $createdBy = null)
    {
        try {
            $creatorName = $createdBy ? $createdBy->name : 'System Administrator';

            // Generate password setup token
            $token = \Illuminate\Support\Facades\Password::broker()->createToken($user);
            $setupUrl = url(route('password.reset', [
                'token' => $token,
                'email' => $user->email,
            ], false));

            // In-app notification (NO password data stored)
            $user->notify(new GeneralNotification([
                'type' => 'account_created',
                'title' => 'Welcome to MLC Classroom',
                'message' => "Your account has been created by {$creatorName}. Please check your email for your password setup link.",
                'data' => [
                    'role' => $user->role,
                    'created_by' => $creatorName,
                    'url' => route('login')
                ]
            ]));

            // Send welcome email with setup link (NOT credentials)
            $data = [
                'type' => 'account_created',
                'user' => $user,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'role' => $user->role,
                'created_by' => $creatorName,
                'setup_url' => $setupUrl,
                'login_url' => route('login'),
                'url' => $setupUrl,
            ];

            self::sendEmailImmediate($user, 'Welcome to MLC Classroom - Set Your Password',
                "Your account has been created. Please click the link below to set your password.", $data);

            return 1;

        } catch (\Exception $e) {
            Log::error("Failed to send account created notification: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * ✅ NEW: Notify user when account is activated
     */
    public static function notifyAccountActivated($user, $activatedBy = null)
    {
        try {
            $activatorName = $activatedBy ? $activatedBy->name : 'Administrator';
            
            $user->notify(new GeneralNotification([
                'type' => 'account_activated',
                'title' => 'Account Activated',
                'message' => "Your account has been activated by {$activatorName}. You can now log in.",
                'data' => [
                    'activated_by' => $activatorName,
                    'url' => route('login')
                ]
            ]));

            $data = [
                'user' => $user,
                'activated_by' => $activatorName,
                'login_url' => route('login')
            ];

            self::sendEmailImmediate($user, 'Account Activated', 
                "Your MLC Classroom account has been activated. You can now log in and access the platform.", $data);

            return 1;

        } catch (\Exception $e) {
            Log::error("Failed to send account activated notification: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * ✅ NEW: Notify user when account is deactivated
     */
    public static function notifyAccountDeactivated($user, $reason = null, $deactivatedBy = null)
    {
        try {
            $deactivatorName = $deactivatedBy ? $deactivatedBy->name : 'Administrator';
            $reasonText = $reason ? " Reason: {$reason}" : '';
            
            $user->notify(new GeneralNotification([
                'type' => 'account_deactivated',
                'title' => 'Account Deactivated',
                'message' => "Your account has been deactivated by {$deactivatorName}.{$reasonText}",
                'data' => [
                    'deactivated_by' => $deactivatorName,
                    'reason' => $reason
                ]
            ]));

            $data = [
                'user' => $user,
                'deactivated_by' => $deactivatorName,
                'reason' => $reason,
                'contact_email' => config('mail.from.address')
            ];

            self::sendEmailImmediate($user, 'Account Deactivated', 
                "Your MLC Classroom account has been deactivated.{$reasonText} Please contact administration if you have questions.", $data);

            return 1;

        } catch (\Exception $e) {
            Log::error("Failed to send account deactivated notification: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * ✅ NEW: Notify when student enrolled in class
     */
    public static function notifyStudentEnrolledInClass($student, $class, $enrolledBy = null)
    {
        try {
            $enrollerName = $enrolledBy ? $enrolledBy->name : 'Administrator';
            $notified = 0;

            // Notify parent
            if ($student->parent) {
                $student->parent->notify(new GeneralNotification([
                    'type' => 'class_enrollment',
                    'title' => 'Student Enrolled in Class',
                    'message' => "{$student->full_name} has been enrolled in {$class->name} by {$enrollerName}",
                    'data' => [
                        'student_id' => $student->id,
                        'student_name' => $student->full_name,
                        'class_id' => $class->id,
                        'class_name' => $class->name,
                        'enrolled_by' => $enrollerName,
                        'url' => route('parent.students.show', $student->id)
                    ]
                ]));

                $data = [
                    'student' => $student,
                    'class' => $class,
                    'enrolled_by' => $enrollerName
                ];

                self::queueEmail($student->parent, 'Student Enrolled in Class', 
                    "{$student->full_name} has been enrolled in {$class->name}", $data);

                $notified++;
            }

            // Notify teacher
            if ($class->teacher) {
                $class->teacher->notify(new GeneralNotification([
                    'type' => 'class_enrollment',
                    'title' => 'New Student in Your Class',
                    'message' => "{$student->full_name} has been enrolled in {$class->name}",
                    'data' => [
                        'student_id' => $student->id,
                        'student_name' => $student->full_name,
                        'class_id' => $class->id,
                        'class_name' => $class->name,
                        'url' => route('teacher.classes.show', $class->id)
                    ]
                ]));

                $data = [
                    'student' => $student,
                    'class' => $class
                ];

                self::queueEmail($class->teacher, 'New Student Enrolled', 
                    "{$student->full_name} has been enrolled in your class {$class->name}", $data);

                $notified++;
            }

            Log::info("Class enrollment notifications sent for student {$student->id} in class {$class->id}");
            return $notified;

        } catch (\Exception $e) {
            Log::error("Failed to send class enrollment notifications: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * ✅ NEW: Notify when student removed from class
     */
    public static function notifyStudentRemovedFromClass($student, $class, $removedBy = null)
    {
        try {
            $removerName = $removedBy ? $removedBy->name : 'Administrator';
            $notified = 0;

            // Notify parent
            if ($student->parent) {
                $student->parent->notify(new GeneralNotification([
                    'type' => 'class_removal',
                    'title' => 'Student Removed from Class',
                    'message' => "{$student->full_name} has been removed from {$class->name}",
                    'data' => [
                        'student_id' => $student->id,
                        'student_name' => $student->full_name,
                        'class_id' => $class->id,
                        'class_name' => $class->name,
                        'removed_by' => $removerName,
                        'url' => route('parent.students.show', $student->id)
                    ]
                ]));

                $data = [
                    'student' => $student,
                    'class' => $class,
                    'removed_by' => $removerName
                ];

                self::sendEmailImmediate($student->parent, 'Student Removed from Class', 
                    "{$student->full_name} has been removed from {$class->name}", $data);

                $notified++;
            }

            // Notify teacher
            if ($class->teacher) {
                $class->teacher->notify(new GeneralNotification([
                    'type' => 'class_removal',
                    'title' => 'Student Removed from Your Class',
                    'message' => "{$student->full_name} has been removed from {$class->name}",
                    'data' => [
                        'student_id' => $student->id,
                        'student_name' => $student->full_name,
                        'class_id' => $class->id,
                        'class_name' => $class->name,
                        'url' => route('teacher.classes.show', $class->id)
                    ]
                ]));

                $notified++;
            }

            return $notified;

        } catch (\Exception $e) {
            Log::error("Failed to send class removal notifications: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * ✅ NEW: Send homework due reminder (24 hours before)
     */
    public static function sendHomeworkDueReminder($homework)
    {
        try {
            $students = $homework->class->students()
                ->wherePivot('status', 'active')
                ->with('parent')
                ->get();

            $notified = 0;

            foreach ($students as $student) {
                if (!$student->parent) {
                    continue;
                }

                // Check if already submitted
                $submitted = $student->homeworkSubmissions()
                    ->where('homework_assignment_id', $homework->id)
                    ->exists();

                if ($submitted) {
                    continue; // Don't remind if already submitted
                }

                $student->parent->notify(new GeneralNotification([
                    'type' => 'homework_reminder',
                    'title' => 'Homework Due Tomorrow',
                    'message' => "Reminder: {$homework->title} for {$homework->class->name} is due tomorrow ({$homework->due_date->format('d/m/Y')})",
                    'data' => [
                        'homework_id' => $homework->id,
                        'homework_title' => $homework->title,
                        'class_name' => $homework->class->name,
                        'due_date' => $homework->due_date->format('Y-m-d'),
                        'student_id' => $student->id,
                        'student_name' => $student->full_name,
                        'url' => route('parent.homework.show', $homework->id)
                    ]
                ]));

                $data = [
                    'homework' => $homework,
                    'student' => $student,
                    'due_date' => $homework->due_date->format('d/m/Y')
                ];

                self::queueEmail($student->parent, 'Homework Due Tomorrow', 
                    "This is a reminder that {$homework->title} is due tomorrow", $data);

                $notified++;
            }

            Log::info("Homework reminder sent for {$homework->id}: {$notified} parents notified");
            return $notified;

        } catch (\Exception $e) {
            Log::error("Failed to send homework reminders: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * ✅ NEW: Send homework overdue notification
     */
    public static function sendHomeworkOverdueNotification($homework)
    {
        try {
            $students = $homework->class->students()
                ->wherePivot('status', 'active')
                ->with('parent')
                ->get();

            $notified = 0;

            foreach ($students as $student) {
                if (!$student->parent) {
                    continue;
                }

                // Check if already submitted
                $submitted = $student->homeworkSubmissions()
                    ->where('homework_assignment_id', $homework->id)
                    ->exists();

                if ($submitted) {
                    continue; // Don't notify if already submitted
                }

                $student->parent->notify(new GeneralNotification([
                    'type' => 'homework_overdue',
                    'title' => 'Homework Overdue',
                    'message' => "{$homework->title} for {$homework->class->name} was due on {$homework->due_date->format('d/m/Y')} and has not been submitted",
                    'data' => [
                        'homework_id' => $homework->id,
                        'homework_title' => $homework->title,
                        'class_name' => $homework->class->name,
                        'due_date' => $homework->due_date->format('Y-m-d'),
                        'student_id' => $student->id,
                        'student_name' => $student->full_name,
                        'url' => route('parent.homework.show', $homework->id)
                    ]
                ]));

                $data = [
                    'homework' => $homework,
                    'student' => $student,
                    'due_date' => $homework->due_date->format('d/m/Y')
                ];

                self::sendEmailImmediate($student->parent, 'Homework Overdue Alert', 
                    "{$homework->title} is now overdue. Please submit as soon as possible.", $data);

                $notified++;
            }

            Log::info("Homework overdue notification sent for {$homework->id}: {$notified} parents notified");
            return $notified;

        } catch (\Exception $e) {
            Log::error("Failed to send homework overdue notifications: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * ✅ NEW: Notify teacher when assigned to class
     */
    public static function notifyTeacherAssignedToClass($teacher, $class, $assignedBy = null)
    {
        try {
            $assignerName = $assignedBy ? $assignedBy->name : 'Administrator';

            $teacher->notify(new GeneralNotification([
                'type' => 'class_assigned',
                'title' => 'Assigned to New Class',
                'message' => "You have been assigned to teach {$class->name} by {$assignerName}",
                'data' => [
                    'class_id' => $class->id,
                    'class_name' => $class->name,
                    'assigned_by' => $assignerName,
                    'student_count' => $class->students()->count(),
                    'url' => route('teacher.classes.show', $class->id)
                ]
            ]));

            $data = [
                'teacher' => $teacher,
                'class' => $class,
                'assigned_by' => $assignerName,
                'student_count' => $class->students()->count()
            ];

            self::sendEmailImmediate($teacher, 'New Class Assignment', 
                "You have been assigned to teach {$class->name}", $data);

            return 1;

        } catch (\Exception $e) {
            Log::error("Failed to send teacher assignment notification: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * ✅ NEW: Send low attendance alert to parent
     */
    public static function sendLowAttendanceAlert($student, $attendancePercentage, $threshold = 75)
    {
        try {
            if (!$student->parent) {
                Log::warning("Student {$student->id} has no parent for low attendance alert");
                return 0;
            }

            $student->parent->notify(new GeneralNotification([
                'type' => 'low_attendance',
                'title' => 'Low Attendance Alert',
                'message' => "{$student->full_name}'s attendance has dropped to {$attendancePercentage}% (below {$threshold}% threshold)",
                'data' => [
                    'student_id' => $student->id,
                    'student_name' => $student->full_name,
                    'attendance_percentage' => $attendancePercentage,
                    'threshold' => $threshold,
                    'url' => route('parent.attendance.show', $student->id)
                ]
            ]));

            $data = [
                'student' => $student,
                'attendance_percentage' => $attendancePercentage,
                'threshold' => $threshold
            ];

            self::sendEmailImmediate($student->parent, 'Low Attendance Alert', 
                "{$student->full_name}'s attendance requires attention", $data);

            return 1;

        } catch (\Exception $e) {
            Log::error("Failed to send low attendance alert: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * ✅ NEW: Notify users about new announcement
     */
    public static function notifyAnnouncement($announcement)
    {
        try {
            $recipients = [];

            // Determine recipients based on target audience
            switch ($announcement->target_audience) {
                case 'all':
                    $recipients = User::whereIn('role', ['parent', 'teacher', 'admin'])
                        ->where('status', 'active')
                        ->get();
                    break;

                case 'parents':
                    $recipients = User::where('role', 'parent')
                        ->where('status', 'active')
                        ->get();
                    break;

                case 'teachers':
                    $recipients = User::where('role', 'teacher')
                        ->where('status', 'active')
                        ->get();
                    break;

                case 'admins':
                    $recipients = User::whereIn('role', ['admin', 'superadmin'])
                        ->where('status', 'active')
                        ->get();
                    break;
            }

            $notified = 0;

            foreach ($recipients as $user) {
                $user->notify(new GeneralNotification([
                    'type' => 'announcement',
                    'title' => $announcement->title,
                    'message' => \Str::limit(strip_tags($announcement->content), 150),
                    'data' => [
                        'announcement_id' => $announcement->id,
                        'publish_date' => $announcement->publish_date->format('Y-m-d'),
                        'url' => '#' // Add announcement view route when available
                    ]
                ]));

                $data = [
                    'announcement' => $announcement
                ];

                self::queueEmail($user, $announcement->title, 
                    strip_tags($announcement->content), $data);

                $notified++;
            }

            Log::info("Announcement notification sent: {$notified} users notified");
            return $notified;

        } catch (\Exception $e) {
            Log::error("Failed to send announcement notifications: " . $e->getMessage());
            return 0;
        }
    }
    
}