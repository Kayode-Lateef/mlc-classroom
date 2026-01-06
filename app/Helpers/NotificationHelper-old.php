<?php

// namespace App\Helpers;

// use App\Models\User;
// use App\Models\Student;
// use App\Models\ClassModel;
// use App\Notifications\GeneralNotification;
// use Illuminate\Support\Facades\Log;

// class NotificationHelper
// {
//     /**
//      * Notify all superadmins
//      */
//     public static function notifySuperAdmins($title, $message, $type = 'general', $additionalData = [])
//     {
//         $superadmins = User::where('role', 'superadmin')
//             ->where('status', 'active')
//             ->get();
        
//         self::sendToUsers($superadmins, $title, $message, $type, $additionalData);
//     }

//     /**
//      * Notify all admins (SuperAdmins + Admins)
//      */
//     public static function notifyAllAdmins($title, $message, $type = 'general', $additionalData = [])
//     {
//         $admins = User::whereIn('role', ['superadmin', 'admin'])
//             ->where('status', 'active')
//             ->get();
        
//         self::sendToUsers($admins, $title, $message, $type, $additionalData);
//     }

//     /**
//      * Notify a student's parent (since students don't have login)
//      */
//     public static function notifyStudentParent(Student $student, $title, $message, $type = 'general', $additionalData = [])
//     {
//         if (!$student->parent) {
//             Log::warning("Student {$student->id} has no parent assigned");
//             return false;
//         }

//         // Add student context to notification
//         $data = array_merge([
//             'student_id' => $student->id,
//             'student_name' => $student->full_name,
//         ], $additionalData);

//         self::sendToUser($student->parent, $title, $message, $type, $data);
//         return true;
//     }

//     /**
//      * Notify all parents in a class
//      */
//     public static function notifyClassParents(ClassModel $class, $title, $message, $type = 'general', $additionalData = [])
//     {
//         $parents = User::where('role', 'parent')
//             ->where('status', 'active')
//             ->whereHas('children', function($query) use ($class) {
//                 $query->whereHas('enrollments', function($q) use ($class) {
//                     $q->where('class_id', $class->id)
//                       ->where('status', 'active');
//                 });
//             })
//             ->get();

//         if ($parents->isEmpty()) {
//             Log::info("No parents found for class {$class->id}");
//             return 0;
//         }

//         // Add class context
//         $data = array_merge([
//             'class_id' => $class->id,
//             'class_name' => $class->name,
//         ], $additionalData);

//         self::sendToUsers($parents, $title, $message, $type, $data);
//         return $parents->count();
//     }

//     /**
//      * Notify all teachers
//      */
//     public static function notifyAllTeachers($title, $message, $type = 'general', $additionalData = [])
//     {
//         $teachers = User::where('role', 'teacher')
//             ->where('status', 'active')
//             ->get();
        
//         self::sendToUsers($teachers, $title, $message, $type, $additionalData);
//         return $teachers->count();
//     }

//     /**
//      * Notify a specific teacher
//      */
//     public static function notifyTeacher(User $teacher, $title, $message, $type = 'general', $additionalData = [])
//     {
//         if ($teacher->role !== 'teacher') {
//             Log::warning("User {$teacher->id} is not a teacher");
//             return false;
//         }

//         self::sendToUser($teacher, $title, $message, $type, $additionalData);
//         return true;
//     }

//     /**
//      * Notify all parents
//      */
//     public static function notifyAllParents($title, $message, $type = 'general', $additionalData = [])
//     {
//         $parents = User::where('role', 'parent')
//             ->where('status', 'active')
//             ->get();
        
//         self::sendToUsers($parents, $title, $message, $type, $additionalData);
//         return $parents->count();
//     }

//     /**
//      * Notify a single user
//      */
//     public static function notifyUser(User $user, $title, $message, $type = 'general', $additionalData = [])
//     {
//         if ($user->status !== 'active') {
//             Log::warning("Attempted to notify inactive user {$user->id}");
//             return false;
//         }

//         self::sendToUser($user, $title, $message, $type, $additionalData);
//         return true;
//     }

//     /**
//      * Send notification to multiple users
//      */
//     protected static function sendToUsers($users, $title, $message, $type, $additionalData)
//     {
//         $notificationData = array_merge([
//             'type' => $type,
//             'title' => $title,
//             'message' => $message,
//             'icon' => self::getIconForType($type),
//             'sent_by' => auth()->check() ? auth()->user()->name : 'System',
//             'sent_at' => now()->format('d M Y, H:i'),
//         ], $additionalData);
        
//         $sentCount = 0;
//         $failedCount = 0;

//         foreach ($users as $user) {
//             try {
//                 $user->notify(new GeneralNotification($notificationData));
//                 $sentCount++;
//             } catch (\Exception $e) {
//                 Log::error("Failed to notify user {$user->id}: " . $e->getMessage());
//                 $failedCount++;
//             }
//         }

//         if ($failedCount > 0) {
//             Log::warning("Notification summary: {$sentCount} sent, {$failedCount} failed");
//         }

//         return $sentCount;
//     }

//     /**
//      * Send notification to a single user
//      */
//     protected static function sendToUser(User $user, $title, $message, $type, $additionalData)
//     {
//         $notificationData = array_merge([
//             'type' => $type,
//             'title' => $title,
//             'message' => $message,
//             'icon' => self::getIconForType($type),
//             'sent_by' => auth()->check() ? auth()->user()->name : 'System',
//             'sent_at' => now()->format('d M Y, H:i'),
//         ], $additionalData);
        
//         try {
//             $user->notify(new GeneralNotification($notificationData));
//             return true;
//         } catch (\Exception $e) {
//             Log::error("Failed to notify user {$user->id}: " . $e->getMessage());
//             return false;
//         }
//     }

//     /**
//      * Get icon for notification type
//      */
//     protected static function getIconForType($type)
//     {
//         return match($type) {
//             'emergency' => 'ti-alert',
//             'homework', 'homework_assigned' => 'ti-pencil-alt',
//             'homework_graded' => 'ti-check',
//             'progress_report' => 'ti-stats-up',
//             'schedule_change' => 'ti-calendar',
//             'absence' => 'ti-info-alt',
//             'class_full' => 'ti-alert',
//             default => 'ti-bell',
//         };
//     }
// }