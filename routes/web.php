<?php

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