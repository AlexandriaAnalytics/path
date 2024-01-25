<?php
namespace App\Http\Controllers;

use App\Exports\CandidateByIdExport;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;

class ExcelController extends Controller {
    public function export(){
        return Excel::download(new UsersExport, 'candidate.xlsx');
    }
    public function exportById($id)
    {
        return Excel::download(new CandidateByIdExport($id), 'candidate.xlsx');
    }
}