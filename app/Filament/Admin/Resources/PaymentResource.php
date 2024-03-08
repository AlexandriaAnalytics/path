<?php

namespace App\Filament\Admin\Resources;

use App\Enums\PaymentMethod;
use App\Filament\Admin\Resources\PaymentResource\Pages;
use App\Models\Candidate;
use App\Models\Country;
use App\Models\Institute;
use App\Models\Payment;
use Carbon\Carbon;
use Cmgmyr\PHPLOC\Log\Text;
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
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationLabel = 'Payments';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('amount')
                ->required()
                ->numeric(),

                Select::make('patment_method')
                ->options(PaymentMethod::values()),


                Select::make('candidate_id')
                ->required()
                ->relationship(titleAttribute:'id', name:'candidate')

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
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('status')
                    ->color(fn(string $state) => match ($state) {
                        'pending', 'rejected' => 'danger',
                        'approved' => 'success',
                        'processing payment' => 'warning'
                    } )
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make(('payment_ticket_link'))
                    ->action(fn (string $payment) => redirect()->to($payment))
                    ->color('primary')

            ])
            ->filters([
                Filter::make('payment_method')
                ->label('show installments')
                 ->query(fn(Builder $query) 
                    => $query->where('payment_method', 'financing by associated'))
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

                        if ($payment->candidate_id != null) {
                            $candidate = Candidate::find($payment->candidate_id);
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
                        }
                    }),
                Tables\Actions\Action::make('edit')
                ->form([

                    ])
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
