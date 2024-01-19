<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public ?string $client_link;

    public static function group(): string
    {
        return 'general';
    }
}
