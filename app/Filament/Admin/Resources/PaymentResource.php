<?php

namespace App\Filament\Admin\Resources;

use App\Enums\PaymentMethod;
use App\Filament\Admin\Resources\PaymentResource\Pages;
use App\Models\Candidate;
use App\Models\Payment;
use App\Models\Student;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Grouping\Group;
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
                Select::make('payment_method')
                    ->options(PaymentMethod::values()),
                Select::make('candidate_id')
                    ->required()
                    ->relationship(titleAttribute: 'id', name: 'candidate')

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function () {
                return Payment::orderByDesc('created_at');
            })
            ->groups([
                Group::make('payment_id')
                    ->groupQueryUsing(fn (Builder $query) => $query->groupBy('payment_id'))
                    ->collapsible(),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('payment_method')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_id')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('candidate')
                    ->formatStateUsing(function (Candidate $state) {
                        return $state->id . ' - ' . $state->student->name . ' ' . $state->student->surname;
                    }),
                Tables\Columns\TextColumn::make('currency')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('status')
                    ->color(fn (string $state) => match ($state) {
                        'pending', 'rejected' => 'danger',
                        'approved' => 'success',
                        'processing payment' => 'warning'
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('link_to_ticket')
                    ->url(fn (Payment $record) => $record->link_to_ticket, shouldOpenInNewTab: true)
                    ->color('primary')

            ])
            ->filters([
                Filter::make('payment_method')
                    ->label('Hide installments')
                    ->query(fn (Builder $query) => $query->where('payment_method', '!=', 'financing by associated')),
            ])
            ->actions([
                Tables\Actions\Action::make('Update state')
                    ->form([
                        Select::make('status')
                            ->options([
                                'pending' => 'pending', 'approved' => 'approved', 'rejected' => 'rejected', 'processing payment' => 'processing payment'
                            ])
                    ])
                    ->action(function (array $data, Payment $payment) {
                        $payment->update(['status' => $data['status']]);

                        $payment->payments->each(function (Payment $p) {
                            $p->financing->update(['state' => 'complete']);
                            $p->update(['status' => 'approved']);
                        });
                    }),
                Tables\Actions\Action::make('edit')
                    ->form([])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->deselectRecordsAfterCompletion(),
                    BulkAction::make('update_state')
                        ->icon('heroicon-o-arrows-right-left')
                        ->form([
                            Select::make('status')
                                ->options([
                                    'pending' => 'pending', 'approved' => 'approved', 'rejected' => 'rejected', 'processing payment' => 'processing payment'
                                ])
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $payment) {
                                $payment->status = $data['status'];
                                $payment->save();
                            }
                        })
                        ->deselectRecordsAfterCompletion()
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
