<?php

namespace App\Exports;

use App\Models\Candidate;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CandidateByIdExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function collection()
    {
        return Candidate::where('id', $this->id)->get();
    }

    public function headings(): array
    {
        return [
            'Full Name',
            'Session Name',
        ];
    }

    public function map($candidate): array
    {
        $fullName = $candidate->student->first_name . ' ' . $candidate->student->last_name;

        return [
            $fullName,
            $candidate->exam->session_name,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '000000'],
            ],
        ]);

        $sheet->getColumnDimension('A')->setWidth(30);

        $sheet->getStyle('B1')->applyFromArray([
            'font' => [
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '000000'],
            ],
        ]);

        $sheet->getColumnDimension('B')->setWidth(40);

        $lastRow = $sheet->getHighestDataRow();
        $lastCol = $sheet->getHighestDataColumn();

        $range = 'A1:' . $lastCol . $lastRow;
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
    }
}
