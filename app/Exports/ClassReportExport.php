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

class ClassReportExport implements WithMultipleSheets
{
    protected $class;
    protected $studentStats;
    protected $attendanceStats;
    protected $homeworkStats;

    public function __construct($class, $studentStats, $attendanceStats, $homeworkStats)
    {
        $this->class = $class;
        $this->studentStats = $studentStats;
        $this->attendanceStats = $attendanceStats;
        $this->homeworkStats = $homeworkStats;
    }

    public function sheets(): array
    {
        return [
            new ClassSummarySheet($this->class, $this->attendanceStats, $this->homeworkStats),
            new ClassStudentsSheet($this->studentStats),
        ];
    }
}

class ClassSummarySheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected $class;
    protected $attendanceStats;
    protected $homeworkStats;

    public function __construct($class, $attendanceStats, $homeworkStats)
    {
        $this->class = $class;
        $this->attendanceStats = $attendanceStats;
        $this->homeworkStats = $homeworkStats;
    }

    public function collection()
    {
        return collect([
            ['Class Information', ''],
            ['Class Name', $this->class->name],
            ['Teacher', $this->class->teacher->name ?? 'N/A'],
            ['Total Students', $this->class->students->count()],
            ['', ''],
            ['Attendance Statistics', ''],
            ['Attendance Rate', $this->attendanceStats['rate'] . '%'],
            ['Total Records', $this->attendanceStats['total']],
            ['Present', $this->attendanceStats['present']],
            ['Absent', $this->attendanceStats['absent']],
            ['Late', $this->attendanceStats['late']],
            ['', ''],
            ['Homework Statistics', ''],
            ['Total Assignments', $this->homeworkStats['total_assignments']],
            ['Average Completion', $this->homeworkStats['average_completion'] . '%'],
        ]);
    }

    public function headings(): array
    {
        return ['Class Performance Report'];
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '667eea']], 'font' => ['color' => ['rgb' => 'FFFFFF']]],
        ];
    }
}

class ClassStudentsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    protected $studentStats;

    public function __construct($studentStats)
    {
        $this->studentStats = $studentStats;
    }

    public function collection()
    {
        return $this->studentStats;
    }

    public function headings(): array
    {
        return ['Student Name', 'Student ID', 'Attendance Rate', 'Homework Completion', 'Average Grade'];
    }

    public function map($stat): array
    {
        return [
            $stat['student']->full_name,
            $stat['student']->id,
            $stat['attendance_rate'] . '%',
            $stat['homework_rate'] . '%',
            $stat['average_grade'],
        ];
    }

    public function title(): string
    {
        return 'Student Performance';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '667eea']], 'font' => ['color' => ['rgb' => 'FFFFFF']]],
        ];
    }
}