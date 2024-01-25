<?php

use App\Http\Controllers\CandidateController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\ExcelController;
use Illuminate\Support\Facades\Route;

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

Route::get('/prueba', function () {
    return view('example');
});

Route::get('/users-excel',[ExcelController::class, 'export']);
// Route::get('/auth/login/candidate', LoginCand)

Route::get('/', [\App\Http\Controllers\WebController::class, 'index']);
Route::get('/candidate', fn(): string => 'Candidate Login')->name('candidate'); 
Route::post('/candidates/confirm', [CandidateController::class, 'confirm'])->name('candidates.confirm');

Route::post('management/auth/logout', function () {
    if (session('impersonator_id')) {
        auth()->loginUsingId(session('impersonator_id'));

        session()->forget('impersonator_id');

        return redirect()->route('filament.admin.pages.dashboard');
    }

    return redirect()->route('filament.management.auth.logout');
})->name('auth.logout');
