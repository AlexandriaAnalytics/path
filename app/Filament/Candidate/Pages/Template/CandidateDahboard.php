<?php

namespace App\Filament\Candidate\Pages\Template;

use App\Filament\Candidate\Resources\CandidateResource\Widgets\ExamSession;
use App\Filament\Candidate\Widgets\WidgetCandidate;
use App\Models\Candidate;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard;
use Filament\Pages\Page;
use Filament\Resources\Concerns\HasTabs;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Panel;

class CandidateDahboard extends Dashboard  //implements HasTable
{

    // use InteractsWithTable;

    protected static ?int $navigationSort = -2;
    protected static string $view = 'filament.candidate.pages.template.candidate-dahboard';

    public static function canAccess(): bool
    {
        return isset(session('candidate')->id);
    }

    public function mount()
    {
        abort_unless(static::canAccess(), 403);
    }

    public function getHeading(): string|Htmlable
    {
        return 'Candidate dashboard';
    }

    public function getWidgets(): array
    {
        return [
            WidgetCandidate::class,
            ExamSession::class
        ];
    }

    protected function getActions(): array
    {
        return [
            //Action::make('Print ticket')
            //    ->icon('heroicon-o-printer'),
            Action::make('important')
                ->label('Payments may take up to 48 hours to be reflected')
                ->disabled()
                ->color('warning')
                ->badge(),
            Action::make('Logout')
                ->color('danger')
                ->url('/candidate/logout'),
        ];
    }
}
