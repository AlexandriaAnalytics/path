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
    public ?string $monetariUnitSymbol;

    public function __construct()
    {
        $this->candidate = \App\Models\Candidate::find(session('candidate')->id);
        $this->monetariUnitSymbol = $this->candidate->student->region->monetary_unit_symbol ?? '$';
        $this->total_amount = random_int(1000, 5000);
    }

    public ?string $payment_method = null;
    public ?int $total_amount = 0;
    

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.candidate.pages.payments';

    public static function canAccess(): bool
    {
        return isset(session('candidate')->candidate_number);
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
       $form->schema([
        
           Select::make('payment_method')   
               ->options($this->candidate->student->region->paymentMethods()->pluck('name', 'slug')->toArray())
        
               
       ]);

         return $form;
   }

   public function selectPaymentMethod()
   {
    Notification::make() 
            ->title('Saved successfully')
            ->success()
            ->send(); 
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
