<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StudentReportExport implements WithMultipleSheets
{
    protected $student;
    protected $attendanceData;
    protected $homeworkData;
    protected $progressData;

    public function __construct($student, $attendanceData, $homeworkData, $progressData)
    {
        $this->student = $student;
        $this->attendanceData = $attendanceData;
        $this->homeworkData = $homeworkData;
        $this->progressData = $progressData;
    }

    public function sheets(): array
    {
        return [
            new StudentSummarySheet($this->student, $this->attendanceData, $this->homeworkData),
            new StudentAttendanceSheet($this->attendanceData),
            new StudentHomeworkSheet($this->homeworkData),
            new StudentProgressSheet($this->progressData),
        ];
    }
}

class StudentSummarySheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected $student;
    protected $attendanceData;
    protected $homeworkData;

    public function __construct($student, $attendanceData, $homeworkData)
    {
        $this->student = $student;
        $this->attendanceData = $attendanceData;
        $this->homeworkData = $homeworkData;
    }

    public function collection()
    {
        return collect([
            ['Student Information', ''],
            ['Name', $this->student->full_name],
            ['Student ID', $this->student->id],
            ['Parent', $this->student->parent->name ?? 'N/A'],
            ['Status', ucfirst($this->student->status)],
            ['', ''],
            ['Performance Summary', ''],
            ['Attendance Rate', $this->attendanceData['rate'] . '%'],
            ['Total Attendance Records', $this->attendanceData['total']],
            ['Present', $this->attendanceData['present']],
            ['Absent', $this->attendanceData['absent']],
            ['Late', $this->attendanceData['late']],
            ['', ''],
            ['Homework Performance', ''],
            ['Homework Completion Rate', $this->homeworkData['rate'] . '%'],
            ['Total Assignments', $this->homeworkData['total']],
            ['Submitted', $this->homeworkData['submitted']],
            ['Graded', $this->homeworkData['graded']],
            ['Average Grade', $this->homeworkData['average_grade']],
        ]);
    }

    public function headings(): array
    {
        return ['Student Performance Report'];
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '667eea']], 'font' => ['color' => ['rgb' => 'FFFFFF']]],
            'A2' => ['font' => ['bold' => true, 'size' => 12]],
            'A7' => ['font' => ['bold' => true, 'size' => 12]],
            'A14' => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}

class StudentAttendanceSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    protected $attendanceData;

    public function __construct($attendanceData)
    {
        $this->attendanceData = $attendanceData;
    }

    public function collection()
    {
        return $this->attendanceData['records'];
    }

    public function headings(): array
    {
        return ['Date', 'Class', 'Status', 'Notes'];
    }

    public function map($attendance): array
    {
        return [
            $attendance->date->format('d/m/Y'),
            $attendance->class->name,
            ucfirst($attendance->status),
            $attendance->notes ?? 'N/A',
        ];
    }

    public function title(): string
    {
        return 'Attendance';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '667eea']], 'font' => ['color' => ['rgb' => 'FFFFFF']]],
        ];
    }
}

class StudentHomeworkSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    protected $homeworkData;

    public function __construct($homeworkData)
    {
        $this->homeworkData = $homeworkData;
    }

    public function collection()
    {
        return $this->homeworkData['submissions'];
    }

    public function headings(): array
    {
        return ['Assignment', 'Class', 'Due Date', 'Submitted', 'Status', 'Grade', 'Comments'];
    }

    public function map($submission): array
    {
        return [
            $submission->homeworkAssignment->title,
            $submission->homeworkAssignment->class->name,
            $submission->homeworkAssignment->due_date->format('d/m/Y'),
            $submission->submitted_date ? $submission->submitted_date->format('d/m/Y') : 'Not Submitted',
            ucfirst($submission->status),
            $submission->grade ?? 'Not Graded',
            $submission->teacher_comments ?? 'N/A',
        ];
    }

    public function title(): string
    {
        return 'Homework';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '667eea']], 'font' => ['color' => ['rgb' => 'FFFFFF']]],
        ];
    }
}

class StudentProgressSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    protected $progressData;

    public function __construct($progressData)
    {
        $this->progressData = $progressData;
    }

    public function collection()
    {
        return $this->progressData['notes'];
    }

    public function headings(): array
    {
        return ['Date', 'Class', 'Topic', 'Performance', 'Notes'];
    }

    public function map($note): array
    {
        return [
            $note->progressSheet->date->format('d/m/Y'),
            $note->progressSheet->class->name,
            $note->progressSheet->topic,
            ucfirst($note->performance),
            $note->notes,
        ];
    }

    public function title(): string
    {
        return 'Progress Notes';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '667eea']], 'font' => ['color' => ['rgb' => 'FFFFFF']]],
        ];
    }
}