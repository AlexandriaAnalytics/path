<?php

namespace App\Filament\Candidate\Pages;

use App\Enums\PaymentMethod;
use App\Models\Candidate;
use App\Models\Payment;
use App\Models\PaymentMethod as PaymentMethodModel;
use Carbon\Carbon;
use Carbon\Doctrine\CarbonType;
use DateTime;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Date;

class Payments extends Page implements HasForms
{
    public $candidate;
    private $country;
    public $candidate_payment_methods = [];
    public ?string $monetariUnitSymbol;
    public ?string $payment_method = null;
    public int $total_amount = 0;
    public ?bool $canApplyToDiscount = false;
    public int $instalment_number = 0;
    public ?DateTime $examDate;
    public $modules = [];

    public function __construct()
    {
        $this->candidate = Candidate::find(session('candidate')->id);
        $this->candidate_payment_methods = $this->candidate->student->region->paymentMethods()->pluck('name')->toArray();
        $this->country = $this->candidate->student->region->name;

        $this->total_amount += $this->candidate->total_amount;

        $this->monetariUnitSymbol = $this->candidate->getMonetaryString();

        $this->examDate = new Carbon('2024-11-03');
        $this->instalment_number = Carbon::now()->diffInMonths($this->examDate);
    }

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.candidate.pages.payments';


    public static function canAccess(): bool
    {
        return isset(session('candidate')->id);
    }

    public function mount()
    {
        abort_unless(static::canAccess(), 403);
    }

    public function paypalFinaciament()
    {
        return redirect()->route('payment.process.cuotas', ['payment_method' => 'paypal', 'amount_value' => $this->total_amount, 'cuotas' => $this->instalment_number]);
    }

    public function mercadoPagoFinanciament()
    {
        return redirect()->route('payment.process.cuotas', ['payment_method' => 'mercado_pago', 'amount_value' => $this->total_amount, 'cuotas' => $this->instalment_number]);
    }

    public function strypeFinanciament()
    {
        return redirect()->route('payment.process.cuotas', [ 
            'payment_method' => 'stripe',
            'amount_value' => $this->total_amount,
            'cuotas' => $this->instalment_number
        ]);
    }




    private  function renderDepositPayment()
    {
        return [
            Action::make('deposit_pay')
                ->label('make a deposit')
                ->form([
                    TextInput::make('total_amount')
                        ->default(fn () => ($this->candidate->total_amount))
                        ->prefix(fn () => $this->candidate->currency . ' $')
                        ->readOnly(),
                    TextInput::make('payment_id')
                        ->required(),
                    TextInput::make('paymeny_ticket_link')
                        ->required()

                ])
                ->action(function (array $data) {
                    Payment::create([
                        'payment_method' => 'deposit',
                        'payment_id' => $data['payment_id'],
                        'amount' => $data['total_amount'],
                        'status' => 'pending',
                        'candidate_id' => $this->candidate->id,
                        'currency' => $this->candidate->currency,
                        'paymeny_ticket_link' => $data['paymeny_ticket_link']
                    ]);

                    $this->candidate->update(['status' => 'processing', 'payment_ticker_link' => $data['paymeny_ticket_link']]);
                })
        ];
    }

    private function renderPaypalFinancing()
    {
        return  [
            Action::make('paypal_financing')
                ->label('Financing with PayPal (' . $this->instalment_number . ' instalments)')
                ->icon('heroicon-o-currency-dollar')
                ->action(fn () => $this->paypalFinaciament()),
        ];
    }

    private function renderMercadoPagoFinancing()
    {
        return  [
            Action::make('MP_financing')
                ->label('Financing with Mercado Pago (' . $this->instalment_number . ' instalments)')
                ->icon('heroicon-o-currency-dollar')
                ->action(fn () => $this->mercadoPagoFinanciament()),
        ];
    }

    private  function currencyInListOfCurrencies(PaymentMethod $paymentMethodEnum)
    {
        return in_array(str_replace('_', ' ', strtolower($paymentMethodEnum->value)), array_map(fn ($item) => strtolower($item), $this->candidate->student->region->paymentMethods()->pluck('name')->toArray()));
    }


    protected function getActions(): array {
        $actions = [];

        $actions += $this->renderDepositPayment();
        $instituteCategory = $this->candidate->student->institute->instituteType()->first()->name;


       if ( $this->candidate->student->institute->installment_plans)  // puedo acceder a cuotas
            if ($this->currencyInListOfCurrencies(PaymentMethod::PAYPAL))
                $actions += $this->renderPaypalFinancing();
            //else if ($this->currencyInListOfCurrencies(PaymentMethod::MERCADO_PAGO))
            $actions += $this->renderMercadoPagoFinancing();
            

        return [Action::make('MP_financing')
                ->label('Financing with Mercado Pago (' . $this->instalment_number . ' instalments)')
                ->icon('heroicon-o-currency-dollar')
                //->disabled(fn () => $this->candidate->student->email == null)
                ->action(fn () => $this->mercadoPagoFinanciament()),  Action::make('stripe_financing')
                ->label('Financing with stripe (' . $this->instalment_number . ' instalments)')
                ->icon('heroicon-o-currency-dollar')
                ->action(fn () => $this->strypeFinanciament()),
        ];
    }

    public function form(Form $form): Form
    {
        $form->schema([
            Select::make('payment_method')
                ->label('Payment method')
                ->placeholder('Select a payment method')
                ->native(false)
                ->options($this->candidate->student->region->paymentMethods()->pluck('name', 'slug')->toArray())
        ]);

        return $form;
    }

    public function selectPaymentMethod()
    {
        $payment_method_selected = $this->form->getState()['payment_method'];
    
        return redirect()
            ->route(
                'payment.process',
                [
                    'payment_method' => $payment_method_selected,
                ]
            );
    }


    protected function getFormActions()
    {
        return [
            Action::make('Submit')
                ->submitTo('submit')
                ->message('Payment method updated successfully.')
                ->successToast(),
        ];
    }
}
