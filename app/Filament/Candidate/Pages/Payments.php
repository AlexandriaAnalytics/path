<?php

namespace App\Filament\Candidate\Pages;

use App\Enums\PaymentMethod;
use App\Models\Candidate;
use App\Models\PaymentMethod as PaymentMethodModel;
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


    protected function getActions(): array
    {
        $actions = [];
        if ($this->candidate->student->institute->instituteType()->first()->name == 'Premium Exam Centre') {
            if (
                in_array(str_replace('_', ' ', strtolower(PaymentMethod::PAYPAL->value)), array_map(fn ($item) => strtolower($item), $this->candidate->student->region->paymentMethods()->pluck('name')->toArray()))
            ) {
                $actions = array_merge($actions, [
                    Action::make('paypal_financing')
                        ->label('Financing with PayPal (' . $this->instalment_number . ' instalments)')
                        ->icon('heroicon-o-currency-dollar')
                        ->action(fn () => $this->paypalFinaciament()),
                ]);
            } else if (
                in_array(str_replace('_', ' ', strtolower(PaymentMethod::MERCADO_PAGO->value)), array_map(fn ($item) => strtolower($item), $this->candidate->student->region->paymentMethods()->pluck('name')->toArray()))
            ) {
                $actions = array_merge($actions, [
                    Action::make('MP_financing')
                        ->label('Financing with Mercado Pago (' . $this->instalment_number . ' instalments)')
                        ->icon('heroicon-o-currency-dollar')
                        ->disabled(fn () => $this->candidate->student->email == null)
                        ->action(fn () => $this->mercadoPagoFinanciament()),
                ]);
            }
        }

        return $actions;
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
        /*
        if (!in_array($payment_method_selected, PaymentMethodModel::all()->pluck('slug')->toArray())){
            Notification::make()
                ->danger()
                ->title('Payment method not selected')
                ->send();
            return;
            }
*/
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
