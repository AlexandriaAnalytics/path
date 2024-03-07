<?php

namespace App\Filament\Candidate\Pages;

use App\Enums\Country;
use App\Enums\PaymentMethod;
use App\Enums\UserStatus;
use App\Models\Candidate;
use App\Models\Country as ModelsCountry;
use App\Models\Payment;
use App\Models\PaymentMethod as PaymentMethodModel;
use Carbon\Carbon;
use Carbon\Doctrine\CarbonType;
use DateTime;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
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

    private function renderTransference(bool $hidde)
    {
        return  Action::make('deposit')
            ->hidden(!$hidde)
            ->label('Transferency')
            ->form([
                TextInput::make('amount')
                    ->default(fn () => $this->candidate->total_amount)
                    ->prefix(fn () => $this->candidate->currency)
                    ->disabled(true),

                TextInput::make('link_to_ticket')
                    ->required()
            ])
            ->action(function (array $data) {
                Payment::create([
                    'payment_id' => 't-' . Carbon::now()->timestamp . rand(1000, 10000),
                    'currency' => $this->candidate->currency,
                    'amount' => $this->candidate->total_amount,
                    'candidate_id' => $this->candidate->id,
                    'link_to_ticket' => $data['link_to_ticket'],
                    'current_period' => Carbon::now()->day(1),
                    'paid_date' => Carbon::now(),
                    'payment_method' => 'transcerence',
                    'status' => 'processing payment',
                ]);
                Candidate::find($this->candidate->id)->update(['status' => UserStatus::Processing_payment]);
            });
    }

    private function renderPaypalFinancing(bool $hidde)
    {
        return
            Action::make('paypal_financing')
            ->label('Financing with PayPal (' . $this->instalment_number . ' instalments)')
            ->icon('heroicon-o-currency-dollar')
            ->action(fn () => $this->paypalFinaciament())
            ->hidden(!$hidde);
    }

    private function renderMercadoPagoFinancing(bool $hidde)
    {
        return
            Action::make('MP_financing')
            ->label('Financing with Mercado Pago (' . $this->instalment_number . ' instalments)')
            ->icon('heroicon-o-currency-dollar')
            ->action(fn () => $this->mercadoPagoFinanciament())
            ->hidden(!$hidde);
    }

    private function renderStripeFinancing(bool $hidde)
    {
        return
            Action::make('stripe_financing')
            ->label('Financing with stripe (' . $this->instalment_number . ' instalments)')
            ->icon('heroicon-o-currency-dollar')
            ->action(fn () => $this->strypeFinanciament())
            ->hidden(!$hidde);
    }

    protected function getActions(): array
    {
        $paymentMethodsAvailable = ModelsCountry::all()->where('monetary_unit', 'ARS')->first()->pyMethods()->get()->pluck('slug')->toArray();
        return [
            $this->renderTransference(false && $this->candidate->status == 'unpaid'),
            $this->renderPaypalFinancing(
                Candidate::first()->student->institute->installment_plans
                    && in_array(PaymentMethod::MERCADO_PAGO->value, $paymentMethodsAvailable)
                    && $this->candidate->status == 'unpaid'
            ),
            $this->renderStripeFinancing(
                Candidate::first()->student->institute->installment_plans
                    && in_array(PaymentMethod::MERCADO_PAGO->value, $paymentMethodsAvailable)
                    && $this->candidate->status == 'unpaid'
            ),
            $this->renderMercadoPagoFinancing(false && $this->candidate->status == 'unpaid')
        ];
    }

    public function form(Form $form): Form
    {

        $form->schema([
            Select::make('payment_method')
                ->label('Payment method')
                ->placeholder('Select a payment method')
                ->native(false)
                ->options(function () {
                    $options = $this->candidate->student->region->paymentMethods()->pluck('name', 'slug')->toArray();
                    $options = ['transfer' => 'Transfer'] + $options;
                    return $options;
                }),
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
