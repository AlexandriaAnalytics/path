<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RecordResource\Pages;
use App\Filament\Admin\Resources\RecordResource\RelationManagers;
use App\Models\Candidate;
use App\Models\Level;
use App\Models\Record;
use App\Models\Section;
use App\Models\StatusActivity;
use App\Models\Trainee;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group as GroupingGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class RecordResource extends Resource
{
    protected static ?string $model = Record::class;

    protected static ?string $navigationGroup = 'Training';
    protected static ?string $navigationIcon = 'heroicon-s-document-chart-bar';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('trainee_id')
                    ->label('Trainee'),
                Select::make('section_id')
                    ->label('Section')
                    ->options(Section::all()->pluck('name', 'id'))
                    ->required(),
                Select::make('status_activity_id')
                    ->label('Status section')
                    ->options(StatusActivity::all()->pluck('name', 'id'))
                    ->required(),
                TextInput::make('comments')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->groups([
                GroupingGroup::make('trainee.user.name')
                    ->groupQueryUsing(fn (Builder $query) => $query->groupBy('trainee.full_name'))
                    ->collapsible(),
            ])
            ->defaultGroup('trainee.user.name')
            ->columns([
                TextColumn::make('trainee.user.name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('section_id')
                    ->label('Section')
                    ->formatStateUsing(function ($state) {
                        return Level::find($state)->name;
                    })
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('result')
                    ->default('-')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('status_activity_id')
                    ->label('Status section')
                    ->formatStateUsing(function ($state) {
                        return StatusActivity::find($state)->name;
                    })
                    ->badge()
                    ->color(function ($state) {
                        return Color::hex(StatusActivity::find($state)->color);
                    })
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('comments')
                    ->sortable()
                    ->searchable()
                    ->default('-')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('updated_at')
                    ->label('Updated on')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Action::make('download-pdf')
                        ->visible(function (Record $record) {
                            return $record->result == null ? false : true;
                        })
                        ->label('Download PDF')
                        ->icon('heroicon-o-document')
                        ->action(function (Record $record) {
                            //dd($record->trainee->answers[3]->question->multipleChoices[0]->answers);
                            try {
                                $filename = "{$record->trainee->user->name} {$record->trainee->user->surname} - {$record->section->name}.pdf";
                                $pdfPath = storage_path('app/temp_pdfs') . '/' . $filename;

                                // Ensure the temporary directory exists and has write permissions
                                if (!File::exists(storage_path('app/temp_pdfs'))) {
                                    File::makeDirectory(storage_path('app/temp_pdfs'), 0755, true); // Create directory with appropriate permissions
                                }
                                Pdf::loadView('pdf.record', ['record' => $record])
                                    ->save($pdfPath);
                                return response()->download($pdfPath, $filename, [
                                    'Content-Type' => 'application/pdf',
                                ]);
                            } catch (\Exception $e) {
                                return response()->json(['error' => 'PDF generation or download failed'], 500);
                            }
                        }),
                    Action::make('refresh-status')
                        ->label('Refresh status')
                        ->icon('heroicon-o-arrow-path')
                        ->action(function (Record $record) {
                            $record->result = null;
                            $record->save();
                        })
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    BulkAction::make('refresh-status')
                        ->label('Refresh status')
                        ->icon('heroicon-o-arrow-path')
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                $record->result = null;
                                $record->save();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('update-status')
                        ->label('Update status')
                        ->icon('heroicon-o-arrows-right-left')
                        ->form([
                            Select::make('status_activity_id')
                                ->label('Status section')
                                ->options(StatusActivity::all()->pluck('name', 'id'))
                                ->required(),
                        ])
                        ->action(function (Collection $records, $data) {
                            foreach ($records as $record) {
                                $record->status_activity_id = $data['status_activity_id'];
                                $record->save();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('download-pdf')
                        ->label('Download PDF')
                        ->icon('heroicon-o-arrow-path')
                        ->icon('heroicon-o-document')
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                $filename = "{$record->trainee->user->name} {$record->trainee->user->surname} - {$record->section->name}.pdf";
                                $pdfPath = storage_path('app/temp_pdfs') . '/' . $filename;

                                // Ensure the temporary directory exists and has write permissions
                                if (!File::exists(storage_path('app/temp_pdfs'))) {
                                    File::makeDirectory(storage_path('app/temp_pdfs'), 0755, true); // Create directory with appropriate permissions
                                }
                                Pdf::loadView('pdf.record', ['record' => $record])
                                    ->save($pdfPath);
                                return response()->download($pdfPath, $filename, [
                                    'Content-Type' => 'application/pdf',
                                ]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListRecords::route('/'),
            'create' => Pages\CreateRecord::route('/create'),
            'edit' => Pages\EditRecord::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
