<?php

namespace App\Exports;

use App\Models\Candidate;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AllCandidateExport implements FromCollection, WithHeadings, WithStyles
{
    /**
     * @return Collection
     */
    public function collection()
    {
        return Candidate::select(
            'candidates.id as candidate_id',
            'candidates.status as candidate_status',
            'students.first_name',
            'students.last_name',
            'exams.session_name as exam_session',
        )
            ->join('students', 'students.id', '=', 'candidates.student_id')
            ->join('exams', 'exams.id', '=', 'candidates.exam_id')
            ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return ['Candidate Number', 'Status','First Name', 'Last Name', 'Exam Session'];
    }

    /**
     * @param Worksheet $sheet
     */
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

        $sheet->getColumnDimension('A')->setWidth(20);


        $sheet->getStyle('B1')->applyFromArray([
            'font' => [
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '000000'],
            ],
        ]);


        $sheet->getColumnDimension('B')->setWidth(20);

        $sheet->getStyle('C1')->applyFromArray([
            'font' => [
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '000000'],
            ],
        ]);

        $sheet->getColumnDimension('C')->setWidth(35);

        $sheet->getStyle('D1')->applyFromArray([
            'font' => [
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '000000'],
            ],
        ]);


        $sheet->getColumnDimension('E')->setWidth(35);

        $sheet->getStyle('E1')->applyFromArray([
            'font' => [
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '000000'],
            ],
        ]);


        $sheet->getColumnDimension('D')->setWidth(30);


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
