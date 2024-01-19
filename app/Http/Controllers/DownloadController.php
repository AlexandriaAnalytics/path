<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;

class DownloadController extends Controller {
    public function downloadCandidate() {
    $data = []; // Pass any data needed for the Blade template
    $html = view('example', $data)->render(); // Render the Blade template

    $pdf = PDF::loadHTML($html);
    return $pdf->download('downloaded_pdf.pdf');
    }
} 