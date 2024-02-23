<?php

namespace App\Filament\Candidate\Pages;

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
        return [
          /*
            Action::make('Print ticket')
                ->icon('heroicon-o-printer'),
            */
          
            Action::make('3Cuotas')
                ->label('financing in paypal ' . $this->instalment_number . ' instalments')
                ->icon('heroicon-o-currency-dollar')
                ->action(fn () => $this->payWithPaypal3Cuotas()),

            Action::make('3cuotasmp')
                ->label('financing in Mercado Pago ' . $this->instalment_number . ' instalments')
                ->icon('heroicon-o-currency-dollar')
                ->action(fn () => $this->payWithMercadoPago()),
        ];
    }

    public function form(Form $form): Form
    {
        // dd($this->candidate->modules->pluck('name')->toArray());
        $form->schema([
            Select::make('payment_method')
                ->label('Payment method')
                ->placeholder('Select a payment method')
                ->native(false)
                ->options([
                    'paypal' => 'Paypal',
                    'stripe' => 'Stripe',
                    'mercado_pago' => 'Mercado Pago',
                ])
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
            //      ->redirect('/candidate/payment'),
        ];
    }
}
