<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ExamSettings extends Settings
{
    public bool $registrations_open;

    public static function group(): string
    {
        return 'exam';
    }
}
