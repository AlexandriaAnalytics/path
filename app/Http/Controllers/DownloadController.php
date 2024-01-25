<?php
namespace App\Http\Controllers;

use App\Models\Candidate;
use Illuminate\Http\Request;
use PDF;

class DownloadController extends Controller {
    public function downloadCandidate() {
        $data = []; 
        $html = view('example', $data)->render(); 

        $pdf = PDF::loadHTML($html);
        return $pdf->download('downloaded_pdf.pdf');
    }

    public function downloadCandidateById($id) {
        $candidate = Candidate::find($id);

        if (!$candidate) {
            abort(404, 'Candidate not found');
        }

        $data = [
            'candidate' => $candidate,
        ];

        $html = view('example', $data)->render();

        $pdf = PDF::loadHTML($html);
        return $pdf->download('downloaded_pdf_' . $id . '.pdf');
    }

} 