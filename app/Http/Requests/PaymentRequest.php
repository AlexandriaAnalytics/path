<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; //TODO: need candidate info session
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payment_method' => ['required', 'string', 'exists:payment_methods,slug'],
            'link_to_ticket' => ['string'],
            
        ];
    }

    public function messages()
    {
        return [
            'payment_method.required' => 'El método de pago es requerido',
            'payment_method.string' => 'El método de pago debe ser una cadena de texto',
            'payment_method.in' => 'El método de pago no es válido',
        ];
    }
    
}
