<?php

namespace App\Enums;

enum ModuleType: string
{
    case Online = 'online';
    case Onsite = 'onsite';
    case Both = 'both';
}
