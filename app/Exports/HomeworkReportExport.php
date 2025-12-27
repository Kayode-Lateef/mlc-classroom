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

class HomeworkReportExport implements WithMultipleSheets
{
    protected $assignments;
    protected $stats;

    public function __construct($assignments, $stats)
    {
        $this->assignments = $assignments;
        $this->stats = $stats;
    }

    public function sheets(): array
    {
        return [
            new HomeworkSummarySheet($this->stats),
            new HomeworkDetailsSheet($this->assignments),
        ];
    }
}

class HomeworkSummarySheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected $stats;

    public function __construct($stats)
    {
        $this->stats = $stats;
    }

    public function collection()
    {
        return collect([
            ['Metric', 'Value'],
            ['Total Assignments', $this->stats['total_assignments']],
            ['Average Completion', $this->stats['average_completion'] . '%'],
            ['Grading Pending', $this->stats['grading_pending']],
            ['Overdue Assignments', $this->stats['overdue']],
        ]);
    }

    public function headings(): array
    {
        return ['Homework Report Summary'];
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '667eea']], 'font' => ['color' => ['rgb' => 'FFFFFF']]],
            'A2:B2' => ['font' => ['bold' => true]],
        ];
    }
}

class HomeworkDetailsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    protected $assignments;

    public function __construct($assignments)
    {
        $this->assignments = $assignments;
    }

    public function collection()
    {
        return $this->assignments;
    }

    public function headings(): array
    {
        return [
            'Assignment',
            'Class',
            'Teacher',
            'Assigned Date',
            'Due Date',
            'Total Students',
            'Submitted',
            'Graded',
            'Completion Rate',
            'Average Grade'
        ];
    }

    public function map($homework): array
    {
        return [
            $homework->title,
            $homework->class->name,
            $homework->teacher->name,
            $homework->assigned_date->format('d/m/Y'),
            $homework->due_date->format('d/m/Y'),
            $homework->total_students ?? 0,
            $homework->submitted_count ?? 0,
            $homework->graded_count ?? 0,
            $homework->completion_rate . '%',
            $homework->average_grade ?? 'N/A',
        ];
    }

    public function title(): string
    {
        return 'Assignments';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '667eea']], 'font' => ['color' => ['rgb' => 'FFFFFF']]],
        ];
    }
}