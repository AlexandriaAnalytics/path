<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PaymentResource\Pages;
use App\Models\Candidate;
use App\Models\Country;
use App\Models\Institute;
use App\Models\Payment;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;;

use Filament\Forms\Set;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationLabel = 'Payments';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Candidate Payment')
                            ->schema([
                                Forms\Components\TextInput::make('amount')
                                    ->numeric()
                                    ->required(),
                                Select::make('currency')
                                    ->options(Country::all()->map(fn (Country $country) => [$country->monetary_unit => $country->monetary_unit])->collapse()->toArray())
                                    ->required(),
                                Select::make('candidate_id')
                                    ->label('Candidate')
                                    ->options(Candidate::all()->map(fn (Candidate $candidate) => [$candidate->id => $candidate->id . '-' . $candidate->student->name . ' ' . $candidate->student->surname])->collapse()->toArray())
                                    ->searchable(),
                                    
                                TextInput::make('payment_id')
                                    ->default('d' . Carbon::now()->timestamp . rand(1000, 9000))
                                    ->disabled(true),
                                Select::make('payment_method')
                                    ->relationship('payment_method')
                                    ->options([ //TODO: sacar fisic
                                        'fisic' => 'fisic', 'deposit' => 'deposit'
                                    ])
                                    ->updateOptionUsing(fn (string $state, Set $set) => Set('payment_id', 'hola'))
                                    ->live()
                                    ->required(),

                                Forms\Components\Select::make('status')
                                    ->options([
                                        'pending' => 'pending', 'approved' => 'approved', 'rejected' => 'rejected', 'processing payment' => 'processing payment'
                                    ])
                                    ->required(),
                            ]),
                        Tabs\Tab::make('Institute Payment')
                            ->schema([
                                Forms\Components\TextInput::make('amount')
                                    ->numeric()
                                    ->required(),
                                Select::make('currency')
                                    ->options(Country::all()->map(fn (Country $country) => [$country->monetary_unit => $country->monetary_unit])->collapse()->toArray())
                                    ->required(),
                                Select::make('institute_id')
                                    ->label('Institute')
                                    ->options(Institute::all()->map(fn (Institute $institute) => [$institute->id => $institute->name])->collapse()->toArray())
                                    ->searchable(),
                                    
                                TextInput::make('payment_id')
                                    ->default('d' . Carbon::now()->timestamp . rand(1000, 9000))
                                    ->disabled(true),
                                Select::make('payment_method')
                                    ->relationship('payment_method')
                                    ->options([ //TODO: sacar fisic
                                        'fisic' => 'fisic', 'deposit' => 'deposit'
                                    ])
                                    ->required(),

                                Forms\Components\Select::make('status')
                                    ->options([
                                        'pending' => 'pending', 'approved' => 'approved', 'rejected' => 'rejected', 'processing payment' => 'processing payment'
                                    ])
                                    ->required(),
                                    Hidden::make('current_period')->default(fn() => Carbon::now()->day(1)),
                                Forms\Components\DatePicker::make('paid_date'),
                            ])


                    ]),




            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payment_method')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_id')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('candidate.id'),
                Tables\Columns\TextColumn::make('currency')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make(('paymeny_ticket'))
                    ->default(fn (Payment $payment) => $payment->candidate->payment_ticket_link)
                    ->action(fn (Payment $payment) => redirect()->to($payment->payment_ticket_link))
                    ->color('primary')

            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('update state')
                    ->form([
                        Select::make('status')
                            ->options([
                                'pending' => 'pending', 'approved' => 'approved', 'rejected' => 'rejected', 'processing payment' => 'processing payment'
                            ])
                    ])
                    ->action(function (array $data, Payment $payment) {
                        $payment->update(['status' => $data['status']]);

                        $candidate = Candidate::find($payment->candidate->id);
                        switch ($data['status']) {
                            case 'pending':
                                $candidate->update(['status' => 'processing payment']);
                                break;
                            case 'approved':
                                $candidate->update(['status' => 'paid']);
                                break;
                            case    'rejected':
                                $candidate->update(['status' => 'unpaid']);
                                break;
                            case  'processing payment':
                                $candidate->update(['status' => 'processing payment']);
                                break;
                        }
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
