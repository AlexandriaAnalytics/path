<?php

namespace App\Filament\Candidate\Pages;

use App\Enums\PaymentMethod;
use Carbon\Carbon;
use Carbon\Doctrine\CarbonType;
use DateTime;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
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
        $this->candidate = \App\Models\Candidate::find(session('candidate')->id);
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

    public function payWithPaypal3Cuotas()
    {
        return redirect()->route('payment.process.cuotas', ['payment_method' => 'paypal', 'amount_value' => $this->total_amount, 'cuotas' => $this->instalment_number]);
    }

    public function payWithMercadoPago()
    {
        return redirect()->route('payment.process.cuotas', ['payment_method' => 'mercado_pago', 'amount_,value' => $this->total_amount, 'cuotas' => $this->instalment_number]);
    }

    protected function getActions(): array
    {
        $actions = [];
        if($this->candidate->student->institute->instituteType()->first()->name == 'Premium Exam Centre'){
            if(
                in_array(PaymentMethod::PAYPAL->value,$this->candidate_payment_methods)){
                $actions = array_merge($actions, 
                [
                    Action::make('paypal_financing')
                        ->label('financing in paypal ' . $this->instalment_number . ' instalments')
                        ->icon('heroicon-o-currency-dollar')
                        ->action(fn () => $this->payWithPaypal3Cuotas()),
                ]
                );
            }else if(in_array( ucwords(str_replace('_', ' ', PaymentMethod::MERCADO_PAGO->value)) , $this->candidate_payment_methods)){
                $actions = array_merge($actions, [
                    Action::make('MP_financing')
                        ->label('financing in Mercado pago ' . $this->instalment_number . ' instalments')
                        ->icon('heroicon-o-currency-dollar')
                        ->action(fn () => $this->payWithPaypal3Cuotas()),
                    ]);
            }
        }

        return $actions;
    }

    public function form(Form $form): Form
    {
        // dd($this->candidate->modules->pluck('name')->toArray());
        $form->schema([
            Select::make('payment_method')
                ->label('Payment method')
                ->placeholder('Select a payment method')
                ->native(false)
                ->options($this->candidate_payment_methods)
            // ->options($this->candidate->student->region->paymentMethods()->pluck('name', 'slug')->toArray())
        ]);

        return $form;
    }

    public function selectPaymentMethod()
    {
        $payment_method_selected = $this->form->getState()['payment_method'];
        if ($payment_method_selected == null) {
            Notification::make()
                ->danger()
                ->title('Payment method not selected')
                ->send();
            return;
        }

        return redirect()
            ->route(
                'payment.process',
                [
                    'payment_method' => $this->candidate_payment_methods [$payment_method_selected],
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
            //      ->redirect('/candidate/payment'),
        ];
    }
}
