<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property string $action e.g., created_student, marked_attendance
 * @property string|null $model_type
 * @property int|null $model_id
 * @property string|null $description
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $model
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog recent($limit = 50)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereUserId($value)
 */
	class ActivityLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $student_id
 * @property int $class_id
 * @property int $schedule_id
 * @property \Illuminate\Support\Carbon $date
 * @property string $status
 * @property int $marked_by
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ClassModel $class
 * @property-read \App\Models\User $markedBy
 * @property-read \App\Models\Schedule $schedule
 * @property-read \App\Models\Student $student
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance absent()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance byDateRange($startDate, $endDate)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance present()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereClassId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereMarkedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereScheduleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereUpdatedAt($value)
 */
	class Attendance extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $student_id
 * @property int $class_id
 * @property \Illuminate\Support\Carbon $enrollment_date
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ClassModel $class
 * @property-read \App\Models\Student $student
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassEnrollment active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassEnrollment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassEnrollment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassEnrollment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassEnrollment whereClassId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassEnrollment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassEnrollment whereEnrollmentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassEnrollment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassEnrollment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassEnrollment whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassEnrollment whereUpdatedAt($value)
 */
	class ClassEnrollment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name e.g., Maths 11+, English GCSE
 * @property string $subject
 * @property string|null $level e.g., Year 6, 11+, GCSE
 * @property string|null $room_number
 * @property int|null $teacher_id
 * @property int $capacity
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attendance> $attendance
 * @property-read int|null $attendance_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ClassEnrollment> $enrollments
 * @property-read int|null $enrollments_count
 * @property-read mixed $enrolled_students_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\HomeworkAssignment> $homeworkAssignments
 * @property-read int|null $homework_assignments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LearningResource> $learningResources
 * @property-read int|null $learning_resources_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProgressSheet> $progressSheets
 * @property-read int|null $progress_sheets_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Schedule> $schedules
 * @property-read int|null $schedules_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Student> $students
 * @property-read int|null $students_count
 * @property-read \App\Models\User|null $teacher
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassModel byTeacher($teacherId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassModel whereCapacity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassModel whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassModel whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassModel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassModel whereRoomNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassModel whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassModel whereTeacherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassModel whereUpdatedAt($value)
 */
	class ClassModel extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $class_id
 * @property int|null $progress_sheet_id
 * @property string $title
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $assigned_date
 * @property \Illuminate\Support\Carbon $due_date
 * @property string|null $file_path Teacher attachment
 * @property int $teacher_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ClassModel $class
 * @property-read mixed $submission_rate
 * @property-read \App\Models\ProgressSheet|null $progressSheet
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\HomeworkSubmission> $submissions
 * @property-read int|null $submissions_count
 * @property-read \App\Models\User $teacher
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkAssignment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkAssignment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkAssignment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkAssignment whereAssignedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkAssignment whereClassId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkAssignment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkAssignment whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkAssignment whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkAssignment whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkAssignment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkAssignment whereProgressSheetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkAssignment whereTeacherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkAssignment whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkAssignment whereUpdatedAt($value)
 */
	class HomeworkAssignment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $homework_assignment_id
 * @property int $student_id
 * @property \Illuminate\Support\Carbon|null $submitted_date
 * @property string $status
 * @property string|null $file_path Student submission
 * @property string|null $teacher_comments
 * @property string|null $grade
 * @property \Illuminate\Support\Carbon|null $graded_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\HomeworkAssignment $homeworkAssignment
 * @property-read \App\Models\Student $student
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkSubmission graded()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkSubmission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkSubmission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkSubmission pendingGrading()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkSubmission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkSubmission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkSubmission whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkSubmission whereGrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkSubmission whereGradedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkSubmission whereHomeworkAssignmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkSubmission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkSubmission whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkSubmission whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkSubmission whereSubmittedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkSubmission whereTeacherComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeworkSubmission whereUpdatedAt($value)
 */
	class HomeworkSubmission extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string $file_path
 * @property string $resource_type
 * @property int $uploaded_by
 * @property int|null $class_id
 * @property string|null $subject
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ClassModel|null $class
 * @property-read \App\Models\User $uploader
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningResource byType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningResource general()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningResource newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningResource newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningResource query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningResource whereClassId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningResource whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningResource whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningResource whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningResource whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningResource whereResourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningResource whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningResource whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningResource whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningResource whereUploadedBy($value)
 */
	class LearningResource extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $notification_type
 * @property bool $email_enabled
 * @property bool $sms_enabled
 * @property bool $in_app_enabled
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereEmailEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereInAppEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereNotificationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereSmsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationSetting whereUserId($value)
 */
	class NotificationSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $email
 * @property string $subject
 * @property string $body
 * @property string $status
 * @property \Illuminate\Support\Carbon $scheduled_at
 * @property int $attempts
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingEmail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingEmail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingEmail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingEmail readyToSend()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingEmail whereAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingEmail whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingEmail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingEmail whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingEmail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingEmail whereScheduledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingEmail whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingEmail whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingEmail whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingEmail whereUserId($value)
 */
	class PendingEmail extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $phone_number
 * @property string $message_type
 * @property string $message_content
 * @property string $status
 * @property \Illuminate\Support\Carbon $scheduled_at
 * @property int $attempts
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingSms newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingSms newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingSms query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingSms readyToSend()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingSms whereAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingSms whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingSms whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingSms whereMessageContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingSms whereMessageType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingSms wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingSms whereScheduledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingSms whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingSms whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingSms whereUserId($value)
 */
	class PendingSms extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $progress_sheet_id
 * @property int $student_id
 * @property string|null $performance
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ProgressSheet $progressSheet
 * @property-read \App\Models\Student $student
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProgressNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProgressNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProgressNote query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProgressNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProgressNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProgressNote whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProgressNote wherePerformance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProgressNote whereProgressSheetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProgressNote whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProgressNote whereUpdatedAt($value)
 */
	class ProgressNote extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $class_id
 * @property int|null $schedule_id
 * @property \Illuminate\Support\Carbon $date
 * @property string|null $objective Lesson objective
 * @property string|null $topic
 * @property int $teacher_id
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ClassModel $class
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\HomeworkAssignment> $homeworkAssignments
 * @property-read int|null $homework_assignments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProgressNote> $progressNotes
 * @property-read int|null $progress_notes_count
 * @property-read \App\Models\Schedule|null $schedule
 * @property-read \App\Models\User $teacher
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProgressSheet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProgressSheet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProgressSheet query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProgressSheet whereClassId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProgressSheet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProgressSheet whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProgressSheet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProgressSheet whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProgressSheet whereObjective($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProgressSheet whereScheduleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProgressSheet whereTeacherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProgressSheet whereTopic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProgressSheet whereUpdatedAt($value)
 */
	class ProgressSheet extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $class_id
 * @property string $day_of_week
 * @property \Illuminate\Support\Carbon $start_time
 * @property \Illuminate\Support\Carbon $end_time
 * @property bool $recurring
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attendance> $attendance
 * @property-read int|null $attendance_count
 * @property-read \App\Models\ClassModel $class
 * @property-read string $time_range
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProgressSheet> $progressSheets
 * @property-read int|null $progress_sheets_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule byDay($day)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereClassId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereDayOfWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereRecurring($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereUpdatedAt($value)
 */
	class Schedule extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $provider
 * @property string $api_key Encrypted
 * @property string $api_secret Encrypted
 * @property string|null $sender_id UK sender ID or phone number
 * @property numeric $credit_balance In GBP
 * @property numeric $low_balance_threshold In GBP
 * @property bool $is_active
 * @property int|null $daily_limit Max SMS per day
 * @property int|null $monthly_limit Max SMS per month
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsConfiguration newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsConfiguration newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsConfiguration query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsConfiguration whereApiKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsConfiguration whereApiSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsConfiguration whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsConfiguration whereCreditBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsConfiguration whereDailyLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsConfiguration whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsConfiguration whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsConfiguration whereLowBalanceThreshold($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsConfiguration whereMonthlyLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsConfiguration whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsConfiguration whereSenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsConfiguration whereUpdatedAt($value)
 */
	class SmsConfiguration extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $phone_number UK format: +44...
 * @property string $message_type
 * @property string $message_content
 * @property string $provider
 * @property string|null $provider_message_id Twilio SID or similar
 * @property string $status
 * @property string|null $failure_reason
 * @property numeric|null $cost In GBP per SMS
 * @property \Illuminate\Support\Carbon|null $sent_at
 * @property \Illuminate\Support\Carbon|null $delivered_at
 * @property int $retry_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsLog byDateRange($startDate, $endDate)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsLog delivered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsLog failed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsLog whereCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsLog whereDeliveredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsLog whereFailureReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsLog whereMessageContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsLog whereMessageType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsLog wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsLog whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsLog whereProviderMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsLog whereRetryCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsLog whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsLog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsLog whereUserId($value)
 */
	class SmsLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property \Illuminate\Support\Carbon $date_of_birth
 * @property int $parent_id
 * @property \Illuminate\Support\Carbon $enrollment_date
 * @property string $status
 * @property string|null $emergency_contact
 * @property string|null $emergency_phone
 * @property string|null $medical_info
 * @property string|null $profile_photo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attendance> $attendance
 * @property-read int|null $attendance_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ClassModel> $classes
 * @property-read int|null $classes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ClassEnrollment> $enrollments
 * @property-read int|null $enrollments_count
 * @property-read string $full_name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\HomeworkSubmission> $homeworkSubmissions
 * @property-read int|null $homework_submissions_count
 * @property-read \App\Models\User $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProgressNote> $progressNotes
 * @property-read int|null $progress_notes_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student byParent($parentId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereEmergencyContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereEmergencyPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereEnrollmentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereMedicalInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereProfilePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereUpdatedAt($value)
 */
	class Student extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string $type
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemSetting whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemSetting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemSetting whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemSetting whereValue($value)
 */
	class SystemSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $role User role in the system
 * @property string|null $phone UK format: +44...
 * @property string|null $profile_photo Path to user profile photo
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ActivityLog> $activityLogs
 * @property-read int|null $activity_logs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Student> $children
 * @property-read int|null $children_count
 * @property-read string $formatted_phone
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\NotificationSetting> $notificationSettings
 * @property-read int|null $notification_settings_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SmsLog> $smsLogs
 * @property-read int|null $sms_logs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ClassModel> $teachingClasses
 * @property-read int|null $teaching_classes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LearningResource> $uploadedResources
 * @property-read int|null $uploaded_resources_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereProfilePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 */
	class User extends \Eloquent {}
}

