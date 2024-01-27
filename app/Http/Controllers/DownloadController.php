<?php
namespace App\Http\Controllers;

use App\Models\Candidate;
use Illuminate\Http\Request;
use PDF;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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

    public function generateQrCode($id) {
        $candidate = Candidate::find($id);

        if (!$candidate) {
            abort(404, 'Candidate not found');
        }

        $qrCode = QrCode::size(150)->generate(route('candidate.view', ['id' => $id]));

        $data = [
            'candidate' => $candidate,
            'qrCode' => $qrCode,
        ];
        $html = view('example_with_qr', $data)->render();

        $pdf = PDF::loadHTML($html);

        return $pdf->download('downloaded_pdf_with_qr_' . $id . '.pdf');
    }

} 