<?php

namespace App\Modules\Payments\MercadoPago\Utils;

use Illuminate\Http\Request;
use Spatie\WebhookClient\Exceptions\InvalidConfig;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator as DefaultSignatureValidator;
use Spatie\WebhookClient\WebhookConfig;

class SignatureValidator implements DefaultSignatureValidator
{
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        $header = $request->header($config->signatureHeaderName);

        if (!$header) {
            return false;
        }

        $signingSecret = $config->signingSecret;

        if (empty($signingSecret)) {
            throw InvalidConfig::signingSecretNotSet();
        }

        // https://www.mercadopago.com.ar/developers/es/docs/your-integrations/notifications/webhooks
        [$timestamp, $key] = explode(',', $header);

        $timestamp = explode('=', $timestamp)[1];
        $key = explode('=', $key)[1];

        $request_id = $request->header('x-request-id');
        $data_id = $request->query('data_id', '');

        $manifest = "id:{$data_id};request-id:{$request_id};ts:{$timestamp};";

        $computedSignature = hash_hmac('sha256', $manifest, $signingSecret);

        return hash_equals($key, $computedSignature);
    }
}
