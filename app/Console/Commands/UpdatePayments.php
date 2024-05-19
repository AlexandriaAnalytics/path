<?php

namespace App\Console\Commands;

use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class UpdatePayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $payments = Payment::distinct()->pluck('payment_id');
        foreach ($payments as $payment) {
            $response = Http::withHeaders([
                'Accept' => '*/*',
                'User-Agent' => 'Thunder Client (https://www.thunderclient.com)',
                'Authorization' => 'Bearer ' . config('mercadopago.access_token'),
            ])
                ->get('https://api.mercadopago.com/preapproval/' . $payment);
            if (json_decode($response->body())->status == "authorized") {
                Payment::where('payment_id', $payment)->where('current_installment', json_decode($response->body())->summarized->charged_quantity)->where('status', 'pending')->update(['status' => 'approved']);
            } elseif (json_decode($response->body())->status == "cancelled") {
                Payment::where('payment_id', $payment)->update(['status' => 'pending']);
            }
        }

        /* Payment::where('payment_id', '123')->where('current_installment', 1)->update(['suscription_code' => json_decode($response->body())->summarized->quotas]); */
    }
}
