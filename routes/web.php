<?php

use App\Http\Controllers\CandidateController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\ExcelController;
use App\Livewire\LoginCandidate;
use App\Models\Candidate;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Row;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/', function () {
    return view('welcome');
});

Route::get('/download/candidate',  [DownloadController::class, 'downloadCandidate']);
Route::get('/download-candidate/{id}', [DownloadController::class, 'downloadCandidateById'])->name('candidate.download-pdf');
Route::get('/view-qr/{id}', [DownloadController::class, 'generateQrCode'])->name('candidate.view');

Route::get('/prueba', function () {
    return view('example');
});

Route::get('/prueba/{id}', [CandidateController::class, 'show']);

Route::get('/users-excel', [ExcelController::class, 'export']);
Route::get('/excel/{id}', [ExcelController::class, 'exportById']);
// Route::get('/auth/login/candidate', LoginCand)

Route::get('/', [\App\Http\Controllers\WebController::class, 'index']);
Route::get('/candidate/login', LoginCandidate::class)->name('candidate.login');

Route::get('/candidate/logout', function(){
    //clean all session
    session()->flush();
    return redirect()->route('candidate.login');
})->name('candidate.logout');


Route::post('management/auth/logout', function () {
    if (session('impersonator_id')) {
        auth()->loginUsingId(session('impersonator_id'));

        session()->forget('impersonator_id');

        return redirect()->route('filament.admin.pages.dashboard');
    }

    return redirect()->route('filament.management.auth.logout');
})->name('auth.logout');
