<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class InformationSettings extends Settings
{
    public ?string $files_url;

    public static function group(): string
    {
        return 'information';
    }
}
