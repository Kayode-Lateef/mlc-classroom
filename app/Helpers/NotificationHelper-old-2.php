<?php

// namespace App\Helpers;

// use App\Models\User;
// use App\Models\Student;
// use App\Models\ClassModel;
// use App\Models\PendingEmail;
// use App\Notifications\GeneralNotification;
// use Illuminate\Support\Facades\Mail;
// use Illuminate\Support\Facades\Log;

// class NotificationHelper
// {
//     /**
//      * ✅ SEND EMAIL IMMEDIATELY (for critical notifications, 1-3 recipients)
//      */
//     private static function sendEmailImmediate($user, $title, $message, $data = [])
//     {
//         try {
//             if (!$user->email) {
//                 Log::warning("User {$user->id} has no email address");
//                 return false;
//             }

//             Mail::send('emails.notification', [
//                 'title' => $title,
//                 'message' => $message,
//                 'url' => $data['url'] ?? null,
//                 'data' => $data,
//                 'type' => $data['type'] ?? 'general',
//             ], function ($mail) use ($user, $title) {
//                 $mail->to($user->email, $user->name)
//                      ->subject($title);
//             });

//             Log::info("Email sent immediately to: {$user->email}");
//             return true;

//         } catch (\Exception $e) {
//             Log::error("Failed to send email to {$user->email}: " . $e->getMessage());
//             return false;
//         }
//     }

//     /**
//      * ✅ QUEUE EMAIL FOR CRON PROCESSING (for bulk operations)
//      */
//     private static function queueEmail($user, $title, $message, $data = [])
//     {
//         try {
//             if (!$user->email) {
//                 Log::warning("User {$user->id} has no email address");
//                 return false;
//             }

//             PendingEmail::create([
//                 'user_id' => $user->id,
//                 'email' => $user->email,
//                 'subject' => $title,
//                 'body' => $message,
//                 'data' => json_encode($data),
//                 'status' => 'pending',
//                 'scheduled_at' => now()->addMinutes(5),
//                 'attempts' => 0,
//             ]);

//             Log::info("Email queued for: {$user->email}");
//             return true;

//         } catch (\Exception $e) {
//             Log::error("Failed to queue email for {$user->email}: " . $e->getMessage());
//             return false;
//         }
//     }

//     /**
//      * ✅ NOTIFY SINGLE USER (with optional email)
//      */
//     public static function notifyUser($user, $title, $message, $type, $data = [], $sendEmail = true)
//     {
//         try {
//             // Create in-app notification
//             $user->notify(new GeneralNotification([
//                 'type' => $type,
//                 'title' => $title,
//                 'message' => $message,
//                 'data' => $data,
//             ]));

//             // Send email if enabled
//             if ($sendEmail && $user->email) {
//                 $data['type'] = $type;
//                 self::sendEmailImmediate($user, $title, $message, $data);
//             }

//             return 1;

//         } catch (\Exception $e) {
//             Log::error("Failed to notify user {$user->id}: " . $e->getMessage());
//             return 0;
//         }
//     }

//     /**
//      * ✅ NOTIFY TEACHER (with email)
//      */
//     public static function notifyTeacher($teacher, $title, $message, $type, $data = [], $sendEmail = true)
//     {
//         return self::notifyUser($teacher, $title, $message, $type, $data, $sendEmail);
//     }

//     /**
//      * ✅ NOTIFY STUDENT'S PARENT (with email)
//      */
//     public static function notifyStudentParent($student, $title, $message, $type, $data = [], $sendEmail = true)
//     {
//         try {
//             if (!$student->parent) {
//                 Log::warning("Student {$student->id} has no parent assigned");
//                 return 0;
//             }

//             // Add student info to data
//             $data['student_id'] = $student->id;
//             $data['student_name'] = $student->full_name;

//             return self::notifyUser($student->parent, $title, $message, $type, $data, $sendEmail);

//         } catch (\Exception $e) {
//             Log::error("Failed to notify student parent: " . $e->getMessage());
//             return 0;
//         }
//     }

//     /**
//      * ✅ NOTIFY ALL PARENTS IN A CLASS (queue emails for bulk)
//      */
//     public static function notifyClassParents($class, $title, $message, $type, $data = [])
//     {
//         try {
//             $students = $class->students()
//                 ->wherePivot('status', 'active')
//                 ->with('parent')
//                 ->get();

//             $notified = 0;

