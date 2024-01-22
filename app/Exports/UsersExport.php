<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Facades\Excel;


class UsersExport implements FromCollection
{
  /**
   * @return \Illuminate\Support\Collection
   */
  public function collection()
  {
    return User::select('name', 'email')->get();
  }

  public function createSheet($excel)
  {
    $sheet = $excel->sheet('Usuarios');

    $sheet->setTitle('Usuarios');

    $sheet->setCellValue('A1', 'Name');
    $sheet->setCellValue('B1', 'Mail');

    $sheet->getStyle('A1:B1')->setFont([
      'bold' => true,
  ]);

    foreach ($this->collection() as $user) {
      $sheet->appendRow([
        $user->name,
        $user->email,
      ]);
    }

    return $sheet;
  }
}
