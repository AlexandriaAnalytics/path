<?php

namespace App\Modules\Payments;

use Illuminate\Http\Request;
use Spatie\WebhookClient\Exceptions\InvalidConfig;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator as DefaultSignatureValidator;
use Spatie\WebhookClient\WebhookConfig;

class SkipSignatureValidation implements DefaultSignatureValidator
{
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        return true;
    }
}
