<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PaymentResource\Pages;
use App\Models\Candidate;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('payment_id')
                    ->required(),
                Forms\Components\Select::make('payment_method')
                    ->relationship('payment_method')
                    ->options([ //TODO: sacar fisic
                        'paypal' => 'paypal', 'mercado_libre' => 'mercado_libre', 'fisic' => 'fisic', 'deposit' => 'deposit'
                    ])
                    ->required(),
                Forms\Components\TextInput::make('currency')
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'pending', 'approved' => 'approved', 'rejected' => 'rejected', 'processing payment' => 'processing payment'
                    ])
                    ->required(),
                /*
                Forms\Components\Select::make('candidate_id')
                ->relationship(name: 'candidate', titleAttribute: 'id')
                ->preload()
                ->required(),
                */
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
