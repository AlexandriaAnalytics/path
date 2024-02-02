<?php
namespace App\Http\Controllers;

use App\Exports\AllCandidateExport;
use App\Exports\AllInstitutesExport;
use App\Exports\AllStudentsExport;
use App\Exports\CandidateByIdExport;
use Maatwebsite\Excel\Facades\Excel;

class ExcelController extends Controller {
    public function export(){
        return Excel::download(new AllCandidateExport, 'candidate.xlsx');
    }
    public function exportById($id)
    {
        return Excel::download(new CandidateByIdExport($id), 'candidate.xlsx');
    }

    public function exportAllStudents() {
        return Excel::download(new AllStudentsExport, 'students.xlsx');
    }

    public function exportAllMembers() {
        return Excel::download(new AllInstitutesExport, 'members.xlsx');
    }
}