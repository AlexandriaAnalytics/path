<?php

namespace App\Filament\Candidate\Pages;

use App\Enums\PaymentMethod;
use App\Enums\UserStatus;
use App\Models\Candidate;
use App\Models\CandidateExam;
use App\Models\Country as ModelsCountry;
use App\Models\Payment;
use App\Models\PaymentMethod as ModelsPaymentMethod;
use Carbon\Carbon;
use DateTime;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Routing\Router;

class Payments extends Page implements HasForms
{
    public $candidate;
    public $candidate_payment_methods = [];
    public ?string $monetariUnitSymbol;
    public ?string $payment_method = null;
    public ?string $link_to_ticket = null;
    public ?string $description = null;
    public int $total_amount = 0;
    public ?bool $canApplyToDiscount = false;
    public int $installment_number = 0;
    public ?DateTime $examDate;
    public $modules = [];
    public bool $showTransferForm = false;
    public $bankData = null;


    public function __construct()
    {
        $this->candidate = Candidate::find(session('candidate')->id);
        $this->candidate_payment_methods = $this->candidate->student->region->paymentMethods()->pluck('name')->toArray();

        $this->total_amount += $this->candidate->total_amount;

        $this->monetariUnitSymbol = $this->candidate->getMonetaryString();

        $exam = CandidateExam::where('candidate_id', $this->candidate->id)->first();
        if ($exam) {
            $this->examDate = $exam->exam->scheduled_date;
            if ($this->candidate->installments) {
                $this->installment_number = $this->candidate->installments;
            }
        }


        $this->bankData = ModelsPaymentMethod::where('name', 'Transfer')->first()->description;




        /* usar este metodo si la devuelve la cantidad en meses hasta el ultimo examen
            puede devolver null si no existen mesas de examen o si la fecha del examen es negativa (esto no deberia pasar...)
        */
        // this->installment_number = $this->installments_available; usar este metodo para obtener la cantidad de cuotas disponibles si la fecha
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
        return redirect()->route('payment.process.cuotas', ['payment_method' => 'paypal', 'amount_value' => $this->total_amount, 'cuotas' => $this->installment_number]);
    }

    public function mercadoPagoFinanciament()
    {
        return redirect()->route('payment.process.cuotas', ['payment_method' => 'mercado_pago', 'amount_value' => $this->total_amount, 'cuotas' => $this->installment_number]);
    }

    public function strypeFinanciament()
    {
        return redirect()->route('payment.process.cuotas', [
            'payment_method' => 'stripe',
            'amount_value' => $this->total_amount,
            'cuotas' => $this->installment_number
        ]);
    }

    private function renderPaypalFinancing(bool $hidde)
    {
        return
            Action::make('paypal_financing')
            ->label('Financing with PayPal (' . $this->installment_number . ' installments)')
            ->icon('heroicon-o-currency-dollar')
            ->action(fn () => $this->paypalFinaciament())
            ->hidden(!$hidde);
    }

    private function renderMercadoPagoFinancing(bool $hidde)
    {
        return
            Action::make('MP_financing')
            ->label('Subscription payment (' . $this->installment_number . ' installments)')
            ->icon('heroicon-o-currency-dollar')
            ->action(fn () => $this->mercadoPagoFinanciament())
            ->hidden(!$hidde);
    }

    private function renderStripeFinancing(bool $hidde)
    {
        return
            Action::make('stripe_financing')
            ->label('Financing with stripe (' . $this->installment_number . ' installments)')
            ->icon('heroicon-o-currency-dollar')
            ->action(fn () => $this->strypeFinanciament())
            ->hidden(!$hidde);
    }

    protected function getActions(): array
    {
        $paymentMethodsAvailable = ModelsCountry::all()->where('monetary_unit', $this->candidate->currency)->first()->pyMethods()->get()->pluck('slug')->toArray();
        return [
            $this->renderPaypalFinancing(
                in_array(PaymentMethod::PAYPAL->value, $paymentMethodsAvailable)
                    && $this->candidate->status == 'unpaid'
                    && $this->candidate->installments > 0
                    && $this->candidate->student->institute->installment_plans
                    && !$this->candidate->student->institute->internal_payment_administration
            ),
            $this->renderStripeFinancing(
                in_array(PaymentMethod::STRIPE->value, $paymentMethodsAvailable)
                    && $this->candidate->status == 'unpaid'
                    && $this->candidate->installments > 0
                    && $this->candidate->student->institute->installment_plans
                    && !$this->candidate->student->institute->internal_payment_administration
            ),
            $this->renderMercadoPagoFinancing(
                in_array(PaymentMethod::MERCADO_PAGO->value, $paymentMethodsAvailable)
                    && $this->candidate->status == 'unpaid'
                    && $this->candidate->installments > 0
                    && $this->candidate->student->institute->installment_plans
                    && !$this->candidate->student->institute->internal_payment_administration
            ) // not implemented yet
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('payment_method')
                    ->label('Payment method')
                    ->placeholder('Select a payment method')
                    ->native(false)
                    ->reactive()
                    ->options(function () {
                        $options = $this->candidate->student->region->paymentMethods()->pluck('name', 'slug')->toArray();
                        return $options;
                    })
                    ->afterStateUpdated(function (callable $set, callable $get) {
                        return $set('description', ModelsPaymentMethod::where('slug', $get('payment_method'))->first()->description);
                    }),
                RichEditor::make('description')
                    ->disableAllToolbarButtons()
                    ->visible(function (callable $get) {
                        return $get('payment_method');
                    })
            ])->columns(2);
    }

    public function getForms(): array
    {
        return [
            'form',
            'formTransfer',
        ];
    }

    public function formTransfer(Form $form): Form
    {

        $form->schema([

            TextInput::make('link_to_ticket')->required(),
            MarkdownEditor::make('description')

        ]);

        return $form;
    }

    public function submitFormTransfer()
    {


        Payment::create([
            'payment_id' => 't-' . Carbon::now()->timestamp . rand(1000, 10000),
            'currency' => $this->candidate->currency,
            'amount' => $this->candidate->total_amount,
            'candidate_id' => $this->candidate->id,
            'link_to_ticket' => $this->formTransfer->getState()['link_to_ticket'],
            'current_period' => Carbon::now()->day(1),
            'paid_date' => Carbon::now(),
            'payment_method' => 'transcerence',
            'status' => 'processing payment',
            'description' => $this->formTransfer->getState()['description'],
        ]);
        Candidate::find($this->candidate->id)->update(['status' => UserStatus::Processing_payment]);

        Notification::make('successful')
            ->title('payment processed')
            ->color('success')
            ->send();

        $this->showTransferForm = false;
        redirect()->route('filament.candidate.pages.payments');
        return;
    }


    public function selectPaymentMethod()
    {
        $payment_method_selected = $this->form->getState()['payment_method'];

        if ($payment_method_selected == 'transfer') {
            return $this->showTransferForm = true;
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
        ];
    }
}
