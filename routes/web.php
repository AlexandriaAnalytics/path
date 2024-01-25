<?php

use App\Http\Controllers\CandidateController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\ExcelController;
use App\Models\Candidate;
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
Route::get('/download-candidate/{id}', 'DownloadController@downloadCandidateById');

Route::get('/prueba', function () {
    return view('example');
});

Route::get('/prueba/{id}', [CandidateController::class, 'show']);

Route::get('/users-excel',[ExcelController::class, 'export']);
Route::get('/excel/{id}', [ExcelController::class, 'exportById']);
// Route::get('/auth/login/candidate', LoginCand)

Route::get('/', [\App\Http\Controllers\WebController::class, 'index']);
Route::get('/candidate', fn(): string => 'Candidate Login')->name('candidate'); 
Route::post('/candidates/confirm', 'CandidateController@confirm')->name('candidates.confirm');

