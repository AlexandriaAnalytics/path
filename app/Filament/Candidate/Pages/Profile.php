<?php

namespace App\Filament\Candidate\Pages;

use App\Models\Candidate;
use Filament\Pages\Page;

class Profile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.candidate.pages.profile';

    protected static ?int $navigationSort = -2;

    public $candidate;
    public function __construct()
    {
        $this->candidate = Candidate::find(session('candidate')->id);
    }
}