//             foreach ($students as $student) {
//                 if (!$student->parent) {
//                     Log::warning("Student {$student->id} has no parent assigned");
//                     continue;
//                 }

//                 // Add student-specific data
//                 $studentData = array_merge($data, [
//                     'student_id' => $student->id,
//                     'student_name' => $student->full_name,
//                     'type' => $type,
//                 ]);

//                 // Create in-app notification
//                 $student->parent->notify(new GeneralNotification([
//                     'type' => $type,
//                     'title' => $title,
//                     'message' => $message,
//                     'data' => $studentData,
//                 ]));

//                 // Queue email (don't send immediately for bulk)
//                 if ($student->parent->email) {
//                     self::queueEmail($student->parent, $title, $message, $studentData);
//                 }

//                 $notified++;
//             }

//             Log::info("Notified {$notified} class parents (in-app + queued emails)");
//             return $notified;

//         } catch (\Exception $e) {
//             Log::error("Failed to notify class parents: " . $e->getMessage());
//             return 0;
//         }
//     }

//     /**
//      * ✅ NOTIFY ALL SUPERADMINS (queue emails)
//      */
//     public static function notifySuperAdmins($title, $message, $type, $data = [])
//     {
//         try {
//             $superAdmins = User::where('role', 'superadmin')
//                 ->where('status', 'active')
//                 ->get();

//             if ($superAdmins->isEmpty()) {
//                 Log::warning("No active superadmins found");
//                 return 0;
//             }

//             $notified = 0;
//             $data['type'] = $type;

//             foreach ($superAdmins as $admin) {
//                 // Create in-app notification
//                 $admin->notify(new GeneralNotification([
//                     'type' => $type,
//                     'title' => $title,
//                     'message' => $message,
//                     'data' => $data,
//                 ]));

//                 // Queue email
//                 if ($admin->email) {
//                     self::queueEmail($admin, $title, $message, $data);
//                 }

//                 $notified++;
//             }

//             Log::info("Notified {$notified} superadmins (in-app + queued emails)");
//             return $notified;

//         } catch (\Exception $e) {
//             Log::error("Failed to notify superadmins: " . $e->getMessage());
//             return 0;
//         }
//     }

//     /**
//      * ✅ NOTIFY ALL PARENTS (queue all emails)
//      */
//     public static function notifyAllParents($title, $message, $type, $data = [])
//     {
//         try {
//             $parents = User::where('role', 'parent')
//                 ->where('status', 'active')
//                 ->get();

//             if ($parents->isEmpty()) {
//                 Log::warning("No active parents found");
//                 return 0;
//             }

//             $notified = 0;
//             $data['type'] = $type;

//             foreach ($parents as $parent) {
//                 // Create in-app notification
//                 $parent->notify(new GeneralNotification([
//                     'type' => $type,
//                     'title' => $title,
//                     'message' => $message,
//                     'data' => $data,
//                 ]));

//                 // Queue email (bulk operation)
//                 if ($parent->email) {
//                     self::queueEmail($parent, $title, $message, $data);
//                 }

//                 $notified++;
//             }

//             Log::info("Notified {$notified} parents (in-app + queued emails)");
//             return $notified;

//         } catch (\Exception $e) {
//             Log::error("Failed to notify all parents: " . $e->getMessage());
//             return 0;
//         }
//     }

//     /**
//      * ✅ NOTIFY ALL TEACHERS (queue all emails)
//      */
//     public static function notifyAllTeachers($title, $message, $type, $data = [])
//     {
//         try {
//             $teachers = User::where('role', 'teacher')
//                 ->where('status', 'active')
//                 ->get();

//             if ($teachers->isEmpty()) {
//                 Log::warning("No active teachers found");
//                 return 0;
//             }

//             $notified = 0;
//             $data['type'] = $type;

//             foreach ($teachers as $teacher) {
//                 // Create in-app notification
//                 $teacher->notify(new GeneralNotification([
//                     'type' => $type,
//                     'title' => $title,
//                     'message' => $message,
//                     'data' => $data,
//                 ]));

//                 // Queue email (bulk operation)
//                 if ($teacher->email) {
//                     self::queueEmail($teacher, $title, $message, $data);
//                 }

//                 $notified++;
//             }

//             Log::info("Notified {$notified} teachers (in-app + queued emails)");
//             return $notified;

//         } catch (\Exception $e) {
//             Log::error("Failed to notify all teachers: " . $e->getMessage());
//             return 0;
//         }
//     }
// }