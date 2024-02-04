<?php

namespace App\Exports;

use App\Models\Candidate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CandidateByIdExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    public function __construct(
        private Collection $candidate_ids,
    ) {
    }

    public function query()
    {
        return Candidate::query()
            ->whereIn('id', $this->candidate_ids)
            ->with('student')
            ->with('exam');
    }

    public function headings(): array
    {
        return [
            'Candidate Number',
            'Full Name',
            'Session Name',
            'Status'
        ];
    }

    public function map($candidate): array
    {
        $fullName = $candidate->student->first_name . ' ' . $candidate->student->surnames;

        return [
            $candidate->id,
            $fullName,
            $candidate->exam->session_name,
            $candidate->status->value
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

        $sheet->getColumnDimension('A')->setWidth(15);
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

        $sheet->getStyle('C1')->applyFromArray([
            'font' => [
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '000000'],
            ],
        ]);

        $sheet->getColumnDimension('C')->setWidth(40);

        $sheet->getStyle('D1')->applyFromArray([
            'font' => [
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '000000'],
            ],
        ]);

        $sheet->getColumnDimension('D')->setWidth(40);

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
