<?php

namespace App\Filament\Candidate\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Payments extends Page implements HasForms
{
    public $candidate;
    private $country;
    public ?string $monetariUnitSymbol;
    public ?string $payment_method = null;
    public int $total_amount = 0;
    public ?bool $canApplyToDiscount = false;

    public $modules = [];

    public function __construct()
    {
        $this->candidate = \App\Models\Candidate::find(session('candidate')->id);
        $this->country = $this->candidate->student->region->name;

        $this->total_amount += $this->candidate->total_amount;

        $this->monetariUnitSymbol = $this->candidate->getMonetaryString();
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

    public function payWithPaypal()
    {
        return redirect()->route('payment.process', ['payment_method' => 'paypal', 'amount_value' => $this->total_amount]);
    }

    public function payWithPaypal3Cuotas()
    {
        return redirect()->route('payment.process.cuotas', ['payment_method' => 'paypal', 'amount_value' => $this->total_amount, 'cuotas' => 3]);
    }

    public function payWithMercadoPago()
    {
        return redirect()->route('payment.process.cuotas', ['payment_method' => 'mercado_pago', 'amount_,value' => $this->total_amount, 'cuotas' => 3]);
    }

    protected function getActions(): array
    {
        return [
            Action::make('Print ticket')
                ->icon('heroicon-o-printer'),

            Action::make('Pay With Paypal')
                ->icon('heroicon-o-currency-dollar')
                ->action(fn () => $this->payWithPaypal()),
            // ->message('Printed successfully.')
            // ->perform(fn () => redirect()->route('candidate.payment')),

            Action::make('3Cuotas')
                ->icon('heroicon-o-currency-dollar')
                ->action(fn () => $this->payWithPaypal3Cuotas()),

            Action::make('3cuotasmp')
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

        if ($payment_method_selected == 'stripe' || $payment_method_selected == 'Stripe') {
            Notification::make()
                ->danger()
                ->title('Method in construction 🚧')
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
