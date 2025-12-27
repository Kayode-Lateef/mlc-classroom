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
use PhpOffice\PhpSpreadsheet\Style\Color;

class AttendanceReportExport implements WithMultipleSheets
{
    protected $data;
    protected $stats;
    protected $dateFrom;
    protected $dateTo;

    public function __construct($data, $stats, $dateFrom, $dateTo)
    {
        $this->data = $data;
        $this->stats = $stats;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function sheets(): array
    {
        return [
            new AttendanceSummarySheet($this->stats, $this->dateFrom, $this->dateTo),
            new AttendanceDetailsSheet($this->data),
        ];
    }
}

class AttendanceSummarySheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected $stats;
    protected $dateFrom;
    protected $dateTo;

    public function __construct($stats, $dateFrom, $dateTo)
    {
        $this->stats = $stats;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function collection()
    {
        return collect([
            ['Metric', 'Value'],
            ['Date Range', $this->dateFrom . ' to ' . $this->dateTo],
            ['Total Records', $this->stats['total']],
            ['Present', $this->stats['present']],
            ['Absent', $this->stats['absent']],
            ['Late', $this->stats['late']],
            ['Attendance Rate', $this->stats['attendance_rate'] . '%'],
        ]);
    }

    public function headings(): array
    {
        return ['Attendance Report Summary'];
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '667eea']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF']],
            ],
            'A3:B3' => ['font' => ['bold' => true]],
        ];
    }
}

class AttendanceDetailsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Student Name',
            'Student ID',
            'Class',
            'Status',
            'Notes',
            'Marked By',
            'Marked At'
        ];
    }

    public function map($attendance): array
    {
        return [
            $attendance->date->format('d/m/Y'),
            $attendance->student->full_name,
            $attendance->student->id,
            $attendance->class->name,
            ucfirst($attendance->status),
            $attendance->notes ?? 'N/A',
            $attendance->markedBy->name ?? 'System',
            $attendance->created_at->format('d/m/Y H:i'),
        ];
    }

    public function title(): string
    {
        return 'Details';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '667eea']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF']],
            ],
        ];
    }
}