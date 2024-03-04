<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use Illuminate\Http\Request;
use PDF;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DownloadController extends Controller
{
    public function downloadCandidate()
    {
        $data = [];
        $html = view('candidate-pdf', $data)->render();

        $pdf = PDF::loadHTML($html);
        return $pdf->download('downloaded_pdf.pdf');
    }

    public function downloadCandidateById($id)
    {
        $candidate = Candidate::find($id);

        if (!$candidate) {
            abort(404, 'Candidate not found');
        }

        $data = [
            'candidate' => $candidate,
        ];

        $html = view('candidate-pdf', $data)->render();

        $pdf = PDF::loadHTML($html);
        return $pdf->stream('downloaded_pdf_' . $id . '.pdf');
    }

    public function generateQrCode($id)
    {
        $candidate = Candidate::find($id);

        if (!$candidate) {
            abort(404, 'Candidate not found');
        }

        $qrCode = QrCode::size(100)->generate(route('candidate.view', ['id' => $id]));

        $data = [
            'candidate' => $candidate,
            'qrCode' => $qrCode,
        ];

        $pdf = PDF::loadHTML(view('qrCandidate', $data)->render());

        return $pdf->stream('downloaded_pdf_with_qr_' . $id . '.pdf');
    }
}
