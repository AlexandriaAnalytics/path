<?php

use App\Http\Controllers\CandidateController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\ExcelController;
use App\Livewire\LoginCandidate;
use App\Models\Candidate;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WebHookPaymentsController;

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

Route::get('/candidate/template/{id}', [CandidateController::class, 'show']);

Route::get('/users-excel', [ExcelController::class, 'export']);
Route::get('/students-excel', [ExcelController::class, 'exportAllStudents']);
Route::get('/members-excel', [ExcelController::class, 'exportAllMembers']);
Route::get('/excel/{id}', [ExcelController::class, 'exportById']);

Route::get('/', [\App\Http\Controllers\WebController::class, 'index']);
Route::get('/candidate/login', LoginCandidate::class)->name('candidate.login');

Route::get('/candidate/logout', function () {
    //clean all session
    session()->forget(['candidate']);
    session()->flush();
    return redirect()->route('candidate.login');
})->name('candidate.logout');

Route::get('/pdf/candidate/{id}', function ($id) {
    $candidate = Candidate::find($id);

    return view('pdf.candidate', ['candidate' => $candidate]);
})->name('candidate.pdf');


// Payment routes for all payment methodss
Route::get('/payment/pay', [PaymentController::class, 'createTransaction'])->name('payment.create');
Route::get('/payment/process', [PaymentController::class, 'processTransaction'])->name('payment.process');
Route::get('/payment/process/cuotas', [PaymentController::class, 'processTransactionCuotas'])->name('payment.process.cuotas');


Route::get('/payment/paypal/success', [PaymentController::class, 'paypalSuccessTransaction'])->name('payment.paypal.success');
Route::get('/payment/paypal/canceled', [PaymentController::class, 'paypalCancelTransaction'])->name('payment.paypal.cancel');
Route::get('/payment/mp/success', [PaymentController::class, 'mpSuccessTransaction'])->name('payment.mp.success');
Route::get('/payment/mp/canceled', [PaymentController::class, 'mpCancelTransaction'])->name('payment.mp.cancel');

Route::get('/payment/webhook/mp', [PaymentController::class, 'mercadopagoNotificationURL'])->name('payment.mp.webhook');

// Payment webhooks
Route::post('/payment/webhook/mp', [WebHookPaymentsController::class, 'mercadopagoWebhook'])->name('payment.mercadopago.webhook');
Route::post('/payment/webhook/paypal', [WebHookPaymentsController::class, 'paypalWebhook'])->name('payment.paypal.webhook');
Route::post('/payment/webhook/stripe', [WebHookPaymentsController::class, 'stripeWebhook'])->name('payment.stripe.webhook');
