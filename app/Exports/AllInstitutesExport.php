<?php

namespace App\Exports;

use App\Models\Institute;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AllInstitutesExport implements FromCollection, WithHeadings, WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Institute::select(
            'institutes.id as institute_id',
            'institutes.name as institute_name',
            'users.name as owner',
            'institute_types.name as membership',
            'institutes.email',
            'institutes.phone',
            'institutes.files_url'
        )->join('users', 'institutes.owner_id', '=', 'users.id')
         ->join('institute_types', 'institutes.institute_type_id', '=', 'institute_types.id')
         ->get()
         ->map(function ($institute) {
             return [
                 'Institute id' => $institute->institute_id,
                 'Institute Name' => $institute->institute_name,
                 'Owner' => $institute->owner,
                 'Membership' => $institute->membership,
                 'Email' => $institute->email,
                 'Phone' => $institute->phone,
                 'File URL' => $institute->files_url,
             ];
         });
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return ['Institute id', 'Institute Name', 'Owner', 'Membership', 'Email', 'Phone', 'File URL'];
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '000000'],
            ],
        ]);

        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(40);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(70);

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

        // Centrar los números en la columna de teléfono (columna F)
        $sheet->getStyle('F')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
}
