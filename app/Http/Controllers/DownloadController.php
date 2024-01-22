<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;

class DownloadController extends Controller {
    public function downloadCandidate() {
    $data = []; 
    $html = view('example', $data)->render(); 

    $pdf = PDF::loadHTML($html);
    return $pdf->download('downloaded_pdf.pdf');
    }
} 