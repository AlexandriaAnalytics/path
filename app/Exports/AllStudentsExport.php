<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AllStudentsExport implements FromCollection, WithHeadings, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Student::select(
            'students.id as student_id',
            'students.names',
            'students.surnames',
            'institutes.name as institute_name'
        )
            ->join('institutes', 'institutes.id', '=', 'students.institute_id')
            ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return ['Student Number', 'First Name', 'Last Name', 'Institute'];
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


        $sheet->getColumnDimension('B')->setWidth(35);

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


        $sheet->getColumnDimension('D')->setWidth(35);


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
