<?php

namespace App\Filament\Candidate\Pages;

use App\Models\Candidate;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;

class ExamSessions extends Page implements HasForms
{
    public $candidate;

    public function __construct()
    {
        $this->candidate = Candidate::find(session('candidate')->id);
    }

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static string $view = 'filament.candidate.pages.examSessions';

    public static function canAccess(): bool
    {
        return isset(session('candidate')->id);
    }

    public function mount()
    {
        abort_unless(static::canAccess(), 403);
    }
}
