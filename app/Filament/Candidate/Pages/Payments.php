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
    private $candidate;
    private $country;
    public ?string $monetariUnitSymbol;
    public ?string $payment_method = null;
    public ?int $total_amount = 0;
    public ?bool $canApplyToDiscount = false;

    public $modules = [];
    
    public function __construct()
    { //$candidate->modules[1]->countryModules[0]->price
        // $candidate->modules[1]->countryModules[0]->country->name

        $this->candidate = \App\Models\Candidate::find(session('candidate')->id);
        $this->country = $this->candidate->student->region->name;
        
        $this->modules = $this->candidate->modules->map(function($module){
            return [
                'name' => $module->name,
                'price' => $module->getPriceBasedOnRegion($this->candidate->student->region)
            ];
        });

        $this->canApplyToDiscount = count($this->modules) == 3; 

        if($this->canApplyToDiscount){
            // (Valor Original +valor fijo ) *((100+valor porcentaje)/100)
            $price_with_discount = $this->candidate->student->institute->discounted_price_diferencial;
            $price_With_discount_percentage = $this->candidate->student->institute->discounted_price_percentage; 
            
            $this->total_amount = 
            ($price_with_discount) * (( 100 + $price_With_discount_percentage ) / 100);
            
        }else {
            $priceDiferencinal = $this->candidate->student->institute
            ->getLevelPaymentDiferencial($this->candidate->student->level->name);
            
            $fixedPrice = $priceDiferencinal->institute_diferencial_aditional_price;
            $percentagePrice = $priceDiferencinal->institute_diferencial_percentage_price;
            
            $this->total_amount = ($this->modules->sum('price') + $fixedPrice) * ((100 + $percentagePrice) / 100);
        }
        $price_right_exam = $this->candidate->student->institute->rigth_exam_diferencial;
        $this->total_amount += $price_right_exam;
        
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

    protected function getActions(): array
    {
        return [
            Action::make('Print ticket')
                ->icon('heroicon-o-printer'),
            // ->message('Printed successfully.')
            // ->perform(fn () => redirect()->route('candidate.payment')),
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
                ->options($this->candidate->student->region->paymentMethods()->pluck('name', 'slug')->toArray())
        ]);

        return $form;
    }

    public function selectPaymentMethod()
    {
        $payment_method_selected = $this->form->getState()['payment_method'];
        if($payment_method_selected == null){
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
                    'amount' => $this->total_amount
                ]);
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
